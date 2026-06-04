<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixGoldTrainTableSchema extends Migration
{
    public function up()
    {
        // First, check if the column exists so we don't crash on reruns
        $db = \Config\Database::connect();
        
        if ($db->tableExists('gold_train_logs')) {
            $fields = $db->getFieldNames('gold_train_logs');
            
            // If the old schema from 2026-06-06 is active, it has schedule_date instead of assigned_time
            if (in_array('schedule_date', $fields) && !in_array('assigned_time', $fields)) {
                $this->forge->modifyColumn('gold_train_logs', [
                    'schedule_date' => [
                        'name' => 'assigned_time',
                        'type' => 'DATETIME',
                        'null' => true,
                    ],
                ]);
            } elseif (!in_array('assigned_time', $fields)) {
                // Completely missing? Add it.
                $this->forge->addColumn('gold_train_logs', [
                    'assigned_time' => [
                        'type' => 'DATETIME',
                        'null' => true,
                        'after' => 'status'
                    ],
                ]);
            }

            // We also need cycle_id if it's missing from the old schema
            if (!in_array('cycle_id', $fields)) {
                $this->forge->addColumn('gold_train_logs', [
                    'cycle_id' => [
                        'type'       => 'VARCHAR',
                        'constraint' => '50',
                        'default'    => 'Manual-Override',
                        'after'      => 'user_id'
                    ],
                ]);
            }
        }
    }

    public function down()
    {
        // To be safe on rollback
        $db = \Config\Database::connect();
        if ($db->tableExists('gold_train_logs')) {
            $fields = $db->getFieldNames('gold_train_logs');
            
            if (in_array('assigned_time', $fields) && !in_array('schedule_date', $fields)) {
                $this->forge->modifyColumn('gold_train_logs', [
                    'assigned_time' => [
                        'name' => 'schedule_date',
                        'type' => 'DATE',
                        'null' => true,
                    ],
                ]);
            }
            
            if (in_array('cycle_id', $fields)) {
                $this->forge->dropColumn('gold_train_logs', 'cycle_id');
            }
        }
    }
}
