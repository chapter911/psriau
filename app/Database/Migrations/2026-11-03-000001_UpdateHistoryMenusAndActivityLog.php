<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateHistoryMenusAndActivityLog extends Migration
{
    public function up()
    {
        $db = $this->db;

        // 1. Update menu_lv2 for History submenus (Header 07)
        if ($db->tableExists('menu_lv2')) {
            // Update 07-01 to Activity History
            $db->table('menu_lv2')->where('id', '07-01')->update([
                'label'    => 'Activity History',
                'link'     => 'admin/history',
                'icon'     => 'fas fa-history',
                'ordering' => 1,
            ]);

            // Update 07-02 to Login History
            $db->table('menu_lv2')->where('id', '07-02')->update([
                'label'    => 'Login History',
                'link'     => 'admin/history/login',
                'icon'     => 'fas fa-user-shield',
                'ordering' => 2,
            ]);

            // Remove 07-03 (Delete History) as it is merged into Activity History
            $db->table('menu_lv2')->where('id', '07-03')->delete();
        }

        // 2. Clean up menu_akses for 07-03
        if ($db->tableExists('menu_akses')) {
            $db->table('menu_akses')->where('menu_id', '07-03')->delete();

            // Ensure role_id 1, 2, 3 have entries for 07-01 and 07-02
            foreach ([1, 2, 3] as $roleId) {
                foreach (['07-01', '07-02'] as $menuId) {
                    $exists = $db->table('menu_akses')
                        ->where('role_id', $roleId)
                        ->where('menu_id', $menuId)
                        ->countAllResults();

                    if ($exists === 0) {
                        $db->table('menu_akses')->insert([
                            'role_id'       => $roleId,
                            'menu_id'       => $menuId,
                            'FiturAdd'      => 0,
                            'FiturEdit'     => 0,
                            'FiturDelete'   => 0,
                            'FiturExport'   => 1,
                            'FiturImport'   => 0,
                            'FiturApproval' => 0,
                        ]);
                    }
                }
            }
        }
    }

    public function down()
    {
        $db = $this->db;

        if ($db->tableExists('menu_lv2')) {
            // Revert 07-01 to Login
            $db->table('menu_lv2')->where('id', '07-01')->update([
                'label'    => 'Login',
                'link'     => 'admin/history/login',
                'icon'     => 'far fa-circle',
                'ordering' => 1,
            ]);

            // Revert 07-02 to Edit
            $db->table('menu_lv2')->where('id', '07-02')->update([
                'label'    => 'Edit',
                'link'     => 'admin/history/edit',
                'icon'     => 'far fa-circle',
                'ordering' => 2,
            ]);

            // Re-insert 07-03 Delete
            $exists0703 = $db->table('menu_lv2')->where('id', '07-03')->countAllResults();
            if ($exists0703 === 0) {
                $db->table('menu_lv2')->insert([
                    'id'       => '07-03',
                    'label'    => 'Delete',
                    'link'     => 'admin/history/delete',
                    'icon'     => 'far fa-circle',
                    'header'   => '07',
                    'ordering' => 3,
                ]);
            }
        }

        if ($db->tableExists('menu_akses')) {
            foreach ([1, 2, 3] as $roleId) {
                $exists = $db->table('menu_akses')
                    ->where('role_id', $roleId)
                    ->where('menu_id', '07-03')
                    ->countAllResults();

                if ($exists === 0) {
                    $db->table('menu_akses')->insert([
                        'role_id'       => $roleId,
                        'menu_id'       => '07-03',
                        'FiturAdd'      => 0,
                        'FiturEdit'     => 0,
                        'FiturDelete'   => 0,
                        'FiturExport'   => 0,
                        'FiturImport'   => 0,
                        'FiturApproval' => 0,
                    ]);
                }
            }
        }
    }
}

