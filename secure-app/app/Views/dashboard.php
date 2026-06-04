<?= $this->include('templates/header', ['title' => 'Protected Dashboard']) ?>

<div class="w-full max-w-7xl mx-auto flex flex-col gap-6 relative z-10 px-4">
    
    <!-- Top Welcome Banner -->
    <div class="glass-panel p-8 rounded-2xl shadow-xl flex flex-col md:flex-row items-center justify-between gap-6 border-l-4 border-l-sky-500">
        <div class="flex items-center gap-6">
            <div class="w-20 h-20 relative shadow-[0_0_30px_rgba(56,189,248,0.2)] rounded-full flex items-center justify-center bg-slate-900/50 border border-slate-700 shrink-0">
                <span class="text-3xl relative z-10 drop-shadow-lg">👋</span>
            </div>
            <div class="text-center md:text-left">
                <h1 class="text-3xl md:text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-sky-400 to-indigo-500 tracking-tight drop-shadow-sm">
                    Welcome, <?= !empty($player_name) ? esc($player_name) : esc($username) ?>!
                </h1>
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-3 mt-2">
                    <span class="text-slate-400 text-sm">Clearance Level:</span>
                    <?php 
                        $roleColorClass = 'bg-sky-500/10 text-sky-400 border-sky-500/30';
                        if ($role === 'super_admin') {
                            $roleColorClass = 'bg-rose-500/10 text-rose-500 border-rose-500/30';
                        } elseif ($role === 'admin') {
                            $roleColorClass = 'bg-amber-500/10 text-amber-500 border-amber-500/30';
                        }
                    ?>
                    <span class="px-3 py-1 rounded-full text-xs font-bold border <?= $roleColorClass ?>">
                        <?= strtoupper(str_replace('_', ' ', esc($role))) ?>
                    </span>
                    <?php if (!empty($player_name)): ?>
                        <span class="text-xs text-slate-500 italic bg-slate-900/50 px-2 py-1 rounded">Login ID: <?= esc($username) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Quick Stats Widget -->
        <div class="flex gap-4">
            <div class="bg-slate-900/60 border border-slate-700/60 rounded-xl p-4 text-center min-w-[120px]">
                <div class="text-3xl font-black text-emerald-400"><?= number_format($total_members ?? 0) ?></div>
                <div class="text-xs text-slate-400 font-bold uppercase tracking-wider mt-1">Active Members</div>
            </div>
            
            <?php if ($role === 'admin' || $role === 'super_admin'): ?>
                <a href="/admin/approvals" class="bg-slate-900/60 border <?= ($pending_approvals ?? 0) > 0 ? 'border-amber-500/50 shadow-[0_0_15px_rgba(245,158,11,0.2)]' : 'border-slate-700/60' ?> hover:bg-slate-800 transition-colors rounded-xl p-4 text-center min-w-[120px] group cursor-pointer block">
                    <div class="text-3xl font-black <?= ($pending_approvals ?? 0) > 0 ? 'text-amber-400' : 'text-slate-500' ?>"><?= number_format($pending_approvals ?? 0) ?></div>
                    <div class="text-xs <?= ($pending_approvals ?? 0) > 0 ? 'text-amber-400' : 'text-slate-400' ?> font-bold uppercase tracking-wider mt-1 group-hover:text-amber-300">Pending Recruits</div>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Layout Grid -->
    <div class="flex flex-col lg:flex-row gap-6">
        
        <!-- Left Column: Navigation Grid -->
        <div class="w-full lg:w-2/3 flex flex-col gap-6">
            
            <!-- Weekly Events Section -->
            <h2 class="text-xl font-bold text-white flex items-center gap-2"><span>🗓️</span> Weekly Events</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Desert Storm Planner -->
                <a href="/ds_planner" class="flex flex-col justify-center p-6 bg-slate-900/80 hover:bg-slate-800 border border-slate-700/60 hover:border-sky-500/50 rounded-xl transition-all duration-300 cursor-pointer shadow-lg hover:shadow-sky-900/20 group relative overflow-hidden h-32">
                    <div class="absolute -right-4 -bottom-4 text-6xl opacity-10 group-hover:scale-110 transition-transform duration-300">🗺️</div>
                    <div class="relative z-10">
                        <h3 class="font-bold text-slate-200 text-lg mb-1 flex items-center gap-2"><span>🗺️</span> Desert Storm</h3>
                        <p class="text-xs text-slate-400">Tactical map room for assignments</p>
                    </div>
                </a>

                <!-- Canyon Storm Planner -->
                <a href="/cs_planner" class="flex flex-col justify-center p-6 bg-slate-900/80 hover:bg-slate-800 border border-slate-700/60 hover:border-amber-500/50 rounded-xl transition-all duration-300 cursor-pointer shadow-lg hover:shadow-amber-900/20 group relative overflow-hidden h-32">
                    <div class="absolute -right-4 -bottom-4 text-6xl opacity-10 group-hover:scale-110 transition-transform duration-300">⛰️</div>
                    <div class="relative z-10">
                        <h3 class="font-bold text-slate-200 text-lg mb-1 flex items-center gap-2"><span>⛰️</span> Canyon Storm</h3>
                        <p class="text-xs text-slate-400">Tactical map room for Canyon events</p>
                    </div>
                </a>

                <!-- Gold Train -->
                <div class="flex items-center bg-slate-900/80 border border-slate-700/60 rounded-xl hover:border-yellow-500/50 transition-all duration-300 shadow-lg group overflow-hidden h-32 relative">
                    <div class="absolute -right-4 -bottom-4 text-6xl opacity-10 group-hover:scale-110 transition-transform duration-300">🚂</div>
                    <div class="flex-1 h-full flex flex-col justify-center p-6 cursor-pointer hover:bg-slate-800 relative z-10" onclick="window.location.href='/gold-train'">
                        <h3 class="font-bold text-yellow-500 text-lg mb-1 flex items-center gap-2"><span>🚂</span> Polar Express</h3>
                        <p class="text-xs text-slate-400">Manage Gold Train schedules</p>
                    </div>
                    <div class="h-full border-l border-slate-700/50 flex flex-col relative z-10">
                        <a href="/gold-train/history" class="flex-1 px-4 flex items-center justify-center bg-slate-800/50 hover:bg-slate-700 hover:text-white text-slate-300 text-xs font-bold transition-colors" title="View Train History">
                            🕰️ History
                        </a>
                    </div>
                </div>
            </div>

            <!-- Command Center Section -->
            <h2 class="text-xl font-bold text-white flex items-center gap-2 mt-4"><span>🚀</span> Command Center</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Weekly Event Log -->
                <a href="<?= ($role === 'admin' || $role === 'super_admin') ? '/admin/events' : '/events/log' ?>" class="flex flex-col justify-center p-6 bg-slate-900/80 hover:bg-slate-800 border border-slate-700/60 hover:border-emerald-500/50 rounded-xl transition-all duration-300 cursor-pointer shadow-lg hover:shadow-emerald-900/20 group relative overflow-hidden h-32">
                    <div class="absolute -right-4 -bottom-4 text-6xl opacity-10 group-hover:scale-110 transition-transform duration-300">📊</div>
                    <div class="relative z-10">
                        <h3 class="font-bold text-emerald-400 text-lg mb-1 flex items-center gap-2"><span>📊</span> Weekly Events Log</h3>
                        <p class="text-xs text-slate-400">Track participation & match scores</p>
                    </div>
                </a>

                <!-- Growth Analytics -->
                <a href="/growth" class="flex flex-col justify-center p-6 bg-slate-900/80 hover:bg-slate-800 border border-slate-700/60 hover:border-sky-500/50 rounded-xl transition-all duration-300 cursor-pointer shadow-lg hover:shadow-sky-900/20 group relative overflow-hidden h-32">
                    <div class="absolute -right-4 -bottom-4 text-6xl opacity-10 group-hover:scale-110 transition-transform duration-300">📈</div>
                    <div class="relative z-10">
                        <h3 class="font-bold text-slate-200 text-lg mb-1 flex items-center gap-2"><span>📈</span> Growth Leaderboard</h3>
                        <p class="text-xs text-slate-400">View alliance power analytics</p>
                    </div>
                </a>
            </div>

            <h2 class="text-xl font-bold text-white flex items-center gap-2 mt-4"><span>👤</span> Personal & Admin Settings</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- My Squad Profile -->
                <a href="/profile/squad" class="flex items-center justify-between p-4 bg-slate-900/60 hover:bg-slate-800 border border-slate-700/60 hover:border-sky-500/50 rounded-xl transition-all duration-300 cursor-pointer group">
                    <div class="flex items-center gap-3">
                        <span class="text-xl">🛡️</span>
                        <div class="text-left">
                            <h3 class="font-bold text-slate-200">My Squad Powers</h3>
                            <p class="text-xs text-slate-500">Update Tank/Air/Missile</p>
                        </div>
                    </div>
                    <span class="text-sky-400 opacity-0 group-hover:opacity-100 transition-opacity">→</span>
                </a>

                <?php if ($role === 'admin' || $role === 'super_admin'): ?>
                    <!-- Match Results Editor -->
                    <a href="/admin/events/select" class="flex items-center justify-between p-4 bg-slate-900/60 hover:bg-slate-800 border border-slate-700/60 hover:border-amber-500/50 rounded-xl transition-all duration-300 cursor-pointer group">
                        <div class="flex items-center gap-3">
                            <span class="text-xl">⚔️</span>
                            <div class="text-left">
                                <h3 class="font-bold text-amber-400">Match Results Editor</h3>
                                <p class="text-xs text-slate-500">Edit past scores</p>
                            </div>
                        </div>
                        <span class="text-amber-500 opacity-0 group-hover:opacity-100 transition-opacity">→</span>
                    </a>

                    <!-- Admin Settings -->
                    <a href="/admin/settings" class="flex items-center justify-between p-4 bg-slate-900/60 hover:bg-slate-800 border border-slate-700/60 hover:border-rose-500/50 rounded-xl transition-all duration-300 cursor-pointer group md:col-span-2">
                        <div class="flex items-center gap-3">
                            <span class="text-xl">⚙️</span>
                            <div class="text-left">
                                <h3 class="font-bold text-rose-400">System Administration</h3>
                                <p class="text-xs text-slate-500">Manage entire roster & configurations</p>
                            </div>
                        </div>
                        <span class="text-rose-400 opacity-0 group-hover:opacity-100 transition-opacity">→</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Column: Top 10 Leaderboards -->
        <div class="w-full lg:w-1/3 flex flex-col gap-6">
            
            <!-- Overall Power Leaderboard -->
            <div class="glass-panel p-6 rounded-2xl shadow-xl flex flex-col border-t-4 border-t-amber-500 relative z-30">
                <h3 class="text-lg font-extrabold text-white mb-1 flex items-center gap-2 justify-between shrink-0">
                    <div class="flex items-center gap-2"><span>🏆</span> Top 10 Powers</div>
                    <a href="/growth" class="text-xs font-bold text-amber-500 border border-amber-500/50 hover:bg-amber-500 hover:text-slate-900 px-2 py-1 rounded transition-colors">See All →</a>
                </h3>
                <p class="text-xs text-slate-400 mb-4 pb-4 border-b border-slate-700/50 shrink-0">Highest total combined combat power.</p>
                
                <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar" style="max-height: 250px;">
                    <div class="flex flex-col gap-3 pb-2">
                        <?php if (empty($top_players)): ?>
                            <div class="text-center text-sm text-slate-500 mt-4">No squad data available yet.</div>
                        <?php else: ?>
                            <?php foreach ($top_players as $index => $player): ?>
                                <div class="flex items-center justify-between p-3 bg-slate-900/40 border border-slate-800/80 rounded-lg hover:bg-slate-800 transition-colors relative group/row z-10">
                                    <div class="flex items-center gap-3">
                                        <div class="w-6 text-center font-black <?= $index === 0 ? 'text-amber-400 text-lg' : ($index === 1 ? 'text-slate-300' : ($index === 2 ? 'text-amber-700' : 'text-slate-600')) ?>">
                                            #<?= $index + 1 ?>
                                        </div>
                                        <div class="flex flex-col relative cursor-help">
                                            <span class="font-bold text-slate-200 text-sm">
                                                <?= !empty($player['player_name']) ? esc($player['player_name']) : esc($player['username']) ?>
                                            </span>
                                            <span class="text-[10px] text-slate-500 font-bold uppercase"><?= esc($player['alliance_level']) ?></span>
                                            
                                            <!-- Hover Tooltip - Positioned Below and Centered -->
                                            <div class="absolute left-1/2 top-[calc(100%+8px)] -translate-x-1/2 bg-slate-800/95 backdrop-blur-md border border-slate-600 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)] p-4 rounded-xl opacity-0 pointer-events-none group-hover/row:opacity-100 transition-all duration-200 w-52 text-left flex flex-col gap-2 hidden lg:flex z-[100]">
                                                <!-- Arrow pointer (Pointing UP) -->
                                                <div class="absolute -top-1.5 left-1/2 -translate-x-1/2 w-3 h-3 bg-slate-800 border-l border-t border-slate-600 rotate-45"></div>

                                                <div class="text-[10px] uppercase font-black tracking-widest text-amber-500 border-b border-slate-700/50 pb-2 mb-1">Squad Details</div>
                                                <div class="flex justify-between items-center text-xs">
                                                    <span class="text-slate-400 flex items-center gap-2">🚜 <span class="font-bold">Tank</span></span> 
                                                    <span class="font-mono text-slate-200 font-bold"><?= number_format($player['power_tank'] ?? 0, 2) ?></span>
                                                </div>
                                                <div class="flex justify-between items-center text-xs">
                                                    <span class="text-slate-400 flex items-center gap-2">🚁 <span class="font-bold">Air</span></span> 
                                                    <span class="font-mono text-slate-200 font-bold"><?= number_format($player['power_air'] ?? 0, 2) ?></span>
                                                </div>
                                                <div class="flex justify-between items-center text-xs">
                                                    <span class="text-slate-400 flex items-center gap-2">🚀 <span class="font-bold">Missile</span></span> 
                                                    <span class="font-mono text-slate-200 font-bold"><?= number_format($player['power_missile'] ?? 0, 2) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="font-mono font-bold text-sky-400 text-sm z-10 relative">
                                        <?= number_format($player['total_power'], 2) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Fastest Growth Leaderboard -->
            <div class="glass-panel p-6 rounded-2xl shadow-xl flex flex-col border-t-4 border-t-emerald-500 relative z-20">
                <h3 class="text-lg font-extrabold text-white mb-1 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-2"><span>🚀</span> Most Growth (30d)</div>
                    <a href="/growth" class="text-xs font-bold text-emerald-500 border border-emerald-500/50 hover:bg-emerald-500 hover:text-slate-900 px-2 py-1 rounded transition-colors">See All →</a>
                </h3>
                <p class="text-xs text-slate-400 mb-4 pb-4 border-b border-slate-700/50 shrink-0">Total combined power gained over the last 30 days.</p>
                
                <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar" style="max-height: 250px;">
                    <div class="flex flex-col gap-3 pb-2">
                        <?php if (empty($growth_leaders)): ?>
                            <div class="text-center text-sm text-slate-500 mt-4">No positive growth recorded in the last 30 days.</div>
                        <?php else: ?>
                            <?php foreach ($growth_leaders as $index => $leader): ?>
                                <div class="flex items-center justify-between p-3 bg-slate-900/40 border border-slate-800/80 rounded-lg hover:bg-slate-800 transition-colors relative group/row z-10">
                                    <div class="flex items-center gap-3">
                                        <div class="w-6 text-center font-black <?= $index === 0 ? 'text-emerald-400 text-lg' : ($index === 1 ? 'text-emerald-500' : ($index === 2 ? 'text-emerald-600' : 'text-slate-600')) ?>">
                                            #<?= $index + 1 ?>
                                        </div>
                                        <div class="flex flex-col relative cursor-help">
                                            <span class="font-bold text-slate-200 text-sm">
                                                <?= !empty($leader['player_name']) ? esc($leader['player_name']) : esc($leader['username']) ?>
                                            </span>
                                            <span class="text-[10px] text-slate-500 font-bold uppercase"><?= esc($leader['alliance_level']) ?></span>
                                            
                                            <!-- Hover Tooltip - Positioned Below and Centered -->
                                            <div class="absolute left-1/2 top-[calc(100%+8px)] -translate-x-1/2 bg-slate-800/95 backdrop-blur-md border border-slate-600 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)] p-4 rounded-xl opacity-0 pointer-events-none group-hover/row:opacity-100 transition-all duration-200 w-52 text-left flex flex-col gap-2 hidden lg:flex z-[100]">
                                                <!-- Arrow pointer (Pointing UP) -->
                                                <div class="absolute -top-1.5 left-1/2 -translate-x-1/2 w-3 h-3 bg-slate-800 border-l border-t border-slate-600 rotate-45"></div>

                                                <div class="text-[10px] uppercase font-black tracking-widest text-emerald-500 border-b border-slate-700/50 pb-2 mb-1">Growth Breakdown</div>
                                                <div class="flex justify-between items-center text-xs">
                                                    <span class="text-slate-400 flex items-center gap-2">🚜 <span class="font-bold">Tank</span></span> 
                                                    <span class="font-mono font-bold <?= $leader['total_tank_diff'] >= 0 ? 'text-emerald-400' : 'text-rose-400' ?>"><?= $leader['total_tank_diff'] > 0 ? '+' : '' ?><?= number_format($leader['total_tank_diff'], 2) ?></span>
                                                </div>
                                                <div class="flex justify-between items-center text-xs">
                                                    <span class="text-slate-400 flex items-center gap-2">🚁 <span class="font-bold">Air</span></span> 
                                                    <span class="font-mono font-bold <?= $leader['total_air_diff'] >= 0 ? 'text-emerald-400' : 'text-rose-400' ?>"><?= $leader['total_air_diff'] > 0 ? '+' : '' ?><?= number_format($leader['total_air_diff'], 2) ?></span>
                                                </div>
                                                <div class="flex justify-between items-center text-xs">
                                                    <span class="text-slate-400 flex items-center gap-2">🚀 <span class="font-bold">Missile</span></span> 
                                                    <span class="font-mono font-bold <?= $leader['total_missile_diff'] >= 0 ? 'text-emerald-400' : 'text-rose-400' ?>"><?= $leader['total_missile_diff'] > 0 ? '+' : '' ?><?= number_format($leader['total_missile_diff'], 2) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="font-mono font-bold text-emerald-400 text-sm z-10 relative">
                                        +<?= number_format($leader['total_growth'], 2) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #475569; }
</style>

<?= $this->include('templates/footer') ?>