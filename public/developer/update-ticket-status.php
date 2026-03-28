<?php
session_start();
include('../../config/config.php');

ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_id = intval($_POST['ticket_id']);
    
    $new_status = strtolower(trim($_POST['status']));
    $new_status = str_replace(' ', '_', $new_status); // Fix spaces to underscores

    $allowed = ['open', 'in-progress', 'resolved', 'closed', 'on_hold'];

    if (!in_array($new_status, $allowed, true)) {
        echo "Invalid status.";
        exit;
    }

    $stmt = $connection->prepare("UPDATE Tickets SET status=? WHERE id=?");
    if (!$stmt) {
        die("Database error: " . $connection->error);
    }

    $stmt->bind_param("si", $new_status, $ticket_id);

    if ($stmt->execute()) {
        echo "Status updated successfully to " . ucfirst(str_replace('_', ' ', $new_status)) . ".";
    } else {
        echo "Failed to update status: " . $stmt->error;
    }
} else {
    echo "Invalid request method.";
}
