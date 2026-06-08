<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDokumenPendukungToLaporanPerjalananDinasTable extends Migration
{
    public function up()
    {
        $fields = [
            'dokumen_pendukung_json' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'foto_dokumentasi_json',
            ],
        ];

        $this->forge->addColumn('laporan_perjalanan_dinas', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('laporan_perjalanan_dinas', 'dokumen_pendukung_json');
    }
}
