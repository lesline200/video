<?php
// app/api/auth/logout.php

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';

// ─── Détruire la session ───────────────────────────────────
destroyUserSession();

echo json_encode([
    'success' => true,
    'message' => 'Logged out successfully'
]);
?>