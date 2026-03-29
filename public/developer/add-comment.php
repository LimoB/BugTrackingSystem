<?php
session_start();
header('Content-Type: application/json'); // Set header for JSON response
include('../../config/config.php');

// Enable error reporting only for development; disable in production
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

$response = ['success' => false, 'message' => ''];

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = "Unauthorized access.";
    echo json_encode($response);
    exit();
}

// Check for required POST data
if (isset($_POST['ticket_id'], $_POST['comment'])) {
    $ticket_id = intval($_POST['ticket_id']);
    $user_id   = intval($_SESSION['user_id']); // Use Session ID for security
    $comment   = trim($_POST['comment']);

    // 1. Validation
    if (empty($comment)) {
        $response['message'] = "Comment content cannot be empty.";
        echo json_encode($response);
        exit();
    }

    // 2. Verify Ticket exists
    $check_stmt = $connection->prepare("SELECT id FROM Tickets WHERE id = ?");
    $check_stmt->bind_param("i", $ticket_id);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows === 0) {
        $response['message'] = "Target ticket does not exist.";
        echo json_encode($response);
        exit();
    }

    // 3. Insert Comment
    $query = "INSERT INTO Comments (ticket_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())";
    if ($stmt = $connection->prepare($query)) {
        $stmt->bind_param("iis", $ticket_id, $user_id, $comment);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "Comment posted successfully.";
        } else {
            $response['message'] = "Database error: " . $stmt->error;
        }
    } else {
        $response['message'] = "SQL preparation failed.";
    }
} else {
    $response['message'] = "Required data fields are missing.";
}

// Send the final JSON response
echo json_encode($response);