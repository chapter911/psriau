<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKontrakSimakKonsultasiTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('trn_kontrak_simak_konsultasi')) {
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
            'jenis_pekerjaan_jasa_konsultansi' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'Perencanaan/Perancangan/Pengawasan/Manajemen Konstruksi/Lainnya',
            ],
            'nama_paket' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'masa_pelaksanaan' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'SYC/MYC',
            ],
            'tahun_anggaran' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
            'pagu_anggaran' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'default' => 0,
                'comment' => 'Pagu anggaran dalam satuan nominal',
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
            'metode_pemilihan' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'Pengadaan Langsung/Penunjukan Langsung/Seleksi',
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
        $this->forge->addKey('jenis_pekerjaan_jasa_konsultansi');
        $this->forge->createTable('trn_kontrak_simak_konsultasi', true);
    }

    public function down()
    {
        $this->forge->dropTable('trn_kontrak_simak_konsultasi', true);
    }
}
