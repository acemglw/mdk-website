<?php

namespace App\Controllers;

use App\Models\EventModel;
use App\Models\WeeklyEventLogModel;
use App\Models\EventPlanModel;
use App\Models\UserModel;

class EventController extends BaseController
{
    public function userLog()
    {
        $userId = session()->get('id');
        $logModel = new WeeklyEventLogModel();
        
        $db = \Config\Database::connect();
        $builder = $db->table('weekly_event_logs');
        $builder->select('weekly_event_logs.*, events.name as event_name');
        $builder->join('events', 'events.id = weekly_event_logs.event_id');
        $builder->where('weekly_event_logs.user_id', $userId);
        $builder->orderBy('weekly_event_logs.event_datetime', 'DESC');
        
        try {
            $data['logs'] = $builder->get()->getResultArray();
        } catch (\Exception $e) {
            $data['logs'] = [];
        }
        
        return view('events/user_log', $data);
    }

    public function adminLog()
    {
        $userModel = new UserModel();
        $eventModel = new EventModel();
        $eventPlanModel = new EventPlanModel();
        $logModel = new WeeklyEventLogModel();
        
        // Let the admin choose the event, default to Desert Storm
        $eventId = $this->request->getGet('event_id');
        if (!$eventId) {
            $dsEvent = $eventModel->where('name', 'Desert Storm')->first();
            $eventId = $dsEvent['id'] ?? 0;
        }
        $data['selected_event_id'] = $eventId;
        $data['events'] = $eventModel->findAll();

        // Range selection
        $lookbackWeeks = (int)($this->request->getGet('weeks') ?: 2);
        $lookbackDate = date('Y-m-d H:i:s', strtotime("-{$lookbackWeeks} weeks"));

        $data['historical_plans'] = [];
        try {
            $data['historical_plans'] = $eventPlanModel->where('event_id', $eventId)
                                                       ->orderBy('event_datetime', 'DESC')
                                                       ->findAll();
        } catch (\Exception $e) {}

        $db = \Config\Database::connect();

        try {
            $builder = $db->table('weekly_event_logs');
            $builder->select('
                weekly_event_logs.user_id, 
                weekly_event_logs.user_id_temp, 
                users.username, 
                users.player_name, 
                COUNT(weekly_event_logs.id) as total_matches,
                SUM(CASE WHEN weekly_event_logs.status = "participated" THEN 1 ELSE 0 END) as participated_count,
                SUM(CASE WHEN weekly_event_logs.status = "missed" THEN 1 ELSE 0 END) as missed_count
            ');
            $builder->join('users', 'users.id = weekly_event_logs.user_id', 'left');
            $builder->where('weekly_event_logs.event_id', $eventId);
            $builder->where('weekly_event_logs.event_datetime >=', $lookbackDate);
            $builder->where('weekly_event_logs.assigned_position IS NOT NULL');
            $builder->groupBy('weekly_event_logs.user_id, weekly_event_logs.user_id_temp');
            $builder->orderBy('participated_count', 'DESC');
            $data['roster'] = $builder->get()->getResultArray();

            foreach ($data['roster'] as &$row) {
                $row['streak'] = 0;
                $history = $logModel->where('event_id', $eventId)
                                    ->where('event_datetime >=', $lookbackDate)
                                    ->groupStart()
                                        ->where('user_id', $row['user_id'])
                                        ->orWhere('user_id_temp', $row['user_id_temp'])
                                    ->groupEnd()
                                    ->orderBy('event_datetime', 'DESC')
                                    ->findAll();
                foreach ($history as $h) {
                    if ($h['status'] === 'missed') $row['streak']++;
                    else break;
                }
            }
        } catch (\Exception $e) {
            return redirect()->to('/admin/events')->with('error', 'Query error: ' . $e->getMessage());
        }

        $data['lookback_weeks'] = $lookbackWeeks;
        $data['event_id'] = $eventId;
        return view('admin/events', $data);
    }
    
    // NEW PAGE: Selector page
    public function selectMatch()
    {
        $eventModel = new EventModel();
        $eventPlanModel = new EventPlanModel();
        
        $data['events'] = $eventModel->findAll();
        
        $selectedEventId = $this->request->getGet('event_id');
        $data['selected_event_id'] = $selectedEventId;
        
        $data['historical_plans'] = [];
        if ($selectedEventId) {
            try {
                $data['historical_plans'] = $eventPlanModel->where('event_id', $selectedEventId)
                                                           ->orderBy('event_datetime', 'DESC')
                                                           ->findAll();
            } catch (\Exception $e) {}
        }

        return view('admin/select_match', $data);
    }
    
    public function editMatch()
    {
        $userModel = new UserModel();
        $eventModel = new EventModel();
        $eventPlanModel = new EventPlanModel();
        $logModel = new WeeklyEventLogModel();
        
        $eventId = $this->request->getGet('event_id');

        // Match selection
        $selectedDatetimeRaw = $this->request->getGet('event_datetime');
        if (!$selectedDatetimeRaw || !$eventId) {
            return redirect()->to('/admin/events/select')->with('error', 'Please select an event and a match to edit.');
        }

        $event = $eventModel->find($eventId);
        if (!$event) {
            return redirect()->to('/admin/events/select')->with('error', 'Invalid event selected.');
        }

        // Keep raw for the view form
        $selectedDatetime = $selectedDatetimeRaw;
        
        // Convert 'T' to space for database queries if needed
        $queryDatetime = str_replace('T', ' ', $selectedDatetimeRaw);
        // Ensure seconds are included if not present
        if (strlen($queryDatetime) === 16) {
            $queryDatetime .= ':00';
        }

        try {
            // First, try a robust search
            // Sometimes the db formats event_datetime slightly differently.
            // Let's get all logs for this event and filter manually.
            $allLogs = $logModel->where('event_id', $eventId)
                                ->where('assigned_position IS NOT NULL')
                                ->findAll();
            
            // To compare reliably, let's normalize strings to timestamps
            $targetTime = strtotime($queryDatetime);
            
            $rawLogs = [];
            foreach ($allLogs as $log) {
                // if timestamps match, it's the right event.
                if (strtotime($log['event_datetime']) === $targetTime) {
                    $rawLogs[] = $log;
                }
            }
            
            // Fallback: If exact timestamp didn't work (maybe missing seconds or something)
            if (empty($rawLogs)) {
                $searchString = substr($queryDatetime, 0, 16);
                foreach ($allLogs as $log) {
                    if (strpos($log['event_datetime'], $searchString) === 0) {
                        $rawLogs[] = $log;
                    }
                }
            }
            
            // Fallback 2: Check if there's an event plan for this datetime and see how its date is stored
            if (empty($rawLogs)) {
               $plan = $eventPlanModel->where('event_id', $eventId)->like('event_datetime', substr($queryDatetime, 0, 16))->first();
               if ($plan) {
                   $rawLogs = $logModel->where('event_id', $eventId)
                                       ->where('event_datetime', $plan['event_datetime'])
                                       ->where('assigned_position IS NOT NULL')
                                       ->findAll();
               }
            }
            
            $formattedRoster = [];
            foreach ($rawLogs as $log) {
                $displayName = '';
                $allianceLevel = '';
                $isTemp = false;

                if (!empty($log['user_id'])) {
                    $user = $userModel->find($log['user_id']);
                    $displayName = $user ? (!empty($user['player_name']) ? $user['player_name'] : $user['username']) : 'Deleted User';
                    $allianceLevel = $user ? $user['alliance_level'] : 'N/A';
                } else {
                    $isTemp = true;
                    $displayName = $log['user_id_temp'];
                    $allianceLevel = 'TEMP';
                }

                $formattedRoster[] = [
                    'user_id' => $log['user_id'],
                    'user_id_temp' => $log['user_id_temp'],
                    'username' => $displayName,
                    'player_name' => $displayName,
                    'alliance_level' => $allianceLevel,
                    'assigned_position' => $log['assigned_position'],
                    'status' => $log['status'],
                    'score' => $log['score'],
                    'is_temp' => $isTemp
                ];
            }
            usort($formattedRoster, function($a, $b) { return strcmp($a['username'], $b['username']); });
            $data['roster'] = $formattedRoster;
        } catch (\Exception $e) {
            return redirect()->to('/admin/events/select')->with('error', 'Query error: ' . $e->getMessage());
        }

        $data['event_name'] = $event['name'];
        $data['selected_datetime'] = $selectedDatetime; // e.g. "2026-05-31T03:00"
        $data['event_id'] = $eventId;
        return view('admin/edit_match', $data);
    }

    public function logParticipation()
    {
        $logModel = new WeeklyEventLogModel();
        $eventId = $this->request->getPost('event_id');
        $eventDatetime = $this->request->getPost('event_datetime');
        $usersArray = $this->request->getPost('users'); 

        if (!$eventId || !$eventDatetime || !is_array($usersArray)) {
            return redirect()->back()->with('error', 'Invalid data.');
        }

        $db = \Config\Database::connect();
        $db->transStart();
        
        // Convert back to space format if it has a T
        $queryDatetime = str_replace('T', ' ', $eventDatetime);
        $searchString = substr($queryDatetime, 0, 16);

        // Normalize time target
        $targetTime = strtotime($queryDatetime);

        foreach ($usersArray as $id => $userData) {
            $isTemp = (strpos((string)$id, 'manual_') === 0 || strpos((string)$id, 'upload_') === 0);
            $status = isset($userData['participated']) ? 'participated' : 'missed';
            $score = isset($userData['score']) ? (int)$userData['score'] : 0;

            // Fetch correctly matching datetime
            $query = $logModel->where('event_id', $eventId);
            if ($isTemp) {
                $query->where('user_id_temp', $id);
            } else {
                $query->where('user_id', $id);
            }
            
            $existingRecords = $query->findAll();
            $existing = null;
            
            // Try exact timestamp match first
            foreach ($existingRecords as $rec) {
                if (strtotime($rec['event_datetime']) === $targetTime) {
                    $existing = $rec;
                    break;
                }
            }
            
            // Fallback to substring match
            if (!$existing) {
                foreach ($existingRecords as $rec) {
                    if (strpos($rec['event_datetime'], $searchString) === 0) {
                        $existing = $rec;
                        break;
                    }
                }
            }

            if ($existing) {
                $logModel->update($existing['id'], ['status' => $status, 'score' => $score]);
            }
        }

        $db->transComplete();
        // Return original datetime in the URL (e.g. 2026-05-31T03:00)
        return redirect()->to("/admin/events/edit?event_id={$eventId}&event_datetime=".urlencode($eventDatetime))->with('success', 'Match participation updated!');
    }
}
