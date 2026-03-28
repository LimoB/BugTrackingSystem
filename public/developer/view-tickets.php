<?php
session_start();
include('../../config/config.php');

// Check if the request is an AJAX request
if (isset($_GET['ajax']) && $_GET['ajax'] == 'true' && isset($_GET['ticket_id'])) {
    // Fetch specific ticket details for AJAX request
    $ticket_id = intval($_GET['ticket_id']);
    
    // Fetch ticket details
    $query = "SELECT * FROM Tickets WHERE id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $ticket_id);
    $stmt->execute();
    $ticket_result = $stmt->get_result();
    
    if ($ticket_result->num_rows == 0) {
        echo json_encode(['error' => 'Ticket not found']);
        exit();
    }
    
    $ticket = $ticket_result->fetch_assoc();
    
    // Fetch comments for this ticket
    $comment_query = "
        SELECT u.name AS user_name, c.comment, c.created_at 
        FROM Comments c
        JOIN Users u ON c.user_id = u.id
        WHERE c.issue_id = ?
        ORDER BY c.created_at DESC
    ";
    $comment_stmt = $connection->prepare($comment_query);
    $comment_stmt->bind_param("i", $ticket_id);
    $comment_stmt->execute();
    $comments_result = $comment_stmt->get_result();

    $comments = [];
    while ($comment = $comments_result->fetch_assoc()) {
        $comments[] = $comment;
    }

    // Return ticket details and comments as JSON
    echo json_encode([
        'ticket' => $ticket,
        'comments' => $comments
    ]);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Tickets</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f4f6f8;
        margin: 0;
        padding: 0;
    }

    .container {
        max-width: 900px;
        margin: 40px auto;
        padding: 0 20px;
    }

    .tickets-container {
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    h2 {
        margin-top: 0;
        font-size: 28px;
        color: #333;
        margin-bottom: 20px;
        border-bottom: 2px solid #e0e0e0;
        padding-bottom: 10px;
    }

    .ticket-item {
        background: #fdfdfd;
        padding: 15px 20px;
        margin-bottom: 10px;
        border-radius: 8px;
        border: 1px solid #ddd;
        transition: background-color 0.2s, box-shadow 0.2s;
    }

    .ticket-item:hover {
        background-color: #f0f8ff;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    }

    .ticket-item a {
        font-weight: 500;
        text-decoration: none;
        color: #333;
        font-size: 16px;
    }

    .ticket-details {
        display: none;
        background: #fafafa;
        padding: 20px;
        border-left: 4px solid #007bff;
        border-radius: 0 0 8px 8px;
        margin-bottom: 20px;
        font-size: 15px;
    }

    .ticket-details h3 {
        margin-top: 0;
        font-size: 22px;
        color: #007bff;
    }

    .ticket-details p {
        margin: 8px 0;
    }

    .ticket-details h4 {
        margin-top: 20px;
        font-size: 18px;
        color: #444;
        border-bottom: 1px solid #ddd;
        padding-bottom: 5px;
    }

    .comment {
        background: #fff;
        border: 1px solid #ddd;
        padding: 12px;
        border-radius: 6px;
        margin-top: 10px;
    }

    .comment p {
        margin: 5px 0;
    }

    .comment small {
        color: #666;
        font-size: 13px;
    }
</style>

    <script>
        // Function to fetch ticket details using AJAX
        function loadTicketDetails(ticketId, element) {
            $.ajax({
                url: 'view-tickets.php?ajax=true&ticket_id=' + ticketId,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.error) {
                        alert(response.error);
                    } else {
                        var ticket = response.ticket;
                        var comments = response.comments;

                        // Display ticket details
                        var ticketDetailsHtml = `
                            <h3>Ticket #${ticket.id}</h3>
                            <p><strong>Title:</strong> ${ticket.title}</p>
                            <p><strong>Description:</strong> ${ticket.description}</p>
                            <p><strong>Status:</strong> ${ticket.status.charAt(0).toUpperCase() + ticket.status.slice(1)}</p>
                            <h4>Comments:</h4>
                        `;

                        // Display comments
                        comments.forEach(function(comment) {
                            ticketDetailsHtml += `
                                <div class="comment">
                                    <p><strong>${comment.user_name}:</strong> ${comment.comment}</p>
                                    <p><small>${comment.created_at}</small></p>
                                </div>
                            `;
                        });

                        // Insert the ticket details into the div
                        $(element).next('.ticket-details').html(ticketDetailsHtml).slideDown();
                    }
                }
            });
        }

        // Handle click event to toggle ticket details visibility
        $(document).on('click', '.ticket-item', function() {
            var ticketId = $(this).data('ticket-id');
            var detailsDiv = $(this).next('.ticket-details');
            
            // Check if details are already shown, then hide it
            if (detailsDiv.is(':visible')) {
                detailsDiv.slideUp();
            } else {
                loadTicketDetails(ticketId, this);  // Load ticket details if not already loaded
            }
        });
    </script>
</head>
<body>

<div class="container">
    <div class="tickets-container">
        <h2>All Tickets</h2>
        <?php
        $query = "SELECT id, title, status FROM Tickets";
        $result = mysqli_query($connection, $query);
        if (!$result) {
            die("Error fetching tickets: " . mysqli_error($connection));
        }
        
        while ($ticket = mysqli_fetch_assoc($result)) {
            echo "<div class='ticket-item' data-ticket-id='" . $ticket['id'] . "'>";
            echo "<a href='javascript:void(0)'>" . $ticket['id'] . ": " . htmlspecialchars($ticket['title']) . " - Status: " . ucfirst($ticket['status']) . "</a>";
            echo "</div>";
            echo "<div class='ticket-details'></div>"; // The details will be inserted here
        }
        ?>
    </div>
</div>

</body>
</html>
