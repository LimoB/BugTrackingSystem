<?php
session_start();
include('../../../config/config.php');

// 🛡️ Security Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    die("<div class='p-6 text-rose-500 font-bold'>Unauthorized Access</div>");
}

$userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($userId > 0) {
    $query = "SELECT id, name, email, role, created_at FROM Users WHERE id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        // Determine if we are in "Edit Mode" based on a secondary flag or just default to the form
        ?>
        <div class="mb-8">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-16 h-16 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-2xl font-black shadow-lg shadow-indigo-200 dark:shadow-none">
                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                </div>
                <div>
                    <h2 class="text-2xl font-black text-slate-900 dark:text-white">Adjust Permissions</h2>
                    <p class="text-slate-500 text-sm">Modifying identity: <span class="font-mono text-indigo-500">#<?php echo str_pad($user['id'], 4, '0', STR_PAD_LEFT); ?></span></p>
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
                <select name="updateRole" class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl font-bold text-sm text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 transition-all outline-none cursor-pointer">
                    <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>Standard User</option>
                    <option value="developer" <?php echo $user['role'] == 'developer' ? 'selected' : ''; ?>>Developer</option>
                    <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>System Admin</option>
                </select>
            </div>

            <div class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800/50 rounded-2xl flex gap-3">
                <i data-lucide="shield-alert" class="w-5 h-5 text-amber-600 shrink-0"></i>
                <p class="text-[11px] text-amber-700 dark:text-amber-400 font-medium leading-relaxed">
                    Changing authority levels affects access to sensitive workstreams and administrative tools. Proceed with caution.
                </p>
            </div>

            <div class="flex gap-3 pt-6">
                <button type="submit" class="flex-grow bg-slate-900 dark:bg-white dark:text-slate-900 text-white font-black py-4 rounded-2xl hover:bg-indigo-600 dark:hover:bg-indigo-500 dark:hover:text-white transition-all text-xs uppercase tracking-widest shadow-xl shadow-slate-200 dark:shadow-none">Save Changes</button>
                <button type="button" onclick="closeUserModal()" class="px-8 py-4 bg-slate-100 dark:bg-slate-800 text-slate-500 font-bold rounded-2xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-all text-xs uppercase tracking-widest">Abort</button>
            </div>
        </form>
        <?php
    } else {
        echo "<div class='p-10 text-center text-slate-400 font-bold'>Error: User signature not found in registry.</div>";
    }
    $stmt->close();
}
?>