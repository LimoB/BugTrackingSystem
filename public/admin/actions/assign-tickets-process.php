<?php
session_start();
header('Content-Type: application/json');
include('../../../config/config.php');

// 1. 🛡️ Admin Security Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Security Violation: Unauthorized assignment attempt.']);
    exit();
}

// 2. 🔍 Input Extraction
// Supporting both your old naming convention and the new AJAX 'assignedTo' key
$ticket_id    = intval($_POST['ticketId'] ?? $_POST['assign_ticket'] ?? 0);
$developer_id = intval($_POST['assignedTo'] ?? $_POST['developer_' . $ticket_id] ?? 0);

if ($ticket_id === 0 || $developer_id === 0) {
    echo json_encode(['error' => 'Data Missing: Please select a valid Developer and Ticket.']);
    exit();
}

// 3. 🚀 Atomic Assignment Update
$update_sql = "UPDATE Tickets SET assigned_to = ?, updated_at = NOW() WHERE id = ?";
$stmt = mysqli_prepare($connection, $update_sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'ii', $developer_id, $ticket_id);
    
    if (mysqli_stmt_execute($stmt)) {
        // Fetch Developer Name for a personalized success message
        $name_query = "SELECT name FROM Users WHERE id = ?";
        $name_stmt = mysqli_prepare($connection, $name_query);
        mysqli_stmt_bind_param($name_stmt, 'i', $developer_id);
        mysqli_stmt_execute($name_stmt);
        mysqli_stmt_bind_result($name_stmt, $developer_name);
        mysqli_stmt_fetch($name_stmt);
        mysqli_stmt_close($name_stmt);

        echo json_encode([
            'success' => true,
            'message' => "Ticket #$ticket_id successfully routed to $developer_name."
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Database Sync Failure: ' . mysqli_stmt_error($stmt)]);
    }
    mysqli_stmt_close($stmt);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'System Failure: Failed to prepare assignment query.']);
}

mysqli_close($connection);