<?php
session_start();
include('../../config/config.php');

// ✅ Admin Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    die("<div class='p-6 text-red-500 font-bold'>Unauthorized Access</div>");
}

if (isset($_GET['project_id']) && is_numeric($_GET['project_id'])) {
    $project_id = intval($_GET['project_id']);

    // Optimized Query: Fetch project and potentially the creator's name if joined
    $query = "SELECT p.*, u.name as creator_name 
              FROM Projects p 
              LEFT JOIN Users u ON p.created_by = u.id 
              WHERE p.id = ?";
    
    $stmt = mysqli_prepare($connection, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $project_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($project = mysqli_fetch_assoc($result)) {
            // Status Styling Logic
            $status_map = [
                'active' => ['bg' => 'bg-emerald-500', 'text' => 'text-emerald-600', 'label' => 'Live & Tracking'],
                'inactive' => ['bg' => 'bg-slate-400', 'text' => 'text-slate-500', 'label' => 'Paused / Idle'],
                'completed' => ['bg' => 'bg-indigo-500', 'text' => 'text-indigo-600', 'label' => 'Archived']
            ];
            $style = $status_map[strtolower($project['status'])] ?? $status_map['inactive'];
?>
            <div class="animate-fade-in">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 pb-6 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl <?php echo $style['bg']; ?> flex items-center justify-center text-white shadow-lg">
                            <i data-lucide="folder-kanban" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">
                                <?php echo htmlspecialchars($project['name']); ?>
                            </h2>
                            <span class="text-[10px] font-black uppercase tracking-widest <?php echo $style['text']; ?>">
                                <?php echo $style['label']; ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="text-right">
                        <div class="text-[10px] font-black uppercase text-slate-400 tracking-tighter mb-1">Project Identifier</div>
                        <code class="px-3 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-xs font-bold text-slate-600 dark:text-slate-300">
                            PRJ-<?php echo str_pad($project['id'], 4, '0', STR_PAD_LEFT); ?>
                        </code>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-2 space-y-4">
                        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest">Scope & Documentation</label>
                        <div class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-3xl border border-slate-100 dark:border-slate-800 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                            <?php echo nl2br(htmlspecialchars($project['description'])); ?>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2">Lead Administrator</label>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold">
                                    <?php echo strtoupper(substr($project['creator_name'] ?? 'S', 0, 1)); ?>
                                </div>
                                <span class="text-sm font-bold text-slate-700 dark:text-slate-300">
                                    <?php echo htmlspecialchars($project['creator_name'] ?? 'System'); ?>
                                </span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1">Timestamp</label>
                            <p class="text-xs text-slate-500 font-medium">
                                Created on <?php echo date('M d, Y', strtotime($project['created_at'])); ?>
                            </p>
                        </div>

                        <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                            <button onclick="editProject(<?php echo $project['id']; ?>)" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-emerald-600 dark:hover:bg-emerald-500 transition-all">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                                Modify Settings
                            </button>
                        </div>
                    </div>
                </div>
            </div>
<?php
        } else {
            echo "<div class='p-12 text-center text-slate-400 font-bold italic'>Error: Project data is unreachable.</div>";
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<script>
    lucide.createIcons();
</script>