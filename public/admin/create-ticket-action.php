<?php
session_start();
header('Content-Type: application/json');
include('../../config/config.php');


// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Check if the user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo json_encode(['error' => 'Access denied!']);
    exit();
}

// Retrieve and sanitize POST data
$ticketTitle = isset($_POST['ticketTitle']) ? trim(mysqli_real_escape_string($connection, $_POST['ticketTitle'])) : '';
$ticketDescription = isset($_POST['ticketDescription']) ? trim(mysqli_real_escape_string($connection, $_POST['ticketDescription'])) : '';
$ticketStatus = isset($_POST['ticketStatus']) ? trim(mysqli_real_escape_string($connection, $_POST['ticketStatus'])) : '';
$ticketProject = isset($_POST['ticketProject']) ? trim(mysqli_real_escape_string($connection, $_POST['ticketProject'])) : '';

// Validate ticket status
$validStatuses = ['open', 'in-progress', 'resolved', 'closed', 'on-hold'];
if (!in_array($ticketStatus, $validStatuses)) {
    echo json_encode(['error' => 'Invalid status']);
    exit();
}

// Check if all necessary data is present
if (!$ticketTitle || !$ticketDescription || !$ticketStatus || !$ticketProject) {
    echo json_encode(['error' => 'Missing data fields']);
    exit();
}

// Use prepared statements for inserting the data securely
$query = "INSERT INTO Tickets (title, description, status, project_id, created_by) VALUES (?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($connection, $query);

if ($stmt) {
    // Bind parameters to the statement
    mysqli_stmt_bind_param($stmt, "sssis", $ticketTitle, $ticketDescription, $ticketStatus, $ticketProject, $_SESSION['user_id']);
    
    // Execute the statement
    if (mysqli_stmt_execute($stmt)) {
        // Success response
        echo json_encode(['success' => 'Ticket created successfully!']);
    } else {
        // Database error response
        echo json_encode(['error' => 'Database error: ' . mysqli_error($connection)]);
    }
    
    // Close the prepared statement
    mysqli_stmt_close($stmt);
} else {
    // Error preparing the query
    echo json_encode(['error' => 'Database error: Unable to prepare query']);
}

mysqli_close($connection); // Close the database connection
?>
