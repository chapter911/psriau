<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLaporanPerjalananDinasTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'nomor_surat_tugas' => [ 'type' => 'VARCHAR', 'constraint' => 255, 'null' => true ],
            'periode_mulai' => [ 'type' => 'DATE', 'null' => true ],
            'periode_selesai' => [ 'type' => 'DATE', 'null' => true ],
            'kota_tujuan' => [ 'type' => 'VARCHAR', 'constraint' => 255, 'null' => true ],
            'tujuan' => [ 'type' => 'TEXT', 'null' => true ],
            'sasaran' => [ 'type' => 'TEXT', 'null' => true ],
            'laporan_hasil' => [ 'type' => 'TEXT', 'null' => true ],
            'pelaksana_json' => [ 'type' => 'TEXT', 'null' => true ],
            'foto_dokumentasi_json' => [ 'type' => 'TEXT', 'null' => true ],
            'creator_name' => [ 'type' => 'VARCHAR', 'constraint' => 255, 'null' => true ],
            'creator_pegawai_json' => [ 'type' => 'TEXT', 'null' => true ],
            'diketahui_oleh_json' => [ 'type' => 'TEXT', 'null' => true ],
            'is_final' => [ 'type' => 'TINYINT', 'constraint' => 1, 'default' => 0 ],
            'created_at' => [ 'type' => 'DATETIME', 'null' => true ],
            'updated_at' => [ 'type' => 'DATETIME', 'null' => true ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('laporan_perjalanan_dinas', true);
    }

    public function down()
    {
        $this->forge->dropTable('laporan_perjalanan_dinas', true);
    }
}
