<?php
session_start();
include('../../config/config.php');
include('../../includes/auth.php');

// Check if the user is an admin
if ($_SESSION['role'] != 'admin') {
    echo "Unauthorized access!";
    exit();
}

if (isset($_GET['ticket_id'])) {
    $ticketId = $_GET['ticket_id'];

    // Query to fetch ticket details
    $query = "SELECT * FROM Tickets WHERE id = ?";
    $stmt = mysqli_prepare($connection, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $ticketId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $ticket = mysqli_fetch_assoc($result);

        if ($ticket) {
            // Return update form with ticket details
            echo '<form id="updateTicketForm">
                    <label for="ticketStatus">Status:</label>
                    <select id="ticketStatus" name="ticketStatus">
                        <option value="open"' . ($ticket['status'] == 'open' ? ' selected' : '') . '>Open</option>
                        <option value="closed"' . ($ticket['status'] == 'closed' ? ' selected' : '') . '>Closed</option>
                        <option value="in_progress"' . ($ticket['status'] == 'in_progress' ? ' selected' : '') . '>In Progress</option>
                    </select>
                    <button type="submit">Update Ticket</button>
                  </form>';
        } else {
            echo "No ticket found with that ID.";
        }

        mysqli_stmt_close($stmt);
    } else {
        echo "Failed to prepare SQL statement: " . mysqli_error($connection);
    }
} else {
    echo "Ticket ID not provided.";
}
?>
