<?php
// app/services/VoiceService.php
// ─── Synthèse vocale via ElevenLabs API ─────────────────────────────
 
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/api_keys.php';
 
class VoiceService
{
    /**
     * Génère un fichier audio MP3 à partir du texte via ElevenLabs.
     *
     * @param string $text       Texte à convertir en parole
     * @param string $voiceKey   Clé de la voix (adam, rachel, josh, etc.)
     * @param float  $speed      Vitesse de lecture (0.5 à 2.0)
     * @param string $outputFile Chemin du fichier MP3 de sortie
     * @return string            Chemin du fichier audio généré
     * @throws Exception
     */
    public function generateAudio(
        string $text,
        string $voiceKey = 'adam',
        float $speed = 1.0,
        string $outputFile = ''
    ): string {
        // $voices  = json_decode(ELEVENLABS_VOICES, true);
        // $voiceId = $voices[$voiceKey] ?? $voices['adam'] ?? '21m00Tcm4TlvDq8ikWAM';
 
        // if (empty($outputFile)) {
        //     $this->ensureDir(AUDIO_DIR);
        //     $outputFile = AUDIO_DIR . '/' . uniqid('audio_') . '.mp3';
        // }
 
        // $url  = "https://api.elevenlabs.io/v1/text-to-speech/{$voiceId}";
        // $data = [
        //     'text'           => $text,
        //     'model_id'       => 'eleven_monolingual_v1',
        //     'voice_settings' => [
        //         'stability'        => 0.5,
        //         'similarity_boost' => 0.75,
        //         'style'            => 0.0,
        //         'use_speaker_boost' => true,
        //     ],
        // ];
 
        // $ch = curl_init($url);
        // curl_setopt_array($ch, [
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_POST           => true,
        //     CURLOPT_HTTPHEADER     => [
        //         'Content-Type: application/json',
        //         'xi-api-key: ' . ELEVENLABS_API_KEY,
        //         'Accept: audio/mpeg',
        //     ],
        //     CURLOPT_POSTFIELDS => json_encode($data),
        //     CURLOPT_TIMEOUT    => 120,
        // ]);
 
        // $response = curl_exec($ch);
        // $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // $error    = curl_error($ch);
        // $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        // curl_close($ch);
 
        // if ($error) {
        //     throw new Exception("ElevenLabs API request failed: $error");
        // }
 
        // if ($httpCode !== 200) {
        //     // La réponse d'erreur est en JSON
        //     $body = json_decode($response, true);
        //     $msg  = $body['detail']['message'] ?? $body['detail'] ?? "HTTP $httpCode";
        //     throw new Exception("ElevenLabs API error: $msg");
        // }
 
        // // Vérifier que c'est bien de l'audio
        // if (strpos($contentType, 'audio') === false && strlen($response) < 1000) {
        //     throw new Exception('ElevenLabs returned non-audio response');
        // }
 
        // file_put_contents($outputFile, $response);
 
        // if (!file_exists($outputFile) || filesize($outputFile) < 100) {
        //     throw new Exception('Audio file is empty or too small');
        // }

        // S'assurer que le dossier de sortie existe et qu'on a un chemin de fichier valide
        if (!is_dir(AUDIO_DIR)) {
            mkdir(AUDIO_DIR, 0755, true);
        }
        if (empty($outputFile)) {
            $outputFile = AUDIO_DIR . '/' . uniqid('audio_') . '.mp3';
        }

        // Utiliser le fichier voice.mp3 fourni à la racine du projet (portable Windows/Linux)
        $sampleVoice = __DIR__ . '/../../voice.mp3';
        if (!file_exists($sampleVoice)) {
            throw new Exception('Sample voice file not found at ' . $sampleVoice);
        }

        $response = file_get_contents($sampleVoice);
        if ($response === false) {
            throw new Exception('Failed to read sample voice file');
        }

        if (file_put_contents($outputFile, $response) === false) {
            throw new Exception('Failed to write audio file to ' . $outputFile);
        }

        return $outputFile;
    }
 
    /**
     * Combine plusieurs fichiers audio en un seul avec FFmpeg.
     *
     * @param array  $audioFiles Liste des chemins de fichiers audio
     * @param string $outputFile Chemin du fichier de sortie
     * @return string            Chemin du fichier combiné
     * @throws Exception
     */
 public function combineAudioFiles(array $audioFiles, string $outputFile): string
{
    if (count($audioFiles) === 1) {
        copy($audioFiles[0], $outputFile);
        return $outputFile;
    }

    $listFile    = AUDIO_DIR . '/' . uniqid('list_') . '.txt';
    $listContent = '';
    foreach ($audioFiles as $file) {
        $escaped      = str_replace('\\', '/', $file);
        $listContent .= "file '{$escaped}'\n";
    }
    file_put_contents($listFile, $listContent);

    $cmd = sprintf(
        '"%s" -f concat -safe 0 -i "%s" -c copy "%s" 2>&1',
        FFMPEG_PATH,
        $listFile,
        $outputFile
    );

    exec($cmd, $output, $returnCode);
    unlink($listFile);

    if ($returnCode !== 0) {
        throw new Exception('FFmpeg audio concatenation failed: ' . implode("\n", $output));
    }

    return $outputFile;
}

 
    /**
     * Obtient la durée d'un fichier audio en secondes.
     *
     * @param string $audioFile Chemin du fichier audio
     * @return float            Durée en secondes
     */
    public function getAudioDuration(string $audioFile): float
{
    $cmd    = '"' . FFPROBE_PATH . '" -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 "' . $audioFile . '" 2>&1';
    $output = trim(shell_exec($cmd));
    return (float) $output;
}
 
    /**
     * S'assure que le répertoire existe.
     */
    private function ensureDir(string $dir): void
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}