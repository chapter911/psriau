<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKopSuratIdToLaporanPerjalananDinas extends Migration
{
    public function up()
    {
        $fields = [
            'kop_surat_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'is_verified',
            ],
        ];
        $this->forge->addColumn('laporan_perjalanan_dinas', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('laporan_perjalanan_dinas', 'kop_surat_id');
    }
}
