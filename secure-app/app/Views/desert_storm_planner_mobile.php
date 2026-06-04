<?= $this->include('templates/header', ['title' => $title]) ?>

<!-- Add React & Babel specific to this page -->
<script src="https://unpkg.com/react@18/umd/react.development.js" crossorigin></script>
<script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js" crossorigin></script>
<script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
<!-- Add html2canvas for image generation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<!-- The React Root -->
<div id="root" class="w-full z-10 relative"></div>

<script type="text/babel">
    const { useState, useEffect, useRef } = React;

    // We pass the dynamic PHP data directly into the React scope via globals
    const INITIAL_PLAYERS = <?= $players_json ?>;
    const AVAILABLE_ROSTER = <?= json_encode($available_roster) ?>;
    const EVENT_ID = <?= $event_id ?>;
    const IS_ADMIN = <?= $is_admin ? 'true' : 'false' ?>;
    const HISTORICAL_PLANS = <?= json_encode($historical_plans) ?>;
    const CURRENT_PLAN = <?= $current_plan ? json_encode($current_plan) : 'null' ?>;

    const BOXES = {
        unassigned: { id: 'unassigned', name: 'Available Roster', color: 'border-slate-700 bg-slate-900/50' },
        green: { id: 'green', name: 'Green Box (Center Spine)', color: 'border-emerald-500 bg-emerald-900/20 text-emerald-400', style: 'top-[10%] left-[35%] w-[30%] h-[76%] rounded-[50%/20%] justify-start pt-28 border-2 border-solid' },
        blue: { id: 'blue', name: 'Blue Box (North Sector)', color: 'border-cyan-500 bg-cyan-900/20 text-cyan-400', style: 'top-[4%] left-[30.5%] w-[39%] h-[28%] rounded-xl justify-start pt-6 border-2 border-solid' },
        yellow: { id: 'yellow', name: 'Yellow Box (South Sector)', color: 'border-amber-500 bg-amber-900/20 text-amber-400', style: 'top-[65%] left-[26%] w-[45%] h-[30%] rounded-xl justify-start pt-6 border-2 border-solid' },
        purple: { id: 'purple', name: 'Purple Box (West Flank)', color: 'border-purple-500 bg-purple-900/20 text-purple-400', style: 'top-[22%] left-[19.5%] w-[14%] h-[53%] rounded-[20%/50%] justify-center border-2 border-solid' },
        red: { id: 'red', name: 'Red Box (East Flank)', color: 'border-rose-500 bg-rose-900/20 text-rose-400', style: 'top-[22%] left-[66.5%] w-[14%] h-[53%] rounded-[20%/50%] justify-center border-2 border-solid' },
        subs: { id: 'subs', name: 'Subs', color: 'border-slate-400 bg-slate-900/20 text-slate-300', style: 'top-[2%] left-[2%] w-[12%] h-[96%] rounded-xl justify-start pt-4 border-2 border-solid' },
        flex: { id: 'flex', name: 'Flex Squad (Free Move)', color: 'border-cyan-500 bg-cyan-900/40 text-cyan-400' }
    };

    function DesertStormPlannerMobile() {
        const [players, setPlayers] = useState(INITIAL_PLAYERS);
        const [selectedPlayer, setSelectedPlayer] = useState(null);
        const [activePlayerList, setActivePlayerList] = useState([]);
        const [currentTab, setCurrentTab] = useState('board'); // 'board', 'active', 'roster', 'history'
        
        const [exportedText, setExportedText] = useState('');
        const [showExportModal, setShowExportModal] = useState(false);
        const [showImageModal, setShowImageModal] = useState(false);
        const [generatedImage, setGeneratedImage] = useState(null);
        const [isGenerating, setIsGenerating] = useState(false);
        const [isSaving, setIsSaving] = useState(false);
        
        const imageCaptureRef = useRef(null);
        
        // Time & Historical Selection State
        const [eventDatetime, setEventDatetime] = useState(() => {
            if (CURRENT_PLAN) {
                return CURRENT_PLAN.event_datetime.slice(0, 16);
            }
            const d = new Date();
            d.setDate(d.getDate() + (6 - d.getDay()));
            d.setHours(20, 0, 0, 0);
            return d.toISOString().slice(0, 16);
        });

        const assignToBox = (playerId, boxId) => {
            if (!IS_ADMIN) return;
            setPlayers(prev => prev.map(p => p.id === playerId ? { ...p, box: boxId } : p));
        };

        const savePlanToDatabase = async () => {
            setIsSaving(true);
            try {
                const response = await fetch('/ds_planner/save', {
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
                    alert('Assignments saved successfully!');
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
            let text = `⚔️ DESERT STORM LINEUP ⚔️\n\n`;
            Object.keys(BOXES).forEach(key => {
                if (key === 'unassigned') return;
                const boxPlayers = players.filter(p => p.box === key);
                if (boxPlayers.length === 0) return;
                text += `${BOXES[key].name.split(' ')[0].toUpperCase()}:\n`;
                const namesLine = boxPlayers.map(p => p.name).join(', ');
                text += `${namesLine}\n\n`;
            });
            setExportedText(text.trim());
            setShowExportModal(true);
        };

        const generateImage = () => {
            setIsGenerating(true);
            setTimeout(() => {
                html2canvas(imageCaptureRef.current, { useCORS: true, backgroundColor: '#0f172a' }).then(canvas => {
                    setGeneratedImage(canvas.toDataURL('image/png'));
                    setShowImageModal(true);
                    setIsGenerating(false);
                });
            }, 100); // Small delay to ensure DOM is ready
        };

        const addToActiveList = (player) => {
            const playerWithPower = INITIAL_PLAYERS.find(p => String(p.id) === String(player.id)) || player;
            setActivePlayerList(prev => [...prev, { ...playerWithPower, targetBox: 'auto' }]);
        };

        const removeFromActiveList = (playerId) => {
            setActivePlayerList(prev => prev.filter(p => String(p.id) !== String(playerId)));
        };

        const updateActivePlayerTargetBox = (playerId, targetBox) => {
            setActivePlayerList(prev => prev.map(p => String(p.id) === String(playerId) ? { ...p, targetBox } : p));
        };

        const autoDistribute = () => {
            let remainingPlayers = [...activePlayerList];
            let updatedBoardPlayers = [...players];

            const specificAssignments = remainingPlayers.filter(p => p.targetBox !== 'auto');
            specificAssignments.forEach(p => {
                const playerIndex = updatedBoardPlayers.findIndex(boardP => String(boardP.id) === String(p.id));
                if (playerIndex !== -1) {
                    updatedBoardPlayers[playerIndex].box = p.targetBox;
                } else {
                    updatedBoardPlayers.push({ ...p, box: p.targetBox });
                }
            });

            let autoPlayers = remainingPlayers.filter(p => p.targetBox === 'auto');
            autoPlayers.sort((a, b) => (b.power_raw || 0) - (a.power_raw || 0));

            const totalAutoPlayers = autoPlayers.length;
            const numBoxes = 5;
            const greenBoxCount = Math.ceil(totalAutoPlayers / numBoxes);
            const strongestForGreen = autoPlayers.splice(0, greenBoxCount);

            strongestForGreen.forEach(p => {
                const playerIndex = updatedBoardPlayers.findIndex(boardP => String(boardP.id) === String(p.id));
                if (playerIndex !== -1) {
                    updatedBoardPlayers[playerIndex].box = 'green';
                } else {
                    updatedBoardPlayers.push({ ...p, box: 'green' });
                }
            });

            const otherBoxes = ['blue', 'red', 'yellow', 'purple'];
            let boxIndex = 0;
            autoPlayers.forEach(p => {
                const playerIndex = updatedBoardPlayers.findIndex(boardP => String(boardP.id) === String(p.id));
                if (playerIndex !== -1) {
                    updatedBoardPlayers[playerIndex].box = otherBoxes[boxIndex];
                } else {
                    updatedBoardPlayers.push({ ...p, box: otherBoxes[boxIndex] });
                }
                boxIndex = (boxIndex + 1) % otherBoxes.length;
            });

            setPlayers(updatedBoardPlayers);
            setActivePlayerList([]);
            setCurrentTab('board'); // Switch to board view to see results
        };

        const availableRosterFiltered = AVAILABLE_ROSTER.filter(
            p => !activePlayerList.some(ap => String(ap.id) === String(p.id))
        );
        
        const allSubs = players.filter(p => p.box === 'subs');
        const leftSubs = allSubs.slice(0, 11); 
        const rightSubs = allSubs.slice(11);

        return (
            <div className="flex flex-col min-h-screen w-full bg-slate-950 text-slate-200">
                {/* Mobile Header / Controls */}
                <div className="sticky top-0 z-50 bg-slate-900 border-b border-slate-800 p-3 shadow-lg">
                    <div className="flex justify-between items-center mb-3">
                        <h1 className="font-bold text-amber-400">🏜️ DS Planner (Mobile)</h1>
                        {IS_ADMIN && (
                            <button onClick={savePlanToDatabase} disabled={isSaving} className="bg-emerald-600 px-3 py-1 rounded text-xs font-bold text-white">
                                {isSaving ? 'Saving...' : 'Save'}
                            </button>
                        )}
                    </div>
                    
                    {/* Navigation Tabs */}
                    <div className="flex bg-slate-800 rounded-lg p-1 gap-1">
                        <button onClick={() => setCurrentTab('board')} className={`flex-1 py-1.5 text-xs font-bold rounded ${currentTab === 'board' ? 'bg-slate-600 text-white' : 'text-slate-400'}`}>Map</button>
                        <button onClick={() => setCurrentTab('active')} className={`flex-1 py-1.5 text-xs font-bold rounded flex items-center justify-center gap-1 ${currentTab === 'active' ? 'bg-slate-600 text-white' : 'text-slate-400'}`}>
                            Active <span className="bg-amber-500 text-slate-900 rounded-full px-1.5 py-0.5 text-[9px]">{activePlayerList.length}</span>
                        </button>
                        <button onClick={() => setCurrentTab('roster')} className={`flex-1 py-1.5 text-xs font-bold rounded flex items-center justify-center gap-1 ${currentTab === 'roster' ? 'bg-slate-600 text-white' : 'text-slate-400'}`}>
                            Roster <span className="bg-slate-500 text-white rounded-full px-1.5 py-0.5 text-[9px]">{availableRosterFiltered.length}</span>
                        </button>
                        <button onClick={() => setCurrentTab('history')} className={`flex-1 py-1.5 text-xs font-bold rounded ${currentTab === 'history' ? 'bg-slate-600 text-white' : 'text-slate-400'}`}>Load</button>
                    </div>
                </div>

                {/* Main Content Area */}
                <div className="flex-1 overflow-y-auto p-3">
                    
                    {/* --- TAB: BOARD (List View instead of Drag & Drop map) --- */}
                    {currentTab === 'board' && (
                        <div className="flex flex-col gap-4">
                            <div className="flex justify-between">
                                <button onClick={generateImage} disabled={isGenerating} className="text-xs bg-cyan-600 px-3 py-1.5 rounded font-bold text-white shadow disabled:bg-slate-700">
                                    {isGenerating ? 'Generating...' : '🖼️ Generate Image'}
                                </button>
                                <button onClick={generateExportText} className="text-xs bg-indigo-600 px-3 py-1.5 rounded font-bold text-white shadow">📋 Export to Game</button>
                            </div>
                            
                            {['green', 'blue', 'yellow', 'purple', 'red', 'flex', 'subs'].map(boxKey => {
                                const box = BOXES[boxKey];
                                const boxPlayers = players.filter(p => p.box === boxKey);
                                
                                return (
                                    <div key={boxKey} className={`border border-solid rounded-lg p-3 ${box.color}`}>
                                        <div className="flex justify-between items-center mb-2 border-b border-current pb-1 opacity-80">
                                            <h3 className="font-bold text-sm uppercase tracking-wider">{box.name}</h3>
                                            <span className="text-xs font-bold">{boxPlayers.length}</span>
                                        </div>
                                        
                                        {boxPlayers.length === 0 ? (
                                            <p className="text-xs italic opacity-50 py-2 text-center">Empty</p>
                                        ) : (
                                            <div className="flex flex-col gap-1.5">
                                                {boxPlayers.map(p => (
                                                    <div key={p.id} className="flex justify-between items-center bg-slate-950/50 p-2 rounded">
                                                        <div className="flex flex-col">
                                                            <span className="text-sm font-bold text-slate-200">{p.name}</span>
                                                            <span className="text-[10px] font-mono">{p.power}</span>
                                                        </div>
                                                        {IS_ADMIN && (
                                                            <select 
                                                                value={boxKey}
                                                                onChange={(e) => assignToBox(p.id, e.target.value)}
                                                                className="bg-slate-900 border border-slate-700 text-xs rounded p-1 max-w-[100px]"
                                                            >
                                                                {Object.keys(BOXES).map(k => (
                                                                    <option key={k} value={k}>{BOXES[k].name.split(' ')[0]}</option>
                                                                ))}
                                                            </select>
                                                        )}
                                                    </div>
                                                ))}
                                            </div>
                                        )}
                                    </div>
                                );
                            })}
                        </div>
                    )}

                    {/* --- TAB: ACTIVE LIST --- */}
                    {currentTab === 'active' && (
                        <div className="flex flex-col gap-3">
                            <div className="flex justify-between items-center bg-slate-900 p-3 rounded-lg border border-slate-800">
                                <div>
                                    <h2 className="text-sm font-bold text-amber-400">Ready to Distribute</h2>
                                    <p className="text-[10px] text-slate-400">These players will be placed on the board.</p>
                                </div>
                                <button onClick={autoDistribute} disabled={activePlayerList.length === 0} className="bg-emerald-600 disabled:bg-slate-700 disabled:text-slate-500 text-white px-3 py-2 rounded-lg font-bold text-xs shadow">
                                    Distribute Now
                                </button>
                            </div>
                            
                            {activePlayerList.length === 0 ? (
                                <div className="text-center p-8 bg-slate-900 rounded-lg border border-slate-800">
                                    <p className="text-slate-400 text-sm mb-2">No players selected.</p>
                                    <button onClick={() => setCurrentTab('roster')} className="text-amber-500 underline text-xs">Go to Roster to add players</button>
                                </div>
                            ) : (
                                <div className="flex flex-col gap-2">
                                    {activePlayerList.map(p => (
                                        <div key={p.id} className="bg-slate-900 border border-slate-800 p-3 rounded-lg flex justify-between items-center">
                                            <div className="flex flex-col">
                                                <span className="font-bold text-sm text-slate-200">{p.name || p.player_name || p.username}</span>
                                                <span className="text-xs text-amber-400 font-mono">{p.power}</span>
                                            </div>
                                            <div className="flex items-center gap-3">
                                                <div className="flex flex-col">
                                                    <span className="text-[9px] text-slate-500 uppercase">Target</span>
                                                    <select 
                                                        value={p.targetBox} 
                                                        onChange={(e) => updateActivePlayerTargetBox(p.id, e.target.value)}
                                                        className="bg-slate-950 border border-slate-700 rounded px-2 py-1 text-xs text-amber-300"
                                                    >
                                                        <option value="auto">Auto</option>
                                                        <option value="subs">Subs</option>
                                                    </select>
                                                </div>
                                                <button onClick={() => removeFromActiveList(p.id)} className="bg-rose-900/30 text-rose-400 px-2 py-1 rounded text-xs border border-rose-900">Remove</button>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    )}

                    {/* --- TAB: ROSTER --- */}
                    {currentTab === 'roster' && (
                        <div className="flex flex-col gap-2">
                            <div className="bg-slate-900 p-3 rounded-lg border border-slate-800 mb-2">
                                <p className="text-xs text-slate-400">Tap a player to add them to your Active List for distribution.</p>
                            </div>
                            {availableRosterFiltered.length === 0 ? (
                                <p className="text-center text-sm text-slate-500 p-4">All available players are in the Active List.</p>
                            ) : (
                                availableRosterFiltered.map(p => (
                                    <div 
                                        key={p.id}
                                        onClick={() => addToActiveList(p)}
                                        className="bg-slate-900 border border-slate-800 p-3 rounded-lg flex justify-between items-center active:bg-slate-800"
                                    >
                                        <div className="flex flex-col">
                                            <span className="font-bold text-sm text-slate-200">{p.player_name || p.username}</span>
                                            <span className="text-xs text-amber-500 font-mono">{parseFloat(p.total_power).toFixed(2)}</span>
                                        </div>
                                        <span className="bg-slate-800 text-emerald-400 w-8 h-8 rounded-full flex items-center justify-center font-bold text-lg leading-none pb-0.5">+</span>
                                    </div>
                                ))
                            )}
                        </div>
                    )}

                    {/* --- TAB: HISTORY --- */}
                    {currentTab === 'history' && (
                        <div className="flex flex-col gap-4">
                            {IS_ADMIN && (
                                <div className="bg-slate-900 border border-slate-800 p-4 rounded-xl flex flex-col gap-2">
                                    <h3 className="text-sm font-bold text-amber-400">Event Time</h3>
                                    <input 
                                        type="datetime-local" 
                                        value={eventDatetime} 
                                        onChange={e => setEventDatetime(e.target.value)}
                                        className="bg-slate-950 border border-slate-700 rounded p-2 text-sm text-slate-200 w-full"
                                    />
                                    <p className="text-[10px] text-slate-500 leading-tight">When saving, the assignments will be logged against this specific match time.</p>
                                </div>
                            )}

                            <div className="bg-slate-900 border border-slate-800 p-4 rounded-xl flex flex-col gap-3">
                                <h3 className="text-sm font-bold text-white flex items-center gap-2"><span>🕒</span> Load Past Plan</h3>
                                <form action="/ds_planner_mobile" method="GET" className="flex flex-col gap-3">
                                    <select name="plan_id" defaultValue="" className="w-full bg-slate-950 border border-slate-700 rounded-lg p-3 text-sm text-slate-200">
                                        <option value="" disabled>Select a past event...</option>
                                        {HISTORICAL_PLANS.length === 0 && <option value="" disabled>No past plans found.</option>}
                                        {HISTORICAL_PLANS.map(plan => {
                                            const d = new Date(plan.event_datetime);
                                            const formatted = d.toLocaleDateString() + ' ' + d.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                                            return <option key={plan.id} value={plan.id}>{formatted}</option>;
                                        })}
                                    </select>
                                    <button type="submit" className="w-full bg-slate-800 text-white py-3 rounded-lg font-bold border border-slate-700">Load Selected Plan</button>
                                </form>
                                
                                <div className="border-t border-slate-800 pt-3 mt-1">
                                    <a href="/ds_planner_mobile" className="block w-full text-center bg-rose-900/20 text-rose-400 py-3 rounded-lg font-bold border border-rose-900/50">Clear Board (Start Fresh)</a>
                                </div>
                            </div>
                        </div>
                    )}
                </div>

                {/* Hidden container for image generation */}
                <div style={{ position: 'absolute', left: '-9999px', top: 0, width: '1280px', height: '720px' }} >
                    <div ref={imageCaptureRef} className="relative w-[1280px] h-[720px] bg-slate-950">
                        <img src="/images/desert_storm.png" alt="Map Layout" className="absolute inset-0 w-full h-full object-cover" />
                        {Object.keys(BOXES).map(key => {
                            const box = BOXES[key];
                            if (key === 'unassigned' || key === 'flex') return null;
                            const boxPlayers = players.filter(p => p.box === key);
                            const isHorizontal = ['blue', 'yellow', 'green'].includes(key);
                            return (
                                <div key={key} className={`absolute flex items-center p-1.5 ${box.style} ${box.color}`}>
                                    <div className={`w-full flex ${isHorizontal ? 'flex-row flex-wrap justify-center content-start gap-1.5 px-2' : 'flex-col gap-1 items-center'}`}>
                                        {boxPlayers.map(p => (
                                            <div key={p.id} className="px-2 py-0.5 rounded text-[10px] font-medium flex items-center justify-center gap-1.5 shadow-sm border bg-slate-950/90 border-slate-800 text-slate-200 shrink-0 min-w-[75px] max-w-[105px]">
                                                <span className="truncate max-w-[65px]">{p.name}</span>
                                                <span className="text-[8px] text-amber-400 font-mono shrink-0">{p.power}</span>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </div>

                {/* Image Modal Overlay */}
                {showImageModal && (
                    <div className="fixed inset-0 bg-slate-950/90 z-[100] flex flex-col p-4">
                        <div className="flex justify-between items-center mb-4 pt-4">
                            <h3 className="text-lg font-bold text-white">🖼️ Generated Map</h3>
                            <button onClick={() => setShowImageModal(false)} className="text-slate-400 text-xl font-bold">✕</button>
                        </div>
                        <div className="flex-1 flex items-center justify-center">
                            {generatedImage && <img src={generatedImage} className="max-w-full max-h-full rounded-lg shadow-2xl" />}
                        </div>
                        <p className="text-center text-xs text-slate-400 py-4">Long-press or right-click the image to save or share.</p>
                    </div>
                )}

                {/* Export Modal Overlay */}
                {showExportModal && (
                    <div className="fixed inset-0 bg-slate-950/80 z-[100] flex flex-col p-4">
                        <div className="flex justify-between items-center mb-4 pt-4">
                            <h3 className="text-lg font-bold text-white">📋 Strategy Export</h3>
                            <button onClick={() => setShowExportModal(false)} className="text-slate-400 text-xl font-bold">✕</button>
                        </div>
                        <textarea readOnly value={exportedText} className="flex-1 w-full bg-slate-900 border border-slate-800 rounded-lg p-4 text-xs font-mono text-slate-300 mb-4" onClick={(e) => e.target.select()} />
                        <button onClick={() => { navigator.clipboard.writeText(exportedText); alert('Copied to clipboard!'); setShowExportModal(false); }} className="w-full bg-indigo-600 text-white py-4 rounded-lg font-bold text-lg mb-8 shadow-lg">Copy to Clipboard</button>
                    </div>
                )}

                <div className="fixed bottom-4 right-4">
                    <a href="/ds_planner?desktop=true" className="text-xs bg-slate-700 text-slate-300 px-3 py-2 rounded-full shadow-lg">
                        View Full Version
                    </a>
                </div>
            </div>
        );
    }

    const domContainer = document.getElementById('root');
    const root = ReactDOM.createRoot(domContainer);
    root.render(React.createElement(DesertStormPlannerMobile));
</script>

<?= $this->include('templates/footer') ?>