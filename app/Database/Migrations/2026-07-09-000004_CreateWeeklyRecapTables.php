<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWeeklyRecapTables extends Migration
{
    public function up()
    {
        $db = $this->db;

        // Drop existing tables to avoid duplicate table errors on retry
        $this->forge->dropTable('laporan_rekapitulasi_mingguan_detail', true);
        $this->forge->dropTable('laporan_rekapitulasi_mingguan_sekolah', true);
        $this->forge->dropTable('laporan_rekapitulasi_mingguan', true);

        // 1. Table: laporan_rekapitulasi_mingguan
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'paket_id' => [
                'type' => 'INT',
                'null' => true,
            ],
            'minggu_ke' => [
                'type' => 'INT',
                'null' => false,
            ],
            'judul' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'file_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'file_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
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
        $this->forge->addKey('paket_id');
        $this->forge->createTable('laporan_rekapitulasi_mingguan');

        // 2. Table: laporan_rekapitulasi_mingguan_sekolah
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'rekapitulasi_mingguan_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
            ],
            'no_urut' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'nama_sekolah' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'jumlah_harga' => [
                'type' => 'DOUBLE',
                'null' => true,
            ],
            'bobot' => [
                'type' => 'DOUBLE',
                'null' => true,
            ],
            'progres_minggu_lalu' => [
                'type' => 'DOUBLE',
                'null' => true,
            ],
            'progres_minggu_ini' => [
                'type' => 'DOUBLE',
                'null' => true,
            ],
            'progres_sampai_minggu_ini' => [
                'type' => 'DOUBLE',
                'null' => true,
            ],
            'rencana' => [
                'type' => 'DOUBLE',
                'null' => true,
            ],
            'deviasi' => [
                'type' => 'DOUBLE',
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
        $this->forge->addKey('rekapitulasi_mingguan_id');
        $this->forge->createTable('laporan_rekapitulasi_mingguan_sekolah');

        // 3. Table: laporan_rekapitulasi_mingguan_detail
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'rekapitulasi_mingguan_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
            ],
            'nama_sekolah' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'no_urut' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'uraian' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'volume' => [
                'type' => 'DOUBLE',
                'null' => true,
            ],
            'satuan' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'harga_satuan' => [
                'type' => 'DOUBLE',
                'null' => true,
            ],
            'jumlah_harga' => [
                'type' => 'DOUBLE',
                'null' => true,
            ],
            'bobot' => [
                'type' => 'DOUBLE',
                'null' => true,
            ],
            'progres_minggu_lalu_vol' => [
                'type' => 'DOUBLE',
                'null' => true,
            ],
            'progres_minggu_lalu_bobot' => [
                'type' => 'DOUBLE',
                'null' => true,
            ],
            'progres_minggu_ini_vol' => [
                'type' => 'DOUBLE',
                'null' => true,
            ],
            'progres_minggu_ini_bobot' => [
                'type' => 'DOUBLE',
                'null' => true,
            ],
            'progres_sampai_minggu_ini_vol' => [
                'type' => 'DOUBLE',
                'null' => true,
            ],
            'progres_sampai_minggu_ini_bobot' => [
                'type' => 'DOUBLE',
                'null' => true,
            ],
            'progres_pekerjaan_persen' => [
                'type' => 'DOUBLE',
                'null' => true,
            ],
            'deviasi_progres' => [
                'type' => 'DOUBLE',
                'null' => true,
            ],
            'sisa_progres' => [
                'type' => 'DOUBLE',
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
        $this->forge->addKey('rekapitulasi_mingguan_id');
        $this->forge->createTable('laporan_rekapitulasi_mingguan_detail');

        // Add foreign keys using raw SQL with short names to prevent MySQL 64-char identifier limit errors
        $db->query("ALTER TABLE `laporan_rekapitulasi_mingguan_sekolah` ADD CONSTRAINT `fk_rekap_sekolah` FOREIGN KEY (`rekapitulasi_mingguan_id`) REFERENCES `laporan_rekapitulasi_mingguan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE");
        $db->query("ALTER TABLE `laporan_rekapitulasi_mingguan_detail` ADD CONSTRAINT `fk_rekap_detail` FOREIGN KEY (`rekapitulasi_mingguan_id`) REFERENCES `laporan_rekapitulasi_mingguan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE");

        // 4. Create Sidebar Menu
        $this->createMenu();
    }

    public function down()
    {
        $db = $this->db;

        if ($db->tableExists('menu_lv2')) {
            $row = $db->table('menu_lv2')
                ->select('id')
                ->where('LOWER(link)', 'admin/laporan/rekap-mingguan')
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

        // Drop constraints first
        if ($db->tableExists('laporan_rekapitulasi_mingguan_sekolah')) {
            try {
                $db->query("ALTER TABLE `laporan_rekapitulasi_mingguan_sekolah` DROP FOREIGN KEY `fk_rekap_sekolah`");
            } catch (\Exception $e) {}
        }
        if ($db->tableExists('laporan_rekapitulasi_mingguan_detail')) {
            try {
                $db->query("ALTER TABLE `laporan_rekapitulasi_mingguan_detail` DROP FOREIGN KEY `fk_rekap_detail`");
            } catch (\Exception $e) {}
        }

        $this->forge->dropTable('laporan_rekapitulasi_mingguan_detail', true);
        $this->forge->dropTable('laporan_rekapitulasi_mingguan_sekolah', true);
        $this->forge->dropTable('laporan_rekapitulasi_mingguan', true);
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
        $link = 'admin/laporan/rekap-mingguan';
        $label = 'Rekapitulasi Mingguan';

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

        // Get ordering
        $ordRow = $db->table('menu_lv2')
            ->selectMax('ordering', 'max_ordering')
            ->where('header', $laporanLv1Id)
            ->get()
            ->getRowArray();
        $ordering = ((int) ($ordRow['max_ordering'] ?? 0)) + 1;

        $db->table('menu_lv2')->insert([
            'id'       => $menuId,
            'label'    => $label,
            'link'     => $link,
            'icon'     => 'far fa-file-excel',
            'header'   => $laporanLv1Id,
            'ordering' => $ordering,
        ]);

        // Seed access
        if ($db->tableExists('menu_akses')) {
            $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';
            $roles = $db->table('menu_akses')->select($roleColumn)->distinct()->get()->getResultArray();
            if ($roles === []) {
                $roles = [[$roleColumn => 1], [$roleColumn => 2]];
            }

            foreach ($roles as $r) {
                $roleId = (int) ($r[$roleColumn] ?? 0);
                if ($roleId <= 0) {
                    continue;
                }

                $isSuperAdmin = $roleId === 1;
                $db->table('menu_akses')->insert([
                    $roleColumn     => $roleId,
                    'menu_id'       => $menuId,
                    'FiturAdd'      => $isSuperAdmin ? 1 : 0,
                    'FiturEdit'     => $isSuperAdmin ? 1 : 0,
                    'FiturDelete'   => $isSuperAdmin ? 1 : 0,
                    'FiturExport'   => $isSuperAdmin ? 1 : 0,
                    'FiturImport'   => $isSuperAdmin ? 1 : 0,
                    'FiturApproval' => $isSuperAdmin ? 1 : 0,
                ]);
            }
        }
    }

    private function generateNextMenuId(string $header): string
    {
        $db = $this->db;
        $rows = $db->table('menu_lv2')
            ->select('id')
            ->where('header', $header)
            ->get()
            ->getResultArray();

        $maxSequence = 0;
        foreach ($rows as $row) {
            $cand = (string) ($row['id'] ?? '');
            $prefix = $header . '-';
            if (strpos($cand, $prefix) === 0) {
                $suffix = substr($cand, strlen($prefix));
                if (preg_match('/^(\d+)$/', $suffix, $matches)) {
                    $maxSequence = max($maxSequence, (int) $matches[1]);
                }
            }
        }

        return $header . '-' . str_pad((string) ($maxSequence + 1), 2, '0', STR_PAD_LEFT);
    }
}
