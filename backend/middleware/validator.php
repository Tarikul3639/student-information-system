<?php
/**
 * Input Validation & Sanitization
 * Server-side validation with XSS protection
 */

/**
 * Sanitize string input
 */
function sanitize(string $input): string
{
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return $input;
}

/**
 * Validate email format
 */
function validateEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (Bangladesh format supported)
 */
function validatePhone(string $phone): bool
{
    // Allows formats: 01XXXXXXXXX, +8801XXXXXXXXX
    return preg_match('/^(\+?88)?01[3-9]\d{8}$/', $phone) === 1;
}

/**
 * Validate password strength
 * Minimum 8 characters, at least one uppercase, one lowercase, one number
 */
function validatePassword(string $password): array
{
    $errors = [];

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter';
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least number';
    }

    return $errors;
}

/**
 * Validate date format (YYYY-MM-DD)
 */
function validateDate(string $date): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

/**
 * Validate gender value
 */
function validateGender(string $gender): bool
{
    return in_array($gender, ['Male', 'Female', 'Other']);
}

/**
 * Validate file upload for profile photo
 */
function validateFileUpload(array $file): array
{
    $errors = [];
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
    $max_size = 2 * 1024 * 1024; // 2 MB

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'File upload failed';
        return $errors;
    }

    // Check MIME type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $allowed_types)) {
        $errors[] = 'Only JPG, JPEG, and PNG files are allowed';
    }

    // Check file size
    if ($file['size'] > $max_size) {
        $errors[] = 'File size must be less than 2 MB';
    }

    // Verify it's a real image
    if (!getimagesize($file['tmp_name'])) {
        $errors[] = 'Invalid image file';
    }

    return $errors;
}

/**
 * Generate safe filename for upload
 */
function generateSafeFilename(string $original_name): string
{
    $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    return uniqid('img_', true) . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
}

/**
 * Send JSON response helper
 */
function jsonResponse(bool $status, string $message, array $data = [], int $http_code = 200): void
{
    http_response_code($http_code);
    echo json_encode(array_merge([
        'status'  => $status,
        'message' => $message,
    ], $data));
    exit;
}
