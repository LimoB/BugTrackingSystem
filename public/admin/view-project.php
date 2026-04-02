<?php
/**
 * File: admin/api/view-project.php
 * Purpose: Deep-dive analytical view of a specific project node.
 */
session_start();
require_once('../../config/config.php');

// 1. 🛡️ Administrative Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    die("<div class='p-12 text-center text-rose-500 font-black uppercase tracking-widest text-xs'>Unauthorized: Access Revoked</div>");
}

if (isset($_GET['project_id']) && is_numeric($_GET['project_id'])) {
    $project_id = intval($_GET['project_id']);

    // Fetch project, creator, and a count of associated tickets in one hit
    $query = "SELECT p.*, u.username as creator_name,
              (SELECT COUNT(*) FROM Tickets WHERE project_id = p.id) as total_tickets,
              (SELECT COUNT(*) FROM Tickets WHERE project_id = p.id AND status = 'open') as open_tickets
              FROM Projects p 
              LEFT JOIN Users u ON p.created_by = u.id 
              WHERE p.id = ? LIMIT 1";
    
    $stmt = $connection->prepare($query);

    if ($stmt) {
        $stmt->bind_param('i', $project_id);
        $stmt->execute();
        $project = $stmt->get_result()->fetch_assoc();

        if ($project) {
            // Status Styling Intelligence
            $status_config = [
                'active'    => ['bg' => 'bg-emerald-500', 'text' => 'text-emerald-500', 'border' => 'border-emerald-100', 'label' => 'Operational'],
                'pending'   => ['bg' => 'bg-amber-500', 'text' => 'text-amber-500', 'border' => 'border-amber-100', 'label' => 'Evaluation'],
                'completed' => ['bg' => 'bg-indigo-500', 'text' => 'text-indigo-500', 'border' => 'border-indigo-100', 'label' => 'Archived'],
                'inactive'  => ['bg' => 'bg-slate-400', 'text' => 'text-slate-400', 'border' => 'border-slate-100', 'label' => 'Decommissioned']
            ];
            $style = $status_config[strtolower($project['status'])] ?? $status_config['inactive'];
?>
            <div class="animate-in fade-in slide-in-from-bottom-4 duration-700">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10 pb-8 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-6">
                        <div class="w-16 h-16 rounded-[2rem] <?php echo $style['bg']; ?> flex items-center justify-center text-white shadow-2xl shadow-<?php echo str_replace('bg-', '', $style['bg']); ?>/30">
                            <i data-lucide="component" class="w-8 h-8"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-3 mb-1">
                                <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tighter">
                                    <?php echo htmlspecialchars($project['name']); ?>
                                </h2>
                                <span class="px-3 py-1 rounded-full <?php echo $style['border']; ?> dark:border-slate-700 border text-[9px] font-black uppercase tracking-widest <?php echo $style['text']; ?>">
                                    <?php echo $style['label']; ?>
                                </span>
                            </div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Global Workstream Registry</p>
                        </div>
                    </div>
                    
                    <div class="flex flex-col items-start md:items-end">
                        <span class="text-[9px] font-black uppercase text-slate-400 tracking-[0.2em] mb-2">Node Identifier</span>
                        <div class="px-4 py-2 bg-slate-900 dark:bg-white rounded-2xl shadow-inner">
                            <code class="text-xs font-black text-white dark:text-slate-900 tracking-widest">
                                #PRJ-<?php echo str_pad($project['id'], 4, '0', STR_PAD_LEFT); ?>
                            </code>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    <div class="lg:col-span-8 space-y-8">
                        <section>
                            <label class="flex items-center gap-2 text-[10px] font-black uppercase text-indigo-500 tracking-widest mb-4">
                                <i data-lucide="file-text" class="w-3 h-3"></i> Strategic Scope
                            </label>
                            <div class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm leading-relaxed text-slate-600 dark:text-slate-400">
                                <p class="text-sm font-medium whitespace-pre-wrap selection:bg-indigo-100"><?php echo nl2br(htmlspecialchars($project['description'])); ?></p>
                            </div>
                        </section>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="p-6 bg-slate-50 dark:bg-slate-800/40 rounded-3xl border border-slate-100 dark:border-slate-800 text-center">
                                <p class="text-[9px] font-black uppercase text-slate-400 mb-1">Total Payload</p>
                                <p class="text-xl font-black text-slate-900 dark:text-white"><?php echo $project['total_tickets']; ?> <span class="text-[10px] opacity-40">Tickets</span></p>
                            </div>
                            <div class="p-6 bg-rose-50/50 dark:bg-rose-950/10 rounded-3xl border border-rose-100 dark:border-rose-900/30 text-center">
                                <p class="text-[9px] font-black uppercase text-rose-500 mb-1">Active Blocks</p>
                                <p class="text-xl font-black text-rose-600"><?php echo $project['open_tickets']; ?> <span class="text-[10px] opacity-40">Open</span></p>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-4 space-y-6">
                        <div class="p-8 bg-slate-50 dark:bg-slate-800/40 rounded-[2.5rem] border border-slate-100 dark:border-slate-800">
                            <h4 class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-6">Metadata Registry</h4>
                            
                            <div class="space-y-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-2xl bg-white dark:bg-slate-900 flex items-center justify-center text-indigo-500 shadow-sm">
                                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-black uppercase text-slate-400">Initialized By</p>
                                        <p class="text-xs font-black text-slate-700 dark:text-slate-200">@<?php echo htmlspecialchars($project['creator_name'] ?? 'system'); ?></p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-2xl bg-white dark:bg-slate-900 flex items-center justify-center text-emerald-500 shadow-sm">
                                        <i data-lucide="calendar" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-black uppercase text-slate-400">Creation Date</p>
                                        <p class="text-xs font-black text-slate-700 dark:text-slate-200"><?php echo date('M d, Y', strtotime($project['created_at'])); ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-10 flex flex-col gap-3">
                                <button onclick="editProject(<?php echo $project['id']; ?>)" class="w-full flex items-center justify-center gap-3 px-6 py-4 bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:scale-[1.02] active:scale-95 transition-all shadow-xl shadow-slate-900/10 dark:shadow-none">
                                    <i data-lucide="settings" class="w-4 h-4"></i>
                                    Modify Workstream
                                </button>
                                <button onclick="loadPage('manage-projects')" class="w-full py-4 text-slate-400 font-black text-[9px] uppercase tracking-widest hover:text-indigo-500 transition-colors">
                                    Return to Registry
                                </button>
                            </div>
                        </div>

                        <div class="p-6 bg-indigo-600 rounded-[2.5rem] text-white shadow-xl shadow-indigo-600/20">
                            <div class="flex items-start gap-4">
                                <i data-lucide="lightbulb" class="w-5 h-5 opacity-50 shrink-0"></i>
                                <p class="text-[11px] font-bold leading-relaxed opacity-90 italic">
                                    "Project status 'Operational' allows developers to continue pushing tickets to this workstream."
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<?php
        } else {
            echo "<div class='p-20 text-center animate-pulse'><i data-lucide='database' class='w-12 h-12 text-slate-300 mx-auto mb-4'></i><p class='font-black text-slate-400 uppercase tracking-widest text-[10px]'>Archive Entry Unreachable</p></div>";
        }
        $stmt->close();
    }
}
?>

<script>
    if(window.lucide) lucide.createIcons();
</script>