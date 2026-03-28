<?php
session_start();
include('../../config/config.php');
include('../../includes/auth.php');

// Check if the user is an admin
if ($_SESSION['role'] != 'admin') {
    echo json_encode(['error' => 'Only admins can delete tickets.']);
    exit();
}

// Ensure the request is a POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['ticket_id']) || empty($_POST['ticket_id'])) {
        echo json_encode(['error' => 'Ticket ID is required.']);
        exit();
    }

    $ticketId = (int)$_POST['ticket_id'];

    // Delete the ticket
    $query = "DELETE FROM Tickets WHERE id = ?";
    $stmt = $connection->prepare($query);

    if ($stmt) {
        $stmt->bind_param("i", $ticketId);
        if ($stmt->execute()) {
            echo json_encode(['success' => 'Ticket deleted successfully.']);
        } else {
            echo json_encode(['error' => 'Failed to delete the ticket.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['error' => 'Failed to prepare the delete statement.']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method.']);
}
