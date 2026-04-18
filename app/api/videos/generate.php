<?php
// app/api/videos/generate.php
// ─── Endpoint pour déclencher la génération d'une vidéo ─────────────
 
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/response_helper.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';
require_once __DIR__ . '/../../services/VideoGenerator.php';
 
setCorsHeaders();
 
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}
 
if (!isLoggedIn()) {
    jsonError('Unauthorized', 401);
}
 
$body = getJsonBody();
if (!$body || empty($body['series_id'])) {
    jsonError('series_id is required', 400);
}
 
$userId   = $_SESSION['user_id'];
$seriesId = $body['series_id'];
$topic    = $body['topic'] ?? null;
 
// Vérifier que la série appartient à l'utilisateur
$db   = getDB();
$stmt = $db->prepare('SELECT id, status FROM content_series WHERE id = ? AND user_id = ? LIMIT 1');
$stmt->execute([$seriesId, (string) $userId]);
$series = $stmt->fetch();
 
if (!$series) {
    jsonError('Series not found', 404);
}
 
if ($series['status'] !== 'active') {
    jsonError('Series is not active. Please resume the series first.', 400);
}
 
// Vérifier s'il y a déjà une vidéo en cours de traitement pour cette série
$stmt = $db->prepare(
    'SELECT COUNT(*) as count FROM videos WHERE series_id = ? AND user_id = ? AND status IN ("pending", "processing")'
);
$stmt->execute([$seriesId, (string) $userId]);
$pending = $stmt->fetch();
 
if ($pending && $pending['count'] > 0) {
    jsonError('A video is already being generated for this series. Please wait for it to complete.', 409);
}
 
// Lancer la génération


// Créer l'entrée vidéo en BDD immédiatement (status = pending)
$videoId = uniqid('vid_', true);
$stmt = $db->prepare(
    'INSERT INTO videos (id, series_id, user_id, script, status, queued_at, processing_started_at)
     VALUES (?, ?, ?, ?, "pending", NOW(), NOW())'
);
$stmt->execute([$videoId, $seriesId, (string)$userId, '{}']);

// Lancer la génération en arrière-plan (process séparé)
$phpBin  = PHP_BINARY; // chemin PHP auto-détecté
$script  = realpath(__DIR__ . '/../../jobs/generate_video_job.php');
$cmd     = sprintf(
    '%s %s %s %s %s',
    escapeshellarg($phpBin),
    escapeshellarg($script),
    escapeshellarg($videoId),
    escapeshellarg($seriesId),
    escapeshellarg((string)$userId)
);

// Windows : start /B pour ne pas bloquer
if (PHP_OS_FAMILY === 'Windows') {
    pclose(popen('start /B ' . $cmd . ' > NUL 2>&1', 'r'));
} else {
    exec($cmd . ' > /dev/null 2>&1 &');
}

// Répondre immédiatement au frontend
jsonSuccess([
    'message'  => 'Video generation started!',
    'video_id' => $videoId,
    'status'   => 'pending',
], 202);