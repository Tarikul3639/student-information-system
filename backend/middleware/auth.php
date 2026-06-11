<?php
/**
 * Authentication Middleware
 * Verifies user is logged in
 */

require_once __DIR__ . '/../config/session.php';

function requireAuth(): void
{
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode([
            'status'  => false,
            'message' => 'Unauthorized. Please login first.'
        ]);
        exit;
    }
}

function getCurrentUser(): ?array
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    return [
        'id'        => $_SESSION['user_id'],
        'full_name' => $_SESSION['full_name'] ?? '',
        'username'  => $_SESSION['username'] ?? '',
        'email'     => $_SESSION['email'] ?? '',
    ];
}
