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

    // Fetch the user data for the form
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
                // Output user form
                echo '<form method="POST" action="update-user-action.php">';
                echo '<input type="hidden" name="userId" value="' . $user['id'] . '">';
                echo '<label for="userName">Name:</label><br>';
                echo '<input type="text" name="userName" value="' . htmlspecialchars($user['name']) . '" required><br><br>';
                echo '<label for="userEmail">Email:</label><br>';
                echo '<input type="email" name="userEmail" value="' . htmlspecialchars($user['email']) . '" required><br><br>';
                echo '<label for="userRole">Role:</label><br>';
                echo '<select name="userRole" required>';
                echo '<option value="admin" ' . ($user['role'] == 'admin' ? 'selected' : '') . '>Admin</option>';
                echo '<option value="user" ' . ($user['role'] == 'user' ? 'selected' : '') . '>User</option>';
                echo '</select><br><br>';
                echo '<button type="submit">Update User</button>';
                echo '</form>';
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
