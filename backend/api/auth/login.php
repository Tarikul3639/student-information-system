<?php

/**
 * User Login API
 * POST /api/auth/login.php
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

$login_input = trim($input['email'] ?? ''); // Can be username or email
$password    = $input['password'] ?? '';

// Validation
if (empty($login_input)) {
    jsonResponse(false, 'Username or Email is required', [], 422);
}
if (empty($password)) {
    jsonResponse(false, 'Password is required', [], 422);
}

try {
    $db = Database::getInstance()->getConnection();

    // Check if input is email or username
    if (validateEmail($login_input)) {
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    } else {
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    }
    $stmt->execute([$login_input]);
    $user = $stmt->fetch();

    if (!$user) {
        jsonResponse(false, 'Invalid credentials', [], 401);
    }

    // Verify password
    if (!password_verify($password, $user['password'])) {
        jsonResponse(false, 'Invalid credentials', [], 401);
    }

    // Check if password needs rehash (algorithm upgrade)
    if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
        $new_hash = password_hash($password, PASSWORD_DEFAULT);
        $update = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update->execute([$new_hash, $user['id']]);
    }

    // Create session
    session_regenerate_id(true);
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['full_name']  = $user['full_name'];
    $_SESSION['username']   = $user['username'];
    $_SESSION['email']      = $user['email'];
    $_SESSION['last_activity'] = time();
    $_SESSION['created']    = time();

    // Generate new CSRF token for authenticated session
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    jsonResponse(true, 'Login successful!', [
        'user' => [
            'id'        => $user['id'],
            'full_name' => $user['full_name'],
            'username'  => $user['username'],
            'email'     => $user['email'],
        ],
        'csrf_token' => $_SESSION['csrf_token']
    ]);
} catch (PDOException $e) {
    jsonResponse(false, 'Login failed. Please try again.', [], 500);
}
