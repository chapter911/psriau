<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDisposisiIdToLaporanPerjalananDinas extends Migration
{
    public function up()
    {
        $db = $this->db;

        if ($db->tableExists('laporan_perjalanan_dinas')) {
            if (! $db->fieldExists('disposisi_id', 'laporan_perjalanan_dinas')) {
                $this->forge->addColumn('laporan_perjalanan_dinas', [
                    'disposisi_id' => [
                        'type'       => 'INT',
                        'constraint' => 11,
                        'unsigned'   => true,
                        'null'       => true,
                        'after'      => 'id',
                    ],
                ]);
            }
        }
    }

    public function down()
    {
        $db = $this->db;

        if ($db->tableExists('laporan_perjalanan_dinas')) {
            if ($db->fieldExists('disposisi_id', 'laporan_perjalanan_dinas')) {
                $this->forge->dropColumn('laporan_perjalanan_dinas', 'disposisi_id');
            }
        }
    }
}
