<?php
/**
 * File: admin/actions/update-ticket-action.php
 * Purpose: Atomic synchronization of ticket state and priority.
 */
ob_start(); 
session_start();
header('Content-Type: application/json');

require_once('../../../config/config.php');

// 1. 🛡️ Admin Security Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    ob_clean();
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Security Violation: Kernel access denied.']);
    exit();
}

// 2. 🔍 Input Extraction & Sanitization
$ticketId       = isset($_POST['ticketId']) ? (int)$_POST['ticketId'] : 0;
$ticketStatus   = isset($_POST['ticketStatus']) ? strtolower(trim($_POST['ticketStatus'])) : '';
$ticketPriority = isset($_POST['ticketPriority']) ? strtolower(trim($_POST['ticketPriority'])) : '';

// Map the admin ID based on your Users table login
$adminId = $_SESSION['id'] ?? $_SESSION['user_id'] ?? 0;

// 3. 🚦 Strict Validation (Prevents "Data Truncated" errors)
// Based on your DESCRIBE Tickets status; output
$validStatuses = ['open', 'in-progress', 'resolved', 'closed', 'on-hold'];
if (!in_array($ticketStatus, $validStatuses)) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => "Kernel Rejection: '$ticketStatus' is not a recognized ENUM state."]);
    exit();
}

if ($ticketId <= 0) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Invalid Reference: Ticket ID is required.']);
    exit();
}

// 4. 🚀 Execute Atomic Update
try {
    // Matches your Tickets table: status, priority, id
    $query = "UPDATE Tickets SET status = ?, priority = ? WHERE id = ? LIMIT 1";
    $stmt = $connection->prepare($query);

    if (!$stmt) {
        throw new Exception("SQL Prepare Failure: " . $connection->error);
    }

    $stmt->bind_param('ssi', $ticketStatus, $ticketPriority, $ticketId);

    if ($stmt->execute()) {
        
        // --- 📝 Activity Logging (Synchronized with your MariaDB schema) ---
        // Your table columns: user_id, action_type, description, ticket_id
        $log_type = "TICKET_OVERRIDE";
        $log_desc = "Admin #$adminId updated Ticket #$ticketId to $ticketStatus ($ticketPriority)";
        
        $log_query = "INSERT INTO activity_log (user_id, action_type, description, ticket_id, created_at) VALUES (?, ?, ?, ?, NOW())";
        
        if ($log_stmt = $connection->prepare($log_query)) {
            // "issi" = int, string, string, int
            $log_stmt->bind_param("issi", $adminId, $log_type, $log_desc, $ticketId);
            $log_stmt->execute();
            $log_stmt->close();
        }

        // 5. 📡 Return Success Payload
        ob_clean();
        echo json_encode([
            'success' => true,
            'message' => "Registry Synchronized: Ticket #$ticketId updated to $ticketStatus."
        ]);
    } else {
        throw new Exception("Execution Failure: " . $stmt->error);
    }
    
    $stmt->close();

} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    // This will now show the exact MariaDB error if one occurs
    echo json_encode(['success' => false, 'error' => 'Database Sync Error: ' . $e->getMessage()]);
}

$connection->close();
ob_end_flush();
exit();