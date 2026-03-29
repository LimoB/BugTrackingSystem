<?php
session_start();
include('../../config/config.php');

// Always return JSON for this endpoint
header('Content-Type: application/json');

// ✅ 1. Quick Security & Role Check
$user_id = $_SESSION['user_id'] ?? null;
$user_role = strtolower($_SESSION['role'] ?? '');

if (!$user_id || !in_array($user_role, ['developer', 'admin'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_id = intval($_POST['ticket_id'] ?? 0);
    
    // ✅ 2. Sanitize and Format Status
    $new_status = strtolower(trim($_POST['status'] ?? ''));
    // Ensure we match the internal naming convention (hyphenated)
    $new_status = str_replace(' ', '-', $new_status); 

    $allowed = ['open', 'in-progress', 'resolved', 'closed', 'on-hold'];

    if ($ticket_id <= 0 || !in_array($new_status, $allowed, true)) {
        echo json_encode(['success' => false, 'error' => 'Invalid ticket ID or status value.']);
        exit;
    }

    // ✅ 3. Permission Check (Optional: Restrict to assigned user unless Admin)
    /*
    $check_stmt = $connection->prepare("SELECT id FROM Tickets WHERE id = ? AND (assigned_to = ? OR ? = 'admin')");
    $check_stmt->bind_param("iis", $ticket_id, $user_id, $user_role);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Permission denied: This ticket is not assigned to you.']);
        exit;
    }
    */

    // ✅ 4. Execute the Update
    $stmt = $connection->prepare("UPDATE Tickets SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Database preparation error.']);
        exit;
    }

    $stmt->bind_param("si", $new_status, $ticket_id);

    if ($stmt->execute()) {
        // Optional: You could insert a system comment here to log the change
        // $log_comment = "System: Status changed to " . ucwords(str_replace('-', ' ', $new_status));
        // $log_stmt = $connection->prepare("INSERT INTO Comments (ticket_id, user_id, comment) VALUES (?, ?, ?)");
        // $log_stmt->bind_param("iis", $ticket_id, $user_id, $log_comment);
        // $log_stmt->execute();

        echo json_encode([
            'success' => true, 
            'message' => 'Status updated to ' . ucwords(str_replace('-', ' ', $new_status)),
            'new_status' => $new_status
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update database.']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}