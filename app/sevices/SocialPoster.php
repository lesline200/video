<?php
// app/services/SocialPoster.php
// ─── Publication automatique sur TikTok, Instagram, YouTube ─────────
 
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/api_keys.php';
 
class SocialPoster
{
   /** @var PDO */
   private $db;
 
    public function __construct()
    {
        $this->db = getDB();
    }
 
    /**
     * Publie une vidéo sur toutes les plateformes configurées pour la série.
     *
     * @param string $videoId    ID de la vidéo dans la BDD
     * @param string $userId     ID de l'utilisateur
     * @param array  $platforms  Plateformes cibles ['youtube', 'tiktok', 'instagram']
     * @param array  $metadata   Titre, description, hashtags
     * @return array             Résultats par plateforme
     */
    public function postToAll(
        string $videoId,
        string $userId,
        array $platforms,
        array $metadata
    ): array {
        $results = [];
 
        foreach ($platforms as $platform) {
            try {
                // Récupérer les tokens OAuth de l'utilisateur pour cette plateforme
                $account = $this->getUserSocialAccount($userId, $platform);
 
                if (!$account) {
                    $results[$platform] = [
                        'status'  => 'failed',
                        'error'   => "No {$platform} account connected. Please connect your account in Settings.",
                    ];
                    $this->savePostResult($videoId, $platform, 'failed', null, null, "No {$platform} account connected");
                    continue;
                }
 
                // Vérifier si le token est expiré et le rafraîchir si nécessaire
                if ($this->isTokenExpired($account)) {
                    $account = $this->refreshToken($account);
                }
 
                // Publier selon la plateforme
                $result = match ($platform) {
                    'youtube'   => $this->postToYouTube($videoId, $account, $metadata),
                    'tiktok'    => $this->postToTikTok($videoId, $account, $metadata),
                    'instagram' => $this->postToInstagram($videoId, $account, $metadata),
                    default     => throw new Exception("Unsupported platform: {$platform}"),
                };
 
                $results[$platform] = $result;
                $this->savePostResult(
                    $videoId,
                    $platform,
                    $result['status'],
                    $result['platform_post_id'] ?? null,
                    $result['platform_post_url'] ?? null,
                    $result['error'] ?? null
                );
            } catch (Exception $e) {
                $results[$platform] = [
                    'status' => 'failed',
                    'error'  => $e->getMessage(),
                ];
                $this->savePostResult($videoId, $platform, 'failed', null, null, $e->getMessage());
            }
        }
 
        return $results;
    }
 
    /**
     * Publie sur YouTube Shorts via l'API YouTube Data v3.
     */
    private function postToYouTube(string $videoId, array $account, array $metadata): array
    {
        $video = $this->getVideoFile($videoId);
        if (!$video) {
            throw new Exception('Video file not found');
        }
 
        $accessToken = $account['access_token'];
        $title       = $metadata['title'] ?? 'Untitled Video';
        $description = ($metadata['description'] ?? '') . "\n\n" . $this->formatHashtags($metadata['hashtags'] ?? []);
 
        // Étape 1 : Initialiser l'upload resumable
        $initUrl = 'https://www.googleapis.com/upload/youtube/v3/videos?uploadType=resumable&part=snippet,status';
 
        $videoData = [
            'snippet' => [
                'title'       => substr($title, 0, 100),
                'description' => substr($description, 0, 5000),
                'tags'        => array_slice($metadata['hashtags'] ?? [], 0, 30),
                'categoryId'  => '22', // People & Blogs
            ],
            'status' => [
                'privacyStatus'           => 'public',
                'selfDeclaredMadeForKids' => false,
            ],
        ];
 
        $ch = curl_init($initUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json; charset=UTF-8',
                'X-Upload-Content-Length: ' . filesize($video['file_path']),
                'X-Upload-Content-Type: video/mp4',
            ],
            CURLOPT_POSTFIELDS => json_encode($videoData),
            CURLOPT_HEADER     => true,
            CURLOPT_TIMEOUT    => 30,
        ]);
 
        $response   = curl_exec($ch);
        $httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);
 
        if ($httpCode !== 200) {
            $body = substr($response, $headerSize);
            $err  = json_decode($body, true);
            throw new Exception('YouTube upload init failed: ' . ($err['error']['message'] ?? "HTTP $httpCode"));
        }
 
        // Extraire l'URL d'upload
        $headers   = substr($response, 0, $headerSize);
        $uploadUrl = null;
        if (preg_match('/location:\s*(.+)/i', $headers, $m)) {
            $uploadUrl = trim($m[1]);
        }
 
        if (!$uploadUrl) {
            throw new Exception('YouTube did not return an upload URL');
        }
 
        // Étape 2 : Upload du fichier vidéo
        $ch = curl_init($uploadUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_PUT            => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: video/mp4',
                'Content-Length: ' . filesize($video['file_path']),
            ],
            CURLOPT_INFILE     => fopen($video['file_path'], 'r'),
            CURLOPT_INFILESIZE => filesize($video['file_path']),
            CURLOPT_TIMEOUT    => 300,
        ]);
 
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
 
        if ($httpCode !== 200) {
            throw new Exception('YouTube video upload failed: HTTP ' . $httpCode);
        }
 
        $result = json_decode($response, true);
 
        return [
            'status'           => 'success',
            'platform_post_id' => $result['id'] ?? null,
            'platform_post_url' => 'https://youtube.com/shorts/' . ($result['id'] ?? ''),
        ];
    }
 
    /**
     * Publie sur TikTok via l'API Content Posting.
     */
    private function postToTikTok(string $videoId, array $account, array $metadata): array
    {
        $video = $this->getVideoFile($videoId);
        if (!$video) {
            throw new Exception('Video file not found');
        }
 
        $accessToken = $account['access_token'];
        $title       = ($metadata['title'] ?? '') . ' ' . $this->formatHashtags($metadata['hashtags'] ?? []);
 
        // TikTok Content Posting API - Initialiser l'upload
        $initUrl = 'https://open.tiktokapis.com/v2/post/publish/video/init/';
 
        $postData = [
            'post_info' => [
                'title'                 => substr($title, 0, 150),
                'privacy_level'         => 'PUBLIC_TO_EVERYONE',
                'disable_duet'          => false,
                'disable_comment'       => false,
                'disable_stitch'        => false,
                'video_cover_timestamp_ms' => 1000,
            ],
            'source_info' => [
                'source'          => 'FILE_UPLOAD',
                'video_size'      => filesize($video['file_path']),
                'chunk_size'      => filesize($video['file_path']),
                'total_chunk_count' => 1,
            ],
        ];
 
        $ch = curl_init($initUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json; charset=UTF-8',
            ],
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_TIMEOUT    => 30,
        ]);
 
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
 
        if ($httpCode !== 200) {
            $body = json_decode($response, true);
            throw new Exception('TikTok upload init failed: ' . ($body['error']['message'] ?? "HTTP $httpCode"));
        }
 
        $result    = json_decode($response, true);
        $uploadUrl = $result['data']['upload_url'] ?? null;
        $publishId = $result['data']['publish_id'] ?? null;
 
        if (!$uploadUrl) {
            throw new Exception('TikTok did not return an upload URL');
        }
 
        // Upload du fichier
        $ch = curl_init($uploadUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_PUT            => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: video/mp4',
                'Content-Range: bytes 0-' . (filesize($video['file_path']) - 1) . '/' . filesize($video['file_path']),
            ],
            CURLOPT_INFILE     => fopen($video['file_path'], 'r'),
            CURLOPT_INFILESIZE => filesize($video['file_path']),
            CURLOPT_TIMEOUT    => 300,
        ]);
 
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
 
        if ($httpCode < 200 || $httpCode >= 300) {
            throw new Exception('TikTok video upload failed: HTTP ' . $httpCode);
        }
 
        return [
            'status'           => 'success',
            'platform_post_id' => $publishId,
            'platform_post_url' => null, // TikTok ne retourne pas l'URL directement
        ];
    }
 
    /**
     * Publie sur Instagram Reels via l'API Graph.
     */
    private function postToInstagram(string $videoId, array $account, array $metadata): array
    {
        $video = $this->getVideoFile($videoId);
        if (!$video) {
            throw new Exception('Video file not found');
        }
 
        $accessToken = $account['access_token'];
        $igUserId    = $account['platform_user_id'];
        $caption     = ($metadata['title'] ?? '') . "\n\n" . ($metadata['description'] ?? '') . "\n\n" . $this->formatHashtags($metadata['hashtags'] ?? []);
 
        // L'URL de la vidéo doit être accessible publiquement pour l'API Instagram
        $videoUrl = $video['video_url'];
        if (empty($videoUrl)) {
            throw new Exception('Video must have a public URL for Instagram posting. Upload to cloud storage first.');
        }
 
        // Étape 1 : Créer le conteneur média
        $createUrl = "https://graph.facebook.com/v18.0/{$igUserId}/media";
 
        $ch = curl_init($createUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'media_type'   => 'REELS',
                'video_url'    => $videoUrl,
                'caption'      => substr($caption, 0, 2200),
                'access_token' => $accessToken,
            ]),
            CURLOPT_TIMEOUT => 60,
        ]);
 
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
 
        if ($httpCode !== 200) {
            $body = json_decode($response, true);
            throw new Exception('Instagram container creation failed: ' . ($body['error']['message'] ?? "HTTP $httpCode"));
        }
 
        $result      = json_decode($response, true);
        $containerId = $result['id'] ?? null;
 
        if (!$containerId) {
            throw new Exception('Instagram did not return a container ID');
        }
 
        // Attendre que le conteneur soit prêt (Instagram traite la vidéo)
        $maxAttempts = 30;
        $ready       = false;
 
        for ($i = 0; $i < $maxAttempts; $i++) {
            sleep(5);
 
            $statusUrl = "https://graph.facebook.com/v18.0/{$containerId}?fields=status_code&access_token={$accessToken}";
            $statusResponse = file_get_contents($statusUrl);
            $statusData     = json_decode($statusResponse, true);
 
            if (($statusData['status_code'] ?? '') === 'FINISHED') {
                $ready = true;
                break;
            }
 
            if (($statusData['status_code'] ?? '') === 'ERROR') {
                throw new Exception('Instagram video processing failed');
            }
        }
 
        if (!$ready) {
            throw new Exception('Instagram video processing timed out');
        }
 
        // Étape 2 : Publier le conteneur
        $publishUrl = "https://graph.facebook.com/v18.0/{$igUserId}/media_publish";
 
        $ch = curl_init($publishUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'creation_id'  => $containerId,
                'access_token' => $accessToken,
            ]),
            CURLOPT_TIMEOUT => 30,
        ]);
 
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
 
        if ($httpCode !== 200) {
            $body = json_decode($response, true);
            throw new Exception('Instagram publish failed: ' . ($body['error']['message'] ?? "HTTP $httpCode"));
        }
 
        $result = json_decode($response, true);
 
        return [
            'status'           => 'success',
            'platform_post_id' => $result['id'] ?? null,
            'platform_post_url' => null,
        ];
    }
 
    // ─── Helper Methods ─────────────────────────────────────────────
 
    /**
     * Récupère le compte social de l'utilisateur pour une plateforme.
     */
    private function getUserSocialAccount(string $userId, string $platform): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM user_social_accounts WHERE user_id = ? AND platform = ? AND is_active = 1 LIMIT 1'
        );
        $stmt->execute([$userId, $platform]);
        return $stmt->fetch() ?: null;
    }
 
    /**
     * Vérifie si le token OAuth est expiré.
     */
    private function isTokenExpired(array $account): bool
    {
        if (empty($account['token_expires_at'])) return false;
        return strtotime($account['token_expires_at']) < time();
    }
 
    /**
     * Rafraîchit le token OAuth (implémentation à personnaliser par plateforme).
     */
    private function refreshToken(array $account): array
{
    // Token expiré : lever une exception explicite plutôt que continuer silencieusement
    throw new Exception(
        "Access token for {$account['platform']} has expired. " .
        "Please reconnect your {$account['platform']} account in Settings."
    );
}
 
    /**
     * Récupère les informations du fichier vidéo.
     */
    private function getVideoFile(string $videoId): ?array
{
    $stmt = $this->db->prepare('SELECT id, video_url, status FROM videos WHERE id = ? LIMIT 1');
    $stmt->execute([$videoId]);
    $video = $stmt->fetch();

    if (!$video || $video['status'] !== 'completed') return null;

    $videoUrl = $video['video_url'];

    // Normaliser UPLOADS_URL pour éviter les problèmes de slash
    $uploadsUrl = rtrim(UPLOADS_URL, '/');
    $uploadDir  = rtrim(UPLOAD_DIR, '/');

    $filePath = str_replace($uploadsUrl, $uploadDir, $videoUrl);

    // Sur Windows : convertir les slashes
    $filePath = str_replace('/', DIRECTORY_SEPARATOR, $filePath);

    if (!file_exists($filePath)) return null;

    return [
        'id'        => $video['id'],
        'video_url' => $videoUrl,
        'file_path' => $filePath,
    ];
}
 
    /**
     * Sauvegarde le résultat de publication dans post_results.
     */
    private function savePostResult(
        string $videoId,
        string $platform,
        string $status,
        ?string $platformPostId,
        ?string $platformPostUrl,
        ?string $errorMessage
    ): void {
        $id = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
 
        $stmt = $this->db->prepare(
            'INSERT INTO post_results (id, video_id, platform, status, platform_post_id, platform_post_url, error_message, posted_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE status = VALUES(status), platform_post_id = VALUES(platform_post_id),
                platform_post_url = VALUES(platform_post_url), error_message = VALUES(error_message),
                posted_at = VALUES(posted_at), updated_at = NOW()'
        );
 
        $stmt->execute([
            $id,
            $videoId,
            $platform,
            $status,
            $platformPostId,
            $platformPostUrl,
            $errorMessage,
            $status === 'success' ? date('Y-m-d H:i:s') : null,
        ]);
    }
 
    /**
     * Formate les hashtags.
     */
    private function formatHashtags(array $hashtags): string
    {
        return implode(' ', array_map(
    function ($tag) { return '#' . ltrim($tag, '#'); },
    $hashtags
));
    }
}