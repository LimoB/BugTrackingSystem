<?php
session_start();
include('../../config/config.php');

// ✅ Admin Security Check
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        die(json_encode(['error' => 'Unauthorized Access']));
    }
    header("Location: ../login/index.php");
    exit();
}

// 1. Initial Data Fetch
if (isset($_GET['project_id'])) {
    $project_id = intval($_GET['project_id']);
    $query = "SELECT * FROM Projects WHERE id = ?";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, 'i', $project_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $project = mysqli_fetch_assoc($result);

    if (!$project) {
        die("Project not found.");
    }
    mysqli_stmt_close($stmt);
} else {
    die("Invalid project ID.");
}

// 2. Handle POST Update (AJAX or Standard)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['projectName'];
    $description = $_POST['projectDescription'];
    $status = $_POST['status'];

    $update_query = "UPDATE Projects SET name = ?, description = ?, status = ? WHERE id = ?";
    $update_stmt = mysqli_prepare($connection, $update_query);

    if ($update_stmt) {
        mysqli_stmt_bind_param($update_stmt, 'sssi', $name, $description, $status, $project_id);
        
        if (mysqli_stmt_execute($update_stmt)) {
            // Check if AJAX request
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                echo json_encode(['success' => 'Project synchronized successfully.']);
                exit();
            }
            header("Location: manage-projects.php?success=updated");
            exit();
        } else {
            $error = mysqli_error($connection);
        }
        mysqli_stmt_close($update_stmt);
    }
}
?>

<div class="animate-fade-in max-w-2xl mx-auto">
    <div class="flex items-center gap-4 mb-8">
        <div class="bg-emerald-100 dark:bg-emerald-900/30 p-3 rounded-2xl text-emerald-600">
            <i data-lucide="settings-2" class="w-6 h-6"></i>
        </div>
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">Project Settings</h1>
            <p class="text-xs text-slate-500 uppercase tracking-widest font-bold">Modifying: <?php echo htmlspecialchars($project['name']); ?></p>
        </div>
    </div>

    <form action="update-project.php?project_id=<?php echo $project_id; ?>" method="POST" id="mainUpdateForm" class="space-y-6 bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-200 dark:border-slate-800 shadow-sm">
        
        <div>
            <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2 ml-1">Identity/Title</label>
            <input type="text" name="projectName" 
                   value="<?php echo htmlspecialchars($project['name']); ?>" 
                   required 
                   class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border border-transparent focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 rounded-2xl outline-none transition-all font-bold text-slate-700 dark:text-slate-200">
        </div>

        <div>
            <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2 ml-1">Description & Scope</label>
            <textarea name="projectDescription" rows="5" required 
                      class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border border-transparent focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 rounded-2xl outline-none transition-all text-sm leading-relaxed text-slate-600 dark:text-slate-400"><?php echo htmlspecialchars($project['description']); ?></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2 ml-1">Current Status</label>
                <div class="relative">
                    <select name="status" class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 rounded-2xl border-none outline-none appearance-none font-bold text-sm cursor-pointer text-slate-700 dark:text-slate-200">
                        <option value="active" <?php echo ($project['status'] == 'active') ? 'selected' : ''; ?>>Active Deployment</option>
                        <option value="inactive" <?php echo ($project['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive / Paused</option>
                        <option value="completed" <?php echo ($project['status'] == 'completed') ? 'selected' : ''; ?>>Archive / Finished</option>
                    </select>
                    <i data-lucide="chevron-down" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
                </div>
            </div>
            
            <div class="bg-emerald-50/50 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-900/30 p-4 rounded-2xl">
                <p class="text-[10px] font-black uppercase text-emerald-600 tracking-widest mb-1">Last Update</p>
                <p class="text-xs font-bold text-slate-500 italic"><?php echo date('F d, Y @ H:i'); ?></p>
            </div>
        </div>

        <div class="pt-6 flex flex-col md:flex-row gap-3">
            <button type="submit" class="flex-grow bg-slate-900 dark:bg-white dark:text-slate-900 text-white font-black py-4 rounded-2xl hover:bg-emerald-600 dark:hover:bg-emerald-500 dark:hover:text-white transition-all flex items-center justify-center gap-2 text-xs uppercase tracking-widest shadow-lg shadow-slate-200 dark:shadow-none">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                Commit Changes
            </button>
            <a href="manage-projects.php" class="px-8 py-4 bg-slate-100 dark:bg-slate-800 text-slate-500 font-bold rounded-2xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-all text-xs uppercase tracking-widest text-center">
                Back to List
            </a>
        </div>
    </form>
</div>

<script>
    lucide.createIcons();
    
    // AJAX Interceptor
    $('#mainUpdateForm').on('submit', function(e) {
        // Only run AJAX if inside the dashboard container
        if(typeof loadPage === 'function') {
            e.preventDefault();
            const form = $(this);
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                success: function(res) {
                    Swal.fire('Success', 'Project configuration updated.', 'success');
                    loadPage('manage-projects');
                }
            });
        }
    });
</script>