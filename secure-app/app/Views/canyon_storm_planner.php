<?= $this->include('templates/header', ['title' => $title]) ?>

<!-- Add React & Babel specific to this page -->
<script src="https://unpkg.com/react@18/umd/react.development.js" crossorigin></script>
<script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js" crossorigin></script>
<script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>

<!-- The React Root -->
<div id="root" class="w-full z-10 relative mt-8"></div>

<script type="text/babel">
    const { useState, useEffect } = React;

    const INITIAL_PLAYERS = <?= $players_json ?>;
    const EVENT_ID = <?= $event_id ?>;
    const IS_ADMIN = <?= $is_admin ? 'true' : 'false' ?>;
    const HISTORICAL_PLANS = <?= json_encode($historical_plans) ?>;
    const CURRENT_PLAN = <?= $current_plan ? json_encode($current_plan) : 'null' ?>;

    const BOXES = {
        unassigned: { id: 'unassigned', name: 'Available Roster', color: 'border-slate-700 bg-transparent' },
        t1: { 
            id: 't1', 
            name: 'T1', 
            color: 'border-sky-500 bg-transparent text-sky-400',
            style: 'top-[11%] left-[4%] w-[19%] h-[39%] rounded-lg justify-start pt-6 border-[3px] border-solid'
        },
        t2: { 
            id: 't2', 
            name: 'T2', 
            color: 'border-emerald-500 bg-transparent text-emerald-400',
            style: 'top-[11%] right-[3%] w-[19.5%] h-[39%] rounded-lg justify-start pt-6 border-[3px] border-solid'
        },
        t3: { 
            id: 't3', 
            name: 'T3', 
            color: 'border-rose-500 bg-transparent text-rose-400',
            style: 'bottom-[11%] right-[3%] w-[19.5%] h-[39%] rounded-lg justify-start pt-6 border-[3px] border-solid'
        },
        t4: { 
            id: 't4', 
            name: 'T4', 
            color: 'border-fuchsia-500 bg-transparent text-fuchsia-400',
            style: 'bottom-[11%] left-[27%] w-[46%] h-[12%] rounded-lg flex-row justify-start pt-2 px-2 gap-1 border-[3px] border-solid'
        },
        t5: { 
            id: 't5', 
            name: 'T5', 
            color: 'border-yellow-400 bg-transparent text-yellow-300',
            style: 'bottom-[11%] left-[4%] w-[19%] h-[39%] rounded-lg justify-start pt-6 border-[3px] border-solid'
        },
        t6: { 
            id: 't6', 
            name: 'T6', 
            color: 'border-orange-500 bg-transparent text-orange-400', 
            style: 'top-[23%] left-[23%] w-[54%] h-[20%] rounded-lg flex-row justify-start pt-2 px-2 gap-1 border-[3px] border-solid'
        },
        subs: {
            id: 'subs',
            name: 'Subs',
            color: 'border-slate-400 bg-transparent text-slate-300',
            style: 'top-[2%] left-[2%] w-[12%] h-[96%] rounded-xl justify-start pt-4 border-2 border-solid hidden'
        },
        flex: { id: 'flex', name: 'Flex Squad (Free Move)', color: 'border-indigo-500 bg-transparent text-indigo-400 text-xs' }
    };

    function CanyonStormPlanner() {
        const [players, setPlayers] = useState(INITIAL_PLAYERS);
        const [selectedPlayer, setSelectedPlayer] = useState(null);
        
        const [newPlayerName, setNewPlayerName] = useState('');
        const [newPlayerPower, setNewPlayerPower] = useState('');
        
        const [exportedText, setExportedText] = useState('');
        const [showExportModal, setShowExportModal] = useState(false);
        const [isSaving, setIsSaving] = useState(false);
        
        const [eventDatetime, setEventDatetime] = useState(() => {
            if (CURRENT_PLAN) {
                return CURRENT_PLAN.event_datetime.slice(0, 16); 
            }
            const d = new Date();
            d.setDate(d.getDate() + (4 - d.getDay())); 
            d.setHours(20, 0, 0, 0);
            return d.toISOString().slice(0, 16);
        });

        const [planIdInput, setPlanIdInput] = useState('');

        const handlePlayerClick = (player) => {
            if (!IS_ADMIN) return; 
            setSelectedPlayer(selectedPlayer?.id === player.id ? null : player);
        };

        const assignToBox = (boxId) => {
            if (!IS_ADMIN || !selectedPlayer) return;
            setPlayers(prev => prev.map(p => p.id === selectedPlayer.id ? { ...p, box: boxId } : p));
            setSelectedPlayer(null);
        };

        const addPlayer = (e) => {
            e.preventDefault();
            if (!newPlayerName.trim()) return;
            const newPlayer = {
                id: 'manual_' + Date.now().toString(),
                name: newPlayerName.trim(),
                power: newPlayerPower ? `${newPlayerPower}M` : 'N/A',
                box: 'unassigned'
            };
            setPlayers([...players, newPlayer]);
            setNewPlayerName('');
            setNewPlayerPower('');
        };

        const removePlayer = (id, e) => {
            e.stopPropagation();
            setPlayers(players.filter(p => p.id !== id));
            if (selectedPlayer?.id === id) setSelectedPlayer(null);
        };

        const savePlanToDatabase = async () => {
            setIsSaving(true);
            
            try {
                const response = await fetch('/cs_planner/save', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new URLSearchParams({
                        'event_id': EVENT_ID,
                        'event_datetime': eventDatetime,
                        'full_plan': JSON.stringify(players),
                    })
                });
                
                const result = await response.json();
                if (result.status === 'success') {
                    alert('Assignments saved successfully and synced with Weekly Logs!');
                    window.location.reload();
                } else {
                    alert('Error saving plan: ' + result.message);
                }
            } catch (error) {
                alert('Connection error occurred while saving.');
            }
            
            setIsSaving(false);
        };

        const generateExportText = () => {
            let text = `⛰️ CANYON STORM LINEUP ⛰️\n\n`;
            
            Object.keys(BOXES).forEach(key => {
                if (key === 'unassigned' || key === 'subs') return;
                const boxPlayers = players.filter(p => p.box === key);
                
                if (boxPlayers.length === 0) return;
                
                text += `${BOXES[key].name.toUpperCase()}:\n`;
                const namesLine = boxPlayers.map(p => p.name).join(', ');
                text += `${namesLine}\n\n`;
            });
            
            setExportedText(text.trim());
            setShowExportModal(true);
        };

        return (
            <div className="flex flex-col lg:flex-row h-screen w-screen overflow-hidden p-4 gap-4 z-10 relative">
                <div className="flex-1 bg-slate-900/90 border border-slate-800 rounded-xl flex flex-col overflow-hidden relative backdrop-blur-sm">
                    <div className="p-4 bg-slate-900/80 border-b border-slate-800 flex justify-between items-center z-10">
                        <div>
                            <h1 className="text-xl font-bold tracking-tight text-white flex items-center gap-2">
                                ⛰️ Canyon Storm Tactical Map Room
                                <a href="/dashboard" className="ml-4 text-xs font-normal text-amber-500 hover:text-amber-400 border border-amber-500/50 hover:border-amber-400 rounded px-2 py-1 transition-colors">
                                    ← Dashboard
                                </a>
                            </h1>
                            <div className="mt-2 flex items-center gap-3">
                                {IS_ADMIN ? (
                                    <React.Fragment>
                                        <label className="text-xs text-slate-400 font-bold">Match Time:</label>
                                        <input 
                                            type="datetime-local" 
                                            value={eventDatetime} 
                                            onChange={e => setEventDatetime(e.target.value)}
                                            className="bg-slate-950 border border-slate-700 rounded px-2 py-1 text-xs text-amber-400 focus:outline-none focus:border-amber-500"
                                        />
                                        {CURRENT_PLAN && <span className="text-xs text-emerald-400 font-bold italic ml-2">Viewing Historical Plan</span>}
                                    </React.Fragment>
                                ) : (
                                    <p className="text-xs text-amber-400">View-Only Mode</p>
                                )}
                            </div>
                        </div>
                        <div className="flex gap-2">
                            {IS_ADMIN && (
                                <button type="button" onClick={savePlanToDatabase} disabled={isSaving} className="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-lg font-medium text-sm transition shadow-lg cursor-pointer flex items-center gap-2">
                                    <span>💾</span> {isSaving ? 'Saving...' : (CURRENT_PLAN ? 'Update Plan' : 'Save Plan')}
                                </button>
                            )}
                            <button type="button" onClick={generateExportText} className="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-lg font-medium text-sm transition shadow-lg cursor-pointer">
                                📋 Export Text
                            </button>
                        </div>
                    </div>

                    <div className="flex-1 relative bg-slate-950/50 flex items-center justify-center p-4 overflow-hidden">
                        <div className="relative w-full max-w-[700px] aspect-[3/4] overflow-hidden">
                            
                            <img 
                                src="/images/canyon-storm.jpg"
                                alt="Map Layout" 
                                className="absolute inset-0 w-full h-full object-cover select-none z-0"
                            />

                            {/* Main Battlefield Sectors */}
                            {Object.keys(BOXES).map(key => {
                                const box = BOXES[key];
                                if (key === 'unassigned' || key === 'flex' || key === 'subs') return null;
                                const boxPlayers = players.filter(p => p.box === key);
                                
                                const isHorizontalSector = ['t4', 't6'].includes(key);
                                const layoutDirectionClasses = isHorizontalSector 
                                    ? "flex-row flex-wrap justify-start content-start" 
                                    : "flex-col items-center px-1";

                                return (
                                    <div 
                                        key={key} 
                                        onClick={() => assignToBox(key)}
                                        className={`absolute ${box.color} ${box.style} p-1.5 transition-all flex overflow-y-auto cursor-pointer z-20`}
                                    >
                                        <span className="absolute top-0 right-1 text-[8px] font-black uppercase bg-slate-950/80 px-1 py-0.5 rounded-b border-x border-b border-slate-800 shadow shadow-black z-30">
                                            {box.name}
                                        </span>
                                        <div className={`w-full h-full pt-3 flex ${layoutDirectionClasses} gap-1`}>
                                            {boxPlayers.map(p => (
                                                <div 
                                                    key={p.id} 
                                                    onClick={(e) => { e.stopPropagation(); handlePlayerClick(p); }}
                                                    className={`px-1.5 py-0.5 rounded text-[9px] font-medium flex items-center justify-between shadow-sm border ${selectedPlayer?.id === p.id ? 'bg-amber-500 border-amber-400 text-slate-950 scale-105 font-bold' : 'bg-slate-950/90 border-slate-800 text-slate-200'} z-30 shrink-0 ${isHorizontalSector ? 'w-auto max-w-[100px]' : 'w-full'}`}
                                                >
                                                    <span className="truncate" title={p.name}>{p.name}</span>
                                                    <span className="text-[8px] text-amber-400 font-mono shrink-0 ml-1">{p.power}</span>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </div>

                    <div onClick={() => assignToBox('flex')} className="p-4 bg-slate-950/90 border-t border-slate-900 flex flex-col gap-2 min-h-[90px] cursor-pointer transition">
                        <h3 className="text-xs font-bold uppercase tracking-wider text-indigo-400">⚡ Flex Squad (Free Move)</h3>
                        <div className="flex flex-wrap gap-2">
                            {players.filter(p => p.box === 'flex').map(p => (
                                <div 
                                    key={p.id}
                                    onClick={(e) => { e.stopPropagation(); handlePlayerClick(p); }}
                                    className={`px-3 py-1 rounded-lg text-xs font-semibold border flex items-center gap-2 cursor-pointer ${selectedPlayer?.id === p.id ? 'bg-amber-500 border-amber-400 text-slate-950' : 'bg-slate-900 border-slate-800 text-indigo-300 hover:bg-slate-800'}`}
                                >
                                    <span>{p.name}</span>
                                    <span className="text-[10px] text-indigo-400 font-mono">{p.power}</span>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>

                <div className="w-full lg:w-80 flex flex-col gap-4 h-full overflow-hidden">
                    
                    {/* Historic Plans Dropdown */}
                    <div className="bg-slate-900/90 border border-slate-800 p-4 rounded-xl flex flex-col gap-2 shrink-0 backdrop-blur-sm">
                        <h3 className="text-sm font-bold text-white flex items-center gap-2"><span>🕒</span> Load Past Plan</h3>
                        <form action="/cs_planner" method="GET" className="flex flex-col gap-2">
                            <select name="plan_id" value={planIdInput} onChange={e => setPlanIdInput(e.target.value)} className="w-full bg-slate-950 border border-slate-700 rounded-lg px-2 py-2 text-xs text-slate-200 focus:outline-none focus:border-amber-500 transition-colors appearance-none">
                                <option value="" disabled>Select a past event...</option>
                                {HISTORICAL_PLANS.length === 0 && <option value="" disabled>No past plans found.</option>}
                                {HISTORICAL_PLANS.map(plan => {
                                    const d = new Date(plan.event_datetime);
                                    const formatted = d.toLocaleDateString() + ' ' + d.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                                    return <option key={plan.id} value={plan.id}>{formatted}</option>;
                                })}
                            </select>
                            <div className="flex gap-2">
                                <button type="submit" className="flex-1 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs py-2 rounded-lg font-bold border border-slate-700 transition-colors shadow-sm">Load</button>
                                <a href="/cs_planner" className="flex-1 bg-slate-950 hover:bg-slate-800 text-slate-400 text-center text-xs py-2 rounded-lg font-bold border border-slate-800 transition-colors flex items-center justify-center">New Plan</a>
                            </div>
                        </form>
                    </div>

                    {/* Add User via UI (Optional Quick Form for temp users) */}
                    <form onSubmit={addPlayer} className="bg-slate-900/90 border border-slate-800 p-4 rounded-xl flex flex-col gap-3 shrink-0 backdrop-blur-sm">
                        <h3 className="text-sm font-bold text-white">Temporary Add</h3>
                        <div className="flex gap-2">
                            <input type="text" placeholder="Name" value={newPlayerName} onChange={e => setNewPlayerName(e.target.value)} className="flex-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-1.5 text-xs text-slate-200" />
                            <input type="number" placeholder="Power" value={newPlayerPower} onChange={e => setNewPlayerPower(e.target.value)} className="w-20 bg-slate-950 border border-slate-800 rounded-lg px-2 py-1.5 text-xs text-slate-200" />
                            <button type="submit" className="bg-slate-800 hover:bg-slate-700 text-slate-200 px-3 text-xs py-1.5 rounded-lg font-bold border border-slate-700 cursor-pointer">+</button>
                        </div>
                    </form>

                    <div className="flex-1 bg-slate-900/90 border border-slate-800 rounded-xl flex flex-col overflow-hidden backdrop-blur-sm">
                        <div className="p-4 border-b border-slate-800 bg-slate-900/50 flex justify-between items-center">
                            <h2 className="text-sm font-bold text-amber-400">Available Active Roster</h2>
                        </div>
                        <div onClick={() => assignToBox('unassigned')} className="flex-1 p-3 overflow-y-auto flex flex-col gap-2 min-h-[200px]">
                            {players.filter(p => p.box === 'unassigned').map(p => (
                                <div 
                                    key={p.id}
                                    onClick={(e) => { e.stopPropagation(); handlePlayerClick(p); }}
                                    className={`p-2.5 rounded-lg border text-xs flex justify-between items-center cursor-pointer ${selectedPlayer?.id === p.id ? 'bg-amber-500 border-amber-400 text-slate-950 font-bold' : 'bg-slate-950 border-slate-800 text-slate-300'}`}
                                >
                                    <span>{p.name} <span className="text-[10px] text-amber-500 font-mono ml-1">({p.power})</span></span>
                                    {p.id.startsWith('manual_') && (
                                        <button type="button" onClick={(e) => removePlayer(p.id, e)} className="text-slate-500 hover:text-rose-400 p-1">✕</button>
                                    )}
                                </div>
                            ))}
                        </div>
                    </div>
                </div>

                {showExportModal && (
                    <div className="fixed inset-0 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 z-50">
                        <div className="bg-slate-900 border border-slate-800 rounded-xl max-w-lg w-full p-6 shadow-2xl flex flex-col gap-4">
                            <h3 className="text-base font-bold text-white">📋 Strategy Export</h3>
                            <textarea readOnly value={exportedText} className="w-full h-64 bg-slate-950 border border-slate-800 rounded-lg p-3 text-xs font-mono text-slate-300" onClick={(e) => e.target.select()} />
                            <div className="flex justify-end gap-2">
                                <button type="button" onClick={() => { navigator.clipboard.writeText(exportedText); alert('Copied!'); }} className="bg-indigo-600 text-white px-4 py-2 rounded-lg text-xs font-bold">Copy</button>
                                <button type="button" onClick={(e) => setShowExportModal(false)} className="bg-slate-800 text-slate-400 px-4 py-2 rounded-lg text-xs font-bold">Dismiss</button>
                            </div>
                        </div>
                    </div>
                )}
            </div>
        );
    }

    const container = document.getElementById('root');
    const root = ReactDOM.createRoot(container);
    root.render(<CanyonStormPlanner />);
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #475569; }
</style>

<?= $this->include('templates/footer') ?>