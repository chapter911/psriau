<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MovePerjalananDinasToSuratSurat extends Migration
{
    public function up()
    {
        $db = $this->db;

        if (! $db->tableExists('menu_lv1') || ! $db->tableExists('menu_lv2') || ! $db->tableExists('menu_akses')) {
            return;
        }

        // 1. Find or create "Surat-surat" menu_lv1
        $suratSuratId = $this->findLv1IdByLabel('Surat-surat');

        if ($suratSuratId === null) {
            // Create "Surat-surat" menu if not exists
            $suratSuratId = $this->generateNextLv1Id();
            $maxOrder = $this->getMaxLv1Ordering();
            $db->table('menu_lv1')->insert([
                'id' => $suratSuratId,
                'label' => 'Surat-surat',
                'link' => null,
                'icon' => 'fas fa-envelope-open-text',
                'ordering' => $maxOrder + 1,
            ]);
            $this->ensureMenuAksesForMenuId($suratSuratId);
        }

        // 2. Delete existing "Perjalanan Dinas" from "Laporan" menu (if exists)
        $laporanId = $this->findLv1IdByLabel('Laporan');
        if ($laporanId !== null) {
            $this->deleteLv2ByHeaderAndLabel($laporanId, 'Perjalanan Dinas');
        }

        // Also delete any existing Perjalanan Dinas menu entry that might be elsewhere
        $this->deleteLv2ByLink('admin/laporan/perjalanan-dinas');
        $this->deleteLv2ByLink('admin/surat/perjalanan-dinas');

        // 3. Create/Update "Perjalanan Dinas" under "Surat-surat"
        $existingPerjadin = $this->findLv2ByHeaderAndLabel($suratSuratId, 'Perjalanan Dinas');
        if ($existingPerjadin === null) {
            // Create new menu entry
            $menuId = $this->generateNextLv2Id($suratSuratId);
            $maxOrder = $this->getMaxLv2Ordering($suratSuratId);
            $db->table('menu_lv2')->insert([
                'id' => $menuId,
                'label' => 'Perjalanan Dinas',
                'link' => 'admin/surat/perjalanan-dinas',
                'icon' => 'far fa-plane',
                'header' => $suratSuratId,
                'ordering' => $maxOrder + 1,
            ]);
            $this->ensureMenuAksesForMenuId($menuId);
        } else {
            // Update existing entry to point to new link
            $db->table('menu_lv2')
                ->where('id', $existingPerjadin)
                ->update(['link' => 'admin/surat/perjalanan-dinas', 'icon' => 'far fa-plane']);
        }

        // 4. Create "Lupa Absen" under "Surat-surat" (if not exists)
        $existingLupaAbsen = $this->findLv2ByHeaderAndLabel($suratSuratId, 'Lupa Absen');
        if ($existingLupaAbsen === null) {
            $menuId = $this->generateNextLv2Id($suratSuratId);
            $maxOrder = $this->getMaxLv2Ordering($suratSuratId);
            $db->table('menu_lv2')->insert([
                'id' => $menuId,
                'label' => 'Lupa Absen',
                'link' => 'admin/surat/lupa-absen',
                'icon' => 'far fa-clock',
                'header' => $suratSuratId,
                'ordering' => $maxOrder + 1,
            ]);
            $this->ensureMenuAksesForMenuId($menuId);
        }
    }

    public function down()
    {
        // This is a one-way migration to reorganize menus
        // down() is not implemented as it would require complex state tracking
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

    private function findLv2ByHeaderAndLabel(string $headerId, string $label): ?string
    {
        $row = $this->db->table('menu_lv2')
            ->select('id')
            ->where('header', $headerId)
            ->where('LOWER(label)', strtolower($label))
            ->get()
            ->getRowArray();

        return isset($row['id']) ? (string) $row['id'] : null;
    }

    private function deleteLv2ByHeaderAndLabel(string $headerId, string $label): void
    {
        $row = $this->db->table('menu_lv2')
            ->select('id')
            ->where('header', $headerId)
            ->where('LOWER(label)', strtolower($label))
            ->get()
            ->getRowArray();

        if (isset($row['id'])) {
            $menuId = (string) $row['id'];
            $db = $this->db;
            $db->table('menu_lv2')->where('id', $menuId)->delete();
            $db->table('menu_akses')->where('menu_id', $menuId)->delete();
        }
    }

    private function deleteLv2ByLink(string $link): void
    {
        $row = $this->db->table('menu_lv2')
            ->select('id')
            ->where('LOWER(link)', strtolower($link))
            ->get()
            ->getRowArray();

        if (isset($row['id'])) {
            $menuId = (string) $row['id'];
            $this->db->table('menu_lv2')->where('id', $menuId)->delete();
            $this->db->table('menu_akses')->where('menu_id', $menuId)->delete();
        }
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

    private function getMaxLv1Ordering(): int
    {
        $row = $this->db->table('menu_lv1')
            ->selectMax('ordering', 'max_ordering')
            ->get()
            ->getRowArray();

        return (int) ($row['max_ordering'] ?? 0);
    }

    private function getMaxLv2Ordering(string $headerId): int
    {
        $row = $this->db->table('menu_lv2')
            ->selectMax('ordering', 'max_ordering')
            ->where('header', $headerId)
            ->get()
            ->getRowArray();

        return (int) ($row['max_ordering'] ?? 0);
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
}
