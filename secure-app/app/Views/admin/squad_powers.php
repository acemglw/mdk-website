<?= $this->include('templates/header', ['title' => 'Combat Roster - Admin']) ?>

<div class="glass-panel p-8 md:p-12 rounded-2xl shadow-2xl max-w-6xl w-full relative z-10 mx-4 my-8">
    
    <!-- Header Section -->
    <div class="mb-8 flex flex-col items-center border-b border-slate-800 pb-8">
        <div class="w-20 h-20 mb-4 relative shadow-[0_0_40px_rgba(245,158,11,0.2)] rounded-full flex items-center justify-center bg-slate-900/50">
            <div class="absolute inset-0 rounded-full border-2 border-amber-500/50 animate-[pulse_3s_linear_infinite]"></div>
            <span class="text-3xl relative z-10 drop-shadow-lg">🛡️</span>
        </div>

        <h1 class="text-2xl md:text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-500 mb-2 tracking-tight drop-shadow-sm text-center">
            Alliance Combat Roster
        </h1>
        <p class="text-slate-400 text-sm text-center">Monitor and adjust combat stats for all active members.</p>
        
        <div class="mt-4 flex gap-2">
            <!-- Export / Import Actions -->
            <a href="/admin/users/squad_powers/export" class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-600 px-4 py-2 rounded-lg text-sm font-bold transition-colors shadow-sm">
                <span>⬇️</span> Export CSV
            </a>
            <button onclick="document.getElementById('import_modal').classList.remove('hidden')" class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-600 px-4 py-2 rounded-lg text-sm font-bold transition-colors shadow-sm">
                <span>⬆️</span> Import CSV
            </button>
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

    <form action="/admin/users/squad_powers/bulk_update" method="POST">
        <?= csrf_field() ?>
        
        <div class="mb-4 flex justify-between items-center px-2">
            <div class="text-xs text-slate-500">
                Click column headers to sort. Remember to save your bulk changes.
            </div>
            <button type="submit" class="bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 text-white font-bold py-2 px-6 rounded-lg transition-all shadow-lg text-sm">
                💾 Save All Changes
            </button>
        </div>

        <!-- Roster Powers Grid -->
        <div class="bg-slate-900/60 border border-slate-700/60 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="text-xs uppercase bg-slate-800/80 text-slate-400 border-b border-slate-700">
                        <tr>
                            <th class="px-6 py-4">
                                <a href="?sort=username&order=<?= ($current_sort === 'username' && $current_order === 'ASC') ? 'DESC' : 'ASC' ?>" class="hover:text-amber-400 transition-colors">
                                    Member <?= $current_sort === 'username' ? ($current_order === 'ASC' ? '↑' : '↓') : '' ?>
                                </a>
                            </th>
                            <th class="px-6 py-4 text-center">
                                <a href="?sort=total_power&order=<?= ($current_sort === 'total_power' && $current_order === 'DESC') ? 'ASC' : 'DESC' ?>" class="hover:text-amber-400 transition-colors">
                                    Total Power <?= $current_sort === 'total_power' ? ($current_order === 'ASC' ? '↑' : '↓') : '' ?>
                                </a>
                            </th>
                            <th class="px-6 py-4">
                                <a href="?sort=power_tank&order=<?= ($current_sort === 'power_tank' && $current_order === 'DESC') ? 'ASC' : 'DESC' ?>" class="hover:text-amber-400 transition-colors">
                                    🚜 Tank <?= $current_sort === 'power_tank' ? ($current_order === 'ASC' ? '↑' : '↓') : '' ?>
                                </a>
                            </th>
                            <th class="px-6 py-4">
                                <a href="?sort=power_air&order=<?= ($current_sort === 'power_air' && $current_order === 'DESC') ? 'ASC' : 'DESC' ?>" class="hover:text-amber-400 transition-colors">
                                    🚁 Air <?= $current_sort === 'power_air' ? ($current_order === 'ASC' ? '↑' : '↓') : '' ?>
                                </a>
                            </th>
                            <th class="px-6 py-4">
                                <a href="?sort=power_missile&order=<?= ($current_sort === 'power_missile' && $current_order === 'DESC') ? 'ASC' : 'DESC' ?>" class="hover:text-amber-400 transition-colors">
                                    🚀 Missile <?= $current_sort === 'power_missile' ? ($current_order === 'ASC' ? '↑' : '↓') : '' ?>
                                </a>
                            </th>
                            <th class="px-6 py-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users_stats as $stat): ?>
                            <tr class="border-b border-slate-700/50 hover:bg-slate-800/30 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-200">
                                        <?= !empty($stat['player_name']) ? esc($stat['player_name']) : esc($stat['username']) ?>
                                    </div>
                                    <div class="text-[10px] text-amber-500 font-bold uppercase">
                                        <?= esc($stat['alliance_level']) ?>
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4 text-center font-mono font-bold text-amber-400/90 text-sm">
                                    <?= number_format($stat['total_power'], 2) ?>
                                </td>
                                
                                <td class="px-6 py-4">
                                    <input type="number" step="0.01" name="stats[<?= $stat['id'] ?>][power_tank]" value="<?= esc($stat['power_tank'] ?? 0) ?>" required
                                        class="w-28 bg-slate-950/50 border border-slate-700 rounded px-2 py-1.5 text-slate-200 focus:outline-none focus:border-amber-500 font-mono text-sm">
                                </td>
                                
                                <td class="px-6 py-4">
                                    <input type="number" step="0.01" name="stats[<?= $stat['id'] ?>][power_air]" value="<?= esc($stat['power_air'] ?? 0) ?>" required
                                        class="w-28 bg-slate-950/50 border border-slate-700 rounded px-2 py-1.5 text-slate-200 focus:outline-none focus:border-sky-500 font-mono text-sm">
                                </td>
                                
                                <td class="px-6 py-4">
                                    <input type="number" step="0.01" name="stats[<?= $stat['id'] ?>][power_missile]" value="<?= esc($stat['power_missile'] ?? 0) ?>" required
                                        class="w-28 bg-slate-950/50 border border-slate-700 rounded px-2 py-1.5 text-slate-200 focus:outline-none focus:border-rose-500 font-mono text-sm">
                                </td>
                                
                                <td class="px-6 py-4 text-right">
                                    <button type="button" onclick="saveSinglePlayer(<?= $stat['id'] ?>, this)" class="opacity-0 group-hover:opacity-100 p-2 bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/30 rounded transition-all text-[10px] font-bold uppercase tracking-wider">
                                        Save Row
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </form>

    <div class="mt-8 text-center">
        <a href="/admin/settings" class="inline-flex items-center text-sm text-slate-400 hover:text-amber-400 transition-colors">
            ← Back to Admin Settings
        </a>
    </div>
</div>

<!-- CSV Import Modal -->
<div id="import_modal" class="hidden fixed inset-0 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 z-50">
    <div class="bg-slate-900 border border-slate-800 rounded-xl max-w-md w-full p-6 shadow-2xl relative">
        <button onclick="document.getElementById('import_modal').classList.add('hidden')" class="absolute top-4 right-4 text-slate-500 hover:text-slate-300">✕</button>
        
        <h3 class="text-xl font-bold text-white mb-2 flex items-center gap-2"><span>⬆️</span> Import Squad Powers</h3>
        <p class="text-xs text-slate-400 mb-6 leading-relaxed">
            Upload a CSV to update stats. Matches by <strong>username</strong>.<br><br>
            <strong>Required columns:</strong><br>
            <code class="block mt-2 bg-slate-950 p-2 rounded border border-slate-800 text-amber-500/80">username, power_tank, power_air, power_missile</code>
        </p>

        <form action="/admin/users/squad_powers/import" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="border-2 border-dashed border-slate-700 rounded-xl p-6 text-center hover:border-amber-500/50 transition-colors bg-slate-950/30 mb-6">
                <input type="file" name="csv_file" accept=".csv" required class="block w-full text-sm text-slate-400
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-full file:border-0
                    file:text-sm file:font-semibold
                    file:bg-slate-800 file:text-amber-400
                    hover:file:bg-slate-700 cursor-pointer"/>
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-slate-900 font-extrabold py-3 rounded-lg transition-all shadow-lg">
                Process Import
            </button>
        </form>
    </div>
</div>

<script>
async function saveSinglePlayer(userId, btn) {
    const row = btn.closest('tr');
    const powerTank = row.querySelector(`input[name="stats[${userId}][power_tank]"]`).value;
    const powerAir = row.querySelector(`input[name="stats[${userId}][power_air]"]`).value;
    const powerMissile = row.querySelector(`input[name="stats[${userId}][power_missile]"]`).value;
    
    const originalText = btn.innerText;
    btn.innerText = 'Saving...';
    btn.disabled = true;

    try {
        const response = await fetch('/admin/users/squad_powers/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                'user_id': userId,
                'power_tank': powerTank,
                'power_air': powerAir,
                'power_missile': powerMissile,
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            })
        });

        if (response.ok) {
            btn.innerText = '✅ Saved';
            btn.classList.replace('text-amber-400', 'text-emerald-400');
            setTimeout(() => {
                btn.innerText = originalText;
                btn.classList.replace('text-emerald-400', 'text-amber-400');
                btn.disabled = false;
            }, 2000);
        } else {
            alert('Failed to save user stats.');
            btn.innerText = originalText;
            btn.disabled = false;
        }
    } catch (error) {
        alert('An error occurred.');
        btn.innerText = originalText;
        btn.disabled = false;
    }
}
</script>

<?= $this->include('templates/footer') ?>