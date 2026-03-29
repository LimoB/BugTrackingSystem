<?php
session_start();
include('../../config/config.php');

// 🛡️ Security Check: Developer Access Only
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'developer') {
    die("Unauthorized Access");
}

$user_id = $_SESSION['user_id'];

// 📊 Pull Live Metrics
// 1. My Active Tickets
$stmt1 = $connection->prepare("SELECT COUNT(*) as total FROM Tickets WHERE assigned_to = ? AND status != 'resolved'");
$stmt1->bind_param("i", $user_id);
$stmt1->execute();
$my_tickets = $stmt1->get_result()->fetch_assoc()['total'];

// 2. Global Critical/Open Issues
$res2 = mysqli_query($connection, "SELECT COUNT(*) as total FROM Tickets WHERE status = 'open' OR priority = 'critical'");
$global_issues = mysqli_fetch_assoc($res2)['total'];

// 3. Recent Activity (Latest 3 tickets assigned to or created by dev)
$activity_query = "SELECT title, status, created_at FROM Tickets ORDER BY created_at DESC LIMIT 3";
$activities = mysqli_query($connection, $activity_query);
?>

<div class="animate-fade-in max-w-6xl mx-auto space-y-10">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2 py-0.5 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 text-[9px] font-black uppercase tracking-[0.2em] rounded">Operational</span>
                <span class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">v2.4.0-stable</span>
            </div>
            <h1 class="text-5xl font-black tracking-tighter text-slate-900 dark:text-white">Command <span class="text-indigo-600">Center.</span></h1>
        </div>
        
        <div class="bg-white dark:bg-slate-900 px-6 py-4 rounded-3xl border border-slate-200 dark:border-slate-800 flex items-center gap-4 shadow-sm">
            <div class="text-right">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">Server Time</p>
                <p class="text-sm font-mono font-bold text-slate-700 dark:text-slate-200"><?php echo date('H:i:s'); ?> UTC</p>
            </div>
            <div class="w-px h-8 bg-slate-100 dark:bg-slate-800"></div>
            <i data-lucide="clock" class="text-indigo-500 w-5 h-5"></i>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div onclick="loadPage('assigned-tickets')" class="cursor-pointer bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-200 dark:border-slate-800 shadow-sm hover:border-indigo-500 hover:-translate-y-1 transition-all duration-300 group">
            <div class="flex justify-between items-start mb-6">
                <div class="w-12 h-12 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 rounded-2xl flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-all">
                    <i data-lucide="terminal"></i>
                </div>
                <i data-lucide="arrow-up-right" class="w-5 h-5 text-slate-300 group-hover:text-indigo-500 transition-colors"></i>
            </div>
            <div class="text-5xl font-black mb-1 tracking-tighter text-slate-900 dark:text-white"><?php echo $my_tickets; ?></div>
            <p class="text-slate-400 font-black text-[10px] uppercase tracking-[0.2em]">Active Assignments</p>
        </div>

        <div onclick="loadPage('view-tickets')" class="cursor-pointer bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-200 dark:border-slate-800 shadow-sm hover:border-rose-500 hover:-translate-y-1 transition-all duration-300 group">
            <div class="flex justify-between items-start mb-6">
                <div class="w-12 h-12 bg-rose-50 dark:bg-rose-900/30 text-rose-600 rounded-2xl flex items-center justify-center group-hover:bg-rose-600 group-hover:text-white transition-all">
                    <i data-lucide="alert-circle"></i>
                </div>
                <i data-lucide="arrow-up-right" class="w-5 h-5 text-slate-300 group-hover:text-rose-500 transition-colors"></i>
            </div>
            <div class="text-5xl font-black mb-1 tracking-tighter text-slate-900 dark:text-white"><?php echo $global_issues; ?></div>
            <p class="text-slate-400 font-black text-[10px] uppercase tracking-[0.2em]">Unresolved Flux</p>
        </div>

        <button onclick="loadPage('create-ticket')" class="bg-indigo-600 p-8 rounded-[2.5rem] text-white text-left hover:bg-slate-900 dark:hover:bg-white dark:hover:text-slate-900 transition-all duration-300 shadow-2xl shadow-indigo-200 dark:shadow-none group relative overflow-hidden">
            <div class="relative z-10">
                <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center mb-6">
                    <i data-lucide="plus-circle"></i>
                </div>
                <div class="text-2xl font-black mb-1 tracking-tight">Deploy Report</div>
                <p class="text-indigo-100 group-hover:text-inherit text-xs font-medium opacity-80 uppercase tracking-widest">Initialize New Ticket</p>
            </div>
            <i data-lucide="zap" class="absolute -right-4 -bottom-4 w-32 h-32 text-white/5 rotate-12 group-hover:rotate-0 transition-transform duration-700"></i>
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-200 dark:border-slate-800 p-8 shadow-sm">
            <h3 class="text-sm font-black uppercase tracking-widest text-slate-400 mb-6 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                Recent Transmissions
            </h3>
            <div class="space-y-6">
                <?php while($row = mysqli_fetch_assoc($activities)): ?>
                <div class="flex items-center gap-4 group cursor-default">
                    <div class="w-2 h-10 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div class="w-full h-1/2 bg-indigo-500"></div>
                    </div>
                    <div class="flex-grow">
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-200 group-hover:text-indigo-600 transition-colors"><?php echo htmlspecialchars($row['title']); ?></p>
                        <p class="text-[10px] text-slate-400 font-medium uppercase tracking-tighter italic">Status: <?php echo $row['status']; ?> • <?php echo date('H:i', strtotime($row['created_at'])); ?></p>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="bg-slate-900 rounded-[2.5rem] p-8 text-indigo-400 font-mono text-xs relative overflow-hidden flex flex-col justify-center border border-slate-800">
            <div class="relative z-10 space-y-2 opacity-80">
                <p><span class="text-emerald-500">➜</span> <span class="text-slate-500">~</span> system --status</p>
                <p class="text-slate-300">[OK] Database Heartbeat detected.</p>
                <p class="text-slate-300">[OK] Environment variables loaded.</p>
                <p><span class="text-emerald-500">➜</span> <span class="text-slate-500">~</span> session --user-role</p>
                <p class="text-indigo-300">"DEVELOPER_ACCESS_GRANTED"</p>
                <p class="animate-pulse">_</p>
            </div>
            <i data-lucide="code-2" class="absolute -right-8 -bottom-8 w-40 h-40 text-white/5 -rotate-12"></i>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();
</script>