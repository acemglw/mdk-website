<?php

namespace App\Models;

use CodeIgniter\Model;

class UserStatModel extends Model
{
    protected $table            = 'user_stats';
    protected $primaryKey       = 'user_id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    
    protected $allowedFields    = [
        'user_id',
        'power_tank',
        'power_air',
        'power_missile',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = '';
    protected $updatedField  = 'updated_at';

    protected $validationRules      = [
        'user_id'       => 'required|is_natural_no_zero',
        'power_tank'    => 'permit_empty|decimal', // Changed from is_natural to decimal
        'power_air'     => 'permit_empty|decimal', // Changed from is_natural to decimal
        'power_missile' => 'permit_empty|decimal', // Changed from is_natural to decimal
    ];
}
