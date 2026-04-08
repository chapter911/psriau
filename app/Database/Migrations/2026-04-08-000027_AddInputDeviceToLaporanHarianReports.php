<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddInputDeviceToLaporanHarianReports extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('laporan_harian_reports')) {
            return;
        }

        if ($this->db->fieldExists('input_device', 'laporan_harian_reports')) {
            return;
        }

        $this->forge->addColumn('laporan_harian_reports', [
            'input_device' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'after' => 'longitude',
            ],
        ]);
    }

    public function down()
    {
        if (! $this->db->tableExists('laporan_harian_reports')) {
            return;
        }

        if ($this->db->fieldExists('input_device', 'laporan_harian_reports')) {
            $this->forge->dropColumn('laporan_harian_reports', 'input_device');
        }
    }
}
