<?php
session_start();
include('../../config/config.php');

// ✅ Security Check
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'developer') {
    die("<div class='p-6 text-red-500'>Access Denied.</div>");
}

$user_id = $_SESSION['user_id'];

// ✅ Fetch Tickets with Prepared Statement
$query = "SELECT t.id, t.title, t.description, t.status, t.created_at, p.name AS project_name
          FROM Tickets t
          LEFT JOIN Projects p ON t.project_id = p.id
          WHERE t.assigned_to = ?
          ORDER BY t.created_at DESC";

$stmt = $connection->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="animate-fade-in">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight">Assigned to Me</h1>
            <p class="text-slate-500 dark:text-slate-400">Manage your active workload and technical tasks.</p>
        </div>
        
        <div class="flex items-center gap-3">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest bg-slate-100 dark:bg-slate-800 px-3 py-1 rounded-lg">
                Total: <?php echo $result->num_rows; ?>
            </span>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">ID</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Issue / Title</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Project</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Status</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): 
                            // Map status to colors
                            $status_class = [
                                'open' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                'in-progress' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                'resolved' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                'closed' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400',
                                'on-hold' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400'
                            ][strtolower($row['status'])] ?? 'bg-slate-100';
                        ?>
                            <tr class="group hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-all">
                                <td class="px-6 py-5 text-sm font-mono text-slate-400">#<?php echo $row['id']; ?></td>
                                <td class="px-6 py-5">
                                    <div class="font-bold text-slate-900 dark:text-white truncate max-w-xs group-hover:text-indigo-600 transition-colors">
                                        <?php echo htmlspecialchars($row['title']); ?>
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        Created <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-sm font-medium">
                                    <span class="flex items-center gap-1.5 text-slate-600 dark:text-slate-400">
                                        <i data-lucide="folder" class="w-3.5 h-3.5 opacity-50"></i>
                                        <?php echo htmlspecialchars($row['project_name'] ?? 'Internal'); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tight <?php echo $status_class; ?>">
                                        <?php echo str_replace('-', ' ', $row['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <button onclick="loadPage('ticket-details&ticket_id=<?php echo $row['id']; ?>')" 
                                            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-indigo-100 dark:shadow-none">
                                        Open Issue
                                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-24 text-center">
                                <div class="max-w-xs mx-auto">
                                    <div class="w-16 h-16 bg-slate-100 dark:bg-slate-800 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i data-lucide="check-circle" class="w-8 h-8"></i>
                                    </div>
                                    <h3 class="text-lg font-bold">Inbox Zero!</h3>
                                    <p class="text-sm text-slate-500 italic">You have no tickets currently assigned to you. Time for a coffee?</p>
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
    // Refresh icons for AJAX loaded content
    lucide.createIcons();
</script>