<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAppSettingsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'app_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 190,
                'default'    => 'PLN EPM-Digi',
            ],
            'primary_color' => [
                'type'       => 'VARCHAR',
                'constraint' => 7,
                'default'    => '#0a66c2',
            ],
            'app_logo_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'login_background_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'auto_logout_minutes' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 60,
            ],
            'preloader_duration_ms' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 500,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_by' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('app_settings');

        $this->db->table('app_settings')->insert([
            'app_name'             => 'PLN EPM-Digi',
            'primary_color'        => '#0a66c2',
            'app_logo_url'         => null,
            'login_background_url' => null,
            'auto_logout_minutes'  => 60,
            'preloader_duration_ms' => 500,
            'updated_at'           => date('Y-m-d H:i:s'),
            'updated_by'           => null,
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('app_settings');
    }
}
