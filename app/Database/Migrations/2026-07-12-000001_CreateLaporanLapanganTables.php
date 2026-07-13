<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLaporanLapanganTables extends Migration
{
    public function up()
    {
        $db = $this->db;

        // 1. Table: laporan_lapangan_proyek
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'paket_id' => [
                'type' => 'INT',
                'null' => false,
            ],
            'sekolah_npsn' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
            ],
            'tanggal' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'jam_mulai' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => false,
            ],
            'jam_selesai' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => false,
            ],
            'cuaca_json' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'pengawas' => [
                'type' => 'INT',
                'null' => false,
                'default' => 0,
            ],
            'pelaksana' => [
                'type' => 'INT',
                'null' => false,
                'default' => 0,
            ],
            'mandor' => [
                'type' => 'INT',
                'null' => false,
                'default' => 0,
            ],
            'tukang' => [
                'type' => 'INT',
                'null' => false,
                'default' => 0,
            ],
            'pekerja' => [
                'type' => 'INT',
                'null' => false,
                'default' => 0,
            ],
            'nama_pelapor' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
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
        $this->forge->addKey('id', true);
        $this->forge->addKey(['sekolah_npsn', 'tanggal']);
        $this->forge->createTable('laporan_lapangan_proyek', true);

        // 2. Table: laporan_lapangan_pekerjaan
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'rab_detail_id' => [
                'type' => 'INT',
                'null' => false,
            ],
            'tanggal' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'status_selesai' => [
                'type' => 'TINYINT',
                'null' => false,
                'default' => 0,
            ],
            'progres_persen' => [
                'type' => 'DOUBLE',
                'null' => false,
                'default' => 0.0,
            ],
            'keterangan_progres' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'kendala' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'foto_paths_json' => [
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
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('rab_detail_id');
        $this->forge->addKey('tanggal');
        $this->forge->createTable('laporan_lapangan_pekerjaan', true);

        // 3. Setup Menu in Sidebar
        $this->createMenu();
    }

    public function down()
    {
        $this->forge->dropTable('laporan_lapangan_pekerjaan', true);
        $this->forge->dropTable('laporan_lapangan_proyek', true);

        $db = $this->db;
        if ($db->tableExists('menu_lv2')) {
            $row = $db->table('menu_lv2')
                ->select('id')
                ->where('LOWER(link)', 'admin/laporan/lapangan')
                ->get()
                ->getRowArray();

            if (is_array($row) && isset($row['id'])) {
                $menuId = (string) $row['id'];
                $db->table('menu_lv2')->where('id', $menuId)->delete();
                if ($db->tableExists('menu_akses')) {
                    $db->table('menu_akses')->where('menu_id', $menuId)->delete();
                }
            }
        }
    }

    private function createMenu(): void
    {
        $db = $this->db;

        if (! $db->tableExists('menu_lv1') || ! $db->tableExists('menu_lv2')) {
            return;
        }

        // Find Laporan menu_lv1 id
        $row = $db->table('menu_lv1')
            ->select('id')
            ->where('LOWER(label)', 'laporan')
            ->get()
            ->getRowArray();

        if (! is_array($row) || ! isset($row['id'])) {
            return;
        }

        $laporanLv1Id = (string) $row['id'];
        $link = 'admin/laporan/lapangan';
        $label = 'Laporan Lapangan';

        // Check if exists
        $exists = $db->table('menu_lv2')
            ->where('header', $laporanLv1Id)
            ->where('LOWER(link)', strtolower($link))
            ->countAllResults() > 0;

        if ($exists) {
            return;
        }

        // Generate menu ID
        $menuId = $this->generateNextMenuId($laporanLv1Id);

        $db->table('menu_lv2')->insert([
            'id'       => $menuId,
            'label'    => $label,
            'link'     => $link,
            'icon'     => 'fas fa-mobile-screen-button',
            'header'   => $laporanLv1Id,
            'ordering' => $this->getNextOrdering('menu_lv2', $laporanLv1Id),
        ]);

        if ($db->tableExists('menu_akses')) {
            $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : ($db->fieldExists('group_id', 'menu_akses') ? 'group_id' : null);
            if ($roleColumn !== null) {
                $rolesToGrant = [1, 2, 3, 5];
                foreach ($rolesToGrant as $roleId) {
                    $existsAkses = (int) $db->table('menu_akses')
                        ->where($roleColumn, $roleId)
                        ->where('menu_id', $menuId)
                        ->countAllResults();

                    if ($existsAkses === 0) {
                        $db->table('menu_akses')->insert([
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
        }
    }

    private function generateNextMenuId(string $header): string
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

    private function getNextOrdering(string $table, string $header): int
    {
        $row = $this->db->table($table)
            ->selectMax('ordering', 'max_ordering')
            ->where('header', $header)
            ->get()
            ->getRowArray();

        return ((int) ($row['max_ordering'] ?? 0)) + 1;
    }
}
