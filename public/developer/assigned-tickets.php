<?php
session_start();
include('../../config/config.php');

// Security Check
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'developer') {
    die("<div class='p-12 text-center text-red-500 font-bold uppercase tracking-widest text-xs'>Access Denied. Insufficient Permissions.</div>");
}

$user_id = (int)$_SESSION['user_id'];

// Fetch Tickets with Prepared Statement
$query = "SELECT t.id, t.title, t.description, t.status, t.created_at, p.name AS project_name
          FROM Tickets t
          LEFT JOIN Projects p ON t.project_id = p.id
          WHERE t.assigned_to = ?
          ORDER BY t.created_at DESC";

$stmt = $connection->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Status Styling Mapping
$status_map = [
    'open'        => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    'in-progress' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    'resolved'    => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
    'closed'      => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400',
    'on-hold'     => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400'
];
?>

<div class="animate-fade-in p-2">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Assigned to Me</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1">Manage your active workload and technical tasks.</p>
        </div>
        
        <div class="flex items-center gap-3">
            <div class="flex flex-col items-end">
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Queue Status</span>
                <span class="inline-flex items-center gap-2 text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 px-4 py-2 rounded-2xl border border-indigo-100 dark:border-indigo-800">
                    <span class="relative flex h-2 w-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                    </span>
                    <?php echo $result->num_rows; ?> Active Tickets
                </span>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Ref</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Issue / Title</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Project</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Status</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): 
                            $raw_status = strtolower($row['status']);
                            $current_status_class = $status_map[$raw_status] ?? 'bg-slate-100 text-slate-600';
                        ?>
                            <tr class="group hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-all">
                                <td class="px-6 py-5">
                                    <span class="text-xs font-mono font-bold text-slate-400 bg-slate-50 dark:bg-slate-800 px-2 py-1 rounded">
                                        #<?php echo $row['id']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="font-bold text-slate-900 dark:text-white truncate max-w-xs group-hover:text-indigo-600 transition-colors">
                                        <?php echo htmlspecialchars($row['title']); ?>
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-1 flex items-center gap-1">
                                        <i data-lucide="calendar" class="w-3 h-3"></i>
                                        <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-600 dark:text-slate-400">
                                        <div class="w-2 h-2 rounded-full bg-indigo-400"></div>
                                        <?php echo htmlspecialchars($row['project_name'] ?? 'Internal Task'); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tight <?php echo $current_status_class; ?>">
                                        <?php echo str_replace('-', ' ', htmlspecialchars($row['status'])); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <button onclick="loadPage('ticket-details', 'ticket_id=<?php echo $row['id']; ?>')" 
                                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-900 dark:bg-indigo-600 hover:bg-indigo-600 dark:hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-lg shadow-slate-200 dark:shadow-none active:scale-95">
                                        View Details
                                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-32 text-center">
                                <div class="max-w-xs mx-auto">
                                    <div class="w-20 h-20 bg-indigo-50 dark:bg-slate-800 text-indigo-500 dark:text-indigo-400 rounded-3xl flex items-center justify-center mx-auto mb-6 rotate-12 group-hover:rotate-0 transition-transform">
                                        <i data-lucide="inbox" class="w-10 h-10"></i>
                                    </div>
                                    <h3 class="text-xl font-black text-slate-900 dark:text-white">All Caught Up!</h3>
                                    <p class="text-sm text-slate-500 mt-2 italic">No pending tickets found for your account. Enjoy the quiet while it lasts.</p>
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
    if (window.lucide) {
        lucide.createIcons();
    }
</script>