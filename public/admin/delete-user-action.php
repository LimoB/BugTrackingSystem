<?php
include('../../config/config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];

    $query = "DELETE FROM Users WHERE id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('i', $user_id);

    if ($stmt->execute()) {
        echo "User deleted successfully!";
    } else {
        echo "Error deleting user: " . $connection->error;
    }
}
?>
