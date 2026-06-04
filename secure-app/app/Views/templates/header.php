<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'MDK Alliance Administration') ?></title>

    <!-- Explicitly defining the favicon -->
    <link rel="icon" type="image/x-icon" href="/images/mdk_favicon.ico">

    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Shared Styles -->
    <style>
        .glass-panel {
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .nav-glass {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        /* Dropdown Styles */
        .dropdown:hover .dropdown-menu {
            display: block;
        }
    </style>
</head>
<body class="bg-slate-950 min-h-screen text-slate-100 flex flex-col items-center justify-start relative font-sans pt-20">
    <!-- Decorative Background Elements -->
    <div class="fixed top-[-10%] left-[-10%] w-[500px] h-[500px] bg-indigo-900/30 rounded-full blur-[100px] pointer-events-none z-0"></div>
    <div class="fixed bottom-[-10%] right-[-10%] w-[500px] h-[500px] bg-amber-900/20 rounded-full blur-[100px] pointer-events-none z-0"></div>
    
    <!-- Global Navigation Bar -->
    <nav class="fixed top-0 left-0 w-full z-50 nav-glass">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo & Brand -->
                <div class="flex-shrink-0 flex items-center gap-3 cursor-pointer" onclick="window.location.href='/'">
                    <img class="h-8 w-8 object-contain rounded-full shadow-[0_0_10px_rgba(245,158,11,0.2)]" src="/images/mdk_logo.png" alt="MDK Logo" onerror="this.src='/images/mdk_log.png';">
                    <span class="font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-amber-600 tracking-tight">MDK Alliance</span>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:block">
                    <div class="ml-10 flex items-center space-x-2">
                        <?php if (session()->get('is_logged_in')): ?>
                            <?php $currentSeg = current_url(true)->getSegment(1); ?>
                            
                            <a href="/dashboard" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-slate-800 hover:text-white <?= $currentSeg === 'dashboard' ? 'bg-slate-800 text-white shadow-inner' : 'text-slate-300' ?>">Dashboard</a>
                            
                            <a href="/growth" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-slate-800 hover:text-white <?= $currentSeg === 'growth' ? 'bg-slate-800 text-white shadow-inner' : 'text-slate-300' ?>">Growth Leaderboard</a>
                            
                            <!-- Events Dropdown Menu -->
                            <div class="dropdown relative inline-block">
                                <button class="px-3 py-2 rounded-md text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white flex items-center gap-1 <?= in_array($currentSeg, ['ds_planner', 'cs_planner', 'events', 'gold-train']) ? 'bg-slate-800 text-white shadow-inner' : '' ?>">
                                    Events ▾
                                </button>
                                <div class="dropdown-menu absolute hidden pt-2 w-48 z-50">
                                    <div class="bg-slate-800 border border-slate-700 rounded-lg shadow-xl overflow-hidden py-1">
                                        <div class="px-3 py-1.5 text-[10px] font-black text-slate-500 uppercase tracking-wider bg-slate-800/50 border-b border-slate-700/50">Weekly Events</div>
                                        <a href="/ds_planner" class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-700 hover:text-white transition-colors">🗺️ Desert Storm</a>
                                        <a href="/cs_planner" class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-700 hover:text-white transition-colors">⛰️ Canyon Storm</a>
                                        <a href="/gold-train" class="block px-4 py-2 text-sm text-amber-400 hover:bg-slate-700 hover:text-amber-300 transition-colors border-t border-slate-700/50">🚂 Polar Express</a>
                                        <a href="/gold-train/history" class="block px-4 py-2 text-sm text-amber-500 hover:bg-slate-700 hover:text-amber-400 transition-colors pl-8">🕰️ Train History</a>

                                        <div class="px-3 py-1.5 text-[10px] font-black text-slate-500 uppercase tracking-wider bg-slate-800/50 border-y border-slate-700/50 mt-1">Analytics</div>
                                        <a href="<?= session()->get('role') === 'admin' || session()->get('role') === 'super_admin' ? '/admin/events' : '/events/log' ?>" class="block px-4 py-2 text-sm text-emerald-400 hover:bg-slate-700 hover:text-emerald-300 transition-colors">📊 Event Tracker</a>
                                    </div>
                                </div>
                            </div>

                            <a href="/profile/squad" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-slate-800 hover:text-white <?= $currentSeg === 'profile' ? 'bg-slate-800 text-white shadow-inner' : 'text-slate-300' ?>">My Squad</a>

                            <?php if (session()->get('role') === 'admin' || session()->get('role') === 'super_admin'): ?>
                                <!-- Admin Dropdown Menu -->
                                <div class="dropdown relative inline-block">
                                    <button class="px-3 py-2 rounded-md text-sm font-medium text-amber-400 hover:bg-slate-800 hover:text-amber-300 border border-transparent hover:border-slate-700 flex items-center gap-1 <?= in_array($currentSeg, ['admin', 'super_admin']) ? 'bg-slate-800 shadow-inner border-slate-700' : '' ?>">
                                        Admin ▾
                                    </button>
                                    <div class="dropdown-menu absolute hidden pt-2 w-48 z-50">
                                        <div class="bg-slate-800 border border-slate-700 rounded-lg shadow-xl overflow-hidden py-1">
                                            <a href="/admin/settings" class="block px-4 py-2 text-sm text-amber-400 hover:bg-slate-700 hover:text-amber-300 transition-colors">⚙️ Settings</a>
                                            <?php if (session()->get('role') === 'super_admin'): ?>
                                                <div class="px-3 py-1.5 text-[10px] font-black text-slate-500 uppercase tracking-wider bg-slate-800/50 border-y border-slate-700/50 mt-1">Super Admin</div>
                                                <a href="/super_admin/access-logs" class="block px-4 py-2 text-sm text-emerald-400 hover:bg-slate-700 hover:text-emerald-300 transition-colors">👁️ Access Logs</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <a href="/logout" class="ml-4 px-3 py-2 rounded-md text-sm font-medium text-rose-400 hover:bg-rose-900/30 hover:text-rose-300 border border-transparent hover:border-rose-900/50">Logout</a>
                        <?php else: ?>
                            <a href="/login" class="px-3 py-2 rounded-md text-sm font-medium text-slate-300 hover:text-white">Login</a>
                            <a href="/register" class="ml-2 px-4 py-2 rounded-md text-sm font-bold bg-amber-500 hover:bg-amber-400 text-slate-900 shadow-lg shadow-amber-500/20">Apply</a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Mobile Menu Button (JS logic can be added later if needed) -->
                <div class="md:hidden flex items-center">
                    <span class="text-slate-400 text-xs mr-2">Use desktop for planning</span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Wrapper -->
    <main class="w-full max-w-7xl mx-auto flex flex-col items-center flex-1 py-10 z-10 relative">