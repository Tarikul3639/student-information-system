<?php
/**
 * Delete Department API
 * DELETE /api/departments/delete.php
 */

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../middleware/validator.php';
require_once __DIR__ . '/../../middleware/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed', [], 405);
}

requireAuth();
requireCsrfToken();

$input = json_decode(file_get_contents('php://input'), true);
$id = intval($input['id'] ?? $_GET['id'] ?? 0);

if ($id <= 0) {
    jsonResponse(false, 'Invalid department ID', [], 422);
}

try {
    $db = Database::getInstance()->getConnection();

    // Check if department has students
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM students WHERE department_id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch();

    if ($result['count'] > 0) {
        jsonResponse(false, 'Cannot delete department with existing students. Reassign students first.', [], 409);
    }

    $stmt = $db->prepare("DELETE FROM departments WHERE id = ?");
    $stmt->execute([$id]);

    jsonResponse(true, 'Department deleted successfully!');

} catch (PDOException $e) {
    jsonResponse(false, 'Failed to delete department', [], 500);
}
