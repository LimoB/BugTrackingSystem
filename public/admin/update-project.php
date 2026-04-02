<?php
/**
 * File: admin/api/update-project.php
 * Purpose: Provides the interface and logic for modifying existing project configurations.
 */
session_start();
require_once('../../config/config.php');

// 1. 🛡️ Administrative Security Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Content-Type: application/json');
        die(json_encode(['error' => 'Unauthorized Access: Admin Clearance Required']));
    }
    header("Location: ../login/index.php");
    exit();
}

// 2. 🔍 Data Fetching Protocol
$project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;

if ($project_id <= 0) {
    die("<div class='p-12 text-center text-rose-500 font-black uppercase tracking-widest text-xs'>Invalid Reference ID</div>");
}

// Fetch current project state
$query = "SELECT * FROM Projects WHERE id = ? LIMIT 1";
$stmt = $connection->prepare($query);
$stmt->bind_param('i', $project_id);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();

if (!$project) {
    die("<div class='p-12 text-center text-slate-400 font-black uppercase tracking-widest text-xs'>Archive Entry Not Found</div>");
}
$stmt->close();

// 3. 🚀 Handle POST Synchronization (AJAX or Standard)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name        = trim($_POST['projectName']);
    $description = trim($_POST['projectDescription']);
    $status      = trim($_POST['status']);

    $update_query = "UPDATE Projects SET name = ?, description = ?, status = ?, updated_at = NOW() WHERE id = ?";
    $update_stmt = $connection->prepare($update_query);

    if ($update_stmt) {
        $update_stmt->bind_param('sssi', $name, $description, $status, $project_id);
        
        if ($update_stmt->execute()) {
            // Handle AJAX response for seamless dashboard updates
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Project configuration synchronized.']);
                exit();
            }
            header("Location: manage-projects.php?success=updated");
            exit();
        } else {
            $error = $connection->error;
        }
        $update_stmt->close();
    }
}
?>

<div class="animate-in fade-in slide-in-from-bottom-4 duration-500 max-w-4xl mx-auto py-6">
    <div class="flex items-center justify-between mb-10 px-4">
        <div class="flex items-center gap-5">
            <div class="bg-indigo-600 text-white p-4 rounded-[1.5rem] shadow-lg shadow-indigo-500/20">
                <i data-lucide="settings-2" class="w-7 h-7"></i>
            </div>
            <div>
                <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tighter">Project Settings</h1>
                <p class="text-[10px] text-slate-400 uppercase tracking-[0.2em] font-black">Ref: #PROJ-<?php echo str_pad($project_id, 3, '0', STR_PAD_LEFT); ?></p>
            </div>
        </div>
        
        <div class="hidden md:block text-right">
            <span class="text-[9px] font-black uppercase text-slate-400 tracking-widest block mb-1">Target Identity</span>
            <span class="px-3 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-xs font-bold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                <?php echo htmlspecialchars($project['name']); ?>
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <form action="update-project.php?project_id=<?php echo $project_id; ?>" method="POST" id="mainUpdateForm" class="lg:col-span-2 space-y-6 bg-white dark:bg-slate-900 p-8 md:p-10 rounded-[3rem] border border-slate-100 dark:border-slate-800 shadow-xl shadow-slate-200/50 dark:shadow-none">
            
            <div class="space-y-1">
                <label class="block text-[10px] font-black uppercase text-indigo-500 tracking-[0.2em] mb-2 ml-1">Identity / Project Title</label>
                <input type="text" name="projectName" 
                       value="<?php echo htmlspecialchars($project['name']); ?>" 
                       required 
                       placeholder="Enter project name..."
                       class="w-full px-6 py-5 bg-slate-50 dark:bg-slate-800/50 border-2 border-transparent focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 rounded-2xl outline-none transition-all font-bold text-slate-700 dark:text-slate-200 shadow-inner">
            </div>

            <div class="space-y-1">
                <label class="block text-[10px] font-black uppercase text-indigo-500 tracking-[0.2em] mb-2 ml-1">Strategic Briefing / Description</label>
                <textarea name="projectDescription" rows="6" required 
                          placeholder="Describe the scope of this workstream..."
                          class="w-full px-6 py-5 bg-slate-50 dark:bg-slate-800/50 border-2 border-transparent focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 rounded-2xl outline-none transition-all text-sm leading-relaxed text-slate-600 dark:text-slate-400 shadow-inner"><?php echo htmlspecialchars($project['description']); ?></textarea>
            </div>

            <div>
                <label class="block text-[10px] font-black uppercase text-indigo-500 tracking-[0.2em] mb-2 ml-1">Lifecycle State</label>
                <div class="relative group">
                    <select name="status" class="w-full px-6 py-5 bg-slate-50 dark:bg-slate-800/50 border-2 border-transparent focus:border-indigo-500 rounded-2xl outline-none appearance-none font-bold text-sm cursor-pointer text-slate-700 dark:text-slate-200 transition-all shadow-inner">
                        <option value="active" <?php echo ($project['status'] == 'active') ? 'selected' : ''; ?>>Active Deployment</option>
                        <option value="pending" <?php echo ($project['status'] == 'pending') ? 'selected' : ''; ?>>Pending / Evaluation</option>
                        <option value="completed" <?php echo ($project['status'] == 'completed') ? 'selected' : ''; ?>>Archive / Finished</option>
                    </select>
                    <i data-lucide="chevron-down" class="absolute right-6 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 pointer-events-none group-hover:text-indigo-500 transition-colors"></i>
                </div>
            </div>

            <div class="pt-8 flex flex-col sm:flex-row gap-4">
                <button type="submit" class="flex-grow bg-slate-900 dark:bg-indigo-600 text-white font-black py-5 rounded-3xl hover:bg-indigo-600 dark:hover:bg-indigo-500 transition-all flex items-center justify-center gap-3 text-[10px] uppercase tracking-[0.2em] shadow-xl shadow-indigo-500/20 active:scale-95">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Commit Configuration
                </button>
                <button type="button" onclick="loadPage('manage-projects')" class="px-10 py-5 bg-slate-100 dark:bg-slate-800 text-slate-500 font-bold rounded-3xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-all text-[10px] uppercase tracking-widest text-center active:scale-95">
                    Cancel
                </button>
            </div>
        </form>

        <div class="space-y-6">
            <div class="p-8 bg-indigo-50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/30 rounded-[2.5rem]">
                <h4 class="text-[10px] font-black uppercase text-indigo-500 tracking-[0.2em] mb-4">System Context</h4>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Registered</span>
                        <span class="text-xs font-black text-slate-700 dark:text-slate-300"><?php echo date('M d, Y', strtotime($project['created_at'])); ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Last Sync</span>
                        <span class="text-xs font-black text-slate-700 dark:text-slate-300"><?php echo $project['updated_at'] ? date('M d, H:i', strtotime($project['updated_at'])) : 'Never'; ?></span>
                    </div>
                </div>
                <div class="mt-6 pt-6 border-t border-indigo-100 dark:border-indigo-900/30">
                    <div class="flex items-center gap-3 text-indigo-600 dark:text-indigo-400">
                        <i data-lucide="info" class="w-4 h-4"></i>
                        <p class="text-[10px] font-bold leading-tight">Changes made here will propagate across all linked developer tickets immediately.</p>
                    </div>
                </div>
            </div>

            <div class="p-8 bg-slate-900 dark:bg-white rounded-[2.5rem] text-white dark:text-slate-900 shadow-xl">
                <i data-lucide="shield-check" class="w-8 h-8 mb-4 opacity-50"></i>
                <h4 class="text-lg font-black tracking-tight mb-1">Administrative Override</h4>
                <p class="text-xs font-medium opacity-60">You are currently operating with Kernel privileges.</p>
            </div>
        </div>
    </div>
</div>

<script>
    if(window.lucide) lucide.createIcons();
    
    // ⚡ Integrated AJAX Sync Engine
    $('#mainUpdateForm').off('submit').on('submit', function(e) {
        if(typeof loadPage === 'function') {
            e.preventDefault();
            const form = $(this);
            const btn = form.find('button[type="submit"]');
            
            btn.prop('disabled', true).addClass('opacity-50');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                success: function(res) {
                    if(res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Configuration Synced',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        loadPage('manage-projects');
                    } else {
                        Swal.fire('Error', res.error || 'Sync failed', 'error');
                        btn.prop('disabled', false).removeClass('opacity-50');
                    }
                },
                error: function() {
                    Swal.fire('Protocol Error', 'Communication with server lost.', 'error');
                    btn.prop('disabled', false).removeClass('opacity-50');
                }
            });
        }
    });
</script>