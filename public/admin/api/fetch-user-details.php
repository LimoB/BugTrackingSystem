<?php
session_start();
include('../../../config/config.php');

// 🛡️ Security Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    http_response_code(403);
    die("<div class='p-6 text-rose-500 font-bold text-center uppercase tracking-widest'>Unauthorized Access</div>");
}

if (isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);

    $query = "SELECT * FROM Users WHERE id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        // Role UI Mapping
        $role_meta = [
            'admin' => [
                'class' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
                'desc' => 'Full System Access',
                'icon' => 'shield-check'
            ],
            'developer' => [
                'class' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
                'desc' => 'Write & Debug Access',
                'icon' => 'code-2'
            ],
            'user' => [
                'class' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                'desc' => 'Read & Report Only',
                'icon' => 'user'
            ]
        ];
        
        $current_role = strtolower($user['role']);
        $meta = $role_meta[$current_role] ?? ['class' => 'bg-slate-100 text-slate-700', 'desc' => 'Standard Access', 'icon' => 'user'];
?>
        <div class="animate-fade-in">
            <div class="flex items-center gap-6 mb-8 pb-6 border-b border-slate-100 dark:border-slate-800">
                <div class="relative">
                    <div class="w-24 h-24 rounded-[2.5rem] bg-indigo-600 dark:bg-indigo-500 flex items-center justify-center text-white text-4xl font-black shadow-2xl shadow-indigo-100 dark:shadow-none">
                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                    </div>
                    <div class="absolute -bottom-1 -right-1 w-8 h-8 rounded-full bg-white dark:bg-slate-900 flex items-center justify-center shadow-lg border-4 border-white dark:border-slate-900">
                        <div class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></div>
                    </div>
                </div>
                
                <div class="flex-grow">
                    <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight leading-none mb-2">
                        <?php echo htmlspecialchars($user['name']); ?>
                    </h2>
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest <?php echo $meta['class']; ?>">
                            <?php echo htmlspecialchars($user['role']); ?>
                        </span>
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            #UID-<?php echo str_pad($user['id'], 4, '0', STR_PAD_LEFT); ?>
                        </span>
                    </div>
                </div>
                
                <button onclick="closeUserModal()" class="self-start p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-2xl transition-all text-slate-400">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-3">Primary Identity</label>
                        <div class="flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-800">
                            <div class="p-2 bg-white dark:bg-slate-700 rounded-lg text-emerald-500 shadow-sm">
                                <i data-lucide="mail" class="w-4 h-4"></i>
                            </div>
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-200"><?php echo htmlspecialchars($user['email']); ?></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-3">Privilege Level</label>
                        <div class="flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-800">
                            <div class="p-2 bg-white dark:bg-slate-700 rounded-lg text-indigo-500 shadow-sm">
                                <i data-lucide="<?php echo $meta['icon']; ?>" class="w-4 h-4"></i>
                            </div>
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-200"><?php echo $meta['desc']; ?></span>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-3">Account Lifecycle</label>
                    <div class="bg-slate-900 dark:bg-indigo-900/20 rounded-[2rem] p-6 text-white dark:text-indigo-100 border border-transparent dark:border-indigo-500/20">
                        <div class="space-y-4">
                            <div class="flex justify-between items-center border-b border-white/10 dark:border-indigo-500/10 pb-3">
                                <span class="text-[10px] font-black uppercase tracking-tighter opacity-60">Registration</span>
                                <span class="text-xs font-bold"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-[10px] font-black uppercase tracking-tighter opacity-60">Account Status</span>
                                <span class="text-xs font-bold text-emerald-400">ACTIVE</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-12 flex flex-col sm:flex-row gap-3">
                <button onclick="editUser(<?php echo $user['id']; ?>)" class="flex-grow bg-slate-900 dark:bg-white dark:text-slate-900 text-white font-black py-4 rounded-2xl hover:bg-indigo-600 dark:hover:bg-indigo-500 dark:hover:text-white transition-all flex items-center justify-center gap-2 text-xs uppercase tracking-widest shadow-xl shadow-slate-200 dark:shadow-none">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                    Modify Permissions
                </button>
                <button onclick="closeUserModal()" class="px-10 py-4 bg-slate-100 dark:bg-slate-800 text-slate-500 font-bold rounded-2xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-all text-xs uppercase tracking-widest">
                    Dismiss
                </button>
            </div>
        </div>

        <script>
            lucide.createIcons();
        </script>
<?php
    } else {
        echo "<div class='p-12 text-center text-slate-400 font-bold italic uppercase tracking-widest'>Identity record missing from central registry.</div>";
    }
}
$stmt->close();
?>