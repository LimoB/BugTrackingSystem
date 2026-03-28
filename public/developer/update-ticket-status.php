<?php
session_start();
include('../../config/config.php');

// ✅ 1. Quick Security & Role Check
if (!isset($_SESSION['user_id']) || !in_array(strtolower($_SESSION['role']), ['developer', 'admin'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized access.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_id = intval($_POST['ticket_id']);
    $user_id = $_SESSION['user_id'];
    
    // ✅ 2. Sanitize and Format Status
    $new_status = strtolower(trim($_POST['status'] ?? ''));
    $new_status = str_replace(' ', '-', $new_status); // Use hyphens to match our CSS classes

    $allowed = ['open', 'in-progress', 'resolved', 'closed', 'on-hold'];

    if (!in_array($new_status, $allowed, true)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Invalid status value provided.']);
        exit;
    }

    // ✅ 3. Permission Check: Ensure the dev is assigned to this ticket 
    // (Optional: Remove this check if you want devs to be able to "grab" any ticket)
    /*
    $check_stmt = $connection->prepare("SELECT id FROM Tickets WHERE id=? AND assigned_to=?");
    $check_stmt->bind_param("ii", $ticket_id, $user_id);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows === 0 && $_SESSION['role'] !== 'admin') {
        echo json_encode(['success' => false, 'error' => 'You are not assigned to this ticket.']);
        exit;
    }
    */

    // ✅ 4. Execute the Update
    $stmt = $connection->prepare("UPDATE Tickets SET status=?, updated_at=CURRENT_TIMESTAMP WHERE id=?");
    if (!$stmt) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Database preparation error.']);
        exit;
    }

    $stmt->bind_param("si", $new_status, $ticket_id);

    header('Content-Type: application/json'); // Always return JSON for AJAX
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Status updated to ' . ucwords(str_replace('-', ' ', $new_status)),
            'new_status' => $new_status
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Query execution failed: ' . $stmt->error]);
    }
    
    $stmt->close();
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}