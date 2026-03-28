<?php
session_start();
header('Content-Type: application/json');
include('../../../config/config.php');

// 1. 🛡️ Admin Security Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Security Violation: Administrative clearance required.']);
    exit();
}

// 2. 🔍 Validation & Self-Preservation
$userId = isset($_POST['userId']) ? intval($_POST['userId']) : 0;

if ($userId === 0) {
    echo json_encode(['error' => 'Invalid Request: Target identifier missing.']);
    exit();
}

if ($userId === intval($_SESSION['user_id'])) {
    echo json_encode(['error' => 'System Safeguard: You cannot delete your own active session.']);
    exit();
}

// 3. 🚦 Database Integrity: Unlink Active Tickets
// We set 'assigned_to' to NULL so the work history remains, but the developer is removed.
$unlink_sql = "UPDATE Tickets SET assigned_to = NULL WHERE assigned_to = ?";
$unlink_stmt = mysqli_prepare($connection, $unlink_sql);
mysqli_stmt_bind_param($unlink_stmt, 'i', $userId);
mysqli_stmt_execute($unlink_stmt);
mysqli_stmt_close($unlink_stmt);

// 4. 🚀 Atomic User Deletion
$query = "DELETE FROM Users WHERE id = ?";
$stmt = mysqli_prepare($connection, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    
    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            echo json_encode([
                'success' => true,
                'message' => "User #$userId has been purged. Associated tickets are now unassigned."
            ]);
        } else {
            echo json_encode(['error' => 'Registry Error: User not found or already deleted.']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Database Failure: ' . mysqli_error($connection)]);
    }
    mysqli_stmt_close($stmt);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'System Failure: Failed to prepare purge sequence.']);
}

mysqli_close($connection);
?>