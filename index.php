<?php
// index.php - Main entry point


session_start();

if (!isset($_SESSION['user_id'])) {
    // User is not logged in, redirect to login page
    header("Location: public/login/index.php");
    exit();
}



?>
