<?php
// app/api/auth/forgot_password.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/response_helper.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';

require_once __DIR__ . '/../../../vendor/phpmailer/autoload.php/';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

setCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

$body = getJsonBody();
if (!$body || empty($body['email'])) {
    jsonError('Email is required.', 400);
}

$email = trim($body['email']);
if (!validateEmail($email)) {
    jsonError('Invalid email address.', 400);
}

$db = getDB();

$stmt = $db->prepare('SELECT id, name FROM users WHERE email = ? AND is_active = 1 LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

// Securite : meme reponse si email inexistant
if (!$user) {
    jsonSuccess(['message' => 'If this email exists, a reset code has been sent.']);
}

// Generer token + code (sans fonction separee)
$tokenId = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
    mt_rand(0, 0xffff), mt_rand(0, 0xffff),
    mt_rand(0, 0xffff),
    mt_rand(0, 0x0fff) | 0x4000,
    mt_rand(0, 0x3fff) | 0x8000,
    mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
);
$token     = bin2hex(random_bytes(32));
$code      = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

// Invalider anciens tokens
$db->prepare('UPDATE password_reset_tokens SET used = 1 WHERE user_id = ?')
   ->execute([(string) $user['id']]);

// Inserer nouveau token
$db->prepare(
    'INSERT INTO password_reset_tokens (id, user_id, token, code, expires_at, used)
     VALUES (?, ?, ?, ?, ?, 0)'
)->execute([$tokenId, (string) $user['id'], $token, $code, $expiresAt]);

// Template email
$name = htmlspecialchars($user['name']);
$year = date('Y');
$emailBody ='
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"/></head>
<body style="font-family:Arial,sans-serif;background:#f7f8fc;padding:2rem;margin:0">
  <div style="max-width:480px;margin:0 auto;background:#fff;border-radius:1rem;padding:2rem;border:1px solid #e4e7f0">
    <div style="text-align:center;margin-bottom:1.5rem">
      <h2 style="color:#0f172a;margin:0;font-size:1.4rem">Password Reset</h2>
      <p style="color:#475569;margin:.5rem 0 0">VidGenius</p>
    </div>
    <p style="color:#475569">Hello <strong>'.$name.'</strong>,</p>
    <p style="color:#475569;line-height:1.6">
      We received a request to reset your password.
      Use the 6-digit code below - it expires in <strong>1 hour</strong>.
    </p>
    <div style="text-align:center;margin:2rem 0">
      <div style="display:inline-block;background:#eff6ff;border-radius:.75rem;padding:1rem 2rem;border:2px dashed #bfdbfe">
        <span style="font-size:2.5rem;font-weight:700;letter-spacing:.6rem;color:#2563eb;font-family:monospace">
        '  .$code.'
        </span>
      </div>
    </div>
    <p style="color:#94a3b8;font-size:.85rem;line-height:1.6">
      If you did not request this, you can safely ignore this email.
    </p>
    <hr style="border:none;border-top:1px solid #e4e7f0;margin:1.5rem 0"/>
    <p style="color:#94a3b8;font-size:.8rem;text-align:center;margin:0">
      &copy; '.$year.' VidGenius - All rights reserved
    </p>
  </div>
</body>
</html>';

// Envoi email via PHPMailer
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = MAIL_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = MAIL_USER;
    $mail->Password   = MAIL_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = MAIL_PORT;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
    $mail->addAddress($email, $user['name']);

    $mail->isHTML(true);
    $mail->Subject = 'Your VidGenius password reset code';
    $mail->Body    = $emailBody;
    $mail->AltBody = 'Your reset code is: '.$code.' (expires in 1 hour)';

    $mail->send();

} catch (Exception $e) {
    error_log('[VidGenius] Mailer error: ' . $mail->ErrorInfo);
    jsonError('Failed to send email. Please try again later.', 500);
}

jsonSuccess(['message' => 'Reset code sent successfully.']);