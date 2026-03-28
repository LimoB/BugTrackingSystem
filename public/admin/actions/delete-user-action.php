<?php
session_start();
header('Content-Type: application/json');
include('../../../config/config.php');

// 1. 🛡️ Admin Security Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Security Violation: Admin credentials required to modify user directory.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

    // Prevent Admins from deleting themselves!
    if ($userId === intval($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Self-Destruct Blocked: You cannot delete your own administrative account.']);
        exit();
    }

    if ($userId <= 0) {
        echo json_encode(['error' => 'Invalid Request: No target user ID provided.']);
        exit();
    }

    // 2. 🚦 Database Integrity: Unlink Tickets
    // Instead of deleting tickets, we set 'assigned_to' to NULL so the work isn't lost.
    $unlinkQuery = "UPDATE Tickets SET assigned_to = NULL WHERE assigned_to = ?";
    $unlinkStmt = $connection->prepare($unlinkQuery);
    $unlinkStmt->bind_param('i', $userId);
    $unlinkStmt->execute();
    $unlinkStmt->close();

    // 3. 🚀 Atomic User Purge
    $query = "DELETE FROM Users WHERE id = ?";
    $stmt = $connection->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param('i', $userId);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'User identity successfully purged. Associated tickets have been set to unassigned.'
                ]);
            } else {
                echo json_encode(['error' => 'Target not found: User may have already been removed.']);
            }
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Execution Failure: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'System Failure: Unable to prepare user deletion.']);
    }
} else {
    echo json_encode(['error' => 'Invalid Request Method.']);
}

$connection->close();
?>