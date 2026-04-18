<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/../vendors/email_vendor/autoload.php';

function sendEmail($recipientEmail, $content, $title)
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.hostinger.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'TON_EMAIL_HOSTINGER'; // ⚠️ à remplir
        $mail->Password = 'TON_MOT_DE_PASSE';    // ⚠️ à remplir
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom('no-reply@trainmastas.com', 'TrainMastas');
        $mail->addAddress($recipientEmail);

        $mail->isHTML(true);
        $mail->Subject = $title;
        $mail->Body = $content;

        $mail->send();

        return true;

    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}