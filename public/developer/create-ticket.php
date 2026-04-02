<?php
session_start();

// Enable Error Reporting for Debugging (Remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    include('../../config/config.php');
} catch (Exception $e) {
    die("Config Load Error: " . $e->getMessage());
}

/**
 * File: developer/create-ticket.php
 * Purpose: Task Deployment with Optional Self-Assignment.
 */

// 🛡️ 1. Security Check
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'developer') {
    http_response_code(403);
    die("<div class='p-10 text-center font-black text-rose-500 uppercase tracking-widest text-xs'>[CRITICAL_FAILURE] Access Denied</div>");
}

$user_id = (int)$_SESSION['user_id'];
$user_name = $_SESSION['name'] ?? 'Unknown Developer';

// --- 🛠️ 2. Logic: Handle Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_ticket'])) {
    
    try {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $assigned_to = isset($_POST['self_assign']) ? $user_id : null; 
        $status = 'open';
        $priority = $_POST['priority'] ?? 'medium';
        $project_id = !empty($_POST['project_id']) ? intval($_POST['project_id']) : null;
        $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : 1;

        if (empty($title)) {
            echo "<script>Swal.fire({ icon: 'warning', title: 'VALIDATION_ERROR', text: 'Title is mandatory.', background: '#0f172a', color: '#fff' });</script>";
        } else {
            /**
             * FIX: Removed 'updated_at' from the column list and values list 
             * because your MariaDB 'Tickets' table does not have that column.
             */
            $sql = "INSERT INTO Tickets (title, description, assigned_to, status, priority, created_by, project_id, category_id, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $connection->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $connection->error);
            }

            // Bind parameters: 5 strings (s), 3 integers (i)
            $stmt->bind_param("sssssiii", $title, $description, $assigned_to, $status, $priority, $user_id, $project_id, $category_id);

            if ($stmt->execute()) {
                $new_id = $connection->insert_id;
                
                // Activity Log logic
                $log_msg = "Dev $user_name deployed Ticket #$new_id" . ($assigned_to ? " (Self-Assigned)" : " (Unassigned)");
                
                // Query matches your activity_log table: user_id, action_type, description, ticket_id, created_at
                $log_stmt = $connection->prepare("INSERT INTO activity_log (user_id, action_type, description, ticket_id, created_at) VALUES (?, 'TICKET_DEPLOY', ?, ?, NOW())");
                $log_stmt->bind_param("isi", $user_id, $log_msg, $new_id);
                $log_stmt->execute();

                echo "<script>
                        Swal.fire({ 
                            icon: 'success', 
                            title: 'TICKET_DEPLOYED', 
                            text: 'Task ID #$new_id is now live.', 
                            timer: 2000, 
                            showConfirmButton: false,
                            background: '#0f172a',
                            color: '#fff'
                        });
                        loadPage('assigned-tickets'); 
                      </script>";
            } else {
                throw new Exception("Execute failed: " . $stmt->error);
            }
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo "DATABASE_EXCEPTION: " . $e->getMessage();
    }
    exit();
}

// --- 🔍 3. Queries for Dropdowns ---
$projectList = mysqli_query($connection, "SELECT id, name FROM Projects ORDER BY name ASC");
?>

<div class="max-w-5xl mx-auto animate-in fade-in slide-in-from-bottom-8 duration-700 pb-20">
    <div class="mb-12 flex flex-col md:flex-row md:items-end justify-between gap-6 px-2">
        <div>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-2xl shadow-indigo-500/40">
                    <i data-lucide="plus-circle" class="w-5 h-5"></i>
                </div>
                <span class="text-[10px] font-black uppercase tracking-[0.4em] text-slate-400">Deployment_Module_v2.2</span>
            </div>
            <h1 class="text-5xl font-black tracking-tighter text-slate-900 dark:text-white uppercase leading-none">
                New <span class="text-indigo-600">Ticket.</span>
            </h1>
        </div>
        <div class="text-right hidden md:block border-l-2 border-slate-100 dark:border-slate-800 pl-6">
            <p class="text-[10px] font-mono font-bold text-slate-400 uppercase tracking-widest mb-1">Status: <span class="text-emerald-500 font-black italic">CONNECTED</span></p>
            <p class="text-[10px] font-mono font-bold text-slate-400 uppercase tracking-widest">Operator: <span class="text-indigo-500"><?php echo htmlspecialchars($user_name); ?></span></p>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[3rem] shadow-2xl shadow-slate-200/50 dark:shadow-none overflow-hidden">
        <div class="h-2 bg-indigo-600 w-full"></div>
        
        <form id="devCreateTicketForm" class="p-10 md:p-16 space-y-10">
            <input type="hidden" name="create_ticket" value="true">
            
            <div class="space-y-3">
                <label class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 ml-2">Ticket Summary</label>
                <input type="text" name="title" placeholder="e.g., FIX: Daraja API Webhook Timeout" required 
                       class="w-full px-8 py-6 bg-slate-50 dark:bg-slate-800/40 border-2 border-transparent focus:border-indigo-600 rounded-3xl outline-none transition-all font-black text-lg text-slate-800 dark:text-white tracking-tight shadow-inner">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="space-y-3">
                    <label class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 ml-2">Target Node</label>
                    <div class="relative group/select">
                        <select name="project_id" class="w-full px-8 py-5 bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 rounded-2xl outline-none appearance-none cursor-pointer font-bold text-[11px] uppercase tracking-widest text-slate-600 dark:text-slate-300">
                            <option value="">-- GLOBAL SYSTEM --</option>
                            <?php while($p = mysqli_fetch_assoc($projectList)) echo "<option value='{$p['id']}'>".htmlspecialchars($p['name'])."</option>"; ?>
                        </select>
                        <i data-lucide="layers" class="absolute right-6 top-5 w-4 h-4 text-slate-400 pointer-events-none"></i>
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 ml-2">Priority Flag</label>
                    <div class="relative group/select">
                        <select name="priority" class="w-full px-8 py-5 bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 rounded-2xl outline-none appearance-none cursor-pointer font-black text-[11px] uppercase tracking-widest text-rose-500">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="critical">CRITICAL</option>
                        </select>
                        <i data-lucide="zap" class="absolute right-6 top-5 w-4 h-4 text-rose-400 pointer-events-none"></i>
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 ml-2">Resource Allocation</label>
                    <div class="w-full px-8 py-4 bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 rounded-2xl flex items-center justify-between">
                        <span class="font-black text-[10px] uppercase tracking-widest text-slate-500">Claim Ticket?</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="self_assign" value="1" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <label class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 ml-2">Technical Description</label>
                <textarea name="description" rows="5" placeholder="Define the technical scope..." 
                          class="w-full px-8 py-6 bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 rounded-[2.5rem] focus:border-indigo-600 outline-none transition-all resize-none font-medium text-slate-600 dark:text-slate-300 italic"></textarea>
            </div>

            <div class="pt-10 flex flex-col md:flex-row items-center justify-between gap-8 border-t border-slate-50 dark:border-slate-800/50">
                <div class="flex items-center gap-4 text-slate-400">
                    <i data-lucide="info" class="w-4 h-4"></i>
                    <p class="text-[10px] font-bold uppercase tracking-widest">Unclaimed tickets enter the global backlog.</p>
                </div>

                <div class="flex gap-4 w-full md:w-auto">
                    <button type="button" onclick="loadPage('dashboard_home')" class="px-10 py-5 text-slate-400 font-black hover:text-rose-500 transition-colors uppercase text-[10px] tracking-widest">
                        Cancel
                    </button>
                    <button type="submit" class="flex-grow md:flex-grow-0 px-16 py-5 bg-slate-900 dark:bg-indigo-600 text-white rounded-[1.5rem] font-black hover:shadow-2xl hover:shadow-indigo-500/30 transition-all flex items-center justify-center gap-4 uppercase text-[10px] tracking-[0.3em]">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        Deploy_Ticket
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    if (window.lucide) { lucide.createIcons(); }

    $('#devCreateTicketForm').off('submit').on('submit', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        const original = $btn.html();
        
        $btn.prop('disabled', true).html('<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> DEPLOYING...');
        if (window.lucide) { lucide.createIcons(); }

        $.ajax({
            url: 'create-ticket.php', 
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                // If response contains <script>, this appends and executes it
                $('#content-area').append(response);
            },
            error: function(xhr) {
                console.error("DEBUG_INFO:", xhr.responseText);
                
                Swal.fire({ 
                    icon: 'error', 
                    title: 'UPLINK_ERROR', 
                    text: 'Status ' + xhr.status + ': Check Console for DB details.', 
                    background: '#0f172a', 
                    color: '#fff' 
                });
                $btn.prop('disabled', false).html(original);
                if (window.lucide) { lucide.createIcons(); }
            }
        });
    });
</script>