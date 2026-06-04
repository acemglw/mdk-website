<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\UserModel;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        
        // Update the existing 'admin' user to 'super_admin'
        $db->table('users')
           ->where('username', 'admin')
           ->update(['role' => 'super_admin']);

        echo "User 'admin' has been promoted to Super Admin.\n";
    }
}
