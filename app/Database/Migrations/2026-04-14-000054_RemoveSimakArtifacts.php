<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveSimakArtifacts extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('menu_akses')) {
            $subQuery = $this->db->table('menu_lv2')
                ->select('id')
                ->where('LOWER(link)', 'admin/simak/paket');

            $this->db->table('menu_akses')
                ->whereIn('menu_id', $subQuery)
                ->delete();

            $this->db->table('menu_akses')
                ->groupStart()
                ->where('menu_id', '10')
                ->orWhere('menu_id', '10-01')
                ->groupEnd()
                ->delete();
        }

        if ($this->db->tableExists('menu_lv2')) {
            $this->db->table('menu_lv2')
                ->where('LOWER(link)', 'admin/simak/paket')
                ->delete();

            $this->db->table('menu_lv2')
                ->where('id', '10-01')
                ->delete();
        }

        if ($this->db->tableExists('menu_lv1')) {
            $this->db->table('menu_lv1')
                ->where('LOWER(label)', 'simak')
                ->delete();
        }

        if ($this->db->tableExists('simak_paket')) {
            $this->forge->dropTable('simak_paket', true);
        }
    }

    public function down()
    {
        // Intentionally left blank.
    }
}