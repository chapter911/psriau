<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKontrakSimakTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('trn_kontrak_simak')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'satker' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'default' => 'Perencanaan Prasarana Strategis',
            ],
            'ppk_nama' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
            ],
            'ppk_nip' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
            'nama_paket' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'tahun_anggaran' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
            'penyedia' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'nomor_kontrak' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
            ],
            'nilai_kontrak' => [
                'type' => 'DECIMAL',
                'constraint' => '18,2',
                'default' => 0,
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
            'dokumen_adm_kontrak' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'dokumen_adm_spmk' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'dokumen_adm_baphp' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'dokumen_adm_bast' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'dokumen_adm_invoice' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'kelengkapan_dokumen_administrasi' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 0,
            ],
            'created_by' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'created_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_by' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'updated_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_by' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('nomor_kontrak');
        $this->forge->addKey('ppk_nip');
        $this->forge->addKey('tahun_anggaran');
        $this->forge->addKey('tanggal_pemeriksaan');
        $this->forge->createTable('trn_kontrak_simak', true);
    }

    public function down()
    {
        $this->forge->dropTable('trn_kontrak_simak', true);
    }
}
