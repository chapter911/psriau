<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKonsultanIndividualTablesAndMenus extends Migration
{
    public function up()
    {
        $db = $this->db;

        // 1. Create trn_konsultan_kontrak table
        if (! $db->tableExists('trn_konsultan_kontrak')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'pegawai_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'tanggal_mulai' => [
                    'type' => 'DATE',
                ],
                'tanggal_selesai' => [
                    'type' => 'DATE',
                ],
                'file_pdf' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'file_size' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'default'    => 0,
                ],
                'file_original_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'keterangan' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'created_by' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'updated_by' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addKey('pegawai_id');
            $this->forge->createTable('trn_konsultan_kontrak', true);
        }

        // 2. Create trn_konsultan_laporan_bulanan table
        if (! $db->tableExists('trn_konsultan_laporan_bulanan')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'pegawai_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'periode_bulan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 7, // Format: YYYY-MM
                ],
                'tahun' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                ],
                'bulan' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                ],
                'file_pdf' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'file_size' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'default'    => 0,
                ],
                'file_original_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'keterangan' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'created_by' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'updated_by' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addKey('pegawai_id');
            $this->forge->addKey('periode_bulan');
            $this->forge->createTable('trn_konsultan_laporan_bulanan', true);
        }

        // 3. Register Menu Level 1: "Konsultan Individual"
        if ($db->tableExists('menu_lv1') && $db->tableExists('menu_lv2')) {
            $konsultanLv1Id = $this->findOrCreateLv1Menu('Konsultan Individual', null, 'fas fa-user-tie', $this->getNextLv1Ordering());

            if ($konsultanLv1Id !== null) {
                // Register Submenu 1: "Kontrak"
                $this->ensureLv2Menu(
                    $konsultanLv1Id,
                    'Kontrak',
                    'admin/konsultan-individual/kontrak',
                    'fas fa-file-contract',
                    1
                );

                // Register Submenu 2: "Laporan Bulanan"
                $this->ensureLv2Menu(
                    $konsultanLv1Id,
                    'Laporan Bulanan',
                    'admin/konsultan-individual/laporan-bulanan',
                    'fas fa-calendar-check',
                    2
                );
            }
        }
    }

    public function down()
    {
        $db = $this->db;

        // 1. Remove Submenus and Menu
        if ($db->tableExists('menu_lv1') && $db->tableExists('menu_lv2')) {
            $konsultanId = $this->findLv1IdByLabel('Konsultan Individual');
            if ($konsultanId !== null) {
                $lv2Rows = $db->table('menu_lv2')
                    ->select('id')
                    ->where('header', $konsultanId)
                    ->get()
                    ->getResultArray();

                foreach ($lv2Rows as $row) {
                    $menuId = (string) ($row['id'] ?? '');
                    if ($menuId !== '') {
                        $db->table('menu_lv2')->where('id', $menuId)->delete();
                        $this->deleteMenuAksesByMenuId($menuId);
                    }
                }

                $db->table('menu_lv1')->where('id', $konsultanId)->delete();
                $this->deleteMenuAksesByMenuId($konsultanId);
            }
        }

        // 2. Drop tables
        $this->forge->dropTable('trn_konsultan_laporan_bulanan', true);
        $this->forge->dropTable('trn_konsultan_kontrak', true);
    }

    private function findOrCreateLv1Menu(string $label, ?string $link, string $icon, int $ordering): ?string
    {
        $existingId = $this->findLv1IdByLabel($label);
        if ($existingId !== null) {
            $this->db->table('menu_lv1')
                ->where('id', $existingId)
                ->update([
                    'icon' => $icon,
                ]);
            $this->ensureMenuAksesForMenuId($existingId);
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
            $this->db->table('menu_lv2')
                ->where('id', $existingByLink)
                ->update([
                    'label'    => $label,
                    'icon'     => $icon,
                    'ordering' => $ordering,
                ]);
            $this->ensureMenuAksesForMenuId($existingByLink);
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
            $roleRows = [[$roleColumn => 1], [$roleColumn => 2], [$roleColumn => 3]];
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

            // Role 1 (Super Admin), Role 2 (Admin), Role 3 (Editor/Konsultan)
            $isSuperOrAdmin = in_array($roleId, [1, 2], true);
            $isEditorOrStaff = $roleId === 3;

            $this->db->table('menu_akses')->insert([
                $roleColumn     => $roleId,
                'menu_id'       => $menuId,
                'FiturAdd'      => ($isSuperOrAdmin || $isEditorOrStaff) ? 1 : 0,
                'FiturEdit'     => ($isSuperOrAdmin || $isEditorOrStaff) ? 1 : 0,
                'FiturDelete'   => ($isSuperOrAdmin || $isEditorOrStaff) ? 1 : 0,
                'FiturExport'   => 1,
                'FiturImport'   => $isSuperOrAdmin ? 1 : 0,
                'FiturApproval' => $isSuperOrAdmin ? 1 : 0,
            ]);
        }
    }

    private function deleteMenuAksesByMenuId(string $menuId): void
    {
        if ($this->db->tableExists('menu_akses')) {
            $this->db->table('menu_akses')->where('menu_id', $menuId)->delete();
        }
    }
}
