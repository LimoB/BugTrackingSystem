<?php
/**
 * File: admin/actions/create-project-action.php
 * Purpose: Initializes new infrastructure projects within the registry.
 */
ob_start();
session_start();
header('Content-Type: application/json');

require_once('../../../config/config.php');

// 1. 🛡️ Security Guard: Administrative Clearance
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    ob_clean();
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Security Violation: Administrative clearance required for initialization.']);
    exit();
}

// 2. 🔍 Data Extraction & Sanitization
$projectName        = isset($_POST['projectName']) ? trim($_POST['projectName']) : '';
$projectDescription = isset($_POST['projectDescription']) ? trim($_POST['projectDescription']) : '';
$projectStatus      = isset($_POST['projectStatus']) ? trim($_POST['projectStatus']) : 'pending';
$adminId            = $_SESSION['user_id'] ?? $_SESSION['id'] ?? 0;

// 3. 🚦 Validation Logic
$validStatuses = ['pending', 'active', 'completed'];

if (empty($projectName) || empty($projectDescription)) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Validation Error: Project Identity and Strategic Briefing are mandatory.']);
    exit();
}

if (!in_array($projectStatus, $validStatuses)) {
    $projectStatus = 'pending'; // Default to pending if tampered with
}

// 4. 🚀 Atomic Database Operation
try {
    $connection->begin_transaction();

    // A. Insert Project Record
    $query = "INSERT INTO Projects (name, description, status, created_by, created_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $connection->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Engine Preparation Failure: " . $connection->error);
    }

    $stmt->bind_param("sssi", $projectName, $projectDescription, $projectStatus, $adminId);
    
    if (!$stmt->execute()) {
        throw new Exception("Execution Failure: " . $stmt->error);
    }

    $newProjectId = $connection->insert_id;
    $stmt->close();

    // B. 📝 Audit Trail: Log Project Initialization
    $logDesc = "Admin #$adminId initialized new Project Node #$newProjectId ($projectName).";
    $logQuery = "INSERT INTO activity_log (user_id, action_type, description, created_at) VALUES (?, 'PROJECT_CREATE', ?, NOW())";
    
    if ($logStmt = $connection->prepare($logQuery)) {
        $logStmt->bind_param("is", $adminId, $logDesc);
        $logStmt->execute();
        $logStmt->close();
    }

    // 🏆 Success: Commit Transaction
    $connection->commit();

    ob_clean();
    echo json_encode([
        'success'    => true,
        'message'    => 'New infrastructure project initialized successfully!',
        'project_id' => $newProjectId,
        'node_ref'   => "PROJ-" . str_pad($newProjectId, 3, '0', STR_PAD_LEFT)
    ]);

} catch (Exception $e) {
    $connection->rollback();
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error'   => 'System Error: ' . $e->getMessage()
    ]);
}

// 5. Cleanup
$connection->close();
ob_end_flush();
exit();