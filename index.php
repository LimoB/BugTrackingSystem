<?php
/**
 * File: index.php (Root)
 * Purpose: Professional High-Conversion Landing Page for Zappr Bug Tracker.
 */
$base_url = "/php-bugtracking-system/";
$site_title = "Zappr | Ship Flawless Code";
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $site_title; ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        .glass { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); }
        .dark-glass { background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(12px); }
        
        .gradient-text { 
            background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%); 
            background-clip: text; 
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent; 
        }

        .hero-blob {
            filter: blur(80px);
            opacity: 0.4;
            z-index: -1;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        .animate-float { animation: float 6s ease-in-out infinite; }
    </style>
</head>

<body class="bg-[#fcfcfd] text-slate-900 antialiased overflow-x-hidden">

<nav class="fixed top-0 w-full z-[100] border-b border-slate-200/60 glass">
    <div class="max-w-7xl mx-auto px-6 h-20 flex justify-between items-center">
        <div class="flex items-center gap-2.5 group cursor-pointer">
            <div class="bg-blue-600 p-2 rounded-xl group-hover:rotate-12 transition-transform duration-300">
                <i data-lucide="zap" class="text-white w-5 h-5 fill-current"></i>
            </div>
            <span class="text-xl font-black tracking-tighter text-slate-900 uppercase">Zappr<span class="text-blue-600">.</span></span>
        </div>

        <div class="hidden lg:flex items-center gap-10">
            <a href="#features" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 hover:text-blue-600 transition">Features</a>
            <a href="#solutions" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 hover:text-blue-600 transition">Solutions</a>
            <a href="#about" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 hover:text-blue-600 transition">Our Vision</a>
        </div>

        <div class="flex items-center gap-4">
            <a href="public/login/index.php" class="hidden sm:block text-[10px] font-black uppercase tracking-[0.2em] text-slate-700 hover:text-blue-600">Log in</a>
            <a href="public/register/index.php" class="bg-slate-900 text-white px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] hover:bg-blue-600 transition shadow-xl shadow-blue-200/20 active:scale-95">Get Started</a>
        </div>
    </div>
</nav>

<section class="relative pt-40 pb-24 lg:pt-56 lg:pb-32">
    <div class="absolute top-40 -left-20 w-96 h-96 bg-blue-400 hero-blob rounded-full"></div>
    <div class="absolute top-60 -right-20 w-96 h-96 bg-purple-400 hero-blob rounded-full"></div>

    <div class="max-w-7xl mx-auto px-6 grid lg:grid-cols-2 gap-16 items-center">
        <div class="text-center lg:text-left">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-blue-50 border border-blue-100 mb-8">
                <div class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></div>
                <span class="text-[9px] font-black text-blue-700 uppercase tracking-[0.2em]">Deployment-Ready Bug Tracking</span>
            </div>
            
            <h1 class="text-5xl lg:text-8xl font-black leading-[0.9] mb-8 tracking-tighter text-slate-900">
                Ship code <br><span class="gradient-text italic">fearlessly.</span>
            </h1>
            
            <p class="text-lg text-slate-500 mb-10 leading-relaxed max-w-md mx-auto lg:mx-0 font-medium">
                The developer-first bug tracker that replaces messy spreadsheets with high-velocity workflows. Centralize logs, assign tasks, and squash bugs.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-6">
                <a href="public/register/index.php" class="w-full sm:w-auto bg-blue-600 text-white px-10 py-5 rounded-3xl font-black uppercase tracking-widest text-[11px] hover:scale-105 transition shadow-2xl shadow-blue-500/20 active:scale-95">
                    Start Squashing Bugs
                </a>
                <div class="flex items-center gap-3">
                    <div class="flex -space-x-3">
                        <img class="w-10 h-10 rounded-full border-4 border-white" src="https://ui-avatars.com/api/?name=Dev+1&bg=2563eb&color=fff" alt="User">
                        <img class="w-10 h-10 rounded-full border-4 border-white" src="https://ui-avatars.com/api/?name=Dev+2&bg=7c3aed&color=fff" alt="User">
                        <img class="w-10 h-10 rounded-full border-4 border-white" src="https://ui-avatars.com/api/?name=Dev+3&bg=0ea5e9&color=fff" alt="User">
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Join 2,000+ devs</span>
                </div>
            </div>
        </div>

        <div class="relative animate-float">
            <div class="bg-slate-900 rounded-[2.5rem] p-4 shadow-2xl border border-slate-800">
                <div class="bg-slate-800/50 rounded-[1.8rem] overflow-hidden border border-slate-700/50">
                    <div class="h-10 px-6 flex items-center gap-2 border-b border-slate-700/50 bg-slate-800/80">
                        <div class="w-2.5 h-2.5 rounded-full bg-rose-500/50"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-amber-500/50"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-500/50"></div>
                        <div class="ml-4 flex-grow h-5 bg-slate-900/50 rounded-lg"></div>
                    </div>
                    <div class="p-6 bg-slate-900">
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="h-24 bg-blue-600/10 border border-blue-500/20 rounded-2xl p-4">
                                <div class="w-8 h-1 bg-blue-500 rounded-full mb-3"></div>
                                <div class="text-2xl font-black text-white leading-none">14</div>
                                <div class="text-[8px] font-bold text-blue-400 uppercase tracking-widest mt-1">Pending Bugs</div>
                            </div>
                            <div class="h-24 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl p-4">
                                <div class="w-8 h-1 bg-emerald-500 rounded-full mb-3"></div>
                                <div class="text-2xl font-black text-white leading-none">98.2%</div>
                                <div class="text-[8px] font-bold text-emerald-400 uppercase tracking-widest mt-1">Uptime</div>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div class="h-12 bg-slate-800/50 rounded-xl flex items-center px-4 gap-3">
                                <div class="w-2 h-2 rounded-full bg-rose-500"></div>
                                <div class="flex-grow h-2 bg-slate-700 rounded-full"></div>
                                <div class="w-12 h-2 bg-blue-600/30 rounded-full"></div>
                            </div>
                            <div class="h-12 bg-slate-800/50 rounded-xl flex items-center px-4 gap-3 opacity-60">
                                <div class="w-2 h-2 rounded-full bg-amber-500"></div>
                                <div class="flex-grow h-2 bg-slate-700 rounded-full"></div>
                                <div class="w-12 h-2 bg-blue-600/30 rounded-full"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="solutions" class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid lg:grid-cols-3 gap-12">
            <div class="p-10 rounded-[2.5rem] bg-slate-50 border border-slate-100 hover:bg-white hover:shadow-2xl transition duration-500">
                <div class="w-14 h-14 bg-emerald-500 text-white rounded-2xl flex items-center justify-center mb-8">
                    <i data-lucide="shield-check" class="w-7 h-7"></i>
                </div>
                <h3 class="text-xl font-black mb-4">Admins</h3>
                <p class="text-slate-500 text-sm font-medium leading-relaxed">
                    Total oversight. Manage users, oversee project health, and configure system-wide security settings.
                </p>
            </div>
            
            <div class="p-10 rounded-[2.5rem] bg-slate-50 border border-slate-100 hover:bg-white hover:shadow-2xl transition duration-500">
                <div class="w-14 h-14 bg-blue-600 text-white rounded-2xl flex items-center justify-center mb-8">
                    <i data-lucide="terminal" class="w-7 h-7"></i>
                </div>
                <h3 class="text-xl font-black mb-4">Developers</h3>
                <p class="text-slate-500 text-sm font-medium leading-relaxed">
                    Zero noise. Get assigned tickets, view stack traces, and update status in a click. No distractions.
                </p>
            </div>

            <div class="p-10 rounded-[2.5rem] bg-slate-50 border border-slate-100 hover:bg-white hover:shadow-2xl transition duration-500">
                <div class="w-14 h-14 bg-indigo-600 text-white rounded-2xl flex items-center justify-center mb-8">
                    <i data-lucide="user" class="w-7 h-7"></i>
                </div>
                <h3 class="text-xl font-black mb-4">Users</h3>
                <p class="text-slate-500 text-sm font-medium leading-relaxed">
                    Seamless reporting. Create tickets, track resolution progress, and get notified when issues are squashed.
                </p>
            </div>
        </div>
    </div>
</section>

<section id="about" class="py-32 bg-slate-950 relative overflow-hidden">
    <div class="max-w-4xl mx-auto px-6 text-center relative z-10">
        <h2 class="text-[10px] font-black text-blue-500 uppercase tracking-[0.4em] mb-8">Ready to Scale?</h2>
        <h3 class="text-4xl md:text-6xl font-black text-white tracking-tighter mb-10 leading-none">
            Stop debugging via email. <br><span class="text-slate-500">Start using Zappr.</span>
        </h3>
        <div class="flex flex-col sm:flex-row justify-center gap-4">
            <a href="public/register/index.php" class="bg-blue-600 text-white px-12 py-5 rounded-2xl font-black uppercase tracking-widest text-[11px] hover:bg-blue-700 transition shadow-2xl shadow-blue-600/20">
                Create Free Account
            </a>
            <a href="public/login/index.php" class="bg-white/10 text-white px-12 py-5 rounded-2xl font-black uppercase tracking-widest text-[11px] hover:bg-white hover:text-slate-900 transition backdrop-blur-md">
                Admin Demo
            </a>
        </div>
    </div>
</section>

<footer class="py-20 bg-white border-t border-slate-100">
    <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-10">
        <div class="flex items-center gap-2.5">
            <div class="bg-blue-600 p-1.5 rounded-lg">
                <i data-lucide="zap" class="text-white w-4 h-4 fill-current"></i>
            </div>
            <span class="text-lg font-black tracking-tighter uppercase">Zappr<span class="text-blue-600">.</span></span>
        </div>
        
        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">
            &copy; <?php echo date("Y"); ?> Zappr Management. All rights reserved.
        </p>

        <div class="flex gap-8">
            <a href="#" class="text-[10px] font-black uppercase tracking-widest text-slate-500 hover:text-blue-600 transition">Twitter</a>
            <a href="#" class="text-[10px] font-black uppercase tracking-widest text-slate-500 hover:text-blue-600 transition">GitHub</a>
        </div>
    </div>
</footer>

<script>
    lucide.createIcons();
</script>
</body>
</html>