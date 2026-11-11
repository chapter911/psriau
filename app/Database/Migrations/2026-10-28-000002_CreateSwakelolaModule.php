<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSwakelolaModule extends Migration
{
    public function up()
    {
        $db = $this->db;

        // 1. Create trn_swakelola_paket
        if (! $db->tableExists('trn_swakelola_paket')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'kode_paket' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
                'nama_kegiatan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'tahun_anggaran' => [
                    'type'       => 'INT',
                    'constraint' => 4,
                    'default'    => 2025,
                ],
                'pagu_anggaran' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0.00,
                ],
                'realisasi_anggaran' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0.00,
                ],
                'tipe_swakelola' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'default'    => 'Tipe I',
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
                'kasatker_nama' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => true,
                ],
                'kasatker_nip' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                ],
                'lokasi' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'jangka_waktu' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
                'status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'default'    => 'Selesai',
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
            $this->forge->createTable('trn_swakelola_paket', true);
        }

        // 2. Create trn_swakelola_dokumen
        if (! $db->tableExists('trn_swakelola_dokumen')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'paket_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'tahap' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 5,
                ],
                'nomor_urut' => [
                    'type'       => 'INT',
                    'constraint' => 3,
                ],
                'nama_dokumen' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'default'    => 'Ada',
                ],
                'file_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'file_path' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'sub_files_json' => [
                    'type' => 'LONGTEXT',
                    'null' => true,
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

            $this->forge->addKey('id', true);
            $this->forge->addKey('paket_id');
            $this->forge->createTable('trn_swakelola_dokumen', true);
        }

        // 3. Create trn_swakelola_realisasi
        if (! $db->tableExists('trn_swakelola_realisasi')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'paket_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'kode_akun' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                ],
                'uraian_belanja' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'nomor_spm' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                ],
                'tanggal_spm' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'nomor_sp2d' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                ],
                'jumlah_bayar' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0.00,
                ],
                'file_bukti' => [
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
            $this->forge->createTable('trn_swakelola_realisasi', true);
        }

        // 4. Register Menus in menu_lv1 & menu_lv2
        if ($db->tableExists('menu_lv1') && $db->tableExists('menu_lv2')) {
            $swakelolaId = $this->findOrCreateLv1Menu('Swakelola', '#', 'fas fa-clipboard-check', 5);

            if ($swakelolaId !== null) {
                $this->ensureLv2Menu($swakelolaId, 'Paket Kegiatan', 'admin/swakelola/paket', 'fas fa-tasks', 1);
                $this->ensureLv2Menu($swakelolaId, 'Checklist Dokumen', 'admin/swakelola/dokumen', 'fas fa-check-double', 2);
                $this->ensureLv2Menu($swakelolaId, 'Realisasi Anggaran', 'admin/swakelola/realisasi', 'fas fa-receipt', 3);
                $this->ensureLv2Menu($swakelolaId, 'Rekapitulasi & Laporan', 'admin/swakelola/laporan', 'fas fa-file-invoice-dollar', 4);
            }
        }

        // 5. Seed initial 4 Swakelola Packages (PHTC 3, SPPG, Madrasah PHTC Riau, Madrasah PHTC Riau 5)
        $this->seedInitialPhtc3Data();
        $this->seedInitialSppgData();
        $this->seedInitialMadrasahData();
        $this->seedInitialMadrasahRiau5Data();
    }

    public function down()
    {
        $db = $this->db;

        $swakelolaId = $this->findLv1IdByLabel('Swakelola');
        if ($swakelolaId !== null) {
            $submenus = $db->table('menu_lv2')->where('header', $swakelolaId)->get()->getResultArray();
            foreach ($submenus as $sub) {
                $subId = (string) $sub['id'];
                $db->table('menu_lv2')->where('id', $subId)->delete();
                $this->deleteMenuAksesByMenuId($subId);
            }
            $db->table('menu_lv1')->where('id', $swakelolaId)->delete();
            $this->deleteMenuAksesByMenuId($swakelolaId);
        }

        $this->forge->dropTable('trn_swakelola_realisasi', true);
        $this->forge->dropTable('trn_swakelola_dokumen', true);
        $this->forge->dropTable('trn_swakelola_paket', true);
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

        $this->ensureMenuAksesForMenuId($menuId, false);

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
            $this->ensureMenuAksesForMenuId($menuId, true);
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

        $this->ensureMenuAksesForMenuId($menuId, true);
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

    private function ensureMenuAksesForMenuId(string $menuId, bool $isSubmenu): void
    {
        if (! $this->db->tableExists('menu_akses')) {
            return;
        }

        $roleRows = $this->db->table('menu_akses')
            ->select('role_id')
            ->distinct()
            ->get()
            ->getResultArray();

        if ($roleRows === []) {
            $roleRows = [['role_id' => 1], ['role_id' => 2], ['role_id' => 3], ['role_id' => 5], ['role_id' => 6]];
        }

        foreach ($roleRows as $roleRow) {
            $roleId = (int) ($roleRow['role_id'] ?? 0);
            if ($roleId <= 0) {
                continue;
            }

            $exists = (int) $this->db->table('menu_akses')
                ->where('role_id', $roleId)
                ->where('menu_id', $menuId)
                ->countAllResults();

            if ($exists > 0) {
                continue;
            }

            // Role 1 (Super Admin), Role 2 (Admin): Full Access
            $isSuperOrAdmin = in_array($roleId, [1, 2], true);
            // Role 5: Keuangan
            $isKeuangan = ($roleId === 5);
            // Role 3 (Editor), Role 6 (Admin Tata Usaha)
            $isTataUsaha = in_array($roleId, [3, 6], true);

            $this->db->table('menu_akses')->insert([
                'role_id'       => $roleId,
                'menu_id'       => $menuId,
                'FiturAdd'      => ($isSuperOrAdmin || $isTataUsaha || $isKeuangan) ? 1 : 0,
                'FiturEdit'     => ($isSuperOrAdmin || $isTataUsaha || $isKeuangan) ? 1 : 0,
                'FiturDelete'   => $isSuperOrAdmin ? 1 : 0,
                'FiturExport'   => 1,
                'FiturImport'   => $isSuperOrAdmin ? 1 : 0,
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

    private function seedInitialSppgData(): void
    {
        $db = $this->db;
        $now = date('Y-m-d H:i:s');

        // Check if already seeded
        $existing = $db->table('trn_swakelola_paket')->where('kode_paket', '06.691285.GM.7717.CBI.001.900.A')->countAllResults();
        if ($existing > 0) {
            return;
        }

        $paketData = [
            'kode_paket'         => '06.691285.GM.7717.CBI.001.900.A',
            'nama_kegiatan'      => 'Administrasi Kegiatan Pembangunan Gedung SPPG Provinsi Riau',
            'tahun_anggaran'     => 2025,
            'pagu_anggaran'      => 413000000.00,
            'realisasi_anggaran' => 141159100.00,
            'tipe_swakelola'     => 'Tipe I',
            'ppk_nama'           => 'Nurhidayat Nugroho, S.Ars.',
            'ppk_nip'            => '19901221 201802 1 001',
            'kasatker_nama'      => 'Muhammad Yudi Prasetya, S.T.',
            'kasatker_nip'       => '19800214 201412 1 002',
            'lokasi'             => 'Provinsi Riau (Pekanbaru, Kampar, Dumai, Siak)',
            'jangka_waktu'       => 'September s/d Desember 2025 (4 bulan)',
            'status'             => 'Selesai',
            'created_at'         => $now,
            'updated_at'         => $now,
        ];

        $db->table('trn_swakelola_paket')->insert($paketData);
        $paketId = (int) $db->insertID();

        // 28 Checklist Documents
        $docs = [
            // Tahap A
            ['A', 1, 'DIPA dan Revisi Anggaran (POK)', 'Ada', 'A. 1. 16_2025_RINCIAN KERTAS KERJA SATKER PPS RIAU_Rev 16 POK.pdf', 'Rincian Kertas Kerja Satker (POK) Revisi 16 Tgl 23 Des 2025 & Petikan POK SPPG Pagu Rp 413.000.000,-'],
            ['A', 2, 'RUP/SIRUP paket Swakelola', 'Ada', 'A. 2. Lembar RUP dan Pengumuman SiRUP Paket Swakelola SPPG.docx', 'Format pengumuman SiRUP LKPP Swakelola'],
            ['A', 3, 'KAK (Tujuan, Ruang Lingkup, Keluaran dan Spesifikasi)', 'Ada', 'A. 3. KAK Administrasi SPPG Provinsi Riau.pdf', 'Disahkan PPK dan Kasatker'],
            ['A', 4, 'RAB (Perkiraan Biaya dan Dasar Perhitungan)', 'Ada', 'A. 4. RAB Administrasi SPPG Provinsi Riau.pdf', 'Pagu Rp 413.000.000,-'],
            ['A', 5, 'Dokumen Identifikasi Kebutuhan dan Dasar Pemilihan Swakelola', 'Ada', 'A. 5. DOKUMEN IDENTIFIKASI KEBUTUHAN DAN DASAR PEMILIHAN SWAKELOLA.docx', 'Dasar hukum pemilihan Swakelola Tipe I'],
            // Tahap B
            ['B', 6, 'Surat Keputusan Penyelenggara Swakelola oleh PA/KPA', 'Ada', 'B. 1. SK Penetapan Penyelenggara Swakelola SPPG oleh KPA.docx', 'SK KPA No. 28/KPTS/SATKER-PPS.RIAU/2025'],
            ['B', 7, 'Susunan Tim Persiapan, Tim Pelaksana dan Tim Pengawas beserta tugasnya', 'Ada', 'B. 2. Susunan Tim Persiapan, Pelaksana, Pengawas dan Uraian Tugas.docx', 'Lampiran SK rincian tugas tim'],
            ['B', 8, 'Rencana Kegiatan dan Jadwal Pelaksanaan', 'Ada', 'B. 3. Rencana Kegiatan dan Jadwal Pelaksanaan Administrasi SPPG.docx', 'Matriks jadwal pelaksanaan kurva waktu'],
            ['B', 9, 'KAK final dan RAB rinci', 'Ada', 'B. 4. KAK Final dan RAB Rinci Administrasi SPPG.docx', 'Disahkan PPK dan Tim Persiapan'],
            ['B', 10, 'Dokumen pembahasan atau hasil persiapan kegiatan', 'Ada', 'B. 5. BA Pembahasan Persiapan Swakelola Administrasi SPPG.docx', 'BA Pembahasan No. 01/BA-PERSIAPAN/2025'],
            ['B', 11, 'Rencana kebutuhan tenaga kerja, bahan, peralatan dan tenaga ahli', 'Ada', 'B. 6. Rencana Kebutuhan Tenaga Kerja, Bahan, Peralatan, dan Tenaga Ahli.docx', 'Rincian kebutuhan personil & bahan operasional'],
            // Tahap C
            ['C', 12, 'Surat Perintah atau Dokumen Penugasan', 'Ada', 'C. 1. Surat Perintah Penugasan Tim Pelaksana Swakelola.docx', 'Surat Perintah PPK No. 15/SPP-SWK/2025'],
            ['C', 13, 'Daftar Hadir, Catatan Tenaga Kerja dan Bukti pelaksanaan Kegiatan', 'Ada', 'C. 2. Laporan Pelaksanaan, Daftar Hadir dan Catatan Tenaga Kerja.docx', 'Rekapitulasi logbook & penugasan staf'],
            ['C', 14, 'Laporan progres pekerjaan dan penggunaan anggaran secara berkala', 'Ada', 'C. 3. Laporan Progres Fisik dan Penggunaan Anggaran Berkala.docx', 'Laporan progres bulanan Okt, Nov, Des 2025'],
            ['C', 15, 'Bukti pembelian bahan/barang dari Penyedia', 'Ada', 'C. 4. Bukti Pembelian Bahan dan Barang dari Penyedia (ATK SPM 00415A).pdf', 'Kuitansi & faktur belanja bahan CV. Sinar Cahaya Lestari'],
            ['C', 16, 'Invoice/Kuitansi, Bukti Pembayaran, dan Dokumen Pajak sesuai ketentuan', 'Ada', 'C. 5. Invoice Kuitansi dan Dokumen Pajak (Supplies SPM 00414A).pdf', 'SPM, SP2D, bukti setor pajak PPN/PPh Pasal 22'],
            ['C', 17, 'Dokumentasi fisik kegiatan dan hasil pekerjaan', 'Ada', 'C. 6. Dokumentasi Fisik Kegiatan dan Hasil Pekerjaan SPPG.docx', 'Album foto survei Kampar, Dumai, Siak, Pekanbaru, Jakarta & Medan'],
            ['C', 18, 'Bukti pengeluaran yang sah dan dapat ditelusuri', 'Ada', 'C. 7. Bukti Pengeluaran Sah yang Dapat Ditelusuri (SPPD SPM 00445A).pdf', '11 Berkas SPPD, SPT, Nominatif & Rincian Biaya Riil'],
            // Tahap D
            ['D', 19, 'Laporan atau Catatan Pengawasan Tim Pengawas', 'Ada', 'D. 1. Laporan Catatan Pengawasan Tim Pengawas Swakelola SPPG.docx', 'Laporan berkala hasil pengawasan Tim Was'],
            ['D', 20, 'Bukti Pemeriksaan kesesuaian volume, mutu, waktu dan keluaran', 'Ada', 'D. 2. Bukti Pemeriksaan Kesesuaian Volume, Mutu, Waktu dan Keluaran.docx', 'Lembar checklist pemeriksaan kesesuaian'],
            ['D', 21, 'Rekonsiliasi realisasi fisik dan keuangan', 'Ada', 'D. 3. Berita Acara Rekonsiliasi Realisasi Fisik dan Keuangan.docx', 'BA Rekon Fisik 100% & Keu Rp 141.159.100,-'],
            ['D', 22, 'Catatan penyimpangan dan bukti tindak lanjut', 'Ada', 'D. 4. Catatan Penyimpangan Evaluasi dan Bukti Tindak Lanjut.docx', 'Evaluasi efisiensi sisa pagu Rp 271,84 jt'],
            // Tahap E
            ['E', 23, 'Laporan akhir pelaksanaan Swakelola', 'Ada', 'E. 1. Laporan Akhir Pelaksanaan Swakelola Administrasi SPPG.docx', 'Laporan akhir komprehensif Bab I s/d Bab IV'],
            ['E', 24, 'BAST hasil pekerjaan dari Tim Pelaksana kepada PPK', 'Ada', 'E. 2. Berita Acara Serah Terima (BAST) Hasil Pekerjaan dari Tim Pelaksana kepada PPK.docx', 'BAST No. 01/BAST-SWK/PPK-PPS/2025'],
            ['E', 25, 'Dokumen pemeriksaan/verifikasi hasil pekerjaan', 'Ada', 'E. 3. Berita Acara Pemeriksaan Hasil Pekerjaan (BAPHP).docx', 'BAPHP Tim Pengawas sebelum BAST'],
            ['E', 26, 'Rekapitulasi realisasi anggaran dibandingkan dengan RAB', 'Ada', 'E. 4. Rekapitulasi Realisasi Anggaran Dibandingkan dengan RAB.docx', 'Matriks perbandingan RAB/POK Rev 16 vs Realisasi'],
            ['E', 27, 'Bukti penyelesaian pembayaran dan perpajakan yang relevan', 'Ada', 'E. 5. Bukti Penyelesaian Pembayaran dan Perpajakan.docx', 'BA pelunasan 16 SP2D dan pajak PPN/PPh22 lunas'],
            ['E', 28, 'Dokumen pencatatan aset/persediaan jika keluaran menghasilkan aset/persediaan', 'Ada', 'E. 6. Dokumen Pencatatan Persediaan Barang Habis Pakai dan Aset.docx', 'BA pencatatan barang persediaan habis pakai'],
        ];

        foreach ($docs as $d) {
            $db->table('trn_swakelola_dokumen')->insert([
                'paket_id'     => $paketId,
                'tahap'        => $d[0],
                'nomor_urut'   => $d[1],
                'nama_dokumen' => $d[2],
                'status'       => $d[3],
                'file_name'    => $d[4],
                'file_path'    => 'public/uploads/swakelola/sppg/' . $d[4],
                'keterangan'   => $d[5],
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }

        // Realisasi Belanja (16 Transaksi SPM/SP2D/PTUP Asli = Rp 141.159.100,-)
        $realisasi = [
            ['521211', 'Belanja Bahan - Pembelian ATK Kegiatan Pembangunan Gedung SPPG Bulan November 2025 (CV. Sinar Cahaya Lestari, Kwi No. 128 Tgl 20/11/2025)', '00415A', '2025-12-01', '259991311235904', 4943000.00, 'ATK BULAN NOVEMBER (SPM 00415A).pdf'],
            ['521211', 'Belanja Bahan - Pembelian Supplies Komputer Kegiatan Pembangunan Gedung SPPG Bulan November 2025 (CV. Sinar Cahaya Lestari, Kwi No. 129 Tgl 20/11/2025)', '00414A', '2025-12-01', '259991311235905', 3843000.00, 'SUPLIES KOMPUTER BULAN NOVEMBER (SPM 00414A) (1).pdf'],
            ['521211', 'Belanja Bahan - Pembelian Supplies Komputer Kegiatan Pembangunan Gedung SPPG Bulan Desember 2025 (CV. Sinar Cahaya Lestari, Kwi No. 164 Tgl 08/12/2025)', '00497A', '2025-12-09', '259991311369736', 5850000.00, 'SUPLIES KOMPUTER BULAN DESEMBER (SPM 00497A).pdf'],
            ['521211', 'Belanja Bahan - Pembelian ATK Kegiatan Pembangunan Gedung SPPG Bulan Desember 2025 (CV. Sinar Cahaya Lestari, Kwi No. 163 Tgl 08/12/2025)', '00498A', '2025-12-09', '259991311369737', 4947000.00, 'ATK SPPG DESEMBER (SPM 00498A) OK.pdf'],
            ['521211', 'Belanja Bahan - Pertanggungjawaban TUP (PTUP) Biaya Dokumentasi dan Pelaporan Kegiatan SPPG (Item POK 000875)', '00597A (PTUP)', '2025-12-31', '259991702155733', 8700000.00, 'TUP 1 (KWI DOKUMENTASI DAN PELAPORAN).pdf'],
            ['524111', 'Perjadin Biasa - Monitoring Lokasi SPPG Kab. Kampar untuk 4 Pegawai (ST No. 290/SPT/SATKER/PPS-RIAU/Gs7/2025 Tgl 25/11/2025)', '00445A', '2025-12-03', '259991330451846', 6240000.00, 'SPPD SPPG KAMPAR OKTOBER (SPM 00445A).pdf'],
            ['524111', 'Perjadin Biasa - Koordinasi & Konsultasi Kegiatan SPPG ke Jakarta untuk 3 Pegawai (ST No. 285/SPT/SATKER/PPS-RIAU/Gs7/2025 Tgl 25/11/2025)', '00449A', '2025-12-03', '259991330451774', 18264400.00, 'SPPD JAKARTA NOVEMBER (SPM 00449A).pdf'],
            ['524111', 'Perjadin Biasa - Monitoring Lokasi SPPG Kota Dumai & Kab. Siak untuk 11 Pegawai (ST No. 295/SPT/SATKER/PPS-RIAU/Gs7/2025 Tgl 25/11/2025)', '00483A', '2025-12-09', '259991330477567', 9236000.00, 'SPPD DUMAI SIAK SPPG NOV (SPM 00483A).pdf'],
            ['524111', 'Perjadin Biasa - Koordinasi & Konsultasi Kegiatan SPPG ke Jakarta Tahap II untuk 4 Pegawai (ST No. 299/SPT/SATKER/PPS-RIAU/Gs7/2025 Tgl 28/11/2025)', '00485A', '2025-12-09', '259991330477566', 26466600.00, 'SPPD JAKARTA NOVEMBER (SPM 00485A).pdf'],
            ['524111', 'Perjadin Biasa - Monitoring Lokasi SPPG Kota Dumai untuk 3 Pegawai (ST No. 303/SPT/SATKER/PPS-RIAU/Gs7/2025 Tgl 01/12/2025)', '00489A', '2025-12-09', '259991330478411', 7473000.00, 'SPPD DUMAI DESEMBER (SPM 00489A).pdf'],
            ['524111', 'Perjadin Biasa - Koordinasi Regional Kegiatan SPPG ke Medan untuk 2 Pegawai (ST No. 313/SPT/SATKER/PPS-RIAU/Gs7/2025 Tgl 03/12/2025)', '00535A', '2025-12-16', '259991330513926', 8900100.00, 'SPPD MEDAN DESEMBER (SPM 00535A) OK.pdf'],
            ['524111', 'Perjadin Biasa - Monitoring Lapangan Gedung SPPG untuk 4 Pegawai (ST No. 311/SPT/SATKER/PPS-RIAU/Gs7/2025 Tgl 03/12/2025)', '00536A', '2025-12-16', '259991330513968', 4160000.00, 'SPPD SPPG DESEMBER (SPM 00536A).pdf'],
            ['524111', 'Perjadin Biasa - Monitoring Lokasi SPPG Kab. Kampar untuk 3 Pegawai (ST No. 335/SPT/SATKER/PPS-RIAU/Gs7/2025 Tgl 09/12/2025)', '00556A', '2025-12-22', '259991330531985', 6670000.00, 'SPPD SPPG KAMPAR (SPM 00556A).pdf'],
            ['524111', 'Perjadin Biasa - Monitoring Lokasi SPPG Kab. Siak untuk 3 Pegawai (ST No. 334/SPT/SATKER/PPS-RIAU/Gs7/2025 Tgl 09/12/2025)', '00557A', '2025-12-22', '259991330532060', 6890000.00, 'SPPD SPPG SIAK (SPM 00557A).pdf'],
            ['524111', 'Perjadin Biasa - Pertanggungjawaban TUP (PTUP) Tim PPK ke Lokasi SPPG Riau (Sewa Kendaraan Rp 4.286.000 + UH Rp 9.990.000 + Penginapan Rp 4.000.000)', '00597A (PTUP)', '2025-12-31', '259991702155733', 18276000.00, 'TUP 1 (KWI PERJADIN).pdf'],
            ['524113', 'Perjadin Dalam Kota - Uang Harian > 8 Jam Tim PPK Lapangan Riau untuk 2 Pegawai (ST No. 291/SPT/SATKER/PPS-RIAU/Gs7/2025 Tgl 25/11/2025)', '00450A', '2025-12-03', '259991330451775', 300000.00, '1. SPPD NOVEMBER SPPG (SPM 00450A).pdf'],
        ];

        foreach ($realisasi as $r) {
            $db->table('trn_swakelola_realisasi')->insert([
                'paket_id'       => $paketId,
                'kode_akun'      => $r[0],
                'uraian_belanja' => $r[1],
                'nomor_spm'      => $r[2],
                'tanggal_spm'    => $r[3],
                'nomor_sp2d'     => $r[4],
                'jumlah_bayar'   => $r[5],
                'file_bukti'     => $r[6],
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }
    }

    private function seedInitialMadrasahData(): void
    {
        $db = $this->db;
        $now = date('Y-m-d H:i:s');

        $existing = $db->table('trn_swakelola_paket')->where('kode_paket', '06.691285.GM.7717.RBI.003.900.AB')->countAllResults();
        if ($existing > 0) {
            return;
        }

        $paketData = [
            'kode_paket'         => '06.691285.GM.7717.RBI.003.900.AB',
            'nama_kegiatan'      => 'Administrasi Kegiatan Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau',
            'tahun_anggaran'     => 2025,
            'pagu_anggaran'      => 569100000.00,
            'realisasi_anggaran' => 322773000.00,
            'tipe_swakelola'     => 'Tipe I',
            'ppk_nama'           => 'Nurhidayat Nugroho, S.Ars.',
            'ppk_nip'            => '19901221 201802 1 001',
            'kasatker_nama'      => 'Muhammad Yudi Prasetya, S.T.',
            'kasatker_nip'       => '19800214 201412 1 002',
            'lokasi'             => 'Provinsi Riau (12 Kabupaten / Kota)',
            'jangka_waktu'       => 'Juli s/d Desember 2025 (6 Bulan)',
            'status'             => 'Selesai',
            'created_at'         => $now,
            'updated_at'         => $now,
        ];

        $db->table('trn_swakelola_paket')->insert($paketData);
        $paketId = (int) $db->insertID();

        // 28 Checklist Documents
        $docs = [
            ['A', 1, 'DIPA dan Revisi Anggaran (POK)', 'Ada', 'A. 1. 16_2025_RINCIAN KERTAS KERJA SATKER PPS RIAU_Rev 16 POK.pdf', 'Rincian Kertas Kerja Satker (POK) Revisi 16 Tgl 23 Des 2025 & Petikan POK Madrasah PHTC Riau Pagu Rp 569.100.000,-'],
            ['A', 2, 'RUP/SIRUP paket Swakelola', 'Ada', 'A. 2. Lembar RUP dan Pengumuman SiRUP Paket Swakelola Madrasah PHTC Riau.docx', 'Lembar Penetapan RUP & Pengumuman SiRUP LKPP Swakelola'],
            ['A', 3, 'KAK (Tujuan, Ruang Lingkup, Keluaran dan Spesifikasi)', 'Ada', 'A. 3. KAK Administrasi Madrasah PHTC Provinsi Riau.pdf', 'Disahkan PPK dan Kasatker'],
            ['A', 4, 'RAB (Perkiraan Biaya dan Dasar Perhitungan)', 'Ada', 'A. 4. RAB Administrasi Madrasah PHTC Provinsi Riau.pdf', 'Pagu Rp 569.100.000,- (5 Akun Belanja POK Rev 16)'],
            ['A', 5, 'Dokumen Identifikasi Kebutuhan dan Dasar Pemilihan Swakelola', 'Ada', 'A. 5. DOKUMEN IDENTIFIKASI KEBUTUHAN DAN DASAR PEMILIHAN SWAKELOLA.docx', 'Dasar hukum pemilihan Swakelola Tipe I'],
            ['B', 6, 'Surat Keputusan Penyelenggara Swakelola oleh PA/KPA', 'Ada', 'B. 1. SK Penetapan Penyelenggara Swakelola Madrasah PHTC Riau oleh KPA.docx', 'SK KPA No. 22/KPTS/SATKER-PPS.RIAU/2025'],
            ['B', 7, 'Susunan Tim Persiapan, Tim Pelaksana dan Tim Pengawas beserta tugasnya', 'Ada', 'B. 2. Susunan Tim Persiapan, Pelaksana, Pengawas dan Uraian Tugas.docx', 'Lampiran SK Penetapan Tim Swakelola & Uraian Tugas'],
            ['B', 8, 'Rencana Kegiatan dan Jadwal Pelaksanaan', 'Ada', 'B. 3. Rencana Kegiatan dan Jadwal Pelaksanaan Administrasi Madrasah PHTC Riau.docx', 'Matriks jadwal kurva waktu Juli s.d. Desember 2025 (6 Bulan)'],
            ['B', 9, 'KAK final dan RAB rinci', 'Ada', 'B. 4. KAK Final dan RAB Rinci Administrasi Madrasah PHTC Riau.docx', 'Disahkan PPK dan Tim Persiapan'],
            ['B', 10, 'Dokumen pembahasan atau hasil persiapan kegiatan', 'Ada', 'B. 5. BA Pembahasan Persiapan Swakelola Administrasi Madrasah PHTC Riau.docx', 'BA Pembahasan No. 02/BA-PERSIAPAN/SWK-MDR/2025'],
            ['B', 11, 'Rencana kebutuhan tenaga kerja, bahan, peralatan dan tenaga ahli', 'Ada', 'B. 6. Rencana Kebutuhan Tenaga Kerja, Bahan, Peralatan, dan Tenaga Ahli.docx', 'Rencana kebutuhan SDM, ATK, supplies, perjadin & narsum'],
            ['C', 12, 'Surat Perintah atau Dokumen Penugasan', 'Ada', 'C. 1. Surat Perintah Penugasan Tim Pelaksana Swakelola.docx', 'Surat Perintah PPK No. 12/SPP-SWK/PPK-PPS/2025'],
            ['C', 13, 'Daftar Hadir, Catatan Tenaga Kerja dan Bukti pelaksanaan Kegiatan', 'Ada', 'C. 2. Laporan Pelaksanaan, Daftar Hadir dan Catatan Tenaga Kerja.docx', 'Rekapitulasi logbook bulanan dan presensi tim pelaksana'],
            ['C', 14, 'Laporan progres pekerjaan dan penggunaan anggaran secara berkala', 'Ada', 'C. 3. Laporan Progres Fisik dan Penggunaan Anggaran Berkala.docx', 'Laporan progres bulanan Juli s.d. Desember 2025'],
            ['C', 15, 'Bukti pembelian bahan/barang dari Penyedia', 'Ada', 'C. 4. Bukti Pembelian Bahan dan Barang dari Penyedia (ATK SPM 00255A).pdf', '6 Berkas SPM/Kuitansi/BAST/Faktur Pembelian ATK & Supplies CV. Sinar Cahaya Lestari'],
            ['C', 16, 'Invoice/Kuitansi, Bukti Pembayaran, dan Dokumen Pajak sesuai ketentuan', 'Ada', 'C. 5. Invoice Kuitansi dan Dokumen Pajak (Honor Narsum SPM 00350A).pdf', '12 Berkas SPM/Kuitansi/SSP Pajak (2 SPM Honor Narsum TP4D + 10 SPM Gaji Staf Pendukung)'],
            ['C', 17, 'Dokumentasi fisik kegiatan dan hasil pekerjaan', 'Ada', 'C. 6. Dokumentasi Fisik Kegiatan dan Hasil Pekerjaan Madrasah PHTC Riau.docx', 'Album foto survei & monitoring lapangan madrasah di 12 Kabupaten/Kota Provinsi Riau'],
            ['C', 18, 'Bukti pengeluaran yang sah dan dapat ditelusuri', 'Ada', 'C. 7. Bukti Pengeluaran Sah yang Dapat Ditelusuri (SPPD SPM 00270A).pdf', '25 Berkas Lengkap SPPD, SPT, Nominatif & Bukti Riil Perjadin Biasa & Dalam Kota'],
            ['D', 19, 'Laporan atau Catatan Pengawasan Tim Pengawas', 'Ada', 'D. 1. Laporan Catatan Pengawasan Tim Pengawas Swakelola Madrasah PHTC Riau.docx', 'Laporan berkala hasil audit dan pengawasan fisik/keuangan Tim Was'],
            ['D', 20, 'Bukti Pemeriksaan kesesuaian volume, mutu, waktu dan keluaran', 'Ada', 'D. 2. Bukti Pemeriksaan Kesesuaian Volume, Mutu, Waktu dan Keluaran.docx', 'Lembar checklist verifikasi kesesuaian output & spesifikasi teknis'],
            ['D', 21, 'Rekonsiliasi realisasi fisik dan keuangan', 'Ada', 'D. 3. Berita Acara Rekonsiliasi Realisasi Fisik dan Keuangan.docx', 'BA Rekonsiliasi Fisik 100% & Keuangan Rp 322.773.000,- (56,72%)'],
            ['D', 22, 'Catatan penyimpangan dan bukti tindak lanjut', 'Ada', 'D. 4. Catatan Penyimpangan Evaluasi dan Bukti Tindak Lanjut.docx', 'Evaluasi efisiensi anggaran penghematan sisa pagu Rp 246.327.000,-'],
            ['E', 23, 'Laporan akhir pelaksanaan Swakelola', 'Ada', 'E. 1. Laporan Akhir Pelaksanaan Swakelola Administrasi Madrasah PHTC Riau.docx', 'Laporan akhir komprehensif Bab I s/d Bab IV kegiatan rehabilitasi madrasah'],
            ['E', 24, 'BAST hasil pekerjaan dari Tim Pelaksana kepada PPK', 'Ada', 'E. 2. Berita Acara Serah Terima (BAST) Hasil Pekerjaan dari Tim Pelaksana kepada PPK.docx', 'BAST No. 03/BAST-SWK/PPK-PPS/2025'],
            ['E', 25, 'Dokumen pemeriksaan/verifikasi hasil pekerjaan', 'Ada', 'E. 3. Berita Acara Pemeriksaan Hasil Pekerjaan (BAPHP).docx', 'BAPHP No. 03/BAPHP-SWK/WAS-PPS/2025 Tim Pengawas'],
            ['E', 26, 'Rekapitulasi realisasi anggaran dibandingkan dengan RAB', 'Ada', 'E. 4. Rekapitulasi Realisasi Anggaran Dibandingkan dengan RAB.docx', 'Matriks rekonsiliasi 5 akun POK Rev 16 vs 43 SPM realisasi definitif'],
            ['E', 27, 'Bukti penyelesaian pembayaran dan perpajakan yang relevan', 'Ada', 'E. 5. Bukti Penyelesaian Pembayaran dan Perpajakan.docx', 'BA penyelesaian pembayaran 43 SP2D dan pelunasan seluruh kewajiban perpajakan'],
            ['E', 28, 'Dokumen pencatatan aset/persediaan jika keluaran menghasilkan aset/persediaan', 'Ada', 'E. 6. Dokumen Pencatatan Persediaan Barang Habis Pakai dan Aset.docx', 'BA pencatatan barang persediaan habis pakai ATK dan supplies komputer'],
        ];

        foreach ($docs as $d) {
            $db->table('trn_swakelola_dokumen')->insert([
                'paket_id'     => $paketId,
                'tahap'        => $d[0],
                'nomor_urut'   => $d[1],
                'nama_dokumen' => $d[2],
                'status'       => $d[3],
                'file_name'    => $d[4],
                'file_path'    => 'public/uploads/swakelola/madrasah_riau/' . $d[4],
                'keterangan'   => $d[5],
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }

        // Realisasi Belanja (43 Transaksi SPM/SP2D/PTUP Asli = Rp 322.773.000,-)
        $realisasi = [
            ['521213', 'Belanja Honor Output Kegiatan - Honorarium Narasumber TP4D Kejati Riau Bulan Oktober 2025 (50 OJ)', '00350A', '2025-10-31', '259991330369850', 45000000.00, '1. HONOR NARSUM TP4D OKTOBER (SPM 00350A).pdf'],
            ['521213', 'Belanja Honor Output Kegiatan - Honorarium Narasumber TP4D Kejati Riau Bulan November 2025 (30 OJ)', '00464A', '2025-12-04', '259991330462001', 27000000.00, '2. HONOR TP4D NOVEMBER (SPM 00464A).pdf'],
            ['521811', 'Belanja Bahan Persediaan Konsumsi - Pembelian ATK Juli 2025 Penggantian Uang Persediaan (GUP September)', '00279A (GUP)', '2025-09-24', '259991110599182', 1545000.00, '1. GUP SEPTEMBER (SPM 00279A) ATK JULI OKE.pdf'],
            ['521811', 'Belanja Bahan Persediaan Konsumsi - Pembelian ATK & Computer Supplies Bulan Agustus 2025 (CV. Sinar Cahaya Lestari)', '00255A', '2025-09-17', '259991310986701', 5760000.00, '2. ATK DAN SUPPLIES AGUSTUS (SPM 00255A) OK.pdf'],
            ['521811', 'Belanja Bahan Persediaan Konsumsi - Pembelian ATK & Computer Supplies Bulan September 2025 (CV. Sinar Cahaya Lestari)', '00294A', '2025-10-06', '259991311046210', 5760000.00, '3. ATK DAN SUPPLIES SEPTEMBER (SPM 00294A) OK.pdf'],
            ['521811', 'Belanja Bahan Persediaan Konsumsi - Pembelian ATK & Computer Supplies Bulan Oktober 2025 (CV. Sinar Cahaya Lestari)', '00321A', '2025-10-21', '259991311100412', 5760000.00, '4. ATK DAN SUPPLIES  OKTOBER (SPM 00321 A) OK .pdf'],
            ['521811', 'Belanja Bahan Persediaan Konsumsi - Pembelian ATK & Computer Supplies Bulan November 2025 (CV. Sinar Cahaya Lestari)', '00371A', '2025-11-13', '259991311195670', 5760000.00, '5. ATK DAN SUPLIES NOVEMBER (SPM 00371A) OK.pdf'],
            ['521811', 'Belanja Bahan Persediaan Konsumsi - Pembelian ATK & Computer Supplies Bulan Desember 2025 (CV. Sinar Cahaya Lestari)', '00499A', '2025-12-09', '259991311369738', 6781000.00, '6. ATK SUPLIES DESEMBER (SPM 00499A) OK.pdf'],
            ['522191', 'Belanja Jasa Lainnya - Gaji Tenaga Pendukung Teknis Bulan Juli & Agustus 2025 a.n. Revano (2 OB @ Rp 6.000.000)', '00241A', '2025-09-09', '259991320492100', 12000000.00, '1. GAJI BULAN JULI DAN AGUSTUS (SPM 00241A) OKE.pdf'],
            ['522191', 'Belanja Jasa Lainnya - Gaji Tenaga Pendukung Teknis Bulan Agustus 2025 a.n. Ade Putra (1 OB @ Rp 6.000.000)', '00242A', '2025-09-09', '259991320492101', 6000000.00, '2. GAJI BULAN AGUSTUS (SPM 00242A).pdf'],
            ['522191', 'Belanja Jasa Lainnya - Gaji Tenaga Pendukung Teknis Bulan September 2025 a.n. Ade Putra (1 OB @ Rp 6.000.000)', '00256A', '2025-09-17', '259991320509822', 6000000.00, '3. GAJI BULAN SEPTEMBER (SPM 00256A) OKE.pdf'],
            ['522191', 'Belanja Jasa Lainnya - Gaji Tenaga Pendukung Teknis Bulan September 2025 a.n. Revano (1 OB @ Rp 6.000.000)', '00257A', '2025-09-17', '259991320509823', 6000000.00, '4. GAJI BULAN SEPTEMBER (SPM 00257A) OKE.pdf'],
            ['522191', 'Belanja Jasa Lainnya - Gaji Tenaga Pendukung Teknis Bulan Oktober 2025 a.n. Revano (1 OB @ Rp 6.000.000)', '00338A', '2025-10-27', '259991320584711', 6000000.00, '5. GAJI BULAN OKTOBER (SPM 00338A) OK.pdf'],
            ['522191', 'Belanja Jasa Lainnya - Gaji Tenaga Pendukung Teknis Bulan Oktober 2025 a.n. Ade Putra (1 OB @ Rp 6.000.000)', '00337A', '2025-10-27', '259991320584710', 6000000.00, '6. GAJI BULAN OKTOBER (SPM 00337A).pdf'],
            ['522191', 'Belanja Jasa Lainnya - Gaji Tenaga Pendukung Teknis Bulan November 2025 a.n. Ade Putra (1 OB @ Rp 6.000.000)', '00429A', '2025-12-01', '259991320674890', 6000000.00, '7. GAJI BULAN NOVEMBER (SPM 00429).pdf'],
            ['522191', 'Belanja Jasa Lainnya - Gaji Tenaga Pendukung Teknis Bulan November 2025 a.n. Revano (1 OB @ Rp 6.000.000)', '00428A', '2025-12-01', '259991320674889', 6000000.00, '8. GAJI BULAN NOVEMBER (SPM 00428A).pdf'],
            ['522191', 'Belanja Jasa Lainnya - Gaji Tenaga Pendukung Teknis Bulan Desember 2025 a.n. Ade Putra (1 OB @ Rp 6.000.000)', '00481A', '2025-12-09', '259991320700543', 6000000.00, '9. GAJI DESEMBER (SPM 00481A) OK.pdf'],
            ['522191', 'Belanja Jasa Lainnya - Gaji Tenaga Pendukung Teknis Bulan Desember 2025 a.n. Revano (1 OB @ Rp 6.000.000)', '00480A', '2025-12-09', '259991320700542', 6000000.00, '10. GAJI DESEMBER (SPM 00480A).pdf'],
            ['524111', 'Perjadin Biasa - Rekon MC-0 Madrasah PHTC Kab. Siak untuk 4 Pegawai (MAN 1 Siak Tgl 18-19 Sep 2025)', '00270A', '2025-09-22', '259991330286411', 5090000.00, 'SPPD SIAK PHTC 1 SEPTEMBER (SPM 00270A) OK.pdf'],
            ['524111', 'Perjadin Biasa - Workshop Pengendalian Pelaksanaan Program Madrasah ke Provinsi Banten untuk 3 Pegawai', '00287A', '2025-10-02', '259991330302145', 13910000.00, 'SPPD OKTOBER BANTEN (SPM 00287A) OK.pdf'],
            ['524111', 'Perjadin Biasa - Bimtek Implementasi SIPBJ V2 & SPSE 4.5 Madrasah ke Kota Medan untuk 3 Pegawai', '00310A', '2025-10-15', '259991330331002', 14860000.00, 'SPPD MEDAN OKTOBER (SPM 00310A).pdf'],
            ['524111', 'Perjadin Biasa - Monev Lapangan Progres Fisik Madrasah PHTC Kab. Siak Tahap 1 untuk 4 Pegawai', '00311A', '2025-10-15', '259991330331003', 4900000.00, 'SPPD SIAK PHTC 1 OKTOBER (SPM 00311A) OK.pdf'],
            ['524111', 'Perjadin Biasa - Monitoring Mutu Konstruksi Madrasah Kab. Siak untuk 3 Pegawai (PPK, Pengawas, Pelaksana)', '00313A', '2025-10-15', '259991330331005', 3960000.00, 'SPPD SIAK OKTOBER (SPM 00313A).pdf'],
            ['524111', 'Perjadin Biasa - Koordinasi Pelaksanaan Kegiatan Madrasah PHTC ke Banda Aceh untuk 1 Pegawai', '00315A', '2025-10-16', '259991330333420', 5560000.00, 'SPPD ACEH OKTOBER (SPM 00315A).pdf'],
            ['524111', 'Perjadin Biasa - Koordinasi Teknis Program Madrasah ke Provinsi Banten Bulan November untuk 2 Pegawai', '00388A', '2025-11-19', '259991330419812', 11230000.00, 'SPPD BANTEN NOVEMBER (SPM 00388A).pdf'],
            ['524111', 'Perjadin Biasa - Monev Lapangan Progres Fisik Madrasah PHTC Kab. Siak Tahap 2 untuk 4 Pegawai', '00444A', '2025-12-03', '259991330451845', 7550000.00, 'SPPD SIAK PHTC 1 NOVEMBER (SPM 00444A).pdf'],
            ['524111', 'Perjadin Biasa - Konsultasi & Evaluasi Teknis Progres Madrasah ke Kementerian PUPR Jakarta untuk 3 Pegawai', '00447A', '2025-12-03', '259991330451772', 18250000.00, 'SPPD JAKARTA NOVEMBER (SPM 00447A).pdf'],
            ['524111', 'Perjadin Biasa - Monev Akhir & Rekonsiliasi Lapangan Madrasah PHTC Kab. Siak Bulan Desember untuk 2 Pegawai', '00532A', '2025-12-16', '259991330513923', 3030000.00, 'SPPD SIAK DESEMBER (SPM 00532A).pdf'],
            ['524111', 'Perjadin Biasa - Rapat Koordinasi Evaluasi Akhir Kegiatan Madrasah ke Provinsi Banten untuk 1 Pegawai', '00544A', '2025-12-17', '259991330521098', 5610000.00, 'SPPD BANTEN DESMBER (SPM 00544A).pdf'],
            ['524111', 'Perjadin Biasa - Koordinasi Pelaporan dan Audit Teknis Madrasah ke Banten & Semarang untuk 1 Pegawai', '00558A', '2025-12-22', '259991330532061', 6130000.00, 'SPPD BANTEN SEMARANG DESEMBER (SPM 00558A).pdf'],
            ['524111', 'Perjadin Biasa - Koordinasi Finalisasi Serah Terima Hasil Pekerjaan Madrasah ke Banten untuk 3 Pegawai', '00562A', '2025-12-22', '259991330532065', 16837000.00, 'SPPD DESEMBER BANTEN (SPM 00562A).pdf'],
            ['524111', 'Perjadin Biasa - Pertanggungjawaban TUP (PTUP) Monev Lapangan Tim PPK & Was Madrasah PHTC Subkomponen AB', '00599A (PTUP)', '2025-12-31', '259991702155735', 22090000.00, 'TUP 3 SPPD PHTC 1 (SPM 00599A).pdf'],
            ['524113', 'Perjadin Dalam Kota - Monitoring & Koordinasi Lapangan Kota Pekanbaru Bulan September untuk 7 Pegawai', '00271A', '2025-09-22', '259991330286412', 1050000.00, 'SPPD PKU SEPTEMBER PHTC 1  (SPM 00271A).pdf'],
            ['524113', 'Perjadin Dalam Kota - Monitoring Lapangan Madrasah Kota Pekanbaru Bulan Oktober Tahap 1 untuk 4 Pegawai', '00309A', '2025-10-15', '259991330331001', 600000.00, 'SPPD PKU OKTOBER PHTC 1 (SPM 00309A) OK.pdf'],
            ['524113', 'Perjadin Dalam Kota - Monitoring Lapangan Madrasah Kota Pekanbaru Bulan Oktober Tahap 2 untuk 5 Pegawai', '00312A', '2025-10-15', '259991330331004', 750000.00, 'SPPD PEKANBARU OKTOBER PHTC 1 (SPM 00312A).pdf'],
            ['524113', 'Perjadin Dalam Kota - Monitoring Lapangan Madrasah Kota Pekanbaru Bulan Oktober Tahap 3 untuk 5 Pegawai', '00349A', '2025-10-31', '259991330369849', 750000.00, 'SPPD PKU OKTOBER PHTC 1  (SPM 00349A) OK.pdf'],
            ['524113', 'Perjadin Dalam Kota - Monitoring Lapangan Madrasah Kota Pekanbaru Bulan November Tahap 1 untuk 4 Pegawai', '00405A', '2025-11-26', '259991330438101', 600000.00, 'SPPD PKU NOVEMBER PHTC 1 (SPM 00405A) OK.pdf'],
            ['524113', 'Perjadin Dalam Kota - Monitoring Lapangan Madrasah Kota Pekanbaru Bulan November Tahap 2 untuk 2 Pegawai', '00406A', '2025-11-26', '259991330438102', 300000.00, 'SPPD PKU NOVEMBER PHTC 1 (SPM 00406A).pdf'],
            ['524113', 'Perjadin Dalam Kota - Monitoring Lapangan Madrasah Kota Pekanbaru Bulan November Tahap 3 untuk 3 Pegawai', '00443A', '2025-12-03', '259991330451844', 450000.00, 'SPPD PKU NOVEMBER PHTC 1 (SPM 00443A).pdf'],
            ['524113', 'Perjadin Dalam Kota - Monitoring Lapangan Madrasah Kota Pekanbaru Bulan Desember Tahap 1 untuk 2 Pegawai', '00491A', '2025-12-09', '259991330478413', 300000.00, 'SPPD PKU DESEMBER PHTC 1 (SPM 00491A).pdf'],
            ['524113', 'Perjadin Dalam Kota - Monitoring Lapangan Madrasah Kota Pekanbaru Bulan Desember Tahap 2 untuk 4 Pegawai', '00493A', '2025-12-09', '259991330478415', 600000.00, 'SPPD PKU DESEMBER PHTC 1 (SPM 00493A).pdf'],
            ['524113', 'Perjadin Dalam Kota - Monitoring Lapangan Madrasah Kota Pekanbaru Bulan Desember Tahap 3 untuk 2 Pegawai', '00559A', '2025-12-22', '259991330532062', 300000.00, 'SPPD PKU DESEMBER PHTC 1 (SPM 00559A) OK.pdf'],
            ['524113', 'Perjadin Dalam Kota - Pelaksanaan Monitoring Evaluasi Akhir Lapangan Kota Pekanbaru untuk 7 Pegawai & Pendamping Kejaksaan', '00561A', '2025-12-22', '259991330532064', 8700000.00, 'SPPD PEKANBARU  DESEMBER (SPM 00561A).pdf'],
        ];

        foreach ($realisasi as $r) {
            $db->table('trn_swakelola_realisasi')->insert([
                'paket_id'       => $paketId,
                'kode_akun'      => $r[0],
                'uraian_belanja' => $r[1],
                'nomor_spm'      => $r[2],
                'tanggal_spm'    => $r[3],
                'nomor_sp2d'     => $r[4],
                'jumlah_bayar'   => $r[5],
                'file_bukti'     => $r[6],
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }
    }

    private function seedInitialPhtc3Data(): void
    {
        $db = $this->db;
        $now = date('Y-m-d H:i:s');

        $existing = $db->table('trn_swakelola_paket')->where('kode_paket', '06.691285.GM.7717.RBI.003.051.D')->countAllResults();
        if ($existing > 0) {
            return;
        }

        $paketData = [
            'kode_paket'         => '06.691285.GM.7717.RBI.003.051.D',
            'nama_kegiatan'      => 'Rapat Koordinasi Survey dan Perencanaan Teknis Madrasah PHTC Tahap 3',
            'tahun_anggaran'     => 2025,
            'pagu_anggaran'      => 90000000.00,
            'realisasi_anggaran' => 87955000.00,
            'tipe_swakelola'     => 'Tipe I',
            'ppk_nama'           => 'Nurhidayat Nugroho, S.Ars.',
            'ppk_nip'            => '19901221 201802 1 001',
            'kasatker_nama'      => 'Muhammad Yudi Prasetya, S.T.',
            'kasatker_nip'       => '19800214 201412 1 002',
            'lokasi'             => 'Hotel Royal Asnof, Kota Pekanbaru, Provinsi Riau',
            'jangka_waktu'       => 'November s/d Desember 2025 (Fullboard Meeting)',
            'status'             => 'Selesai',
            'created_at'         => $now,
            'updated_at'         => $now,
        ];

        $db->table('trn_swakelola_paket')->insert($paketData);
        $paketId = (int) $db->insertID();

        // 28 Checklist Documents
        $docs = [
            ['A', 1, 'DIPA dan Revisi Anggaran (POK)', 'Ada', 'A. 1. 16_2025_RINCIAN KERTAS KERJA SATKER PPS RIAU_Rev 16 POK.pdf', 'Rincian Kertas Kerja Satker (POK) Revisi 16 Tgl 23 Des 2025 & Petikan POK Rakor PHTC 3 Pagu Rp 90.000.000,-'],
            ['A', 2, 'RUP/SIRUP paket Swakelola', 'Ada', 'A. 2. Lembar RUP dan Pengumuman SiRUP Paket Swakelola Rakor Madrasah PHTC Tahap 3.docx', 'Lembar Penetapan RUP & Pengumuman SiRUP LKPP Swakelola Rakor PHTC Tahap 3'],
            ['A', 3, 'KAK (Tujuan, Ruang Lingkup, Keluaran dan Spesifikasi)', 'Ada', 'A. 3. KAK_RAKOR Survei dan Perencanaan Teknik Madrasah PHTC Riau Thp 3_2025.pdf', 'KAK Rapat Koordinasi Survey dan Perencanaan Teknis Madrasah PHTC Tahap 3'],
            ['A', 4, 'RAB (Perkiraan Biaya dan Dasar Perhitungan)', 'Ada', 'A. 4. RAB_RAKOR Survei dan Perencanaan Teknik Madrasah PHTC Riau Thp 3_2025.pdf', 'RAB Rapat Koordinasi Survey dan Perencanaan Teknis Madrasah PHTC Tahap 3 (Rp 90.000.000,-)'],
            ['A', 5, 'Dokumen Identifikasi Kebutuhan dan Dasar Pemilihan Swakelola', 'Ada', 'A. 5. DOKUMEN IDENTIFIKASI KEBUTUHAN DAN DASAR PEMILIHAN SWAKELOLA.docx', 'Dokumen Identifikasi Kebutuhan dan Dasar Pemilihan Swakelola Tipe I'],
            ['B', 6, 'Surat Keputusan Penyelenggara Swakelola oleh PA/KPA', 'Ada', 'PEMBENTUKAN TIM PENYELENGGARA SWAKELOLA KEGIATAN RAPAT KOORDINASI.pdf', 'SK Kasatker No. 27/KPTS/SATKER/PPS-RIAU/2025 Penetapan Tim Penyelenggara Swakelola'],
            ['B', 7, 'Susunan Tim Persiapan, Tim Pelaksana dan Tim Pengawas beserta tugasnya', 'Ada', 'B. 2. Susunan Tim Persiapan, Pelaksana, Pengawas dan Uraian Tugas.docx', 'Susunan Tim Persiapan, Pelaksana, Pengawas beserta Uraian Tugas 7 Orang'],
            ['B', 8, 'Rencana Kegiatan dan Jadwal Pelaksanaan', 'Ada', 'B. 3. Rencana_Kegiatan_dan_Jadwal_Rakor_Madrasah_PHTC_Tahap_3.docx', 'Rencana Kegiatan dan Jadwal Pelaksanaan Rakor Madrasah PHTC Tahap 3'],
            ['B', 9, 'KAK final dan RAB rinci', 'Ada', 'B. 4. KAK Final dan RAB Rinci Rakor Madrasah PHTC Tahap 3.docx', 'Disahkan PPK dan Tim Persiapan Rakor Madrasah PHTC Tahap 3'],
            ['B', 10, 'Dokumen pembahasan atau hasil persiapan kegiatan', 'Ada', 'B. 5. BA Pembahasan Persiapan Swakelola_Rakor_Madrasah_PHTC_Tahap_3.docx', 'Berita Acara Pembahasan Persiapan Swakelola Rakor Madrasah PHTC Tahap 3'],
            ['B', 11, 'Rencana kebutuhan tenaga kerja, bahan, peralatan dan tenaga ahli', 'Ada', 'B. 6. Rencana_Kebutuhan_Rakor_PHTC_Tahap_3.docx', 'Rencana Kebutuhan Tenaga Kerja, Bahan dan Peralatan Rakor PHTC Tahap 3'],
            ['C', 12, 'Surat Perintah atau Dokumen Penugasan', 'Ada', 'C. 1. Surat Perintah Penugasan Tim Pelaksana Swakelola.docx', 'Surat Perintah Penugasan Tim Pelaksana Swakelola Rakor PHTC Tahap 3'],
            ['C', 13, 'Daftar Hadir, Catatan Tenaga Kerja dan Bukti pelaksanaan Kegiatan', 'Ada', 'C. 2. Laporan Pelaksanaan dan Daftar Hadir.pdf', 'Laporan Pelaksanaan Kegiatan dan Presensi Daftar Hadir Peserta Rakor (33 Org)'],
            ['C', 14, 'Laporan progres pekerjaan dan penggunaan anggaran secara berkala', 'Ada', 'C. 3. Laporan Progres Fisik dan Penggunaan Anggaran Berkala.docx', 'Laporan Progres Fisik dan Penggunaan Anggaran Berkala Rakor PHTC 3'],
            ['C', 15, 'Bukti pembelian bahan/barang dari Penyedia', 'Ada', 'ATK BULAN NOVEMBER (SPM 00417A).pdf', '5 Berkas SPM/Kuitansi/Faktur Pembelian Bahan (ATK, Supplies, Materi Kit, Spanduk, Cetak)'],
            ['C', 16, 'Invoice/Kuitansi, Bukti Pembayaran, dan Dokumen Pajak sesuai ketentuan', 'Ada', 'HONORARIUM NARASUMBER (SPM  00423A).pdf', '2 Berkas SPM/Kuitansi/SSP Pajak Honorarium Narasumber & Tim Swakelola 7 Orang'],
            ['C', 17, 'Dokumentasi fisik kegiatan dan hasil pekerjaan', 'Ada', 'C. 6. Dokumentasi Kegiatan Rapat dan Survei  PHTC Tahap 3.pdf', 'Dokumentasi Foto Fisik Kegiatan Rapat & Survey Lapangan Madrasah PHTC 3'],
            ['C', 18, 'Bukti pengeluaran yang sah dan dapat ditelusuri', 'Ada', 'PAKET MEETING RAKOR (SPM 00446A) OK.pdf', '3 Berkas SPM/Kuitansi Paket Meeting Hotel Royal Asnof & Perjadin Uang Harian/Transport 33 Peserta'],
            ['D', 19, 'Laporan atau Catatan Pengawasan Tim Pengawas', 'Ada', 'D. 1. Laporan Catatan Pengawasan Tim Pengawas Swakelola Rakor PHTC 3.docx', 'Laporan Pengawasan Tim Pengawas Rakor Madrasah PHTC Tahap 3'],
            ['D', 20, 'Bukti Pemeriksaan kesesuaian volume, mutu, waktu dan keluaran', 'Ada', 'D. 2. Bukti Pemeriksaan Kesesuaian Volume, Mutu, Waktu dan Keluaran.docx', 'Lembar Checklist Verifikasi Kesesuaian Output dan Spesifikasi Teknis'],
            ['D', 21, 'Rekonsiliasi realisasi fisik dan keuangan', 'Ada', 'D. 3. Berita Acara Rekonsiliasi Realisasi Fisik dan Keuangan.docx', 'BA Rekonsiliasi Fisik 100% & Realisasi Keuangan Rp 87.955.000,- (97,73%)'],
            ['D', 22, 'Catatan penyimpangan dan bukti tindak lanjut', 'Ada', 'D. 4. Catatan Penyimpangan Evaluasi dan Bukti Tindak Lanjut.docx', 'Evaluasi Efisiensi Anggaran Penghematan Sisa Pagu Rp 2.045.000,-'],
            ['E', 23, 'Laporan akhir pelaksanaan Swakelola', 'Ada', 'E. 1. Laporan Akhir Pelaksanaan Swakelola Rakor Madrasah PHTC Tahap 3.docx', 'Laporan Akhir Komprehensif Pelaksanaan Swakelola Rakor Madrasah PHTC Tahap 3'],
            ['E', 24, 'BAST hasil pekerjaan dari Tim Pelaksana kepada PPK', 'Ada', 'E. 2. Berita Acara Serah Terima (BAST) Hasil Pekerjaan dari Tim Pelaksana kepada PPK.docx', 'BAST No. 04/BAST-SWK/PPK-PPS/2025 Hasil Pekerjaan Rakor PHTC Tahap 3'],
            ['E', 25, 'Dokumen pemeriksaan/verifikasi hasil pekerjaan', 'Ada', 'E. 3. Berita Acara Pemeriksaan Hasil Pekerjaan (BAPHP).docx', 'BAPHP Tim Pengawas Rakor Madrasah PHTC Tahap 3'],
            ['E', 26, 'Rekapitulasi realisasi anggaran dibandingkan dengan RAB', 'Ada', 'E. 4. Rekapitulasi Realisasi Anggaran Dibandingkan dengan RAB.docx', 'Rekapitulasi Realisasi 3 Akun Anggaran vs RAB/POK Rev 16 (Rp 87.955.000,-)'],
            ['E', 27, 'Bukti penyelesaian pembayaran dan perpajakan yang relevan', 'Ada', 'E. 5. Bukti Penyelesaian Pembayaran dan Perpajakan.docx', 'BA Penyelesaian Pembayaran 10 SP2D dan Pelunasan Seluruh Perpajakan'],
            ['E', 28, 'Dokumen pencatatan aset/persediaan jika keluaran menghasilkan aset/persediaan', 'Ada', 'E. 6. Dokumen Pencatatan Persediaan Barang Habis Pakai dan Aset.docx', 'BA Pencatatan Barang Persediaan Habis Pakai ATK dan Materi Kit Rakor'],
        ];

        foreach ($docs as $d) {
            $db->table('trn_swakelola_dokumen')->insert([
                'paket_id'     => $paketId,
                'tahap'        => $d[0],
                'nomor_urut'   => $d[1],
                'nama_dokumen' => $d[2],
                'status'       => $d[3],
                'file_name'    => $d[4],
                'file_path'    => 'public/uploads/swakelola/phtc3/' . $d[4],
                'keterangan'   => $d[5],
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }

        // Realisasi Belanja (10 Transaksi SPM/SP2D Asli = Rp 87.955.000,-)
        $realisasi = [
            ['521211', 'Belanja Bahan - Pembelian ATK Rakor Survey & Perencanaan Teknis Madrasah PHTC Tahap 3 Bulan November 2025 (CV. Sinar Cahaya Lestari)', '00417A', '2025-12-01', '259991311235906', 2990000.00, 'ATK BULAN NOVEMBER (SPM 00417A).pdf'],
            ['521211', 'Belanja Bahan - Pembelian Supplies Komputer Rakor Madrasah PHTC Tahap 3 Bulan November 2025 (CV. Sinar Cahaya Lestari)', '00416A', '2025-12-01', '259991311235907', 1460000.00, 'SUPLIES KOMPUTER NOVEMBER (SPM 00416A) OK.pdf'],
            ['521211', 'Belanja Bahan - Pengadaan Materi Kit Rakor Survey & Perencanaan Teknis Madrasah PHTC Tahap 3 (33 Peserta + Narasumber)', '00392A', '2025-11-20', '259991311210450', 12250000.00, 'MATERI KIT (SPM 00392A).pdf'],
            ['521211', 'Belanja Bahan - Pengadaan Spanduk / Backdrop Rakor Madrasah PHTC Tahap 3 Penggantian Uang Persediaan (GUP 02 Desember)', '00442A (GUP)', '2025-12-02', '259991110645120', 560000.00, 'GUP 02 DES (SPM 00442A) SPANDUK.pdf'],
            ['521211', 'Belanja Bahan - Cetak dan Penggandaan Laporan Hasil Pelaksanaan Kegiatan Rakor PHTC Tahap 3 (CV. Riau Grafika Mandiri)', '00462A', '2025-12-04', '259991311289654', 2000000.00, 'CETAK LAPORAN RAKOR PHTC 3 (SPM 00462 A).pdf'],
            ['521213', 'Belanja Honor Output Kegiatan - Honorarium Narasumber Rakor Teknis Madrasah Pejabat Eselon III (2 Jam Pelajaran @ Rp 900.000)', '00423A', '2025-12-01', '259991330441205', 1800000.00, 'HONORARIUM NARASUMBER (SPM  00423A).pdf'],
            ['521213', 'Belanja Honor Output Kegiatan - Honorarium Tim Penyelenggara Swakelola Rakor Madrasah PHTC Tahap 3 (7 Orang Sesuai SK No. 27/2025)', '00467A', '2025-12-04', '259991330462110', 2100000.00, 'HONOR TIM SWAKELOLA (SPM 00467A) OKE.pdf'],
            ['524114', 'Belanja Perjalanan Dinas Paket Meeting - Pembayaran Paket Fullboard Meeting Hotel Royal Asnof Pekanbaru (33 Peserta + Tim Satker)', '00446A', '2025-12-03', '259991330451850', 49000000.00, 'PAKET MEETING RAKOR (SPM 00446A) OK.pdf'],
            ['524114', 'Belanja Perjalanan Dinas Paket Meeting - Uang Harian Paket Meeting Dalam Kota Peserta Rakor Madrasah PHTC Tahap 3 (33 Orang)', '00425A', '2025-12-01', '259991330441208', 8580000.00, 'UANG HARIAN PESERTA (SPM 00425A).pdf'],
            ['524114', 'Belanja Perjalanan Dinas Paket Meeting - Biaya Transportasi Lokal Peserta Rakor Survey dan Perencanaan Teknis Madrasah PHTC Tahap 3 (33 Orang)', '00424A', '2025-12-01', '259991330441207', 7215000.00, 'UANG TRANSPORT PESERTA (SPM 00424A).pdf'],
        ];

        foreach ($realisasi as $r) {
            $db->table('trn_swakelola_realisasi')->insert([
                'paket_id'       => $paketId,
                'kode_akun'      => $r[0],
                'uraian_belanja' => $r[1],
                'nomor_spm'      => $r[2],
                'tanggal_spm'    => $r[3],
                'nomor_sp2d'     => $r[4],
                'jumlah_bayar'   => $r[5],
                'file_bukti'     => $r[6],
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }
    }

    private function seedInitialMadrasahRiau5Data(): void
    {
        $db = $this->db;
        $now = date('Y-m-d H:i:s');

        $existing = $db->table('trn_swakelola_paket')->where('kode_paket', '06.691285.GM.7717.RBI.003.900.G')->countAllResults();
        if ($existing > 0) {
            return;
        }

        $paketData = [
            'kode_paket'         => '06.691285.GM.7717.RBI.003.900.G',
            'nama_kegiatan'      => 'Administrasi Kegiatan Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 5',
            'tahun_anggaran'     => 2026,
            'pagu_anggaran'      => 109828000.00,
            'realisasi_anggaran' => 86533000.00,
            'tipe_swakelola'     => 'Tipe I',
            'ppk_nama'           => 'Nurhidayat Nugroho, S.Ars.',
            'ppk_nip'            => '19901221 201802 1 001',
            'kasatker_nama'      => 'Muhammad Yudi Prasetya, S.T.',
            'kasatker_nip'       => '19800214 201412 1 002',
            'lokasi'             => 'Kabupaten Bengkalis dan Kota Dumai, Provinsi Riau',
            'jangka_waktu'       => 'Januari s/d Juni 2026 (6 Bulan)',
            'status'             => 'Selesai',
            'created_at'         => $now,
            'updated_at'         => $now,
        ];

        $db->table('trn_swakelola_paket')->insert($paketData);
        $paketId = (int) $db->insertID();

        // 28 Checklist Documents
        $docs = [
            ['A', 1, 'DIPA dan Revisi Anggaran (POK)', 'Ada', 'A. 1. 16_2025_RINCIAN KERTAS KERJA SATKER PPS RIAU_Rev 16 POK.pdf', 'Rincian Kertas Kerja Satker (POK) Revisi 16 Tgl 23 Des 2025 & Petikan POK Madrasah PHTC Riau 5'],
            ['A', 2, 'RUP/SIRUP paket Swakelola', 'Ada', 'A. 2. Lembar RUP dan Pengumuman SiRUP Paket Swakelola Madrasah PHTC Riau 5.docx', 'Lembar Penetapan RUP & Pengumuman SiRUP LKPP Swakelola Madrasah PHTC Riau 5'],
            ['A', 3, 'KAK (Tujuan, Ruang Lingkup, Keluaran dan Spesifikasi)', 'Ada', 'KAK ADMINISTRASI PHTC PROVINSI RIAU 5 rev.pdf', 'Disahkan PPK dan Kasatker Administrasi Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 5'],
            ['A', 4, 'RAB (Perkiraan Biaya dan Dasar Perhitungan)', 'Ada', 'RAB ADMINISTRASI PHTC PROVINSI RIAU 5 ok.pdf', 'RAB Definitif Alokasi Rp 109.828.000,- (3 Akun Belanja POK Rev 16 / TA 2026)'],
            ['A', 5, 'Dokumen Identifikasi Kebutuhan dan Dasar Pemilihan Swakelola', 'Ada', 'A. 5. DOKUMEN IDENTIFIKASI KEBUTUHAN DAN DASAR PEMILIHAN SWAKELOLA.docx', 'Dokumen Identifikasi Kebutuhan dan Dasar Pemilihan Swakelola Tipe I'],
            ['B', 6, 'Surat Keputusan Penyelenggara Swakelola oleh PA/KPA', 'Ada', 'B. 1. SK Penetapan Penyelenggara Swakelola Madrasah PHTC Riau 5 oleh KPA.docx', 'SK Penetapan Penyelenggara Swakelola No. 18/KPTS/SATKER-PPS.RIAU/2026'],
            ['B', 7, 'Susunan Tim Persiapan, Tim Pelaksana dan Tim Pengawas beserta tugasnya', 'Ada', 'B. 2. Susunan Tim Persiapan, Pelaksana, Pengawas dan Uraian Tugas.docx', 'Lampiran SK Penetapan Tim Swakelola Madrasah PHTC Riau 5 & Uraian Tugas'],
            ['B', 8, 'Rencana Kegiatan dan Jadwal Pelaksanaan', 'Ada', 'B. 3. Rencana Kegiatan dan Jadwal Pelaksanaan Administrasi Madrasah PHTC Riau 5.docx', 'Matriks Jadwal Pelaksanaan Kurva Waktu Januari s.d. Juni 2026 (6 Bulan)'],
            ['B', 9, 'KAK final dan RAB rinci', 'Ada', 'B. 4. KAK Final dan RAB Rinci Administrasi Madrasah PHTC Riau 5.docx', 'Disahkan PPK dan Tim Persiapan Administrasi Madrasah PHTC Riau 5'],
            ['B', 10, 'Dokumen pembahasan atau hasil persiapan kegiatan', 'Ada', 'B. 5. BA Pembahasan Persiapan Swakelola Administrasi Madrasah PHTC Riau 5.docx', 'BA Pembahasan Persiapan Swakelola Madrasah PHTC Riau 5'],
            ['B', 11, 'Rencana kebutuhan tenaga kerja, bahan, peralatan dan tenaga ahli', 'Ada', 'B. 6. Rencana Kebutuhan Tenaga Kerja, Bahan, Peralatan, dan Tenaga Ahli.docx', 'Rencana Kebutuhan Tenaga Kerja, Bahan, Peralatan dan Tenaga Ahli Ade Putra, S.T.'],
            ['C', 12, 'Surat Perintah atau Dokumen Penugasan', 'Ada', 'Surat Perjanjian Kerja.pdf', 'Surat Perjanjian Kerja (SPK) Tenaga Ahli Ade Putra, S.T. No. 01/2026 & Surat Penugasan Tim'],
            ['C', 13, 'Daftar Hadir, Catatan Tenaga Kerja dan Bukti pelaksanaan Kegiatan', 'Ada', 'C. 2. Laporan Pelaksanaan, Daftar Hadir dan Catatan Tenaga Kerja.docx', 'Rekapitulasi Logbook Bulanan dan Presensi Tim Pelaksana Jan-Jun 2026'],
            ['C', 14, 'Laporan progres pekerjaan dan penggunaan anggaran secara berkala', 'Ada', 'C. 3. Laporan Progres Fisik dan Penggunaan Anggaran Berkala.docx', 'Laporan Progres Fisik dan Penggunaan Anggaran Berkala Jan s.d. Jun 2026'],
            ['C', 15, 'Bukti pembelian bahan/barang dari Penyedia', 'Ada', '86. GUP III MARET (SPM 00086A) atk phtc 5.pdf', 'Bukti Pembelian Bahan ATK PHTC 5 Penggantian Uang Persediaan GUP III Maret 2026 (SPM 00086A)'],
            ['C', 16, 'Invoice/Kuitansi, Bukti Pembayaran, dan Dokumen Pajak sesuai ketentuan', 'Ada', '1. GAJI JAN ADE (SPM 00048A).pdf', '5 Berkas SPM/Kuitansi/SSP Pajak Gaji Tenaga Teknis Ade Putra, S.T. Jan s.d. Mei 2026 (Rp 30.000.000,-)'],
            ['C', 17, 'Dokumentasi fisik kegiatan dan hasil pekerjaan', 'Ada', 'C. 6. Dokumentasi Fisik Kegiatan dan Hasil Pekerjaan Madrasah PHTC Riau 5.docx', 'Dokumentasi Foto Fisik Lapangan Rehabilitasi Madrasah di Bengkalis dan Dumai'],
            ['C', 18, 'Bukti pengeluaran yang sah dan dapat ditelusuri', 'Ada', '1. SPPD JANUARI BENGKALIS (SPM 00029A).pdf', '6 Berkas Lengkap SPPD, SPT, Nominatif & Bukti Riil Perjadin Bengkalis & Dumai (Rp 54.972.000,-)'],
            ['D', 19, 'Laporan atau Catatan Pengawasan Tim Pengawas', 'Ada', 'D. 1. Laporan Catatan Pengawasan Tim Pengawas Swakelola Madrasah PHTC Riau 5.docx', 'Laporan Berkala Hasil Audit dan Pengawasan Fisik/Keuangan Tim Was'],
            ['D', 20, 'Bukti Pemeriksaan kesesuaian volume, mutu, waktu dan keluaran', 'Ada', 'D. 2. Bukti Pemeriksaan Kesesuaian Volume, Mutu, Waktu dan Keluaran.docx', 'Lembar Checklist Verifikasi Kesesuaian Output dan Spesifikasi Teknis Lapangan'],
            ['D', 21, 'Rekonsiliasi realisasi fisik dan keuangan', 'Ada', 'D. 3. Berita Acara Rekonsiliasi Realisasi Fisik dan Keuangan.docx', 'BA Rekonsiliasi Fisik 100% & Realisasi Keuangan Rp 86.533.000,- (78,79%)'],
            ['D', 22, 'Catatan penyimpangan dan bukti tindak lanjut', 'Ada', 'D. 4. Catatan Penyimpangan Evaluasi dan Rekomendasi Efisiensi Anggaran.docx', 'Evaluasi Efisiensi Anggaran Penghematan Sisa RAB Rp 23.295.000,-'],
            ['E', 23, 'Laporan akhir pelaksanaan Swakelola', 'Ada', 'E. 1. Laporan Akhir Pelaksanaan Swakelola Administrasi Madrasah PHTC Riau 5.docx', 'Laporan Akhir Komprehensif Bab I s/d Bab IV Administrasi Madrasah PHTC Riau 5'],
            ['E', 24, 'BAST hasil pekerjaan dari Tim Pelaksana kepada PPK', 'Ada', 'E. 2. Berita Acara Serah Terima (BAST) Hasil Pekerjaan dari Tim Pelaksana kepada PPK.docx', 'BAST No. 05/BAST-SWK/PPK-PPS/2026 Hasil Pekerjaan Administrasi Madrasah PHTC Riau 5'],
            ['E', 25, 'Dokumen pemeriksaan/verifikasi hasil pekerjaan', 'Ada', 'E. 3. Berita Acara Pemeriksaan Hasil Pekerjaan (BAPHP).docx', 'BAPHP Tim Pengawas Swakelola Madrasah PHTC Riau 5'],
            ['E', 26, 'Rekapitulasi realisasi anggaran dibandingkan dengan RAB', 'Ada', 'E. 4. Rekapitulasi Realisasi Anggaran Dibandingkan dengan RAB.docx', 'Matriks Rekonsiliasi 3 Akun Belanja vs 12 SPM Realisasi Definitif (Rp 86.533.000,-)'],
            ['E', 27, 'Bukti penyelesaian pembayaran dan perpajakan yang relevan', 'Ada', 'E. 5. Bukti Penyelesaian Pembayaran dan Perpajakan.docx', 'BA Penyelesaian Pembayaran 12 SP2D dan Pelunasan Seluruh Kewajiban Perpajakan'],
            ['E', 28, 'Dokumen pencatatan aset/persediaan jika keluaran menghasilkan aset/persediaan', 'Ada', 'E. 6. Dokumen Pencatatan Persediaan Barang Habis Pakai dan Aset.docx', 'BA Pencatatan Barang Persediaan Habis Pakai ATK Madrasah PHTC 5'],
        ];

        foreach ($docs as $d) {
            $db->table('trn_swakelola_dokumen')->insert([
                'paket_id'     => $paketId,
                'tahap'        => $d[0],
                'nomor_urut'   => $d[1],
                'nama_dokumen' => $d[2],
                'status'       => $d[3],
                'file_name'    => $d[4],
                'file_path'    => 'public/uploads/swakelola/madrasah_riau5/' . $d[4],
                'keterangan'   => $d[5],
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }

        // Realisasi Belanja (12 Transaksi SPM/SP2D Asli = Rp 86.533.000,-)
        $realisasi = [
            ['521811', 'Belanja Bahan Persediaan Konsumsi - Belanja Bahan ATK PHTC 5 Penggantian Uang Persediaan GUP III Maret 2026', '00086A (GUP)', '2026-03-25', '269991110125890', 1561000.00, '86. GUP III MARET (SPM 00086A) atk phtc 5.pdf'],
            ['522191', 'Belanja Jasa Lainnya - Gaji Tenaga Teknis Pengawasan Ade Putra, S.T. Bulan Januari 2026 (SPK No. 01/2026)', '00048A', '2026-02-05', '269991320102145', 6000000.00, '1. GAJI JAN ADE (SPM 00048A).pdf'],
            ['522191', 'Belanja Jasa Lainnya - Gaji Tenaga Teknis Pengawasan Ade Putra, S.T. Bulan Februari 2026 (SPK No. 01/2026)', '00059A', '2026-03-02', '269991320125891', 6000000.00, '2. GAJI FEB ADE (SPM 00059A).pdf'],
            ['522191', 'Belanja Jasa Lainnya - Gaji Tenaga Teknis Pengawasan Ade Putra, S.T. Bulan Maret 2026 (SPK No. 01/2026)', '00112A', '2026-04-06', '269991320148902', 6000000.00, '3. GAJI MARET ADE (SPM 00112A).pdf'],
            ['522191', 'Belanja Jasa Lainnya - Gaji Tenaga Teknis Pengawasan Ade Putra, S.T. Bulan April 2026 (SPK No. 01/2026)', '00147A', '2026-05-04', '269991320172314', 6000000.00, '4. GAJI APRIL ADE (SPM 00147A).pdf'],
            ['522191', 'Belanja Jasa Lainnya - Gaji Tenaga Teknis Pengawasan Ade Putra, S.T. Bulan Mei 2026 (SPK No. 01/2026)', '00200A', '2026-06-03', '269991320198450', 6000000.00, '5. GAJI MEI ADE (SPM 00200A).pdf'],
            ['524111', 'Belanja Perjalanan Dinas Biasa - SPPD Monitoring Madrasah PHTC Kab. Bengkalis Tahap I Bulan Januari 2026', '00029A', '2026-01-20', '269991330089123', 8432000.00, '1. SPPD JANUARI BENGKALIS (SPM 00029A).pdf'],
            ['524111', 'Belanja Perjalanan Dinas Biasa - SPPD Monitoring Madrasah PHTC Kab. Bengkalis Tahap II Bulan Januari 2026', '00030A', '2026-01-22', '269991330089124', 6454000.00, '2. SPPD JANUARI BENGKALIS (SPM 00030A).pdf'],
            ['524111', 'Belanja Perjalanan Dinas Biasa - SPPD Monitoring Evaluasi Lapangan Madrasah PHTC Kab. Bengkalis Bulan Februari 2026', '00075A', '2026-03-12', '269991330112340', 11144000.00, '3. SPPD FEBRUARI BENGKALIS (SPM 00075A).pdf'],
            ['524111', 'Belanja Perjalanan Dinas Biasa - SPPD Monitoring Evaluasi Lapangan Madrasah PHTC Kab. Bengkalis Bulan Maret 2026', '00106A', '2026-04-02', '269991330134567', 10364000.00, '4. SPPD MARET BENGKALIS (SPM 00106A).pdf'],
            ['524111', 'Belanja Perjalanan Dinas Biasa - SPPD Monitoring Evaluasi Lapangan Madrasah PHTC Kab. Bengkalis Bulan April 2026', '00140A', '2026-05-02', '269991330167890', 10364000.00, '5. SPPD APRIL BENGKALIS (SPM 00140A).pdf'],
            ['524111', 'Belanja Perjalanan Dinas Biasa - SPPD Monev Lapangan Madrasah PHTC Kota Dumai & Bengkalis Bulan Juni 2026', '00226A', '2026-06-18', '269991330214560', 8214000.00, '6. SPPD DUMAI BENGKALIS JUNI (SPM 00226A).pdf'],
        ];

        foreach ($realisasi as $r) {
            $db->table('trn_swakelola_realisasi')->insert([
                'paket_id'       => $paketId,
                'kode_akun'      => $r[0],
                'uraian_belanja' => $r[1],
                'nomor_spm'      => $r[2],
                'tanggal_spm'    => $r[3],
                'nomor_sp2d'     => $r[4],
                'jumlah_bayar'   => $r[5],
                'file_bukti'     => $r[6],
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }
    }
}


