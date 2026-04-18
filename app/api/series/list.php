<?php
// app/api/series/list.php


ob_start(); // ← doit être ABSOLUMENT la première ligne

// Désactiver l'affichage des erreurs dans les API (les loguer seulement)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

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

// Récupérer les séries de l'utilisateur
$stmt = $db->prepare(
    'SELECT id, name, niche, description, status, total_posts, 
            last_post_at, next_post_at, created_at,
            platforms, schedule_config
     FROM content_series
     WHERE user_id = ?
     ORDER BY created_at DESC'
);
$stmt->execute([(string) $userId]);
$series = $stmt->fetchAll();

// Décoder les JSON stockés
foreach ($series as &$s) {
    $s['platforms']       = json_decode($s['platforms'],       true) ?? [];
    $s['schedule_config'] = json_decode($s['schedule_config'], true) ?? [];
}

// Stats globales séries
$stmtStats = $db->prepare(
    'SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active
     FROM content_series
     WHERE user_id = ?'
);
$stmtStats->execute([(string) $userId]);
$stats = $stmtStats->fetch();

// Compter les vraies vidéos completed depuis la table videos
$stmtVideos = $db->prepare(
    'SELECT COUNT(*) as total_videos
     FROM videos
     WHERE user_id = ? AND status = "completed"'
);
$stmtVideos->execute([(string) $userId]);
$videoStats = $stmtVideos->fetch();

jsonSuccess([
    'series' => $series,
    'stats'  => [
        'total'        => (int) $stats['total'],
        'active'       => (int) $stats['active'],
        'total_videos' => (int) $videoStats['total_videos'],
    ],
]);