<?php
/**
 * Delete Student API
 * DELETE /api/students/delete.php
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

// Get ID
$input = json_decode(file_get_contents('php://input'), true);
$id = intval($input['id'] ?? $_GET['id'] ?? 0);

if ($id <= 0) {
    jsonResponse(false, 'Invalid student ID', [], 422);
}

try {
    $db = Database::getInstance()->getConnection();

    // Get student to delete photo
    $stmt = $db->prepare("SELECT photo FROM students WHERE id = ?");
    $stmt->execute([$id]);
    $student = $stmt->fetch();

    if (!$student) {
        jsonResponse(false, 'Student not found', [], 404);
    }

    // Delete student
    $stmt = $db->prepare("DELETE FROM students WHERE id = ?");
    $stmt->execute([$id]);

    // Delete photo file
    if ($student['photo']) {
        $photo_path = __DIR__ . '/../../uploads/' . $student['photo'];
        if (file_exists($photo_path)) {
            unlink($photo_path);
        }
    }

    jsonResponse(true, 'Student deleted successfully!');

} catch (PDOException $e) {
    jsonResponse(false, 'Failed to delete student', [], 500);
}
