<?php
session_start();
include('../../../config/config.php');

// ✅ Admin Security Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    die("<div class='p-6 text-rose-500 font-black text-center'>Access Denied: Administrative Clearance Required</div>");
}

// 📡 Fetch active projects
$project_query = "SELECT id, name FROM Projects ORDER BY name ASC";
$projects = mysqli_query($connection, $project_query);

// 📡 Fetch categories (The new table we added)
$cat_query = "SELECT id, name FROM Categories ORDER BY name ASC";
$categories = mysqli_query($connection, $cat_query);
?>

<div class="animate-in fade-in slide-in-from-bottom-4 duration-500">
    <div class="flex items-center gap-4 mb-8 border-b border-slate-100 dark:border-slate-800 pb-6">
        <div class="bg-indigo-600 dark:bg-emerald-600 p-4 rounded-2xl shadow-lg shadow-indigo-200 dark:shadow-none text-white">
            <i data-lucide="shield-plus" class="w-6 h-6"></i>
        </div>
        <div>
            <h3 class="text-2xl font-black text-slate-900 dark:text-white leading-tight tracking-tight">Initialize Ticket</h3>
            <p class="text-[10px] text-slate-500 uppercase tracking-[0.2em] font-black">Deploy new entry into system backlog</p>
        </div>
    </div>

    <form id="createTicketForm" class="space-y-6">
        <div class="space-y-1.5">
            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Issue Intel Headline</label>
            <input type="text" name="ticketTitle" placeholder="e.g., Auth-Server Latency Spike" required 
                   class="w-full px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-2 border-transparent focus:border-indigo-500/20 dark:focus:border-emerald-500/20 rounded-[1.5rem] font-bold text-slate-700 dark:text-slate-200 transition-all outline-none">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="space-y-1.5">
                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Workstream</label>
                <div class="relative">
                    <select name="ticketProject" required class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800/50 border-none rounded-2xl font-bold text-xs appearance-none cursor-pointer focus:ring-2 focus:ring-indigo-500 dark:focus:ring-emerald-500 outline-none text-slate-600 dark:text-slate-300">
                        <option value="" disabled selected>Select Unit</option>
                        <?php while($row = mysqli_fetch_assoc($projects)): ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                    <i data-lucide="chevron-down" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Classification</label>
                <div class="relative">
                    <select name="ticketCategory" required class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800/50 border-none rounded-2xl font-bold text-xs appearance-none cursor-pointer focus:ring-2 focus:ring-indigo-500 dark:focus:ring-emerald-500 outline-none text-slate-600 dark:text-slate-300">
                        <option value="" disabled selected>Define Type</option>
                        <?php while($cat = mysqli_fetch_assoc($categories)): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                    <i data-lucide="tag" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Priority Matrix</label>
                <div class="relative">
                    <select name="ticketPriority" required class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800/50 border-none rounded-2xl font-bold text-xs appearance-none cursor-pointer focus:ring-2 focus:ring-indigo-500 dark:focus:ring-emerald-500 outline-none text-slate-600 dark:text-slate-300">
                        <option value="low">Low / Cosmetic</option>
                        <option value="medium" selected>Medium / Operational</option>
                        <option value="high">High / Critical</option>
                    </select>
                    <i data-lucide="zap" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
                </div>
            </div>
        </div>

        <div class="space-y-1.5">
            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Full Intel Summary</label>
            <textarea name="ticketDescription" rows="4" placeholder="Detail the technical parameters and expected outcome..." required 
                      class="w-full px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-2 border-transparent focus:border-indigo-500/20 dark:focus:border-emerald-500/20 rounded-[1.5rem] font-medium text-sm text-slate-600 dark:text-slate-300 transition-all outline-none resize-none"></textarea>
        </div>

        <div class="flex gap-4 pt-6">
            <button type="submit" id="submitBtn" class="flex-grow bg-slate-900 dark:bg-emerald-600 text-white font-black py-5 rounded-[1.5rem] hover:bg-indigo-600 dark:hover:bg-emerald-500 transition-all flex items-center justify-center gap-3 text-xs uppercase tracking-[0.2em] shadow-xl shadow-indigo-500/10 dark:shadow-none">
                <i data-lucide="box-select" class="w-4 h-4"></i>
                Initialize Record
            </button>
            <button type="button" onclick="closeTicketModal()" class="px-10 py-5 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-black rounded-[1.5rem] hover:bg-slate-200 dark:hover:bg-slate-700 transition-all text-xs uppercase tracking-widest">
                Abort
            </button>
        </div>
    </form>
</div>

<script>
    if (window.lucide) lucide.createIcons();

    $('#createTicketForm').off('submit').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const btn = $('#submitBtn');
        
        btn.prop('disabled', true).addClass('opacity-50 cursor-not-allowed').html('<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> SYNCING...');
        lucide.createIcons();

        $.ajax({
            url: './actions/create-ticket-action.php',
            method: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(res) {
                if(res.success) {
                    Swal.fire({ 
                        icon: 'success', 
                        title: 'RECORD INITIALIZED', 
                        text: res.message, 
                        background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#fff',
                        color: document.documentElement.classList.contains('dark') ? '#fff' : '#000',
                        showConfirmButton: false,
                        timer: 1500 
                    });
                    loadPage('manage-tickets');
                    closeTicketModal();
                } else {
                    Swal.fire('SYSTEM REJECTION', res.error || 'Unknown Error', 'error');
                    resetBtn(btn);
                }
            },
            error: function() {
                Swal.fire('CORE FAILURE', 'Communication with server terminated.', 'error');
                resetBtn(btn);
            }
        });
    });

    function resetBtn(btn) {
        btn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed').html('<i data-lucide="box-select" class="w-4 h-4"></i> Initialize Record');
        lucide.createIcons();
    }
</script>