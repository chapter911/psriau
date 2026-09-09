<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddInventarisasiMenuWithSubmenus extends Migration
{
    public function up()
    {
        $db = $this->db;

        if (! $db->tableExists('menu_lv1') || ! $db->tableExists('menu_lv2') || ! $db->tableExists('menu_akses')) {
            return;
        }

        // 1. Find or create "Inventarisasi" main menu item (menu_lv1)
        $inventarisasiMenuId = $this->findOrCreateLv1Menu('Inventarisasi', null, 'fas fa-boxes-stacked', $this->getNextLv1Ordering());

        if ($inventarisasiMenuId === null) {
            return;
        }

        // 2. Ensure "Inventaris Satker" submenu exists under "Inventarisasi"
        $this->ensureLv2Menu($inventarisasiMenuId, 'Inventaris Satker', 'admin/inventaris/satker', 'fas fa-building', 1);

        // 3. Ensure "Inventaris Sekolah" submenu exists under "Inventarisasi"
        $this->ensureLv2Menu($inventarisasiMenuId, 'Inventaris Sekolah', 'admin/inventaris/sekolah', 'fas fa-school', 2);
    }

    public function down()
    {
        $db = $this->db;

        if (! $db->tableExists('menu_lv2') || ! $db->tableExists('menu_lv1') || ! $db->tableExists('menu_akses')) {
            return;
        }

        $inventarisasiId = $this->findLv1IdByLabel('Inventarisasi');
        if ($inventarisasiId !== null) {
            $lv2Rows = $db->table('menu_lv2')
                ->select('id')
                ->where('header', $inventarisasiId)
                ->get()
                ->getResultArray();

            foreach ($lv2Rows as $row) {
                $menuId = (string) ($row['id'] ?? '');
                if ($menuId !== '') {
                    $db->table('menu_lv2')->where('id', $menuId)->delete();
                    $this->deleteMenuAksesByMenuId($menuId);
                }
            }

            $db->table('menu_lv1')->where('id', $inventarisasiId)->delete();
            $this->deleteMenuAksesByMenuId($inventarisasiId);
        }
    }

    private function findLv1IdByLabel(string $label): ?string
    {
        $row = $this->db->table('menu_lv1')
            ->select('id')
            ->where('LOWER(label)', strtolower($label))
            ->orderBy('id', 'ASC')
            ->get()
            ->getRowArray();

        return isset($row['id']) ? (string) $row['id'] : null;
    }

    private function findLv2ByHeaderAndLink(string $headerId, string $link): ?string
    {
        $row = $this->db->table('menu_lv2')
            ->select('id')
            ->where('header', $headerId)
            ->where('LOWER(link)', strtolower($link))
            ->get()
            ->getRowArray();

        return isset($row['id']) ? (string) $row['id'] : null;
    }

    private function findOrCreateLv1Menu(string $label, ?string $link, string $icon, int $ordering): ?string
    {
        $existingId = $this->findLv1IdByLabel($label);
        if ($existingId !== null) {
            return $existingId;
        }

        $menuId = $this->generateNextLv1Id();

        $this->db->table('menu_lv1')->insert([
            'id'       => $menuId,
            'label'    => $label,
            'link'     => $link,
            'icon'     => $icon,
            'ordering' => $ordering,
        ]);

        $this->ensureMenuAksesForMenuId($menuId);

        return $menuId;
    }

    private function generateNextLv1Id(): string
    {
        $rows = $this->db->table('menu_lv1')
            ->select('id')
            ->get()
            ->getResultArray();

        $maxSequence = 0;
        foreach ($rows as $row) {
            $candidateId = (string) ($row['id'] ?? '');
            if (preg_match('/^(\d+)$/', $candidateId, $matches)) {
                $maxSequence = max($maxSequence, (int) $matches[1]);
            }
        }

        return str_pad((string) ($maxSequence + 1), 2, '0', STR_PAD_LEFT);
    }

    private function getNextLv1Ordering(): int
    {
        $row = $this->db->table('menu_lv1')
            ->selectMax('ordering', 'max_ordering')
            ->get()
            ->getRowArray();

        return ((int) ($row['max_ordering'] ?? 0)) + 1;
    }

    private function ensureLv2Menu(string $headerId, string $label, string $link, string $icon, int $ordering): void
    {
        $existingByLink = $this->findLv2ByHeaderAndLink($headerId, $link);
        if ($existingByLink !== null) {
            return;
        }

        $existingByLabel = $this->db->table('menu_lv2')
            ->select('id')
            ->where('header', $headerId)
            ->where('LOWER(label)', strtolower($label))
            ->get()
            ->getRowArray();

        if (isset($existingByLabel['id'])) {
            return;
        }

        $menuId = $this->generateNextLv2Id($headerId);

        $this->db->table('menu_lv2')->insert([
            'id'       => $menuId,
            'label'    => $label,
            'link'     => $link,
            'icon'     => $icon,
            'header'   => $headerId,
            'ordering' => $ordering,
        ]);

        $this->ensureMenuAksesForMenuId($menuId);
    }

    private function generateNextLv2Id(string $header): string
    {
        $rows = $this->db->table('menu_lv2')
            ->select('id')
            ->where('header', $header)
            ->get()
            ->getResultArray();

        $maxSequence = 0;
        foreach ($rows as $row) {
            $candidateId = (string) ($row['id'] ?? '');
            $prefix = $header . '-';
            if (strpos($candidateId, $prefix) !== 0) {
                continue;
            }

            $suffix = substr($candidateId, strlen($prefix));
            if (preg_match('/^(\d+)$/', $suffix, $matches)) {
                $maxSequence = max($maxSequence, (int) $matches[1]);
            }
        }

        return $header . '-' . str_pad((string) ($maxSequence + 1), 2, '0', STR_PAD_LEFT);
    }

    private function ensureMenuAksesForMenuId(string $menuId): void
    {
        if (! $this->db->tableExists('menu_akses')) {
            return;
        }

        $roleColumn = $this->db->fieldExists('role_id', 'menu_akses') ? 'role_id' : ($this->db->fieldExists('group_id', 'menu_akses') ? 'group_id' : null);
        if ($roleColumn === null) {
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

            $exists = (int) $this->db->table('menu_akses')
                ->where($roleColumn, $roleId)
                ->where('menu_id', $menuId)
                ->countAllResults();

            if ($exists > 0) {
                continue;
            }

            // Grant full CRUD for Super Administrator (1) and Admin (2)
            $isSuperOrAdmin = in_array($roleId, [1, 2], true);

            $this->db->table('menu_akses')->insert([
                $roleColumn     => $roleId,
                'menu_id'       => $menuId,
                'FiturAdd'      => $isSuperOrAdmin ? 1 : 0,
                'FiturEdit'     => $isSuperOrAdmin ? 1 : 0,
                'FiturDelete'   => $isSuperOrAdmin ? 1 : 0,
                'FiturExport'   => $isSuperOrAdmin ? 1 : 0,
                'FiturImport'   => $isSuperOrAdmin ? 1 : 0,
                'FiturApproval' => $isSuperOrAdmin ? 1 : 0,
            ]);
        }
    }

    private function deleteMenuAksesByMenuId(string $menuId): void
    {
        if (! $this->db->tableExists('menu_akses')) {
            return;
        }

        $this->db->table('menu_akses')->where('menu_id', $menuId)->delete();
    }
}
