<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRincianBiayaJsonToLaporanPerjalananDinas extends Migration
{
    public function up()
    {
        $db = $this->db;
        if ($db->tableExists('laporan_perjalanan_dinas') && ! $db->fieldExists('rincian_biaya_json', 'laporan_perjalanan_dinas')) {
            $this->forge->addColumn('laporan_perjalanan_dinas', [
                'rincian_biaya_json' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'mata_anggaran_id',
                ],
            ]);
        }
    }

    public function down()
    {
        $db = $this->db;
        if ($db->tableExists('laporan_perjalanan_dinas') && $db->fieldExists('rincian_biaya_json', 'laporan_perjalanan_dinas')) {
            $this->forge->dropColumn('laporan_perjalanan_dinas', 'rincian_biaya_json');
        }
    }
}
