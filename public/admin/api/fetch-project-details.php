<?php
session_start();
include('../../../config/config.php');

// 🛡️ Admin Security Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    die("<div class='p-6 text-rose-500 font-bold'>Unauthorized Access</div>");
}

$projectId = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;

if ($projectId <= 0) {
    die("<div class='p-10 text-center text-slate-400 font-bold uppercase tracking-widest text-xs'>Invalid Reference ID</div>");
}

// Fetch project details
$query = "SELECT * FROM Projects WHERE id = ?";
$stmt = $connection->prepare($query);
$stmt->bind_param('i', $projectId);
$stmt->execute();
$result = $stmt->get_result();

if ($project = $result->fetch_assoc()) {
    $status_dot = [
        'active' => 'bg-emerald-500',
        'pending' => 'bg-amber-500',
        'completed' => 'bg-slate-400'
    ][strtolower($project['status'])] ?? 'bg-slate-300';
    ?>

    <div class="flex items-start justify-between mb-8">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <span class="px-2 py-0.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded text-[10px] font-mono font-black">
                    #PROJ-<?php echo str_pad($project['id'], 3, '0', STR_PAD_LEFT); ?>
                </span>
                <div class="flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                    <div class="w-1.5 h-1.5 rounded-full <?php echo $status_dot; ?> animate-pulse"></div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">
                        <?php echo $project['status']; ?>
                    </span>
                </div>
            </div>
            <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                <?php echo htmlspecialchars($project['name']); ?>
            </h2>
        </div>
        <button onclick="closeModal()" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all text-slate-400">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
    </div>

    <div class="space-y-6">
        <div class="p-6 bg-slate-50 dark:bg-slate-800/50 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-inner">
            <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-500 mb-3 flex items-center gap-2">
                <i data-lucide="file-text" class="w-3 h-3"></i> Project Briefing
            </h3>
            <div class="text-slate-600 dark:text-slate-300 text-sm leading-relaxed italic">
                <?php echo nl2br(htmlspecialchars($project['description'])); ?>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="px-6 py-4 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl">
                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">Lifecycle State</p>
                <p class="text-sm font-bold text-slate-700 dark:text-slate-200 capitalize"><?php echo $project['status']; ?></p>
            </div>
            <div class="px-6 py-4 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl">
                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">Created At</p>
                <p class="text-sm font-bold text-slate-700 dark:text-slate-200">
                    <?php echo isset($project['created_at']) ? date('M d, Y', strtotime($project['created_at'])) : 'N/A'; ?>
                </p>
            </div>
        </div>
    </div>

    <div class="mt-10 pt-6 border-t border-slate-100 dark:border-slate-800 flex gap-3">
        <button onclick="editProject(<?php echo $project['id']; ?>)" class="flex-grow bg-slate-900 dark:bg-white dark:text-slate-900 text-white font-black py-4 rounded-2xl hover:bg-indigo-600 dark:hover:bg-indigo-500 dark:hover:text-white transition-all text-xs uppercase tracking-widest shadow-xl shadow-slate-200 dark:shadow-none">
            Modify Configuration
        </button>
        <button onclick="closeModal()" class="px-8 py-4 bg-slate-50 dark:bg-slate-800 text-slate-500 font-bold rounded-2xl hover:bg-slate-100 dark:hover:bg-slate-700 transition-all text-xs uppercase tracking-widest">
            Dismiss
        </button>
    </div>

    <?php
} else {
    echo "<div class='p-12 text-center text-rose-500 font-black uppercase tracking-widest text-xs'>Archive Entry Not Found</div>";
}

$stmt->close();
$connection->close();
?>