<?php

namespace App\Models;

use CodeIgniter\Model;

class WheelSpinModel extends Model
{
    protected $table            = 'wheel_spins';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'winner_name',
        'winner_user_id',
        'spun_by_user_id',
        'spun_by_name',
        'title',
        'participants_count',
        'created_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    /**
     * Retrieve the most recent spins with optional limit.
     */
    public function getRecentSpins(int $limit = 25): array
    {
        return $this->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}

