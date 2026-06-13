<?php

namespace App\Models;

use CodeIgniter\Model;

class GoldTrainLogModel extends Model
{
    protected $table            = 'gold_train_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    
    protected $allowedFields    = [
        'user_id',
        'cycle_id',
        'cycle_name',
        'status',
        'mvp_user_id',
        'guardian_user_id',
        'assigned_time',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
