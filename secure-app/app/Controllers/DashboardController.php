<?php

namespace App\Controllers;

use App\Models\UserModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $userModel = new UserModel();
        $db = \Config\Database::connect();
        
        $role = session()->get('role');

        $data = [
            'username'    => session()->get('username'),
            'player_name' => session()->get('player_name'),
            'role'        => $role
        ];

        // 1. Total Alliance Size (Approved users)
        $data['total_members'] = $userModel->where('status', 'approved')->countAllResults();

        // 2. Pending Approvals count (Only queried if admin/super_admin)
        $data['pending_approvals'] = 0;
        if ($role === 'admin' || $role === 'super_admin') {
            $data['pending_approvals'] = $userModel->where('status', 'pending')->countAllResults();
        }

        // 3. Top 10 Players by Overall Squad Power
        // We join users and user_stats, sum the powers, and sort descending
        // Ensure we pull tank, air, and missile individually for the tooltip!
        $builder = $db->table('users');
        $builder->select('
            users.username, 
            users.player_name, 
            users.alliance_level, 
            IFNULL(user_stats.power_tank, 0) as power_tank, 
            IFNULL(user_stats.power_air, 0) as power_air, 
            IFNULL(user_stats.power_missile, 0) as power_missile, 
            (IFNULL(user_stats.power_tank, 0) + IFNULL(user_stats.power_air, 0) + IFNULL(user_stats.power_missile, 0)) as total_power
        ');
        $builder->join('user_stats', 'user_stats.user_id = users.id', 'left');
        $builder->where('users.status', 'approved');
        $builder->orderBy('total_power', 'DESC');
        $builder->limit(10);
        
        $data['top_players'] = [];
        try {
            $data['top_players'] = $builder->get()->getResultArray();
        } catch (\Exception $e) {
            $data['top_players'] = [];
        }

        // 4. Fastest Growth Leaderboard (Last 30 Days - Total Power Gained)
        $thirtyDaysAgo = date('Y-m-d H:i:s', strtotime('-30 days'));

        $growthBuilder = $db->table('user_stats_history');
        $growthBuilder->select('
            users.username, 
            users.player_name, 
            users.alliance_level, 
            SUM(user_stats_history.tank_diff) as total_tank_diff,
            SUM(user_stats_history.air_diff) as total_air_diff,
            SUM(user_stats_history.missile_diff) as total_missile_diff,
            SUM(user_stats_history.tank_diff + user_stats_history.air_diff + user_stats_history.missile_diff) as total_growth
        ');
        $growthBuilder->join('users', 'users.id = user_stats_history.user_id');
        $growthBuilder->where('users.status', 'approved');
        $growthBuilder->where('user_stats_history.created_at >=', $thirtyDaysAgo);
        
        $growthBuilder->groupBy('users.id, users.username, users.player_name, users.alliance_level');
        
        // We only want users who actually gained power overall in the last 30 days
        $growthBuilder->having('total_growth >', 0);
        
        // Sort strictly by the total power gained
        $growthBuilder->orderBy('total_growth', 'DESC');
        $growthBuilder->limit(10);

        $data['growth_leaders'] = [];
        try {
            $data['growth_leaders'] = $growthBuilder->get()->getResultArray();
        } catch (\Exception $e) {
            $data['growth_leaders'] = [];
        }

        return view('dashboard', $data);
    }

    public function growthAnalytics()
    {
        $db = \Config\Database::connect();
        $userModel = new UserModel();
        
        $userIdFilter = $this->request->getGet('user_id');
        $daysLimit = (int)$this->request->getGet('days_limit');
        if (!isset($_GET['days_limit'])) {
            $daysLimit = 30; // Default to last 30 days
        }

        $data['users_dropdown'] = $userModel->where('status', 'approved')->orderBy('player_name', 'ASC')->findAll();
        $data['selected_user'] = $userIdFilter;
        $data['days_limit'] = $daysLimit;

        $histBuilder = $db->table('user_stats_history');
        $histBuilder->select('
            user_stats_history.*, 
            users.username, 
            users.player_name, 
            users.alliance_level,
            (user_stats_history.tank_diff + user_stats_history.air_diff + user_stats_history.missile_diff) as total_growth,
            (user_stats_history.power_tank + user_stats_history.power_air + user_stats_history.power_missile) as total_power
        ');
        $histBuilder->join('users', 'users.id = user_stats_history.user_id');
        $histBuilder->where('users.status', 'approved');
        
        if ($daysLimit > 0) {
            $dateLimit = date('Y-m-d H:i:s', strtotime("-{$daysLimit} days"));
            $histBuilder->where('user_stats_history.created_at >=', $dateLimit);
        }

        if (!empty($userIdFilter)) {
            $histBuilder->where('user_stats_history.user_id', $userIdFilter);
        }

        $histBuilder->orderBy('user_stats_history.created_at', 'DESC');
        
        $data['growth_history'] = [];
        try {
            $data['growth_history'] = $histBuilder->get()->getResultArray();
        } catch (\Exception $e) {}

        // Calculate Alliance Totals (similar to Admin view)
        $builder = $db->table('users');
        $builder->select('
            SUM(IFNULL(user_stats.power_tank, 0)) as total_tank,
            SUM(IFNULL(user_stats.power_air, 0)) as total_air,
            SUM(IFNULL(user_stats.power_missile, 0)) as total_missile,
            SUM(IFNULL(user_stats.power_tank, 0) + IFNULL(user_stats.power_air, 0) + IFNULL(user_stats.power_missile, 0)) as total_overall
        ');
        $builder->join('user_stats', 'user_stats.user_id = users.id', 'left');
        $builder->where('users.status', 'approved');
        
        $totalsRow = [];
        try {
            $totalsRow = $builder->get()->getRowArray();
        } catch (\Exception $e) {}
        
        $data['alliance_totals'] = [
            'tank'    => $totalsRow['total_tank'] ?? 0,
            'air'     => $totalsRow['total_air'] ?? 0,
            'missile' => $totalsRow['total_missile'] ?? 0,
            'overall' => $totalsRow['total_overall'] ?? 0,
        ];

        return view('growth_analytics', $data);
    }
}
