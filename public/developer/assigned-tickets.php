<?php
session_start();
include('../../config/config.php');

// Ensure the user is logged in and is a developer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'developer') {
    header("Location: ../login/index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch tickets assigned to this developer along with project name
$ticket_query = "SELECT t.id, t.title, t.description, t.status, p.name AS project_name
                 FROM Tickets t
                 LEFT JOIN Projects p ON t.project_id = p.id
                 WHERE t.assigned_to=?";
$ticket_stmt = $connection->prepare($ticket_query);
$ticket_stmt->bind_param("i", $user_id);
$ticket_stmt->execute();
$ticket_result = $ticket_stmt->get_result();

if (!$ticket_result) {
    die("Error fetching tickets: " . $ticket_stmt->error);
}
?>

<div class="content">
    <h2>Your Assigned Tickets</h2>

    <div class="tickets-list">
        <?php
        if ($ticket_result->num_rows > 0) {
            while ($ticket = $ticket_result->fetch_assoc()) {
                echo '<div class="ticket">';
                echo '<h3>Ticket #' . $ticket['id'] . ' - ' . htmlspecialchars($ticket['title']) . '</h3>';
                echo '<p><strong>Description:</strong> ' . htmlspecialchars($ticket['description']) . '</p>';
                echo '<p><strong>Project:</strong> ' . htmlspecialchars($ticket['project_name'] ?? 'Unassigned') . '</p>';
                echo '<p><strong>Status:</strong> ' . ucfirst($ticket['status']) . '</p>';
                echo '<a href="#" class="view-details" data-ticket-id="' . $ticket['id'] . '">View Details</a>';
                echo '</div>';
            }
        } else {
            echo '<p class="no-tickets">You have no assigned tickets.</p>';
        }
        ?>
    </div>
</div>

<!-- Modal for viewing ticket details -->
<div id="ticket-modal" class="modal" style="display:none;">
    <div class="modal-content" style="background:#fff; padding:20px; border-radius:10px; max-width:600px; margin:50px auto; position:relative;">
        <span class="close-btn" style="position:absolute; top:10px; right:15px; font-size:24px; cursor:pointer;">&times;</span>
        <div id="ticket-details-content">
            <!-- Ticket details will be loaded here via AJAX -->
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<script>
    // Open modal with ticket details
    $(document).on('click', '.view-details', function(e) {
        e.preventDefault();
        const ticketId = $(this).data('ticket-id');

        $.get('get-ticket-details.php', { ticket_id: ticketId }, function(data) {
            $('#ticket-details-content').html(data);
            $('#ticket-modal').fadeIn();
        });
    });

    // Close modal
    $(document).on('click', '.close-btn', function() {
        $('#ticket-modal').fadeOut();
    });

    $(window).on('click', function(e) {
        if ($(e.target).is('#ticket-modal')) {
            $('#ticket-modal').fadeOut();
        }
    });

    // Handle status update via AJAX
    $(document).on('submit', '#update-status-form', function(e) {
        e.preventDefault();

        $.ajax({
            type: 'POST',
            url: 'update-ticket-status.php',
            data: $(this).serialize(),
            success: function(response) {
                $('#status-message').text(response);
                setTimeout(() => {
                    const ticketId = $('input[name="ticket_id"]').val();
                    $('#ticket-details-content').load('get-ticket-details.php?ticket_id=' + ticketId);
                }, 1000);
            }
        });
    });

    // Handle comment form submission
    $(document).on('submit', '#add-comment-form', function(e) {
        e.preventDefault();

        const commentText = $('textarea[name="comment"]').val().trim();
        if (commentText === "") {
            $('#comment-message').text("Comment cannot be empty.").css("color", "red");
            return;
        }

        $.post('add-comment.php', $(this).serialize(), function(response) {
            $('#comment-message').text(response).css("color", "green");

            const ticketId = $('input[name="ticket_id"]').val();
            setTimeout(() => {
                $('#ticket-details-content').load('get-ticket-details.php?ticket_id=' + ticketId);
            }, 1000);
        });
    });
</script>
