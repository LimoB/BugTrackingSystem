<?php
// Define base URL
$base_url = "/php-bugtracking-system/";
$site_title = "Zappr | Ship Flawless Code";
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <title><?php echo $site_title; ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
    body { font-family: 'Plus Jakarta Sans', sans-serif; }
    .glass { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(16px); }
    .gradient-text { background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%); background-clip: text; -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
  </style>
</head>

<body class="bg-[#fcfcfd] text-slate-900 antialiased">

<nav class="fixed top-0 w-full z-[100] border-b border-slate-200/60 glass">
  <div class="max-w-7xl mx-auto px-6 h-20 flex justify-between items-center">
    <div class="flex items-center gap-2.5 group cursor-pointer">
      <div class="bg-blue-600 p-2 rounded-xl group-hover:rotate-12 transition-transform">
        <i data-lucide="zap" class="text-white w-5 h-5 fill-current"></i>
      </div>
      <span class="text-xl font-black tracking-tighter text-slate-900">ZAPPR<span class="text-blue-600">.</span></span>
    </div>

    <div class="hidden md:flex items-center gap-10">
      <a href="#features" class="text-xs font-bold uppercase tracking-widest text-slate-500 hover:text-blue-600 transition">Product</a>
      <a href="#solutions" class="text-xs font-bold uppercase tracking-widest text-slate-500 hover:text-blue-600 transition">Solutions</a>
      <a href="#about" class="text-xs font-bold uppercase tracking-widest text-slate-500 hover:text-blue-600 transition">About</a>
    </div>

    <div class="flex items-center gap-4">
      <a href="<?php echo $base_url; ?>public/login/index.php" class="text-xs font-bold uppercase tracking-widest text-slate-700 hover:text-blue-600">Log in</a>
      <a href="<?php echo $base_url; ?>public/register/index.php" class="bg-slate-900 text-white px-6 py-3 rounded-2xl text-xs font-bold uppercase tracking-widest hover:bg-blue-600 transition shadow-xl shadow-blue-200/20">Get Started</a>
    </div>
  </div>
</nav>

<section class="relative pt-48 pb-32 overflow-hidden">
  <div class="max-w-7xl mx-auto px-6 grid lg:grid-cols-2 gap-16 items-center">
    <div>
      <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-blue-50 border border-blue-100 mb-8">
        <div class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></div>
        <span class="text-[10px] font-black text-blue-700 uppercase tracking-[0.2em]">Next-Gen Error Monitoring</span>
      </div>
      <h1 class="text-6xl lg:text-8xl font-black leading-[0.9] mb-8 tracking-tighter text-slate-900">
        Ship code <br><span class="gradient-text">fearlessly.</span>
      </h1>
      <p class="text-lg text-slate-500 mb-10 leading-relaxed max-w-md font-medium">
        The mission-critical bug tracker built for speed. Centralize tickets, automate assignments, and clear your backlog.
      </p>
      <div class="flex flex-wrap gap-5">
        <a href="#register" class="bg-blue-600 text-white px-10 py-5 rounded-3xl font-black uppercase tracking-widest text-[11px] hover:scale-105 transition shadow-2xl shadow-blue-500/20 active:scale-95">
          Start Project
        </a>
        <div class="flex -space-x-4 items-center">
          <div class="w-12 h-12 rounded-2xl border-4 border-white bg-slate-200 overflow-hidden shadow-sm">
            <img src="https://ui-avatars.com/api/?name=Limo&bg=2563eb&color=fff" alt="User">
          </div>
          <div class="w-12 h-12 rounded-2xl border-4 border-white bg-slate-200 overflow-hidden shadow-sm">
            <img src="https://ui-avatars.com/api/?name=Boaz&bg=7c3aed&color=fff" alt="User">
          </div>
          <div class="pl-8 text-[11px] font-black uppercase tracking-widest text-slate-400">Trusted by 200+ Devs</div>
        </div>
      </div>
    </div>

    <div class="relative">
      <div class="absolute -inset-10 bg-gradient-to-tr from-blue-500/20 to-purple-500/20 rounded-[4rem] blur-3xl opacity-50"></div>
      <div class="relative bg-white dark:bg-slate-900 rounded-[3rem] border border-slate-200 dark:border-slate-800 shadow-2xl p-6">
        <div class="flex items-center justify-between mb-8">
            <div class="flex gap-2">
                <div class="w-3 h-3 rounded-full bg-rose-500"></div>
                <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
            </div>
            <div class="px-4 py-1.5 bg-slate-100 dark:bg-slate-800 rounded-full text-[9px] font-black uppercase tracking-widest text-slate-500">Admin Console v2.0</div>
        </div>
        <div class="space-y-4">
            <div class="h-12 w-3/4 bg-slate-50 dark:bg-slate-800 rounded-2xl"></div>
            <div class="grid grid-cols-2 gap-4">
                <div class="h-32 bg-blue-600 rounded-3xl p-6 flex flex-col justify-end">
                    <span class="text-white/60 text-[10px] font-black uppercase tracking-widest">Active Tickets</span>
                    <span class="text-3xl font-black text-white leading-none">128</span>
                </div>
                <div class="h-32 bg-slate-50 dark:bg-slate-800 rounded-3xl p-6 flex flex-col justify-end border border-slate-100 dark:border-slate-700">
                    <span class="text-slate-400 text-[10px] font-black uppercase tracking-widest">Resolved</span>
                    <span class="text-3xl font-black text-slate-900 dark:text-white leading-none">84%</span>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="solutions" class="py-32 bg-white">
  <div class="max-w-7xl mx-auto px-6">
    <div class="text-center mb-24">
      <h2 class="text-xs font-black text-blue-600 uppercase tracking-[0.3em] mb-4">Enterprise Solutions</h2>
      <p class="text-4xl md:text-5xl font-black tracking-tighter text-slate-900">Tailored for your stack.</p>
    </div>
    
    <div class="grid md:grid-cols-2 gap-12">
      <div class="group p-10 bg-slate-50 rounded-[3rem] border border-slate-100 hover:bg-white hover:shadow-2xl transition duration-500">
        <div class="w-16 h-16 bg-blue-600 text-white rounded-3xl flex items-center justify-center mb-8 group-hover:scale-110 transition">
          <i data-lucide="shield-check" class="w-8 h-8"></i>
        </div>
        <h3 class="text-2xl font-black mb-4">For QA Teams</h3>
        <p class="text-slate-500 font-medium leading-relaxed mb-6">Capture detailed bug reports with environment data, categories, and priority levels automatically synced to the developer pool.</p>
        <ul class="space-y-3">
          <li class="flex items-center gap-3 text-xs font-black uppercase tracking-widest text-slate-700"><i data-lucide="check" class="w-4 h-4 text-emerald-500"></i> Auto-Environment Logging</li>
          <li class="flex items-center gap-3 text-xs font-black uppercase tracking-widest text-slate-700"><i data-lucide="check" class="w-4 h-4 text-emerald-500"></i> Screen Capture Support</li>
        </ul>
      </div>

      <div class="group p-10 bg-slate-50 rounded-[3rem] border border-slate-100 hover:bg-white hover:shadow-2xl transition duration-500">
        <div class="w-16 h-16 bg-indigo-600 text-white rounded-3xl flex items-center justify-center mb-8 group-hover:scale-110 transition">
          <i data-lucide="code-2" class="w-8 h-8"></i>
        </div>
        <h3 class="text-2xl font-black mb-4">For Developers</h3>
        <p class="text-slate-500 font-medium leading-relaxed mb-6">Focused workspaces where devs only see what’s assigned to them. Clear noise, fix bugs, and close tickets with one click.</p>
        <ul class="space-y-3">
          <li class="flex items-center gap-3 text-xs font-black uppercase tracking-widest text-slate-700"><i data-lucide="check" class="w-4 h-4 text-emerald-500"></i> Smart Triage Engine</li>
          <li class="flex items-center gap-3 text-xs font-black uppercase tracking-widest text-slate-700"><i data-lucide="check" class="w-4 h-4 text-emerald-500"></i> Role-Based Access Control</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<section id="about" class="py-32 bg-slate-900 relative overflow-hidden">
  <div class="absolute top-0 left-0 w-full h-full bg-[radial-gradient(circle_at_30%_20%,#2563eb1a_0%,transparent_50%)]"></div>
  <div class="max-w-7xl mx-auto px-6 relative z-10">
    <div class="grid lg:grid-cols-2 gap-20 items-center">
      <div>
        <h2 class="text-xs font-black text-blue-400 uppercase tracking-[0.3em] mb-6">Our Philosophy</h2>
        <h3 class="text-4xl md:text-5xl font-black text-white tracking-tighter mb-8 leading-tight">Built by developers <br>who hate messy backlogs.</h3>
        <p class="text-slate-400 text-lg font-medium leading-relaxed mb-8">
          Zappr was born from the frustration of traditional, bloated bug trackers. We believe tracking a bug shouldn't take longer than fixing it. Our platform is optimized for the "Developer Flow"—minimizing clicks and maximizing visibility.
        </p>
        <div class="grid grid-cols-2 gap-8">
            <div>
                <div class="text-3xl font-black text-white mb-1">40%</div>
                <div class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-400">Faster Resolution</div>
            </div>
            <div>
                <div class="text-3xl font-black text-white mb-1">0%</div>
                <div class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-400">Config Bloat</div>
            </div>
        </div>
      </div>
      <div class="grid grid-cols-1 gap-4">
        <div class="p-8 bg-slate-800/50 rounded-3xl border border-slate-700">
            <i data-lucide="cpu" class="text-blue-500 mb-4 w-6 h-6"></i>
            <h4 class="text-white font-bold mb-2">Atomic Updates</h4>
            <p class="text-slate-500 text-sm">Real-time synchronization using our MariaDB kernel. No page refreshes, just data.</p>
        </div>
        <div class="p-8 bg-slate-800/50 rounded-3xl border border-slate-700">
            <i data-lucide="activity" class="text-indigo-500 mb-4 w-6 h-6"></i>
            <h4 class="text-white font-bold mb-2">Activity Logging</h4>
            <p class="text-slate-500 text-sm">Every status change and assignment is logged for complete team accountability.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<footer class="bg-white py-20 border-t border-slate-100">
  <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-10">
    <div>
        <div class="flex items-center gap-2.5 mb-4">
            <div class="bg-blue-600 p-1.5 rounded-lg">
                <i data-lucide="zap" class="text-white w-4 h-4 fill-current"></i>
            </div>
            <span class="text-lg font-black tracking-tighter">ZAPPR<span class="text-blue-600">.</span></span>
        </div>
        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">© <?php echo date("Y"); ?> Zappr Management System. All rights reserved.</p>
    </div>
    
    <div class="flex gap-10">
        <a href="#" class="text-[10px] font-black uppercase tracking-widest text-slate-500 hover:text-blue-600">Twitter</a>
        <a href="#" class="text-[10px] font-black uppercase tracking-widest text-slate-500 hover:text-blue-600">GitHub</a>
        <a href="#" class="text-[10px] font-black uppercase tracking-widest text-slate-500 hover:text-blue-600">Status</a>
    </div>
  </div>
</footer>

<script>
  lucide.createIcons();
</script>
</body>
</html>