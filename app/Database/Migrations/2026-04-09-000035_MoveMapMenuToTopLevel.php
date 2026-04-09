<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MoveMapMenuToTopLevel extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('menu_lv1')) {
            return;
        }

        $mapMenuId = $this->resolveOrCreateTopLevelMapMenu();
        if ($mapMenuId === '') {
            return;
        }

        $this->ensureMenuAccess($mapMenuId);
        $this->removeLegacyLv2MapMenu();
    }

    public function down()
    {
        if ($this->db->tableExists('menu_akses')) {
            $this->db->table('menu_akses')
                ->where('menu_id', $this->resolveTopLevelMapMenuId())
                ->delete();
        }

        if ($this->db->tableExists('menu_lv1')) {
            $this->db->table('menu_lv1')
                ->groupStart()
                    ->where('LOWER(link)', 'admin/map')
                    ->orWhere('LOWER(link)', 'admin/dashboard/map')
                ->groupEnd()
                ->delete();
        }
    }

    private function resolveOrCreateTopLevelMapMenu(): string
    {
        $existing = $this->db->table('menu_lv1')
            ->select('id, ordering')
            ->groupStart()
                ->where('LOWER(link)', 'admin/map')
                ->orWhere('LOWER(link)', 'admin/dashboard/map')
                ->orWhere('LOWER(label)', 'map')
            ->groupEnd()
            ->orderBy('id', 'ASC')
            ->get()
            ->getRowArray();

        if (is_array($existing) && ! empty($existing['id'])) {
            $menuId = (string) $existing['id'];
            $this->db->table('menu_lv1')
                ->where('id', $menuId)
                ->update([
                    'label' => 'Map',
                    'link' => 'admin/map',
                    'icon' => 'fas fa-map-marked-alt',
                    'old_icon' => null,
                ]);

            return $menuId;
        }

        $menuId = $this->generateLv1Id();
        if ($menuId === '') {
            return '';
        }

        $this->db->table('menu_lv1')->insert([
            'id' => $menuId,
            'label' => 'Map',
            'link' => 'admin/map',
            'icon' => 'fas fa-map-marked-alt',
            'old_icon' => null,
            'ordering' => $this->nextLv1Ordering(),
        ]);

        return $menuId;
    }

    private function resolveTopLevelMapMenuId(): string
    {
        if (! $this->db->tableExists('menu_lv1')) {
            return '';
        }

        $row = $this->db->table('menu_lv1')
            ->select('id')
            ->where('LOWER(link)', 'admin/map')
            ->orderBy('id', 'ASC')
            ->get()
            ->getRowArray();

        return (string) ($row['id'] ?? '');
    }

    private function removeLegacyLv2MapMenu(): void
    {
        if (! $this->db->tableExists('menu_lv2')) {
            return;
        }

        $rows = $this->db->table('menu_lv2')
            ->select('id')
            ->where('LOWER(link)', 'admin/dashboard/map')
            ->get()
            ->getResultArray();

        if ($rows === []) {
            return;
        }

        $ids = array_values(array_filter(array_map(static fn (array $row): string => (string) ($row['id'] ?? ''), $rows), static fn (string $id): bool => $id !== ''));
        if ($ids === []) {
            return;
        }

        if ($this->db->tableExists('menu_akses')) {
            $this->db->table('menu_akses')->whereIn('menu_id', $ids)->delete();
        }

        $this->db->table('menu_lv2')->whereIn('id', $ids)->delete();
    }

    private function generateLv1Id(): string
    {
        $rows = $this->db->table('menu_lv1')
            ->select('id')
            ->get()
            ->getResultArray();

        $max = 0;
        foreach ($rows as $row) {
            $id = (string) ($row['id'] ?? '');
            if (preg_match('/(\d+)$/', $id, $matches)) {
                $max = max($max, (int) $matches[1]);
            }
        }

        return str_pad((string) ($max + 1), 2, '0', STR_PAD_LEFT);
    }

    private function nextLv1Ordering(): int
    {
        $row = $this->db->table('menu_lv1')
            ->selectMax('ordering', 'max_ordering')
            ->get()
            ->getRowArray();

        return ((int) ($row['max_ordering'] ?? 0)) + 1;
    }

    private function ensureMenuAccess(string $menuId): void
    {
        if ($menuId === '' || ! $this->db->tableExists('menu_akses')) {
            return;
        }

        $roleColumn = $this->db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';

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

            $exists = $this->db->table('menu_akses')
                ->where($roleColumn, $roleId)
                ->where('menu_id', $menuId)
                ->countAllResults();

            if ($exists > 0) {
                continue;
            }

            $isPrivileged = $roleId === 1 || $this->isSuperAdministratorRole($roleId, $roleColumn);
            $value = $isPrivileged ? 1 : 0;

            $this->db->table('menu_akses')->insert([
                $roleColumn => $roleId,
                'menu_id' => $menuId,
                'FiturAdd' => $value,
                'FiturEdit' => $value,
                'FiturDelete' => $value,
                'FiturExport' => $value,
                'FiturImport' => $value,
                'FiturApproval' => $value,
            ]);
        }
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
