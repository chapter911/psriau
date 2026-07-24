<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTutorialFlowchartMenu extends Migration
{
    public function up()
    {
        $db = $this->db;

        if (! $db->tableExists('menu_lv1')) {
            return;
        }

        // Check if menu_tutorial already exists
        $existing = $db->table('menu_lv1')
            ->select('id')
            ->where('LOWER(link)', 'admin/tutorial')
            ->orWhere('LOWER(label)', 'tutorial / flowchart')
            ->get()
            ->getRowArray();

        $menuId = 'menu_tutorial';

        if (is_array($existing) && isset($existing['id'])) {
            $menuId = (string) $existing['id'];
        } else {
            // Find max ordering
            $maxOrderingRow = $db->table('menu_lv1')->selectMax('ordering', 'max_order')->get()->getRowArray();
            $maxOrder = ((int) ($maxOrderingRow['max_order'] ?? 80)) + 1;

            $db->table('menu_lv1')->insert([
                'id' => $menuId,
                'label' => 'Tutorial / Flowchart',
                'link' => 'admin/tutorial',
                'icon' => 'fas fa-sitemap',
                'ordering' => $maxOrder,
            ]);
        }

        // Grant access to ALL roles in menu_akses
        if ($db->tableExists('menu_akses')) {
            $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : ($db->fieldExists('group_id', 'menu_akses') ? 'group_id' : null);
            
            if ($roleColumn !== null) {
                // Fetch all role IDs or default 1, 2, 3
                $roleIds = [1, 2, 3];
                if ($db->tableExists('access_roles')) {
                    $rows = $db->table('access_roles')->select('id')->get()->getResultArray();
                    if (! empty($rows)) {
                        $roleIds = array_map(static fn (array $r): int => (int) $r['id'], $rows);
                    }
                }

                foreach ($roleIds as $rId) {
                    $exists = (int) $db->table('menu_akses')
                        ->where($roleColumn, $rId)
                        ->where('menu_id', $menuId)
                        ->countAllResults();

                    if ($exists === 0) {
                        $db->table('menu_akses')->insert([
                            $roleColumn => $rId,
                            'menu_id' => $menuId,
                            'FiturAdd' => 1,
                            'FiturEdit' => 1,
                            'FiturDelete' => 1,
                            'FiturExport' => 1,
                            'FiturImport' => 1,
                            'FiturApproval' => 1,
                        ]);
                    }
                }
            }
        }
    }

    public function down()
    {
        $db = $this->db;

        if ($db->tableExists('menu_lv1')) {
            $db->table('menu_lv1')->where('id', 'menu_tutorial')->orWhere('link', 'admin/tutorial')->delete();
        }

        if ($db->tableExists('menu_akses')) {
            $db->table('menu_akses')->where('menu_id', 'menu_tutorial')->delete();
        }
    }
}
