<?php

namespace App\Models;

use CodeIgniter\Model;

class PageAccessLogSnapshotModel extends Model
{
    protected $table            = 'page_access_log_snapshots';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'url',
        'visit_count',
        'start_date',
        'end_date',
        'created_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
}
