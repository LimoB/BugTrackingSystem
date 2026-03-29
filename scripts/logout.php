<?php
/**
 * System Terminus: Secure Session Deconstruction
 */

// 1. Initialize session access
session_start();

// 2. Clear all session variables
$_SESSION = array();

// 3. Invalidate the session cookie in the browser
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// 4. Destroy the server-side session record
session_destroy();

// 5. Redirect to the authentication gateway
// Adjust the path below to match your login page location
header("Location: ../../../login/index.php?logout=success");
exit();