<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInventarisTidakTerdataTable extends Migration
{
    public function up()
    {
        $db = $this->db;
        $forge = \Config\Database::forge();

        // 1. Buat Tabel trn_inventaris_tidak_terdata
        if (! $db->tableExists('trn_inventaris_tidak_terdata')) {
            $forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'nama_barang' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => false,
                ],
                'jumlah' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => false,
                    'default'    => 1,
                ],
                'satuan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => false,
                    'default'    => 'Buah',
                ],
                'merk_tipe' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'default'    => null,
                ],
                'tahun_perolehan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 10,
                    'null'       => true,
                    'default'    => null,
                ],
                'ruangan_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'default'    => null,
                ],
                'lokasi_penempatan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => true,
                    'default'    => null,
                ],
                'kondisi' => [
                    'type'       => 'ENUM',
                    'constraint' => ['baik', 'rusak_ringan', 'rusak_berat'],
                    'null'       => false,
                    'default'    => 'baik',
                ],
                'keterangan' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'petugas_nama' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => true,
                    'default'    => 'Hendrick Bastiar',
                ],
                'petugas_nip' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                    'default'    => '197810162025211023',
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
            $forge->addKey('nama_barang');
            $forge->addKey('ruangan_id');
            $forge->createTable('trn_inventaris_tidak_terdata', true);
        }

        // 2. Registrasi Menu Lv2 "5. Aset Tidak Terdata" (11-05)
        if ($db->tableExists('menu_lv2')) {
            $this->upsertLv2Menu('11-05', '11', 'Aset Tidak Terdata', 'admin/inventaris/tidak-terdata', 'fas fa-clipboard-list', 5);
        }

        // 3. Pre-seed data awal sesuai lampiran spreadsheet pengguna
        if ($db->tableExists('trn_inventaris_tidak_terdata')) {
            $count = $db->table('trn_inventaris_tidak_terdata')->countAllResults();
            if ($count === 0) {
                $now = date('Y-m-d H:i:s');
                $keteranganBppw = 'Diperoleh Pada Saat Masih PPK PS di BPPW Dirjen Cipta Karya';

                $seedData = [
                    [
                        'nama_barang'       => 'P.C Unit',
                        'jumlah'            => 3,
                        'satuan'            => 'Buah',
                        'merk_tipe'         => 'PC Desktop Intel Core i5 8400 - 2.806HZ - 8 GB - 1 TB + Monitor 21"',
                        'tahun_perolehan'   => '2023',
                        'ruangan_id'        => null,
                        'lokasi_penempatan' => 'Ruang Tata Usaha',
                        'kondisi'           => 'baik',
                        'keterangan'        => $keteranganBppw,
                        'petugas_nama'      => 'Hendrick Bastiar',
                        'petugas_nip'       => '197810162025211023',
                        'created_at'        => $now,
                        'updated_at'        => $now,
                    ],
                    [
                        'nama_barang'       => 'Printer (Peralatan Personal Komputer)',
                        'jumlah'            => 1,
                        'satuan'            => 'Buah',
                        'merk_tipe'         => 'HP Smart Tank 210',
                        'tahun_perolehan'   => '2023',
                        'ruangan_id'        => null,
                        'lokasi_penempatan' => 'Ruang Tata Usaha',
                        'kondisi'           => 'baik',
                        'keterangan'        => $keteranganBppw,
                        'petugas_nama'      => 'Hendrick Bastiar',
                        'petugas_nip'       => '197810162025211023',
                        'created_at'        => $now,
                        'updated_at'        => $now,
                    ],
                    [
                        'nama_barang'       => 'Printer (Peralatan Personal Komputer)',
                        'jumlah'            => 1,
                        'satuan'            => 'Buah',
                        'merk_tipe'         => 'EPSON PRINTER ECOTANK L3210 A4',
                        'tahun_perolehan'   => '2023',
                        'ruangan_id'        => null,
                        'lokasi_penempatan' => 'Ruang Tata Usaha',
                        'kondisi'           => 'baik',
                        'keterangan'        => $keteranganBppw,
                        'petugas_nama'      => 'Hendrick Bastiar',
                        'petugas_nip'       => '197810162025211023',
                        'created_at'        => $now,
                        'updated_at'        => $now,
                    ],
                    [
                        'nama_barang'       => 'A.C. Split',
                        'jumlah'            => 1,
                        'satuan'            => 'Buah',
                        'merk_tipe'         => 'PANASONIC AC STANDARD 1 PK',
                        'tahun_perolehan'   => '2023',
                        'ruangan_id'        => null,
                        'lokasi_penempatan' => 'Ruang Rapat',
                        'kondisi'           => 'baik',
                        'keterangan'        => $keteranganBppw,
                        'petugas_nama'      => 'Hendrick Bastiar',
                        'petugas_nip'       => '197810162025211023',
                        'created_at'        => $now,
                        'updated_at'        => $now,
                    ],
                    [
                        'nama_barang'       => 'AC Split',
                        'jumlah'            => 1,
                        'satuan'            => 'Buah',
                        'merk_tipe'         => 'TLC',
                        'tahun_perolehan'   => null,
                        'ruangan_id'        => null,
                        'lokasi_penempatan' => 'Ruang Staf',
                        'kondisi'           => 'baik',
                        'keterangan'        => null,
                        'petugas_nama'      => 'Hendrick Bastiar',
                        'petugas_nip'       => '197810162025211023',
                        'created_at'        => $now,
                        'updated_at'        => $now,
                    ],
                    [
                        'nama_barang'       => 'AC Floor Stand',
                        'jumlah'            => 1,
                        'satuan'            => 'Buah',
                        'merk_tipe'         => null,
                        'tahun_perolehan'   => null,
                        'ruangan_id'        => null,
                        'lokasi_penempatan' => 'Aula Pertemuan',
                        'kondisi'           => 'baik',
                        'keterangan'        => null,
                        'petugas_nama'      => 'Hendrick Bastiar',
                        'petugas_nip'       => '197810162025211023',
                        'created_at'        => $now,
                        'updated_at'        => $now,
                    ],
                    [
                        'nama_barang'       => 'Lemari Es',
                        'jumlah'            => 1,
                        'satuan'            => 'Buah',
                        'merk_tipe'         => 'Polytron',
                        'tahun_perolehan'   => null,
                        'ruangan_id'        => null,
                        'lokasi_penempatan' => 'Pantry / Dapur',
                        'kondisi'           => 'baik',
                        'keterangan'        => null,
                        'petugas_nama'      => 'Hendrick Bastiar',
                        'petugas_nip'       => '197810162025211023',
                        'created_at'        => $now,
                        'updated_at'        => $now,
                    ],
                ];

                $db->table('trn_inventaris_tidak_terdata')->insertBatch($seedData);
            }
        }
    }

    public function down()
    {
        $db = $this->db;
        $forge = \Config\Database::forge();

        if ($db->tableExists('trn_inventaris_tidak_terdata')) {
            $forge->dropTable('trn_inventaris_tidak_terdata', true);
        }

        if ($db->tableExists('menu_lv2')) {
            $db->table('menu_lv2')->where('id', '11-05')->delete();
        }

        if ($db->tableExists('menu_akses')) {
            $db->table('menu_akses')->where('menu_id', '11-05')->delete();
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
