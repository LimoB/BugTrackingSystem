<?php
session_start();
header('Content-Type: application/json');
include('../../../config/config.php');

// 1. 🛡️ Admin Security Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Security Violation: Admin authority required to purge projects.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $projectId = isset($_POST['project_id']) ? intval($_POST['project_id']) : 0;

    if ($projectId === 0) {
        echo json_encode(['error' => 'Invalid Request: No project identifier provided.']);
        exit();
    }

    // 2. 🚦 Integrity Check: Are there tickets attached?
    // This prevents accidental data corruption.
    $check_query = "SELECT COUNT(*) as ticket_count FROM Tickets WHERE project_id = ?";
    $check_stmt = mysqli_prepare($connection, $check_query);
    mysqli_stmt_bind_param($check_stmt, 'i', $projectId);
    mysqli_stmt_execute($check_stmt);
    $result = mysqli_stmt_get_result($check_stmt);
    $data = mysqli_fetch_assoc($result);

    if ($data['ticket_count'] > 0) {
        echo json_encode([
            'error' => "Cannot delete project. There are still {$data['ticket_count']} tickets assigned to this workstream. Archive or reassign them first."
        ]);
        exit();
    }

    // 3. 🚀 Atomic Purge
    $query = "DELETE FROM Projects WHERE id = ?";
    $stmt = mysqli_prepare($connection, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $projectId);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode([
                'success' => true, 
                'message' => 'Project successfully purged from the registry.'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Execution Failure: ' . mysqli_error($connection)]);
        }
        mysqli_stmt_close($stmt);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'System Failure: Unable to prepare delete statement.']);
    }
} else {
    echo json_encode(['error' => 'Invalid Request Method.']);
}

mysqli_close($connection);
?>