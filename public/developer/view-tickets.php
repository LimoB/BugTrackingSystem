<?php
session_start();
include('../../config/config.php');

// ✅ 1. Security & Role Check
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'developer') {
    die("<div class='p-6 text-red-500 font-bold'>Unauthorized Access</div>");
}

// ✅ 2. AJAX Detail Fetcher (Returns JSON)
if (isset($_GET['ajax']) && $_GET['ajax'] == 'true' && isset($_GET['ticket_id'])) {
    $ticket_id = intval($_GET['ticket_id']);
    
    // Fetch ticket + project + creator name
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
        echo json_encode(['error' => 'Ticket not found']);
        exit();
    }
    
    // Fetch comments (fixed column name issue_id -> ticket_id to match your DB)
    $c_query = "SELECT u.name, c.comment, c.created_at FROM Comments c
                JOIN Users u ON c.user_id = u.id
                WHERE c.ticket_id = ? ORDER BY c.created_at DESC";
    $c_stmt = $connection->prepare($c_query);
    $c_stmt->bind_param("i", $ticket_id);
    $c_stmt->execute();
    $comments = $c_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['ticket' => $ticket, 'comments' => $comments]);
    exit();
}
?>

<div class="animate-fade-in">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold tracking-tight">Global Backlog</h1>
        <p class="text-slate-500 dark:text-slate-400">All system issues across all projects.</p>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[2rem] overflow-hidden shadow-sm">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Reference</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Issue Title</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Status</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <?php
                $all_tickets = mysqli_query($connection, "SELECT id, title, status FROM Tickets ORDER BY id DESC");
                while ($t = mysqli_fetch_assoc($all_tickets)):
                    $status_color = [
                        'open' => 'text-blue-600 bg-blue-50',
                        'in-progress' => 'text-amber-600 bg-amber-50',
                        'resolved' => 'text-emerald-600 bg-emerald-50',
                    ][strtolower($t['status'])] ?? 'text-slate-500 bg-slate-50';
                ?>
                <tr class="group hover:bg-slate-50/30 dark:hover:bg-slate-800/20 transition-all">
                    <td class="px-6 py-4 font-mono text-xs text-slate-400">#<?php echo $t['id']; ?></td>
                    <td class="px-6 py-4">
                        <span class="font-bold text-slate-700 dark:text-slate-200 group-hover:text-indigo-600 transition-colors cursor-pointer" 
                              onclick="toggleDetails(<?php echo $t['id']; ?>, this)">
                            <?php echo htmlspecialchars($t['title']); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase <?php echo $status_color; ?>">
                            <?php echo $t['status']; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <button onclick="loadPage('ticket-details&ticket_id=<?php echo $t['id']; ?>')" 
                                class="p-2 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 text-slate-400 hover:text-indigo-600 rounded-lg transition-all">
                            <i data-lucide="maximize-2" class="w-4 h-4"></i>
                        </button>
                    </td>
                </tr>
                <tr id="details-<?php echo $t['id']; ?>" class="hidden bg-slate-50/50 dark:bg-slate-800/40">
                    <td colspan="4" class="px-10 py-6 border-l-4 border-indigo-500">
                        <div id="content-<?php echo $t['id']; ?>" class="text-sm space-y-4">
                            <div class="flex items-center gap-4 text-indigo-400">
                                <div class="animate-spin rounded-full h-4 w-4 border-2 border-indigo-500 border-t-transparent"></div>
                                <span class="font-bold text-xs uppercase tracking-widest">Fetching Technical Data...</span>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    lucide.createIcons();

    function toggleDetails(id, el) {
        const row = $(`#details-${id}`);
        const content = $(`#content-${id}`);

        if (row.hasClass('hidden')) {
            row.removeClass('hidden');
            
            // AJAX Fetch
            $.getJSON(`view-tickets.php?ajax=true&ticket_id=${id}`, function(data) {
                if(data.error) {
                    content.html(`<p class='text-red-500'>${data.error}</p>`);
                } else {
                    let commentsHtml = data.comments.length > 0 
                        ? data.comments.map(c => `<div class='mb-2 text-xs'><b class='text-indigo-500'>${c.name}:</b> ${c.comment}</div>`).join('')
                        : `<p class='text-slate-400 italic text-xs'>No discussion yet.</p>`;

                    content.html(`
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="text-[10px] font-black uppercase text-slate-400 mb-2 tracking-widest">Description</h4>
                                <p class="text-slate-600 dark:text-slate-300 leading-relaxed">${data.ticket.description}</p>
                                <div class="mt-4 flex gap-4 text-[10px] font-bold">
                                    <span class="text-slate-400 uppercase">Project: <b class="text-slate-600 dark:text-slate-200">${data.ticket.project_name || 'Unassigned'}</b></span>
                                    <span class="text-slate-400 uppercase">Reporter: <b class="text-slate-600 dark:text-slate-200">${data.ticket.creator_name || 'System'}</b></span>
                                </div>
                            </div>
                            <div class="border-l border-slate-200 dark:border-slate-700 pl-6">
                                <h4 class="text-[10px] font-black uppercase text-slate-400 mb-2 tracking-widest">Latest Activity</h4>
                                ${commentsHtml}
                                <button onclick="loadPage('ticket-details&ticket_id=${id}')" class="mt-4 text-xs font-bold text-indigo-600 hover:underline flex items-center gap-1">
                                    Join Discussion <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                </button>
                            </div>
                        </div>
                    `);
                    lucide.createIcons();
                }
            });
        } else {
            row.addClass('hidden');
        }
    }
</script>