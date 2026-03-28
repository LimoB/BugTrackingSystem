<?php
// config.php - Database configuration file

$host = 'localhost';  // Database host
$username = 'root';   // Database username
$password = 'lim1234';       // Database password
$dbname = 'bug_tracking_system';  // Database name

// Create connection
$connection = mysqli_connect($host, $username, $password, $dbname);

// Check connection
if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
