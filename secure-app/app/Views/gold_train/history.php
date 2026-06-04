<?= $this->include('templates/header', ['title' => 'Polar Express History']) ?>

<div class="glass-panel p-8 md:p-12 rounded-2xl shadow-2xl max-w-7xl w-full relative z-10 mx-4 my-8">
    <div class="flex flex-col md:flex-row items-center justify-between mb-8 gap-4 border-b border-slate-800 pb-6">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 relative shadow-[0_0_40px_rgba(245,158,11,0.2)] rounded-full flex items-center justify-center bg-slate-900/50">
                <div class="absolute inset-0 rounded-full border-2 border-yellow-500/50"></div>
                <span class="text-2xl relative z-10 drop-shadow-lg">🕰️</span>
            </div>
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-yellow-600 tracking-tight drop-shadow-sm">
                    Train History & Stats
                </h1>
                <p class="text-slate-400 mt-1 text-xs">Review past cycle performance and historical player stats.</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="/gold-train" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg text-sm font-semibold transition-colors border border-slate-700 shadow-sm">
                ← Active Roster
            </a>
            <a href="/dashboard" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-slate-400 rounded-lg text-sm font-semibold transition-colors border border-slate-800 shadow-sm">
                Dashboard
            </a>
        </div>
    </div>

    <!-- Cycle Filter -->
    <div class="bg-slate-900/40 rounded-xl border border-slate-700/50 p-6 shadow-lg mb-8 flex flex-col md:flex-row items-center justify-between gap-4">
        <form action="/gold-train/history" method="GET" class="flex items-center gap-4 w-full md:w-auto">
            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Select Cycle:</label>
            <select name="cycle_id" onchange="this.form.submit()" class="bg-slate-950/80 border border-slate-700 text-slate-200 text-sm rounded-lg px-4 py-2 focus:outline-none focus:border-yellow-500 appearance-none min-w-[250px]">
                <option value="all" <?= ($selectedCycle === 'all') ? 'selected' : '' ?>>All Historical Records</option>
                <?php foreach ($availableCycles as $cycle): ?>
                    <option value="<?= esc($cycle) ?>" <?= ($selectedCycle === $cycle) ? 'selected' : '' ?>>
                        <?= esc($cycle) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <!-- Summary Stats for selected view -->
        <div class="flex gap-6 items-center">
            <div class="text-center">
                <span class="block text-2xl font-black text-emerald-400"><?= $stats['completed'] ?></span>
                <span class="text-[10px] text-slate-500 uppercase tracking-widest font-bold">Successful</span>
            </div>
            <div class="w-px h-8 bg-slate-800"></div>
            <div class="text-center">
                <span class="block text-2xl font-black text-rose-500"><?= $stats['failed'] ?></span>
                <span class="text-[10px] text-slate-500 uppercase tracking-widest font-bold">Failed/No-Show</span>
            </div>
            <div class="w-px h-8 bg-slate-800"></div>
            <div class="text-center">
                <span class="block text-2xl font-black text-yellow-500"><?= $stats['total'] ?></span>
                <span class="text-[10px] text-slate-500 uppercase tracking-widest font-bold">Total Logs</span>
            </div>
        </div>
    </div>

    <!-- Historical Data Table -->
    <div class="overflow-hidden bg-slate-900/60 rounded-xl border border-slate-700/60 shadow-lg">
        <div class="p-4 border-b border-slate-700/50 flex justify-between items-center bg-slate-800/80">
            <h4 class="font-bold text-slate-200 text-sm">Historical Logs</h4>
            <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Search player or cycle..." class="px-3 py-1.5 bg-slate-950/50 border border-slate-700 rounded-lg focus:outline-none focus:border-yellow-500 text-slate-200 text-xs w-64 transition-colors">
        </div>

        <div class="overflow-x-auto custom-scrollbar max-h-[60vh] overflow-y-auto">
            <table class="w-full text-left text-sm text-slate-300" id="historyTable">
                <thead class="text-[10px] text-slate-400 uppercase bg-slate-800/80 border-b border-slate-700 sticky top-0 z-10 backdrop-blur-md">
                    <tr>
                        <th scope="col" class="px-6 py-4 cursor-pointer hover:text-yellow-400 transition-colors" onclick="sortTable(0, 'string')">Cycle ID ↕</th>
                        <th scope="col" class="px-6 py-4 cursor-pointer hover:text-yellow-400 transition-colors" onclick="sortTable(1, 'string')">Conductor ↕</th>
                        <th scope="col" class="px-6 py-4 cursor-pointer hover:text-yellow-400 transition-colors" onclick="sortTable(2, 'string')">Assigned Date ↕</th>
                        <th scope="col" class="px-6 py-4 cursor-pointer hover:text-yellow-400 transition-colors" onclick="sortTable(3, 'string')">MVP ↕</th>
                        <th scope="col" class="px-6 py-4 cursor-pointer hover:text-yellow-400 transition-colors" onclick="sortTable(4, 'string')">Guardian ↕</th>
                        <th scope="col" class="px-6 py-4 text-center cursor-pointer hover:text-yellow-400 transition-colors" onclick="sortTable(5, 'string')">Final Status ↕</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    <?php if (!empty($historyLogs)): ?>
                        <?php foreach ($historyLogs as $row): ?>
                            <tr class="hover:bg-slate-800/30 transition-colors history-row">
                                <td class="px-6 py-4 text-xs font-mono text-slate-500 search-target" data-value="<?= esc($row['cycle_id']) ?>">
                                    <?= esc($row['cycle_id']) ?>
                                </td>
                                <td class="px-6 py-4 font-bold text-slate-200 search-target" data-value="<?= esc(strtolower($row['player_name'])) ?>">
                                    <?= esc($row['player_name']) ?>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-400" data-value="<?= esc($row['assigned_time'] ?? 'z') ?>">
                                    <?= $row['assigned_time'] ? date('M d, Y', strtotime($row['assigned_time'])) : '-' ?>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-300 search-target" data-value="<?= esc(strtolower($row['mvp_name'] ?? 'none')) ?>">
                                    <?= esc($row['mvp_name'] ?? '-') ?>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-300 search-target" data-value="<?= esc(strtolower($row['guardian_name'] ?? 'none')) ?>">
                                    <?= esc($row['guardian_name'] ?? '-') ?>
                                </td>
                                <td class="px-6 py-4 text-center" data-value="<?= esc($row['status'] ?? 'pending') ?>">
                                    <?php 
                                        $status = $row['status'] ?? 'pending';
                                        $statusClasses = [
                                            'pending'     => 'bg-slate-800 text-slate-400 border-slate-600',
                                            'scheduled'   => 'bg-sky-500/10 text-sky-400 border-sky-500/30',
                                            'completed'   => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                                            'failed'      => 'bg-rose-500/10 text-rose-400 border-rose-500/30',
                                            'skipped'     => 'bg-rose-900/30 text-rose-500 border-rose-800/50',
                                            'rescheduled' => 'bg-amber-500/10 text-amber-400 border-amber-500/30'
                                        ];
                                        $sClass = $statusClasses[strtolower($status)] ?? $statusClasses['pending'];
                                    ?>
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded border <?= $sClass ?>">
                                        <?= ucfirst(esc($status)) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500 italic">
                                No historical records found for this selection.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #475569; }
</style>

<script>
    function filterTable() {
        const input = document.getElementById("searchInput");
        const filter = input.value.toLowerCase();
        const table = document.getElementById("historyTable");
        const trs = table.getElementsByClassName("history-row");

        for (let i = 0; i < trs.length; i++) {
            let rowText = "";
            const targets = trs[i].getElementsByClassName("search-target");
            for (let j = 0; j < targets.length; j++) {
                rowText += (targets[j].textContent || targets[j].innerText).toLowerCase() + " ";
            }

            if (rowText.indexOf(filter) > -1) {
                trs[i].style.display = "";
            } else {
                trs[i].style.display = "none";
            }
        }
    }

    let currentSortCol = -1;
    let currentSortAsc = true;

    function sortTable(columnIndex, type) {
        const table = document.getElementById("historyTable");
        const tbody = table.querySelector("tbody");
        const rows = Array.from(tbody.querySelectorAll("tr.history-row"));

        if (currentSortCol === columnIndex) {
            currentSortAsc = !currentSortAsc;
        } else {
            currentSortAsc = true;
            currentSortCol = columnIndex;
        }

        rows.sort((a, b) => {
            let cellA = a.cells[columnIndex].getAttribute('data-value') || a.cells[columnIndex].innerText.trim();
            let cellB = b.cells[columnIndex].getAttribute('data-value') || b.cells[columnIndex].innerText.trim();

            if (type === 'numeric') {
                cellA = parseFloat(cellA.replace(/[^0-9.-]+/g,"")) || 0;
                cellB = parseFloat(cellB.replace(/[^0-9.-]+/g,"")) || 0;
                return currentSortAsc ? cellA - cellB : cellB - cellA;
            } else {
                return currentSortAsc ? cellA.localeCompare(cellB) : cellB.localeCompare(cellA);
            }
        });

        rows.forEach(row => tbody.appendChild(row));
    }
</script>

<?= $this->include('templates/footer') ?>