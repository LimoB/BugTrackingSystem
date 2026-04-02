<?php
/**
 * File: admin/actions/delete-ticket-action.php
 * Purpose: Permanent purge of ticket records and associated meta-data.
 */
ob_start();
session_start();
header('Content-Type: application/json');

require_once('../../../config/config.php');

// 1. 🛡️ Security Guard: Strict Admin Authorization
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    ob_clean();
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Security Violation: Kernel level clearance required for purging.']);
    exit();
}

// 2. 🔍 Input Extraction & Protocol Check
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean();
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Protocol Error: Invalid request method.']);
    exit();
}

$ticketId = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;
$adminId  = $_SESSION['user_id'] ?? $_SESSION['id'] ?? 0;

if ($ticketId <= 0) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Identifier Error: Valid Ticket ID required.']);
    exit();
}

// 3. 🚀 Atomic Purge Sequence (Transaction Based)
$connection->begin_transaction();

try {
    // A. Cleanup Dependency: Comments
    $cleanComments = $connection->prepare("DELETE FROM Ticket_Comments WHERE ticket_id = ?");
    $cleanComments->bind_param("i", $ticketId);
    $cleanComments->execute();
    $cleanComments->close();

    // B. Cleanup Dependency: Activity Logs (Optional, depending on your architecture)
    // If you want to keep history, skip this. If you want a total wipe, include it.
    /*
    $cleanLogs = $connection->prepare("DELETE FROM activity_log WHERE description LIKE ?");
    $searchTerm = "%Ticket #$ticketId%";
    $cleanLogs->bind_param("s", $searchTerm);
    $cleanLogs->execute();
    $cleanLogs->close();
    */

    // C. Primary Purge: The Ticket Record
    $purgeTicket = $connection->prepare("DELETE FROM Tickets WHERE id = ?");
    $purgeTicket->bind_param("i", $ticketId);
    $purgeTicket->execute();

    if ($purgeTicket->affected_rows === 0) {
        throw new Exception("Target record not found or already purged.");
    }
    
    $purgeTicket->close();

    // D. Finalize Audit: Log the destruction event
    $logDesc = "Admin #$adminId executed permanent purge of Ticket #$ticketId and associated assets.";
    $auditLog = $connection->prepare("INSERT INTO activity_log (user_id, action_type, description, created_at) VALUES (?, 'TICKET_PURGE', ?, NOW())");
    $auditLog->bind_param("is", $adminId, $logDesc);
    $auditLog->execute();
    $auditLog->close();

    // 🏆 Success: Commit all changes
    $connection->commit();

    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => "Ticket #$ticketId and all dependencies have been successfully purged from the registry."
    ]);

} catch (Exception $e) {
    // 🛑 Failure: Rollback database to state before deletion attempt
    $connection->rollback();
    
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Purge Sequence Aborted: ' . $e->getMessage()
    ]);
}

// 4. Cleanup
$connection->close();
ob_end_flush();
exit();