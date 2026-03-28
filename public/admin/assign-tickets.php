<?php
session_start();
include('../../config/config.php');

// ✅ Admin Security
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    die("<div class='p-6 text-red-500 font-bold'>Unauthorized Access</div>");
}

// 1. Fetch Developers once to save resources
$dev_query = "SELECT id, name FROM Users WHERE role = 'developer' ORDER BY name ASC";
$dev_result = mysqli_query($connection, $dev_query);
$developers = [];
while ($dev = mysqli_fetch_assoc($dev_result)) {
    $developers[] = $dev;
}

// 2. Fetch Unassigned Tickets
$sql = "SELECT t.id, t.title, t.description, p.name AS project_name, t.created_at
        FROM Tickets t
        LEFT JOIN Projects p ON t.project_id = p.id
        WHERE t.assigned_to IS NULL OR t.assigned_to = 0
        ORDER BY t.created_at DESC";
$result = mysqli_query($connection, $sql);
?>

<div class="animate-fade-in">
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">Pending Assignments</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm">Distribute unassigned tickets to your development team.</p>
        </div>
        <div class="bg-emerald-100 text-emerald-700 px-4 py-2 rounded-2xl text-xs font-black uppercase tracking-widest">
            <?php echo mysqli_num_rows($result); ?> Tickets Waiting
        </div>
    </div>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[2rem] shadow-sm overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">ID</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Ticket Details</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Project</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Assign Dev</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <?php while ($ticket = mysqli_fetch_assoc($result)): ?>
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-all">
                            <td class="px-6 py-5 text-xs font-mono text-slate-400 text-center">#<?php echo $ticket['id']; ?></td>
                            <td class="px-6 py-5">
                                <div class="font-bold text-slate-900 dark:text-white leading-tight mb-1">
                                    <?php echo htmlspecialchars($ticket['title']); ?>
                                </div>
                                <div class="text-xs text-slate-500 line-clamp-1 italic">
                                    <?php echo htmlspecialchars($ticket['description']); ?>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <span class="text-xs font-bold text-slate-600 dark:text-slate-400 flex items-center gap-1.5">
                                    <i data-lucide="folder-dot" class="w-3.5 h-3.5 text-emerald-500"></i>
                                    <?php echo htmlspecialchars($ticket['project_name'] ?? 'Internal'); ?>
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                <form class="assign-form flex items-center gap-2">
                                    <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                    <select name="developer_id" required 
                                            class="bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-xs font-bold px-3 py-2 focus:ring-2 focus:ring-emerald-500 outline-none cursor-pointer w-full max-w-[180px]">
                                        <option value="">Choose Developer...</option>
                                        <?php foreach ($developers as $dev): ?>
                                            <option value="<?php echo $dev['id']; ?>"><?php echo htmlspecialchars($dev['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                            </td>
                            <td class="px-6 py-5 text-right">
                                    <button type="submit" 
                                            class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-tighter transition-all shadow-md shadow-emerald-100 dark:shadow-none">
                                        Deploy
                                        <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[2rem] p-20 text-center shadow-sm">
            <div class="w-20 h-20 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6">
                <i data-lucide="check-circle-2" class="w-10 h-10"></i>
            </div>
            <h2 class="text-2xl font-black text-slate-900 dark:text-white mb-2">Queue Clear!</h2>
            <p class="text-slate-500 max-w-sm mx-auto">Every ticket has been assigned to a developer. Your backlog is fully operational.</p>
        </div>
    <?php endif; ?>
</div>

<script>
    lucide.createIcons();

    // ⚡ AJAX Assignment Logic
    $('.assign-form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const btn = form.find('button');
        const originalText = btn.html();

        btn.prop('disabled', true).html('<i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin"></i>');
        lucide.createIcons();

        $.ajax({
            url: './actions/assign-tickets-process.php',
            method: 'POST',
            data: form.serialize(),
            dataType: 'json', // Expecting JSON from your process file
            success: function(res) {
                if(res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Assigned!',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    // Remove the row from the UI smoothly
                    form.closest('tr').fadeOut(400, function() {
                        $(this).remove();
                        // If no more rows, refresh page content to show the "Queue Clear" message
                        if ($('tbody tr').length === 0) loadPage('assign-tickets');
                    });
                } else {
                    Swal.fire('Error', res.error, 'error');
                    btn.prop('disabled', false).html(originalText);
                    lucide.createIcons();
                }
            },
            error: function() {
                Swal.fire('System Error', 'Check database connection.', 'error');
                btn.prop('disabled', false).html(originalText);
                lucide.createIcons();
            }
        });
    });
</script>