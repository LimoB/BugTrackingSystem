<?php
session_start();
include('../../config/config.php');

// ✅ Admin Security Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    die("<div class='p-12 text-rose-500 font-black text-center uppercase tracking-widest'>Access Denied</div>");
}

// 📡 Multi-Vector Data Fetching
// We use a single query for tickets to get a breakdown (Optimization)
$ticketStats = mysqli_fetch_assoc(mysqli_query($connection, "
    SELECT 
        COUNT(*) as total, 
        SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_tickets,
        SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_tickets
    FROM Tickets
"));

$projectStats = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as total FROM Projects"));
$userStats    = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as total FROM Users"));

// Fetch recent 5 activities (assuming you have an Audit_Log or just recent tickets)
$recentTickets = mysqli_query($connection, "SELECT title, created_at FROM Tickets ORDER BY id DESC LIMIT 5");
?>

<div class="animate-fade-in space-y-8">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">System Live • Master Control</span>
            </div>
            <h1 class="text-4xl font-black tracking-tighter text-slate-900 dark:text-white">Command Center</h1>
        </div>
        <div class="text-right">
            <p class="text-xs font-bold text-slate-400">Current Node</p>
            <p class="text-sm font-black text-indigo-600 dark:text-indigo-400 font-mono">ZAPPR-ALPHA-V3</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-200 dark:border-slate-800 shadow-sm group hover:border-indigo-500/30 transition-all">
            <div class="flex justify-between items-start mb-6">
                <div class="w-14 h-14 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i data-lucide="ticket" class="w-7 h-7"></i>
                </div>
                <span class="text-[10px] font-black px-2 py-1 bg-emerald-50 text-emerald-600 rounded-lg"><?php echo $ticketStats['open_tickets']; ?> Active</span>
            </div>
            <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1">Total Backlog</p>
            <h3 class="text-4xl font-black text-slate-900 dark:text-white"><?php echo $ticketStats['total']; ?></h3>
        </div>

        <div class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-200 dark:border-slate-800 shadow-sm group hover:border-emerald-500/30 transition-all">
            <div class="flex justify-between items-start mb-6">
                <div class="w-14 h-14 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i data-lucide="layers" class="w-7 h-7"></i>
                </div>
            </div>
            <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1">Managed Workstreams</p>
            <h3 class="text-4xl font-black text-slate-900 dark:text-white"><?php echo $projectStats['total']; ?></h3>
        </div>

        <div class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-200 dark:border-slate-800 shadow-sm group hover:border-rose-500/30 transition-all">
            <div class="flex justify-between items-start mb-6">
                <div class="w-14 h-14 bg-rose-50 dark:bg-rose-900/30 text-rose-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i data-lucide="users" class="w-7 h-7"></i>
                </div>
            </div>
            <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1">Verified Identities</p>
            <h3 class="text-4xl font-black text-slate-900 dark:text-white"><?php echo $userStats['total']; ?></h3>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
        <div class="lg:col-span-3 bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-200 dark:border-slate-800 p-10">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-xl font-black text-slate-900 dark:text-white">Recent Telemetry</h3>
                <button onclick="loadPage('manage-tickets')" class="text-[10px] font-black uppercase tracking-widest text-indigo-500 hover:text-indigo-600 transition-colors">View All</button>
            </div>
            <div class="space-y-6">
                <?php while($row = mysqli_fetch_assoc($recentTickets)): ?>
                    <div class="flex items-center gap-4 group cursor-default">
                        <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-400 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                            <i data-lucide="hash" class="w-4 h-4"></i>
                        </div>
                        <div class="flex-grow">
                            <p class="text-sm font-bold text-slate-700 dark:text-slate-200 line-clamp-1"><?php echo htmlspecialchars($row['title']); ?></p>
                            <p class="text-[10px] text-slate-400 uppercase font-medium"><?php echo date('M d, H:i', strtotime($row['created_at'])); ?> • New Ticket</p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="lg:col-span-2 bg-indigo-600 rounded-[2.5rem] p-10 text-white relative overflow-hidden flex flex-col justify-center">
            <div class="relative z-10">
                <h2 class="text-3xl font-black mb-4 leading-tight">Sync Global<br>Backlog.</h2>
                <p class="text-indigo-100 text-sm mb-8 leading-relaxed opacity-80">Orchestrate your development teams and resolve pending workstreams in one unified view.</p>
                <button onclick="loadPage('manage-tickets')" class="w-full bg-white text-indigo-600 px-8 py-5 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-indigo-50 transition-all shadow-xl shadow-indigo-900/20">
                    Open Intelligence Matrix
                </button>
            </div>
            <i data-lucide="shield-check" class="absolute -right-12 -bottom-12 w-64 h-64 text-indigo-500/20 rotate-12 pointer-events-none"></i>
        </div>
    </div>
</div>

<script>lucide.createIcons();</script>