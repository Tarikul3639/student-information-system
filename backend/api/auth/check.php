<?php
/**
 * Check Session / Auth Status API
 * GET /api/auth/check.php
 */

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../middleware/validator.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Method not allowed', [], 405);
}

if (isset($_SESSION['user_id'])) {
    jsonResponse(true, 'Authenticated', [
        'user' => [
            'id'        => $_SESSION['user_id'],
            'full_name' => $_SESSION['full_name'] ?? '',
            'username'  => $_SESSION['username'] ?? '',
            'email'     => $_SESSION['email'] ?? '',
        ],
        'csrf_token' => $_SESSION['csrf_token'] ?? ''
    ]);
} else {
    jsonResponse(false, 'Not authenticated', [], 401);
}
