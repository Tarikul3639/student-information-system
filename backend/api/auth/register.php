<?php
/**
 * User Registration API
 * POST /api/auth/register.php
 */

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../middleware/validator.php';
require_once __DIR__ . '/../../middleware/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed', [], 405);
}

requireCsrfToken();

// Get input
$input = json_decode(file_get_contents('php://input'), true);

$full_name      = trim($input['full_name'] ?? '');
$username       = trim($input['username'] ?? '');
$email          = trim($input['email'] ?? '');
$password       = $input['password'] ?? '';
$confirm_password = $input['confirm_password'] ?? '';

// Validation
$errors = [];

if (empty($full_name)) {
    $errors[] = 'Full Name is required';
}
if (empty($username)) {
    $errors[] = 'Username is required';
} elseif (strlen($username) < 3) {
    $errors[] = 'Username must be at least 3 characters';
} elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    $errors[] = 'Username can only contain letters, numbers, and underscores';
}
if (empty($email)) {
    $errors[] = 'Email is required';
} elseif (!validateEmail($email)) {
    $errors[] = 'Invalid email format';
}
if (empty($password)) {
    $errors[] = 'Password is required';
} else {
    $password_errors = validatePassword($password);
    $errors = array_merge($errors, $password_errors);
}
if ($password !== $confirm_password) {
    $errors[] = 'Passwords do not match';
}

if (!empty($errors)) {
    jsonResponse(false, 'Validation Failed', ['errors' => $errors], 422);
}

// Database operations
try {
    $db = Database::getInstance()->getConnection();

    // Check unique email
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        jsonResponse(false, 'Email already exists', [], 409);
    }

    // Check unique username
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        jsonResponse(false, 'Username already taken', [], 409);
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $stmt = $db->prepare("INSERT INTO users (full_name, username, email, password) VALUES (?, ?, ?, ?)");
    $stmt->execute([$full_name, $username, $email, $hashed_password]);

    jsonResponse(true, 'Registration successful! You can now login.', ['user_id' => $db->lastInsertId()]);

} catch (PDOException $e) {
    jsonResponse(false, 'Registration failed. Please try again.', [], 500);
}
