<?php
session_start();
include('../../config/config.php');

// 1. Validation: Ensure both Ticket ID and Session exist
if (!isset($_GET['ticket_id']) || empty($_GET['ticket_id'])) {
    die("<div class='p-12 text-center'><h1 class='text-red-500 font-black uppercase tracking-widest'>Error: Missing Ticket ID</h1><p class='text-slate-500 text-sm'>No reference ID was provided in the request.</p></div>");
}

if (!isset($_SESSION['user_id'])) {
    die("<div class='p-12 text-center'><h1 class='text-red-500 font-black uppercase tracking-widest'>Error: Session Expired</h1><p class='text-slate-500 text-sm'>Please log in again to view this ticket.</p></div>");
}

$ticket_id = intval($_GET['ticket_id']);
$user_id = (int)$_SESSION['user_id'];

// 2. Fetch Ticket & Project Info
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
    die("<div class='p-12 text-center text-slate-500 italic'>Ticket data could not be retrieved from the database.</div>");
}

// 3. Fetch Comments
$c_query = "SELECT c.comment, c.created_at, u.name 
            FROM Comments c 
            JOIN Users u ON c.user_id = u.id 
            WHERE c.ticket_id = ? 
            ORDER BY c.created_at DESC";
$c_stmt = $connection->prepare($c_query);
$c_stmt->bind_param("i", $ticket_id);
$c_stmt->execute();
$comments = $c_stmt->get_result();
?>

<div class="animate-fade-in max-w-5xl mx-auto p-4">
    <div class="flex items-center justify-between mb-8">
        <button onclick="loadPage('assigned-tickets')" class="flex items-center gap-2 text-slate-500 hover:text-indigo-600 font-bold transition">
            <i data-lucide="arrow-left" class="w-5 h-5"></i> Back to List
        </button>
        <span class="text-xs font-mono bg-slate-100 dark:bg-slate-800 px-3 py-1 rounded text-slate-500 uppercase tracking-widest">
            Reference: #<?php echo $ticket['id']; ?>
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[2rem] p-8 shadow-sm">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h2 class="text-2xl font-extrabold mb-1 text-slate-900 dark:text-white"><?php echo htmlspecialchars($ticket['title']); ?></h2>
                        <p class="text-indigo-600 font-bold text-sm uppercase tracking-wide">
                            <i data-lucide="layers" class="w-4 h-4 inline mr-1"></i> 
                            <?php echo htmlspecialchars($ticket['project_name'] ?? 'General Backlog'); ?>
                        </p>
                    </div>
                    <?php 
                        $status_colors = [
                            'open' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                            'in-progress' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                            'resolved' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                            'closed' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400',
                            'on-hold' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400'
                        ];
                        $color = $status_colors[strtolower($ticket['status'])] ?? 'bg-slate-100';
                    ?>
                    <span class="px-4 py-1 rounded-full text-xs font-black uppercase tracking-tighter <?php echo $color; ?>">
                        <?php echo str_replace('-', ' ', $ticket['status']); ?>
                    </span>
                </div>

                <div class="prose dark:prose-invert max-w-none">
                    <h4 class="text-[10px] uppercase text-slate-400 font-black mb-3 tracking-widest">Description</h4>
                    <div class="text-slate-700 dark:text-slate-300 leading-relaxed bg-slate-50 dark:bg-slate-800/50 p-5 rounded-2xl border border-slate-100 dark:border-slate-800">
                        <?php echo nl2br(htmlspecialchars($ticket['description'])); ?>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <h3 class="text-lg font-bold flex items-center gap-2 px-2 text-slate-800 dark:text-white">
                    <i data-lucide="message-square" class="w-5 h-5 text-indigo-500"></i>
                    Internal Discussion
                </h3>
                
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-4 shadow-sm">
                    <form id="add-comment-form" class="space-y-3">
                        <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
                        <textarea name="comment" placeholder="Add a technical update or internal note..." required 
                                  class="w-full p-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all h-28 resize-none"></textarea>
                        <div class="flex justify-end">
                            <button type="submit" class="bg-indigo-600 text-white px-8 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 dark:shadow-none">
                                Post Update
                            </button>
                        </div>
                    </form>
                </div>

                <div id="comments-section" class="space-y-4">
                    <?php if ($comments->num_rows > 0): ?>
                        <?php while ($c = $comments->fetch_assoc()): ?>
                            <div class="p-6 border-l-4 border-indigo-500 bg-white dark:bg-slate-900 rounded-r-3xl shadow-sm border-y border-r border-slate-100 dark:border-slate-800">
                                <div class="flex justify-between items-center mb-3">
                                    <span class="font-black text-xs text-indigo-600 uppercase tracking-tight"><?php echo htmlspecialchars($c['name']); ?></span>
                                    <span class="text-[10px] text-slate-400 font-bold uppercase"><?php echo date('M d, H:i', strtotime($c['created_at'])); ?></span>
                                </div>
                                <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed"><?php echo nl2br(htmlspecialchars($c['comment'])); ?></p>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-12 bg-slate-50 dark:bg-slate-800/30 rounded-3xl border border-dashed border-slate-200 dark:border-slate-800 italic text-slate-400 text-sm">
                            No internal activity logged for this ticket.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[2rem] p-6 shadow-sm">
                <h4 class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-6">Management Actions</h4>
                
                <form id="update-status-form" class="space-y-5">
                    <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-3 px-1">Update Status</label>
                        <select name="status" id="status" class="w-full p-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-indigo-500 cursor-pointer outline-none transition-all">
                            <?php
                            $statuses = ['open', 'in-progress', 'resolved', 'closed', 'on-hold'];
                            foreach ($statuses as $st) {
                                $selected = (strtolower($ticket['status']) === $st) ? 'selected' : '';
                                $label = ucwords(str_replace('-', ' ', $st));
                                echo "<option value='$st' $selected>$label</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-slate-900 dark:bg-indigo-600 text-white py-4 rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] hover:bg-indigo-600 dark:hover:bg-indigo-700 transition-all shadow-xl shadow-slate-200 dark:shadow-none active:scale-95">
                        Sync Changes
                    </button>
                </form>
            </div>

            <div class="bg-indigo-600 rounded-[2rem] p-8 text-white shadow-xl shadow-indigo-100 dark:shadow-none relative overflow-hidden">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                <h4 class="text-[10px] font-black uppercase opacity-70 tracking-widest mb-3">Workflow Reminder</h4>
                <p class="text-xs leading-relaxed font-medium">
                    Please mark tickets as In Progress immediately upon starting technical work to avoid duplicate efforts.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    // Refresh Icons
    if (window.lucide) { lucide.createIcons(); }

    // Update Ticket Status Logic
    $('#update-status-form').off('submit').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        
        $.post('update-status.php', formData, function(res) {
            if(res.success) {
                Swal.fire({ 
                    icon: 'success', 
                    title: 'Status Synced', 
                    timer: 1000, 
                    showConfirmButton: false 
                });
                loadPage('ticket-details', 'ticket_id=<?php echo $ticket_id; ?>');
            } else {
                Swal.fire('Error', res.error || 'Failed to update status', 'error');
            }
        }, 'json').fail(function() {
            Swal.fire('404 Not Found', 'The file update-status.php is missing from the developer folder.', 'error');
        });
    });

    // Add Comment Logic
    $('#add-comment-form').off('submit').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();

        $.post('add-comment.php', formData, function(res) {
            if(res.success) {
                loadPage('ticket-details', 'ticket_id=<?php echo $ticket_id; ?>');
            } else {
                Swal.fire('Error', res.message || 'Failed to post comment', 'error');
            }
        }, 'json').fail(function() {
            Swal.fire('404 Not Found', 'The file add-comment.php is missing from the developer folder.', 'error');
        });
    });
</script>