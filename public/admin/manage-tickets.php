<?php
session_start();
include('../../config/config.php');
include('../../includes/auth.php');

// Check if the user is an admin
if ($_SESSION['role'] != 'admin') {
    header("Location: ../login/index.php");
    exit();
}

// Fetch all tickets with the associated project name
$query = "SELECT Tickets.id, Tickets.title, Tickets.status, Projects.name AS project_name, Users.name AS developer_name 
          FROM Tickets 
          LEFT JOIN Projects ON Tickets.project_id = Projects.id
          LEFT JOIN Users ON Tickets.assigned_to = Users.id";

$result = mysqli_query($connection, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tickets</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Modal Styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            overflow: auto;
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            max-width: 600px;
            min-width: 300px;
        }

        .close-btn {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            float: right;
        }

        .close-btn:hover {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        table th {
            background-color: #007bff;
        }

        .action-link {
            text-decoration: none;
            color: #007BFF;
        }

        .create-ticket-btn {
            margin-bottom: 20px;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }

        .create-ticket-btn:hover {
            background-color: #218838;
        }

        /* Alert messages */
        .alert {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .alert.success {
            background-color: #28a745;
            color: white;
        }

        .alert.error {
            background-color: #dc3545;
            color: white;
        }
    </style>

    <script type="text/javascript">
        // Function to display success popup
        function showSuccessPopup(message) {
            alert(message);
        }
    </script>
</head>

<body>


    <div id="content-area">
        <!-- Success or Error Message -->
        <div id="message"></div>

        <!-- Create Ticket Button -->
        <button class="create-ticket-btn" onclick="openCreateTicketModal()">Create Ticket</button>

        <table>
            <thead>
                <tr>
                    <th>Ticket ID</th>
                    <th>Title</th>
                    <th>Project</th>
                    <th>Status</th>
                    <th>Assigned To</th> <!-- New column for Developer -->
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="ticketTable">
                <?php while ($ticket = mysqli_fetch_assoc($result)) { ?>
                    <tr id="ticket-<?php echo $ticket['id']; ?>">
                        <td><?php echo $ticket['id']; ?></td>
                        <td><?php echo $ticket['title']; ?></td>
                        <td><?php echo $ticket['project_name']; ?></td>
                        <td class="ticket-status"><?php echo ucfirst($ticket['status']); ?></td>
                        <td><?php echo $ticket['developer_name']; ?></td> <!-- Display Developer Name -->
                        <td>
                            <a href="#" class="view-ticket-link" data-id="<?php echo $ticket['id']; ?>">View</a> |
                            <a href="#" class="update-ticket-link" data-id="<?php echo $ticket['id']; ?>">Update</a> |
                            <a href="#" class="delete-ticket-link" data-id="<?php echo $ticket['id']; ?>">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>

        </table>
    </div>

    <!-- View Ticket Modal -->
    <div id="viewTicketModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeViewTicketModal()">&times;</span>
            <h2>Ticket Details</h2>
            <div id="ticketDetails">
                <!-- Ticket details will be loaded here dynamically -->
            </div>
        </div>
    </div>

    <!-- Update Ticket Modal -->
    <div id="updateTicketModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeUpdateTicketModal()">&times;</span>
            <h2>Update Ticket</h2>
            <form id="updateTicketForm">
                <input type="hidden" id="updateTicketId" name="ticketId">

                <label for="updateTicketStatus">Status:</label><br>
                <select id="updateTicketStatus" name="ticketStatus" required>
                    <option value="open">Open</option>
                    <option value="in-progress">In Progress</option>
                    <option value="resolved">Resolved</option>
                    <option value="closed">Closed</option>
                    <option value="on_hold">On Hold</option>
                </select><br><br>

                <label for="updateTicketProject">Project:</label><br>
                <select id="updateTicketProject" name="ticketProject" required>
                    <?php
                    // Fetch all projects for the dropdown
                    $projectQuery = "SELECT id, name FROM Projects WHERE status = 'active'";
                    $projectResult = mysqli_query($connection, $projectQuery);
                    while ($project = mysqli_fetch_assoc($projectResult)) {
                        echo "<option value='" . $project['id'] . "'>" . $project['name'] . "</option>";
                    }
                    ?>
                </select><br><br>

                <button type="submit">Update Ticket</button>
            </form>
        </div>
    </div>

    <!-- Create Ticket Modal -->
    <div id="createTicketModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeCreateTicketModal()">&times;</span>
            <h2>Create Ticket</h2>
            <form id="createTicketForm">
                <label for="ticketTitle">Title:</label><br>
                <input type="text" id="ticketTitle" name="ticketTitle" required><br><br>

                <label for="ticketDescription">Description:</label><br>
                <textarea id="ticketDescription" name="ticketDescription" required></textarea><br><br>

                <label for="ticketStatus">Status:</label><br>
                <select id="ticketStatus" name="ticketStatus" required>
                    <option value="open">Open</option>
                    <option value="in-progress">In Progress</option>
                    <option value="resolved">Resolved</option>
                    <option value="closed">Closed</option>
                    <option value="on_hold">On Hold</option>
                </select><br><br>

                <!-- Project Selection -->
                <label for="ticketProject">Project:</label><br>
                <select id="ticketProject" name="ticketProject" required>
                    <?php
                    // Fetch all projects for the dropdown
                    $projectQuery = "SELECT id, name FROM Projects WHERE status = 'active'";
                    $projectResult = mysqli_query($connection, $projectQuery);
                    while ($project = mysqli_fetch_assoc($projectResult)) {
                        echo "<option value='" . $project['id'] . "'>" . $project['name'] . "</option>";
                    }
                    ?>
                </select><br><br>

                <button type="submit">Create Ticket</button>
            </form>
        </div>
    </div>




    <script>
        // Open Update Ticket Modal
        $(document).on('click', '.update-ticket-link', function(e) {
            e.preventDefault();
            var ticketId = $(this).data('id');
            console.log("Clicked update for ticket ID:", ticketId);

            $.ajax({
                url: 'fetch-ticket-details.php',
                type: 'GET',
                data: {
                    ticket_id: ticketId
                },
                success: function(response) {
                    console.log('Raw response:', response);
                    var data = JSON.parse(response);
                    if (data.error) {
                        $('#message').html('<div class="alert error">' + data.error + '</div>');
                    } else {
                        var ticket = data;
                        $('#updateTicketId').val(ticket.id);
                        $('#updateTicketStatus').val(ticket.status);
                        $('#updateTicketModal').show();
                    }
                },
                error: function() {
                    $('#message').html('<div class="alert error">Error loading ticket details.</div>');
                }
            });
        });

        // Open Create Ticket Modal
        function openCreateTicketModal() {
            $('#createTicketModal').show();
        }

        // Close Create Ticket Modal
        function closeCreateTicketModal() {
            $('#createTicketModal').hide();
        }

        // Close View Ticket Modal
        function closeViewTicketModal() {
            $('#viewTicketModal').hide();
        }

        // Close Update Ticket Modal
        function closeUpdateTicketModal() {
            $('#updateTicketModal').hide();
        }

        // Create Ticket Form Submission
        $('#createTicketForm').on('submit', function(e) {
            e.preventDefault();

            var ticketTitle = $('#ticketTitle').val();
            var ticketDescription = $('#ticketDescription').val();
            var ticketStatus = $('#ticketStatus').val();
            var ticketProject = $('#ticketProject').val();
            $.ajax({
                url: 'create-ticket-action.php',
                type: 'POST',
                data: {
                    ticketTitle: ticketTitle,
                    ticketDescription: ticketDescription,
                    ticketStatus: ticketStatus,
                    ticketProject: ticketProject
                },
                success: function(response) {
                    console.log(response);
                    if (response && response.success) {
                        alert('Ticket created successfully!');
                    } else if (response && response.error) {
                        alert(response.error);
                    } else {
                        alert('Unexpected response format.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', status, error);
                    alert('An error occurred while creating the ticket.');
                }
            });
        });

        // Open View Ticket Modal
        $(document).on('click', '.view-ticket-link', function(e) {
            e.preventDefault();
            var ticketId = $(this).data('id');
            console.log("Clicked view for ticket ID:", ticketId);

            $.ajax({
                url: 'fetch-ticket-details.php',
                type: 'GET',
                data: {
                    ticket_id: ticketId
                },
                success: function(response) {
                    console.log('Raw response:', response);
                    var data = JSON.parse(response);
                    if (data.error) {
                        $('#message').html('<div class="alert error">' + data.error + '</div>');
                    } else {
                        var ticket = data;
                        var ticketDetailsHTML = `
                    <p><strong>Ticket ID:</strong> ${ticket.id}</p>
                    <p><strong>Title:</strong> ${ticket.title}</p>
                    <p><strong>Description:</strong> ${ticket.description}</p>
                    <p><strong>Status:</strong> ${ticket.status}</p>
                    <p><strong>Project:</strong> ${ticket.project_name}</p>
                    <p><strong>Created At:</strong> ${ticket.created_at}</p>
                    <p><strong>Updated At:</strong> ${ticket.updated_at}</p>
                `;
                        // $('#ticketDetails').html(ticketDetailsHTML);
                        // $('#viewTicketModal').show();

                        // Load ticket details
                        $('#ticketDetails').html(ticketDetailsHTML);

                        // Load comments
                        $.ajax({
                            url: 'fetch-comments.php',
                            type: 'GET',
                            data: {
                                ticket_id: ticketId
                            },
                            success: function(response) {
                                $('#ticketDetails').append(`
                                    <h3>Comments</h3>
                                    <div id="commentsSection">${response}</div>
                                    <h4>Post a Comment</h4>
                                    <form id="commentForm">
                                        <textarea name="comment" id="commentText" rows="4" style="width:100%;" required></textarea><br><br>
                                        <input type="hidden" name="ticket_id" value="${ticketId}">
                                        <button type="submit">Post Comment</button>
                                    </form>
                                `);
                            }
                        });

                        $('#viewTicketModal').show();

                    }
                },
                error: function() {
                    $('#message').html('<div class="alert error">Error loading ticket details.</div>');
                }
            });
        });

        // JS to handle comment posting
        $(document).on('submit', '#commentForm', function(e) {
            e.preventDefault();
            var commentText = $('#commentText').val();
            var ticketId = $(this).find('input[name="ticket_id"]').val();

            $.ajax({
                url: 'post-comment.php',
                type: 'POST',
                data: {
                    ticket_id: ticketId,
                    comment: commentText
                },
                success: function(response) {
                    $('#commentsSection').append(response); // add new comment without reloading
                    $('#commentText').val('');
                }
            });
        });


        // Close View Ticket Modal
        function closeViewTicketModal() {
            $('#viewTicketModal').hide();
        }

        // Update Ticket Form Submission
        $('#updateTicketForm').on('submit', function(e) {
            e.preventDefault();

            var ticketId = $('#updateTicketId').val();
            var ticketStatus = $('#updateTicketStatus').val();
            var ticketProject = $('#updateTicketProject').val();

            $.ajax({
                url: 'update-ticket-action.php',
                type: 'POST',
                data: {
                    ticketId: ticketId,
                    ticketStatus: ticketStatus,
                    ticketProject: ticketProject
                },
                success: function(response) {
                    var data = JSON.parse(response);
                    if (data.success) {
                        $('#message').html('<div class="alert success">' + data.success + '</div>');
                        location.reload();
                    } else {
                        $('#message').html('<div class="alert error">' + data.error + '</div>');
                    }
                },
                error: function() {
                    $('#message').html('<div class="alert error">Error updating ticket.</div>');
                }
            });
        });

        // Delete Ticket
        $(document).on('click', '.delete-ticket-link', function(e) {
            e.preventDefault();
            var ticketId = $(this).data('id');
            console.log("Clicked delete for ticket ID:", ticketId);

            if (confirm('Are you sure you want to delete this ticket?')) {
                $.ajax({
                    url: 'delete-ticket-action.php',
                    type: 'POST',
                    data: {
                        ticket_id: ticketId
                    },
                    success: function(response) {
                        console.log('Raw response:', response);
                        var data = JSON.parse(response);
                        if (data.success) {
                            $('#message').html('<div class="alert success">' + data.success + '</div>');
                            $('#ticket-' + ticketId).remove();
                        } else {
                            $('#message').html('<div class="alert error">' + data.error + '</div>');
                        }
                    },
                    error: function() {
                        $('#message').html('<div class="alert error">Error deleting ticket.</div>');
                    }
                });
            }
        });
    </script>



</body>

</html>