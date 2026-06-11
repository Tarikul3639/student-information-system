<?php
/**
 * Read Departments API
 * GET /api/departments/read.php
 */

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../middleware/validator.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Method not allowed', [], 405);
}

requireAuth();

$search = trim($_GET['search'] ?? '');

try {
    $db = Database::getInstance()->getConnection();

    if (!empty($search)) {
        $stmt = $db->prepare("SELECT * FROM departments WHERE department_name LIKE ? OR department_code LIKE ? ORDER BY department_name ASC");
        $term = "%{$search}%";
        $stmt->execute([$term, $term]);
    } else {
        $stmt = $db->query("SELECT * FROM departments ORDER BY department_name ASC");
    }

    $departments = $stmt->fetchAll();

    jsonResponse(true, 'Departments retrieved successfully', [
        'departments' => $departments
    ]);

} catch (PDOException $e) {
    jsonResponse(false, 'Failed to retrieve departments', [], 500);
}
