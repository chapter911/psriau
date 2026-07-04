<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVerificationToLaporanPerjalananDinas extends Migration
{
    public function up()
    {
        $fields = [
            'dasar_spt_ids_json' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'nomor_surat_tugas',
            ],
            'tanggal_tanda_tangan' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'dasar_spt_ids_json',
            ],
            'is_verified' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'tanggal_tanda_tangan',
            ],
        ];

        $this->forge->addColumn('laporan_perjalanan_dinas', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('laporan_perjalanan_dinas', ['dasar_spt_ids_json', 'tanggal_tanda_tangan', 'is_verified']);
    }
}
