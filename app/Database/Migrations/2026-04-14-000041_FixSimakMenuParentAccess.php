<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixSimakMenuParentAccess extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('menu_akses')) {
            return;
        }

        // Check if Paket menu (10) has access entry for super_administrator (role_id=1)
        $roleColumn = $this->db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';
        
        $exists = $this->db->table('menu_akses')
            ->where($roleColumn, 1)
            ->where('menu_id', '10')
            ->countAllResults();

        // If not exists, create it
        if ($exists === 0) {
            $this->db->table('menu_akses')->insert([
                $roleColumn => 1,
                'menu_id' => '10',
                'FiturAdd' => 1,
                'FiturEdit' => 1,
                'FiturDelete' => 0,
                'FiturExport' => 1,
                'FiturImport' => 1,
                'FiturApproval' => 0,
            ]);
        }

        // Also ensure for all other active roles (2=admin, 3=editor)
        $roleIds = [1, 2, 3];
        foreach ($roleIds as $rId) {
            $exists = $this->db->table('menu_akses')
                ->where($roleColumn, $rId)
                ->where('menu_id', '10')
                ->countAllResults();

            if ($exists === 0) {
                $isPrivileged = $rId === 1;
                $this->db->table('menu_akses')->insert([
                    $roleColumn => $rId,
                    'menu_id' => '10',
                    'FiturAdd' => $isPrivileged ? 1 : 0,
                    'FiturEdit' => $isPrivileged ? 1 : 0,
                    'FiturDelete' => 0,
                    'FiturExport' => $isPrivileged ? 1 : 0,
                    'FiturImport' => $isPrivileged ? 1 : 0,
                    'FiturApproval' => 0,
                ]);
            }
        }
    }

    public function down()
    {
        // No-op
    }
}
