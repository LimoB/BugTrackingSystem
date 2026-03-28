<?php
session_start();
include('../../config/config.php');

// Check if the user is an admin
if ($_SESSION['role'] != 'admin') {
    echo "Unauthorized access!";
    exit();
}

if (isset($_POST['userId']) && is_numeric($_POST['userId'])) {
    $userId = $_POST['userId'];

    // Delete user
    $query = "DELETE FROM Users WHERE id = ?";
    if ($stmt = mysqli_prepare($connection, $query)) {
        // Bind the parameter (user_id)
        mysqli_stmt_bind_param($stmt, 'i', $userId);

        // Execute the statement
        if (mysqli_stmt_execute($stmt)) {
            echo "User deleted successfully!";
        } else {
            echo "Error deleting user: " . mysqli_error($connection);
        }

        // Close prepared statement
        mysqli_stmt_close($stmt);
    } else {
        echo "Failed to prepare SQL statement.";
    }
} else {
    echo "Invalid user ID.";
}
?>
