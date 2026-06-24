<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSuratSuratMenuWithSubmenus extends Migration
{
    public function up()
    {
        $db = $this->db;

        if (! $db->tableExists('menu_lv1') || ! $db->tableExists('menu_lv2') || ! $db->tableExists('menu_akses')) {
            return;
        }

        // 1. Create "Surat-surat" main menu item (menu_lv1)
        $suratSuratMenuId = $this->findOrCreateLv1Menu('Surat-surat', null, 'fas fa-envelope-open-text', $this->getNextLv1Ordering());

        if ($suratSuratMenuId === null) {
            return;
        }

        // 2. Create "Perjalanan Dinas" submenu under "Surat-surat"
        $this->ensureLv2Menu($suratSuratMenuId, 'Perjalanan Dinas', 'admin/surat/perjalanan-dinas', 'far fa-plane', $this->getNextLv2Ordering($suratSuratMenuId));

        // 3. Create "Lupa Absen" submenu under "Surat-surat"
        $this->ensureLv2Menu($suratSuratMenuId, 'Lupa Absen', 'admin/surat/lupa-absen', 'far fa-clock', $this->getNextLv2Ordering($suratSuratMenuId));
    }

    public function down()
    {
        $db = $this->db;

        if (! $db->tableExists('menu_lv2') || ! $db->tableExists('menu_lv1') || ! $db->tableExists('menu_akses')) {
            return;
        }

        // Delete submenus under "Surat-surat"
        $suratSuratId = $this->findLv1IdByLabel('Surat-surat');
        if ($suratSuratId !== null) {
            // Delete all menu_lv2 entries where header = suratSuratId
            $lv2Rows = $db->table('menu_lv2')
                ->select('id')
                ->where('header', $suratSuratId)
                ->get()
                ->getResultArray();

            foreach ($lv2Rows as $row) {
                $menuId = (string) ($row['id'] ?? '');
                if ($menuId !== '') {
                    $db->table('menu_lv2')->where('id', $menuId)->delete();
                    $this->deleteMenuAksesByMenuId($menuId);
                }
            }

            // Delete "Surat-surat" menu_lv1
            $db->table('menu_lv1')->where('id', $suratSuratId)->delete();
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

        if (! is_array($row) || ! isset($row['id'])) {
            return null;
        }

        return (string) $row['id'];
    }

    private function findOrCreateLv1Menu(string $label, ?string $link, string $icon, int $ordering): ?string
    {
        $existingId = $this->findLv1IdByLabel($label);
        if ($existingId !== null) {
            return $existingId;
        }

        // Generate next menu_lv1 ID
        $menuId = $this->generateNextLv1Id();

        $this->db->table('menu_lv1')->insert([
            'id' => $menuId,
            'label' => $label,
            'link' => $link,
            'icon' => $icon,
            'ordering' => $ordering,
        ]);

        // Give admin (role_id=1) access to this menu
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

        return (string) ($maxSequence + 1);
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
        // Check if exists by link
        $existingByLink = $this->db->table('menu_lv2')
            ->select('id')
            ->where('header', $headerId)
            ->where('LOWER(link)', strtolower($link))
            ->get()
            ->getRowArray();

        if (is_array($existingByLink) && isset($existingByLink['id'])) {
            return;
        }

        // Check if exists by label
        $existingByLabel = $this->db->table('menu_lv2')
            ->select('id')
            ->where('header', $headerId)
            ->where('LOWER(label)', strtolower($label))
            ->get()
            ->getRowArray();

        if (is_array($existingByLabel) && isset($existingByLabel['id'])) {
            return;
        }

        // Generate ID
        $menuId = $this->generateNextLv2Id($headerId);

        $this->db->table('menu_lv2')->insert([
            'id' => $menuId,
            'label' => $label,
            'link' => $link,
            'icon' => $icon,
            'header' => $headerId,
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

    private function getNextLv2Ordering(string $headerId): int
    {
        $row = $this->db->table('menu_lv2')
            ->selectMax('ordering', 'max_ordering')
            ->where('header', $headerId)
            ->get()
            ->getRowArray();

        return ((int) ($row['max_ordering'] ?? 0)) + 1;
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

            $isAdministrator = $roleId === 1;

            $this->db->table('menu_akses')->insert([
                $roleColumn => $roleId,
                'menu_id' => $menuId,
                'FiturAdd' => $isAdministrator ? 1 : 0,
                'FiturEdit' => $isAdministrator ? 1 : 0,
                'FiturDelete' => $isAdministrator ? 1 : 0,
                'FiturExport' => $isAdministrator ? 1 : 0,
                'FiturImport' => $isAdministrator ? 1 : 0,
                'FiturApproval' => $isAdministrator ? 1 : 0,
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
