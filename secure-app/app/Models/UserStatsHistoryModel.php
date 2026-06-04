<?php

namespace App\Models;

use CodeIgniter\Model;

class UserStatsHistoryModel extends Model
{
    protected $table            = 'user_stats_history';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    
    protected $allowedFields    = [
        'user_id',
        'power_tank',
        'power_air',
        'power_missile',
        'tank_diff',
        'air_diff',
        'missile_diff',
        'days_since_last',
        'created_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
}
