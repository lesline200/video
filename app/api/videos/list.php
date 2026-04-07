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

// ── Pagination ──────────────────────────────────────────────
$page    = max(1, (int) ($_GET['page']     ?? 1));
$perPage = min(100, max(1, (int) ($_GET['per_page'] ?? 50)));
$offset  = ($page - 1) * $perPage;

// ── Filtre par statut (optionnel) ───────────────────────────
$statusFilter = '';
$params       = [(string) $userId];

if (!empty($_GET['status']) && in_array($_GET['status'], ['pending', 'processing', 'completed', 'failed', 'cancelled'])) {
    $statusFilter = ' AND v.status = ?';
    $params[]     = $_GET['status'];
}

// ── Recherche par texte (optionnel) ─────────────────────────
$searchFilter = '';
if (!empty($_GET['search'])) {
    $searchFilter = ' AND (v.title LIKE ? OR v.description LIKE ? OR cs.name LIKE ?)';
    $searchTerm   = '%' . $_GET['search'] . '%';
    $params[]     = $searchTerm;
    $params[]     = $searchTerm;
    $params[]     = $searchTerm;
}

// ── Compter le total ────────────────────────────────────────
$countSql = 'SELECT COUNT(*) as total
     FROM videos v
     LEFT JOIN content_series cs ON v.series_id = cs.id
     WHERE v.user_id = ?' . $statusFilter . $searchFilter;
$stmtCount = $db->prepare($countSql);
$stmtCount->execute($params);
$total = (int) $stmtCount->fetch()['total'];

// ── Récupérer les vidéos ────────────────────────────────────
$sql = 'SELECT v.id, v.series_id, v.status, v.video_url, v.thumbnail_url,
            v.duration_seconds, v.created_at, v.error_message,
            v.title, v.description, v.hashtags_used,
            v.scheduled_at, v.is_published,
            cs.name as series_name, cs.niche
     FROM videos v
     LEFT JOIN content_series cs ON v.series_id = cs.id
     WHERE v.user_id = ?' . $statusFilter . $searchFilter . '
     ORDER BY v.created_at DESC
     LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset;

$stmt = $db->prepare($sql);
$stmt->execute($params);
$videos = $stmt->fetchAll();

jsonSuccess([
    'videos'     => $videos,
    'pagination' => [
        'page'      => $page,
        'per_page'  => $perPage,
        'total'     => $total,
        'pages'     => (int) ceil($total / $perPage),
    ],
]);
