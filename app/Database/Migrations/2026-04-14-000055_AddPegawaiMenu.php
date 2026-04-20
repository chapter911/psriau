<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPegawaiMenu extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('menu_lv1') || ! $this->db->tableExists('menu_lv2')) {
            return;
        }

        $masterId = $this->findOrCreateLv1Menu('Master', 'fas fa-boxes');
        if ($masterId === null) {
            return;
        }

        $pegawaiId = $this->upsertLv2ByLink('admin/master/pegawai', 'Pegawai', 'fas fa-user-tie', $masterId);

        if ($this->db->tableExists('menu_akses')) {
            $this->ensureAksesForMenu($masterId);
            $this->ensureAksesForMenu($pegawaiId);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('menu_lv2')) {
            return;
        }

        $menuRow = $this->db->table('menu_lv2')
            ->select('id')
            ->where('LOWER(link)', 'admin/master/pegawai')
            ->get()
            ->getRowArray();

        if (! is_array($menuRow) || empty($menuRow['id'])) {
            return;
        }

        $menuId = (string) $menuRow['id'];

        if ($this->db->tableExists('menu_akses')) {
            $this->db->table('menu_akses')->where('menu_id', $menuId)->delete();
        }

        $this->db->table('menu_lv2')->where('id', $menuId)->delete();
    }

    private function findOrCreateLv1Menu(string $label, string $icon): ?string
    {
        $existing = $this->db->table('menu_lv1')
            ->select('id')
            ->where('LOWER(label)', strtolower($label))
            ->orderBy('ordering', 'ASC')
            ->get()
            ->getRowArray();

        if (is_array($existing) && ! empty($existing['id'])) {
            return (string) $existing['id'];
        }

        $newId = $this->generateLv1Id();
        if ($newId === '') {
            return null;
        }

        $this->db->table('menu_lv1')->insert([
            'id' => $newId,
            'label' => $label,
            'link' => '#',
            'icon' => $icon,
            'old_icon' => null,
            'ordering' => $this->nextLv1Ordering(),
        ]);

        return $newId;
    }

    private function upsertLv2ByLink(string $link, string $label, string $icon, string $header): string
    {
        $existing = $this->db->table('menu_lv2')
            ->select('id')
            ->where('LOWER(link)', strtolower($link))
            ->get()
            ->getRowArray();

        if (is_array($existing) && ! empty($existing['id'])) {
            $menuId = (string) $existing['id'];
            $this->db->table('menu_lv2')->where('id', $menuId)->update([
                'label' => $label,
                'icon' => $icon,
                'header' => $header,
            ]);

            return $menuId;
        }

        $menuId = $this->generateLv2Id($header);
        $maxRow = $this->db->table('menu_lv2')
            ->selectMax('ordering', 'max_ordering')
            ->where('header', $header)
            ->get()
            ->getRowArray();

        $this->db->table('menu_lv2')->insert([
            'id' => $menuId,
            'label' => $label,
            'link' => $link,
            'icon' => $icon,
            'header' => $header,
            'ordering' => ((int) ($maxRow['max_ordering'] ?? 0)) + 1,
        ]);

        return $menuId;
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

    private function generateLv2Id(string $header): string
    {
        $rows = $this->db->table('menu_lv2')
            ->select('id')
            ->where('header', $header)
            ->get()
            ->getResultArray();

        $max = 0;
        $prefix = $header . '-';
        foreach ($rows as $row) {
            $id = (string) ($row['id'] ?? '');
            if (strpos($id, $prefix) === 0) {
                $suffix = substr($id, strlen($prefix));
                if (ctype_digit($suffix)) {
                    $max = max($max, (int) $suffix);
                }
            }
        }

        return $header . '-' . str_pad((string) ($max + 1), 2, '0', STR_PAD_LEFT);
    }

    private function ensureAksesForMenu(string $menuId): void
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

            $isPrivileged = $roleId === 1 || $this->isSuperAdministratorRole($roleId);
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

    private function isSuperAdministratorRole(int $roleId): bool
    {
        if (! $this->db->tableExists('access_roles')) {
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
