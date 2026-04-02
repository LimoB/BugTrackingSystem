<?php
/**
 * File: user/index.php
 * Purpose: High-Performance Client/User Portal with Full Mobile Support.
 */
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
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
        body { transition: background-color 0.3s; font-feature-settings: "cv11", "ss01"; }
        
        .nav-active {
            background-color: rgb(239 246 255); 
            color: rgb(37 99 235) !important; 
            border-right: 4px solid rgb(37 99 235);
        }
        .dark .nav-active {
            background-color: rgba(30, 58, 138, 0.2);
            color: rgb(96 165 250) !important; 
            border-right: 4px solid rgb(59 130 246);
        }

        /* Mobile Sidebar Slide */
        #mobile-sidebar { transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        .sidebar-open { transform: translateX(0) !important; }
        
        #content-area { transition: opacity 0.2s ease-in-out; }
        .loading { pointer-events: none; opacity: 0.5; }

        /* Smooth Profile Dropdown */
        .profile-dropdown {
            transform: translateY(10px);
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .group:hover .profile-dropdown {
            transform: translateY(0);
            opacity: 1;
            visibility: visible;
        }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex min-h-screen overflow-x-hidden">

    <aside class="w-64 border-r border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hidden md:flex flex-col sticky top-0 h-screen z-40">
        <div class="p-6 flex items-center gap-3 border-b border-slate-100 dark:border-slate-800">
            <div class="bg-blue-600 p-1.5 rounded-lg shadow-lg shadow-blue-500/20">
                <i data-lucide="zap" class="text-white w-5 h-5 fill-current"></i>
            </div>
            <span class="text-xl font-bold tracking-tight uppercase italic">Zappr<span class="text-blue-600">.</span></span>
        </div>

        <nav class="flex-grow p-4 space-y-1 mt-4">
            <a href="javascript:void(0)" onclick="loadPage('dashboard_home')" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition-all text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50" data-page="dashboard_home">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard
            </a>
            
            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-4 mt-8 mb-2">Support</div>
            
            <a href="javascript:void(0)" onclick="loadPage('view-tickets')" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition-all text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50" data-page="view-tickets">
                <i data-lucide="ticket" class="w-5 h-5"></i> My Tickets
            </a>
            <a href="javascript:void(0)" onclick="loadPage('create-ticket')" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition-all text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50" data-page="create-ticket">
                <i data-lucide="plus-circle" class="w-5 h-5"></i> Report Bug
            </a>
        </nav>

        <div class="p-4 border-t border-slate-100 dark:border-slate-800 space-y-2">
            <button id="theme-toggle-desktop" class="w-full flex items-center gap-3 px-4 py-2 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all text-sm font-bold theme-switcher">
                <i data-lucide="moon" class="w-4 h-4 dark:hidden"></i>
                <i data-lucide="sun" class="w-4 h-4 hidden dark:block"></i>
                <span>Switch Theme</span>
            </button>
        </div>
    </aside>

    <div id="mobile-overlay" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[60] hidden md:hidden"></div>
    <aside id="mobile-sidebar" class="fixed top-0 left-0 h-full w-72 bg-white dark:bg-slate-900 z-[70] shadow-2xl -translate-x-full md:hidden flex flex-col">
        <div class="p-6 flex items-center justify-between border-b border-slate-100 dark:border-slate-800">
            <span class="font-black italic uppercase">Zappr<span class="text-blue-600">.user</span></span>
            <button onclick="toggleMobileMenu()" class="p-2 bg-slate-100 dark:bg-slate-800 rounded-lg"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <nav class="p-4 space-y-2 flex-grow">
            <a href="javascript:void(0)" onclick="loadPage('dashboard_home'); toggleMobileMenu();" class="flex items-center gap-3 px-4 py-4 rounded-xl font-bold bg-slate-50 dark:bg-slate-800/40"><i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard</a>
            <a href="javascript:void(0)" onclick="loadPage('view-tickets'); toggleMobileMenu();" class="flex items-center gap-3 px-4 py-4 rounded-xl font-bold"><i data-lucide="ticket" class="w-5 h-5"></i> My Tickets</a>
            <a href="javascript:void(0)" onclick="loadPage('create-ticket'); toggleMobileMenu();" class="flex items-center gap-3 px-4 py-4 rounded-xl font-bold"><i data-lucide="plus-circle" class="w-5 h-5"></i> Report Bug</a>
        </nav>
    </aside>

    <main class="flex-grow flex flex-col min-w-0 bg-white dark:bg-slate-950 relative">
        
        <header class="h-16 md:h-20 border-b border-slate-100 dark:border-slate-800 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md flex items-center justify-between px-6 md:px-10 sticky top-0 z-50">
            <div class="flex items-center gap-4">
                <button onclick="toggleMobileMenu()" class="md:hidden p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <h2 id="page-title" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Client Portal</h2>
            </div>

            <div class="flex items-center gap-6 relative group">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-black text-slate-800 dark:text-slate-100"><?php echo htmlspecialchars($user_name); ?></p>
                    <p class="text-[10px] text-blue-500 font-bold uppercase tracking-widest">End User</p>
                </div>
                <div class="w-10 h-10 rounded-2xl bg-blue-600 flex items-center justify-center text-white font-black shadow-lg shadow-blue-500/20 group-hover:scale-105 transition-transform cursor-pointer">
                    <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                </div>

                <div class="profile-dropdown absolute right-0 top-full mt-2 w-56 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[1.5rem] shadow-2xl py-3 z-50">
                    <div class="px-5 py-2 border-b border-slate-50 dark:border-slate-800 mb-2">
                        <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest">Settings</p>
                    </div>
                    <a href="javascript:void(0)" onclick="loadPage('profile')" class="flex items-center gap-3 px-5 py-3 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-600 transition-all">
                        <i data-lucide="settings" class="w-4 h-4"></i> Account Info
                    </a>
                    <div class="h-px bg-slate-50 dark:bg-slate-800 my-2"></div>
                    <a href="../../scripts/logout.php" class="flex items-center gap-3 px-5 py-3 text-xs font-bold text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-all">
                        <i data-lucide="log-out" class="w-4 h-4"></i> Sign Out
                    </a>
                </div>
            </div>
        </header>

        <div id="content-area" class="p-4 md:p-8 lg:p-12 min-h-[calc(100vh-64px)] pb-24 md:pb-8">
            <div class="flex flex-col items-center justify-center py-40">
                <div class="w-10 h-10 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mt-6">Loading Portal Data...</p>
            </div>
        </div>

        <div class="fixed bottom-0 left-0 w-full bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl border-t border-slate-200 dark:border-slate-800 px-6 py-3 flex justify-between items-center md:hidden z-50">
            <button onclick="loadPage('dashboard_home')" class="flex flex-col items-center gap-1 text-slate-400 hover:text-blue-600">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                <span class="text-[8px] font-black uppercase tracking-widest">Home</span>
            </button>
            <button onclick="loadPage('view-tickets')" class="flex flex-col items-center gap-1 text-slate-400 hover:text-blue-600">
                <i data-lucide="ticket" class="w-5 h-5"></i>
                <span class="text-[8px] font-black uppercase tracking-widest">Tickets</span>
            </button>
            <button onclick="loadPage('create-ticket')" class="flex flex-col items-center gap-1 text-slate-400 hover:text-blue-600">
                <i data-lucide="plus-circle" class="w-5 h-5"></i>
                <span class="text-[8px] font-black uppercase tracking-widest">Report</span>
            </button>
            <button class="theme-switcher flex flex-col items-center gap-1 text-slate-400">
                <i data-lucide="sun-moon" class="w-5 h-5"></i>
                <span class="text-[8px] font-black uppercase tracking-widest">Mode</span>
            </button>
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

    $(".theme-switcher").on('click', function() {
        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('color-theme', 'light');
        } else {
            document.documentElement.classList.add('dark');
            localStorage.setItem('color-theme', 'dark');
        }
    });

    function toggleMobileMenu() {
        const isOpen = $("#mobile-sidebar").hasClass("sidebar-open");
        $("#mobile-sidebar").toggleClass("sidebar-open");
        if (!isOpen) $("#mobile-overlay").fadeIn(300);
        else $("#mobile-overlay").fadeOut(300);
    }

    $("#mobile-overlay").on('click', toggleMobileMenu);

    // Optimized AJAX Router
    function loadPage(page) {
        if(!page) return;
        const contentArea = $("#content-area");
        contentArea.addClass("loading");

        $.ajax({
            url: page + ".php",
            method: "GET",
            success: function (data) {
                setTimeout(() => {
                    window.location.hash = page;
                    contentArea.html(data).removeClass("loading");
                    if (window.lucide) lucide.createIcons();
                    
                    $(".nav-link").removeClass("nav-active");
                    $(`.nav-link[data-page='${page}']`).addClass("nav-active");
                    $("#page-title").text(page.replace(/-/g, ' '));
                }, 100);
            },
            error: function () {
                contentArea.removeClass("loading").html(`<div class="text-center py-20 text-rose-500 font-bold uppercase tracking-widest">Portal Access Error</div>`);
                if (window.lucide) lucide.createIcons();
            }
        });
    }

    $(document).ready(function () {
        initTheme();
        
        const lastView = window.location.hash.replace('#', '') || 'dashboard_home';
        loadPage(lastView);
        
        if (window.lucide) lucide.createIcons();
    });
</script>
</body>
</html>