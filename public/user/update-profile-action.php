<?php
session_start();
include('../../config/config.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid Request Method']);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Session Expired. Please login again.']);
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';

if (empty($current_password) || empty($new_password)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit();
}

// 1. Verify Current Password
$stmt = $connection->prepare("SELECT password FROM Users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || !password_verify($current_password, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
    exit();
}

// 2. Hash and Update New Password
$hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
$update_stmt = $connection->prepare("UPDATE Users SET password = ? WHERE id = ?");
$update_stmt->bind_param("si", $hashed_password, $user_id);

if ($update_stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Security credentials updated successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error. Failed to update.']);
}

$stmt->close();
$update_stmt->close();
$connection->close();