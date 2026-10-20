<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\WheelAllowedSpinnerModel;
use App\Models\WheelSpinModel;

class WheelController extends BaseController
{
    /**
     * Check if current user is R4, R5, or super_admin
     */
    protected function canManage(): bool
    {
        $role = session()->get('role');
        $level = session()->get('alliance_level');
        return ($role === 'super_admin' || in_array($level, ['R4', 'R5']));
    }

    /**
     * Check if current user is allowed to spin the wheel
     * (R4, R5, super_admin, or explicitly granted in wheel_allowed_spinners)
     */
    protected function canSpin(): bool
    {
        if ($this->canManage()) {
            return true;
        }

        $userId = (int) session()->get('id');
        if (!$userId) {
            return false;
        }

        $spinnerModel = new WheelAllowedSpinnerModel();
        return $spinnerModel->isAllowed($userId);
    }

    /**
     * Display the Wheel of Fortune page
     */
    public function index()
    {
        $userModel = new UserModel();
        $spinnerModel = new WheelAllowedSpinnerModel();
        $spinModel = new WheelSpinModel();

        $canManage = $this->canManage();
        $canSpin   = $this->canSpin();

        // 1. Fetch all approved alliance members
        $allMembers = $userModel->where('status', 'approved')
            ->select('id, username, player_name, alliance_level, role')
            ->orderBy('alliance_level', 'DESC')
            ->orderBy('player_name', 'ASC')
            ->orderBy('username', 'ASC')
            ->findAll();

        // Normalize display name for each member
        $members = array_map(function ($m) {
            $displayName = !empty($m['player_name']) ? trim($m['player_name']) : trim($m['username']);
            return [
                'id'             => (int) $m['id'],
                'username'       => $m['username'],
                'player_name'    => $m['player_name'],
                'display_name'   => $displayName,
                'alliance_level' => $m['alliance_level'] ?? 'R1',
                'role'           => $m['role'] ?? 'user',
            ];
        }, $allMembers);

        // 2. Fetch allowed spinners (delegated users)
        $allowedSpinners = $spinnerModel->getAllowedSpinnersWithDetails();

        // Build a set of delegated user IDs for quick lookup in UI
        $delegatedUserIds = array_column($allowedSpinners, 'user_id');

        // 3. Fetch recent spin history
        $recentSpins = $spinModel->getRecentSpins(30);

        $data = [
            'title'            => 'Wheel of Fortune',
            'canManage'        => $canManage,
            'canSpin'          => $canSpin,
            'members'          => $members,
            'allowedSpinners'  => $allowedSpinners,
            'delegatedUserIds' => $delegatedUserIds,
            'recentSpins'      => $recentSpins,
            'currentUserId'    => (int) session()->get('id'),
            'currentUserName'  => session()->get('player_name') ?: session()->get('username'),
            'currentUserRole'  => session()->get('role'),
            'currentUserLevel' => session()->get('alliance_level'),
        ];

        return view('wheel/index', $data);
    }

    /**
     * Record a completed spin in history
     * POST /wheel/record-spin
     */
    public function recordSpin()
    {
        if (!$this->canSpin()) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Unauthorized: Only R4/R5, Super Admin, or authorized spinners can spin the wheel.'
            ]);
        }

        $rawInput = $this->request->getJSON(true) ?: $this->request->getPost();
        $winnerName = trim($rawInput['winner_name'] ?? '');
        $winnerUserId = !empty($rawInput['winner_user_id']) ? (int) $rawInput['winner_user_id'] : null;
        $title = trim($rawInput['title'] ?? 'Alliance Raffle');
        $participantsCount = (int) ($rawInput['participants_count'] ?? 0);

        if (empty($winnerName)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Winner name is required.'
            ]);
        }

        $spinModel = new WheelSpinModel();
        $spunById = (int) session()->get('id');
        $spunByName = session()->get('player_name') ?: session()->get('username');

        $record = [
            'winner_name'        => mb_substr($winnerName, 0, 100),
            'winner_user_id'     => $winnerUserId,
            'spun_by_user_id'    => $spunById,
            'spun_by_name'       => $spunByName,
            'title'              => mb_substr($title, 0, 100),
            'participants_count' => $participantsCount,
            'created_at'         => date('Y-m-d H:i:s'),
        ];

        $insertedId = $spinModel->insert($record);

        return $this->response->setJSON([
            'success' => true,
            'id'      => $insertedId,
            'record'  => $record
        ]);
    }

    /**
     * Add a user to allowed spinners list
     * POST /wheel/add-spinner
     */
    public function addSpinner()
    {
        if (!$this->canManage()) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Unauthorized']);
            }
            return redirect()->back()->with('error', 'Only R4, R5, or Super Admin can delegate spinners.');
        }

        $rawInput = $this->request->getJSON(true) ?: $this->request->getPost();
        $userId = (int) ($rawInput['user_id'] ?? 0);

        if (!$userId) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Please select a valid member.']);
            }
            return redirect()->back()->with('error', 'Please select a valid member.');
        }

        $userModel = new UserModel();
        $user = $userModel->find($userId);
        if (!$user || $user['status'] !== 'approved') {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Member not found or not approved.']);
            }
            return redirect()->back()->with('error', 'Member not found or not approved.');
        }

        $spinnerModel = new WheelAllowedSpinnerModel();
        if (!$spinnerModel->isAllowed($userId)) {
            $spinnerModel->insert([
                'user_id'    => $userId,
                'added_by'   => (int) session()->get('id'),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $name = !empty($user['player_name']) ? $user['player_name'] : $user['username'];
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'message' => "Spin permission granted to {$name}."]);
        }
        return redirect()->back()->with('success', "Spin permission granted to {$name}.");
    }

    /**
     * Remove a user from allowed spinners list
     * POST /wheel/remove-spinner
     */
    public function removeSpinner()
    {
        if (!$this->canManage()) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Unauthorized']);
            }
            return redirect()->back()->with('error', 'Only R4, R5, or Super Admin can manage spinners.');
        }

        $rawInput = $this->request->getJSON(true) ?: $this->request->getPost();
        $spinnerId = (int) ($rawInput['id'] ?? 0);
        $userId = (int) ($rawInput['user_id'] ?? 0);

        $spinnerModel = new WheelAllowedSpinnerModel();
        if ($spinnerId) {
            $spinnerModel->delete($spinnerId);
        } elseif ($userId) {
            $spinnerModel->where('user_id', $userId)->delete();
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'message' => 'Spin permission revoked.']);
        }
        return redirect()->back()->with('success', 'Spin permission revoked.');
    }

    /**
     * Clear all spin history
     * POST /wheel/clear-history
     */
    public function clearHistory()
    {
        if (!$this->canManage()) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Unauthorized']);
            }
            return redirect()->back()->with('error', 'Only R4, R5, or Super Admin can clear wheel history.');
        }

        $spinModel = new WheelSpinModel();
        $spinModel->where('id >', 0)->delete();

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'message' => 'Wheel spin history cleared.']);
        }
        return redirect()->back()->with('success', 'Wheel spin history cleared.');
    }
}

