<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKontrakSimakKonsultasiVerifikasiDokumenTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('trn_kontrak_simak_konsultasi_verifikasi_dokumen')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'simak_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'nomor_dokumen' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'tipe_dokumen' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'nama_file' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'file_path' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'Lengkap/Belum Sesuai/Belum Verifikasi/Belum Ada',
            ],
            'catatan' => [
                'type' => 'TEXT',
                'null' => true,
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
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('simak_id');
        $this->forge->addKey('status');
        $this->forge->createTable('trn_kontrak_simak_konsultasi_verifikasi_dokumen', true);
    }

    public function down()
    {
        $this->forge->dropTable('trn_kontrak_simak_konsultasi_verifikasi_dokumen', true);
    }
}
