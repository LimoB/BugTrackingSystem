<?php
// index.php - User Dashboard

include('../../includes/db.php');
include('../../includes/auth.php');

// Check if the user is logged in
if (!isAuthenticated()) {
    header("Location: ../login/index.php");
    exit;
}

// Fetch assigned issues for the logged-in user
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM Issues WHERE assigned_to=$user_id";
$result = mysqli_query($connection, $query);
?>

<h1>Dashboard</h1>
<table>
    <thead>
        <tr>
            <th>Title</th>
            <th>Status</th>
            <th>Priority</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $row['title'] ?></td>
                <td><?= $row['status'] ?></td>
                <td><?= $row['priority'] ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>
