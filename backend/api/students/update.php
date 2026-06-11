<?php
/**
 * Update Student API
 * PUT /api/students/update.php
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

// Get input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$id            = intval($input['id'] ?? 0);
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

if ($id <= 0) {
    $errors[] = 'Invalid student ID';
}
if (empty($student_id)) {
    $errors[] = 'Student ID is required';
}
if (empty($full_name)) {
    $errors[] = 'Full Name is required';
}
if ($department_id <= 0) {
    $errors[] = 'Department is required';
}
if (!empty($gender) && !validateGender($gender)) {
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

    // Check student exists
    $stmt = $db->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    if (!$existing) {
        jsonResponse(false, 'Student not found', [], 404);
    }

    // Check unique student_id (exclude current)
    $stmt = $db->prepare("SELECT id FROM students WHERE student_id = ? AND id != ?");
    $stmt->execute([$student_id, $id]);
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
    $photo = $existing['photo'];
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
            // Delete old photo
            if ($photo && file_exists($upload_dir . $photo)) {
                unlink($upload_dir . $photo);
            }
            $photo = $safe_name;
        }
    }

    // Update student
    $stmt = $db->prepare("UPDATE students SET student_id = ?, full_name = ?, department_id = ?, gender = ?, dob = ?, email = ?, phone = ?, address = ?, photo = ? WHERE id = ?");
    $stmt->execute([
        sanitize($student_id),
        sanitize($full_name),
        $department_id,
        $gender ?: $existing['gender'],
        $dob ?: null,
        sanitize($email),
        sanitize($phone),
        sanitize($address),
        $photo,
        $id
    ]);

    jsonResponse(true, 'Student updated successfully!');

} catch (PDOException $e) {
    jsonResponse(false, 'Failed to update student', [], 500);
}
