<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class GrantSuperAdministratorFullMenuAccess extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('access_roles') || ! $this->db->tableExists('menu_akses')) {
            return;
        }

        $roleRow = $this->db->table('access_roles')
            ->select('id')
            ->groupStart()
                ->where('role_key', 'super_administrator')
                ->orWhere('role_key', 'super administrator')
                ->orWhere('role_key', 'super-admin')
                ->orWhere('role_key', 'superadmin')
            ->groupEnd()
            ->orderBy('id', 'ASC')
            ->get()
            ->getRowArray();

        if (! is_array($roleRow) || ! isset($roleRow['id'])) {
            return;
        }

        $roleId = (int) $roleRow['id'];
        if ($roleId <= 0) {
            return;
        }

        $roleColumn = $this->db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';
        $menuIds = [];

        if ($this->db->tableExists('menu_lv1')) {
            foreach ($this->db->table('menu_lv1')->select('id')->get()->getResultArray() as $row) {
                $menuIds[] = (string) ($row['id'] ?? '');
            }
        }
        if ($this->db->tableExists('menu_lv2')) {
            foreach ($this->db->table('menu_lv2')->select('id')->get()->getResultArray() as $row) {
                $menuIds[] = (string) ($row['id'] ?? '');
            }
        }
        if ($this->db->tableExists('menu_lv3')) {
            foreach ($this->db->table('menu_lv3')->select('id')->get()->getResultArray() as $row) {
                $menuIds[] = (string) ($row['id'] ?? '');
            }
        }

        $menuIds = array_values(array_unique(array_filter($menuIds, static fn (string $id): bool => trim($id) !== '')));
        if ($menuIds === []) {
            return;
        }

        $rows = [];
        foreach ($menuIds as $menuId) {
            $rows[] = [
                $roleColumn => $roleId,
                'menu_id' => $menuId,
                'FiturAdd' => 1,
                'FiturEdit' => 1,
                'FiturDelete' => 1,
                'FiturExport' => 1,
                'FiturImport' => 1,
                'FiturApproval' => 1,
            ];
        }

        $this->db->transStart();
        $this->db->table('menu_akses')->where($roleColumn, $roleId)->delete();
        $this->db->table('menu_akses')->insertBatch($rows);
        $this->db->transComplete();
    }

    public function down()
    {
        if (! $this->db->tableExists('access_roles') || ! $this->db->tableExists('menu_akses')) {
            return;
        }

        $roleRow = $this->db->table('access_roles')
            ->select('id')
            ->where('role_key', 'super_administrator')
            ->orderBy('id', 'ASC')
            ->get()
            ->getRowArray();

        if (! is_array($roleRow) || ! isset($roleRow['id'])) {
            return;
        }

        $roleColumn = $this->db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';
        $this->db->table('menu_akses')->where($roleColumn, (int) $roleRow['id'])->delete();
    }
}
