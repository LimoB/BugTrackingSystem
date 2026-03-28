<?php
// index.php
session_start();
include('../../config/config.php');
include('../../includes/auth.php');

if ($_SESSION['role'] != 'admin') {
    header("Location: ../login/index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<script>


//i will come back to this script it works the same way in //assign-tickets-.php
//it actually handles the message pop up message of developer assigned successfully

        // Get URL parameters
        const params = new URLSearchParams(window.location.search + window.location.hash.replace('#', '&'));

        const success = params.get('success');
        const error = params.get('error');

        if (success) {
            alert(success);
            // Clean the URL after showing the alert
            window.history.replaceState(null, null, window.location.pathname + "#assign-tickets");
        }

        if (error) {
            alert(error);
            window.history.replaceState(null, null, window.location.pathname + "#assign-tickets");
        }
    </script>
<body>
    <header>
        <div class="navbar">
            <div class="logo">Bug Tracking System</div>
            <nav>
                <ul>
                    <li><a href="#" class="nav-link" data-page="dashboard">Dashboard</a></li>
                    <li><a href="#" class="nav-link" data-page="manage-tickets">Manage Tickets</a></li>
                    <!-- Updated this line to use data-page -->
                    <li><a href="index.php#assign-tickets" class="nav-link" data-page="assign-tickets">Assign Tickets</a></li>
                    <li><a href="#" class="nav-link" data-page="manage-projects">Manage Projects</a></li>
                    <li><a href="#" class="nav-link" data-page="manage-users">Manage Users</a></li>
                    <li><a href="../../scripts/logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
        <div class="welcome-message">
            <?php echo isset($_SESSION['name']) ? "Welcome, " . htmlspecialchars($_SESSION['name']) . " (admin)!" : "Welcome, admin!"; ?>
        </div>
    </header>

    <div id="content-area">
        <h1>Welcome to the Admin Dashboard</h1>
        <p>Select an option from the menu above to manage issues, projects, or users.</p>
    </div>

    <?php include('../../includes/footer.php'); ?>

    <script>
        $(document).ready(function () {
            $(".nav-link").click(function (e) {
                e.preventDefault();
                var page = $(this).data("page");
                if (!page) return;
                $.ajax({
                    url: page + ".php", // this will dynamically load the relevant page
                    method: "GET",
                    success: function (data) {
                        $("#content-area").html(data); // Insert the page content into #content-area
                    },
                    error: function () {
                        $("#content-area").html("<p>Error loading content. Please try again later.</p>");
                    }
                });
            });
        });
    </script>
</body>
</html>
