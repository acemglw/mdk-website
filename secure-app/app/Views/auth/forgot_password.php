<?= $this->include('templates/header', ['title' => 'Forgot Password - MDK Alliance']) ?>

<div class="glass-panel p-8 md:p-12 rounded-2xl shadow-2xl max-w-md w-full relative z-10 mx-4">
    <!-- Header Section -->
    <div class="mb-8 text-center">
        <div class="inline-flex w-20 h-20 mb-4 relative shadow-[0_0_30px_rgba(245,158,11,0.2)] rounded-full items-center justify-center bg-slate-900/50">
            <div class="absolute inset-0 rounded-full border-2 border-amber-500/50 animate-pulse"></div>
            <img src="/images/mdk_logo.png" alt="MDK Logo" class="w-16 h-16 object-contain rounded-full relative z-10 drop-shadow-lg" onerror="this.src='/images/mdk_logo_placeholder.png';" />
        </div>
        <h1 class="text-2xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-500 tracking-tight drop-shadow-sm">
            Recover Access
        </h1>
        <p class="text-slate-400 text-sm mt-2">Enter your email to receive a password reset link.</p>
    </div>

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

    <!-- Forgot Password Form -->
    <form action="/forgot-password/send" method="POST" class="space-y-4">
        <?= csrf_field() ?>
        <div>
            <label class="block text-slate-300 text-sm font-bold mb-2">Email Address</label>
            <input type="email" name="email" required autocomplete="email"
                class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-slate-200 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/50 transition-colors"
                placeholder="Enter your registered email">
        </div>

        <div class="pt-4">
            <button type="submit" class="w-full bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-slate-900 font-extrabold py-3 rounded-xl transition-all shadow-[0_0_20px_rgba(245,158,11,0.2)] hover:shadow-[0_0_25px_rgba(245,158,11,0.4)]">
                Send Reset Link
            </button>
        </div>
    </form>

    <div class="mt-6 text-center">
        <a href="/login" class="text-sm text-slate-400 hover:text-amber-400 transition-colors">
            Remembered? Return to Login
        </a>
    </div>
</div>

<?= $this->include('templates/footer') ?>