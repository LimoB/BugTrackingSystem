<aside class="w-72 border-r border-slate-200 bg-white dark:bg-slate-900 dark:border-slate-800 min-h-screen hidden md:flex flex-col sticky top-0 transition-colors duration-300 z-50">
    <div class="p-8 flex items-center gap-4 border-b border-slate-100 dark:border-slate-800">
        <div class="bg-blue-600 p-2 rounded-xl shadow-lg shadow-blue-500/20">
            <i data-lucide="zap" class="text-white w-6 h-6 fill-current"></i>
        </div>
        <div>
            <span class="text-2xl font-black tracking-tighter dark:text-white">Zappr<span class="text-blue-600">.</span></span>
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mt-1">Portal</p>
        </div>
    </div>

    <nav class="flex-grow p-6 space-y-2">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4 px-4">Menu</p>
        
        <?php 
            $current_page = basename($_SERVER['PHP_SELF']); 
            function navLink($page, $icon, $label, $current_page) {
                $active = ($page == $current_page) ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800';
                echo "<a href='$page' class='flex items-center gap-3 px-5 py-3.5 rounded-2xl font-bold transition-all $active'>
                        <i data-lucide='$icon' class='w-5 h-5'></i> $label
                      </a>";
            }

            navLink('index.php', 'layout-dashboard', 'Dashboard', $current_page);
            navLink('view-tickets.php', 'ticket', 'My Tickets', $current_page);
            navLink('create-ticket.php', 'plus-circle', 'New Report', $current_page);
        ?>
    </nav>

    <div class="p-6 border-t border-slate-100 dark:border-slate-800 space-y-2">
        <button onclick="toggleTheme()" class="w-full flex items-center justify-between px-5 py-3.5 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-2xl transition-all font-bold group">
            <span id="themeText">Dark Mode</span>
            <i id="themeIcon" data-lucide="moon" class="w-5 h-5 group-hover:rotate-12 transition-transform"></i>
        </button>

        <a href="../../scripts/logout.php" class="flex items-center gap-3 px-5 py-3.5 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-2xl transition-all font-bold">
            <i data-lucide="log-out" class="w-5 h-5"></i> Logout
        </a>
    </div>
</aside>