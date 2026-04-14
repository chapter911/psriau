<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSimakPaketTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('simak_paket')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'nama_paket' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'tahun_anggaran' => [
                'type' => 'YEAR',
            ],
            'penyedia' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'nomor_kontrak' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'nilai_kontrak' => [
                'type' => 'DECIMAL',
                'constraint' => [15, 2],
            ],
            'add_kontrak' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'tahapan_pekerjaan' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'tanggal_pemeriksaan' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'satker' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'ppk' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'nip' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'created_by' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'created_date' => [
                'type' => 'DATETIME',
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
            'updated_by' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'updated_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['tahun_anggaran', 'created_date']);
        $this->forge->createTable('simak_paket');
    }

    public function down()
    {
        if ($this->db->tableExists('simak_paket')) {
            $this->forge->dropTable('simak_paket');
        }
    }
}
