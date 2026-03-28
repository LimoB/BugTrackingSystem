<?php
session_start();
include('../../config/config.php');
include('../../includes/auth.php');

// Check if the user is an admin
if ($_SESSION['role'] != 'admin') {
    header("Location: ../login/index.php");
    exit();
}

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

// Update project logic
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($connection, $_POST['projectName']);
    $description = mysqli_real_escape_string($connection, $_POST['projectDescription']);
    $status = mysqli_real_escape_string($connection, $_POST['status']);

    $update_query = "UPDATE Projects SET name = ?, description = ?, status = ? WHERE id = ?";
    $update_stmt = mysqli_prepare($connection, $update_query);

    if ($update_stmt) {
        mysqli_stmt_bind_param($update_stmt, 'sssi', $name, $description, $status, $project_id);
        if (mysqli_stmt_execute($update_stmt)) {
            header("Location: manage-projects.php"); // Redirect to the project management page after successful update
            exit();
        } else {
            echo "Error updating project: " . mysqli_error($connection);
        }
        mysqli_stmt_close($update_stmt);
    } else {
        echo "Error: " . mysqli_error($connection);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Project</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <h1>Update Project</h1>

    <form action="update-project.php?project_id=<?php echo $project_id; ?>" method="POST">
        <label for="projectName">Project Name:</label>
        <input type="text" id="projectName" name="projectName" value="<?php echo htmlspecialchars($project['name']); ?>" required>

        <label for="projectDescription">Project Description:</label>
        <textarea id="projectDescription" name="projectDescription" required><?php echo htmlspecialchars($project['description']); ?></textarea>

        <label for="status">Status:</label>
        <select id="status" name="status" required>
            <option value="active" <?php echo ($project['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
            <option value="inactive" <?php echo ($project['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
        </select>

        <button type="submit">Update Project</button>
    </form>

    <footer>
        <p>&copy; 2025 Bug Tracking System. All rights reserved.</p>
    </footer>

</body>
</html>
