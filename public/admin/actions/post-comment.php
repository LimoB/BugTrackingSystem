<?php
session_start();
include('../../../config/config.php');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticketId = intval($_POST['ticket_id']);
    $userId = intval($_SESSION['user_id']);
    $comment = trim($_POST['comment']);

    if (empty($comment)) {
        echo json_encode(['error' => 'Comment cannot be empty.']);
        exit;
    }

    $query = "INSERT INTO Comments (ticket_id, user_id, comment) VALUES (?, ?, ?)";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("iis", $ticketId, $userId, $comment);

    if ($stmt->execute()) {
        echo json_encode(['success' => 'Comment posted successfully.']);
    } else {
        echo json_encode(['error' => 'Failed to post comment.']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method.']);
}
?>
