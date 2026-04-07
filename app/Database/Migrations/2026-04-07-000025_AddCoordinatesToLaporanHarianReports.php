<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCoordinatesToLaporanHarianReports extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('laporan_harian_reports')) {
            return;
        }

        $fields = [];

        if (! $this->db->fieldExists('latitude', 'laporan_harian_reports')) {
            $fields['latitude'] = [
                'type' => 'DECIMAL',
                'constraint' => '10,7',
                'null' => true,
                'after' => 'cuaca_hujan',
            ];
        }

        if (! $this->db->fieldExists('longitude', 'laporan_harian_reports')) {
            $fields['longitude'] = [
                'type' => 'DECIMAL',
                'constraint' => '10,7',
                'null' => true,
                'after' => 'latitude',
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn('laporan_harian_reports', $fields);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('laporan_harian_reports')) {
            return;
        }

        if ($this->db->fieldExists('longitude', 'laporan_harian_reports')) {
            $this->forge->dropColumn('laporan_harian_reports', 'longitude');
        }

        if ($this->db->fieldExists('latitude', 'laporan_harian_reports')) {
            $this->forge->dropColumn('laporan_harian_reports', 'latitude');
        }
    }
}
