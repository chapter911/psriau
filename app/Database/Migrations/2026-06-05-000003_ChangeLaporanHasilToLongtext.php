<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ChangeLaporanHasilToLongtext extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('laporan_perjalanan_dinas', [
            'laporan_hasil' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('laporan_perjalanan_dinas', [
            'laporan_hasil' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);
    }
}
