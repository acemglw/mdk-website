<?php

namespace App\Controllers;

use App\Models\UserStatModel;
use App\Models\UserStatsHistoryModel;

class ProfileController extends BaseController
{
    public function squad()
    {
        $userId = session()->get('id');
        $statModel = new UserStatModel();
        $historyModel = new UserStatsHistoryModel();

        // Fetch current user stats
        $data['stats'] = $statModel->find($userId);

        // Fetch user history (last 5 records)
        $data['history'] = $historyModel->where('user_id', $userId)
                                        ->orderBy('created_at', 'DESC')
                                        ->limit(5)
                                        ->findAll();

        // Fallback if the user somehow doesn't have stats yet
        if (!$data['stats']) {
            $data['stats'] = [
                'power_tank' => 0.00,
                'power_air' => 0.00,
                'power_missile' => 0.00,
                'updated_at' => null
            ];
        }

        return view('profile/squad', $data);
    }

    public function updateSquad()
    {
        $userId = session()->get('id');
        $statModel = new UserStatModel();
        $historyModel = new UserStatsHistoryModel();

        // Get new values
        $newTank = (float)$this->request->getPost('power_tank');
        $newAir = (float)$this->request->getPost('power_air');
        $newMissile = (float)$this->request->getPost('power_missile');

        // Fetch existing stats to calculate differences
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

        $data = [
            'user_id'       => $userId,
            'power_tank'    => $newTank,
            'power_air'     => $newAir,
            'power_missile' => $newMissile,
        ];

        if ($statModel->save($data)) {
            return redirect()->back()->with('success', 'Squad powers updated successfully!');
        }

        return redirect()->back()->with('error', 'Failed to update squad powers. Make sure you entered numbers only.');
    }
}
