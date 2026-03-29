<?php
/**
 * File: actions/assign-tickets-process.php
 * Purpose: Handles ticket assignment and logs the activity.
 */
ob_start(); 
session_start();
header('Content-Type: application/json');

require_once('../../../config/config.php');

// 1. 🛡️ Security Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    ob_clean();
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized Access']);
    exit();
}

// 2. 🔍 Data Extraction
$ticketId   = isset($_POST['ticketId']) ? (int)$_POST['ticketId'] : 0;
$assignedTo = isset($_POST['assignedTo']) ? (int)$_POST['assignedTo'] : 0;
$adminId    = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

// 3. 🚦 Validation
if ($ticketId <= 0 || $assignedTo <= 0) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Invalid Ticket or Developer selection.']);
    exit();
}

if ($adminId === 0) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Session Error: Admin ID not found.']);
    exit();
}

// 4. 🚀 Execute Ticket Update
// Update assigned_to, assigned_by, and set status to 'in-progress'
$query = "UPDATE Tickets 
          SET assigned_to = ?, 
              assigned_by = ?, 
              status = 'in-progress' 
          WHERE id = ?";

$stmt = mysqli_prepare($connection, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'iii', $assignedTo, $adminId, $ticketId);
    
    if (mysqli_stmt_execute($stmt)) {
        
        // --- 📝 START ACTIVITY LOGGING ---
        // Fetch names for a readable log entry
        $dev_res = mysqli_query($connection, "SELECT name FROM Users WHERE id = $assignedTo LIMIT 1");
        $dev_row = mysqli_fetch_assoc($dev_res);
        $dev_name = $dev_row['name'] ?? 'Unknown Dev';

        $log_desc = "Ticket #$ticketId was assigned to $dev_name by Admin (ID: $adminId)";
        $log_query = "INSERT INTO activity_log (user_id, action_type, description) VALUES (?, 'TICKET_ASSIGNED', ?)";
        
        $log_stmt = mysqli_prepare($connection, $log_query);
        if ($log_stmt) {
            mysqli_stmt_bind_param($log_stmt, 'is', $adminId, $log_desc);
            mysqli_stmt_execute($log_stmt);
            mysqli_stmt_close($log_stmt);
        }
        // --- 📝 END ACTIVITY LOGGING ---

        ob_clean();
        echo json_encode([
            'success' => true,
            'message' => "Ticket #$ticketId successfully deployed to $dev_name"
        ]);
    } else {
        ob_clean();
        echo json_encode(['success' => false, 'error' => 'Database update failed: ' . mysqli_error($connection)]);
    }
    mysqli_stmt_close($stmt);
} else {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'SQL Preparation failed: ' . mysqli_error($connection)]);
}

mysqli_close($connection);
ob_end_flush();
exit();
?>