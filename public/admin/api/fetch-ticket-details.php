<?php
session_start();
include('../../../config/config.php');

// 🛡️ Security Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    die("<div class='p-6 text-rose-500 font-bold'>Unauthorized Access</div>");
}

$ticketId = isset($_GET['ticket_id']) ? intval($_GET['ticket_id']) : 0;

if ($ticketId > 0) {
    // 📡 Fetch comprehensive ticket data with Project and Creator info
    $query = "SELECT t.*, p.name AS project_name, u.name AS creator_name 
              FROM Tickets t 
              LEFT JOIN Projects p ON t.project_id = p.id 
              LEFT JOIN Users u ON t.created_by = u.id 
              WHERE t.id = ?";
              
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $ticketId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($ticket = $result->fetch_assoc()) {
        $status_colors = [
            'open' => 'bg-emerald-500',
            'in-progress' => 'bg-blue-500',
            'resolved' => 'bg-indigo-500',
            'closed' => 'bg-slate-500',
            'on-hold' => 'bg-amber-500'
        ];
        $dot_color = $status_colors[strtolower($ticket['status'])] ?? 'bg-slate-300';
        ?>

        <div class="flex items-start justify-between mb-8">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-500 rounded text-[10px] font-mono font-bold">
                        #TKT-<?php echo str_pad($ticket['id'], 4, '0', STR_PAD_LEFT); ?>
                    </span>
                    <div class="flex items-center gap-1.5 px-2 py-0.5 rounded-full border border-slate-100 dark:border-slate-800">
                        <div class="w-1.5 h-1.5 rounded-full <?php echo $dot_color; ?> animate-pulse"></div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">
                            <?php echo str_replace('-', ' ', $ticket['status']); ?>
                        </span>
                    </div>
                </div>
                <h2 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">
                    <?php echo htmlspecialchars($ticket['title']); ?>
                </h2>
            </div>
            <button onclick="closeTicketModal()" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all text-slate-400">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-8">
            <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-800">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Workstream</p>
                <p class="text-sm font-bold text-slate-700 dark:text-slate-200">
                    <?php echo htmlspecialchars($ticket['project_name'] ?? 'General Backlog'); ?>
                </p>
            </div>
            <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-800">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Originator</p>
                <p class="text-sm font-bold text-slate-700 dark:text-slate-200">
                    <?php echo htmlspecialchars($ticket['creator_name'] ?? 'System'); ?>
                </p>
            </div>
        </div>

        <div class="space-y-2 mb-8">
            <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-500 flex items-center gap-2">
                <i data-lucide="align-left" class="w-3 h-3"></i> Briefing & Description
            </h3>
            <div class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed p-6 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-[2rem] shadow-sm italic">
                "<?php echo nl2br(htmlspecialchars($ticket['description'])); ?>"
            </div>
        </div>

        <div class="flex items-center justify-between pt-6 border-t border-slate-100 dark:border-slate-800">
            <div class="text-[10px] font-medium text-slate-400 italic">
                Captured on <?php echo date('M d, Y • H:i', strtotime($ticket['created_at'])); ?>
            </div>
            <div class="flex gap-2">
                <button onclick="editTicket(<?php echo $ticket['id']; ?>)" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 dark:shadow-none">
                    Modify Intel
                </button>
                <button onclick="closeTicketModal()" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-500 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all">
                    Dismiss
                </button>
            </div>
        </div>

        <?php
    } else {
        echo "<div class='p-12 text-center font-bold text-rose-500'>Error: Ticket reference has been purged from registry.</div>";
    }
    $stmt->close();
}
?>