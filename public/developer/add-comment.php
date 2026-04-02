<?php
session_start();
header('Content-Type: application/json');
include('../../config/config.php');

/**
 * File: developer/add-comment.php
 * Purpose: Securely process internal discussion logs for tickets.
 */

$response = ['success' => false, 'message' => ''];

// 🛡️ 1. Security Check: Session validation
if (!isset($_SESSION['user_id'])) {
    $response['message'] = "UNAUTHORIZED_ACCESS: Clearance Level 0";
    echo json_encode($response);
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$user_name = $_SESSION['name'] ?? 'System Node';

// 🔍 2. Check for required POST data
if (isset($_POST['ticket_id'], $_POST['comment'])) {
    $ticket_id = intval($_POST['ticket_id']);
    $comment   = trim($_POST['comment']);

    // Validation: Empty content check
    if (empty($comment)) {
        $response['message'] = "Payload Error: Comment string is null or empty.";
        echo json_encode($response);
        exit();
    }

    // Verify Ticket existence before attaching data
    $check_stmt = $connection->prepare("SELECT title FROM Tickets WHERE id = ?");
    $check_stmt->bind_param("i", $ticket_id);
    $check_stmt->execute();
    $ticket_res = $check_stmt->get_result();
    $ticket_data = $ticket_res->fetch_assoc();

    if (!$ticket_data) {
        $response['message'] = "Target reference ID #$ticket_id does not exist in the ledger.";
        echo json_encode($response);
        exit();
    }

    // 💾 3. Insert Comment into the Discussion Thread
    $query = "INSERT INTO Comments (ticket_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())";
    if ($stmt = $connection->prepare($query)) {
        $stmt->bind_param("iis", $ticket_id, $user_id, $comment);
        
        if ($stmt->execute()) {
            
            // 📝 4. Sync with Activity Log (For Dashboard Terminal)
            $action_type = "COMMENT_PUSH";
            $log_desc = "[$user_name] logged a technical update on Ticket #$ticket_id";
            
            $log_stmt = $connection->prepare("INSERT INTO activity_log (user_id, action_type, description, ticket_id, created_at) VALUES (?, ?, ?, ?, NOW())");
            $log_stmt->bind_param("issi", $user_id, $action_type, $log_desc, $ticket_id);
            $log_stmt->execute();

            $response['success'] = true;
            $response['message'] = "Transmission Successful: Log updated.";
        } else {
            $response['message'] = "DATABASE_WRITE_FAILURE: " . $stmt->error;
        }
    } else {
        $response['message'] = "SQL_PREP_FAILURE: Memory allocation or syntax error.";
    }
} else {
    $response['message'] = "INCOMPLETE_PACKET: Required fields missing.";
}

// 📤 5. Final JSON Dispatch
echo json_encode($response);