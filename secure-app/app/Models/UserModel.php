<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'username', 
        'player_name', 
        'email', 
        'alliance_level', 
        'password', 
        'role', 
        'status',
        'reset_token',
        'reset_expires_at',
        'created_at',
        'updated_at'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    // For {id} placeholder to work in is_unique, 'id' must have its own rule.
    protected $validationRules = [
        'id'             => 'permit_empty|is_natural_no_zero',
        'username'       => 'required|alpha_numeric_space|min_length[3]|max_length[100]|is_unique[users.username,id,{id}]',
        'player_name'    => 'permit_empty|max_length[100]',
        'email'          => 'required|valid_email|is_unique[users.email,id,{id}]',
        'alliance_level' => 'required|in_list[R1,R2,R3,R4,R5]',
        'password'       => 'permit_empty|min_length[8]',
    ];
    
    protected $validationMessages   = [
        'username' => [
            'is_unique' => 'That username is already taken.',
        ],
        'email' => [
            'is_unique' => 'An account with that email already exists.',
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}