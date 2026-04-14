<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveSimakModule extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('menu_akses')) {
            $menuIds = [];

            if ($this->db->tableExists('menu_lv2')) {
                $rows = $this->db->table('menu_lv2')
                    ->select('id')
                    ->where('LOWER(link)', 'admin/paket/simak')
                    ->orWhere('LOWER(label)', 'simak')
                    ->get()
                    ->getResultArray();

                foreach ($rows as $row) {
                    $menuIds[] = (string) ($row['id'] ?? '');
                }
            }

            if ($this->db->tableExists('menu_lv1')) {
                $rows = $this->db->table('menu_lv1')
                    ->select('id')
                    ->where('LOWER(label)', 'paket')
                    ->get()
                    ->getResultArray();

                foreach ($rows as $row) {
                    $menuIds[] = (string) ($row['id'] ?? '');
                }
            }

            $menuIds = array_values(array_filter(array_unique($menuIds), static fn (string $value): bool => $value !== ''));

            if ($menuIds !== []) {
                $this->db->table('menu_akses')->whereIn('menu_id', $menuIds)->delete();
            }
        }

        if ($this->db->tableExists('menu_lv2')) {
            $this->db->table('menu_lv2')
                ->where('LOWER(link)', 'admin/paket/simak')
                ->orWhere('LOWER(label)', 'simak')
                ->delete();
        }

        if ($this->db->tableExists('menu_lv1')) {
            $remainingChildren = $this->db->table('menu_lv2')
                ->select('id')
                ->where('header', '10')
                ->countAllResults();

            if ($remainingChildren === 0) {
                $this->db->table('menu_lv1')
                    ->where('id', '10')
                    ->orWhere('LOWER(label)', 'paket')
                    ->delete();
            }
        }

        if ($this->db->tableExists('trn_simak_file')) {
            $this->forge->dropTable('trn_simak_file', true);
        }

        if ($this->db->tableExists('trn_simak')) {
            $this->forge->dropTable('trn_simak', true);
        }
    }

    public function down()
    {
        // Intentionally left blank: the module is being removed.
    }
}