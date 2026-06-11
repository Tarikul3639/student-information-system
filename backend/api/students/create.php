<?php
/**
 * Create Student API
 * POST /api/students/create.php
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

// Get input (support both JSON and form-data for file upload)
$input = $_POST;
if (empty($input)) {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
}

$student_id    = trim($input['student_id'] ?? '');
$full_name     = trim($input['full_name'] ?? '');
$department_id = intval($input['department_id'] ?? 0);
$gender        = trim($input['gender'] ?? '');
$dob           = trim($input['dob'] ?? '');
$email         = trim($input['email'] ?? '');
$phone         = trim($input['phone'] ?? '');
$address       = trim($input['address'] ?? '');

// Validation
$errors = [];

if (empty($student_id)) {
    $errors[] = 'Student ID is required';
}
if (empty($full_name)) {
    $errors[] = 'Full Name is required';
}
if ($department_id <= 0) {
    $errors[] = 'Department is required';
}
if (empty($gender)) {
    $errors[] = 'Gender is required';
} elseif (!validateGender($gender)) {
    $errors[] = 'Invalid gender value';
}
if (!empty($dob) && !validateDate($dob)) {
    $errors[] = 'Invalid date of birth format';
}
if (!empty($email) && !validateEmail($email)) {
    $errors[] = 'Invalid email format';
}
if (!empty($phone) && !validatePhone($phone)) {
    $errors[] = 'Invalid phone number format';
}

if (!empty($errors)) {
    jsonResponse(false, 'Validation Failed', ['errors' => $errors], 422);
}

try {
    $db = Database::getInstance()->getConnection();

    // Check unique student_id
    $stmt = $db->prepare("SELECT id FROM students WHERE student_id = ?");
    $stmt->execute([$student_id]);
    if ($stmt->fetch()) {
        jsonResponse(false, 'Student ID already exists', [], 409);
    }

    // Verify department exists
    $stmt = $db->prepare("SELECT id FROM departments WHERE id = ?");
    $stmt->execute([$department_id]);
    if (!$stmt->fetch()) {
        jsonResponse(false, 'Invalid department', [], 422);
    }

    // Handle file upload
    $photo = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload_errors = validateFileUpload($_FILES['photo']);
        if (!empty($upload_errors)) {
            jsonResponse(false, 'File upload failed', ['errors' => $upload_errors], 422);
        }

        $upload_dir = __DIR__ . '/../../uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $safe_name = generateSafeFilename($_FILES['photo']['name']);
        $target_path = $upload_dir . $safe_name;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_path)) {
            $photo = $safe_name;
        } else {
            jsonResponse(false, 'Failed to upload photo', [], 500);
        }
    }

    // Insert student
    $stmt = $db->prepare("INSERT INTO students (student_id, full_name, department_id, gender, dob, email, phone, address, photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        sanitize($student_id),
        sanitize($full_name),
        $department_id,
        $gender,
        $dob ?: null,
        sanitize($email),
        sanitize($phone),
        sanitize($address),
        $photo
    ]);

    jsonResponse(true, 'Student added successfully!', [
        'id' => $db->lastInsertId()
    ]);

} catch (PDOException $e) {
    jsonResponse(false, 'Failed to add student', [], 500);
}
