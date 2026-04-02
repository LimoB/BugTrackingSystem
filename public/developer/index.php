<?php
session_start();

/**
 * File: developer/index.php
 * Purpose: Professional UX-focused Dashboard for Developers.
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

// 📊 Notification/Badge Logic
$stmt_count = $connection->prepare("SELECT COUNT(*) as count FROM Tickets WHERE assigned_to = ? AND status NOT IN ('resolved', 'closed')");
$stmt_count->bind_param("i", $user_id);
$stmt_count->execute();
$active_tasks = $stmt_count->get_result()->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workspace | Zappr Dashboard</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'Plus Jakarta Sans', 'sans-serif'] },
                }
            }
        }
    </script>
    
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap');
        
        .nav-active {
            background-color: #eff6ff;
            color: #2563eb !important;
            border-right: 3px solid #2563eb;
        }
        
        .dark .nav-active {
            background-color: #1e293b;
            color: #60a5fa !important;
            border-right: 3px solid #3b82f6;
        }
        
        #content-area { transition: all 0.2s ease; }
        .loading-blur { filter: blur(5px); opacity: 0.4; pointer-events: none; }
        
        body { font-feature-settings: "cv11", "ss01"; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex min-h-screen">

    <aside class="w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 hidden md:flex flex-col sticky top-0 h-screen z-40">
        <div class="p-6">
            <div class="flex items-center gap-2 mb-8">
                <div class="bg-blue-600 p-2 rounded-lg shadow-lg">
                    <i data-lucide="zap" class="text-white w-5 h-5 fill-current"></i>
                </div>
                <span class="text-xl font-bold tracking-tight">Zappr<span class="text-blue-600">.</span></span>
            </div>

            <nav class="space-y-1">
                <p class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-3">Main Menu</p>
                
                <a href="javascript:void(0)" onclick="loadPage('dashboard_home')" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all group" data-page="dashboard_home">
                    <i data-lucide="home" class="w-4 h-4"></i> Dashboard
                </a>

                <a href="javascript:void(0)" onclick="loadPage('assigned-tickets')" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all group" data-page="assigned-tickets">
                    <i data-lucide="check-circle" class="w-4 h-4"></i> My Tasks
                    <?php if($active_tasks > 0): ?>
                        <span class="ml-auto bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 text-[10px] font-bold px-2 py-0.5 rounded-full"><?php echo $active_tasks; ?></span>
                    <?php endif; ?>
                </a>

                <a href="javascript:void(0)" onclick="loadPage('create-ticket')" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all group" data-page="create-ticket">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i> New Ticket
                </a>

                <div class="pt-6">
                    <p class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-3">Resources</p>
                    <a href="javascript:void(0)" onclick="loadPage('view-tickets')" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all group" data-page="view-tickets">
                        <i data-lucide="search" class="w-4 h-4"></i> Explore All
                    </a>
                </div>
            </nav>
        </div>

        <div class="p-4 mt-auto border-t border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-3 p-2">
                <div class="w-9 h-9 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center text-slate-700 dark:text-slate-300 font-bold border border-white dark:border-slate-700 shadow-sm">
                    <?php echo strtoupper(substr($user['name'] ?? 'U', 0, 1)); ?>
                </div>
                <div class="truncate">
                    <p class="text-sm font-bold truncate"><?php echo htmlspecialchars($user['name']); ?></p>
                    <p class="text-[10px] text-slate-400 font-medium">Professional Plan</p>
                </div>
            </div>
            
            <div class="flex gap-2 mt-4">
                <button onclick="toggleTheme()" class="flex-grow flex items-center justify-center p-2.5 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 rounded-xl transition-all border border-slate-200 dark:border-slate-700">
                    <i data-lucide="sun" class="w-4 h-4 hidden dark:block"></i>
                    <i data-lucide="moon" class="w-4 h-4 dark:hidden"></i>
                </button>
                <a href="../../scripts/logout.php" class="flex-grow flex items-center justify-center p-2.5 bg-rose-50 text-rose-500 hover:bg-rose-100 rounded-xl transition-all border border-rose-100">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                </a>
            </div>
        </div>
    </aside>

    <main class="flex-grow flex flex-col min-w-0 bg-white dark:bg-slate-950 md:m-2 md:rounded-3xl md:shadow-2xl border border-transparent md:border-slate-200 md:dark:border-slate-800 overflow-hidden">
        
        <header class="h-16 px-8 flex items-center justify-between border-b border-slate-100 dark:border-slate-800 bg-white/50 dark:bg-slate-900/50 backdrop-blur-md">
            <h2 id="page-title" class="text-sm font-bold text-slate-400 capitalize">Workspace Overview</h2>
            
            <div class="flex items-center gap-4 text-xs font-medium text-slate-500">
                <span class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Systems Operational
                </span>
            </div>
        </header>

        <div id="content-area" class="flex-grow overflow-y-auto p-6 lg:p-10">
            <div class="flex flex-col items-center justify-center h-full text-center">
                <div class="w-10 h-10 border-2 border-blue-600 border-t-transparent rounded-full animate-spin mb-4"></div>
                <p class="text-sm font-medium text-slate-400">Opening your workspace...</p>
            </div>
        </div>
    </main>

    <script>
    /**
     * Navigation Controller
     */
    function loadPage(page, params = "") {
        const contentArea = $("#content-area");
        const titleArea = $("#page-title");
        
        contentArea.addClass("loading-blur");
        
        // Relative path to developer directory
        let url = page + ".php";
        if (params) url += "?" + params;

        $.ajax({
            url: url,
            method: "GET",
            success: function(data) {
                setTimeout(() => {
                    contentArea.html(data).removeClass("loading-blur");
                    
                    // Human-friendly title formatting
                    let friendlyTitle = page.replace(/-/g, ' ').replace('dashboard_home', 'Overview');
                    titleArea.text(friendlyTitle);
                    
                    if (window.lucide) lucide.createIcons();
                    
                    // Update Sidebar
                    $(".nav-link").removeClass("nav-active");
                    $(`.nav-link[data-page='${page}']`).addClass("nav-active");
                    
                    localStorage.setItem('last_view', page);
                }, 100); 
            },
            error: function() {
                contentArea.removeClass("loading-blur").html(`
                    <div class="flex flex-col items-center justify-center py-20">
                        <div class="bg-rose-50 p-4 rounded-full mb-4">
                            <i data-lucide="alert-circle" class="w-8 h-8 text-rose-500"></i>
                        </div>
                        <h3 class="text-lg font-bold">Page not found</h3>
                        <p class="text-slate-500 text-sm mt-1">We couldn't reach the module: ${page}</p>
                        <button onclick="loadPage('dashboard_home')" class="mt-6 px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold">Go Home</button>
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
        if (localStorage.getItem('theme') === 'dark') document.documentElement.classList.add('dark');
        
        // Default to dashboard_home
        const lastPage = localStorage.getItem('last_view') || 'dashboard_home';
        loadPage(lastPage);
        
        if (window.lucide) lucide.createIcons();
    });
    </script>
</body>
</html>