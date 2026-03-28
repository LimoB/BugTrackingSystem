<?php
// login.php - Login Page
session_start();
include('../../config/config.php');

$error = "";

// Handle form submission
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Prepare and execute a secure SQL statement
    $query = "SELECT * FROM Users WHERE email = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if a user was found
    if ($result && $user = $result->fetch_assoc()) {
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];

            // Redirect based on user role
            if ($user['role'] === 'admin') {
                header("Location: ../admin/index.php");
            } elseif ($user['role'] === 'developer') {
                header("Location: ../developer/index.php");
            } else {
                header("Location: ../user/index.php");
            }
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="login-container">
    <form action="index.php" method="POST" class="login-form">
        <h2>Login</h2>

        <?php if (!empty($error)) : ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <input type="email" name="email" placeholder="Email" required class="input-field">
        <input type="password" name="password" placeholder="Password" required class="input-field">
        <button type="submit" name="login" class="submit-btn">Login</button>

        <p class="register-link">Don't have an account? <a href="../register/index.php">Register</a></p>
    </form>
</div>

</body>
</html>
