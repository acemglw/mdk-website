<?= $this->include('templates/header', ['title' => 'Register - MDK Alliance']) ?>

<div class="glass-panel p-8 md:p-12 rounded-2xl shadow-2xl max-w-md w-full relative z-10 mx-4 my-8">
    <!-- Header Section -->
    <div class="mb-8 text-center">
        <div class="inline-flex w-20 h-20 mb-4 relative shadow-[0_0_30px_rgba(245,158,11,0.2)] rounded-full items-center justify-center bg-slate-900/50">
            <div class="absolute inset-0 rounded-full border-2 border-amber-500/50 animate-pulse"></div>
            <img src="/images/mdk_logo.png" alt="MDK Logo" class="w-16 h-16 object-contain rounded-full relative z-10 drop-shadow-lg" onerror="this.src='/images/mdk_log.png';" />
        </div>
        <h1 class="text-2xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-500 tracking-tight drop-shadow-sm">
            Alliance Registration
        </h1>
        <p class="text-slate-400 text-sm mt-2">Join the ranks. Await approval.</p>
    </div>

    <!-- Error Messages -->
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="bg-rose-500/10 border border-rose-500/50 text-rose-400 p-4 rounded-xl mb-6 text-sm">
            <ul class="list-disc list-inside pl-2">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Registration Form -->
    <form action="/register/store" method="POST" class="space-y-4">
        <?= csrf_field() ?>
        <div>
            <label class="block text-slate-300 text-sm font-bold mb-1">Username (Login ID)</label>
            <input type="text" name="username" value="<?= old('username') ?>" required
                class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-slate-200 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/50 transition-colors"
                placeholder="e.g. jsmith123">
        </div>

        <div>
            <label class="block text-slate-300 text-sm font-bold mb-1">In-Game Alias (Player Name)</label>
            <p class="text-xs text-slate-500 mb-2">How you are known in the game (optional)</p>
            <input type="text" name="player_name" value="<?= old('player_name') ?>"
                class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-slate-200 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/50 transition-colors"
                placeholder="e.g. MDK_ACE">
        </div>

        <div>
            <label class="block text-slate-300 text-sm font-bold mb-2">Email Address</label>
            <input type="email" name="email" value="<?= old('email') ?>" required
                class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-slate-200 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/50 transition-colors"
                placeholder="commander@example.com">
        </div>

        <div>
            <label class="block text-slate-300 text-sm font-bold mb-2">Alliance Level</label>
            <select name="alliance_level" required class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-slate-200 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/50 transition-colors appearance-none">
                <option value="" disabled selected>Select your rank...</option>
                <option value="R5" <?= old('alliance_level') === 'R5' ? 'selected' : '' ?>>R5 (Leader)</option>
                <option value="R4" <?= old('alliance_level') === 'R4' ? 'selected' : '' ?>>R4 (Officer)</option>
                <option value="R3" <?= old('alliance_level') === 'R3' ? 'selected' : '' ?>>R3 (Veteran)</option>
                <option value="R2" <?= old('alliance_level') === 'R2' ? 'selected' : '' ?>>R2 (Member)</option>
                <option value="R1" <?= old('alliance_level') === 'R1' ? 'selected' : '' ?>>R1 (Recruit)</option>
            </select>
        </div>

        <div>
            <label class="block text-slate-300 text-sm font-bold mb-2">Password</label>
            <input type="password" name="password" required minlength="8"
                class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-slate-200 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/50 transition-colors"
                placeholder="Min 8 characters">
        </div>

        <div class="pt-4">
            <button type="submit" class="w-full bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-slate-900 font-extrabold py-3 rounded-xl transition-all shadow-[0_0_20px_rgba(245,158,11,0.2)] hover:shadow-[0_0_25px_rgba(245,158,11,0.4)]">
                Submit Application
            </button>
        </div>
    </form>

    <div class="mt-6 text-center">
        <a href="/login" class="text-sm text-slate-400 hover:text-amber-400 transition-colors">
            Already have an account? Log in here
        </a>
    </div>
</div>

<?= $this->include('templates/footer') ?>