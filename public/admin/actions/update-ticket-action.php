<?php
/**
 * File: admin/actions/update-ticket-action.php
 * Purpose: Atomic synchronization of ticket state, priority, assignment, and category.
 * Verified with MariaDB Schema: [id, status, priority, assigned_to, assigned_by, category_id]
 */
ob_start(); 
session_start();
header('Content-Type: application/json');

require_once('../../../config/config.php');

// 1. 🛡️ Admin Security Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    ob_clean();
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Security Violation: Kernel access denied.']);
    exit();
}

// 2. 🔍 Input Extraction & Mapping
$ticketId       = isset($_POST['ticketId']) ? (int)$_POST['ticketId'] : 0;
$ticketStatus   = isset($_POST['ticketStatus']) ? strtolower(trim($_POST['ticketStatus'])) : '';
$ticketPriority = isset($_POST['ticketPriority']) ? strtolower(trim($_POST['ticketPriority'])) : '';
$assignedTo     = !empty($_POST['assignedTo']) ? (int)$_POST['assignedTo'] : null;
$categoryId     = !empty($_POST['categoryId']) ? (int)$_POST['categoryId'] : null;

// Map current logged-in Admin to assigned_by
$adminId = $_SESSION['user_id'] ?? $_SESSION['id'] ?? 0;

// 3. 🚦 Integrity Validation
$validStatuses = ['open', 'in-progress', 'resolved', 'closed', 'on-hold'];
if (!in_array($ticketStatus, $validStatuses)) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => "Rejection: '$ticketStatus' is not a valid state."]);
    exit();
}

if ($ticketId <= 0) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Reference Error: Valid Ticket ID required.']);
    exit();
}

// 4. 🚀 Database Operations
try {
    // Constructing the update query dynamically to handle NULL values for category and assignment
    $query = "UPDATE Tickets SET 
                status = ?, 
                priority = ?, 
                assigned_to = ?, 
                assigned_by = ?, 
                category_id = ? 
              WHERE id = ? LIMIT 1";

    $stmt = $connection->prepare($query);

    if (!$stmt) {
        throw new Exception("SQL Preparation Error: " . $connection->error);
    }

    // Bind parameters: 'ssiiii' (string, string, int, int, int, int)
    // Note: PHP mysqli bind_param handles null values correctly as long as the variable is null
    $stmt->bind_param('ssiiii', 
        $ticketStatus, 
        $ticketPriority, 
        $assignedTo, 
        $adminId, 
        $categoryId, 
        $ticketId
    );

    if ($stmt->execute()) {
        
        // --- 📝 Audit Trail / Activity Log ---
        $log_type = "TICKET_UPDATE";
        $assignee_info = $assignedTo ? "Dev #$assignedTo" : "Unassigned";
        $cat_info = $categoryId ? "Category #$categoryId" : "Uncategorized";
        
        $log_desc = "Admin #$adminId synced Ticket #$ticketId: [$ticketStatus | $ticketPriority] Assigned to: $assignee_info, Class: $cat_info";
        
        $log_query = "INSERT INTO activity_log (user_id, action_type, description, ticket_id, created_at) VALUES (?, ?, ?, ?, NOW())";
        
        if ($log_stmt = $connection->prepare($log_query)) {
            $log_stmt->bind_param("issi", $adminId, $log_type, $log_desc, $ticketId);
            $log_stmt->execute();
            $log_stmt->close();
        }

        // 5. 📡 Payload Response
        ob_clean();
        echo json_encode([
            'success' => true,
            'message' => "Registry Synchronized: Ticket #$ticketId updated and classified."
        ]);
    } else {
        throw new Exception("Execution Failed: " . $stmt->error);
    }
    
    $stmt->close();

} catch (Exception $e) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Kernel Sync Error: ' . $e->getMessage()]);
}

$connection->close();
ob_end_flush();
exit();