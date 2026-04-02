<?php
session_start();


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
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        
        /* Smooth transitions for theme switching */
        body { transition: background-color 0.3s, color 0.3s; }

        .nav-active {
            background-color: rgb(240 253 244); /* emerald-50 */
            color: rgb(5 150 105); /* emerald-600 */
            border-right: 4px solid rgb(5 150 105);
        }

        .dark .nav-active {
            background-color: rgba(6, 78, 59, 0.2); /* dark emerald tint */
            color: rgb(52 211 153); /* emerald-400 */
            border-right: 4px solid rgb(16 185 129);
        }

        #content-area { transition: opacity 0.2s ease-in-out; }
        .loading { pointer-events: none; cursor: wait; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex min-h-screen">

    <aside class="w-64 border-r border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hidden md:flex flex-col sticky top-0 h-screen z-20">
        <div class="p-6 flex items-center gap-3 border-b border-slate-100 dark:border-slate-800">
            <div class="bg-emerald-600 p-1.5 rounded-lg shadow-lg shadow-emerald-200 dark:shadow-none">
                <i data-lucide="shield-check" class="text-white w-5 h-5"></i>
            </div>
            <span class="text-xl font-bold tracking-tight">Admin<span class="text-emerald-600">Box</span></span>
        </div>

        <nav class="flex-grow p-4 space-y-1 mt-4">
            <a href="#admin_stats" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition-all text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50" data-page="admin_stats">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard
            </a>
            
            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-4 mt-6 mb-2">Operations</div>
            
            <a href="#manage-tickets" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50" data-page="manage-tickets">
                <i data-lucide="ticket" class="w-5 h-5"></i> Manage Tickets
            </a>
            <a href="#assign-tickets" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50" data-page="assign-tickets">
                <i data-lucide="user-plus" class="w-5 h-5"></i> Assign Developers
            </a>
            
            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-4 mt-6 mb-2">System</div>
            
            <a href="#manage-projects" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50" data-page="manage-projects">
                <i data-lucide="folder-kanban" class="w-5 h-5"></i> Projects
            </a>
            <a href="#manage-users" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50" data-page="manage-users">
                <i data-lucide="users" class="w-5 h-5"></i> User Access
            </a>
        </nav>

        <div class="p-4 border-t border-slate-100 dark:border-slate-800 space-y-2">
            <button id="theme-toggle" class="w-full flex items-center gap-3 px-4 py-2 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all text-sm font-bold">
                <i data-lucide="moon" class="w-4 h-4 dark:hidden"></i>
                <i data-lucide="sun" class="w-4 h-4 hidden dark:block"></i>
                <span class="dark:hidden">Dark Mode</span>
                <span class="hidden dark:block">Light Mode</span>
            </button>
            
            <a href="/php-bugtracking-system/scripts/logout.php" class="flex items-center gap-3 px-4 py-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-all text-sm font-bold">
    <i data-lucide="log-out" class="w-4 h-4"></i> Sign Out
</a>
        </div>
    </aside>

    <main class="flex-grow">
        <header class="h-16 border-b border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md flex items-center justify-between px-8 sticky top-0 z-10">
            <div class="text-sm font-medium text-slate-500 dark:text-slate-400">
                System Status: <span class="text-emerald-500 font-bold">Operational</span>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-bold leading-none text-slate-800 dark:text-slate-100"><?php echo htmlspecialchars($admin_name); ?></p>
                    <p class="text-[10px] text-slate-400 font-medium mt-1">Super Administrator</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-600 flex items-center justify-center text-white text-sm font-bold shadow-lg shadow-emerald-200 dark:shadow-none">
                    AD
                </div>
            </div>
        </header>

        <div id="content-area" class="p-8 lg:p-12 min-h-[calc(100vh-64px)]">
            <div class="flex items-center justify-center py-32">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-600"></div>
            </div>
        </div>
    </main>

<script>
    // --- 🌙 Dark Mode Handler ---
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

    // --- 🚀 Global AJAX Router ---
    function loadPage(page) {
        if(!page) return;
        
        const contentArea = $("#content-area");
        contentArea.addClass("loading").css('opacity', '0.4');

        $.ajax({
            url: page + ".php",
            method: "GET",
            success: function (data) {
                window.location.hash = page;
                contentArea.html(data).css('opacity', '1').removeClass("loading");
                if (window.lucide) lucide.createIcons();
                
                $(".nav-link").removeClass("nav-active");
                $(`.nav-link[data-page='${page}']`).addClass("nav-active");
            },
            error: function (xhr) {
                contentArea.css('opacity', '1').removeClass("loading");
                let errorTitle = xhr.status === 404 ? "Module Not Found" : "Connection Error";
                contentArea.html(`
                    <div class="flex flex-col items-center justify-center py-32">
                        <div class="bg-red-50 dark:bg-red-900/20 p-6 rounded-full mb-6">
                            <i data-lucide="shield-alert" class="w-12 h-12 text-red-500"></i>
                        </div>
                        <h2 class="text-2xl font-black text-slate-800 dark:text-slate-100">${errorTitle}</h2>
                        <p class="text-slate-500 dark:text-slate-400 mt-2 font-medium">Please check if ${page}.php exists.</p>
                        <button onclick="loadPage('admin_stats')" class="mt-8 px-6 py-2 bg-emerald-600 text-white rounded-xl font-bold text-xs shadow-lg shadow-emerald-200 dark:shadow-none">Return to Dashboard</button>
                    </div>
                `);
                lucide.createIcons();
            }
        });
    }

    $(document).ready(function () {
        initTheme();
        lucide.createIcons();

        $(".nav-link").on('click', function (e) {
            e.preventDefault();
            const page = $(this).attr("data-page");
            loadPage(page);
        });

        const currentHash = window.location.hash.replace('#', '');
        loadPage(currentHash || 'admin_stats');

        const params = new URLSearchParams(window.location.search);
        if (params.has('success')) Swal.fire('Done!', params.get('success'), 'success');
    });
</script>
</body>
</html>