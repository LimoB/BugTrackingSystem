<?php
session_start();

/**
 * File: developer/index.php
 * Purpose: Professional High-Performance Dashboard for Developers.
 */

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

// 📊 Notification/Badge Logic (Live Task Count)
$stmt_count = $connection->prepare("SELECT COUNT(*) as count FROM Tickets WHERE assigned_to = ? AND status NOT IN ('resolved', 'closed')");
$stmt_count->bind_param("i", $user_id);
$stmt_count->execute();
$active_tasks = $stmt_count->get_result()->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        
        /* Custom Scrollbar - Minimalist */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #3b82f6; border-radius: 10px; }

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
        
        #content-area { transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); }
        .loading-blur { filter: blur(8px); opacity: 0.5; pointer-events: none; }

        /* Status Pulse Animation */
        .pulse-green {
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
    </style>
</head>
<body class="bg-slate-100 dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex min-h-screen">

    <aside class="w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 hidden md:flex flex-col sticky top-0 h-screen z-40">
        <div class="p-6 border-b border-slate-50 dark:border-slate-800">
            <div class="flex items-center gap-3">
                <div class="bg-blue-600 p-2 rounded-xl shadow-lg shadow-blue-500/20">
                    <i data-lucide="zap" class="text-white w-5 h-5 fill-current"></i>
                </div>
                <div class="flex flex-col">
                    <span class="text-lg font-black tracking-tighter uppercase italic">Zappr<span class="text-blue-600">.dev</span></span>
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none">Internal Terminal</span>
                </div>
            </div>
        </div>

        <nav class="flex-grow p-4 space-y-1 overflow-y-auto">
            <div class="px-3 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-4 mt-2">Navigation</div>
            
            <a href="javascript:void(0)" onclick="loadPage('dashboard_home')" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all group" data-page="dashboard_home">
                <i data-lucide="layout-grid" class="w-4 h-4 group-hover:text-blue-500 transition-colors"></i> Dashboard
            </a>

            <a href="javascript:void(0)" onclick="loadPage('assigned-tickets')" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all group" data-page="assigned-tickets">
                <i data-lucide="terminal" class="w-4 h-4 group-hover:text-blue-500 transition-colors"></i> My Tasks
                <?php if($active_tasks > 0): ?>
                    <span class="ml-auto bg-blue-600 text-white text-[10px] font-black px-2 py-0.5 rounded-md"><?php echo $active_tasks; ?></span>
                <?php endif; ?>
            </a>

            <div class="px-3 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-4 mt-8">System Explorer</div>

            <a href="javascript:void(0)" onclick="loadPage('view-tickets')" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all group" data-page="view-tickets">
                <i data-lucide="database" class="w-4 h-4 group-hover:text-blue-500 transition-colors"></i> Master Logs
            </a>

            <a href="javascript:void(0)" onclick="loadPage('create-ticket')" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all group" data-page="create-ticket">
                <i data-lucide="plus-square" class="w-4 h-4 group-hover:text-blue-500 transition-colors"></i> Debug Entry
            </a>
        </nav>

        <div class="p-4 border-t border-slate-100 dark:border-slate-800 space-y-3">
            <div class="flex items-center gap-3 px-2 py-2 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-800">
                <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center text-white font-black text-sm shadow-lg shadow-blue-500/20">
                    <?php echo strtoupper(substr($user['name'] ?? 'D', 0, 1)); ?>
                </div>
                <div class="truncate">
                    <p class="text-xs font-black truncate text-slate-800 dark:text-white"><?php echo htmlspecialchars($user['name']); ?></p>
                    <p class="text-[9px] text-blue-500 font-bold uppercase tracking-widest">Developer Mode</p>
                </div>
            </div>
            
            <div class="flex gap-2">
                <button onclick="toggleTheme()" class="flex-1 flex items-center justify-center p-3 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-xl transition-all border border-slate-200 dark:border-slate-800 shadow-sm">
                    <i data-lucide="sun" class="w-4 h-4 hidden dark:block text-amber-400"></i>
                    <i data-lucide="moon" class="w-4 h-4 dark:hidden text-blue-600"></i>
                </button>
                <a href="../../scripts/logout.php" class="flex-1 flex items-center justify-center p-3 bg-rose-50 dark:bg-rose-900/10 text-rose-500 hover:bg-rose-500 hover:text-white rounded-xl transition-all border border-rose-100 dark:border-rose-900/30 shadow-sm">
                    <i data-lucide="power" class="w-4 h-4"></i>
                </a>
            </div>
        </div>
    </aside>

    <main class="flex-grow flex flex-col min-w-0 md:m-3 md:rounded-[2.5rem] bg-white dark:bg-slate-900 shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        
        <header class="h-20 px-10 flex items-center justify-between border-b border-slate-50 dark:border-slate-800/50 bg-white/40 dark:bg-slate-900/40 backdrop-blur-xl sticky top-0 z-10">
            <div class="flex items-center gap-4">
                <div class="hidden lg:flex items-center gap-2 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-900/30 rounded-full">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 pulse-green"></span>
                    <span class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Core Engine Active</span>
                </div>
                <h2 id="page-title" class="text-xs font-black text-slate-400 uppercase tracking-[0.3em] ml-2">Loading...</h2>
            </div>
            
            <div class="flex items-center gap-8">
                <div class="hidden sm:flex flex-col items-end">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Session Time</span>
                    <span class="text-xs font-mono font-bold text-slate-700 dark:text-slate-300"><?php echo date('H:i'); ?> UTC</span>
                </div>
                <button onclick="loadPage('profile')" class="group p-2 rounded-full hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                     <i data-lucide="settings" class="w-5 h-5 text-slate-400 group-hover:rotate-90 group-hover:text-blue-500 transition-all duration-500"></i>
                </button>
            </div>
        </header>

        <div id="content-area" class="flex-grow overflow-y-auto p-8 lg:p-12">
            <div class="flex flex-col items-center justify-center h-full py-40">
                <div class="relative">
                    <div class="w-16 h-16 border-4 border-blue-600/20 border-t-blue-600 rounded-full animate-spin"></div>
                    <i data-lucide="zap" class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-6 h-6 text-blue-600"></i>
                </div>
                <p class="text-[11px] font-black uppercase tracking-[0.4em] text-slate-400 mt-8 animate-pulse">Initializing Dev Environment...</p>
            </div>
        </div>
    </main>

    <script>
    /**
     * AJAX Router
     */
    function loadPage(pagePath) {
        if(!pagePath) return;
        
        const contentArea = $("#content-area");
        const titleArea = $("#page-title");
        
        contentArea.addClass("loading-blur");

        let parts = pagePath.split('&');
        let fileName = parts[0]; 
        let queryString = parts.slice(1).join('&');

        let finalUrl = fileName + ".php";
        if (queryString) {
            finalUrl += "?" + queryString;
        }

        $.ajax({
            url: finalUrl,
            method: "GET",
            success: function(data) {
                setTimeout(() => {
                    window.location.hash = pagePath;
                    contentArea.html(data).removeClass("loading-blur");
                    
                    let friendlyTitle = fileName.replace(/-/g, ' ').replace('dashboard_home', 'Global Overview');
                    titleArea.text(friendlyTitle);
                    
                    if (window.lucide) lucide.createIcons();
                    
                    $(".nav-link").removeClass("nav-active");
                    $(`.nav-link[data-page='${fileName}']`).addClass("nav-active");
                    
                    localStorage.setItem('last_dev_view', pagePath);
                }, 150); 
            },
            error: function() {
                contentArea.removeClass("loading-blur").html(`
                    <div class="flex flex-col items-center justify-center py-32 text-center">
                        <div class="w-20 h-20 bg-rose-50 dark:bg-rose-900/10 rounded-3xl flex items-center justify-center mb-6">
                            <i data-lucide="terminal" class="w-10 h-10 text-rose-500"></i>
                        </div>
                        <h3 class="text-2xl font-black uppercase tracking-tighter">Stack Trace Error</h3>
                        <p class="text-slate-500 font-medium italic mt-2">Failed to load module: ${finalUrl}</p>
                        <button onclick="loadPage('dashboard_home')" class="mt-8 px-10 py-4 bg-slate-900 dark:bg-white dark:text-slate-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] shadow-xl active:scale-95 transition-all">
                            Reboot Workspace
                        </button>
                    </div>
                `);
                if (window.lucide) lucide.createIcons();
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
        
        const lastPage = window.location.hash.replace('#', '') || localStorage.getItem('last_dev_view') || 'dashboard_home';
        loadPage(lastPage);
        
        if (window.lucide) lucide.createIcons();
    });
    </script>
</body>
</html>