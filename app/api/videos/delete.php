<?php
// app/api/videos/delete.php

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
$stmt = $db->prepare('SELECT id FROM videos WHERE id = ? AND user_id = ? LIMIT 1');
$stmt->execute([$videoId, (string) $userId]);

if (!$stmt->fetch()) {
    jsonError('Video not found.', 404);
}

// Supprimer la vidéo
$db->prepare('DELETE FROM videos WHERE id = ? AND user_id = ?')
   ->execute([$videoId, (string) $userId]);

jsonSuccess(['message' => 'Video deleted successfully']);