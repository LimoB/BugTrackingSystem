<?php
session_start();
header('Content-Type: application/json'); // ✅ Ensure AJAX receives an object
include('../../../config/config.php');

// 1. 🛡️ Strict Admin Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'System Violation: Unauthorized profile modification attempt.']);
    exit();
}

// 2. 🔍 Input Processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['userId'] ?? 0);
    $name    = trim($_POST['userName'] ?? '');
    $email   = trim($_POST['userEmail'] ?? '');
    $role    = strtolower(trim($_POST['userRole'] ?? 'user'));

    // 🛑 Safety Check: Prevent Admin Self-Demotion
    if ($user_id === intval($_SESSION['user_id']) && $role !== 'admin') {
        echo json_encode(['error' => 'Security Lock: You cannot revoke your own Administrator status.']);
        exit();
    }

    // 3. 🚦 Basic Validation
    if (empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['error' => 'Data Integrity Error: Name and valid Email are required.']);
        exit();
    }

    // 4. 🚀 Atomic Update
    $query = "UPDATE Users SET name = ?, email = ?, role = ? WHERE id = ?";
    $stmt  = $connection->prepare($query);

    if ($stmt) {
        $stmt->bind_param('sssi', $name, $email, $role, $user_id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode([
                    'success' => true, 
                    'message' => "Profile for $name has been synchronized."
                ]);
            } else {
                echo json_encode([
                    'success' => true, 
                    'message' => "No changes detected. Profile is already up to date."
                ]);
            }
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Database Sync Failed: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'System Failure: Failed to prepare user update.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method.']);
}

$connection->close();