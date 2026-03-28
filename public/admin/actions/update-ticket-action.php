<?php
session_start();
header('Content-Type: application/json'); // ✅ Ensure browser interprets as JSON
include('../../../config/config.php');

// 1. 🛡️ Robust Security Layer
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Critical: Unauthorized access attempt blocked.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method.']);
    exit();
}

// 2. 🔍 Input Extraction & Sanitization
$ticketId      = isset($_POST['ticketId']) ? (int)$_POST['ticketId'] : 0;
$ticketStatus  = isset($_POST['ticketStatus']) ? strtolower(trim($_POST['ticketStatus'])) : '';
$ticketProject = isset($_POST['ticketProject']) ? (int)$_POST['ticketProject'] : 0;

// 3. 🚦 Validation Logic
$validStatuses = ['open', 'in-progress', 'resolved', 'closed', 'on_hold'];

if ($ticketId <= 0 || $ticketProject <= 0) {
    echo json_encode(['error' => 'Invalid Ticket or Project Reference.']);
    exit();
}

if (!in_array($ticketStatus, $validStatuses)) {
    echo json_encode(['error' => "Invalid status: '$ticketStatus' is not recognized by the system."]);
    exit();
}

// 4. 🚀 Execute Atomic Update
$query = "UPDATE Tickets SET status = ?, project_id = ? WHERE id = ?";
$stmt = mysqli_prepare($connection, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'sii', $ticketStatus, $ticketProject, $ticketId);

    if (mysqli_stmt_execute($stmt)) {
        // Prepare a rich response for the frontend
        echo json_encode([
            'success'   => true,
            'message'   => "Ticket #$ticketId successfully synchronized.",
            'new_state' => [
                'status' => ucfirst(str_replace('_', ' ', $ticketStatus)),
                'badge_class' => getStatusBadgeClass($ticketStatus) // Optional helper
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Database Sync Failed: ' . mysqli_error($connection)]);
    }
    mysqli_stmt_close($stmt);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'System Failure: Could not prepare update statement.']);
}

// Helper for UI consistency
function getStatusBadgeClass($status) {
    return [
        'open'        => 'bg-blue-100 text-blue-700',
        'in-progress' => 'bg-amber-100 text-amber-700',
        'resolved'    => 'bg-emerald-100 text-emerald-700',
        'closed'      => 'bg-slate-100 text-slate-700',
        'on_hold'     => 'bg-rose-100 text-rose-700'
    ][$status] ?? 'bg-slate-100';
}
?>