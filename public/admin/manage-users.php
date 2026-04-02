<?php
/**
 * File: admin/pages/manage-users.php
 * Purpose: Centralized Command for User Lifecycle, Permissions, and Access Control.
 */
session_start();
require_once('../../config/config.php');

// 1. 🛡️ Administrative Security Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    die("<div class='p-12 text-center'><i data-lucide='shield-alert' class='w-12 h-12 text-rose-500 mx-auto mb-4'></i><p class='text-xs font-black uppercase text-slate-400 tracking-[0.3em]'>Unauthorized: Kernel Access Denied</p></div>");
}

// 2. Fetch Users with Strategic Priority (Admins > Developers > Users)
$query = "SELECT id, name, email, role, created_at FROM Users 
          ORDER BY CASE 
            WHEN role = 'admin' THEN 1 
            WHEN role = 'developer' THEN 2 
            ELSE 3 
          END, name ASC";
$result = $connection->query($query);
?>

<div class="animate-in fade-in slide-in-from-bottom-4 duration-700 space-y-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 px-2">
        <div>
            <h1 class="text-4xl font-black tracking-tighter text-slate-900 dark:text-white">User Directory</h1>
            <p class="text-slate-400 dark:text-slate-500 text-xs font-bold uppercase tracking-widest mt-1 italic">Security Credentials & Permission Matrix</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="relative hidden lg:block">
                <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                <input type="text" id="userSearch" placeholder="Search identity..." 
                       class="pl-12 pr-6 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl text-xs font-bold outline-none focus:ring-4 focus:ring-indigo-500/10 transition-all w-64">
            </div>
            <button onclick="openUserModal('create')" class="inline-flex items-center gap-3 px-8 py-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-[1.5rem] text-[10px] font-black uppercase tracking-widest transition-all shadow-xl shadow-indigo-600/20 active:scale-95">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                Onboard User
            </button>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-[3rem] overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="userTable">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/30 border-b border-slate-100 dark:border-slate-800">
                        <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Identity Node</th>
                        <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Communication Channel</th>
                        <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Access Level</th>
                        <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 text-right">Operational Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                    <?php while ($user = $result->fetch_assoc()): 
                        $role_badge = [
                            'admin'     => 'bg-rose-50 text-rose-600 border-rose-100 dark:bg-rose-900/20 dark:text-rose-400 dark:border-rose-800/50',
                            'developer' => 'bg-indigo-50 text-indigo-600 border-indigo-100 dark:bg-indigo-900/20 dark:text-indigo-400 dark:border-indigo-800/50',
                            'user'      => 'bg-emerald-50 text-emerald-600 border-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800/50'
                        ][strtolower($user['role'])] ?? 'bg-slate-100 text-slate-600';
                        
                        $is_self = ($user['id'] == $_SESSION['user_id']);
                    ?>
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/20 transition-all group" id="user-row-<?php echo $user['id']; ?>">
                        <td class="px-8 py-5">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center font-black text-slate-400 group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-inner uppercase text-sm">
                                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div class="font-black text-slate-900 dark:text-white leading-tight flex items-center gap-2">
                                        <?php echo htmlspecialchars($user['name']); ?>
                                        <?php if($is_self): ?>
                                            <span class="text-[8px] px-1.5 py-0.5 bg-slate-900 text-white rounded dark:bg-white dark:text-slate-900 uppercase">Self</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-[9px] text-slate-400 font-black uppercase tracking-tighter mt-1">UID: #<?php echo str_pad($user['id'], 4, '0', STR_PAD_LEFT); ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-5">
                            <span class="text-xs font-bold text-slate-600 dark:text-slate-400 flex items-center gap-2">
                                <i data-lucide="mail" class="w-3 h-3 opacity-40"></i>
                                <?php echo htmlspecialchars($user['email']); ?>
                            </span>
                        </td>
                        <td class="px-8 py-5">
                            <span class="px-3 py-1.5 rounded-xl border text-[9px] font-black uppercase tracking-widest <?php echo $role_badge; ?>">
                                <?php echo $user['role']; ?>
                            </span>
                        </td>
                        <td class="px-8 py-5 text-right space-x-1">
                            <button onclick="viewUser(<?php echo $user['id']; ?>)" class="p-3 hover:bg-white dark:hover:bg-slate-700 text-slate-400 hover:text-indigo-600 rounded-xl transition-all shadow-sm border border-transparent hover:border-slate-100" title="View Profile">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                            <button onclick="editUser(<?php echo $user['id']; ?>)" class="p-3 hover:bg-white dark:hover:bg-slate-700 text-slate-400 hover:text-amber-600 rounded-xl transition-all shadow-sm border border-transparent hover:border-slate-100" title="Modify Permissions">
                                <i data-lucide="shield-check" class="w-4 h-4"></i>
                            </button>
                            <?php if(!$is_self): ?>
                            <button onclick="deleteUser(<?php echo $user['id']; ?>)" class="p-3 hover:bg-rose-50 dark:hover:bg-rose-900/20 text-slate-400 hover:text-rose-600 rounded-xl transition-all shadow-sm" title="Revoke Access">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="userModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-md transition-opacity" onclick="closeUserModal()"></div>
    <div class="relative w-full max-w-xl transform transition-all animate-in zoom-in-95 duration-300">
        <div class="bg-white dark:bg-slate-900 rounded-[3rem] shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div id="userModalBody">
                </div>
        </div>
    </div>
</div>

<script>
    // 1. Initialize Icons
    if(window.lucide) lucide.createIcons();

    // 2. Search Engine: Real-time Registry Filter
    $('#userSearch').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $("#userTable tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // 3. Modal Controller Logic
    function openUserModal(type, id = null) {
        const body = $('#userModalBody');
        $('#userModal').removeClass('hidden').addClass('flex');

        if (type === 'create') {
            body.html(`
                <div class="p-10">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-14 h-14 bg-indigo-600 text-white rounded-[1.5rem] flex items-center justify-center shadow-lg shadow-indigo-600/20">
                            <i data-lucide="user-plus" class="w-7 h-7"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Onboard Node</h2>
                            <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest">New Identity Generation</p>
                        </div>
                    </div>

                    <form id="createUserForm" class="space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-1">
                                <label class="text-[9px] font-black uppercase tracking-[0.2em] text-indigo-500 ml-1">Full Legal Name</label>
                                <input type="text" name="userName" required class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-indigo-500 rounded-2xl font-bold text-sm outline-none transition-all">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[9px] font-black uppercase tracking-[0.2em] text-indigo-500 ml-1">Email Address</label>
                                <input type="email" name="userEmail" required class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-indigo-500 rounded-2xl font-bold text-sm outline-none transition-all">
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[9px] font-black uppercase tracking-[0.2em] text-indigo-500 ml-1">Temporary Credential (Password)</label>
                            <input type="password" name="userPassword" required class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-indigo-500 rounded-2xl font-bold text-sm outline-none transition-all">
                        </div>

                        <div class="space-y-1">
                            <label class="text-[9px] font-black uppercase tracking-[0.2em] text-indigo-500 ml-1">Authority Clearance</label>
                            <select name="userRole" class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-indigo-500 rounded-2xl font-bold text-sm outline-none cursor-pointer">
                                <option value="user">Standard Agent</option>
                                <option value="developer" selected>System Developer</option>
                                <option value="admin">Kernel Admin</option>
                            </select>
                        </div>

                        <div class="flex gap-3 pt-6">
                            <button type="submit" class="flex-grow bg-slate-900 dark:bg-indigo-600 text-white font-black py-5 rounded-[1.5rem] hover:bg-indigo-600 transition-all shadow-xl shadow-indigo-600/10 uppercase text-[10px] tracking-widest active:scale-95">Deploy Account</button>
                            <button type="button" onclick="closeUserModal()" class="px-8 py-5 bg-slate-100 dark:bg-slate-800 text-slate-500 font-black rounded-[1.5rem] hover:bg-slate-200 transition-all text-[10px] uppercase tracking-widest">Abort</button>
                        </div>
                    </form>
                </div>
            `);
            if(window.lucide) lucide.createIcons();
        }
    }

    function closeUserModal() { 
        $('#userModal').addClass('hidden').removeClass('flex'); 
    }

    function viewUser(id) {
        $('#userModal').removeClass('hidden').addClass('flex');
        $('#userModalBody').html(`<div class='p-20 text-center animate-pulse'><i data-lucide='cpu' class='w-12 h-12 text-slate-300 mx-auto mb-4'></i><p class='text-[9px] font-black uppercase tracking-widest text-slate-400'>Accessing Registry...</p></div>`);
        lucide.createIcons();
        $.get('./api/fetch-user-details.php', { user_id: id }, function(data) {
            $('#userModalBody').html(data);
            if(window.lucide) lucide.createIcons();
        });
    }

    function editUser(id) {
        $('#userModal').removeClass('hidden').addClass('flex');
        $('#userModalBody').html(`<div class='p-20 text-center animate-pulse'><i data-lucide='shield-check' class='w-12 h-12 text-indigo-500 mx-auto mb-4'></i><p class='text-[9px] font-black uppercase tracking-widest text-indigo-400'>Fetching Permissions...</p></div>`);
        lucide.createIcons();
        $.get('./api/fetch-user-details-update.php', { user_id: id }, function(data) {
            $('#userModalBody').html(data);
            if(window.lucide) lucide.createIcons();
        });
    }

    // ⚡ AJAX Event Delegation (Handles dynamically loaded forms)
    $(document).off('submit', '#createUserForm').on('submit', '#createUserForm', function(e) {
        e.preventDefault();
        const btn = $(this).find('button[type="submit"]');
        const originalText = btn.html();
        btn.prop('disabled', true).html('<i data-lucide="refresh-cw" class="w-4 h-4 animate-spin mx-auto"></i>');
        lucide.createIcons();

        $.post('./actions/create-user-action.php', $(this).serialize(), function(response) {
            if(response.success) {
                closeUserModal();
                Swal.fire('Success', 'Identity Established.', 'success');
                loadPage('manage-users');
            } else {
                Swal.fire('Error', response.error, 'error');
                btn.prop('disabled', false).html(originalText);
            }
        }, 'json');
    });

    $(document).off('submit', '#updateUserForm').on('submit', '#updateUserForm', function(e) {
        e.preventDefault();
        const btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<i data-lucide="refresh-cw" class="w-4 h-4 animate-spin mx-auto"></i>');
        lucide.createIcons();

        $.post('./actions/update-user-action.php', $(this).serialize(), function(response) {
            if(response.success) {
                closeUserModal();
                Swal.fire('Registry Updated', response.message, 'success');
                loadPage('manage-users');
            } else {
                Swal.fire('Update Failed', response.error, 'error');
                btn.prop('disabled', false).text('Commit Changes');
            }
        }, 'json');
    });

    function deleteUser(id) {
        Swal.fire({
            title: 'EXECUTE TERMINATION?',
            text: "This identity will be purged from the security matrix.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            confirmButtonText: 'CONFIRM PURGE',
            background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#fff',
            color: document.documentElement.classList.contains('dark') ? '#f1f5f9' : '#0f172a'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('./actions/delete-user-action.php', { user_id: id }, function(res) {
                    if(res.success) {
                        $(`#user-row-${id}`).addClass('scale-95 opacity-0 transition-all duration-700');
                        setTimeout(() => loadPage('manage-users'), 700);
                        Swal.fire('Purged!', res.message, 'success');
                    } else {
                        Swal.fire('Error', res.error, 'error');
                    }
                }, 'json');
            }
        });
    }

    // Keyboard Accessibility
    $(document).keyup(function(e) { if (e.key === "Escape") closeUserModal(); });
</script>