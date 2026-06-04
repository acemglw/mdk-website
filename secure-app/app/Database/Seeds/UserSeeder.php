<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'username'   => 'admin_user',
                'password'   => password_hash('YourSecureAdminPassword123!', PASSWORD_DEFAULT),
                'role'       => 'admin',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'username'   => 'editor_user',
                'password'   => password_hash('YourSecureEditorPassword123!', PASSWORD_DEFAULT),
                'role'       => 'editor',
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Simple Queries to insert the data
        $this->db->table('users')->insertBatch($data);
    }
}