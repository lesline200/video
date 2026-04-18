<?php
// app/api/videos/status.php
// ─── Endpoint pour vérifier le statut de génération d'une vidéo ─────
 
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/response_helper.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';
 
setCorsHeaders();
 
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonError('Method not allowed', 405);
}
 
if (!isLoggedIn()) {
    jsonError('Unauthorized', 401);
}
 
$videoId = $_GET['video_id'] ?? '';
if (empty($videoId)) {
    jsonError('video_id is required', 400);
}
 
$userId = $_SESSION['user_id'];
$db     = getDB();
 
$stmt = $db->prepare(
    'SELECT v.id, v.series_id, v.status, v.video_url, v.thumbnail_url,
            v.audio_url, v.duration_seconds, v.file_size_bytes,
            v.error_message, v.script, v.hashtags_used,
            v.created_at, v.processing_started_at, v.processing_completed_at,
            cs.name as series_name, cs.niche
     FROM videos v
     LEFT JOIN content_series cs ON v.series_id = cs.id
     WHERE v.id = ? AND v.user_id = ?
     LIMIT 1'
);
$stmt->execute([$videoId, (string) $userId]);
$video = $stmt->fetch();
 
if (!$video) {
    jsonError('Video not found', 404);
}
 
// Récupérer la progression en temps réel
$progress    = null;
$progressFile = sys_get_temp_dir() . '/vidgenius_progress_' . $videoId . '.json';
if (file_exists($progressFile)) {
    $progress = json_decode(file_get_contents($progressFile), true);
}
 
// Récupérer les résultats de publication
$stmt = $db->prepare(
    'SELECT platform, status, platform_post_url, error_message, posted_at
     FROM post_results WHERE video_id = ?'
);
$stmt->execute([$videoId]);
$postResults = $stmt->fetchAll();
 
$script = json_decode($video['script'], true);
 
jsonSuccess([
    'video' => [
        'id'               => $video['id'],
        'series_id'        => $video['series_id'],
        'series_name'      => $video['series_name'],
        'niche'            => $video['niche'],
        'status'           => $video['status'],
        'title'            => $script['title'] ?? null,
        'video_url'        => $video['video_url'],
        'thumbnail_url'    => $video['thumbnail_url'],
        'audio_url'        => $video['audio_url'],
        'duration_seconds' => $video['duration_seconds'],
        'file_size_bytes'  => $video['file_size_bytes'],
        'error_message'    => $video['error_message'],
        'hashtags'         => $video['hashtags_used'] ? explode(',', $video['hashtags_used']) : [],
        'scenes'           => $script['scenes'] ?? [],
        'created_at'       => $video['created_at'],
        'started_at'       => $video['processing_started_at'],
        'completed_at'     => $video['processing_completed_at'],
    ],
    'progress'     => $progress,
    'post_results' => $postResults,
]);