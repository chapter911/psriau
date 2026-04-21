<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMasterSimakSubmenu extends Migration
{
    public function up()
    {
        $db = $this->db;

        if (! $db->tableExists('menu_lv1') || ! $db->tableExists('menu_lv2') || ! $db->tableExists('menu_lv3')) {
            return;
        }

        $masterId = $this->resolveMasterMenuId();
        if ($masterId === null || $masterId === '') {
            return;
        }

        $simakLv2Id = $this->findLv2IdByHeaderAndLabel($masterId, 'Simak');
        if ($simakLv2Id === null) {
            $simakLv2Id = $this->generateNextChildMenuId('menu_lv2', $masterId);
            $this->db->table('menu_lv2')->insert([
                'id' => $simakLv2Id,
                'label' => 'Simak',
                'link' => null,
                'icon' => 'far fa-circle',
                'header' => $masterId,
                'ordering' => $this->getNextOrdering('menu_lv2', $masterId),
            ]);
            $this->ensureMenuAksesForMenuId($simakLv2Id);
        }

        $this->ensureLv3Menu($simakLv2Id, 'Kontruksi', 'admin/kontrak/simak/konstruksi');
        $this->ensureLv3Menu($simakLv2Id, 'Konsultasi', 'admin/kontrak/simak/konsultasi');
    }

    public function down()
    {
        $db = $this->db;

        if (! $db->tableExists('menu_lv2') || ! $db->tableExists('menu_lv3')) {
            return;
        }

        $simakLv2Id = $this->findLv2IdByLabel('Simak');

        $targets = [
            'admin/kontrak/simak/konstruksi',
            'admin/kontrak/simak/konsultasi',
        ];

        foreach ($targets as $targetLink) {
            $row = $db->table('menu_lv3')
                ->select('id')
                ->where('LOWER(link)', strtolower($targetLink))
                ->get()
                ->getRowArray();

            if (! is_array($row) || ! isset($row['id'])) {
                continue;
            }

            $menuId = (string) $row['id'];
            $db->table('menu_lv3')->where('id', $menuId)->delete();
            $this->deleteMenuAksesByMenuId($menuId);
        }

        if ($simakLv2Id !== null) {
            $remainingChildren = (int) $db->table('menu_lv3')
                ->where('header', $simakLv2Id)
                ->countAllResults();

            if ($remainingChildren === 0) {
                $db->table('menu_lv2')->where('id', $simakLv2Id)->delete();
                $this->deleteMenuAksesByMenuId($simakLv2Id);
            }
        }
    }

    private function ensureLv3Menu(string $headerId, string $label, string $link): void
    {
        $existingByLink = $this->db->table('menu_lv3')
            ->select('id')
            ->where('header', $headerId)
            ->where('LOWER(link)', strtolower($link))
            ->get()
            ->getRowArray();

        if (is_array($existingByLink) && isset($existingByLink['id'])) {
            return;
        }

        $existingByLabel = $this->db->table('menu_lv3')
            ->select('id')
            ->where('header', $headerId)
            ->where('LOWER(label)', strtolower($label))
            ->get()
            ->getRowArray();

        if (is_array($existingByLabel) && isset($existingByLabel['id'])) {
            return;
        }

        $menuId = $this->generateNextChildMenuId('menu_lv3', $headerId);
        $this->db->table('menu_lv3')->insert([
            'id' => $menuId,
            'label' => $label,
            'link' => $link,
            'icon' => 'far fa-dot-circle',
            'header' => $headerId,
            'ordering' => $this->getNextOrdering('menu_lv3', $headerId),
        ]);

        $this->ensureMenuAksesForMenuId($menuId);
    }

    private function resolveMasterMenuId(): ?string
    {
        $fromLv2 = $this->db->table('menu_lv2')
            ->select('header')
            ->where('LOWER(link) LIKE', 'admin/master/%')
            ->orderBy('id', 'ASC')
            ->get()
            ->getRowArray();

        if (is_array($fromLv2) && isset($fromLv2['header']) && (string) $fromLv2['header'] !== '') {
            return (string) $fromLv2['header'];
        }

        $fromLv1 = $this->db->table('menu_lv1')
            ->select('id')
            ->where('LOWER(label)', 'master')
            ->orderBy('id', 'ASC')
            ->get()
            ->getRowArray();

        if (is_array($fromLv1) && isset($fromLv1['id']) && (string) $fromLv1['id'] !== '') {
            return (string) $fromLv1['id'];
        }

        return null;
    }

    private function findLv2IdByHeaderAndLabel(string $header, string $label): ?string
    {
        $row = $this->db->table('menu_lv2')
            ->select('id')
            ->where('header', $header)
            ->where('LOWER(label)', strtolower($label))
            ->orderBy('id', 'ASC')
            ->get()
            ->getRowArray();

        if (! is_array($row) || ! isset($row['id'])) {
            return null;
        }

        return (string) $row['id'];
    }

    private function findLv2IdByLabel(string $label): ?string
    {
        $row = $this->db->table('menu_lv2')
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
