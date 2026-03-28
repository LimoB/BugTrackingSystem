<?php
// create-ticket.php - Form to create a new ticket

session_start();

// Check if the user is logged in and has the developer role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'developer') {
    header("Location: ../login/index.php");
    exit();
}

include('../../config/config.php');

// Handle form submission
if (isset($_POST['create_ticket'])) {
    $title = mysqli_real_escape_string($connection, $_POST['title']);
    $description = mysqli_real_escape_string($connection, $_POST['description']);
    $assigned_to = mysqli_real_escape_string($connection, $_POST['assigned_to']);
    $status = mysqli_real_escape_string($connection, $_POST['status']);
    $created_by = $_SESSION['user_id']; // <- NEW LINE

    if (empty($assigned_to)) {
        $message = "Please select someone to assign the ticket to.";
    } else {
        $query = "INSERT INTO Tickets (title, description, assigned_to, status, created_by) 
                  VALUES ('$title', '$description', '$assigned_to', '$status', '$created_by')";

        if (mysqli_query($connection, $query)) {
            header("Location: index.php?page=view-tickets");
            exit();
        } else {
            $message = "Error inserting ticket: " . mysqli_error($connection);
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Ticket</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* CSS for fade-in effect */
        .fade-message {
            display: none;
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            text-align: center;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        /* CSS for showing the fade message */
        .fade-message.show {
            display: block;
            animation: fadeIn 3s forwards;
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
            }

            100% {
                opacity: 1;
            }
        }
    </style>
</head>

<body>

    <div class="ticket-form-container">
        <h2>Create a New Ticket</h2>

        <!-- Display success message if ticket is created -->
        <?php if (isset($message)): ?>
            <div class="fade-message show"><?php echo $message; ?></div>
        <?php endif; ?>

        <form action="create-ticket.php" method="POST" class="ticket-form">
            <input type="text" name="title" placeholder="Ticket Title" required class="input-field">

            <textarea name="description" placeholder="Ticket Description" required class="input-field"></textarea>

            <!-- Dropdown for Assigning to Developer or User -->
            <select name="assigned_to" required class="input-field">
                <option value="">Assign to Developer/User</option>
                <?php
                // Fetch all users (developers and users) to assign the ticket
                $query = "SELECT id, name, role FROM Users WHERE role='developer' OR role='user'";
                $result = mysqli_query($connection, $query);
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<option value='" . $row['id'] . "'>" . $row['name'] . " (" . ucfirst($row['role']) . ")</option>";
                }
                ?>
            </select>

            <!-- Add a status dropdown -->
            <select name="status">
                <option value="open">Open</option>
                <option value="in-progress">In Progress</option>
                <option value="resolved">Resolved</option>
                <option value="closed">Closed</option>
                <option value="on_hold">On Hold</option>
            </select>


            <button type="submit" name="create_ticket" class="submit-btn">Create Ticket</button>
        </form>
    </div>

</body>

</html>