<?php
// app/api/auth/logout.php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/response_helper.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';

setCorsHeaders();

destroyUserSession();

jsonSuccess(['message' => 'Logged out successfully']);
?>