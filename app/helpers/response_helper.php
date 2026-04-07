<?php
// app/helpers/response_helper.php

/**
 * Configure les headers CORS et Content-Type JSON.
 * À appeler en tout début de chaque endpoint API.
 */
function setCorsHeaders(): void {
    $allowed_origins = [
        'http://localhost',
        'http://localhost:80',
        'http://127.0.0.1',
    ];

    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

    if (in_array($origin, $allowed_origins, true)) {
        header("Access-Control-Allow-Origin: $origin");
    } else {
        header("Access-Control-Allow-Origin: http://localhost");
    }

    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Content-Type: application/json; charset=UTF-8');

    // Preflight OPTIONS → répondre immédiatement
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

/**
 * Envoyer une réponse JSON de succès.
 */
function jsonSuccess(array $data = [], int $code = 200): void {
    http_response_code($code);
    echo json_encode(array_merge(['success' => true], $data), JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Envoyer une réponse JSON d'erreur.
 */
function jsonError(string $message, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Lire et décoder le body JSON de la requête.
 * Retourne null si le body est vide ou invalide.
 */
function getJsonBody(): ?array {
    $raw = file_get_contents('php://input');
    if (empty($raw)) return null;
    $data = json_decode($raw, true);
    return (json_last_error() === JSON_ERROR_NONE) ? $data : null;
}
