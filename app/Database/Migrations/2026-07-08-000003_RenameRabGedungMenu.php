<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameRabGedungMenu extends Migration
{
    public function up()
    {
        $db = $this->db;

        if ($db->tableExists('menu_lv2')) {
            $db->table('menu_lv2')
                ->where('link', 'admin/laporan/rab-gedung')
                ->update(['label' => 'RAB']);
        }
    }

    public function down()
    {
        $db = $this->db;

        if ($db->tableExists('menu_lv2')) {
            $db->table('menu_lv2')
                ->where('link', 'admin/laporan/rab-gedung')
                ->update(['label' => 'RAB Per Gedung']);
        }
    }
}
