<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSimakMenuOnly extends Migration
{
    public function up()
    {
        // Check if Simak menu already exists
        $existing = $this->db->table('menu_lv1')
            ->where('id', '10')
            ->countAllResults();

        if ($existing === 0) {
            $this->db->table('menu_lv1')->insert([
                'id' => '10',
                'label' => 'Simak',
                'link' => '#',
                'icon' => 'fas fa-list',
                'old_icon' => null,
                'ordering' => 11,
            ]);
        }

        // Check if Paket submenu already exists
        $existing = $this->db->table('menu_lv2')
            ->where('id', '10-01')
            ->countAllResults();

        if ($existing === 0) {
            $this->db->table('menu_lv2')->insert([
                'id' => '10-01',
                'label' => 'Paket',
                'link' => 'admin/simak/paket',
                'icon' => 'far fa-circle',
                'header' => '10',
                'ordering' => 1,
            ]);
        }

        // Grant access to all roles
        if ($this->db->tableExists('menu_akses')) {
            $roleColumn = $this->db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';

            // Get all distinct roles
            $roles = $this->db->table('menu_akses')
                ->select($roleColumn)
                ->distinct()
                ->get()
                ->getResultArray();

            if (! empty($roles)) {
                foreach ($roles as $roleRow) {
                    $roleId = (int) ($roleRow[$roleColumn] ?? 0);
                    if ($roleId > 0) {
                        // Check if access already granted
                        $exists = $this->db->table('menu_akses')
                            ->where($roleColumn, $roleId)
                            ->where('menu_id', '10-01')
                            ->countAllResults();

                        if ($exists === 0) {
                            // Add for Paket menu
                            $this->db->table('menu_akses')->insert([
                                $roleColumn => $roleId,
                                'menu_id' => '10-01',
                                'FiturAdd' => 1,
                                'FiturEdit' => 1,
                                'FiturDelete' => 1,
                                'FiturExport' => 1,
                                'FiturImport' => 1,
                                'FiturApproval' => 0,
                            ]);
                        }
                    }
                }
            }
        }
    }

    public function down()
    {
        $this->db->table('menu_akses')
            ->where('menu_id', '10-01')
            ->delete();

        $this->db->table('menu_lv2')
            ->where('id', '10-01')
            ->delete();

        $this->db->table('menu_lv1')
            ->where('id', '10')
            ->delete();
    }
}
