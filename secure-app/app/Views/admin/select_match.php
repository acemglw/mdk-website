<?= $this->include('templates/header', ['title' => 'Select Match - Admin']) ?>

<div class="glass-panel p-8 md:p-12 rounded-2xl shadow-2xl max-w-2xl w-full text-center relative z-10 mx-4 my-8">
    
    <!-- Header Section -->
    <div class="mb-8 flex flex-col items-center border-b border-slate-800 pb-8">
        <div class="w-20 h-20 mb-4 relative shadow-[0_0_40px_rgba(245,158,11,0.2)] rounded-full flex items-center justify-center bg-slate-900/50">
            <div class="absolute inset-0 rounded-full border-2 border-amber-500/50 animate-[pulse_3s_linear_infinite]"></div>
            <span class="text-3xl relative z-10 drop-shadow-lg">⚔️</span>
        </div>

        <h1 class="text-2xl md:text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-500 mb-2 tracking-tight drop-shadow-sm">
            Match Results Editor
        </h1>
        <p class="text-slate-400 text-sm max-w-md mx-auto mt-2">
            Select a specific event match to edit player participation and scores.
        </p>
    </div>

    <!-- Alert Messages -->
    <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-rose-500/10 border border-rose-500/50 text-rose-400 p-4 rounded-xl mb-6 text-sm text-center">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <!-- Selection Form -->
    <form action="/admin/events/edit" method="GET" class="flex flex-col gap-6 max-w-md mx-auto">
        
        <!-- We use javascript to reload the page when the event changes to fetch the correct historical plans -->
        <div>
            <label class="block text-slate-400 text-sm font-bold mb-2 text-left">Event Type:</label>
            <select name="event_id" id="event_select" required onchange="window.location.href='/admin/events/select?event_id=' + this.value" class="w-full bg-slate-950/50 border border-slate-700 rounded-lg px-4 py-3 text-sm text-slate-200 focus:outline-none focus:border-amber-500 transition-colors appearance-none">
                <option value="" disabled <?= empty($selected_event_id) ? 'selected' : '' ?>>-- Select an Event --</option>
                <?php foreach ($events as $event): ?>
                    <option value="<?= $event['id'] ?>" <?= ($selected_event_id == $event['id']) ? 'selected' : '' ?>><?= esc($event['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block text-slate-400 text-sm font-bold mb-2 text-left">Match Date & Time:</label>
            <select name="event_datetime" required class="w-full bg-slate-950/50 border border-slate-700 rounded-lg px-4 py-3 text-sm text-slate-200 focus:outline-none focus:border-amber-500 transition-colors appearance-none">
                <?php if (empty($selected_event_id)): ?>
                    <option value="" disabled selected>Please select an Event Type first.</option>
                <?php elseif (empty($historical_plans)): ?>
                    <option value="" disabled selected>No matches have been planned for this event.</option>
                <?php else: ?>
                    <?php 
                    // Make sure we only show unique dates across all plans
                    $uniqueDates = [];
                    foreach ($historical_plans as $plan) {
                        $dateStr = date('M d, Y @ H:i', strtotime($plan['event_datetime']));
                        if (!isset($uniqueDates[$dateStr])) {
                            $uniqueDates[$dateStr] = $plan['event_datetime'];
                        }
                    }
                    ?>
                    <?php foreach ($uniqueDates as $displayStr => $rawVal): ?>
                        <option value="<?= $rawVal ?>">
                            <?= $displayStr ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <button type="submit" <?= (empty($selected_event_id) || empty($historical_plans)) ? 'disabled' : '' ?> class="bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 text-white font-extrabold py-3 px-12 rounded-xl transition-all shadow-[0_0_20px_rgba(16,185,129,0.2)] hover:shadow-[0_0_25px_rgba(16,185,129,0.4)] text-lg mt-4 w-full disabled:opacity-50 disabled:cursor-not-allowed">
            Edit This Match
        </button>
    </form>

    <div class="mt-10 text-center">
        <a href="/dashboard" class="inline-flex items-center text-sm text-slate-400 hover:text-amber-400 transition-colors">
            ← Back to Dashboard
        </a>
    </div>
</div>

<?= $this->include('templates/footer') ?>