<?php
session_start();
include('../../config/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_id = mysqli_real_escape_string($connection, $_POST['ticket_id']);
    $comment = mysqli_real_escape_string($connection, $_POST['comment']);
    $user_id = $_SESSION['user_id'];

    // Insert the comment into the database
    $query = "INSERT INTO Comments (ticket_id, user_id, comment) VALUES ('$ticket_id', '$user_id', '$comment')";
    
    if (mysqli_query($connection, $query)) {
        // Return success response as JSON
        echo json_encode(['success' => 'Comment added successfully!']);
    } else {
        // Return error response as JSON
        echo json_encode(['error' => 'Failed to add comment: ' . mysqli_error($connection)]);
    }
}
?>
