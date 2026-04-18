<?php
// app/config/config.php


ob_start(); // ← PREMIÈRE ligne, avant tout le reste

// Désactiver l'affichage des erreurs dans les API (les loguer seulement)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);


// ─── Base de données ─────────────────────────────────────────────
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
// ... reste du fichier

// ─── Email (Hostinger SMTP) ───────────────────────────────────────
define('MAIL_HOST',     'smtp.hostinger.com');
define('MAIL_PORT',     465);
define('MAIL_SECURE',   'ssl');                        // 'ssl' pour port 465
define('MAIL_USER',     'support@grayboost.com');      // ← ton email Hostinger
define('MAIL_PASS',     'GrayBoost@2026');                           // ← mot de passe email Hostinger
define('MAIL_FROM',     'support@grayboost.com');
define('MAIL_FROM_NAME','VidGenius');
// ─── Base de données ─────────────────────────────────────────────
// ─── Base de données ─────────────────────────────────────────────
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'social_automator');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');

// ─── Environnement ───────────────────────────────────────────────
define('APP_ENV', 'development');   // 'development' | 'production'
define('APP_URL', 'http://localhost/video');

// ─── JWT ─────────────────────────────────────────────────────────
define('JWT_SECRET',  'CHANGE_ME_SUPER_SECRET_KEY_32CHARS!!');
define('JWT_EXPIRY',  60 * 60 * 24 * 7);   // 7 jours

// ─── Google OAuth ────────────────────────────────────────────────
define('GOOGLE_CLIENT_ID', '979354572454-oag7h3nr78843ni7gdc0ds6t4r3q3nef.apps.googleusercontent.com');

// ─── Session ─────────────────────────────────────────────────────
define('SESSION_NAME',    'video_session');
define('SESSION_EXPIRY',  60 * 60 * 24 * 7); // 7 jours



// ─── Chemins fichiers générés ─────────────────────────────────
define('BASE_DIR',   'C:/wamp64/www/video');
define('IMAGES_DIR', 'C:/wamp64/www/video');
define('AUDIO_DIR',  'C:/wamp64/www/video');


// ─── Initialisation session ──────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => SESSION_EXPIRY,
        'path'     => '/',
        'domain'   => '',
        'secure'   => APP_ENV === 'production',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
