<?php
/**
 * File: developer/index.php
 * Purpose: Professional High-Performance Dashboard with Mobile Responsiveness.
 */
session_start();

// 🛡️ Security Check
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'developer') {
    header("Location: ../login/index.php");
    exit();
}

include('../../config/config.php');

// 🔍 Fetch Profile
$user_id = (int)$_SESSION['user_id'];
$stmt = $connection->prepare("SELECT name, email FROM Users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// 📊 Task Count for Badge
$stmt_count = $connection->prepare("SELECT COUNT(*) as count FROM Tickets WHERE assigned_to = ? AND status NOT IN ('resolved', 'closed')");
$stmt_count->bind_param("i", $user_id);
$stmt_count->execute();
$active_tasks = $stmt_count->get_result()->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dev Workspace | Zappr</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { 
                        sans: ['Plus Jakarta Sans', 'Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'Fira Code', 'monospace']
                    },
                }
            }
        }
    </script>
    
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap');
        
        body { transition: background-color 0.3s; font-feature-settings: "cv11", "ss01"; }
        
        .nav-active {
            background-color: rgb(37 99 235 / 0.1);
            color: #2563eb !important;
            border-left: 4px solid #2563eb;
        }
        
        .dark .nav-active {
            background-color: rgb(59 130 246 / 0.1);
            color: #60a5fa !important;
            border-left: 4px solid #3b82f6;
        }

        /* Mobile Sidebar Slide */
        #mobile-sidebar { transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        .sidebar-open { transform: translateX(0) !important; }
        
        #content-area { transition: opacity 0.2s ease; }
        .loading-blur { opacity: 0.5; pointer-events: none; }

        @keyframes pulse-soft {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .animate-pulse-soft { animation: pulse-soft 2s infinite; }
    </style>
</head>
<body class="bg-slate-100 dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex min-h-screen overflow-x-hidden">

    <aside class="w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 hidden md:flex flex-col sticky top-0 h-screen z-40">
        <div class="p-6 border-b border-slate-50 dark:border-slate-800">
            <div class="flex items-center gap-3">
                <div class="bg-blue-600 p-2 rounded-xl shadow-lg shadow-blue-500/20">
                    <i data-lucide="zap" class="text-white w-5 h-5 fill-current"></i>
                </div>
                <span class="text-lg font-black tracking-tighter uppercase italic text-slate-900 dark:text-white">Zappr<span class="text-blue-600">.dev</span></span>
            </div>
        </div>

        <nav class="flex-grow p-4 space-y-1">
            <a href="javascript:void(0)" onclick="loadPage('dashboard_home')" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all group" data-page="dashboard_home">
                <i data-lucide="layout-grid" class="w-4 h-4"></i> Dashboard
            </a>
            <a href="javascript:void(0)" onclick="loadPage('assigned-tickets')" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all group" data-page="assigned-tickets">
                <i data-lucide="terminal" class="w-4 h-4"></i> My Tasks
                <?php if($active_tasks > 0): ?>
                    <span class="ml-auto bg-blue-600 text-white text-[10px] font-black px-2 py-0.5 rounded-md"><?php echo $active_tasks; ?></span>
                <?php endif; ?>
            </a>
            <a href="javascript:void(0)" onclick="loadPage('view-tickets')" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all group" data-page="view-tickets">
                <i data-lucide="database" class="w-4 h-4"></i> Master Logs
            </a>
        </nav>

        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            <button onclick="toggleTheme()" class="w-full flex items-center gap-3 px-4 py-2 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all text-sm font-bold">
                <i data-lucide="moon" class="w-4 h-4 dark:hidden"></i>
                <i data-lucide="sun" class="w-4 h-4 hidden dark:block"></i>
                <span>Switch Theme</span>
            </button>
            <a href="../../scripts/logout.php" class="flex items-center gap-3 px-4 py-2 mt-2 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/10 rounded-xl transition-all text-sm font-bold">
                <i data-lucide="power" class="w-4 h-4"></i> Sign Out
            </a>
        </div>
    </aside>

    <div id="mobile-overlay" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[60] hidden md:hidden"></div>
    <aside id="mobile-sidebar" class="fixed top-0 left-0 h-full w-72 bg-white dark:bg-slate-900 z-[70] shadow-2xl -translate-x-full md:hidden flex flex-col">
        <div class="p-6 flex items-center justify-between border-b border-slate-100 dark:border-slate-800">
            <span class="font-black italic uppercase">Zappr<span class="text-blue-600">.dev</span></span>
            <button onclick="toggleMobileMenu()" class="p-2 bg-slate-100 dark:bg-slate-800 rounded-lg"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <nav class="p-4 space-y-2 flex-grow">
            <a href="javascript:void(0)" onclick="loadPage('dashboard_home'); toggleMobileMenu();" class="flex items-center gap-3 px-4 py-4 rounded-xl font-bold bg-slate-50 dark:bg-slate-800/40"><i data-lucide="layout-grid" class="w-5 h-5"></i> Dashboard</a>
            <a href="javascript:void(0)" onclick="loadPage('assigned-tickets'); toggleMobileMenu();" class="flex items-center gap-3 px-4 py-4 rounded-xl font-bold"><i data-lucide="terminal" class="w-5 h-5"></i> My Tasks</a>
            <a href="javascript:void(0)" onclick="loadPage('view-tickets'); toggleMobileMenu();" class="flex items-center gap-3 px-4 py-4 rounded-xl font-bold"><i data-lucide="database" class="w-5 h-5"></i> Logs</a>
        </nav>
    </aside>

    <main class="flex-grow flex flex-col min-w-0 md:m-3 md:rounded-[2.5rem] bg-white dark:bg-slate-900 shadow-2xl border-slate-200 dark:border-slate-800 overflow-hidden relative">
        
        <header class="h-16 md:h-20 px-6 md:px-10 flex items-center justify-between border-b border-slate-50 dark:border-slate-800/50 bg-white/40 dark:bg-slate-900/40 backdrop-blur-xl sticky top-0 z-10">
            <div class="flex items-center gap-4">
                <button onclick="toggleMobileMenu()" class="md:hidden p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <h2 id="page-title" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Environment</h2>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="hidden sm:flex flex-col items-end">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest"><?php echo date('D, M j'); ?></span>
                    <span class="text-xs font-mono font-bold text-blue-600 dark:text-blue-400"><?php echo date('H:i'); ?> UTC</span>
                </div>
                <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-blue-600 font-bold border border-slate-200 dark:border-slate-700">
                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                </div>
            </div>
        </header>

        <div id="content-area" class="flex-grow overflow-y-auto p-4 md:p-8 lg:p-12 pb-24 md:pb-8">
            <div class="flex flex-col items-center justify-center h-full py-40">
                <div class="w-12 h-12 border-4 border-blue-600/20 border-t-blue-600 rounded-full animate-spin"></div>
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 mt-6">Loading Kernel...</p>
            </div>
        </div>

        <div class="fixed bottom-0 left-0 w-full bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border-t border-slate-200 dark:border-slate-800 px-8 py-3 flex justify-between items-center md:hidden z-50">
            <button onclick="loadPage('dashboard_home')" class="flex flex-col items-center gap-1 text-slate-400 hover:text-blue-600">
                <i data-lucide="layout-grid" class="w-5 h-5"></i>
                <span class="text-[9px] font-black uppercase tracking-widest">Home</span>
            </button>
            <button onclick="loadPage('assigned-tickets')" class="flex flex-col items-center gap-1 text-slate-400 hover:text-blue-600 relative">
                <i data-lucide="terminal" class="w-5 h-5"></i>
                <?php if($active_tasks > 0): ?>
                    <span class="absolute -top-1 -right-1 w-2 h-2 bg-blue-600 rounded-full animate-pulse"></span>
                <?php endif; ?>
                <span class="text-[9px] font-black uppercase tracking-widest">Tasks</span>
            </button>
            <button onclick="loadPage('view-tickets')" class="flex flex-col items-center gap-1 text-slate-400 hover:text-blue-600">
                <i data-lucide="database" class="w-5 h-5"></i>
                <span class="text-[9px] font-black uppercase tracking-widest">Logs</span>
            </button>
            <button onclick="toggleTheme()" class="flex flex-col items-center gap-1 text-slate-400">
                <i data-lucide="sun-moon" class="w-5 h-5"></i>
                <span class="text-[9px] font-black uppercase tracking-widest">Mode</span>
            </button>
        </div>
    </main>

    <script>
    function toggleMobileMenu() {
        const isOpening = !$("#mobile-sidebar").hasClass("sidebar-open");
        $("#mobile-sidebar").toggleClass("sidebar-open");
        if (isOpening) $("#mobile-overlay").fadeIn(300);
        else $("#mobile-overlay").fadeOut(300);
    }

    $("#mobile-overlay").on('click', toggleMobileMenu);

    function loadPage(pagePath) {
        if(!pagePath) return;
        const contentArea = $("#content-area");
        contentArea.addClass("loading-blur");

        $.ajax({
            url: pagePath + ".php",
            method: "GET",
            success: function(data) {
                setTimeout(() => {
                    window.location.hash = pagePath;
                    contentArea.html(data).removeClass("loading-blur");
                    $("#page-title").text(pagePath.replace(/_/g, ' '));
                    if (window.lucide) lucide.createIcons();
                    
                    $(".nav-link").removeClass("nav-active");
                    $(`.nav-link[data-page='${pagePath}']`).addClass("nav-active");
                }, 100); 
            },
            error: function() {
                contentArea.removeClass("loading-blur").html(`<div class="text-center py-20 text-rose-500 font-bold uppercase tracking-widest">Kernel Access Denied</div>`);
            }
        });
    }

    function toggleTheme() {
        const isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
    }

    $(document).ready(function() {
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
        const lastPage = window.location.hash.replace('#', '') || 'dashboard_home';
        loadPage(lastPage);
        if (window.lucide) lucide.createIcons();
    });
    </script>
</body>
</html>