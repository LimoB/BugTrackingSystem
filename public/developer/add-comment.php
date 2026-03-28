<?php
session_start();
include('../../config/config.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the necessary POST data is available
if (isset($_POST['ticket_id'], $_POST['user_id'], $_POST['comment'])) {
    $ticket_id = intval($_POST['ticket_id']);
    $user_id = intval($_POST['user_id']);
    $comment = trim($_POST['comment']);

    // Validate the comment text
    if (empty($comment)) {
        echo "Comment cannot be empty.";
        exit();
    }

    // Check if the ticket exists in the Tickets table
    $ticket_check_query = "SELECT id FROM Tickets WHERE id = ?";
    $ticket_check_stmt = $connection->prepare($ticket_check_query);
    $ticket_check_stmt->bind_param("i", $ticket_id);
    $ticket_check_stmt->execute();
    $ticket_check_result = $ticket_check_stmt->get_result();

    if ($ticket_check_result->num_rows == 0) {
        echo "Error: The ticket you are trying to comment on does not exist.";
        exit();
    }

    // Prepare and execute the database query to insert the comment
    $comment_query = "INSERT INTO Comments (ticket_id, user_id, comment) VALUES (?, ?, ?)";  // Changed issue_id to ticket_id
    if ($stmt = $connection->prepare($comment_query)) {
        // Bind parameters and execute
        $stmt->bind_param("iis", $ticket_id, $user_id, $comment);
        
        if ($stmt->execute()) {
            echo "Comment added successfully!";
        } else {
            echo "Error: " . $stmt->error;  // Show specific error if the query fails
        }
    } else {
        echo "Error preparing the SQL statement.";  // In case the statement fails to prepare
    }
} else {
    echo "Required data not received.";
}
