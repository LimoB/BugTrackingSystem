<?php
session_start();
include('../../config/config.php');

// ✅ Admin Security Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    die("<div class='p-12 text-rose-500 font-black text-center uppercase tracking-widest'>Access Denied</div>");
}

/** * 📡 Optimization: Single Query for Ticket Metrics
 * Note: Added check for deleted_at to ensure we only see active tickets
 */
$ticketMetrics = mysqli_fetch_assoc(mysqli_query($connection, "
    SELECT 
        COUNT(*) as total, 
        SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_count,
        SUM(CASE WHEN status = 'in-progress' THEN 1 ELSE 0 END) as progress_count,
        SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_count,
        SUM(CASE WHEN priority = 'high' AND status != 'resolved' THEN 1 ELSE 0 END) as high_priority_count
    FROM Tickets 
    WHERE deleted_at IS NULL
"));

$projectCount = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as total FROM Projects"))['total'];
$userCount    = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as total FROM Users"))['total'];

// 📈 Calculate Percentages for Progress Bar
$total = ($ticketMetrics['total'] > 0) ? $ticketMetrics['total'] : 1;
$resolvedPercent = round(($ticketMetrics['resolved_count'] / $total) * 100);

// 📜 Fetch Recent System Activity (Audit Log)
// Updated to use 'action_details' which we added to your DB schema
$activities = mysqli_query($connection, "
    SELECT a.*, u.name as admin_name 
    FROM activity_log a 
    JOIN Users u ON a.user_id = u.id 
    ORDER BY a.created_at DESC LIMIT 6
");
?>

<div class="animate-in fade-in slide-in-from-bottom-4 duration-700 space-y-10">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 border-b border-slate-200 dark:border-slate-800 pb-8">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </span>
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500">Node: Localhost • Kernel V3.4.0</span>
            </div>
            <h1 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white">System Executive Overview</h1>
        </div>
        <div class="flex gap-4">
            <div class="px-6 py-3 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm text-center">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Health Score</p>
                <p class="text-xl font-black text-emerald-500">98.2%</p>
            </div>
            <button onclick="loadPage('admin_stats')" class="p-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all text-slate-500">
                <i data-lucide="refresh-cw" class="w-5 h-5"></i>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-200 dark:border-slate-800 shadow-sm relative group transition-all hover:shadow-xl hover:shadow-indigo-500/5">
            <div class="flex justify-between items-center mb-4 relative z-10">
                <div class="p-3 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-xl">
                    <i data-lucide="ticket" class="w-6 h-6"></i>
                </div>
                <div class="text-right">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Tickets</span>
                    <h3 class="text-3xl font-black text-slate-900 dark:text-white"><?php echo $ticketMetrics['total']; ?></h3>
                </div>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden mt-4">
                <div class="bg-indigo-600 h-full rounded-full transition-all duration-1000" style="width: <?php echo $resolvedPercent; ?>%"></div>
            </div>
            <p class="mt-2 text-[10px] font-bold text-slate-500"><?php echo $resolvedPercent; ?>% Efficiency Rate</p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-200 dark:border-slate-800 shadow-sm relative group transition-all hover:shadow-xl hover:shadow-rose-500/5">
            <div class="flex justify-between items-center mb-4 relative z-10">
                <div class="p-3 bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 rounded-xl">
                    <i data-lucide="zap" class="w-6 h-6"></i>
                </div>
                <div class="text-right">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Critical Path</span>
                    <h3 class="text-3xl font-black text-rose-600"><?php echo $ticketMetrics['high_priority_count']; ?></h3>
                </div>
            </div>
            <p class="text-[10px] text-rose-500 font-bold uppercase tracking-tight">Requires Immediate Action</p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-200 dark:border-slate-800 shadow-sm relative transition-all hover:shadow-xl hover:shadow-emerald-500/5">
            <div class="flex justify-between items-center mb-4">
                <div class="p-3 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-xl">
                    <i data-lucide="folder-kanban" class="w-6 h-6"></i>
                </div>
                <div class="text-right">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Active Units</span>
                    <h3 class="text-3xl font-black text-slate-900 dark:text-white"><?php echo $projectCount; ?></h3>
                </div>
            </div>
            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-tight">Resource Allocation: Stable</p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-200 dark:border-slate-800 shadow-sm relative transition-all hover:shadow-xl hover:shadow-amber-500/5">
            <div class="flex justify-between items-center mb-4">
                <div class="p-3 bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 rounded-xl">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
                <div class="text-right">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Personnel</span>
                    <h3 class="text-3xl font-black text-slate-900 dark:text-white"><?php echo $userCount; ?></h3>
                </div>
            </div>
            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-tight">Active Access Nodes</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-200 dark:border-slate-800 p-8 shadow-sm">
            <div class="flex items-center justify-between mb-8 border-b border-slate-50 dark:border-slate-800 pb-5">
                <div>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Audit Trail</h3>
                    <p class="text-[10px] text-slate-400 uppercase font-black tracking-widest">System Events & Deployment Logs</p>
                </div>
                <i data-lucide="list-checks" class="text-slate-300 dark:text-slate-700 w-6 h-6"></i>
            </div>
            
            <div class="space-y-6">
                <?php if(mysqli_num_rows($activities) > 0): ?>
                    <?php while($log = mysqli_fetch_assoc($activities)): ?>
                        <div class="flex gap-4 p-4 rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-all border border-transparent hover:border-slate-100 dark:hover:border-slate-800 group">
                            <div class="w-10 h-10 rounded-xl bg-slate-900 dark:bg-slate-800 group-hover:bg-emerald-600 transition-colors flex items-center justify-center text-white shrink-0">
                                <i data-lucide="activity" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <p class="text-sm text-slate-700 dark:text-slate-200 font-medium leading-relaxed">
                                    <span class="font-black text-slate-900 dark:text-white"><?php echo htmlspecialchars($log['admin_name']); ?></span> 
                                    <?php echo htmlspecialchars($log['action_details'] ?? 'performed an action'); ?>
                                </p>
                                <p class="text-[10px] text-slate-400 font-bold uppercase mt-1 tracking-widest">
                                    <?php echo date('d M • H:i:s', strtotime($log['created_at'])); ?>
                                </p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-10">
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">No recent activity detected</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="space-y-8">
            <div class="bg-slate-900 dark:bg-slate-900 rounded-[2.5rem] p-8 text-white border border-slate-800 shadow-2xl">
                <h3 class="text-lg font-black mb-6 flex items-center gap-2">
                    <i data-lucide="pie-chart" class="w-5 h-5 text-emerald-400"></i> Workflow Status
                </h3>
                <div class="space-y-5">
                    <?php 
                        $statusData = [
                            ['label' => 'Open', 'count' => $ticketMetrics['open_count'], 'color' => 'bg-emerald-400'],
                            ['label' => 'In Progress', 'count' => $ticketMetrics['progress_count'], 'color' => 'bg-amber-400'],
                            ['label' => 'Resolved', 'count' => $ticketMetrics['resolved_count'], 'color' => 'bg-blue-400']
                        ];
                        foreach($statusData as $item):
                            $percent = ($item['count'] / $total) * 100;
                    ?>
                    <div>
                        <div class="flex justify-between text-[10px] font-black uppercase mb-2">
                            <span><?php echo $item['label']; ?></span>
                            <span><?php echo $item['count']; ?></span>
                        </div>
                        <div class="h-1.5 w-full bg-white/10 rounded-full overflow-hidden">
                            <div class="h-full <?php echo $item['color']; ?> rounded-full" style="width: <?php echo $percent; ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="bg-indigo-600 rounded-[2.5rem] p-8 text-white shadow-xl shadow-indigo-500/20 group cursor-pointer" onclick="loadPage('assign-tickets')">
                <div class="flex justify-between items-start mb-6">
                    <div class="p-3 bg-white/20 rounded-2xl">
                        <i data-lucide="send" class="w-6 h-6"></i>
                    </div>
                    <i data-lucide="arrow-up-right" class="w-5 h-5 opacity-50 group-hover:opacity-100 transition-opacity"></i>
                </div>
                <h3 class="text-xl font-black mb-2">Assign Queue</h3>
                <p class="text-xs text-indigo-100 opacity-70 mb-6">Dispatch unassigned tickets to available personnel instantly.</p>
                <div class="w-full bg-white text-indigo-600 py-4 rounded-2xl font-black text-xs uppercase tracking-widest text-center group-hover:bg-indigo-50 transition-all">
                    Launch Matrix
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Re-initialize Lucide icons for the newly loaded AJAX content
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>