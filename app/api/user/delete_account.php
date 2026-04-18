<?php
// app/api/user/delete_account.php

ob_start(); // ← ajoute cette ligne



// ... reste du fichier

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';

// ─── Authentification ───────────────────────────────────────
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$user = getSessionUser();
$userId = $user['id'];

// ─── Récupérer les données POST ────────────────────────────
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['password']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Password required for account deletion']);
    exit;
}

// ─── Connexion BD ──────────────────────────────────────────
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=social_automator;charset=utf8mb4',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

// ─── Récupérer l'utilisateur actuel ────────────────────────
try {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$currentUser) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'User not found']);
        exit;
    }

    // ─── Vérifier le mot de passe ──────────────────────────
    if (!verifyPassword($data['password'], $currentUser['password_hash'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Incorrect password']);
        exit;
    }

    // ─── Commencer une transaction ─────────────────────────
    $pdo->beginTransaction();

    // ─── Supprimer ou archiver les données de l'utilisateur ──
    // 1. Marquer l'utilisateur comme supprimé (soft delete)
    $stmt = $pdo->prepare('UPDATE users SET deleted_at = NOW() WHERE id = ?');
    $stmt->execute([$userId]);

    // 2. Supprimer les séries de contenu de cet utilisateur
    $stmt = $pdo->prepare('DELETE FROM content_series WHERE user_id = ?');
    $stmt->execute([$userId]);

    // 3. Supprimer les vidéos de cet utilisateur
    $stmt = $pdo->prepare('DELETE FROM videos WHERE user_id = ?');
    $stmt->execute([$userId]);

    // 4. Supprimer l'historique de facturation
    $stmt = $pdo->prepare('DELETE FROM billing_history WHERE user_id = ?');
    $stmt->execute([$userId]);

    // 5. Supprimer les sessions utilisateur
    $stmt = $pdo->prepare('DELETE FROM user_sessions WHERE user_id = ?');
    $stmt->execute([$userId]);

    // 6. Supprimer les comptes de réseaux sociaux liés
    $stmt = $pdo->prepare('DELETE FROM user_social_accounts WHERE user_id = ?');
    $stmt->execute([$userId]);

    // 7. Supprimer les statistiques d'utilisation
    $stmt = $pdo->prepare('DELETE FROM usage_stats WHERE user_id = ?');
    $stmt->execute([$userId]);

    // ─── Valider la transaction ────────────────────────────
    $pdo->commit();

    // ─── Détruire la session ───────────────────────────────
    destroyUserSession();

    echo json_encode([
        'success' => true,
        'message' => 'Your account has been permanently deleted'
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Deletion failed: ' . $e->getMessage()]);
}
?>