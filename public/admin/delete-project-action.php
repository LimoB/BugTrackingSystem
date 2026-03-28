<?php
session_start();
include('../../config/config.php');
include('../../includes/auth.php');

if ($_SESSION['role'] != 'admin') {
    header("Location: ../login/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the project ID from POST data
    $projectId = $_POST['project_id'];

    if (empty($projectId)) {
        echo "Invalid project ID.";
        exit();
    }

    // Delete the project from the database
    $query = "DELETE FROM Projects WHERE id = ?";
    $stmt = mysqli_prepare($connection, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $projectId);
        if (mysqli_stmt_execute($stmt)) {
            echo "Project deleted successfully!";
        } else {
            echo "Error executing query: " . mysqli_error($connection);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "Error preparing statement: " . mysqli_error($connection);
    }
}
?>
