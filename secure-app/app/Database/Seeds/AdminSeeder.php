<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\UserModel;
use App\Models\UserStatModel;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $userModel = new UserModel();
        $userStatModel = new UserStatModel();

        $data = [
            'username'       => 'admin',
            'email'          => 'acemglw@gmail.com',
            'alliance_level' => 'R5',
            'password'       => password_hash('MDK4$SecureAdminPassword123!', PASSWORD_DEFAULT),
            'role'           => 'admin',
            'status'         => 'approved'
        ];

        // Insert the default admin user
        $userId = $userModel->insert($data);
        
        if ($userId) {
            echo "Default Admin user created successfully!\n";
            
            // Give admin default empty stats
            $userStatModel->insert([
                'user_id'       => $userId,
                'power_tank'    => 0,
                'power_air'     => 0,
                'power_missile' => 0
            ]);
            echo "Default Admin squad stats created successfully!\n";
        } else {
            echo "Failed to create Admin user. Errors:\n";
            print_r($userModel->errors());
        }
    }
}