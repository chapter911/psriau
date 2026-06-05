<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ChangeFotoDokumentasiJsonToLongtext extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('laporan_perjalanan_dinas', [
            'foto_dokumentasi_json' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('laporan_perjalanan_dinas', [
            'foto_dokumentasi_json' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);
    }
}
