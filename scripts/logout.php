<?php
session_start();
session_unset();
session_destroy();

// Use the full path to the login index
header("Location: /php-bugtracking-system/public/login/index.php?logout=success");
exit();