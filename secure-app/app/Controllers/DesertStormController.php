<?php

namespace App\Controllers;

use App\Models\EventModel;
use App\Models\EventPlanModel;
use App\Models\UserModel;

class DesertStormController extends BaseController
{
    public function index()
    {
        // Check for mobile device, unless specifically requesting desktop view
        $agent = $this->request->getUserAgent();
        if ($agent->isMobile() && !$this->request->getGet('desktop')) {
            return redirect()->to('/ds_planner_mobile' . ($this->request->getGet('plan_id') ? '?plan_id=' . $this->request->getGet('plan_id') : ''));
        }

        $db = \Config\Database::connect();
        
        // 1. We need the Event ID for "Desert Storm"
        $eventModel = new EventModel();
        try {
            $dsEvent = $eventModel->where('name', 'Desert Storm')->first();
            $eventId = $dsEvent['id'] ?? 0;
        } catch (\Exception $e) {
            $eventId = 0;
        }

        // Fetch all historical plans for this event so the user can select them from a dropdown
        $eventPlanModel = new EventPlanModel();
        $historicalPlans = [];
        try {
            $historicalPlans = $eventPlanModel->where('event_id', $eventId)
                                              ->orderBy('event_datetime', 'DESC')
                                              ->findAll();
        } catch (\Exception $e) {
            // Migrations haven't run
        }

        // Determine which specific Event Plan we are viewing (if any)
        $planId = $this->request->getGet('plan_id');
        $currentPlan = null;
        $formattedPlayers = [];
        
        if ($planId) {
            $currentPlan = $eventPlanModel->find($planId);
        }

        // If we loaded a specific plan, we use the EXACT player state saved in the JSON
        if ($currentPlan && !empty($currentPlan['plan_data'])) {
            $formattedPlayers = json_decode($currentPlan['plan_data'], true);
            
            // Check for new users that joined the alliance AFTER this snapshot was saved
            try {
                $builder = $db->table('users');
                $builder->select('users.id, users.username, users.player_name, user_stats.power_tank, user_stats.power_air, user_stats.power_missile');
                $builder->join('user_stats', 'user_stats.user_id = users.id', 'left');
                $builder->where('users.status', 'approved');
                $currentUsers = $builder->get()->getResultArray();

                // Map out who is already in the snapshot
                $snapshotIds = array_column($formattedPlayers, 'id');

                foreach ($currentUsers as $u) {
                    $stringId = (string)$u['id'];
                    if (!in_array($stringId, $snapshotIds)) {
                        $powerTank = isset($u['power_tank']) ? (float)$u['power_tank'] : 0.00;
                        $powerAir = isset($u['power_air']) ? (float)$u['power_air'] : 0.00;
                        $powerMissile = isset($u['power_missile']) ? (float)$u['power_missile'] : 0.00;
                        $totalPower = $powerTank + $powerAir + $powerMissile;
                        
                        $displayName = !empty($u['player_name']) ? $u['player_name'] : $u['username'];

                        $formattedPlayers[] = [
                            'id'    => $stringId,
                            'name'  => $displayName,
                            'power' => $totalPower > 0 ? number_format($totalPower, 2) : '0.00',
                            'power_raw' => $totalPower, // for sorting
                            'box'   => 'unassigned' // Newly joined users start unassigned
                        ];
                    }
                }
            } catch (\Exception $e) {
                // Ignore if tables are missing
            }

        } else {
            // Build a fresh roster from the current database
            $builder = $db->table('users');
            $builder->select('users.id, users.username, users.player_name, user_stats.power_tank, user_stats.power_air, user_stats.power_missile');
            $builder->join('user_stats', 'user_stats.user_id = users.id', 'left');
            $builder->where('users.status', 'approved');
            
            try {
                $users = $builder->get()->getResultArray();
            } catch (\Exception $e) {
                $users = []; 
            }

            foreach ($users as $u) {
                // Find strongest squad
                $powerTank = isset($u['power_tank']) ? (float)$u['power_tank'] : 0.00;
                $powerAir = isset($u['power_air']) ? (float)$u['power_air'] : 0.00;
                $powerMissile = isset($u['power_missile']) ? (float)$u['power_missile'] : 0.00;
                
                // For planners, total power might be better or max power depending on needs.
                // We'll calculate total here to allow sorting by overall power as requested.
                $totalPower = $powerTank + $powerAir + $powerMissile;
                $powerDisplay = $totalPower > 0 ? number_format($totalPower, 2) : '0.00';
                
                $displayName = !empty($u['player_name']) ? $u['player_name'] : $u['username'];

                $formattedPlayers[] = [
                    'id'    => (string)$u['id'],
                    'name'  => $displayName,
                    'power' => $powerDisplay,
                    'power_raw' => $totalPower, // Raw float for sorting
                    'box'   => 'unassigned' // Fresh board
                ];
            }
        }
        
        // Sort formatted players by raw power descending for the unassigned roster
        usort($formattedPlayers, function($a, $b) {
            $powA = isset($a['power_raw']) ? (float)$a['power_raw'] : 0;
            $powB = isset($b['power_raw']) ? (float)$b['power_raw'] : 0;
            return $powB <=> $powA;
        });

        // Fetch all approved users for the "Available Roster"
        $userModel = new UserModel();
        $availableRoster = $userModel->select('users.id, users.username, users.player_name, (user_stats.power_tank + user_stats.power_air + user_stats.power_missile) as total_power')
                                     ->join('user_stats', 'user_stats.user_id = users.id', 'left')
                                     ->where('users.status', 'approved')
                                     ->orderBy('total_power', 'DESC')
                                     ->findAll();

        $userRole = session()->get('role');
        $data = [
            'title'            => 'Desert Storm Alliance Planner',
            'players_json'     => json_encode($formattedPlayers),
            'available_roster' => $availableRoster,
            'event_id'         => $eventId,
            'current_plan'     => $currentPlan,
            'historical_plans' => $historicalPlans,
            'is_admin'         => in_array($userRole, ['admin', 'super_admin'])
        ];

        return view('desert_storm_planner', $data);
    }

    public function mobile()
    {
        $db = \Config\Database::connect();
        
        // 1. We need the Event ID for "Desert Storm"
        $eventModel = new EventModel();
        try {
            $dsEvent = $eventModel->where('name', 'Desert Storm')->first();
            $eventId = $dsEvent['id'] ?? 0;
        } catch (\Exception $e) {
            $eventId = 0;
        }

        // Fetch all historical plans for this event so the user can select them from a dropdown
        $eventPlanModel = new EventPlanModel();
        $historicalPlans = [];
        try {
            $historicalPlans = $eventPlanModel->where('event_id', $eventId)
                                              ->orderBy('event_datetime', 'DESC')
                                              ->findAll();
        } catch (\Exception $e) {
            // Migrations haven't run
        }

        // Determine which specific Event Plan we are viewing (if any)
        $planId = $this->request->getGet('plan_id');
        $currentPlan = null;
        $formattedPlayers = [];
        
        if ($planId) {
            $currentPlan = $eventPlanModel->find($planId);
        }

        // If we loaded a specific plan, we use the EXACT player state saved in the JSON
        if ($currentPlan && !empty($currentPlan['plan_data'])) {
            $formattedPlayers = json_decode($currentPlan['plan_data'], true);
            
            // Check for new users that joined the alliance AFTER this snapshot was saved
            try {
                $builder = $db->table('users');
                $builder->select('users.id, users.username, users.player_name, user_stats.power_tank, user_stats.power_air, user_stats.power_missile');
                $builder->join('user_stats', 'user_stats.user_id = users.id', 'left');
                $builder->where('users.status', 'approved');
                $currentUsers = $builder->get()->getResultArray();

                // Map out who is already in the snapshot
                $snapshotIds = array_column($formattedPlayers, 'id');

                foreach ($currentUsers as $u) {
                    $stringId = (string)$u['id'];
                    if (!in_array($stringId, $snapshotIds)) {
                        $powerTank = isset($u['power_tank']) ? (float)$u['power_tank'] : 0.00;
                        $powerAir = isset($u['power_air']) ? (float)$u['power_air'] : 0.00;
                        $powerMissile = isset($u['power_missile']) ? (float)$u['power_missile'] : 0.00;
                        $totalPower = $powerTank + $powerAir + $powerMissile;
                        
                        $displayName = !empty($u['player_name']) ? $u['player_name'] : $u['username'];

                        $formattedPlayers[] = [
                            'id'    => $stringId,
                            'name'  => $displayName,
                            'power' => $totalPower > 0 ? number_format($totalPower, 2) : '0.00',
                            'power_raw' => $totalPower, // for sorting
                            'box'   => 'unassigned' // Newly joined users start unassigned
                        ];
                    }
                }
            } catch (\Exception $e) {
                // Ignore if tables are missing
            }

        } else {
            // Build a fresh roster from the current database
            $builder = $db->table('users');
            $builder->select('users.id, users.username, users.player_name, user_stats.power_tank, user_stats.power_air, user_stats.power_missile');
            $builder->join('user_stats', 'user_stats.user_id = users.id', 'left');
            $builder->where('users.status', 'approved');
            
            try {
                $users = $builder->get()->getResultArray();
            } catch (\Exception $e) {
                $users = []; 
            }

            foreach ($users as $u) {
                // Find strongest squad
                $powerTank = isset($u['power_tank']) ? (float)$u['power_tank'] : 0.00;
                $powerAir = isset($u['power_air']) ? (float)$u['power_air'] : 0.00;
                $powerMissile = isset($u['power_missile']) ? (float)$u['power_missile'] : 0.00;
                
                // For planners, total power might be better or max power depending on needs.
                // We'll calculate total here to allow sorting by overall power as requested.
                $totalPower = $powerTank + $powerAir + $powerMissile;
                $powerDisplay = $totalPower > 0 ? number_format($totalPower, 2) : '0.00';
                
                $displayName = !empty($u['player_name']) ? $u['player_name'] : $u['username'];

                $formattedPlayers[] = [
                    'id'    => (string)$u['id'],
                    'name'  => $displayName,
                    'power' => $powerDisplay,
                    'power_raw' => $totalPower, // Raw float for sorting
                    'box'   => 'unassigned' // Fresh board
                ];
            }
        }
        
        // Sort formatted players by raw power descending for the unassigned roster
        usort($formattedPlayers, function($a, $b) {
            $powA = isset($a['power_raw']) ? (float)$a['power_raw'] : 0;
            $powB = isset($b['power_raw']) ? (float)$b['power_raw'] : 0;
            return $powB <=> $powA;
        });

        // Fetch all approved users for the "Available Roster"
        $userModel = new UserModel();
        $availableRoster = $userModel->select('users.id, users.username, users.player_name, (user_stats.power_tank + user_stats.power_air + user_stats.power_missile) as total_power')
                                     ->join('user_stats', 'user_stats.user_id = users.id', 'left')
                                     ->where('users.status', 'approved')
                                     ->orderBy('total_power', 'DESC')
                                     ->findAll();

        $userRole = session()->get('role');
        $data = [
            'title'            => 'Desert Storm Mobile Planner',
            'players_json'     => json_encode($formattedPlayers),
            'available_roster' => $availableRoster,
            'event_id'         => $eventId,
            'current_plan'     => $currentPlan,
            'historical_plans' => $historicalPlans,
            'is_admin'         => in_array($userRole, ['admin', 'super_admin'])
        ];

        return view('desert_storm_planner_mobile', $data);
    }

    public function saveAssignments()
    {
        $userRole = session()->get('role');

        // Allow both admins and super_admins to save assignments
        if (!in_array($userRole, ['admin', 'super_admin'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $eventId = $this->request->getPost('event_id');
        $eventDatetime = $this->request->getPost('event_datetime'); // The specific time of this DS match
        $fullPlanJson = $this->request->getPost('full_plan');       // The complete React state array
        
        // This is still needed to sync with the Weekly Event Log
        $weekStart = date('Y-m-d', strtotime('monday this week', strtotime($eventDatetime))); 

        if (!$eventId || !$eventDatetime || !$fullPlanJson) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Missing data. Ensure you selected a Date and Time.']);
        }

        $eventPlanModel = new EventPlanModel();
        
        // 1. Save the exact snapshot of the board (including temp players) into event_plans
        try {
            // Check if we are updating an existing plan for this exact datetime, or making a new one
            $existingPlan = $eventPlanModel->where('event_id', $eventId)
                                           ->where('event_datetime', $eventDatetime)
                                           ->first();
            
            if ($existingPlan) {
                $eventPlanModel->update($existingPlan['id'], ['plan_data' => $fullPlanJson]);
            } else {
                $eventPlanModel->insert([
                    'event_id'       => $eventId,
                    'event_datetime' => $eventDatetime,
                    'plan_data'      => $fullPlanJson
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Database migration needed for event_plans table.']);
        }

        // 2. Sync the REAL users with the weekly_event_logs table for tracking
        $assignments = json_decode($fullPlanJson, true);
        $logModel = new \App\Models\WeeklyEventLogModel();
        $savedCount = 0;

        foreach ($assignments as $player) {
            // Skip temp users when it comes to the official participation tracking log
            if (strpos($player['id'], 'manual_') === 0 || strpos($player['id'], 'upload_') === 0) {
                continue;
            }

            $userId = $player['id'];
            $boxId = $player['box'];

            // We now track logs by event_datetime to allow multiple DS matches per week
            try {
                $existing = $logModel->where('user_id', $userId)
                                     ->where('event_id', $eventId)
                                     ->where('event_datetime', $eventDatetime)
                                     ->first();

                if ($boxId === 'unassigned') {
                    if ($existing) {
                        $logModel->update($existing['id'], ['assigned_position' => null]);
                    }
                    continue;
                }

                if ($existing) {
                    $logModel->update($existing['id'], [
                        'assigned_position' => $boxId
                    ]);
                } else {
                    $logModel->insert([
                        'user_id'           => $userId,
                        'event_id'          => $eventId,
                        'week_start'        => $weekStart,
                        'event_datetime'    => $eventDatetime, // Track specific match time
                        'assigned_position' => $boxId,
                        'status'            => 'participated', 
                        'score'             => 0
                    ]);
                }
                $savedCount++;
            } catch (\Exception $e) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Database migration needed for weekly_event_logs datetime.']);
            }
        }

        return $this->response->setJSON(['status' => 'success', 'message' => "Snapshot saved successfully! {$savedCount} official users synced to logs."]);
    }
}