<?php
/**
 * File: admin/assign-tickets.php
 * Purpose: Strategic Ticket Deployment & Resource Allocation Matrix.
 */
session_start();
require_once('../../config/config.php');

// 1. 🛡️ Admin Security Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    die("<div class='p-12 text-rose-500 font-black text-center bg-rose-50 dark:bg-rose-950/20 rounded-3xl'>Access Denied: Administrative Clearance Required</div>");
}

// 2. 📡 Fetch Available Developers (REMOVED 'status' filter to fix the SQL error)
$dev_query = "SELECT id, name FROM Users WHERE role = 'developer' ORDER BY name ASC";
$dev_result = mysqli_query($connection, $dev_query);
$developers = [];
if ($dev_result) {
    while ($dev = mysqli_fetch_assoc($dev_result)) { 
        $developers[] = $dev; 
    }
}

// 3. 📡 Fetch Unallocated Tickets (Soft Delete aware & Priority Ordered)
$sql = "SELECT t.id, t.title, t.priority, p.name AS project_name, c.name AS category_name
        FROM Tickets t
        LEFT JOIN Projects p ON t.project_id = p.id
        LEFT JOIN Categories c ON t.category_id = c.id
        WHERE (t.assigned_to IS NULL OR t.assigned_to = 0) 
        AND t.deleted_at IS NULL
        ORDER BY CASE t.priority 
            WHEN 'high' THEN 1 
            WHEN 'medium' THEN 2 
            WHEN 'low' THEN 3 ELSE 4 END ASC, t.created_at DESC";
            
$result = mysqli_query($connection, $sql);
$waiting_count = $result ? mysqli_num_rows($result) : 0;
?>

<div class="animate-in fade-in slide-in-from-bottom-4 duration-700 p-4 md:p-8">
    <div class="mb-10 flex flex-col lg:flex-row lg:items-end justify-between gap-6">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <span class="px-3 py-1 bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 rounded-lg text-[10px] font-black uppercase tracking-widest italic border border-amber-200/50">Action Required</span>
                <span class="text-slate-400 text-[10px] font-black uppercase tracking-widest"><?php echo $waiting_count; ?> Resources Unallocated</span>
            </div>
            <h1 class="text-4xl font-black tracking-tighter text-slate-900 dark:text-white">Assignment Matrix</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Deploy pending technical intel to available development nodes.</p>
        </div>
        
        <div class="hidden lg:block">
            <div class="flex -space-x-3">
                <?php foreach(array_slice($developers, 0, 5) as $d): ?>
                    <div title="<?php echo htmlspecialchars($d['name']); ?>" class="w-10 h-10 rounded-full border-4 border-white dark:border-slate-900 bg-indigo-600 flex items-center justify-center text-white text-[10px] font-black shadow-lg">
                        <?php echo strtoupper(substr($d['name'], 0, 1)); ?>
                    </div>
                <?php endforeach; ?>
                <?php if(count($developers) > 5): ?>
                    <div class="w-10 h-10 rounded-full border-4 border-white dark:border-slate-900 bg-slate-100 dark:bg-slate-800 text-slate-500 flex items-center justify-center text-[10px] font-black">+<?php echo count($developers)-5; ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($waiting_count > 0): ?>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[2.5rem] overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Registry Ref</th>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Intelligence Brief</th>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Target Deployment</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                        <?php while ($ticket = mysqli_fetch_assoc($result)): ?>
                            <?php 
                                $prioStyles = [
                                    'high' => 'text-rose-500 bg-rose-50 dark:bg-rose-900/20 border-rose-100',
                                    'medium' => 'text-amber-500 bg-amber-50 dark:bg-amber-900/20 border-amber-100',
                                    'low' => 'text-emerald-500 bg-emerald-50 dark:bg-emerald-900/20 border-emerald-100'
                                ];
                                $p_class = $prioStyles[strtolower($ticket['priority'])] ?? 'bg-slate-100 text-slate-500';
                            ?>
                            <tr class="hover:bg-slate-50/30 dark:hover:bg-slate-800/20 transition-all group">
                                <td class="px-8 py-5">
                                    <span class="font-mono text-[10px] font-black text-slate-300 dark:text-slate-600 group-hover:text-indigo-500 transition-colors">
                                        #TK-<?php echo str_pad($ticket['id'], 4, '0', STR_PAD_LEFT); ?>
                                    </span>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="font-bold text-slate-700 dark:text-slate-200"><?php echo htmlspecialchars($ticket['title']); ?></div>
                                    <div class="flex items-center gap-2 mt-1.5">
                                        <span class="text-[9px] font-black uppercase tracking-tighter text-indigo-500"><?php echo htmlspecialchars($ticket['project_name'] ?? 'General'); ?></span>
                                        <span class="w-1 h-1 rounded-full bg-slate-300 dark:bg-slate-700"></span>
                                        <span class="text-[9px] font-black uppercase tracking-tighter text-slate-400"><?php echo htmlspecialchars($ticket['category_name'] ?? 'Task'); ?></span>
                                        <span class="px-2 py-0.5 rounded-lg text-[8px] font-black uppercase tracking-widest border <?php echo $p_class; ?>">
                                            <?php echo $ticket['priority']; ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <form class="assign-form-submit flex items-center gap-3 justify-end">
                                        <input type="hidden" name="ticketId" value="<?php echo $ticket['id']; ?>">
                                        
                                        <div class="relative w-full max-w-[200px]">
                                            <select name="assignedTo" required 
                                                    class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-[11px] font-black uppercase tracking-widest px-5 py-3.5 appearance-none cursor-pointer focus:ring-2 focus:ring-indigo-500 outline-none text-slate-600 dark:text-slate-300">
                                                <option value="" disabled selected>Allocate Node...</option>
                                                <?php foreach ($developers as $dev): ?>
                                                    <option value="<?php echo $dev['id']; ?>"><?php echo htmlspecialchars($dev['name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <i data-lucide="chevron-down" class="absolute right-4 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400 pointer-events-none"></i>
                                        </div>

                                        <button type="submit" 
                                                class="deploy-btn inline-flex items-center gap-2 px-6 py-3.5 bg-slate-900 dark:bg-indigo-600 hover:bg-indigo-500 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] transition-all shadow-lg shadow-indigo-500/10 active:scale-95">
                                            <i data-lucide="send" class="w-3.5 h-3.5"></i>
                                            Deploy
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[3rem] p-24 text-center">
            <div class="inline-flex p-6 rounded-full bg-emerald-50 dark:bg-emerald-950/20 text-emerald-500 mb-6">
                <i data-lucide="check-circle-2" class="w-12 h-12"></i>
            </div>
            <h2 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tighter">Registry Optimized</h2>
            <p class="text-slate-500 text-sm mt-2 font-medium">All active intelligence tickets have been deployed to developer nodes.</p>
        </div>
    <?php endif; ?>
</div>

<script>
    if(window.lucide) lucide.createIcons();

    // Ensure we don't double-bind the submit event
    $(document).off('submit', '.assign-form-submit').on('submit', '.assign-form-submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const btn = form.find('.deploy-btn');
        const devId = form.find('select[name="assignedTo"]').val();

        if(!devId) {
            Swal.fire({ 
                icon: 'warning', 
                title: 'Deployment Blocked', 
                text: 'A target developer node must be authorized for this task.',
                background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#fff',
                color: document.documentElement.classList.contains('dark') ? '#fff' : '#000'
            });
            return;
        }

        // Visual feedback during sync
        btn.prop('disabled', true).addClass('opacity-50').html('<i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin"></i> SYNCING...');
        if(window.lucide) lucide.createIcons();

        $.ajax({
            url: './actions/assign-tickets-process.php', 
            method: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(res) {
                if(res.success) {
                    Swal.fire({ 
                        icon: 'success', 
                        title: 'DEPLOYMENT SUCCESSFUL', 
                        text: res.message, 
                        timer: 1500, 
                        showConfirmButton: false,
                        background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#fff',
                        color: document.documentElement.classList.contains('dark') ? '#fff' : '#000'
                    });
                    
                    // Smooth row removal
                    form.closest('tr').addClass('scale-95 opacity-0 transition-all duration-500 pointer-events-none');
                    setTimeout(() => {
                        loadPage('assign-tickets');
                    }, 500);
                } else {
                    Swal.fire('DEPLOYMENT ERROR', res.error, 'error');
                    resetDeployBtn(btn);
                }
            },
            error: function() {
                Swal.fire('SYSTEM FAILURE', 'The assignment engine encountered a critical communication error.', 'error');
                resetDeployBtn(btn);
            }
        });
    });

    function resetDeployBtn(btn) {
        btn.prop('disabled', false).removeClass('opacity-50').html('<i data-lucide="send" class="w-3.5 h-3.5"></i> Deploy');
        if(window.lucide) lucide.createIcons();
    }
</script>