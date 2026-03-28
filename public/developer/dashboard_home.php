<?php
session_start();
include('../../config/config.php');

// Fetch some quick stats for the dashboard cards
$user_id = $_SESSION['user_id'];

// Get assigned count
$stmt1 = $connection->prepare("SELECT COUNT(*) as total FROM Tickets WHERE assigned_to = ? AND status != 'resolved'");
$stmt1->bind_param("i", $user_id);
$stmt1->execute();
$my_tickets = $stmt1->get_result()->fetch_assoc()['total'];

// Get global open count
$res2 = mysqli_query($connection, "SELECT COUNT(*) as total FROM Tickets WHERE status = 'open'");
$global_open = mysqli_fetch_assoc($res2)['total'];
?>

<div class="animate-fade-in max-w-5xl mx-auto">
    <div class="mb-10">
        <h1 class="text-4xl font-extrabold tracking-tight mb-2 text-slate-900 dark:text-white">System Overview</h1>
        <p class="text-slate-500 dark:text-slate-400 text-lg">Real-time status of your development pipeline.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-slate-900 p-8 rounded-[2rem] border border-slate-200 dark:border-slate-800 shadow-sm group hover:border-indigo-500 transition-all">
            <div class="w-12 h-12 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 rounded-2xl flex items-center justify-center mb-6">
                <i data-lucide="zap"></i>
            </div>
            <div class="text-4xl font-black mb-1"><?php echo $my_tickets; ?></div>
            <p class="text-slate-500 font-bold text-xs uppercase tracking-widest">My Active Bugs</p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-8 rounded-[2rem] border border-slate-200 dark:border-slate-800 shadow-sm group hover:border-indigo-500 transition-all">
            <div class="w-12 h-12 bg-rose-50 dark:bg-rose-900/30 text-rose-600 rounded-2xl flex items-center justify-center mb-6">
                <i data-lucide="globe"></i>
            </div>
            <div class="text-4xl font-black mb-1"><?php echo $global_open; ?></div>
            <p class="text-slate-500 font-bold text-xs uppercase tracking-widest">Global Open Tickets</p>
        </div>

        <button onclick="loadPage('create-ticket')" class="bg-indigo-600 p-8 rounded-[2rem] text-white text-left hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-200 dark:shadow-none group">
            <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center mb-6">
                <i data-lucide="plus"></i>
            </div>
            <div class="text-xl font-black mb-1">New Ticket</div>
            <p class="text-indigo-100 text-sm opacity-80">Report a new internal bug or task.</p>
        </button>
    </div>
</div>

<script>
    lucide.createIcons();
</script>