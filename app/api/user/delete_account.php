<?php
// app/api/user/delete_account.php

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
$password = $body['password'] ?? '';

$db = getDB();

// ── Vérifier le mot de passe avant suppression ─────────────
$stmt = $db->prepare('SELECT password_hash, google_id FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Si compte Google → pas besoin de mot de passe
// Si compte email → vérifier le mot de passe
if (empty($user['google_id'])) {
    if (empty($password)) {
        jsonError('Please enter your password to confirm deletion.');
    }
    if (!verifyPassword($password, $user['password_hash'])) {
        jsonError('Incorrect password.');
    }
}

// ── Supprimer l'utilisateur ────────────────────────────────
$db->prepare('DELETE FROM users WHERE id = ?')->execute([$userId]);

// ── Détruire la session ────────────────────────────────────
destroyUserSession();

jsonSuccess(['message' => 'Account deleted successfully']);