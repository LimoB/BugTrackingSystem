<?php
session_start();

// 🛡️ Security Check: Developer Access Only
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'developer') {
    header("Location: ../login/index.php");
    exit();
}

include('../../config/config.php');
$base_url = "/php-bugtracking-system/";

// 🔍 Fetch Developer Profile
$user_id = $_SESSION['user_id'];
$stmt = $connection->prepare("SELECT name, email FROM Users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// 📊 Global Stats for Sidebar/Header
$stmt_count = $connection->prepare("SELECT COUNT(*) as count FROM Tickets WHERE assigned_to = ? AND status != 'resolved'");
$stmt_count->bind_param("i", $user_id);
$stmt_count->execute();
$my_active_bugs = $stmt_count->get_result()->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Developer Terminal | Zappr</title>
    
    <link href="<?php echo $base_url; ?>dist/output.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@0.344.0/dist/umd/lucide.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; overflow-x: hidden; }
        
        /* Standard CSS for active states (Browsers don't support @apply) */
        .nav-active {
            background-color: #4f46e5; /* indigo-600 */
            color: white !important;
            box-shadow: 0 10px 15px -3px rgba(165, 180, 252, 0.4);
        }
        
        .dark .nav-active {
            box-shadow: none;
        }

        .dark ::-webkit-scrollbar { width: 6px; }
        .dark ::-webkit-scrollbar-track { background: #0f172a; }
        .dark ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 10px; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex min-h-screen">

    <aside class="w-72 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border-r border-slate-200 dark:border-slate-800 hidden md:flex flex-col sticky top-0 h-screen z-40 transition-all duration-300">
        <div class="p-8 mb-4">
            <div class="flex items-center gap-3">
                <div class="bg-indigo-600 p-2 rounded-xl shadow-xl shadow-indigo-200 dark:shadow-none">
                    <i data-lucide="terminal" class="text-white w-5 h-5"></i>
                </div>
                <span class="text-2xl font-black tracking-tighter uppercase">DevHub<span class="text-indigo-600">.</span></span>
            </div>
            <div class="mt-4 flex items-center gap-2">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">System Pulse: Online</span>
            </div>
        </div>

        <nav class="flex-grow px-4 space-y-1">
            <p class="px-4 text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Main Console</p>
            
            <a href="javascript:void(0)" onclick="loadPage('dashboard_home')" class="nav-link flex items-center gap-3 px-4 py-3.5 rounded-2xl font-bold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all" data-page="dashboard_home">
                <i data-lucide="cpu" class="w-5 h-5"></i> Overview
            </a>

            <a href="javascript:void(0)" onclick="loadPage('assigned-tickets')" class="nav-link flex items-center gap-3 px-4 py-3.5 rounded-2xl font-bold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all" data-page="assigned-tickets">
                <i data-lucide="bug" class="w-5 h-5"></i> My Queue
                <?php if($my_active_bugs > 0): ?>
                    <span class="ml-auto bg-rose-500 text-white text-[10px] font-black px-2 py-0.5 rounded-lg shadow-lg shadow-rose-200 dark:shadow-none"><?php echo $my_active_bugs; ?></span>
                <?php endif; ?>
            </a>

            <a href="javascript:void(0)" onclick="loadPage('view-tickets')" class="nav-link flex items-center gap-3 px-4 py-3.5 rounded-2xl font-bold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all" data-page="view-tickets">
                <i data-lucide="database" class="w-5 h-5"></i> Global Backlog
            </a>

            <div class="pt-6">
                <p class="px-4 text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Maintenance</p>
                <a href="javascript:void(0)" onclick="loadPage('system-logs')" class="nav-link flex items-center gap-3 px-4 py-3.5 rounded-2xl font-bold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all" data-page="system-logs">
                    <i data-lucide="scroll-text" class="w-5 h-5"></i> Error Logs
                </a>
            </div>
        </nav>

        <div class="p-6 mt-auto border-t border-slate-100 dark:border-slate-800">
            <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-[1.5rem] mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white font-black shadow-lg">
                        <?php echo strtoupper(substr($user['name'] ?? 'D', 0, 1)); ?>
                    </div>
                    <div class="truncate">
                        <p class="text-xs font-black truncate text-slate-900 dark:text-white"><?php echo htmlspecialchars($user['name'] ?? 'Developer'); ?></p>
                        <p class="text-[9px] text-indigo-500 font-black uppercase tracking-widest">Developer</p>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-2">
                <button onclick="toggleTheme()" class="flex items-center justify-center p-3 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-all text-slate-500">
                    <i id="themeIcon" data-lucide="moon" class="w-4 h-4"></i>
                </button>
                <a href="../../scripts/logout.php" class="flex items-center justify-center p-3 bg-rose-50 dark:bg-rose-900/20 text-rose-500 hover:bg-rose-100 rounded-xl transition-all">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                </a>
            </div>
        </div>
    </aside>

    <main class="flex-grow flex flex-col min-w-0">
        <div id="content-area" class="p-6 lg:p-12 transition-all duration-300">
            <div class="flex items-center justify-center min-h-[60vh]">
                <div class="text-center">
                    <div class="w-12 h-12 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
                    <p class="font-black text-[10px] uppercase tracking-[0.3em] text-slate-400">Initializing Terminal</p>
                </div>
            </div>
        </div>
    </main>

    <script>
    // --- 🚀 Enhanced AJAX Page Loader ---
    // Added support for 'params' to fix the Ticket ID issue
    function loadPage(page, params = "") {
        const contentArea = $("#content-area");
        contentArea.css("opacity", "0.4");
        
        let url = page + ".php";
        if (params) {
            url += "?" + params;
        }

        $.ajax({
            url: url,
            method: "GET",
            cache: false,
            success: function(data) {
                contentArea.html(data).css("opacity", "1");
                if (window.lucide) lucide.createIcons();
                
                // Update Sidebar Active Class
                $(".nav-link").removeClass("nav-active");
                $(`.nav-link[data-page='${page}']`).addClass("nav-active");
                
                localStorage.setItem('last_dev_page', page);
            },
            error: function(xhr) {
                contentArea.css("opacity", "1").html(`
                    <div class="text-center py-20">
                        <h2 class="text-xl font-bold text-rose-500">Module Error (404)</h2>
                        <p class="text-slate-500 mt-2">Target: ${url}</p>
                        <button onclick="loadPage('dashboard_home')" class="mt-4 text-indigo-600 font-bold underline">Go Back</button>
                    </div>
                `);
            }
        });
    }

    $(document).ready(function() {
        const lastPage = localStorage.getItem('last_dev_page') || 'dashboard_home';
        loadPage(lastPage);
        lucide.createIcons();
    });

    function setTheme(theme) {
        const html = document.documentElement;
        if (theme === "dark") {
            html.classList.add("dark");
        } else {
            html.classList.remove("dark");
        }
        localStorage.setItem("theme", theme);
        if(window.lucide) lucide.createIcons(); 
    }

    function toggleTheme() {
        const current = localStorage.getItem("theme") || "light";
        setTheme(current === "light" ? "dark" : "light");
    }

    (function () {
        setTheme(localStorage.getItem("theme") || "light");
    })();
</script>
</body>
</html>