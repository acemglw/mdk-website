<?= $this->include('templates/header', ['title' => 'My Squad Stats']) ?>

<div class="glass-panel p-8 md:p-12 rounded-2xl shadow-2xl max-w-2xl w-full relative z-10 mx-4 my-8">
    <!-- Header Section -->
    <div class="mb-8 flex flex-col items-center">
        <div class="w-20 h-20 mb-4 relative shadow-[0_0_40px_rgba(56,189,248,0.2)] rounded-full flex items-center justify-center bg-slate-900/50">
            <div class="absolute inset-0 rounded-full border-2 border-sky-500/50 animate-[pulse_3s_linear_infinite]"></div>
            <span class="text-3xl relative z-10 drop-shadow-lg">🛡️</span>
        </div>

        <h1 class="text-2xl md:text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-sky-400 to-indigo-500 mb-2 tracking-tight drop-shadow-sm text-center">
            My Squad Powers
        </h1>
        <p class="text-slate-400 text-sm text-center">Keep your active combat stats updated.</p>
        
        <?php $totalPower = (float)$stats['power_tank'] + (float)$stats['power_air'] + (float)$stats['power_missile']; ?>
        <div class="mt-4 bg-slate-900/80 border border-slate-700 px-6 py-2 rounded-xl shadow-inner">
            <span class="text-slate-400 text-xs font-bold uppercase tracking-wider">Total Combined Power:</span>
            <span class="ml-3 font-mono font-bold text-lg text-emerald-400"><?= number_format($totalPower, 2) ?></span>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-emerald-500/10 border border-emerald-500/50 text-emerald-400 p-4 rounded-xl mb-6 text-sm text-center">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-rose-500/10 border border-rose-500/50 text-rose-400 p-4 rounded-xl mb-6 text-sm text-center">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <!-- Update Form -->
    <form action="/profile/squad/update" method="POST" class="bg-slate-900/60 border border-slate-700/60 rounded-xl p-6 shadow-lg space-y-6">
        <?= csrf_field() ?>
        
        <!-- Tank Power -->
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-amber-500/20 border border-amber-500/50 rounded-lg flex items-center justify-center shrink-0">
                <span class="text-2xl">🚜</span>
            </div>
            <div class="flex-1">
                <label class="block text-amber-400 text-xs font-bold mb-1 uppercase tracking-wider">Tank Squad Power</label>
                <div class="relative">
                    <input type="number" step="0.01" name="power_tank" value="<?= esc($stats['power_tank']) ?>" required
                        class="w-full bg-slate-950/50 border border-slate-700 rounded-lg pl-3 pr-10 py-3 text-slate-200 focus:outline-none focus:border-amber-500 transition-colors font-mono text-lg tracking-wide">
                </div>
            </div>
        </div>

        <!-- Air Power -->
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-sky-500/20 border border-sky-500/50 rounded-lg flex items-center justify-center shrink-0">
                <span class="text-2xl">🚁</span>
            </div>
            <div class="flex-1">
                <label class="block text-sky-400 text-xs font-bold mb-1 uppercase tracking-wider">Air Squad Power</label>
                <div class="relative">
                    <input type="number" step="0.01" name="power_air" value="<?= esc($stats['power_air']) ?>" required
                        class="w-full bg-slate-950/50 border border-slate-700 rounded-lg pl-3 pr-10 py-3 text-slate-200 focus:outline-none focus:border-sky-500 transition-colors font-mono text-lg tracking-wide">
                </div>
            </div>
        </div>

        <!-- Missile Power -->
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-rose-500/20 border border-rose-500/50 rounded-lg flex items-center justify-center shrink-0">
                <span class="text-2xl">🚀</span>
            </div>
            <div class="flex-1">
                <label class="block text-rose-400 text-xs font-bold mb-1 uppercase tracking-wider">Missile Squad Power</label>
                <div class="relative">
                    <input type="number" step="0.01" name="power_missile" value="<?= esc($stats['power_missile']) ?>" required
                        class="w-full bg-slate-950/50 border border-slate-700 rounded-lg pl-3 pr-10 py-3 text-slate-200 focus:outline-none focus:border-rose-500 transition-colors font-mono text-lg tracking-wide">
                </div>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-700/50">
            <button type="submit" class="w-full bg-gradient-to-r from-sky-600 to-indigo-500 hover:from-sky-500 hover:to-indigo-400 text-slate-100 font-extrabold py-3 rounded-lg transition-all shadow-[0_0_20px_rgba(56,189,248,0.2)] hover:shadow-[0_0_25px_rgba(56,189,248,0.4)]">
                Save Combat Stats
            </button>
        </div>
    </form>

    <?php if (!empty($history)): ?>
        <div class="mt-8 bg-slate-900/60 border border-slate-700/60 rounded-xl p-6 shadow-lg">
            <h3 class="text-lg font-bold text-slate-200 mb-4 flex items-center gap-2">
                <span>📈</span> Growth History
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="text-[10px] uppercase bg-slate-800/80 text-slate-400 border-b border-slate-700">
                        <tr>
                            <th class="px-3 py-2">Date</th>
                            <th class="px-3 py-2 text-center">Days Since</th>
                            <th class="px-3 py-2 text-right">🚜 Tank Growth</th>
                            <th class="px-3 py-2 text-right">🚁 Air Growth</th>
                            <th class="px-3 py-2 text-right">🚀 Missile Growth</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $log): ?>
                            <tr class="border-b border-slate-700/50 hover:bg-slate-800/30 transition-colors">
                                <td class="px-3 py-3 font-mono text-xs text-slate-200">
                                    <?= date('M d, Y', strtotime($log['created_at'])) ?>
                                </td>
                                <td class="px-3 py-3 text-center text-xs font-bold text-slate-400">
                                    <?= $log['days_since_last'] ?> days
                                </td>
                                
                                <td class="px-3 py-3 text-right font-mono text-xs font-bold <?= $log['tank_diff'] > 0 ? 'text-emerald-400' : ($log['tank_diff'] < 0 ? 'text-rose-400' : 'text-slate-500') ?>">
                                    <?= $log['tank_diff'] > 0 ? '+' : '' ?><?= number_format($log['tank_diff'], 2) ?>
                                </td>
                                
                                <td class="px-3 py-3 text-right font-mono text-xs font-bold <?= $log['air_diff'] > 0 ? 'text-emerald-400' : ($log['air_diff'] < 0 ? 'text-rose-400' : 'text-slate-500') ?>">
                                    <?= $log['air_diff'] > 0 ? '+' : '' ?><?= number_format($log['air_diff'], 2) ?>
                                </td>
                                
                                <td class="px-3 py-3 text-right font-mono text-xs font-bold <?= $log['missile_diff'] > 0 ? 'text-emerald-400' : ($log['missile_diff'] < 0 ? 'text-rose-400' : 'text-slate-500') ?>">
                                    <?= $log['missile_diff'] > 0 ? '+' : '' ?><?= number_format($log['missile_diff'], 2) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <div class="mt-8 text-center">
        <a href="/dashboard" class="inline-flex items-center text-sm text-slate-400 hover:text-sky-400 transition-colors">
            ← Back to Dashboard
        </a>
    </div>
</div>

<?= $this->include('templates/footer') ?>