<?php
// fetch-project-update-form.php
include('../../../config/config.php');

if (isset($_GET['project_id'])) {
    $project_id = intval($_GET['project_id']);
    $stmt = $connection->prepare("SELECT * FROM Projects WHERE id = ?");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($project = $result->fetch_assoc()) {
        ?>
        <div class="animate-fade-in p-2">
            <div class="mb-6 flex items-center gap-3">
                <div class="bg-emerald-100 text-emerald-600 p-2 rounded-xl">
                    <i data-lucide="edit-3" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900 dark:text-white">Modify Project</h3>
                    <p class="text-xs text-slate-500 uppercase tracking-widest font-bold">Project ID: #<?php echo $project['id']; ?></p>
                </div>
            </div>

            <form id="updateProjectForm" class="space-y-5">
                <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">

                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2 ml-1">Project Name</label>
                    <input type="text" name="name" 
                           value="<?php echo htmlspecialchars($project['name']); ?>" 
                           required 
                           class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all font-semibold">
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2 ml-1">Project Description</label>
                    <textarea name="description" rows="4" required 
                              class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all resize-none text-sm leading-relaxed"><?php echo htmlspecialchars($project['description']); ?></textarea>
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2 ml-1">Lifecycle Status</label>
                    <div class="relative">
                        <select name="status" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none appearance-none cursor-pointer font-bold text-sm">
                            <option value="active" <?php if ($project['status'] == 'active') echo 'selected'; ?>>Active Deployment</option>
                            <option value="completed" <?php if ($project['status'] == 'completed') echo 'selected'; ?>>Completed / Archived</option>
                            <option value="pending" <?php if ($project['status'] == 'pending') echo 'selected'; ?>>Pending Review</option>
                        </select>
                        <i data-lucide="chevron-down" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
                    </div>
                </div>

                <div class="pt-4 flex gap-3">
                    <button type="submit" class="flex-grow bg-emerald-600 hover:bg-emerald-700 text-white font-black py-3.5 rounded-2xl shadow-lg shadow-emerald-100 dark:shadow-none transition-all flex items-center justify-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Sync Changes
                    </button>
                    <button type="button" onclick="closeModal()" class="px-6 py-3.5 bg-slate-100 dark:bg-slate-800 text-slate-500 font-bold rounded-2xl hover:bg-slate-200 transition-all">
                        Cancel
                    </button>
                </div>
            </form>
        </div>

        <script>
            lucide.createIcons();

            // AJAX Submission for Project Update
            $('#updateProjectForm').on('submit', function(e) {
                e.preventDefault();
                const formData = $(this).serialize();
                
                $.ajax({
                    url: './actions/update-project-action.php', // Ensure this file returns JSON
                    method: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(res) {
                        if(res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Updated',
                                text: 'Project configuration synchronized.',
                                timer: 1500,
                                showConfirmButton: false
                            });
                            // Reload the project list behind the modal
                            loadPage('manage-projects');
                            if(typeof closeModal === 'function') closeModal();
                        } else {
                            Swal.fire('Error', res.error, 'error');
                        }
                    }
                });
            });
        </script>
        <?php
    } else {
        echo "<div class='p-10 text-center text-slate-400 italic'>Project record not found in database.</div>";
    }
    $stmt->close();
} else {
    echo "<div class='p-10 text-center text-red-500'>Invalid parameters provided.</div>";
}
$connection->close();
?>