<?php
session_start();
include('../../config/config.php');

// 1. Security Check
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'developer') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

// 2. Validate Inputs
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ticket_id']) && isset($_POST['status'])) {
    
    $ticket_id = intval($_POST['ticket_id']);
    $new_status = $_POST['status'];
    $valid_statuses = ['open', 'in-progress', 'resolved', 'closed', 'on-hold'];

    if (!in_array($new_status, $valid_statuses)) {
        echo json_encode(['success' => false, 'error' => 'Invalid status value']);
        exit();
    }

    // 3. Update Database
    $query = "UPDATE Tickets SET status = ? WHERE id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("si", $new_status, $ticket_id);

    if ($stmt->execute()) {
        // Optional: Log the change in a history table here
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database update failed']);
    }
    
    $stmt->close();
    exit();
}

echo json_encode(['success' => false, 'error' => 'Invalid Request']);