<?php
/**
 * Dashboard Statistics API
 * GET /api/dashboard/stats.php
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

    // Total students
    $total_students = $db->query("SELECT COUNT(*) as count FROM students")->fetch()['count'];

    // Male students
    $male_students = $db->query("SELECT COUNT(*) as count FROM students WHERE gender = 'Male'")->fetch()['count'];

    // Female students
    $female_students = $db->query("SELECT COUNT(*) as count FROM students WHERE gender = 'Female'")->fetch()['count'];

    // Total departments
    $total_departments = $db->query("SELECT COUNT(*) as count FROM departments")->fetch()['count'];

    // Recent students (last 5)
    $stmt = $db->query("SELECT s.*, d.department_name FROM students s LEFT JOIN departments d ON s.department_id = d.id ORDER BY s.created_at DESC LIMIT 5");
    $recent_students = $stmt->fetchAll();

    // Department distribution
    $stmt = $db->query("SELECT d.department_name, COUNT(s.id) as student_count FROM departments d LEFT JOIN students s ON d.id = s.department_id GROUP BY d.id ORDER BY student_count DESC");
    $department_stats = $stmt->fetchAll();

    jsonResponse(true, 'Dashboard statistics retrieved', [
        'stats' => [
            'total_students'    => intval($total_students),
            'male_students'     => intval($male_students),
            'female_students'   => intval($female_students),
            'total_departments' => intval($total_departments),
        ],
        'recent_students'   => $recent_students,
        'department_stats'  => $department_stats,
    ]);

} catch (PDOException $e) {
    jsonResponse(false, 'Failed to retrieve statistics', [], 500);
}
