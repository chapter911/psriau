<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMaintenanceModeToAppSettings extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('app_settings')) {
            return;
        }

        $fields = [
            'maintenance_mode' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
            ],
        ];

        $this->forge->addColumn('app_settings', $fields);
    }

    public function down()
    {
        if (! $this->db->tableExists('app_settings')) {
            return;
        }

        $this->forge->dropColumn('app_settings', 'maintenance_mode');
    }
}
