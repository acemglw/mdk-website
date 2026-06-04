<?= $this->include('templates/header', ['title' => 'Event Tracking - Admin']) ?>

<div class="glass-panel p-8 md:p-12 rounded-2xl shadow-2xl max-w-6xl w-full relative z-10 mx-4 my-8 max-h-[90vh] flex flex-col">
    
    <!-- Header Section -->
    <div class="mb-8 flex flex-col items-center border-b border-slate-800 pb-8 shrink-0">
        <div class="w-16 h-16 mb-4 relative shadow-[0_0_40px_rgba(245,158,11,0.2)] rounded-full flex items-center justify-center bg-slate-900/50">
            <div class="absolute inset-0 rounded-full border-2 border-amber-500/50 animate-[pulse_3s_linear_infinite]"></div>
            <span class="text-2xl relative z-10 drop-shadow-lg">📊</span>
        </div>

        <h1 class="text-xl md:text-2xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-500 mb-1 tracking-tight drop-shadow-sm text-center">
            Match Participation Tracker
        </h1>
        <p class="text-slate-400 text-xs text-center">Reviewing match results and attendance reliability.</p>
    </div>

    <!-- Alert Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-emerald-500/10 border border-emerald-500/50 text-emerald-400 p-3 rounded-xl mb-6 text-xs text-center shrink-0">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-rose-500/10 border border-rose-500/50 text-rose-400 p-3 rounded-xl mb-6 text-xs text-center shrink-0">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <!-- Filter Section -->
    <div class="mb-6 flex flex-wrap items-center justify-between gap-6 bg-slate-900/40 p-4 rounded-xl border border-slate-800 shrink-0">
        
        <form action="/admin/events" method="GET" class="flex items-center gap-3">
            <label class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Select Event:</label>
            <select name="event_id" onchange="this.form.submit()" class="bg-slate-950/50 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-amber-500 appearance-none">
                <?php foreach ($events as $event): ?>
                    <option value="<?= $event['id'] ?>" <?= $event_id == $event['id'] ? 'selected' : '' ?>>
                        <?= esc($event['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label class="text-slate-400 text-[10px] font-bold uppercase tracking-wider ml-4">Reliability Range:</label>
            <select name="weeks" onchange="this.form.submit()" class="bg-slate-950/50 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-amber-500 appearance-none">
                <option value="1" <?= $lookback_weeks == 1 ? 'selected' : '' ?>>Last 1 Week</option>
                <option value="2" <?= $lookback_weeks == 2 ? 'selected' : '' ?>>Last 2 Weeks</option>
                <option value="4" <?= $lookback_weeks == 4 ? 'selected' : '' ?>>Last 4 Weeks</option>
                <option value="8" <?= $lookback_weeks == 8 ? 'selected' : '' ?>>Last 8 Weeks</option>
            </select>
        </form>

        <form action="/admin/events/edit" method="GET" class="flex items-center gap-3">
            <input type="hidden" name="event_id" value="<?= esc($event_id) ?>">
            
            <label class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Edit Match:</label>
            <select name="event_datetime" class="bg-slate-950/50 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-amber-500 appearance-none">
                <option value="" selected disabled>Select a match</option>
                <?php foreach ($historical_plans as $plan): ?>
                    <?php 
                        $val = str_replace(' ', 'T', substr($plan['event_datetime'], 0, 16));
                    ?>
                    <option value="<?= $val ?>">
                        Match: <?= date('M d, Y @ H:i', strtotime($plan['event_datetime'])) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="bg-amber-600 hover:bg-amber-500 text-white font-bold py-2 px-4 rounded-lg text-xs transition-colors">
                Edit
            </button>
        </form>
    </div>

    <!-- Main Content -->
    <div class="flex-1 overflow-hidden flex flex-col bg-slate-900/60 border border-slate-700/60 rounded-xl">
        <!-- Summary View -->
        <div class="flex-1 overflow-y-auto custom-scrollbar">
            <table class="w-full text-left text-sm text-slate-300 border-collapse">
                <thead class="sticky top-0 z-20 text-[10px] uppercase bg-slate-800 text-slate-400 border-b border-slate-700 shadow-md">
                    <tr>
                        <th class="px-6 py-4 bg-slate-800">Member</th>
                        <th class="px-6 py-4 text-center bg-slate-800">Matches Assigned</th>
                        <th class="px-6 py-4 text-center bg-slate-800">Played</th>
                        <th class="px-6 py-4 text-center bg-slate-800">Missed</th>
                        <th class="px-6 py-4 text-center bg-slate-800">Miss Streak</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($roster)): ?>
                        <tr><td colspan="5" class="px-6 py-12 text-center text-slate-500 italic">No participation data for the selected range.</td></tr>
                    <?php else: ?>
                        <?php foreach ($roster as $user): ?>
                            <?php 
                                $isDanger = ($user['streak'] > 0);
                                $rowClass = $isDanger ? 'bg-rose-500/5' : '';
                            ?>
                            <tr class="border-b border-slate-700/50 hover:bg-slate-800/30 transition-colors <?= $rowClass ?>">
                                <td class="px-6 py-4">
                                    <div class="font-bold <?= $isDanger ? 'text-rose-400' : 'text-slate-200' ?>">
                                        <?= !empty($user['player_name']) ? esc($user['player_name']) : esc($user['username']) ?>
                                        <?php if (empty($user['user_id'])): ?>
                                            <span class="text-[8px] bg-slate-800 text-slate-500 px-1 py-0.5 rounded ml-1">TEMP</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center"><?= $user['total_matches'] ?></td>
                                <td class="px-6 py-4 text-center text-emerald-400 font-bold"><?= $user['participated_count'] ?></td>
                                <td class="px-6 py-4 text-center <?= $user['missed_count'] > 0 ? 'text-rose-400 font-bold' : '' ?>"><?= $user['missed_count'] ?></td>
                                <td class="px-6 py-4 text-center">
                                    <?php if ($user['streak'] > 0): ?>
                                        <span class="px-2 py-0.5 rounded bg-rose-500 text-white text-[9px] font-black">
                                            <?= $user['streak'] ?> WKS
                                        </span>
                                    <?php else: ?>
                                        <span class="text-emerald-500 font-bold">0</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4 text-center shrink-0">
        <a href="/admin/settings" class="inline-flex items-center text-xs text-slate-500 hover:text-amber-400 transition-colors">
            ← Back to Admin
        </a>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #475569; }
</style>

<?= $this->include('templates/footer') ?>