<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCycleNameToGoldTrainLogs extends Migration
{
    public function up()
    {
        $fields = [
            'cycle_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'cycle_id',
            ],
        ];

        $this->forge->addColumn('gold_train_logs', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('gold_train_logs', 'cycle_name');
    }
}
