<?php
session_start();

// ✅ Check if the user is logged in and has the developer role
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'developer') {
    header("Location: ../login/index.php");
    exit();
}

// Include database connection
include('../../config/config.php');

// ✅ Fetch developer user data securely
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM Users WHERE id=?";
$stmt = $connection->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Error fetching user data: " . $stmt->error);
}

$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Developer Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>

    <div class="dashboard-container">
        <header>
            <h1>Welcome, <?php echo htmlspecialchars($user['name']); ?> (Developer)</h1>
            <nav>
                <ul>
                    <li><a href="#" class="nav-link" data-page="dashboard">Dashboard</a></li>
                    <li><a href="#" class="nav-link" data-page="assigned-tickets">Assigned Tickets</a></li>
                    <li><a href="#" class="nav-link" data-page="view-tickets">View All Tickets</a></li>
                    <!-- <li><a href="#" class="nav-link" data-page="create-ticket">Create Ticket</a></li> -->
                    <li><a href="../../scripts/logout.php">Logout</a></li>
                </ul>
            </nav>
        </header>

        <div class="content">
            <div id="content-area">
                <!-- Default content on page load -->
                <h1>Welcome to the Developer Dashboard</h1>
                <p>Here you can manage your tickets, track assignments</p>
            </div>
        </div>
    </div>

    <script>
        // Dynamic content loading via AJAX
        $(document).ready(function() {
            $(".nav-link").click(function(e) {
                e.preventDefault();

                var page = $(this).data("page");

                $.ajax({
                    url: page + ".php", // Load page content dynamically
                    method: "GET",
                    success: function(data) {
                        $("#content-area").html(data);
                    },
                    error: function() {
                        $("#content-area").html("<p>Error loading content. Please try again later.</p>");
                    }
                });
            });
        });


        // If there's a ?page=something in the URL, load that page automatically
        const params = new URLSearchParams(window.location.search);
        const initialPage = params.get('page');

        if (initialPage) {
            $.ajax({
                url: initialPage + ".php",
                method: "GET",
                success: function(data) {
                    $("#content-area").html(data);
                },
                error: function() {
                    $("#content-area").html("<p>Error loading content.</p>");
                }
            });
        }
    </script>

</body>

</html>