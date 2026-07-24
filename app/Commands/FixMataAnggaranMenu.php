<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class FixMataAnggaranMenu extends BaseCommand
{
    protected $group       = 'Maintenance';
    protected $name        = 'fix:mata-anggaran-menu';
    protected $description = 'Ensures Master Mata Anggaran menu and permissions are properly set up in database.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        // 1. Find master lv1 ID
        $masterLv1 = $db->table('menu_lv1')
            ->select('id')
            ->where('LOWER(label)', 'master')
            ->orWhere('LOWER(id)', 'master')
            ->get()
            ->getRowArray();

        $headerId = isset($masterLv1['id']) ? (string) $masterLv1['id'] : 'master';

        // 2. Ensure menu_lv2 entry
        $menuLv2 = $db->table('menu_lv2')
            ->where('LOWER(link)', 'admin/master/mata-anggaran')
            ->get()
            ->getRowArray();

        if (! is_array($menuLv2)) {
            // Generate next child menu ID
            $rows = $db->table('menu_lv2')->select('id')->where('header', $headerId)->get()->getResultArray();
            $maxSeq = 0;
            foreach ($rows as $r) {
                $cid = (string) ($r['id'] ?? '');
                $prefix = $headerId . '-';
                if (strpos($cid, $prefix) === 0) {
                    $suffix = substr($cid, strlen($prefix));
                    if (preg_match('/^(\d+)$/', $suffix, $m)) {
                        $maxSeq = max($maxSeq, (int) $m[1]);
                    }
                }
            }
            $menuId = $headerId . '-' . str_pad((string) ($maxSeq + 1), 2, '0', STR_PAD_LEFT);

            $maxOrdRow = $db->table('menu_lv2')->selectMax('ordering', 'max_ord')->where('header', $headerId)->get()->getRowArray();
            $nextOrd = ((int) ($maxOrdRow['max_ord'] ?? 0)) + 1;

            $db->table('menu_lv2')->insert([
                'id'       => $menuId,
                'label'    => 'Mata Anggaran',
                'link'     => 'admin/master/mata-anggaran',
                'icon'     => 'fas fa-wallet',
                'header'   => $headerId,
                'ordering' => $nextOrd,
            ]);

            CLI::write("Created menu_lv2 item ID: {$menuId}", 'green');
        } else {
            $menuId = (string) $menuLv2['id'];
            CLI::write("Found existing menu_lv2 item ID: {$menuId}", 'yellow');
        }

        // 3. Ensure menu_akses entries for all roles/groups
        if ($db->tableExists('menu_akses')) {
            $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : ($db->fieldExists('group_id', 'menu_akses') ? 'group_id' : null);

            if ($roleColumn !== null) {
                // Get all roles
                $roleIds = [1, 2];
                if ($db->tableExists('access_roles')) {
                    $roles = $db->table('access_roles')->select('id')->get()->getResultArray();
                    foreach ($roles as $r) {
                        $roleIds[] = (int) $r['id'];
                    }
                }
                $roleIds = array_values(array_unique(array_filter($roleIds)));

                foreach ($roleIds as $rId) {
                    $exists = $db->table('menu_akses')
                        ->where($roleColumn, $rId)
                        ->where('menu_id', $menuId)
                        ->countAllResults() > 0;

                    if (! $exists) {
                        $db->table('menu_akses')->insert([
                            $roleColumn     => $rId,
                            'menu_id'       => $menuId,
                            'FiturAdd'      => 1,
                            'FiturEdit'     => 1,
                            'FiturDelete'   => 1,
                            'FiturExport'   => 1,
                            'FiturImport'   => 1,
                            'FiturApproval' => 1,
                        ]);
                        CLI::write("Granted menu_akses for role ID {$rId} to menu {$menuId}", 'green');
                    } else {
                        // Make sure permissions are set to 1 for this menu
                        $db->table('menu_akses')
                            ->where($roleColumn, $rId)
                            ->where('menu_id', $menuId)
                            ->update([
                                'FiturAdd'      => 1,
                                'FiturEdit'     => 1,
                                'FiturDelete'   => 1,
                                'FiturExport'   => 1,
                                'FiturImport'   => 1,
                                'FiturApproval' => 1,
                            ]);
                        CLI::write("Updated menu_akses permissions for role ID {$rId} on menu {$menuId}", 'yellow');
                    }
                }
            }
        }

        CLI::write('Mata Anggaran menu fix completed!', 'green');
    }
}
