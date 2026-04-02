<?php
session_start();

// ✅ Strict User Check
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'user') {
    header("Location: ../login/index.php");
    exit();
}

$user_name = $_SESSION['name'] ?? 'Client';
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Portal | Zappr</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                }
            }
        }
    </script>
    
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { transition: background-color 0.3s; }
        
        /* Custom Scrollbar for Kali environment look */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .dark ::-webkit-scrollbar-thumb { background: #334155; }

        .nav-active {
            background-color: rgb(239 246 255); 
            color: rgb(37 99 235); 
            border-right: 4px solid rgb(37 99 235);
        }
        .dark .nav-active {
            background-color: rgba(30, 58, 138, 0.2);
            color: rgb(96 165 250); 
            border-right: 4px solid rgb(59 130 246);
        }
        #content-area { transition: opacity 0.2s ease-in-out; }
        .loading { pointer-events: none; cursor: wait; }

        /* Dropdown Animation */
        .profile-dropdown {
            transform: translateY(10px);
            transition: all 0.2s ease-out;
        }
        .group:hover .profile-dropdown {
            transform: translateY(0);
            opacity: 1;
            visibility: visible;
        }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex min-h-screen">

    <aside class="w-64 border-r border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hidden md:flex flex-col sticky top-0 h-screen z-20">
        <div class="p-6 flex items-center gap-3 border-b border-slate-100 dark:border-slate-800">
            <div class="bg-blue-600 p-1.5 rounded-lg shadow-lg shadow-blue-200 dark:shadow-none">
                <i data-lucide="zap" class="text-white w-5 h-5"></i>
            </div>
            <span class="text-xl font-bold tracking-tight">Zappr<span class="text-blue-600">User</span></span>
        </div>

        <nav class="flex-grow p-4 space-y-1 mt-4">
            <a href="#dashboard_home" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition-all text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50" data-page="dashboard_home">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard
            </a>
            
            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-4 mt-6 mb-2">Support</div>
            
            <a href="#view-tickets" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50" data-page="view-tickets">
                <i data-lucide="ticket" class="w-5 h-5"></i> My Tickets
            </a>
            <a href="#create-ticket" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50" data-page="create-ticket">
                <i data-lucide="plus-circle" class="w-5 h-5"></i> Report Bug
            </a>
        </nav>

        <div class="p-4 border-t border-slate-100 dark:border-slate-800 space-y-2">
            <button id="theme-toggle" class="w-full flex items-center gap-3 px-4 py-2 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all text-sm font-bold">
                <i data-lucide="moon" class="w-4 h-4 dark:hidden"></i>
                <i data-lucide="sun" class="w-4 h-4 hidden dark:block"></i>
                <span class="dark:hidden">Dark Mode</span>
                <span class="hidden dark:block">Light Mode</span>
            </button>
        </div>
    </aside>

    <main class="flex-grow">
        <header class="h-16 border-b border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md flex items-center justify-between px-8 sticky top-0 z-10">
            <div class="text-sm font-medium text-slate-500 dark:text-slate-400">
                Portal: <span class="text-blue-500 font-bold italic">External Access</span>
            </div>

            <div class="flex items-center gap-4 relative group cursor-pointer">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-bold leading-none text-slate-800 dark:text-slate-100"><?php echo htmlspecialchars($user_name); ?></p>
                    <p class="text-[10px] text-slate-400 font-medium mt-1 uppercase tracking-widest">End User</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center text-white text-sm font-bold shadow-lg shadow-blue-200 dark:shadow-none transition-transform group-hover:scale-105">
                    <?php echo strtoupper(substr($user_name, 0, 2)); ?>
                </div>

                <div class="profile-dropdown absolute right-0 top-full mt-2 w-52 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl py-3 opacity-0 invisible z-50 overflow-hidden">
                    <div class="px-4 py-2 border-b border-slate-50 dark:border-slate-800 mb-2">
                        <p class="text-[9px] font-black uppercase text-slate-400 tracking-[0.2em]">Account Management</p>
                    </div>
                    
                    <a href="#profile" onclick="loadPage('profile')" class="flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-600 transition-colors">
                        <i data-lucide="user-cog" class="w-4 h-4"></i> My Profile
                    </a>
                    
                    <div class="border-t border-slate-50 dark:border-slate-800 my-2"></div>
                    
                    <a href="../../scripts/logout.php" class="flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-colors">
                        <i data-lucide="log-out" class="w-4 h-4"></i> Sign Out
                    </a>
                </div>
            </div>
        </header>

        <div id="content-area" class="p-8 lg:p-12 min-h-[calc(100vh-64px)]">
            <div class="flex items-center justify-center py-32">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            </div>
        </div>
    </main>

<script>
    // Theme Logic
    function initTheme() {
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }

    $("#theme-toggle").on('click', function() {
        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('color-theme', 'light');
        } else {
            document.documentElement.classList.add('dark');
            localStorage.setItem('color-theme', 'dark');
        }
    });

    // FIXED AJAX Router
    function loadPage(pagePath) {
        if(!pagePath) return;
        
        const contentArea = $("#content-area");
        contentArea.addClass("loading").css('opacity', '0.4');

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
            success: function (data) {
                window.location.hash = pagePath;
                contentArea.html(data).css('opacity', '1').removeClass("loading");
                if (window.lucide) lucide.createIcons();
                
                // Update Sidebar States
                $(".nav-link").removeClass("nav-active");
                $(`.nav-link[data-page='${fileName}']`).addClass("nav-active");
            },
            error: function () {
                contentArea.css('opacity', '1').removeClass("loading");
                contentArea.html(`<div class="flex flex-col items-center justify-center py-20 text-rose-500 font-black uppercase tracking-widest gap-4">
                    <i data-lucide="alert-circle" class="w-12 h-12"></i> Error: Protocol Unreachable
                </div>`);
                if (window.lucide) lucide.createIcons();
            }
        });
    }

    $(document).ready(function () {
        initTheme();
        lucide.createIcons();

        $(".nav-link").on('click', function (e) {
            e.preventDefault();
            loadPage($(this).attr("data-page"));
        });

        const currentHash = window.location.hash.replace('#', '');
        loadPage(currentHash || 'dashboard_home');
    });
</script>
</body>
</html>