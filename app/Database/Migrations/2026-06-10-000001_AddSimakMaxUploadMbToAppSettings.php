<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSimakMaxUploadMbToAppSettings extends Migration
{
    public function up()
    {
        $table = 'app_settings';

        if (! $this->db->tableExists($table) || $this->db->fieldExists('simak_max_upload_mb', $table)) {
            return;
        }

        $this->forge->addColumn($table, [
            'simak_max_upload_mb' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 20,
                'null' => false,
                'after' => 'simak_upload_tutorial_url',
            ],
        ]);
    }

    public function down()
    {
        $table = 'app_settings';

        if (! $this->db->tableExists($table) || ! $this->db->fieldExists('simak_max_upload_mb', $table)) {
            return;
        }

        $this->forge->dropColumn($table, 'simak_max_upload_mb');
    }
}
