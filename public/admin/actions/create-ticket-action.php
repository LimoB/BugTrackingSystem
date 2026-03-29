<?php
/**
 * File: admin/actions/create-ticket-action.php
 * Purpose: AJAX handler for creating new tickets
 */

// 1. 🛡️ Initialization & Headers
ob_start(); // Buffer to catch any accidental whitespace or notices
session_start();
header('Content-Type: application/json');

// 2. 🔌 Database Connection
// Ensure path: ../ (actions) -> ../ (admin) -> ../ (public) -> config/config.php
require_once('../../../config/config.php'); 

// 3. 🛡️ Admin Security Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    ob_clean();
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Security Violation: Admin privileges required.']);
    exit();
}

// 4. 🔍 Data Extraction & Cleaning
// Note: We use the name attributes from your HTML form
$ticketTitle       = isset($_POST['ticketTitle']) ? trim($_POST['ticketTitle']) : '';
$ticketDescription = isset($_POST['ticketDescription']) ? trim($_POST['ticketDescription']) : '';
$ticketProject     = isset($_POST['ticketProject']) ? intval($_POST['ticketProject']) : 0;
$ticketPriority    = isset($_POST['ticketPriority']) ? trim($_POST['ticketPriority']) : 'medium';

// CRITICAL: Check your login script. If you saved the ID as 'id', change this to $_SESSION['id']
$createdBy = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : (isset($_SESSION['id']) ? intval($_SESSION['id']) : 0);

// 5. 🚦 Validation
if (empty($ticketTitle) || empty($ticketDescription) || $ticketProject === 0) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Data Integrity Error: Title, Description, and Project are required.']);
    exit();
}

if ($createdBy === 0) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Session Error: User ID not found. Please re-login.']);
    exit();
}

// 6. 🚀 Database Injection
// Mapping to your 7 columns: title, description, status, priority, project_id, created_by, created_at
$query = "INSERT INTO Tickets (title, description, status, priority, project_id, created_by, created_at) 
          VALUES (?, ?, 'open', ?, ?, ?, NOW())";

$stmt = mysqli_prepare($connection, $query);

if ($stmt) {
    // sssii = string (title), string (description), string (priority), integer (project_id), integer (created_by)
    mysqli_stmt_bind_param($stmt, "sssii", 
        $ticketTitle, 
        $ticketDescription, 
        $ticketPriority, 
        $ticketProject, 
        $createdBy
    );
    
    if (mysqli_stmt_execute($stmt)) {
        ob_clean(); // Discard any background warnings
        echo json_encode([
            'success' => true,
            'message' => 'New ticket deployed to backlog successfully!',
            'ticket_id' => mysqli_insert_id($connection)
        ]);
    } else {
        ob_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database Execution Error: ' . mysqli_stmt_error($stmt)]);
    }
    
    mysqli_stmt_close($stmt);
} else {
    ob_clean();
    http_response_code(500);
    // If this triggers, it usually means a column name is misspelled in the $query above
    echo json_encode(['success' => false, 'error' => 'System Failure: Unable to prepare query. ' . mysqli_error($connection)]);
}

// 7. Cleanup
mysqli_close($connection);
ob_end_flush();
exit();
?>