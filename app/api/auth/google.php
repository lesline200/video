<?php
// app/api/auth/google.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/response_helper.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';

setCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

$body = getJsonBody();
if (!$body || empty($body['credential'])) {
    jsonError('Missing Google credential token.', 400);
}

$credential = $body['credential'];

// ══════════════════════════════════════════════════════════
//  Vérifier le Google ID Token via l'API Google
//  (sans dépendance externe : on appelle tokeninfo)
// ══════════════════════════════════════════════════════════
$verifyUrl = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($credential);
$context   = stream_context_create([
    'http' => [
        'timeout'        => 10,
        'ignore_errors'  => true,
    ]
]);

$response = @file_get_contents($verifyUrl, false, $context);
if ($response === false) {
    jsonError('Failed to verify Google token. Check your internet connection.', 503);
}

$googleData = json_decode($response, true);

// Vérifier les champs obligatoires
if (
    empty($googleData['sub'])   ||
    empty($googleData['email']) ||
    empty($googleData['email_verified']) ||
    $googleData['email_verified'] !== 'true'
) {
    jsonError('Invalid or unverified Google token.', 401);
}

// Vérifier que le token est destiné à notre app
if ($googleData['aud'] !== GOOGLE_CLIENT_ID) {
    jsonError('Token audience mismatch.', 401);
}

// ── Données Google ─────────────────────────────────────────
$googleId   = $googleData['sub'];
$email      = $googleData['email'];
$name       = $googleData['name']    ?? 'User';
$avatarUrl  = $googleData['picture'] ?? null;

// ── Chercher l'utilisateur existant ───────────────────────
$db   = getDB();
$stmt = $db->prepare(
    'SELECT id, name, email, plan, is_active, google_id FROM users
     WHERE google_id = ? OR email = ?
     LIMIT 1'
);
$stmt->execute([$googleId, $email]);
$user = $stmt->fetch();

if ($user) {
    // ── Utilisateur existant ───────────────────────────────
    if (!$user['is_active']) {
        jsonError('Your account has been disabled.', 403);
    }

    // Lier le google_id s'il manque (compte créé par email/password)
    if (empty($user['google_id'])) {
        $db->prepare('UPDATE users SET google_id = ?, avatar_url = ?, last_login_at = NOW() WHERE id = ?')
           ->execute([$googleId, $avatarUrl, $user['id']]);
    } else {
        $db->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')
           ->execute([$user['id']]);
    }

    $message = 'Login successful';

} else {
    // ── Nouveau compte via Google ──────────────────────────
    $stmt = $db->prepare(
        'INSERT INTO users (name, email, google_id, avatar_url, plan, created_at, last_login_at)
         VALUES (?, ?, ?, ?, "free", NOW(), NOW())'
    );
    $stmt->execute([$name, $email, $googleId, $avatarUrl]);
    $user = [
        'id'    => (int) $db->lastInsertId(),
        'name'  => $name,
        'email' => $email,
        'plan'  => 'free',
    ];
    $message = 'Account created';
}

// ── Session ────────────────────────────────────────────────
createUserSession($user);

// ── JWT ────────────────────────────────────────────────────
$token = jwtEncode([
    'user_id' => $user['id'],
    'email'   => $user['email'],
    'plan'    => $user['plan'],
]);

// ── Réponse ────────────────────────────────────────────────
jsonSuccess([
    'message' => $message,
    'token'   => $token,
    'user'    => [
        'id'    => $user['id'],
        'name'  => $user['name'],
        'email' => $user['email'],
        'plan'  => $user['plan'],
    ],
]);
