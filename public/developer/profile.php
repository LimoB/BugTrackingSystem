<?php
session_start();
include('../../config/config.php');

if (!isset($_SESSION['user_id'])) die("Access Denied.");

$user_id = (int)$_SESSION['user_id'];
$query = "SELECT name, email, role, created_at FROM Users WHERE id = $user_id";
$res = mysqli_query($connection, $query);
$user = mysqli_fetch_assoc($res);
?>

<div class="max-w-5xl mx-auto animate-in fade-in slide-in-from-bottom-8 duration-700">
    <div class="mb-12">
        <div class="flex items-center gap-3 mb-2">
            <i data-lucide="settings-2" class="w-5 h-5 text-blue-600"></i>
            <span class="text-[10px] font-black uppercase tracking-[0.4em] text-slate-400">Environment Configuration</span>
        </div>
        <h1 class="text-4xl font-black text-slate-900 dark:text-white uppercase tracking-tighter">
            Developer <span class="text-blue-600">Identity.</span>
        </h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
        <div class="lg:col-span-4 space-y-6">
            <div class="bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 p-8 rounded-[2.5rem] shadow-sm">
                <div class="relative w-28 h-28 mx-auto mb-6">
                    <div class="w-full h-full bg-blue-600 rounded-[2rem] flex items-center justify-center text-4xl font-black text-white shadow-2xl shadow-blue-500/40 rotate-3">
                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                    </div>
                    <div class="absolute -bottom-2 -right-2 bg-emerald-500 w-8 h-8 rounded-full border-4 border-white dark:border-slate-800 pulse-green"></div>
                </div>
                
                <div class="text-center space-y-1">
                    <h3 class="text-xl font-black text-slate-900 dark:text-white"><?php echo htmlspecialchars($user['name']); ?></h3>
                    <p class="text-xs font-mono text-blue-500 font-bold tracking-widest uppercase">Level: Senior Dev</p>
                </div>

                <div class="mt-10 pt-10 border-t border-slate-200 dark:border-slate-700 space-y-6">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Access Node</span>
                        <span class="text-[10px] font-mono font-bold text-slate-600 dark:text-slate-300">127.0.0.1 (Kali)</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Auth Role</span>
                        <span class="px-2 py-0.5 bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded text-[9px] font-black uppercase">Root Dev</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-8">
            <div class="bg-white dark:bg-slate-900 rounded-[3rem] border border-slate-200 dark:border-slate-800 shadow-xl overflow-hidden">
                <div class="p-8 md:p-12">
                    <div class="flex items-center gap-4 mb-10">
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-2xl">
                            <i data-lucide="key-round" class="w-6 h-6 text-blue-600"></i>
                        </div>
                        <div>
                            <h4 class="font-black text-slate-900 dark:text-white uppercase tracking-tight">Security Credentials</h4>
                            <p class="text-xs text-slate-500 font-medium italic">Synchronize your access tokens and passwords.</p>
                        </div>
                    </div>

                    <form id="updateDevPasswordForm" class="space-y-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-3">
                                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Current Auth Key</label>
                                <input type="password" name="current_password" required
                                       class="w-full px-6 py-5 bg-slate-50 dark:bg-slate-800/50 border-2 border-transparent focus:border-blue-600/20 rounded-2xl outline-none font-bold text-sm transition-all focus:bg-white dark:focus:bg-slate-800">
                            </div>
                            <div class="space-y-3">
                                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">New Dev Secret</label>
                                <input type="password" name="new_password" required
                                       class="w-full px-6 py-5 bg-slate-50 dark:bg-slate-800/50 border-2 border-transparent focus:border-blue-600/20 rounded-2xl outline-none font-bold text-sm transition-all focus:bg-white dark:focus:bg-slate-800">
                            </div>
                        </div>

                        <div class="p-6 bg-amber-50 dark:bg-amber-900/10 rounded-2xl border border-amber-100 dark:border-amber-900/30 flex items-start gap-4">
                            <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-500 mt-1"></i>
                            <p class="text-[10px] font-bold text-amber-700 dark:text-amber-400 leading-relaxed uppercase tracking-wider">
                                CAUTION: Updating credentials will invalidate all existing session tokens. You may need to re-authenticate across your environment after syncing.
                            </p>
                        </div>

                        <div class="flex justify-end pt-4">
                            <button type="submit" class="group px-12 py-5 bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-2xl font-black uppercase text-[10px] tracking-[0.3em] hover:bg-blue-600 dark:hover:bg-blue-500 hover:text-white transition-all shadow-xl flex items-center gap-4">
                                <i data-lucide="refresh-ccw" class="w-4 h-4 group-hover:rotate-180 transition-transform duration-700"></i>
                                Sync Profile Logs
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    if (window.lucide) { lucide.createIcons(); }

    $('#updateDevPasswordForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button');
        const original = $btn.html();
        
        $btn.prop('disabled', true).html('<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> SYNCING...');
        if (window.lucide) { lucide.createIcons(); }

        $.ajax({
            url: '../user/update-profile-action.php', // Note: Pointing to the shared action file
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if(res.success) {
                    Swal.fire({ 
                        icon: 'success', 
                        title: 'SYNC COMPLETE', 
                        text: res.message, 
                        background: '#0f172a', 
                        color: '#fff',
                        confirmButtonColor: '#2563eb'
                    });
                    $('#updateDevPasswordForm')[0].reset();
                } else {
                    Swal.fire({ icon: 'error', title: 'AUTH FAILURE', text: res.message, background: '#0f172a', color: '#fff' });
                }
                $btn.prop('disabled', false).html(original);
                if (window.lucide) { lucide.createIcons(); }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'NODE ERROR', text: 'Connection to update-action timed out.' });
                $btn.prop('disabled', false).html(original);
                if (window.lucide) { lucide.createIcons(); }
            }
        });
    });
</script>