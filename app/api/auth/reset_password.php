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

try {
    $raw = file_get_contents('php://input');
    if (empty($raw)) {
        jsonError('Empty request body.', 400);
    }

    $body = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        jsonError('Invalid JSON: ' . json_last_error_msg(), 400);
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

    // ── Trouver le token valide (sans filtrer expires_at en SQL) ──
    if (!empty($token)) {
        $stmt = $db->prepare(
            'SELECT id, user_id, used, expires_at FROM password_reset_tokens
             WHERE token = ?
             ORDER BY created_at DESC
             LIMIT 1'
        );
        $stmt->execute([$token]);
    } else {
        $stmt = $db->prepare(
            'SELECT id, user_id, used, expires_at FROM password_reset_tokens
             WHERE code = ?
             ORDER BY created_at DESC
             LIMIT 1'
        );
        $stmt->execute([$code]);
    }

    $resetToken = $stmt->fetch();

    if (!$resetToken) {
        jsonError('Reset token not found. Please request a new code.', 400);
    }

    if ((int)$resetToken['used'] === 1) {
        jsonError('This reset code has already been used. Please request a new one.', 400);
    }

    // Verifier expiration en PHP pour eviter les problemes de timezone PHP/MySQL
    if (strtotime($resetToken['expires_at']) < time()) {
        jsonError('This reset code has expired. Please request a new one.', 400);
    }

    // ── Mettre à jour le mot de passe ──────────────────────────
    $db->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
       ->execute([hashPassword($password), $resetToken['user_id']]);

    // ── Invalider le token ─────────────────────────────────────
    $db->prepare('UPDATE password_reset_tokens SET used = 1 WHERE id = ?')
       ->execute([$resetToken['id']]);

    jsonSuccess(['message' => 'Password reset successfully. You can now log in.']);

} catch (Exception $e) {
    error_log('[reset_password] Error: ' . $e->getMessage());
    jsonError('Server error. Please try again.', 500);
}
