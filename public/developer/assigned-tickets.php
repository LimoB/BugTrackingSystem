<?php
session_start();
include('../../config/config.php');

/**
 * File: developer/assigned-tickets.php
 * Purpose: A high-density worklist for the logged-in developer.
 * Logic: Filters by session user_id and sorts by technical priority.
 */

// 🛡️ Security Check: Developer Access Only
if (!isset($_SESSION['user_id']) && !isset($_SESSION['id'])) {
    die("<div class='p-12 text-center text-red-500 font-black uppercase tracking-widest text-xs'>Session Expired. Please Login.</div>");
}

if (strtolower($_SESSION['role'] ?? '') !== 'developer') {
    die("<div class='p-12 text-center text-red-500 font-black uppercase tracking-widest text-xs'>Access Denied. Insufficient Permissions.</div>");
}

// Support for both common session key variations
$user_id = (int)($_SESSION['user_id'] ?? $_SESSION['id']);

// 🔍 Fetch Tickets with Prepared Statement
// Joining Projects for context; Order by Priority (Critical first) then Date
$query = "SELECT t.id, t.title, t.description, t.status, t.created_at, t.priority, p.name AS project_name
          FROM Tickets t
          LEFT JOIN Projects p ON t.project_id = p.id
          WHERE t.assigned_to = ? AND t.status != 'closed'
          ORDER BY 
            CASE 
                WHEN t.priority = 'critical' THEN 1 
                WHEN t.priority = 'high' THEN 2 
                WHEN t.priority = 'medium' THEN 3 
                ELSE 4 
            END, t.created_at DESC";

$stmt = $connection->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// 🎨 Status & Priority Styling
$status_map = [
    'open'        => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    'in-progress' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    'resolved'    => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
    'on-hold'     => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400'
];

$priority_map = [
    'critical' => 'text-rose-600 dark:text-rose-400 font-black',
    'high'     => 'text-orange-500 dark:text-orange-400 font-bold',
    'medium'   => 'text-sky-500 dark:text-sky-400',
    'low'      => 'text-slate-400'
];
?>

<div class="animate-in fade-in slide-in-from-bottom-4 duration-500 p-2">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-10 gap-6">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="p-2 bg-indigo-600 rounded-lg">
                    <i data-lucide="terminal" class="w-5 h-5 text-white"></i>
                </div>
                <h1 class="text-3xl font-black tracking-tighter text-slate-900 dark:text-white uppercase">Development Queue</h1>
            </div>
            <p class="text-slate-500 dark:text-slate-400 font-medium italic text-sm ml-12">Active assignments and technical debt.</p>
        </div>
        
        <div class="bg-white dark:bg-slate-900 px-6 py-4 rounded-[2rem] border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-5">
            <div class="flex flex-col items-end">
                <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">Total Backlog</span>
                <span class="text-xl font-black text-indigo-600 dark:text-indigo-400 leading-none"><?php echo $result->num_rows; ?> Tickets</span>
            </div>
            <div class="w-px h-10 bg-slate-100 dark:bg-slate-800"></div>
            <span class="relative flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-indigo-500"></span>
            </span>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-200 dark:border-slate-800 shadow-xl shadow-slate-200/40 dark:shadow-none overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                        <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Ref</th>
                        <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Issue Description</th>
                        <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Severity</th>
                        <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Status</th>
                        <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): 
                            $raw_status = strtolower($row['status']);
                            $current_status_class = $status_map[$raw_status] ?? 'bg-slate-100 text-slate-600';
                            $priority_class = $priority_map[strtolower($row['priority'])] ?? 'text-slate-400';
                        ?>
                            <tr class="group hover:bg-slate-50/50 dark:hover:bg-indigo-900/10 transition-all duration-200">
                                <td class="px-8 py-6">
                                    <span class="text-xs font-mono font-bold text-slate-400 bg-slate-50 dark:bg-slate-800 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 group-hover:text-indigo-600 transition-colors">
                                        #<?php echo str_pad($row['id'], 3, '0', STR_PAD_LEFT); ?>
                                    </span>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="font-bold text-slate-800 dark:text-white truncate max-w-sm group-hover:text-indigo-600 transition-colors uppercase tracking-tight">
                                        <?php echo htmlspecialchars($row['title']); ?>
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-1 flex items-center gap-2 font-bold">
                                        <i data-lucide="folder-git-2" class="w-3 h-3 text-indigo-500"></i>
                                        <span class="uppercase tracking-widest"><?php echo htmlspecialchars($row['project_name'] ?? 'Unassigned Project'); ?></span>
                                        <span class="mx-1 text-slate-300">•</span>
                                        <span><?php echo strtoupper(date('M d, Y', strtotime($row['created_at']))); ?></span>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-2 <?php echo $priority_class; ?>">
                                        <div class="w-1 h-1 rounded-full bg-current"></div>
                                        <span class="text-[10px] font-black uppercase tracking-widest italic"><?php echo $row['priority']; ?></span>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest <?php echo $current_status_class; ?>">
                                        <?php echo str_replace('-', ' ', htmlspecialchars($row['status'])); ?>
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <button onclick="loadPage('ticket-details', 'ticket_id=<?php echo $row['id']; ?>')" 
                                            class="inline-flex items-center gap-3 px-6 py-3 bg-slate-900 dark:bg-indigo-600 hover:bg-indigo-500 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all shadow-lg active:scale-95 group-hover:shadow-indigo-200 dark:group-hover:shadow-none">
                                        Inspect
                                        <i data-lucide="external-link" class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-8 py-40 text-center">
                                <div class="max-w-md mx-auto">
                                    <div class="w-24 h-24 bg-indigo-50 dark:bg-slate-800 text-indigo-500 rounded-[2.5rem] flex items-center justify-center mx-auto mb-8 animate-pulse">
                                        <i data-lucide="coffee" class="w-10 h-10"></i>
                                    </div>
                                    <h3 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tighter">Queue Decoupled</h3>
                                    <p class="text-slate-500 mt-3 font-medium px-10 leading-relaxed italic">Your technical queue is currently empty. All assigned tasks have been processed.</p>
                                    <button onclick="loadPage('dashboard_home')" class="mt-8 text-indigo-600 font-black text-[10px] uppercase tracking-[0.3em] border-b-2 border-indigo-600 pb-1 hover:text-indigo-400 hover:border-indigo-400 transition-all">Return to Dashboard</button>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Re-initialize icons for AJAX content
    if (window.lucide) {
        lucide.createIcons();
    }
    
    // Auto-scroll to top for smooth transition
    window.scrollTo({ top: 0, behavior: 'smooth' });
</script>