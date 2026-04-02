<?php
/**
 * File: admin/actions/add-user-action.php
 * Purpose: Atomic Onboarding of New Identity into the Matrix.
 */
session_start();
header('Content-Type: application/json'); // ✅ Ensure all outputs are JSON
require_once('../../../config/config.php');

// 1. 🛡️ Admin Security Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'error' => 'Security Violation: Kernel-level admin authority required.'
    ]);
    exit();
}

// 2. 🔍 Data Extraction & Sanitization
// Match these keys to your HTML form 'name' attributes
$name     = isset($_POST['userName']) ? trim($_POST['userName']) : '';
$email    = isset($_POST['userEmail']) ? trim($_POST['userEmail']) : '';
$password = isset($_POST['userPassword']) ? $_POST['userPassword'] : '';
$role     = isset($_POST['userRole']) ? strtolower(trim($_POST['userRole'])) : 'user';

// 3. 🚦 Validation Logic
if (empty($name) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'error' => 'Data Integrity Error: Name, Email, and Password are required.']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Invalid Format: Provide a valid communication address.']);
    exit();
}

// 4. 🛑 Duplicate Email Check (Prevention of Registry Conflict)
$check_query = "SELECT id FROM Users WHERE email = ? LIMIT 1";
$check_stmt = $connection->prepare($check_query);
$check_stmt->bind_param("s", $email);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Conflict: This email is already bound to another identity.']);
    $check_stmt->close();
    exit();
}
$check_stmt->close();

// 5. 🔐 Security Transformation (Hashing)
// Use BCRYPT to ensure the password is never stored in plain text
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// 6. 🚀 Atomic User Insertion
$user_query = "INSERT INTO Users (name, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())";
$stmt = $connection->prepare($user_query);

if ($stmt) {
    $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);
    
    if ($stmt->execute()) {
        // Log the successful onboarding if you have an activity log table
        echo json_encode([
            'success' => true,
            'message' => "Identity for " . htmlspecialchars($name) . " has been successfully established.",
            'user_id' => $stmt->insert_id
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database Sync Failed: Registry write error.']);
    }
    $stmt->close();
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'System Failure: Unable to prepare onboarding statement.']);
}

$connection->close();
?>