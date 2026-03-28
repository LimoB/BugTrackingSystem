<?php
// user/create-ticket.php - Form to create a new ticket (by regular user)

session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);



// Check if the user is logged in and has the 'user' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login/index.php");
    exit();
}

include('../../config/config.php');

$message = '';

// Fetch available projects from the Projects table
$projectsQuery = "SELECT * FROM Projects";
$projectsResult = mysqli_query($connection, $projectsQuery);

if (!$projectsResult) {
    die("Error fetching projects: " . mysqli_error($connection));
}

if (isset($_POST['create_ticket'])) {
    $project_id = mysqli_real_escape_string($connection, $_POST['project_id']);
    $title = mysqli_real_escape_string($connection, $_POST['title']);
    $description = mysqli_real_escape_string($connection, $_POST['description']);
    $status = mysqli_real_escape_string($connection, $_POST['status']);
    $created_by = $_SESSION['user_id'];

    // Validate inputs
    if (empty($title) || empty($description) || empty($project_id)) {
        $message = "Title, Description, and Project are required.";
    } else {
        // Insert ticket with selected project_id
        $query = "INSERT INTO Tickets (title, description, assigned_to, status, created_by, project_id)
                  VALUES ('$title', '$description', NULL, '$status', '$created_by', '$project_id')";

        if (mysqli_query($connection, $query)) {
            $message = "Ticket created successfully!";
        } else {
            $message = "Error: " . mysqli_error($connection);
        }
    }

    // Set the message and redirect to the dashboard
    $_SESSION['message'] = $message;
    header("Location: index.php");  // Redirect to dashboard
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Ticket</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .ticket-form-container {
            margin: 20px;
            padding: 20px;
            background-color: #f4f7fc;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .ticket-form h2 {
            margin-bottom: 20px;
        }
        .input-field, .submit-btn, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .submit-btn {
            background-color: #007bff; /* Navy blue */
            color: white;
            cursor: pointer;
        }
        .submit-btn:hover {
            background-color: #007bff; /* Slightly lighter navy */
        }
    </style>
</head>
<body>
<?php include('header.php'); ?>

<div id="content-area">
    <h1>Create a New Ticket</h1>

    <div class="ticket-form-container">
        <form action="create-ticket.php" method="POST" class="ticket-form">
            <input type="text" name="title" placeholder="Ticket Title" required class="input-field">

            <textarea name="description" placeholder="Ticket Description" required class="input-field"></textarea>

            <select name="status" required class="input-field">
                <option value="open">Open</option>
                <option value="in-progress">In Progress</option>
                <option value="resolved">Resolved</option>
                <option value="closed">Closed</option>
                <option value="on_hold">On Hold</option>
            </select>

            <select name="project_id" required class="input-field">
                <option value="">Select Project</option>
                <?php
                while ($project = mysqli_fetch_assoc($projectsResult)) {
                    echo '<option value="' . $project['id'] . '">' . $project['name'] . '</option>';
                }
                ?>
            </select>

            <button type="submit" name="create_ticket" class="submit-btn">Create Ticket</button>
        </form>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>

</body>
</html>
