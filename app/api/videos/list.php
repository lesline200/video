<?php
// app/api/videos/list.php

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

$userId = $_SESSION['user_id'];
$db     = getDB();

$stmt = $db->prepare(
    'SELECT v.id, v.series_id, v.status, v.video_url, v.thumbnail_url,
            v.duration_seconds, v.created_at, v.error_message,
            cs.name as series_name, cs.niche
     FROM videos v
     LEFT JOIN content_series cs ON v.series_id = cs.id
     WHERE v.user_id = ?
     ORDER BY v.created_at DESC
     LIMIT 50'
);
$stmt->execute([(string) $userId]);
$videos = $stmt->fetchAll();

jsonSuccess(['videos' => $videos]);