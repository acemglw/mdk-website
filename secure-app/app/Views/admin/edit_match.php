<?= $this->include('templates/header', ['title' => 'Edit Match Participation - Admin']) ?>

<div class="glass-panel p-8 md:p-12 rounded-2xl shadow-2xl max-w-6xl w-full relative z-10 mx-4 my-8 flex flex-col" style="height: 85vh;">
    
    <!-- Header Section -->
    <div class="mb-4 flex flex-col items-center border-b border-slate-800 pb-4 shrink-0">
        <div class="w-12 h-12 mb-2 relative shadow-[0_0_40px_rgba(245,158,11,0.2)] rounded-full flex items-center justify-center bg-slate-900/50">
            <div class="absolute inset-0 rounded-full border-2 border-amber-500/50 animate-[pulse_3s_linear_infinite]"></div>
            <span class="text-xl relative z-10 drop-shadow-lg">⚔️</span>
        </div>

        <h1 class="text-xl md:text-2xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-500 mb-1 tracking-tight drop-shadow-sm text-center">
            Edit Match Participation
        </h1>
        <p class="text-slate-400 text-xs text-center">Update participation status and score for match on <?= date('M d, Y @ H:i', strtotime($selected_datetime)) ?></p>
    </div>

    <!-- Alert Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-emerald-500/10 border border-emerald-500/50 text-emerald-400 p-3 rounded-xl mb-4 text-xs text-center shrink-0">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-rose-500/10 border border-rose-500/50 text-rose-400 p-3 rounded-xl mb-4 text-xs text-center shrink-0">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="flex-1 overflow-hidden flex flex-col bg-slate-900/60 border border-slate-700/60 rounded-xl relative">
        <!-- Edit Mode -->
        <form action="/admin/events/log_participation" method="POST" class="flex flex-col h-full absolute inset-0">
            <?= csrf_field() ?>
            <input type="hidden" name="event_id" value="<?= esc($event_id) ?>">
            <input type="hidden" name="event_datetime" value="<?= esc($selected_datetime) ?>">

            <div class="flex-1 overflow-y-auto custom-scrollbar">
                <table class="w-full text-left text-sm text-slate-300 border-collapse">
                    <thead class="sticky top-0 z-20 text-[10px] uppercase bg-slate-800 text-slate-400 border-b border-slate-700 shadow-md">
                        <tr>
                            <th class="px-6 py-4 bg-slate-800">Member</th>
                            <th class="px-6 py-4 text-center bg-slate-800">Assigned Box</th>
                            <th class="px-6 py-4 text-center bg-slate-800">Participated?</th>
                            <th class="px-6 py-4 text-right bg-slate-800">Final Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($roster)): ?>
                            <tr><td colspan="4" class="px-6 py-12 text-center text-slate-500 italic">No users were assigned to this match.</td></tr>
                        <?php else: ?>
                            <?php foreach ($roster as $user): ?>
                                <tr class="border-b border-slate-700/50 hover:bg-slate-800/30 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-200">
                                            <?= !empty($user['player_name']) ? esc($user['player_name']) : esc($user['username']) ?>
                                            <?php if ($user['is_temp']): ?>
                                                <span class="text-[8px] bg-slate-800 text-slate-500 px-1 py-0.5 rounded ml-1">TEMP</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2 py-0.5 rounded border border-amber-600/30 bg-amber-900/10 text-amber-500 text-[9px] font-bold uppercase">
                                            <?= esc($user['assigned_position']) ?>
                                        </span>
                                    </td>
                                    
                                    <td class="px-6 py-4 text-center">
                                        <label class="inline-flex items-center cursor-pointer p-2">
                                            <input type="checkbox" name="users[<?= $user['user_id'] ?: $user['user_id_temp'] ?>][participated]" value="1" 
                                                class="w-5 h-5 rounded border-slate-700 text-emerald-500 focus:ring-emerald-500/50 bg-slate-950 transition-all hover:border-emerald-500"
                                                <?= $user['status'] === 'participated' ? 'checked' : '' ?>>
                                        </label>
                                    </td>
                                    
                                    <td class="px-6 py-4 text-right">
                                        <input type="number" name="users[<?= $user['user_id'] ?: $user['user_id_temp'] ?>][score]" value="<?= esc($user['score']) ?>"
                                            class="w-20 bg-slate-950/50 border border-slate-700 rounded px-2 py-1.5 text-xs text-slate-200 focus:outline-none focus:border-amber-500 font-mono text-right ml-auto">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-800 bg-slate-900/80 shrink-0 flex justify-between items-center z-20 shadow-[0_-4px_10px_rgba(0,0,0,0.3)]">
                <div class="text-[10px] text-slate-500 italic max-w-xs">
                    Checked users will be marked as "Participated". Unchecked users will be marked as "Missed".
                </div>
                <button type="submit" class="bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-amber-500 text-slate-950 font-black py-2.5 px-8 rounded-xl transition-all shadow-lg text-xs uppercase tracking-widest">
                    Update Results
                </button>
            </div>
        </form>
    </div>

    <div class="mt-4 text-center shrink-0">
        <a href="/admin/events" class="inline-flex items-center text-xs text-slate-500 hover:text-amber-400 transition-colors">
            ← Back to Summary
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