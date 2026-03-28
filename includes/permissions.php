<?php
// permissions.php - Define role-based access control

// Restrict access to only admins
function requireAdmin() {
    if (!isAdmin()) {
        header("Location: ../login/index.php");
        exit;
    }
}

// Restrict access to admins and developers
function requireAdminOrDeveloper() {
    if (!isAdmin() && !isDeveloper()) {
        header("Location: ../login/index.php");
        exit;
    }
}
?>
