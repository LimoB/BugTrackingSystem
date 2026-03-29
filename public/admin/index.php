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
            background-color: rgb(240 253 244); /* emerald-50 */
            color: rgb(5 150 105); /* emerald-600 */
            border-right: 4px solid rgb(5 150 105);
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
            <a href="#admin_stats" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition-all text-slate-500 hover:bg-slate-50" data-page="admin_stats">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard
            </a>
            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-4 mt-6 mb-2">Operations</div>
            <a href="#manage-tickets" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all text-slate-500 hover:bg-slate-50" data-page="manage-tickets">
                <i data-lucide="ticket" class="w-5 h-5"></i> Manage Tickets
            </a>
            <a href="#assign-tickets" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all text-slate-500 hover:bg-slate-50" data-page="assign-tickets">
                <i data-lucide="user-plus" class="w-5 h-5"></i> Assign Developers
            </a>
            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-4 mt-6 mb-2">System</div>
            <a href="#manage-projects" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all text-slate-500 hover:bg-slate-50" data-page="manage-projects">
                <i data-lucide="folder-kanban" class="w-5 h-5"></i> Projects
            </a>
            <a href="#manage-users" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all text-slate-500 hover:bg-slate-50" data-page="manage-users">
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
            <div class="flex items-center justify-center py-32">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-600"></div>
            </div>
        </div>
    </main>

<script>
    // --- 🚀 Global AJAX Router ---
    function loadPage(page) {
        if(!page) return;
        
        const contentArea = $("#content-area");
        contentArea.addClass("loading").css('opacity', '0.4');

        $.ajax({
            url: page + ".php",
            method: "GET",
            success: function (data) {
                // Update URL hash for persistence
                window.location.hash = page;
                
                // Content Switch
                contentArea.html(data).css('opacity', '1').removeClass("loading");
                
                // Re-initialize icons
                if (window.lucide) lucide.createIcons();
                
                // Update Sidebar Styles
                $(".nav-link").removeClass("nav-active");
                $(`.nav-link[data-page='${page}']`).addClass("nav-active");
            },
            error: function (xhr) {
                contentArea.css('opacity', '1').removeClass("loading");
                let errorTitle = xhr.status === 404 ? "Module Not Found" : "Connection Error";
                let errorText = xhr.status === 404 ? `The file "${page}.php" is missing.` : "Could not connect to server.";
                
                contentArea.html(`
                    <div class="flex flex-col items-center justify-center py-32">
                        <div class="bg-red-50 p-6 rounded-full mb-6">
                            <i data-lucide="shield-alert" class="w-12 h-12 text-red-500"></i>
                        </div>
                        <h2 class="text-2xl font-black text-slate-800">${errorTitle}</h2>
                        <p class="text-slate-500 mt-2 font-medium">${errorText}</p>
                        <button onclick="loadPage('admin_stats')" class="mt-8 px-6 py-2 bg-slate-900 text-white rounded-xl font-bold text-xs">Return to Dashboard</button>
                    </div>
                `);
                lucide.createIcons();
            }
        });
    }

    $(document).ready(function () {
        lucide.createIcons();

        // 1. Sidebar Nav Click
        $(".nav-link").on('click', function (e) {
            e.preventDefault();
            const page = $(this).attr("data-page");
            loadPage(page);
        });

        // 2. Initial Page Load (Priority: Hash > Default)
        const currentHash = window.location.hash.replace('#', '');
        loadPage(currentHash || 'admin_stats');

        // 3. Simple SweetAlert triggers from URL params
        const params = new URLSearchParams(window.location.search);
        if (params.has('success')) Swal.fire('Done!', params.get('success'), 'success');
    });

    // Modal Global Helper
    function closeUserModal() {
        // Implementation depends on your modal library (e.g., Bootstrap, custom div)
        $('.modal-backdrop').fadeOut();
        $('#modalContainer').fadeOut();
    }
</script>
</body>
</html>