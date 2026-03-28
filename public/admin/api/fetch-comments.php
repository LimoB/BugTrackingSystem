<?php
session_start();
include('../../../config/config.php');

// 🛡️ Security Guard
if (!isset($_SESSION['role'])) {
    die("<div class='p-4 text-rose-500 font-bold'>Session Expired</div>");
}

$ticketId = isset($_GET['ticket_id']) ? intval($_GET['ticket_id']) : 0;

if ($ticketId > 0) {
    // 🔍 Fetch comments with User metadata
    $query = "SELECT c.comment, c.created_at, u.name, u.role 
              FROM Ticket_Comments c 
              JOIN Users u ON c.user_id = u.id 
              WHERE c.ticket_id = ? 
              ORDER BY c.created_at ASC";

    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $ticketId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo '
        <div class="flex flex-col items-center justify-center py-12 text-slate-300 dark:text-slate-600">
            <i data-lucide="message-square-dashed" class="w-12 h-12 mb-3 opacity-20"></i>
            <p class="text-[10px] font-black uppercase tracking-[0.2em]">No dialogue recorded yet</p>
        </div>';
    } else {
        echo '<div class="space-y-6 pt-4">';
        while ($comment = $result->fetch_assoc()) {
            $is_admin = strtolower($comment['role']) === 'admin';
            $initial = strtoupper(substr($comment['name'], 0, 1));
            $formatted_time = date('M j, g:i a', strtotime($comment['created_at']));
            ?>
            
            <div class="flex gap-4 group">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center text-xs font-black shadow-sm 
                        <?php echo $is_admin ? 'bg-rose-500 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-500'; ?>">
                        <?php echo $initial; ?>
                    </div>
                </div>

                <div class="flex-grow">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs font-black text-slate-900 dark:text-white leading-none">
                            <?php echo htmlspecialchars($comment['name']); ?>
                        </span>
                        <?php if($is_admin): ?>
                            <span class="px-1.5 py-0.5 rounded bg-rose-50 dark:bg-rose-900/20 text-rose-500 text-[8px] font-black uppercase tracking-tighter border border-rose-100 dark:border-rose-800/50">Staff</span>
                        <?php endif; ?>
                        <span class="text-[9px] font-medium text-slate-400 ml-auto"><?php echo $formatted_time; ?></span>
                    </div>
                    
                    <div class="relative p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl rounded-tl-none border border-slate-100 dark:border-slate-800 group-hover:bg-white dark:group-hover:bg-slate-800 transition-colors">
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-wrap"><?php echo htmlspecialchars($comment['comment']); ?></p>
                    </div>
                </div>
            </div>

            <?php
        }
        echo '</div>';
    }
    $stmt->close();
}
?>