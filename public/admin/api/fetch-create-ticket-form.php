<?php
session_start();
include('../../../config/config.php');

// ✅ Admin Security
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    die("<div class='p-6 text-rose-500 font-black'>Access Denied</div>");
}

// Fetch active projects for the dropdown
$project_query = "SELECT id, name FROM Projects WHERE status = 'active' ORDER BY name ASC";
$projects = mysqli_query($connection, $project_query);
?>

<div class="animate-fade-in">
    <div class="flex items-center gap-4 mb-8 border-b border-slate-100 dark:border-slate-800 pb-5">
        <div class="bg-emerald-500 p-3 rounded-2xl shadow-lg shadow-emerald-100 dark:shadow-none text-white">
            <i data-lucide="plus-circle" class="w-5 h-5"></i>
        </div>
        <div>
            <h3 class="text-xl font-black text-slate-900 dark:text-white leading-tight">Initialize Ticket</h3>
            <p class="text-[10px] text-slate-500 uppercase tracking-widest font-bold">Deploy a new entry into the tracking backlog</p>
        </div>
    </div>

    <form id="createTicketForm" class="space-y-5">
        <div class="space-y-1">
            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Issue Headline</label>
            <input type="text" name="ticketTitle" placeholder="e.g., Critical Header Overflow on Mobile" required 
                   class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl font-bold text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-emerald-500 transition-all outline-none">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="space-y-1">
                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Target Project</label>
                <div class="relative">
                    <select name="ticketProject" required class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl font-bold text-sm appearance-none cursor-pointer focus:ring-2 focus:ring-emerald-500 outline-none">
                        <option value="" disabled selected>Select Infrastructure</option>
                        <?php while($row = mysqli_fetch_assoc($projects)): ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                    <i data-lucide="chevron-down" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Priority Matrix</label>
                <div class="relative">
                    <select name="ticketPriority" required class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl font-bold text-sm appearance-none cursor-pointer focus:ring-2 focus:ring-emerald-500 outline-none">
                        <option value="low">Low / Cosmetic</option>
                        <option value="medium" selected>Medium / Operational</option>
                        <option value="high">High / Critical</option>
                    </select>
                    <i data-lucide="alert-circle" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
                </div>
            </div>
        </div>

        <div class="space-y-1">
            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Detailed Intelligence</label>
            <textarea name="ticketDescription" rows="4" placeholder="Steps to reproduce, expected vs actual behavior..." required 
                      class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl font-medium text-sm focus:ring-2 focus:ring-emerald-500 transition-all outline-none"></textarea>
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit" class="flex-grow bg-slate-900 dark:bg-white dark:text-slate-900 text-white font-black py-4 rounded-2xl hover:bg-emerald-600 dark:hover:bg-emerald-500 dark:hover:text-white transition-all flex items-center justify-center gap-2 text-xs uppercase tracking-widest shadow-xl shadow-slate-200 dark:shadow-none">
                <i data-lucide="send" class="w-4 h-4"></i>
                Deploy Ticket
            </button>
            <button type="button" onclick="closeUserModal()" class="px-8 py-4 bg-slate-100 dark:bg-slate-800 text-slate-500 font-bold rounded-2xl hover:bg-slate-200 transition-all text-xs uppercase tracking-widest">
                Abort
            </button>
        </div>
    </form>
</div>

<script>
    lucide.createIcons();

    $('#createTicketForm').off('submit').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const btn = form.find('button[type="submit"]');
        
        btn.prop('disabled', true).html('<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Processing...');
        lucide.createIcons();

        $.ajax({
            url: './actions/create-ticket-action.php',
            method: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(res) {
                if(res.success) {
                    Swal.fire({ icon: 'success', title: 'Deployed!', text: res.message, timer: 2000, showConfirmButton: false });
                    if(typeof loadPage === 'function') loadPage('manage-tickets');
                    if(typeof closeUserModal === 'function') closeUserModal();
                } else {
                    Swal.fire('Error', res.error || 'Unknown error', 'error');
                    btn.prop('disabled', false).html('<i data-lucide="send" class="w-4 h-4"></i> Deploy Ticket');
                    lucide.createIcons();
                }
            },
            error: function(xhr) {
                let errorMsg = "Critical failure: Ticket deployment aborted.";
                try {
                    const res = JSON.parse(xhr.responseText);
                    errorMsg = res.error || errorMsg;
                } catch(e) {
                    // This handles the 500 HTML error pages
                    errorMsg = "Server Error (500): Check if the 'priority' column exists in your database.";
                }
                
                Swal.fire('Deployment Failed', errorMsg, 'error');
                btn.prop('disabled', false).html('<i data-lucide="send" class="w-4 h-4"></i> Deploy Ticket');
                lucide.createIcons();
            }
        });
    });
</script>