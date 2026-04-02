<?php
/**
 * File: admin/index.php
 * Purpose: Central Command for System Administrators with Full Mobile Support.
 */
session_start();

// ✅ Strict Admin Check
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: ../login/index.php");
    exit();
}

$admin_name = $_SESSION['name'] ?? 'Administrator';
$base_url = "/php-bugtracking-system/";
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
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
        body { font-family: 'Plus Jakarta Sans', sans-serif; transition: background-color 0.3s; }

        .nav-active {
            background-color: rgba(16, 185, 129, 0.1); 
            color: #059669 !important;
            border-right: 4px solid #059669;
        }

        .dark .nav-active {
            background-color: rgba(16, 185, 129, 0.1);
            color: #34d399 !important; 
            border-right: 4px solid #10b821;
        }

        /* Mobile Sidebar Slide */
        #mobile-sidebar { transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        .sidebar-open { transform: translateX(0) !important; }

        #content-area { transition: opacity 0.25s ease; }
        .loading-state { opacity: 0.4; pointer-events: none; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex min-h-screen overflow-x-hidden">

    <aside class="w-64 border-r border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hidden md:flex flex-col sticky top-0 h-screen z-40">
        <div class="p-6 flex items-center gap-3 border-b border-slate-100 dark:border-slate-800">
            <div class="bg-emerald-600 p-2 rounded-xl shadow-lg shadow-emerald-500/20">
                <i data-lucide="shield-check" class="text-white w-5 h-5"></i>
            </div>
            <span class="text-xl font-bold tracking-tight italic">Admin<span class="text-emerald-600">Box.</span></span>
        </div>

        <nav class="flex-grow p-4 space-y-1 mt-4">
            <a href="javascript:void(0)" onclick="loadPage('admin_stats')" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50" data-page="admin_stats">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard
            </a>
            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-4 mt-8 mb-2">Operations</div>
            <a href="javascript:void(0)" onclick="loadPage('manage-tickets')" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50" data-page="manage-tickets">
                <i data-lucide="ticket" class="w-5 h-5"></i> Manage Tickets
            </a>
            <a href="javascript:void(0)" onclick="loadPage('manage-projects')" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50" data-page="manage-projects">
                <i data-lucide="folder-kanban" class="w-5 h-5"></i> Projects
            </a>
            <a href="javascript:void(0)" onclick="loadPage('manage-users')" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50" data-page="manage-users">
                <i data-lucide="users" class="w-5 h-5"></i> User Access
            </a>
        </nav>

        <div class="p-4 border-t border-slate-100 dark:border-slate-800 space-y-2">
            <button onclick="toggleTheme()" class="w-full flex items-center gap-3 px-4 py-2 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all text-sm font-bold">
                <i data-lucide="moon" class="w-4 h-4 dark:hidden"></i>
                <i data-lucide="sun" class="w-4 h-4 hidden dark:block"></i>
                <span>Theme Mode</span>
            </button>
            <a href="../../scripts/logout.php" class="flex items-center gap-3 px-4 py-2 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/10 rounded-xl transition-all text-sm font-bold">
                <i data-lucide="power" class="w-4 h-4"></i> Sign Out
            </a>
        </div>
    </aside>

    <div id="mobile-overlay" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[60] hidden md:hidden"></div>
    <aside id="mobile-sidebar" class="fixed top-0 left-0 h-full w-72 bg-white dark:bg-slate-900 z-[70] shadow-2xl -translate-x-full md:hidden flex flex-col">
        <div class="p-6 flex items-center justify-between border-b border-slate-100 dark:border-slate-800">
            <span class="font-black italic text-emerald-600 uppercase">AdminBox.</span>
            <button onclick="toggleMobileMenu()" class="p-2 bg-slate-100 dark:bg-slate-800 rounded-lg"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <nav class="p-4 space-y-2">
            <a href="javascript:void(0)" onclick="loadPage('admin_stats'); toggleMobileMenu();" class="flex items-center gap-3 px-4 py-4 rounded-xl font-bold bg-slate-50 dark:bg-slate-800/40"><i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard</a>
            <a href="javascript:void(0)" onclick="loadPage('manage-tickets'); toggleMobileMenu();" class="flex items-center gap-3 px-4 py-4 rounded-xl font-bold"><i data-lucide="ticket" class="w-5 h-5"></i> Tickets</a>
            <a href="javascript:void(0)" onclick="loadPage('manage-projects'); toggleMobileMenu();" class="flex items-center gap-3 px-4 py-4 rounded-xl font-bold"><i data-lucide="folder-kanban" class="w-5 h-5"></i> Projects</a>
            <a href="javascript:void(0)" onclick="loadPage('manage-users'); toggleMobileMenu();" class="flex items-center gap-3 px-4 py-4 rounded-xl font-bold"><i data-lucide="users" class="w-5 h-5"></i> Users</a>
        </nav>
    </aside>

    <main class="flex-grow flex flex-col min-w-0 bg-white dark:bg-slate-950 relative">
        
        <header class="h-16 md:h-20 border-b border-slate-100 dark:border-slate-800 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md flex items-center justify-between px-6 md:px-10 sticky top-0 z-50">
            <div class="flex items-center gap-4">
                <button onclick="toggleMobileMenu()" class="md:hidden p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <div class="hidden sm:flex items-center gap-2">
                    <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Node: Active</span>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-black text-slate-800 dark:text-slate-100"><?php echo htmlspecialchars($admin_name); ?></p>
                    <p class="text-[9px] text-emerald-500 font-bold uppercase tracking-widest">Root Authority</p>
                </div>
                <div class="w-10 h-10 rounded-2xl bg-emerald-600 flex items-center justify-center text-white font-black shadow-lg shadow-emerald-200 dark:shadow-none">
                    <?php echo strtoupper(substr($admin_name, 0, 1)); ?>
                </div>
            </div>
        </header>

        <div id="content-area" class="p-4 md:p-8 lg:p-12 min-h-[calc(100vh-64px)] pb-24 md:pb-8">
            <div class="flex flex-col items-center justify-center py-40">
                <div class="w-10 h-10 border-4 border-emerald-600 border-t-transparent rounded-full animate-spin"></div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mt-6 italic">Synchronizing Logs...</p>
            </div>
        </div>

        <div class="fixed bottom-0 left-0 w-full bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl border-t border-slate-200 dark:border-slate-800 px-6 py-3 flex justify-between items-center md:hidden z-50">
            <button onclick="loadPage('admin_stats')" class="flex flex-col items-center gap-1 text-slate-400 hover:text-emerald-600">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                <span class="text-[8px] font-black uppercase tracking-widest">Home</span>
            </button>
            <button onclick="loadPage('manage-tickets')" class="flex flex-col items-center gap-1 text-slate-400 hover:text-emerald-600">
                <i data-lucide="ticket" class="w-5 h-5"></i>
                <span class="text-[8px] font-black uppercase tracking-widest">Tickets</span>
            </button>
            <button onclick="loadPage('manage-users')" class="flex flex-col items-center gap-1 text-slate-400 hover:text-emerald-600">
                <i data-lucide="users" class="w-5 h-5"></i>
                <span class="text-[8px] font-black uppercase tracking-widest">Users</span>
            </button>
            <button onclick="toggleTheme()" class="flex flex-col items-center gap-1 text-slate-400">
                <i data-lucide="sun-moon" class="w-5 h-5"></i>
                <span class="text-[8px] font-black uppercase tracking-widest">Theme</span>
            </button>
        </div>
    </main>

<script>
    function toggleMobileMenu() {
        const isOpen = $("#mobile-sidebar").hasClass("sidebar-open");
        $("#mobile-sidebar").toggleClass("sidebar-open");
        if (!isOpen) $("#mobile-overlay").fadeIn(300);
        else $("#mobile-overlay").fadeOut(300);
    }

    $("#mobile-overlay").on('click', toggleMobileMenu);

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
                }, 100);
            },
            error: function () {
                contentArea.removeClass("loading-state").html(`<div class="text-center py-20 text-rose-500 font-bold uppercase tracking-widest">System Resource Error</div>`);
                lucide.createIcons();
            }
        });
    }

    function toggleTheme() {
        const isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('admin-theme', isDark ? 'dark' : 'light');
    }

    $(document).ready(function () {
        if (localStorage.getItem('admin-theme') === 'dark' || (!('admin-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
        
        const lastView = window.location.hash.replace('#', '') || localStorage.getItem('admin_last_view') || 'admin_stats';
        loadPage(lastView);
        if (window.lucide) lucide.createIcons();
    });
</script>
</body>
</html>