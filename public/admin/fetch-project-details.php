<?php
include('../../config/config.php');

$projectId = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;

if ($projectId <= 0) {
    echo "Invalid project ID.";
    exit;
}

$query = "SELECT * FROM Projects WHERE id = ?";
$stmt = $connection->prepare($query);
$stmt->bind_param('i', $projectId);
$stmt->execute();
$result = $stmt->get_result();

if ($project = $result->fetch_assoc()) {
    echo "<div class='project-details'>";
    echo "<p><strong>Project Name:</strong> " . htmlspecialchars($project['name']) . "</p>";
    echo "<p><strong>Project Description:</strong> " . nl2br(htmlspecialchars($project['description'])) . "</p>";
    echo "<p><strong>Status:</strong> " . ucfirst(htmlspecialchars($project['status'])) . "</p>";
    echo "</div>";
} else {
    echo "Project not found.";
}

$stmt->close();
$connection->close();
?>
