<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddApiDocumentationMenu extends Migration
{
    public function up()
    {
        $db = $this->db;

        if (! $db->tableExists('menu_lv1') || ! $db->tableExists('menu_lv2')) {
            return;
        }

        // Find "Pengaturan" menu_lv1 id (header)
        $pengaturanLv1Id = $this->findLv1IdByLabel('pengaturan');
        if ($pengaturanLv1Id === null) {
            return;
        }

        // Add the menu under "Pengaturan"
        $this->ensureLv2Menu($pengaturanLv1Id, 'API', 'admin/pengaturan/api', 'fas fa-code');
    }

    public function down()
    {
        $db = $this->db;

        if (! $db->tableExists('menu_lv2')) {
            return;
        }

        $row = $db->table('menu_lv2')
            ->select('id')
            ->where('LOWER(link)', 'admin/pengaturan/api')
            ->get()
            ->getRowArray();

        if (is_array($row) && isset($row['id'])) {
            $menuId = (string) $row['id'];
            $db->table('menu_lv2')->where('id', $menuId)->delete();
            $this->deleteMenuAksesByMenuId($menuId);
        }
    }

    private function ensureLv2Menu(string $headerId, string $label, string $link, string $icon): void
    {
        // Check if already exists by link
        $existingByLink = $this->db->table('menu_lv2')
            ->select('id')
            ->where('header', $headerId)
            ->where('LOWER(link)', strtolower($link))
            ->get()
            ->getRowArray();

        if (is_array($existingByLink) && isset($existingByLink['id'])) {
            return;
        }

        // Check if already exists by label
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
            'icon' => $icon,
            'header' => $headerId,
            'ordering' => $this->getNextOrdering('menu_lv2', $headerId),
        ]);

        $this->ensureMenuAksesForSuperAdminOnly($menuId);
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

    private function ensureMenuAksesForSuperAdminOnly(string $menuId): void
    {
        if (! $this->db->tableExists('menu_akses')) {
            return;
        }

        $roleColumn = $this->db->fieldExists('role_id', 'menu_akses') ? 'role_id' : ($this->db->fieldExists('group_id', 'menu_akses') ? 'group_id' : null);
        if ($roleColumn === null) {
            return;
        }

        // Insert access mapping ONLY for Super Administrator (role_id = 1)
        $superAdminRoleId = 1;

        $exists = (int) $this->db->table('menu_akses')
            ->where($roleColumn, $superAdminRoleId)
            ->where('menu_id', $menuId)
            ->countAllResults();

        if ($exists === 0) {
            $this->db->table('menu_akses')->insert([
                $roleColumn => $superAdminRoleId,
                'menu_id' => $menuId,
                'FiturAdd' => 1,
                'FiturEdit' => 1,
                'FiturDelete' => 1,
                'FiturExport' => 1,
                'FiturImport' => 1,
                'FiturApproval' => 1,
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
