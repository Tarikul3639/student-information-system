<?php
/**
 * Get CSRF Token API
 * GET /api/auth/csrf.php
 */

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../middleware/csrf.php';
require_once __DIR__ . '/../../middleware/validator.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Method not allowed', [], 405);
}

$token = getCsrfToken();

jsonResponse(true, 'CSRF token generated', ['csrf_token' => $token]);
