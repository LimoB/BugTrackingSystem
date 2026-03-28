<?php
session_start();
header('Content-Type: application/json'); // ✅ Standardize for AJAX
include('../../../config/config.php');

// 1. 🛡️ Admin Security Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Security Violation: Admin authority required for user onboarding.']);
    exit();
}

// 2. 🔍 Data Extraction & Sanitization
$name     = isset($_POST['userName']) ? trim($_POST['userName']) : '';
$email    = isset($_POST['userEmail']) ? trim($_POST['userEmail']) : '';
$password = isset($_POST['userPassword']) ? $_POST['userPassword'] : '';
$role     = isset($_POST['userRole']) ? trim($_POST['userRole']) : 'user';

// 3. 🚦 Validation Logic
if (empty($name) || empty($email) || empty($password)) {
    echo json_encode(['error' => 'Data Integrity Error: Name, Email, and Password are required.']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['error' => 'Invalid Format: Please provide a valid email address.']);
    exit();
}

// 4. 🛑 Duplicate Email Check
$email_check_query = "SELECT id FROM Users WHERE email = ? LIMIT 1";
$email_check_stmt = $connection->prepare($email_check_query);
$email_check_stmt->bind_param("s", $email);
$email_check_stmt->execute();
if ($email_check_stmt->get_result()->num_rows > 0) {
    echo json_encode(['error' => 'Conflict: This email is already registered in the directory.']);
    exit();
}

// 5. 🔐 Password Hashing
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// 6. 🚀 Atomic User Insertion
$user_query = "INSERT INTO Users (name, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())";
$stmt = $connection->prepare($user_query);

if ($stmt) {
    $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => "Identity for $name has been successfully established.",
            'user_id' => $connection->insert_id
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Database Sync Failed: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    http_response_code(500);
    echo json_encode(['error' => 'System Failure: Unable to prepare onboarding statement.']);
}

$connection->close();
?>