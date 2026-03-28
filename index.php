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
  <link href="<?php echo $base_url; ?>dist/output.css" rel="stylesheet">
  <script src="https://unpkg.com/lucide@0.344.0/dist/umd/lucide.js"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
    body { font-family: 'Plus+Jakarta+Sans', sans-serif; }
    .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(12px); }
  </style>
</head>

<body class="bg-[#fcfcfd] text-slate-900 antialiased">

<nav class="fixed top-0 w-full z-[100] border-b border-slate-200/60 glass">
  <div class="max-w-7xl mx-auto px-6 h-20 flex justify-between items-center">
    <div class="flex items-center gap-2.5">
      <div class="bg-blue-600 p-2 rounded-xl">
        <i data-lucide="zap" class="text-white w-5 h-5 fill-current"></i>
      </div>
      <span class="text-xl font-bold tracking-tight text-slate-900">Zappr.</span>
    </div>

    <div class="hidden md:flex items-center gap-10">
      <a href="#features" class="text-sm font-medium text-slate-500 hover:text-blue-600 transition">Product</a>
      <a href="#solutions" class="text-sm font-medium text-slate-500 hover:text-blue-600 transition">Solutions</a>
      <a href="#about" class="text-sm font-medium text-slate-500 hover:text-blue-600 transition">About</a>
    </div>

    <div class="flex items-center gap-3">
      <a href="<?php echo $base_url; ?>public/login/index.php" class="px-5 py-2.5 text-sm font-semibold text-slate-700 hover:text-blue-600 transition">Log in</a>
      <a href="<?php echo $base_url; ?>public/register/index.php" class="bg-slate-900 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-600 transition shadow-lg shadow-slate-200">Get Started</a>
    </div>
  </div>
</nav>

<section class="relative pt-40 pb-20 overflow-hidden">
  <div class="max-w-7xl mx-auto px-6 grid lg:grid-cols-2 gap-16 items-center">
    <div>
      <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 border border-blue-100 mb-6">
        <span class="relative flex h-2 w-2">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
          <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
        </span>
        <span class="text-xs font-bold text-blue-700 uppercase tracking-wider">v2.0 is now live</span>
      </div>
      <h1 class="text-6xl lg:text-7xl font-extrabold leading-[1.1] mb-8 tracking-tight">
        Debug with <br><span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-500">Confidence.</span>
      </h1>
      <p class="text-lg text-slate-500 mb-10 leading-relaxed max-w-md">
        The issue tracker that actually helps you code. Centralize reports, automate triage, and ship faster than ever.
      </p>
      <div class="flex flex-wrap gap-4">
        <a href="#" class="bg-blue-600 text-white px-8 py-4 rounded-2xl font-bold hover:scale-105 transition shadow-xl shadow-blue-200">Start Free Project</a>
        <div class="flex -space-x-3 items-center ml-4">
          <img class="w-10 h-10 rounded-full border-4 border-white" src="https://ui-avatars.com/api/?name=JD&bg=6366f1&color=fff" alt="">
          <img class="w-10 h-10 rounded-full border-4 border-white" src="https://ui-avatars.com/api/?name=AB&bg=3b82f6&color=fff" alt="">
          <img class="w-10 h-10 rounded-full border-4 border-white" src="https://ui-avatars.com/api/?name=MS&bg=10b981&color=fff" alt="">
          <span class="pl-6 text-sm font-medium text-slate-400 font-mono">Join 2k+ developers</span>
        </div>
      </div>
    </div>

    <div class="relative">
      <div class="absolute -inset-4 bg-gradient-to-tr from-blue-100 to-purple-100 rounded-[3rem] blur-2xl opacity-50"></div>
      <div class="relative bg-slate-900 rounded-[2rem] border border-slate-800 shadow-2xl p-4 overflow-hidden">
        <div class="flex gap-1.5 mb-4 px-2">
          <div class="w-2.5 h-2.5 rounded-full bg-slate-700"></div>
          <div class="w-2.5 h-2.5 rounded-full bg-slate-700"></div>
          <div class="w-2.5 h-2.5 rounded-full bg-slate-700"></div>
        </div>
        <div class="space-y-3">
          <div class="bg-slate-800/50 h-8 w-full rounded-lg"></div>
          <div class="grid grid-cols-3 gap-3">
            <div class="bg-blue-500/10 border border-blue-500/20 h-24 rounded-xl flex flex-col items-center justify-center">
              <span class="text-blue-400 text-2xl font-bold">12</span>
              <span class="text-[10px] text-blue-300/60 uppercase">Active</span>
            </div>
            <div class="bg-slate-800/50 h-24 rounded-xl"></div>
            <div class="bg-slate-800/50 h-24 rounded-xl"></div>
          </div>
          <div class="bg-slate-800/30 h-32 w-full rounded-xl border border-slate-700/50 p-4">
            <div class="flex justify-between items-center mb-4">
              <div class="h-2 w-20 bg-slate-600 rounded"></div>
              <div class="h-4 w-12 bg-green-500/20 rounded-full"></div>
            </div>
            <div class="space-y-2">
              <div class="h-2 w-full bg-slate-700 rounded"></div>
              <div class="h-2 w-3/4 bg-slate-700 rounded"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="features" class="py-32 bg-white">
  <div class="max-w-7xl mx-auto px-6">
    <div class="flex flex-col md:flex-row justify-between items-end mb-20 gap-6">
      <div class="max-w-xl">
        <h2 class="text-4xl font-bold tracking-tight mb-4">Everything you need, <br>nothing you don't.</h2>
        <p class="text-slate-500 text-lg">We stripped away the clutter of legacy trackers to focus on developer speed.</p>
      </div>
      <a href="#" class="group text-blue-600 font-bold flex items-center gap-2">
        See all features <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition"></i>
      </a>
    </div>

    <div class="grid md:grid-cols-3 gap-8">
      <div class="p-10 rounded-[2.5rem] bg-slate-50 hover:bg-white hover:shadow-2xl hover:shadow-blue-100 transition duration-500 border border-transparent hover:border-blue-50">
        <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center shadow-sm mb-8">
          <i data-lucide="layers" class="text-blue-600"></i>
        </div>
        <h3 class="text-xl font-bold mb-4">Smart Grouping</h3>
        <p class="text-slate-500 leading-relaxed">Automatically bundle identical error reports. Keep your backlog clean and actionable.</p>
      </div>

      <div class="p-10 rounded-[2.5rem] bg-slate-50 hover:bg-white hover:shadow-2xl hover:shadow-blue-100 transition duration-500 border border-transparent hover:border-blue-50">
        <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center shadow-sm mb-8">
          <i data-lucide="terminal" class="text-indigo-600"></i>
        </div>
        <h3 class="text-xl font-bold mb-4">Technical Context</h3>
        <p class="text-slate-500 leading-relaxed">Logs, stack traces, and browser versions are captured automatically with every report.</p>
      </div>

      <div class="p-10 rounded-[2.5rem] bg-slate-50 hover:bg-white hover:shadow-2xl hover:shadow-blue-100 transition duration-500 border border-transparent hover:border-blue-50">
        <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center shadow-sm mb-8">
          <i data-lucide="git-branch" class="text-purple-600"></i>
        </div>
        <h3 class="text-xl font-bold mb-4">Git Integration</h3>
        <p class="text-slate-500 leading-relaxed">Link issues directly to commits. Close bugs automatically when your PR is merged.</p>
      </div>
    </div>
  </div>
</section>

<section class="py-20 px-6">
  <div class="max-w-5xl mx-auto bg-slate-900 rounded-[3rem] p-12 md:p-20 relative overflow-hidden text-center">
    <div class="absolute top-0 right-0 w-64 h-64 bg-blue-600/20 blur-[100px]"></div>
    <div class="relative z-10">
      <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">Stop chasing ghosts. <br>Start shipping code.</h2>
      <p class="text-slate-400 text-lg mb-10 max-w-lg mx-auto">Get your team on the same page and reduce time-to-fix by up to 40%.</p>
      <div class="flex flex-col sm:flex-row justify-center gap-4">
        <button class="bg-white text-slate-900 px-8 py-4 rounded-2xl font-bold hover:bg-blue-50 transition">Create your workspace</button>
        <button class="border border-slate-700 text-white px-8 py-4 rounded-2xl font-bold hover:bg-slate-800 transition">Schedule Demo</button>
      </div>
    </div>
  </div>
</section>

<footer class="bg-white py-16 border-t border-slate-100">
  <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-4 gap-12">
    <div class="col-span-2">
      <div class="flex items-center gap-2.5 mb-6">
        <div class="bg-blue-600 p-1.5 rounded-lg">
          <i data-lucide="zap" class="text-white w-4 h-4 fill-current"></i>
        </div>
        <span class="text-lg font-bold">Zappr.</span>
      </div>
      <p class="text-slate-500 max-w-xs">Building the future of error management. Made for developers, by developers.</p>
    </div>
    <div>
      <h4 class="font-bold mb-6">Connect</h4>
      <div class="flex gap-4">
        <a href="#" class="p-3 bg-slate-50 rounded-xl text-slate-400 hover:text-blue-600 transition"><i data-lucide="twitter" class="w-5 h-5"></i></a>
        <a href="#" class="p-3 bg-slate-50 rounded-xl text-slate-400 hover:text-blue-600 transition"><i data-lucide="github" class="w-5 h-5"></i></a>
      </div>
    </div>
    <div>
      <p class="text-sm font-medium text-slate-400">© <?php echo date("Y"); ?> Zappr Inc. <br> All rights reserved.</p>
    </div>
  </div>
</footer>

<script>
  lucide.createIcons();
</script>
</body>
</html>