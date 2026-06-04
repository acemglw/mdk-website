<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRolesToGoldTrain extends Migration
{
    public function up()
    {
        $this->forge->addColumn('gold_train_logs', [
            'mvp_user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'guardian_user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ]
        ]);
        
        // Optional: add foreign keys if you strictly want to enforce it
        // $this->forge->addForeignKey('mvp_user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        // $this->forge->addForeignKey('guardian_user_id', 'users', 'id', 'SET NULL', 'CASCADE');
    }

    public function down()
    {
        $this->forge->dropColumn('gold_train_logs', 'mvp_user_id');
        $this->forge->dropColumn('gold_train_logs', 'guardian_user_id');
    }
}
