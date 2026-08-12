<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTimerAndLiveColumnsToGateballMatches extends Migration
{
    public function up()
    {
        $table = 'gateball_matches';

        if ($this->db->tableExists($table)) {
            $fields = [];

            if (! $this->db->fieldExists('timer_seconds', $table)) {
                $fields['timer_seconds'] = [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 1800, // 30 minutes in seconds
                    'null'       => false,
                    'after'      => 'score2',
                ];
            }

            if (! $this->db->fieldExists('timer_status', $table)) {
                $fields['timer_status'] = [
                    'type'       => 'ENUM',
                    'constraint' => ['stopped', 'running', 'paused'],
                    'default'    => 'stopped',
                    'null'       => false,
                    'after'      => 'timer_seconds',
                ];
            }

            if (! $this->db->fieldExists('timer_started_at', $table)) {
                $fields['timer_started_at'] = [
                    'type'  => 'DATETIME',
                    'null'  => true,
                    'after' => 'timer_status',
                ];
            }

            if (! $this->db->fieldExists('score_details_json', $table)) {
                $fields['score_details_json'] = [
                    'type'  => 'TEXT',
                    'null'  => true,
                    'after' => 'timer_started_at',
                ];
            }

            if (! empty($fields)) {
                $this->forge->addColumn($table, $fields);
            }

            // Modify status enum if needed to allow 'ongoing'
            $this->db->query("ALTER TABLE `{$table}` MODIFY COLUMN `status` ENUM('pending', 'ongoing', 'completed') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down()
    {
        $table = 'gateball_matches';

        if ($this->db->tableExists($table)) {
            $columnsToDrop = [];
            foreach (['timer_seconds', 'timer_status', 'timer_started_at', 'score_details_json'] as $col) {
                if ($this->db->fieldExists($col, $table)) {
                    $columnsToDrop[] = $col;
                }
            }

            if (! empty($columnsToDrop)) {
                $this->forge->dropColumn($table, $columnsToDrop);
            }
        }
    }
}
