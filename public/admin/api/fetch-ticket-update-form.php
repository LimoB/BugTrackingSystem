<?php
/**
 * File: admin/api/fetch-ticket-update-form.php
 * Purpose: High-level administrative override for ticket lifecycle and prioritization.
 */
session_start();
require_once('../../../config/config.php');

// 1. 🛡️ Admin Security Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    http_response_code(403);
    die("<div class='p-12 text-rose-500 font-black text-center bg-rose-50 dark:bg-rose-950/20 rounded-3xl uppercase tracking-widest text-xs'>Access Denied: Administrative Clearance Required</div>");
}

// 2. 🔍 Input Verification
$ticketId = isset($_GET['ticket_id']) ? intval($_GET['ticket_id']) : 0;

if ($ticketId > 0) {
    $query = "SELECT t.*, p.name as project_name, u.name as assignee_name 
              FROM Tickets t 
              LEFT JOIN Projects p ON t.project_id = p.id 
              LEFT JOIN Users u ON t.assigned_to = u.id
              WHERE t.id = ? LIMIT 1";
    
    $stmt = $connection->prepare($query);
    
    if (!$stmt) {
        http_response_code(500);
        die("<p class='p-6 text-rose-500 font-bold'>System Failure: Query Preparation Error</p>");
    }

    $stmt->bind_param('i', $ticketId);
    $stmt->execute();
    $result = $stmt->get_result();
    $ticket = $result->fetch_assoc();

    if ($ticket) {
        ?>
        <div class="animate-in fade-in slide-in-from-bottom-4 duration-500 p-2">
            <div class="flex items-center gap-5 mb-8 border-b border-slate-100 dark:border-slate-800 pb-6">
                <div class="w-14 h-14 bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 rounded-2xl flex items-center justify-center shadow-sm shrink-0">
                    <i data-lucide="shield-alert" class="w-7 h-7"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white tracking-tighter">Admin Override</h3>
                    <p class="text-[10px] text-slate-400 uppercase tracking-[0.2em] font-black">
                        Kernel Access: Ticket #<?php echo str_pad($ticket['id'], 4, '0', STR_PAD_LEFT); ?>
                    </p>
                </div>
            </div>

            <form id="updateTicketForm" class="space-y-6">
                <input type="hidden" name="ticketId" value="<?php echo $ticket['id']; ?>">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-4 bg-slate-50 dark:bg-slate-800/40 rounded-2xl border border-slate-100 dark:border-slate-800">
                        <label class="block text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Attached Workstream</label>
                        <p class="text-xs font-bold text-slate-700 dark:text-slate-200 truncate">
                            <?php echo htmlspecialchars($ticket['project_name'] ?? 'General / Internal'); ?>
                        </p>
                    </div>
                    <div class="p-4 bg-slate-50 dark:bg-slate-800/40 rounded-2xl border border-slate-100 dark:border-slate-800">
                        <label class="block text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Current Assignee</label>
                        <p class="text-xs font-bold text-slate-700 dark:text-slate-200 truncate">
                            <?php echo htmlspecialchars($ticket['assignee_name'] ?? 'Unassigned Pool'); ?>
                        </p>
                    </div>
                </div>

                <div class="px-5 py-4 bg-white dark:bg-slate-900 border-l-4 border-rose-500 rounded-r-2xl shadow-sm">
                    <label class="block text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Issue Subject</label>
                    <p class="text-sm font-bold text-slate-800 dark:text-slate-100 leading-tight">
                        <?php echo htmlspecialchars($ticket['title']); ?>
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Force Status</label>
                        <div class="relative">
                            <select name="ticketStatus" class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl font-black text-xs uppercase tracking-widest text-slate-600 dark:text-slate-300 outline-none appearance-none cursor-pointer focus:ring-2 focus:ring-rose-500/50 transition-all shadow-inner">
                                <?php
                                $statuses = [
                                    'open' => '🔴 Open / New',
                                    'in-progress' => '🔵 In Progress',
                                    'resolved' => '🟢 Resolved / Fixed',
                                    'closed' => '⚪ Closed / Archived',
                                    'on-hold' => '🟡 On Hold' // Sync with DB ENUM hyphen
                                ];
                                foreach ($statuses as $val => $label) {
                                    $selected = (strtolower($ticket['status']) == $val) ? 'selected' : '';
                                    echo "<option value='$val' $selected>$label</option>";
                                }
                                ?>
                            </select>
                            <i data-lucide="chevron-down" class="absolute right-5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Override Priority</label>
                        <div class="relative">
                            <select name="ticketPriority" class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl font-black text-xs uppercase tracking-widest text-slate-600 dark:text-slate-300 outline-none appearance-none cursor-pointer focus:ring-2 focus:ring-rose-500/50 transition-all shadow-inner">
                                <?php
                                $priorities = ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'];
                                foreach ($priorities as $val => $label) {
                                    $selected = (strtolower($ticket['priority']) == $val) ? 'selected' : '';
                                    echo "<option value='$val' $selected>$label</option>";
                                }
                                ?>
                            </select>
                            <i data-lucide="chevron-down" class="absolute right-5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
                        </div>
                    </div>
                </div>

                <div class="pt-8 flex flex-col sm:flex-row gap-3">
                    <button type="submit" class="flex-grow bg-slate-900 dark:bg-rose-600 text-white font-black py-5 rounded-3xl hover:bg-rose-700 dark:hover:bg-rose-500 transition-all text-[10px] uppercase tracking-[0.2em] shadow-xl shadow-rose-500/10 active:scale-95 flex items-center justify-center gap-3">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                        Update Lifecycle
                    </button>
                    <button type="button" onclick="closeTicketModal()" class="px-8 py-5 bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 font-black rounded-3xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-all text-[10px] uppercase tracking-widest active:scale-95">
                        Cancel
                    </button>
                </div>
            </form>
        </div>

        <script>
            if(window.lucide) lucide.createIcons();

            $('#updateTicketForm').off('submit').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const btn = form.find('button[type="submit"]');
                const originalHtml = btn.html();
                
                btn.prop('disabled', true).addClass('opacity-70').html('<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> UPDATING...');
                if(window.lucide) lucide.createIcons();

                $.ajax({
                    url: './actions/update-ticket-action.php', 
                    method: 'POST',
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(res) {
                        if(res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'System Updated',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false,
                                background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#fff',
                                color: document.documentElement.classList.contains('dark') ? '#fff' : '#000'
                            });
                            
                            if(typeof loadPage === 'function') loadPage('manage-tickets'); 
                            if(typeof closeTicketModal === 'function') closeTicketModal();
                        } else {
                            Swal.fire({ icon: 'error', title: 'Update Failed', text: res.error });
                            btn.prop('disabled', false).removeClass('opacity-70').html(originalHtml);
                            if(window.lucide) lucide.createIcons();
                        }
                    },
                    error: function(xhr) {
                        // Check if the server returned a JSON error even on 500
                        let errorMsg = 'Kernel communication failed (Status: ' + xhr.status + ')';
                        if(xhr.responseJSON && xhr.responseJSON.error) {
                            errorMsg = xhr.responseJSON.error;
                        }
                        Swal.fire('Connection Error', errorMsg, 'error');
                        btn.prop('disabled', false).removeClass('opacity-70').html(originalHtml);
                        if(window.lucide) lucide.createIcons();
                    }
                });
            });
        </script>
        <?php
    }
    $stmt->close();
}
$connection->close();
?>