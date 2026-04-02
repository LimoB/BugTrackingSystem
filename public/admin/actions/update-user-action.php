<?php
/**
 * File: admin/actions/update-user-action.php
 * Purpose: Synchronizes modified user metadata and permission tiers with the core registry.
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

// 2. 🚦 Guard: Enforce POST Protocol
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Invalid Access Method.']);
    exit();
}

// 3. 🔍 Identity & Data Extraction
$userId = isset($_POST['target_user_id']) ? intval($_POST['target_user_id']) : 0;
$name   = isset($_POST['updateName']) ? trim($_POST['updateName']) : '';
$email  = isset($_POST['updateEmail']) ? trim($_POST['updateEmail']) : '';
$role   = isset($_POST['updateRole']) ? strtolower(trim($_POST['updateRole'])) : 'user';

$currentAdminId = $_SESSION['user_id'] ?? 0;

// 4. 🛑 Self-Preservation Logic
// Prevents an admin from accidentally stripping their own 'admin' role.
if ($userId === (int)$currentAdminId && $role !== 'admin') {
    echo json_encode([
        'success' => false, 
        'error' => 'Security Lock: You cannot revoke your own Administrator status.'
    ]);
    exit();
}

// 5. 🚦 Validation Logic
if (empty($name) || empty($email)) {
    echo json_encode(['success' => false, 'error' => 'Data Integrity Error: Name and Email are mandatory.']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Invalid Format: Provide a valid communication address.']);
    exit();
}

// 6. 🚀 Atomic Registry Update
$query = "UPDATE Users SET name = ?, email = ?, role = ? WHERE id = ? LIMIT 1";
$stmt = $connection->prepare($query);

if ($stmt) {
    $stmt->bind_param("sssi", $name, $email, $role, $userId);
    
    if ($stmt->execute()) {
        // execute() returns true even if 0 rows are changed (e.g., admin clicked save without edits)
        echo json_encode([
            'success' => true,
            'message' => "Identity parameters for " . htmlspecialchars($name) . " successfully synchronized."
        ]);
    } else {
        // Handle MySQL Error 1062: Duplicate entry for unique key 'email'
        if ($connection->errno === 1062) {
            echo json_encode([
                'success' => false, 
                'error' => 'Conflict: This email is already bound to another identity.'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'error' => 'Database Sync Failed: ' . $stmt->error
            ]);
        }
    }
    $stmt->close();
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'System Failure: Unable to prepare registry update.']);
}

$connection->close();
?>