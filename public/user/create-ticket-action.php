<?php
session_start();
include('../../config/config.php');

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized session.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $project_id = intval($_POST['project_id']);
    $priority = $_POST['priority'] ?? 'medium';
    $created_by = $_SESSION['user_id'];
    $status = 'open';

    if (empty($title) || empty($description) || empty($project_id)) {
        echo json_encode(['success' => false, 'message' => 'All technical fields are required.']);
        exit();
    }

    $stmt = $connection->prepare("INSERT INTO Tickets (title, description, status, priority, created_by, project_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssii", $title, $description, $status, $priority, $created_by, $project_id);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => "Ticket # " . $stmt->insert_id . " has been logged in the system backlog."
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $connection->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}