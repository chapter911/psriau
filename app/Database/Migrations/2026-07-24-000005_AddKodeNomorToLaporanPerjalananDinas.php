<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKodeNomorToLaporanPerjalananDinas extends Migration
{
    public function up()
    {
        $db = $this->db;

        if ($db->tableExists('laporan_perjalanan_dinas')) {
            if (! $db->fieldExists('kode_nomor', 'laporan_perjalanan_dinas')) {
                $this->forge->addColumn('laporan_perjalanan_dinas', [
                    'kode_nomor' => [
                        'type' => 'VARCHAR',
                        'constraint' => 50,
                        'null' => true,
                        'after' => 'nomor_surat_tugas',
                    ],
                ]);
            }
        }

        if ($db->tableExists('app_settings')) {
            if (! $db->fieldExists('last_kode_nomor_sppd', 'app_settings')) {
                $this->forge->addColumn('app_settings', [
                    'last_kode_nomor_sppd' => [
                        'type' => 'INT',
                        'constraint' => 11,
                        'default' => 0,
                        'after' => 'maintenance_mode',
                    ],
                ]);
            }
        }
    }

    public function down()
    {
        $db = $this->db;

        if ($db->tableExists('laporan_perjalanan_dinas')) {
            if ($db->fieldExists('kode_nomor', 'laporan_perjalanan_dinas')) {
                $this->forge->dropColumn('laporan_perjalanan_dinas', 'kode_nomor');
            }
        }

        if ($db->tableExists('app_settings')) {
            if ($db->fieldExists('last_kode_nomor_sppd', 'app_settings')) {
                $this->forge->dropColumn('app_settings', 'last_kode_nomor_sppd');
            }
        }
    }
}
