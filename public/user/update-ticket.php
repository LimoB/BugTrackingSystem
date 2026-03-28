<?php
session_start();
// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login/index.php");
    exit();
}

include('../../config/config.php');
$base_url = "/php-bugtracking-system/";

// Validate Ticket ID
if (!isset($_GET['ticket_id']) || !is_numeric($_GET['ticket_id'])) {
    header("Location: view-tickets.php");
    exit();
}

$ticket_id = $_GET['ticket_id'];
$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Fetch ticket details securely
$stmt = $connection->prepare("SELECT * FROM Tickets WHERE id = ? AND (created_by = ? OR assigned_to = ?)");
$stmt->bind_param("iii", $ticket_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Ticket not found or permission denied.");
}
$ticket = $result->fetch_assoc();

// Handle Form Submission
if (isset($_POST['update_ticket'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $status = $_POST['status'];

    if (empty($title) || empty($description)) {
        $message = "Title and Description are required.";
        $message_type = "error";
    } else {
        $update_stmt = $connection->prepare("UPDATE Tickets SET title = ?, description = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $update_stmt->bind_param("sssi", $title, $description, $status, $ticket_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['message'] = "Ticket updated successfully!";
            $_SESSION['message_type'] = "success";
            header("Location: view-tickets.php");
            exit();
        } else {
            $message = "Update failed: " . $connection->error;
            $message_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Ticket #<?php echo $ticket_id; ?> | Zappr</title>
    <link href="<?php echo $base_url; ?>dist/output.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@0.344.0/dist/umd/lucide.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex min-h-screen">

    <?php include('header.php'); ?>

    <main class="flex-grow p-6 lg:p-10 flex flex-col items-center">
        
        <div class="w-full max-w-2xl">
            <a href="view-tickets.php" class="inline-flex items-center gap-2 text-sm font-bold text-slate-400 hover:text-blue-600 transition mb-6 group">
                <i data-lucide="chevron-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"></i>
                Back to List
            </a>

            <div class="mb-8">
                <div class="flex items-center gap-3 mb-2">
                    <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-xs font-bold rounded-full uppercase tracking-widest">
                        Editing Ticket #<?php echo $ticket_id; ?>
                    </span>
                </div>
                <h1 class="text-3xl font-extrabold tracking-tight">Modify Issue Details</h1>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-200 dark:border-slate-800 shadow-xl shadow-slate-200/40 dark:shadow-none p-8">
                
                <form action="update-ticket.php?ticket_id=<?php echo $ticket_id; ?>" method="POST" class="space-y-6">
                    
                    <div>
                        <label class="block text-sm font-bold text-slate-500 mb-2 ml-1">Title</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($ticket['title']); ?>" required 
                               class="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-600 transition-all outline-none font-semibold">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-500 mb-2 ml-1">Description</label>
                        <textarea name="description" rows="5" required 
                                  class="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-600 transition-all outline-none resize-none"><?php echo htmlspecialchars($ticket['description']); ?></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-500 mb-2 ml-1">Status Update</label>
                        <div class="relative">
                            <select name="status" required 
                                    class="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-600 transition-all outline-none appearance-none cursor-pointer font-medium">
                                <option value="open" <?php echo $ticket['status'] == 'open' ? 'selected' : ''; ?>>🔵 Open</option>
                                <option value="in-progress" <?php echo $ticket['status'] == 'in-progress' ? 'selected' : ''; ?>>🟡 In Progress</option>
                                <option value="resolved" <?php echo $ticket['status'] == 'resolved' ? 'selected' : ''; ?>>🟢 Resolved</option>
                                <option value="closed" <?php echo $ticket['status'] == 'closed' ? 'selected' : ''; ?>>⚪ Closed</option>
                                <option value="on_hold" <?php echo $ticket['status'] == 'on_hold' ? 'selected' : ''; ?>>🟠 On Hold</option>
                            </select>
                            <i data-lucide="chevron-down" class="w-4 h-4 absolute right-5 top-4.5 text-slate-400 pointer-events-none"></i>
                        </div>
                    </div>

                    <div class="pt-4 flex gap-3">
                        <button type="submit" name="update_ticket" 
                                class="flex-grow bg-blue-600 text-white py-4 rounded-xl font-bold hover:bg-blue-700 transition-all shadow-lg shadow-blue-200 dark:shadow-none flex items-center justify-center gap-2">
                            <i data-lucide="save" class="w-5 h-5"></i>
                            Save Changes
                        </button>
                        <a href="view-tickets.php" class="px-6 py-4 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-xl font-bold hover:bg-slate-200 transition-all">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        lucide.createIcons();

        // Show Error if validation fails
        <?php if ($message_type === 'error'): ?>
        Swal.fire({
            icon: 'error',
            title: 'Wait!',
            text: '<?php echo $message; ?>',
            confirmButtonColor: '#2563eb'
        });
        <?php endif; ?>
    </script>
</body>
</html>