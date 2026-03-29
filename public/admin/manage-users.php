<?php
session_start();
include('../../config/config.php');

// ✅ Admin Security Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    die("<div class='p-6 text-red-500 font-bold text-center uppercase tracking-widest'>Unauthorized Access</div>");
}

// Fetch all users with sorted priority (Admins first)
$query = "SELECT * FROM Users ORDER BY CASE WHEN role = 'admin' THEN 1 WHEN role = 'developer' THEN 2 ELSE 3 END, name ASC";
$result = mysqli_query($connection, $query);
?>

<div class="animate-fade-in space-y-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">User Directory</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm italic">Manage system access levels and security credentials.</p>
        </div>
        <button onclick="openUserModal('create')" class="inline-flex items-center gap-2 px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-sm font-black transition-all shadow-lg shadow-emerald-100 dark:shadow-none hover:scale-[1.02] active:scale-95">
            <i data-lucide="user-plus" class="w-5 h-5"></i>
            Onboard User
        </button>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[2.5rem] overflow-hidden shadow-sm">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Identity</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Email Address</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Access Level</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <?php while ($user = mysqli_fetch_assoc($result)): 
                    $role_badge = [
                        'admin'     => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
                        'developer' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
                        'user'      => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                    ][strtolower($user['role'])] ?? 'bg-slate-100 text-slate-700';
                ?>
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-all group" id="user-row-<?php echo $user['id']; ?>">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center font-black text-slate-400 group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-inner uppercase">
                                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                            </div>
                            <div>
                                <div class="font-bold text-slate-900 dark:text-white leading-tight"><?php echo htmlspecialchars($user['name']); ?></div>
                                <div class="text-[10px] text-slate-400 font-mono tracking-tighter">UID: #<?php echo str_pad($user['id'], 4, '0', STR_PAD_LEFT); ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-medium text-slate-600 dark:text-slate-400 underline decoration-slate-200 dark:decoration-slate-700 underline-offset-4 decoration-2">
                            <?php echo htmlspecialchars($user['email']); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter <?php echo $role_badge; ?>">
                            <?php echo $user['role']; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right space-x-1">
                        <button onclick="viewUser(<?php echo $user['id']; ?>)" class="p-2 hover:bg-blue-50 dark:hover:bg-blue-900/20 text-slate-400 hover:text-blue-600 rounded-lg transition-all" title="View Profile">
                            <i data-lucide="info" class="w-4 h-4"></i>
                        </button>
                        <button onclick="editUser(<?php echo $user['id']; ?>)" class="p-2 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 text-slate-400 hover:text-emerald-600 rounded-lg transition-all" title="Edit Permissions">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                        </button>
                        <button onclick="deleteUser(<?php echo $user['id']; ?>)" class="p-2 hover:bg-rose-50 dark:hover:bg-rose-900/20 text-slate-400 hover:text-rose-600 rounded-lg transition-all" title="Revoke Access">
                            <i data-lucide="user-minus" class="w-4 h-4"></i>
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="userModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeUserModal()"></div>
    <div class="relative w-full max-w-lg transform transition-all">
        <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div id="userModalBody" class="p-8">
                </div>
        </div>
    </div>
</div>

<script>
    // Initialize Interface Icons
    lucide.createIcons();

    // 🛠 MODAL CONTROLLER
    function openUserModal(type, id = null) {
        const body = $('#userModalBody');
        $('#userModal').removeClass('hidden').addClass('flex');

        if (type === 'create') {
            body.html(`
                <div class="mb-6">
                    <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Onboard New User</h2>
                    <p class="text-slate-500 text-sm italic">Generate system credentials and assign initial scope.</p>
                </div>
                <form id="createUserForm" class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Full Name</label>
                        <input type="text" name="userName" placeholder="John Doe" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl font-bold focus:ring-2 focus:ring-emerald-500 transition-all outline-none">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Email Address</label>
                        <input type="email" name="userEmail" placeholder="john@company.com" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl focus:ring-2 focus:ring-emerald-500 transition-all outline-none">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Initial Password</label>
                        <input type="password" name="userPassword" placeholder="••••••••" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl focus:ring-2 focus:ring-emerald-500 transition-all outline-none">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Authority Level</label>
                        <select name="userRole" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl font-bold focus:ring-2 focus:ring-emerald-500 transition-all cursor-pointer outline-none">
                            <option value="user">Standard User</option>
                            <option value="developer" selected>Developer</option>
                            <option value="admin">System Admin</option>
                        </select>
                    </div>
                    <div class="flex gap-2 pt-6">
                        <button type="submit" class="flex-grow bg-emerald-600 text-white font-black py-4 rounded-2xl hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-100 dark:shadow-none uppercase text-xs tracking-widest">Deploy Account</button>
                        <button type="button" onclick="closeUserModal()" class="px-6 py-4 bg-slate-100 dark:bg-slate-800 text-slate-500 font-bold rounded-2xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-all text-xs uppercase tracking-widest">Cancel</button>
                    </div>
                </form>
            `);
            lucide.createIcons();
        }
    }

    function closeUserModal() { 
        $('#userModal').addClass('hidden').removeClass('flex'); 
    }

    function viewUser(id) {
        $('#userModal').removeClass('hidden').addClass('flex');
        $('#userModalBody').html(`
            <div class="flex flex-col items-center justify-center p-12 text-slate-400">
                <div class="animate-spin mb-4"><i data-lucide="loader-2" class="w-8 h-8"></i></div>
                <p class="font-bold text-xs uppercase tracking-widest">Accessing Profile...</p>
            </div>
        `);
        lucide.createIcons();
        $.get('./api/fetch-user-details.php', { user_id: id }, function(data) {
            $('#userModalBody').html(data);
            lucide.createIcons();
        });
    }

    function editUser(id) {
        $('#userModal').removeClass('hidden').addClass('flex');
        $('#userModalBody').html(`
            <div class="flex flex-col items-center justify-center p-12 text-indigo-500">
                <div class="animate-pulse mb-4"><i data-lucide="shield-check" class="w-8 h-8"></i></div>
                <p class="font-bold text-xs uppercase tracking-widest">Fetching Permissions...</p>
            </div>
        `);
        lucide.createIcons();
        $.get('./api/fetch-user-details-update.php', { user_id: id }, function(data) {
            $('#userModalBody').html(data);
            lucide.createIcons();
        });
    }

    // ⚡ AJAX FORM HANDLERS (Event Delegation)

    // Handle User Creation
    $(document).on('submit', '#createUserForm', function(e) {
        e.preventDefault();
        const btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<i data-lucide="loader-2" class="w-5 h-5 animate-spin mx-auto"></i>');
        
        $.post('./actions/create-user-action.php', $(this).serialize(), function(response) {
            closeUserModal();
            Swal.fire('Identity Verified', 'New user has been established.', 'success');
            loadPage('manage-users');
        });
    });

    // Handle User Update (This fixes your "Save Changes" issue)
    $(document).on('submit', '#updateUserForm', function(e) {
        e.preventDefault();
        const form = $(this);
        const btn = form.find('button[type="submit"]');
        const originalText = btn.text();

        btn.prop('disabled', true).html('<i data-lucide="loader-2" class="w-4 h-4 animate-spin mx-auto"></i>');
        lucide.createIcons();

        $.ajax({
            url: './actions/update-user-action.php',
            method: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    closeUserModal();
                    Swal.fire('Success', response.message, 'success');
                    loadPage('manage-users');
                } else {
                    Swal.fire('Error', response.error, 'error');
                    btn.prop('disabled', false).text(originalText);
                }
            },
            error: function() {
                Swal.fire('System Error', 'Could not reach server action.', 'error');
                btn.prop('disabled', false).text(originalText);
            }
        });
    });

    function deleteUser(id) {
        Swal.fire({
            title: 'Revoke Access?',
            text: "This user will be permanently removed from the system.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            confirmButtonText: 'Yes, Terminate',
            background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#fff',
            color: document.documentElement.classList.contains('dark') ? '#f1f5f9' : '#0f172a'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('./actions/delete-user-action.php', { user_id: id }, function(res) {
                    $(`#user-row-${id}`).addClass('scale-95 opacity-0 transition-all duration-500');
                    setTimeout(() => $(`#user-row-${id}`).remove(), 500);
                    Swal.fire('Revoked!', 'User access has been terminated.', 'success');
                });
            }
        });
    }

    $(document).keyup(function(e) { if (e.key === "Escape") closeUserModal(); });
</script>