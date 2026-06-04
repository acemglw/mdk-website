<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPlayerNameToUsers extends Migration
{
    public function up()
    {
        // Add player_name to users if it doesn't already exist
        if (!$this->db->fieldExists('player_name', 'users')) {
            $this->forge->addColumn('users', [
                'player_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '100',
                    'null'       => true,
                    'after'      => 'username'
                ]
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('player_name', 'users')) {
            $this->forge->dropColumn('users', 'player_name');
        }
    }
}
