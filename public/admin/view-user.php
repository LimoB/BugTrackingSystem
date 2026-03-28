<?php
session_start();
include('../../config/config.php');

// ✅ Admin Security Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    die("<div class='p-6 text-red-500 font-bold'>Unauthorized Access</div>");
}

if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $userId = intval($_GET['user_id']);

    $query = "SELECT id, name, email, role, created_at FROM Users WHERE id = ?";
    $stmt = mysqli_prepare($connection, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        if ($user) {
            // Define Role Badge Colors
            $role_style = [
                'admin' => 'bg-rose-100 text-rose-700 border-rose-200',
                'developer' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                'user' => 'bg-emerald-100 text-emerald-700 border-emerald-200'
            ][strtolower($user['role'])] ?? 'bg-slate-100 text-slate-700 border-slate-200';
?>
            <div class="animate-fade-in">
                <div class="flex flex-col items-center text-center mb-8 pb-6 border-b border-slate-100 dark:border-slate-800">
                    <div class="w-20 h-20 rounded-[2rem] bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-700 flex items-center justify-center mb-4 shadow-inner">
                        <span class="text-3xl font-black text-slate-400">
                            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                        </span>
                    </div>
                    <h2 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">
                        <?php echo htmlspecialchars($user['name']); ?>
                    </h2>
                    <div class="mt-2 inline-flex items-center px-3 py-1 rounded-full border text-[10px] font-black uppercase tracking-widest <?php echo $role_style; ?>">
                        <?php echo $user['role']; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest">System Identifier</label>
                        <p class="font-mono text-sm text-slate-600 dark:text-slate-300">#UID-<?php echo str_pad($user['id'], 5, '0', STR_PAD_LEFT); ?></p>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Primary Contact</label>
                        <p class="text-sm font-bold text-slate-600 dark:text-slate-300"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Onboarding Date</label>
                        <p class="text-sm font-medium text-slate-500 italic">
                            <?php echo date('F d, Y', strtotime($user['created_at'] ?? 'now')); ?>
                        </p>
                    </div>

                    <div class="space-y-1 text-right">
                        <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest block">Account Status</label>
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-500">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            Active Profile
                        </span>
                    </div>
                </div>

                <div class="mt-10 flex gap-3">
                    <button onclick="editUser(<?php echo $user['id']; ?>)" class="flex-grow flex items-center justify-center gap-2 px-4 py-4 bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-indigo-600 dark:hover:bg-indigo-500 transition-all shadow-lg">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                        Modify Access
                    </button>
                    <button onclick="closeUserModal()" class="px-8 py-4 bg-slate-100 dark:bg-slate-800 text-slate-500 font-bold rounded-2xl hover:bg-slate-200 transition-all text-xs uppercase tracking-widest">
                        Dismiss
                    </button>
                </div>
            </div>
<?php
        } else {
            echo "<div class='p-10 text-center text-slate-400 font-bold italic'>Error: Profile Not Found.</div>";
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<script>
    lucide.createIcons();
</script>