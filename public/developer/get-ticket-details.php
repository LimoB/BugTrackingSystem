<?php
session_start();
include('../../config/config.php');

if (!isset($_GET['ticket_id'])) {
    echo "Ticket ID is missing.";
    exit();
}

if (!isset($_SESSION['user_id'])) {
    echo "User not logged in.";
    exit();
}

$ticket_id = intval($_GET['ticket_id']);
$user_id = intval($_SESSION['user_id']);

// Get ticket details including project name
$ticket_query = "SELECT t.*, p.name AS project_name 
                 FROM Tickets t 
                 LEFT JOIN Projects p ON t.project_id = p.id 
                 WHERE t.id=?";
$ticket_stmt = $connection->prepare($ticket_query);
$ticket_stmt->bind_param("i", $ticket_id);
$ticket_stmt->execute();
$ticket = $ticket_stmt->get_result()->fetch_assoc();

if (!$ticket) {
    echo "Ticket not found.";
    exit();
}

// Fetch comments
$comment_query = "SELECT c.comment, c.created_at, u.name 
                  FROM Comments c 
                  JOIN Users u ON c.user_id = u.id 
                  WHERE c.ticket_id = ?  -- Updated from issue_id to ticket_id
                  ORDER BY c.created_at DESC";
$comment_stmt = $connection->prepare($comment_query);
$comment_stmt->bind_param("i", $ticket_id);
$comment_stmt->execute();
$comments_result = $comment_stmt->get_result();
?>

<div>
    <h3>Ticket #<?php echo $ticket['id']; ?> - <?php echo htmlspecialchars($ticket['title']); ?></h3>
    <p><strong>Project:</strong> <?php echo htmlspecialchars($ticket['project_name'] ?? 'Unassigned'); ?></p>
    <p><strong>Description:</strong> <?php echo htmlspecialchars($ticket['description']); ?></p>
    <p><strong>Status:</strong> <?php echo ucfirst($ticket['status']); ?></p>

    <!-- Status Update -->
    <form id="update-status-form">
        <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
        <label for="status">Update Status:</label>
        <select name="status" id="status">
            <?php
            $statuses = ['open', 'in-progress', 'resolved', 'closed', 'on hold'];
            foreach ($statuses as $status) {
                echo "<option value=\"$status\" " . ($ticket['status'] === $status ? 'selected' : '') . ">$status</option>";
            }
            ?>
        </select>
        <button type="submit">Update Status</button>
        <div id="status-message" style="margin-top:10px; color:green;"></div>
    </form>

    <!-- Comments Section -->
    <h4>Comments</h4>
    <div id="comments-section">
        <?php
        if ($comments_result->num_rows > 0) {
            while ($comment = $comments_result->fetch_assoc()) {
                echo "<p><strong>{$comment['name']}</strong> ({$comment['created_at']}):<br>" . nl2br(htmlspecialchars($comment['comment'])) . "</p>";
            }
        } else {
            echo "<p>No comments yet.</p>";
        }
        ?>
    </div>

    <!-- Add Comment -->
    <form id="add-comment-form" style="margin-top: 20px;">
        <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
        <textarea name="comment" rows="3" placeholder="Add your comment..." required></textarea><br>
        <button type="submit">Post Comment</button>
        <div id="comment-message" style="margin-top:10px; color:green;"></div>
    </form>
</div>
