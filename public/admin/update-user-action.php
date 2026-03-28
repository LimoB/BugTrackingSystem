<?php
session_start();
include('../../config/config.php');
include('../../includes/auth.php');

// Check if the user is an admin
if ($_SESSION['role'] != 'admin') {
    header("Location: ../login/index.php");
    exit();
}

// Check if the form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['userId'];
    $name = $_POST['userName'];
    $email = $_POST['userEmail'];
    $role = $_POST['userRole'];

    // Prepare the update query
    $query = "UPDATE Users SET name = ?, email = ?, role = ? WHERE id = ?";
    $stmt = $connection->prepare($query);

    if ($stmt) {
        $stmt->bind_param('sssi', $name, $email, $role, $user_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo "User updated successfully!";
        } else {
            echo "No changes made or user not found.";
        }

        $stmt->close();
    } else {
        echo "Error: " . $connection->error;
    }
}
?>
