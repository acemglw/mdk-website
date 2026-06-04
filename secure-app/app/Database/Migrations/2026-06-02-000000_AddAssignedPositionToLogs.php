<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAssignedPositionToLogs extends Migration
{
    public function up()
    {
        $this->forge->addColumn('weekly_event_logs', [
            'assigned_position' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
                'after'      => 'event_id'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('weekly_event_logs', 'assigned_position');
    }
}
