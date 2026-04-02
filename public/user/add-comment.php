<?php
session_start();
include('../../config/config.php');

// Set header for JSON response
header('Content-Type: application/json');

// ✅ Security Check: Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Session expired. Please log in again.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $ticket_id = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;
    $comment = trim($_POST['comment'] ?? '');
    $user_id = (int)$_SESSION['user_id'];

    if ($ticket_id <= 0 || empty($comment)) {
        echo json_encode(['success' => false, 'message' => 'Invalid comment data provided.']);
        exit();
    }

    // ✅ SECURE PREPARED STATEMENT
    $stmt = $connection->prepare("INSERT INTO Comments (ticket_id, user_id, comment) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $ticket_id, $user_id, $comment);
    
    if ($stmt->execute()) {
        // Return success response as JSON
        echo json_encode([
            'success' => true, 
            'message' => 'Comment synced successfully!',
            'data' => [
                'user' => $_SESSION['name'],
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        // Return error response as JSON
        echo json_encode(['success' => false, 'message' => 'Database failure: ' . $stmt->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Direct access protocol denied.']);
}
?>