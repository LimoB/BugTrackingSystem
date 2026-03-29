<?php
session_start();
include('../../../config/config.php');

// 🛡️ Security Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    http_response_code(403);
    die("<div class='p-6 text-rose-500 font-bold uppercase tracking-widest text-center'>Unauthorized Access</div>");
}

$userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$currentAdminId = $_SESSION['user_id'] ?? 0;

if ($userId > 0) {
    // Use the $connection variable from your config
    $query = "SELECT id, name, email, role FROM Users WHERE id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        ?>
        <div class="animate-fade-in">
            <div class="mb-8">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-16 h-16 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-2xl font-black shadow-lg shadow-indigo-100 dark:shadow-none uppercase">
                        <?php echo substr($user['name'], 0, 1); ?>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Adjust Permissions</h2>
                        <p class="text-slate-500 text-sm italic">Modifying identity: <span class="font-mono text-indigo-500 font-bold">#<?php echo str_pad($user['id'], 4, '0', STR_PAD_LEFT); ?></span></p>
                    </div>
                </div>
            </div>

            <form id="updateUserForm" class="space-y-5">
                <input type="hidden" name="target_user_id" value="<?php echo $user['id']; ?>">
                
                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Full Legal Name</label>
                    <input type="text" name="updateName" value="<?php echo htmlspecialchars($user['name']); ?>" required 
                           class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl font-bold text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 transition-all outline-none">
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Registered Email</label>
                    <input type="email" name="updateEmail" value="<?php echo htmlspecialchars($user['email']); ?>" required 
                           class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl font-bold text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 transition-all outline-none">
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">System Authority Role</label>
                    <?php if ($userId == $currentAdminId): ?>
                        <div class="p-4 bg-slate-100 dark:bg-slate-800 rounded-2xl text-[11px] font-bold text-slate-500 border border-dashed border-slate-300 dark:border-slate-700">
                            Self-modification of authority levels is restricted.
                            <input type="hidden" name="updateRole" value="admin">
                        </div>
                    <?php else: ?>
                        <select name="updateRole" class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl font-bold text-sm text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 transition-all outline-none cursor-pointer">
                            <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>Standard User</option>
                            <option value="developer" <?php echo $user['role'] == 'developer' ? 'selected' : ''; ?>>Developer</option>
                            <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>System Admin</option>
                        </select>
                    <?php endif; ?>
                </div>

                <div class="p-4 bg-amber-50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-800/30 rounded-2xl flex gap-3">
                    <i data-lucide="shield-alert" class="w-5 h-5 text-amber-600 shrink-0"></i>
                    <p class="text-[11px] text-amber-700 dark:text-amber-400 font-medium leading-relaxed">
                        Changing authority levels affects access to sensitive workstreams. This action is logged.
                    </p>
                </div>

                <div class="flex gap-3 pt-6">
                    <button type="submit" class="flex-grow bg-slate-900 dark:bg-white dark:text-slate-900 text-white font-black py-4 rounded-2xl hover:bg-indigo-600 dark:hover:bg-indigo-500 dark:hover:text-white transition-all text-xs uppercase tracking-widest shadow-xl shadow-slate-200 dark:shadow-none">
                        Save Changes
                    </button>
                    <button type="button" onclick="closeUserModal()" class="px-8 py-4 bg-slate-100 dark:bg-slate-800 text-slate-500 font-bold rounded-2xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-all text-xs uppercase tracking-widest">
                        Abort
                    </button>
                </div>
            </form>
        </div>

        <script>
            // Re-init icons for the dynamic content
            lucide.createIcons();

            // Clear previous bindings to prevent duplicate triggers
            $(document).off('submit', '#updateUserForm').on('submit', '#updateUserForm', function(e) {
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
                        if (response.success) {
                            closeUserModal();
                            Swal.fire({
                                title: 'Success',
                                text: response.message,
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            // Refresh main view
                            if (typeof loadPage === 'function') {
                                loadPage('manage-users');
                            } else {
                                location.reload();
                            }
                        } else {
                            Swal.fire('Update Failed', response.error, 'error');
                            btn.prop('disabled', false).text(originalText);
                            lucide.createIcons();
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Internal Server Error. Check connectivity.', 'error');
                        btn.prop('disabled', false).text(originalText);
                        lucide.createIcons();
                    }
                });
            });
        </script>
        <?php
    } else {
        echo "<div class='p-10 text-center text-slate-400 font-bold uppercase tracking-widest'>Identity Not Found</div>";
    }
    $stmt->close();
} else {
    echo "<div class='p-10 text-center text-rose-400 font-bold uppercase tracking-widest'>Invalid Request</div>";
}
?>