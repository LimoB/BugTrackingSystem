<?php
session_start();
include('../../config/config.php');

// ✅ Admin Security Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    die("<div class='p-12 text-rose-500 font-black text-center bg-rose-50 rounded-3xl'>Access Denied: Administrative Clearance Required</div>");
}

// Fetch tickets with Project and Developer info
$query = "SELECT t.id, t.title, t.status, p.name AS project_name, u.name AS developer_name 
          FROM Tickets t 
          LEFT JOIN Projects p ON t.project_id = p.id
          LEFT JOIN Users u ON t.assigned_to = u.id
          ORDER BY t.id DESC";

$result = mysqli_query($connection, $query);
$ticket_count = mysqli_num_rows($result);
?>

<div class="animate-fade-in">
    <div class="mb-10 flex flex-col lg:flex-row lg:items-end justify-between gap-6">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <span class="px-3 py-1 bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 rounded-lg text-[10px] font-black uppercase tracking-widest">System Monitor</span>
                <span class="text-slate-400 text-[10px] font-bold uppercase tracking-widest"><?php echo $ticket_count; ?> Total Entries</span>
            </div>
            <h1 class="text-4xl font-black tracking-tighter text-slate-900 dark:text-white">Global Backlog</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Orchestrate development workflow and moderate ticket lifecycles.</p>
        </div>
        
        <button onclick="openTicketModal('create')" class="group inline-flex items-center gap-3 px-8 py-4 bg-slate-900 dark:bg-white dark:text-slate-900 text-white rounded-2xl text-xs font-black uppercase tracking-widest transition-all hover:bg-indigo-600 dark:hover:bg-indigo-500 dark:hover:text-white shadow-xl shadow-slate-200 dark:shadow-none hover:-translate-y-1">
            <i data-lucide="plus-square" class="w-4 h-4 transition-transform group-hover:rotate-90"></i>
            Initialize New Ticket
        </button>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[2.5rem] overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Ref</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Issue Intel</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Workstream</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Assigned To</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">State</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Operations</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <?php if($ticket_count > 0): ?>
                        <?php while ($ticket = mysqli_fetch_assoc($result)): 
                            $status_colors = [
                                'open'        => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                'in-progress' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                'resolved'    => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
                                'closed'      => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-500',
                                'on-hold'     => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'
                            ];
                            $status_class = $status_colors[strtolower($ticket['status'])] ?? 'bg-slate-50';
                        ?>
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-all group" id="row-<?php echo $ticket['id']; ?>">
                            <td class="px-8 py-5">
                                <span class="font-mono text-[10px] font-black text-slate-300 group-hover:text-indigo-500 transition-colors">
                                    #<?php echo str_pad($ticket['id'], 4, '0', STR_PAD_LEFT); ?>
                                </span>
                            </td>
                            <td class="px-8 py-5 max-w-[240px]">
                                <div class="font-bold text-slate-700 dark:text-slate-200 truncate" title="<?php echo htmlspecialchars($ticket['title']); ?>">
                                    <?php echo htmlspecialchars($ticket['title']); ?>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-2">
                                    <div class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-700"></div>
                                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400">
                                        <?php echo htmlspecialchars($ticket['project_name'] ?? 'General Backlog'); ?>
                                    </span>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-3">
                                    <?php if($ticket['developer_name']): ?>
                                        <div class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-[10px] font-black border border-indigo-100 dark:border-indigo-800/50">
                                            <?php echo strtoupper(substr($ticket['developer_name'], 0, 1)); ?>
                                        </div>
                                        <span class="text-xs font-bold text-slate-600 dark:text-slate-300 italic"><?php echo htmlspecialchars($ticket['developer_name']); ?></span>
                                    <?php else: ?>
                                        <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Unassigned</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest <?php echo $status_class; ?> border border-transparent group-hover:border-current/10 transition-all">
                                    <?php echo str_replace('-', ' ', $ticket['status']); ?>
                                </span>
                            </td>
                            <td class="px-8 py-5 text-right space-x-1">
                                <button onclick="viewTicket(<?php echo $ticket['id']; ?>)" class="p-2.5 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 text-slate-400 hover:text-indigo-600 rounded-xl transition-all" title="Inspect">
                                    <i data-lucide="maximize-2" class="w-4 h-4"></i>
                                </button>
                                <button onclick="editTicket(<?php echo $ticket['id']; ?>)" class="p-2.5 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 text-slate-400 hover:text-emerald-600 rounded-xl transition-all" title="Modify">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </button>
                                <button onclick="deleteTicket(<?php echo $ticket['id']; ?>)" class="p-2.5 hover:bg-rose-50 dark:hover:bg-rose-900/30 text-slate-400 hover:text-rose-600 rounded-xl transition-all" title="Purge">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-8 py-20 text-center">
                                <div class="flex flex-col items-center opacity-20">
                                    <i data-lucide="inbox" class="w-16 h-16 mb-4"></i>
                                    <p class="text-xl font-black uppercase tracking-widest">Backlog Clear</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="ticketModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 sm:p-6">
    <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-md" onclick="closeTicketModal()"></div>
    <div class="relative w-full max-w-2xl transform transition-all">
        <div class="bg-white dark:bg-slate-900 rounded-[3rem] shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-800">
            <div id="modalBody" class="p-10 max-h-[90vh] overflow-y-auto custom-scrollbar">
                </div>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();

    function openTicketModal(type, id = null) {
        const modal = $('#ticketModal');
        const body = $('#modalBody');
        modal.removeClass('hidden').addClass('flex');

        if (type === 'create') {
            body.html(`
                <div class="flex flex-col items-center justify-center p-20 text-indigo-500">
                    <div class="animate-spin mb-4"><i data-lucide="loader-2" class="w-10 h-10"></i></div>
                    <p class="font-black text-[10px] uppercase tracking-[0.2em]">Building Creation Matrix...</p>
                </div>
            `);
            lucide.createIcons();
            $.get('./api/fetch-create-ticket-form.php', function(data) {
                body.html(data);
                lucide.createIcons();
            });
        }
    }

    function closeTicketModal() {
        $('#ticketModal').addClass('hidden').removeClass('flex');
    }

    function viewTicket(id) {
        $('#ticketModal').removeClass('hidden').addClass('flex');
        $('#modalBody').html(`
            <div class="flex flex-col items-center justify-center p-20 text-slate-400">
                <div class="animate-pulse mb-4"><i data-lucide="shield-search" class="w-10 h-10"></i></div>
                <p class="font-black text-[10px] uppercase tracking-[0.2em]">Decrypting Intel...</p>
            </div>
        `);
        lucide.createIcons();
        $.get('./api/fetch-ticket-details.php', { ticket_id: id }, function(data) {
            $('#modalBody').html(data);
            lucide.createIcons();
        });
    }

    function editTicket(id) {
        $('#ticketModal').removeClass('hidden').addClass('flex');
        $('#modalBody').html(`
            <div class="flex flex-col items-center justify-center p-20 text-emerald-500">
                <div class="animate-bounce mb-4"><i data-lucide="terminal" class="w-10 h-10"></i></div>
                <p class="font-black text-[10px] uppercase tracking-[0.2em]">Syncing Configuration...</p>
            </div>
        `);
        lucide.createIcons();
        $.get('./api/fetch-ticket-update-form.php', { ticket_id: id }, function(data) {
            $('#modalBody').html(data);
            lucide.createIcons();
        });
    }

    function deleteTicket(id) {
        Swal.fire({
            title: 'Purge Ticket?',
            text: "Warning: This permanently removes all associated lifecycle data.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#f43f5e',
            confirmButtonText: 'Confirm Purge',
            background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#fff',
            color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#0f172a'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('./actions/delete-ticket-action.php', { ticket_id: id }, function(res) {
                    try {
                        const data = JSON.parse(res);
                        if(data.success) {
                            $(`#row-${id}`).addClass('scale-90 opacity-0 transition-all duration-500 pointer-events-none');
                            setTimeout(() => loadPage('manage-tickets'), 500);
                            Swal.fire('Purged', data.success, 'success');
                        } else {
                            Swal.fire('Error', data.error, 'error');
                        }
                    } catch(e) {
                        Swal.fire('Error', 'System returned malformed response.', 'error');
                    }
                });
            }
        });
    }
</script>