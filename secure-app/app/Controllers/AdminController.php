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

        if (!$user) {
            return redirect()->back()->with('error', 'User not found.');
        }

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
        if (!empty($user['email'])) {
            if (!str_ends_with(strtolower($user['email']), '@mdk.com')) {
                try {
                    $email = \Config\Services::email();
                    $email->setTo($user['email']);
                    
                    $displayName = esc(!empty($user['player_name']) ? $user['player_name'] : $user['username']);
                    $loginUrl = base_url('/login');
                    
                    if ($newStatus === 'approved') {
                        $email->setSubject('Account Approved - TKU Alliance');
                        $message = "
                        <html>
                        <body style='font-family: Arial, sans-serif; color: #333; line-height: 1.6;'>
                            <h2 style='color: #10b981;'>Account Approved!</h2>
                            <p>Hello {$displayName},</p>
                            <p>Your TKU Alliance account has been reviewed and <strong>approved</strong> by an administrator.</p>
                            <p>You can now log in to access the tactical planners, leaderboards, and update your squad stats.</p>
                            <br>
                            <p><a href='{$loginUrl}' style='background: #d97706; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Login Now</a></p>
                            <br>
                            <p>Best regards,<br><strong>The MDK Command Team</strong></p>
                        </body>
                        </html>
                        ";
                    } else {
                        $email->setSubject('Account Update - TKU Alliance');
                        $message = "
                        <html>
                        <body style='font-family: Arial, sans-serif; color: #333; line-height: 1.6;'>
                            <h2 style='color: #ef4444;'>Account Application Update</h2>
                            <p>Hello {$displayName},</p>
                            <p>Unfortunately, your application for the TKU Alliance portal has been <strong>declined</strong> by an administrator at this time.</p>
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

        $role = $this->request->getPost('role') ?: 'user';
        // Security check: Only super_admin can create another super_admin
        if ($role === 'super_admin' && session()->get('role') !== 'super_admin') {
            $role = 'user';
        }

        $rawPassword = (string)$this->request->getPost('password');
        if (strlen($rawPassword) < 8) {
            return redirect()->back()->withInput()->with('error', 'Password must be at least 8 characters long.');
        }

        // Get form inputs
        $data = [
            'username'       => $this->request->getPost('username'),
            'player_name'    => $this->request->getPost('player_name'),
            'email'          => $this->request->getPost('email'),
            'alliance_level' => $this->request->getPost('alliance_level'),
            'password'       => password_hash($rawPassword, PASSWORD_DEFAULT),
            'role'           => $role,
            'status'         => 'approved' // Automatically approved when created by admin
        ];

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

        $currentLoggedInId = session()->get('id');
        $newRole = $this->request->getPost('role');
        $newStatus = $this->request->getPost('status');

        // Prevent self-lockout: An admin cannot demote themselves or reject their own status
        if ($userId == $currentLoggedInId) {
            if ($newStatus !== 'approved') {
                return redirect()->back()->with('error', 'You cannot change your own active status.');
            }
            if ($existingUser['role'] === 'super_admin' && $newRole !== 'super_admin') {
                return redirect()->back()->with('error', 'You cannot demote your own Super Admin role.');
            }
        }

        // Security check: Only super_admin can modify a super_admin or elevate someone to super_admin
        $isSuperAdmin = (session()->get('role') === 'super_admin');

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
            'status'         => $newStatus,
        ];

        // Only update password if provided
        $password = (string)$this->request->getPost('password');
        if (!empty($password)) {
            if (strlen($password) < 8) {
                return redirect()->back()->withInput()->with('error', 'Password must be at least 8 characters long.');
            }
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

        if (!$file || !$file->isValid() || strtolower($file->getClientExtension()) !== 'csv') {
            return redirect()->back()->with('error', 'Please upload a valid CSV file.');
        }

        $filepath = $file->getTempName();
        $handle = fopen($filepath, 'r');
        if ($handle === false) {
            return redirect()->back()->with('error', 'Unable to open the uploaded CSV file.');
        }

        // Read header row: username, player_name, email, alliance_level, role, password
        $header = fgetcsv($handle);
        $successCount = 0;
        $failCount = 0;

        while (($row = fgetcsv($handle)) !== false) {
            // Skip empty rows
            if (empty($row) || (count($row) === 1 && $row[0] === null)) {
                continue;
            }

            // Very basic validation mapping
            if (count($row) >= 6) {
                $username = trim($row[0]);
                $playerName = trim($row[1]);
                $email = trim($row[2]);
                $allianceLevel = trim($row[3]);
                $role = trim($row[4]);
                $rawPassword = trim($row[5]);

                if (empty($username) || empty($email) || empty($rawPassword)) {
                    $failCount++;
                    continue;
                }
                
                // Security: Don't allow CSV import to create super_admins unless the current user is one
                if ($role === 'super_admin' && session()->get('role') !== 'super_admin') {
                    $role = 'admin';
                }

                $data = [
                    'username'       => $username,
                    'player_name'    => $playerName,
                    'email'          => $email,
                    'alliance_level' => $allianceLevel,
                    'role'           => $role,
                    'password'       => password_hash($rawPassword, PASSWORD_DEFAULT),
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
            } else {
                $failCount++;
            }
        }
        fclose($handle);

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
        $rawOrder = strtoupper((string)$this->request->getGet('order'));
        $order = ($rawOrder === 'DESC') ? 'DESC' : 'ASC';

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
        
        // Whitelist allowed sort columns to prevent SQL injection or invalid column errors
        $allowedStatsCols = ['power_tank', 'power_air', 'power_missile', 'total_power'];
        $allowedUserCols = ['username', 'player_name', 'alliance_level', 'id'];

        if (in_array($sortBy, $allowedStatsCols)) {
            if ($sortBy === 'total_power') {
                $builder->orderBy('total_power', $order);
            } else {
                $builder->orderBy('user_stats.' . $sortBy, $order);
            }
        } elseif (in_array($sortBy, $allowedUserCols)) {
            $builder->orderBy('users.' . $sortBy, $order);
        } else {
            $sortBy = 'username';
            $builder->orderBy('users.username', $order);
        }
        
        $data['users_stats'] = $builder->get()->getResultArray();
        $data['current_sort'] = $sortBy;
        $data['current_order'] = $order;
        
        return view('admin/squad_powers', $data);
    }

    // Squad Powers Growth Analytics
    public function squadGrowth()
    {
        // Filter params
        $daysParam = $this->request->getGet('days_limit');
        $daysLimit = ($daysParam !== null) ? max(0, (int)$daysParam) : 30; // Default to last 30 days
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
        $histBuilder->where('users.status', 'approved');
        
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

        // Save the actual current stat first
        $saved = $statModel->save([
            'user_id'       => $userId,
            'power_tank'    => $newTank,
            'power_air'     => $newAir,
            'power_missile' => $newMissile,
        ]);

        if (!$saved) {
            return false;
        }

        // Only log history if save succeeded and values changed
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

        return true;
    }

    // Update a specific user's Squad Powers (Single row save)
    public function updateSquadPower()
    {
        $userId = $this->request->getPost('user_id');
        $newTank = (float)$this->request->getPost('power_tank');
        $newAir = (float)$this->request->getPost('power_air');
        $newMissile = (float)$this->request->getPost('power_missile');

        $isAjax = $this->request->isAJAX();

        if (!$userId) {
            if ($isAjax) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status'  => 'error',
                    'message' => 'User ID is required.'
                ]);
            }
            return redirect()->back()->with('error', 'User ID is required.');
        }

        try {
            $success = $this->savePowerWithHistory($userId, $newTank, $newAir, $newMissile);
            if ($success) {
                if ($isAjax) {
                    return $this->response->setJSON([
                        'status'  => 'success',
                        'message' => 'User powers updated successfully!'
                    ]);
                }
                return redirect()->back()->with('success', 'User powers updated successfully!');
            }
        } catch (\Exception $e) {
            log_message('error', 'Error updating squad power: ' . $e->getMessage());
        }

        if ($isAjax) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Failed to update user powers.'
            ]);
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

        $allSucceeded = true;
        foreach ($statsData as $userId => $powers) {
            if (!is_array($powers)) {
                continue;
            }

            $tank = isset($powers['power_tank']) ? (float)$powers['power_tank'] : 0.0;
            $air = isset($powers['power_air']) ? (float)$powers['power_air'] : 0.0;
            $missile = isset($powers['power_missile']) ? (float)$powers['power_missile'] : 0.0;

            if (!$this->savePowerWithHistory($userId, $tank, $air, $missile)) {
                $allSucceeded = false;
                break;
            }
        }

        if (!$allSucceeded) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Failed to update some user powers. Changes were rolled back.');
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
        
        $buffer = fopen('php://temp', 'w+');
        // Write CSV Header
        fputcsv($buffer, ['username', 'power_tank', 'power_air', 'power_missile']);
        
        foreach ($users as $u) {
            fputcsv($buffer, [
                $u['username'],
                $u['power_tank'] ?? 0.00,
                $u['power_air'] ?? 0.00,
                $u['power_missile'] ?? 0.00
            ]);
        }
        
        rewind($buffer);
        $csvContent = stream_get_contents($buffer);
        fclose($buffer);

        return $this->response
            ->setHeader('Content-Type', 'text/csv')
            ->download($filename, $csvContent);
    }

    /**
     * Import Squad Powers from CSV
     */
    public function importSquadPowers()
    {
        $userModel = new UserModel();
        
        $file = $this->request->getFile('csv_file');

        if (!$file || !$file->isValid() || strtolower($file->getClientExtension()) !== 'csv') {
            return redirect()->back()->with('error', 'Please upload a valid CSV file.');
        }

        $filepath = $file->getTempName();
        $handle = fopen($filepath, 'r');
        if ($handle === false) {
            return redirect()->back()->with('error', 'Unable to open the uploaded CSV file.');
        }

        // Remove Header row
        fgetcsv($handle);
        
        $successCount = 0;
        $failCount = 0;

        $db = \Config\Database::connect();
        $db->transStart();

        while (($row = fgetcsv($handle)) !== false) {
            // Skip empty rows
            if (empty($row) || (count($row) === 1 && $row[0] === null)) {
                continue;
            }

            if (count($row) >= 4) {
                $username = trim($row[0]);

                if (empty($username) || !is_numeric(trim($row[1])) || !is_numeric(trim($row[2])) || !is_numeric(trim($row[3]))) {
                    $failCount++;
                    continue;
                }
                
                // Find user by username
                $user = $userModel->where('username', $username)->first();
                
                if ($user) {
                    // Use our centralized tracking logic for imports
                    $saved = $this->savePowerWithHistory(
                        $user['id'],
                        (float)trim($row[1]),
                        (float)trim($row[2]),
                        (float)trim($row[3])
                    );
                    if ($saved) {
                        $successCount++;
                    } else {
                        $failCount++;
                    }
                } else {
                    $failCount++;
                }
            } else {
                $failCount++;
            }
        }
        fclose($handle);

        $db->transComplete();

        if ($db->transStatus() === FALSE) {
            return redirect()->back()->with('error', 'Database error occurred during import.');
        }

        return redirect()->to('/admin/users/squad_powers')->with('success', "Import complete: {$successCount} players updated. {$failCount} skipped/failed.");
    }
}