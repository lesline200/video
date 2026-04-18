<?php
// app/config/api_keys.php
// ─── Clés API pour les services IA ──────────────────────────────────
 

ob_start(); // ← doit être ABSOLUMENT la première ligne

// Désactiver l'affichage des erreurs dans les API (les loguer seulement)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// ─── OpenAI (GPT-4 + DALL-E 3) ─────────────────────────────────────
define('OPENAI_API_KEY', getenv('OPENAI_API_KEY'));
define('OPENAI_MODEL',   'gpt-4');
define('OPENAI_IMAGE_MODEL', 'dall-e-3');
 
// ─── ElevenLabs (Text-to-Speech) ────────────────────────────────────
define('ELEVENLABS_API_KEY', getenv('ELEVENLABS_API_KEY') );
 
// Voix disponibles par défaut (ID ElevenLabs)
define('ELEVENLABS_VOICES', json_encode([
    'adam'    => '21m00Tcm4TlvDq8ikWAM',
    'rachel'  => '21m00Tcm4TlvDq8ikWAM',
    'josh'    => 'TxGEqnHWrfWFTfGW9XjX',
    'bella'   => 'EXAVITQu4vr4xnSDxMaL',
    'elli'    => 'MF3mGyEYCl7XYWbV9V6O',
    'sam'     => 'yoZ06aMxZJJ28mfd3POQ',
]));
 
// ─── Chemins de stockage ────────────────────────────────────────────
define('UPLOAD_DIR',       __DIR__ . '/../../uploads');
define('VIDEOS_DIR',       UPLOAD_DIR . '/videos');
define('AUDIO_DIR',        UPLOAD_DIR . '/audio');
define('IMAGES_DIR',       UPLOAD_DIR . '/images');
 
// ─── FFmpeg ─────────────────────────────────────────────────────────
define('FFMPEG_PATH',  getenv('FFMPEG_PATH') ?: 'C:/ffmpeg/bin/ffmpeg.exe');
define('FFPROBE_PATH', getenv('FFPROBE_PATH') ?: 'C:/ffmpeg/bin/ffprobe.exe');
 
// ─── URLs publiques ─────────────────────────────────────────────────
define('UPLOADS_URL', APP_URL . '/uploads');