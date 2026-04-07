<?php
// app/api/auth/reset_password.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/response_helper.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';

setCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

$body = getJsonBody();
if (!$body) {
    jsonError('Invalid JSON body', 400);
}

$token    = $body['token']        ?? '';
$code     = $body['code']         ?? '';
$password = $body['new_password'] ?? '';

if (empty($token) && empty($code)) {
    jsonError('Token or code is required.');
}
if (empty($password)) {
    jsonError('New password is required.');
}
if (strlen($password) < 8) {
    jsonError('Password must be at least 8 characters.');
}

$db = getDB();

// ── Trouver le token valide ────────────────────────────────
if (!empty($token)) {
    $stmt = $db->prepare(
        'SELECT id, user_id FROM password_reset_tokens
         WHERE token = ? AND used = 0 AND expires_at > NOW()
         LIMIT 1'
    );
    $stmt->execute([$token]);
} else {
    $stmt = $db->prepare(
        'SELECT id, user_id FROM password_reset_tokens
         WHERE code = ? AND used = 0 AND expires_at > NOW()
         LIMIT 1'
    );
    $stmt->execute([$code]);
}

$resetToken = $stmt->fetch();

if (!$resetToken) {
    jsonError('Invalid or expired reset token. Please request a new one.', 400);
}

// ── Mettre à jour le mot de passe ──────────────────────────
$db->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
   ->execute([hashPassword($password), $resetToken['user_id']]);

// ── Invalider le token ─────────────────────────────────────
$db->prepare('UPDATE password_reset_tokens SET used = 1 WHERE id = ?')
   ->execute([$resetToken['id']]);

jsonSuccess(['message' => 'Password reset successfully. You can now log in.']);