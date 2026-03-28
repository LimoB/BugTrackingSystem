<?php
// fetch-project-update-form.php
include('../../config/config.php');

if (isset($_GET['project_id'])) {
    $project_id = intval($_GET['project_id']);
    $stmt = $connection->prepare("SELECT * FROM Projects WHERE id = ?");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($project = $result->fetch_assoc()) {
        ?>
        <form id="updateProjectForm">
            <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">

            <label for="name">Project Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($project['name']); ?>" required>

            <label for="description">Project Description</label>
            <textarea name="description" required><?php echo htmlspecialchars($project['description']); ?></textarea>

            <label for="status">Status</label>
            <select name="status">
                <option value="active" <?php if ($project['status'] == 'active') echo 'selected'; ?>>Active</option>
                <option value="completed" <?php if ($project['status'] == 'completed') echo 'selected'; ?>>Completed</option>
                <option value="pending" <?php if ($project['status'] == 'pending') echo 'selected'; ?>>Pending</option>
            </select>

            <button type="submit" class="submit-btn">Update Project</button>
        </form>
        <?php
    } else {
        echo "Project not found.";
    }

    $stmt->close();
} else {
    echo "Invalid request.";
}

$connection->close();
?>