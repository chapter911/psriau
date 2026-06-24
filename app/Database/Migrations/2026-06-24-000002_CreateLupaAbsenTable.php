<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLupaAbsenTable extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('lupa_absen')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'nip' => [
                    'type' => 'VARCHAR',
                    'constraint' => 30,
                    'null' => true,
                ],
                'nama' => [
                    'type' => 'VARCHAR',
                    'constraint' => 150,
                    'null' => true,
                ],
                'tanggal_absen' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'jenis_absen' => [
                    'type' => 'ENUM',
                    'constraint' => ['masuk', 'pulang'],
                    'null' => true,
                ],
                'jam_absen' => [
                    'type' => 'TIME',
                    'null' => true,
                ],
                'keterangan' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'status' => [
                    'type' => 'ENUM',
                    'constraint' => ['pending', 'disetujui', 'ditolak'],
                    'default' => 'pending',
                ],
                'approved_by' => [
                    'type' => 'VARCHAR',
                    'constraint' => 150,
                    'null' => true,
                ],
                'approved_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'created_by' => [
                    'type' => 'VARCHAR',
                    'constraint' => 150,
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
            $this->forge->addKey('nip');
            $this->forge->addKey('tanggal_absen');
            $this->forge->addKey('status');
            $this->forge->createTable('lupa_absen', true);
        }
    }

    public function down()
    {
        if ($this->db->tableExists('lupa_absen')) {
            $this->forge->dropTable('lupa_absen', true);
        }
    }
}
