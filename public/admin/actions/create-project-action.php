<?php
session_start();
header('Content-Type: application/json');
include('../../../config/config.php');

// 1. 🛡️ Admin Security Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized: System Admin credentials required.']);
    exit();
}

// 2. 🔍 Data Extraction (No need to escape if using Prepared Statements)
$projectName        = isset($_POST['projectName']) ? trim($_POST['projectName']) : '';
$projectDescription = isset($_POST['projectDescription']) ? trim($_POST['projectDescription']) : '';
$projectStatus      = isset($_POST['projectStatus']) ? trim($_POST['projectStatus']) : 'pending';
$createdBy          = intval($_SESSION['user_id']);

// 3. 🚦 Validation Logic
$validStatuses = ['pending', 'active', 'completed'];

if (empty($projectName) || empty($projectDescription)) {
    echo json_encode(['error' => 'Validation Error: Project Name and Description are mandatory.']);
    exit();
}

if (!in_array($projectStatus, $validStatuses)) {
    echo json_encode(['error' => 'System Error: Invalid status identifier.']);
    exit();
}

// 4. 🚀 Atomic Database Insert
$query = "INSERT INTO Projects (name, description, status, created_by, created_at) VALUES (?, ?, ?, ?, NOW())";
$stmt = mysqli_prepare($connection, $query);

if ($stmt) {
    // Mapping: Name(s), Description(s), Status(s), CreatedBy(i)
    mysqli_stmt_bind_param($stmt, "sssi", $projectName, $projectDescription, $projectStatus, $createdBy);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode([
            'success' => true,
            'message' => 'New infrastructure project initialized!',
            'project_id' => mysqli_insert_id($connection)
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Execution Failure: ' . mysqli_stmt_error($stmt)]);
    }
    mysqli_stmt_close($stmt);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Preparation Failure: Could not link project to core database.']);
}

mysqli_close($connection);
?>