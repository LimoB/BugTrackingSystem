<?php
session_start();
include('../../config/config.php');

if (!isset($_SESSION['user_id'])) die("Session Expired.");

$user_id = (int)$_SESSION['user_id'];
$query = "SELECT name, email, role FROM Users WHERE id = $user_id";
$res = mysqli_query($connection, $query);
$user = mysqli_fetch_assoc($res);
?>

<div class="max-w-4xl mx-auto animate-in fade-in slide-in-from-bottom-4 duration-500">
    <div class="flex items-center gap-6 mb-12">
        <div class="w-20 h-20 bg-emerald-600 rounded-3xl flex items-center justify-center text-white text-3xl font-black shadow-xl shadow-emerald-200 dark:shadow-none">
            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
        </div>
        <div>
            <h1 class="text-3xl font-black text-slate-900 dark:text-white uppercase tracking-tight">System Administrator</h1>
            <p class="text-slate-500 font-medium italic">Internal ID: #<?php echo str_pad($user_id, 4, '0', STR_PAD_LEFT); ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="space-y-6">
            <div class="bg-slate-50 dark:bg-slate-900/50 p-6 rounded-2xl border border-slate-100 dark:border-slate-800">
                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Full Identity</label>
                <p class="font-bold text-slate-800 dark:text-slate-200 mt-1"><?php echo htmlspecialchars($user['name']); ?></p>
            </div>
            <div class="bg-slate-50 dark:bg-slate-900/50 p-6 rounded-2xl border border-slate-100 dark:border-slate-800">
                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Primary Mailbox</label>
                <p class="font-bold text-slate-800 dark:text-slate-200 mt-1"><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
        </div>

        <div class="md:col-span-2">
            <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="flex items-center gap-3 mb-8">
                    <i data-lucide="shield-lock" class="w-5 h-5 text-emerald-600"></i>
                    <h3 class="font-black uppercase tracking-tight text-slate-800 dark:text-white">Update Root Credentials</h3>
                </div>

                <form id="adminUpdateForm" class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Current Password</label>
                        <input type="password" name="current_password" required
                               class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-emerald-500/20 rounded-xl outline-none font-bold text-sm transition-all">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">New System Password</label>
                        <input type="password" name="new_password" required
                               class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-emerald-500/20 rounded-xl outline-none font-bold text-sm transition-all">
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full py-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-black uppercase text-[11px] tracking-[0.2em] shadow-lg shadow-emerald-200 dark:shadow-none transition-all flex items-center justify-center gap-3">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            Overwrite Credentials
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    if(window.lucide) lucide.createIcons();

    $('#adminUpdateForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button');
        $btn.prop('disabled', true).addClass('opacity-50');

        $.ajax({
            url: '../user/update-profile-action.php', // Reusing the shared logic
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if(res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'SYSTEM UPDATED',
                        text: res.message,
                        confirmButtonColor: '#059669'
                    });
                    $('#adminUpdateForm')[0].reset();
                } else {
                    Swal.fire({ icon: 'error', title: 'ACCESS DENIED', text: res.message });
                }
                $btn.prop('disabled', false).removeClass('opacity-50');
            }
        });
    });
</script>