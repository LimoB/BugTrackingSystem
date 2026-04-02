<?php
session_start();
include('../../config/config.php');

$user_id = $_SESSION['user_id'];

// Get Stats
$stmt1 = $connection->prepare("SELECT COUNT(*) AS total FROM Tickets WHERE created_by = ?");
$stmt1->bind_param("i", $user_id);
$stmt1->execute();
$total_tickets = $stmt1->get_result()->fetch_assoc()['total'];

$stmt2 = $connection->prepare("SELECT COUNT(*) AS unresolved FROM Tickets WHERE created_by = ? AND status NOT IN ('resolved', 'closed')");
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$unresolved_tickets = $stmt2->get_result()->fetch_assoc()['unresolved'];
?>

<div class="animate-in fade-in slide-in-from-bottom-4 duration-700">
    <div class="mb-10">
        <h1 class="text-4xl font-black text-slate-900 dark:text-white tracking-tighter uppercase">
            Hello, <?php echo htmlspecialchars(explode(' ', $_SESSION['name'])[0]); ?>! 👋
        </h1>
        <p class="text-slate-500 font-medium italic mt-1">Welcome to your bug tracking portal.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
        <div class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-200 dark:border-slate-800 shadow-xl shadow-slate-200/50 dark:shadow-none">
            <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/20 text-blue-600 rounded-2xl flex items-center justify-center mb-6">
                <i data-lucide="layout" class="w-6 h-6"></i>
            </div>
            <h3 class="text-slate-400 font-bold uppercase text-[10px] tracking-widest mb-1">Total Reports</h3>
            <p class="text-5xl font-black"><?php echo $total_tickets; ?></p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-200 dark:border-slate-800 shadow-xl shadow-slate-200/50 dark:shadow-none border-l-4 border-l-amber-400">
            <div class="w-12 h-12 bg-amber-50 dark:bg-amber-900/20 text-amber-600 rounded-2xl flex items-center justify-center mb-6">
                <i data-lucide="clock" class="w-6 h-6"></i>
            </div>
            <h3 class="text-slate-400 font-bold uppercase text-[10px] tracking-widest mb-1">Pending Fix</h3>
            <p class="text-5xl font-black text-amber-500"><?php echo $unresolved_tickets; ?></p>
        </div>

        <div class="bg-slate-900 dark:bg-blue-600 p-8 rounded-[2.5rem] text-white flex flex-col justify-between">
            <div>
                <h3 class="text-blue-200 font-bold uppercase text-[10px] tracking-widest mb-4 italic underline">System Update</h3>
                <p class="text-lg font-medium leading-tight">Attach clear descriptions to fix issues 40% faster.</p>
            </div>
            <div class="mt-6 flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></div>
                <span class="text-[10px] font-black uppercase tracking-widest opacity-70">Server: Active</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <button onclick="loadPage('create-ticket')" class="group p-10 bg-blue-600 rounded-[2.5rem] text-left transition-all hover:scale-[1.02] shadow-2xl shadow-blue-500/20">
            <i data-lucide="plus-circle" class="text-white w-10 h-10 mb-4 group-hover:rotate-90 transition-transform duration-500"></i>
            <h3 class="text-xl font-black text-white uppercase italic">Report An Issue</h3>
            <p class="text-blue-100 text-sm mt-2 font-medium">Click to select a project and describe a bug.</p>
        </button>

        <button onclick="loadPage('view-tickets')" class="group p-10 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[2.5rem] text-left transition-all hover:scale-[1.02]">
            <i data-lucide="list" class="text-blue-500 w-10 h-10 mb-4"></i>
            <h3 class="text-xl font-black text-slate-800 dark:text-white uppercase italic">Activity Log</h3>
            <p class="text-slate-500 text-sm mt-2 font-medium">Monitor your current reports and feedback status.</p>
        </button>
    </div>
</div>