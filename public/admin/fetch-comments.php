<?php
include('../../config/config.php');

if (isset($_GET['ticket_id'])) {
    $ticketId = intval($_GET['ticket_id']);

    $query = "SELECT Comments.comment, Comments.created_at, Users.name 
              FROM Comments 
              JOIN Users ON Comments.user_id = Users.id 
              WHERE Comments.ticket_id = ? 
              ORDER BY Comments.created_at ASC";

    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $ticketId);
    $stmt->execute();
    $result = $stmt->get_result();

    $comments = [];
    while ($row = $result->fetch_assoc()) {
        $comments[] = $row;
    }
    
    // Output styled comments
    echo '<div class="comments-section">';
    foreach ($comments as $comment) {
        echo '<div class="comment">';
        echo '<div class="comment-header">';
        echo '<span class="comment-author">' . htmlspecialchars($comment['name']) . '</span>';
        echo '<span class="comment-time">' . $comment['created_at'] . '</span>';
        echo '</div>';
        echo '<div class="comment-body">';
        echo '<p>' . htmlspecialchars($comment['comment']) . '</p>';
        echo '</div>';
        echo '</div>';
    }
    echo '</div>';
} else {
    echo json_encode(['error' => 'Ticket ID not provided.']);
}
?>
