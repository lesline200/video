<?php
// app/api/videos/post.php
// ─── Endpoint pour publier une vidéo sur les réseaux sociaux ────────
 
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/response_helper.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';
require_once __DIR__ . '/../../sevices/SocialPoster.php';
 
setCorsHeaders();
 
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}
 
if (!isLoggedIn()) {
    jsonError('Unauthorized', 401);
}
 
$body = getJsonBody();
if (!$body || empty($body['video_id'])) {
    jsonError('video_id is required', 400);
}
 
$userId  = $_SESSION['user_id'];
$videoId = $body['video_id'];
$platforms = $body['platforms'] ?? [];
 
if (empty($platforms)) {
    jsonError('At least one platform is required', 400);
}
 
$db = getDB();
 
// Vérifier que la vidéo appartient à l'utilisateur et est complétée
$stmt = $db->prepare(
    'SELECT v.id, v.video_url, v.status, v.script, v.hashtags_used,
            cs.name, cs.niche, cs.platforms as series_platforms
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
 
if ($video['status'] !== 'completed') {
    jsonError('Video must be completed before posting. Current status: ' . $video['status'], 400);
}
 
if (empty($video['video_url'])) {
    jsonError('Video file not found', 400);
}
 
// Préparer les métadonnées
$script = json_decode($video['script'], true) ?: [];
$metadata = [
    'title'       => $script['title'] ?? $video['name'] ?? 'Video',
    'description' => $script['description'] ?? '',
    'hashtags'    => $video['hashtags_used'] ? explode(',', $video['hashtags_used']) : ($script['hashtags'] ?? []),
];
 
// Publier
try {
    $poster  = new SocialPoster();
    $results = $poster->postToAll($videoId, (string) $userId, $platforms, $metadata);
 
    $allSuccess = true;
    $errors     = [];
    foreach ($results as $platform => $result) {
        if ($result['status'] !== 'success') {
            $allSuccess = false;
            $errors[] = "{$platform}: " . ($result['error'] ?? 'Unknown error');
        }
    }
 
    if ($allSuccess) {
        jsonSuccess([
            'message' => 'Video posted successfully to all platforms!',
            'results' => $results,
        ]);
    } else {
        jsonSuccess([
            'message' => 'Video posted with some issues.',
            'results' => $results,
            'errors'  => $errors,
        ]);
    }
} catch (Exception $e) {
    jsonError('Posting failed: ' . $e->getMessage(), 500);
}