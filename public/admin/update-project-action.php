<?php
include('../../config/config.php');

if (isset($_POST['project_id'], $_POST['name'], $_POST['description'], $_POST['status'])) {
    $id = intval($_POST['project_id']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $status = trim($_POST['status']);

    $stmt = $connection->prepare("UPDATE Projects SET name = ?, description = ?, status = ? WHERE id = ?");
    $stmt->bind_param("sssi", $name, $description, $status, $id);

    if ($stmt->execute()) {
        echo "Project updated successfully!";
    } else {
        echo "Failed to update project.";
    }

    $stmt->close();
} else {
    echo "Invalid data submitted.";
}

$connection->close();
