<?php
/**
 * File: admin/actions/update-project-action.php
 * Purpose: Synchronizes project configuration changes to the registry.
 */
ob_start();
session_start();
header('Content-Type: application/json');

require_once('../../../config/config.php');

// 1. 🛡️ Security Guard: Strict Admin Validation
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    ob_clean();
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Security Violation: Administrative clearance required.']);
    exit();
}

// 2. 🔍 Data Extraction (Matching the AJAX Form Keys)
$id          = isset($_POST['projectId']) ? (int)$_POST['projectId'] : 0;
$name        = isset($_POST['projectName']) ? trim($_POST['projectName']) : '';
$description = isset($_POST['projectDescription']) ? trim($_POST['projectDescription']) : '';
$status      = isset($_POST['projectStatus']) ? trim($_POST['projectStatus']) : '';

// 3. 🚦 Validation Logic
if ($id <= 0 || empty($name) || empty($description)) {
    ob_clean();
    echo json_encode([
        'success' => false, 
        'error' => 'Validation Error: Project Identity, Name, and Description are required.'
    ]);
    exit();
}

// 4. 🚀 Execute Database Synchronization
try {
    // Prepare the update statement
    $stmt = $connection->prepare("UPDATE Projects SET name = ?, description = ?, status = ? WHERE id = ?");
    
    if (!$stmt) {
        throw new Exception("Engine Preparation Failure: " . $connection->error);
    }

    $stmt->bind_param("sssi", $name, $description, $status, $id);

    if ($stmt->execute()) {
        
        // --- 📝 Optional: Log the change to activity_log ---
        $adminId = $_SESSION['user_id'] ?? $_SESSION['id'] ?? 0;
        $log_desc = "Admin #$adminId updated configuration for Project #$id ($name)";
        $log_query = "INSERT INTO activity_log (user_id, action_type, description) VALUES (?, 'PROJECT_UPDATE', ?)";
        
        if ($log_stmt = $connection->prepare($log_query)) {
            $log_stmt->bind_param("is", $adminId, $log_desc);
            $log_stmt->execute();
            $log_stmt->close();
        }

        ob_clean();
        echo json_encode([
            'success' => true,
            'message' => "Project #$id has been successfully synchronized.",
            'project_name' => $name
        ]);
    } else {
        throw new Exception("Execution Failure: " . $stmt->error);
    }
    
    $stmt->close();

} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'System Error: ' . $e->getMessage()
    ]);
}

// 5. Cleanup
$connection->close();
ob_end_flush();
exit();