<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCutiSubmenu extends Migration
{
    public function up()
    {
        $db = $this->db;

        if (! $db->tableExists('menu_lv1') || ! $db->tableExists('menu_lv2') || ! $db->tableExists('menu_akses')) {
            return;
        }

        // Find "Surat-surat" main menu ID
        $suratSuratRow = $db->table('menu_lv1')
            ->select('id')
            ->where('LOWER(label)', 'surat-surat')
            ->get()
            ->getRowArray();

        $headerId = isset($suratSuratRow['id']) ? (string) $suratSuratRow['id'] : null;

        if ($headerId === null) {
            // Fallback find any lv1 menu containing "surat"
            $suratRow = $db->table('menu_lv1')
                ->select('id')
                ->like('LOWER(label)', 'surat')
                ->get()
                ->getRowArray();
            $headerId = isset($suratRow['id']) ? (string) $suratRow['id'] : null;
        }

        if ($headerId === null) {
            return;
        }

        // Check if "admin/surat/cuti" already exists in menu_lv2
        $existing = $db->table('menu_lv2')
            ->where('header', $headerId)
            ->where('LOWER(link)', 'admin/surat/cuti')
            ->get()
            ->getRowArray();

        if ($existing !== null) {
            return;
        }

        // Generate next ID for menu_lv2 under headerId
        $menuId = $this->generateNextLv2Id($headerId);

        // Get max ordering under headerId
        $maxOrderRow = $db->table('menu_lv2')
            ->selectMax('ordering', 'max_ordering')
            ->where('header', $headerId)
            ->get()
            ->getRowArray();
        $ordering = ((int) ($maxOrderRow['max_ordering'] ?? 0)) + 1;

        $db->table('menu_lv2')->insert([
            'id'       => $menuId,
            'label'    => 'Cuti',
            'link'     => 'admin/surat/cuti',
            'icon'     => 'far fa-calendar-alt',
            'header'   => $headerId,
            'ordering' => $ordering,
        ]);

        $this->ensureMenuAksesForMenuId($menuId);
    }

    public function down()
    {
        $db = $this->db;

        if ($db->tableExists('menu_lv2')) {
            $cutiRows = $db->table('menu_lv2')
                ->select('id')
                ->where('LOWER(link)', 'admin/surat/cuti')
                ->get()
                ->getResultArray();

            foreach ($cutiRows as $row) {
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

            $isAdministrator = $roleId === 1;

            $this->db->table('menu_akses')->insert([
                $roleColumn     => $roleId,
                'menu_id'       => $menuId,
                'FiturAdd'      => $isAdministrator ? 1 : 0,
                'FiturEdit'     => $isAdministrator ? 1 : 0,
                'FiturDelete'   => $isAdministrator ? 1 : 0,
                'FiturExport'   => $isAdministrator ? 1 : 0,
                'FiturImport'   => $isAdministrator ? 1 : 0,
                'FiturApproval' => $isAdministrator ? 1 : 0,
            ]);
        }
    }
}
