<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTenisMejaTournamentsTable extends Migration
{
    public function up()
    {
        $table = 'tenis_meja_tournaments';

        if (! $this->db->tableExists($table)) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'title' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'default'    => 'Turnamen Tenis Meja Sistem Gugur',
                ],
                'category' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'default'    => 'Tunggal Putra',
                ],
                'participant_count' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                ],
                'participants_json' => [
                    'type' => 'LONGTEXT',
                    'null' => true,
                ],
                'bracket_json' => [
                    'type' => 'LONGTEXT',
                    'null' => true,
                ],
                'status' => [
                    'type'       => 'ENUM',
                    'constraint' => ['draft', 'ongoing', 'completed'],
                    'default'    => 'ongoing',
                ],
                'is_active' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 1,
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
            $this->forge->addKey('is_active');
            $this->forge->createTable($table, true);
        }
    }

    public function down()
    {
        $table = 'tenis_meja_tournaments';
        if ($this->db->tableExists($table)) {
            $this->forge->dropTable($table, true);
        }
    }
}
