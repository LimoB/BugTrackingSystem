<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login/index.php");
    exit();
}

include('../../config/config.php');

$user_id = $_SESSION['user_id'];

// Get the filter and search parameters
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($connection, $_GET['status']) : '';
$search_query = isset($_GET['search']) ? mysqli_real_escape_string($connection, $_GET['search']) : '';
$sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'id'; // Default to sorting by ID

// Add validation for sort columns to prevent SQL injection
$valid_sort_columns = ['id', 'title', 'status', 'created_by', 'assigned_by', 'assigned_to'];
if (!in_array($sort_column, $valid_sort_columns)) {
    $sort_column = 'id'; // Default to 'id' if invalid column is selected
}

// Start building the SQL query
$query = "SELECT T.id, T.title, T.description, T.status, T.created_by, T.assigned_by, T.assigned_to, T.project_id,
                 U.name AS created_by_name, A.name AS assigned_by_name, B.name AS assigned_to_name, P.name AS project_name
          FROM Tickets T
          LEFT JOIN Users U ON T.created_by = U.id
          LEFT JOIN Users A ON T.assigned_by = A.id
          LEFT JOIN Users B ON T.assigned_to = B.id
          LEFT JOIN Projects P ON T.project_id = P.id
          WHERE T.assigned_to = '$user_id' OR T.created_by = '$user_id'";

// Apply the status filter if it's set
if ($status_filter) {
    $query .= " AND T.status = '$status_filter'";
}

// Apply the search query if it's set
if ($search_query) {
    $query .= " AND (T.title LIKE '%$search_query%' OR T.description LIKE '%$search_query%')";
}

// Add sorting by ticket column (ID, title, status, created_by, etc.)
$query .= " ORDER BY T.$sort_column DESC";

// Execute the query
$result = mysqli_query($connection, $query);

if (!$result) {
    die("Query error: " . mysqli_error($connection));
}

// Handle comment insertion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $ticket_id = $_POST['ticket_id'];
    $comment = mysqli_real_escape_string($connection, $_POST['comment']);

    // Insert comment into the database
    $insert_comment_query = "INSERT INTO Comments (ticket_id, user_id, comment) 
                             VALUES ('$ticket_id', '$user_id', '$comment')";
    if (mysqli_query($connection, $insert_comment_query)) {
        echo "<p>Comment added successfully!</p>";
    } else {
        echo "<p>Error adding comment: " . mysqli_error($connection) . "</p>";
    }
}

// Fetch comments for a specific ticket
if (isset($_GET['ticket_id'])) {
    $ticket_id = $_GET['ticket_id'];
    $comment_query = "SELECT C.comment, U.name AS user_name, C.created_at
                      FROM Comments C
                      LEFT JOIN Users U ON C.user_id = U.id
                      WHERE C.ticket_id = '$ticket_id'
                      ORDER BY C.created_at ASC";
    $comments_result = mysqli_query($connection, $comment_query);

    // Check if there are any comments
    $comments = [];
    while ($row = mysqli_fetch_assoc($comments_result)) {
        $comments[] = [
            'user_name' => $row['user_name'],
            'comment' => $row['comment'],
            'created_at' => $row['created_at']
        ];
    }

    // Return the comments as JSON
    header('Content-Type: application/json');
    echo json_encode(['comments' => $comments]);
    exit(); // Make sure to stop further execution
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tickets</title>

    <style>
        /* Global Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
        }

        h2 {
            color: #007bff;
            margin-bottom: 20px;
            text-align: center;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #f8f9fc;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #007bff;
            color: white;
            font-size: 16px;
        }

        tr:nth-child(even) {
            background-color: #f1f1f1;
        }

        tr:hover {
            background-color: #e0e0e0;
        }

        .status {
            font-weight: bold;
            padding: 4px 8px;
            border-radius: 4px;
            color: white;
        }

        .open {
            background-color: #007bff;
        }

        .in-progress {
            background-color: #ffc107;
        }

        .resolved {
            background-color: #28a745;
        }

        .closed {
            background-color: #6c757d;
        }

        .on_hold {
            background-color: #007bff;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0, 0, 0);
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .comment-box {
            margin-bottom: 10px;
            padding: 10px;
            background-color: #f8f9fc;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .add-comment textarea {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            border-radius: 4px;
            border: 1px solid #ddd;
            margin-bottom: 10px;
        }

        .add-comment button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .add-comment button:hover {
            background-color: #007bff;
        }

    </style>
</head>

<body>

    <?php include('header.php'); ?>

    <h2>My Tickets</h2>

    <div class="ticket-container">
        <table>
            <thead>
                <tr>
                    <th><a href="?sort=id">ID</a></th>
                    <th><a href="?sort=title">Title</a></th>
                    <th><a href="?sort=description">Description</a></th>
                    <th><a href="?sort=status">Status</a></th>
                    <th><a href="?sort=created_by">Created By</a></th>
                    <!-- <th><a href="?sort=assigned_by">Assigned By</a></th> -->
                    <th><a href="?sort=assigned_to">Assigned To</a></th>
                    <th>Project</th>
                    <th>Comments</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                            <td>
                                <span class="status <?php echo strtolower(str_replace('-', '_', $row['status'])); ?>">
                                    <?php echo ucfirst($row['status']); ?>
                                </span>
                            </td>
                            <td><?php echo $row['created_by_name'] ?: 'Not Assigned Yet'; ?></td>
                            <td><?php echo $row['assigned_by_name'] ?: 'Not Assigned Yet'; ?></td>
                            <td><?php echo $row['assigned_to_name'] ?: 'Not Assigned Yet'; ?></td>
                            <td><?php echo $row['project_name'] ?: 'No Project'; ?></td>
                            <td><a href="javascript:void(0);" onclick="openModal(<?php echo $row['id']; ?>)">View Comments</a></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9">No tickets found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal for comments -->
    <div id="commentsModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>Comments for Ticket <span id="ticketId"></span></h3>
            <div id="commentsContainer"></div>
            <div class="add-comment">
                <textarea id="commentInput" placeholder="Enter your comment here..."></textarea>
                <button onclick="addComment()">Add Comment</button>
            </div>
        </div>
    </div>

    <script>
        // Open the modal
        function openModal(ticketId) {
            document.getElementById('ticketId').innerText = ticketId;
            document.getElementById('commentsModal').style.display = "block";
            fetchComments(ticketId);
        }

        // Close the modal
        function closeModal() {
            document.getElementById('commentsModal').style.display = "none";
        }

        // Fetch comments for the ticket
        function fetchComments(ticketId) {
            fetch('view-tickets.php?ticket_id=' + ticketId)
                .then(response => response.json()) // Ensure the response is JSON
                .then(data => {
                    const commentsContainer = document.getElementById('commentsContainer');
                    commentsContainer.innerHTML = ''; // Clear the container before adding new comments

                    data.comments.forEach(comment => {
                        const commentDiv = document.createElement('div');
                        commentDiv.classList.add('comment-box');
                        commentDiv.innerHTML = `<strong>${comment.user_name}</strong>: ${comment.comment} <br><small>${comment.created_at}</small>`;
                        commentsContainer.appendChild(commentDiv);
                    });
                })
                .catch(error => {
                    console.error('Error fetching comments:', error);
                    alert('Failed to load comments.');
                });
        }

        // Add a comment
        function addComment() {
            const ticketId = document.getElementById('ticketId').innerText;
            const comment = document.getElementById('commentInput').value; // Grabbing the value of the comment input field

            if (!comment) {
                alert('Please enter a comment.');
                return;
            }

            const formData = new FormData();
            formData.append('ticket_id', ticketId);
            formData.append('comment', comment);

            fetch('add-comment.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Comment added successfully!');
                        fetchComments(ticketId); // Reload comments after adding a new one
                        document.getElementById('commentInput').value = ''; // Clear the input field
                    } else {
                        alert(data.error); // Show the error message if there's an issue
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while adding the comment.');
                });
        }
    </script>

</body>

</html>