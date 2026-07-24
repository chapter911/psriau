<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMataAnggaranIdToLaporanPerjalananDinas extends Migration
{
    public function up()
    {
        $db = $this->db;
        if ($db->tableExists('laporan_perjalanan_dinas') && ! $db->fieldExists('mata_anggaran_id', 'laporan_perjalanan_dinas')) {
            $this->forge->addColumn('laporan_perjalanan_dinas', [
                'mata_anggaran_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'kop_surat_id',
                ],
            ]);
        }
    }

    public function down()
    {
        $db = $this->db;
        if ($db->tableExists('laporan_perjalanan_dinas') && $db->fieldExists('mata_anggaran_id', 'laporan_perjalanan_dinas')) {
            $this->forge->dropColumn('laporan_perjalanan_dinas', 'mata_anggaran_id');
        }
    }
}
