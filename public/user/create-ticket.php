<?php
session_start();
include('../../config/config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    die("Unauthorized Access Protocol.");
}

// Fetch projects for the dropdown
$projectsResult = mysqli_query($connection, "SELECT id, name FROM Projects ORDER BY name ASC");
?>

<div class="max-w-4xl mx-auto animate-in fade-in slide-in-from-bottom-8 duration-700 pb-20">
    <button onclick="loadPage('dashboard_home')" class="inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 hover:text-blue-600 transition mb-8 group">
        <i data-lucide="arrow-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"></i>
        Back to Dashboard
    </button>

    <div class="mb-10">
        <h1 class="text-4xl font-black tracking-tighter text-slate-900 dark:text-white uppercase">
            Report a <span class="text-blue-600">New Bug.</span>
        </h1>
        <p class="text-slate-500 dark:text-slate-400 mt-2 font-medium italic">Provide the technical details for our developer nodes to resolve.</p>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-200 dark:border-slate-800 shadow-2xl shadow-slate-200/50 dark:shadow-none overflow-hidden">
        <div class="h-2 bg-blue-600 w-full"></div>
        
        <form id="ticketSubmissionForm" class="p-8 md:p-12 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-3">
                    <label class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 ml-2">Target Project</label>
                    <div class="relative">
                        <select name="project_id" required 
                                class="w-full pl-12 pr-6 py-5 bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 rounded-2xl outline-none appearance-none font-bold text-xs uppercase text-slate-700 dark:text-slate-200 focus:border-blue-600 transition-all">
                            <option value="">-- SELECT SYSTEM --</option>
                            <?php while ($project = mysqli_fetch_assoc($projectsResult)): ?>
                                <option value="<?php echo $project['id']; ?>"><?php echo htmlspecialchars($project['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                        <i data-lucide="layers" class="absolute left-5 top-5 w-4 h-4 text-slate-400"></i>
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 ml-2">Urgency Level</label>
                    <div class="relative">
                        <select name="priority" class="w-full pl-12 pr-6 py-5 bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 rounded-2xl outline-none appearance-none font-bold text-xs uppercase text-blue-600 focus:border-blue-600 transition-all">
                            <option value="low">Low Priority</option>
                            <option value="medium" selected>Standard</option>
                            <option value="high">Critical / Urgent</option>
                        </select>
                        <i data-lucide="alert-circle" class="absolute left-5 top-5 w-4 h-4 text-blue-400"></i>
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <label class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 ml-2">Subject / Title</label>
                <input type="text" name="title" placeholder="e.g. Database connection timeout on login" required 
                       class="w-full px-8 py-5 bg-slate-50 dark:bg-slate-800/40 border-2 border-transparent focus:border-blue-600 rounded-2xl outline-none transition-all font-bold text-slate-800 dark:text-white">
            </div>

            <div class="space-y-3">
                <label class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 ml-2">Technical Description</label>
                <textarea name="description" rows="6" placeholder="Describe the steps to reproduce the anomaly..." required 
                          class="w-full px-8 py-6 bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 rounded-[2rem] focus:border-blue-600 outline-none transition-all resize-none text-slate-600 dark:text-slate-300 font-medium"></textarea>
            </div>

            <div class="pt-6 flex justify-end gap-4">
                <button type="button" onclick="loadPage('dashboard_home')" class="px-8 py-4 text-slate-400 font-black uppercase text-[10px] tracking-widest hover:text-rose-500 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-12 py-5 bg-blue-600 text-white rounded-[1.5rem] font-black uppercase text-[10px] tracking-[0.2em] hover:shadow-2xl hover:shadow-blue-500/40 transition-all flex items-center gap-3 active:scale-95">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    Submit Report
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    if (window.lucide) { lucide.createIcons(); }

    $('#ticketSubmissionForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        const originalText = $btn.html();
        
        $btn.prop('disabled', true).html('TRANSMITTING...');

        $.ajax({
            url: 'create-ticket-action.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if(res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'TICKET_CREATED',
                        text: res.message,
                        background: '#0f172a',
                        color: '#fff',
                        confirmButtonColor: '#2563eb'
                    }).then(() => {
                        loadPage('view-tickets');
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'ERROR', text: res.message, background: '#0f172a', color: '#fff' });
                    $btn.prop('disabled', false).html(originalText);
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'CONNECTION_LOST', text: 'Server did not respond.', background: '#0f172a', color: '#fff' });
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });
</script>