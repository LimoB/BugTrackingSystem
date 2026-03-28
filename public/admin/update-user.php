<?php
session_start();
include('../../config/config.php');

// ✅ Admin Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    die("<div class='p-6 text-red-500 font-bold'>Unauthorized: Admin Privileges Required</div>");
}

if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $userId = intval($_GET['user_id']);

    $query = "SELECT * FROM Users WHERE id = ?";
    $stmt = mysqli_prepare($connection, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        if ($user) {
?>
            <div class="animate-fade-in">
                <div class="flex items-center gap-4 mb-8 border-b border-slate-100 dark:border-slate-800 pb-5">
                    <div class="bg-indigo-600 p-3 rounded-2xl shadow-lg shadow-indigo-100 dark:shadow-none text-white">
                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-slate-900 dark:text-white">Modify Permissions</h3>
                        <p class="text-[10px] text-slate-500 uppercase tracking-widest font-bold">Adjusting Access for UID #<?php echo $user['id']; ?></p>
                    </div>
                </div>

                <form id="updateUserForm" class="space-y-5">
                    <input type="hidden" name="userId" value="<?php echo $user['id']; ?>">

                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2 ml-1">Full Legal Name</label>
                        <div class="relative">
                            <i data-lucide="user" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-300"></i>
                            <input type="text" name="userName" value="<?php echo htmlspecialchars($user['name']); ?>" required
                                   class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none font-bold text-sm transition-all text-slate-700 dark:text-slate-200">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2 ml-1">Primary Email</label>
                        <div class="relative">
                            <i data-lucide="mail" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-300"></i>
                            <input type="email" name="userEmail" value="<?php echo htmlspecialchars($user['email']); ?>" required
                                   class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none font-bold text-sm transition-all text-slate-700 dark:text-slate-200">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2 ml-1">System Access Level</label>
                        <div class="relative">
                            <select name="userRole" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 rounded-xl border-none outline-none appearance-none font-bold text-sm cursor-pointer text-slate-700 dark:text-slate-200">
                                <option value="admin" <?php echo ($user['role'] == 'admin' ? 'selected' : ''); ?>>Administrator (Full Access)</option>
                                <option value="developer" <?php echo ($user['role'] == 'developer' ? 'selected' : ''); ?>>Developer (Scoped Access)</option>
                                <option value="user" <?php echo ($user['role'] == 'user' ? 'selected' : ''); ?>>Standard User (Limited)</option>
                            </select>
                            <i data-lucide="chevron-down" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-6">
                        <button type="submit" class="flex-grow bg-slate-900 dark:bg-white dark:text-slate-900 text-white font-black py-4 rounded-2xl hover:bg-indigo-600 dark:hover:bg-indigo-500 dark:hover:text-white transition-all flex items-center justify-center gap-2 text-xs uppercase tracking-widest shadow-xl shadow-indigo-100 dark:shadow-none">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            Synchronize Profile
                        </button>
                        <button type="button" onclick="closeUserModal()" class="px-6 py-4 bg-slate-100 dark:bg-slate-800 text-slate-500 font-bold rounded-2xl hover:bg-slate-200 transition-all text-xs uppercase tracking-widest">
                            Dismiss
                        </button>
                    </div>
                </form>
            </div>
<?php
        } else {
            echo "<div class='p-10 text-center text-slate-400 italic'>Account not found.</div>";
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<script>
    lucide.createIcons();

    // 🚀 Handle AJAX Submission
    $('#updateUserForm').on('submit', function(e) {
        e.preventDefault();
        const btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).addClass('opacity-50');

        $.ajax({
            url: './actions/update-user-action.php',
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                Swal.fire({
                    icon: 'success',
                    title: 'Account Updated',
                    text: 'User credentials have been synchronized.',
                    timer: 1500,
                    showConfirmButton: false
                });
                if(typeof loadPage === 'function') loadPage('manage-users');
                closeUserModal();
            },
            error: function() {
                Swal.fire('Error', 'Failed to update user.', 'error');
                btn.prop('disabled', false).removeClass('opacity-50');
            }
        });
    });
</script>