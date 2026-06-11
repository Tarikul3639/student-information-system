<?php
/**
 * Create Department API
 * POST /api/departments/create.php
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

$department_name = trim($input['department_name'] ?? '');
$department_code = trim($input['department_code'] ?? '');
$description     = trim($input['description'] ?? '');

$errors = [];

if (empty($department_name)) {
    $errors[] = 'Department Name is required';
}
if (empty($department_code)) {
    $errors[] = 'Department Code is required';
}

if (!empty($errors)) {
    jsonResponse(false, 'Validation Failed', ['errors' => $errors], 422);
}

try {
    $db = Database::getInstance()->getConnection();

    // Check unique code
    $stmt = $db->prepare("SELECT id FROM departments WHERE department_code = ?");
    $stmt->execute([$department_code]);
    if ($stmt->fetch()) {
        jsonResponse(false, 'Department code already exists', [], 409);
    }

    $stmt = $db->prepare("INSERT INTO departments (department_name, department_code, description) VALUES (?, ?, ?)");
    $stmt->execute([
        sanitize($department_name),
        sanitize($department_code),
        sanitize($description)
    ]);

    jsonResponse(true, 'Department added successfully!', [
        'id' => $db->lastInsertId()
    ]);

} catch (PDOException $e) {
    jsonResponse(false, 'Failed to add department', [], 500);
}
