<?php

namespace App\Controllers;

use App\Models\PageAccessLogModel;
use App\Models\PageAccessLogSnapshotModel;
use App\Models\UserModel;
use CodeIgniter\Controller;

class SuperAdminController extends BaseController
{
    public function accessLogs()
    {
        $logModel = new PageAccessLogModel();
        $userModel = new UserModel();
        $snapshotModel = new PageAccessLogSnapshotModel();

        // Optional filters
        $userId = $this->request->getGet('user_id');
        $selectedUrl = $this->request->getGet('url');
        $viewType = $this->request->getGet('view_type') ?: 'current'; // 'current', 'snapshots', or 'top_users'

        $data['view_type'] = $viewType;

        if ($viewType === 'snapshots') {
            // View 3: Weekly Snapshots Archive
            $snapshotModel->select('page_access_log_snapshots.*, users.username, users.player_name');
            $snapshotModel->join('users', 'users.id = page_access_log_snapshots.user_id', 'left');
            
            if ($userId) {
                $snapshotModel->where('page_access_log_snapshots.user_id', $userId);
            }
            
            $snapshotModel->orderBy('page_access_log_snapshots.end_date', 'DESC');
            $snapshotModel->orderBy('page_access_log_snapshots.visit_count', 'DESC');
            
            $data['logs'] = $snapshotModel->paginate(50);
            $data['pager'] = $snapshotModel->pager;
            $data['view_mode'] = 'snapshots';

        } elseif ($viewType === 'top_users') {
            // View 4: Top Users (Aggregated across live logs)
            $logModel->select('user_id, users.username, users.player_name, COUNT(page_access_logs.id) as total_visits, MAX(page_access_logs.created_at) as last_active');
            $logModel->join('users', 'users.id = page_access_logs.user_id', 'left');
            
            $logModel->groupBy('user_id');
            $logModel->orderBy('total_visits', 'DESC');
            
            $data['logs'] = $logModel->paginate(50);
            $data['pager'] = $logModel->pager;
            $data['view_mode'] = 'top_users';

        } elseif ($selectedUrl) {
            // View 2: Detailed stats for a SPECIFIC page (Current Logs)
            $urlToSearch = $selectedUrl;
            
            $logModel->select('page_access_logs.*, users.username, users.player_name');
            $logModel->join('users', 'users.id = page_access_logs.user_id', 'left');
            $logModel->where('page_access_logs.url', $urlToSearch);
            
            if ($userId) {
                $logModel->where('page_access_logs.user_id', $userId);
            }
            
            $logModel->orderBy('page_access_logs.created_at', 'DESC');
            
            $data['logs'] = $logModel->paginate(50);
            $data['pager'] = $logModel->pager;
            
            $data['view_mode'] = 'page_detail';
            $data['selected_url'] = $urlToSearch;
            
        } else {
            // View 1: Grouped list of all pages (Current Logs)
            $logModel->select('url, COUNT(id) as visit_count, MAX(created_at) as last_visited');
            
            if ($userId) {
                $logModel->where('user_id', $userId);
            }
            
            $logModel->groupBy('url');
            $logModel->orderBy('visit_count', 'DESC');
            
            $data['logs'] = $logModel->paginate(50);
            $data['pager'] = $logModel->pager;
            
            $data['view_mode'] = 'page_list';
        }
        
        // Fetch users for the filter dropdown
        $data['users'] = $userModel->select('id, username, player_name')->orderBy('username', 'ASC')->findAll();

        return view('super_admin/access_logs', $data);
    }

    /**
     * Creates a summary snapshot of all logs and then truncates the live log table.
     * This is intended to be triggered via Cron, but provides a button for Super Admins to run manually.
     */
    public function generateLogSnapshot()
    {
        $db = \Config\Database::connect();
        $logModel = new PageAccessLogModel();
        $snapshotModel = new PageAccessLogSnapshotModel();

        // 1. Group the current live logs by User ID AND URL
        $builder = $db->table('page_access_logs');
        $builder->select('user_id, url, COUNT(id) as total_visits, MIN(created_at) as first_visit, MAX(created_at) as last_visit');
        $builder->groupBy('user_id, url');
        
        $results = $builder->get()->getResultArray();

        if (empty($results)) {
            return redirect()->back()->with('error', 'There are no logs to snapshot.');
        }

        // 2. Insert into the snapshots table
        $db->transStart();

        foreach ($results as $row) {
            $snapshotModel->insert([
                'user_id'     => $row['user_id'],
                'url'         => $row['url'],
                'visit_count' => $row['total_visits'],
                'start_date'  => $row['first_visit'],
                'end_date'    => $row['last_visit']
            ]);
        }

        // 3. Truncate the live log table
        $db->table('page_access_logs')->truncate();

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Failed to generate snapshot due to a database error.');
        }

        return redirect()->to('/super_admin/access-logs?view_type=snapshots')->with('success', 'Snapshot created successfully and live logs truncated.');
    }
}