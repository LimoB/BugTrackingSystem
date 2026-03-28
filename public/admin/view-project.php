<?php
session_start();
include('../../config/config.php');
include('../../includes/auth.php');

// Check if the user is an admin
if ($_SESSION['role'] != 'admin') {
    header("Location: ../login/index.php");
    exit();
}

// Get the project ID from the URL
if (isset($_GET['project_id'])) {
    $project_id = $_GET['project_id'];

    // Fetch the project details from the database
    $query = "SELECT * FROM Projects WHERE id = ?";
    $stmt = mysqli_prepare($connection, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $project_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $project = mysqli_fetch_assoc($result);
        } else {
            echo "Project not found.";
            exit();
        }

        mysqli_stmt_close($stmt);
    } else {
        echo "Error: " . mysqli_error($connection);
        exit();
    }
} else {
    echo "Invalid project ID.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Project - <?php echo htmlspecialchars($project['name']); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Back to manage projects button -->
    <a href="manage-projects.php" class="button">Back to Manage Projects</a>

    <!-- Project Details -->
    <div class="project-details">
        <h2><?php echo htmlspecialchars($project['name']); ?></h2>
        <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($project['description'])); ?></p>
        <p><strong>Status:</strong> <?php echo ucfirst(htmlspecialchars($project['status'])); ?></p>
        <p><strong>Created By:</strong> <?php echo htmlspecialchars($project['created_by']); ?> <!-- Optionally you could link to the user details page here --></p>
        <p><strong>Created At:</strong> <?php echo htmlspecialchars($project['created_at']); ?></p>
    </div>

    <footer>
        <p>&copy; 2025 Bug Tracking System. All rights reserved.</p>
    </footer>

</body>
</html>
