<?php
// app/api/series/create.php


ob_start(); // ← doit être ABSOLUMENT la première ligne

// Désactiver l'affichage des erreurs dans les API (les loguer seulement)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/response_helper.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';
require_once __DIR__ . '/../../sevices/VideoGenerator.php';

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

$userId = $_SESSION['user_id'];

// ── Champs obligatoires ────────────────────────────────────
$name  = sanitizeString($body['name']  ?? '');
$niche = sanitizeString($body['niche'] ?? '');

if (empty($name))  jsonError('Series name is required.');
if (empty($niche)) jsonError('Niche is required.');

// ── Champs optionnels ──────────────────────────────────────
$description    = sanitizeString($body['description'] ?? '');
$platforms      = $body['platforms']       ?? [];
$contentConfig  = $body['content_config']  ?? [];
$contentRules   = $body['content_rules']   ?? [];
$scheduleConfig = $body['schedule_config'] ?? [];
$generateVideo  = $body['generate_initial_video'] ?? false; // Nouveau paramètre optionnel

// ── Générer un UUID v4 ─────────────────────────────────────
function generateUUID(): string {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

$seriesId = generateUUID();

// ── Insertion ──────────────────────────────────────────────
$db   = getDB();
$stmt = $db->prepare(
    'INSERT INTO content_series 
        (id, user_id, name, niche, description, content_config, platforms, content_rules, schedule_config, status)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, "active")'
);
$stmt->execute([
    $seriesId,
    (string) $userId,
    $name,
    $niche,
    $description,
    json_encode($contentConfig),
    json_encode($platforms),
    json_encode($contentRules),
    json_encode($scheduleConfig),
]);

// ── Générer une vidéo initiale si demandé ──────────────────
$initialVideo = null;
if ($generateVideo) {
    try {
        $generator = new VideoGenerator();
        $initialVideo = $generator->generate($seriesId, (string) $userId);
    } catch (Exception $e) {
        // Ne pas échouer la création de série si la génération vidéo échoue
        error_log('Initial video generation failed: ' . $e->getMessage());
    }
}

jsonSuccess([
    'message' => 'Series created successfully',
    'series'  => [
        'id'     => $seriesId,
        'name'   => $name,
        'niche'  => $niche,
        'status' => 'active',
    ],
    'initial_video' => $initialVideo,
], 201);