<?php

namespace App\Controllers;

use App\Models\EventModel;
use App\Models\EventPlanModel;
use App\Models\UserModel;
use App\Models\UserStatModel;

class CanyonStormController extends BaseController
{
    public function index()
    {
        // Force error reporting for this request to help debug
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        try {
            $db = \Config\Database::connect();
            
            // 1. Get Event ID
            $eventModel = new EventModel();
            // In the migration it was named 'Canyon', let's check for both
            $csEvent = $eventModel->where('name', 'Canyon Storm')->orWhere('name', 'Canyon')->first();
            
            if (!$csEvent) {
                $eventId = $eventModel->insert([
                    'name' => 'Canyon Storm',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                $eventId = $csEvent['id'];
                // If it was just 'Canyon', let's update it to 'Canyon Storm' for consistency
                if ($csEvent['name'] === 'Canyon') {
                    $eventModel->update($eventId, ['name' => 'Canyon Storm']);
                }
            }

            // 2. Fetch Historical Plans
            $eventPlanModel = new EventPlanModel();
            $historicalPlans = [];
            try {
                $historicalPlans = $eventPlanModel->where('event_id', $eventId)
                                                  ->orderBy('event_datetime', 'DESC')
                                                  ->findAll() ?: [];
            } catch (\Exception $e) {
                // Ignore table missing errors
            }

            // 3. Match selection
            $planId = $this->request->getGet('plan_id');
            $currentPlan = $planId ? $eventPlanModel->find($planId) : null;
            $formattedPlayers = [];

            if ($currentPlan && !empty($currentPlan['plan_data'])) {
                $formattedPlayers = json_decode($currentPlan['plan_data'], true) ?: [];
                
                // Add new users since snapshot
                try {
                    $builder = $db->table('users');
                    $builder->select('users.id, users.username, users.player_name, user_stats.power_tank, user_stats.power_air, user_stats.power_missile');
                    $builder->join('user_stats', 'user_stats.user_id = users.id', 'left');
                    $builder->where('users.status', 'approved');
                    $currentUsers = $builder->get()->getResultArray();

                    $snapshotIds = array_column($formattedPlayers, 'id');

                    foreach ($currentUsers as $u) {
                        $stringId = (string)$u['id'];
                        if (!in_array($stringId, $snapshotIds)) {
                            $pow = $this->calculatePower($u);
                            $formattedPlayers[] = [
                                'id'        => $stringId,
                                'name'      => !empty($u['player_name']) ? $u['player_name'] : $u['username'],
                                'power'     => number_format($pow, 2),
                                'power_raw' => $pow,
                                'box'       => 'unassigned'
                            ];
                        }
                    }
                } catch (\Exception $e) {}
            } else {
                // Fresh roster
                $builder = $db->table('users');
                $builder->select('users.id, users.username, users.player_name, user_stats.power_tank, user_stats.power_air, user_stats.power_missile');
                $builder->join('user_stats', 'user_stats.user_id = users.id', 'left');
                $builder->where('users.status', 'approved');
                
                $users = $builder->get()->getResultArray() ?: [];

                foreach ($users as $u) {
                    $pow = $this->calculatePower($u);
                    $formattedPlayers[] = [
                        'id'        => (string)$u['id'],
                        'name'      => !empty($u['player_name']) ? $u['player_name'] : $u['username'],
                        'power'     => number_format($pow, 2),
                        'power_raw' => $pow,
                        'box'       => 'unassigned'
                    ];
                }
            }
            
            usort($formattedPlayers, function($a, $b) {
                return ($b['power_raw'] ?? 0) <=> ($a['power_raw'] ?? 0);
            });

            $data = [
                'title'            => 'Canyon Storm Alliance Planner',
                'players_json'     => json_encode($formattedPlayers),
                'event_id'         => $eventId,
                'current_plan'     => $currentPlan,
                'historical_plans' => $historicalPlans,
                'is_admin'         => in_array(session()->get('role'), ['admin', 'super_admin'])
            ];
            
            return view('canyon_storm_planner', $data);

        } catch (\Throwable $e) {
            // Catch EVERYTHING (Errors and Exceptions)
            return "<h2>Debug Error Information:</h2>" .
                   "<p><strong>Message:</strong> " . $e->getMessage() . "</p>" .
                   "<p><strong>File:</strong> " . $e->getFile() . " (Line: " . $e->getLine() . ")</p>" .
                   "<pre>" . $e->getTraceAsString() . "</pre>";
        }
    }

    private function calculatePower($user)
    {
        $t = (float)($user['power_tank'] ?? 0);
        $a = (float)($user['power_air'] ?? 0);
        $m = (float)($user['power_missile'] ?? 0);
        return $t + $a + $m;
    }

    public function saveAssignments()
    {
        $userRole = session()->get('role');
        if (!in_array($userRole, ['admin', 'super_admin'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $eventId = $this->request->getPost('event_id');
        $eventDatetime = $this->request->getPost('event_datetime');
        $fullPlanJson = $this->request->getPost('full_plan');
        
        if (empty($eventDatetime)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Missing Match Time.']);
        }

        try {
            $eventDatetimeObj = new \DateTime($eventDatetime);
            $weekStart = $eventDatetimeObj->modify('monday this week')->format('Y-m-d');

            $eventPlanModel = new EventPlanModel();
            
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

            $assignments = json_decode($fullPlanJson, true);
            $logModel = new \App\Models\WeeklyEventLogModel();

            foreach ($assignments as $player) {
                if (strpos($player['id'], 'manual_') === 0 || strpos($player['id'], 'upload_') === 0) {
                    continue;
                }

                $userId = $player['id'];
                $boxId = $player['box'];

                $existing = $logModel->where('user_id', $userId)
                                     ->where('event_id', $eventId)
                                     ->where('event_datetime', $eventDatetime)
                                     ->first();

                if ($boxId === 'unassigned') {
                    if ($existing) $logModel->update($existing['id'], ['assigned_position' => null]);
                    continue;
                }

                if ($existing) {
                    $logModel->update($existing['id'], ['assigned_position' => $boxId]);
                } else {
                    $logModel->insert([
                        'user_id'           => $userId,
                        'event_id'          => $eventId,
                        'week_start'        => $weekStart,
                        'event_datetime'    => $eventDatetime,
                        'assigned_position' => $boxId,
                        'status'            => 'participated', 
                        'score'             => 0
                    ]);
                }
            }

            return $this->response->setJSON(['status' => 'success', 'message' => "Saved successfully!"]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
