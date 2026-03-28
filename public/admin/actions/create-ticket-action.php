<?php
session_start();
header('Content-Type: application/json');
include('../../../config/config.php');

// 1. 🛡️ Admin Security Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Security Violation: Admin privileges required.']);
    exit();
}

// 2. 🔍 Data Extraction & Cleaning
// Note: We don't need mysqli_real_escape_string if we use Prepared Statements (it's redundant)
$ticketTitle       = isset($_POST['ticketTitle']) ? trim($_POST['ticketTitle']) : '';
$ticketDescription = isset($_POST['ticketDescription']) ? trim($_POST['ticketDescription']) : '';
$ticketProject     = isset($_POST['ticketProject']) ? intval($_POST['ticketProject']) : 0;
$ticketPriority    = isset($_POST['ticketPriority']) ? trim($_POST['ticketPriority']) : 'medium';
$createdBy         = intval($_SESSION['user_id']);

// Default status for new tickets
$ticketStatus      = 'open'; 

// 3. 🚦 Validation
if (empty($ticketTitle) || empty($ticketDescription) || $ticketProject === 0) {
    echo json_encode(['error' => 'Data Integrity Error: Title, Description, and Project are required.']);
    exit();
}

// 4. 🚀 Database Injection (Using Atomic Prepared Statement)
$query = "INSERT INTO Tickets (title, description, status, priority, project_id, created_by, created_at) 
          VALUES (?, ?, ?, ?, ?, ?, NOW())";

$stmt = mysqli_prepare($connection, $query);

if ($stmt) {
    // Correct types: s = string, i = integer
    // Pattern: title(s), desc(s), status(s), priority(s), project(i), creator(i)
    mysqli_stmt_bind_param($stmt, "ssssii", 
        $ticketTitle, 
        $ticketDescription, 
        $ticketStatus, 
        $ticketPriority, 
        $ticketProject, 
        $createdBy
    );
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode([
            'success' => true,
            'message' => 'New ticket deployed to backlog successfully!',
            'ticket_id' => mysqli_insert_id($connection)
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Database Execution Error: ' . mysqli_stmt_error($stmt)]);
    }
    
    mysqli_stmt_close($stmt);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'System Failure: Unable to prepare insertion query.']);
}

mysqli_close($connection);
?>