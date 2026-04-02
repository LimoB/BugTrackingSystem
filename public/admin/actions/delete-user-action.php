<?php
/**
 * File: admin/actions/delete-user-action.php
 * Purpose: Secure removal of user identity and preservation of work history.
 */
session_start();
header('Content-Type: application/json');
require_once('../../../config/config.php');

// 1. 🛡️ Admin Security Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Security Violation: Kernel-level clearance required.']);
    exit();
}

// 2. 🚦 Guard: Enforce POST Protocol
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Invalid Access Method.']);
    exit();
}

$userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$currentAdminId = $_SESSION['user_id'] ?? 0;

// 3. 🛑 Self-Preservation Logic
if ($userId === (int)$currentAdminId) {
    echo json_encode(['success' => false, 'error' => 'Action Aborted: You cannot purge your own administrative identity.']);
    exit();
}

if ($userId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid Identifier: No target specified.']);
    exit();
}

// 4. ⚡ Atomic Operation: Transaction Start
$connection->begin_transaction();

try {
    // Stage A: Preserve Work History (Unlink Tickets)
    // We set 'assigned_to' to NULL so existing tickets remain in the system as "Unassigned"
    $unlinkQuery = "UPDATE Tickets SET assigned_to = NULL WHERE assigned_to = ?";
    $unlinkStmt = $connection->prepare($unlinkQuery);
    $unlinkStmt->bind_param('i', $userId);
    $unlinkStmt->execute();
    $unlinkStmt->close();

    // Stage B: Execute Identity Purge
    $deleteQuery = "DELETE FROM Users WHERE id = ? LIMIT 1";
    $deleteStmt = $connection->prepare($deleteQuery);
    $deleteStmt->bind_param('i', $userId);
    $deleteStmt->execute();

    if ($deleteStmt->affected_rows > 0) {
        // ✅ Commit changes to database
        $connection->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Identity successfully purged from registry. Work history has been preserved and unassigned.'
        ]);
    } else {
        throw new Exception("Identity not found or already purged.");
    }
    $deleteStmt->close();

} catch (Exception $e) {
    // ❌ Rollback on failure to maintain data integrity
    $connection->rollback();
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Purge Protocol Failed: ' . $e->getMessage()
    ]);
}

$connection->close();
?>