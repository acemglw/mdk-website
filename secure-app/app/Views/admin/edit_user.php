<?= $this->include('templates/header', ['title' => 'Edit User - Admin']) ?>

<div class="glass-panel p-8 md:p-12 rounded-2xl shadow-2xl max-w-2xl w-full relative z-10 mx-4 my-8">
    
    <div class="mb-8 flex flex-col items-center border-b border-slate-800 pb-8">
        <h1 class="text-2xl md:text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-500 mb-2 tracking-tight drop-shadow-sm text-center">
            Modify Member Profile
        </h1>
        <p class="text-slate-400 text-sm text-center">Updating record for: <span class="text-amber-500 font-bold"><?= esc($user['username']) ?></span></p>
    </div>

    <!-- Alert Messages -->
    <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-rose-500/10 border border-rose-500/50 text-rose-400 p-4 rounded-xl mb-6 text-sm text-center">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="bg-rose-500/10 border border-rose-500/50 text-rose-400 p-4 rounded-xl mb-6 text-sm">
            <ul class="list-disc list-inside pl-2">
                <?php foreach (session()->getFlashdata('errors') as $err): ?>
                    <li><?= esc($err) ?></li>
                <?php endforeach ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="/admin/users/update" method="POST" class="space-y-6">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $user['id'] ?>">
        <!-- Ensure username is sent even if disabled -->
        <input type="hidden" name="username" value="<?= esc($user['username']) ?>">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-slate-500 text-xs font-bold mb-1 uppercase tracking-wider">Login Username (Locked)</label>
                <input type="text" value="<?= esc($user['username']) ?>" disabled
                    class="w-full bg-slate-900/30 border border-slate-800 rounded-lg px-4 py-3 text-slate-500 cursor-not-allowed font-mono">
            </div>

            <div>
                <label class="block text-slate-400 text-xs font-bold mb-1 uppercase tracking-wider">In-Game Player Name</label>
                <input type="text" name="player_name" value="<?= esc($user['player_name']) ?>"
                    class="w-full bg-slate-950/50 border border-slate-700 rounded-lg px-4 py-3 text-slate-200 focus:outline-none focus:border-amber-500 transition-colors">
            </div>
        </div>

        <div>
            <label class="block text-slate-400 text-xs font-bold mb-1 uppercase tracking-wider">Email Address</label>
            <input type="email" name="email" value="<?= esc($user['email']) ?>" required
                class="w-full bg-slate-950/50 border border-slate-700 rounded-lg px-4 py-3 text-slate-200 focus:outline-none focus:border-amber-500 transition-colors">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-slate-400 text-xs font-bold mb-1 uppercase tracking-wider">Alliance Rank</label>
                <select name="alliance_level" required class="w-full bg-slate-950/50 border border-slate-700 rounded-lg px-3 py-3 text-slate-200 focus:outline-none focus:border-amber-500 transition-colors appearance-none">
                    <?php foreach (['R1', 'R2', 'R3', 'R4', 'R5'] as $level): ?>
                        <option value="<?= $level ?>" <?= $user['alliance_level'] === $level ? 'selected' : '' ?>><?= $level ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-slate-400 text-xs font-bold mb-1 uppercase tracking-wider">System Role</label>
                <select name="role" required class="w-full bg-slate-950/50 border border-slate-700 rounded-lg px-3 py-3 text-slate-200 focus:outline-none focus:border-amber-500 transition-colors appearance-none">
                    <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Standard User</option>
                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Administrator</option>
                    
                    <?php if (session()->get('role') === 'super_admin'): ?>
                        <option value="super_admin" <?= $user['role'] === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                    <?php elseif ($user['role'] === 'super_admin'): ?>
                        <!-- If a standard admin views a super_admin somehow, keep the option visible but it will be blocked on submit -->
                        <option value="super_admin" selected>Super Admin</option>
                    <?php endif; ?>
                </select>
            </div>

            <div>
                <label class="block text-slate-400 text-xs font-bold mb-1 uppercase tracking-wider">Account Status</label>
                <select name="status" required class="w-full bg-slate-950/50 border border-slate-700 rounded-lg px-3 py-3 text-slate-200 focus:outline-none focus:border-amber-500 transition-colors appearance-none">
                    <option value="pending" <?= $user['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="approved" <?= $user['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="rejected" <?= $user['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-800">
            <label class="block text-slate-400 text-xs font-bold mb-1 uppercase tracking-wider">Reset Password</label>
            <p class="text-[10px] text-slate-500 mb-2 italic">Leave blank to keep existing password</p>
            <input type="password" name="password" minlength="8"
                class="w-full bg-slate-950/50 border border-slate-700 rounded-lg px-4 py-3 text-slate-200 focus:outline-none focus:border-amber-500 transition-colors"
                placeholder="New password (min 8 chars)">
        </div>

        <div class="pt-6 flex gap-4">
            <a href="/admin/users" class="flex-1 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold py-3 rounded-xl text-center transition-colors">
                Cancel
            </a>
            <button type="submit" class="flex-[2] bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-slate-900 font-extrabold py-3 rounded-xl transition-all shadow-lg">
                Update User Record
            </button>
        </div>
    </form>
</div>

<?= $this->include('templates/footer') ?>