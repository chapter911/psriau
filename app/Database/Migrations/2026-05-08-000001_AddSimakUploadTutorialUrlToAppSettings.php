<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSimakUploadTutorialUrlToAppSettings extends Migration
{
    public function up()
    {
        $table = 'app_settings';

        if (! $this->db->tableExists($table) || $this->db->fieldExists('simak_upload_tutorial_url', $table)) {
            return;
        }

        $this->forge->addColumn($table, [
            'simak_upload_tutorial_url' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'login_background_url',
            ],
        ]);
    }

    public function down()
    {
        $table = 'app_settings';

        if (! $this->db->tableExists($table) || ! $this->db->fieldExists('simak_upload_tutorial_url', $table)) {
            return;
        }

        $this->forge->dropColumn($table, 'simak_upload_tutorial_url');
    }
}
