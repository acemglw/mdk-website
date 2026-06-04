<?= $this->include('templates/header', ['title' => 'Page Access Logs - MDK Alliance']) ?>

<div class="glass-panel p-6 md:p-8 rounded-2xl shadow-2xl max-w-6xl w-full mx-auto relative z-10">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 border-b border-slate-700/50 pb-4 gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 via-green-300 to-emerald-500 tracking-tight drop-shadow-sm">
                System Access Logs
            </h1>
            <p class="text-slate-400 text-sm mt-1">
                <?php if ($view_mode === 'page_detail'): ?>
                    Viewing detailed history for <span class="text-emerald-400 font-mono break-all"><?= esc($selected_url) ?></span>
                <?php elseif ($view_mode === 'snapshots'): ?>
                    Viewing Weekly Snapshots Archive.
                <?php elseif ($view_mode === 'top_users'): ?>
                    Viewing Most Active Users across all pages.
                <?php else: ?>
                    Monitor aggregate page views across the system.
                <?php endif; ?>
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <?php if ($view_mode === 'page_detail'): ?>
                <a href="/super_admin/access-logs?view_type=<?= esc($view_type) ?><?= isset($_GET['user_id']) ? '&user_id=' . esc($_GET['user_id']) : '' ?>" class="px-4 py-2 bg-slate-800/80 hover:bg-slate-700 text-slate-300 text-sm font-semibold rounded-xl border border-slate-600 transition-colors shadow-sm">
                    &larr; Back to Page List
                </a>
            <?php endif; ?>
            
            <?php if ($view_mode === 'snapshots'): ?>
                <a href="/super_admin/access-logs" class="px-4 py-2 bg-emerald-600/80 hover:bg-emerald-500 text-white text-sm font-semibold rounded-xl border border-emerald-500 transition-colors shadow-sm">
                    View Live Logs
                </a>
            <?php else: ?>
                <a href="/super_admin/access-logs?view_type=snapshots" class="px-4 py-2 bg-indigo-600/80 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl border border-indigo-500 transition-colors shadow-sm">
                    View Snapshots
                </a>
            <?php endif; ?>

            <a href="/dashboard" class="px-4 py-2 bg-slate-800/80 hover:bg-slate-700 text-slate-300 text-sm font-semibold rounded-xl border border-slate-600 transition-colors shadow-sm">
                &larr; Dashboard
            </a>
        </div>
    </div>

    <!-- Filters & Action Buttons -->
    <?php if ($view_mode !== 'page_detail'): ?>
        <div class="mb-6 bg-slate-900/50 p-4 rounded-xl border border-slate-700/50 flex flex-col md:flex-row justify-between items-center gap-4">
            <form action="/super_admin/access-logs" method="GET" class="flex flex-col md:flex-row md:items-end gap-4 w-full md:w-auto">
                <input type="hidden" name="view_type" value="<?= esc($view_type) ?>">
                
                <?php if ($view_mode !== 'top_users'): ?>
                <div class="flex-1">
                    <label class="block text-slate-300 text-sm font-bold mb-2">Filter by User</label>
                    <select name="user_id" class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-2 text-slate-200 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50 transition-colors">
                        <option value="">-- All Users --</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?= esc($u['id']) ?>" <?= (isset($_GET['user_id']) && $_GET['user_id'] == $u['id']) ? 'selected' : '' ?>>
                                <?= esc($u['player_name'] ?: $u['username']) ?> (<?= esc($u['username']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <div class="w-full md:w-auto mt-2 md:mt-0 flex gap-2">
                    <?php if ($view_mode !== 'top_users'): ?>
                        <button type="submit" class="w-full md:w-auto px-6 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-lg transition-colors">
                            Filter
                        </button>
                    <?php endif; ?>

                    <?php if ($view_mode === 'page_list'): ?>
                        <a href="/super_admin/access-logs?view_type=top_users" class="w-full md:w-auto px-6 py-2 bg-amber-600 hover:bg-amber-500 text-white font-bold rounded-lg transition-colors text-center">
                            Top Users
                        </a>
                    <?php elseif ($view_mode === 'top_users'): ?>
                        <a href="/super_admin/access-logs" class="w-full md:w-auto px-6 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-lg transition-colors text-center">
                            Back to Page List
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <?php if ($view_mode !== 'snapshots'): ?>
            <div class="w-full md:w-auto border-t md:border-t-0 md:border-l border-slate-700/50 pt-4 md:pt-0 md:pl-4">
                <form action="/super_admin/access-logs/snapshot" method="POST" onsubmit="return confirm('Are you sure you want to run the snapshot? This will archive all current logs and clear the live tracking table.');">
                    <?= csrf_field() ?>
                    <button type="submit" class="w-full md:w-auto px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white text-sm font-bold rounded-lg transition-colors flex items-center gap-2">
                        <span>📸</span> Run Manual Snapshot
                    </button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Error/Success Messages -->
    <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-rose-500/10 border border-rose-500/50 text-rose-400 p-4 rounded-xl mb-6 text-sm text-center">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-emerald-500/10 border border-emerald-500/50 text-emerald-400 p-4 rounded-xl mb-6 text-sm text-center">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <!-- Logs Table -->
    <div class="overflow-x-auto rounded-xl border border-slate-700/50 shadow-inner">
        <table class="w-full text-left border-collapse">
            <thead>
                <?php if ($view_mode === 'snapshots'): ?>
                    <tr class="bg-indigo-900/80 border-b border-indigo-700 text-indigo-300 text-sm uppercase tracking-wider">
                        <th class="p-4 font-semibold">User</th>
                        <th class="p-4 font-semibold">URL Page</th>
                        <th class="p-4 font-semibold text-center w-32">Visits</th>
                        <th class="p-4 font-semibold">First Visit</th>
                        <th class="p-4 font-semibold">Last Visit</th>
                    </tr>
                <?php elseif ($view_mode === 'top_users'): ?>
                    <tr class="bg-amber-900/80 border-b border-amber-700 text-amber-300 text-sm uppercase tracking-wider">
                        <th class="p-4 font-semibold w-16 text-center">Rank</th>
                        <th class="p-4 font-semibold">User</th>
                        <th class="p-4 font-semibold text-center w-40">Total Actions</th>
                        <th class="p-4 font-semibold w-48">Last Active</th>
                        <th class="p-4 font-semibold text-right w-24">Action</th>
                    </tr>
                <?php elseif ($view_mode === 'page_list'): ?>
                    <tr class="bg-slate-800/80 border-b border-slate-700 text-slate-300 text-sm uppercase tracking-wider">
                        <th class="p-4 font-semibold">URL Page</th>
                        <th class="p-4 font-semibold text-center w-32">Total Visits</th>
                        <th class="p-4 font-semibold w-48">Last Visited</th>
                        <th class="p-4 font-semibold text-right w-24">Action</th>
                    </tr>
                <?php else: ?>
                    <tr class="bg-slate-800/80 border-b border-slate-700 text-slate-300 text-sm uppercase tracking-wider">
                        <th class="p-4 font-semibold w-48">Timestamp</th>
                        <th class="p-4 font-semibold">User</th>
                        <th class="p-4 font-semibold">IP Address</th>
                        <th class="p-4 font-semibold text-right w-24">Details</th>
                    </tr>
                <?php endif; ?>
            </thead>
            <tbody class="divide-y divide-slate-800 bg-slate-900/20">
                <?php if (!empty($logs)): ?>
                    <?php $rank = 1 + ($pager->getCurrentPage() - 1) * 50; ?>
                    <?php foreach ($logs as $log): ?>
                        <tr class="hover:bg-slate-800/50 transition-colors">
                            <?php if ($view_mode === 'snapshots'): ?>
                                <td class="p-4">
                                    <?php if ($log['username']): ?>
                                        <div class="text-slate-200 font-medium"><?= esc($log['player_name'] ?: $log['username']) ?></div>
                                        <div class="text-slate-500 text-xs">@<?= esc($log['username']) ?></div>
                                    <?php else: ?>
                                        <span class="text-slate-500 italic">Anonymous/Guest</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-sm text-slate-300 break-all font-mono">
                                    <?= esc($log['url']) ?>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="inline-flex items-center justify-center px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-400 font-bold text-sm border border-indigo-500/30">
                                        <?= esc($log['visit_count']) ?>
                                    </span>
                                </td>
                                <td class="p-4 text-sm text-slate-400 whitespace-nowrap">
                                    <?= date('Y-m-d H:i', strtotime($log['start_date'])) ?>
                                </td>
                                <td class="p-4 text-sm text-slate-400 whitespace-nowrap">
                                    <?= date('Y-m-d H:i', strtotime($log['end_date'])) ?>
                                </td>
                            <?php elseif ($view_mode === 'top_users'): ?>
                                <td class="p-4 text-center">
                                    <?php if ($rank === 1): ?>
                                        <span class="text-2xl" title="Rank 1">🥇</span>
                                    <?php elseif ($rank === 2): ?>
                                        <span class="text-2xl" title="Rank 2">🥈</span>
                                    <?php elseif ($rank === 3): ?>
                                        <span class="text-2xl" title="Rank 3">🥉</span>
                                    <?php else: ?>
                                        <span class="text-slate-400 font-bold">#<?= $rank ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4">
                                    <?php if ($log['username']): ?>
                                        <div class="text-slate-200 font-medium"><?= esc($log['player_name'] ?: $log['username']) ?></div>
                                        <div class="text-slate-500 text-xs">@<?= esc($log['username']) ?></div>
                                    <?php else: ?>
                                        <span class="text-slate-500 italic">Anonymous/Guest</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="inline-flex items-center justify-center px-3 py-1 rounded-full bg-amber-500/20 text-amber-400 font-bold text-sm border border-amber-500/30">
                                        <?= esc($log['total_visits']) ?>
                                    </span>
                                </td>
                                <td class="p-4 text-sm text-slate-400 whitespace-nowrap">
                                    <?= date('Y-m-d H:i:s', strtotime($log['last_active'])) ?>
                                </td>
                                <td class="p-4 text-right">
                                    <a href="/super_admin/access-logs?user_id=<?= esc($log['user_id']) ?>" class="text-xs px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-md transition-colors inline-block whitespace-nowrap">
                                        View Pages
                                    </a>
                                </td>
                                <?php $rank++; ?>
                            <?php elseif ($view_mode === 'page_list'): ?>
                                <td class="p-4 text-sm text-slate-300 break-all font-mono">
                                    <a href="/super_admin/access-logs?url=<?= urlencode($log['url']) ?><?= isset($_GET['user_id']) ? '&user_id=' . esc($_GET['user_id']) : '' ?>" class="hover:text-emerald-400 transition-colors block">
                                        <?= esc($log['url']) ?>
                                    </a>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="inline-flex items-center justify-center px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-400 font-bold text-sm border border-emerald-500/30">
                                        <?= esc($log['visit_count']) ?>
                                    </span>
                                </td>
                                <td class="p-4 text-sm text-slate-400 whitespace-nowrap">
                                    <?= date('Y-m-d H:i:s', strtotime($log['last_visited'])) ?>
                                </td>
                                <td class="p-4 text-right">
                                    <a href="/super_admin/access-logs?url=<?= urlencode($log['url']) ?><?= isset($_GET['user_id']) ? '&user_id=' . esc($_GET['user_id']) : '' ?>" class="text-xs px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-md transition-colors inline-block">
                                        View History
                                    </a>
                                </td>
                            <?php else: ?>
                                <td class="p-4 text-sm text-slate-400 whitespace-nowrap">
                                    <?= date('Y-m-d H:i:s', strtotime($log['created_at'])) ?>
                                </td>
                                <td class="p-4">
                                    <?php if ($log['username']): ?>
                                        <div class="text-slate-200 font-medium"><?= esc($log['player_name'] ?: $log['username']) ?></div>
                                        <div class="text-slate-500 text-xs">@<?= esc($log['username']) ?></div>
                                    <?php else: ?>
                                        <span class="text-slate-500 italic">Anonymous/Guest</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-sm text-slate-400 font-mono">
                                    <?= esc($log['ip_address']) ?>
                                </td>
                                <td class="p-4 text-right">
                                    <button onclick="alert('User Agent:\n<?= htmlspecialchars(addslashes($log['user_agent']), ENT_QUOTES) ?>')" class="text-xs text-slate-400 hover:text-emerald-400 transition-colors" title="View User Agent">
                                        ℹ️ Info
                                    </button>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-500 italic">No access logs found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6 flex justify-center">
        <?= $pager->links() ?>
    </div>
</div>

<?= $this->include('templates/footer') ?>