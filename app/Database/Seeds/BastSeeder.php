<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class BastSeeder extends Seeder
{
    /**
     * Seeder untuk 18 Dokumen Berita Acara Serah Terima (BAST)
     * Satuan Kerja Pelaksanaan Prasarana Strategis Riau
     * (9 Paket Fisik, 8 Paket Manajemen Konstruksi, 1 Paket Supervisi)
     * Sumber data: Dokumen Asli Kontrak Satker PPS Riau (do_not_upload/bast)
     */
    public function run()
    {
        $db = $this->db;

        if (! $db->tableExists('trn_bast')) {
            echo "Tabel trn_bast belum ada. Silakan jalankan migrasi terlebih dahulu: php spark migrate\n";
            return;
        }

        // 1. Cari data PPK aktif (Nurhidayat Nugroho)
        $ppkNama    = 'Nurhidayat Nugroho, S. Ars';
        $ppkNip     = '199012212018021001';
        $ppkJabatan = 'Pejabat Penandatangan Kontrak Pelaksanaan Prasarana Strategis, Satuan Kerja Pelaksanaan Prasarana Strategis Riau';
        $ppkSatker  = 'Satuan Kerja Pelaksanaan Prasarana Strategis Riau';
        $ppkAlamat  = 'Jl. Bakti Ruko Komplek Perumahan Mutiara Asri Garden, Kel. Sidomulyo Timur, Kec. Marpoyan Damai, Pekanbaru, Riau';
        $ppkId      = null;

        if ($db->tableExists('mst_pegawai')) {
            $pegawai = $db->table('mst_pegawai')
                ->groupStart()
                    ->like('nama', 'Nurhidayat')
                    ->orLike('nama', 'Nugroho')
                ->groupEnd()
                ->where('is_active', 1)
                ->get()
                ->getRowArray();

            if ($pegawai) {
                $ppkId   = (int) $pegawai['id'];
                $ppkNama = $pegawai['nama'];
                $ppkNip  = $pegawai['nip'];
            }
        }

        // 2. Cari Kop Surat aktif
        $kopId = null;
        if ($db->tableExists('kop_surat')) {
            $kop = $db->table('kop_surat')
                ->where('is_active', 1)
                ->orderBy('id', 'DESC')
                ->get()
                ->getRowArray();
            if ($kop) {
                $kopId = (int) $kop['id'];
            }
        }

        // 3. Daftar 18 Dokumen BAST Lengkap
        $items = [
            [
                'nomor_bast' => 'BAST/FSK-PHTC.01/Gs7/2026',
                'judul_bast' => 'BERITA ACARA SERAH TERIMA I',
                'jenis_pekerjaan' => 'PEKERJAAN KONSTRUKSI',
                'lingkup_jasa' => 'Fisik',
                'paket_id' => 1,
                'nama_paket' => 'Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 1',
                'tanggal_bast' => '2026-05-30',
                'kota_bast' => 'Pekanbaru',
                'penyedia_nama' => 'PT. NOVAL CIPTAFLORA',
                'penyedia_wakil' => 'Ngatimin Setyo Budi',
                'penyedia_jabatan' => 'Direktur Cabang Riau',
                'penyedia_alamat' => 'Jl. Pengayoman, Gg. Pengayoman No. 48 Pekanbaru, Riau',
                'dasar_pelaksanaan' => '["Kontrak/Surat Perjanjian Pekerjaan Konstruksi Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 1 Nomor HK.02.01/PPS-RIAU/FSK-PHTC.1/2025/01 tanggal 16 September 2025", "Surat Perintah Mulai Kerja (SPMK) Nomor HK.02.03/PPS-RIAU/SPMK/FSK-PHTC.1/2025/01 tanggal 17 September 2025", "Surat Penunjukan Penyedia Barang/Jasa (SPPBJ) Nomor 01/SPPBJ/FSKRIAU1/PPSRIAU/IX/2025 tanggal 03 September 2025"]',
                'rincian_hasil_pekerjaan' => '[{"no": "1", "uraian": "Pekerjaan Konstruksi Fisik Rehabilitasi & Renovasi Madrasah Terbangun Sesuai Kontrak", "jumlah": "1 Paket", "keterangan": "Kondisi Fisik 100% Selesai"}, {"no": "2", "uraian": "Gambar Terlaksana (As-Built Drawings)", "jumlah": "3 Set", "keterangan": "Hardcopy & softcopy"}, {"no": "3", "uraian": "Laporan Akhir Pelaksanaan Fisik & Back Up Data", "jumlah": "3 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "4", "uraian": "Dokumentasi Visual / Foto Pelaksanaan 0%, 50%, 100%", "jumlah": "3 Album", "keterangan": "Hardcopy & softcopy"}, {"no": "5", "uraian": "Manual Pemeliharaan Bangunan Gedung", "jumlah": "3 Buku", "keterangan": "Hardcopy & softcopy"}]',
                'kesesuaian_pekerjaan' => 'telah sesuai',
                'persentase_pembayaran' => '100%',
                'created_by' => 'seeder',
            ],
            [
                'nomor_bast' => 'BAST/FSK-PHTC.02/Gs7/2026',
                'judul_bast' => 'BERITA ACARA SERAH TERIMA I',
                'jenis_pekerjaan' => 'PEKERJAAN KONSTRUKSI',
                'lingkup_jasa' => 'Fisik',
                'paket_id' => 2,
                'nama_paket' => 'Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 2',
                'tanggal_bast' => '2026-05-30',
                'kota_bast' => 'Pekanbaru',
                'penyedia_nama' => 'PT. Bumi Palapa Perkasa - CV. Mitra Kulim Mandiri, KSO',
                'penyedia_wakil' => 'Agus Supriyono',
                'penyedia_jabatan' => 'Direktur Utama',
                'penyedia_alamat' => 'Jalan Hangtuah Ujung Perum BMP III K.64 RT. 001 RW. 009, Sialangsakti, Tenayan Raya, Kota Pekanbaru, Riau',
                'dasar_pelaksanaan' => '["Kontrak/Surat Perjanjian Pekerjaan Konstruksi Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 2 Nomor HK.02.01/PPS-RIAU/FSK-PHTC.2/2025/01 tanggal 18 Desember 2025", "Surat Perintah Mulai Kerja (SPMK) Nomor HK.02.03/PPS-RIAU/SPMK/FSK-PHTC.2/2025/01 tanggal 19 Desember 2025", "Surat Penunjukan Penyedia Barang/Jasa (SPPBJ) Nomor PB.02.01/SATKER//PPS-RIAU/Gs7/2025/207 tanggal 05 Desember 2025"]',
                'rincian_hasil_pekerjaan' => '[{"no": "1", "uraian": "Pekerjaan Konstruksi Fisik Rehabilitasi & Renovasi Madrasah Terbangun Sesuai Kontrak", "jumlah": "1 Paket", "keterangan": "Kondisi Fisik 100% Selesai"}, {"no": "2", "uraian": "Gambar Terlaksana (As-Built Drawings)", "jumlah": "3 Set", "keterangan": "Hardcopy & softcopy"}, {"no": "3", "uraian": "Laporan Akhir Pelaksanaan Fisik & Back Up Data", "jumlah": "3 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "4", "uraian": "Dokumentasi Visual / Foto Pelaksanaan 0%, 50%, 100%", "jumlah": "3 Album", "keterangan": "Hardcopy & softcopy"}, {"no": "5", "uraian": "Manual Pemeliharaan Bangunan Gedung", "jumlah": "3 Buku", "keterangan": "Hardcopy & softcopy"}]',
                'kesesuaian_pekerjaan' => 'telah sesuai',
                'persentase_pembayaran' => '100%',
                'created_by' => 'seeder',
            ],
            [
                'nomor_bast' => 'BAST/FSK-PHTC.03/Gs7/2026',
                'judul_bast' => 'BERITA ACARA SERAH TERIMA I',
                'jenis_pekerjaan' => 'PEKERJAAN KONSTRUKSI',
                'lingkup_jasa' => 'Fisik',
                'paket_id' => 3,
                'nama_paket' => 'Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 3',
                'tanggal_bast' => '2026-05-30',
                'kota_bast' => 'Pekanbaru',
                'penyedia_nama' => 'PT. Jolundra Putra - CV. Mitra Kulim Mandiri, KSO',
                'penyedia_wakil' => 'Ponintan Purba',
                'penyedia_jabatan' => 'Direktur',
                'penyedia_alamat' => 'Rukan Taman Pondok Kelapa Blok F3 Jl. Raya Pondok Kelapa Duren Sawit Jakarta Timur, DKI Jakarta',
                'dasar_pelaksanaan' => '["Kontrak/Surat Perjanjian Pekerjaan Konstruksi Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 3 Nomor HK.02.01/PPS-RIAU/FSK-PHTC.3/2025/01 tanggal 01 Desember 2025", "Surat Perintah Mulai Kerja (SPMK) Nomor HK.02.03/PPS-RIAU/SPMK/FSK-PHTC.3/2025/01 tanggal 02 Desember 2025", "Surat Penunjukan Penyedia Barang/Jasa (SPPBJ) Nomor PB.02.01/SATKER//PPS-RIAU/Gs7/2025/159 tanggal 12 November 2025"]',
                'rincian_hasil_pekerjaan' => '[{"no": "1", "uraian": "Pekerjaan Konstruksi Fisik Rehabilitasi & Renovasi Madrasah Terbangun Sesuai Kontrak", "jumlah": "1 Paket", "keterangan": "Kondisi Fisik 100% Selesai"}, {"no": "2", "uraian": "Gambar Terlaksana (As-Built Drawings)", "jumlah": "3 Set", "keterangan": "Hardcopy & softcopy"}, {"no": "3", "uraian": "Laporan Akhir Pelaksanaan Fisik & Back Up Data", "jumlah": "3 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "4", "uraian": "Dokumentasi Visual / Foto Pelaksanaan 0%, 50%, 100%", "jumlah": "3 Album", "keterangan": "Hardcopy & softcopy"}, {"no": "5", "uraian": "Manual Pemeliharaan Bangunan Gedung", "jumlah": "3 Buku", "keterangan": "Hardcopy & softcopy"}]',
                'kesesuaian_pekerjaan' => 'telah sesuai',
                'persentase_pembayaran' => '100%',
                'created_by' => 'seeder',
            ],
            [
                'nomor_bast' => 'BAST/FSK-PHTC.04/Gs7/2026',
                'judul_bast' => 'BERITA ACARA SERAH TERIMA I',
                'jenis_pekerjaan' => 'PEKERJAAN KONSTRUKSI',
                'lingkup_jasa' => 'Fisik',
                'paket_id' => 4,
                'nama_paket' => 'Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 4',
                'tanggal_bast' => '2026-05-30',
                'kota_bast' => 'Pekanbaru',
                'penyedia_nama' => 'PT. Toleransi Aceh - PT. Bripona Jaya Abadi (KSO)',
                'penyedia_wakil' => 'Ahmad Baharuddin',
                'penyedia_jabatan' => 'Direktur',
                'penyedia_alamat' => 'Jl. Tgk Glee Iniem No. 24 A Tungkop Darussalam Aceh Besar - Aceh Besar',
                'dasar_pelaksanaan' => '["Kontrak/Surat Perjanjian Pekerjaan Konstruksi Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 4 Nomor HK.02.01/PPS-RIAU/FSK-PHTC.4/2025/01 tanggal 01 Desember 2025", "Surat Perintah Mulai Kerja (SPMK) Nomor HK.02.03/PPS-RIAU/SPMK/FSK-PHTC.4/2025/01 tanggal 02 Desember 2025", "Surat Penunjukan Penyedia Barang/Jasa (SPPBJ) Nomor PB.02.01/SATKER//PPS-RIAU/Gs7/2025/159 tanggal 12 November 2025"]',
                'rincian_hasil_pekerjaan' => '[{"no": "1", "uraian": "Pekerjaan Konstruksi Fisik Rehabilitasi & Renovasi Madrasah Terbangun Sesuai Kontrak", "jumlah": "1 Paket", "keterangan": "Kondisi Fisik 100% Selesai"}, {"no": "2", "uraian": "Gambar Terlaksana (As-Built Drawings)", "jumlah": "3 Set", "keterangan": "Hardcopy & softcopy"}, {"no": "3", "uraian": "Laporan Akhir Pelaksanaan Fisik & Back Up Data", "jumlah": "3 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "4", "uraian": "Dokumentasi Visual / Foto Pelaksanaan 0%, 50%, 100%", "jumlah": "3 Album", "keterangan": "Hardcopy & softcopy"}, {"no": "5", "uraian": "Manual Pemeliharaan Bangunan Gedung", "jumlah": "3 Buku", "keterangan": "Hardcopy & softcopy"}]',
                'kesesuaian_pekerjaan' => 'telah sesuai',
                'persentase_pembayaran' => '100%',
                'created_by' => 'seeder',
            ],
            [
                'nomor_bast' => 'BAST/FSK-PHTC.05/Gs7/2026',
                'judul_bast' => 'BERITA ACARA SERAH TERIMA I',
                'jenis_pekerjaan' => 'PEKERJAAN KONSTRUKSI',
                'lingkup_jasa' => 'Fisik',
                'paket_id' => 5,
                'nama_paket' => 'Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 5',
                'tanggal_bast' => '2026-05-30',
                'kota_bast' => 'Pekanbaru',
                'penyedia_nama' => 'PT. Polada Mutiara - Kota Raja, KSO',
                'penyedia_wakil' => 'Gufron Agung Wijaya',
                'penyedia_jabatan' => 'Direktur',
                'penyedia_alamat' => 'Komplek Peurada Utama Indah Lorong III No. 16 Kel/Desa Lamgugop, Kec. Syiah Kuala, Kota Banda Aceh',
                'dasar_pelaksanaan' => '["Kontrak/Surat Perjanjian Pekerjaan Konstruksi Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 5 Nomor HK.02.01/PPS-RIAU/FSK-PHTC.5/2025/01 tanggal 02 Desember 2025", "Surat Perintah Mulai Kerja (SPMK) Nomor HK.02.03/PPS-RIAU/SPMK/FSK-PHTC.5/2025/01 tanggal 03 Desember 2025", "Surat Penunjukan Penyedia Barang/Jasa (SPPBJ) Nomor PB.02.01/SATKER//PPS-RIAU/Gs7/2025/161 tanggal 12 November 2025"]',
                'rincian_hasil_pekerjaan' => '[{"no": "1", "uraian": "Pekerjaan Konstruksi Fisik Rehabilitasi & Renovasi Madrasah Terbangun Sesuai Kontrak", "jumlah": "1 Paket", "keterangan": "Kondisi Fisik 100% Selesai"}, {"no": "2", "uraian": "Gambar Terlaksana (As-Built Drawings)", "jumlah": "3 Set", "keterangan": "Hardcopy & softcopy"}, {"no": "3", "uraian": "Laporan Akhir Pelaksanaan Fisik & Back Up Data", "jumlah": "3 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "4", "uraian": "Dokumentasi Visual / Foto Pelaksanaan 0%, 50%, 100%", "jumlah": "3 Album", "keterangan": "Hardcopy & softcopy"}, {"no": "5", "uraian": "Manual Pemeliharaan Bangunan Gedung", "jumlah": "3 Buku", "keterangan": "Hardcopy & softcopy"}]',
                'kesesuaian_pekerjaan' => 'telah sesuai',
                'persentase_pembayaran' => '100%',
                'created_by' => 'seeder',
            ],
            [
                'nomor_bast' => 'BAST/FSK-PHTC.06/Gs7/2026',
                'judul_bast' => 'BERITA ACARA SERAH TERIMA I',
                'jenis_pekerjaan' => 'PEKERJAAN KONSTRUKSI',
                'lingkup_jasa' => 'Fisik',
                'paket_id' => 6,
                'nama_paket' => 'Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 6',
                'tanggal_bast' => '2026-05-30',
                'kota_bast' => 'Pekanbaru',
                'penyedia_nama' => 'PT. Karya Inti Bumi Konstruksi',
                'penyedia_wakil' => 'Muhammad Isra\'',
                'penyedia_jabatan' => 'Direktur',
                'penyedia_alamat' => 'Jln. T. Iskandar No. 15, Desa/Kelurahan Lamglumpang, Kec. Ulee Kareng, Kota Banda Aceh, Provinsi Aceh',
                'dasar_pelaksanaan' => '["Kontrak/Surat Perjanjian Pekerjaan Konstruksi Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 6 Nomor HK.02.01/PPS-RIAU/FSK-PHTC.6/2025/01 tanggal 01 Desember 2025", "Surat Perintah Mulai Kerja (SPMK) Nomor HK.02.03/PPS-RIAU/SPMK/FSK-PHTC.6/2025/01 tanggal 02 Desember 2025", "Surat Penunjukan Penyedia Barang/Jasa (SPPBJ) Nomor PB.02.01/SATKER//PPS-RIAU/Gs7/2025/162 tanggal 12 November 2025"]',
                'rincian_hasil_pekerjaan' => '[{"no": "1", "uraian": "Pekerjaan Konstruksi Fisik Rehabilitasi & Renovasi Madrasah Terbangun Sesuai Kontrak", "jumlah": "1 Paket", "keterangan": "Kondisi Fisik 100% Selesai"}, {"no": "2", "uraian": "Gambar Terlaksana (As-Built Drawings)", "jumlah": "3 Set", "keterangan": "Hardcopy & softcopy"}, {"no": "3", "uraian": "Laporan Akhir Pelaksanaan Fisik & Back Up Data", "jumlah": "3 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "4", "uraian": "Dokumentasi Visual / Foto Pelaksanaan 0%, 50%, 100%", "jumlah": "3 Album", "keterangan": "Hardcopy & softcopy"}, {"no": "5", "uraian": "Manual Pemeliharaan Bangunan Gedung", "jumlah": "3 Buku", "keterangan": "Hardcopy & softcopy"}]',
                'kesesuaian_pekerjaan' => 'telah sesuai',
                'persentase_pembayaran' => '100%',
                'created_by' => 'seeder',
            ],
            [
                'nomor_bast' => 'BAST/FSK-PHTC.07/Gs7/2026',
                'judul_bast' => 'BERITA ACARA SERAH TERIMA I',
                'jenis_pekerjaan' => 'PEKERJAAN KONSTRUKSI',
                'lingkup_jasa' => 'Fisik',
                'paket_id' => 7,
                'nama_paket' => 'Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 7',
                'tanggal_bast' => '2026-05-30',
                'kota_bast' => 'Pekanbaru',
                'penyedia_nama' => 'PT. Murda Jaya Abadi',
                'penyedia_wakil' => 'Murda Julisman',
                'penyedia_jabatan' => 'Direktur',
                'penyedia_alamat' => 'Jl. Cipta Karya, Perumahan Griya Cipta Blok C 2, RT 006, RW 001, Kelurahan Tuah Karya, Kecamatan Tampan, Pekanbaru',
                'dasar_pelaksanaan' => '["Kontrak/Surat Perjanjian Pekerjaan Konstruksi Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 7 Nomor HK.02.01/PPS-RIAU/FSK-PHTC.7/2025/01 tanggal 28 November 2025", "Surat Perintah Mulai Kerja (SPMK) Nomor HK.02.03/PPS-RIAU/SPMK/FSK-PHTC.7/2025/01 tanggal 01 Desember 2025", "Surat Penunjukan Penyedia Barang/Jasa (SPPBJ) Nomor PB.02.01/SATKER//PPS-RIAU/Gs7/2025/163 tanggal 12 November 2025"]',
                'rincian_hasil_pekerjaan' => '[{"no": "1", "uraian": "Pekerjaan Konstruksi Fisik Rehabilitasi & Renovasi Madrasah Terbangun Sesuai Kontrak", "jumlah": "1 Paket", "keterangan": "Kondisi Fisik 100% Selesai"}, {"no": "2", "uraian": "Gambar Terlaksana (As-Built Drawings)", "jumlah": "3 Set", "keterangan": "Hardcopy & softcopy"}, {"no": "3", "uraian": "Laporan Akhir Pelaksanaan Fisik & Back Up Data", "jumlah": "3 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "4", "uraian": "Dokumentasi Visual / Foto Pelaksanaan 0%, 50%, 100%", "jumlah": "3 Album", "keterangan": "Hardcopy & softcopy"}, {"no": "5", "uraian": "Manual Pemeliharaan Bangunan Gedung", "jumlah": "3 Buku", "keterangan": "Hardcopy & softcopy"}]',
                'kesesuaian_pekerjaan' => 'telah sesuai',
                'persentase_pembayaran' => '100%',
                'created_by' => 'seeder',
            ],
            [
                'nomor_bast' => 'BAST/FSK-PHTC.08/Gs7/2026',
                'judul_bast' => 'BERITA ACARA SERAH TERIMA I',
                'jenis_pekerjaan' => 'PEKERJAAN KONSTRUKSI',
                'lingkup_jasa' => 'Fisik',
                'paket_id' => 8,
                'nama_paket' => 'Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 8',
                'tanggal_bast' => '2026-05-30',
                'kota_bast' => 'Pekanbaru',
                'penyedia_nama' => 'PT. Alam Surya - PT. Kota Raja, KSO',
                'penyedia_wakil' => 'Ismail',
                'penyedia_jabatan' => 'Direktur',
                'penyedia_alamat' => 'Komplek Villa Cendana Mas Blok H No. 26 Kel. Tangkerang Tengah, Tenayan Raya, Kota Pekanbaru, Riau',
                'dasar_pelaksanaan' => '["Kontrak/Surat Perjanjian Pekerjaan Konstruksi Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 8 Nomor HK.02.01/PPS-RIAU/FSK-PHTC.8/2025/01 tanggal 28 November 2025", "Surat Perintah Mulai Kerja (SPMK) Nomor HK.02.03/PPS-RIAU/SPMK/FSK-PHTC.8/2025/01 tanggal 01 Desember 2025", "Surat Penunjukan Penyedia Barang/Jasa (SPPBJ) Nomor PB.02.01/SATKER//PPS-RIAU/Gs7/2025/164 tanggal 12 November 2025"]',
                'rincian_hasil_pekerjaan' => '[{"no": "1", "uraian": "Pekerjaan Konstruksi Fisik Rehabilitasi & Renovasi Madrasah Terbangun Sesuai Kontrak", "jumlah": "1 Paket", "keterangan": "Kondisi Fisik 100% Selesai"}, {"no": "2", "uraian": "Gambar Terlaksana (As-Built Drawings)", "jumlah": "3 Set", "keterangan": "Hardcopy & softcopy"}, {"no": "3", "uraian": "Laporan Akhir Pelaksanaan Fisik & Back Up Data", "jumlah": "3 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "4", "uraian": "Dokumentasi Visual / Foto Pelaksanaan 0%, 50%, 100%", "jumlah": "3 Album", "keterangan": "Hardcopy & softcopy"}, {"no": "5", "uraian": "Manual Pemeliharaan Bangunan Gedung", "jumlah": "3 Buku", "keterangan": "Hardcopy & softcopy"}]',
                'kesesuaian_pekerjaan' => 'telah sesuai',
                'persentase_pembayaran' => '100%',
                'created_by' => 'seeder',
            ],
            [
                'nomor_bast' => 'BAST/FSK-PHTC.09/Gs7/2026',
                'judul_bast' => 'BERITA ACARA SERAH TERIMA I',
                'jenis_pekerjaan' => 'PEKERJAAN KONSTRUKSI',
                'lingkup_jasa' => 'Fisik',
                'paket_id' => 9,
                'nama_paket' => 'Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 9',
                'tanggal_bast' => '2026-05-30',
                'kota_bast' => 'Pekanbaru',
                'penyedia_nama' => 'PT. Zhafira Tetap Jaya',
                'penyedia_wakil' => 'Satya Anugrah Akbar',
                'penyedia_jabatan' => 'Direktur Utama',
                'penyedia_alamat' => 'Jl. STM / Pembangunan No. 12/18 Medan, Kota Medan, Sumatera Utara',
                'dasar_pelaksanaan' => '["Kontrak/Surat Perjanjian Pekerjaan Konstruksi Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 9 Nomor HK.02.01/PPS-RIAU/FSK-PHTC.9/2025/01 tanggal 28 November 2025", "Surat Perintah Mulai Kerja (SPMK) Nomor HK.02.03/PPS-RIAU/SPMK/FSK-PHTC.9/2025/01 tanggal 01 Desember 2025", "Surat Penunjukan Penyedia Barang/Jasa (SPPBJ) Nomor PB.02.01/SATKER//PPS-RIAU/Gs7/2025/165 tanggal 12 November 2025"]',
                'rincian_hasil_pekerjaan' => '[{"no": "1", "uraian": "Pekerjaan Konstruksi Fisik Rehabilitasi & Renovasi Madrasah Terbangun Sesuai Kontrak", "jumlah": "1 Paket", "keterangan": "Kondisi Fisik 100% Selesai"}, {"no": "2", "uraian": "Gambar Terlaksana (As-Built Drawings)", "jumlah": "3 Set", "keterangan": "Hardcopy & softcopy"}, {"no": "3", "uraian": "Laporan Akhir Pelaksanaan Fisik & Back Up Data", "jumlah": "3 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "4", "uraian": "Dokumentasi Visual / Foto Pelaksanaan 0%, 50%, 100%", "jumlah": "3 Album", "keterangan": "Hardcopy & softcopy"}, {"no": "5", "uraian": "Manual Pemeliharaan Bangunan Gedung", "jumlah": "3 Buku", "keterangan": "Hardcopy & softcopy"}]',
                'kesesuaian_pekerjaan' => 'telah sesuai',
                'persentase_pembayaran' => '100%',
                'created_by' => 'seeder',
            ],
            [
                'nomor_bast' => 'BAST/SPV-PHTC.01/Gs7/2026',
                'judul_bast' => 'BERITA ACARA SERAH TERIMA I',
                'jenis_pekerjaan' => 'PEKERJAAN JASA KONSULTANSI KONSTRUKSI',
                'lingkup_jasa' => 'Supervisi',
                'paket_id' => 1,
                'nama_paket' => 'Supervisi Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 1',
                'tanggal_bast' => '2026-05-30',
                'kota_bast' => 'Pekanbaru',
                'penyedia_nama' => 'CV. Citratama Arsitek',
                'penyedia_wakil' => 'Enercy Ichlas',
                'penyedia_jabatan' => 'Direktur Utama',
                'penyedia_alamat' => 'Jl. Merak Sakti Blok E-6, Kel. Simpang Baru, Kec. Binawidya Kota Pekanbaru, Provinsi Riau',
                'dasar_pelaksanaan' => '["Kontrak/Surat Perjanjian Pekerjaan Jasa Konsultansi Supervisi Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 1 Nomor HK.02.01/PPS-RIAU/SPV-PHTC.1/2025/01 tanggal 16 September 2025", "Surat Perintah Mulai Kerja (SPMK) Nomor HK.02.03/PPS-RIAU/SPMK/SPV-PHTC.1/2025/01 tanggal 17 September 2025", "Surat Penunjukan Penyedia Barang/Jasa (SPPBJ) Nomor 01/SPPBJ/SPVRIAU1/PPSRIAU/IX/2025 tanggal 02 September 2025"]',
                'rincian_hasil_pekerjaan' => '[{"no": "1", "uraian": "Laporan Mingguan", "jumlah": "36 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "2", "uraian": "Laporan Bulanan", "jumlah": "9 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "3", "uraian": "Laporan Akhir Pengawasan Teknis", "jumlah": "1 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "4", "uraian": "Laporan Khusus (Profil Kegiatan)", "jumlah": "1 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "5", "uraian": "SSD Eksternal", "jumlah": "1 Tb", "keterangan": "Barang"}]',
                'kesesuaian_pekerjaan' => 'telah sesuai',
                'persentase_pembayaran' => '100%',
                'created_by' => 'seeder',
            ],
            [
                'nomor_bast' => 'BAST/MK-PHTC.02/Gs7/2026',
                'judul_bast' => 'BERITA ACARA SERAH TERIMA I',
                'jenis_pekerjaan' => 'PEKERJAAN JASA KONSULTANSI KONSTRUKSI',
                'lingkup_jasa' => 'Manajemen Konstruksi',
                'paket_id' => 2,
                'nama_paket' => 'Manajemen Konstruksi Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 2',
                'tanggal_bast' => '2026-05-30',
                'kota_bast' => 'Pekanbaru',
                'penyedia_nama' => 'CV. Bintang Sembilan Konsultan KSO CV. Althis Konsultan dan PT. Perencana Jaya Indonesia',
                'penyedia_wakil' => 'Syaifudin Nur Majid',
                'penyedia_jabatan' => 'Direktur Utama',
                'penyedia_alamat' => 'DS. Tukum RT. 024 RW. 008, Tekung, Tekung, Kab. Lumajang, Jawa Timur',
                'dasar_pelaksanaan' => '["Kontrak/Surat Perjanjian Pekerjaan Jasa Konsultansi Manajemen Konstruksi Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 2 Nomor HK.02.01/PPS-RIAU/MK-PHTC.2/2025/01 tanggal 18 Desember 2025", "Surat Perintah Mulai Kerja (SPMK) Nomor HK.02.03/PPS-RIAU/SPMK/MK-PHTC.2/2025/01 tanggal 19 Desember 2025", "Surat Penunjukan Penyedia Barang/Jasa (SPPBJ) Nomor PB.02.01/SATKER//PPS-RIAU/Gs7/2025/206 tanggal 05 Desember 2025"]',
                'rincian_hasil_pekerjaan' => '[{"no": "1", "uraian": "Laporan Mingguan", "jumlah": "48 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "2", "uraian": "Laporan Bulanan", "jumlah": "12 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "3", "uraian": "Laporan Akhir Pengawasan Teknis", "jumlah": "1 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "4", "uraian": "Laporan Khusus (Profil Kegiatan)", "jumlah": "1 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "5", "uraian": "Laporan Khusus (Dokumen Permohonan SLF)", "jumlah": "1 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "6", "uraian": "SSD Eksternal", "jumlah": "1 Tb", "keterangan": "Barang"}]',
                'kesesuaian_pekerjaan' => 'telah sesuai',
                'persentase_pembayaran' => '100%',
                'created_by' => 'seeder',
            ],
            [
                'nomor_bast' => 'BAST/MK-PHTC.03/Gs7/2026',
                'judul_bast' => 'BERITA ACARA SERAH TERIMA I',
                'jenis_pekerjaan' => 'PEKERJAAN JASA KONSULTANSI KONSTRUKSI',
                'lingkup_jasa' => 'Manajemen Konstruksi',
                'paket_id' => 3,
                'nama_paket' => 'Manajemen Konstruksi Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 3',
                'tanggal_bast' => '2026-05-30',
                'kota_bast' => 'Pekanbaru',
                'penyedia_nama' => 'PT. Pribia Jaya Persada KSO PT. Astadipati Biro Insinjur dan Arsitek',
                'penyedia_wakil' => 'Ir. Dwi Oktarini, S.T, M.M',
                'penyedia_jabatan' => 'Direktur Utama',
                'penyedia_alamat' => 'Jl. Batang Hari No. 53A RT. 012 RW. 003, Nusa Indah, Ratu Agung, Kota Bengkulu, Bengkulu',
                'dasar_pelaksanaan' => '["Kontrak/Surat Perjanjian Pekerjaan Jasa Konsultansi Manajemen Konstruksi Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 3 Nomor HK.02.01/PPS-RIAU/MK-PHTC.3/2025/01 tanggal 01 Desember 2025", "Surat Perintah Mulai Kerja (SPMK) Nomor HK.02.03/PPS-RIAU/SPMK/MK-PHTC.3/2025/01 tanggal 02 Desember 2025", "Surat Penunjukan Penyedia Barang/Jasa (SPPBJ) Nomor PB.02.01/SATKER//PPS-RIAU/Gs7/2025/166 tanggal 12 November 2025"]',
                'rincian_hasil_pekerjaan' => '[{"no": "1", "uraian": "Laporan Mingguan", "jumlah": "48 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "2", "uraian": "Laporan Bulanan", "jumlah": "12 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "3", "uraian": "Laporan Akhir Pengawasan Teknis", "jumlah": "1 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "4", "uraian": "Laporan Khusus (Profil Kegiatan)", "jumlah": "1 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "5", "uraian": "Laporan Khusus (Dokumen Permohonan SLF)", "jumlah": "1 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "6", "uraian": "SSD Eksternal", "jumlah": "1 Tb", "keterangan": "Barang"}]',
                'kesesuaian_pekerjaan' => 'telah sesuai',
                'persentase_pembayaran' => '100%',
                'created_by' => 'seeder',
            ],
            [
                'nomor_bast' => 'BAST/MK-PHTC.04/Gs7/2026',
                'judul_bast' => 'BERITA ACARA SERAH TERIMA I',
                'jenis_pekerjaan' => 'PEKERJAAN JASA KONSULTANSI KONSTRUKSI',
                'lingkup_jasa' => 'Manajemen Konstruksi',
                'paket_id' => 4,
                'nama_paket' => 'Manajemen Konstruksi Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 4',
                'tanggal_bast' => '2026-05-30',
                'kota_bast' => 'Pekanbaru',
                'penyedia_nama' => 'PT. Astadipati Duta H KSO PT. SCE',
                'penyedia_wakil' => 'Ir. Eri Budi Purnomo, ST',
                'penyedia_jabatan' => 'Direktur Utama',
                'penyedia_alamat' => 'Jl. Rajawali No. 181 Kel. Cempaka Permai Kec. Gading Cempaka, Kota Bengkulu, Bengkulu',
                'dasar_pelaksanaan' => '["Kontrak/Surat Perjanjian Pekerjaan Jasa Konsultansi Manajemen Konstruksi Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 4 Nomor HK.02.01/PPS-RIAU/MK-PHTC.4/2025/01 tanggal 28 November 2025", "Surat Perintah Mulai Kerja (SPMK) Nomor HK.02.03/PPS-RIAU/SPMK/MK-PHTC.4/2025/01 tanggal 01 Desember 2025", "Surat Penunjukan Penyedia Barang/Jasa (SPPBJ) Nomor PB.02.01/SATKER//PPS-RIAU/Gs7/2025/167 tanggal 12 November 2025"]',
                'rincian_hasil_pekerjaan' => '[{"no": "1", "uraian": "Laporan Mingguan", "jumlah": "48 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "2", "uraian": "Laporan Bulanan", "jumlah": "12 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "3", "uraian": "Laporan Akhir Pengawasan Teknis", "jumlah": "1 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "4", "uraian": "Laporan Khusus (Profil Kegiatan)", "jumlah": "1 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "5", "uraian": "Laporan Khusus (Dokumen Permohonan SLF)", "jumlah": "1 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "6", "uraian": "SSD Eksternal", "jumlah": "1 Tb", "keterangan": "Barang"}]',
                'kesesuaian_pekerjaan' => 'telah sesuai',
                'persentase_pembayaran' => '100%',
                'created_by' => 'seeder',
            ],
            [
                'nomor_bast' => 'BAST/MK-PHTC.05/Gs7/2026',
                'judul_bast' => 'BERITA ACARA SERAH TERIMA I',
                'jenis_pekerjaan' => 'PEKERJAAN JASA KONSULTANSI KONSTRUKSI',
                'lingkup_jasa' => 'Manajemen Konstruksi',
                'paket_id' => 5,
                'nama_paket' => 'Manajemen Konstruksi Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 5',
                'tanggal_bast' => '2026-05-30',
                'kota_bast' => 'Pekanbaru',
                'penyedia_nama' => 'PT. Manggala Karya Bangun Sarana KSO PT. Andalas Raya Consulindo',
                'penyedia_wakil' => 'Bowo Setyawan, S.T',
                'penyedia_jabatan' => 'Direktur',
                'penyedia_alamat' => 'Sabrik RT/RW 001/002, Kel. Duku Dungus, Kec. Grabag, Kab. Purworejo, Jawa Tengah',
                'dasar_pelaksanaan' => '["Kontrak/Surat Perjanjian Pekerjaan Jasa Konsultansi Manajemen Konstruksi Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 5 Nomor HK.02.01/PPS-RIAU/MK-PHTC.5/2025/01 tanggal 28 November 2025", "Surat Perintah Mulai Kerja (SPMK) Nomor HK.02.03/PPS-RIAU/SPMK/MK-PHTC.5/2025/01 tanggal 01 Desember 2025", "Surat Penunjukan Penyedia Barang/Jasa (SPPBJ) Nomor PB.02.01/SATKER//PPS-RIAU/Gs7/2025/168 tanggal 12 November 2025"]',
                'rincian_hasil_pekerjaan' => '[{"no": "1", "uraian": "Laporan Mingguan", "jumlah": "48 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "2", "uraian": "Laporan Bulanan", "jumlah": "12 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "3", "uraian": "Laporan Akhir Pengawasan Teknis", "jumlah": "1 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "4", "uraian": "Laporan Khusus (Profil Kegiatan)", "jumlah": "1 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "5", "uraian": "Laporan Khusus (Dokumen Permohonan SLF)", "jumlah": "1 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "6", "uraian": "SSD Eksternal", "jumlah": "1 Tb", "keterangan": "Barang"}]',
                'kesesuaian_pekerjaan' => 'telah sesuai',
                'persentase_pembayaran' => '100%',
                'created_by' => 'seeder',
            ],
            [
                'nomor_bast' => 'BAST/MK-PHTC.06/Gs7/2026',
                'judul_bast' => 'BERITA ACARA SERAH TERIMA I',
                'jenis_pekerjaan' => 'PEKERJAAN JASA KONSULTANSI KONSTRUKSI',
                'lingkup_jasa' => 'Manajemen Konstruksi',
                'paket_id' => 6,
                'nama_paket' => 'Manajemen Konstruksi Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 6',
                'tanggal_bast' => '2026-05-30',
                'kota_bast' => 'Pekanbaru',
                'penyedia_nama' => 'PT. Mahakarya Abadi Konsultan KSO CV. Sinergi Lestari Karya',
                'penyedia_wakil' => 'Bambang Taidi, S.T',
                'penyedia_jabatan' => 'Direktur Utama',
                'penyedia_alamat' => 'Lingkungan VI RT/RW 021/011 Kel. Hutuo, Kec. Limboto, Kab. Gorontalo, Gorontalo',
                'dasar_pelaksanaan' => '["Kontrak/Surat Perjanjian Pekerjaan Jasa Konsultansi Manajemen Konstruksi Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 6 Nomor HK.02.01/PPS-RIAU/MK-PHTC.6/2025/01 tanggal 28 November 2025", "Surat Perintah Mulai Kerja (SPMK) Nomor HK.02.03/PPS-RIAU/SPMK/MK-PHTC.6/2025/01, tanggal 01 Desember 2025.", "Surat Penunjukan Penyedia Barang/Jasa (SPPBJ) Nomor PB.03.01/SATKER//PPS-RIAU/Gs7/2025/169 tanggal 12 November 2025"]',
                'rincian_hasil_pekerjaan' => '[{"no": "1", "uraian": "Laporan Mingguan", "jumlah": "48 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "2", "uraian": "Laporan Bulanan", "jumlah": "12 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "3", "uraian": "Laporan Akhir Pengawasan Teknis", "jumlah": "1 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "4", "uraian": "Laporan Khusus (Profil Kegiatan)", "jumlah": "1 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "5", "uraian": "Laporan Khusus (Dokumen Permohonan SLF)", "jumlah": "1 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "6", "uraian": "SSD Eksternal", "jumlah": "1 Tb", "keterangan": "Barang"}]',
                'kesesuaian_pekerjaan' => 'telah sesuai',
                'persentase_pembayaran' => '100%',
                'created_by' => 'seeder',
            ],
            [
                'nomor_bast' => 'BAST/MK-PHTC.07/Gs7/2026',
                'judul_bast' => 'BERITA ACARA SERAH TERIMA I',
                'jenis_pekerjaan' => 'PEKERJAAN JASA KONSULTANSI KONSTRUKSI',
                'lingkup_jasa' => 'Manajemen Konstruksi',
                'paket_id' => 7,
                'nama_paket' => 'Manajemen Konstruksi Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 7',
                'tanggal_bast' => '2026-05-30',
                'kota_bast' => 'Pekanbaru',
                'penyedia_nama' => 'PT. Astadipati Duta Harindo',
                'penyedia_wakil' => 'Ir. Eri Budi Purnomo, ST',
                'penyedia_jabatan' => 'Direktur Utama',
                'penyedia_alamat' => 'Jl. Rajawali No. 181 Kel. Cempaka Permai Kec. Gading Cempaka, Kota Bengkulu, Bengkulu',
                'dasar_pelaksanaan' => '["Kontrak/Surat Perjanjian Pekerjaan Jasa Konsultansi Manajemen Konstruksi Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 7 Nomor HK.02.01/PPS-RIAU/MK-PHTC.7/2025/01 tanggal 28 November 2025", "Surat Perintah Mulai Kerja (SPMK) Nomor HK.02.03/PPS-RIAU/SPMK/MK-PHTC.7/2025/01 tanggal 01 Desember 2025", "Surat Penunjukan Penyedia Barang/Jasa (SPPBJ) Nomor PB.02.01/SATKER//PPS-RIAU/Gs7/2025/170 tanggal 12 November 2025"]',
                'rincian_hasil_pekerjaan' => '[{"no": "1", "uraian": "Laporan Mingguan", "jumlah": "48 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "2", "uraian": "Laporan Bulanan", "jumlah": "12 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "3", "uraian": "Laporan Akhir Pengawasan Teknis", "jumlah": "1 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "4", "uraian": "Laporan Khusus (Profil Kegiatan)", "jumlah": "1 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "5", "uraian": "Laporan Khusus (Dokumen Permohonan SLF)", "jumlah": "1 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "6", "uraian": "SSD Eksternal", "jumlah": "1 Tb", "keterangan": "Barang"}]',
                'kesesuaian_pekerjaan' => 'telah sesuai',
                'persentase_pembayaran' => '100%',
                'created_by' => 'seeder',
            ],
            [
                'nomor_bast' => 'BAST/MK-PHTC.08/Gs7/2026',
                'judul_bast' => 'BERITA ACARA SERAH TERIMA I',
                'jenis_pekerjaan' => 'PEKERJAAN JASA KONSULTANSI KONSTRUKSI',
                'lingkup_jasa' => 'Manajemen Konstruksi',
                'paket_id' => 8,
                'nama_paket' => 'Manajemen Konstruksi Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 8',
                'tanggal_bast' => '2026-05-30',
                'kota_bast' => 'Pekanbaru',
                'penyedia_nama' => 'PT. Laras Sembada KSO PT. Citra Yasa Persada',
                'penyedia_wakil' => 'Drs. Arry Budiyanto, M.Dev.Plg',
                'penyedia_jabatan' => 'Direktur',
                'penyedia_alamat' => 'Gedung Perkantoran EightyEight@Kasablanka Office Tower Lt. 10 Unit E, Jl. Casablanca Kav. 88 - Jakarta Selatan, DKI Jakarta',
                'dasar_pelaksanaan' => '["Kontrak/Surat Perjanjian Pekerjaan Jasa Konsultansi Manajemen Konstruksi Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 8 Nomor HK.02.01/PPS-RIAU/MK-PHTC.8/2025/01 tanggal 28 November 2025", "Surat Perintah Mulai Kerja (SPMK) Nomor HK.02.03/PPS-RIAU/SPMK/MK-PHTC.8/2025/01 tanggal 01 Desember 2025", "Surat Penunjukan Penyedia Barang/Jasa (SPPBJ) Nomor PB.02.01/SATKER//PPS-RIAU/Gs7/2025/171 tanggal 12 November 2025"]',
                'rincian_hasil_pekerjaan' => '[{"no": "1", "uraian": "Laporan Mingguan", "jumlah": "48 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "2", "uraian": "Laporan Bulanan", "jumlah": "12 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "3", "uraian": "Laporan Akhir Pengawasan Teknis", "jumlah": "1 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "4", "uraian": "Laporan Khusus (Profil Kegiatan)", "jumlah": "1 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "5", "uraian": "Laporan Khusus (Dokumen Permohonan SLF)", "jumlah": "1 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "6", "uraian": "SSD Eksternal", "jumlah": "1 Tb", "keterangan": "Barang"}]',
                'kesesuaian_pekerjaan' => 'telah sesuai',
                'persentase_pembayaran' => '100%',
                'created_by' => 'seeder',
            ],
            [
                'nomor_bast' => 'BAST/MK-PHTC.09/Gs7/2026',
                'judul_bast' => 'BERITA ACARA SERAH TERIMA I',
                'jenis_pekerjaan' => 'PEKERJAAN JASA KONSULTANSI KONSTRUKSI',
                'lingkup_jasa' => 'Manajemen Konstruksi',
                'paket_id' => 9,
                'nama_paket' => 'Manajemen Konstruksi Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 9',
                'tanggal_bast' => '2026-05-30',
                'kota_bast' => 'Pekanbaru',
                'penyedia_nama' => 'PT. Astadipati Duta H KSO PT. SCE',
                'penyedia_wakil' => 'Ir. Eri Budi Purnomo, ST',
                'penyedia_jabatan' => 'Direktur Utama',
                'penyedia_alamat' => 'Jl. Rajawali No. 181 Kel. Cempaka Permai Kec. Gading Cempaka, Kota Bengkulu, Bengkulu',
                'dasar_pelaksanaan' => '["Kontrak/Surat Perjanjian Pekerjaan Jasa Konsultansi Manajemen Konstruksi Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 9 Nomor HK.02.01/PPS-RIAU/MK-PHTC.9/2025/01 tanggal 28 November 2025", "Surat Perintah Mulai Kerja (SPMK) Nomor HK.02.03/PPS-RIAU/SPMK/MK-PHTC.9/2025/01 tanggal 01 Desember 2025", "Surat Penunjukan Penyedia Barang/Jasa (SPPBJ) Nomor PB.02.01/SATKER//PPS-RIAU/Gs7/2025/172 tanggal 12 November 2025"]',
                'rincian_hasil_pekerjaan' => '[{"no": "1", "uraian": "Laporan Mingguan", "jumlah": "48 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "2", "uraian": "Laporan Bulanan", "jumlah": "12 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "3", "uraian": "Laporan Akhir Pengawasan Teknis", "jumlah": "1 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "4", "uraian": "Laporan Khusus (Profil Kegiatan)", "jumlah": "1 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "5", "uraian": "Laporan Khusus (Dokumen Permohonan SLF)", "jumlah": "1 Buku", "keterangan": "Hardcopy & softcopy"}, {"no": "6", "uraian": "SSD Eksternal", "jumlah": "1 Tb", "keterangan": "Barang"}]',
                'kesesuaian_pekerjaan' => 'telah sesuai',
                'persentase_pembayaran' => '100%',
                'created_by' => 'seeder',
            ],
        ];

        $insertedCount = 0;
        $updatedCount  = 0;

        foreach ($items as $item) {
            $nomorBast = $item['nomor_bast'];

            $payload = array_merge($item, [
                'kop_surat_id'   => $kopId,
                'ppk_pegawai_id' => $ppkId,
                'ppk_nama'       => $ppkNama,
                'ppk_nip'        => $ppkNip,
                'ppk_jabatan'    => $ppkJabatan,
                'ppk_satker'     => $ppkSatker,
                'ppk_alamat'     => $ppkAlamat,
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);

            $existing = $db->table('trn_bast')
                ->where('nomor_bast', $nomorBast)
                ->get()
                ->getRowArray();

            if ($existing) {
                $db->table('trn_bast')
                    ->where('id', $existing['id'])
                    ->update($payload);
                $updatedCount++;
            } else {
                $payload['created_at'] = date('Y-m-d H:i:s');
                $db->table('trn_bast')->insert($payload);
                $insertedCount++;
            }
        }

        echo "BAST Seeder Selesai! Ditambahkan: {$insertedCount}, Diperbarui: {$updatedCount}. Total: " . count($items) . " berkas BAST.\n";
    }
}
