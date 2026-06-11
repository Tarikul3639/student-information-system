<?php
/**
 * CSRF Protection Middleware
 * Generates and validates CSRF tokens
 */

require_once __DIR__ . '/../config/session.php';

/**
 * Generate CSRF token if not exists
 */
function generateCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Get current CSRF token (generate if needed)
 */
function getCsrfToken(): string
{
    return generateCsrfToken();
}

/**
 * Validate CSRF token from request
 * Checks both header and POST body
 */
function validateCsrfToken(): bool
{
    $token = null;

    // Check header first (for AJAX requests)
    $headers = getallheaders();
    $token = $headers['X-CSRF-Token'] ?? null;

    // Fallback to POST body
    if (!$token) {
        $input = json_decode(file_get_contents('php://input'), true);
        $token = $input['_csrf_token'] ?? null;
    }

    // Also check $_POST
    if (!$token) {
        $token = $_POST['_csrf_token'] ?? null;
    }

    if (!$token || !isset($_SESSION['csrf_token'])) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Require valid CSRF token for POST/PUT/DELETE requests
 */
function requireCsrfToken(): void
{
    $method = $_SERVER['REQUEST_METHOD'];
    if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
        if (!validateCsrfToken()) {
            http_response_code(403);
            echo json_encode([
                'status'  => false,
                'message' => 'CSRF token validation failed. Please refresh the page.'
            ]);
            exit;
        }
    }
}
