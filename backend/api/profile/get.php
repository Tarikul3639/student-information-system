<?php
/**
 * Get User Profile API
 * GET /api/profile/get.php
 */

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../middleware/validator.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Method not allowed', [], 405);
}

requireAuth();

try {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("SELECT id, full_name, username, email, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        jsonResponse(false, 'User not found', [], 404);
    }

    jsonResponse(true, 'Profile retrieved successfully', ['user' => $user]);

} catch (PDOException $e) {
    jsonResponse(false, 'Failed to retrieve profile', [], 500);
}
