<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateMasterSimakMenuLinks extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('menu_lv3')) {
            return;
        }

        $this->db->table('menu_lv3')
            ->where('LOWER(link)', 'admin/kontrak/simak/konstruksi')
            ->update(['link' => 'admin/master/simak/konstruksi']);

        $this->db->table('menu_lv3')
            ->where('LOWER(link)', 'admin/kontrak/simak/konsultasi')
            ->update(['link' => 'admin/master/simak/konsultasi']);
    }

    public function down()
    {
        if (! $this->db->tableExists('menu_lv3')) {
            return;
        }

        $this->db->table('menu_lv3')
            ->where('LOWER(link)', 'admin/master/simak/konstruksi')
            ->update(['link' => 'admin/kontrak/simak/konstruksi']);

        $this->db->table('menu_lv3')
            ->where('LOWER(link)', 'admin/master/simak/konsultasi')
            ->update(['link' => 'admin/kontrak/simak/konsultasi']);
    }
}