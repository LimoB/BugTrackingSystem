<?php
session_start();
include('../../config/config.php');
include('../../includes/auth.php');

// Check if the user is an admin
if ($_SESSION['role'] != 'admin') {
    header("Location: ../login/index.php");
    exit();
}

// Check if the necessary POST data is available
if (isset($_POST['userName'], $_POST['userEmail'], $_POST['userPassword'], $_POST['userRole'])) {
    $name = trim($_POST['userName']);
    $email = trim($_POST['userEmail']);
    $password = trim($_POST['userPassword']);
    $role = trim($_POST['userRole']);

    // Validate the form data
    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        echo "All fields are required.";
        exit();
    }

    // Check if email already exists
    $email_check_query = "SELECT id FROM Users WHERE email = ?";
    $email_check_stmt = $connection->prepare($email_check_query);
    $email_check_stmt->bind_param("s", $email);
    $email_check_stmt->execute();
    $email_check_result = $email_check_stmt->get_result();

    if ($email_check_result->num_rows > 0) {
        echo "Error: The email address is already in use.";
        exit();
    }

    // Hash the password before storing it
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare and execute the database query to insert the new user
    $user_query = "INSERT INTO Users (name, email, password, role) VALUES (?, ?, ?, ?)";
    if ($stmt = $connection->prepare($user_query)) {
        // Bind parameters and execute
        $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);
        
        if ($stmt->execute()) {
            echo "User created successfully!";
        } else {
            echo "Error: " . $stmt->error;  // Show specific error if the query fails
        }
    } else {
        echo "Error preparing the SQL statement.";  // In case the statement fails to prepare
    }
} else {
    echo "Required data not received.";
}

?>
