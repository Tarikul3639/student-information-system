<?php
/**
 * Read Students API
 * GET /api/students/read.php
 * Supports: search, department filter, pagination, sorting
 */

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../middleware/validator.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Method not allowed', [], 405);
}

requireAuth();

// Query parameters
$search      = trim($_GET['search'] ?? '');
$department  = trim($_GET['department'] ?? '');
$page        = max(1, intval($_GET['page'] ?? 1));
$per_page    = max(1, min(100, intval($_GET['per_page'] ?? 10)));
$sort_by     = $_GET['sort_by'] ?? 'created_at';
$sort_order  = strtoupper($_GET['sort_order'] ?? 'DESC');

// Whitelist sort columns
$allowed_sort = ['student_id', 'full_name', 'gender', 'dob', 'email', 'created_at'];
if (!in_array($sort_by, $allowed_sort)) {
    $sort_by = 'created_at';
}
if (!in_array($sort_order, ['ASC', 'DESC'])) {
    $sort_order = 'DESC';
}

try {
    $db = Database::getInstance()->getConnection();

    // Build query
    $where = [];
    $params = [];

    if (!empty($search)) {
        $where[] = "(s.student_id LIKE ? OR s.full_name LIKE ? OR s.email LIKE ? OR s.phone LIKE ?)";
        $search_term = "%{$search}%";
        $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
    }

    if (!empty($department)) {
        $where[] = "s.department_id = ?";
        $params[] = intval($department);
    }

    $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    // Count total
    $count_sql = "SELECT COUNT(*) as total FROM students s {$where_clause}";
    $stmt = $db->prepare($count_sql);
    $stmt->execute($params);
    $total = $stmt->fetch()['total'];

    // Fetch data
    $offset = ($page - 1) * $per_page;
    $sql = "SELECT s.*, d.department_name, d.department_code 
            FROM students s 
            LEFT JOIN departments d ON s.department_id = d.id 
            {$where_clause} 
            ORDER BY s.{$sort_by} {$sort_order} 
            LIMIT {$per_page} OFFSET {$offset}";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $students = $stmt->fetchAll();

    // Sanitize output
    foreach ($students as &$student) {
        $student['full_name'] = htmlspecialchars_decode($student['full_name']);
        $student['email'] = htmlspecialchars_decode($student['email'] ?? '');
        $student['address'] = htmlspecialchars_decode($student['address'] ?? '');
    }

    jsonResponse(true, 'Students retrieved successfully', [
        'students'    => $students,
        'pagination'  => [
            'total'       => intval($total),
            'page'        => $page,
            'per_page'    => $per_page,
            'total_pages' => ceil($total / $per_page),
        ]
    ]);

} catch (PDOException $e) {
    jsonResponse(false, 'Failed to retrieve students', [], 500);
}
