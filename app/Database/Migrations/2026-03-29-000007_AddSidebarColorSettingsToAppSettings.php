<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSidebarColorSettingsToAppSettings extends Migration
{
    public function up()
    {
        $this->forge->addColumn('app_settings', [
            'sidebar_bg_color' => [
                'type'       => 'VARCHAR',
                'constraint' => 7,
                'default'    => '#2F3A45',
                'after'      => 'primary_color',
            ],
            'sidebar_text_color' => [
                'type'       => 'VARCHAR',
                'constraint' => 7,
                'default'    => '#C2CBD5',
                'after'      => 'sidebar_bg_color',
            ],
            'sidebar_active_bg_color' => [
                'type'       => 'VARCHAR',
                'constraint' => 7,
                'default'    => '#0A66C2',
                'after'      => 'sidebar_text_color',
            ],
            'sidebar_active_text_color' => [
                'type'       => 'VARCHAR',
                'constraint' => 7,
                'default'    => '#FFFFFF',
                'after'      => 'sidebar_active_bg_color',
            ],
        ]);

        $this->db->table('app_settings')->set([
            'sidebar_bg_color'          => '#2F3A45',
            'sidebar_text_color'        => '#C2CBD5',
            'sidebar_active_bg_color'   => '#0A66C2',
            'sidebar_active_text_color' => '#FFFFFF',
        ])->update();
    }

    public function down()
    {
        $this->forge->dropColumn('app_settings', 'sidebar_active_text_color');
        $this->forge->dropColumn('app_settings', 'sidebar_active_bg_color');
        $this->forge->dropColumn('app_settings', 'sidebar_text_color');
        $this->forge->dropColumn('app_settings', 'sidebar_bg_color');
    }
}
