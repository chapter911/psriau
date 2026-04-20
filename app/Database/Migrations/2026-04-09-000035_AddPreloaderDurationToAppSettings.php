<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPreloaderDurationToAppSettings extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('app_settings') || $this->db->fieldExists('preloader_duration_ms', 'app_settings')) {
            return;
        }

        $this->forge->addColumn('app_settings', [
            'preloader_duration_ms' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 500,
                'after'      => 'auto_logout_minutes',
            ],
        ]);

        $this->db->table('app_settings')->set([
            'preloader_duration_ms' => 500,
        ])->update();
    }

    public function down()
    {
        $this->forge->dropColumn('app_settings', 'preloader_duration_ms');
    }
}