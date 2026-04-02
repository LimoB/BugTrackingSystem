<?php
/**
 * File: admin/api/fetch-project-update-form.php
 * Purpose: Fetches project data and renders the high-fidelity update interface.
 */
include('../../../config/config.php');

if (isset($_GET['project_id'])) {
    $project_id = intval($_GET['project_id']);
    
    // 🛡️ Prepared Statement for Security
    $stmt = $connection->prepare("SELECT * FROM Projects WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($project = $result->fetch_assoc()) {
        ?>
        <div class="animate-in fade-in slide-in-from-bottom-4 duration-500">
            <div class="mb-10 flex items-center gap-5">
                <div class="w-14 h-14 bg-amber-50 dark:bg-amber-900/20 text-amber-500 rounded-2xl flex items-center justify-center shadow-sm">
                    <i data-lucide="settings-2" class="w-7 h-7"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white tracking-tighter leading-tight">Configuration Mode</h3>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] flex items-center gap-2">
                        <span class="w-2 h-2 bg-slate-200 dark:bg-slate-700 rounded-full"></span>
                        Registry Node: #PROJ-<?php echo str_pad($project['id'], 3, '0', STR_PAD_LEFT); ?>
                    </p>
                </div>
            </div>

            <form id="updateProjectForm" class="space-y-6">
                <input type="hidden" name="projectId" value="<?php echo $project['id']; ?>">

                <div class="space-y-2">
                    <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Workstream Identity</label>
                    <input type="text" name="projectName" 
                           value="<?php echo htmlspecialchars($project['name']); ?>" 
                           required 
                           placeholder="Enter project name..."
                           class="w-full px-6 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-[1.5rem] focus:ring-2 focus:ring-amber-500/50 outline-none transition-all font-bold text-slate-700 dark:text-slate-200 shadow-inner">
                </div>

                <div class="space-y-2">
                    <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Strategic Objective</label>
                    <textarea name="projectDescription" rows="4" required 
                              placeholder="Describe the mission parameters..."
                              class="w-full px-6 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-[1.5rem] focus:ring-2 focus:ring-amber-500/50 outline-none transition-all resize-none text-sm leading-relaxed text-slate-600 dark:text-slate-400 shadow-inner font-medium"><?php echo htmlspecialchars($project['description']); ?></textarea>
                </div>

                <div class="space-y-2">
                    <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Operational Lifecycle</label>
                    <div class="relative group">
                        <select name="projectStatus" class="w-full px-6 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-[1.5rem] focus:ring-2 focus:ring-amber-500/50 outline-none appearance-none cursor-pointer font-black text-xs uppercase tracking-widest text-slate-600 dark:text-slate-300 shadow-inner transition-all">
                            <option value="active" <?php echo ($project['status'] == 'active') ? 'selected' : ''; ?>>🟢 Active Production</option>
                            <option value="pending" <?php echo ($project['status'] == 'pending') ? 'selected' : ''; ?>>🟡 Discovery / Pending</option>
                            <option value="completed" <?php echo ($project['status'] == 'completed') ? 'selected' : ''; ?>>⚪ Completed Legacy</option>
                        </select>
                        <i data-lucide="chevron-down" class="absolute right-6 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none group-hover:text-amber-500 transition-colors"></i>
                    </div>
                </div>

                <div class="pt-6 flex flex-col sm:flex-row gap-3">
                    <button type="submit" class="flex-grow bg-slate-900 dark:bg-amber-500 text-white dark:text-slate-900 font-black py-5 rounded-[1.5rem] shadow-xl hover:bg-amber-500 dark:hover:bg-amber-400 hover:text-white transition-all flex items-center justify-center gap-3 text-[10px] uppercase tracking-[0.2em]">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                        Sync Configuration
                    </button>
                    <button type="button" onclick="closeModal()" class="px-8 py-5 bg-slate-100 dark:bg-slate-800 text-slate-400 font-black rounded-[1.5rem] hover:bg-slate-200 dark:hover:bg-slate-700 hover:text-slate-600 transition-all text-[10px] uppercase tracking-widest">
                        Abort
                    </button>
                </div>
            </form>
        </div>

        <script>
            // Re-initialize icons for the newly injected HTML
            if(window.lucide) lucide.createIcons();

            // Intercept form submission
            $('#updateProjectForm').on('submit', function(e) {
                e.preventDefault();
                const submitBtn = $(this).find('button[type="submit"]');
                const originalHtml = submitBtn.html();

                // 🚦 Visual Feedback: Loading State
                submitBtn.prop('disabled', true).html('<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> SYNCING...');
                lucide.createIcons();
                
                $.ajax({
                    url: './actions/update-project-action.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(res) {
                        if(res.success) {
                            Toast.fire({
                                icon: 'success',
                                title: 'Registry Updated',
                                background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#fff',
                                color: document.documentElement.classList.contains('dark') ? '#fff' : '#000'
                            });
                            
                            // Refresh the background list
                            loadPage('manage-projects');
                            
                            // Close modal safely
                            if(typeof closeModal === 'function') closeModal();
                        } else {
                            Swal.fire('Sync Error', res.error, 'error');
                            submitBtn.prop('disabled', false).html(originalHtml);
                            lucide.createIcons();
                        }
                    },
                    error: function() {
                        Swal.fire('System Error', 'Could not establish connection to the update engine.', 'error');
                        submitBtn.prop('disabled', false).html(originalHtml);
                        lucide.createIcons();
                    }
                });
            });

            // Define Toast if not already defined globally
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            });
        </script>
        <?php
    } else {
        echo "<div class='p-20 text-center animate-pulse'><i data-lucide='alert-octagon' class='w-12 h-12 text-rose-500 mx-auto mb-4'></i><p class='font-black text-slate-400 uppercase tracking-widest text-xs'>Node Not Found</p></div>";
    }
    $stmt->close();
}
$connection->close();
?>