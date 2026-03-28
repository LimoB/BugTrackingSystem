<?php
session_start();
header('Content-Type: application/json');
include('../../../config/config.php');

// 1. 🛡️ Admin Security Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Security Violation: Admin clearance required to purge records.']);
    exit();
}

// 2. 🔍 Input Validation
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Protocol Error: Invalid request method.']);
    exit();
}

$ticketId = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;

if ($ticketId <= 0) {
    echo json_encode(['error' => 'Identifier Error: A valid Ticket ID is required for this operation.']);
    exit();
}

// 3. 🧹 Cleanup Dependencies (Comments/Logs)
// We delete associated comments first to maintain database integrity
$cleanupQuery = "DELETE FROM Ticket_Comments WHERE ticket_id = ?";
$cleanupStmt = $connection->prepare($cleanupQuery);
if ($cleanupStmt) {
    $cleanupStmt->bind_param("i", $ticketId);
    $cleanupStmt->execute();
    $cleanupStmt->close();
}

// 4. 🚀 Atomic Ticket Purge
$query = "DELETE FROM Tickets WHERE id = ?";
$stmt = $connection->prepare($query);

if ($stmt) {
    $stmt->bind_param("i", $ticketId);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => "Ticket #$ticketId and all associated data have been purged."
            ]);
        } else {
            echo json_encode(['error' => 'Record not found: The ticket may have already been removed.']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Execution Failure: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    http_response_code(500);
    echo json_encode(['error' => 'System Failure: Unable to prepare destruction sequence.']);
}

$connection->close();
?>