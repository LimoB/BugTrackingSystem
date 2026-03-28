<?php
// Check if the user is logged in (without checking for the role)
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/index.php");
    exit();
}
?>

<header>
    <div class="navbar">
        <a href="../../public/admin/index.php" class="logo">Bug Tracking System</a>
        <nav>
            <ul>
                <li><a href="../../scripts/logout.php">Logout</a></li>
            </ul>
        </nav>
    </div>
</header>
