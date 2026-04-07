<?php
// app/api/videos/update.php
// Met à jour titre, description, plateformes, planification d'une vidéo

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/response_helper.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';

setCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

if (!isLoggedIn()) {
    jsonError('Unauthorized', 401);
}

$body = getJsonBody();
if (!$body || empty($body['video_id'])) {
    jsonError('Video ID is required.', 400);
}

$userId  = $_SESSION['user_id'];
$videoId = $body['video_id'];

$db = getDB();

// Vérifier que la vidéo appartient à l'utilisateur
$stmt = $db->prepare('SELECT id, status FROM videos WHERE id = ? AND user_id = ? LIMIT 1');
$stmt->execute([$videoId, (string) $userId]);
$video = $stmt->fetch();

if (!$video) {
    jsonError('Video not found.', 404);
}

// ── Champs modifiables ──────────────────────────────────────
$updates = [];
$params  = [];

if (isset($body['title'])) {
    $title = mb_substr(trim($body['title']), 0, 100);
    $updates[] = 'title = ?';
    $params[]  = $title;
}

if (isset($body['description'])) {
    $description = mb_substr(trim($body['description']), 0, 2200);
    $updates[] = 'description = ?';
    $params[]  = $description;
}

if (isset($body['scheduled_at'])) {
    $scheduledAt = $body['scheduled_at'];
    // Valider le format datetime
    if (!empty($scheduledAt) && strtotime($scheduledAt) !== false) {
        $updates[] = 'scheduled_at = ?';
        $params[]  = date('Y-m-d H:i:s', strtotime($scheduledAt));
    } elseif (empty($scheduledAt)) {
        $updates[] = 'scheduled_at = NULL';
    }
}

if (isset($body['is_published'])) {
    $updates[] = 'is_published = ?';
    $params[]  = $body['is_published'] ? 1 : 0;
}

if (empty($updates)) {
    jsonError('No fields to update.', 400);
}

$params[] = $videoId;
$params[] = (string) $userId;

$sql = 'UPDATE videos SET ' . implode(', ', $updates) . ' WHERE id = ? AND user_id = ?';
$db->prepare($sql)->execute($params);

jsonSuccess(['message' => 'Video updated successfully']);
