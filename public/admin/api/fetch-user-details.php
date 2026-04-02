<?php
/**
 * File: admin/api/fetch-user-details.php
 * Purpose: Technical deep-dive and identity verification for system administrators.
 */
session_start();
require_once('../../../config/config.php');

// 1. 🛡️ Kernel Security Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    http_response_code(403);
    die("<div class='p-12 text-center'><i data-lucide='shield-alert' class='w-12 h-12 text-rose-500 mx-auto mb-4'></i><p class='text-[10px] font-black uppercase text-slate-400 tracking-[0.3em]'>Unauthorized: Admin Clearance Required</p></div>");
}

if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);

    // 🔍 Extract target profile from registry
    $query = "SELECT id, name, email, role, created_at FROM Users WHERE id = ? LIMIT 1";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        // Strategic Role & Privilege Mapping
        $role_meta = [
            'admin' => [
                'badge' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400 border-rose-200/50',
                'desc'  => 'Kernel Level / Full System Control',
                'icon'  => 'shield-check',
                'accent'=> 'text-rose-500'
            ],
            'developer' => [
                'badge' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 border-indigo-200/50',
                'desc'  => 'Write & Debug / Scoped Technical Access',
                'icon'  => 'code-2',
                'accent'=> 'text-indigo-500'
            ],
            'user' => [
                'badge' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border-emerald-200/50',
                'desc'  => 'Standard Agent / Read & Report Only',
                'icon'  => 'user',
                'accent'=> 'text-emerald-500'
            ]
        ];
        
        $current_role = strtolower($user['role']);
        $meta = $role_meta[$current_role] ?? [
            'badge' => 'bg-slate-100 text-slate-700', 
            'desc'  => 'Standard Access Tier', 
            'icon'  => 'user',
            'accent'=> 'text-slate-400'
        ];
?>
        <div class="p-8 md:p-10 animate-in fade-in slide-in-from-bottom-4 duration-500">
            <div class="flex items-center gap-6 mb-10 pb-8 border-b border-slate-100 dark:border-slate-800">
                <div class="relative group">
                    <div class="w-24 h-24 rounded-[2.5rem] bg-indigo-600 dark:bg-indigo-500 flex items-center justify-center text-white text-4xl font-black shadow-2xl shadow-indigo-200 dark:shadow-none transition-transform group-hover:scale-105 duration-500">
                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                    </div>
                    <div class="absolute -bottom-1 -right-1 w-9 h-9 rounded-2xl bg-white dark:bg-slate-900 flex items-center justify-center shadow-lg border-4 border-white dark:border-slate-900">
                        <div class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></div>
                    </div>
                </div>
                
                <div class="flex-grow">
                    <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tighter leading-none mb-2">
                        <?php echo htmlspecialchars($user['name']); ?>
                    </h2>
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="px-3 py-1 rounded-xl border text-[9px] font-black uppercase tracking-[0.15em] <?php echo $meta['badge']; ?>">
                            <?php echo htmlspecialchars($user['role']); ?>
                        </span>
                        <span class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest font-mono">
                            #UID-<?php echo str_pad($user['id'], 5, '0', STR_PAD_LEFT); ?>
                        </span>
                    </div>
                </div>
                
                <button onclick="closeUserModal()" class="self-start p-3 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-2xl transition-all text-slate-400 hover:text-rose-500">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] mb-3 ml-1">Primary Communication</label>
                        <div class="flex items-center gap-4 p-5 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-inner group">
                            <div class="w-10 h-10 bg-white dark:bg-slate-700 rounded-xl text-emerald-500 flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                                <i data-lucide="mail" class="w-4 h-4"></i>
                            </div>
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-200 truncate">
                                <?php echo htmlspecialchars($user['email']); ?>
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] mb-3 ml-1">Authority Description</label>
                        <div class="flex items-center gap-4 p-5 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-inner group">
                            <div class="w-10 h-10 bg-white dark:bg-slate-700 rounded-xl <?php echo $meta['accent']; ?> flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                                <i data-lucide="<?php echo $meta['icon']; ?>" class="w-4 h-4"></i>
                            </div>
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-200"><?php echo $meta['desc']; ?></span>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <label class="block text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] mb-3 ml-1">Account Lifecycle</label>
                    <div class="bg-slate-900 dark:bg-indigo-950/20 rounded-[2.5rem] p-8 text-white border border-transparent dark:border-indigo-500/20 shadow-2xl relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-4 opacity-10">
                            <i data-lucide="database" class="w-20 h-20"></i>
                        </div>
                        
                        <div class="relative space-y-5">
                            <div class="flex justify-between items-center border-b border-white/10 pb-4">
                                <span class="text-[9px] font-black uppercase tracking-[0.2em] opacity-50">Onboarding Date</span>
                                <span class="text-xs font-bold font-mono"><?php echo date('d M Y', strtotime($user['created_at'])); ?></span>
                            </div>
                            <div class="flex justify-between items-center border-b border-white/10 pb-4">
                                <span class="text-[9px] font-black uppercase tracking-[0.2em] opacity-50">Security State</span>
                                <span class="flex items-center gap-2 text-[10px] font-black text-emerald-400 uppercase tracking-widest">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.6)]"></span>
                                    Verified Active
                                </span>
                            </div>
                            <div class="flex justify-between items-center pt-2">
                                <span class="text-[9px] font-black uppercase tracking-[0.2em] opacity-50">Registry Sync</span>
                                <span class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">Successful</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-12 flex flex-col sm:flex-row gap-4">
                <button onclick="editUser(<?php echo $user['id']; ?>)" class="flex-grow bg-slate-900 dark:bg-white dark:text-slate-900 text-white font-black py-5 rounded-[1.5rem] hover:bg-indigo-600 dark:hover:bg-indigo-500 dark:hover:text-white transition-all flex items-center justify-center gap-3 text-[10px] uppercase tracking-[0.2em] shadow-xl shadow-indigo-600/10 active:scale-95">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                    Override Identity Parameters
                </button>
                <button onclick="closeUserModal()" class="px-10 py-5 bg-slate-100 dark:bg-slate-800 text-slate-500 font-black rounded-[1.5rem] hover:bg-slate-200 dark:hover:bg-slate-700 transition-all text-[10px] uppercase tracking-widest active:scale-95">
                    Close Registry
                </button>
            </div>
        </div>

        <script>
            // Ensure Lucide icons are hydrated after dynamic content injection
            if(window.lucide) lucide.createIcons();
        </script>
<?php
    } else {
        echo "
        <div class='p-20 text-center flex flex-col items-center animate-pulse'>
            <div class='w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-2xl flex items-center justify-center text-slate-300 mb-4'>
                <i data-lucide='database-zap' class='w-8 h-8'></i>
            </div>
            <p class='text-[10px] font-black uppercase text-slate-400 tracking-[0.3em]'>Identity Record Missing from Matrix</p>
        </div>";
    }
}
$stmt->close();
?>