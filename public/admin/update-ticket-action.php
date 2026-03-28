<?php
include('../../config/config.php');
include('../../includes/auth.php');

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Ensure the request is a POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'User is not authenticated']);
        exit();
    }

    // Get and sanitize inputs
    $ticketId = isset($_POST['ticketId']) ? (int)$_POST['ticketId'] : 0;
    $ticketStatus = isset($_POST['ticketStatus']) ? trim($_POST['ticketStatus']) : '';
    $ticketProject = isset($_POST['ticketProject']) ? (int)$_POST['ticketProject'] : 0;

    // Log received inputs for debugging
    error_log("Received ticket ID: $ticketId");
    error_log("Received ticket status (raw): '$ticketStatus'");

    // Validate required fields
    if (empty($ticketId) || empty($ticketStatus) || empty($ticketProject)) {
        echo json_encode(['error' => 'Ticket ID, Status, and Project are required.']);
        exit();
    }

    // Normalize status: Convert status to lowercase, remove any extra spaces
    $ticketStatus = strtolower(trim($ticketStatus));
    
    // Log normalized status for debugging
    error_log("Normalized ticket status: '$ticketStatus'");

    // Allowed status values (should match ENUM exactly)
    $validStatuses = ['open', 'in-progress', 'resolved', 'closed', 'on_hold'];

    // Check if the normalized status is valid
    if (!in_array($ticketStatus, $validStatuses)) {
        echo json_encode(['error' => 'Invalid ticket status.']);
        exit();
    }

    // Prepare SQL query (use prepared statements for safety)
    $query = "UPDATE Tickets SET status = ?, project_id = ? WHERE id = ?";
    $stmt = mysqli_prepare($connection, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'sii', $ticketStatus, $ticketProject, $ticketId);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode([
                'success' => 'Ticket updated successfully.',
                'status' => ucfirst($ticketStatus),
                'project' => $ticketProject
            ]);
        } else {
            echo json_encode(['error' => 'Error updating ticket.']);
        }

        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(['error' => 'Failed to prepare update statement.']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method.']);
}
