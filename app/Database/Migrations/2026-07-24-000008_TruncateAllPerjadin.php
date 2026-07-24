<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TruncateAllPerjadin extends Migration
{
    public function up()
    {
        $db = $this->db;
        $db->disableForeignKeyChecks();

        if ($db->tableExists('laporan_perjalanan_dinas')) {
            $db->table('laporan_perjalanan_dinas')->truncate();
        }

        if ($db->tableExists('disposisi_perjalanan_dinas')) {
            $db->table('disposisi_perjalanan_dinas')->truncate();
        }

        if ($db->tableExists('app_settings')) {
            $existing = $db->table('app_settings')->get()->getRowArray();
            if (is_array($existing)) {
                $db->table('app_settings')->update(['last_kode_nomor_sppd' => 0]);
            }
        }

        $db->enableForeignKeyChecks();
    }

    public function down()
    {
    }
}
