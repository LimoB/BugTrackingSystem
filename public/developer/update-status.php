<?php
session_start();
include('../../config/config.php');

// 1. Security Check
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'developer') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized Access Protocol.']);
    exit();
}

header('Content-Type: application/json');

// 2. Validate Inputs
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ticket_id']) && isset($_POST['status'])) {
    
    $ticket_id = intval($_POST['ticket_id']);
    $new_status = $_POST['status'];
    $user_id = (int)$_SESSION['user_id'];
    $user_name = $_SESSION['name'] ?? 'Developer';
    
    $valid_statuses = ['open', 'in-progress', 'resolved', 'closed', 'on-hold'];

    if (!in_array($new_status, $valid_statuses)) {
        echo json_encode(['success' => false, 'error' => 'Invalid Status Flag Received.']);
        exit();
    }

    // 3. Update Tickets Table
    $query = "UPDATE Tickets SET status = ? WHERE id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("si", $new_status, $ticket_id);

    if ($stmt->execute()) {
        // 4. 📝 Log the Activity (Matching your activity_log schema)
        // Description follows the pattern seen in your MariaDB logs
        $action_type = "TICKET_STATUS_UPDATE";
        $log_desc = "Developer $user_name (ID: $user_id) updated Ticket #$ticket_id to $new_status.";
        
        $log_query = "INSERT INTO activity_log (user_id, action_type, description, ticket_id, created_at) 
                      VALUES (?, ?, ?, ?, NOW())";
        
        $log_stmt = $connection->prepare($log_query);
        $log_stmt->bind_param("issi", $user_id, $action_type, $log_desc, $ticket_id);
        $log_stmt->execute();
        $log_stmt->close();

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database Sync Failed. Check Connection.']);
    }
    
    $stmt->close();
    exit();
}

echo json_encode(['success' => false, 'error' => 'Invalid Request Structure.']);