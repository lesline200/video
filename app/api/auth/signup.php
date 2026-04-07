<?php
// app/api/auth/signup.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/response_helper.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';

setCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

// ── Body ───────────────────────────────────────────────────
$body = getJsonBody();
error_log("Body received: " . file_get_contents('php://input'));
if (!$body) {
    jsonError('Invalid JSON body', 400);
}

$name     = sanitizeString($body['name']     ?? '');
$email    = trim($body['email']    ?? '');
$password =       $body['password'] ?? '';

// ── Validation ─────────────────────────────────────────────
if (empty($name)) {
    jsonError('Full name is required.');
}
if (strlen($name) < 2 || strlen($name) > 100) {
    jsonError('Name must be between 2 and 100 characters.');
}
if (empty($email) || !validateEmail($email)) {
    jsonError('A valid email address is required.');
}
if (strlen($password) < 8) {
    jsonError('Password must be at least 8 characters.');
}

// ── Vérifier si l'email existe déjà ───────────────────────
$db   = getDB();
$stmt = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    jsonError('An account with this email already exists.', 409);
}

// ── Insérer l'utilisateur ──────────────────────────────────
$hash = hashPassword($password);
$stmt = $db->prepare(
    'INSERT INTO users (name, email, password_hash, plan, created_at, last_login_at)
     VALUES (?, ?, ?, "free", NOW(), NOW())'
);
$stmt->execute([$name, $email, $hash]);
$userId = (int) $db->lastInsertId();

$user = [
    'id'    => $userId,
    'name'  => $name,
    'email' => $email,
    'plan'  => 'free',
];

// ── Session ────────────────────────────────────────────────
createUserSession($user);

// ── JWT ────────────────────────────────────────────────────
$token = jwtEncode([
    'user_id' => $userId,
    'email'   => $email,
    'plan'    => 'free',
]);

// ── Réponse ────────────────────────────────────────────────
jsonSuccess([
    'message' => 'Account created successfully',
    'token'   => $token,
    'user'    => $user,
], 201);
