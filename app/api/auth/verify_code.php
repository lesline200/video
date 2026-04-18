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

try {
    $raw = file_get_contents('php://input');
    if (empty($raw)) {
        jsonError('Empty request body.', 400);
    }

    $body = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        jsonError('Invalid JSON: ' . json_last_error_msg(), 400);
    }

    if (!$body || empty($body['code'])) {
        jsonError('Code is required.', 400);
    }

    $code = trim($body['code']);
    $db   = getDB();

    // Chercher le code sans filtrer used/expires pour donner un message precis
    $stmt = $db->prepare(
        'SELECT id, user_id, used, expires_at FROM password_reset_tokens
         WHERE code = ?
         ORDER BY created_at DESC
         LIMIT 1'
    );
    $stmt->execute([$code]);
    $resetToken = $stmt->fetch();

    if (!$resetToken) {
        jsonError('Code not found. Please request a new code.', 400);
    }

    if ((int)$resetToken['used'] === 1) {
        jsonError('This code has already been used. Please request a new code.', 400);
    }

    // Verifier expiration en PHP pour eviter les problemes de timezone PHP/MySQL
    if (strtotime($resetToken['expires_at']) < time()) {
        jsonError('This code has expired. Please request a new code.', 400);
    }

    // Code valide, on ne touche a rien
    jsonSuccess(['message' => 'Code verified.', 'valid' => true]);

} catch (Exception $e) {
    error_log('[verify_code] Error: ' . $e->getMessage());
    jsonError('Server error. Please try again.', 500);
}
