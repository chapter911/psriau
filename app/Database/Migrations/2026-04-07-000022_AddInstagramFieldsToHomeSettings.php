<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddInstagramFieldsToHomeSettings extends Migration
{
    public function up()
    {
        $this->forge->addColumn('home_settings', [
            'instagram_profile_url' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'contact_map_url',
            ],
            'instagram_post_urls' => [
                'type' => 'LONGTEXT',
                'null' => true,
                'after' => 'instagram_profile_url',
            ],
        ]);

        $this->db->table('home_settings')->update([
            'instagram_profile_url' => 'https://www.instagram.com/pu_prasaranastrategis_riau/',
            'instagram_post_urls' => '',
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('home_settings', ['instagram_profile_url', 'instagram_post_urls']);
    }
}