<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGameStatsTables extends Migration
{
    public function up()
    {
        // ---------------------------------------------------
        // 1. USER SQUAD STATS TABLE
        // ---------------------------------------------------
        $this->forge->addField([
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
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
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('user_id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('user_stats', true);

        // ---------------------------------------------------
        // 2. EVENTS TABLE (Extensible list of events)
        // ---------------------------------------------------
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('events', true);

        // Seed default events automatically
        $db = \Config\Database::connect();
        $db->table('events')->insertBatch([
            ['name' => 'Desert Storm', 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Canyon', 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'War Capital', 'created_at' => date('Y-m-d H:i:s')],
        ]);

        // ---------------------------------------------------
        // 3. WEEKLY EVENT TRACKING LOG
        // ---------------------------------------------------
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
            'event_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'week_start' => [
                'type' => 'DATE', // The Monday of that specific week
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'default'    => 'participated', // e.g., participated, missed, excused
            ],
            'score' => [
                'type'    => 'INT',
                'default' => 0,
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
        $this->forge->addForeignKey('event_id', 'events', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('weekly_event_logs', true);
    }

    public function down()
    {
        $this->forge->dropTable('weekly_event_logs', true);
        $this->forge->dropTable('events', true);
        $this->forge->dropTable('user_stats', true);
    }
}
