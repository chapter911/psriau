<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMstMataAnggaran extends Migration
{
    public function up()
    {
        $db = $this->db;

        // 1. Create table 'mst_mata_anggaran'
        if (! $db->tableExists('mst_mata_anggaran')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'mata_anggaran' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => false,
                ],
                'berlaku_dari' => [
                    'type' => 'DATE',
                    'null' => false,
                ],
                'berlaku_hingga' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'status' => [
                    'type'       => 'ENUM',
                    'constraint' => ['aktif', 'tidak_aktif'],
                    'default'    => 'aktif',
                    'null'       => false,
                ],
                'created_by' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => true,
                ],
                'created_date' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_by' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => true,
                ],
                'updated_date' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('mata_anggaran');
            $this->forge->createTable('mst_mata_anggaran', true);
        }

        // 2. Add menu submenu under Master
        if (! $db->tableExists('menu_lv1') || ! $db->tableExists('menu_lv2')) {
            return;
        }

        $masterLv1Id = $this->findLv1IdByLabel('master');
        if ($masterLv1Id === null) {
            return;
        }

        $this->ensureLv2Menu($masterLv1Id, 'Mata Anggaran', 'admin/master/mata-anggaran', 'fas fa-wallet');
    }

    public function down()
    {
        $db = $this->db;

        // 1. Drop table
        $this->forge->dropTable('mst_mata_anggaran', true);

        // 2. Remove menu submenu
        if (! $db->tableExists('menu_lv2')) {
            return;
        }

        $row = $db->table('menu_lv2')
            ->select('id')
            ->where('LOWER(link)', 'admin/master/mata-anggaran')
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
            'id'       => $menuId,
            'label'    => $label,
            'link'     => $link,
            'icon'     => $icon,
            'header'   => $headerId,
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

            $exists = $this->db->table('menu_akses')
                ->where($roleColumn, $roleId)
                ->where('menu_id', $menuId)
                ->countAllResults() > 0;

            if (! $exists) {
                $this->db->table('menu_akses')->insert([
                    $roleColumn     => $roleId,
                    'menu_id'       => $menuId,
                    'FiturAdd'      => 1,
                    'FiturEdit'     => 1,
                    'FiturDelete'   => 1,
                    'FiturExport'   => 1,
                    'FiturImport'   => 1,
                    'FiturApproval' => 1,
                ]);
            }
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
