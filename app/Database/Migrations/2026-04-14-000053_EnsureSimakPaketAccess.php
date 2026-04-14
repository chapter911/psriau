<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnsureSimakPaketAccess extends Migration
{
    public function up()
    {
        // Step 1: Ensure Simak menu exists in menu_lv1
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

        // Step 2: Ensure Paket submenu exists in menu_lv2
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

        // Step 3: Remove old access entries for this menu
        $this->db->table('menu_akses')
            ->where('menu_id', '10-01')
            ->delete();

        // Step 4: Grant access to all roles for Paket menu
        if ($this->db->tableExists('menu_akses')) {
            // Detect which column name is used
            $roleColumn = $this->db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';

            // Get all distinct roles
            $roles = $this->db->table('menu_akses')
                ->select($roleColumn)
                ->distinct()
                ->get()
                ->getResultArray();

            if (!empty($roles)) {
                foreach ($roles as $role) {
                    $roleId = (int) ($role[$roleColumn] ?? 0);
                    if ($roleId > 0) {
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

        log_message('info', 'Simak/Paket menu structure and access verified');
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
