<?php
session_start();
header('Content-Type: application/json');
include('../../../config/config.php');

// 1. Strict Admin Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'System Violation: Unauthorized profile modification attempt.']);
    exit();
}

// 2. Input Processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mapping keys to match the 'name' attributes in your update form
    $user_id = intval($_POST['target_user_id'] ?? 0);
    $name    = trim($_POST['updateName'] ?? '');
    $email   = trim($_POST['updateEmail'] ?? '');
    $role    = strtolower(trim($_POST['updateRole'] ?? 'user'));

    // Safety Check: Prevent Admin Self-Demotion
    if ($user_id === intval($_SESSION['user_id'] ?? 0) && $role !== 'admin') {
        echo json_encode(['success' => false, 'error' => 'Security Lock: You cannot revoke your own Administrator status.']);
        exit();
    }

    // 3. Basic Validation
    if (empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => 'Data Integrity Error: Name and valid Email are required.']);
        exit();
    }

    // 4. Atomic Update
    // Using $connection (the variable from your config)
    $query = "UPDATE Users SET name = ?, email = ?, role = ? WHERE id = ?";
    $stmt  = $connection->prepare($query);

    if ($stmt) {
        $stmt->bind_param('sssi', $name, $email, $role, $user_id);
        
        if ($stmt->execute()) {
            // Note: affected_rows is 0 if the admin hits save without changing any text
            echo json_encode([
                'success' => true, 
                'message' => "Profile for " . htmlspecialchars($name) . " has been synchronized."
            ]);
        } else {
            // Check for duplicate emails
            if ($connection->errno === 1062) {
                echo json_encode(['success' => false, 'error' => 'Conflict: This email is already registered to another user.']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Database Sync Failed.']);
            }
        }
        $stmt->close();
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'System Failure: Failed to prepare user update.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}

$connection->close();