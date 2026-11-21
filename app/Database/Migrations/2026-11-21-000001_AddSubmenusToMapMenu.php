<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSubmenusToMapMenu extends Migration
{
    public function up()
    {
        $db = $this->db;

        // 1. Update menu_lv1 Map link to '#'
        if ($db->tableExists('menu_lv1')) {
            $db->table('menu_lv1')->where('id', '08')->update([
                'link' => '#',
            ]);
        }

        // 2. Insert menu_lv2 submenus
        if ($db->tableExists('menu_lv2')) {
            $submenus = [
                [
                    'id'       => '08-01',
                    'header'   => '08',
                    'label'    => 'Sebaran Sekolah',
                    'link'     => 'admin/map',
                    'icon'     => 'fas fa-school',
                    'ordering' => 1,
                ],
                [
                    'id'       => '08-02',
                    'header'   => '08',
                    'label'    => 'Rencana Perjalanan',
                    'link'     => 'admin/map/perjalanan',
                    'icon'     => 'fas fa-route',
                    'ordering' => 2,
                ],
            ];

            foreach ($submenus as $sm) {
                $exists = (int) $db->table('menu_lv2')->where('id', $sm['id'])->countAllResults();
                if ($exists === 0) {
                    $db->table('menu_lv2')->insert($sm);
                } else {
                    $db->table('menu_lv2')->where('id', $sm['id'])->update($sm);
                }
            }
        }

        // 3. Insert menu_akses for both submenus
        if ($db->tableExists('menu_akses')) {
            $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';
            $rolesWithMap = $db->table('menu_akses')
                ->select($roleColumn)
                ->where('menu_id', '08')
                ->distinct()
                ->get()
                ->getResultArray();

            $targetRoles = ! empty($rolesWithMap) ? array_column($rolesWithMap, $roleColumn) : [1, 2, 3, 5, 7];

            foreach (['08-01', '08-02'] as $subId) {
                foreach ($targetRoles as $rid) {
                    $rid = (int) $rid;
                    $already = (int) $db->table('menu_akses')
                        ->where($roleColumn, $rid)
                        ->where('menu_id', $subId)
                        ->countAllResults();

                    if ($already === 0) {
                        $db->table('menu_akses')->insert([
                            $roleColumn     => $rid,
                            'menu_id'       => $subId,
                            'FiturAdd'      => in_array($rid, [1, 2], true) ? 1 : 0,
                            'FiturEdit'     => in_array($rid, [1, 2], true) ? 1 : 0,
                            'FiturDelete'   => in_array($rid, [1, 2], true) ? 1 : 0,
                            'FiturExport'   => 1,
                            'FiturImport'   => in_array($rid, [1, 2], true) ? 1 : 0,
                            'FiturApproval' => in_array($rid, [1, 2], true) ? 1 : 0,
                        ]);
                    }
                }
            }
        }
    }

    public function down()
    {
        $db = $this->db;

        if ($db->tableExists('menu_akses')) {
            $db->table('menu_akses')->whereIn('menu_id', ['08-01', '08-02'])->delete();
        }

        if ($db->tableExists('menu_lv2')) {
            $db->table('menu_lv2')->whereIn('id', ['08-01', '08-02'])->delete();
        }

        if ($db->tableExists('menu_lv1')) {
            $db->table('menu_lv1')->where('id', '08')->update([
                'link' => 'admin/map',
            ]);
        }
    }
}
