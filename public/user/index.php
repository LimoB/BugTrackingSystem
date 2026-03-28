<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login/index.php");
    exit();
}

include('../../config/config.php');
$base_url = "/php-bugtracking-system/";

$user_id = $_SESSION['user_id'];

// Total Tickets
$stmt1 = $connection->prepare("SELECT COUNT(*) AS total FROM Tickets WHERE created_by = ?");
$stmt1->bind_param("i", $user_id);
$stmt1->execute();
$total_tickets = $stmt1->get_result()->fetch_assoc()['total'];

// Unresolved Tickets
$stmt2 = $connection->prepare("SELECT COUNT(*) AS unresolved FROM Tickets WHERE created_by = ? AND status NOT IN ('resolved', 'closed')");
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$unresolved_tickets = $stmt2->get_result()->fetch_assoc()['unresolved'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Zappr</title>
    <link href="<?php echo $base_url; ?>dist/output.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@0.344.0/dist/umd/lucide.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 transition-colors duration-300 flex">

    <?php include('header.php'); ?>

    <main class="flex-grow p-6 lg:p-10">
        <header class="flex flex-col md:flex-row md:items-center justify-between mb-10 gap-4">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight">
                    Hello, <?php echo htmlspecialchars(explode(' ', $_SESSION['name'])[0]); ?>! 👋
                </h1>
                <p class="text-slate-500 dark:text-slate-400">Welcome to your bug tracking portal.</p>
            </div>
            <a href="create-ticket.php" class="bg-blue-600 text-white px-6 py-3 rounded-2xl font-bold shadow-lg shadow-blue-200 dark:shadow-none hover:bg-blue-700 hover:scale-[1.02] transition-all flex items-center gap-2">
                <i data-lucide="plus" class="w-5 h-5"></i> Create Ticket
            </a>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/20 text-blue-600 rounded-2xl flex items-center justify-center mb-6">
                    <i data-lucide="layout" class="w-6 h-6"></i>
                </div>
                <h3 class="text-slate-400 font-bold uppercase text-xs tracking-widest mb-1">Total Reports</h3>
                <p class="text-5xl font-black"><?php echo $total_tickets; ?></p>
            </div>

            <div class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-200 dark:border-slate-800 shadow-sm border-l-4 border-l-amber-400">
                <div class="w-12 h-12 bg-amber-50 dark:bg-amber-900/20 text-amber-600 rounded-2xl flex items-center justify-center mb-6">
                    <i data-lucide="clock" class="w-6 h-6"></i>
                </div>
                <h3 class="text-slate-400 font-bold uppercase text-xs tracking-widest mb-1">Pending Fix</h3>
                <p class="text-5xl font-black text-amber-500"><?php echo $unresolved_tickets; ?></p>
            </div>

            <div class="bg-slate-900 dark:bg-blue-600 p-8 rounded-[2.5rem] text-white">
                <h3 class="text-blue-200 font-bold uppercase text-xs tracking-widest mb-4">Quick Tip</h3>
                <p class="text-lg font-medium leading-relaxed">
                    Attach screenshots to your bug reports to help developers fix issues 40% faster!
                </p>
                <div class="mt-6 flex gap-2">
                    <i data-lucide="zap" class="w-5 h-5 text-yellow-400 fill-current"></i>
                    <span class="text-sm font-bold opacity-80">Zappr Pro Account</span>
                </div>
            </div>
        </div>

        <div class="mt-10 bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-200 dark:border-slate-800 p-8">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-xl font-bold tracking-tight">Recent Activity</h2>
                <a href="view-tickets.php" class="text-blue-600 font-bold text-sm hover:underline">See All</a>
            </div>
            
            <?php if ($total_tickets == 0): ?>
                <div class="text-center py-12">
                    <div class="bg-slate-50 dark:bg-slate-800 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="ghost" class="w-10 h-10 text-slate-300"></i>
                    </div>
                    <p class="text-slate-500">No tickets found. Your workspace is clean!</p>
                </div>
            <?php else: ?>
                <div class="flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-800">
                    <div class="p-2 bg-green-100 dark:bg-green-900/30 text-green-600 rounded-lg">
                        <i data-lucide="check-circle" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="font-bold text-sm">Welcome to Zappr v2.0</p>
                        <p class="text-xs text-slate-500">Your professional bug tracking dashboard is now active.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        lucide.createIcons();

        function setTheme(theme) {
            const html = document.documentElement;
            const themeText = document.getElementById("themeText");
            const themeIcon = document.getElementById("themeIcon");

            if (theme === "dark") {
                html.classList.add("dark");
                if(themeText) themeText.textContent = "Light Mode";
                if(themeIcon) themeIcon.setAttribute("data-lucide", "sun");
            } else {
                html.classList.remove("dark");
                if(themeText) themeText.textContent = "Dark Mode";
                if(themeIcon) themeIcon.setAttribute("data-lucide", "moon");
            }
            localStorage.setItem("theme", theme);
            lucide.createIcons(); 
        }

        function toggleTheme() {
            const current = localStorage.getItem("theme") || "light";
            setTheme(current === "light" ? "dark" : "light");
        }

        (function () {
            const savedTheme = localStorage.getItem("theme") || "light";
            setTheme(savedTheme);
        })();
    </script>
</body>
</html>