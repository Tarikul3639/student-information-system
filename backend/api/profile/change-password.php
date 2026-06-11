<?php
/**
 * Change Password API
 * POST /api/profile/change-password.php
 */

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../middleware/validator.php';
require_once __DIR__ . '/../../middleware/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed', [], 405);
}

requireAuth();
requireCsrfToken();

$input = json_decode(file_get_contents('php://input'), true);

$current_password = $input['current_password'] ?? '';
$new_password     = $input['new_password'] ?? '';
$confirm_password = $input['confirm_password'] ?? '';

$errors = [];

if (empty($current_password)) {
    $errors[] = 'Current password is required';
}
if (empty($new_password)) {
    $errors[] = 'New password is required';
} else {
    $pwd_errors = validatePassword($new_password);
    $errors = array_merge($errors, $pwd_errors);
}
if ($new_password !== $confirm_password) {
    $errors[] = 'New password and confirm password do not match';
}
if ($current_password === $new_password) {
    $errors[] = 'New password must be different from current password';
}

if (!empty($errors)) {
    jsonResponse(false, 'Validation Failed', ['errors' => $errors], 422);
}

try {
    $db = Database::getInstance()->getConnection();

    // Verify current password
    $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($current_password, $user['password'])) {
        jsonResponse(false, 'Current password is incorrect', [], 401);
    }

    // Hash and update new password
    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashed, $_SESSION['user_id']]);

    jsonResponse(true, 'Password changed successfully!');

} catch (PDOException $e) {
    jsonResponse(false, 'Failed to change password', [], 500);
}
