<?php
session_start();
include('../../config/config.php');

// ✅ Admin Security Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    die("<div class='p-12 text-rose-500 font-black text-center bg-rose-50 dark:bg-rose-950/20 rounded-3xl'>Access Denied: Administrative Clearance Required</div>");
}

// 📡 Fetch Projects with Ticket Statistics
$query = "SELECT p.*, 
          (SELECT COUNT(*) FROM Tickets t WHERE t.project_id = p.id AND t.deleted_at IS NULL) as ticket_count,
          (SELECT COUNT(*) FROM Tickets t WHERE t.project_id = p.id AND t.status = 'resolved' AND t.deleted_at IS NULL) as resolved_count
          FROM Projects p 
          ORDER BY p.id DESC";
$result = mysqli_query($connection, $query);
$total_projects = mysqli_num_rows($result);
?>

<div class="animate-in fade-in slide-in-from-bottom-4 duration-700">
    <div class="mb-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
            <h1 class="text-4xl font-black tracking-tighter text-slate-900 dark:text-white">Project Registry</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1 italic">Strategize and oversee active development workstreams.</p>
        </div>
        
        <div class="flex items-center gap-4">
            <div class="hidden sm:flex bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 px-6 py-3 rounded-2xl items-center gap-4 shadow-sm">
                <div class="w-10 h-10 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-xl flex items-center justify-center">
                    <i data-lucide="layers" class="w-5 h-5"></i>
                </div>
                <div>
                    <div class="text-[10px] font-black uppercase text-slate-400 tracking-widest leading-none mb-1">Active Assets</div>
                    <div class="text-xl font-black text-slate-900 dark:text-white leading-none"><?php echo $total_projects; ?></div>
                </div>
            </div>

            <button onclick="openModal('create')" class="inline-flex items-center gap-3 px-8 py-4 bg-slate-900 dark:bg-emerald-600 hover:bg-emerald-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] transition-all shadow-xl shadow-emerald-500/10 hover:scale-[1.02] active:scale-95">
                <i data-lucide="plus-circle" class="w-4 h-4 text-emerald-400"></i>
                Initialize Project
            </button>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[2.5rem] overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                        <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-slate-400">Identity</th>
                        <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-slate-400">Workstream</th>
                        <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-slate-400">Ticket Load</th>
                        <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-slate-400">Lifecycle</th>
                        <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Operations</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                    <?php while ($project = mysqli_fetch_assoc($result)): 
                        $status_map = [
                            'active'    => 'text-emerald-500 bg-emerald-50 dark:bg-emerald-900/20',
                            'pending'   => 'text-amber-500 bg-amber-50 dark:bg-amber-900/20',
                            'completed' => 'text-slate-500 bg-slate-100 dark:bg-slate-800'
                        ];
                        $status_class = $status_map[strtolower($project['status'])] ?? 'bg-slate-50 text-slate-400';
                        $progress = ($project['ticket_count'] > 0) ? round(($project['resolved_count'] / $project['ticket_count']) * 100) : 0;
                    ?>
                    <tr class="hover:bg-slate-50/30 dark:hover:bg-slate-800/20 transition-all group" id="project-row-<?php echo $project['id']; ?>">
                        <td class="px-8 py-6">
                            <span class="font-mono text-[10px] font-black text-slate-300 dark:text-slate-600 group-hover:text-indigo-500 transition-colors">
                                #PROJ-<?php echo str_pad($project['id'], 3, '0', STR_PAD_LEFT); ?>
                            </span>
                        </td>
                        <td class="px-8 py-6">
                            <div class="font-bold text-slate-900 dark:text-white leading-tight"><?php echo htmlspecialchars($project['name']); ?></div>
                            <div class="text-[10px] text-slate-400 mt-1 font-medium line-clamp-1 italic">"<?php echo htmlspecialchars($project['description']); ?>"</div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-3">
                                <div class="text-xs font-black text-slate-700 dark:text-slate-300"><?php echo $project['ticket_count']; ?></div>
                                <div class="w-16 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden flex">
                                    <div class="h-full bg-indigo-500" style="width: <?php echo $progress; ?>%"></div>
                                </div>
                                <span class="text-[9px] font-bold text-slate-400"><?php echo $progress; ?>%</span>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <span class="px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest border border-current/10 <?php echo $status_class; ?>">
                                <?php echo $project['status']; ?>
                            </span>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button onclick="viewProject(<?php echo $project['id']; ?>)" class="p-2.5 text-slate-400 hover:text-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-xl transition-all" title="Inspect">
                                    <i data-lucide="maximize-2" class="w-4 h-4"></i>
                                </button>
                                <button onclick="editProject(<?php echo $project['id']; ?>)" class="p-2.5 text-slate-400 hover:text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-xl transition-all" title="Configure">
                                    <i data-lucide="settings-2" class="w-4 h-4"></i>
                                </button>
                                <button onclick="deleteProject(<?php echo $project['id']; ?>)" class="p-2.5 text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-xl transition-all" title="Archive">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="projectModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-950/40 backdrop-blur-md" id="modalBackdrop"></div>
    <div class="relative w-full max-w-xl animate-in zoom-in-95 duration-300">
        <div class="bg-white dark:bg-slate-900 rounded-[3rem] shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-800">
            <div id="modalContent" class="p-12">
                </div>
        </div>
    </div>
</div>

<script>
    if(window.lucide) lucide.createIcons();

    // 🛠 MODAL CONTROLLER
    function openModal(type, id = null) {
        const modal = $('#projectModal');
        const content = $('#modalContent');
        modal.removeClass('hidden').addClass('flex');

        if (type === 'create') {
            content.html(`
                <div class="mb-10 text-center">
                    <div class="w-16 h-16 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-500 rounded-3xl flex items-center justify-center mx-auto mb-6">
                        <i data-lucide="plus" class="w-8 h-8"></i>
                    </div>
                    <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tighter">New Workstream</h2>
                    <p class="text-slate-500 text-sm mt-1">Initialize a strategic development project.</p>
                </div>
                <form id="createProjectForm" class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Workstream Name</label>
                        <input type="text" name="projectName" placeholder="e.g., Quantum Core v2" required class="w-full px-6 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl font-bold text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-emerald-500 outline-none transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Strategic Description</label>
                        <textarea name="projectDescription" placeholder="What are the mission goals?" rows="3" required class="w-full px-6 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl font-medium text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-emerald-500 outline-none transition-all"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                         <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Initial Status</label>
                            <select name="projectStatus" class="w-full px-6 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl font-bold text-xs text-slate-600 dark:text-slate-300 outline-none cursor-pointer">
                                <option value="pending">🟡 Pending</option>
                                <option value="active" selected>🟢 Active</option>
                                <option value="completed">⚪ Completed</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex flex-col gap-3 pt-6">
                        <button type="submit" class="w-full bg-slate-900 dark:bg-emerald-600 text-white font-black py-5 rounded-2xl hover:bg-emerald-600 transition-all text-[10px] uppercase tracking-widest shadow-xl">Launch Project</button>
                        <button type="button" onclick="closeModal()" class="w-full py-4 text-slate-400 font-bold text-[10px] uppercase tracking-widest hover:text-slate-600 transition-colors">Cancel Authorization</button>
                    </div>
                </form>
            `);
            if(window.lucide) lucide.createIcons();
        }
    }

    function closeModal() {
        $('#projectModal').addClass('hidden').removeClass('flex');
    }

    $('#modalBackdrop').click(function() { closeModal(); });

    // 👁 VIEW DETAILS
    function viewProject(id) {
        $('#projectModal').removeClass('hidden').addClass('flex');
        $('#modalContent').html('<div class="p-20 text-center"><div class="animate-spin inline-block"><i data-lucide="loader-2" class="w-8 h-8 text-slate-300"></i></div></div>');
        if(window.lucide) lucide.createIcons();
        $.get('./api/fetch-project-details.php', { project_id: id }, (data) => {
            $('#modalContent').html(data);
            if(window.lucide) lucide.createIcons();
        });
    }

    // 📝 EDIT CONFIG
    function editProject(id) {
        $('#projectModal').removeClass('hidden').addClass('flex');
        $('#modalContent').html('<div class="p-20 text-center"><i data-lucide="settings" class="w-8 h-8 text-amber-500 animate-spin mx-auto"></i></div>');
        if(window.lucide) lucide.createIcons();
        $.get('./api/fetch-project-update-form.php', { project_id: id }, (data) => {
            $('#modalContent').html(data);
            if(window.lucide) lucide.createIcons();
        });
    }

    // 🗑 DELETE ACTION
    function deleteProject(id) {
        Swal.fire({
            title: 'Authorize Purge?',
            text: "This will permanently disconnect all assets linked to this workstream.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            confirmButtonText: 'PURGE PROJECT',
            background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#fff',
            color: document.documentElement.classList.contains('dark') ? '#fff' : '#000'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('./actions/delete-project-action.php', { project_id: id }, function() {
                    $(`#project-row-${id}`).addClass('scale-95 opacity-0 duration-500');
                    setTimeout(() => loadPage('manage-projects'), 500);
                    Swal.fire('Purged!', 'Project removed from registry.', 'success');
                });
            }
        });
    }

    // 🚀 FORM HANDLER
    $(document).off('submit', '#createProjectForm').on('submit', '#createProjectForm', function(e) {
        e.preventDefault();
        const btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).text('PROCESSING...');

        $.post('./actions/create-project-action.php', $(this).serialize(), function(res) {
            try {
                const data = typeof res === 'string' ? JSON.parse(res) : res;
                if(data.success) {
                    closeModal();
                    Swal.fire('Initialized', data.message, 'success');
                    loadPage('manage-projects');
                } else {
                    Swal.fire('Error', data.error, 'error');
                    btn.prop('disabled', false).text('Launch Project');
                }
            } catch(e) { console.error(res); }
        });
    });
</script>