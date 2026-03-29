<?php
session_start();

// 1. Error Suppression for Production feel (prevents 500 crashes)
error_reporting(E_ALL & ~E_NOTICE); 

$config_path = '../../config/config.php';
if (!file_exists($config_path)) {
    die("<div class='p-12 text-center bg-rose-50 dark:bg-rose-950/20 rounded-[3rem] border border-rose-200 dark:border-rose-800/50'>
            <h1 class='text-rose-500 font-black uppercase tracking-widest text-xs mb-2'>[SYSTEM_HALT]</h1>
            <p class='text-slate-500 text-xs font-mono'>Configuration baseline missing at target path.</p>
         </div>");
}
include($config_path);

// 🛡️ Security Guard
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'developer') {
    die("<div class='p-12 text-center font-black uppercase tracking-[0.3em] text-slate-400 text-[10px]'>Access Denied: Level 4 Clearance Required</div>");
}

// 🩺 Check Database Connection safely
$db_status = "Disconnected";
$db_version = "Unknown Source";
if (isset($connection) && @$connection->ping()) {
    $db_status = "Operational";
    $db_version = $connection->server_info;
}

// 📝 Technical Event Fetching - Optimized for speed and stability
$logs = [];
try {
    // Check if table exists to prevent crash
    $table_check = mysqli_query($connection, "SHOW TABLES LIKE 'Tickets'");
    if ($table_check && mysqli_num_rows($table_check) > 0) {
        // Simplified query to avoid "as type" reserved word issues in some SQL versions
        $log_query = "SELECT title, updated_at, status FROM Tickets ORDER BY updated_at DESC LIMIT 15";
        $result = mysqli_query($connection, $log_query);
        if ($result) {
            while($row = mysqli_fetch_assoc($result)) { 
                $logs[] = $row; 
            }
        }
    }
} catch (Exception $e) {
    // Silent catch to prevent 500 error
}

// Helper for memory
$mem_usage = round(memory_get_usage() / 1024 / 1024, 2);
?>

<div class="animate-fade-in space-y-8 p-2">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="h-1.5 w-8 bg-indigo-500 rounded-full"></div>
                <span class="text-[10px] font-black uppercase tracking-[0.4em] text-indigo-500">Node_Diagnostics_v3</span>
            </div>
            <h1 class="text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white">System <span class="text-indigo-600/50">Pulse</span></h1>
        </div>
        
        <div class="flex items-center gap-4 bg-white dark:bg-slate-900 p-2 pl-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex flex-col items-end">
                <span class="text-[9px] font-black uppercase text-slate-400 tracking-widest">Active Runtime</span>
                <span id="terminal-clock" class="text-xs font-mono font-bold text-slate-700 dark:text-indigo-400">00:00:00</span>
            </div>
            <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-200 dark:shadow-none">
                <i data-lucide="activity" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex justify-between items-center mb-4">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Database</p>
                <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-500 text-[9px] font-bold rounded-md">LIVE</span>
            </div>
            <p class="text-lg font-bold text-slate-800 dark:text-slate-100"><?php echo substr($db_version, 0, 18); ?></p>
            <p class="text-[10px] text-slate-400 mt-1">Status: <?php echo $db_status; ?></p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Memory Load</p>
            <div class="flex items-end gap-2">
                <p class="text-2xl font-black text-slate-800 dark:text-slate-100"><?php echo $mem_usage; ?></p>
                <p class="text-xs font-bold text-slate-400 mb-1">MB</p>
            </div>
            <div class="mt-3 w-full bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden">
                <div class="bg-indigo-500 h-full transition-all duration-1000" style="width: <?php echo min(($mem_usage * 5), 100); ?>%"></div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">System Identity</p>
            <p class="text-lg font-bold text-slate-800 dark:text-slate-100 italic">Zappr_Dev_Core</p>
            <div class="flex items-center gap-2 mt-2">
                <i data-lucide="shield-check" class="w-3 h-3 text-emerald-500"></i>
                <span class="text-[10px] font-bold text-slate-400 uppercase">Clearance Level 4</span>
            </div>
        </div>
    </div>

    <div class="bg-slate-950 rounded-[2.5rem] border border-slate-800/60 overflow-hidden shadow-2xl">
        <div class="bg-slate-900/50 px-8 py-4 border-b border-slate-800/50 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex gap-1.5">
                    <div class="w-2.5 h-2.5 rounded-full bg-slate-700"></div>
                    <div class="w-2.5 h-2.5 rounded-full bg-slate-700"></div>
                    <div class="w-2.5 h-2.5 rounded-full bg-slate-700"></div>
                </div>
                <span class="text-[10px] font-mono text-slate-500 uppercase tracking-widest font-bold ml-4">Event_Stream.log</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                </span>
                <span class="text-[9px] font-mono text-slate-400 font-bold">LISTENING</span>
            </div>
        </div>

        <div class="p-8 font-mono text-[11px] leading-relaxed max-h-[400px] overflow-y-auto custom-scrollbar bg-grid-pattern">
            <?php if (empty($logs)): ?>
                <p class="text-slate-600 italic">// No recent activities recorded in the ticket buffer.</p>
            <?php else: ?>
                <?php foreach($logs as $log): ?>
                    <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-6 mb-3 group">
                        <span class="text-slate-600 shrink-0 font-bold">[<?php echo date('H:i:s', strtotime($log['updated_at'])); ?>]</span>
                        <span class="text-indigo-500 font-black tracking-tighter shrink-0">TICKET_PUSH</span>
                        <span class="text-slate-300 group-hover:text-white transition-colors">
                            "<?php echo htmlspecialchars($log['title']); ?>" 
                            <span class="text-slate-600 ml-2">--status=</span><span class="text-emerald-500/80"><?php echo strtoupper($log['status']); ?></span>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <div class="mt-6 flex items-center gap-2">
                <span class="text-indigo-500 animate-pulse font-black text-sm">>></span>
                <span class="text-slate-600 font-bold">Awaiting new system interrupts...</span>
            </div>
        </div>
    </div>
</div>

<script>
    if (window.lucide) { lucide.createIcons(); }

    function updateClock() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', { hour12: false });
        const clockEl = document.getElementById('terminal-clock');
        if (clockEl) clockEl.textContent = timeString;
    }
    
    if (window.diagClock) clearInterval(window.diagClock);
    window.diagClock = setInterval(updateClock, 1000);
    updateClock();
</script>

<style>
    .bg-grid-pattern {
        background-image: radial-gradient(circle, #ffffff05 1px, transparent 1px);
        background-size: 20px 20px;
    }
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 10px; }
</style>