<?= $this->include('templates/header', ['title' => 'Admin Settings']) ?>

<div class="glass-panel p-8 md:p-12 rounded-2xl shadow-2xl max-w-4xl w-full text-center relative z-10 mx-4">
    <!-- Header Section -->
    <div class="mb-12 flex flex-col items-center">
        <div class="w-20 h-20 mb-4 relative shadow-[0_0_40px_rgba(245,158,11,0.2)] rounded-full flex items-center justify-center bg-slate-900/50">
            <div class="absolute inset-0 rounded-full border-2 border-amber-500/50 animate-[pulse_3s_linear_infinite]"></div>
            <span class="text-3xl relative z-10 drop-shadow-lg">⚙️</span>
        </div>

        <h1 class="text-2xl md:text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-500 mb-2 tracking-tight drop-shadow-sm">
            Alliance Control Center
        </h1>
        <p class="text-slate-400 text-sm">System administration and roster management.</p>
    </div>

    <!-- Administrative Modules Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-3xl mx-auto">
        
        <!-- Module: Approvals -->
        <a href="/admin/approvals" class="flex flex-col items-center p-6 bg-slate-900/60 hover:bg-slate-800 border border-slate-700/60 hover:border-amber-500/50 rounded-2xl transition-all duration-300 group shadow-lg">
            <div class="w-12 h-12 bg-slate-800 rounded-xl flex items-center justify-center border border-slate-700 mb-4 group-hover:scale-110 transition-transform">
                <span class="text-2xl">📋</span>
            </div>
            <h3 class="font-bold text-slate-200 mb-1">Recruit Approvals</h3>
            <p class="text-[11px] text-slate-500 text-center leading-relaxed">Review and approve new alliance applications</p>
        </a>

        <!-- Module: Roster Management -->
        <a href="/admin/users" class="flex flex-col items-center p-6 bg-slate-900/60 hover:bg-slate-800 border border-slate-700/60 hover:border-amber-500/50 rounded-2xl transition-all duration-300 group shadow-lg">
            <div class="w-12 h-12 bg-slate-800 rounded-xl flex items-center justify-center border border-slate-700 mb-4 group-hover:scale-110 transition-transform">
                <span class="text-2xl">👥</span>
            </div>
            <h3 class="font-bold text-slate-200 mb-1">Manage Roster</h3>
            <p class="text-[11px] text-slate-500 text-center leading-relaxed">View members, assign roles, or remove users</p>
        </a>

        <!-- Module: Squad Powers Management -->
        <a href="/admin/users/squad_powers" class="flex flex-col items-center p-6 bg-slate-900/60 hover:bg-slate-800 border border-slate-700/60 hover:border-amber-500/50 rounded-2xl transition-all duration-300 group shadow-lg">
            <div class="w-12 h-12 bg-slate-800 rounded-xl flex items-center justify-center border border-slate-700 mb-4 group-hover:scale-110 transition-transform">
                <span class="text-2xl">🛡️</span>
            </div>
            <h3 class="font-bold text-slate-200 mb-1">Combat Roster</h3>
            <p class="text-[11px] text-slate-500 text-center leading-relaxed">Monitor and update everyone's squad powers</p>
        </a>

        <!-- Module: Event Logs -->
        <a href="/admin/events" class="flex flex-col items-center p-6 bg-slate-900/60 hover:bg-slate-800 border border-slate-700/60 hover:border-amber-500/50 rounded-2xl transition-all duration-300 group shadow-lg">
            <div class="w-12 h-12 bg-slate-800 rounded-xl flex items-center justify-center border border-slate-700 mb-4 group-hover:scale-110 transition-transform">
                <span class="text-2xl">📊</span>
            </div>
            <h3 class="font-bold text-slate-200 mb-1">Participation Tracker</h3>
            <p class="text-[11px] text-slate-500 text-center leading-relaxed">Log event scores and attendance</p>
        </a>

        <?php if (session()->get('role') === 'super_admin'): ?>
        <!-- Module: Access Logs -->
        <a href="/super_admin/access-logs" class="flex flex-col items-center p-6 bg-slate-900/60 hover:bg-slate-800 border border-slate-700/60 hover:border-emerald-500/50 rounded-2xl transition-all duration-300 group shadow-lg col-span-1 md:col-span-2 md:w-1/2 md:mx-auto">
            <div class="w-12 h-12 bg-slate-800 rounded-xl flex items-center justify-center border border-slate-700 mb-4 group-hover:scale-110 transition-transform">
                <span class="text-2xl">👁️</span>
            </div>
            <h3 class="font-bold text-emerald-400 mb-1">Access Logs</h3>
            <p class="text-[11px] text-slate-500 text-center leading-relaxed">System-wide page visit tracking (Super Admin only)</p>
        </a>
        <?php endif; ?>

    </div>

    <div class="mt-12 pt-8 border-t border-slate-800/60">
        <a href="/dashboard" class="inline-flex items-center text-sm text-slate-400 hover:text-amber-400 transition-colors">
            ← Back to Dashboard
        </a>
    </div>
</div>

<?= $this->include('templates/footer') ?>