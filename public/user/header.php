<header>
    <style>
        /* Header Styling */
        header {
            background-color: #007bff;
            color: white;
            padding: 20px 0;
            font-family: Arial, sans-serif;
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
            color: white;
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
            padding: 10px;
            border-radius: 5px;
        }

        nav ul li a:hover {
            background-color: #007bff;
        }

        /* Welcome Message Styling */
        .welcome-message {
            text-align: center;
            font-size: 18px;
            color: white;
            margin-top: 10px;
        }

        /* Optional: Add a small shadow to the navbar for better visibility */
        .navbar {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Hover effect for nav links */
        .nav-link:hover {
            background-color: #007bff;
            transition: background-color 0.3s ease;
        }


        #themeToggle {
            position: absolute;
            top: 15px;
            right: 20px;
            padding: 6px 10px;
            border-radius: 5px;
            cursor: pointer;
            background: transparent;
            color: var(--text-color);
            font-size: 18px;
        }

/* to handle night mode i will come back to it */
        :root {
            --bg-color: #f4f7fc;
            --text-color: #007bff;
            --table-bg: #f8f9fc;
            --hover-bg: #e0e0e0;
            --modal-bg: #fefefe;
            --status-open: #007bff;
            --status-in-progress: #ffc107;
            --status-resolved: #28a745;
            --status-closed: #6c757d;
            --status-on-hold: #17a2b8;
        }

        [data-theme="dark"] {
            --bg-color: #1c1c1c;
            --text-color: #ffffff;
            --table-bg: #2c2c2c;
            --hover-bg: #333;
            --modal-bg: #2b2b2b;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
        }

        table {
            background-color: var(--table-bg);
        }

        tr:hover {
            background-color: var(--hover-bg);
        }

        .modal-content {
            background-color: var(--modal-bg);
            color: var(--text-color);
        }
    </style>

    <div class="navbar">
        <div class="logo">Bug Tracking System</div>
        <nav>
            <ul>
                <li><a href="index.php" class="nav-link">Dashboard</a></li>
                <li><a href="create-ticket.php" class="nav-link">Create Ticket</a></li>
                <li><a href="view-tickets.php" class="nav-link">View Tickets</a></li>
                <li><a href="../../scripts/logout.php">Logout</a></li>

                <!-- to handle night mode i will look at it -->
                <button onclick="toggleTheme()" id="themeToggle">🌙</button>
            </ul>
        </nav>
    </div>

    <div class="welcome-message">
        <?php
        if (isset($_SESSION['name'])) {
            echo "Welcome, " . htmlspecialchars($_SESSION['name']) . " (User)!";
        } else {
            echo "Welcome, User!";
        }
        ?>
    </div>
</header>