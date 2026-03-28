<?php
session_start();
include('../../config/config.php');
include('../../includes/auth.php');

// Check if the user is an admin
if ($_SESSION['role'] != 'admin') {
    echo "Unauthorized access!";
    exit();
}

// Check if user_id is provided
if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $userId = $_GET['user_id'];

    // Query to fetch user details
    $query = "SELECT * FROM Users WHERE id = ?";
    if ($stmt = mysqli_prepare($connection, $query)) {
        // Bind the parameter (user_id)
        mysqli_stmt_bind_param($stmt, 'i', $userId);

        // Execute the statement
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);

            // Check if user was found
            if ($user) {
                // Output user details
                echo "<p><strong>User ID:</strong> " . $user['id'] . "</p>";
                echo "<p><strong>Name:</strong> " . htmlspecialchars($user['name']) . "</p>";
                echo "<p><strong>Email:</strong> " . htmlspecialchars($user['email']) . "</p>";
                echo "<p><strong>Role:</strong> " . ucfirst($user['role']) . "</p>";
            } else {
                echo "No user found with that ID.";
            }
        } else {
            echo "Error executing query: " . mysqli_error($connection);
        }

        // Close prepared statement
        mysqli_stmt_close($stmt);
    } else {
        echo "Failed to prepare SQL statement.";
    }
} else {
    echo "Invalid or missing User ID.";
}
?>
