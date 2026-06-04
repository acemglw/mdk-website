<?= $this->include('templates/header', ['title' => 'User Approvals - Admin']) ?>

<div class="glass-panel p-8 md:p-12 rounded-2xl shadow-2xl max-w-5xl w-full relative z-10 mx-4">
    <!-- Header Section -->
    <div class="mb-8 flex flex-col items-center">
        <div class="w-20 h-20 mb-4 relative shadow-[0_0_40px_rgba(245,158,11,0.2)] rounded-full flex items-center justify-center bg-slate-900/50">
            <div class="absolute inset-0 rounded-full border-2 border-amber-500/50 animate-[pulse_2s_linear_infinite]"></div>
            <span class="text-3xl relative z-10 drop-shadow-lg">📋</span>
        </div>

        <h1 class="text-2xl md:text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-500 mb-2 tracking-tight drop-shadow-sm text-center">
            Pending Recruit Approvals
        </h1>
        <p class="text-slate-400 text-sm text-center">Review and approve new alliance members.</p>
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

    <!-- Pending Users Table/List -->
    <div class="bg-slate-900/60 border border-slate-700/60 rounded-xl overflow-hidden">
        <?php if (empty($pending_users)): ?>
            <div class="p-8 text-center text-slate-400">
                <span class="text-4xl block mb-3">✅</span>
                No pending requests. The recruitment queue is empty!
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="text-xs uppercase bg-slate-800/80 text-slate-400 border-b border-slate-700">
                        <tr>
                            <th class="px-6 py-4">Player / Login</th>
                            <?php if (session()->get('role') === 'super_admin'): ?>
                                <th class="px-6 py-4">Email</th>
                            <?php endif; ?>
                            <th class="px-6 py-4 text-center">Rank</th>
                            <th class="px-6 py-4 text-center">Applied On</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_users as $user): ?>
                            <tr class="border-b border-slate-700/50 hover:bg-slate-800/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-200">
                                        <?= !empty($user['player_name']) ? esc($user['player_name']) : esc($user['username']) ?>
                                    </div>
                                    <div class="text-[10px] text-slate-500">
                                        Login: <?= esc($user['username']) ?>
                                    </div>
                                </td>
                                <?php if (session()->get('role') === 'super_admin'): ?>
                                    <td class="px-6 py-4"><?= esc($user['email']) ?></td>
                                <?php endif; ?>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-1 rounded bg-amber-500/10 border border-amber-500/30 text-amber-400 text-xs font-bold">
                                        <?= esc($user['alliance_level']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center text-slate-500">
                                    <?= date('M d, Y', strtotime($user['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <form action="/admin/approvals/update" method="POST" class="inline-flex gap-2">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        
                                        <button type="submit" name="action" value="approve" 
                                            class="p-2 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 rounded transition-colors" title="Approve">
                                            ✓
                                        </button>
                                        
                                        <button type="submit" name="action" value="reject" 
                                            class="p-2 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 rounded transition-colors" title="Reject">
                                            ✕
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="mt-8 text-center">
        <a href="/admin/settings" class="inline-flex items-center text-sm text-slate-400 hover:text-amber-400 transition-colors">
            ← Back to Admin Settings
        </a>
    </div>
</div>

<?= $this->include('templates/footer') ?>