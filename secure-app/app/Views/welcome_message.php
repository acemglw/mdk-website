<?= $this->include('templates/header', ['title' => $title]) ?>

<div class="glass-panel p-8 md:p-12 rounded-2xl shadow-2xl max-w-3xl w-full text-center relative z-10 mx-4 mt-8 mb-12">
    
    <!-- Header Section -->
    <div class="mb-10 flex flex-col items-center mt-4">
        <div class="w-32 h-32 mb-6 relative shadow-[0_0_40px_rgba(245,158,11,0.2)] rounded-full flex items-center justify-center bg-slate-900/50">
            <div class="absolute inset-0 rounded-full border-2 border-amber-500/50 animate-pulse"></div>
            <img src="/images/mdk_logo.png" alt="MDK Logo" class="w-28 h-28 object-contain rounded-full relative z-10 drop-shadow-lg" onerror="this.src='/images/mdk_log.png';" />
        </div>

        <h1 class="text-4xl md:text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-500 mb-4 tracking-tight drop-shadow-sm">
            MDK Alliance
        </h1>
        <h2 class="text-xl md:text-2xl font-medium text-slate-300">
            Welcome to the Administration Page
        </h2>
    </div>

    <p class="text-slate-400 mb-10 text-lg leading-relaxed max-w-xl mx-auto">
        Manage your alliance strategies, coordinate battle formations, and prepare your warriors for upcoming events.
    </p>

    <!-- Tools Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-2xl mx-auto">

        <!-- Link to Planner (Protected by middleware, but visible here) -->
        <a href="/ds_planner" class="group flex flex-col items-center justify-center p-6 bg-slate-900/60 hover:bg-slate-800 border border-slate-700/60 hover:border-amber-500/50 rounded-xl transition-all duration-300 cursor-pointer shadow-lg hover:shadow-amber-900/20">
            <div class="w-12 h-12 bg-slate-800 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300 border border-slate-700">
                <span class="text-2xl">🗺️</span>
            </div>
            <span class="font-bold text-slate-200 text-lg mb-1">Desert Storm Planner</span>
            <span class="text-sm text-slate-400 text-center">
                <?php if (session()->get('is_logged_in')): ?>
                    Tactical map room & assignments
                <?php else: ?>
                    <span class="text-rose-400 font-bold">Requires Login 🔒</span>
                <?php endif; ?>
            </span>
        </a>
        
        <a href="/cs_planner" class="group flex flex-col items-center justify-center p-6 bg-slate-900/60 hover:bg-slate-800 border border-slate-700/60 hover:border-amber-500/50 rounded-xl transition-all duration-300 cursor-pointer shadow-lg hover:shadow-amber-900/20">
            <div class="w-12 h-12 bg-slate-800 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300 border border-slate-700">
                <span class="text-2xl">⛰️</span>
            </div>
            <span class="font-bold text-slate-200 text-lg mb-1">Canyon Storm Planner</span>
            <span class="text-sm text-slate-400 text-center">
                <?php if (session()->get('is_logged_in')): ?>
                    Tactical map room for Canyon Storm
                <?php else: ?>
                    <span class="text-rose-400 font-bold">Requires Login 🔒</span>
                <?php endif; ?>
            </span>
        </a>

    </div>
</div>

<?= $this->include('templates/footer') ?>