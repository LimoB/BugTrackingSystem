<?php
/**
 * File: admin/actions/delete-project-action.php
 * Purpose: Securely purges project nodes from the registry while maintaining data integrity.
 */
ob_start();
session_start();
header('Content-Type: application/json');

require_once('../../../config/config.php');

// 1. 🛡️ Security Guard: Administrative Clearance
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

$projectId = isset($_POST['project_id']) ? (int)$_POST['project_id'] : 0;
$adminId   = $_SESSION['user_id'] ?? $_SESSION['id'] ?? 0;

if ($projectId <= 0) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Identifier Error: Valid Project ID required for destruction.']);
    exit();
}

// 3. 🚀 Atomic Operation Logic
try {
    $connection->begin_transaction();

    // A. 🚦 Integrity Check: Block deletion if orphaned tickets would be created
    $checkQuery = "SELECT COUNT(*) as ticket_count FROM Tickets WHERE project_id = ? AND deleted_at IS NULL";
    $checkStmt = $connection->prepare($checkQuery);
    $checkStmt->bind_param('i', $projectId);
    $checkStmt->execute();
    $data = $checkStmt->get_result()->fetch_assoc();
    $checkStmt->close();

    if ($data['ticket_count'] > 0) {
        throw new Exception("Operational Block: This project contains {$data['ticket_count']} active tickets. Reassign or purge them before removing the workstream.");
    }

    // B. Fetch Project Name for the Log (before it's gone)
    $nameQuery = "SELECT name FROM Projects WHERE id = ?";
    $nameStmt = $connection->prepare($nameQuery);
    $nameStmt->bind_param('i', $projectId);
    $nameStmt->execute();
    $project = $nameStmt->get_result()->fetch_assoc();
    $projectName = $project['name'] ?? 'Unknown Project';
    $nameStmt->close();

    // C. Primary Purge: The Project Record
    $purgeStmt = $connection->prepare("DELETE FROM Projects WHERE id = ?");
    $purgeStmt->bind_param("i", $projectId);
    $purgeStmt->execute();

    if ($purgeStmt->affected_rows === 0) {
        throw new Exception("Target record not found in registry.");
    }
    $purgeStmt->close();

    // D. 📝 Audit Trail: Log the destruction event
    $logDesc = "Admin #$adminId executed permanent purge of Project Node #$projectId ($projectName).";
    $auditLog = $connection->prepare("INSERT INTO activity_log (user_id, action_type, description, created_at) VALUES (?, 'PROJECT_PURGE', ?, NOW())");
    $auditLog->bind_param("is", $adminId, $logDesc);
    $auditLog->execute();
    $auditLog->close();

    // 🏆 Success: Commit Sequence
    $connection->commit();

    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => "Project #$projectId ($projectName) has been successfully synchronized and purged."
    ]);

} catch (Exception $e) {
    // 🛑 Failure: Rollback
    $connection->rollback();
    
    ob_clean();
    echo json_encode([
        'success' => false, 
        'error'   => $e->getMessage()
    ]);
}

// 4. Cleanup
$connection->close();
ob_end_flush();
exit();