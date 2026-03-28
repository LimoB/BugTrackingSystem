<?php
// register.php - Registration Page

include('../../config/config.php');

// Handle form submission
if (isset($_POST['register'])) {
    $name = mysqli_real_escape_string($connection, $_POST['name']);
    $email = mysqli_real_escape_string($connection, $_POST['email']);
    $password = mysqli_real_escape_string($connection, $_POST['password']);
    $confirmPassword = mysqli_real_escape_string($connection, $_POST['confirm_password']);
    $role = mysqli_real_escape_string($connection, $_POST['role']);
    
    // Check if passwords match
    if ($password !== $confirmPassword) {
        echo "Passwords do not match!";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $query = "INSERT INTO Users (name, email, password, role) 
                  VALUES ('$name', '$email', '$hashedPassword', '$role')";
        
        if (mysqli_query($connection, $query)) {
            echo "User registered successfully!";
        } else {
            echo "Error: " . mysqli_error($connection);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="register-container">
    <form action="index.php" method="POST" class="register-form">
        <h2>Register</h2>
        
        <input type="text" name="name" placeholder="Full Name" required class="input-field">
        
        <input type="email" name="email" placeholder="Email" required class="input-field">
        
        <input type="password" name="password" placeholder="Password" required class="input-field">
        
        <input type="password" name="confirm_password" placeholder="Confirm Password" required class="input-field">
        
        <select name="role" class="role-select">
            <option value="user">User</option>
            <option value="developer">Developer</option>
            <option value="admin">Admin</option>
        </select>
        
        <button type="submit" name="register" class="submit-btn">Register</button>

        <p class="login-link">Already have an account? <a href="../login/index.php">Login</a></p>
    </form>
</div>

</body>
</html>
