<?php
session_start();
include('../../config/config.php');

/**
 * File: developer/ticket-details.php
 * Purpose: Full terminal-style view for a single technical issue.
 */

// 🛡️ 1. Validation: Ensure both Ticket ID and Session exist
if (!isset($_GET['ticket_id']) || empty($_GET['ticket_id'])) {
    die("<div class='p-12 text-center'><h1 class='text-red-500 font-black uppercase tracking-widest text-xs'>Critical Error: Missing Ticket Reference</h1></div>");
}

if (!isset($_SESSION['user_id'])) {
    die("<div class='p-12 text-center'><h1 class='text-red-500 font-black uppercase tracking-widest text-xs'>Session Expired</h1></div>");
}

$ticket_id = intval($_GET['ticket_id']);
$user_id = (int)$_SESSION['user_id'];

// 🔍 2. Fetch Ticket & Project Info (Using JOINs for full context)
$query = "SELECT t.*, p.name AS project_name, u.name AS creator_name, c.name AS category_name
          FROM Tickets t 
          LEFT JOIN Projects p ON t.project_id = p.id 
          LEFT JOIN Users u ON t.created_by = u.id
          LEFT JOIN Categories c ON t.category_id = c.id
          WHERE t.id = ?";
$stmt = $connection->prepare($query);
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();

if (!$ticket) {
    die("<div class='p-12 text-center text-slate-500 italic'>Reference #$ticket_id could not be decrypted from the database.</div>");
}

// 💬 3. Fetch Comments
$c_query = "SELECT c.comment, c.created_at, u.name 
            FROM Comments c 
            JOIN Users u ON c.user_id = u.id 
            WHERE c.ticket_id = ? 
            ORDER BY c.created_at DESC";
$c_stmt = $connection->prepare($c_query);
$c_stmt->bind_param("i", $ticket_id);
$c_stmt->execute();
$comments_result = $c_stmt->get_result();
?>

<div class="animate-in fade-in slide-in-from-bottom-4 duration-500 max-w-6xl mx-auto p-2">
    <div class="flex items-center justify-between mb-10">
        <button onclick="loadPage('assigned-tickets')" class="group flex items-center gap-3 text-slate-400 hover:text-indigo-600 font-black uppercase text-[10px] tracking-[0.2em] transition-all">
            <i data-lucide="chevron-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"></i> Return to Queue
        </button>
        <span class="text-[10px] font-mono font-bold bg-slate-100 dark:bg-slate-800 px-4 py-1.5 rounded-full text-slate-500 border border-slate-200 dark:border-slate-700 uppercase tracking-widest">
            LOG_ID: <?php echo str_pad($ticket['id'], 4, '0', STR_PAD_LEFT); ?>
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
        
        <div class="lg:col-span-8 space-y-8">
            
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[2.5rem] p-10 shadow-xl shadow-slate-200/50 dark:shadow-none relative overflow-hidden">
                <div class="absolute top-0 right-0 w-40 h-40 bg-indigo-500/5 rounded-full -mr-20 -mt-20 blur-3xl"></div>
                
                <div class="flex flex-col md:flex-row md:items-start justify-between gap-6 mb-10 relative z-10">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-[9px] font-black uppercase tracking-[0.3em] text-indigo-600 bg-indigo-50 dark:bg-indigo-900/30 px-3 py-1 rounded-md border border-indigo-100 dark:border-indigo-800">
                                <?php echo htmlspecialchars($ticket['category_name'] ?? 'General'); ?>
                            </span>
                            <span class="text-[9px] font-black uppercase tracking-[0.3em] text-slate-400">/</span>
                            <span class="text-[9px] font-black uppercase tracking-[0.3em] text-slate-500 italic">
                                <?php echo htmlspecialchars($ticket['project_name'] ?? 'System Core'); ?>
                            </span>
                        </div>
                        <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tighter uppercase leading-tight">
                            <?php echo htmlspecialchars($ticket['title']); ?>
                        </h2>
                    </div>
                    
                    <?php 
                        $status_colors = [
                            'open'        => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 border-blue-200/50',
                            'in-progress' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border-amber-200/50',
                            'resolved'    => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border-emerald-200/50',
                            'closed'      => 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-500 border-slate-200/50',
                            'on-hold'     => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 border-purple-200/50'
                        ];
                        $status_style = $status_colors[strtolower($ticket['status'])] ?? 'bg-slate-100';
                    ?>
                    <span class="px-5 py-2 rounded-2xl text-[10px] font-black uppercase tracking-widest border <?php echo $status_style; ?> shadow-sm">
                        <?php echo str_replace('-', ' ', $ticket['status']); ?>
                    </span>
                </div>

                <div class="space-y-4">
                    <h4 class="text-[10px] uppercase text-slate-400 font-black tracking-[0.3em] mb-4 flex items-center gap-2">
                        <i data-lucide="align-left" class="w-3 h-3"></i> Technical Brief
                    </h4>
                    <div class="text-slate-700 dark:text-slate-300 leading-relaxed bg-slate-50 dark:bg-slate-800/40 p-8 rounded-[2rem] border border-slate-100 dark:border-slate-800 font-medium italic">
                        <?php echo nl2br(htmlspecialchars($ticket['description'])); ?>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <h3 class="text-lg font-black uppercase tracking-tighter flex items-center gap-3 px-4 text-slate-800 dark:text-white">
                    <i data-lucide="message-square" class="w-5 h-5 text-indigo-600"></i>
                    Technical Logs & Discussion
                </h3>
                
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[2rem] p-6 shadow-sm">
                    <form id="add-comment-form" class="space-y-4">
                        <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
                        <textarea name="comment" placeholder="Log technical update or internal note..." required 
                                  class="w-full p-6 bg-slate-50 dark:bg-slate-800/50 border-none rounded-[1.5rem] text-sm font-medium focus:ring-2 focus:ring-indigo-600 outline-none transition-all h-32 resize-none"></textarea>
                        <div class="flex justify-end">
                            <button type="submit" class="bg-indigo-600 text-white px-10 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-500 transition shadow-xl shadow-indigo-100 dark:shadow-none active:scale-95">
                                Commit Log Update
                            </button>
                        </div>
                    </form>
                </div>

                <div id="comments-section" class="space-y-5">
                    <?php if ($comments_result->num_rows > 0): ?>
                        <?php while ($c = $comments_result->fetch_assoc()): ?>
                            <div class="group p-8 border-l-4 border-indigo-600 bg-white dark:bg-slate-900 rounded-r-[2rem] shadow-sm border-y border-r border-slate-100 dark:border-slate-800 transition-all hover:bg-slate-50/50 dark:hover:bg-indigo-900/10">
                                <div class="flex justify-between items-center mb-4">
                                    <span class="font-black text-[10px] text-indigo-600 uppercase tracking-widest"><?php echo htmlspecialchars($c['name']); ?></span>
                                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter"><?php echo date('M d, Y • H:i', strtotime($c['created_at'])); ?></span>
                                </div>
                                <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed font-medium italic">"<?php echo nl2br(htmlspecialchars($c['comment'])); ?>"</p>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-20 bg-slate-50 dark:bg-slate-800/20 rounded-[2.5rem] border border-dashed border-slate-200 dark:border-slate-800 italic text-slate-400 text-xs font-bold uppercase tracking-[0.2em]">
                            No technical transmissions logged for this reference.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="lg:col-span-4 space-y-8">
            
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[2.5rem] p-8 shadow-sm">
                <h4 class="text-[10px] font-black uppercase text-slate-400 tracking-[0.3em] mb-8 flex items-center gap-2">
                    <i data-lucide="settings-2" class="w-3 h-3"></i> Operations
                </h4>
                
                <form id="update-status-form" class="space-y-6">
                    <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
                    <div>
                        <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-4 px-1">Transition Status</label>
                        <select name="status" id="status" class="w-full p-5 bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-2xl text-[11px] font-black uppercase tracking-widest focus:ring-2 focus:ring-indigo-600 cursor-pointer outline-none transition-all shadow-inner">
                            <?php
                            $statuses = ['open', 'in-progress', 'resolved', 'closed', 'on-hold'];
                            foreach ($statuses as $st) {
                                $selected = (strtolower($ticket['status']) === $st) ? 'selected' : '';
                                $label = str_replace('-', ' ', $st);
                                echo "<option value='$st' $selected>$label</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-slate-900 dark:bg-indigo-600 text-white py-5 rounded-[1.5rem] font-black text-[10px] uppercase tracking-[0.3em] hover:bg-indigo-500 transition-all shadow-2xl active:scale-95">
                        Sync Data Changes
                    </button>
                </form>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[2.5rem] p-8 shadow-sm">
                <h4 class="text-[10px] font-black uppercase text-slate-400 tracking-[0.3em] mb-6">Object Metadata</h4>
                <div class="space-y-5">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Reporter</span>
                        <span class="text-[10px] font-black text-slate-700 dark:text-slate-200 uppercase"><?php echo htmlspecialchars($ticket['creator_name'] ?? 'System'); ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Deployed</span>
                        <span class="text-[10px] font-black text-slate-700 dark:text-slate-200 uppercase"><?php echo date('d M Y', strtotime($ticket['created_at'])); ?></span>
                    </div>
                </div>
            </div>

            <div class="bg-indigo-600 rounded-[2.5rem] p-10 text-white shadow-2xl shadow-indigo-200 dark:shadow-none relative overflow-hidden group">
                <div class="absolute -right-6 -top-6 w-32 h-32 bg-white/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
                <h4 class="text-[10px] font-black uppercase opacity-70 tracking-[0.3em] mb-4">Protocol Reminder</h4>
                <p class="text-xs leading-relaxed font-bold italic opacity-90">
                    Always include stack traces or specific line references when logging technical updates to expedite resolution.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    // Initialize Icons
    if (window.lucide) { lucide.createIcons(); }

    // AJAX: Update Status
    $('#update-status-form').off('submit').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        
        $.post('update-status.php', formData, function(res) {
            if(res.success) {
                Swal.fire({ 
                    icon: 'success', 
                    title: 'SYSTEM SYNCED', 
                    background: '#0f172a',
                    color: '#fff',
                    timer: 1500, 
                    showConfirmButton: false 
                });
                loadPage('ticket-details', 'ticket_id=<?php echo $ticket_id; ?>');
            } else {
                Swal.fire('Error', res.error || 'Sync Failed', 'error');
            }
        }, 'json');
    });

    // AJAX: Add Comment
    $('#add-comment-form').off('submit').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();

        $.post('add-comment.php', formData, function(res) {
            if(res.success) {
                loadPage('ticket-details', 'ticket_id=<?php echo $ticket_id; ?>');
            } else {
                Swal.fire('Error', res.message || 'Transmission Failed', 'error');
            }
        }, 'json');
    });
</script>