<?php
session_start();
include('../../config/config.php');

/**
 * File: developer/dashboard_home.php
 * Purpose: Refined SaaS-style Workspace Dashboard.
 */

// 🛡️ Security Check
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'developer') {
    http_response_code(403);
    die("Access Denied: Please log in to view your workspace.");
}

$user_id = (int)($_SESSION['user_id'] ?? $_SESSION['id'] ?? 0);

// 📊 1. My Assignments
$stmt1 = $connection->prepare("SELECT COUNT(*) as total FROM Tickets WHERE assigned_to = ? AND status NOT IN ('resolved', 'closed')");
$stmt1->bind_param("i", $user_id);
$stmt1->execute();
$my_tasks = $stmt1->get_result()->fetch_assoc()['total'];

// 📊 2. Global Critical Issues
$res2 = mysqli_query($connection, "SELECT COUNT(*) as total FROM Tickets WHERE status = 'open' AND priority = 'critical'");
$critical_count = mysqli_fetch_assoc($res2)['total'];

// 📊 3. Recent Activity Feed
$activity_query = "SELECT title, status, created_at, priority FROM Tickets ORDER BY created_at DESC LIMIT 5";
$activities = mysqli_query($connection, $activity_query);

// 🕒 Time-based Greeting
$hour = date('H');
$greeting = ($hour < 12) ? "Good morning" : (($hour < 17) ? "Good afternoon" : "Good evening");
?>

<div class="animate-in fade-in slide-in-from-bottom-4 duration-700 max-w-6xl mx-auto space-y-8 pb-10">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-600 dark:text-blue-400">Developer Environment</span>
                <span class="h-px w-8 bg-slate-200 dark:bg-slate-800"></span>
            </div>
            <h1 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white">
                <?php echo $greeting; ?>, <span class="text-blue-600"><?php echo explode(' ', $_SESSION['name'] ?? 'Dev')[0]; ?></span>.
            </h1>
        </div>
        
        <div class="flex items-center gap-4 bg-white dark:bg-slate-900 px-6 py-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex flex-col items-end">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">Server Status</p>
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                    <span class="text-sm font-bold text-slate-700 dark:text-slate-200">Online</span>
                </div>
            </div>
            <div class="w-px h-8 bg-slate-100 dark:bg-slate-800"></div>
            <p class="text-sm font-bold text-slate-500 font-mono"><?php echo date('M d, Y'); ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <div onclick="loadPage('assigned-tickets')" class="group cursor-pointer bg-white dark:bg-slate-900 p-8 rounded-[2rem] border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-2xl hover:shadow-blue-500/10 hover:border-blue-500 transition-all relative overflow-hidden">
            <div class="relative z-10">
                <div class="flex justify-between items-start mb-6">
                    <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/30 text-blue-600 rounded-2xl flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-all duration-500 shadow-sm">
                        <i data-lucide="layout-list" class="w-5 h-5"></i>
                    </div>
                    <i data-lucide="arrow-up-right" class="w-5 h-5 text-slate-300 group-hover:text-blue-500 transition-colors"></i>
                </div>
                <div class="text-6xl font-black mb-1 tracking-tighter text-slate-900 dark:text-white"><?php echo $my_tasks; ?></div>
                <p class="text-slate-500 font-bold text-xs uppercase tracking-widest">Active Assignments</p>
            </div>
            <div class="absolute -right-4 -bottom-4 opacity-[0.03] dark:opacity-[0.05] group-hover:scale-110 transition-transform duration-700">
                <i data-lucide="layout-list" class="w-32 h-32 text-slate-900 dark:text-white"></i>
            </div>
        </div>

        <div onclick="loadPage('view-tickets')" class="group cursor-pointer bg-white dark:bg-slate-900 p-8 rounded-[2rem] border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-2xl hover:shadow-rose-500/10 hover:border-rose-500 transition-all relative overflow-hidden">
            <div class="relative z-10">
                <div class="flex justify-between items-start mb-6">
                    <div class="w-12 h-12 bg-rose-50 dark:bg-rose-900/30 text-rose-600 rounded-2xl flex items-center justify-center group-hover:bg-rose-600 group-hover:text-white transition-all duration-500 shadow-sm">
                        <i data-lucide="flame" class="w-5 h-5"></i>
                    </div>
                    <?php if($critical_count > 0): ?>
                        <span class="flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="text-6xl font-black mb-1 tracking-tighter text-slate-900 dark:text-white"><?php echo $critical_count; ?></div>
                <p class="text-slate-500 font-bold text-xs uppercase tracking-widest">Critical Blocker<?php echo $critical_count != 1 ? 's' : ''; ?></p>
            </div>
            <div class="absolute -right-4 -bottom-4 opacity-[0.03] dark:opacity-[0.05] group-hover:scale-110 transition-transform duration-700">
                <i data-lucide="flame" class="w-32 h-32 text-slate-900 dark:text-white"></i>
            </div>
        </div>

        <button onclick="loadPage('create-ticket')" class="group bg-slate-950 dark:bg-blue-600 p-8 rounded-[2rem] text-white text-left hover:shadow-2xl hover:shadow-blue-500/20 transition-all relative overflow-hidden border border-transparent">
            <div class="relative z-10">
                <div class="w-14 h-14 bg-white/10 backdrop-blur-md rounded-2xl flex items-center justify-center mb-10 group-hover:scale-110 transition-transform duration-500">
                    <i data-lucide="plus-square" class="w-6 h-6 text-white"></i>
                </div>
                <div class="text-2xl font-black mb-1 tracking-tight">Deploy Ticket</div>
                <p class="text-white/50 text-xs font-bold uppercase tracking-widest group-hover:text-white transition-colors">Initialize New Task</p>
            </div>
            <i data-lucide="zap" class="absolute -right-6 -bottom-6 w-32 h-32 text-white/5 rotate-12 group-hover:rotate-0 transition-transform duration-700"></i>
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-200 dark:border-slate-800 p-8 shadow-sm">
            <div class="flex items-center justify-between mb-10">
                <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-widest flex items-center gap-3">
                    <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                    Recent Activity
                </h3>
                <span class="text-[10px] font-bold text-slate-400 uppercase">Live Feed</span>
            </div>
            
            <div class="space-y-8">
                <?php if(mysqli_num_rows($activities) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($activities)): ?>
                    <div class="flex items-start gap-5 group cursor-default">
                        <div class="mt-1 w-1.5 h-10 rounded-full <?php echo ($row['priority'] === 'critical') ? 'bg-rose-500' : 'bg-slate-200 dark:bg-slate-800 group-hover:bg-blue-500'; ?> transition-colors duration-300"></div>
                        <div class="flex-grow">
                            <p class="text-sm font-bold text-slate-800 dark:text-slate-200 group-hover:text-blue-600 transition-colors line-clamp-1">
                                <?php echo htmlspecialchars($row['title']); ?>
                            </p>
                            <div class="flex items-center gap-3 mt-2">
                                <span class="text-[10px] font-black uppercase px-2 py-0.5 <?php echo ($row['priority'] === 'critical') ? 'bg-rose-100 text-rose-600' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'; ?> rounded">
                                    <?php echo $row['status']; ?>
                                </span>
                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter">
                                    <i data-lucide="clock" class="w-3 h-3 inline mr-1 mb-0.5"></i>
                                    <?php echo date('H:i', strtotime($row['created_at'])); ?> hrs
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-10">
                        <p class="text-slate-400 text-sm font-medium italic">No recent activity detected.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-blue-600 dark:bg-slate-900 rounded-[2.5rem] p-10 text-white relative overflow-hidden shadow-xl border border-transparent dark:border-slate-800">
            <div class="relative z-10 h-full flex flex-col">
                <div class="flex items-center gap-3 mb-8">
                    <div class="bg-white/20 p-2 rounded-xl backdrop-blur-md border border-white/10">
                        <i data-lucide="cpu" class="w-5 h-5 text-white"></i>
                    </div>
                    <h3 class="text-sm font-black uppercase tracking-[0.2em]">Session Insights</h3>
                </div>
                
                <div class="space-y-4 flex-grow">
                    <div class="flex items-center justify-between py-3 border-b border-white/10">
                        <span class="text-xs font-bold uppercase tracking-widest opacity-60">Security Context</span>
                        <span class="text-[10px] font-black bg-white/20 px-3 py-1 rounded-full uppercase tracking-widest">Auth_Level_04</span>
                    </div>
                    <div class="flex items-center justify-between py-3 border-b border-white/10">
                        <span class="text-xs font-bold uppercase tracking-widest opacity-60">Working Branch</span>
                        <span class="text-xs font-mono font-bold italic">main/stable</span>
                    </div>
                    <div class="flex items-center justify-between py-3">
                        <span class="text-xs font-bold uppercase tracking-widest opacity-60">Database Link</span>
                        <span class="text-xs font-black text-emerald-300 flex items-center gap-1.5">
                            <i data-lucide="lock" class="w-3 h-3"></i> Encrypted
                        </span>
                    </div>
                </div>

                <div class="mt-10 p-5 bg-white/10 backdrop-blur-lg rounded-3xl border border-white/10">
                    <div class="flex gap-4">
                        <i data-lucide="info" class="w-5 h-5 text-blue-200 shrink-0"></i>
                        <p class="text-[11px] font-bold leading-relaxed text-blue-50">
                            You are currently in the developer workspace. All system logs, global tickets, and sensitive deployment protocols are accessible from this terminal.
                        </p>
                    </div>
                </div>
            </div>
            
            <i data-lucide="fingerprint" class="absolute -right-8 -bottom-8 w-48 h-48 text-white/10 -rotate-12"></i>
        </div>

    </div>
</div>

<script>
    // Initialize icons for AJAX loaded content
    if (window.lucide) {
        lucide.createIcons();
    }
</script>