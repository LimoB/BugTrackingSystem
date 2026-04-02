<?php
/**
 * File: admin/actions/assign-tickets-process.php
 * Purpose: Handles ticket assignment with strict validation and activity auditing.
 */
ob_start(); 
session_start();
header('Content-Type: application/json');

require_once('../../../config/config.php');

// 1. 🛡️ Security Guard: Strict Admin Check
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    ob_clean();
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Security Violation: Administrative clearance required.']);
    exit();
}

// 2. 🔍 Data Extraction & Sanitization
$ticketId   = isset($_POST['ticketId']) ? (int)$_POST['ticketId'] : 0;
$assignedTo = isset($_POST['assignedTo']) ? (int)$_POST['assignedTo'] : 0;
$adminId    = $_SESSION['user_id'] ?? $_SESSION['id'] ?? 0;

// 3. 🚦 Pre-Flight Validation
if ($ticketId <= 0 || $assignedTo <= 0) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Input Error: A valid Ticket and Developer must be selected.']);
    exit();
}

if ($adminId === 0) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Authentication Error: Admin session node not found.']);
    exit();
}

// 4. 🚀 Execute Assignment Transaction
// We only change status to 'in-progress' if it is currently 'open'
$query = "UPDATE Tickets 
          SET assigned_to = ?, 
              assigned_by = ?, 
              status = CASE WHEN status = 'open' THEN 'in-progress' ELSE status END 
          WHERE id = ? AND deleted_at IS NULL";

$stmt = mysqli_prepare($connection, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'iii', $assignedTo, $adminId, $ticketId);
    
    if (mysqli_stmt_execute($stmt)) {
        
        // --- 📝 ACTIVITY LOGGING (Clean Fetch) ---
        $dev_res = mysqli_query($connection, "SELECT name FROM Users WHERE id = $assignedTo LIMIT 1");
        $dev_row = mysqli_fetch_assoc($dev_res);
        $dev_name = $dev_row['name'] ?? 'Unknown Developer';

        $log_desc = "Ticket #$ticketId deployed to $dev_name by Admin (ID: $adminId)";
        $log_query = "INSERT INTO activity_log (user_id, action_type, description) VALUES (?, 'TICKET_ASSIGNED', ?)";
        
        if ($log_stmt = mysqli_prepare($connection, $log_query)) {
            mysqli_stmt_bind_param($log_stmt, 'is', $adminId, $log_desc);
            mysqli_stmt_execute($log_stmt);
            mysqli_stmt_close($log_stmt);
        }

        ob_clean();
        echo json_encode([
            'success' => true,
            'message' => "Intel successfully deployed. Ticket #$ticketId is now with $dev_name."
        ]);
    } else {
        ob_clean();
        echo json_encode(['success' => false, 'error' => 'Execution Failure: ' . mysqli_error($connection)]);
    }
    mysqli_stmt_close($stmt);
} else {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Engine Failure: Unable to prepare assignment query.']);
}

// 5. Cleanup
mysqli_close($connection);
ob_end_flush();
exit();