<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\UserStatModel;
use App\Models\UserStatsHistoryModel;

class AdminController extends BaseController
{
    public function settings()
    {
        return view('admin/settings');
    }

    public function approvals()
    {
        $userModel = new UserModel();
        
        // Fetch users that are pending approval
        $data['pending_users'] = $userModel->where('status', 'pending')->findAll();
        
        return view('admin/approvals', $data);
    }


    public function updateApprovalStatus()
    {
        $userModel = new UserModel();
        $userStatModel = new UserStatModel();
        
        $userId = $this->request->getPost('user_id');
        $action = $this->request->getPost('action'); // 'approve' or 'reject'

        if (!$userId || !in_array($action, ['approve', 'reject'])) {
            return redirect()->back()->with('error', 'Invalid action.');
        }

        $newStatus = ($action === 'approve') ? 'approved' : 'rejected';

        // Fetch user data before updating
        $user = $userModel->find($userId);

        // Update the user's status
        $userModel->update($userId, ['status' => $newStatus]);

        // Ensure stats exist if approved via the queue
        if ($newStatus === 'approved') {
            if (!$userStatModel->find($userId)) {
                $userStatModel->insert([
                    'user_id'       => $userId,
                    'power_tank'    => 0,
                    'power_air'     => 0,
                    'power_missile' => 0,
                ]);
            }
        }

        // Send Email Notification (Filtered for @mdk.com)
        if ($user && !empty($user['email'])) {
            if (!str_ends_with(strtolower($user['email']), '@mdk.com')) {
                try {
                    $email = \Config\Services::email();
                    $email->setTo($user['email']);
                    
                    $displayName = !empty($user['player_name']) ? $user['player_name'] : $user['username'];
                    
                    if ($newStatus === 'approved') {
                        $email->setSubject('Account Approved - MDK Alliance');
                        $message = "
                        <html>
                        <body style='font-family: Arial, sans-serif; color: #333; line-height: 1.6;'>
                            <h2 style='color: #10b981;'>Account Approved!</h2>
                            <p>Hello {$displayName},</p>
                            <p>Your MDK Alliance account has been reviewed and <strong>approved</strong> by an administrator.</p>
                            <p>You can now log in to access the tactical planners, leaderboards, and update your squad stats.</p>
                            <br>
                            <p><a href='https://ace.h1x.com/login' style='background: #d97706; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Login Now</a></p>
                            <br>
                            <p>Best regards,<br><strong>The MDK Command Team</strong></p>
                        </body>
                        </html>
                        ";
                    } else {
                        $email->setSubject('Account Update - MDK Alliance');
                        $message = "
                        <html>
                        <body style='font-family: Arial, sans-serif; color: #333; line-height: 1.6;'>
                            <h2 style='color: #ef4444;'>Account Application Update</h2>
                            <p>Hello {$displayName},</p>
                            <p>Unfortunately, your application for the MDK Alliance portal has been <strong>declined</strong> by an administrator at this time.</p>
                            <br>
                            <p>Best regards,<br><strong>The MDK Command Team</strong></p>
                        </body>
                        </html>
                        ";
                    }
                    
                    $email->setMessage($message);
                    
                    if (!$email->send()) {
                        log_message('error', 'Failed to send approval/rejection email to ' . $user['email'] . '. Error: ' . $email->printDebugger(['headers']));
                    }
                } catch (\Exception $e) {
                    log_message('error', 'Exception caught while sending approval email: ' . $e->getMessage());
                }
            } else {
                log_message('info', 'Suppressed notification email for @mdk.com user: ' . $user['email']);
            }
        }

        $message = ($newStatus === 'approved') ? 'User has been approved!' : 'User application rejected.';
        return redirect()->back()->with('success', $message);
    }

    public function users()
    {
        $userModel = new UserModel();
        
        $search = $this->request->getGet('search');
        
        if (!empty($search)) {
            $userModel->groupStart()
                      ->like('username', $search)
                      ->orLike('player_name', $search)
                      ->orLike('email', $search)
                      ->groupEnd();
        }

        // Fetch all users who are not pending (approved or rejected)
        $data['users'] = $userModel->where('status !=', 'pending')->findAll();
        $data['search'] = $search;
        
        return view('admin/users', $data);
    }

    public function addMemberView()
    {
        return view('admin/add_member');
    }

    public function addUser()
    {
        $userModel = new UserModel();
        $userStatModel = new UserStatModel();

        // Get form inputs
        $data = [
            'username'       => $this->request->getPost('username'),
            'player_name'    => $this->request->getPost('player_name'),
            'email'          => $this->request->getPost('email'),
            'alliance_level' => $this->request->getPost('alliance_level'),
            'password'       => $this->request->getPost('password'),
            'role'           => $this->request->getPost('role'),
            'status'         => 'approved' // Automatically approved when created by admin
        ];

        // Hash the password if provided
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        // Attempt to save the user
        $userId = $userModel->insert($data);
        if ($userId) {
            // Initialize empty stats
            $userStatModel->insert([
                'user_id'       => $userId,
                'power_tank'    => 0,
                'power_air'     => 0,
                'power_missile' => 0,
            ]);
            return redirect()->to('/admin/users')->with('success', 'New member added to the roster successfully!');
        } else {
            // Validation failed
            return redirect()->back()->withInput()->with('errors', $userModel->errors());
        }
    }

    public function editUser($id)
    {
        $userModel = new UserModel();
        $user = $userModel->find($id);

        if (!$user) {
            return redirect()->to('/admin/users')->with('error', 'User not found.');
        }

        // Security check: Only super_admin can edit another super_admin or demote them
        if ($user['role'] === 'super_admin' && session()->get('role') !== 'super_admin') {
            return redirect()->to('/admin/users')->with('error', 'You do not have permission to edit a Super Administrator.');
        }

        $data['user'] = $user;
        return view('admin/edit_user', $data);
    }

    public function updateUser()
    {
        $userModel = new UserModel();
        $userId = $this->request->getPost('id');

        if (!$userId) {
            return redirect()->to('/admin/users')->with('error', 'User ID is missing.');
        }

        // Fetch current record to check permissions
        $existingUser = $userModel->find($userId);
        if (!$existingUser) {
            return redirect()->to('/admin/users')->with('error', 'User not found.');
        }

        // Security check: Only super_admin can modify a super_admin or elevate someone to super_admin
        $isSuperAdmin = (session()->get('role') === 'super_admin');
        $newRole = $this->request->getPost('role');

        if (($existingUser['role'] === 'super_admin' || $newRole === 'super_admin') && !$isSuperAdmin) {
            return redirect()->back()->with('error', 'Only Super Administrators can manage Super Admin roles.');
        }

        $data = [
            'id'             => $userId,
            'username'       => $this->request->getPost('username'),
            'player_name'    => $this->request->getPost('player_name'),
            'email'          => $this->request->getPost('email'),
            'alliance_level' => $this->request->getPost('alliance_level'),
            'role'           => $newRole,
            'status'         => $this->request->getPost('status'),
        ];

        // Only update password if provided
        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }

        try {
            if ($userModel->save($data)) {
                return redirect()->to('/admin/users')->with('success', 'User updated successfully.');
            } else {
                return redirect()->back()->withInput()->with('errors', $userModel->errors());
            }
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Database error: ' . $e->getMessage());
        }
    }

    public function importUsers()
    {
        $userModel = new UserModel();
        $userStatModel = new UserStatModel();
        $file = $this->request->getFile('csv_file');

        if (!$file->isValid() || $file->getExtension() !== 'csv') {
            return redirect()->back()->with('error', 'Please upload a valid CSV file.');
        }

        $filepath = $file->getTempName();
        $csvData = array_map('str_getcsv', file($filepath));

        // Assuming header row: username, player_name, email, alliance_level, role, password
        $header = array_shift($csvData);
        $successCount = 0;
        $failCount = 0;

        foreach ($csvData as $row) {
            // Very basic validation mapping
            if (count($row) >= 6) {
                $role = trim($row[4]);
                
                // Security: Don't allow CSV import to create super_admins unless the current user is one
                if ($role === 'super_admin' && session()->get('role') !== 'super_admin') {
                    $role = 'admin';
                }

                $data = [
                    'username'       => trim($row[0]),
                    'player_name'    => trim($row[1]),
                    'email'          => trim($row[2]),
                    'alliance_level' => trim($row[3]),
                    'role'           => $role,
                    'password'       => password_hash(trim($row[5]), PASSWORD_DEFAULT),
                    'status'         => 'approved'
                ];

                // Check for existing user/email
                if (!$userModel->where('username', $data['username'])->orWhere('email', $data['email'])->first()) {
                    $userId = $userModel->insert($data);
                    if ($userId) {
                        // Init Stats
                        $userStatModel->insert([
                            'user_id'       => $userId,
                            'power_tank'    => 0,
                            'power_air'     => 0,
                            'power_missile' => 0,
                        ]);
                        $successCount++;
                    } else {
                        $failCount++;
                    }
                } else {
                    $failCount++;
                }
            }
        }

        return redirect()->to('/admin/users')->with('success', "Import completed. {$successCount} added, {$failCount} skipped/failed.");
    }

    public function deleteUser()
    {
        $userModel = new UserModel();
        $userId = $this->request->getPost('user_id');

        // Prevent admin from deleting themselves
        if ($userId == session()->get('id')) {
            return redirect()->back()->with('error', 'You cannot delete your own active account.');
        }

        // Security check
        $targetUser = $userModel->find($userId);
        if ($targetUser && $targetUser['role'] === 'super_admin' && session()->get('role') !== 'super_admin') {
            return redirect()->back()->with('error', 'Only Super Administrators can delete other Super Admins.');
        }

        if ($userModel->delete($userId)) {
            return redirect()->back()->with('success', 'User permanently removed from the alliance.');
        }

        return redirect()->back()->with('error', 'Failed to remove user.');
    }

    // View & Administer everyone's Squad Powers
    public function squadPowers()
    {
        $db = \Config\Database::connect();
        
        $sortBy = $this->request->getGet('sort') ?: 'username';
        $order = $this->request->getGet('order') ?: 'ASC';

        // Fetch all approved users along with their stats
        $builder = $db->table('users');
        
        // We use an IFNULL logic to sum up the values so the SQL query handles the Total Power calculation correctly
        $builder->select('
            users.id, 
            users.username, 
            users.player_name, 
            users.alliance_level, 
            user_stats.power_tank, 
            user_stats.power_air, 
            user_stats.power_missile,
            (IFNULL(user_stats.power_tank, 0) + IFNULL(user_stats.power_air, 0) + IFNULL(user_stats.power_missile, 0)) as total_power
        ');
        $builder->join('user_stats', 'user_stats.user_id = users.id', 'left');
        $builder->where('users.status', 'approved');
        
        // Sorting logic
        if (in_array($sortBy, ['power_tank', 'power_air', 'power_missile', 'total_power'])) {
            // For calculated or joined columns
            if ($sortBy === 'total_power') {
                $builder->orderBy('total_power', $order);
            } else {
                $builder->orderBy('user_stats.' . $sortBy, $order);
            }
        } else {
            $builder->orderBy('users.' . $sortBy, $order);
        }
        
        $data['users_stats'] = $builder->get()->getResultArray();
        $data['current_sort'] = $sortBy;
        $data['current_order'] = $order;
        
        return view('admin/squad_powers', $data);
    }

    // Squad Powers Growth Analytics
    public function squadGrowth()
    {
        $historyModel = new UserStatsHistoryModel();
        
        // Filter params
        $daysLimit = (int)$this->request->getGet('days_limit');
        if (!isset($_GET['days_limit'])) {
            $daysLimit = 30; // Default to last 30 days
        }
        $usernameFilter = $this->request->getGet('username_filter');

        $db = \Config\Database::connect();
        
        // 1. Fetch Alliance Totals
        // Calculate the current overall alliance power
        $builder = $db->table('users');
        $builder->select('
            SUM(IFNULL(user_stats.power_tank, 0)) as total_tank,
            SUM(IFNULL(user_stats.power_air, 0)) as total_air,
            SUM(IFNULL(user_stats.power_missile, 0)) as total_missile,
            SUM(IFNULL(user_stats.power_tank, 0) + IFNULL(user_stats.power_air, 0) + IFNULL(user_stats.power_missile, 0)) as total_overall
        ');
        $builder->join('user_stats', 'user_stats.user_id = users.id', 'left');
        $builder->where('users.status', 'approved');
        $totalsRow = $builder->get()->getRowArray();
        
        $data['alliance_totals'] = [
            'tank'    => $totalsRow['total_tank'] ?? 0,
            'air'     => $totalsRow['total_air'] ?? 0,
            'missile' => $totalsRow['total_missile'] ?? 0,
            'overall' => $totalsRow['total_overall'] ?? 0,
        ];

        // 2. Fetch Growth History
        $histBuilder = $db->table('user_stats_history');
        $histBuilder->select('user_stats_history.*, users.username, users.player_name, users.alliance_level');
        $histBuilder->join('users', 'users.id = user_stats_history.user_id');
        
        if ($daysLimit > 0) {
            $dateLimit = date('Y-m-d H:i:s', strtotime("-{$daysLimit} days"));
            $histBuilder->where('user_stats_history.created_at >=', $dateLimit);
        }
        
        if (!empty($usernameFilter)) {
            $histBuilder->groupStart();
            $histBuilder->like('users.username', $usernameFilter);
            $histBuilder->orLike('users.player_name', $usernameFilter);
            $histBuilder->groupEnd();
        }
        
        $histBuilder->orderBy('user_stats_history.created_at', 'DESC');
        
        $data['growth_history'] = $histBuilder->get()->getResultArray();
        $data['days_limit'] = $daysLimit;
        $data['username_filter'] = $usernameFilter;
        
        return view('admin/squad_powers_growth', $data);
    }

    // Helper method to handle history tracking centrally
    private function savePowerWithHistory($userId, $newTank, $newAir, $newMissile)
    {
        $statModel = new UserStatModel();
        $historyModel = new UserStatsHistoryModel();
        
        $existing = $statModel->find($userId);

        $oldTank = isset($existing['power_tank']) ? (float)$existing['power_tank'] : 0.00;
        $oldAir = isset($existing['power_air']) ? (float)$existing['power_air'] : 0.00;
        $oldMissile = isset($existing['power_missile']) ? (float)$existing['power_missile'] : 0.00;

        // Only log if something actually changed
        if ($oldTank != $newTank || $oldAir != $newAir || $oldMissile != $newMissile) {
            
            // Calculate Days Since Last Update
            $daysSinceLast = 0;
            if ($existing && !empty($existing['updated_at'])) {
                $lastDate = new \DateTime($existing['updated_at']);
                $now = new \DateTime();
                $interval = $lastDate->diff($now);
                $daysSinceLast = $interval->days;
            }

            // Calculate Differences
            $tankDiff = $newTank - $oldTank;
            $airDiff = $newAir - $oldAir;
            $missileDiff = $newMissile - $oldMissile;

            // Log it in history table
            $historyModel->insert([
                'user_id'         => $userId,
                'power_tank'      => $newTank,
                'power_air'       => $newAir,
                'power_missile'   => $newMissile,
                'tank_diff'       => $tankDiff,
                'air_diff'        => $airDiff,
                'missile_diff'    => $missileDiff,
                'days_since_last' => $daysSinceLast
            ]);
        }

        // Save the actual current stat
        return $statModel->save([
            'user_id'       => $userId,
            'power_tank'    => $newTank,
            'power_air'     => $newAir,
            'power_missile' => $newMissile,
        ]);
    }

    // Update a specific user's Squad Powers (Single row save)
    public function updateSquadPower()
    {
        $userId = $this->request->getPost('user_id');
        $newTank = (float)$this->request->getPost('power_tank');
        $newAir = (float)$this->request->getPost('power_air');
        $newMissile = (float)$this->request->getPost('power_missile');

        if ($this->savePowerWithHistory($userId, $newTank, $newAir, $newMissile)) {
            return redirect()->back()->with('success', 'User powers updated successfully!');
        }
        
        return redirect()->back()->with('error', 'Failed to update user powers.');
    }

    /**
     * Bulk update squad powers (Save All Changes)
     */
    public function bulkUpdateSquadPowers()
    {
        $statsData = $this->request->getPost('stats');

        if (!is_array($statsData)) {
            return redirect()->back()->with('error', 'No data provided.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        foreach ($statsData as $userId => $powers) {
            $this->savePowerWithHistory(
                $userId, 
                (float)$powers['power_tank'], 
                (float)$powers['power_air'], 
                (float)$powers['power_missile']
            );
        }

        $db->transComplete();

        if ($db->transStatus() === FALSE) {
            return redirect()->back()->with('error', 'Database error occurred during bulk update.');
        }

        return redirect()->back()->with('success', 'Combat roster updated successfully.');
    }

    /**
     * Export Squad Powers to CSV
     */
    public function exportSquadPowers()
    {
        $db = \Config\Database::connect();
        
        $builder = $db->table('users');
        $builder->select('users.username, user_stats.power_tank, user_stats.power_air, user_stats.power_missile');
        $builder->join('user_stats', 'user_stats.user_id = users.id', 'left');
        $builder->where('users.status', 'approved');
        $users = $builder->get()->getResultArray();

        $filename = 'squad_powers_export_' . date('Y-m-d') . '.csv';
        
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$filename");
        header("Content-Type: application/csv; "); 
        
        $file = fopen('php://output', 'w');
        
        // Write CSV Header
        fputcsv($file, ['username', 'power_tank', 'power_air', 'power_missile']);
        
        foreach ($users as $u) {
            fputcsv($file, [
                $u['username'],
                $u['power_tank'] ?? 0.00,
                $u['power_air'] ?? 0.00,
                $u['power_missile'] ?? 0.00
            ]);
        }
        
        fclose($file);
        exit;
    }

    /**
     * Import Squad Powers from CSV
     */
    public function importSquadPowers()
    {
        $userModel = new UserModel();
        
        $file = $this->request->getFile('csv_file');

        if (!$file->isValid() || $file->getExtension() !== 'csv') {
            return redirect()->back()->with('error', 'Please upload a valid CSV file.');
        }

        $filepath = $file->getTempName();
        $csvData = array_map('str_getcsv', file($filepath));
        
        // Remove Header row
        array_shift($csvData);
        
        $successCount = 0;
        $failCount = 0;

        $db = \Config\Database::connect();
        $db->transStart();

        foreach ($csvData as $row) {
            if (count($row) >= 4) {
                $username = trim($row[0]);
                
                // Find user by username
                $user = $userModel->where('username', $username)->first();
                
                if ($user) {
                    // Use our new centralized tracking logic for imports too!
                    $this->savePowerWithHistory(
                        $user['id'],
                        (float)trim($row[1]),
                        (float)trim($row[2]),
                        (float)trim($row[3])
                    );
                    $successCount++;
                } else {
                    $failCount++;
                }
            }
        }

        $db->transComplete();

        if ($db->transStatus() === FALSE) {
            return redirect()->back()->with('error', 'Database error occurred during import.');
        }

        return redirect()->back()->with('success', "Import complete: {$successCount} players updated. {$failCount} skipped (user not found).");
    }
}