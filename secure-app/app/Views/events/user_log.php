<?= $this->include('templates/header', ['title' => 'My Event Log']) ?>

<div class="glass-panel p-8 md:p-12 rounded-2xl shadow-2xl max-w-4xl w-full relative z-10 mx-4 my-8">
    
    <!-- Header Section -->
    <div class="mb-8 flex flex-col items-center">
        <div class="w-20 h-20 mb-4 relative shadow-[0_0_40px_rgba(56,189,248,0.2)] rounded-full flex items-center justify-center bg-slate-900/50">
            <div class="absolute inset-0 rounded-full border-2 border-sky-500/50 animate-[pulse_3s_linear_infinite]"></div>
            <span class="text-3xl relative z-10 drop-shadow-lg">📊</span>
        </div>

        <h1 class="text-2xl md:text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-sky-400 to-indigo-500 mb-2 tracking-tight drop-shadow-sm text-center">
            My Event History
        </h1>
        <p class="text-slate-400 text-sm text-center">Your participation and performance records per match.</p>
    </div>

    <!-- History Table -->
    <div class="bg-slate-900/60 border border-slate-700/60 rounded-xl overflow-hidden">
        <?php if (empty($logs)): ?>
            <div class="p-8 text-center text-slate-400 flex flex-col items-center justify-center">
                <span class="text-4xl block mb-3">📭</span>
                You don't have any logged event history yet.
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="text-xs uppercase bg-slate-800/80 text-slate-400 border-b border-slate-700">
                        <tr>
                            <th class="px-6 py-4">Match Date & Time</th>
                            <th class="px-6 py-4">Event Name</th>
                            <th class="px-6 py-4 text-center">Assignment</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-right">Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr class="border-b border-slate-700/50 hover:bg-slate-800/30 transition-colors">
                                <td class="px-6 py-4 font-bold text-slate-200">
                                    <?= date('M d, Y @ H:i', strtotime($log['event_datetime'])) ?>
                                </td>
                                <td class="px-6 py-4 font-semibold text-sky-400">
                                    <?= esc($log['event_name']) ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php if (!empty($log['assigned_position'])): ?>
                                        <span class="px-2 py-0.5 rounded border border-slate-600 bg-slate-800 text-slate-300 text-[10px] font-mono uppercase">
                                            <?= esc($log['assigned_position']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-600 text-xs">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php if ($log['status'] === 'participated'): ?>
                                        <span class="px-2 py-1 rounded bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold uppercase">Participated</span>
                                    <?php elseif ($log['status'] === 'excused'): ?>
                                        <span class="px-2 py-1 rounded bg-amber-500/10 border border-amber-500/30 text-amber-400 text-xs font-bold uppercase">Excused</span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 rounded bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs font-bold uppercase">Missed</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right font-mono font-bold text-slate-200">
                                    <?= number_format($log['score']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="mt-8 text-center">
        <a href="/dashboard" class="inline-flex items-center text-sm text-slate-400 hover:text-sky-400 transition-colors">
            ← Back to Dashboard
        </a>
    </div>
</div>

<?= $this->include('templates/footer') ?>