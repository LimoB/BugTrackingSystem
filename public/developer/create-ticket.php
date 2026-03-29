<?php
session_start();
include('../../config/config.php');

// 🛡️ Security Check
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'developer') {
    die("<div class='p-10 text-center font-black text-rose-500 uppercase tracking-widest'>Access Denied: Terminal Restricted</div>");
}

$user_id = $_SESSION['user_id'];

// --- 🛠️ Logic: Handle Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_ticket'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $assigned_to = intval($_POST['assigned_to']);
    $status = $_POST['status'] ?? 'open';
    $priority = $_POST['priority'] ?? 'medium';
    $project_id = !empty($_POST['project_id']) ? intval($_POST['project_id']) : null;

    if (empty($title) || empty($assigned_to)) {
        echo "<script>Swal.fire('Validation Error', 'Title and Assignee are mandatory.', 'warning');</script>";
    } else {
        $stmt = $connection->prepare("INSERT INTO Tickets (title, description, assigned_to, status, priority, created_by, project_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssii", $title, $description, $assigned_to, $status, $priority, $user_id, $project_id);

        if ($stmt->execute()) {
            echo "<script>
                    Swal.fire({ 
                        icon: 'success', 
                        title: 'Ticket Deployed', 
                        text: 'Task ID #" . $connection->insert_id . " is now live.', 
                        timer: 1500, 
                        showConfirmButton: false,
                        background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#fff',
                        color: document.documentElement.classList.contains('dark') ? '#fff' : '#000'
                    });
                    loadPage('assigned-tickets'); 
                  </script>";
            exit();
        } else {
            echo "<script>Swal.fire('Database Error', '" . addslashes($connection->error) . "', 'error');</script>";
        }
    }
    exit();
}

// --- 🔍 Queries: Data for Selects ---
$userList = mysqli_query($connection, "SELECT id, name, role FROM Users WHERE role IN ('developer', 'admin') ORDER BY name ASC");
$projectList = mysqli_query($connection, "SELECT id, name FROM Projects ORDER BY name ASC");
?>

<div class="max-w-5xl mx-auto animate-fade-in pb-20">
    <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white shadow-lg">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                </div>
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400">New Internal Issue</span>
            </div>
            <h1 class="text-4xl font-black tracking-tighter text-slate-900 dark:text-white uppercase">Initialize <span class="text-indigo-600 text-outline">Task.</span></h1>
        </div>
        <div class="text-right hidden md:block">
            <p class="text-[10px] font-mono text-slate-400">AUTHOR_ID: #<?php echo str_pad($user_id, 4, '0', STR_PAD_LEFT); ?></p>
            <p class="text-[10px] font-mono text-slate-400">TIMESTAMP: <?php echo date('Y-m-d H:i'); ?></p>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[2.5rem] shadow-2xl shadow-slate-200/50 dark:shadow-none overflow-hidden">
        <form id="devCreateTicketForm" class="p-8 md:p-12 space-y-8">
            <input type="hidden" name="create_ticket" value="true">
            
            <div class="space-y-2">
                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Issue Summary</label>
                <input type="text" name="title" placeholder="e.g., Critical: API Latency on /v1/auth" required 
                       class="w-full px-6 py-5 bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-bold text-slate-700 dark:text-slate-200 placeholder:text-slate-300 dark:placeholder:text-slate-600">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Assigned Project</label>
                    <div class="relative">
                        <select name="project_id" class="w-full px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 rounded-2xl outline-none appearance-none cursor-pointer font-bold text-sm">
                            <option value="">-- SYSTEM CORE --</option>
                            <?php while($p = mysqli_fetch_assoc($projectList)) echo "<option value='{$p['id']}'>".htmlspecialchars($p['name'])."</option>"; ?>
                        </select>
                        <i data-lucide="chevron-down" class="absolute right-5 top-4 w-4 h-4 text-slate-400 pointer-events-none"></i>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Priority Level</label>
                    <div class="relative">
                        <select name="priority" class="w-full px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 rounded-2xl outline-none appearance-none cursor-pointer font-bold text-sm text-rose-500">
                            <option value="low">Low (Standard)</option>
                            <option value="medium" selected>Medium (Moderate)</option>
                            <option value="high">High (Immediate)</option>
                            <option value="critical">Critical (Blocker)</option>
                        </select>
                        <i data-lucide="zap" class="absolute right-5 top-4 w-4 h-4 text-slate-400 pointer-events-none"></i>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Target Assignee</label>
                    <div class="relative">
                        <select name="assigned_to" required class="w-full px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 rounded-2xl outline-none appearance-none cursor-pointer font-bold text-sm">
                            <option value="">Select Resource...</option>
                            <?php mysqli_data_seek($userList, 0); ?>
                            <?php while($u = mysqli_fetch_assoc($userList)): ?>
                                <option value="<?php echo $u['id']; ?>" <?php echo ($u['id'] == $user_id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($u['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <i data-lucide="user" class="absolute right-5 top-4 w-4 h-4 text-slate-400 pointer-events-none"></i>
                    </div>
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Technical Context</label>
                <textarea name="description" rows="5" placeholder="Steps to reproduce, environment details, or task requirements..." 
                          class="w-full px-6 py-5 bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 rounded-3xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all resize-none font-medium text-slate-600 dark:text-slate-300"></textarea>
            </div>

            <div class="pt-10 flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-3">
                    <div class="flex -space-x-3">
                        <div class="w-8 h-8 rounded-full bg-indigo-500 border-2 border-white dark:border-slate-900 flex items-center justify-center text-[10px] text-white font-bold italic">?</div>
                        <div class="w-8 h-8 rounded-full bg-slate-200 border-2 border-white dark:border-slate-900"></div>
                    </div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">All submissions are logged to the audit stream.</p>
                </div>

                <div class="flex gap-4 w-full sm:w-auto">
                    <button type="button" onclick="loadPage('dashboard_home')" class="flex-grow sm:flex-grow-0 px-8 py-4 text-slate-500 font-bold hover:bg-slate-100 dark:hover:bg-slate-800 rounded-2xl transition-all uppercase text-[10px] tracking-widest">
                        Abort
                    </button>
                    <button type="submit" class="flex-grow sm:flex-grow-0 px-12 py-4 bg-slate-900 dark:bg-white dark:text-slate-900 text-white rounded-2xl font-black hover:bg-indigo-600 dark:hover:bg-indigo-500 dark:hover:text-white transition-all flex items-center justify-center gap-3 uppercase text-[10px] tracking-widest shadow-xl shadow-indigo-100 dark:shadow-none">
                        <i data-lucide="rocket" class="w-4 h-4"></i>
                        Deploy Ticket
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    lucide.createIcons();

    // 🚀 AJAX Handler: Submit without reload
    $('#devCreateTicketForm').off('submit').on('submit', function(e) {
        e.preventDefault();
        
        const btn = $(this).find('button[type="submit"]');
        const originalHtml = btn.html();
        
        // Visual loading state
        btn.prop('disabled', true).html('<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> ENCRYPTING...');
        lucide.createIcons();

        $.ajax({
            url: 'create-ticket.php',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                // Since response contains a <script> tag with Swal and loadPage, 
                // appending it to a hidden div or the content area executes it.
                $('#content-area').append(response);
            },
            error: function() {
                Swal.fire('Error', 'Communication with the server failed.', 'error');
                btn.prop('disabled', false).html(originalHtml);
                lucide.createIcons();
            }
        });
    });
</script>