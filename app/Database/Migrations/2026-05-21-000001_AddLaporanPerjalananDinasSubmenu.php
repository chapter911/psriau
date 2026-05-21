<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLaporanPerjalananDinasSubmenu extends Migration
{
    public function up()
    {
        $db = $this->db;

        if (! $db->tableExists('menu_lv1') || ! $db->tableExists('menu_lv2')) {
            return;
        }

        $laporanLv1Id = $this->findLv1IdByLabel('Laporan');
        if ($laporanLv1Id === null) {
            return;
        }

        $this->ensureLv2Menu($laporanLv1Id, 'Perjalanan Dinas', 'admin/laporan/perjalanan-dinas');
    }

    public function down()
    {
        $db = $this->db;

        if (! $db->tableExists('menu_lv2')) {
            return;
        }

        $targetLink = 'admin/laporan/perjalanan-dinas';
        $row = $db->table('menu_lv2')
            ->select('id')
            ->where('LOWER(link)', strtolower($targetLink))
            ->get()
            ->getRowArray();

        if (! is_array($row) || ! isset($row['id'])) {
            return;
        }

        $menuId = (string) $row['id'];
        $db->table('menu_lv2')->where('id', $menuId)->delete();
        $this->deleteMenuAksesByMenuId($menuId);
    }

    private function ensureLv2Menu(string $headerId, string $label, string $link): void
    {
        $existingByLink = $this->db->table('menu_lv2')
            ->select('id')
            ->where('header', $headerId)
            ->where('LOWER(link)', strtolower($link))
            ->get()
            ->getRowArray();

        if (is_array($existingByLink) && isset($existingByLink['id'])) {
            return;
        }

        $existingByLabel = $this->db->table('menu_lv2')
            ->select('id')
            ->where('header', $headerId)
            ->where('LOWER(label)', strtolower($label))
            ->get()
            ->getRowArray();

        if (is_array($existingByLabel) && isset($existingByLabel['id'])) {
            return;
        }

        $menuId = $this->generateNextChildMenuId('menu_lv2', $headerId);
        $this->db->table('menu_lv2')->insert([
            'id' => $menuId,
            'label' => $label,
            'link' => $link,
            'icon' => 'far fa-circle',
            'header' => $headerId,
            'ordering' => $this->getNextOrdering('menu_lv2', $headerId),
        ]);

        $this->ensureMenuAksesForMenuId($menuId);
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

    private function generateNextChildMenuId(string $table, string $header): string
    {
        $rows = $this->db->table($table)
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

    private function getNextOrdering(string $table, string $header): int
    {
        $row = $this->db->table($table)
            ->selectMax('ordering', 'max_ordering')
            ->where('header', $header)
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
