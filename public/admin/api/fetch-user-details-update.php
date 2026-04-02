<?php
/**
 * File: admin/api/fetch-user-details-update.php
 * Purpose: Secure Administrative Interface for User Metadata & Permission Tuning.
 */
session_start();
require_once('../../../config/config.php');

// 1. 🛡️ Kernel Security Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    http_response_code(403);
    die("<div class='p-12 text-center'><i data-lucide='shield-alert' class='w-12 h-12 text-rose-500 mx-auto mb-4'></i><p class='text-xs font-black uppercase text-slate-400 tracking-[0.3em]'>Unauthorized: Admin Clearance Required</p></div>");
}

$userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$currentAdminId = $_SESSION['user_id'] ?? 0;

if ($userId > 0) {
    // 🔍 Target Extraction
    $query = "SELECT id, name, email, role FROM Users WHERE id = ? LIMIT 1";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        $is_self = ($userId === (int)$currentAdminId);
        ?>
        <div class="p-8 md:p-10 animate-in fade-in zoom-in-95 duration-500">
            <div class="flex items-center gap-5 mb-10 border-b border-slate-100 dark:border-slate-800 pb-8">
                <div class="w-16 h-16 rounded-[1.5rem] bg-indigo-600 text-white flex items-center justify-center text-2xl font-black shadow-xl shadow-indigo-600/20 uppercase tracking-tighter">
                    <?php echo substr($user['name'], 0, 1); ?>
                </div>
                <div>
                    <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tighter">Tune Permissions</h2>
                    <p class="text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] mt-1">
                        Node Identity: <span class="text-indigo-500">#<?php echo str_pad($user['id'], 4, '0', STR_PAD_LEFT); ?></span>
                        <?php if($is_self): ?> <span class="ml-2 px-2 py-0.5 bg-slate-900 text-white dark:bg-white dark:text-slate-900 rounded text-[8px]">Active Session</span> <?php endif; ?>
                    </p>
                </div>
            </div>

            <form id="updateUserForm" class="grid grid-cols-1 gap-6">
                <input type="hidden" name="target_user_id" value="<?php echo $user['id']; ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-[9px] font-black uppercase tracking-[0.2em] text-indigo-500 ml-1">Full Legal Name</label>
                        <div class="relative group">
                             <i data-lucide="user" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-300 group-focus-within:text-indigo-500 transition-colors"></i>
                             <input type="text" name="updateName" value="<?php echo htmlspecialchars($user['name']); ?>" required 
                               class="w-full pl-12 pr-5 py-4 bg-slate-50 dark:bg-slate-800/50 border-2 border-transparent focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 rounded-2xl font-bold text-sm text-slate-700 dark:text-slate-200 outline-none transition-all shadow-inner">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[9px] font-black uppercase tracking-[0.2em] text-indigo-500 ml-1">Verified Email</label>
                        <div class="relative group">
                             <i data-lucide="mail" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-300 group-focus-within:text-indigo-500 transition-colors"></i>
                             <input type="email" name="updateEmail" value="<?php echo htmlspecialchars($user['email']); ?>" required 
                               class="w-full pl-12 pr-5 py-4 bg-slate-50 dark:bg-slate-800/50 border-2 border-transparent focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 rounded-2xl font-bold text-sm text-slate-700 dark:text-slate-200 outline-none transition-all shadow-inner">
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-[9px] font-black uppercase tracking-[0.2em] text-indigo-500 ml-1">Authority Clearance Level</label>
                    <?php if ($is_self): ?>
                        <div class="p-5 bg-slate-100 dark:bg-slate-800 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700">
                            <div class="flex items-center gap-3 text-slate-500">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                                <span class="text-[11px] font-bold">Self-modification of Authority Tiers is restricted to prevent lockout.</span>
                            </div>
                            <input type="hidden" name="updateRole" value="admin">
                        </div>
                    <?php else: ?>
                        <div class="relative group">
                            <i data-lucide="shield" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-300 group-focus-within:text-indigo-500 transition-colors"></i>
                            <select name="updateRole" class="w-full pl-12 pr-10 py-4 bg-slate-50 dark:bg-slate-800/50 border-2 border-transparent focus:border-indigo-500 rounded-2xl font-bold text-sm text-slate-700 dark:text-slate-200 outline-none cursor-pointer appearance-none transition-all shadow-inner">
                                <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>Standard Agent (Limited Access)</option>
                                <option value="developer" <?php echo $user['role'] == 'developer' ? 'selected' : ''; ?>>Developer (Technical Clearance)</option>
                                <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>System Admin (Kernel Clearance)</option>
                            </select>
                            <i data-lucide="chevron-down" class="absolute right-5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="p-5 bg-amber-50 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900/30 rounded-2xl flex gap-4">
                    <div class="w-10 h-10 rounded-xl bg-white dark:bg-slate-900 flex items-center justify-center text-amber-600 shadow-sm shrink-0">
                        <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    </div>
                    <p class="text-[11px] text-amber-700 dark:text-amber-400 font-bold leading-relaxed">
                        Security Notice: Modifying credentials will propagate to the global registry immediately. Ensure legal names match identity documents.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 pt-8">
                    <button type="submit" id="submitUpdate" class="flex-grow bg-slate-900 dark:bg-indigo-600 text-white font-black py-5 rounded-[1.5rem] hover:bg-indigo-500 transition-all flex items-center justify-center gap-3 text-[10px] uppercase tracking-[0.2em] shadow-xl shadow-indigo-600/10 active:scale-95">
                        <i data-lucide="refresh-cw" id="updateIcon" class="w-4 h-4"></i>
                        Commit Synchronization
                    </button>
                    <button type="button" onclick="closeUserModal()" class="px-10 py-5 bg-slate-100 dark:bg-slate-800 text-slate-500 font-black rounded-[1.5rem] hover:bg-slate-200 transition-all text-[10px] uppercase tracking-widest active:scale-95">
                        Cancel
                    </button>
                </div>
            </form>
        </div>

        <script>
            // 🌀 Refresh Icons
            if(window.lucide) lucide.createIcons();

            // ⚡ AJAX Identity Sync Engine
            $(document).off('submit', '#updateUserForm').on('submit', '#updateUserForm', function(e) {
                e.preventDefault();
                
                const form = $(this);
                const btn = $('#submitUpdate');
                const icon = $('#updateIcon');
                const originalContent = btn.html();

                // Start Visual Sync
                btn.prop('disabled', true).addClass('opacity-50');
                icon.addClass('animate-spin');

                $.ajax({
                    url: './actions/update-user-action.php',
                    method: 'POST',
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            closeUserModal();
                            Swal.fire({
                                icon: 'success',
                                title: 'Registry Updated',
                                text: res.message || 'Identity successfully synchronized with core database.',
                                timer: 2000,
                                showConfirmButton: false,
                                background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#fff',
                                color: document.documentElement.classList.contains('dark') ? '#f1f5f9' : '#0f172a'
                            });
                            
                            // Refresh logic
                            if (typeof loadPage === 'function') {
                                loadPage('manage-users');
                            } else {
                                location.reload();
                            }
                        } else {
                            Swal.fire('Update Failed', res.error || 'Identity conflict detected.', 'error');
                            btn.prop('disabled', false).removeClass('opacity-50').html(originalContent);
                            lucide.createIcons();
                        }
                    },
                    error: function() {
                        Swal.fire('Protocol Error', 'Lost communication with identity server.', 'error');
                        btn.prop('disabled', false).removeClass('opacity-50').html(originalContent);
                        lucide.createIcons();
                    }
                });
            });
        </script>
        <?php
    } else {
        echo "<div class='p-20 text-center text-slate-400 font-black uppercase tracking-widest text-xs italic animate-pulse'>Target Identity Not Located in Registry</div>";
    }
    $stmt->close();
} else {
    echo "<div class='p-20 text-center text-rose-500 font-black uppercase tracking-widest text-xs'>Invalid Reference Provided</div>";
}
?>