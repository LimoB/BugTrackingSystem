<?php
session_start();
include('../../config/config.php');

// ✅ 1. Security & Role Check
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'developer') {
    die("<div class='p-12 text-center text-red-500 font-bold tracking-widest uppercase text-xs'>Unauthorized Access</div>");
}

// ✅ 2. AJAX Detail Fetcher
if (isset($_GET['ajax']) && $_GET['ajax'] === 'true' && isset($_GET['ticket_id'])) {
    header('Content-Type: application/json');
    $ticket_id = intval($_GET['ticket_id']);
    
    $query = "SELECT t.*, p.name AS project_name, u.name AS creator_name 
              FROM Tickets t 
              LEFT JOIN Projects p ON t.project_id = p.id 
              LEFT JOIN Users u ON t.created_by = u.id
              WHERE t.id = ?";
    
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $ticket_id);
    $stmt->execute();
    $ticket = $stmt->get_result()->fetch_assoc();
    
    if (!$ticket) {
        echo json_encode(['error' => 'Ticket data not found.']);
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

<div class="animate-fade-in p-2">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Global Backlog</h1>
        <p class="text-slate-500 dark:text-slate-400 mt-1 italic text-sm font-medium">Full visibility of system-wide issues and technical debt.</p>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[2rem] overflow-hidden shadow-xl shadow-slate-200/50 dark:shadow-none">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                        <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Ref</th>
                        <th class="px-6 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Technical Issue</th>
                        <th class="px-6 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Status</th>
                        <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <?php
                    $all_tickets = mysqli_query($connection, "SELECT id, title, status FROM Tickets ORDER BY id DESC");
                    while ($t = mysqli_fetch_assoc($all_tickets)):
                        $raw_status = strtolower($t['status']);
                        $status_color = [
                            'open'        => 'text-blue-600 bg-blue-50 dark:bg-blue-900/30 dark:text-blue-400',
                            'in-progress' => 'text-amber-600 bg-amber-50 dark:bg-amber-900/30 dark:text-amber-400',
                            'resolved'    => 'text-emerald-600 bg-emerald-50 dark:bg-emerald-900/30 dark:text-emerald-400',
                            'on-hold'     => 'text-purple-600 bg-purple-50 dark:bg-purple-900/30 dark:text-purple-400'
                        ][$raw_status] ?? 'text-slate-500 bg-slate-100 dark:bg-slate-800';
                    ?>
                    <tr class="group hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10 transition-all duration-300">
                        <td class="px-8 py-6 font-mono text-[11px] font-bold text-slate-400 group-hover:text-indigo-500 transition-colors">#<?php echo $t['id']; ?></td>
                        <td class="px-6 py-6">
                            <span class="font-bold text-slate-700 dark:text-slate-200 group-hover:text-slate-900 dark:group-hover:text-white transition-all cursor-pointer select-none decoration-indigo-500/30 hover:underline underline-offset-4" 
                                  onclick="toggleDetails(<?php echo $t['id']; ?>, this)">
                                <?php echo htmlspecialchars($t['title']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-6">
                            <span class="px-3 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest <?php echo $status_color; ?> border border-current/10">
                                <?php echo str_replace('-', ' ', htmlspecialchars($t['status'])); ?>
                            </span>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <button onclick="loadPage('ticket-details', 'ticket_id=<?php echo $t['id']; ?>')" 
                                    class="p-3 bg-slate-50 dark:bg-slate-800/50 hover:bg-indigo-600 dark:hover:bg-indigo-600 text-slate-400 hover:text-white rounded-xl transition-all shadow-sm active:scale-90">
                                <i data-lucide="external-link" class="w-4 h-4"></i>
                            </button>
                        </td>
                    </tr>
                    <tr id="details-<?php echo $t['id']; ?>" class="hidden">
                        <td colspan="4" class="px-4 pb-6 bg-white dark:bg-slate-900">
                           <div class="mx-4 p-8 bg-slate-50/80 dark:bg-slate-800/40 rounded-[2rem] border border-slate-200/60 dark:border-slate-700/50 shadow-inner overflow-hidden relative">
                                <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/5 rounded-full -mr-16 -mt-16 blur-3xl"></div>
                                <div id="content-<?php echo $t['id']; ?>" class="relative z-10">
                                    <div class="flex items-center gap-4 text-indigo-500 font-bold italic text-xs">
                                        <div class="animate-bounce"><i data-lucide="refresh-cw" class="w-4 h-4"></i></div>
                                        Fetching technical context...
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

    function toggleDetails(id, el) {
        const row = $(`#details-${id}`);
        const content = $(`#content-${id}`);

        if (row.hasClass('hidden')) {
            row.removeClass('hidden');
            
            // AJAX Fetch
            $.getJSON(`view-tickets.php?ajax=true&ticket_id=${id}`, function(data) {
                if(data.error) {
                    content.html(`<p class='text-rose-500 font-black uppercase text-[10px] tracking-widest'>Error: ${data.error}</p>`);
                } else {
                    let commentsHtml = data.comments.length > 0 
                        ? data.comments.map(c => `
                            <div class='mb-3 p-4 bg-white/60 dark:bg-slate-900/60 rounded-2xl border border-slate-200/50 dark:border-slate-700/50 shadow-sm'>
                                <div class='flex justify-between items-center mb-2'>
                                    <span class='text-indigo-600 dark:text-indigo-400 text-[10px] font-black uppercase tracking-tighter'>${c.name}</span>
                                    <span class='text-[9px] font-medium text-slate-400'>${new Date(c.created_at).toLocaleDateString('en-US', {month: 'short', day: 'numeric'})}</span>
                                </div>
                                <div class='text-xs text-slate-600 dark:text-slate-300 leading-relaxed font-medium'>${c.comment}</div>
                            </div>`).join('')
                        : `<div class='text-center py-8 opacity-40 italic text-xs font-medium'>No internal discussion logged.</div>`;

                    content.html(`
                        <div class="grid grid-cols-1 lg:grid-cols-5 gap-12">
                            <div class="lg:col-span-3 space-y-6">
                                <div>
                                    <div class="flex items-center gap-2 mb-4">
                                        <div class="h-1 w-8 bg-indigo-500 rounded-full"></div>
                                        <h4 class="text-[10px] font-black uppercase text-slate-400 tracking-[0.3em]">Full Specification</h4>
                                    </div>
                                    <div class="text-slate-700 dark:text-slate-300 text-sm leading-relaxed bg-white/80 dark:bg-slate-900/80 p-6 rounded-[1.5rem] border border-white dark:border-slate-700 shadow-sm whitespace-pre-wrap font-medium">${data.ticket.description}</div>
                                </div>
                                <div class="flex flex-wrap gap-8 items-center pt-2">
                                    <div class="flex flex-col">
                                        <span class="text-[9px] uppercase font-black text-slate-400 tracking-widest mb-1">Target Project</span>
                                        <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400">${data.ticket.project_name || 'Global'}</span>
                                    </div>
                                    <div class="w-px h-8 bg-slate-200 dark:bg-slate-700"></div>
                                    <div class="flex flex-col">
                                        <span class="text-[9px] uppercase font-black text-slate-400 tracking-widest mb-1">Technical Reporter</span>
                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-200">${data.ticket.creator_name || 'System'}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="lg:col-span-2 flex flex-col justify-between">
                                <div>
                                    <h4 class="text-[10px] font-black uppercase text-slate-400 mb-5 tracking-[0.3em]">Recent Activity</h4>
                                    <div class="space-y-3">
                                        ${commentsHtml}
                                    </div>
                                </div>
                                <button onclick="loadPage('ticket-details', 'ticket_id=${id}')" 
                                        class="mt-8 group w-full flex items-center justify-center gap-3 py-4 bg-slate-900 dark:bg-indigo-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] hover:bg-indigo-600 transition-all shadow-xl shadow-slate-200 dark:shadow-none active:scale-95">
                                    Full Terminal Access
                                    <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                                </button>
                            </div>
                        </div>
                    `);
                    if (window.lucide) { lucide.createIcons(); }
                }
            }).fail(function() {
                content.html(`<div class="text-rose-500 font-bold p-4 text-xs">Error: Target file [view-tickets.php] could not be reached.</div>`);
            });
        } else {
            row.addClass('hidden');
        }
    }
</script>