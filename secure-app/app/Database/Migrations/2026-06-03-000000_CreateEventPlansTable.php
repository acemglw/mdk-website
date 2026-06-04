<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEventPlansTable extends Migration
{
    public function up()
    {
        // 1. Create the event_plans table to save complete planner JSON states
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'event_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'event_datetime' => [
                'type' => 'DATETIME',
            ],
            'plan_data' => [
                'type' => 'TEXT', // Will store the JSON string of the exact player layout
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('event_id', 'events', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('event_plans', true);

        // 2. Add event_datetime to weekly_event_logs so we can track multiple events per week
        $this->forge->addColumn('weekly_event_logs', [
            'event_datetime' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'after'      => 'week_start'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('weekly_event_logs', 'event_datetime');
        $this->forge->dropTable('event_plans', true);
    }
}
