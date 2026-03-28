<?php
// auth.php - Handle authentication and session management

// Check if a session is already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

// Check if the logged-in user is an admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Check if the logged-in user is a developer
function isDeveloper() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'developer';
}
?>

