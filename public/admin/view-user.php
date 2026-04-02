<?php
/**
 * File: admin/api/fetch-user-details.php
 * Purpose: Deep-dive analytical view of a specific user identity.
 */
session_start();
require_once('../../../config/config.php');

// 1. 🛡️ Kernel Security Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    http_response_code(403);
    die("<div class='p-12 text-center'><i data-lucide='shield-alert' class='w-12 h-12 text-rose-500 mx-auto mb-4'></i><p class='text-[10px] font-black uppercase text-slate-400 tracking-[0.3em]'>Access Denied: Admin Clearance Required</p></div>");
}

$userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($userId > 0) {
    // 🔍 Extract target profile from registry
    $query = "SELECT id, name, email, role, created_at FROM Users WHERE id = ? LIMIT 1";
    $stmt = $connection->prepare($query);

    if ($stmt) {
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user) {
            // Strategic Role Styling Logic
            $role_map = [
                'admin'     => ['bg' => 'bg-rose-50', 'text' => 'text-rose-600', 'border' => 'border-rose-100', 'icon' => 'shield-check'],
                'developer' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-600', 'border' => 'border-indigo-100', 'icon' => 'code-2'],
                'user'      => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'border' => 'border-emerald-100', 'icon' => 'user']
            ];
            $style = $role_map[strtolower($user['role'])] ?? ['bg' => 'bg-slate-50', 'text' => 'text-slate-500', 'border' => 'border-slate-100', 'icon' => 'user-minus'];
?>
            <div class="p-10 animate-in fade-in slide-in-from-bottom-4 duration-500">
                <div class="flex flex-col items-center text-center mb-10 pb-10 border-b border-slate-100 dark:border-slate-800">
                    <div class="relative mb-6">
                        <div class="w-24 h-24 rounded-[2.5rem] bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-900 flex items-center justify-center shadow-inner border border-slate-100 dark:border-slate-700">
                            <span class="text-4xl font-black text-slate-300 dark:text-slate-600 tracking-tighter">
                                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                            </span>
                        </div>
                        <div class="absolute -bottom-2 -right-2 w-10 h-10 rounded-2xl <?php echo $style['bg']; ?> <?php echo $style['border']; ?> border flex items-center justify-center <?php echo $style['text']; ?> shadow-lg">
                            <i data-lucide="<?php echo $style['icon']; ?>" class="w-5 h-5"></i>
                        </div>
                    </div>
                    
                    <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tighter leading-none mb-2">
                        <?php echo htmlspecialchars($user['name']); ?>
                    </h2>
                    <span class="px-4 py-1.5 rounded-full border text-[9px] font-black uppercase tracking-[0.2em] <?php echo $style['bg']; ?> <?php echo $style['text']; ?> <?php echo $style['border']; ?>">
                        <?php echo $user['role']; ?> Access Level
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label class="text-[9px] font-black uppercase text-slate-400 tracking-[0.2em] flex items-center gap-2">
                            <i data-lucide="hash" class="w-3 h-3 text-indigo-500"></i> Registry ID
                        </label>
                        <div class="px-4 py-3 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-800">
                            <code class="text-xs font-black text-slate-700 dark:text-slate-300 tracking-widest">
                                #UID-<?php echo str_pad($user['id'], 5, '0', STR_PAD_LEFT); ?>
                            </code>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[9px] font-black uppercase text-slate-400 tracking-[0.2em] flex items-center gap-2">
                            <i data-lucide="mail" class="w-3 h-3 text-indigo-500"></i> Communication
                        </label>
                        <div class="px-4 py-3 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-800">
                            <p class="text-xs font-bold text-slate-700 dark:text-slate-300 truncate">
                                <?php echo htmlspecialchars($user['email']); ?>
                            </p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[9px] font-black uppercase text-slate-400 tracking-[0.2em] flex items-center gap-2">
                            <i data-lucide="calendar" class="w-3 h-3 text-indigo-500"></i> Onboarded Since
                        </label>
                        <div class="px-4 py-3 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-800">
                            <p class="text-xs font-bold text-slate-600 dark:text-slate-400 italic">
                                <?php echo date('F d, Y', strtotime($user['created_at'] ?? 'now')); ?>
                            </p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[9px] font-black uppercase text-slate-400 tracking-[0.2em] block">Security Status</label>
                        <div class="px-4 py-3 bg-emerald-50/30 dark:bg-emerald-900/10 rounded-2xl border border-emerald-100/50 dark:border-emerald-900/30">
                            <span class="flex items-center gap-2 text-[10px] font-black text-emerald-600 uppercase tracking-widest">
                                <span class="relative flex h-2 w-2">
                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                  <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                </span>
                                Verified Active
                            </span>
                        </div>
                    </div>
                </div>

                <div class="mt-12 flex flex-col sm:flex-row gap-4">
                    <button onclick="editUser(<?php echo $user['id']; ?>)" class="flex-grow flex items-center justify-center gap-3 px-8 py-5 bg-slate-900 dark:bg-indigo-600 text-white rounded-[1.5rem] text-[10px] font-black uppercase tracking-widest hover:bg-indigo-500 transition-all shadow-xl shadow-indigo-600/10 active:scale-95">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                        Override Permissions
                    </button>
                    <button onclick="closeUserModal()" class="px-10 py-5 bg-slate-100 dark:bg-slate-800 text-slate-500 font-black rounded-[1.5rem] hover:bg-slate-200 transition-all text-[10px] uppercase tracking-widest active:scale-95">
                        Close Registry
                    </button>
                </div>
            </div>
<?php
        } else {
            echo "<div class='p-20 text-center'><i data-lucide='database-zap' class='w-12 h-12 text-slate-300 mx-auto mb-4'></i><p class='font-black text-slate-400 uppercase tracking-[0.3em] text-[10px] italic'>Identity Missing from Matrix</p></div>";
        }
        $stmt->close();
    }
}
?>

<script>
    if(window.lucide) lucide.createIcons();
</script>