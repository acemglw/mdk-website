<?= $this->include('templates/header', ['title' => 'Polar Express']) ?>

<div class="glass-panel p-8 md:p-12 rounded-2xl shadow-2xl max-w-7xl w-full relative z-10 mx-4 my-8">
    <div class="flex flex-col md:flex-row items-center justify-between mb-8 gap-4 border-b border-slate-800 pb-6">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 relative shadow-[0_0_40px_rgba(245,158,11,0.2)] rounded-full flex items-center justify-center bg-slate-900/50">
                <div class="absolute inset-0 rounded-full border-2 border-yellow-500/50 animate-[pulse_3s_linear_infinite]"></div>
                <span class="text-2xl relative z-10 drop-shadow-lg">🚂</span>
            </div>
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-yellow-600 tracking-tight drop-shadow-sm">
                    Polar Express
                </h1>
                <p class="text-slate-400 mt-1 text-xs">Manage the Alliance Gold Train schedule and tracking.</p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="/gold-train/history" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg text-sm font-semibold transition-colors border border-slate-700 shadow-sm flex items-center gap-2">
                <span>🕰️</span> History
            </a>
            <?php if (in_array(session()->get('role'), ['admin', 'super_admin'])) : ?>
                <form action="/gold-train/shift-down" method="POST" onsubmit="return confirm('This will shift every scheduled player forward by 1 day. Do you want to continue?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="px-4 py-2 bg-emerald-600/20 hover:bg-emerald-600/40 text-emerald-500 rounded-lg text-sm font-semibold transition-colors border border-emerald-600/50 shadow-sm flex items-center gap-2">
                        <span>⏬</span> Shift +1 Day
                    </button>
                </form>
                <form action="/gold-train/reset" method="POST" onsubmit="return confirm('Are you sure you want to reset the entire roster schedule? This will wipe all current status, MVP assignments, and dates to pending.');">
                    <?= csrf_field() ?>
                    <button type="submit" class="px-4 py-2 bg-rose-600/20 hover:bg-rose-600/40 text-rose-500 rounded-lg text-sm font-semibold transition-colors border border-rose-600/50 shadow-sm flex items-center gap-2">
                        <span>⚠️</span> Reset Roster
                    </button>
                </form>
            <?php endif; ?>
            <a href="/dashboard" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-slate-400 rounded-lg text-sm font-semibold transition-colors border border-slate-800 shadow-sm">
                Dashboard
            </a>
        </div>
    </div>

    <?php if (session()->getFlashdata('message')) : ?>
        <div class="bg-emerald-500/10 border border-emerald-500/50 text-emerald-400 p-4 rounded-xl mb-6 text-sm text-center">
            <?= session()->getFlashdata('message') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')) : ?>
        <div class="bg-rose-500/10 border border-rose-500/50 text-rose-400 p-4 rounded-xl mb-6 text-sm text-center">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="bg-slate-900/60 rounded-xl border border-slate-700/60 p-6 shadow-lg mb-8">
        <h3 class="text-lg font-bold text-slate-200 mb-3 flex items-center gap-2">
            <span>📜</span> Rules & Guidelines
        </h3>
        <ul class="list-disc list-inside text-slate-400 space-y-1.5 text-xs leading-relaxed">
            <li>Lineup is automatically prioritized by alliance rank (R5 leads, followed by R4s, R3s, R2s, R1s).</li>
            <li>The assigned player must successfully <strong class="text-yellow-400">turn the train gold</strong>.</li>
            <li>The player must reach a <strong class="text-yellow-400">70k minimum rating status</strong>.</li>
            <li>Failure to meet these requirements or a "No Show" will terminate the player's turn for the next rotation, unless rescheduled by an Admin.</li>
        </ul>
    </div>

    <!-- Admin Tools -->
    <?php if (in_array(session()->get('role'), ['admin', 'super_admin'])) : ?>
    
    <!-- Auto-Generate Cycle (Full Width) -->
    <div class="bg-slate-800/40 rounded-xl border border-slate-700/50 p-6 shadow-lg mb-6">
        <h3 class="text-lg font-bold text-yellow-500 mb-4 flex items-center gap-2">
            <span>⏱️</span> Auto-Generate Cycle
        </h3>
        <p class="text-xs text-slate-400 mb-4">Evenly distribute players across a date range.</p>
        
        <form action="/gold-train/generate-cycle" method="POST" class="flex flex-col md:flex-row gap-4">
            <?= csrf_field() ?>
            
            <div class="flex-1 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-1 uppercase tracking-wider">Cycle Name</label>
                    <input type="text" name="cycle_name" required placeholder="e.g., Season 1 - March" class="w-full px-4 py-2 bg-slate-950/50 border border-slate-700 rounded-lg focus:outline-none focus:border-yellow-500 text-slate-200 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-1 uppercase tracking-wider">Starting Player</label>
                    <select name="start_user_id" required class="w-full px-4 py-2 bg-slate-950/50 border border-slate-700 rounded-lg focus:outline-none focus:border-yellow-500 text-slate-200 text-sm appearance-none">
                        <option value="" disabled selected>-- Select a player --</option>
                        <?php if (!empty($roster) && is_array($roster)): ?>
                            <?php foreach ($roster as $index => $row): ?>
                                <option value="<?= $row['user_id'] ?>">#<?= $index + 1 ?> - <?= esc($row['player_name']) ?> (<?= esc($row['alliance_level']) ?>)</option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <div class="flex-1 space-y-4 flex flex-col">
                <div class="flex gap-4">
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-slate-400 mb-1 uppercase tracking-wider">Start Date & Time</label>
                        <input type="datetime-local" name="start_date" required class="w-full px-4 py-2 bg-slate-950/50 border border-slate-700 rounded-lg focus:outline-none focus:border-yellow-500 text-slate-200 text-sm">
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-slate-400 mb-1 uppercase tracking-wider">End Date</label>
                        <input type="date" name="end_date" required class="w-full px-4 py-2 bg-slate-950/50 border border-slate-700 rounded-lg focus:outline-none focus:border-yellow-500 text-slate-200 text-sm">
                    </div>
                </div>
                
                <div class="mt-auto pt-2">
                    <button type="submit" class="w-full px-6 py-2 bg-gradient-to-r from-yellow-600 to-yellow-500 hover:from-yellow-500 hover:to-yellow-400 text-slate-900 rounded-lg text-sm font-bold transition-all shadow-lg shadow-yellow-900/20">
                        Generate Cycle
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Secondary Admin Tools (Three Columns) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8 items-stretch">
        
        <!-- Load Cycle -->
        <div class="bg-slate-800/40 rounded-xl border border-slate-700/50 p-6 shadow-lg flex flex-col h-full">
            <div>
                <h3 class="text-lg font-bold text-emerald-400 mb-4 flex items-center gap-2">
                    <span>📂</span> Load Past Cycle
                </h3>
                <p class="text-xs text-slate-400 mb-4">View a previously saved cycle schedule.</p>
            </div>
            
            <form action="/gold-train/load-cycle" method="POST" class="flex flex-col flex-1">
                <?= csrf_field() ?>
                
                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-400 mb-1 uppercase tracking-wider">Select Cycle</label>
                    <select name="load_cycle_id" required class="w-full px-4 py-2 bg-slate-950/50 border border-slate-700 rounded-lg focus:outline-none focus:border-emerald-500 text-slate-200 text-sm appearance-none">
                        <option value="" disabled selected>-- Select a cycle --</option>
                        <?php if (!empty($availableCycles)): ?>
                            <?php foreach ($availableCycles as $id => $name): ?>
                                <option value="<?= esc($id) ?>"><?= esc($name) ?></option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="" disabled>No historical cycles found.</option>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div class="mt-auto pt-2 space-y-2">
                    <button type="submit" <?= empty($availableCycles) ? 'disabled' : '' ?> class="w-full px-6 py-2 bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 disabled:from-slate-600 disabled:to-slate-700 disabled:text-slate-400 text-slate-900 rounded-lg text-sm font-bold transition-all shadow-lg shadow-emerald-900/20">
                        Load Data
                    </button>
                    
                    <?php if (isset($_GET['load_cycle_id'])): ?>
                        <a href="/gold-train" class="block w-full text-center px-6 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg text-sm font-bold transition-colors border border-slate-700">
                            Clear View (Load Active)
                        </a>
                    <?php else: ?>
                        <div class="h-9"></div> <!-- Placeholder to keep height consistent when clear button is absent -->
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Manual Cycle Saver -->
        <div class="bg-slate-800/40 rounded-xl border border-slate-700/50 p-6 shadow-lg flex flex-col h-full">
            <div>
                <h3 class="text-lg font-bold text-teal-400 mb-4 flex items-center gap-2">
                    <span>✍️</span> Save Manual Cycle
                </h3>
                <p class="text-xs text-slate-400 mb-4">Save the current active schedule as a new named cycle for historical tracking.</p>
            </div>
            
            <form action="/gold-train/save-cycle" method="POST" id="manualCycleForm" class="flex flex-col flex-1">
                <?= csrf_field() ?>
                
                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-400 mb-1 uppercase tracking-wider">Cycle Name</label>
                    <input type="text" name="cycle_name" required placeholder="e.g., Special Event Cycle" class="w-full px-4 py-2 bg-slate-950/50 border border-slate-700 rounded-lg focus:outline-none focus:border-teal-500 text-slate-200 text-sm">
                </div>
                
                <input type="hidden" name="scheduled_ids" id="scheduledIdsInput" value="">
                
                <p class="text-[10px] text-slate-500 leading-tight mb-4">This will take all players with dates in the table below and snapshot them into the new cycle.</p>
                
                <div class="mt-auto pt-2 space-y-2">
                    <button type="button" onclick="submitManualCycle()" class="w-full px-6 py-2 bg-gradient-to-r from-teal-600 to-teal-500 hover:from-teal-500 hover:to-teal-400 text-slate-900 rounded-lg text-sm font-bold transition-all shadow-lg shadow-teal-900/20">
                        Save Manual Assignments
                    </button>
                    <div class="h-9"></div> <!-- Placeholder for alignment -->
                </div>
            </form>
        </div>

        <!-- Schedule Swap -->
        <div class="bg-slate-800/40 rounded-xl border border-slate-700/50 p-6 shadow-lg flex flex-col h-full">
            <div>
                <h3 class="text-lg font-bold text-sky-400 mb-4 flex items-center gap-2">
                    <span>🔄</span> Swap Player Schedules
                </h3>
                <p class="text-xs text-slate-400 mb-4">Select two players currently scheduled to instantly swap their assigned dates.</p>
            </div>
            
            <form action="/gold-train/swap" method="POST" class="flex flex-col flex-1">
                <?= csrf_field() ?>
                <div class="mb-4 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1 uppercase tracking-wider">Player 1</label>
                        <select name="user_id_1" required class="w-full px-4 py-2 bg-slate-950/50 border border-slate-700 rounded-lg focus:outline-none focus:border-sky-500 text-slate-200 text-sm appearance-none">
                            <option value="" disabled selected>-- Select first player --</option>
                            <?php if (!empty($roster) && is_array($roster)): ?>
                                <?php foreach ($roster as $row): ?>
                                    <?php if ($row['schedule_date']): ?>
                                        <option value="<?= $row['user_id'] ?>"><?= esc($row['player_name']) ?> (<?= date('M d', strtotime($row['schedule_date'])) ?>)</option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1 uppercase tracking-wider">Player 2</label>
                        <select name="user_id_2" required class="w-full px-4 py-2 bg-slate-950/50 border border-slate-700 rounded-lg focus:outline-none focus:border-sky-500 text-slate-200 text-sm appearance-none">
                            <option value="" disabled selected>-- Select second player --</option>
                            <?php if (!empty($roster) && is_array($roster)): ?>
                                <?php foreach ($roster as $row): ?>
                                    <?php if ($row['schedule_date']): ?>
                                        <option value="<?= $row['user_id'] ?>"><?= esc($row['player_name']) ?> (<?= date('M d', strtotime($row['schedule_date'])) ?>)</option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                
                <div class="mt-auto pt-2 space-y-2">
                    <button type="submit" class="w-full px-6 py-2 bg-gradient-to-r from-sky-600 to-sky-500 hover:from-sky-500 hover:to-sky-400 text-slate-900 rounded-lg text-sm font-bold transition-all shadow-lg shadow-sky-900/20">
                        Swap Dates
                    </button>
                    <div class="h-9"></div> <!-- Placeholder for alignment -->
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Roster Data Table -->
    <div class="overflow-hidden bg-slate-900/60 rounded-xl border border-slate-700/60 shadow-lg">
        <div class="p-4 border-b border-slate-700/50 flex justify-between items-center bg-slate-800/80">
            <h4 class="font-bold text-slate-200 text-sm flex items-center gap-2">
                Alliance Schedule Order
                <?php if (isset($activeCycleName) && $activeCycleName): ?>
                    <span class="bg-yellow-500/20 text-yellow-400 border border-yellow-500/50 px-2 py-0.5 rounded text-xs font-mono ml-2">Current Cycle: <?= esc($activeCycleName) ?></span>
                <?php endif; ?>
            </h4>
            <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Search players..." class="px-3 py-1.5 bg-slate-950/50 border border-slate-700 rounded-lg focus:outline-none focus:border-yellow-500 text-slate-200 text-xs w-64 transition-colors">
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left text-sm text-slate-300" id="rosterTable">
                <thead class="text-[10px] text-slate-400 uppercase bg-slate-800/80 border-b border-slate-700">
                    <tr>
                        <th scope="col" class="px-6 py-4 cursor-pointer hover:text-yellow-400 transition-colors" onclick="sortTable(0, 'numeric')">Order ↕</th>
                        <th scope="col" class="px-6 py-4 cursor-pointer hover:text-yellow-400 transition-colors" onclick="sortTable(1, 'string')">Conductor ↕</th>
                        <th scope="col" class="px-6 py-4 cursor-pointer hover:text-yellow-400 transition-colors" onclick="sortTable(2, 'string')">Rank ↕</th>
                        <th scope="col" class="px-6 py-4 cursor-pointer hover:text-yellow-400 transition-colors" onclick="sortTable(3, 'string')">Scheduled Date ↕</th>
                        <th scope="col" class="px-6 py-4 cursor-pointer hover:text-yellow-400 transition-colors" onclick="sortTable(4, 'string')">MVP ↕</th>
                        <th scope="col" class="px-6 py-4 cursor-pointer hover:text-yellow-400 transition-colors" onclick="sortTable(5, 'string')">Guardian ↕</th>
                        <th scope="col" class="px-6 py-4 text-center cursor-pointer hover:text-yellow-400 transition-colors" onclick="sortTable(6, 'string')">Status ↕</th>
                        <th scope="col" class="px-6 py-4 text-center">Turned Gold?</th>
                        <th scope="col" class="px-6 py-4 text-center">70k Rating?</th>
                        <?php if (in_array(session()->get('role'), ['admin', 'super_admin'])) : ?>
                            <th scope="col" class="px-6 py-4 rounded-tr-lg text-right">Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    <?php if (!empty($roster) && is_array($roster)): ?>
                        <?php $counter = 1; ?>
                        <?php foreach ($roster as $row): ?>
                            <tr class="hover:bg-slate-800/30 transition-colors roster-row" data-user-id="<?= $row['user_id'] ?>">
                                <td class="px-6 py-4 font-black text-yellow-500" data-value="<?= $counter ?>">#<?= $counter++ ?></td>
                                <td class="px-6 py-4 font-bold text-slate-200 player-name" data-value="<?= esc(strtolower($row['player_name'] ?: 'unknown')) ?>">
                                    <?= esc($row['player_name'] ?: 'Unknown') ?>
                                </td>
                                <td class="px-6 py-4" data-value="<?= esc($row['alliance_level']) ?>">
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-slate-800 text-slate-300 border border-slate-600">
                                        <?= esc($row['alliance_level']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs font-mono text-slate-400 schedule-cell" data-value="<?= esc($row['schedule_date'] ?? 'z') ?>">
                                    <?= $row['schedule_date'] ? date('M d, Y H:i', strtotime($row['schedule_date'])) : 'Not Scheduled' ?>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-300" data-value="<?= esc(strtolower($row['mvp_name'] ?? 'none')) ?>">
                                    <?= esc($row['mvp_name'] ?? '-') ?>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-300" data-value="<?= esc(strtolower($row['guardian_name'] ?? 'none')) ?>">
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
                                <td class="px-6 py-4 text-center text-lg">
                                    <?= (!empty($row['turned_gold'])) ? '✅' : '<span class="opacity-20">❌</span>' ?>
                                </td>
                                <td class="px-6 py-4 text-center text-lg">
                                    <?= (!empty($row['reached_minimum'])) ? '✅' : '<span class="opacity-20">❌</span>' ?>
                                </td>
                                
                                <?php if (in_array(session()->get('role'), ['admin', 'super_admin'])) : ?>
                                    <td class="px-6 py-4 text-right">
                                        <button type="button" onclick="openModal('editModal<?= $row['user_id'] ?>')" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-amber-500 border border-slate-600 hover:border-amber-500/50 rounded transition-colors text-[10px] font-bold uppercase tracking-wider">
                                            Edit
                                        </button>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?= in_array(session()->get('role'), ['admin', 'super_admin']) ? '10' : '9' ?>" class="px-6 py-12 text-center text-slate-500 italic">
                                No roster found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (in_array(session()->get('role'), ['admin', 'super_admin'])) : ?>
    <?php if (!empty($roster) && is_array($roster)): ?>
        <?php foreach ($roster as $row): ?>
            <!-- Edit Modal for <?= esc($row['player_name']) ?> -->
            <div id="editModal<?= $row['user_id'] ?>" class="fixed inset-0 z-[100] hidden items-center justify-center overflow-auto bg-slate-950/80 backdrop-blur-sm p-4">
                <div class="glass-panel p-8 rounded-2xl w-full max-w-md relative mx-auto my-auto shadow-2xl border border-slate-700">
                    
                    <button type="button" onclick="closeModal('editModal<?= $row['user_id'] ?>')" class="absolute top-4 right-4 text-slate-500 hover:text-slate-300 transition-colors">
                        ✕
                    </button>

                    <h3 class="text-xl font-black text-yellow-500 mb-6 flex flex-col">
                        <span>Edit Status</span>
                        <span class="text-slate-200 text-sm font-bold mt-1"><?= esc($row['player_name']) ?></span>
                    </h3>

                    <form action="/gold-train/update-status" method="POST" class="space-y-4">
                        <?= csrf_field() ?>
                        <input type="hidden" name="user_id" value="<?= $row['user_id'] ?>">
                        <?php if (isset($_GET['load_cycle_id'])): ?>
                            <input type="hidden" name="active_cycle_id" value="<?= esc($_GET['load_cycle_id']) ?>">
                        <?php endif; ?>
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1 uppercase tracking-wider">Schedule Date & Time</label>
                            <!-- Changed from date to datetime-local to allow time specification -->
                            <input type="datetime-local" name="schedule_date" value="<?= esc($row['schedule_date'] ?? '') ?>"
                                class="w-full px-3 py-2 bg-slate-950/50 border border-slate-700 rounded-lg focus:outline-none focus:border-yellow-500 text-sm text-slate-200">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1 uppercase tracking-wider">MVP (Rider)</label>
                            <select name="mvp_user_id" id="mvpSelect<?= $row['user_id'] ?>" onchange="handleRiderSelection('<?= $row['user_id'] ?>', 'mvp')" class="w-full px-3 py-2 bg-slate-950/50 border border-slate-700 rounded-lg focus:outline-none focus:border-yellow-500 text-sm text-slate-200 appearance-none">
                                <option value="">-- Select MVP --</option>
                                <?php foreach ($roster as $p): ?>
                                    <?php if ($p['user_id'] != $row['user_id']): ?>
                                        <option value="<?= $p['user_id'] ?>" <?= (isset($row['mvp_user_id']) && $row['mvp_user_id'] == $p['user_id']) ? 'selected' : '' ?>>
                                            <?= esc($p['player_name']) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1 uppercase tracking-wider">Guardian Defender (VIP)</label>
                            <select name="guardian_user_id" id="guardianSelect<?= $row['user_id'] ?>" onchange="handleRiderSelection('<?= $row['user_id'] ?>', 'guardian')" class="w-full px-3 py-2 bg-slate-950/50 border border-slate-700 rounded-lg focus:outline-none focus:border-yellow-500 text-sm text-slate-200 appearance-none">
                                <option value="">-- Select Guardian --</option>
                                <?php foreach ($roster as $p): ?>
                                    <?php if ($p['user_id'] != $row['user_id']): ?>
                                        <option value="<?= $p['user_id'] ?>" <?= (isset($row['guardian_user_id']) && $row['guardian_user_id'] == $p['user_id']) ? 'selected' : '' ?>>
                                            <?= esc($p['player_name']) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                            <p class="text-[10px] text-slate-500 mt-1 italic" id="riderMsg<?= $row['user_id'] ?>">Note: The conductor can only carry one rider (MVP or Guardian).</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1 uppercase tracking-wider">Status</label>
                            <select name="status" class="w-full px-3 py-2 bg-slate-950/50 border border-slate-700 rounded-lg focus:outline-none focus:border-yellow-500 text-sm text-slate-200 appearance-none">
                                <option value="pending" <?= (isset($row['status']) && $row['status'] == 'pending') ? 'selected' : '' ?>>Pending</option>
                                <option value="scheduled" <?= (isset($row['status']) && $row['status'] == 'scheduled') ? 'selected' : '' ?>>Scheduled</option>
                                <option value="completed" <?= (isset($row['status']) && $row['status'] == 'completed') ? 'selected' : '' ?>>Completed</option>
                                <option value="failed" <?= (isset($row['status']) && $row['status'] == 'failed') ? 'selected' : '' ?>>Failed / No Show</option>
                                <option value="skipped" <?= (isset($row['status']) && $row['status'] == 'skipped') ? 'selected' : '' ?>>Skipped (Penalty)</option>
                                <option value="rescheduled" <?= (isset($row['status']) && $row['status'] == 'rescheduled') ? 'selected' : '' ?>>Rescheduled</option>
                            </select>
                        </div>

                        <div class="space-y-3 pt-4 border-t border-slate-800">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <div class="relative flex items-center">
                                    <input type="checkbox" name="turned_gold" value="1" <?= (!empty($row['turned_gold'])) ? 'checked' : '' ?> class="peer w-5 h-5 rounded border-slate-600 text-yellow-500 focus:ring-yellow-500/50 bg-slate-950">
                                </div>
                                <span class="text-sm font-bold text-slate-300 group-hover:text-yellow-400 transition-colors">Successfully Turned Gold</span>
                            </label>

                            <label class="flex items-center gap-3 cursor-pointer group">
                                <div class="relative flex items-center">
                                    <input type="checkbox" name="reached_minimum" value="1" <?= (!empty($row['reached_minimum'])) ? 'checked' : '' ?> class="peer w-5 h-5 rounded border-slate-600 text-yellow-500 focus:ring-yellow-500/50 bg-slate-950">
                                </div>
                                <span class="text-sm font-bold text-slate-300 group-hover:text-yellow-400 transition-colors">Reached 70k Minimum Rating</span>
                            </label>
                        </div>

                        <div class="pt-6 flex justify-end gap-3">
                            <button type="button" onclick="closeModal('editModal<?= $row['user_id'] ?>')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg text-sm font-bold transition-colors border border-slate-700">
                                Cancel
                            </button>
                            <button type="submit" class="px-6 py-2 bg-gradient-to-r from-yellow-600 to-yellow-500 hover:from-yellow-500 hover:to-yellow-400 text-slate-900 rounded-lg text-sm font-bold transition-all shadow-lg">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
<?php endif; ?>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #475569; }
</style>

<script>
    // --- Manual Cycle Save Logic ---
    function submitManualCycle() {
        const rows = document.querySelectorAll('#rosterTable tbody tr.roster-row');
        const scheduledUserIds = [];

        rows.forEach(row => {
            const userId = row.getAttribute('data-user-id');
            const dateCell = row.querySelector('.schedule-cell');
            
            if (userId && dateCell && dateCell.innerText.trim() !== 'Not Scheduled') {
                scheduledUserIds.push(userId);
            }
        });

        if (scheduledUserIds.length === 0) {
            alert('Cannot save. No players are currently scheduled on the board.');
            return;
        }

        document.getElementById('scheduledIdsInput').value = scheduledUserIds.join(',');
        document.getElementById('manualCycleForm').submit();
    }

    // --- Logic for Rider Exclusivity ---
    function handleRiderSelection(userId, changedSelect) {
        const mvpSelect = document.getElementById('mvpSelect' + userId);
        const guardianSelect = document.getElementById('guardianSelect' + userId);
        
        if (changedSelect === 'mvp' && mvpSelect.value !== "") {
            guardianSelect.value = ""; // Reset guardian if MVP is picked
        } else if (changedSelect === 'guardian' && guardianSelect.value !== "") {
            mvpSelect.value = ""; // Reset MVP if guardian is picked
        }
    }

    // Initialize all modals on load to enforce the exclusivity if data exists
    document.addEventListener("DOMContentLoaded", () => {
        const modals = document.querySelectorAll('div[id^="editModal"]');
        modals.forEach(modal => {
            const userId = modal.id.replace('editModal', '');
            const mvpSelect = document.getElementById('mvpSelect' + userId);
            const guardianSelect = document.getElementById('guardianSelect' + userId);
            
            if (mvpSelect && mvpSelect.value !== "") {
                guardianSelect.value = "";
            } else if (guardianSelect && guardianSelect.value !== "") {
                mvpSelect.value = "";
            }
        });
    });

    // --- Modal Logic ---
    function openModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden'; 
        }
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
        }
    }

    window.addEventListener('click', function(event) {
        const modals = document.querySelectorAll('.fixed.inset-0');
        modals.forEach(modal => {
            if (event.target === modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = 'auto';
            }
        });
    });

    // --- Table Filtering Logic ---
    function filterTable() {
        const input = document.getElementById("searchInput");
        const filter = input.value.toLowerCase();
        const table = document.getElementById("rosterTable");
        const trs = table.getElementsByClassName("roster-row");

        for (let i = 0; i < trs.length; i++) {
            const td = trs[i].getElementsByClassName("player-name")[0];
            if (td) {
                const txtValue = td.textContent || td.innerText;
                if (txtValue.toLowerCase().indexOf(filter) > -1) {
                    trs[i].style.display = "";
                } else {
                    trs[i].style.display = "none";
                }
            }       
        }
    }

    // --- Table Sorting Logic ---
    let currentSortCol = -1;
    let currentSortAsc = true;

    function sortTable(columnIndex, type) {
        const table = document.getElementById("rosterTable");
        const tbody = table.querySelector("tbody");
        const rows = Array.from(tbody.querySelectorAll("tr.roster-row"));
        
        // Toggle direction if clicking same column
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
        
        // Append rows back to tbody in sorted order
        rows.forEach(row => tbody.appendChild(row));
    }
</script>

<?= $this->include('templates/footer') ?>