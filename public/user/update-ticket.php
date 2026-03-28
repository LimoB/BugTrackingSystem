<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
include('../../config/config.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/index.php");
    exit();
}

// Check if ticket ID is provided in the URL
if (!isset($_GET['ticket_id']) || !is_numeric($_GET['ticket_id'])) {
    die("Invalid Ticket ID.");
}

$ticket_id = $_GET['ticket_id'];
$user_id = $_SESSION['user_id'];

// Fetch ticket details
$query = "SELECT * FROM Tickets WHERE id = $ticket_id AND (created_by = $user_id OR assigned_to = $user_id)";
$result = mysqli_query($connection, $query);

if (mysqli_num_rows($result) == 0) {
    die("Ticket not found or you do not have permission to update it.");
}

$ticket = mysqli_fetch_assoc($result);

// Handle form submission for ticket update
if (isset($_POST['update_ticket'])) {
    $title = mysqli_real_escape_string($connection, $_POST['title']);
    $description = mysqli_real_escape_string($connection, $_POST['description']);
    $status = mysqli_real_escape_string($connection, $_POST['status']);

    if (empty($title) || empty($description)) {
        echo "Title and Description cannot be empty!";
    } else {
        // Update the ticket in the database
        $update_query = "UPDATE Tickets SET title = '$title', description = '$description', status = '$status', updated_at = CURRENT_TIMESTAMP WHERE id = $ticket_id";
        
        if (mysqli_query($connection, $update_query)) {
            echo "Ticket updated successfully!";
        } else {
            echo "Error updating ticket: " . mysqli_error($connection);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Ticket</title>

    <style>
        /* Global Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #1f3c88;
            color: white;
            padding: 20px 0;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
        }

        nav ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
            display: flex;
        }

        nav ul li {
            margin-right: 20px;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            font-size: 16px;
        }

        nav ul li a:hover {
            background-color: #3b74cc;
        }

        .ticket-form-container {
            max-width: 600px;
            margin: 40px auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .ticket-form-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #1f3c88;
        }

        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }

        input[type="text"], textarea, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        textarea {
            height: 150px;
        }

        button.submit-btn {
            background-color: #1f3c88;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        button.submit-btn:hover {
            background-color: #3b74cc;
        }

    </style>
</head>
<body>

<header>
    <div class="navbar">
        <div class="logo">Bug Tracking System</div>
        <nav>
            <ul>
                <li><a href="index.php" class="nav-link">Dashboard</a></li>
                <li><a href="create-ticket.php" class="nav-link">Create Ticket</a></li>
                <li><a href="view-tickets.php" class="nav-link">View Tickets</a></li>
                <li><a href="update-ticket.php" class="nav-link">Update Ticket</a></li> <!-- Update Ticket Link -->
                <li><a href="../../scripts/logout.php">Logout</a></li>
            </ul>
        </nav>
    </div>
</header>

<div class="ticket-form-container">
    <h2>Update Ticket</h2>
    
    <form action="update-ticket.php?ticket_id=<?php echo $ticket_id; ?>" method="POST">
        <label for="title">Ticket Title</label>
        <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($ticket['title']); ?>" required>
        
        <label for="description">Ticket Description</label>
        <textarea name="description" id="description" required><?php echo htmlspecialchars($ticket['description']); ?></textarea>
        
        <label for="status">Ticket Status</label>
        <select name="status" id="status" required>
            <option value="open" <?php echo $ticket['status'] == 'open' ? 'selected' : ''; ?>>Open</option>
            <option value="in-progress" <?php echo $ticket['status'] == 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
            <option value="resolved" <?php echo $ticket['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
            <option value="closed" <?php echo $ticket['status'] == 'closed' ? 'selected' : ''; ?>>Closed</option>
            <option value="on_hold" <?php echo $ticket['status'] == 'on_hold' ? 'selected' : ''; ?>>On Hold</option>
        </select>
        
        <button type="submit" name="update_ticket" class="submit-btn">Update Ticket</button>
    </form>
</div>

</body>
</html>
