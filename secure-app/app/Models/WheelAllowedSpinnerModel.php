<?php

namespace App\Models;

use CodeIgniter\Model;

class WheelAllowedSpinnerModel extends Model
{
    protected $table            = 'wheel_allowed_spinners';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'added_by',
        'created_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    /**
     * Check if a specific user_id is in the allowed spinners list.
     */
    public function isAllowed(int $userId): bool
    {
        return $this->where('user_id', $userId)->countAllResults() > 0;
    }

    /**
     * Retrieve all allowed spinners along with their user profile info and who granted the access.
     */
    public function getAllowedSpinnersWithDetails(): array
    {
        return $this->select('wheel_allowed_spinners.id, wheel_allowed_spinners.user_id, wheel_allowed_spinners.created_at, u.username, u.player_name, u.alliance_level, u.role, adder.player_name as added_by_name, adder.username as added_by_username')
            ->join('users u', 'u.id = wheel_allowed_spinners.user_id', 'inner')
            ->join('users adder', 'adder.id = wheel_allowed_spinners.added_by', 'left')
            ->orderBy('wheel_allowed_spinners.created_at', 'DESC')
            ->findAll();
    }
}

