<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ChangePowerToDecimal extends Migration
{
    public function up()
    {
        // Alter user_stats columns from BIGINT to DECIMAL to allow floating point numbers
        $fields = [
            'power_tank' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
            'power_air' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
            'power_missile' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
        ];
        
        $this->forge->modifyColumn('user_stats', $fields);
    }

    public function down()
    {
        // Revert back to BIGINT if needed
        $fields = [
            'power_tank' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'default'    => 0,
            ],
            'power_air' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'default'    => 0,
            ],
            'power_missile' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'default'    => 0,
            ],
        ];
        
        $this->forge->modifyColumn('user_stats', $fields);
    }
}
