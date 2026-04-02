<?php
/**
 * File: public/user/index.php
 * Purpose: Professional User Dashboard with full Mobile Responsiveness.
 */
session_start();

// ✅ Strict User Check
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'user') {
    header("Location: ../login/index.php");
    exit();
}

$user_name = $_SESSION['name'] ?? 'Client';
$base_url = "/php-bugtracking-system/";
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Portal | Zappr</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; transition: background-color 0.3s; }
        
        /* Sidebar active state */
        .nav-active {
            background-color: rgba(37, 99, 235, 0.1); 
            color: #2563eb !important; 
            border-right: 4px solid #2563eb;
        }
        .dark .nav-active {
            background-color: rgba(59, 130, 246, 0.1);
            color: #60a5fa !important; 
            border-right: 4px solid #3b82f6;
        }

        /* Smooth slide for mobile menu */
        #mobile-sidebar { transition: transform 0.3s ease-in-out; }
        .sidebar-open { transform: translateX(0) !important; }

        /* Hide scrollbar but keep functionality */
        .no-scrollbar::-webkit-scrollbar { display: none; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex min-h-screen overflow-x-hidden">

    <aside class="w-64 border-r border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hidden md:flex flex-col sticky top-0 h-screen z-40">
        <div class="p-6 flex items-center gap-3 border-b border-slate-100 dark:border-slate-800">
            <div class="bg-blue-600 p-1.5 rounded-lg">
                <i data-lucide="zap" class="text-white w-5 h-5"></i>
            </div>
            <span class="text-xl font-bold tracking-tight">Zappr<span class="text-blue-600 italic">User</span></span>
        </div>

        <nav class="flex-grow p-4 space-y-2 mt-4">
            <a href="#dashboard_home" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition-all text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50" data-page="dashboard_home">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard
            </a>
            <a href="#view-tickets" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50" data-page="view-tickets">
                <i data-lucide="ticket" class="w-5 h-5"></i> My Tickets
            </a>
            <a href="#create-ticket" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50" data-page="create-ticket">
                <i data-lucide="plus-circle" class="w-5 h-5"></i> Report Bug
            </a>
        </nav>

        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            <button onclick="toggleTheme()" class="w-full flex items-center gap-3 px-4 py-2 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all text-sm font-bold">
                <i data-lucide="moon" class="w-4 h-4 dark:hidden"></i>
                <i data-lucide="sun" class="w-4 h-4 hidden dark:block"></i>
                <span>Toggle Theme</span>
            </button>
        </div>
    </aside>

    <div id="mobile-sidebar-overlay" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[60] hidden md:hidden"></div>
    <aside id="mobile-sidebar" class="fixed top-0 left-0 h-full w-72 bg-white dark:bg-slate-900 z-[70] shadow-2xl -translate-x-full md:hidden flex flex-col">
        <div class="p-6 flex items-center justify-between border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-3">
                <div class="bg-blue-600 p-1.5 rounded-lg"><i data-lucide="zap" class="text-white w-4 h-4"></i></div>
                <span class="font-bold">Zappr</span>
            </div>
            <button onclick="closeMobileMenu()" class="p-2 bg-slate-50 dark:bg-slate-800 rounded-lg">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <nav class="p-4 space-y-2 flex-grow">
            <a href="#dashboard_home" onclick="closeMobileMenu()" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-bold" data-page="dashboard_home">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard
            </a>
            <a href="#view-tickets" onclick="closeMobileMenu()" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-medium" data-page="view-tickets">
                <i data-lucide="ticket" class="w-5 h-5"></i> My Tickets
            </a>
            <a href="#create-ticket" onclick="closeMobileMenu()" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-medium" data-page="create-ticket">
                <i data-lucide="plus-circle" class="w-5 h-5"></i> Report Bug
            </a>
        </nav>
    </aside>

    <main class="flex-grow flex flex-col min-w-0">
        
        <header class="h-16 border-b border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md flex items-center justify-between px-4 md:px-8 sticky top-0 z-50">
            <div class="flex items-center gap-4">
                <button onclick="openMobileMenu()" class="md:hidden p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <div class="text-xs font-medium text-slate-500 hidden sm:block">
                    Portal: <span class="text-blue-500 font-bold">User Access</span>
                </div>
            </div>

            <div class="flex items-center gap-3 relative group cursor-pointer">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-bold leading-none"><?php echo htmlspecialchars($user_name); ?></p>
                    <p class="text-[9px] text-slate-400 uppercase tracking-widest mt-1">Client Account</p>
                </div>
                <div class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center text-white text-xs font-bold shadow-lg shadow-blue-200 dark:shadow-none">
                    <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                </div>

                <div class="absolute right-0 top-full mt-2 w-48 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50">
                    <a href="#profile" onclick="loadPage('profile')" class="flex items-center gap-2 px-4 py-2.5 text-xs font-bold hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                        <i data-lucide="user" class="w-4 h-4 text-blue-500"></i> My Profile
                    </a>
                    <div class="border-t border-slate-50 dark:border-slate-800 my-1"></div>
                    <a href="../../scripts/logout.php" class="flex items-center gap-2 px-4 py-2.5 text-xs font-bold text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-colors">
                        <i data-lucide="log-out" class="w-4 h-4"></i> Sign Out
                    </a>
                </div>
            </div>
        </header>

        <div id="content-area" class="p-4 md:p-8 lg:p-12 min-h-[calc(100vh-64px)] pb-24 md:pb-8">
            <div class="flex items-center justify-center py-32">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            </div>
        </div>
    </main>

    <div class="fixed bottom-0 left-0 w-full bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 px-6 py-3 flex justify-between items-center md:hidden z-50 shadow-[0_-4px_20px_rgba(0,0,0,0.05)]">
        <button onclick="loadPage('dashboard_home')" class="flex flex-col items-center gap-1 text-slate-400 active:text-blue-600">
            <i data-lucide="layout-dashboard" class="w-6 h-6"></i>
            <span class="text-[9px] font-bold uppercase tracking-widest">Home</span>
        </button>
        <button onclick="loadPage('view-tickets')" class="flex flex-col items-center gap-1 text-slate-400 active:text-blue-600">
            <i data-lucide="ticket" class="w-6 h-6"></i>
            <span class="text-[9px] font-bold uppercase tracking-widest">Tickets</span>
        </button>
        <button onclick="loadPage('create-ticket')" class="flex flex-col items-center gap-1 text-slate-400 active:text-blue-600">
            <i data-lucide="plus-circle" class="w-6 h-6"></i>
            <span class="text-[9px] font-bold uppercase tracking-widest">Report</span>
        </button>
    </div>

<script>
    // Theme Management
    function toggleTheme() {
        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('color-theme', 'light');
        } else {
            document.documentElement.classList.add('dark');
            localStorage.setItem('color-theme', 'dark');
        }
    }

    // Mobile Menu Handlers
    function openMobileMenu() {
        $("#mobile-sidebar").addClass("sidebar-open");
        $("#mobile-sidebar-overlay").fadeIn(200);
    }

    function closeMobileMenu() {
        $("#mobile-sidebar").removeClass("sidebar-open");
        $("#mobile-sidebar-overlay").fadeOut(200);
    }

    $("#mobile-sidebar-overlay").on('click', closeMobileMenu);

    // Dynamic Content Loader (AJAX Router)
    function loadPage(pagePath) {
        if(!pagePath) return;
        
        const contentArea = $("#content-area");
        contentArea.css('opacity', '0.4');

        $.ajax({
            url: pagePath + ".php",
            method: "GET",
            success: function (data) {
                window.location.hash = pagePath;
                contentArea.html(data).animate({opacity: 1}, 200);
                if (window.lucide) lucide.createIcons();
                
                // Active link styles
                $(".nav-link").removeClass("nav-active");
                $(`.nav-link[data-page='${pagePath}']`).addClass("nav-active");
            },
            error: function () {
                contentArea.css('opacity', '1').html(`<div class="text-center py-20 text-rose-500 font-bold uppercase tracking-widest">Error Loading Module</div>`);
                if (window.lucide) lucide.createIcons();
            }
        });
    }

    $(document).ready(function () {
        // Init Theme
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
        
        lucide.createIcons();

        // Handle link clicks
        $(".nav-link").on('click', function (e) {
            e.preventDefault();
            loadPage($(this).attr("data-page"));
        });

        // Load page on startup
        const currentHash = window.location.hash.replace('#', '');
        loadPage(currentHash || 'dashboard_home');
    });
</script>
</body>
</html>