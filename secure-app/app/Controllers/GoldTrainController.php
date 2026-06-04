<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\GoldTrainLogModel;

class GoldTrainController extends BaseController
{
    public function index()
    {
        $userModel = new UserModel();
        
        $role = session()->get('role');
        $data = [
            'title' => 'Polar Express (Gold Train)'
        ];

        // 1. Fetch all approved users
        $allUsers = $userModel->where('status', 'approved')->orderBy('alliance_level', 'DESC')->orderBy('username', 'ASC')->findAll();
        
        // Build a lookup map of user_id => player name for translating MVP and Guardian IDs
        $nameMap = [];
        foreach ($allUsers as $u) {
            $nameMap[$u['id']] = !empty($u['player_name']) ? $u['player_name'] : $u['username'];
        }

        // 2. Map their latest train log (if they have one)
        $rosterData = [];
        $rankValues = ['R5' => 5, 'R4' => 4, 'R3' => 3, 'R2' => 2, 'R1' => 1];

        // Wrap in try/catch just in case the migration hasn't run yet
        try {
            $trainModel = new GoldTrainLogModel();
            
            // Fetch the most recent active cycle ID, if any.
            $latestCycle = $trainModel->orderBy('created_at', 'DESC')->first();
            $activeCycleId = $latestCycle ? $latestCycle['cycle_id'] : null;
            
            foreach ($allUsers as $user) {
                // Get their most recent log entry for the CURRENT ACTIVE CYCLE ONLY
                if ($activeCycleId) {
                    $log = $trainModel->where('user_id', $user['id'])
                                      ->where('cycle_id', $activeCycleId)
                                      ->orderBy('created_at', 'DESC')
                                      ->first();
                } else {
                    $log = null;
                }
                
                $rosterData[] = [
                    'user_id' => $user['id'],
                    'player_name' => !empty($user['player_name']) ? $user['player_name'] : $user['username'],
                    'alliance_level' => $user['alliance_level'],
                    'rank_val' => $rankValues[$user['alliance_level']] ?? 0,
                    'status' => $log ? $log['status'] : 'pending',
                    'schedule_date' => $log && $log['assigned_time'] ? date('Y-m-d H:i:s', strtotime($log['assigned_time'])) : null,
                    'turned_gold' => $log ? ($log['status'] === 'completed' ? 1 : 0) : 0, 
                    'reached_minimum' => $log ? ($log['status'] === 'completed' ? 1 : 0) : 0, 
                    'mvp_user_id' => $log['mvp_user_id'] ?? null,
                    'guardian_user_id' => $log['guardian_user_id'] ?? null,
                    'mvp_name' => ($log && !empty($log['mvp_user_id'])) ? ($nameMap[$log['mvp_user_id']] ?? 'Unknown') : null,
                    'guardian_name' => ($log && !empty($log['guardian_user_id'])) ? ($nameMap[$log['guardian_user_id']] ?? 'Unknown') : null,
                ];
            }
        } catch (\Exception $e) {
            // If the table doesn't exist, just build the base roster without logs
            foreach ($allUsers as $user) {
                $rosterData[] = [
                    'user_id' => $user['id'],
                    'player_name' => !empty($user['player_name']) ? $user['player_name'] : $user['username'],
                    'alliance_level' => $user['alliance_level'],
                    'rank_val' => $rankValues[$user['alliance_level']] ?? 0,
                    'status' => 'pending',
                    'schedule_date' => null,
                    'turned_gold' => 0, 
                    'reached_minimum' => 0, 
                    'mvp_user_id' => null,
                    'guardian_user_id' => null,
                    'mvp_name' => null,
                    'guardian_name' => null,
                ];
            }
        }

        // Sort by rank value (highest first), then by name
        usort($rosterData, function($a, $b) {
            if ($a['rank_val'] == $b['rank_val']) {
                return strcmp(strtolower($a['player_name']), strtolower($b['player_name']));
            }
            return $b['rank_val'] - $a['rank_val'];
        });

        $data['roster'] = $rosterData;
        
        return view('gold_train/index', $data);
    }

    public function generateCycle()
    {
        // Only Admins/R4+ can run this
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return redirect()->back()->with('error', 'Unauthorized to generate cycles.');
        }

        $startDateStr = $this->request->getPost('start_date');
        $startUserId = $this->request->getPost('start_user_id');

        if (!$startDateStr || !$startUserId) {
            return redirect()->back()->with('error', 'Please provide a start datetime and a user.');
        }

        $userModel = new UserModel();

        // Re-fetch sorted roster (R5 -> R1, then alphabetical) to know the official order
        $allUsers = $userModel->where('status', 'approved')->findAll();
        $rankValues = ['R5' => 5, 'R4' => 4, 'R3' => 3, 'R2' => 2, 'R1' => 1];
        
        usort($allUsers, function($a, $b) use ($rankValues) {
            $rA = $rankValues[$a['alliance_level']] ?? 0;
            $rB = $rankValues[$b['alliance_level']] ?? 0;
            if ($rA == $rB) {
                $nameA = !empty($a['player_name']) ? $a['player_name'] : $a['username'];
                $nameB = !empty($b['player_name']) ? $b['player_name'] : $b['username'];
                return strcmp(strtolower($nameA), strtolower($nameB));
            }
            return $rB - $rA;
        });

        // Find the index of the selected starting user
        $startIndex = -1;
        foreach ($allUsers as $index => $u) {
            if ($u['id'] == $startUserId) {
                $startIndex = $index;
                break;
            }
        }

        if ($startIndex === -1) {
            return redirect()->back()->with('error', 'Selected user not found in active roster.');
        }

        $currentDate = new \DateTime($startDateStr);
        $cycleId = 'Cycle-' . date('Ymd-Hi'); // Create a unique batch identifier

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $trainModel = new GoldTrainLogModel();
            
            // Mark older pending/scheduled logs in the PREVIOUS cycle as "completed"
            // if they were part of a full run (since the cycle is effectively over)
            $latestCycleRow = $trainModel->orderBy('created_at', 'DESC')->first();
            if ($latestCycleRow) {
                $oldCycleId = $latestCycleRow['cycle_id'];
                
                // Fetch anyone in the old cycle who was scheduled or pending and update them to completed.
                // Assuming they successfully drove their train before the cycle ended.
                $unfinishedLogs = $trainModel->where('cycle_id', $oldCycleId)
                                             ->whereIn('status', ['pending', 'scheduled'])
                                             ->where('assigned_time IS NOT NULL')
                                             ->findAll();
                                             
                foreach ($unfinishedLogs as $log) {
                    $trainModel->update($log['id'], [
                        'status' => 'completed'
                    ]);
                }
            }
            
            // Generate schedule for everyone starting from the selected index to the bottom
            for ($i = $startIndex; $i < count($allUsers); $i++) {
                $trainModel->insert([
                    'user_id' => $allUsers[$i]['id'],
                    'cycle_id' => $cycleId,
                    'status' => 'scheduled',
                    'assigned_time' => $currentDate->format('Y-m-d H:i:s')
                ]);
                
                // Increment by exactly 1 day for the next person (time remains the same)
                $currentDate->modify('+1 day');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Database error: Please run migrations first to create the gold train table.');
        }

        $db->transComplete();

        if ($db->transStatus() === FALSE) {
            return redirect()->back()->with('error', 'Database error generating cycle.');
        }

        return redirect()->back()->with('message', 'Successfully generated schedule cycle starting from ' . $allUsers[$startIndex]['username'] . '. Previous cycle logs were automatically marked as completed.');
    }

    public function shiftDown()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return redirect()->back()->with('error', 'Unauthorized to shift schedule.');
        }

        try {
            $trainModel = new GoldTrainLogModel();
            
            // Get current active cycle
            $latestCycle = $trainModel->orderBy('created_at', 'DESC')->first();
            $activeCycleId = $latestCycle ? $latestCycle['cycle_id'] : null;

            if (!$activeCycleId) {
                return redirect()->back()->with('error', 'No active cycle found to shift.');
            }

            // Get all logs in the current cycle that are scheduled for the future or pending
            // We want to shift everything forward by 1 day
            $logs = $trainModel->where('cycle_id', $activeCycleId)
                               ->where('assigned_time IS NOT NULL')
                               ->orderBy('assigned_time', 'ASC')
                               ->findAll();

            if (empty($logs)) {
                return redirect()->back()->with('error', 'No scheduled dates found to shift in the active cycle.');
            }

            $db = \Config\Database::connect();
            $db->transStart();

            foreach ($logs as $log) {
                $currentDate = new \DateTime($log['assigned_time']);
                $currentDate->modify('+1 day');
                $trainModel->update($log['id'], ['assigned_time' => $currentDate->format('Y-m-d H:i:s')]);
            }

            $db->transComplete();

            if ($db->transStatus() === FALSE) {
                return redirect()->back()->with('error', 'Database error shifting schedule.');
            }

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Database error: ' . $e->getMessage());
        }

        return redirect()->back()->with('message', 'The active schedule has been successfully shifted forward by one day to accommodate a new player!');
    }

    public function swapSchedule()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return redirect()->back()->with('error', 'Unauthorized to swap schedule.');
        }

        $user1Id = $this->request->getPost('user_id_1');
        $user2Id = $this->request->getPost('user_id_2');

        if (!$user1Id || !$user2Id || $user1Id === $user2Id) {
            return redirect()->back()->with('error', 'Invalid users selected for swap.');
        }

        try {
            $trainModel = new GoldTrainLogModel();
            
            // Get current cycle
            $latestCycle = $trainModel->orderBy('created_at', 'DESC')->first();
            $activeCycleId = $latestCycle ? $latestCycle['cycle_id'] : null;

            if (!$activeCycleId) {
                return redirect()->back()->with('error', 'No active cycle found to swap within.');
            }

            // Find logs for both users in the active cycle
            $log1 = $trainModel->where('user_id', $user1Id)->where('cycle_id', $activeCycleId)->first();
            $log2 = $trainModel->where('user_id', $user2Id)->where('cycle_id', $activeCycleId)->first();

            if (!$log1 || !$log2) {
                return redirect()->back()->with('error', 'Both users must be scheduled in the active cycle to swap.');
            }

            // Swap assigned_time
            $time1 = $log1['assigned_time'];
            $time2 = $log2['assigned_time'];

            $db = \Config\Database::connect();
            $db->transStart();

            $trainModel->update($log1['id'], ['assigned_time' => $time2]);
            $trainModel->update($log2['id'], ['assigned_time' => $time1]);

            $db->transComplete();

            if ($db->transStatus() === FALSE) {
                return redirect()->back()->with('error', 'Database error during swap.');
            }

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Database error: ' . $e->getMessage());
        }

        return redirect()->back()->with('message', 'Schedule swapped successfully!');
    }

    public function updateStatus()
    {
        // Only Admins/R4+ can run this
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return redirect()->back()->with('error', 'Unauthorized to update status.');
        }

        $userId = $this->request->getPost('user_id');
        $scheduleDate = $this->request->getPost('schedule_date');
        $status = $this->request->getPost('status');
        $mvpUserId = $this->request->getPost('mvp_user_id');
        $guardianUserId = $this->request->getPost('guardian_user_id');
        
        // These are checkboxes, if set they equal 1
        $turnedGold = $this->request->getPost('turned_gold') ? 1 : 0;
        $reachedMinimum = $this->request->getPost('reached_minimum') ? 1 : 0;

        if (!$userId || !$status) {
            return redirect()->back()->with('error', 'Missing required data.');
        }

        try {
            $trainModel = new GoldTrainLogModel();
            
            // Determine active cycle
            $latestCycle = $trainModel->orderBy('created_at', 'DESC')->first();
            $activeCycleId = $latestCycle ? $latestCycle['cycle_id'] : 'Manual-Override';
            
            // Get latest log for this user to update, or create one for the active cycle
            $log = $trainModel->where('user_id', $userId)
                              ->where('cycle_id', $activeCycleId)
                              ->orderBy('created_at', 'DESC')
                              ->first();

            // Overriding the schema design slightly based on HTML checkboxes. 
            if ($turnedGold && $reachedMinimum && $status === 'pending') {
                $status = 'completed';
            }

            $data = [
                'status' => $status,
                'assigned_time' => $scheduleDate ? date('Y-m-d H:i:s', strtotime($scheduleDate)) : null,
                'mvp_user_id' => empty($mvpUserId) ? null : $mvpUserId,
                'guardian_user_id' => empty($guardianUserId) ? null : $guardianUserId,
            ];

            if ($log) {
                $trainModel->update($log['id'], $data);
            } else {
                $data['user_id'] = $userId;
                $data['cycle_id'] = $activeCycleId;
                $trainModel->insert($data);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Database error: Please run migrations first. ' . $e->getMessage());
        }

        return redirect()->back()->with('message', 'Player status updated successfully.');
    }

    public function resetRoster()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return redirect()->back()->with('error', 'Unauthorized to reset the roster.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $trainModel = new GoldTrainLogModel();
            
            // Before resetting, let's mark all currently scheduled players in the active cycle as completed
            // so they are preserved correctly in history instead of staying 'scheduled'.
            $latestCycleRow = $trainModel->orderBy('created_at', 'DESC')->first();
            if ($latestCycleRow) {
                $oldCycleId = $latestCycleRow['cycle_id'];
                
                $unfinishedLogs = $trainModel->where('cycle_id', $oldCycleId)
                                             ->whereIn('status', ['pending', 'scheduled'])
                                             ->where('assigned_time IS NOT NULL')
                                             ->findAll();
                                             
                foreach ($unfinishedLogs as $log) {
                    $trainModel->update($log['id'], [
                        'status' => 'completed'
                    ]);
                }
            }

            // Instead of truncating, we generate a brand new 'pending' cycle for all users.
            // This preserves the historical records in the database.
            $userModel = new UserModel();
            $allUsers = $userModel->where('status', 'approved')->findAll();
            $cycleId = 'Cycle-Reset-' . date('Ymd-Hi');
            
            foreach ($allUsers as $user) {
                $trainModel->insert([
                    'user_id' => $user['id'],
                    'cycle_id' => $cycleId,
                    'status' => 'pending',
                    'assigned_time' => null
                ]);
            }
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error resetting roster: ' . $e->getMessage());
        }

        $db->transComplete();

        if ($db->transStatus() === FALSE) {
            return redirect()->back()->with('error', 'Transaction failed while resetting roster.');
        }

        return redirect()->back()->with('message', 'The active Gold Train roster schedule has been reset for a new cycle. Previous cycle records were saved to history.');
    }

    public function history()
    {
        $trainModel = new GoldTrainLogModel();
        $userModel = new UserModel();
        
        $role = session()->get('role');
        $data = [
            'title' => 'Polar Express (Gold Train) - History'
        ];

        // Fetch all unique cycles
        $cycles = $trainModel->select('cycle_id')->distinct()->orderBy('created_at', 'DESC')->findAll();
        $availableCycles = array_column($cycles, 'cycle_id');
        
        // Filter by specific cycle if provided
        $selectedCycle = $this->request->getGet('cycle_id');
        if (!$selectedCycle) {
            // Default to 'all' if none selected
            $selectedCycle = 'all';
        }

        // Build base query for history
        $builder = $trainModel->orderBy('created_at', 'DESC');
        if ($selectedCycle !== 'all') {
            $builder->where('cycle_id', $selectedCycle);
        }
        
        $historyLogs = $builder->findAll();

        // Build a lookup map of user_id => player name for quick mapping
        $allUsers = $userModel->findAll();
        $nameMap = [];
        foreach ($allUsers as $u) {
            $nameMap[$u['id']] = !empty($u['player_name']) ? $u['player_name'] : $u['username'];
        }

        // Compile logs with names and stats
        $stats = ['completed' => 0, 'failed' => 0, 'total' => 0];
        
        foreach ($historyLogs as &$log) {
            $log['player_name'] = $nameMap[$log['user_id']] ?? 'Unknown';
            $log['mvp_name'] = !empty($log['mvp_user_id']) ? ($nameMap[$log['mvp_user_id']] ?? 'Unknown') : null;
            $log['guardian_name'] = !empty($log['guardian_user_id']) ? ($nameMap[$log['guardian_user_id']] ?? 'Unknown') : null;
            
            $stats['total']++;
            if ($log['status'] === 'completed') {
                $stats['completed']++;
            } elseif (in_array($log['status'], ['failed', 'skipped'])) {
                $stats['failed']++;
            }
        }

        $data['historyLogs'] = $historyLogs;
        $data['availableCycles'] = $availableCycles;
        $data['selectedCycle'] = $selectedCycle;
        $data['stats'] = $stats;
        
        return view('gold_train/history', $data);
    }
}