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

        // 5. Seed initial SPPG packet data
        $this->seedInitialSppgData();
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
            ['A', 1, 'DIPA dan Revisi Anggaran (POK)', 'Ada', 'A. 1. Petikan DIPA dan Revisi Anggaran POK SPPG TA 2025.docx', 'Petikan resmi POK Satker Pagu Rp 413.000.000,-'],
            ['A', 2, 'RUP/SIRUP paket Swakelola', 'Ada', 'A. 2. Lembar RUP dan Pengumuman SiRUP Paket Swakelola SPPG.docx', 'Format pengumuman SiRUP LKPP Swakelola'],
            ['A', 3, 'KAK (Tujuan, Ruang Lingkup, Keluaran dan Spesifikasi)', 'Ada', 'A. 3. KAK Administrasi SPPG Provinsi Riau.pdf', 'Disahkan PPK dan Kasatker'],
            ['A', 4, 'RAB (Perkiraan Biaya dan Dasar Perhitungan)', 'Ada', 'A. 4. RAB Administrasi SPPG Provinsi Riau.pdf', 'Pagu Rp 413.000.000,-'],
            ['A', 5, 'Dokumen Identifikasi Kebutuhan dan Dasar Pemilihan Swakelola', 'Ada', 'A. 5. DOKUMEN IDENTIFIKASI KEBUTUHAN DAN DASAR PEMILIHAN SWAKELOLA.docx', 'Dasar hukum pemilihan Swakelola Tipe I'],
            // Tahap B
            ['B', 6, 'Surat Keputusan Penyelenggara Swakelola oleh PA/KPA', 'Ada', 'B. 1. SK Penetapan Penyelenggara Swakelola SPPG oleh KPA.docx', 'SK KPA No. 28/KPTS/SATKER-PPS.RIAU/2025'],
            ['B', 7, 'Susunan Tim Persiapan, Tim Pelaksana dan Tim Pengawas beserta tugasnya', 'Ada', 'B. 2. Susunan Tim Persiapan, Pelaksana, Pengawas dan Uraian Tugas.docx', 'Lampiran SK rincian tugas tim'],
            ['B', 8, 'Rencana Kegiatan dan Jadwal Pelaksanaan', 'Ada', 'B. 3. Rencana Kegiatan dan Jadwal Pelaksanaan Administrasi SPPG.docx', 'Matriks jadwal pelaksanaan kurva waktu'],
            ['B', 9, 'KAK final dan RAB rinci', 'Ada', 'B. 4. a. KAK Final & B. 4. b. RAB Final Administrasi SPPG.docx', 'Disahkan PPK dan Tim Persiapan'],
            ['B', 10, 'Dokumen pembahasan atau hasil persiapan kegiatan', 'Ada', 'B. 5. BA Pembahasan Persiapan Swakelola Administrasi SPPG.docx', 'BA Pembahasan No. 01/BA-PERSIAPAN/2025'],
            ['B', 11, 'Rencana kebutuhan tenaga kerja, bahan, peralatan dan tenaga ahli', 'Ada', 'B. 6. Rencana Kebutuhan Tenaga Kerja, Bahan, Peralatan, dan Tenaga Ahli.docx', 'Rincian kebutuhan personil & bahan operasional'],
            // Tahap C
            ['C', 12, 'Surat Perintah atau Dokumen Penugasan', 'Ada', 'C. 1. Surat Perintah Penugasan Tim Pelaksana Swakelola.docx', 'Surat Perintah PPK No. 15/SPP-SWK/2025'],
            ['C', 13, 'Daftar Hadir, Catatan Tenaga Kerja dan Bukti pelaksanaan Kegiatan', 'Ada', 'C. 2. Laporan Pelaksanaan, Daftar Hadir dan Catatan Tenaga Kerja.docx', 'Rekapitulasi logbook & penugasan staf'],
            ['C', 14, 'Laporan progres pekerjaan dan penggunaan anggaran secara berkala', 'Ada', 'C. 3. Laporan Progres Fisik dan Penggunaan Anggaran Berkala.docx', 'Laporan progres bulanan Okt, Nov, Des 2025'],
            ['C', 15, 'Bukti pembelian bahan/barang dari Penyedia', 'Ada', 'C. 4. Bukti Pembelian Bahan dan Barang dari Penyedia (ATK SPM 00415A).pdf', 'Kuitansi & faktur belanja bahan'],
            ['C', 16, 'Invoice/Kuitansi, Bukti Pembayaran, dan Dokumen Pajak sesuai ketentuan', 'Ada', 'C. 5. Invoice Kuitansi dan Dokumen Pajak (Supplies SPM 00414A).pdf', 'SPM, SP2D, bukti setor pajak PPN/PPh'],
            ['C', 17, 'Dokumentasi fisik kegiatan dan hasil pekerjaan', 'Ada', 'C. 6. Dokumentasi Fisik Kegiatan dan Hasil Pekerjaan SPPG.docx', 'Album foto survei Kampar, Dumai, Siak, Jakarta'],
            ['C', 18, 'Bukti pengeluaran yang sah dan dapat ditelusuri', 'Ada', 'C. 7. Bukti Pengeluaran Sah yang Dapat Ditelusuri (SPPD SPM 00445A).pdf', 'SPPD & rincian biaya riil'],
            // Tahap D
            ['D', 19, 'Laporan atau Catatan Pengawasan Tim Pengawas', 'Ada', 'D. 1. Laporan Catatan Pengawasan Tim Pengawas Swakelola SPPG.docx', 'Laporan berkala hasil pengawasan Tim Was'],
            ['D', 20, 'Bukti Pemeriksaan kesesuaian volume, mutu, waktu dan keluaran', 'Ada', 'D. 2. Bukti Pemeriksaan Kesesuaian Volume, Mutu, Waktu dan Keluaran.docx', 'Lembar checklist pemeriksaan kesesuaian'],
            ['D', 21, 'Rekonsiliasi realisasi fisik dan keuangan', 'Ada', 'D. 3. Berita Acara Rekonsiliasi Realisasi Fisik dan Keuangan.docx', 'BA Rekon Fisik 100% & Keu Rp 141.159.100,-'],
            ['D', 22, 'Catatan penyimpangan dan bukti tindak lanjut', 'Ada', 'D. 4. Catatan Penyimpangan Evaluasi dan Bukti Tindak Lanjut.docx', 'Evaluasi efisiensi sisa pagu Rp 271,8 jt'],
            // Tahap E
            ['E', 23, 'Laporan akhir pelaksanaan Swakelola', 'Ada', 'E. 1. Laporan Akhir Pelaksanaan Swakelola Administrasi SPPG.docx', 'Laporan akhir komprehensif Bab I s/d Bab IV'],
            ['E', 24, 'BAST hasil pekerjaan dari Tim Pelaksana kepada PPK', 'Ada', 'E. 2. Berita Acara Serah Terima (BAST) Hasil Pekerjaan dari Tim Pelaksana kepada PPK.docx', 'BAST No. 01/BAST-SWK/PPK-PPS/2025'],
            ['E', 25, 'Dokumen pemeriksaan/verifikasi hasil pekerjaan', 'Ada', 'E. 3. Berita Acara Pemeriksaan Hasil Pekerjaan (BAPHP).docx', 'BAPHP Tim Pengawas sebelum BAST'],
            ['E', 26, 'Rekapitulasi realisasi anggaran dibandingkan dengan RAB', 'Ada', 'E. 4. Rekapitulasi Realisasi Anggaran Dibandingkan dengan RAB.docx', 'Matriks perbandingan RAB vs Realisasi'],
            ['E', 27, 'Bukti penyelesaian pembayaran dan perpajakan yang relevan', 'Ada', 'E. 5. Bukti Penyelesaian Pembayaran dan Perpajakan.docx', 'BA pelunasan SP2D dan pajak lunas'],
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

        // Realisasi Belanja
        $realisasi = [
            ['521211', 'Belanja Bahan - ATK Bulan November', '00415A', '2025-12-03', '259991330451774', 18264400.00, 'ATK BULAN NOVEMBER (SPM 00415A).pdf'],
            ['521211', 'Belanja Bahan - Supplies Komputer Bulan November', '00414A', '2025-12-03', '259991330451773', 18344100.00, 'SUPLIES KOMPUTER BULAN NOVEMBER (SPM 00414A) (1).pdf'],
            ['521211', 'Belanja Bahan - ATK SPPG Desember', '00498A', '2025-12-15', '259991330489012', 12500000.00, 'ATK SPPG DESEMBER (SPM 00498A) OK.pdf'],
            ['521211', 'Belanja Bahan - Supplies Komputer SPPG Desember', '00497A', '2025-12-15', '259991330489011', 11980000.00, 'SUPLIES KOMPUTER BULAN DESEMBER (SPM 00497A).pdf'],
            ['524111', 'Perjadin Biasa - Monitoring Kampar Oktober', '00445A', '2025-12-03', '259991330451846', 6240000.00, 'SPPD SPPG KAMPAR OKTOBER (SPM 00445A).pdf'],
            ['524111', 'Perjadin Biasa - Koordinasi Jakarta November', '00449A', '2025-12-03', '259991330451850', 18264400.00, 'SPPD JAKARTA NOVEMBER (SPM 00449A).pdf'],
            ['524111', 'Perjadin Biasa - Monitoring Dumai & Siak November', '00483A', '2025-12-10', '259991330472110', 21450000.00, 'SPPD DUMAI SIAK SPPG NOV (SPM 00483A).pdf'],
            ['524111', 'Perjadin Biasa - Koordinasi Jakarta November Tahap 2', '00485A', '2025-12-10', '259991330472115', 14500000.00, 'SPPD JAKARTA NOVEMBER (SPM 00485A).pdf'],
            ['524111', 'Perjadin Biasa - Monitoring Dumai Desember', '00489A', '2025-12-12', '259991330480100', 8200000.00, 'SPPD DUMAI DESEMBER (SPM 00489A).pdf'],
            ['524111', 'Perjadin Biasa - Koordinasi Penyelenggaraan Wilayah Medan', '00535A', '2025-12-18', '259991330495120', 4816200.00, 'SPPD MEDAN DESEMBER (SPM 00535A) OK.pdf'],
            ['524111', 'Perjadin Biasa - Monitoring SPPG Desember', '00536A', '2025-12-18', '259991330495125', 3800000.00, 'SPPD SPPG DESEMBER (SPM 00536A).pdf'],
            ['524113', 'Perjadin Dalam Kota - Tim PPK Lapangan November', '00450A', '2025-12-03', '259991330451775', 3000000.00, '1. SPPD NOVEMBER SPPG (SPM 00450A).pdf'],
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

