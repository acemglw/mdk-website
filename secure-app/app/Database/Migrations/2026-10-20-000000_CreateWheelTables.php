<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWheelTables extends Migration
{
    public function up()
    {
        // ---------------------------------------------------
        // 1. WHEEL ALLOWED SPINNERS TABLE
        // Specific users authorized by R4/R5/super_admin to spin
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
            'added_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('user_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('wheel_allowed_spinners', true);

        // ---------------------------------------------------
        // 2. WHEEL SPINS TABLE (Raffle history / winner log)
        // ---------------------------------------------------
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'winner_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'winner_user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'spun_by_user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'spun_by_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'default'    => 'Alliance Raffle',
            ],
            'participants_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('spun_by_user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('wheel_spins', true);
    }

    public function down()
    {
        $this->forge->dropTable('wheel_spins', true);
        $this->forge->dropTable('wheel_allowed_spinners', true);
    }
}

