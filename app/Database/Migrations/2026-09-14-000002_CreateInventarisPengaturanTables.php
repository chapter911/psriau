<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInventarisPengaturanTables extends Migration
{
    public function up()
    {
        $forge = \Config\Database::forge();
        $db = $this->db;

        // 1. Tabel Kop Surat Khusus Dokumen Inventarisasi (dengan Masa Berlaku)
        if (! $db->tableExists('cfg_inventaris_kop_surat')) {
            $forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'nama_kop' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => false,
                ],
                'image_url' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => false,
                ],
                'berlaku_dari' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'berlaku_sampai' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'is_active' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 1,
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
            ]);

            $forge->addKey('id', true);
            $forge->addKey('is_active');
            $forge->createTable('cfg_inventaris_kop_surat', true);

            // Seed data dari master kop_surat jika ada
            if ($db->tableExists('kop_surat')) {
                $existingKop = $db->table('kop_surat')->get()->getResultArray();
                foreach ($existingKop as $k) {
                    $db->table('cfg_inventaris_kop_surat')->insert([
                        'nama_kop'       => $k['title'] ?? 'KOP Satker',
                        'image_url'      => $k['image_url'] ?? '',
                        'berlaku_dari'   => '2024-01-01',
                        'berlaku_sampai' => null,
                        'is_active'      => (int) ($k['is_active'] ?? 1),
                        'keterangan'     => $k['description'] ?? 'Diimpor otomatis dari master kop surat',
                        'created_at'     => date('Y-m-d H:i:s'),
                        'updated_at'     => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        }

        // 2. Tabel Riwayat Jabatan Kasatker (Kuasa Pengguna Barang) Berdasarkan Periode
        if (! $db->tableExists('cfg_inventaris_kasatker')) {
            $forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'nama' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => false,
                ],
                'nip' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                ],
                'jabatan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'default'    => 'Kepala Satuan Kerja Pelaksanaan Prasarana Strategis Riau selaku Kuasa Pengguna Barang',
                ],
                'periode_mulai' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'periode_selesai' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'status_jabatan' => [
                    'type'       => 'ENUM',
                    'constraint' => ['definitif', 'plt', 'plh'],
                    'default'    => 'definitif',
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
            $forge->addKey('is_active');
            $forge->createTable('cfg_inventaris_kasatker', true);

            // Seed Kasatker Default (Muhammad Yudi Prasetya, ST.)
            $db->table('cfg_inventaris_kasatker')->insert([
                'nama'            => 'Muhammad Yudi Prasetya, ST.',
                'nip'             => '198002142014121002',
                'jabatan'         => 'Kepala Satuan Kerja Pelaksanaan Prasarana Strategis Riau selaku Kuasa Pengguna Barang',
                'periode_mulai'   => '2024-01-01',
                'periode_selesai' => null,
                'status_jabatan'  => 'definitif',
                'is_active'       => 1,
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ]);
        }

        // 3. Tabel Delegasi Penandatangan BMN (a.n. Kuasa Pengguna Barang)
        if (! $db->tableExists('cfg_inventaris_delegasi')) {
            $forge->addField([
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
                    'null'       => true,
                ],
                'nama' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => false,
                ],
                'nip' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                ],
                'jabatan_struktural' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'jabatan_bmn' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'default'    => 'Pengurus Barang Pengguna',
                ],
                'format_ttd' => [
                    'type' => 'TEXT',
                    'null' => false,
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
            $forge->addKey('is_active');
            $forge->createTable('cfg_inventaris_delegasi', true);

            // Coba ambil pegawai Hendrick Bastiar jika ada
            $pegHendrick = null;
            if ($db->tableExists('mst_pegawai')) {
                $pegHendrick = $db->table('mst_pegawai')
                    ->like('nama', 'Hendrick Bastiar')
                    ->get()
                    ->getRowArray();
            }

            $db->table('cfg_inventaris_delegasi')->insert([
                'pegawai_id'         => $pegHendrick['id'] ?? null,
                'nama'               => $pegHendrick['nama'] ?? 'Hendrick Bastiar',
                'nip'                => $pegHendrick['nip'] ?? '197810162025211023',
                'jabatan_struktural' => 'Staf Tata Usaha',
                'jabatan_bmn'        => 'Pengurus Barang Pengguna',
                'format_ttd'         => "a.n. Kuasa Pengguna Barang,\nPengurus Barang Pengguna",
                'is_active'          => 1,
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ]);
        }

        // 4. Registrasi Menu Lv2 "7. Pengaturan Dokumen" (11-07)
        if ($db->tableExists('menu_lv2')) {
            $this->upsertLv2Menu('11-07', '11', 'Pengaturan Dokumen', 'admin/inventaris/pengaturan', 'fas fa-sliders-h', 7);
        }
    }

    public function down()
    {
        $db = $this->db;
        $forge = \Config\Database::forge();

        if ($db->tableExists('cfg_inventaris_delegasi')) {
            $forge->dropTable('cfg_inventaris_delegasi', true);
        }

        if ($db->tableExists('cfg_inventaris_kasatker')) {
            $forge->dropTable('cfg_inventaris_kasatker', true);
        }

        if ($db->tableExists('cfg_inventaris_kop_surat')) {
            $forge->dropTable('cfg_inventaris_kop_surat', true);
        }

        if ($db->tableExists('menu_lv2')) {
            $db->table('menu_lv2')->where('id', '11-07')->delete();
        }

        if ($db->tableExists('menu_akses')) {
            $db->table('menu_akses')->where('menu_id', '11-07')->delete();
        }
    }

    private function upsertLv2Menu(string $id, string $header, string $label, string $link, string $icon, int $ordering): void
    {
        $db = $this->db;
        $existing = $db->table('menu_lv2')->where('id', $id)->get()->getRowArray();

        if ($existing) {
            $db->table('menu_lv2')->where('id', $id)->update([
                'header'   => $header,
                'label'    => $label,
                'link'     => $link,
                'icon'     => $icon,
                'ordering' => $ordering,
            ]);
        } else {
            $db->table('menu_lv2')->insert([
                'id'       => $id,
                'header'   => $header,
                'label'    => $label,
                'link'     => $link,
                'icon'     => $icon,
                'ordering' => $ordering,
            ]);
        }

        $this->ensureMenuAksesForMenuId($id);
    }

    private function ensureMenuAksesForMenuId(string $menuId): void
    {
        $db = $this->db;

        if (! $db->tableExists('menu_akses')) {
            return;
        }

        $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';

        $roleRows = $db->table('menu_akses')
            ->select($roleColumn)
            ->distinct()
            ->get()
            ->getResultArray();

        if (empty($roleRows)) {
            $roleRows = [[$roleColumn => 1], [$roleColumn => 2]];
        }

        foreach ($roleRows as $roleRow) {
            $roleId = (int) ($roleRow[$roleColumn] ?? 0);
            if ($roleId <= 0) {
                continue;
            }

            $exists = (int) $db->table('menu_akses')
                ->where($roleColumn, $roleId)
                ->where('menu_id', $menuId)
                ->countAllResults();

            if ($exists > 0) {
                continue;
            }

            $isSuperOrAdmin = in_array($roleId, [1, 2], true);

            $db->table('menu_akses')->insert([
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
