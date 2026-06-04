<?php

namespace App\Models;

use CodeIgniter\Model;

class GoldTrainModel extends Model
{
    protected $table            = 'gold_train_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    
    protected $allowedFields    = [
        'user_id',
        'schedule_date',
        'turned_gold',
        'reached_minimum',
        'status',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get the full train roster with status and user details, ordered by alliance level (R5 first, then R4, etc.)
     */
    public function getTrainRoster()
    {
        // For SQLite compatibility, we use a simpler sorting approach rather than the FIELD() function
        // which is MySQL specific.
        
        $roster = $this->db->table('users u')
            ->select('u.id as user_id, u.player_name, u.alliance_level, g.id as log_id, g.schedule_date, g.status, g.turned_gold, g.reached_minimum')
            ->join('gold_train_logs g', 'g.user_id = u.id AND g.status IN ("pending", "scheduled")', 'left')
            ->orderBy('u.alliance_level', 'DESC') // Because R5 > R4 > R3
            ->get()
            ->getResultArray();
            
        return $roster;
    }
}
