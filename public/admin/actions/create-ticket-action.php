<?php
/**
 * File: admin/actions/create-ticket-action.php
 * Purpose: AJAX handler for creating new tickets with Category support
 */

ob_start(); 
session_start();
header('Content-Type: application/json');

// 1. 🔌 Database Connection
require_once('../../../config/config.php'); 

// 2. 🛡️ Admin Security Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    ob_clean();
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Security Violation: Admin privileges required.']);
    exit();
}

// 3. 🔍 Data Extraction (Matching the new Form names)
$ticketTitle       = isset($_POST['ticketTitle']) ? trim($_POST['ticketTitle']) : '';
$ticketDescription = isset($_POST['ticketDescription']) ? trim($_POST['ticketDescription']) : '';
$ticketProject     = isset($_POST['ticketProject']) ? intval($_POST['ticketProject']) : 0;
$ticketCategory    = isset($_POST['ticketCategory']) ? intval($_POST['ticketCategory']) : 0;
$ticketPriority    = isset($_POST['ticketPriority']) ? trim($_POST['ticketPriority']) : 'medium';

// Identify the creator (Checking common session keys)
$createdBy = $_SESSION['user_id'] ?? $_SESSION['id'] ?? 0;

// 4. 🚦 Validation
if (empty($ticketTitle) || $ticketProject === 0 || $ticketCategory === 0) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Data Integrity Error: Title, Project, and Category are required.']);
    exit();
}

if ($createdBy === 0) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Session Expired: Please re-authenticate.']);
    exit();
}

// 5. 🚀 Database Injection
// Including category_id and explicitly setting deleted_at to NULL (though DB default should handle it)
$query = "INSERT INTO Tickets (title, description, status, priority, project_id, category_id, created_by, created_at, deleted_at) 
          VALUES (?, ?, 'open', ?, ?, ?, ?, NOW(), NULL)";

$stmt = mysqli_prepare($connection, $query);

if ($stmt) {
    // sssiii = string(3), integer(3)
    mysqli_stmt_bind_param($stmt, "sssiii", 
        $ticketTitle, 
        $ticketDescription, 
        $ticketPriority, 
        $ticketProject, 
        $ticketCategory,
        $createdBy
    );
    
    if (mysqli_stmt_execute($stmt)) {
        $new_id = mysqli_insert_id($connection);
        
        // 📝 Optional: Log to Activity Table if you have one
        // $log_query = "INSERT INTO Activity_Logs (user_id, action, target_id) VALUES ($createdBy, 'created_ticket', $new_id)";
        // mysqli_query($connection, $log_query);

        ob_clean();
        echo json_encode([
            'success' => true,
            'message' => 'Record #'.str_pad($new_id, 4, '0', STR_PAD_LEFT).' successfully initialized in the backlog.',
            'ticket_id' => $new_id
        ]);
    } else {
        ob_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'DB Error: ' . mysqli_stmt_error($stmt)]);
    }
    mysqli_stmt_close($stmt);
} else {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Prepare Failed: Check if "category_id" exists in Tickets table.']);
}

mysqli_close($connection);
ob_end_flush();
exit();