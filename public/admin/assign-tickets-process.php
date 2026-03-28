<?php
session_start();
include('../../config/config.php');
include('../../includes/auth.php');

// Check if the user is an admin
if ($_SESSION['role'] != 'admin') {
    header("Location: ../login/index.php");
    exit();
}

$success_message = '';
$error_message = '';

if (isset($_POST['assign_ticket'])) {
    $ticket_id = $_POST['assign_ticket'];
    $developer_id = $_POST["developer_" . $ticket_id];

    if (empty($developer_id)) {
        $error_message = "Please select a developer for ticket $ticket_id.";
    } else {
        $update_sql = "UPDATE Tickets SET assigned_to = ? WHERE id = ?";
        $stmt = mysqli_prepare($connection, $update_sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'ii', $developer_id, $ticket_id);
            $result = mysqli_stmt_execute($stmt);

            if ($result) {
                $developer_name_query = "SELECT name FROM Users WHERE id = ?";
                $stmt_name = mysqli_prepare($connection, $developer_name_query);
                if ($stmt_name) {
                    mysqli_stmt_bind_param($stmt_name, 'i', $developer_id);
                    mysqli_stmt_execute($stmt_name);
                    mysqli_stmt_bind_result($stmt_name, $developer_name);
                    mysqli_stmt_fetch($stmt_name);

                    $success_message = "Ticket $ticket_id has been successfully assigned to developer $developer_name.";
                    mysqli_stmt_close($stmt_name);
                } else {
                    $error_message = "Error fetching developer's name: " . mysqli_error($connection);
                }
            } else {
                $error_message = "Error assigning ticket $ticket_id: " . mysqli_error($connection);
            }
            mysqli_stmt_close($stmt);
        } else {
            $error_message = "Error preparing SQL query: " . mysqli_error($connection);
        }
    }
}

if (!empty($success_message)) {
    header("Location: index.php?success=" . urlencode($success_message) . "#assign-tickets");
} else {
    header("Location: admin/index.php?error=" . urlencode($error_message) . "#assign-tickets");
}
exit();
?>
