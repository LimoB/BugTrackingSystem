<?php
// create-project-action.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');
include('../../config/config.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Access denied']);
    exit();
}

$projectName = isset($_POST['projectName']) ? mysqli_real_escape_string($connection, $_POST['projectName']) : '';
$projectDescription = isset($_POST['projectDescription']) ? mysqli_real_escape_string($connection, $_POST['projectDescription']) : '';
$projectStatus = isset($_POST['projectStatus']) ? mysqli_real_escape_string($connection, $_POST['projectStatus']) : '';

$validStatuses = ['pending', 'active', 'completed'];
if (!in_array($projectStatus, $validStatuses)) {
    echo json_encode(['error' => 'Invalid status']);
    exit();
}

if (empty($projectName) || empty($projectDescription)) {
    echo json_encode(['error' => 'All fields are required']);
    exit();
}

$createdBy = $_SESSION['user_id'];

$query = "INSERT INTO Projects (name, description, status, created_by) VALUES ('$projectName', '$projectDescription', '$projectStatus', $createdBy)";
$result = mysqli_query($connection, $query);

if ($result) {
    echo json_encode(['success' => 'Project created successfully!']);
} else {
    echo json_encode(['error' => 'Database error: ' . mysqli_error($connection)]);
}
?>
