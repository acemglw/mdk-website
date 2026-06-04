<?= $this->include('templates/header', ['title' => 'Manage Users - Admin']) ?>

<div class="glass-panel p-8 md:p-12 rounded-2xl shadow-2xl max-w-5xl w-full relative z-10 mx-4 my-8">
    <!-- Header Section -->
    <div class="mb-8 flex flex-col items-center">
        <div class="w-20 h-20 mb-4 relative shadow-[0_0_40px_rgba(245,158,11,0.2)] rounded-full flex items-center justify-center bg-slate-900/50">
            <div class="absolute inset-0 rounded-full border-2 border-amber-500/50 animate-[pulse_3s_linear_infinite]"></div>
            <span class="text-3xl relative z-10 drop-shadow-lg">👥</span>
        </div>

        <h1 class="text-2xl md:text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-500 mb-2 tracking-tight drop-shadow-sm text-center">
            Alliance Roster Management
        </h1>
        <p class="text-slate-400 text-sm text-center">View active members and manage their access.</p>
        
        <div class="mt-6 flex flex-wrap items-center justify-center gap-4">
            <a href="/admin/users/add" class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-slate-900 font-extrabold py-2 px-6 rounded-xl transition-all shadow-[0_0_20px_rgba(245,158,11,0.2)] hover:shadow-[0_0_25px_rgba(245,158,11,0.4)] text-sm">
                <span>➕</span> Add Members
            </a>

            <!-- Player Search Filter -->
            <form action="/admin/users" method="GET" class="relative group">
                <input type="text" name="search" value="<?= esc($search ?? '') ?>" 
                    placeholder="Search by name or email..." 
                    class="bg-slate-900/80 border border-slate-700 rounded-xl py-2 pl-4 pr-10 text-xs text-slate-200 focus:outline-none focus:border-amber-500 w-64 transition-all focus:ring-1 focus:ring-amber-500/50">
                <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 group-hover:text-amber-500 transition-colors">
                    🔍
                </button>
                <?php if (!empty($search)): ?>
                    <a href="/admin/users" class="absolute -bottom-5 right-0 text-[9px] text-slate-500 hover:text-amber-400 uppercase font-bold tracking-tighter">Clear Filter</a>
                <?php endif; ?>
            </form>
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

    <!-- Users Table (Main Content) -->
    <div class="w-full">
        <div class="bg-slate-900/60 border border-slate-700/60 rounded-xl overflow-hidden h-full">
            <?php if (empty($users)): ?>
                <div class="p-8 text-center text-slate-400 flex flex-col items-center justify-center h-full">
                    <span class="text-4xl block mb-3">👻</span>
                    <?= !empty($search) ? 'No matches found for "'.esc($search).'"' : 'No active users found in the system.' ?>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-300">
                        <thead class="text-xs uppercase bg-slate-800/80 text-slate-400 border-b border-slate-700">
                            <tr>
                                <th class="px-4 py-3">Player / Login</th>
                                <th class="px-4 py-3 text-center">Rank</th>
                                <th class="px-4 py-3 text-center">Role</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr class="border-b border-slate-700/50 hover:bg-slate-800/30 transition-colors">
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-slate-200">
                                            <?= !empty($user['player_name']) ? esc($user['player_name']) : esc($user['username']) ?>
                                        </div>
                                        <div class="text-[10px] text-slate-500">
                                            Login: <?= esc($user['username']) ?> 
                                            <?php if (session()->get('role') === 'super_admin'): ?>
                                                | <?= esc($user['email']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2 py-1 rounded bg-amber-500/10 border border-amber-500/30 text-amber-400 text-xs font-bold">
                                            <?= esc($user['alliance_level']) ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <?php if ($user['role'] === 'super_admin'): ?>
                                            <span class="px-2 py-0.5 rounded bg-rose-500/10 text-rose-400 text-[10px] font-bold uppercase tracking-wider">Super Admin</span>
                                        <?php elseif ($user['role'] === 'admin'): ?>
                                            <span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-400 text-[10px] font-bold uppercase tracking-wider">Admin</span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 rounded bg-slate-700/50 text-slate-300 text-[10px] font-bold uppercase tracking-wider">User</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <?php if ($user['status'] === 'approved'): ?>
                                            <span class="text-emerald-400 font-bold text-xs">● Active</span>
                                        <?php elseif ($user['status'] === 'pending'): ?>
                                            <span class="text-amber-400 font-bold text-xs">● Pending</span>
                                        <?php else: ?>
                                            <span class="text-rose-400 font-bold text-xs">● Rejected</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a href="/admin/users/edit/<?= $user['id'] ?>" class="p-1.5 bg-sky-500/10 hover:bg-sky-500/20 text-sky-400 border border-sky-500/30 rounded transition-colors text-xs" title="Edit User">
                                                ✏️
                                            </a>
                                            
                                            <?php if ($user['id'] != session()->get('id')): ?>
                                                <form action="/admin/users/delete" method="POST" class="inline" onsubmit="return confirm('Are you absolutely sure you want to permanently delete <?= esc($user['username']) ?>?');">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                    <button type="submit" class="p-1.5 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 rounded transition-colors text-xs" title="Delete User">
                                                        🗑️
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-[10px] text-slate-500 italic p-1.5">Me</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-8 text-center">
        <a href="/admin/settings" class="inline-flex items-center text-sm text-slate-400 hover:text-amber-400 transition-colors">
            ← Back to Admin Settings
        </a>
    </div>
</div>

<?= $this->include('templates/footer') ?>