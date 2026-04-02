<?php
session_start();
include('../../config/config.php');

/**
 * File: developer/view-tickets.php
 * Purpose: Global visibility for developers with Self-Assignment capability.
 */

// ✅ 1. Security & Role Check
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'developer') {
    die("<div class='p-12 text-center text-red-500 font-black tracking-widest uppercase text-xs'>Unauthorized Access Protocol.</div>");
}

$current_user_id = intval($_SESSION['user_id']);

// ✅ 2. Logic: Handle Self-Assignment (AJAX POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'claim_ticket') {
    header('Content-Type: application/json');
    $ticket_id = intval($_POST['ticket_id']);

    // Verify ticket is still unassigned before claiming
    $check = $connection->prepare("SELECT assigned_to FROM Tickets WHERE id = ?");
    $check->bind_param("i", $ticket_id);
    $check->execute();
    $res = $check->get_result()->fetch_assoc();

    if ($res && is_null($res['assigned_to'])) {
        $update = $connection->prepare("UPDATE Tickets SET assigned_to = ?, status = 'in-progress' WHERE id = ?");
        $update->bind_param("ii", $current_user_id, $ticket_id);
        
        if ($update->execute()) {
            // Log the activity
            $log_msg = "Developer #" . $current_user_id . " claimed ownership of Ticket #$ticket_id";
            $log = $connection->prepare("INSERT INTO activity_log (user_id, action_type, description, ticket_id, created_at) VALUES (?, 'TICKET_CLAIM', ?, ?, NOW())");
            $log->bind_param("isi", $current_user_id, $log_msg, $ticket_id);
            $log->execute();

            echo json_encode(['success' => true, 'message' => 'Ticket successfully linked to your terminal.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database write failure.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Ticket already assigned to another node.']);
    }
    exit();
}

// ✅ 3. AJAX Detail Fetcher (for Quick View)
if (isset($_GET['ajax']) && $_GET['ajax'] === 'true' && isset($_GET['ticket_id'])) {
    header('Content-Type: application/json');
    $ticket_id = intval($_GET['ticket_id']);
    
    $query = "SELECT t.*, p.name AS project_name, u.name AS creator_name, dev.name AS developer_name
              FROM Tickets t 
              LEFT JOIN Projects p ON t.project_id = p.id 
              LEFT JOIN Users u ON t.created_by = u.id
              LEFT JOIN Users dev ON t.assigned_to = dev.id
              WHERE t.id = ?";
    
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $ticket_id);
    $stmt->execute();
    $ticket = $stmt->get_result()->fetch_assoc();
    
    if (!$ticket) {
        echo json_encode(['error' => 'Reference missing.']);
        exit();
    }
    
    $c_query = "SELECT u.name, c.comment, c.created_at FROM Comments c
                JOIN Users u ON c.user_id = u.id
                WHERE c.ticket_id = ? ORDER BY c.created_at DESC LIMIT 3";
    $c_stmt = $connection->prepare($c_query);
    $c_stmt->bind_param("i", $ticket_id);
    $c_stmt->execute();
    $comments = $c_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['ticket' => $ticket, 'comments' => $comments]);
    exit();
}
?>

<div class="animate-in fade-in slide-in-from-bottom-4 duration-500 p-2">
    <div class="mb-10 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black tracking-tighter text-slate-900 dark:text-white uppercase">System-Wide Backlog</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1 italic text-sm font-medium ml-1">Claim unassigned tasks to begin resolution protocols.</p>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[2.5rem] overflow-hidden shadow-2xl shadow-slate-200/50 dark:shadow-none transition-all">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                        <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Ref Code</th>
                        <th class="px-6 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Technical Issue</th>
                        <th class="px-6 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Ownership</th>
                        <th class="px-6 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Status</th>
                        <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 text-right">Operations</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <?php
                    $all_tickets = mysqli_query($connection, "SELECT t.id, t.title, t.status, t.assigned_to, u.name as dev_name 
                                                              FROM Tickets t 
                                                              LEFT JOIN Users u ON t.assigned_to = u.id 
                                                              ORDER BY t.id DESC");
                    while ($t = mysqli_fetch_assoc($all_tickets)):
                        $raw_status = strtolower($t['status']);
                        $status_style = [
                            'open'        => 'text-blue-600 bg-blue-50 dark:bg-blue-900/30 border-blue-200/50',
                            'in-progress' => 'text-amber-600 bg-amber-50 dark:bg-amber-900/30 border-amber-200/50',
                            'resolved'    => 'text-emerald-600 bg-emerald-50 dark:bg-emerald-900/30 border-emerald-200/50',
                            'closed'      => 'text-slate-400 bg-slate-50 border-slate-200/50'
                        ][$raw_status] ?? 'text-slate-500 bg-slate-100 border-slate-200';
                    ?>
                    <tr class="group hover:bg-indigo-50/20 dark:hover:bg-indigo-900/10 transition-all duration-300">
                        <td class="px-8 py-7 font-mono text-[11px] font-bold text-slate-400 group-hover:text-indigo-500">
                            #<?php echo str_pad($t['id'], 3, '0', STR_PAD_LEFT); ?>
                        </td>
                        <td class="px-6 py-7">
                            <span class="font-black text-slate-700 dark:text-slate-200 cursor-pointer uppercase tracking-tight" 
                                  onclick="toggleDetails(<?php echo $t['id']; ?>, this)">
                                <?php echo htmlspecialchars($t['title']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-7">
                            <?php if ($t['assigned_to']): ?>
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-slate-300"></div>
                                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                                        <?php echo ($t['assigned_to'] == $current_user_id) ? 'YOU' : htmlspecialchars($t['dev_name']); ?>
                                    </span>
                                </div>
                            <?php else: ?>
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></div>
                                    <span class="text-[10px] font-black text-rose-500 uppercase tracking-[0.2em]">UNASSIGNED</span>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-7">
                            <span class="px-4 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest <?php echo $status_style; ?> border">
                                <?php echo str_replace('-', ' ', htmlspecialchars($t['status'])); ?>
                            </span>
                        </td>
                        <td class="px-8 py-7 text-right flex items-center justify-end gap-3">
                            <?php if (!$t['assigned_to']): ?>
                                <button onclick="claimTicket(<?php echo $t['id']; ?>)" 
                                        class="px-4 py-2 bg-rose-500 hover:bg-rose-600 text-white rounded-xl text-[9px] font-black uppercase tracking-widest transition-all shadow-lg hover:shadow-rose-500/40">
                                    Claim
                                </button>
                            <?php endif; ?>
                            <button onclick="loadPage('ticket-details', 'ticket_id=<?php echo $t['id']; ?>')" 
                                    class="p-3 bg-slate-900 dark:bg-indigo-600 hover:bg-indigo-500 text-white rounded-2xl transition-all shadow-lg">
                                <i data-lucide="terminal" class="w-4 h-4"></i>
                            </button>
                        </td>
                    </tr>
                    
                    <tr id="details-<?php echo $t['id']; ?>" class="hidden bg-slate-50/30 dark:bg-black/20">
                        <td colspan="5" class="px-4 pb-10">
                           <div class="mx-6 p-10 bg-white dark:bg-slate-950 rounded-[2.5rem] border border-slate-200 dark:border-slate-800 shadow-2xl relative overflow-hidden">
                                <div id="content-<?php echo $t['id']; ?>" class="relative z-10">
                                    <div class="flex items-center gap-4 text-indigo-500 font-black text-xs uppercase tracking-widest">
                                        <div class="animate-spin"><i data-lucide="loader-2" class="w-4 h-4"></i></div>
                                        Decrypting Ticket Data...
                                    </div>
                                </div>
                           </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    if (window.lucide) { lucide.createIcons(); }

    function claimTicket(id) {
        Swal.fire({
            title: 'CONFIRM OWNERSHIP',
            text: "Are you ready to initiate the resolution protocol for this ticket?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#f43f5e',
            confirmButtonText: 'CLAIM_NODE',
            background: '#0f172a',
            color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('view-tickets.php', { action: 'claim_ticket', ticket_id: id }, function(res) {
                    if(res.success) {
                        Swal.fire({ icon: 'success', title: 'TICKET_LINKED', text: res.message, background: '#0f172a', color: '#fff' });
                        loadPage('view-tickets'); // Refresh view
                    } else {
                        Swal.fire({ icon: 'error', title: 'LINK_FAILURE', text: res.message, background: '#0f172a', color: '#fff' });
                    }
                }, 'json');
            }
        });
    }

    function toggleDetails(id, el) {
        const row = $(`#details-${id}`);
        const content = $(`#content-${id}`);

        if (row.hasClass('hidden')) {
            row.removeClass('hidden animate-in fade-in slide-in-from-top-2 duration-300');
            $.getJSON(`view-tickets.php?ajax=true&ticket_id=${id}`, function(data) {
                if(data.error) {
                    content.html(`<p class='text-rose-500 font-black uppercase text-[10px] tracking-widest p-4'>Error: ${data.error}</p>`);
                } else {
                    let devDisplay = data.ticket.developer_name 
                        ? `<span class="text-xs font-black text-slate-700 dark:text-slate-200 uppercase">${data.ticket.developer_name}</span>`
                        : `<span class="text-xs font-black text-rose-500 uppercase">UNASSIGNED</span>`;

                    content.html(`
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-16">
                            <div class="lg:col-span-7 space-y-8">
                                <div>
                                    <h4 class="text-[10px] font-black uppercase text-slate-400 tracking-[0.4em] mb-4">Description</h4>
                                    <div class="text-slate-700 dark:text-slate-300 text-sm bg-slate-50 dark:bg-slate-900/40 p-8 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-inner font-medium">
                                        ${data.ticket.description}
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-8 pt-4">
                                    <div class="p-5 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800">
                                        <span class="text-[9px] uppercase font-black text-slate-400 tracking-widest block mb-2">Target Node</span>
                                        <span class="text-xs font-black text-indigo-600 uppercase tracking-tighter">${data.ticket.project_name || 'General'}</span>
                                    </div>
                                    <div class="p-5 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800">
                                        <span class="text-[9px] uppercase font-black text-slate-400 tracking-widest block mb-2">Assigned Dev</span>
                                        ${devDisplay}
                                    </div>
                                </div>
                            </div>
                            <div class="lg:col-span-5">
                                <h4 class="text-[10px] font-black uppercase text-slate-400 mb-6 tracking-[0.4em]">Recent Activity</h4>
                                <div class="max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                                    ${data.comments.length > 0 ? data.comments.map(c => `
                                        <div class="mb-4 p-5 bg-slate-50 dark:bg-slate-900/50 rounded-3xl border border-slate-100 dark:border-slate-800">
                                            <div class="flex justify-between items-center mb-2">
                                                <span class="text-indigo-600 text-[10px] font-black uppercase">${c.name}</span>
                                                <span class="text-[9px] font-bold text-slate-400 uppercase">${new Date(c.created_at).toLocaleDateString()}</span>
                                            </div>
                                            <div class="text-xs text-slate-600 italic">"${c.comment}"</div>
                                        </div>`).join('') : '<div class="text-center py-10 opacity-30 text-xs">No activity yet.</div>'}
                                </div>
                            </div>
                        </div>
                    `);
                    if (window.lucide) { lucide.createIcons(); }
                }
            });
        } else { row.addClass('hidden'); }
    }
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #1e293b; }
</style>