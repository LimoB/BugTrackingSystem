<?php
/**
 * File: admin/api/fetch-project-details.php
 * Purpose: Renders a high-level intelligence dossier for a specific project.
 */
session_start();
include('../../../config/config.php');

// 🛡️ Admin Security Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    die("<div class='p-12 text-rose-500 font-black text-center bg-rose-50 dark:bg-rose-950/20 rounded-3xl uppercase tracking-widest text-xs'>Unauthorized System Access</div>");
}

$projectId = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;

if ($projectId <= 0) {
    die("<div class='p-12 text-center text-slate-400 font-black uppercase tracking-widest text-xs animate-pulse'>Invalid Registry Reference</div>");
}

// 📡 Fetch project details + Ticket Statistics in one go
$query = "SELECT p.*, 
          (SELECT COUNT(*) FROM Tickets WHERE project_id = p.id AND deleted_at IS NULL) as total_tickets,
          (SELECT COUNT(*) FROM Tickets WHERE project_id = p.id AND status = 'resolved' AND deleted_at IS NULL) as resolved_tickets,
          (SELECT COUNT(*) FROM Tickets WHERE project_id = p.id AND status = 'open' AND deleted_at IS NULL) as open_tickets
          FROM Projects p WHERE p.id = ? LIMIT 1";

$stmt = $connection->prepare($query);
$stmt->bind_param('i', $projectId);
$stmt->execute();
$result = $stmt->get_result();

if ($project = $result->fetch_assoc()) {
    $status_color = [
        'active'    => 'text-emerald-500 bg-emerald-50 dark:bg-emerald-900/20 border-emerald-100 dark:border-emerald-800',
        'pending'   => 'text-amber-500 bg-amber-50 dark:bg-amber-900/20 border-amber-100 dark:border-amber-800',
        'completed' => 'text-slate-400 bg-slate-50 dark:bg-slate-800 border-slate-100 dark:border-slate-800'
    ][strtolower($project['status'])] ?? 'bg-slate-100 text-slate-400';

    $progress = ($project['total_tickets'] > 0) ? round(($project['resolved_tickets'] / $project['total_tickets']) * 100) : 0;
    ?>

    <div class="animate-in fade-in slide-in-from-bottom-4 duration-500">
        <div class="flex items-start justify-between mb-10">
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <span class="px-3 py-1 bg-slate-900 text-white dark:bg-white dark:text-slate-900 rounded-lg text-[10px] font-black tracking-widest">
                        #PROJ-<?php echo str_pad($project['id'], 3, '0', STR_PAD_LEFT); ?>
                    </span>
                    <span class="px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest border <?php echo $status_color; ?>">
                        ● <?php echo $project['status']; ?>
                    </span>
                </div>
                <h2 class="text-4xl font-black text-slate-900 dark:text-white tracking-tighter leading-tight">
                    <?php echo htmlspecialchars($project['name']); ?>
                </h2>
            </div>
            <button onclick="closeModal()" class="w-10 h-10 flex items-center justify-center hover:bg-slate-100 dark:hover:bg-slate-800 rounded-2xl transition-all text-slate-400">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>

        <div class="space-y-6">
            <div class="p-8 bg-slate-50 dark:bg-slate-800/40 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-inner relative overflow-hidden">
                <div class="absolute top-0 right-0 p-6 opacity-5 dark:opacity-10 pointer-events-none">
                    <i data-lucide="quote-right" class="w-16 h-16 text-slate-900 dark:text-white"></i>
                </div>
                <h3 class="text-[10px] font-black uppercase tracking-[0.3em] text-indigo-500 mb-4 flex items-center gap-2">
                    <i data-lucide="file-text" class="w-3.5 h-3.5"></i> Strategic Briefing
                </h3>
                <div class="text-slate-600 dark:text-slate-300 text-sm leading-relaxed italic font-medium">
                    "<?php echo nl2br(htmlspecialchars($project['description'])); ?>"
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-6 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm">
                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">Active Load</p>
                    <div class="flex items-end gap-2">
                        <span class="text-2xl font-black text-slate-900 dark:text-white"><?php echo $project['open_tickets']; ?></span>
                        <span class="text-[10px] font-bold text-rose-500 mb-1 uppercase tracking-tighter">Open Tasks</span>
                    </div>
                </div>
                <div class="p-6 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm">
                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">Resolved</p>
                    <div class="flex items-end gap-2">
                        <span class="text-2xl font-black text-slate-900 dark:text-white"><?php echo $project['resolved_tickets']; ?></span>
                        <span class="text-[10px] font-bold text-emerald-500 mb-1 uppercase tracking-tighter">Completed</span>
                    </div>
                </div>
                <div class="p-6 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm">
                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">Project Velocity</p>
                    <div class="flex items-center gap-3 mt-1">
                        <span class="text-2xl font-black text-slate-900 dark:text-white"><?php echo $progress; ?>%</span>
                        <div class="flex-grow h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full bg-indigo-500 rounded-full shadow-[0_0_8px_rgba(99,102,241,0.5)]" style="width: <?php echo $progress; ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between px-8 py-5 bg-slate-50 dark:bg-slate-800/20 border border-dashed border-slate-200 dark:border-slate-800 rounded-3xl">
                <div class="flex items-center gap-2">
                    <i data-lucide="calendar" class="w-4 h-4 text-slate-400"></i>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Entry Created:</span>
                    <span class="text-xs font-bold text-slate-600 dark:text-slate-300">
                        <?php echo date('M d, Y', strtotime($project['created_at'])); ?>
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <i data-lucide="layers" class="w-4 h-4 text-slate-400"></i>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Assets:</span>
                    <span class="text-xs font-bold text-slate-600 dark:text-slate-300"><?php echo $project['total_tickets']; ?> Tickets</span>
                </div>
            </div>
        </div>

        <div class="mt-12 flex flex-col sm:flex-row gap-4">
            <button onclick="editProject(<?php echo $project['id']; ?>)" class="flex-grow bg-slate-900 dark:bg-indigo-600 text-white font-black py-5 rounded-3xl hover:bg-indigo-600 dark:hover:bg-indigo-500 transition-all text-[10px] uppercase tracking-[0.2em] shadow-xl shadow-indigo-500/10 active:scale-95 flex items-center justify-center gap-2">
                <i data-lucide="edit-3" class="w-4 h-4"></i>
                Modify Configuration
            </button>
            <button onclick="closeModal()" class="px-10 py-5 bg-slate-100 dark:bg-slate-800 text-slate-500 font-black rounded-3xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-all text-[10px] uppercase tracking-widest active:scale-95">
                Dismiss Dossier
            </button>
        </div>
    </div>

    <script>
        if(window.lucide) lucide.createIcons();
    </script>

    <?php
} else {
    echo "<div class='p-20 text-center'><i data-lucide='alert-triangle' class='w-12 h-12 text-rose-500 mx-auto mb-4'></i><p class='font-black text-slate-400 uppercase tracking-widest text-xs'>Archive Entry Not Found</p></div>";
}

$stmt->close();
$connection->close();
?>