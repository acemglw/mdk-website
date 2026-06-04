<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class IncreasePasswordColumnLength extends Migration
{
    public function up()
    {
        $fields = [
            'password' => [
                'name'       => 'password',
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
        ];

        $this->forge->modifyColumn('users', $fields);
    }

    public function down()
    {
        // Revert the change if needed
        $fields = [
            'password' => [
                'name'       => 'password',
                'type'       => 'VARCHAR',
                'constraint' => 60,
            ],
        ];

        $this->forge->modifyColumn('users', $fields);
    }
}
