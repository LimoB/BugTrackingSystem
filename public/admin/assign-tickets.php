<?php
session_start();
include('../../config/config.php');
include('../../includes/auth.php');

// Check if the user is an admin
if ($_SESSION['role'] != 'admin') {
    header("Location: ../login/index.php");
    exit();
}

// Fetch unassigned tickets from the database
$sql = "SELECT t.id AS ticket_id, t.title, t.description, p.name AS project_name
        FROM Tickets t
        LEFT JOIN Projects p ON t.project_id = p.id
        WHERE t.assigned_to IS NULL";
$result = mysqli_query($connection, $sql);

if (!$result) {
    die("Error fetching tickets: " . mysqli_error($connection));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Tickets</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <!-- Success or error message -->
    <?php if (isset($_GET['success'])): ?>
        <div class="success-message"><?php echo htmlspecialchars($_GET['success']); ?></div>
    <?php elseif (isset($_GET['error'])): ?>
        <div class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>


    <!-- Assign Tickets Section -->
    <section id="assign-tickets">
        <h1>Assign Tickets to Developers</h1>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <form action="assign-tickets-process.php" method="POST">
                <table>
                    <thead>
                        <tr>
                            <th>Ticket ID</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Project</th>
                            <th>Select Developer</th>
                            <th>Assign</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($ticket = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo $ticket['ticket_id']; ?></td>
                                <td><?php echo $ticket['title']; ?></td>
                                <td><?php echo $ticket['description']; ?></td>
                                <td><?php echo $ticket['project_name'] ? $ticket['project_name'] : 'No project'; ?></td>
                                <td>
                                    <!-- Dropdown to select developer -->
                                    <select name="developer_<?php echo $ticket['ticket_id']; ?>">
                                        <?php
                                        // Fetch developers for each ticket
                                        $developer_sql = "SELECT * FROM Users WHERE role = 'developer'";
                                        $developer_result = mysqli_query($connection, $developer_sql);

                                        if ($developer_result) {
                                            while ($developer = mysqli_fetch_assoc($developer_result)) {
                                                echo "<option value='" . $developer['id'] . "'>" . $developer['name'] . "</option>";
                                            }
                                        } else {
                                            echo "<option>No developers available</option>";
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td>
                                    <!-- Assign button for each ticket -->
                                    <button type="submit" name="assign_ticket" value="<?php echo $ticket['ticket_id']; ?>">Assign</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </form>
        <?php else: ?>
            <p>No unassigned tickets available.</p>
        <?php endif; ?>
    </section>

   


</body>

</html>

<?php
// Close the connection
mysqli_close($connection);
?>