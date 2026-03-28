<?php
session_start();
include('../../config/config.php');

// ✅ Admin Security Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    die("<div class='p-6 text-red-500 font-bold text-center'>Unauthorized Access</div>");
}

// Fetch Projects with statistics (Optional: You can add a join here later to count tickets)
$query = "SELECT * FROM Projects ORDER BY id DESC";
$result = mysqli_query($connection, $query);
$total_projects = mysqli_num_rows($result);
?>

<div class="animate-fade-in">
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white leading-tight">Project Registry</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm italic">Create and oversee development workstreams.</p>
        </div>
        
        <div class="flex items-center gap-4">
            <div class="hidden sm:flex bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 px-6 py-3 rounded-2xl items-center gap-4 shadow-sm">
                <div class="w-10 h-10 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-xl flex items-center justify-center">
                    <i data-lucide="layers" class="w-5 h-5"></i>
                </div>
                <div>
                    <div class="text-[10px] font-black uppercase text-slate-400 tracking-widest leading-none mb-1">Total Assets</div>
                    <div class="text-xl font-black text-slate-900 dark:text-white leading-none"><?php echo $total_projects; ?></div>
                </div>
            </div>

            <button onclick="openModal('create')" class="inline-flex items-center gap-2 px-6 py-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-xs font-black uppercase tracking-widest transition-all shadow-lg shadow-emerald-100 dark:shadow-none hover:scale-[1.02] active:scale-95">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                New Project
            </button>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[2.5rem] overflow-hidden shadow-sm">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                    <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Reference</th>
                    <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Workstream Name</th>
                    <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Current Status</th>
                    <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Operations</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <?php while ($project = mysqli_fetch_assoc($result)): 
                    $status_class = [
                        'active'    => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                        'pending'   => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                        'completed' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400'
                    ][strtolower($project['status'])] ?? 'bg-slate-50 text-slate-500';
                ?>
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-all group" id="project-row-<?php echo $project['id']; ?>">
                    <td class="px-6 py-5">
                        <span class="font-mono text-[10px] font-black text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded-md">
                            #PROJ-<?php echo str_pad($project['id'], 3, '0', STR_PAD_LEFT); ?>
                        </span>
                    </td>
                    <td class="px-6 py-5">
                        <div class="font-bold text-slate-900 dark:text-white group-hover:text-emerald-600 transition-colors">
                            <?php echo htmlspecialchars($project['name']); ?>
                        </div>
                    </td>
                    <td class="px-6 py-5">
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter <?php echo $status_class; ?>">
                            <?php echo $project['status']; ?>
                        </span>
                    </td>
                    <td class="px-6 py-5 text-right space-x-1">
                        <button onclick="viewProject(<?php echo $project['id']; ?>)" class="p-2 hover:bg-blue-50 dark:hover:bg-blue-900/20 text-slate-400 hover:text-blue-500 rounded-lg transition-all">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                        <button onclick="editProject(<?php echo $project['id']; ?>)" class="p-2 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 text-slate-400 hover:text-emerald-600 rounded-lg transition-all">
                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                        </button>
                        <button onclick="deleteProject(<?php echo $project['id']; ?>)" class="p-2 hover:bg-rose-50 dark:hover:bg-rose-900/20 text-slate-400 hover:text-rose-600 rounded-lg transition-all">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="projectModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" id="modalBackdrop"></div>
    <div class="relative w-full max-w-lg transform transition-all">
        <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-800">
            <div id="modalContent" class="p-10">
                </div>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();

    // 🛠 MODAL CONTROLLER
    function openModal(type, id = null) {
        const modal = $('#projectModal');
        const content = $('#modalContent');
        modal.removeClass('hidden').addClass('flex');

        if (type === 'create') {
            content.html(`
                <div class="mb-8">
                    <h2 class="text-2xl font-black text-slate-900 dark:text-white">Initialize Project</h2>
                    <p class="text-slate-500 text-sm italic">Define a new workstream for the development team.</p>
                </div>
                <form id="createProjectForm" class="space-y-5">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Workstream Name</label>
                        <input type="text" name="projectName" placeholder="e.g., Nexus API Redesign" required class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl font-bold text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-emerald-500 transition-all outline-none">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Strategic Description</label>
                        <textarea name="projectDescription" placeholder="What is the primary objective?" rows="4" required class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl font-medium text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-emerald-500 transition-all outline-none"></textarea>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Lifecycle Status</label>
                        <select name="projectStatus" class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl font-bold text-sm text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-emerald-500 transition-all outline-none cursor-pointer">
                            <option value="pending">🟡 Pending / Discovery</option>
                            <option value="active" selected>🟢 Active / Production</option>
                            <option value="completed">⚪ Completed / Legacy</option>
                        </select>
                    </div>
                    <div class="flex gap-3 pt-6">
                        <button type="submit" class="flex-grow bg-slate-900 dark:bg-white dark:text-slate-900 text-white font-black py-4 rounded-2xl hover:bg-emerald-600 dark:hover:bg-emerald-500 dark:hover:text-white transition-all text-xs uppercase tracking-widest shadow-xl shadow-slate-200 dark:shadow-none">Launch Project</button>
                        <button type="button" onclick="closeModal()" class="px-8 py-4 bg-slate-100 dark:bg-slate-800 text-slate-500 font-bold rounded-2xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-all text-xs uppercase tracking-widest">Abort</button>
                    </div>
                </form>
            `);
            lucide.createIcons();
        }
    }

    function closeModal() {
        $('#projectModal').addClass('hidden').removeClass('flex');
    }

    // Modal Backdrop closer (Fixed)
    $('#modalBackdrop').click(function() { closeModal(); });

    // 👁 VIEW PROJECT
    function viewProject(id) {
        $('#projectModal').removeClass('hidden').addClass('flex');
        $('#modalContent').html(`
            <div class="flex flex-col items-center justify-center p-12 text-slate-400">
                <div class="animate-spin mb-4"><i data-lucide="loader-2" class="w-8 h-8"></i></div>
                <p class="font-bold text-xs uppercase tracking-widest">Accessing Registry...</p>
            </div>
        `);
        lucide.createIcons();
        $.get('./api/fetch-project-details.php', { project_id: id }, function(data) {
            $('#modalContent').html(data);
            lucide.createIcons();
        });
    }

    // 📝 EDIT PROJECT
    function editProject(id) {
        $('#projectModal').removeClass('hidden').addClass('flex');
        $('#modalContent').html(`
            <div class="flex flex-col items-center justify-center p-12 text-emerald-500">
                <div class="animate-pulse mb-4"><i data-lucide="edit-3" class="w-8 h-8"></i></div>
                <p class="font-bold text-xs uppercase tracking-widest">Fetching Configuration...</p>
            </div>
        `);
        lucide.createIcons();
        $.get('./api/fetch-project-update-form.php', { project_id: id }, function(data) {
            $('#modalContent').html(data);
            lucide.createIcons();
        });
    }

    // 🗑 DELETE PROJECT
    function deleteProject(id) {
        Swal.fire({
            title: 'Purge Project?',
            text: "Warning: This action will sever all associated ticket links!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Purge Project',
            background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#fff',
            color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#0f172a'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('./actions/delete-project-action.php', { project_id: id }, function(response) {
                    $(`#project-row-${id}`).addClass('scale-95 opacity-0 transition-all duration-500');
                    setTimeout(() => loadPage('manage-projects'), 500);
                    Swal.fire('Purged', 'Workstream removed from system.', 'success');
                });
            }
        });
    }

    // 🚀 CREATE AJAX
    $(document).on('submit', '#createProjectForm', function(e) {
        e.preventDefault();
        const btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Initializing...');
        lucide.createIcons();

        $.post('./actions/create-project-action.php', $(this).serialize(), function(response) {
            const data = typeof response === 'string' ? JSON.parse(response) : response;
            if(data.success) {
                closeModal();
                Swal.fire('Success', data.message, 'success');
                loadPage('manage-projects');
            } else {
                Swal.fire('Error', data.error, 'error');
                btn.prop('disabled', false).html('Launch Project');
            }
        });
    });
</script>