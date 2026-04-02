<?php
session_start();
include('../../../config/config.php');

// 🛡️ Security Guard: Ensure only Admins can decrypt this intel
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    die("<div class='p-12 text-rose-500 font-black text-center bg-rose-50 dark:bg-rose-950/20 rounded-3xl'>Access Denied: Administrative Clearance Required</div>");
}

$ticketId = isset($_GET['ticket_id']) ? intval($_GET['ticket_id']) : 0;

if ($ticketId > 0) {
    // 📡 Fetching Comprehensive Data: Joining Projects, Users, and the new Categories table
    $query = "SELECT t.*, 
                     p.name AS project_name, 
                     u.name AS creator_name,
                     c.name AS category_name
              FROM Tickets t 
              LEFT JOIN Projects p ON t.project_id = p.id 
              LEFT JOIN Users u ON t.created_by = u.id 
              LEFT JOIN Categories c ON t.category_id = c.id
              WHERE t.id = ?";
              
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $ticketId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($ticket = $result->fetch_assoc()) {
        // Dynamic Status Styling
        $status_colors = [
            'open'        => 'bg-emerald-500 shadow-emerald-500/20',
            'in-progress' => 'bg-blue-500 shadow-blue-500/20',
            'resolved'    => 'bg-indigo-500 shadow-indigo-500/20',
            'closed'      => 'bg-slate-500 shadow-slate-500/20',
            'on-hold'     => 'bg-amber-500 shadow-amber-500/20'
        ];
        $dot_color = $status_colors[strtolower($ticket['status'])] ?? 'bg-slate-300';
        
        // Priority Badge Logic
        $prio_styles = [
            'high'   => 'text-rose-500 bg-rose-50 dark:bg-rose-900/20 border-rose-100 dark:border-rose-800',
            'medium' => 'text-amber-500 bg-amber-50 dark:bg-amber-900/20 border-amber-100 dark:border-amber-800',
            'low'    => 'text-emerald-500 bg-emerald-50 dark:bg-emerald-900/20 border-emerald-100 dark:border-emerald-800'
        ];
        $p_class = $prio_styles[strtolower($ticket['priority'])] ?? 'text-slate-500 bg-slate-50';
        ?>

        <div class="flex items-start justify-between mb-8">
            <div class="space-y-3">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="px-3 py-1 bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-lg text-[10px] font-black tracking-widest">
                        REF #<?php echo str_pad($ticket['id'], 4, '0', STR_PAD_LEFT); ?>
                    </span>
                    <div class="flex items-center gap-2 px-3 py-1 rounded-full border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm">
                        <div class="w-2 h-2 rounded-full <?php echo $dot_color; ?> animate-pulse shadow-lg"></div>
                        <span class="text-[10px] font-black uppercase tracking-[0.1em] text-slate-500">
                            <?php echo str_replace('-', ' ', $ticket['status']); ?>
                        </span>
                    </div>
                    <span class="px-3 py-1 rounded-lg border text-[10px] font-black uppercase tracking-widest <?php echo $p_class; ?>">
                        Priority: <?php echo $ticket['priority']; ?>
                    </span>
                </div>
                <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tighter leading-tight">
                    <?php echo htmlspecialchars($ticket['title']); ?>
                </h2>
            </div>
            <button onclick="closeTicketModal()" class="p-3 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-2xl transition-all text-slate-400 group">
                <i data-lucide="x" class="w-6 h-6 group-hover:rotate-90 transition-transform"></i>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="p-5 bg-slate-50 dark:bg-slate-800/40 rounded-3xl border border-slate-100 dark:border-slate-800/50">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">Workstream</p>
                <div class="flex items-center gap-2">
                    <i data-lucide="layers" class="w-4 h-4 text-indigo-500"></i>
                    <p class="text-xs font-bold text-slate-700 dark:text-slate-200">
                        <?php echo htmlspecialchars($ticket['project_name'] ?? 'General Backlog'); ?>
                    </p>
                </div>
            </div>
            <div class="p-5 bg-slate-50 dark:bg-slate-800/40 rounded-3xl border border-slate-100 dark:border-slate-800/50">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">Classification</p>
                <div class="flex items-center gap-2">
                    <i data-lucide="tag" class="w-4 h-4 text-emerald-500"></i>
                    <p class="text-xs font-bold text-slate-700 dark:text-slate-200">
                        <?php echo htmlspecialchars($ticket['category_name'] ?? 'Uncategorized'); ?>
                    </p>
                </div>
            </div>
            <div class="p-5 bg-slate-50 dark:bg-slate-800/40 rounded-3xl border border-slate-100 dark:border-slate-800/50">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">Originator</p>
                <div class="flex items-center gap-2">
                    <i data-lucide="user-plus" class="w-4 h-4 text-blue-500"></i>
                    <p class="text-xs font-bold text-slate-700 dark:text-slate-200">
                        <?php echo htmlspecialchars($ticket['creator_name'] ?? 'System Core'); ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="space-y-3 mb-10">
            <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-500 flex items-center gap-2 ml-1">
                <i data-lucide="terminal" class="w-3 h-3"></i> Intelligence Summary
            </h3>
            <div class="text-slate-600 dark:text-slate-300 text-sm leading-relaxed p-8 bg-slate-50/50 dark:bg-slate-800/20 border border-slate-100 dark:border-slate-800 rounded-[2.5rem] relative overflow-hidden shadow-inner italic">
                <div class="absolute top-0 left-0 w-1 h-full bg-indigo-500/20"></div>
                "<?php echo nl2br(htmlspecialchars($ticket['description'])); ?>"
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-8 border-t border-slate-100 dark:border-slate-800">
            <div class="flex flex-col">
                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Capture Timestamp</span>
                <span class="text-[11px] font-bold text-slate-500 italic">
                    <?php echo date('F d, Y • H:i:s', strtotime($ticket['created_at'])); ?>
                </span>
            </div>
            
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <button onclick="editTicket(<?php echo $ticket['id']; ?>)" class="flex-grow sm:flex-none px-8 py-4 bg-indigo-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.15em] hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-200 dark:shadow-none hover:-translate-y-1">
                    Modify Record
                </button>
                <button onclick="closeTicketModal()" class="flex-grow sm:flex-none px-8 py-4 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 rounded-2xl text-[10px] font-black uppercase tracking-[0.15em] hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">
                    Dismiss
                </button>
            </div>
        </div>

        <?php
    } else {
        echo "<div class='p-20 text-center'>
                <i data-lucide='alert-triangle' class='w-12 h-12 text-rose-500 mx-auto mb-4'></i>
                <p class='font-black text-rose-500 uppercase tracking-widest'>Data Purge Detected: Record Not Found</p>
              </div>";
    }
    $stmt->close();
}
?>

<script>
    // Refresh icons inside the modal
    if(window.lucide) lucide.createIcons();
</script>