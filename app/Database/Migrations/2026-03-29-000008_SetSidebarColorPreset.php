<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SetSidebarColorPreset extends Migration
{
    public function up()
    {
        $this->db->table('app_settings')->set([
            'sidebar_bg_color'          => '#27313D',
            'sidebar_text_color'        => '#E7ECF2',
            'sidebar_active_bg_color'   => '#1D6FD0',
            'sidebar_active_text_color' => '#FFFFFF',
            'updated_at'                => date('Y-m-d H:i:s'),
            'updated_by'                => 1,
        ])->update();
    }

    public function down()
    {
        $this->db->table('app_settings')->set([
            'sidebar_bg_color'          => '#2F3A45',
            'sidebar_text_color'        => '#C2CBD5',
            'sidebar_active_bg_color'   => '#0A66C2',
            'sidebar_active_text_color' => '#FFFFFF',
            'updated_at'                => date('Y-m-d H:i:s'),
        ])->update();
    }
}
