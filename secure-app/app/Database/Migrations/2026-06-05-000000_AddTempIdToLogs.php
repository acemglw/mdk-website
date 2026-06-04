<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTempIdToLogs extends Migration
{
    public function up()
    {
        $this->forge->addColumn('weekly_event_logs', [
            'user_id_temp' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
                'after'      => 'user_id'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('weekly_event_logs', 'user_id_temp');
    }
}
