<?php
header("Content-Type: application/json");

// ========== AUTH BYPASS (শুধু ল্যাবের জন্য) ==========
// যেকোনো existing session বা auth চেককে ওভাররাইড করে দিচ্ছি
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['user_id'] = 1;
$_SESSION['logged_in'] = true;
$_SESSION['role'] = 'admin';

// ফেক ফাংশন যাতে requireAuth() কল করলে কিছু না করে
if (!function_exists('requireAuth')) {
    function requireAuth() { return true; }
}
// =================================================

// শুধু ডাটাবেস কনফিগারেশন include করো (কোনো middleware ছাড়া)
require_once __DIR__ . '/../../config/database.php';

// সরাসরি ডাটাবেস কানেকশন নাও (Database ক্লাস থেকে)
$db = Database::getInstance()->getConnection();

// ❌ VULNERABLE: সরাসরি ইউজার ইনপুট (কোনো intval, filter, prepared statement নেই)
$id = isset($_GET['id']) ? $_GET['id'] : '0';

// ❌ VULNERABLE QUERY: string concatenation
$query = "SELECT * FROM students WHERE id = $id";

try {
    $stmt = $db->query($query);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student) {
        echo json_encode([
            "success" => false,
            "message" => "Student not found",
            "sql" => $query
        ]);
    } else {
        echo json_encode([
            "success" => true,
            "student" => $student,
            "sql" => $query
        ]);
    }
} catch (PDOException $e) {
    // ❌ error message exposed – sqlmap error-based injection এর জন্য ব্যবহার করবে
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage(),
        "sql" => $query
    ]);
}
?>