<?= $this->include('templates/header', ['title' => 'Squad Growth - Admin']) ?>

<div class="glass-panel p-8 md:p-12 rounded-2xl shadow-2xl max-w-7xl w-full relative z-10 mx-4 my-8">
    
    <!-- Header Section -->
    <div class="mb-8 flex flex-col items-center border-b border-slate-800 pb-8">
        <div class="w-20 h-20 mb-4 relative shadow-[0_0_40px_rgba(16,185,129,0.2)] rounded-full flex items-center justify-center bg-slate-900/50">
            <div class="absolute inset-0 rounded-full border-2 border-emerald-500/50 animate-[pulse_3s_linear_infinite]"></div>
            <span class="text-3xl relative z-10 drop-shadow-lg">📈</span>
        </div>

        <h1 class="text-2xl md:text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 via-green-300 to-emerald-500 mb-2 tracking-tight drop-shadow-sm text-center">
            Alliance Growth Analytics
        </h1>
        <p class="text-slate-400 text-sm text-center">View power progression and historical updates across all members.</p>
    </div>

    <!-- Analytics Dashboard Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-slate-900/80 border border-slate-700/60 rounded-xl p-4 text-center">
            <div class="text-sm text-slate-400 font-bold uppercase mb-1">Total Tank Power</div>
            <div class="text-2xl font-mono text-emerald-400"><?= number_format($alliance_totals['tank'], 2) ?></div>
        </div>
        <div class="bg-slate-900/80 border border-slate-700/60 rounded-xl p-4 text-center">
            <div class="text-sm text-slate-400 font-bold uppercase mb-1">Total Air Power</div>
            <div class="text-2xl font-mono text-sky-400"><?= number_format($alliance_totals['air'], 2) ?></div>
        </div>
        <div class="bg-slate-900/80 border border-slate-700/60 rounded-xl p-4 text-center">
            <div class="text-sm text-slate-400 font-bold uppercase mb-1">Total Missile Power</div>
            <div class="text-2xl font-mono text-rose-400"><?= number_format($alliance_totals['missile'], 2) ?></div>
        </div>
        <div class="bg-slate-900/80 border border-amber-500/30 rounded-xl p-4 text-center shadow-[0_0_15px_rgba(245,158,11,0.1)]">
            <div class="text-sm text-amber-400 font-bold uppercase mb-1">Overall Alliance Power</div>
            <div class="text-2xl font-black font-mono text-amber-500"><?= number_format($alliance_totals['overall'], 2) ?></div>
        </div>
    </div>

    <!-- Filter Form -->
    <form action="/admin/users/squad_powers/growth" method="GET" class="mb-6 flex flex-col md:flex-row items-end gap-4 bg-slate-900/40 p-4 rounded-xl border border-slate-800 justify-between">
        <div class="flex items-center gap-4 flex-1">
            <div>
                <label class="block text-slate-400 text-xs font-bold mb-1">Filter by Username (Optional):</label>
                <input type="text" name="username_filter" value="<?= esc($username_filter) ?>" placeholder="Search..."
                    class="w-64 bg-slate-950/50 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500 transition-colors">
            </div>
            <div>
                <label class="block text-slate-400 text-xs font-bold mb-1">Time Range:</label>
                <select name="days_limit" class="bg-slate-950/50 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500 transition-colors">
                    <option value="7" <?= $days_limit == 7 ? 'selected' : '' ?>>Last 7 Days</option>
                    <option value="14" <?= $days_limit == 14 ? 'selected' : '' ?>>Last 14 Days</option>
                    <option value="30" <?= $days_limit == 30 ? 'selected' : '' ?>>Last 30 Days</option>
                    <option value="90" <?= $days_limit == 90 ? 'selected' : '' ?>>Last 90 Days</option>
                    <option value="0" <?= $days_limit == 0 ? 'selected' : '' ?>>All Time</option>
                </select>
            </div>
            <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-600 px-6 py-2 rounded-lg text-sm font-bold transition-colors">
                Apply
            </button>
        </div>
        <div class="text-xs text-slate-500 text-right">
            Showing <?= count($growth_history) ?> log entries.
        </div>
    </form>

    <!-- Detailed History Table -->
    <div class="bg-slate-900/60 border border-slate-700/60 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="text-xs uppercase bg-slate-800/80 text-slate-400 border-b border-slate-700">
                    <tr>
                        <th class="px-6 py-4">Date Logged</th>
                        <th class="px-6 py-4">Member</th>
                        <th class="px-6 py-4 text-center">Days Since Prev</th>
                        <th class="px-6 py-4 text-right">New Total Power</th>
                        <th class="px-6 py-4 text-right">Total Growth</th>
                        <th class="px-6 py-4 text-right">🚜 Tank Growth</th>
                        <th class="px-6 py-4 text-right">🚁 Air Growth</th>
                        <th class="px-6 py-4 text-right">🚀 Missile Growth</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($growth_history)): ?>
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-500 italic">No growth records found matching these filters.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($growth_history as $log): 
                            $totalDiff = $log['tank_diff'] + $log['air_diff'] + $log['missile_diff'];
                            $newTotal = $log['power_tank'] + $log['power_air'] + $log['power_missile'];
                        ?>
                            <tr class="border-b border-slate-700/50 hover:bg-slate-800/30 transition-colors">
                                <td class="px-6 py-4 font-mono text-xs text-slate-400">
                                    <?= date('M d, Y', strtotime($log['created_at'])) ?>
                                </td>
                                
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-200">
                                        <?= !empty($log['player_name']) ? esc($log['player_name']) : esc($log['username']) ?>
                                    </div>
                                    <div class="text-[10px] text-amber-500 font-bold uppercase">
                                        <?= esc($log['alliance_level']) ?>
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4 text-center font-bold <?= $log['days_since_last'] == 0 ? 'text-slate-500' : 'text-slate-300' ?>">
                                    <?= $log['days_since_last'] ?>
                                </td>

                                <td class="px-6 py-4 text-right font-mono font-bold text-slate-300">
                                    <?= number_format($newTotal, 2) ?>
                                </td>

                                <td class="px-6 py-4 text-right font-mono font-bold text-sm <?= $totalDiff > 0 ? 'text-emerald-400' : ($totalDiff < 0 ? 'text-rose-400' : 'text-slate-500') ?>">
                                    <?= $totalDiff > 0 ? '+' : '' ?><?= number_format($totalDiff, 2) ?>
                                </td>
                                
                                <td class="px-6 py-4 text-right font-mono text-xs <?= $log['tank_diff'] > 0 ? 'text-emerald-400' : ($log['tank_diff'] < 0 ? 'text-rose-400' : 'text-slate-500') ?>">
                                    <?= $log['tank_diff'] > 0 ? '+' : '' ?><?= number_format($log['tank_diff'], 2) ?>
                                </td>
                                
                                <td class="px-6 py-4 text-right font-mono text-xs <?= $log['air_diff'] > 0 ? 'text-emerald-400' : ($log['air_diff'] < 0 ? 'text-rose-400' : 'text-slate-500') ?>">
                                    <?= $log['air_diff'] > 0 ? '+' : '' ?><?= number_format($log['air_diff'], 2) ?>
                                </td>
                                
                                <td class="px-6 py-4 text-right font-mono text-xs <?= $log['missile_diff'] > 0 ? 'text-emerald-400' : ($log['missile_diff'] < 0 ? 'text-rose-400' : 'text-slate-500') ?>">
                                    <?= $log['missile_diff'] > 0 ? '+' : '' ?><?= number_format($log['missile_diff'], 2) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-8 text-center flex justify-center gap-4">
        <a href="/admin/users/squad_powers" class="inline-flex items-center text-sm text-slate-400 hover:text-amber-400 transition-colors">
            ← Back to Combat Roster
        </a>
        <a href="/admin/settings" class="inline-flex items-center text-sm text-slate-400 hover:text-amber-400 transition-colors">
            Go to Admin Settings
        </a>
    </div>
</div>

<?= $this->include('templates/footer') ?>