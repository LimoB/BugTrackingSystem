<?php
/**
 * File: admin/actions/delete-user-action.php
 * Purpose: Secure removal of user identity with work history preservation.
 */
session_start();
header('Content-Type: application/json');
require_once('../../../config/config.php');

// 1. 🛡️ Kernel Security Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'success' => false, 
        'error' => 'Security Violation: Kernel-level admin clearance required.'
    ]);
    exit();
}

// 2. 🔍 Data Extraction & Self-Preservation
$userId = isset($_POST['userId']) ? intval($_POST['userId']) : 0;
$currentAdminId = $_SESSION['user_id'] ?? 0;

if ($userId === 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid Identifier: Target user ID missing.']);
    exit();
}

if ($userId === (int)$currentAdminId) {
    echo json_encode(['success' => false, 'error' => 'System Safeguard: You cannot purge your own active session.']);
    exit();
}

// 3. ⚡ Atomic Operation: Transaction Start
// Ensures unlinking and deletion are treated as a single unit of work.
$connection->begin_transaction();

try {
    // Stage A: Preserve Work History (Unlink Tickets)
    // We set 'assigned_to' to NULL so existing tickets remain as "Unassigned" rather than disappearing.
    $unlinkQuery = "UPDATE Tickets SET assigned_to = NULL WHERE assigned_to = ?";
    $unlinkStmt = $connection->prepare($unlinkQuery);
    
    if (!$unlinkStmt) throw new Exception("Failed to prepare ticket unlinking statement.");
    
    $unlinkStmt->bind_param('i', $userId);
    $unlinkStmt->execute();
    $unlinkStmt->close();

    // Stage B: Execute Identity Purge
    $deleteQuery = "DELETE FROM Users WHERE id = ? LIMIT 1";
    $deleteStmt = $connection->prepare($deleteQuery);
    
    if (!$deleteStmt) throw new Exception("Failed to prepare user deletion statement.");
    
    $deleteStmt->bind_param('i', $userId);
    $deleteStmt->execute();

    if ($deleteStmt->affected_rows > 0) {
        // ✅ Success: Commit changes to database
        $connection->commit();
        echo json_encode([
            'success' => true,
            'message' => "Identity successfully purged. Associated tasks have been moved to the unassigned pool."
        ]);
    } else {
        throw new Exception("Identity not found or already removed from the registry.");
    }
    $deleteStmt->close();

} catch (Exception $e) {
    // ❌ Rollback: Undo any changes if an error occurred during the sequence
    $connection->rollback();
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Registry Error: ' . $e->getMessage()
    ]);
}

$connection->close();
?>