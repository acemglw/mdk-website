<?php

namespace App\Models;

use CodeIgniter\Model;

class WeeklyEventLogModel extends Model
{
    protected $table            = 'weekly_event_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    
    protected $allowedFields    = [
        'user_id',
        'user_id_temp',
        'event_id',
        'assigned_position', 
        'week_start',
        'event_datetime',
        'status',
        'score',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
