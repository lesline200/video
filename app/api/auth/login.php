<?php
// app/api/auth/login.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/response_helper.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';

setCorsHeaders();

// Accepte uniquement POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

// ── Lire le body JSON ──────────────────────────────────────
$body = getJsonBody();
if (!$body) {
    jsonError('Invalid JSON body', 400);
}

$email    = trim($body['email']    ?? '');
$password =       $body['password'] ?? '';

// ── Validation basique ─────────────────────────────────────
if (empty($email) || empty($password)) {
    jsonError('Email and password are required.');
}
if (!validateEmail($email)) {
    jsonError('Invalid email address.');
}

// ── Requête BDD ────────────────────────────────────────────
$db   = getDB();
$stmt = $db->prepare('SELECT id, name, email, password_hash, plan, is_active FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

// ── Vérifications ──────────────────────────────────────────
if (!$user) {
    jsonError('Invalid email or password.', 401);
}
if (!$user['is_active']) {
    jsonError('Your account has been disabled. Please contact support.', 403);
}
if (empty($user['password_hash'])) {
    // Compte créé via Google → pas de mot de passe
    jsonError('This account uses Google Sign-In. Please use "Continue with Google".', 400);
}
if (!verifyPassword($password, $user['password_hash'])) {
    jsonError('Invalid email or password.', 401);
}

// ── Mettre à jour last_login_at ────────────────────────────
$db->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')->execute([$user['id']]);

// ── Créer la session ───────────────────────────────────────
createUserSession($user);

// ── Générer un JWT (utile si tu veux aussi du token-based) ─
$token = jwtEncode([
    'user_id' => $user['id'],
    'email'   => $user['email'],
    'plan'    => $user['plan'],
]);

// ── Réponse ────────────────────────────────────────────────
jsonSuccess([
    'message' => 'Login successful',
    'token'   => $token,
    'user'    => [
        'id'    => $user['id'],
        'name'  => $user['name'],
        'email' => $user['email'],
        'plan'  => $user['plan'],
    ],
]);
