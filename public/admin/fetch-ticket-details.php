<?php
include('../../config/config.php');

if (isset($_GET['ticket_id'])) {
    $ticket_id = $_GET['ticket_id'];
    $query = "SELECT Tickets.*, Projects.name AS project_name 
              FROM Tickets 
              LEFT JOIN Projects ON Tickets.project_id = Projects.id 
              WHERE Tickets.id = $ticket_id";
    $result = mysqli_query($connection, $query);
    $ticket = mysqli_fetch_assoc($result);

    if ($ticket) {
        echo json_encode($ticket); // Send ticket data back as JSON
    } else {
        echo json_encode(['error' => 'Ticket not found']);
    }
} else {
    echo json_encode(['error' => 'Ticket ID not provided']);
}
?>
