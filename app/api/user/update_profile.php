<?php
// app/api/user/update_profile.php

ob_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';

// ─── Authentification ───────────────────────────────────────
if (!isLoggedIn()) {
    ob_clean();
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$user   = getSessionUser();
$userId = $user['id'];

// ─── Méthode POST uniquement ────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean();
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// ─── Récupérer les données POST ─────────────────────────────
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data || !is_array($data)) {
    ob_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON payload']);
    exit;
}

// ─── Rate limiting simple (sessions) ────────────────────────
if (!isset($_SESSION['profile_attempts'])) {
    $_SESSION['profile_attempts']    = 0;
    $_SESSION['profile_attempts_ts'] = time();
}
if (time() - $_SESSION['profile_attempts_ts'] > 900) {
    $_SESSION['profile_attempts']    = 0;
    $_SESSION['profile_attempts_ts'] = time();
}
if ($_SESSION['profile_attempts'] >= 5) {
    ob_clean();
    http_response_code(429);
    echo json_encode(['success' => false, 'error' => 'Too many attempts. Please wait 15 minutes.']);
    exit;
}

// ─── Connexion BD ───────────────────────────────────────────
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// ─── Récupérer l'utilisateur actuel ─────────────────────────
try {
    $stmt = $pdo->prepare('SELECT id, name, email, password_hash FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $currentUser = $stmt->fetch();

    if (!$currentUser) {
        ob_clean();
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'User not found']);
        exit;
    }
} catch (PDOException $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
}

// ─── Construire les champs à mettre à jour ──────────────────
$updateFields = [];
$errors       = [];

// — Nom —
if (array_key_exists('name', $data)) {
    $name = trim($data['name']);
    if (strlen($name) < 2) {
        $errors[] = 'Name must be at least 2 characters.';
    } elseif (strlen($name) > 100) {
        $errors[] = 'Name cannot exceed 100 characters.';
    } else {
        $updateFields['name'] = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    }
}

// — Changement de mot de passe —
$changingPassword = !empty($data['new_password']);

if ($changingPassword) {
    $_SESSION['profile_attempts']++;

    if (empty($data['current_password'])) {
        ob_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Current password is required to set a new password.']);
        exit;
    }

    if (!password_verify($data['current_password'], $currentUser['password_hash'])) {
        ob_clean();
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Current password is incorrect.']);
        exit;
    }

    $newPwd = $data['new_password'];
    if (strlen($newPwd) < 8) {
        $errors[] = 'New password must be at least 8 characters.';
    } elseif (strlen($newPwd) > 255) {
        $errors[] = 'New password is too long.';
    } elseif ($data['current_password'] === $newPwd) {
        $errors[] = 'New password must be different from the current password.';
    } else {
        $updateFields['password_hash'] = password_hash($newPwd, PASSWORD_BCRYPT, ['cost' => 12]);
        $_SESSION['profile_attempts']  = 0;
    }
}

// ─── Stopper si erreurs de validation ───────────────────────
if (!empty($errors)) {
    ob_clean();
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => implode(' ', $errors)]);
    exit;
}

// ─── Rien à mettre à jour ───────────────────────────────────
if (empty($updateFields)) {
    ob_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No changes to save.']);
    exit;
}

// ─── Mise à jour en base ─────────────────────────────────────
try {
    $setClauses = [];
    $values     = [];

    foreach ($updateFields as $col => $val) {
        $setClauses[] = "$col = ?";
        $values[]     = $val;
    }
    $values[] = $userId;

    $sql  = 'UPDATE users SET ' . implode(', ', $setClauses) . ' WHERE id = ?';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($values);

    if (isset($updateFields['name'])) {
        $_SESSION['user_name'] = $updateFields['name'];
    }

    $updatedName = $updateFields['name'] ?? $currentUser['name'];

    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => $changingPassword
            ? 'Profile and password updated successfully.'
            : 'Profile updated successfully.',
        'id'    => $userId,
        'name'  => $updatedName,
        'email' => $currentUser['email'],
    ]);

} catch (PDOException $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Update failed: ' . $e->getMessage()]);
}