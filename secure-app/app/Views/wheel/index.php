<?= $this->include('templates/header', ['title' => 'Wheel of Fortune']) ?>

<div class="w-full max-w-7xl mx-auto flex flex-col gap-6 relative z-10 px-4">
    
    <!-- Top Header Banner -->
    <div class="glass-panel p-6 sm:p-8 rounded-2xl shadow-xl flex flex-col md:flex-row items-center justify-between gap-6 border-l-4 border-l-amber-500">
        <div class="flex items-center gap-5">
            <div class="w-16 h-16 sm:w-20 sm:h-20 relative shadow-[0_0_30px_rgba(245,158,11,0.3)] rounded-full flex items-center justify-center bg-slate-900/70 border border-amber-500/40 shrink-0">
                <span class="text-3xl sm:text-4xl relative z-10 animate-bounce">🎡</span>
            </div>
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-rose-400 to-indigo-400 tracking-tight">
                        Alliance Wheel of Fortune
                    </h1>
                </div>
                <p class="text-slate-400 text-xs sm:text-sm mt-1">
                    Random raffle picker for alliance rewards, weekly gold trains, and event prizes.
                </p>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <?php if ($canSpin): ?>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                            🟢 Authorized Spinner
                            <?php if ($canManage): ?>
                                (<?= esc($currentUserRole === 'super_admin' ? 'Super Admin' : $currentUserLevel) ?>)
                            <?php else: ?>
                                (Delegated Member)
                            <?php endif; ?>
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-800 text-slate-400 border border-slate-700">
                            👁️ Spectator Mode (Spin restricted to R4/R5 & delegated members)
                        </span>
                    <?php endif; ?>

                    <span id="activeCountBadge" class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500/15 text-amber-300 border border-amber-500/30">
                        0 on wheel
                    </span>
                </div>
            </div>
        </div>

        <!-- Top Actions -->
        <div class="flex items-center gap-3 flex-wrap justify-end">
            <!-- Sound Toggle Button -->
            <button id="soundToggleBtn" onclick="toggleSound()" class="px-3.5 py-2 rounded-xl bg-slate-900/80 border border-slate-700/80 hover:bg-slate-800 text-slate-300 text-xs font-bold flex items-center gap-2 transition-all shadow-sm">
                <span id="soundIcon">🔊</span>
                <span id="soundLabel">Sound ON</span>
            </button>

            <!-- Delegated Spinners Manager Button (R4/R5/Super Admin only) -->
            <?php if ($canManage): ?>
                <button onclick="openSpinnerModal()" class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-600 to-amber-700 hover:from-amber-500 hover:to-amber-600 text-slate-950 font-black text-xs uppercase tracking-wider flex items-center gap-2 shadow-lg shadow-amber-600/20 transition-all">
                    <span>👥</span>
                    <span>Manage Spinners</span>
                    <?php if (!empty($allowedSpinners)): ?>
                        <span class="px-1.5 py-0.5 rounded-full bg-slate-950 text-amber-400 text-[10px] font-bold">
                            <?= count($allowedSpinners) ?>
                        </span>
                    <?php endif; ?>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Notification Alert (for redirects or JS feedback) -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm flex items-center justify-between shadow-lg">
            <div class="flex items-center gap-2">
                <span>✅</span>
                <span><?= esc(session()->getFlashdata('success')) ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white">&times;</button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm flex items-center justify-between shadow-lg">
            <div class="flex items-center gap-2">
                <span>⚠️</span>
                <span><?= esc(session()->getFlashdata('error')) ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-400 hover:text-white">&times;</button>
        </div>
    <?php endif; ?>

    <!-- Main Content Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left / Center: The Wheel Canvas and Spinner Controls (7 Cols on desktop) -->
        <div class="lg:col-span-7 flex flex-col gap-6">
            
            <div class="glass-panel p-6 rounded-2xl shadow-xl flex flex-col items-center relative overflow-hidden">
                <!-- Background subtle radial glow -->
                <div class="absolute inset-0 bg-radial from-amber-500/10 via-transparent to-transparent pointer-events-none"></div>

                <!-- Event Title Bar -->
                <div class="w-full max-w-md flex items-center gap-2 mb-4 relative z-10">
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">Raffle Title:</label>
                    <input type="text" id="raffleTitle" value="Alliance Weekly Raffle" maxlength="80"
                        class="w-full bg-slate-900/90 border border-slate-700 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-lg px-3 py-1.5 text-sm text-slate-100 font-semibold transition-all">
                </div>

                <!-- Wheel Canvas Container -->
                <div class="relative flex items-center justify-center my-2 p-2">
                    <!-- Pointer / Needle Indicator at Top -->
                    <div id="wheelPointer" class="absolute top-0 z-30 flex flex-col items-center pointer-events-none transition-transform duration-75" style="transform-origin: 50% 0%;">
                        <svg width="46" height="52" viewBox="0 0 46 52" fill="none" class="drop-shadow-[0_4px_10px_rgba(0,0,0,0.7)]">
                            <path d="M23 52 L6 6 C4 2 8 0 12 0 L34 0 C38 0 42 2 40 6 L23 52 Z" fill="#F59E0B" stroke="#FDE68A" stroke-width="2.5" />
                            <circle cx="23" cy="14" r="5" fill="#78350F" />
                        </svg>
                    </div>

                    <!-- Canvas -->
                    <canvas id="wheelCanvas" width="560" height="560" class="max-w-full w-[340px] sm:w-[460px] md:w-[520px] aspect-square rounded-full shadow-[0_0_50px_rgba(245,158,11,0.15)]"></canvas>

                    <!-- Center Hub Overlay -->
                    <div class="absolute w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-gradient-to-br from-amber-400 via-amber-600 to-amber-900 border-4 border-amber-200 shadow-[0_0_25px_rgba(245,158,11,0.6)] flex items-center justify-center z-20 pointer-events-none">
                        <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-slate-950 flex flex-col items-center justify-center text-center">
                            <span class="text-xs font-black tracking-widest text-amber-400 uppercase">TKU</span>
                            <span class="text-[9px] text-slate-400 font-bold uppercase">WIN</span>
                        </div>
                    </div>
                </div>

                <!-- Wheel Action Buttons -->
                <div class="w-full max-w-md flex flex-col items-center gap-3 mt-4 relative z-10">
                    <?php if ($canSpin): ?>
                        <button id="spinBtn" onclick="startSpin()" class="w-full py-4 px-8 rounded-2xl bg-gradient-to-r from-amber-500 via-yellow-400 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-lg sm:text-xl uppercase tracking-wider shadow-[0_0_25px_rgba(245,158,11,0.5)] hover:shadow-[0_0_35px_rgba(245,158,11,0.8)] active:scale-95 transition-all duration-200 flex items-center justify-center gap-3 cursor-pointer">
                            <span>⚡</span>
                            <span>SPIN THE WHEEL</span>
                            <span>⚡</span>
                        </button>
                    <?php else: ?>
                        <button disabled class="w-full py-4 px-6 rounded-2xl bg-slate-800/80 border border-slate-700 text-slate-500 font-bold text-sm uppercase tracking-wider cursor-not-allowed flex items-center justify-center gap-2">
                            <span>🔒</span>
                            <span>Spin Restricted to R4/R5 & Authorized Spinners</span>
                        </button>
                    <?php endif; ?>

                    <div class="flex items-center gap-2 w-full justify-between text-xs text-slate-400">
                        <button onclick="shuffleParticipants()" class="px-3 py-1.5 rounded-lg bg-slate-900/60 border border-slate-700/60 hover:bg-slate-800 hover:text-white transition-colors flex items-center gap-1.5">
                            <span>🔀</span>
                            <span>Shuffle Slices</span>
                        </button>
                        <span id="winnerAnnounceMini" class="font-bold text-amber-400 truncate max-w-[200px]"></span>
                        <button onclick="resetToAllMembers()" class="px-3 py-1.5 rounded-lg bg-slate-900/60 border border-slate-700/60 hover:bg-slate-800 hover:text-white transition-colors flex items-center gap-1.5">
                            <span>🔄</span>
                            <span>Reset All</span>
                        </button>
                    </div>
                </div>

            </div>

            <!-- Recent Spin History Panel -->
            <div class="glass-panel p-6 rounded-2xl shadow-xl flex flex-col gap-4">
                <div class="flex items-center justify-between border-b border-slate-700/60 pb-3">
                    <h3 class="text-base font-extrabold text-white flex items-center gap-2">
                        <span>📜</span>
                        <span>Recent Winners History</span>
                    </h3>
                    <?php if ($canManage): ?>
                        <button onclick="clearSpinHistory()" class="text-xs text-rose-400 hover:text-rose-300 hover:underline flex items-center gap-1 font-semibold">
                            <span>🗑️</span>
                            <span>Clear History</span>
                        </button>
                    <?php endif; ?>
                </div>

                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-slate-400 border-b border-slate-800 bg-slate-900/40">
                                <th class="py-2.5 px-3 font-semibold">Winner</th>
                                <th class="py-2.5 px-3 font-semibold">Event Title</th>
                                <th class="py-2.5 px-3 font-semibold">Spun By</th>
                                <th class="py-2.5 px-3 font-semibold">Pool Size</th>
                                <th class="py-2.5 px-3 font-semibold text-right">Date & Time</th>
                            </tr>
                        </thead>
                        <tbody id="historyTableBody" class="divide-y divide-slate-800/60">
                            <?php if (empty($recentSpins)): ?>
                                <tr id="emptyHistoryRow">
                                    <td colspan="5" class="py-6 text-center text-slate-500 italic">
                                        No spins recorded yet. Spin the wheel to crown the first winner!
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentSpins as $spin): ?>
                                    <tr class="hover:bg-slate-800/40 transition-colors">
                                        <td class="py-2.5 px-3 font-bold text-amber-400 flex items-center gap-1.5">
                                            <span>🏆</span>
                                            <span><?= esc($spin['winner_name']) ?></span>
                                        </td>
                                        <td class="py-2.5 px-3 text-slate-300"><?= esc($spin['title'] ?: 'Alliance Raffle') ?></td>
                                        <td class="py-2.5 px-3 text-slate-400"><?= esc($spin['spun_by_name']) ?></td>
                                        <td class="py-2.5 px-3 text-slate-400"><?= esc($spin['participants_count']) ?> players</td>
                                        <td class="py-2.5 px-3 text-slate-500 text-right font-mono"><?= esc(date('M j, Y H:i', strtotime($spin['created_at']))) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Right: Participants Selection & Pool Management (5 Cols on desktop) -->
        <div class="lg:col-span-5 flex flex-col gap-6">
            
            <div class="glass-panel p-6 rounded-2xl shadow-xl flex flex-col gap-4">
                <div class="flex items-center justify-between border-b border-slate-700/60 pb-3">
                    <h3 class="text-base font-extrabold text-white flex items-center gap-2">
                        <span>🎯</span>
                        <span>Wheel Participants</span>
                    </h3>
                    <span id="selectedCountText" class="text-xs font-bold text-amber-400">
                        0 selected
                    </span>
                </div>

                <!-- Quick Filter Buttons -->
                <div class="flex flex-wrap gap-1.5">
                    <button onclick="filterPreset('all')" class="px-2.5 py-1 text-xs rounded-lg font-bold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700/80 transition-colors">
                        All Members
                    </button>
                    <button onclick="filterPreset('r4r5')" class="px-2.5 py-1 text-xs rounded-lg font-bold bg-amber-500/10 hover:bg-amber-500/20 text-amber-300 border border-amber-500/30 transition-colors">
                        R4 / R5 Only
                    </button>
                    <button onclick="filterPreset('r1r3')" class="px-2.5 py-1 text-xs rounded-lg font-bold bg-sky-500/10 hover:bg-sky-500/20 text-sky-300 border border-sky-500/30 transition-colors">
                        R1 – R3 Only
                    </button>
                    <button onclick="clearAllSelections()" class="px-2.5 py-1 text-xs rounded-lg font-bold bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 border border-rose-500/30 transition-colors ml-auto">
                        Deselect All
                    </button>
                </div>

                <!-- Search Filter & Custom Add -->
                <div class="flex flex-col gap-2">
                    <div class="relative">
                        <input type="text" id="memberSearchInput" oninput="onSearchMembers()" placeholder="Search members by name or rank..."
                            class="w-full bg-slate-900/90 border border-slate-700 focus:border-amber-500 rounded-lg pl-8 pr-3 py-1.5 text-xs text-slate-200 placeholder-slate-500 transition-all">
                        <span class="absolute left-2.5 top-2 text-slate-500 text-xs">🔍</span>
                    </div>

                    <!-- Add Custom Guest / Custom Entry -->
                    <div class="flex items-center gap-2">
                        <input type="text" id="customNameInput" placeholder="Add custom player / prize slice..." maxlength="40"
                            class="w-full bg-slate-900/90 border border-slate-700 focus:border-amber-500 rounded-lg px-3 py-1.5 text-xs text-slate-200 placeholder-slate-500 transition-all"
                            onkeydown="if(event.key === 'Enter') addCustomParticipant()">
                        <button onclick="addCustomParticipant()" class="px-3 py-1.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs rounded-lg transition-colors whitespace-nowrap">
                            + Add
                        </button>
                    </div>
                </div>

                <!-- Participants Checkbox List -->
                <div id="participantsListContainer" class="flex flex-col gap-1.5 max-h-[480px] overflow-y-auto pr-1.5 custom-scrollbar border border-slate-800/80 rounded-xl p-2 bg-slate-950/40">
                    <!-- Populated dynamically via JS -->
                </div>

                <!-- Quick Help Tip -->
                <div class="p-3 rounded-xl bg-slate-900/50 border border-slate-800 text-[11px] text-slate-400 flex items-start gap-2">
                    <span class="text-amber-400 text-sm">💡</span>
                    <span>
                        Toggle members to add or remove them from the wheel slice roster. When a winner is chosen, you can easily remove them for consecutive drawings.
                    </span>
                </div>
            </div>

            <!-- Delegated Spinners Summary Card -->
            <?php if (!empty($allowedSpinners)): ?>
                <div class="glass-panel p-5 rounded-2xl shadow-xl flex flex-col gap-3">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center justify-between">
                        <span class="flex items-center gap-1.5">
                            <span>🔑</span>
                            <span>Delegated Wheel Spinners</span>
                        </span>
                        <span class="px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 text-[11px] font-bold border border-emerald-500/30">
                            <?= count($allowedSpinners) ?> active
                        </span>
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($allowedSpinners as $s): ?>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs bg-slate-900/80 border border-slate-700 text-slate-300">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                <span class="font-bold text-white"><?= esc(!empty($s['player_name']) ? $s['player_name'] : $s['username']) ?></span>
                                <span class="text-[10px] text-amber-400 font-semibold"><?= esc($s['alliance_level'] ?? 'R1') ?></span>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>

    </div>

</div>

<!-- WINNER CELEBRATION MODAL -->
<div id="winnerModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md hidden transition-opacity duration-300">
    <div class="relative w-full max-w-md glass-panel p-8 rounded-3xl shadow-[0_0_80px_rgba(245,158,11,0.3)] border border-amber-500/40 text-center flex flex-col items-center gap-4 animate-scaleUp">
        
        <!-- Trophy Icon & Glow -->
        <div class="w-24 h-24 rounded-full bg-gradient-to-tr from-amber-500 via-yellow-300 to-amber-600 p-1 shadow-[0_0_35px_rgba(245,158,11,0.6)] flex items-center justify-center">
            <div class="w-full h-full rounded-full bg-slate-950 flex items-center justify-center text-5xl">
                🏆
            </div>
        </div>

        <div class="space-y-1">
            <span class="text-xs uppercase font-black tracking-widest text-amber-400">We Have A Winner!</span>
            <h2 id="winnerModalName" class="text-3xl sm:text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-yellow-300 via-amber-400 to-amber-500 break-words drop-shadow-md">
                Player Name
            </h2>
            <p id="winnerModalSubtitle" class="text-xs text-slate-400"></p>
        </div>

        <!-- Action Buttons -->
        <div class="w-full flex flex-col gap-2.5 mt-2">
            <button onclick="removeWinnerAndClose()" class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-rose-600 to-rose-700 hover:from-rose-500 hover:to-rose-600 text-white font-bold text-xs uppercase tracking-wider transition-all shadow-md flex items-center justify-center gap-2">
                <span>✂️</span>
                <span>Remove Winner & Spin Next</span>
            </button>
            <button onclick="keepWinnerAndClose()" class="w-full py-3 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs uppercase tracking-wider border border-slate-700 transition-all flex items-center justify-center gap-2">
                <span>👌</span>
                <span>Keep Winner On Wheel</span>
            </button>
        </div>

    </div>
</div>

<!-- MANAGE SPINNERS MODAL (R4/R5/Super Admin) -->
<?php if ($canManage): ?>
<div id="spinnerModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md hidden transition-opacity duration-300">
    <div class="relative w-full max-w-xl glass-panel p-6 sm:p-8 rounded-3xl shadow-2xl border border-slate-700 text-left flex flex-col gap-6">
        
        <div class="flex items-center justify-between border-b border-slate-700/80 pb-4">
            <div class="flex items-center gap-3">
                <span class="text-2xl">👥</span>
                <div>
                    <h2 class="text-lg font-extrabold text-white">Delegated Wheel Spinners</h2>
                    <p class="text-xs text-slate-400">R4/R5 & Super Admin can spin by default. Add other members here.</p>
                </div>
            </div>
            <button onclick="closeSpinnerModal()" class="text-slate-400 hover:text-white text-2xl font-bold">&times;</button>
        </div>

        <!-- Form to Add Spinner -->
        <form action="/wheel/add-spinner" method="POST" class="flex flex-col sm:flex-row gap-3">
            <?= csrf_field() ?>
            <select name="user_id" required class="flex-1 bg-slate-900 border border-slate-700 focus:border-amber-500 rounded-xl px-3 py-2 text-xs text-slate-200">
                <option value="">-- Select Member to Grant Spin Access --</option>
                <?php foreach ($members as $m): ?>
                    <?php 
                        $isInherited = in_array($m['alliance_level'], ['R4', 'R5']) || ($m['role'] ?? '') === 'super_admin';
                        $isAlreadyDelegated = in_array($m['id'], $delegatedUserIds);
                    ?>
                    <?php if (!$isInherited && !$isAlreadyDelegated): ?>
                        <option value="<?= $m['id'] ?>">
                            [<?= esc($m['alliance_level']) ?>] <?= esc($m['display_name']) ?> (<?= esc($m['username']) ?>)
                        </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs uppercase tracking-wider transition-all whitespace-nowrap">
                Grant Access
            </button>
        </form>

        <!-- Current Delegated Spinners List -->
        <div class="flex flex-col gap-3">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Authorized Delegates (<?= count($allowedSpinners) ?>)</h4>
            
            <div class="max-h-60 overflow-y-auto divide-y divide-slate-800 border border-slate-800 rounded-xl bg-slate-950/40">
                <?php if (empty($allowedSpinners)): ?>
                    <div class="p-4 text-center text-xs text-slate-500 italic">
                        No custom delegated spinners yet. Only R4/R5 and Super Admin can currently spin.
                    </div>
                <?php else: ?>
                    <?php foreach ($allowedSpinners as $s): ?>
                        <div class="p-3 flex items-center justify-between hover:bg-slate-900/60 transition-colors">
                            <div>
                                <div class="text-xs font-bold text-white flex items-center gap-1.5">
                                    <span><?= esc(!empty($s['player_name']) ? $s['player_name'] : $s['username']) ?></span>
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300">
                                        <?= esc($s['alliance_level'] ?? 'R1') ?>
                                    </span>
                                </div>
                                <div class="text-[10px] text-slate-500 mt-0.5">
                                    Added by <?= esc(!empty($s['added_by_name']) ? $s['added_by_name'] : ($s['added_by_username'] ?? 'Admin')) ?> on <?= esc(date('M j, Y', strtotime($s['created_at']))) ?>
                                </div>
                            </div>
                            <form action="/wheel/remove-spinner" method="POST" onsubmit="return confirm('Revoke spin permission for this member?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                <input type="hidden" name="user_id" value="<?= $s['user_id'] ?>">
                                <button type="submit" class="px-3 py-1 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 text-rose-300 text-xs font-bold transition-colors">
                                    Revoke
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="flex justify-end pt-2">
            <button type="button" onclick="closeSpinnerModal()" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition-colors">
                Done
            </button>
        </div>

    </div>
</div>
<?php endif; ?>

<!-- CONFETTI CANVAS OVERLAY -->
<canvas id="confettiCanvas" class="fixed inset-0 pointer-events-none z-50 hidden"></canvas>

<!-- WHEEL LOGIC, AUDIO & ANIMATION SCRIPT -->
<script>
    // --- Initial Data from Backend ---
    const rawAllianceMembers = <?= json_encode($members) ?>;
    const canSpin = <?= $canSpin ? 'true' : 'false' ?>;
    const canManage = <?= $canManage ? 'true' : 'false' ?>;
    const csrfName = '<?= csrf_token() ?>';
    const csrfHash = '<?= csrf_hash() ?>';

    // Local state for participants on the wheel
    let participants = [];
    let customCounter = 1;
    let isSpinning = false;
    let currentAngle = 0; // in radians
    let lastWinner = null;
    let soundEnabled = true;

    // Palette of rich vibrant colors for wheel slices
    const sliceColors = [
        '#F59E0B', '#EF4444', '#10B981', '#3B82F6', '#8B5CF6',
        '#EC4899', '#14B8A6', '#F97316', '#6366F1', '#84CC16',
        '#06B6D4', '#E11D48', '#059669', '#7C3AED', '#D97706'
    ];

    // --- Audio Synthesis via Web Audio API ---
    let audioCtx = null;
    function getAudioContext() {
        if (!audioCtx) {
            const AudioContextClass = window.AudioContext || window.webkitAudioContext;
            if (AudioContextClass) {
                audioCtx = new AudioContextClass();
            }
        }
        if (audioCtx && audioCtx.state === 'suspended') {
            audioCtx.resume();
        }
        return audioCtx;
    }

    function playTickSound() {
        if (!soundEnabled) return;
        try {
            const ctx = getAudioContext();
            if (!ctx) return;
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            osc.type = 'triangle';
            osc.frequency.setValueAtTime(600, ctx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(120, ctx.currentTime + 0.035);

            gain.gain.setValueAtTime(0.18, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.035);

            osc.connect(gain);
            gain.connect(ctx.destination);

            osc.start();
            osc.stop(ctx.currentTime + 0.04);
        } catch (e) {
            // Audio context failed or blocked by autoplay
        }
    }

    function playVictoryFanfare() {
        if (!soundEnabled) return;
        try {
            const ctx = getAudioContext();
            if (!ctx) return;
            const notes = [
                { f: 523.25, d: 0.12 }, // C5
                { f: 659.25, d: 0.12 }, // E5
                { f: 783.99, d: 0.16 }, // G5
                { f: 1046.50, d: 0.50 } // C6
            ];
            let now = ctx.currentTime;
            notes.forEach(n => {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(n.f, now);

                gain.gain.setValueAtTime(0.25, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + n.d);

                osc.connect(gain);
                gain.connect(ctx.destination);

                osc.start(now);
                osc.stop(now + n.d);
                now += n.d * 0.85;
            });
        } catch (e) {}
    }

    function toggleSound() {
        soundEnabled = !soundEnabled;
        document.getElementById('soundIcon').innerText = soundEnabled ? '🔊' : '🔇';
        document.getElementById('soundLabel').innerText = soundEnabled ? 'Sound ON' : 'Muted';
    }

    // --- Initialize Participants List ---
    function initParticipants() {
        participants = rawAllianceMembers.map(m => ({
            id: m.id,
            name: m.display_name,
            level: m.alliance_level,
            selected: true,
            isCustom: false
        }));
        renderParticipantsList();
        drawWheel();
    }

    function renderParticipantsList() {
        const container = document.getElementById('participantsListContainer');
        const filter = (document.getElementById('memberSearchInput').value || '').toLowerCase();
        container.innerHTML = '';

        let visibleCount = 0;
        let selectedCount = 0;

        participants.forEach((p, idx) => {
            if (p.selected) selectedCount++;

            const matchesSearch = p.name.toLowerCase().includes(filter) || (p.level && p.level.toLowerCase().includes(filter));
            if (!matchesSearch) return;

            visibleCount++;
            const item = document.createElement('div');
            item.className = `flex items-center justify-between p-2 rounded-lg text-xs transition-colors cursor-pointer ${p.selected ? 'bg-slate-900/90 text-white border border-slate-700/80' : 'bg-slate-950/30 text-slate-500 border border-transparent hover:bg-slate-900/40'}`;
            
            item.onclick = (e) => {
                if (e.target.tagName !== 'BUTTON') {
                    toggleParticipant(idx);
                }
            };

            const left = document.createElement('div');
            left.className = 'flex items-center gap-2 truncate';

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.checked = p.selected;
            checkbox.className = 'w-3.5 h-3.5 rounded bg-slate-800 border-slate-700 text-amber-500 focus:ring-0';
            checkbox.onclick = (e) => e.stopPropagation();
            checkbox.onchange = () => toggleParticipant(idx);

            const badge = document.createElement('span');
            badge.className = 'px-1.5 py-0.5 rounded text-[10px] font-black ' + getRankBadgeColor(p.level);
            badge.innerText = p.level || 'GUEST';

            const name = document.createElement('span');
            name.className = 'font-bold truncate ' + (p.selected ? 'text-slate-100' : 'text-slate-500 line-through');
            name.innerText = p.name;

            left.appendChild(checkbox);
            left.appendChild(badge);
            left.appendChild(name);

            item.appendChild(left);

            if (p.isCustom) {
                const delBtn = document.createElement('button');
                delBtn.innerHTML = '&times;';
                delBtn.className = 'text-rose-400 hover:text-rose-300 font-bold px-1.5 text-sm';
                delBtn.title = 'Remove custom participant';
                delBtn.onclick = (e) => {
                    e.stopPropagation();
                    removeParticipantByIndex(idx);
                };
                item.appendChild(delBtn);
            }

            container.appendChild(item);
        });

        if (visibleCount === 0) {
            container.innerHTML = '<div class="text-center py-4 text-xs text-slate-500 italic">No matching members found.</div>';
        }

        // Update counts
        const activeCount = participants.filter(p => p.selected).length;
        document.getElementById('selectedCountText').innerText = `${activeCount} / ${participants.length} selected`;
        document.getElementById('activeCountBadge').innerText = `${activeCount} on wheel`;
    }

    function getRankBadgeColor(level) {
        switch (level) {
            case 'R5': return 'bg-rose-500/20 text-rose-400 border border-rose-500/30';
            case 'R4': return 'bg-amber-500/20 text-amber-400 border border-amber-500/30';
            case 'R3': return 'bg-indigo-500/20 text-indigo-400 border border-indigo-500/30';
            case 'R2': return 'bg-sky-500/20 text-sky-400 border border-sky-500/30';
            case 'R1': return 'bg-slate-700 text-slate-300';
            default: return 'bg-purple-500/20 text-purple-300 border border-purple-500/30';
        }
    }

    function toggleParticipant(idx) {
        if (isSpinning) return;
        participants[idx].selected = !participants[idx].selected;
        renderParticipantsList();
        drawWheel();
    }

    function removeParticipantByIndex(idx) {
        if (isSpinning) return;
        participants.splice(idx, 1);
        renderParticipantsList();
        drawWheel();
    }

    function addCustomParticipant() {
        if (isSpinning) return;
        const input = document.getElementById('customNameInput');
        const name = (input.value || '').trim();
        if (!name) return;

        participants.unshift({
            id: null,
            name: name,
            level: 'GUEST',
            selected: true,
            isCustom: true
        });

        input.value = '';
        renderParticipantsList();
        drawWheel();
    }

    function onSearchMembers() {
        renderParticipantsList();
    }

    function filterPreset(preset) {
        if (isSpinning) return;
        participants.forEach(p => {
            if (preset === 'all') {
                p.selected = true;
            } else if (preset === 'r4r5') {
                p.selected = (p.level === 'R4' || p.level === 'R5');
            } else if (preset === 'r1r3') {
                p.selected = (p.level === 'R1' || p.level === 'R2' || p.level === 'R3');
            }
        });
        renderParticipantsList();
        drawWheel();
    }

    function clearAllSelections() {
        if (isSpinning) return;
        participants.forEach(p => p.selected = false);
        renderParticipantsList();
        drawWheel();
    }

    function resetToAllMembers() {
        if (isSpinning) return;
        initParticipants();
    }

    function shuffleParticipants() {
        if (isSpinning) return;
        // Fisher-Yates shuffle
        for (let i = participants.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [participants[i], participants[j]] = [participants[j], participants[i]];
        }
        renderParticipantsList();
        drawWheel();
    }

    // --- Wheel Drawing Physics & Canvas ---
    const canvas = document.getElementById('wheelCanvas');
    const ctx = canvas.getContext('2d');

    function getActiveSlices() {
        return participants.filter(p => p.selected);
    }

    function drawWheel() {
        const slices = getActiveSlices();
        const width = canvas.width;
        const height = canvas.height;
        const centerX = width / 2;
        const centerY = height / 2;
        const radius = (width / 2) - 15;

        ctx.clearRect(0, 0, width, height);

        if (slices.length === 0) {
            // Empty state
            ctx.beginPath();
            ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
            ctx.fillStyle = '#0f172a';
            ctx.fill();
            ctx.lineWidth = 6;
            ctx.strokeStyle = '#334155';
            ctx.stroke();

            ctx.fillStyle = '#94a3b8';
            ctx.font = 'bold 20px sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText('Select at least 2 participants', centerX, centerY);
            return;
        }

        const sliceAngle = (2 * Math.PI) / slices.length;

        // Draw Outer Glowing Rim
        ctx.save();
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius + 8, 0, 2 * Math.PI);
        ctx.fillStyle = '#1e293b';
        ctx.fill();
        ctx.lineWidth = 10;
        ctx.strokeStyle = '#F59E0B';
        ctx.shadowColor = 'rgba(245, 158, 11, 0.4)';
        ctx.shadowBlur = 15;
        ctx.stroke();
        ctx.restore();

        // Draw Slices
        slices.forEach((slice, i) => {
            const start = currentAngle + (i * sliceAngle);
            const end = start + sliceAngle;

            ctx.save();
            ctx.beginPath();
            ctx.moveTo(centerX, centerY);
            ctx.arc(centerX, centerY, radius, start, end);
            ctx.closePath();

            ctx.fillStyle = sliceColors[i % sliceColors.length];
            ctx.fill();

            // Inner slice border
            ctx.lineWidth = 1.5;
            ctx.strokeStyle = 'rgba(255, 255, 255, 0.25)';
            ctx.stroke();
            ctx.restore();

            // Draw Text Label
            ctx.save();
            ctx.translate(centerX, centerY);
            ctx.rotate(start + (sliceAngle / 2));
            ctx.textAlign = 'right';
            ctx.textBaseline = 'middle';
            ctx.fillStyle = '#FFFFFF';

            // Scale font size based on slice count
            let fontSize = 16;
            if (slices.length > 30) fontSize = 11;
            else if (slices.length > 20) fontSize = 13;
            else if (slices.length > 12) fontSize = 15;

            ctx.font = `bold ${fontSize}px sans-serif`;
            ctx.shadowColor = 'rgba(0, 0, 0, 0.8)';
            ctx.shadowBlur = 4;

            // Truncate long names so they don't overlap the center hub
            let displayName = slice.name;
            const maxLen = slices.length > 25 ? 12 : 18;
            if (displayName.length > maxLen) {
                displayName = displayName.substring(0, maxLen - 1) + '…';
            }

            // Draw near the outer edge
            ctx.fillText(displayName, radius - 22, 0);
            ctx.restore();

            // Draw perimeter golden peg/stud
            const pegX = centerX + (radius - 2) * Math.cos(start);
            const pegY = centerY + (radius - 2) * Math.sin(start);
            ctx.beginPath();
            ctx.arc(pegX, pegY, 4, 0, 2 * Math.PI);
            ctx.fillStyle = '#FDE68A';
            ctx.shadowColor = '#000000';
            ctx.shadowBlur = 3;
            ctx.fill();
        });
    }

    // --- Spinning Animation Engine ---
    let spinAnimationId = null;

    function startSpin() {
        if (isSpinning) return;
        const slices = getActiveSlices();
        if (slices.length < 2) {
            alert('Please select at least 2 participants on the wheel to spin!');
            return;
        }

        isSpinning = true;
        const spinBtn = document.getElementById('spinBtn');
        if (spinBtn) {
            spinBtn.disabled = true;
            spinBtn.classList.add('opacity-50', 'cursor-not-allowed');
            spinBtn.innerHTML = '<span>🌀</span><span>SPINNING...</span><span>🌀</span>';
        }

        // Random spin physics
        const duration = 5500 + Math.random() * 2000; // 5.5 to 7.5 seconds
        const fullRotations = 5 + Math.floor(Math.random() * 5); // 5 to 9 full spins
        const extraAngle = Math.random() * 2 * Math.PI;
        const totalDistance = (fullRotations * 2 * Math.PI) + extraAngle;
        const startAngle = currentAngle;
        const startTime = performance.now();

        let lastPegPassed = -1;
        const sliceAngle = (2 * Math.PI) / slices.length;

        function animate(now) {
            const elapsed = now - startTime;
            const progress = Math.min(elapsed / duration, 1);

            // Ease out cubic deceleration
            const easeOut = 1 - Math.pow(1 - progress, 3.5);
            currentAngle = startAngle + (totalDistance * easeOut);

            // Needle ticking physics
            // Pointer is at the top: angle 3*PI/2 (270 degrees)
            const pointerAngle = 1.5 * Math.PI;
            const normalized = ((pointerAngle - currentAngle) % (2 * Math.PI) + 2 * Math.PI) % (2 * Math.PI);
            const currentPegIndex = Math.floor(normalized / sliceAngle);

            if (currentPegIndex !== lastPegPassed) {
                lastPegPassed = currentPegIndex;
                playTickSound();
                wigglePointer();
            }

            drawWheel();

            if (progress < 1) {
                spinAnimationId = requestAnimationFrame(animate);
            } else {
                // Spin complete! Determine winning slice
                isSpinning = false;
                if (spinBtn) {
                    spinBtn.disabled = false;
                    spinBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    spinBtn.innerHTML = '<span>⚡</span><span>SPIN THE WHEEL</span><span>⚡</span>';
                }

                const winningIndex = Math.floor(normalized / sliceAngle) % slices.length;
                const winner = slices[winningIndex];
                onSpinComplete(winner);
            }
        }

        spinAnimationId = requestAnimationFrame(animate);
    }

    function wigglePointer() {
        const pointer = document.getElementById('wheelPointer');
        if (!pointer) return;
        pointer.style.transform = 'rotate(-16deg)';
        setTimeout(() => {
            pointer.style.transform = 'rotate(0deg)';
        }, 60);
    }

    function onSpinComplete(winner) {
        lastWinner = winner;

        // Victory sound & celebration
        playVictoryFanfare();
        triggerConfetti();

        // Show winner in mini indicator
        document.getElementById('winnerAnnounceMini').innerText = `🏆 Winner: ${winner.name}`;

        // Show winner modal
        document.getElementById('winnerModalName').innerText = winner.name;
        document.getElementById('winnerModalSubtitle').innerText = (winner.level && winner.level !== 'GUEST') 
            ? `Rank: ${winner.level} • TKU Alliance Champion` 
            : `Alliance Raffle Champion`;
        
        const modal = document.getElementById('winnerModal');
        modal.classList.remove('hidden');

        // Record spin to backend
        recordSpinToBackend(winner);
    }

    function recordSpinToBackend(winner) {
        const title = (document.getElementById('raffleTitle').value || 'Alliance Raffle').trim();
        const activeCount = getActiveSlices().length;

        fetch('/wheel/record-spin', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                winner_name: winner.name,
                winner_user_id: winner.id,
                title: title,
                participants_count: activeCount,
                [csrfName]: csrfHash
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.record) {
                prependHistoryRow(data.record);
            }
        })
        .catch(err => {
            console.error('Failed to log spin history:', err);
        });
    }

    function prependHistoryRow(record) {
        const tableBody = document.getElementById('historyTableBody');
        const emptyRow = document.getElementById('emptyHistoryRow');
        if (emptyRow) emptyRow.remove();

        const tr = document.createElement('tr');
        tr.className = 'hover:bg-slate-800/40 transition-colors animate-fadeIn';
        tr.innerHTML = `
            <td class="py-2.5 px-3 font-bold text-amber-400 flex items-center gap-1.5">
                <span>🏆</span>
                <span>${escapeHtml(record.winner_name)}</span>
            </td>
            <td class="py-2.5 px-3 text-slate-300">${escapeHtml(record.title || 'Alliance Raffle')}</td>
            <td class="py-2.5 px-3 text-slate-400">${escapeHtml(record.spun_by_name)}</td>
            <td class="py-2.5 px-3 text-slate-400">${record.participants_count} players</td>
            <td class="py-2.5 px-3 text-slate-500 text-right font-mono">Just Now</td>
        `;

        tableBody.insertBefore(tr, tableBody.firstChild);
    }

    function escapeHtml(str) {
        return (str || '').replace(/[&<>"']/g, function(m) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
        });
    }

    // Modal actions
    function closeWinnerModal() {
        document.getElementById('winnerModal').classList.add('hidden');
        stopConfetti();
    }

    function keepWinnerAndClose() {
        closeWinnerModal();
    }

    function removeWinnerAndClose() {
        if (lastWinner) {
            // Find and uncheck/remove
            const idx = participants.findIndex(p => p.name === lastWinner.name && p.selected);
            if (idx !== -1) {
                if (participants[idx].isCustom) {
                    participants.splice(idx, 1);
                } else {
                    participants[idx].selected = false;
                }
                renderParticipantsList();
                drawWheel();
            }
        }
        closeWinnerModal();
    }

    // Clear History Action (for R4/R5/Admin)
    function clearSpinHistory() {
        if (!confirm('Are you sure you want to clear the spin history? This cannot be undone.')) return;

        fetch('/wheel/clear-history', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ [csrfName]: csrfHash })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('historyTableBody').innerHTML = `
                    <tr id="emptyHistoryRow">
                        <td colspan="5" class="py-6 text-center text-slate-500 italic">
                            No spins recorded yet. Spin the wheel to crown the first winner!
                        </td>
                    </tr>
                `;
            }
        });
    }

    // Modal management for Spinner Permissions
    function openSpinnerModal() {
        document.getElementById('spinnerModal').classList.remove('hidden');
    }

    function closeSpinnerModal() {
        document.getElementById('spinnerModal').classList.add('hidden');
    }

    // --- Confetti Particle System ---
    const confettiCanvas = document.getElementById('confettiCanvas');
    const confettiCtx = confettiCanvas.getContext('2d');
    let confettiParticles = [];
    let confettiAnimationId = null;

    function resizeConfetti() {
        confettiCanvas.width = window.innerWidth;
        confettiCanvas.height = window.innerHeight;
    }
    window.addEventListener('resize', resizeConfetti);
    resizeConfetti();

    function triggerConfetti() {
        confettiCanvas.classList.remove('hidden');
        confettiParticles = [];
        const colors = ['#F59E0B', '#EF4444', '#10B981', '#3B82F6', '#8B5CF6', '#F43F5E', '#FBBF24'];

        for (let i = 0; i < 150; i++) {
            confettiParticles.push({
                x: window.innerWidth / 2,
                y: window.innerHeight / 2,
                vx: (Math.random() - 0.5) * 18,
                vy: (Math.random() - 0.7) * 18,
                size: Math.random() * 8 + 4,
                color: colors[Math.floor(Math.random() * colors.length)],
                rotation: Math.random() * 360,
                rotSpeed: (Math.random() - 0.5) * 12,
                gravity: 0.35,
                opacity: 1
            });
        }

        if (confettiAnimationId) cancelAnimationFrame(confettiAnimationId);
        animateConfetti();
    }

    function animateConfetti() {
        confettiCtx.clearRect(0, 0, confettiCanvas.width, confettiCanvas.height);
        let active = false;

        confettiParticles.forEach(p => {
            p.x += p.vx;
            p.y += p.vy;
            p.vy += p.gravity;
            p.vx *= 0.98;
            p.rotation += p.rotSpeed;
            p.opacity -= 0.005;

            if (p.opacity > 0 && p.y < confettiCanvas.height + 20) {
                active = true;
                confettiCtx.save();
                confettiCtx.translate(p.x, p.y);
                confettiCtx.rotate((p.rotation * Math.PI) / 180);
                confettiCtx.fillStyle = p.color;
                confettiCtx.globalAlpha = Math.max(p.opacity, 0);
                confettiCtx.fillRect(-p.size / 2, -p.size / 2, p.size, p.size);
                confettiCtx.restore();
            }
        });

        if (active) {
            confettiAnimationId = requestAnimationFrame(animateConfetti);
        } else {
            stopConfetti();
        }
    }

    function stopConfetti() {
        if (confettiAnimationId) cancelAnimationFrame(confettiAnimationId);
        confettiCtx.clearRect(0, 0, confettiCanvas.width, confettiCanvas.height);
        confettiCanvas.classList.add('hidden');
    }

    // --- On DOM Ready ---
    document.addEventListener('DOMContentLoaded', () => {
        initParticipants();
    });
</script>

<style>
    @keyframes scaleUp {
        0% { transform: scale(0.8); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }
    .animate-scaleUp {
        animation: scaleUp 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    @keyframes fadeIn {
        0% { opacity: 0; transform: translateY(-6px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeIn {
        animation: fadeIn 0.3s ease-out forwards;
    }
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: rgba(15, 23, 42, 0.4);
        border-radius: 9999px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(100, 116, 139, 0.4);
        border-radius: 9999px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(100, 116, 139, 0.7);
    }
</style>

<?= $this->include('templates/footer') ?>

