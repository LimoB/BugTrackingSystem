<?php
session_start();
include('../../config/config.php');
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'user') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_id = (int)$_POST['ticket_id'];
    $user_id = (int)$_SESSION['user_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    // SECURE CHECK: Only update if the status is still 'open'
    $check = $connection->prepare("SELECT status FROM Tickets WHERE id = ? AND created_by = ?");
    $check->bind_param("ii", $ticket_id, $user_id);
    $check->execute();
    $current_status = $check->get_result()->fetch_assoc()['status'] ?? '';

    if ($current_status !== 'open') {
        echo json_encode(['success' => false, 'message' => 'Cannot edit a ticket that is already in-progress or closed.']);
        exit();
    }

    $stmt = $connection->prepare("UPDATE Tickets SET title = ?, description = ? WHERE id = ? AND created_by = ?");
    $stmt->bind_param("ssii", $title, $description, $ticket_id, $user_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Ticket details updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
}