<?php
// app/api/auth/verify_code.php
// Verifie le code 6 chiffres SANS modifier le mot de passe

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/response_helper.php';

setCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

$body = getJsonBody();
if (!$body || empty($body['code'])) {
    jsonError('Code is required.', 400);
}

$code = trim($body['code']);
$db   = getDB();

$stmt = $db->prepare(
    'SELECT id, user_id FROM password_reset_tokens
     WHERE code = ? AND used = 0 AND expires_at > NOW()
     LIMIT 1'
);
$stmt->execute([$code]);
$resetToken = $stmt->fetch();

if (!$resetToken) {
    jsonError('Invalid or expired code. Please request a new one.', 400);
}

// Code valide, on ne touche a rien
jsonSuccess(['message' => 'Code verified.', 'valid' => true]);
