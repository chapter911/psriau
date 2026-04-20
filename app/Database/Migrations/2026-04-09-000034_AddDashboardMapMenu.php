<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDashboardMapMenu extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('menu_lv1') || ! $this->db->tableExists('menu_lv2')) {
            return;
        }

        $dashboardMenu = $this->db->table('menu_lv1')
            ->select('id, label, link')
            ->groupStart()
                ->where('LOWER(link)', 'admin')
                ->orWhere('LOWER(link)', '/admin')
            ->groupEnd()
            ->orderBy('id', 'ASC')
            ->get()
            ->getRowArray();

        if (! is_array($dashboardMenu)) {
            $dashboardMenu = $this->db->table('menu_lv1')
                ->select('id, label, link')
                ->where('LOWER(label)', 'dashboard')
                ->orderBy('id', 'ASC')
                ->get()
                ->getRowArray();
        }

        if (! is_array($dashboardMenu) || empty($dashboardMenu['id'])) {
            return;
        }

        $headerId = (string) $dashboardMenu['id'];

        $existing = $this->db->table('menu_lv2')
            ->select('id')
            ->where('LOWER(link)', 'admin/dashboard/map')
            ->orderBy('id', 'ASC')
            ->get()
            ->getRowArray();

        if (is_array($existing) && ! empty($existing['id'])) {
            $menuId = (string) $existing['id'];
        } else {
            $menuId = $this->generateLv2Id($headerId);

            $this->db->table('menu_lv2')->insert([
                'id' => $menuId,
                'label' => 'Map',
                'link' => 'admin/dashboard/map',
                'icon' => 'far fa-map',
                'header' => $headerId,
                'ordering' => $this->nextLv2Ordering($headerId),
            ]);
        }

        $this->ensureMenuAccess($menuId);
    }

    public function down()
    {
        if ($this->db->tableExists('menu_akses')) {
            $this->db->table('menu_akses')
                ->where('menu_id', $this->resolveInsertedMenuId())
                ->delete();
        }

        if ($this->db->tableExists('menu_lv2')) {
            $this->db->table('menu_lv2')
                ->where('LOWER(link)', 'admin/dashboard/map')
                ->delete();
        }
    }

    private function resolveInsertedMenuId(): string
    {
        if (! $this->db->tableExists('menu_lv2')) {
            return '';
        }

        $row = $this->db->table('menu_lv2')
            ->select('id')
            ->where('LOWER(link)', 'admin/dashboard/map')
            ->orderBy('id', 'ASC')
            ->get()
            ->getRowArray();

        return (string) ($row['id'] ?? '');
    }

    private function generateLv2Id(string $headerId): string
    {
        $rows = $this->db->table('menu_lv2')
            ->select('id')
            ->where('header', $headerId)
            ->get()
            ->getResultArray();

        $max = 0;
        $prefix = $headerId . '-';

        foreach ($rows as $row) {
            $id = (string) ($row['id'] ?? '');
            if (strpos($id, $prefix) !== 0) {
                continue;
            }

            $suffix = substr($id, strlen($prefix));
            if (preg_match('/^(\d+)$/', $suffix, $matches)) {
                $max = max($max, (int) $matches[1]);
            }
        }

        return $headerId . '-' . str_pad((string) ($max + 1), 2, '0', STR_PAD_LEFT);
    }

    private function nextLv2Ordering(string $headerId): int
    {
        $row = $this->db->table('menu_lv2')
            ->selectMax('ordering', 'max_ordering')
            ->where('header', $headerId)
            ->get()
            ->getRowArray();

        return ((int) ($row['max_ordering'] ?? 0)) + 1;
    }

    private function ensureMenuAccess(string $menuId): void
    {
        if ($menuId === '' || ! $this->db->tableExists('menu_akses')) {
            return;
        }

        $fields = $this->menuAksesFields();
        $hasRoleId = in_array('role_id', $fields, true);
        $hasGroupId = in_array('group_id', $fields, true);
        $roleColumn = $hasRoleId ? 'role_id' : ($hasGroupId ? 'group_id' : '');
        if ($roleColumn === '') {
            return;
        }

        $roleRows = $this->db->table('menu_akses')
            ->select($roleColumn)
            ->distinct()
            ->get()
            ->getResultArray();

        if ($roleRows === []) {
            $roleRows = [[$roleColumn => 1]];
        }

        foreach ($roleRows as $roleRow) {
            $roleId = (int) ($roleRow[$roleColumn] ?? 0);
            if ($roleId <= 0) {
                continue;
            }

            $existsBuilder = $this->db->table('menu_akses')->where('menu_id', $menuId);
            $existsBuilder->groupStart()->where($roleColumn, $roleId);
            if ($roleColumn === 'role_id' && $hasGroupId) {
                $existsBuilder->orWhere('group_id', $roleId);
            }
            if ($roleColumn === 'group_id' && $hasRoleId) {
                $existsBuilder->orWhere('role_id', $roleId);
            }
            $existsBuilder->groupEnd();

            $exists = (int) $existsBuilder->countAllResults();

            if ($exists > 0) {
                continue;
            }

            $isPrivileged = $roleId === 1 || $this->isSuperAdministratorRole($roleId, $roleColumn);
            $value = $isPrivileged ? 1 : 0;

            $insertData = [
                $roleColumn => $roleId,
                'menu_id' => $menuId,
                'FiturAdd' => $value,
                'FiturEdit' => $value,
                'FiturDelete' => $value,
                'FiturExport' => $value,
                'FiturImport' => $value,
                'FiturApproval' => $value,
            ];

            if ($hasRoleId) {
                $insertData['role_id'] = $roleId;
            }
            if ($hasGroupId) {
                $insertData['group_id'] = $roleId;
            }

            $this->db->table('menu_akses')->insert($insertData);
        }
    }

    private function menuAksesFields(): array
    {
        if (! $this->db->tableExists('menu_akses')) {
            return [];
        }

        $fields = [];
        $result = $this->db->query('SHOW COLUMNS FROM menu_akses')->getResultArray();
        foreach ($result as $row) {
            $name = strtolower((string) ($row['Field'] ?? ''));
            if ($name !== '') {
                $fields[] = $name;
            }
        }

        return $fields;
    }

    private function isSuperAdministratorRole(int $roleId, string $roleColumn): bool
    {
        if ($roleColumn !== 'role_id' || ! $this->db->tableExists('access_roles')) {
            return false;
        }

        $row = $this->db->table('access_roles')
            ->select('role_key')
            ->where('id', $roleId)
            ->get()
            ->getRowArray();

        $roleKey = strtolower(trim((string) ($row['role_key'] ?? '')));

        return in_array($roleKey, ['super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);
    }
}
