<?php
// app/api/videos/update.php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';

ob_clean();
header('Content-Type: application/json; charset=utf-8');

$user = requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON body']);
    exit;
}

$video_id    = trim($body['video_id']    ?? '');
$title       = trim($body['title']       ?? '');
$description = trim($body['description'] ?? '');
$sched_date  = trim($body['sched_date']  ?? '');
$sched_time  = trim($body['sched_time']  ?? '');
$is_published = !empty($body['published']) ? 1 : 0;  // ← is_published en DB

if (!$video_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'video_id is required']);
    exit;
}

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Vérifier que la vidéo appartient à l'utilisateur
    $check = $pdo->prepare('SELECT id FROM videos WHERE id = ? AND user_id = ?');
    $check->execute([$video_id, $user['id']]);
    if (!$check->fetch()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Video not found or access denied']);
        exit;
    }

    $scheduled_at = null;
    if ($sched_date && $sched_time) {
        $scheduled_at = $sched_date . ' ' . $sched_time . ':00';
    }

    $stmt = $pdo->prepare('
        UPDATE videos
        SET title        = ?,
            description  = ?,
            scheduled_at = ?,
            is_published = ?
        WHERE id = ? AND user_id = ?
    ');
    $stmt->execute([$title, $description, $scheduled_at, $is_published, $video_id, $user['id']]);

    echo json_encode(['success' => true, 'message' => 'Video updated']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}