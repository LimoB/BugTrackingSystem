<?php
session_start();
include('../../config/config.php');
include('../../includes/auth.php');

// Check if the user is an admin
if ($_SESSION['role'] != 'admin') {
    header("Location: ../login/index.php");
    exit();
}

// Check if user ID is provided
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    // Fetch user details from the database
    $query = "SELECT * FROM Users WHERE id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo json_encode($user); // Send the response as JSON
    } else {
        echo json_encode(['error' => 'User not found']); // Handle user not found case
    }

    $stmt->close();
} else {
    echo json_encode(['error' => 'No user ID provided']);
}
?>
