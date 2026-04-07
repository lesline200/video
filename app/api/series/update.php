<?php
// app/api/series/update.php
// Gère : delete, toggle status (active/paused)

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
if (!$body) {
    jsonError('Invalid JSON body', 400);
}

$userId   = $_SESSION['user_id'];
$action   = $body['action']    ?? '';
$seriesId = $body['series_id'] ?? '';

if (empty($seriesId)) jsonError('Series ID is required.');
if (empty($action))   jsonError('Action is required.');

$db = getDB();

// Vérifier que la série appartient à l'utilisateur
$stmt = $db->prepare('SELECT id, status FROM content_series WHERE id = ? AND user_id = ? LIMIT 1');
$stmt->execute([$seriesId, (string) $userId]);
$series = $stmt->fetch();

if (!$series) {
    jsonError('Series not found.', 404);
}

// ── Actions ────────────────────────────────────────────────
switch ($action) {

    case 'delete':
        // Compter les vidéos associées avant suppression (CASCADE les supprimera)
        $stmtCount = $db->prepare('SELECT COUNT(*) as count FROM videos WHERE series_id = ?');
        $stmtCount->execute([$seriesId]);
        $videoCount = (int) $stmtCount->fetch()['count'];

        $db->prepare('DELETE FROM content_series WHERE id = ? AND user_id = ?')
           ->execute([$seriesId, (string) $userId]);
        jsonSuccess([
            'message'       => 'Series deleted successfully',
            'videos_deleted' => $videoCount,
        ]);
        break;

    case 'toggle':
        $newStatus = $series['status'] === 'active' ? 'paused' : 'active';
        $db->prepare('UPDATE content_series SET status = ? WHERE id = ? AND user_id = ?')
           ->execute([$newStatus, $seriesId, (string) $userId]);
        jsonSuccess([
            'message' => 'Status updated',
            'status'  => $newStatus,
        ]);
        break;

    default:
        jsonError('Invalid action. Use: delete or toggle');
}

