<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAtkSuppliesModule extends Migration
{
    public function up()
    {
        $db = $this->db;
        helper('custom');

        // 1. Master Penyedia ATK & Supplies
        if (! $db->tableExists('mst_atk_penyedia')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'nama_perusahaan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 200,
                ],
                'alamat' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'kota' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'default'    => 'Pekanbaru',
                ],
                'telepon' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
                'email' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => true,
                ],
                'nama_direktur' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => true,
                ],
                'jabatan_pimpinan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'default'    => 'Direktur',
                ],
                'npwp' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
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
            $this->forge->addKey('id', true);
            $this->forge->createTable('mst_atk_penyedia', true);
        }

        // 2. Master Katalog Barang & Harga
        if (! $db->tableExists('mst_atk_barang')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'penyedia_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'tahun_anggaran' => [
                    'type'       => 'INT',
                    'constraint' => 4,
                    'default'    => 2026,
                ],
                'kategori' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'default'    => 'ATK',
                ],
                'nama_barang' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'satuan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'default'    => 'Buah',
                ],
                'harga_satuan' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0.00,
                ],
                'termasuk_pajak' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 1,
                ],
                'keterangan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
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
            $this->forge->addKey('id', true);
            $this->forge->addKey('penyedia_id');
            $this->forge->addKey(['tahun_anggaran', 'kategori']);
            $this->forge->createTable('mst_atk_barang', true);
        }

        // 3. Transaksi Pengadaan ATK & Supplies
        if (! $db->tableExists('trn_atk_pengadaan')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'tahun_anggaran' => [
                    'type'       => 'INT',
                    'constraint' => 4,
                    'default'    => 2026,
                ],
                'bulan' => [
                    'type'       => 'TINYINT',
                    'constraint' => 2,
                    'default'    => 1,
                ],
                'kategori' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'default'    => 'ATK',
                ],
                'kegiatan_label' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'default'    => 'Dukungan Kegiatan Prasarana Strategis',
                ],
                'mata_anggaran_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'kode_mata_anggaran' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'default'    => '7987.FAD.002.101.A.521211',
                ],
                'penyedia_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'kop_surat_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'no_usulan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 120,
                    'null'       => true,
                ],
                'tgl_usulan' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'no_pesanan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 120,
                    'null'       => true,
                ],
                'tgl_pesanan' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'tgl_batas_terima' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'persen_denda_harian' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '5,2',
                    'default'    => 2.00,
                ],
                'no_bastp' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 120,
                    'null'       => true,
                ],
                'tgl_bastp' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'no_faktur' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 120,
                    'null'       => true,
                ],
                'tgl_faktur' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'no_bukti_kuitansi' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 120,
                    'null'       => true,
                ],
                'tgl_kuitansi' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'tgl_lunas_bayar' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'ppk_pegawai_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'ppk_nama' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => true,
                ],
                'ppk_nip' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                ],
                'bendahara_pegawai_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'bendahara_nama' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => true,
                ],
                'bendahara_nip' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                ],
                'total_nilai' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0.00,
                ],
                'terbilang' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                    'default'    => 'draft',
                ],
                'catatan' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'rejection_reason' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'rejected_by' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'rejected_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'approved_by' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'approved_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'created_by' => [
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
            $this->forge->addKey('id', true);
            $this->forge->addKey(['tahun_anggaran', 'bulan', 'kategori']);
            $this->forge->addKey('status');
            $this->forge->createTable('trn_atk_pengadaan', true);
        }

        // 4. Item Rincian Pengadaan ATK & Supplies
        if (! $db->tableExists('trn_atk_pengadaan_item')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'pengadaan_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'barang_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'nama_barang' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'satuan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'default'    => 'Buah',
                ],
                'volume' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '12,2',
                    'default'    => 1.00,
                ],
                'harga_satuan' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0.00,
                ],
                'subtotal' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0.00,
                ],
                'kondisi_barang' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'default'    => 'Baik',
                ],
                'keterangan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'urutan' => [
                    'type'       => 'INT',
                    'constraint' => 4,
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
            $this->forge->addKey('id', true);
            $this->forge->addKey('pengadaan_id');
            $this->forge->createTable('trn_atk_pengadaan_item', true);
        }

        // 5. Dokumen Tanda Tangan Basah (Upload & Verifikasi / Approval)
        if (! $db->tableExists('trn_atk_dokumen_ttd')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'pengadaan_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'jenis_dokumen' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'default'    => 'semua_gabungan',
                ],
                'file_path' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'file_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'file_size' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                ],
                'catatan' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'status_verifikasi' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                    'default'    => 'disetujui',
                ],
                'verified_by' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'verified_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'rejection_reason' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'uploaded_by' => [
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
            $this->forge->addKey('id', true);
            $this->forge->addKey('pengadaan_id');
            $this->forge->createTable('trn_atk_dokumen_ttd', true);
        }

        // 6. Registrasi Menu di menu_lv1, menu_lv2, dan menu_akses
        if ($db->tableExists('menu_lv1') && $db->tableExists('menu_lv2')) {
            $atkMenuId = $this->findOrCreateLv1Menu('ATK & Supplies', '#', 'fas fa-boxes', 6);

            if ($atkMenuId !== null) {
                $this->ensureLv2Menu($atkMenuId, 'Pengadaan & SPJ', 'admin/atk/pengadaan', 'fas fa-file-invoice', 1);
                $this->ensureLv2Menu($atkMenuId, 'Katalog Barang & Harga', 'admin/atk/katalog', 'fas fa-tags', 2);
                $this->ensureLv2Menu($atkMenuId, 'Penyedia (Vendor)', 'admin/atk/penyedia', 'fas fa-store', 3);
                $this->ensureLv2Menu($atkMenuId, 'Rekapitulasi & Laporan', 'admin/atk/laporan', 'fas fa-chart-bar', 4);
            }
        }

        // 7. Seed Data Awal (Penyedia, Katalog 2026, & 18 Transaksi Feb–Okt 2026)
        $this->seedInitialData();
    }

    public function down()
    {
        $db = $this->db;

        $atkMenuId = $this->findLv1IdByLabel('ATK & Supplies');
        if ($atkMenuId !== null) {
            $submenus = $db->table('menu_lv2')->where('header', $atkMenuId)->get()->getResultArray();
            foreach ($submenus as $sub) {
                $subId = (string) $sub['id'];
                $db->table('menu_lv2')->where('id', $subId)->delete();
                $this->deleteMenuAksesByMenuId($subId);
            }
            $db->table('menu_lv1')->where('id', $atkMenuId)->delete();
            $this->deleteMenuAksesByMenuId($atkMenuId);
        }

        $this->forge->dropTable('trn_atk_dokumen_ttd', true);
        $this->forge->dropTable('trn_atk_pengadaan_item', true);
        $this->forge->dropTable('trn_atk_pengadaan', true);
        $this->forge->dropTable('mst_atk_barang', true);
        $this->forge->dropTable('mst_atk_penyedia', true);
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

    private function findOrCreateLv1Menu(string $label, ?string $link, string $icon, int $ordering): ?string
    {
        $existingId = $this->findLv1IdByLabel($label);
        if ($existingId !== null) {
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

    private function ensureLv2Menu(string $headerId, string $label, string $link, string $icon, int $ordering): void
    {
        $existing = $this->db->table('menu_lv2')
            ->select('id')
            ->where('header', $headerId)
            ->where('LOWER(link)', strtolower($link))
            ->get()
            ->getRowArray();

        if ($existing !== null) {
            $menuId = (string) $existing['id'];
            $this->db->table('menu_lv2')
                ->where('id', $menuId)
                ->update([
                    'label'    => $label,
                    'icon'     => $icon,
                    'ordering' => $ordering,
                ]);
            $this->ensureMenuAksesForMenuId($menuId);
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

        $roleColumn = $this->db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';
        $roleIds = [];

        if ($this->db->tableExists('access_roles')) {
            $rows = $this->db->table('access_roles')->select('id')->get()->getResultArray();
            foreach ($rows as $row) {
                if ((int) ($row['id'] ?? 0) > 0) {
                    $roleIds[] = (int) $row['id'];
                }
            }
        }

        if ($roleIds === []) {
            $rows = $this->db->table('menu_akses')->select($roleColumn)->distinct()->get()->getResultArray();
            foreach ($rows as $row) {
                if ((int) ($row[$roleColumn] ?? 0) > 0) {
                    $roleIds[] = (int) $row[$roleColumn];
                }
            }
        }

        if ($roleIds === []) {
            $roleIds = [1, 2, 3, 5, 6];
        }

        foreach (array_unique($roleIds) as $roleId) {
            $exists = (int) $this->db->table('menu_akses')
                ->where($roleColumn, $roleId)
                ->where('menu_id', $menuId)
                ->countAllResults();

            if ($exists > 0) {
                continue;
            }

            $isSuperOrAdmin = in_array($roleId, [1, 2], true);
            $isKeuangan     = ($roleId === 5);
            $isTataUsaha    = in_array($roleId, [3, 6], true);

            $this->db->table('menu_akses')->insert([
                $roleColumn     => $roleId,
                'menu_id'       => $menuId,
                'FiturAdd'      => ($isSuperOrAdmin || $isTataUsaha || $isKeuangan) ? 1 : 0,
                'FiturEdit'     => ($isSuperOrAdmin || $isTataUsaha || $isKeuangan) ? 1 : 0,
                'FiturDelete'   => $isSuperOrAdmin ? 1 : 0,
                'FiturExport'   => 1,
                'FiturImport'   => ($isSuperOrAdmin || $isKeuangan) ? 1 : 0,
                'FiturApproval' => ($isSuperOrAdmin || $isKeuangan) ? 1 : 0,
            ]);
        }
    }

    private function deleteMenuAksesByMenuId(string $menuId): void
    {
        if ($this->db->tableExists('menu_akses')) {
            $this->db->table('menu_akses')->where('menu_id', $menuId)->delete();
        }
    }

    private function seedInitialData(): void
    {
        $now = date('Y-m-d H:i:s');

        // A. Seed Penyedia
        $penyediaId = null;
        if ((int) $this->db->table('mst_atk_penyedia')->countAllResults() === 0) {
            $this->db->table('mst_atk_penyedia')->insert([
                'nama_perusahaan'  => 'CV. SINAR CAHAYA LESTARI',
                'alamat'           => 'Jalan Bukit Barisan Ujung No. 97 RT. 003 RW. 009 Tangkerang Timur',
                'kota'             => 'Pekanbaru',
                'telepon'          => '0853-6390-0871 / 0812-7594-0912',
                'email'            => 'sclsinarcahayalestari070122@gmail.com',
                'nama_direktur'    => 'ILHAM RAMAPALA',
                'jabatan_pimpinan' => 'Direktur',
                'is_active'        => 1,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
            $penyediaId = (int) $this->db->insertID();
        } else {
            $row = $this->db->table('mst_atk_penyedia')->orderBy('id', 'ASC')->get()->getRowArray();
            $penyediaId = $row ? (int) $row['id'] : null;
        }

        // B. Seed Katalog Barang (75 item PDF 2026 + 5 item tambahan dari SPJ Excel 2026)
        $barangMap = [];
        if ((int) $this->db->table('mst_atk_barang')->countAllResults() === 0) {
            $catalogItems = [
                // Page 1 PDF (1 - 36)
                ['ATK', 'Kertas HVS A4 75 gr Merk Paper One', 'Kotak', 349000],
                ['ATK', 'Kertas HVS F4 70 gr Merk Paper One', 'Kotak', 405000],
                ['ATK', 'Buku F4 isi 100 Mirage Merk Okey', 'Buah', 34000],
                ['ATK', 'Buku Agenda Surat Masuk - Surat Keluar', 'Buah', 39000],
                ['ATK', 'Map Biasa Merk Biola', 'Kotak', 146000],
                ['ATK', 'Map Plastik Kancing Merk Felix', 'Kotak', 47000],
                ['ATK', 'Binder Klip Merk Joyko No. 105', 'Kotak Kecil', 11000],
                ['ATK', 'Binder Klip Merk Joyko No. 260', 'Kotak Kecil', 47000],
                ['ATK', 'Post It', 'Buah', 30000],
                ['ATK', 'Stick Note', 'Buah', 30000],
                ['ATK', 'Stapler Merk Kangaro HS - 10 Y No. 10', 'Buah', 22000],
                ['ATK', 'Map Combo 401', 'Buah', 45000],
                ['ATK', 'Pena Signo Merk Uni Ball', 'Lusin', 337000],
                ['ATK', 'Box File Executive', 'Buah', 45000],
                ['ATK', 'Isi Staples No. 10 Merk Kangaro', 'Kotak Besar', 67000],
                ['ATK', 'Kertas HVS Warna A4 80GSM', 'Rim', 120000],
                ['ATK', 'Pena Gel Kenko K - 1', 'Lusin', 90000],
                ['ATK', 'Stabillo Boss Original', 'Lusin', 90000],
                ['ATK', 'Amplop Dinas Coklat Viva', 'Kotak', 101000],
                ['ATK', 'Lem Glue Stick Sedang', 'Buah', 8000],
                ['ATK', 'Amplop Paperline 110 pps putih', 'Kotak', 45000],
                ['ATK', 'Gunting Besar', 'Buah', 17000],
                ['ATK', 'Pensil 2B Greebel', 'Lusin', 55000],
                ['ATK', 'Pena Faster (12)', 'Lusin', 67000],
                ['ATK', 'Binder Klip Merk Joyko No. 200', 'Kotak Kecil', 34000],
                ['ATK', 'Binder Klip Merk Joyko No. 155', 'Kotak Kecil', 22000],
                ['ATK', 'Map Tulang Biola', 'Kotak', 202000],
                ['ATK', 'Spidol Snowman', 'Buah', 124000],
                ['ATK', 'Kenko Cutter L - 500', 'Buah', 22000],
                ['ATK', 'Isi Kenko Cutter L - 500', 'Kotak', 11000],
                ['ATK', 'Stapler Merk Kangaro HS - 10 Y No. 30', 'Buah', 67000],
                ['ATK', 'Isi Staples No. 30 Merk Kangaro', 'Kotak Besar', 135000],
                ['ATK', 'Tisu Besar', 'Buah', 79000],
                ['ATK', 'Pembuka Staples Kecil', 'Buah', 28000],
                ['Supplies Komputer', 'Tinta Brother BT - 5000 Warna', 'Botol', 157000],
                ['Supplies Komputer', 'Tinta Brother BT - 5000 Hitam', 'Botol', 157000],
                // Page 2 PDF (37 - 75)
                ['Supplies Komputer', 'Tinta Epson 003 Hitam', 'Botol', 135000],
                ['Supplies Komputer', 'Tinta Epson 003 Warna', 'Botol', 135000],
                ['Supplies Komputer', 'Tinta Epson 664 Hitam', 'Botol', 135000],
                ['Supplies Komputer', 'Tinta Epson 664 Warna', 'Botol', 135000],
                ['ATK', 'Penggaris Besi 30 cm', 'Buah', 5000],
                ['ATK', 'Tip Ex Cair Merk Kenko/Joyko', 'Buah', 7000],
                ['ATK', 'Kertas HVS A3 75 gr Merk Paper One', 'Kotak', 730000],
                ['ATK', 'Amplop Surat A4', 'Pack', 202000],
                ['ATK', 'Kertas Jilid', 'Pack', 90000],
                ['ATK', 'Kertas Plastik', 'Pack', 90000],
                ['ATK', 'Lakban Putih Kecil Merk Kenko', 'Buah', 5000],
                ['ATK', 'Lakban Putih Sedang Merk Kenko', 'Buah', 11000],
                ['ATK', 'Lakban Putih Besar Merk Kenko', 'Buah', 19000],
                ['ATK', 'Lakban Hitam Kecil Merk Kenko', 'Buah', 11000],
                ['ATK', 'Lakban Hitam Sedang Merk Kenko', 'Buah', 16000],
                ['ATK', 'Lakban Hitam Besar Merk Kenko', 'Buah', 22000],
                ['ATK', 'Kalkulator Merk Citizen 14 Digit', 'Buah', 151000],
                ['ATK', 'Map L Ukuran F4', 'Pack', 50000],
                ['ATK', 'Paper Clip No 1 Merk Joyko', 'Kotak Besar', 101000],
                ['ATK', 'Map Lucky', 'Buah', 45000],
                ['ATK', 'Penghapus Besar', 'Buah', 4000],
                ['ATK', 'Rautan Peruncing Biasa', 'Buah', 4000],
                ['Supplies Komputer', 'Tinta HP Smart Tank 210 Hitam', 'Botol', 157000],
                ['Supplies Komputer', 'Tinta HP Smart Tank 210 Warna', 'Botol', 157000],
                ['ATK', 'Pembolong Kertas Kecil', 'Buah', 28000],
                ['ATK', 'Map Ordner Ukuran F4', 'Buah', 50000],
                ['Supplies Komputer', 'Flashdisk 64Gb', 'Buah', 185000],
                ['Supplies Komputer', 'Tinta Epson 008 Hitam', 'Botol', 250000],
                ['Supplies Komputer', 'Tinta Epson 008 Warna', 'Botol', 250000],
                ['ATK', 'Kertas HVS Warna F4 60 GSM', 'Rim', 101000],
                ['Supplies Komputer', 'Hardisk Eksternal Seatage 1 TB', 'Buah', 1400000],
                ['ATK', 'Kertas NCR', 'Rim', 112000],
                ['ATK', 'Pena Gel Kenko', 'Lusin', 90000],
                ['ATK', 'Map Plastik Bening', 'Pack', 47000],
                ['Supplies Komputer', 'Mouse Wireless Merk Logitech', 'Buah', 170000],
                ['ATK', 'Map Clear Holder Folio', 'Buah', 30000],
                ['ATK', 'Map Ijazah Resleting', 'Buah', 120000],
                ['ATK', 'Penghapus Merk Jocyo', 'Kotak', 55000],
                ['ATK', 'Kalkulator CC-32 Merk Joyco', 'Kotak', 45000],
                // Item tambahan yang muncul di SPJ Excel 2026
                ['ATK', 'Paper Clip No 1 Merk Atom', 'Kotak Besar', 101000],
                ['ATK', 'Binder Klip Merk Combo No. 105', 'Kotak Kecil', 11000],
                ['ATK', 'Binder Klip Merk Combo No. 260', 'Kotak Kecil', 47000],
                ['Supplies Komputer', 'Mouse Logitech B175', 'Buah', 205000],
                ['Supplies Komputer', 'Flashdisk 64Gb Sandisk', 'Buah', 155000],
            ];

            foreach ($catalogItems as $item) {
                $this->db->table('mst_atk_barang')->insert([
                    'penyedia_id'    => $penyediaId,
                    'tahun_anggaran' => 2026,
                    'kategori'       => $item[0],
                    'nama_barang'    => $item[1],
                    'satuan'         => $item[2],
                    'harga_satuan'   => $item[3],
                    'termasuk_pajak' => 1,
                    'keterangan'     => 'Daftar Harga CV. Sinar Cahaya Lestari 2026 (Termasuk Pajak)',
                    'is_active'      => 1,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]);
                $barangMap[strtolower($item[1])] = (int) $this->db->insertID();
            }
        } else {
            foreach ($this->db->table('mst_atk_barang')->get()->getResultArray() as $b) {
                $barangMap[strtolower((string) $b['nama_barang'])] = (int) $b['id'];
            }
        }

        // C. Seed 18 Transaksi Pengadaan Feb–Okt 2026 dari KWI ATK DAN SUPPLIES DUKPRAS 2026.xls
        if ((int) $this->db->table('trn_atk_pengadaan')->countAllResults() === 0) {
            $mataAnggaranId = null;
            if ($this->db->tableExists('mst_mata_anggaran')) {
                $maRow = $this->db->table('mst_mata_anggaran')
                    ->like('mata_anggaran', '521211')
                    ->get()
                    ->getRowArray();
                if ($maRow) {
                    $mataAnggaranId = (int) $maRow['id'];
                }
            }

            $kopSuratId = null;
            if ($this->db->tableExists('kop_surat')) {
                $kopRow = $this->db->table('kop_surat')
                    ->orderBy('is_active', 'DESC')
                    ->orderBy('id', 'DESC')
                    ->get()
                    ->getRowArray();
                if ($kopRow) {
                    $kopSuratId = (int) $kopRow['id'];
                }
            }

            $ppkId = null;
            $bendaharaId = null;
            if ($this->db->tableExists('mst_pegawai')) {
                $ppkRow = $this->db->table('mst_pegawai')->like('nama', 'Nurhidayat')->get()->getRowArray();
                if ($ppkRow) {
                    $ppkId = (int) $ppkRow['id'];
                }
                $benRow = $this->db->table('mst_pegawai')->like('nama', 'Sri Handayani')->get()->getRowArray();
                if ($benRow) {
                    $bendaharaId = (int) $benRow['id'];
                }
            }

            $transactions = [
                [
                    'bulan' => 2, 'kategori' => 'ATK',
                    'tgl_usulan' => '2026-02-06', 'no_pesanan' => '06/PES/SATKER/PPS-RIAU/2026', 'tgl_pesanan' => '2026-02-09', 'tgl_batas_terima' => '2026-02-10',
                    'no_bastp' => '', 'tgl_bastp' => '2026-02-10', 'tgl_faktur' => '2026-02-10', 'tgl_kuitansi' => '2026-02-10',
                    'items' => [
                        ['Kertas HVS A4 75 gr Merk Paper One', 'Kotak', 1, 349000],
                        ['Isi Staples No. 10 Merk Kangaro', 'Kotak besar', 1, 67000],
                        ['Map Biasa Merk Biola', 'Kotak', 1, 146000],
                        ['Map Plastik Kancing Merk Felix', 'Kotak', 1, 47000],
                        ['Pena Signo Merk Uni Ball', 'Lusin', 1, 337000],
                        ['Post It', 'Buah', 1, 30000],
                        ['Tip Ex Cair Merk Kenko', 'Buah', 2, 7000],
                    ],
                ],
                [
                    'bulan' => 2, 'kategori' => 'Supplies Komputer',
                    'tgl_usulan' => '2026-02-06', 'no_pesanan' => '07/PES/SATKER/PPS-RIAU/2026', 'tgl_pesanan' => '2026-02-09', 'tgl_batas_terima' => '2026-02-10',
                    'no_bastp' => '', 'tgl_bastp' => '2026-02-10', 'tgl_faktur' => '2026-02-10', 'tgl_kuitansi' => '2026-02-10',
                    'items' => [
                        ['Tinta Epson 664 Hitam', 'Botol', 2, 135000],
                        ['Tinta Epson 664 Warna', 'Botol', 3, 135000],
                    ],
                ],
                [
                    'bulan' => 3, 'kategori' => 'ATK',
                    'tgl_usulan' => '2026-03-05', 'no_pesanan' => '', 'tgl_pesanan' => '2026-03-06', 'tgl_batas_terima' => '2026-03-09',
                    'no_bastp' => '', 'tgl_bastp' => '2026-03-09', 'tgl_faktur' => '2026-03-09', 'tgl_kuitansi' => '2026-03-09',
                    'items' => [
                        ['Pena Gel Kenko K - 1', 'Lusin', 1, 90000],
                        ['Kertas HVS A4 75 gr Merk Paper One', 'Kotak', 1, 349000],
                        ['Stabillo Boss Original', 'Lusin', 1, 90000],
                        ['Map Biasa Merk Biola', 'Kotak', 1, 146000],
                        ['Stapler Merk Kangaro HS - 10 Y No. 10', 'Buah', 2, 22000],
                        ['Binder Klip Merk Joyko No. 155', 'Kotak Kecil', 4, 22000],
                        ['Stick Note', 'Buah', 3, 30000],
                        ['Binder Klip Merk Joyko No. 200', 'Kotak Kecil', 3, 34000],
                    ],
                ],
                [
                    'bulan' => 3, 'kategori' => 'Supplies Komputer',
                    'tgl_usulan' => '2026-03-05', 'no_pesanan' => '', 'tgl_pesanan' => '2026-03-06', 'tgl_batas_terima' => '2026-03-09',
                    'no_bastp' => '', 'tgl_bastp' => '2026-03-09', 'tgl_faktur' => '2026-03-09', 'tgl_kuitansi' => '2026-03-09',
                    'items' => [
                        ['Tinta Brother BT - 5000 Warna', 'Botol', 3, 157000],
                        ['Tinta Brother BT - 5000 Hitam', 'Botol', 1, 157000],
                    ],
                ],
                [
                    'bulan' => 4, 'kategori' => 'ATK',
                    'tgl_usulan' => '2026-04-07', 'no_pesanan' => '22/PES/SATKER/PPS-RIAU/2026', 'tgl_pesanan' => '2026-04-08', 'tgl_batas_terima' => '2026-04-09',
                    'no_bastp' => '', 'tgl_bastp' => '2026-04-09', 'tgl_faktur' => '2026-04-09', 'tgl_kuitansi' => '2026-04-09',
                    'items' => [
                        ['Map Biasa Merk Biola', 'Kotak', 1, 146000],
                        ['Map Plastik Kancing Merk Felix', 'Kotak', 2, 47000],
                        ['Kertas HVS A4 75 gr Merk Paper One', 'Kotak', 1, 349000],
                        ['Paper Clip No 1 Merk Joyko', 'Kotak Besar', 1, 101000],
                        ['Binder Klip Merk Joyko No. 200', 'Kotak Kecil', 3, 34000],
                        ['Binder Klip Merk Joyko No. 155', 'Kotak Kecil', 5, 22000],
                        ['Stick Note', 'Buah', 3, 30000],
                    ],
                ],
                [
                    'bulan' => 4, 'kategori' => 'Supplies Komputer',
                    'tgl_usulan' => '2026-04-07', 'no_pesanan' => '23/PES/SATKER/PPS-RIAU/2026', 'tgl_pesanan' => '2026-04-08', 'tgl_batas_terima' => '2026-04-09',
                    'no_bastp' => '', 'tgl_bastp' => '2026-04-09', 'tgl_faktur' => '2026-04-09', 'tgl_kuitansi' => '2026-04-09',
                    'items' => [
                        ['Tinta Epson 008 Warna', 'Botol', 2, 250000],
                        ['Tinta Epson 008 Hitam', 'Botol', 1, 250000],
                    ],
                ],
                [
                    'bulan' => 5, 'kategori' => 'ATK',
                    'tgl_usulan' => '2026-05-06', 'no_pesanan' => '32/PES/SATKER/PPS-RIAU/2026', 'tgl_pesanan' => '2026-05-07', 'tgl_batas_terima' => '2026-05-08',
                    'no_bastp' => '', 'tgl_bastp' => '2026-05-08', 'tgl_faktur' => '2026-05-08', 'tgl_kuitansi' => '2026-05-08',
                    'items' => [
                        ['Pena Signo Merk Uni Ball', 'Lusin', 1, 337000],
                        ['Map Plastik Kancing Merk Felix', 'Kotak', 2, 47000],
                        ['Kertas HVS A4 75 gr Merk Paper One', 'Kotak', 1, 349000],
                        ['Isi Staples No. 10 Merk Kangaro', 'Kotak besar', 1, 67000],
                        ['Binder Klip Merk Joyko No. 200', 'Kotak Kecil', 3, 34000],
                        ['Binder Klip Merk Joyko No. 155', 'Kotak Kecil', 3, 22000],
                    ],
                ],
                [
                    'bulan' => 5, 'kategori' => 'Supplies Komputer',
                    'tgl_usulan' => '2026-05-06', 'no_pesanan' => '33/PES/SATKER/PPS-RIAU/2026', 'tgl_pesanan' => '2026-05-07', 'tgl_batas_terima' => '2026-05-08',
                    'no_bastp' => '', 'tgl_bastp' => '2026-05-08', 'tgl_faktur' => '2026-05-08', 'tgl_kuitansi' => '2026-05-08',
                    'items' => [
                        ['Tinta Epson 003 Hitam', 'Botol', 2, 135000],
                        ['Tinta Epson 003 Warna', 'Botol', 3, 135000],
                        ['Tinta Epson 664 Warna', 'Botol', 1, 135000],
                    ],
                ],
                [
                    'bulan' => 6, 'kategori' => 'ATK',
                    'tgl_usulan' => '2026-06-09', 'no_pesanan' => '51/PES/SATKER/PPS-RIAU/2026', 'tgl_pesanan' => '2026-06-10', 'tgl_batas_terima' => '2026-06-11',
                    'no_bastp' => '', 'tgl_bastp' => '2026-06-11', 'tgl_faktur' => '2026-06-11', 'tgl_kuitansi' => '2026-06-11',
                    'items' => [
                        ['Kertas HVS A4 75 gr Merk Paper One', 'Kotak', 1, 349000],
                        ['Pena Gel Kenko K - 1', 'Lusin', 1, 90000],
                        ['Binder Klip Merk Joyko No. 260', 'Kotak Kecil', 3, 47000],
                        ['Binder Klip Merk Joyko No. 200', 'Kotak Kecil', 5, 34000],
                        ['Binder Klip Merk Joyko No. 155', 'Kotak Kecil', 3, 22000],
                        ['Post It', 'Buah', 2, 30000],
                        ['Stick Note', 'Buah', 3, 30000],
                        ['Paper Clip No 1 Merk Joyko', 'Kotak Besar', 1, 101000],
                    ],
                ],
                [
                    'bulan' => 6, 'kategori' => 'Supplies Komputer',
                    'tgl_usulan' => '2026-06-09', 'no_pesanan' => '52/PES/SATKER/PPS-RIAU/2026', 'tgl_pesanan' => '2026-06-10', 'tgl_batas_terima' => '2026-06-11',
                    'no_bastp' => '', 'tgl_bastp' => '2026-06-11', 'tgl_faktur' => '2026-06-11', 'tgl_kuitansi' => '2026-06-11',
                    'items' => [
                        ['Tinta Epson 003 Hitam', 'Botol', 2, 135000],
                        ['Tinta Epson 003 Warna', 'Botol', 3, 135000],
                        ['Tinta Brother BT - 5000 Hitam', 'Botol', 1, 157000],
                    ],
                ],
                [
                    'bulan' => 7, 'kategori' => 'ATK',
                    'tgl_usulan' => '2026-07-02', 'no_pesanan' => '67/PES/SATKER/PPS-RIAU/2026', 'tgl_pesanan' => '2026-07-03', 'tgl_batas_terima' => '2026-07-06',
                    'no_bastp' => '55/BASTP/SATKER/PPS-RIAU/2026', 'tgl_bastp' => '2026-07-06', 'tgl_faktur' => '2026-07-06', 'tgl_kuitansi' => '2026-07-06',
                    'items' => [
                        ['Kertas HVS A4 75 gr Merk Paper One', 'Kotak', 2, 349000],
                        ['Stabillo Boss Original', 'Lusin', 1, 90000],
                        ['Map Biasa Merk Biola', 'Kotak', 1, 146000],
                        ['Map Plastik Kancing Merk Felix', 'Kotak', 2, 47000],
                        ['Pena Signo Merk Uni Ball', 'Lusin', 1, 337000],
                        ['Binder Klip Merk Joyko No. 200', 'Kotak Kecil', 10, 34000],
                        ['Binder Klip Merk Joyko No. 155', 'Kotak Kecil', 5, 22000],
                        ['Isi Staples No. 10 Merk Kangaro', 'Kotak besar', 1, 67000],
                        ['Stick Note', 'Buah', 3, 30000],
                        ['Box File Executive', 'Buah', 2, 45000],
                        ['Pena Gel Kenko K - 1', 'Lusin', 1, 90000],
                        ['Paper Clip No 1 Merk Atom', 'Kotak Besar', 1, 101000],
                        ['Binder Klip Merk Combo No. 105', 'Kotak Kecil', 10, 11000],
                    ],
                ],
                [
                    'bulan' => 7, 'kategori' => 'Supplies Komputer',
                    'tgl_usulan' => '2026-07-02', 'no_pesanan' => '68/PES/SATKER/PPS-RIAU/2026', 'tgl_pesanan' => '2026-07-03', 'tgl_batas_terima' => '2026-07-06',
                    'no_bastp' => '56/BASTP/SATKER/PPS-RIAU/2026', 'tgl_bastp' => '2026-07-06', 'tgl_faktur' => '2026-07-06', 'tgl_kuitansi' => '2026-07-06',
                    'items' => [
                        ['Tinta Epson 003 Hitam', 'Botol', 2, 135000],
                        ['Tinta Epson 008 Hitam', 'Botol', 1, 250000],
                        ['Tinta HP Smart Tank 210 Hitam', 'Botol', 1, 157000],
                        ['Tinta HP Smart Tank 210 Warna', 'Botol', 1, 157000],
                        ['Tinta Epson 664 Hitam', 'Botol', 2, 135000],
                        ['Tinta Epson 664 Warna', 'Botol', 2, 135000],
                    ],
                ],
                [
                    'bulan' => 8, 'kategori' => 'ATK',
                    'tgl_usulan' => '2026-08-06', 'no_pesanan' => '86/PES/SATKER/PPS-RIAU/2026', 'tgl_pesanan' => '2026-08-07', 'tgl_batas_terima' => '2026-08-10',
                    'no_bastp' => '75/BASTP/SATKER/PPS-RIAU/2026', 'tgl_bastp' => '2026-08-10', 'tgl_faktur' => '2026-08-10', 'tgl_kuitansi' => '2026-08-10',
                    'items' => [
                        ['Map L Ukuran F4', 'Pack', 1, 50000],
                        ['Kertas HVS A4 75 gr Merk Paper One', 'Kotak', 2, 349000],
                        ['Stick Note', 'Buah', 3, 30000],
                        ['Binder Klip Merk Combo No. 260', 'Kotak Kecil', 3, 47000],
                        ['Binder Klip Merk Joyko No. 200', 'Kotak Kecil', 6, 34000],
                        ['Binder Klip Merk Joyko No. 155', 'Kotak Kecil', 6, 22000],
                        ['Map Biasa Merk Biola', 'Kotak', 1, 146000],
                        ['Map Plastik Kancing Merk Felix', 'Kotak', 3, 47000],
                        ['Pena Signo Merk Uni Ball', 'Lusin', 1, 337000],
                    ],
                ],
                [
                    'bulan' => 8, 'kategori' => 'Supplies Komputer',
                    'tgl_usulan' => '2026-08-06', 'no_pesanan' => '87/PES/SATKER/PPS-RIAU/2026', 'tgl_pesanan' => '2026-08-07', 'tgl_batas_terima' => '2026-08-10',
                    'no_bastp' => '76/BASTP/SATKER/PPS-RIAU/2026', 'tgl_bastp' => '2026-08-10', 'tgl_faktur' => '2026-08-10', 'tgl_kuitansi' => '2026-08-10',
                    'items' => [
                        ['Tinta Epson 008 Hitam', 'Botol', 1, 250000],
                        ['Tinta Epson 003 Hitam', 'Botol', 2, 135000],
                        ['Tinta Epson 003 Warna', 'Botol', 3, 135000],
                        ['Tinta HP Smart Tank 210 Hitam', 'Botol', 1, 157000],
                        ['Tinta HP Smart Tank 210 Warna', 'Botol', 1, 157000],
                        ['Tinta Epson 664 Hitam', 'Botol', 1, 135000],
                    ],
                ],
                [
                    'bulan' => 9, 'kategori' => 'ATK',
                    'tgl_usulan' => '2026-09-11', 'no_pesanan' => '98/PES/SATKER/PPS-RIAU/2026', 'tgl_pesanan' => '2026-09-14', 'tgl_batas_terima' => '2026-09-15',
                    'no_bastp' => '91/BASTP/SATKER/PPS-RIAU/2026', 'tgl_bastp' => '2026-09-15', 'tgl_faktur' => '2026-09-15', 'tgl_kuitansi' => '2026-09-15',
                    'items' => [
                        ['Post It', 'Buah', 1, 30000],
                        ['Stick Note', 'Buah', 3, 30000],
                        ['Kertas HVS A4 75 gr Merk Paper One', 'Kotak', 2, 349000],
                        ['Map Biasa Merk Biola', 'Kotak', 1, 146000],
                        ['Map Plastik Kancing Merk Felix', 'Kotak', 3, 47000],
                        ['Map Ijazah Resleting', 'Buah', 1, 120000],
                        ['Pena Signo Merk Uni Ball', 'Lusin', 1, 337000],
                        ['Binder Klip Merk Joyko No. 200', 'Kotak Kecil', 10, 34000],
                        ['Binder Klip Merk Joyko No. 155', 'Kotak Kecil', 5, 22000],
                        ['Binder Klip Merk Combo No. 260', 'Kotak Kecil', 3, 47000],
                        ['Box File Executive', 'Buah', 3, 45000],
                        ['Paper Clip No 1 Merk Atom', 'Kotak Besar', 1, 101000],
                        ['Map L Ukuran F4', 'Pack', 1, 50000],
                        ['Pena Gel Kenko K - 1', 'Lusin', 1, 90000],
                    ],
                ],
                [
                    'bulan' => 9, 'kategori' => 'Supplies Komputer',
                    'tgl_usulan' => '2026-09-11', 'no_pesanan' => '99/PES/SATKER/PPS-RIAU/2026', 'tgl_pesanan' => '2026-09-14', 'tgl_batas_terima' => '2026-09-15',
                    'no_bastp' => '92/BASTP/SATKER/PPS-RIAU/2026', 'tgl_bastp' => '2026-09-15', 'tgl_faktur' => '2026-09-15', 'tgl_kuitansi' => '2026-09-15',
                    'items' => [
                        ['Tinta Epson 008 Hitam', 'Botol', 2, 250000],
                        ['Tinta Epson 003 Hitam', 'Botol', 2, 135000],
                        ['Tinta Epson 003 Warna', 'Botol', 6, 135000],
                        ['Tinta HP Smart Tank 210 Hitam', 'Botol', 1, 157000],
                        ['Tinta HP Smart Tank 210 Warna', 'Botol', 1, 157000],
                        ['Tinta Epson 664 Hitam', 'Botol', 1, 135000],
                        ['Tinta Epson 664 Warna', 'Botol', 3, 135000],
                    ],
                ],
                [
                    'bulan' => 10, 'kategori' => 'ATK',
                    'tgl_usulan' => '2026-10-06', 'no_pesanan' => '109/PES/SATKER/PPS-RIAU/2026', 'tgl_pesanan' => '2026-10-07', 'tgl_batas_terima' => '2026-10-08',
                    'no_bastp' => '104/BASTP/SATKER/PPS-RIAU/2026', 'tgl_bastp' => '2026-10-08', 'tgl_faktur' => '2026-10-08', 'tgl_kuitansi' => '2026-10-08',
                    'items' => [
                        ['Map L Ukuran F4', 'Pack', 2, 50000],
                        ['Kertas HVS Warna A4 80GSM', 'Rim', 1, 120000],
                        ['Kertas HVS A4 75 gr Merk Paper One', 'Kotak', 3, 349000],
                        ['Map Biasa Merk Biola', 'Kotak', 1, 146000],
                        ['Map Plastik Kancing Merk Felix', 'Kotak', 3, 47000],
                        ['Pena Signo Merk Uni Ball', 'Lusin', 1, 337000],
                        ['Binder Klip Merk Combo No. 260', 'Kotak Kecil', 2, 47000],
                        ['Binder Klip Merk Joyko No. 200', 'Kotak Kecil', 5, 34000],
                        ['Binder Klip Merk Joyko No. 155', 'Kotak Kecil', 5, 22000],
                        ['Pena Gel Kenko K - 1', 'Lusin', 1, 90000],
                        ['Stick Note', 'Buah', 3, 30000],
                        ['Stabillo Boss Original', 'Lusin', 1, 90000],
                    ],
                ],
                [
                    'bulan' => 10, 'kategori' => 'Supplies Komputer',
                    'tgl_usulan' => '2026-10-06', 'no_pesanan' => '110/PES/SATKER/PPS-RIAU/2026', 'tgl_pesanan' => '2026-10-07', 'tgl_batas_terima' => '2026-10-08',
                    'no_bastp' => '105/BASTP/SATKER/PPS-RIAU/2026', 'tgl_bastp' => '2026-10-08', 'tgl_faktur' => '2026-10-08', 'tgl_kuitansi' => '2026-10-08',
                    'items' => [
                        ['Mouse Logitech B175', 'Buah', 1, 205000],
                        ['Tinta Epson 008 Hitam', 'Botol', 2, 250000],
                        ['Tinta Epson 008 Warna', 'Botol', 3, 250000],
                        ['Flashdisk 64Gb Sandisk', 'Buah', 1, 155000],
                        ['Tinta Epson 664 Hitam', 'Botol', 1, 135000],
                        ['Tinta Epson 664 Warna', 'Botol', 1, 135000],
                        ['Tinta Epson 003 Hitam', 'Botol', 2, 135000],
                        ['Tinta HP Smart Tank 210 Hitam', 'Botol', 1, 157000],
                    ],
                ],
            ];

            foreach ($transactions as $trx) {
                $total = 0;
                foreach ($trx['items'] as $it) {
                    $total += ($it[2] * $it[3]);
                }

                $terbilangText = ucwords(terbilang_angka($total)) . ' Rupiah,-';

                $this->db->table('trn_atk_pengadaan')->insert([
                    'tahun_anggaran'       => 2026,
                    'bulan'                => $trx['bulan'],
                    'kategori'             => $trx['kategori'],
                    'kegiatan_label'       => 'Dukungan Kegiatan Prasarana Strategis',
                    'mata_anggaran_id'     => $mataAnggaranId,
                    'kode_mata_anggaran'   => '7987.FAD.002.101.A.521211',
                    'penyedia_id'          => $penyediaId,
                    'kop_surat_id'         => $kopSuratId,
                    'no_usulan'            => '',
                    'tgl_usulan'           => $trx['tgl_usulan'],
                    'no_pesanan'           => $trx['no_pesanan'],
                    'tgl_pesanan'          => $trx['tgl_pesanan'],
                    'tgl_batas_terima'     => $trx['tgl_batas_terima'],
                    'persen_denda_harian'  => 2.00,
                    'no_bastp'             => $trx['no_bastp'],
                    'tgl_bastp'            => $trx['tgl_bastp'],
                    'no_faktur'            => '',
                    'tgl_faktur'           => $trx['tgl_faktur'],
                    'no_bukti_kuitansi'    => '',
                    'tgl_kuitansi'         => $trx['tgl_kuitansi'],
                    'ppk_pegawai_id'       => $ppkId,
                    'ppk_nama'             => 'NURHIDAYAT NUGROHO, S.Ars.',
                    'ppk_nip'              => '199012212018021001',
                    'bendahara_pegawai_id' => $bendaharaId,
                    'bendahara_nama'       => 'KH. SRI HANDAYANI, S.Si., M.T.',
                    'bendahara_nip'        => '198204022014122002',
                    'total_nilai'          => $total,
                    'terbilang'            => $terbilangText,
                    'status'               => 'disetujui',
                    'catatan'              => 'Data historis SPJ ATK & Supplies DUKPRAS 2026',
                    'approved_at'          => $now,
                    'created_at'           => $now,
                    'updated_at'           => $now,
                ]);
                $pengadaanId = (int) $this->db->insertID();

                $urutan = 1;
                foreach ($trx['items'] as $it) {
                    $key = strtolower($it[0]);
                    $barangId = $barangMap[$key] ?? ($key === 'tip ex cair merk kenko' ? ($barangMap['tip ex cair merk kenko/joyko'] ?? null) : null);
                    $subtotal = $it[2] * $it[3];
                    $this->db->table('trn_atk_pengadaan_item')->insert([
                        'pengadaan_id'   => $pengadaanId,
                        'barang_id'      => $barangId,
                        'nama_barang'    => $it[0],
                        'satuan'         => $it[1],
                        'volume'         => $it[2],
                        'harga_satuan'   => $it[3],
                        'subtotal'       => $subtotal,
                        'kondisi_barang' => 'Baik',
                        'urutan'         => $urutan++,
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ]);
                }
            }
        }
    }
}

