<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStrukturOrganisasiTable extends Migration
{
    public function up()
    {
        $db = $this->db;

        // 1. Create table tb_struktur_organisasi
        if (! $db->tableExists('tb_struktur_organisasi')) {
            $forge = \Config\Database::forge();
            $forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'parent_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'pegawai_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'jabatan_bagian' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => false,
                ],
                'kategori_kelompok' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                    'default'    => 'utama',
                ],
                'urutan' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 1,
                ],
                'level' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 1,
                ],
                'is_active' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 1,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);

            $forge->addKey('id', true);
            $forge->addKey('parent_id');
            $forge->addKey('pegawai_id');
            $forge->addKey('is_active');
            $forge->createTable('tb_struktur_organisasi', true);

            // Seed default nodes matching Satker PPS Riau structure template
            $now = date('Y-m-d H:i:s');
            
            // Level 1: Menteri
            $db->table('tb_struktur_organisasi')->insert([
                'id' => 1,
                'parent_id' => null,
                'pegawai_id' => null,
                'jabatan_bagian' => 'Menteri Pekerjaan Umum',
                'kategori_kelompok' => 'pimpinan',
                'urutan' => 1,
                'level' => 1,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Level 2: Dirjen
            $db->table('tb_struktur_organisasi')->insert([
                'id' => 2,
                'parent_id' => 1,
                'pegawai_id' => null,
                'jabatan_bagian' => 'Plt. Direktur Jenderal Prasarana Strategis',
                'kategori_kelompok' => 'pimpinan',
                'urutan' => 1,
                'level' => 2,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Level 3: Kasatker
            $db->table('tb_struktur_organisasi')->insert([
                'id' => 3,
                'parent_id' => 2,
                'pegawai_id' => null,
                'jabatan_bagian' => 'Kasatker Pelaksanaan Prasarana Strategis Riau',
                'kategori_kelompok' => 'pimpinan',
                'urutan' => 1,
                'level' => 3,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Level 4: 4 Branches under Kasatker
            $db->table('tb_struktur_organisasi')->insertBatch([
                [
                    'id' => 4,
                    'parent_id' => 3,
                    'pegawai_id' => null,
                    'jabatan_bagian' => 'Kasubbag Umum & Tata Usaha',
                    'kategori_kelompok' => 'subbag',
                    'urutan' => 1,
                    'level' => 4,
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id' => 5,
                    'parent_id' => 3,
                    'pegawai_id' => null,
                    'jabatan_bagian' => 'PPK Prasarana Strategis I',
                    'kategori_kelompok' => 'ppk1',
                    'urutan' => 2,
                    'level' => 4,
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id' => 6,
                    'parent_id' => 3,
                    'pegawai_id' => null,
                    'jabatan_bagian' => 'PPK Prasarana Strategis II',
                    'kategori_kelompok' => 'ppk2',
                    'urutan' => 3,
                    'level' => 4,
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id' => 7,
                    'parent_id' => 3,
                    'pegawai_id' => null,
                    'jabatan_bagian' => 'PPK Prasarana Strategis III',
                    'kategori_kelompok' => 'ppk3',
                    'urutan' => 4,
                    'level' => 4,
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }

        // 2. Add menu_lv2 entry under Master Data
        if ($db->tableExists('menu_lv1') && $db->tableExists('menu_lv2')) {
            $masterLv1Id = $this->findLv1IdByLabel('master');
            if ($masterLv1Id !== null) {
                $this->ensureLv2Menu($masterLv1Id, 'Struktur Organisasi', 'admin/master/struktur-organisasi', 'fas fa-sitemap');
            }
        }
    }

    public function down()
    {
        $db = $this->db;

        if ($db->tableExists('tb_struktur_organisasi')) {
            $forge = \Config\Database::forge();
            $forge->dropTable('tb_struktur_organisasi', true);
        }

        if ($db->tableExists('menu_lv2')) {
            $row = $db->table('menu_lv2')
                ->select('id')
                ->where('LOWER(link)', 'admin/master/struktur-organisasi')
                ->get()
                ->getRowArray();

            if (is_array($row) && isset($row['id'])) {
                $menuId = (string) $row['id'];
                $db->table('menu_lv2')->where('id', $menuId)->delete();
                $this->deleteMenuAksesByMenuId($menuId);
            }
        }
    }

    private function findLv1IdByLabel(string $keyword): ?string
    {
        $row = $this->db->table('menu_lv1')
            ->select('id')
            ->like('LOWER(label)', strtolower($keyword))
            ->get()
            ->getRowArray();

        if (is_array($row) && isset($row['id'])) {
            return (string) $row['id'];
        }

        return null;
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
            'header'   => $headerId,
            'label'    => $label,
            'link'     => $link,
            'icon'     => $icon,
            'ordering' => $this->getNextOrdering('menu_lv2', $headerId),
        ]);

        $this->ensureMenuAksesForMenuId($menuId);
    }

    private function generateNextChildMenuId(string $table, string $header): string
    {
        $rows = $this->db->table($table)
            ->select('id')
            ->where('header', $header)
            ->get()
            ->getResultArray();

        $maxNum = 0;
        $prefix = $header . '-';

        foreach ($rows as $row) {
            $idStr = (string) $row['id'];
            if (strpos($idStr, $prefix) === 0) {
                $suffix = substr($idStr, strlen($prefix));
                if (ctype_digit($suffix)) {
                    $val = (int) $suffix;
                    if ($val > $maxNum) {
                        $maxNum = $val;
                    }
                }
            }
        }

        return sprintf('%s-%02d', $header, $maxNum + 1);
    }

    private function getNextOrdering(string $table, string $header): int
    {
        $row = $this->db->table($table)
            ->selectMax('ordering', 'max_ordering')
            ->where('header', $header)
            ->get()
            ->getRowArray();

        $maxVal = is_array($row) ? (int) ($row['max_ordering'] ?? 0) : 0;

        return $maxVal + 1;
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
            ->groupBy($roleColumn)
            ->get()
            ->getResultArray();

        $roleIds = array_filter(array_map(static fn (array $row): int => (int) ($row[$roleColumn] ?? 0), $roleRows));
        if (empty($roleIds)) {
            $roleIds = [1, 2, 3];
        }

        foreach ($roleIds as $roleId) {
            $exists = (int) $this->db->table('menu_akses')
                ->where($roleColumn, $roleId)
                ->where('menu_id', $menuId)
                ->countAllResults();

            if ($exists > 0) {
                continue;
            }

            $this->db->table('menu_akses')->insert([
                $roleColumn => $roleId,
                'menu_id'   => $menuId,
                'is_active' => 1,
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
