<?php
session_start();
include('../../config/config.php');

if (!isset($_SESSION['user_id'])) die("Unauthorized.");

$user_id = (int)$_SESSION['user_id'];
$query = "SELECT name, email, role, created_at FROM Users WHERE id = $user_id";
$res = mysqli_query($connection, $query);
$user = mysqli_fetch_assoc($res);
?>

<div class="max-w-4xl mx-auto animate-in fade-in slide-in-from-bottom-8 duration-700">
    <div class="mb-10">
        <h1 class="text-4xl font-black text-slate-900 dark:text-white uppercase tracking-tighter">
            Account <span class="text-blue-600">Profile.</span>
        </h1>
        <p class="text-slate-500 font-medium italic mt-1">Manage your identity and security settings.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="space-y-6">
            <div class="bg-white dark:bg-slate-900 p-8 rounded-[2rem] border border-slate-200 dark:border-slate-800 text-center shadow-sm">
                <div class="w-24 h-24 bg-blue-600 rounded-3xl mx-auto mb-4 flex items-center justify-center text-3xl font-black text-white shadow-xl shadow-blue-500/20">
                    <?php echo strtoupper(substr($user['name'], 0, 2)); ?>
                </div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white"><?php echo htmlspecialchars($user['name']); ?></h3>
                <p class="text-[10px] font-black text-blue-600 uppercase tracking-widest mt-1"><?php echo $user['role']; ?></p>
                
                <div class="mt-8 pt-8 border-t border-slate-100 dark:border-slate-800 space-y-4 text-left">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Email Address</p>
                        <p class="text-xs font-bold text-slate-700 dark:text-slate-300"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Member Since</p>
                        <p class="text-xs font-bold text-slate-700 dark:text-slate-300"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="md:col-span-2 space-y-8">
            <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="p-8 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20">
                    <h3 class="text-xs font-black uppercase tracking-[0.2em] text-slate-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-4 h-4 text-blue-600"></i> Security Credentials
                    </h3>
                </div>
                
                <form id="updatePasswordForm" class="p-8 space-y-6">
                    <div class="grid grid-cols-1 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Current Password</label>
                            <input type="password" name="current_password" required
                                   class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800/50 border-none rounded-xl focus:ring-2 focus:ring-blue-600 outline-none font-bold text-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">New Password</label>
                            <input type="password" name="new_password" required
                                   class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800/50 border-none rounded-xl focus:ring-2 focus:ring-blue-600 outline-none font-bold text-sm">
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full py-4 bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-xl font-black uppercase text-[10px] tracking-[0.2em] hover:bg-blue-600 dark:hover:bg-blue-500 hover:text-white transition-all shadow-lg">
                        Update Security Key
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    if (window.lucide) { lucide.createIcons(); }

    $('#updatePasswordForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button');
        $btn.prop('disabled', true).text('SYNCING...');

        $.ajax({
            url: 'update-profile-action.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if(res.success) {
                    Swal.fire({ icon: 'success', title: 'SUCCESS', text: res.message, background: '#0f172a', color: '#fff' });
                    $('#updatePasswordForm')[0].reset();
                } else {
                    Swal.fire({ icon: 'error', title: 'DENIED', text: res.message, background: '#0f172a', color: '#fff' });
                }
                $btn.prop('disabled', false).text('UPDATE SECURITY KEY');
            }
        });
    });
</script>