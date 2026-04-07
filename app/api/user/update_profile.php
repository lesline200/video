<?php
// app/api/user/update_profile.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/response_helper.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';

setCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

// Vérifier que l'utilisateur est connecté
if (!isLoggedIn()) {
    jsonError('Unauthorized', 401);
}

$body = getJsonBody();
if (!$body) {
    jsonError('Invalid JSON body', 400);
}

$userId   = $_SESSION['user_id'];
$name     = sanitizeString($body['name']        ?? '');
$password = $body['new_password']               ?? '';
$current  = $body['current_password']           ?? '';

// ── Validation ─────────────────────────────────────────────
if (empty($name)) {
    jsonError('Name cannot be empty.');
}
if (strlen($name) < 2 || strlen($name) > 100) {
    jsonError('Name must be between 2 and 100 characters.');
}

$db = getDB();

// ── Mise à jour du nom ─────────────────────────────────────
$db->prepare('UPDATE users SET name = ? WHERE id = ?')
   ->execute([$name, $userId]);

// Mettre à jour la session
$_SESSION['user_name'] = $name;

// ── Changement de mot de passe (optionnel) ─────────────────
if (!empty($password)) {
    if (strlen($password) < 8) {
        jsonError('New password must be at least 8 characters.');
    }

    // Vérifier le mot de passe actuel
    $stmt = $db->prepare('SELECT password_hash FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (empty($user['password_hash'])) {
        jsonError('This account uses Google Sign-In. Cannot set a password.');
    }
    if (!verifyPassword($current, $user['password_hash'])) {
        jsonError('Current password is incorrect.');
    }

    $db->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
       ->execute([hashPassword($password), $userId]);
}

jsonSuccess(['message' => 'Profile updated successfully', 'name' => $name]);