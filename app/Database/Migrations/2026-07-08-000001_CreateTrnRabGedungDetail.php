<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTrnRabGedungDetail extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'sekolah_npsn' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'nama_sekolah' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'pekerjaan_utama' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'gedung' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'kategori_1' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'kategori_2' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'no_urut' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'uraian' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'satuan' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'kontrak_volume' => [
                'type'       => 'DOUBLE',
                'null'       => true,
            ],
            'kontrak_harga_satuan' => [
                'type'       => 'DOUBLE',
                'null'       => true,
            ],
            'kontrak_jumlah_harga' => [
                'type'       => 'DOUBLE',
                'null'       => true,
            ],
            'tambah_volume' => [
                'type'       => 'DOUBLE',
                'null'       => true,
            ],
            'tambah_jumlah_harga' => [
                'type'       => 'DOUBLE',
                'null'       => true,
            ],
            'kurang_volume' => [
                'type'       => 'DOUBLE',
                'null'       => true,
            ],
            'kurang_jumlah_harga' => [
                'type'       => 'DOUBLE',
                'null'       => true,
            ],
            'mc_nol_volume' => [
                'type'       => 'DOUBLE',
                'null'       => true,
            ],
            'mc_nol_jumlah_harga' => [
                'type'       => 'DOUBLE',
                'null'       => true,
            ],
            'bobot_persen' => [
                'type'       => 'DOUBLE',
                'null'       => true,
            ],
            'prestasi_persen' => [
                'type'       => 'DOUBLE',
                'null'       => true,
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
        $this->forge->addKey('sekolah_npsn');
        $this->forge->createTable('trn_rab_gedung_detail');
    }

    public function down()
    {
        $this->forge->dropTable('trn_rab_gedung_detail');
    }
}
