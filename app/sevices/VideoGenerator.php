<?php
// app/services/VideoGenerator.php
// ─── Orchestrateur principal du pipeline de génération vidéo ────────
// Compatible PHP 7.3+ (pas de typed properties ni d'arrow functions)
 
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/api_keys.php';
require_once __DIR__ . '/ScriptService.php';
require_once __DIR__ . '/VoiceService.php';
require_once __DIR__ . '/ImageService.php';
require_once __DIR__ . '/VideoAssembler.php';
 
class VideoGenerator
{
    /** @var PDO */
    private $db;
    /** @var ScriptService */
    private $scriptService;
    /** @var VoiceService */
    private $voiceService;
    /** @var ImageService */
    private $imageService;
    /** @var VideoAssembler */
    private $videoAssembler;
 
    public function __construct()
    {
        $this->db             = getDB();
        $this->scriptService  = new ScriptService();
        $this->voiceService   = new VoiceService();
        $this->imageService   = new ImageService();
        $this->videoAssembler = new VideoAssembler();
    }
 
    /**
     * Pipeline complet de génération vidéo.
     *
     * @param string $seriesId  ID de la série
     * @param string $userId    ID de l'utilisateur
     * @param string|null $topic Sujet spécifique (optionnel)
     * @return array            Résultat de la génération
     * @throws Exception
     */
    public function generate(string $seriesId, string $userId, ?string $topic = null): array
    {
        // 1. Récupérer la configuration de la série
        $series = $this->getSeries($seriesId, $userId);
        if (!$series) {
            throw new Exception('Series not found or access denied');
        }
 
        $contentConfig = json_decode($series['content_config'], true) ?: [];
        $contentRules  = json_decode($series['content_rules'], true) ?: [];
 
        $voiceKey     = $contentConfig['voice'] ?? 'adam';
        $voiceSpeed   = (float)($contentConfig['voice_speed'] ?? 1.0);
        $imageStyle   = $contentConfig['image_style'] ?? 'cinematic';
        $videoLength  = (int)($contentConfig['video_length'] ?? 60);
        $captionStyle = $contentConfig['caption_style'] ?? 'karaoke';
        $tone         = $contentRules['tone'] ?? 'dramatic';
 
        // 2. Créer l'entrée vidéo en BDD (status = processing)
        $videoId = $this->createVideoRecord($seriesId, $userId);
        $this->updateVideoStatus($videoId, 'processing');
 
        try {
            // ── Étape 1 : Générer le script IA ──────────────────────
            $this->logProgress($videoId, 'script', 'Generating script...');
            $script = $this->scriptService->generateScript(
                $series['niche'],
                $tone,
                $videoLength,
                'en',
                $topic
            );
 
            // Sauvegarder le script dans la BDD
            $this->updateVideoScript($videoId, $script);
            $this->logProgress($videoId, 'script', 'Script generated: ' . $script['title']);
 
            // ── Étape 2 : Générer l'audio (narration) ───────────────
            $this->logProgress($videoId, 'audio', 'Generating voice narration...');
 
            // Combiner toute la narration
            $fullNarration = '';
            foreach ($script['scenes'] as $scene) {
                $fullNarration .= $scene['narration'] . ' ';
            }
            $fullNarration = trim($fullNarration);
 
            $audioFile = $this->voiceService->generateAudio(
                $fullNarration,
                $voiceKey,
                $voiceSpeed
            );
            $this->logProgress($videoId, 'audio', 'Audio narration generated');
 
            // Sauvegarder l'URL de l'audio
            $audioUrl = str_replace(UPLOAD_DIR, UPLOADS_URL, $audioFile);
            $this->updateVideoField($videoId, 'audio_url', $audioUrl);
 
            // ── Étape 3 : Générer les images des scènes ─────────────
            $this->logProgress($videoId, 'images', 'Generating scene images...');
            $images = $this->imageService->generateSceneImages(
                $script['scenes'],
                $imageStyle
            );
            $this->logProgress($videoId, 'images', count($images) . ' images generated');
 
            // Sauvegarder les URLs des images
            $imageUrls = array_map(
                function ($img) {
                    return str_replace(UPLOAD_DIR, UPLOADS_URL, $img);
                },
                $images
            );
            $this->updateVideoField($videoId, 'images_used', json_encode($imageUrls));
 
            // ── Étape 4 : Assembler la vidéo ────────────────────────
            $this->logProgress($videoId, 'assembly', 'Assembling final video...');
            $videoFile = $this->videoAssembler->assemble(
                $images,
                $audioFile,
                $script['scenes'],
                $captionStyle
            );
            $this->logProgress($videoId, 'assembly', 'Video assembled successfully');
 
            // Récupérer les infos de la vidéo
            $videoInfo = $this->videoAssembler->getVideoInfo($videoFile);
 
            // Générer la miniature
            $thumbnailFile = $this->generateThumbnail($videoFile);
            $thumbnailUrl  = $thumbnailFile
                ? str_replace(UPLOAD_DIR, UPLOADS_URL, $thumbnailFile)
                : null;
 
            // Sauvegarder les URLs et infos
            $videoUrl = str_replace(UPLOAD_DIR, UPLOADS_URL, $videoFile);
 
            $this->db->prepare(
                'UPDATE videos SET
                    video_url = ?, thumbnail_url = ?,
                    duration_seconds = ?, file_size_bytes = ?,
                    hashtags_used = ?,
                    status = "completed",
                    processing_completed_at = NOW()
                 WHERE id = ?'
            )->execute([
                $videoUrl,
                $thumbnailUrl,
                $videoInfo['duration_seconds'],
                $videoInfo['file_size_bytes'],
                implode(',', $script['hashtags'] ?? []),
                $videoId,
            ]);
 
            // Mettre à jour les stats de la série
            $this->db->prepare(
                'UPDATE content_series SET total_posts = total_posts + 1, last_post_at = NOW() WHERE id = ?'
            )->execute([$seriesId]);
 
            // Mettre à jour les stats utilisateur
            $this->updateUserStats($userId);
 
            $this->logProgress($videoId, 'complete', 'Video generation complete!');
 
            return [
                'success'       => true,
                'video_id'      => $videoId,
                'title'         => $script['title'],
                'video_url'     => $videoUrl,
                'thumbnail_url' => $thumbnailUrl,
                'duration'      => $videoInfo['duration_seconds'],
                'status'        => 'completed',
            ];
 
        } catch (Exception $e) {
            // Marquer la vidéo comme échouée
            $this->db->prepare(
                'UPDATE videos SET status = "failed", error_message = ?, processing_completed_at = NOW() WHERE id = ?'
            )->execute([$e->getMessage(), $videoId]);
 
            $this->logProgress($videoId, 'error', 'Generation failed: ' . $e->getMessage());
 
            throw $e;
        }
    }
 
    /**
     * Accepte un videoId existant pour permettre la génération
     * d'une vidéo initiale lors de la création de la série.
     */
    public function generateWithId(string $videoId, string $seriesId, string $userId, ?string $topic = null): array
    {
        $series = $this->getSeries($seriesId, $userId);
        if (!$series) {
            throw new Exception('Series not found or access denied');
        }
 
        $contentConfig = json_decode($series['content_config'], true) ?: [];
        $contentRules  = json_decode($series['content_rules'], true) ?: [];
 
        $voiceKey     = $contentConfig['voice'] ?? 'adam';
        $voiceSpeed   = (float)($contentConfig['voice_speed'] ?? 1.0);
        $imageStyle   = $contentConfig['image_style'] ?? 'cinematic';
        $videoLength  = (int)($contentConfig['video_length'] ?? 60);
        $captionStyle = $contentConfig['caption_style'] ?? 'karaoke';
        $tone         = $contentRules['tone'] ?? 'dramatic';
 
        $this->updateVideoStatus($videoId, 'processing');
 
        try {
            $this->logProgress($videoId, 'script', 'Generating script...');
            $script = $this->scriptService->generateScript(
                $series['niche'], $tone, $videoLength, 'en', $topic
            );
            $this->updateVideoScript($videoId, $script);
 
            $this->logProgress($videoId, 'audio', 'Generating voice narration...');
            $fullNarration = trim(implode(' ', array_column($script['scenes'], 'narration')));
            $audioFile = $this->voiceService->generateAudio($fullNarration, $voiceKey, $voiceSpeed);
            $audioUrl  = str_replace(UPLOAD_DIR, UPLOADS_URL, $audioFile);
            $this->updateVideoField($videoId, 'audio_url', $audioUrl);
 
            $this->logProgress($videoId, 'images', 'Generating scene images...');
            $images    = $this->imageService->generateSceneImages($script['scenes'], $imageStyle);
            $imageUrls = array_map(
                function ($img) {
                    return str_replace(UPLOAD_DIR, UPLOADS_URL, $img);
                },
                $images
            );
            $this->updateVideoField($videoId, 'images_used', json_encode($imageUrls));
 
            $this->logProgress($videoId, 'assembly', 'Assembling final video...');
            $videoFile = $this->videoAssembler->assemble($images, $audioFile, $script['scenes'], $captionStyle);
            $videoInfo = $this->videoAssembler->getVideoInfo($videoFile);
 
            $thumbnailFile = $this->generateThumbnail($videoFile);
            $thumbnailUrl  = $thumbnailFile ? str_replace(UPLOAD_DIR, UPLOADS_URL, $thumbnailFile) : null;
            $videoUrl      = str_replace(UPLOAD_DIR, UPLOADS_URL, $videoFile);
 
            $this->db->prepare(
                'UPDATE videos SET
                    video_url = ?, thumbnail_url = ?,
                    duration_seconds = ?, file_size_bytes = ?,
                    hashtags_used = ?, status = "completed",
                    processing_completed_at = NOW()
                 WHERE id = ?'
            )->execute([
                $videoUrl, $thumbnailUrl,
                $videoInfo['duration_seconds'], $videoInfo['file_size_bytes'],
                implode(',', $script['hashtags'] ?? []),
                $videoId,
            ]);
 
            $this->db->prepare(
                'UPDATE content_series SET total_posts = total_posts + 1, last_post_at = NOW() WHERE id = ?'
            )->execute([$seriesId]);
 
            $this->updateUserStats($userId);
            $this->logProgress($videoId, 'complete', 'Video generation complete!');
 
            return [
                'success'       => true,
                'video_id'      => $videoId,
                'title'         => $script['title'],
                'video_url'     => $videoUrl,
                'thumbnail_url' => $thumbnailUrl,
                'duration'      => $videoInfo['duration_seconds'],
                'status'        => 'completed',
            ];
 
        } catch (Exception $e) {
            $this->db->prepare(
                'UPDATE videos SET status = "failed", error_message = ?, processing_completed_at = NOW() WHERE id = ?'
            )->execute([$e->getMessage(), $videoId]);
            $this->logProgress($videoId, 'error', 'Generation failed: ' . $e->getMessage());
            throw $e;
        }
    }
 
    /**
     * Récupère une série par ID.
     */
    private function getSeries(string $seriesId, string $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM content_series WHERE id = ? AND user_id = ? AND status != "archived" LIMIT 1'
        );
        $stmt->execute([$seriesId, $userId]);
        return $stmt->fetch() ?: null;
    }
 
    /**
     * Crée un enregistrement vidéo en BDD.
     */
    private function createVideoRecord(string $seriesId, string $userId): string
    {
        $videoId = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
 
        $this->db->prepare(
            'INSERT INTO videos (id, series_id, user_id, script, status, queued_at, processing_started_at)
             VALUES (?, ?, ?, ?, "pending", NOW(), NOW())'
        )->execute([$videoId, $seriesId, $userId, '{}']);
 
        return $videoId;
    }
 
    /**
     * Met à jour le statut d'une vidéo.
     */
    private function updateVideoStatus(string $videoId, string $status): void
    {
        $this->db->prepare('UPDATE videos SET status = ? WHERE id = ?')
            ->execute([$status, $videoId]);
    }
 
    /**
     * Met à jour le script d'une vidéo.
     */
    private function updateVideoScript(string $videoId, array $script): void
    {
        $this->db->prepare('UPDATE videos SET script = ? WHERE id = ?')
            ->execute([json_encode($script), $videoId]);
    }
 
    /**
     * Met à jour un champ spécifique d'une vidéo.
     */
    private function updateVideoField(string $videoId, string $field, string $value): void
    {
        // Whitelist des champs autorisés
        $allowed = ['audio_url', 'thumbnail_url', 'video_url', 'images_used', 'hashtags_used'];
        if (!in_array($field, $allowed, true)) {
            throw new Exception("Field not allowed: {$field}");
        }
 
        $this->db->prepare("UPDATE videos SET {$field} = ? WHERE id = ?")
            ->execute([$value, $videoId]);
    }
 
    /**
     * Génère une miniature à partir de la vidéo.
     */
private function generateThumbnail(string $videoFile): ?string
{
    $thumbnailFile = IMAGES_DIR . '/' . uniqid('thumb_') . '.jpg';

    $cmd = sprintf(
        '"%s" -y -i "%s" -ss 00:00:01 -vframes 1 -q:v 2 "%s" 2>&1',
        FFMPEG_PATH,
        $videoFile,
        $thumbnailFile
    );

    exec($cmd, $output, $returnCode);

    if ($returnCode !== 0 || !file_exists($thumbnailFile)) {
        return null;
    }

    return $thumbnailFile;
}
 
    /**
     * Met à jour les statistiques utilisateur.
     */
    private function updateUserStats(string $userId): void
    {
        $this->db->prepare(
            'UPDATE users SET
                videos_generated_this_month = videos_generated_this_month + 1,
                total_videos_generated = total_videos_generated + 1,
                last_video_generated_at = NOW()
             WHERE id = ?'
        )->execute([$userId]);
    }
 
    /**
     * Log la progression de la génération.
     */
    private function logProgress(string $videoId, string $step, string $message): void
    {
        // Stocker dans un fichier temporaire pour le polling côté client
        $progressFile = sys_get_temp_dir() . '/vidgenius_progress_' . $videoId . '.json';
        $progress = [
            'video_id'   => $videoId,
            'step'       => $step,
            'message'    => $message,
            'timestamp'  => date('Y-m-d H:i:s'),
        ];
        file_put_contents($progressFile, json_encode($progress));
 
        error_log("[VidGenius] Video {$videoId} - {$step}: {$message}");
    }
}