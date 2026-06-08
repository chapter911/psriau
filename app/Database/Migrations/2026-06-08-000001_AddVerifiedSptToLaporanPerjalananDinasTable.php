<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVerifiedSptToLaporanPerjalananDinasTable extends Migration
{
    public function up()
    {
        $fields = [
            'verified_spt_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'is_final',
            ],
        ];

        $this->forge->addColumn('laporan_perjalanan_dinas', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('laporan_perjalanan_dinas', 'verified_spt_path');
    }
}
