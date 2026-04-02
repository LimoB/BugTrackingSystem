<?php
/**
 * File: admin/api/fetch-ticket-comments.php
 * Purpose: Renders the chronological dialogue history for a specific ticket.
 */
session_start();
include('../../../config/config.php');

// 🛡️ Security Guard: Ensure session is active
if (!isset($_SESSION['role'])) {
    die("<div class='p-12 text-center'><i data-lucide='lock' class='w-8 h-8 text-rose-500 mx-auto mb-3'></i><p class='text-[10px] font-black uppercase text-slate-400 tracking-widest'>Encryption Key Required</p></div>");
}

$ticketId = isset($_GET['ticket_id']) ? intval($_GET['ticket_id']) : 0;

if ($ticketId > 0) {
    // 🔍 Fetch comments with User metadata (Joining for Role and Name)
    $query = "SELECT c.comment, c.created_at, u.username, u.role 
              FROM Ticket_Comments c 
              JOIN Users u ON c.user_id = u.id 
              WHERE c.ticket_id = ? 
              ORDER BY c.created_at ASC";

    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $ticketId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // Empty State Branding
        echo '
        <div class="flex flex-col items-center justify-center py-20 text-slate-300 dark:text-slate-700 animate-in fade-in zoom-in duration-700">
            <div class="w-16 h-16 bg-slate-50 dark:bg-slate-800/50 rounded-[2rem] flex items-center justify-center mb-4">
                <i data-lucide="message-square-dashed" class="w-8 h-8 opacity-20"></i>
            </div>
            <p class="text-[10px] font-black uppercase tracking-[0.3em]">No operational dialogue recorded</p>
        </div>';
    } else {
        echo '<div class="space-y-8 py-6">';
        while ($comment = $result->fetch_assoc()) {
            $role = strtolower($comment['role']);
            $is_admin = ($role === 'admin');
            $is_dev = ($role === 'developer');
            
            // Generate distinct initials
            $initial = strtoupper(substr($comment['username'], 0, 1));
            $formatted_time = date('M j, g:i a', strtotime($comment['created_at']));

            // Logic for Dynamic Avatar Colors
            $avatar_class = 'bg-slate-100 text-slate-500 dark:bg-slate-800';
            if ($is_admin) $avatar_class = 'bg-rose-500 text-white shadow-lg shadow-rose-500/20';
            if ($is_dev)   $avatar_class = 'bg-indigo-500 text-white shadow-lg shadow-indigo-500/20';
            ?>
            
            <div class="flex gap-5 group animate-in slide-in-from-left-4 duration-500">
                <div class="flex-shrink-0 relative">
                    <div class="w-11 h-11 rounded-[1.2rem] flex items-center justify-center text-xs font-black transition-transform group-hover:scale-110 <?php echo $avatar_class; ?>">
                        <?php echo $initial; ?>
                    </div>
                    <?php if($is_admin): ?>
                        <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-white dark:bg-slate-900 rounded-full flex items-center justify-center shadow-sm">
                            <div class="w-2 h-2 bg-rose-500 rounded-full"></div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="flex-grow">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-xs font-black text-slate-900 dark:text-white tracking-tight">
                            @<?php echo htmlspecialchars($comment['username']); ?>
                        </span>
                        
                        <?php if($is_admin): ?>
                            <span class="px-2 py-0.5 rounded-lg bg-rose-50 dark:bg-rose-900/20 text-rose-500 text-[8px] font-black uppercase tracking-widest border border-rose-100 dark:border-rose-800/50">Staff / Admin</span>
                        <?php elseif($is_dev): ?>
                            <span class="px-2 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 text-indigo-500 text-[8px] font-black uppercase tracking-widest border border-indigo-100 dark:border-indigo-800/50">Developer</span>
                        <?php endif; ?>

                        <span class="text-[9px] font-bold text-slate-400 ml-auto flex items-center gap-1">
                            <i data-lucide="clock" class="w-3 h-3"></i>
                            <?php echo $formatted_time; ?>
                        </span>
                    </div>
                    
                    <div class="relative p-5 bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-800 rounded-[1.8rem] rounded-tl-none shadow-sm group-hover:shadow-md transition-all">
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed font-medium whitespace-pre-wrap selection:bg-indigo-100"><?php echo htmlspecialchars($comment['comment']); ?></p>
                        
                        <?php if($is_admin): ?>
                            <div class="absolute inset-0 bg-gradient-to-br from-rose-500/5 to-transparent rounded-[1.8rem] pointer-events-none"></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php
        }
        echo '</div>';
    }
    $stmt->close();
} else {
    echo "<div class='p-12 text-center text-slate-400 font-black text-[10px] uppercase tracking-widest'>Invalid Access Vector</div>";
}
$connection->close();
?>

<script>
    if(window.lucide) lucide.createIcons();
</script>