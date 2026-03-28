<aside class="w-64 border-r border-slate-200 bg-white dark:bg-slate-900 dark:border-slate-800 min-h-screen hidden md:flex flex-col sticky top-0 transition-colors duration-300">
    <div class="p-6 flex items-center gap-3 border-b border-slate-100 dark:border-slate-800">
        <div class="bg-blue-600 p-1.5 rounded-lg shadow-lg shadow-blue-200 dark:shadow-none">
            <i data-lucide="zap" class="text-white w-5 h-5 fill-current"></i>
        </div>
        <span class="text-xl font-bold tracking-tight dark:text-white">Zappr.</span>
    </div>

    <nav class="flex-grow p-4 space-y-2 mt-4">
        <a href="index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition-all <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800'; ?>">
            <i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard
        </a>
        <a href="view-tickets.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium <?php echo basename($_SERVER['PHP_SELF']) == 'view-tickets.php' ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800'; ?>">
            <i data-lucide="ticket" class="w-5 h-5"></i> My Tickets
        </a>
        <a href="create-ticket.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium <?php echo basename($_SERVER['PHP_SELF']) == 'create-ticket.php' ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800'; ?>">
            <i data-lucide="plus-circle" class="w-5 h-5"></i> New Report
        </a>
    </nav>

    <div class="p-4 border-t border-slate-100 dark:border-slate-800">
        <button onclick="toggleTheme()" class="w-full flex items-center justify-between px-4 py-3 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-xl transition-all font-medium">
            <span id="themeText">Dark Mode</span>
            <i id="themeIcon" data-lucide="moon" class="w-5 h-5"></i>
        </button>
        <a href="../../scripts/logout.php" class="flex items-center gap-3 px-4 py-3 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-all font-medium mt-2">
            <i data-lucide="log-out" class="w-5 h-5"></i> Logout
        </a>
    </div>
</aside>