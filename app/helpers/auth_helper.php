<?php
// app/helpers/auth_helper.php

require_once __DIR__ . '/../config/config.php';

// ═══════════════════════════════════════════════════════
//  JWT (implémentation légère sans librairie externe)
// ═══════════════════════════════════════════════════════

function base64UrlEncode(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64UrlDecode(string $data): string {
    $padded = str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT);
    return base64_decode($padded);
}

/**
 * Générer un JWT signé (HS256).
 */
function jwtEncode(array $payload): string {
    $header  = base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload['iat'] = time();
    $payload['exp'] = time() + JWT_EXPIRY;
    $body    = base64UrlEncode(json_encode($payload));
    $sig     = base64UrlEncode(hash_hmac('sha256', "$header.$body", JWT_SECRET, true));
    return "$header.$body.$sig";
}

/**
 * Décoder et vérifier un JWT.
 * Retourne le payload ou null si invalide/expiré.
 */
function jwtDecode(string $token): ?array {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;

    [$header, $body, $sig] = $parts;

    $expected = base64UrlEncode(hash_hmac('sha256', "$header.$body", JWT_SECRET, true));
    if (!hash_equals($expected, $sig)) return null;

    $payload = json_decode(base64UrlDecode($body), true);
    if (!$payload) return null;

    // Vérifie expiration
    if (isset($payload['exp']) && $payload['exp'] < time()) return null;

    return $payload;
}

// ═══════════════════════════════════════════════════════
//  Session helpers
// ═══════════════════════════════════════════════════════

/**
 * Créer une session utilisateur après login/signup.
 */
function createUserSession(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name']  = $user['name'];
    $_SESSION['logged_in']  = true;
    $_SESSION['login_time'] = time();
}

/**
 * Détruire la session (logout).
 */
function destroyUserSession(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

/**
 * Vérifier si l'utilisateur est connecté (via session).
 */
function isLoggedIn(): bool {
    return !empty($_SESSION['logged_in']) && !empty($_SESSION['user_id']);
}

/**
 * Récupérer l'utilisateur connecté depuis la session.
 */
function getSessionUser(): ?array {
    if (!isLoggedIn()) return null;
    return [
        'id'    => $_SESSION['user_id'],
        'email' => $_SESSION['user_email'],
        'name'  => $_SESSION['user_name'],
    ];
}

/**
 * Rediriger vers login si non connecté.
 * À utiliser dans les pages protégées (non-API).
 */
function requireAuth(): array {
    if (!isLoggedIn()) {
        header('Location: /video/login.php');
        exit;
    }
    return getSessionUser();
}

// ═══════════════════════════════════════════════════════
//  Password helpers
// ═══════════════════════════════════════════════════════

function hashPassword(string $password): string {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

function verifyPassword(string $password, string $hash): bool {
    return password_verify($password, $hash);
}

// ═══════════════════════════════════════════════════════
//  Validation helpers
// ═══════════════════════════════════════════════════════


function validateEmail(string $email): bool {
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

function sanitizeString(string $str): string {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}
