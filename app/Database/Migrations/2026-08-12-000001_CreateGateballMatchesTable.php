<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGateballMatchesTable extends Migration
{
    public function up()
    {
        $table = 'gateball_matches';

        if (! $this->db->tableExists($table)) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'category' => [
                    'type'       => 'ENUM',
                    'constraint' => ['putra', 'putri'],
                    'default'    => 'putra',
                ],
                'match_number' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                ],
                'team1' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                ],
                'team2' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                ],
                'score1' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                    'default'    => null,
                ],
                'score2' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                    'default'    => null,
                ],
                'status' => [
                    'type'       => 'ENUM',
                    'constraint' => ['pending', 'completed'],
                    'default'    => 'pending',
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
            $this->forge->addKey(['category', 'match_number']);
            $this->forge->createTable($table, true);
        }

        // Seed initial matches if table is empty
        $count = $this->db->table($table)->countAllResults();
        if ($count === 0) {
            $now = date('Y-m-d H:i:s');

            // PUTRA Matches (10 matches from image 2)
            $putraMatches = [
                ['match_number' => 1,  'team1' => 'PS',    'team2' => 'BWS'],
                ['match_number' => 2,  'team1' => 'BPBPK', 'team2' => 'BP2JK'],
                ['match_number' => 3,  'team1' => 'BPJN',  'team2' => 'PS'],
                ['match_number' => 4,  'team1' => 'BWS',   'team2' => 'BPBPK'],
                ['match_number' => 5,  'team1' => 'BP2JK', 'team2' => 'BPJN'],
                ['match_number' => 6,  'team1' => 'PS',    'team2' => 'BPBPK'],
                ['match_number' => 7,  'team1' => 'BWS',   'team2' => 'BPJN'],
                ['match_number' => 8,  'team1' => 'BP2JK', 'team2' => 'PS'],
                ['match_number' => 9,  'team1' => 'BPBPK', 'team2' => 'BPJN'],
                ['match_number' => 10, 'team1' => 'BWS',   'team2' => 'BP2JK'],
            ];

            foreach ($putraMatches as $m) {
                $this->db->table($table)->insert([
                    'category'     => 'putra',
                    'match_number' => $m['match_number'],
                    'team1'        => $m['team1'],
                    'team2'        => $m['team2'],
                    'score1'       => null,
                    'score2'       => null,
                    'status'       => 'pending',
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ]);
            }

            // PUTRI Matches (10 matches from image 1)
            $putriMatches = [
                ['match_number' => 1,  'team1' => 'BP2JK', 'team2' => 'BPBPK'],
                ['match_number' => 2,  'team1' => 'PS',    'team2' => 'BPJN'],
                ['match_number' => 3,  'team1' => 'BWS',   'team2' => 'BP2JK'],
                ['match_number' => 4,  'team1' => 'BPBPK', 'team2' => 'PS'],
                ['match_number' => 5,  'team1' => 'BPJN',  'team2' => 'BWS'],
                ['match_number' => 6,  'team1' => 'BP2JK', 'team2' => 'PS'],
                ['match_number' => 7,  'team1' => 'BWS',   'team2' => 'BPBPK'],
                ['match_number' => 8,  'team1' => 'BPJN',  'team2' => 'BP2JK'],
                ['match_number' => 9,  'team1' => 'BWS',   'team2' => 'PS'],
                ['match_number' => 10, 'team1' => 'BPJN',  'team2' => 'BPBPK'],
            ];

            foreach ($putriMatches as $m) {
                $this->db->table($table)->insert([
                    'category'     => 'putri',
                    'match_number' => $m['match_number'],
                    'team1'        => $m['team1'],
                    'team2'        => $m['team2'],
                    'score1'       => null,
                    'score2'       => null,
                    'status'       => 'pending',
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ]);
            }
        }
    }

    public function down()
    {
        $table = 'gateball_matches';
        if ($this->db->tableExists($table)) {
            $this->forge->dropTable($table, true);
        }
    }
}
