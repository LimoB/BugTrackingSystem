<?php
session_start();
include('../../../config/config.php');

// ✅ Admin Security
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    die("<div class='p-6 text-red-500 font-bold'>Unauthorized Access</div>");
}

if (isset($_GET['ticket_id'])) {
    $ticketId = intval($_GET['ticket_id']);

    $query = "SELECT t.*, p.name as project_name FROM Tickets t 
              LEFT JOIN Projects p ON t.project_id = p.id 
              WHERE t.id = ?";
    $stmt = mysqli_prepare($connection, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $ticketId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $ticket = mysqli_fetch_assoc($result);

        if ($ticket) {
            ?>
            <div class="animate-fade-in">
                <div class="flex items-center gap-4 mb-6 border-b border-slate-100 dark:border-slate-800 pb-4">
                    <div class="bg-emerald-600 p-2.5 rounded-2xl shadow-lg shadow-emerald-100 dark:shadow-none">
                        <i data-lucide="shield-alert" class="text-white w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-900 dark:text-white">Admin Override</h3>
                        <p class="text-[10px] text-slate-500 uppercase tracking-widest font-bold">
                            Modify Ticket #<?php echo $ticket['id']; ?> — <?php echo htmlspecialchars($ticket['project_name'] ?? 'General'); ?>
                        </p>
                    </div>
                </div>

                <form id="updateTicketForm" class="space-y-6">
                    <input type="hidden" name="ticketId" value="<?php echo $ticket['id']; ?>">
                    <input type="hidden" name="ticketProject" value="<?php echo $ticket['project_id']; ?>">

                    <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-2xl border border-slate-100 dark:border-slate-800">
                        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1">Current Subject</label>
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-300"><?php echo htmlspecialchars($ticket['title']); ?></p>
                    </div>

                    <div>
                        <label for="ticketStatus" class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2 ml-1">Force System Status</label>
                        <div class="relative">
                            <select id="ticketStatus" name="ticketStatus" 
                                    class="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none appearance-none cursor-pointer font-bold text-sm transition-all">
                                <?php
                                $statuses = [
                                    'open' => 'Open / New',
                                    'in-progress' => 'In Progress / Active',
                                    'resolved' => 'Resolved / Fixed',
                                    'closed' => 'Closed / Archived',
                                    'on_hold' => 'On Hold / Pending Info' // ✅ Matches validation array
                                ];
                                foreach ($statuses as $val => $label) {
                                    $selected = ($ticket['status'] == $val) ? 'selected' : '';
                                    echo "<option value='$val' $selected>$label</option>";
                                }
                                ?>
                            </select>
                            <i data-lucide="chevron-down" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="flex-grow bg-slate-900 dark:bg-white dark:text-slate-900 text-white font-black py-4 rounded-2xl hover:bg-emerald-600 dark:hover:bg-emerald-500 dark:hover:text-white transition-all flex items-center justify-center gap-2 text-xs uppercase tracking-widest">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            Update Lifecycle
                        </button>
                        <button type="button" onclick="closeTicketModal()" class="px-6 py-4 bg-slate-100 dark:bg-slate-800 text-slate-500 font-bold rounded-2xl hover:bg-slate-200 transition-all text-xs uppercase tracking-widest">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>

            <script>
                lucide.createIcons();

                $('#updateTicketForm').off('submit').on('submit', function(e) {
                    e.preventDefault();
                    const btn = $(this).find('button[type="submit"]');
                    btn.prop('disabled', true).addClass('opacity-50 cursor-not-allowed');

                    $.ajax({
                        // ✅ Go up to admin level then into actions
                        url: './actions/update-ticket-action.php', 
                        method: 'POST',
                        data: $(this).serialize(),
                        dataType: 'json',
                        success: function(res) {
                            if(res.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Lifecycle Updated',
                                    text: res.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                // Refresh your specific page loader
                                if(typeof loadPage === 'function') loadPage('manage-tickets'); 
                                if(typeof closeTicketModal === 'function') closeTicketModal();
                            } else {
                                Swal.fire('Error', res.error, 'error');
                                btn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('System Error', 'Status: ' + xhr.status, 'error');
                            btn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
                        }
                    });
                });
            </script>
            <?php
        } else {
            echo "<div class='p-10 text-center text-slate-400 italic'>Ticket ID #$ticketId does not exist.</div>";
        }
        mysqli_stmt_close($stmt);
    }
}
?>