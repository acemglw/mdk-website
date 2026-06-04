<?= $this->include('templates/header', ['title' => 'Add Members - Admin']) ?>

<div class="glass-panel p-8 md:p-12 rounded-2xl shadow-2xl max-w-5xl w-full relative z-10 mx-4 my-8">
    
    <div class="mb-8 flex flex-col items-center border-b border-slate-800 pb-8">
        <h1 class="text-2xl md:text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-500 mb-2 tracking-tight drop-shadow-sm text-center">
            Add Alliance Members
        </h1>
        <p class="text-slate-400 text-sm text-center">Manually add single recruits or bulk import via CSV.</p>
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

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        
        <!-- Form 1: Manual Addition -->
        <div class="bg-slate-900/80 border border-slate-700/60 rounded-xl p-6 shadow-lg">
            <h3 class="text-lg font-bold text-slate-200 mb-4 flex items-center gap-2">
                <span>📝</span> Manual Entry
            </h3>
            
            <form action="/admin/users/add" method="POST" class="space-y-4">
                <?= csrf_field() ?>
                <div>
                    <label class="block text-slate-400 text-xs font-bold mb-1">Username (Login ID)</label>
                    <input type="text" name="username" value="<?= old('username') ?>" required
                        class="w-full bg-slate-950/50 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-amber-500 transition-colors">
                </div>

                <div>
                    <label class="block text-slate-400 text-xs font-bold mb-1">In-Game Player Name</label>
                    <input type="text" name="player_name" value="<?= old('player_name') ?>"
                        class="w-full bg-slate-950/50 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-amber-500 transition-colors">
                </div>
                
                <div>
                    <label class="block text-slate-400 text-xs font-bold mb-1">Email</label>
                    <input type="email" name="email" value="<?= old('email') ?>" required
                        class="w-full bg-slate-950/50 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-amber-500 transition-colors">
                </div>
                
                <div class="flex gap-2">
                    <div class="flex-1">
                        <label class="block text-slate-400 text-xs font-bold mb-1">Rank</label>
                        <select name="alliance_level" required class="w-full bg-slate-950/50 border border-slate-700 rounded-lg px-2 py-2 text-sm text-slate-200 focus:outline-none focus:border-amber-500 transition-colors appearance-none">
                            <option value="R1">R1</option>
                            <option value="R2">R2</option>
                            <option value="R3">R3</option>
                            <option value="R4">R4</option>
                            <option value="R5">R5</option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-slate-400 text-xs font-bold mb-1">Role</label>
                        <select name="role" required class="w-full bg-slate-950/50 border border-slate-700 rounded-lg px-2 py-2 text-sm text-slate-200 focus:outline-none focus:border-amber-500 transition-colors appearance-none">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label class="block text-slate-400 text-xs font-bold mb-1">Password</label>
                    <input type="password" name="password" required minlength="8"
                        class="w-full bg-slate-950/50 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-amber-500 transition-colors"
                        placeholder="Min 8 characters">
                </div>
                
                <div class="pt-4">
                    <button type="submit" class="w-full bg-slate-800 hover:bg-slate-700 text-amber-400 border border-slate-700 hover:border-amber-500/50 font-bold py-3 rounded-lg transition-colors shadow-sm">
                        Create Approved User
                    </button>
                </div>
            </form>
        </div>

        <!-- Form 2: CSV Bulk Import -->
        <div class="bg-slate-900/80 border border-slate-700/60 rounded-xl p-6 shadow-lg flex flex-col">
            <h3 class="text-lg font-bold text-slate-200 mb-4 flex items-center gap-2">
                <span>📁</span> Bulk CSV Import
            </h3>
            
            <p class="text-xs text-slate-400 mb-6 leading-relaxed">
                Upload a CSV file containing your alliance roster. The first row must be headers.<br><br>
                <strong>Format required:</strong><br>
                <code class="block mt-2 bg-slate-950 p-2 rounded border border-slate-800 text-amber-500/80">username, player_name, email, alliance_level, role, password</code>
            </p>

            <form action="/admin/users/import" method="POST" enctype="multipart/form-data" class="flex-1 flex flex-col justify-between">
                <?= csrf_field() ?>
                
                <div class="border-2 border-dashed border-slate-700 rounded-xl p-8 text-center hover:border-amber-500/50 transition-colors bg-slate-950/30 mb-6">
                    <input type="file" name="csv_file" accept=".csv" required class="block w-full text-sm text-slate-400
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-full file:border-0
                        file:text-sm file:font-semibold
                        file:bg-slate-800 file:text-amber-400
                        hover:file:bg-slate-700 cursor-pointer"/>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-slate-900 font-extrabold py-3 rounded-lg transition-all shadow-[0_0_20px_rgba(245,158,11,0.2)] hover:shadow-[0_0_25px_rgba(245,158,11,0.4)]">
                    Upload & Import Roster
                </button>
            </form>
        </div>

    </div>

    <div class="mt-8 text-center">
        <a href="/admin/users" class="inline-flex items-center text-sm text-slate-400 hover:text-amber-400 transition-colors">
            ← Back to Roster
        </a>
    </div>
</div>

<?= $this->include('templates/footer') ?>