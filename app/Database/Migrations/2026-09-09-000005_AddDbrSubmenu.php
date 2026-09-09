<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDbrSubmenu extends Migration
{
    public function up()
    {
        $db = $this->db;

        if (! $db->tableExists('menu_lv1') || ! $db->tableExists('menu_lv2') || ! $db->tableExists('menu_akses')) {
            return;
        }

        // Find "Inventarisasi" main menu ID
        $inventarisRow = $db->table('menu_lv1')
            ->select('id')
            ->where('LOWER(label)', 'inventarisasi')
            ->get()
            ->getRowArray();

        $headerId = isset($inventarisRow['id']) ? (string) $inventarisRow['id'] : null;
        if ($headerId === null) {
            return;
        }

        // Check if "admin/inventaris/dbr" already exists
        $existing = $db->table('menu_lv2')
            ->where('header', $headerId)
            ->where('LOWER(link)', 'admin/inventaris/dbr')
            ->get()
            ->getRowArray();

        if ($existing !== null) {
            return;
        }

        // Generate next ID
        $menuId = $this->generateNextLv2Id($headerId);

        // Position it between Satker and Sekolah
        $db->table('menu_lv2')->insert([
            'id'       => $menuId,
            'label'    => 'Daftar Barang Ruangan (DBR)',
            'link'     => 'admin/inventaris/dbr',
            'icon'     => 'fas fa-door-open',
            'header'   => $headerId,
            'ordering' => 2,
        ]);

        // Shift Inventaris Sekolah to ordering 3
        $db->table('menu_lv2')
            ->where('header', $headerId)
            ->where('LOWER(link)', 'admin/inventaris/sekolah')
            ->update(['ordering' => 3]);

        $this->ensureMenuAksesForMenuId($menuId);
    }

    public function down()
    {
        $db = $this->db;

        if ($db->tableExists('menu_lv2')) {
            $rows = $db->table('menu_lv2')
                ->select('id')
                ->where('LOWER(link)', 'admin/inventaris/dbr')
                ->get()
                ->getResultArray();

            foreach ($rows as $row) {
                $menuId = (string) ($row['id'] ?? '');
                if ($menuId !== '') {
                    $db->table('menu_lv2')->where('id', $menuId)->delete();
                    if ($db->tableExists('menu_akses')) {
                        $db->table('menu_akses')->where('menu_id', $menuId)->delete();
                    }
                }
            }
        }
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
}
