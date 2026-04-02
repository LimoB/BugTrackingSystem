<?php
/**
 * File: admin/api/fetch-user-details-update.php
 * Purpose: Provides the administrative interface for reconfiguring user authority and metadata.
 */
session_start();
require_once('../../config/config.php');

// 1. 🛡️ Kernel Access Check
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    die("<div class='p-12 text-center text-rose-500 font-black uppercase tracking-widest text-xs'>Access Denied: Admin Clearance Required</div>");
}

$userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($userId > 0) {
    // 🔍 Fetching Target Identity
    $query = "SELECT id, name, email, role FROM Users WHERE id = ? LIMIT 1";
    $stmt = $connection->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user) {
            $is_self = ($user['id'] == $_SESSION['user_id']);
?>
            <div class="p-10 animate-in fade-in zoom-in-95 duration-500">
                <div class="flex items-center gap-5 mb-10 border-b border-slate-100 dark:border-slate-800 pb-8">
                    <div class="w-14 h-14 bg-indigo-600 text-white rounded-[1.5rem] flex items-center justify-center shadow-xl shadow-indigo-600/20">
                        <i data-lucide="shield-check" class="w-7 h-7"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-black text-slate-900 dark:text-white tracking-tighter">Authority Override</h3>
                        <p class="text-[10px] text-slate-400 uppercase tracking-[0.2em] font-black mt-1">
                            Modifying Node: <span class="text-indigo-500">#<?php echo str_pad($user['id'], 4, '0', STR_PAD_LEFT); ?></span>
                        </p>
                    </div>
                </div>

                <form id="updateUserForm" class="space-y-6">
                    <input type="hidden" name="userId" value="<?php echo $user['id']; ?>">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Legal Name</label>
                            <div class="relative group">
                                <i data-lucide="user" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-300 group-focus-within:text-indigo-500 transition-colors"></i>
                                <input type="text" name="userName" value="<?php echo htmlspecialchars($user['name']); ?>" required
                                       class="w-full pl-12 pr-5 py-4 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 rounded-2xl outline-none font-bold text-sm transition-all text-slate-700 dark:text-slate-200 shadow-inner">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Secure Email</label>
                            <div class="relative group">
                                <i data-lucide="mail" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-300 group-focus-within:text-indigo-500 transition-colors"></i>
                                <input type="email" name="userEmail" value="<?php echo htmlspecialchars($user['email']); ?>" required
                                       class="w-full pl-12 pr-5 py-4 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 rounded-2xl outline-none font-bold text-sm transition-all text-slate-700 dark:text-slate-200 shadow-inner">
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Access Tier Clearance</label>
                        <div class="relative group">
                            <i data-lucide="key" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-300 group-focus-within:text-indigo-500 transition-colors"></i>
                            <select name="userRole" class="w-full pl-12 pr-10 py-4 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-indigo-500 rounded-2xl outline-none appearance-none font-bold text-sm cursor-pointer text-slate-700 dark:text-slate-200 transition-all shadow-inner">
                                <option value="admin" <?php echo ($user['role'] == 'admin' ? 'selected' : ''); ?>>Kernel Admin (System-Wide Control)</option>
                                <option value="developer" <?php echo ($user['role'] == 'developer' ? 'selected' : ''); ?>>Developer (Scoped Technical Access)</option>
                                <option value="user" <?php echo ($user['role'] == 'user' ? 'selected' : ''); ?>>Standard Agent (Read/Write Limited)</option>
                            </select>
                            <i data-lucide="chevron-down" class="absolute right-5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none group-hover:text-indigo-500 transition-colors"></i>
                        </div>
                        <?php if($is_self): ?>
                            <p class="text-[9px] text-rose-500 font-bold mt-2 ml-1 flex items-center gap-1 italic">
                                <i data-lucide="alert-triangle" class="w-3 h-3"></i> Note: You are modifying your own administrative privileges.
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="flex gap-3 pt-8">
                        <button type="submit" id="submitSync" class="flex-grow bg-slate-900 dark:bg-indigo-600 text-white font-black py-5 rounded-[1.5rem] hover:bg-indigo-600 transition-all flex items-center justify-center gap-3 text-[10px] uppercase tracking-[0.2em] shadow-xl shadow-indigo-600/10 active:scale-95">
                            <i data-lucide="refresh-cw" id="syncIcon" class="w-4 h-4"></i>
                            Commit Sync
                        </button>
                        <button type="button" onclick="closeUserModal()" class="px-10 py-5 bg-slate-100 dark:bg-slate-800 text-slate-500 font-black rounded-[1.5rem] hover:bg-slate-200 transition-all text-[10px] uppercase tracking-widest active:scale-95">
                            Abort
                        </button>
                    </div>
                </form>
            </div>
<?php
        } else {
            echo "<div class='p-20 text-center text-slate-400 font-black uppercase tracking-widest text-[10px] italic'>Identity Missing from Matrix</div>";
        }
        $stmt->close();
    }
}
?>

<script>
    // Initialize UI Icons
    if(window.lucide) lucide.createIcons();

    // ⚡ AJAX Identity Sync Engine
    $('#updateUserForm').off('submit').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const btn = $('#submitSync');
        const icon = $('#syncIcon');

        // Visual Feedback: Start Sync
        btn.prop('disabled', true).addClass('opacity-50');
        icon.addClass('animate-spin');

        $.ajax({
            url: './actions/update-user-action.php',
            method: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(res) {
                if(res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Registry Updated',
                        text: res.message || 'Identity parameters successfully synchronized.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    closeUserModal();
                    if(typeof loadPage === 'function') loadPage('manage-users');
                } else {
                    Swal.fire('Sync Error', res.error || 'Failed to update identity.', 'error');
                    btn.prop('disabled', false).removeClass('opacity-50');
                    icon.removeClass('animate-spin');
                }
            },
            error: function() {
                Swal.fire('Protocol Error', 'Communication with user database failed.', 'error');
                btn.prop('disabled', false).removeClass('opacity-50');
                icon.removeClass('animate-spin');
            }
        });
    });
</script>