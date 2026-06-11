<?php
/**
 * Update Department API
 * PUT /api/departments/update.php
 */

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../middleware/validator.php';
require_once __DIR__ . '/../../middleware/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed', [], 405);
}

requireAuth();
requireCsrfToken();

$input = json_decode(file_get_contents('php://input'), true);

$id              = intval($input['id'] ?? 0);
$department_name = trim($input['department_name'] ?? '');
$department_code = trim($input['department_code'] ?? '');
$description     = trim($input['description'] ?? '');

$errors = [];

if ($id <= 0) {
    $errors[] = 'Invalid department ID';
}
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

    // Check unique code (exclude current)
    $stmt = $db->prepare("SELECT id FROM departments WHERE department_code = ? AND id != ?");
    $stmt->execute([$department_code, $id]);
    if ($stmt->fetch()) {
        jsonResponse(false, 'Department code already exists', [], 409);
    }

    $stmt = $db->prepare("UPDATE departments SET department_name = ?, department_code = ?, description = ? WHERE id = ?");
    $stmt->execute([
        sanitize($department_name),
        sanitize($department_code),
        sanitize($description),
        $id
    ]);

    jsonResponse(true, 'Department updated successfully!');

} catch (PDOException $e) {
    jsonResponse(false, 'Failed to update department', [], 500);
}
