<?php
session_start();
include('../../config/config.php');

if (!isset($_GET['ticket_id']) || !isset($_SESSION['user_id'])) {
    echo "<div class='p-6 text-red-500 font-bold'>Error: Missing Ticket ID or Session.</div>";
    exit();
}

$ticket_id = intval($_GET['ticket_id']);
$user_id = intval($_SESSION['user_id']);

// 1. Fetch Ticket & Project Info
$query = "SELECT t.*, p.name AS project_name, u.name AS creator_name 
          FROM Tickets t 
          LEFT JOIN Projects p ON t.project_id = p.id 
          LEFT JOIN Users u ON t.created_by = u.name
          WHERE t.id=?";
$stmt = $connection->prepare($query);
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();

if (!$ticket) {
    echo "<div class='p-6 text-slate-500 italic'>Ticket data could not be retrieved.</div>";
    exit();
}

// 2. Fetch Comments
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

<div class="animate-fade-in max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <button onclick="loadPage('assigned-tickets')" class="flex items-center gap-2 text-slate-500 hover:text-indigo-600 font-bold transition">
            <i data-lucide="arrow-left" class="w-5 h-5"></i> Back to List
        </button>
        <span class="text-xs font-mono bg-slate-100 dark:bg-slate-800 px-3 py-1 rounded text-slate-500">ID: #<?php echo $ticket['id']; ?></span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[2rem] p-8 shadow-sm">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h2 class="text-2xl font-extrabold mb-1"><?php echo htmlspecialchars($ticket['title']); ?></h2>
                        <p class="text-indigo-600 font-bold text-sm uppercase tracking-wide">
                            <i data-lucide="layers" class="w-4 h-4 inline mr-1"></i> 
                            <?php echo htmlspecialchars($ticket['project_name'] ?? 'General Backlog'); ?>
                        </p>
                    </div>
                    <?php 
                        $status_colors = [
                            'open' => 'bg-blue-100 text-blue-700',
                            'in-progress' => 'bg-amber-100 text-amber-700',
                            'resolved' => 'bg-emerald-100 text-emerald-700',
                            'closed' => 'bg-slate-100 text-slate-700',
                            'on-hold' => 'bg-purple-100 text-purple-700'
                        ];
                        $color = $status_colors[strtolower($ticket['status'])] ?? 'bg-slate-100';
                    ?>
                    <span class="px-4 py-1 rounded-full text-xs font-black uppercase tracking-tighter <?php echo $color; ?>">
                        <?php echo $ticket['status']; ?>
                    </span>
                </div>

                <div class="prose dark:prose-invert max-w-none">
                    <h4 class="text-xs uppercase text-slate-400 font-black mb-2 tracking-widest">Description</h4>
                    <p class="text-slate-700 dark:text-slate-300 leading-relaxed bg-slate-50 dark:bg-slate-800/50 p-4 rounded-xl border border-slate-100 dark:border-slate-800">
                        <?php echo nl2br(htmlspecialchars($ticket['description'])); ?>
                    </p>
                </div>
            </div>

            <div class="space-y-4">
                <h3 class="text-lg font-bold flex items-center gap-2 px-2">
                    <i data-lucide="message-circle" class="w-5 h-5 text-indigo-500"></i>
                    Discussion Feed
                </h3>
                
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
                    <form id="add-comment-form" class="space-y-3">
                        <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
                        <textarea name="comment" placeholder="Write a technical update..." required 
                                  class="w-full p-4 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all h-24 resize-none"></textarea>
                        <div class="flex justify-end">
                            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm font-bold hover:bg-indigo-700 transition">
                                Post Comment
                            </button>
                        </div>
                    </form>
                </div>

                <div id="comments-section" class="space-y-4">
                    <?php if ($comments->num_rows > 0): ?>
                        <?php while ($c = $comments->fetch_assoc()): ?>
                            <div class="p-5 border-l-4 border-indigo-500 bg-white dark:bg-slate-900 rounded-r-2xl shadow-sm border-y border-r border-slate-100 dark:border-slate-800">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="font-black text-sm text-indigo-600"><?php echo htmlspecialchars($c['name']); ?></span>
                                    <span class="text-[10px] text-slate-400 font-bold uppercase"><?php echo date('M d, H:i', strtotime($c['created_at'])); ?></span>
                                </div>
                                <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed"><?php echo nl2br(htmlspecialchars($c['comment'])); ?></p>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-10 opacity-50 italic text-sm">No activity logged for this ticket yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[2rem] p-6 shadow-sm">
                <h4 class="text-xs font-black uppercase text-slate-400 tracking-widest mb-4">Ticket Controls</h4>
                
                <form id="update-status-form" class="space-y-4">
                    <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-2">Set Current Status</label>
                        <select name="status" id="status" class="w-full p-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm font-bold focus:ring-2 focus:ring-indigo-500 cursor-pointer outline-none">
                            <?php
                            $statuses = ['open', 'in-progress', 'resolved', 'closed', 'on-hold'];
                            foreach ($statuses as $st) {
                                $selected = (strtolower($ticket['status']) === $st) ? 'selected' : '';
                                echo "<option value='$st' $selected>" . ucwords(str_replace('-', ' ', $st)) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-slate-900 dark:bg-white dark:text-slate-900 text-white py-3 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-indigo-600 dark:hover:bg-indigo-500 transition-all">
                        Sync Changes
                    </button>
                </form>
            </div>

            <div class="bg-indigo-600 rounded-[2rem] p-6 text-white shadow-lg shadow-indigo-200 dark:shadow-none">
                <h4 class="text-[10px] font-black uppercase opacity-70 tracking-widest mb-4">Developer Note</h4>
                <p class="text-xs leading-relaxed opacity-90">
                    Always update the status to <b>In Progress</b> before starting work so the team knows this is being zapped.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();

    // 🔄 Handle Status Update
    $('#update-status-form').on('submit', function(e) {
        e.preventDefault();
        $.post('update-status.php', $(this).serialize(), function(res) {
            if(res.success) {
                Swal.fire({ icon: 'success', title: 'Synced', text: res.message, timer: 1500, showConfirmButton: false });
                loadPage('ticket-details&ticket_id=<?php echo $ticket_id; ?>'); // Refresh view
            } else {
                Swal.fire('Error', res.error, 'error');
            }
        });
    });

    // 💬 Handle New Comment
    $('#add-comment-form').on('submit', function(e) {
        e.preventDefault();
        $.post('add-comment.php', $(this).serialize(), function(res) {
            // Note: add-comment.php should return JSON {success: true}
            loadPage('ticket-details&ticket_id=<?php echo $ticket_id; ?>');
        });
    });
</script>

        // 🍞 Show Error if validation fails
        <?php if ($message_type === 'error'): ?>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '<?php echo addslashes($message); ?>'
        });
        <?php endif; ?>
</script>   