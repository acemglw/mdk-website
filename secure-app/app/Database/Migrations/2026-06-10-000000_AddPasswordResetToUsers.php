<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPasswordResetToUsers extends Migration
{
    public function up()
    {
        $fields = [
            'reset_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'reset_expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ];

        // Ensure the column doesn't exist before adding to avoid crash if it partially ran
        $db = \Config\Database::connect();
        if ($db->tableExists('users')) {
            $existingFields = $db->getFieldNames('users');
            if (!in_array('reset_token', $existingFields)) {
                $this->forge->addColumn('users', $fields);
            }
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        if ($db->tableExists('users')) {
            $existingFields = $db->getFieldNames('users');
            if (in_array('reset_token', $existingFields)) {
                $this->forge->dropColumn('users', 'reset_token');
                $this->forge->dropColumn('users', 'reset_expires_at');
            }
        }
    }
}