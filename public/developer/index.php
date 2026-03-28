<?php
session_start();

// ✅ Security Check: Developer Role Only
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'developer') {
    header("Location: ../login/index.php");
    exit();
}

include('../../config/config.php');
$base_url = "/php-bugtracking-system/";

// ✅ Fetch Developer Data
$user_id = $_SESSION['user_id'];
$stmt = $connection->prepare("SELECT name, email FROM Users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Quick Stats for the "Home" view
$stmt_count = $connection->prepare("SELECT COUNT(*) as count FROM Tickets WHERE assigned_to = ? AND status != 'resolved'");
$stmt_count->bind_param("i", $user_id);
$stmt_count->execute();
$my_active_bugs = $stmt_count->get_result()->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevHub | Zappr</title>
    
    <link href="<?php echo $base_url; ?>dist/output.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@0.344.0/dist/umd/lucide.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        .nav-active {
            @apply bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 border-r-4 border-indigo-600;
        }

        /* Smooth loading transition */
        #content-area {
            transition: opacity 0.2s ease-in-out;
        }
        .loading { opacity: 0.5; pointer-events: none; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex min-h-screen">

    <aside class="w-64 border-r border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hidden md:flex flex-col sticky top-0 h-screen">
        <div class="p-6 flex items-center gap-3 border-b border-slate-100 dark:border-slate-800">
            <div class="bg-indigo-600 p-1.5 rounded-lg shadow-lg shadow-indigo-200 dark:shadow-none">
                <i data-lucide="terminal" class="text-white w-5 h-5"></i>
            </div>
            <span class="text-xl font-bold tracking-tight">DevHub<span class="text-indigo-600">.</span></span>
        </div>

        <nav class="flex-grow p-4 space-y-2 mt-4">
            <a href="#" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition-all hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-500" data-page="dashboard_home">
                <i data-lucide="layout-grid" class="w-5 h-5"></i> Overview
            </a>
            <a href="#" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-500" data-page="assigned-tickets">
                <i data-lucide="bug" class="w-5 h-5"></i> Assigned to Me
                <?php if($my_active_bugs > 0): ?>
                    <span class="ml-auto bg-indigo-600 text-white text-[10px] px-2 py-0.5 rounded-full"><?php echo $my_active_bugs; ?></span>
                <?php endif; ?>
            </a>
            <a href="#" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-500" data-page="view-tickets">
                <i data-lucide="database" class="w-5 h-5"></i> Global Backlog
            </a>
        </nav>

        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-3 px-4 py-3 mb-2">
                <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center text-indigo-600 font-bold text-xs">
                    <?php echo strtoupper(substr($user['name'], 0, 2)); ?>
                </div>
                <div class="truncate">
                    <p class="text-xs font-bold truncate"><?php echo htmlspecialchars($user['name']); ?></p>
                    <p class="text-[10px] text-slate-500 uppercase tracking-tighter">Developer Role</p>
                </div>
            </div>
            
            <button onclick="toggleTheme()" class="w-full flex items-center justify-between px-4 py-2.5 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-xl transition-all text-sm font-medium">
                <span id="themeText">Dark Mode</span>
                <i id="themeIcon" data-lucide="moon" class="w-4 h-4"></i>
            </button>
            
            <a href="../../scripts/logout.php" class="flex items-center gap-3 px-4 py-2.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-all text-sm font-medium mt-1">
                <i data-lucide="log-out" class="w-4 h-4"></i> Logout
            </a>
        </div>
    </aside>

    <main class="flex-grow">
        <div id="content-area" class="p-6 lg:p-10 min-h-screen">
            <div class="max-w-4xl">
                <h1 class="text-4xl font-extrabold tracking-tight mb-2">System Ready, <?php echo htmlspecialchars(explode(' ', $user['name'])[0]); ?>.</h1>
                <p class="text-slate-500 dark:text-slate-400 text-lg mb-10">You have <span class="text-indigo-600 font-bold"><?php echo $my_active_bugs; ?> tickets</span> requiring immediate action.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <button data-page="assigned-tickets" class="nav-link text-left p-8 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[2rem] hover:shadow-xl hover:border-indigo-500 transition-all group">
                        <div class="w-12 h-12 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                            <i data-lucide="play-circle"></i>
                        </div>
                        <h3 class="text-xl font-bold mb-2">Start Working</h3>
                        <p class="text-slate-500 text-sm italic">Jump into your assigned tasks and update progress.</p>
                    </button>

                    <button data-page="view-tickets" class="nav-link text-left p-8 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[2rem] hover:shadow-xl hover:border-indigo-500 transition-all group">
                        <div class="w-12 h-12 bg-slate-50 dark:bg-slate-800 text-slate-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-slate-800 group-hover:text-white transition-all">
                            <i data-lucide="search"></i>
                        </div>
                        <h3 class="text-xl font-bold mb-2">Browse Backlog</h3>
                        <p class="text-slate-500 text-sm italic">View all system reports and grab unassigned bugs.</p>
                    </button>
                </div>
            </div>
        </div>
    </main>

    <script>
    // --- 🚀 Global AJAX Page Loader ---
    // Moved outside document.ready so onclick attributes can find it
    function loadPage(page) {
        const contentArea = $("#content-area");
        contentArea.addClass("loading").css("opacity", "0.5");
        
        // Transform 'page-name&id=1' into 'page-name.php?id=1'
        let url = page;
        if (page.includes('&')) {
            let parts = page.split('&');
            url = parts[0] + ".php?" + parts.slice(1).join('&');
        } else {
            url = page + ".php";
        }

        $.ajax({
            url: url,
            method: "GET",
            success: function(data) {
                contentArea.html(data).removeClass("loading").css("opacity", "1");
                lucide.createIcons();
                
                // Update Sidebar UI: Active State
                $(".nav-link").removeClass("nav-active");
                // Match the base page name (before the '&')
                const basePage = page.split('&')[0];
                $(`.nav-link[data-page='${basePage}']`).addClass("nav-active");
            },
            error: function() {
                contentArea.html(`
                    <div class="text-center py-20">
                        <i data-lucide="alert-triangle" class="w-12 h-12 text-red-500 mx-auto mb-4"></i>
                        <h2 class="text-xl font-bold">Module Not Found</h2>
                        <p class="text-slate-500 italic">Expected: ${url}</p>
                    </div>
                `).removeClass("loading").css("opacity", "1");
                lucide.createIcons();
            }
        });
    }

    $(document).ready(function() {
        // 1. Initial Load: Load the home screen immediately
        loadPage('dashboard_home');

        // 2. Sidebar Navigation Handler
        $(".nav-link").on('click', function(e) {
            e.preventDefault();
            const page = $(this).data("page");
            if(page) loadPage(page);
        });

        lucide.createIcons();
    });

    // --- 🌙 Dark Mode Support ---
    function setTheme(theme) {
        const html = document.documentElement;
        const themeText = document.getElementById("themeText");
        const themeIcon = document.getElementById("themeIcon");

        if (theme === "dark") {
            html.classList.add("dark");
            if(themeText) themeText.textContent = "Light Mode";
            if(themeIcon) themeIcon.setAttribute("data-lucide", "sun");
        } else {
            html.classList.remove("dark");
            if(themeText) themeText.textContent = "Dark Mode";
            if(themeIcon) themeIcon.setAttribute("data-lucide", "moon");
        }
        localStorage.setItem("theme", theme);
        if(window.lucide) lucide.createIcons(); 
    }

    function toggleTheme() {
        const current = localStorage.getItem("theme") || "light";
        setTheme(current === "light" ? "dark" : "light");
    }

    (function () {
        const savedTheme = localStorage.getItem("theme") || "light";
        setTheme(savedTheme);
    })();
</script>
</body>
</html>