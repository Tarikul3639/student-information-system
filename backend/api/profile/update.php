<?php
/**
 * Update User Profile API
 * POST /api/profile/update.php
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

// Support both JSON and form-data
$input = $_POST;
if (empty($input)) {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
}

$full_name = trim($input['full_name'] ?? '');
$username  = trim($input['username'] ?? '');
$email     = trim($input['email'] ?? '');

$errors = [];

if (empty($full_name)) {
    $errors[] = 'Full Name is required';
}
if (empty($username)) {
    $errors[] = 'Username is required';
} elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    $errors[] = 'Username can only contain letters, numbers, and underscores';
}
if (empty($email)) {
    $errors[] = 'Email is required';
} elseif (!validateEmail($email)) {
    $errors[] = 'Invalid email format';
}

if (!empty($errors)) {
    jsonResponse(false, 'Validation Failed', ['errors' => $errors], 422);
}

try {
    $db = Database::getInstance()->getConnection();

    // Check unique username (exclude current user)
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $stmt->execute([$username, $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        jsonResponse(false, 'Username already taken', [], 409);
    }

    // Check unique email (exclude current user)
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        jsonResponse(false, 'Email already exists', [], 409);
    }

    $stmt = $db->prepare("UPDATE users SET full_name = ?, username = ?, email = ? WHERE id = ?");
    $stmt->execute([$full_name, $username, $email, $_SESSION['user_id']]);

    // Update session
    $_SESSION['full_name'] = $full_name;
    $_SESSION['username']  = $username;
    $_SESSION['email']     = $email;

    jsonResponse(true, 'Profile updated successfully!');

} catch (PDOException $e) {
    jsonResponse(false, 'Failed to update profile', [], 500);
}
