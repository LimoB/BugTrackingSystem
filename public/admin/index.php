<?php
session_start();

/**
 * File: admin/index.php
 * Purpose: Central Command for System Administrators.
 */

// ✅ Strict Admin Check
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: ../login/index.php");
    exit();
}

$admin_name = $_SESSION['name'] ?? 'Administrator';
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | Zappr</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'Inter', 'sans-serif'] },
                }
            }
        }
    </script>
    
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { transition: background-color 0.3s; font-feature-settings: "cv11", "ss01"; }

        .nav-active {
            background-color: rgb(240 253 244); 
            color: rgb(5 150 105) !important;
            border-right: 4px solid rgb(5 150 105);
        }

        .dark .nav-active {
            background-color: rgba(6, 78, 59, 0.3);
            color: rgb(52 211 153) !important;
            border-right: 4px solid rgb(16 185 129);
        }

        #content-area { transition: all 0.3s ease; }
        .loading-state { opacity: 0.5; filter: blur(4px); pointer-events: none; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex min-h-screen">

    <aside class="w-64 border-r border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hidden md:flex flex-col sticky top-0 h-screen z-20">
        <div class="p-6 flex items-center gap-3 border-b border-slate-100 dark:border-slate-800">
            <div class="bg-emerald-600 p-2 rounded-xl shadow-lg shadow-emerald-200 dark:shadow-none">
                <i data-lucide="shield-check" class="text-white w-5 h-5"></i>
            </div>
            <span class="text-xl font-bold tracking-tight italic">Admin<span class="text-emerald-600">Box.</span></span>
        </div>

        <nav class="flex-grow p-4 space-y-1 mt-4">
            <a href="javascript:void(0)" onclick="loadPage('admin_stats')" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition-all text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50" data-page="admin_stats">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard
            </a>
            
            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-4 mt-8 mb-2">Operations</div>
            <a href="javascript:void(0)" onclick="loadPage('manage-tickets')" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition-all text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50" data-page="manage-tickets">
                <i data-lucide="ticket" class="w-5 h-5"></i> Manage Tickets
            </a>
            
            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-4 mt-8 mb-2">System Control</div>
            <a href="javascript:void(0)" onclick="loadPage('manage-projects')" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition-all text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50" data-page="manage-projects">
                <i data-lucide="folder-kanban" class="w-5 h-5"></i> Projects
            </a>
            <a href="javascript:void(0)" onclick="loadPage('manage-users')" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition-all text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50" data-page="manage-users">
                <i data-lucide="users" class="w-5 h-5"></i> User Access
            </a>
        </nav>

        <div class="p-4 border-t border-slate-100 dark:border-slate-800 space-y-2">
            <button onclick="toggleTheme()" class="w-full flex items-center gap-3 px-4 py-3 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all text-sm font-bold">
                <i data-lucide="moon" class="w-4 h-4 dark:hidden text-indigo-500"></i>
                <i data-lucide="sun" class="w-4 h-4 hidden dark:block text-amber-400"></i>
                <span>Toggle Theme</span>
            </button>
            <a href="../../scripts/logout.php" class="flex items-center gap-3 px-4 py-3 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/10 rounded-xl transition-all text-sm font-bold">
                <i data-lucide="log-out" class="w-4 h-4"></i> Sign Out
            </a>
        </div>
    </aside>

    <main class="flex-grow flex flex-col min-w-0 bg-white dark:bg-slate-950">
        <header class="h-20 border-b border-slate-100 dark:border-slate-800 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md flex items-center justify-between px-10 sticky top-0 z-10">
            <div class="flex items-center gap-3">
                <div class="w-3 h-3 bg-emerald-500 rounded-full animate-pulse"></div>
                <span id="header-status" class="text-[10px] font-black uppercase tracking-widest text-slate-400">Live Infrastructure</span>
            </div>

            <div class="flex items-center gap-6">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-black text-slate-800 dark:text-slate-100"><?php echo htmlspecialchars($admin_name); ?></p>
                    <p class="text-[10px] text-emerald-500 font-bold uppercase tracking-widest">Root Access</p>
                </div>
                <button onclick="loadPage('profile')" class="group relative">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-600 flex items-center justify-center text-white text-lg font-black shadow-lg shadow-emerald-200 dark:shadow-none group-hover:scale-105 transition-transform cursor-pointer">
                        <?php echo strtoupper(substr($admin_name, 0, 1)); ?>
                    </div>
                </button>
            </div>
        </header>

        <div id="content-area" class="p-8 lg:p-12 overflow-y-auto">
            <div class="flex flex-col items-center justify-center py-40">
                <div class="w-12 h-12 border-4 border-emerald-600 border-t-transparent rounded-full animate-spin"></div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mt-6">Initializing Admin Node...</p>
            </div>
        </div>
    </main>

<script>
    function loadPage(page) {
        if(!page) return;
        const contentArea = $("#content-area");
        contentArea.addClass("loading-state");

        $.ajax({
            url: page + ".php",
            method: "GET",
            success: function (data) {
                setTimeout(() => {
                    window.location.hash = page;
                    contentArea.html(data).removeClass("loading-state");
                    if (window.lucide) lucide.createIcons();
                    
                    $(".nav-link").removeClass("nav-active");
                    $(`.nav-link[data-page='${page}']`).addClass("nav-active");
                    
                    localStorage.setItem('admin_last_view', page);
                }, 150);
            },
            error: function () {
                contentArea.removeClass("loading-state").html(`
                    <div class="text-center py-32">
                        <i data-lucide="alert-octagon" class="w-16 h-16 text-rose-500 mx-auto mb-6"></i>
                        <h2 class="text-2xl font-black uppercase">Service Error</h2>
                        <p class="text-slate-500 italic mt-2">Resource [${page}.php] not reachable.</p>
                    </div>
                `);
                lucide.createIcons();
            }
        });
    }

    function toggleTheme() {
        const isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('admin-theme', isDark ? 'dark' : 'light');
    }

    $(document).ready(function () {
        if (localStorage.getItem('admin-theme') === 'dark') document.documentElement.classList.add('dark');
        
        const currentHash = window.location.hash.replace('#', '');
        const lastView = localStorage.getItem('admin_last_view') || 'admin_stats';
        loadPage(currentHash || lastView);
        
        if (window.lucide) lucide.createIcons();
    });
</script>
</body>
</html>