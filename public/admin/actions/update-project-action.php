<?php
session_start();
header('Content-Type: application/json'); // ✅ Tell the browser to expect JSON
include('../../../config/config.php');

// 1. 🛡️ Security: Ensure only Admins can hit this endpoint
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized system access.']);
    exit();
}

// 2. 🔍 Validation: Ensure all keys exist and aren't empty
$required = ['project_id', 'name', 'description', 'status'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        echo json_encode(['error' => "Missing required field: $field"]);
        exit();
    }
}

// 3. 🧼 Sanitization
$id = intval($_POST['project_id']);
$name = trim($_POST['name']);
$description = trim($_POST['description']);
$status = trim($_POST['status']);

// 4. 🚀 Database Operation
try {
    $stmt = $connection->prepare("UPDATE Projects SET name = ?, description = ?, status = ? WHERE id = ?");
    $stmt->bind_param("sssi", $name, $description, $status, $id);

    if ($stmt->execute()) {
        // Return a structured success response
        echo json_encode([
            'success' => true,
            'message' => 'Project synchronized successfully!',
            'project_name' => $name
        ]);
    } else {
        throw new Exception("Execution failed: " . $stmt->error);
    }
    
    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

$connection->close();