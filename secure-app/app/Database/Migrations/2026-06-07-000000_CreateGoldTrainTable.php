<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGoldTrainTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'cycle_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '50', // E.g. "2026-06-Week1"
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'default'    => 'pending', // pending, riding, skipped
            ],
            'assigned_time' => [
                'type' => 'DATETIME',
                'null' => true,
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
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('gold_train_logs', true);
    }

    public function down()
    {
        $this->forge->dropTable('gold_train_logs', true);
    }
}