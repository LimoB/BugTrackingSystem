<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login/index.php");
    exit();
}

include('../../config/config.php');

// Fetch some data for the dashboard (e.g., total tickets, unresolved tickets)
$query = "SELECT COUNT(*) AS total_tickets FROM Tickets WHERE created_by = '{$_SESSION['user_id']}'";
$result = mysqli_query($connection, $query);
$total_tickets = mysqli_fetch_assoc($result)['total_tickets'];

$query = "SELECT COUNT(*) AS unresolved_tickets FROM Tickets WHERE created_by = '{$_SESSION['user_id']}' AND status != 'resolved' AND status != 'closed'";
$result = mysqli_query($connection, $query);
$unresolved_tickets = mysqli_fetch_assoc($result)['unresolved_tickets'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <style>
        /* Global Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
        }

        h1 {
            color: #007bff;
            text-align: center;
            margin: 40px 0;
        }

        /* Navbar Styles */
        .navbar {
            background-color: #007bff;
            color: white;
            padding: 15px;
            text-align: center;
        }

        .navbar ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        .navbar ul li {
            display: inline;
            margin-right: 20px;
        }

        .navbar ul li a {
            color: white;
            text-decoration: none;
            font-size: 16px;
        }

        .navbar ul li a:hover {
            text-decoration: underline;
        }

        /* Dashboard Styles */
        .dashboard-container {
            display: flex;
            justify-content: space-around;
            margin: 20px;
            gap: 20px;
        }

        .card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 250px;
            padding: 20px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }

        .card h3 {
            margin-bottom: 10px;
            color: #333;
        }

        .card p {
            font-size: 18px;
            color: #555;
        }

        .card .btn {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 10px;
        }

        .card .btn:hover {
            background-color: #007bff;
        }
    </style>
</head>
<body>

<?php include('header.php'); ?>

<h1>Welcome to Your Dashboard</h1>

<div class="dashboard-container">
    <div class="card">
        <h3>Total Tickets</h3>
        <p><?php echo $total_tickets; ?></p>
        <a href="view-tickets.php" class="btn">View Tickets</a>
    </div>

    <div class="card">
        <h3>Unresolved Tickets</h3>
        <p><?php echo $unresolved_tickets; ?></p>
        <a href="view-tickets.php" class="btn">View Unresolved</a>
    </div>
</div>


<!-- 
to handle  night mode -->

<script>
    function setTheme(theme) {
        document.documentElement.setAttribute("data-theme", theme);
        localStorage.setItem("theme", theme);
        document.getElementById("themeToggle").textContent = theme === "dark" ? "☀️" : "🌙";
    }

    function toggleTheme() {
        const current = localStorage.getItem("theme") || "light";
        const newTheme = current === "light" ? "dark" : "light";
        setTheme(newTheme);
    }

    // Load saved theme on page load
    (function () {
        const savedTheme = localStorage.getItem("theme") || "light";
        setTheme(savedTheme);
    })();
</script>


</body>
</html>
