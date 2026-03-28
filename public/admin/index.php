<?php
session_start();
include('../../config/config.php');

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
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        .nav-active {
            @apply bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400 border-r-4 border-emerald-600;
        }

        #content-area { transition: all 0.3s ease; }
        .loading { opacity: 0.5; filter: blur(2px); pointer-events: none; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex min-h-screen">

    <aside class="w-64 border-r border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hidden md:flex flex-col sticky top-0 h-screen">
        <div class="p-6 flex items-center gap-3 border-b border-slate-100 dark:border-slate-800">
            <div class="bg-emerald-600 p-1.5 rounded-lg shadow-lg shadow-emerald-200 dark:shadow-none">
                <i data-lucide="shield-check" class="text-white w-5 h-5"></i>
            </div>
            <span class="text-xl font-bold tracking-tight">Admin<span class="text-emerald-600">Box</span></span>
        </div>

        <nav class="flex-grow p-4 space-y-1 mt-4">
            <a href="#" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition-all text-slate-500 hover:bg-slate-50" data-page="admin_stats">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard
            </a>
            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-4 mt-6 mb-2">Operations</div>
            <a href="#" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all text-slate-500 hover:bg-slate-50" data-page="manage-tickets">
                <i data-lucide="ticket" class="w-5 h-5"></i> Manage Tickets
            </a>
            <a href="#" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all text-slate-500 hover:bg-slate-50" data-page="assign-tickets">
                <i data-lucide="user-plus" class="w-5 h-5"></i> Assign Developers
            </a>
            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-4 mt-6 mb-2">System</div>
            <a href="#" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all text-slate-500 hover:bg-slate-50" data-page="manage-projects">
                <i data-lucide="folder-kanban" class="w-5 h-5"></i> Projects
            </a>
            <a href="#" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all text-slate-500 hover:bg-slate-50" data-page="manage-users">
                <i data-lucide="users" class="w-5 h-5"></i> User Access
            </a>
        </nav>

        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            <a href="../../scripts/logout.php" class="flex items-center gap-3 px-4 py-3 text-red-500 hover:bg-red-50 rounded-xl transition-all text-sm font-bold">
                <i data-lucide="log-out" class="w-4 h-4"></i> Sign Out
            </a>
        </div>
    </aside>

    <main class="flex-grow">
        <header class="h-16 border-b border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-900/50 backdrop-blur-md flex items-center justify-between px-8 sticky top-0 z-10">
            <div class="text-sm font-medium text-slate-500">
                System Status: <span class="text-emerald-500 font-bold">Operational</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm font-bold"><?php echo htmlspecialchars($admin_name); ?></span>
                <div class="w-8 h-8 rounded-full bg-emerald-600 flex items-center justify-center text-white text-xs font-bold">AD</div>
            </div>
        </header>

        <div id="content-area" class="p-8 lg:p-12 min-h-[calc(100vh-64px)]">
            <div class="max-w-4xl">
                <h1 class="text-4xl font-black tracking-tight text-slate-900 mb-4">Core Infrastructure</h1>
                <p class="text-slate-500 text-lg mb-8">Welcome to the Zappr Administrative Suite. Monitor system health and manage resources from this terminal.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="p-6 bg-white border border-slate-200 rounded-3xl">
                        <h3 class="font-bold mb-2">Pending Assignments</h3>
                        <p class="text-sm text-slate-500 mb-4">There are unassigned tickets waiting for developer review.</p>
                        <button onclick="loadPage('assign-tickets')" class="text-emerald-600 text-sm font-bold hover:underline">Route Tickets →</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

   <script>
    // --- 🚀 Global AJAX Router ---
    function loadPage(page) {
        const contentArea = $("#content-area");
        
        // Visual feedback during load
        contentArea.addClass("loading").css('opacity', '0.5');

        $.ajax({
            url: page + ".php",
            method: "GET",
            cache: false, // Ensure fresh data for stats/tickets
            success: function (data) {
                // Smooth transition
                contentArea.hide().html(data).fadeIn(200).removeClass("loading").css('opacity', '1');
                
                // Re-initialize icons for the new content
                if (typeof lucide !== 'undefined') lucide.createIcons();
                
                // Update Sidebar Active State
                $(".nav-link").removeClass("nav-active bg-emerald-50 text-emerald-600 border-r-4 border-emerald-600");
                $(`.nav-link[data-page='${page}']`).addClass("nav-active bg-emerald-50 text-emerald-600 border-r-4 border-emerald-600");
                
                // Update URL hash for bookmarking (Optional)
                window.location.hash = page;
            },
            error: function (xhr) {
                let errorMsg = xhr.status === 404 ? `Module "${page}.php" not found.` : "System restriction or network error.";
                contentArea.html(`
                    <div class="flex flex-col items-center justify-center py-32 animate-pulse">
                        <div class="bg-red-50 p-6 rounded-full mb-6">
                            <i data-lucide="shield-alert" class="w-12 h-12 text-red-500"></i>
                        </div>
                        <h2 class="text-2xl font-black text-slate-800">Operational Block</h2>
                        <p class="text-slate-500 mt-2 font-medium">${errorMsg}</p>
                        <button onclick="loadPage('admin_stats')" class="mt-8 px-6 py-2 bg-slate-900 text-white rounded-xl font-bold text-xs uppercase tracking-widest">Return to Base</button>
                    </div>
                `).removeClass("loading").css('opacity', '1');
                lucide.createIcons();
            }
        });
    }

    $(document).ready(function () {
        // 1. Initial Icon Render
        lucide.createIcons();

        // 2. Handle Flash Messages (SweetAlert2)
        const params = new URLSearchParams(window.location.search);
        if (params.has('success')) Swal.fire('System Updated', params.get('success'), 'success');
        if (params.has('error')) Swal.fire('Alert', params.get('error'), 'error');

        // 3. Sidebar Click Handler
        $(".nav-link").click(function (e) {
            e.preventDefault();
            const page = $(this).attr("data-page");
            if (page) loadPage(page);
        });

        // 4. Default Landing Logic
        // Checks if there is a hash (e.g., #manage-users) otherwise loads stats
        const initialPage = window.location.hash.replace('#', '') || 'admin_stats';
        loadPage(initialPage);
    });

    //  Global Modal Helper (Used by child pages)
    function closeModal() {
        $('.modal').hide();
        // If using Tailwind 'hidden' classes:
        // $('.modal-container').addClass('hidden');
    }
</script>
</body>
</html>