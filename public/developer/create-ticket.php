<?php
session_start();
include('../../config/config.php');

// ✅ Security Check
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'developer') {
    die("Unauthorized access.");
}

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Handle form submission
if (isset($_POST['create_ticket'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $assigned_to = $_POST['assigned_to'];
    $status = $_POST['status'];
    $project_id = $_POST['project_id'] ?? null; // Added project support

    if (empty($title) || empty($assigned_to)) {
        $message = "Title and Assignee are required.";
        $message_type = "error";
    } else {
        $stmt = $connection->prepare("INSERT INTO Tickets (title, description, assigned_to, status, created_by, project_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiii", $title, $description, $assigned_to, $status, $user_id, $project_id);

        if ($stmt->execute()) {
            // Since this is loaded via AJAX, we return a success script or message
            echo "<script>
                    Swal.fire({ icon: 'success', title: 'Ticket Created', text: 'Task has been assigned.', timer: 2000, showConfirmButton: false });
                    loadPage('assigned-tickets'); 
                  </script>";
            exit();
        } else {
            $message = "Error: " . $connection->error;
            $message_type = "error";
        }
    }
}

// Fetch Users for Assignment
$userList = mysqli_query($connection, "SELECT id, name, role FROM Users WHERE role IN ('developer', 'admin') ORDER BY name ASC");
// Fetch Projects
$projectList = mysqli_query($connection, "SELECT id, name FROM Projects ORDER BY name ASC");
?>

<div class="max-w-4xl mx-auto animate-fade-in">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Create Task</h1>
            <p class="text-slate-500 dark:text-slate-400">Initialize a new ticket or technical task.</p>
        </div>
        <i data-lucide="plus-circle" class="w-10 h-10 text-indigo-500 opacity-20"></i>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[2rem] shadow-sm p-8">
        <form id="devCreateTicketForm" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-slate-600 dark:text-slate-400 mb-2 ml-1">Ticket Title</label>
                    <input type="text" name="title" placeholder="Brief summary of the issue..." required 
                           class="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-semibold">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-600 dark:text-slate-400 mb-2 ml-1">Project</label>
                    <select name="project_id" class="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl outline-none appearance-none cursor-pointer">
                        <option value="">Internal / No Project</option>
                        <?php while($p = mysqli_fetch_assoc($projectList)) echo "<option value='{$p['id']}'>{$p['name']}</option>"; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-600 dark:text-slate-400 mb-2 ml-1">Assign To</label>
                    <select name="assigned_to" required class="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl outline-none appearance-none cursor-pointer font-medium">
                        <option value="">Select Assignee</option>
                        <?php while($u = mysqli_fetch_assoc($userList)): ?>
                            <option value="<?php echo $u['id']; ?>" <?php echo ($u['id'] == $user_id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($u['name']); ?> (<?php echo ucfirst($u['role']); ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-slate-600 dark:text-slate-400 mb-2 ml-1">Technical Details</label>
                    <textarea name="description" rows="4" placeholder="Describe the bug, environment, or task requirements..." 
                              class="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all resize-none"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-600 dark:text-slate-400 mb-2 ml-1">Initial Status</label>
                    <select name="status" class="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl outline-none">
                        <option value="open">Open</option>
                        <option value="in-progress">In Progress</option>
                        <option value="on-hold">On Hold</option>
                    </select>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-4">
                <button type="button" onclick="loadPage('dashboard_home')" class="px-8 py-4 text-slate-500 font-bold hover:bg-slate-100 dark:hover:bg-slate-800 rounded-2xl transition-all">
                    Discard
                </button>
                <button type="submit" name="create_ticket" class="px-10 py-4 bg-indigo-600 text-white rounded-2xl font-bold hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-200 transition-all flex items-center gap-2">
                    <i data-lucide="send" class="w-5 h-5"></i>
                    Deploy Ticket
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Initialize Lucide icons for the newly loaded HTML
    lucide.createIcons();

    // AJAX Form Submission so the page doesn't refresh
    $('#devCreateTicketForm').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize() + '&create_ticket=true';
        
        $.ajax({
            url: 'create-ticket.php',
            method: 'POST',
            data: formData,
            success: function(response) {
                // If the response contains a script (like our redirect), execute it
                $('#content-area').html(response);
            }
        });
    });
</script>