<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBmnMadrasahPhtcTables extends Migration
{
    public function up()
    {
        $db = $this->db;
        $forge = \Config\Database::forge();

        // 1. Tabel trn_bmn_madrasah_paket
        if (! $db->tableExists('trn_bmn_madrasah_paket')) {
            $forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'no_paket' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => false,
                ],
                'nama_paket' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => false,
                ],
                'wilayah' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => false,
                ],
                'tahun_anggaran' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'default'    => '2025-2026',
                    'null'       => false,
                ],
                'jenis_bmn' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'default'    => 'Aset Tetap Lainnya Dalam Pengerjaan',
                    'null'       => false,
                ],
                'kode_bmn' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'default'    => '7010101005',
                    'null'       => false,
                ],
                'nilai_kontrak_fisik' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '18,2',
                    'default'    => 0.00,
                    'null'       => false,
                ],
                'nilai_mk_supervisi' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '18,2',
                    'default'    => 0.00,
                    'null'       => false,
                ],
                'total_asp_nilai' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '18,2',
                    'default'    => 0.00,
                    'null'       => false,
                ],
                'total_gabungan' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '18,2',
                    'default'    => 0.00,
                    'null'       => false,
                ],
                'jumlah_madrasah' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                    'null'       => false,
                ],
                'jumlah_asp_unit' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
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
            $forge->addKey('id', true);
            $forge->addKey('no_paket');
            $forge->createTable('trn_bmn_madrasah_paket', true);
        }

        // 2. Tabel trn_bmn_madrasah_sekolah
        if (! $db->tableExists('trn_bmn_madrasah_sekolah')) {
            $forge->addField([
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
                    'null'       => false,
                ],
                'nama_sekolah' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => false,
                ],
                'status_sekolah' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'default'    => 'Negeri',
                    'null'       => false,
                ],
                'kabupaten' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => true,
                ],
                'acuan_ded' => [
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
            $forge->addKey('id', true);
            $forge->addKey('paket_id');
            $forge->createTable('trn_bmn_madrasah_sekolah', true);
        }

        // 3. Tabel trn_bmn_madrasah_asp
        if (! $db->tableExists('trn_bmn_madrasah_asp')) {
            $forge->addField([
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
                    'null'       => false,
                ],
                'no_urut' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 1,
                    'null'       => false,
                ],
                'nama_barang' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => false,
                ],
                'jenis_bmn' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => false,
                ],
                'kode_bmn' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => false,
                ],
                'merk_tipe' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'tahun' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'default'    => '2025',
                    'null'       => false,
                ],
                'kondisi_baik' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 1,
                    'null'       => false,
                ],
                'kondisi_rr' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                    'null'       => false,
                ],
                'kondisi_rb' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                    'null'       => false,
                ],
                'satuan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'default'    => 'Unit',
                    'null'       => false,
                ],
                'jumlah' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 1,
                    'null'       => false,
                ],
                'harga_satuan' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '18,2',
                    'default'    => 0.00,
                    'null'       => false,
                ],
                'total_nilai' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '18,2',
                    'default'    => 0.00,
                    'null'       => false,
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
            $forge->addKey('paket_id');
            $forge->createTable('trn_bmn_madrasah_asp', true);
        }

        // Seed data jika masih kosong
        $now = date('Y-m-d H:i:s');
        if ($db->table('trn_bmn_madrasah_paket')->countAllResults() === 0) {
            $paketData = [
                [
                    'id'                  => 1,
                    'no_paket'            => 1,
                    'nama_paket'          => 'Rehabilitasi Dan Renovasi Madrasah PHTC Provinsi Riau 1',
                    'wilayah'             => 'Kota Pekanbaru, Kab. Siak',
                    'tahun_anggaran'      => '2025',
                    'jenis_bmn'           => 'Aset Tetap Lainnya Dalam Pengerjaan',
                    'kode_bmn'            => '7010101005',
                    'nilai_kontrak_fisik' => 16627730000.00,
                    'nilai_mk_supervisi'  => 706879000.00,
                    'total_asp_nilai'     => 2101493069.00,
                    'total_gabungan'      => 18729223069.00,
                    'jumlah_madrasah'     => 5,
                    'jumlah_asp_unit'     => 1940,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ],
                [
                    'id'                  => 2,
                    'no_paket'            => 2,
                    'nama_paket'          => 'Rehabilitasi Dan Renovasi Madrasah PHTC Provinsi Riau 2',
                    'wilayah'             => 'Kab. Kampar, Kab. Rokan Hulu',
                    'tahun_anggaran'      => '2025-2026',
                    'jenis_bmn'           => 'Aset Tetap Lainnya Dalam Pengerjaan',
                    'kode_bmn'            => '7010101005',
                    'nilai_kontrak_fisik' => 14560932000.00,
                    'nilai_mk_supervisi'  => 653576000.00,
                    'total_asp_nilai'     => 3897849558.00,
                    'total_gabungan'      => 18458781558.00,
                    'jumlah_madrasah'     => 6,
                    'jumlah_asp_unit'     => 2846,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ],
                [
                    'id'                  => 3,
                    'no_paket'            => 3,
                    'nama_paket'          => 'Rehabilitasi Dan Renovasi Madrasah PHTC Provinsi Riau 3',
                    'wilayah'             => 'Kab. Pelalawan, Kab. Inhu, Kota Dumai, Kab. Bengkalis (Kec. Rupat)',
                    'tahun_anggaran'      => '2025-2026',
                    'jenis_bmn'           => 'Tanah dan Bangunan dalam proses',
                    'kode_bmn'            => '1010901001',
                    'nilai_kontrak_fisik' => 12700000000.00,
                    'nilai_mk_supervisi'  => 591357000.00,
                    'total_asp_nilai'     => 2151531478.00,
                    'total_gabungan'      => 14851531478.00,
                    'jumlah_madrasah'     => 6,
                    'jumlah_asp_unit'     => 1351,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ],
                [
                    'id'                  => 4,
                    'no_paket'            => 4,
                    'nama_paket'          => 'Rehabilitasi Dan Renovasi Madrasah PHTC Provinsi Riau 4',
                    'wilayah'             => 'Kab. Kep. Meranti',
                    'tahun_anggaran'      => '2025-2026',
                    'jenis_bmn'           => 'Tanah dan Bangunan dalam proses',
                    'kode_bmn'            => '1010901001',
                    'nilai_kontrak_fisik' => 37348699000.00,
                    'nilai_mk_supervisi'  => 1228954000.00,
                    'total_asp_nilai'     => 2579980944.00,
                    'total_gabungan'      => 39928679944.00,
                    'jumlah_madrasah'     => 11,
                    'jumlah_asp_unit'     => 1715,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ],
                [
                    'id'                  => 5,
                    'no_paket'            => 5,
                    'nama_paket'          => 'Rehabilitasi Dan Renovasi Madrasah PHTC Provinsi Riau 5',
                    'wilayah'             => 'Kab. Bengkalis (Kec. Bengkalis)',
                    'tahun_anggaran'      => '2025-2026',
                    'jenis_bmn'           => 'Aset Tetap Lainnya Dalam Pengerjaan',
                    'kode_bmn'            => '7010101005',
                    'nilai_kontrak_fisik' => 29865170000.00,
                    'nilai_mk_supervisi'  => 1126899000.00,
                    'total_asp_nilai'     => 2388180917.00,
                    'total_gabungan'      => 32253350917.00,
                    'jumlah_madrasah'     => 6,
                    'jumlah_asp_unit'     => 1706,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ],
                [
                    'id'                  => 6,
                    'no_paket'            => 6,
                    'nama_paket'          => 'Rehabilitasi Dan Renovasi Madrasah PHTC Provinsi Riau 6',
                    'wilayah'             => 'Kab. Bengkalis (Kec. Bantan)',
                    'tahun_anggaran'      => '2025-2026',
                    'jenis_bmn'           => 'Aset Tetap Lainnya Dalam Pengerjaan',
                    'kode_bmn'            => '7010101005',
                    'nilai_kontrak_fisik' => 27713227000.00,
                    'nilai_mk_supervisi'  => 883924000.00,
                    'total_asp_nilai'     => 2012474048.00,
                    'total_gabungan'      => 29725701048.00,
                    'jumlah_madrasah'     => 8,
                    'jumlah_asp_unit'     => 1389,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ],
                [
                    'id'                  => 7,
                    'no_paket'            => 7,
                    'nama_paket'          => 'Rehabilitasi Dan Renovasi Madrasah PHTC Provinsi Riau 7',
                    'wilayah'             => 'Kota Dumai, Kab. Kuansing, Kab. Rokan Hilir, Kab. Bengkalis',
                    'tahun_anggaran'      => '2025-2026',
                    'jenis_bmn'           => 'Aset Tetap Lainnya Dalam Pengerjaan',
                    'kode_bmn'            => '7010101005',
                    'nilai_kontrak_fisik' => 26240648000.00,
                    'nilai_mk_supervisi'  => 1250988000.00,
                    'total_asp_nilai'     => 3653355110.00,
                    'total_gabungan'      => 29894003110.00,
                    'jumlah_madrasah'     => 7,
                    'jumlah_asp_unit'     => 2976,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ],
                [
                    'id'                  => 8,
                    'no_paket'            => 8,
                    'nama_paket'          => 'Rehabilitasi Dan Renovasi Madrasah PHTC Provinsi Riau 8',
                    'wilayah'             => 'Kab. Inhil - Kab. Kep Meranti',
                    'tahun_anggaran'      => '2025-2026',
                    'jenis_bmn'           => 'Aset Tetap Lainnya Dalam Pengerjaan',
                    'kode_bmn'            => '7010101005',
                    'nilai_kontrak_fisik' => 44539856000.00,
                    'nilai_mk_supervisi'  => 1285125000.00,
                    'total_asp_nilai'     => 3344504414.00,
                    'total_gabungan'      => 47884360414.00,
                    'jumlah_madrasah'     => 4,
                    'jumlah_asp_unit'     => 2538,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ],
                [
                    'id'                  => 9,
                    'no_paket'            => 9,
                    'nama_paket'          => 'Rehabilitasi Dan Renovasi Madrasah PHTC Provinsi Riau 9',
                    'wilayah'             => 'Kab. Rokan Hilir',
                    'tahun_anggaran'      => '2025-2026',
                    'jenis_bmn'           => 'Aset Tetap Lainnya Dalam Pengerjaan',
                    'kode_bmn'            => '7010101005',
                    'nilai_kontrak_fisik' => 36122327000.00,
                    'nilai_mk_supervisi'  => 1008844000.00,
                    'total_asp_nilai'     => 3129138645.00,
                    'total_gabungan'      => 39251465645.00,
                    'jumlah_madrasah'     => 9,
                    'jumlah_asp_unit'     => 2420,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ],
            ];
            $db->table('trn_bmn_madrasah_paket')->insertBatch($paketData);
        }

        // Seed Madrasah
        if ($db->table('trn_bmn_madrasah_sekolah')->countAllResults() === 0) {
            $sekolahData = [
                // Paket 1 (5)
                ['paket_id' => 1, 'nama_sekolah' => 'MIN 2 Pekanbaru', 'status_sekolah' => 'Negeri', 'kabupaten' => 'Kota Pekanbaru', 'acuan_ded' => 'MIN 2 PEKANBARU.rar'],
                ['paket_id' => 1, 'nama_sekolah' => 'MTsN 2 Pekanbaru', 'status_sekolah' => 'Negeri', 'kabupaten' => 'Kota Pekanbaru', 'acuan_ded' => 'MTSN 2 PEKANBARU.rar'],
                ['paket_id' => 1, 'nama_sekolah' => 'MAN 1 Pekanbaru', 'status_sekolah' => 'Negeri', 'kabupaten' => 'Kota Pekanbaru', 'acuan_ded' => 'MAN 1.rar'],
                ['paket_id' => 1, 'nama_sekolah' => 'MAN 2 Pekanbaru', 'status_sekolah' => 'Negeri', 'kabupaten' => 'Kota Pekanbaru', 'acuan_ded' => 'MAN 2 PEKANBARU.rar'],
                ['paket_id' => 1, 'nama_sekolah' => 'MAN 1 Siak', 'status_sekolah' => 'Negeri', 'kabupaten' => 'Kab. Siak', 'acuan_ded' => 'Siak.rar'],

                // Paket 2 (6)
                ['paket_id' => 2, 'nama_sekolah' => 'MTsN 3 Kampar', 'status_sekolah' => 'Negeri', 'kabupaten' => 'Kab. Kampar', 'acuan_ded' => 'DED+MTSN+3+KAMPAR (Dokumen Perencana)'],
                ['paket_id' => 2, 'nama_sekolah' => 'MTsN 6 Kampar', 'status_sekolah' => 'Negeri', 'kabupaten' => 'Kab. Kampar', 'acuan_ded' => 'DED+MTSN+6+KAMPAR (Dokumen Perencana)'],
                ['paket_id' => 2, 'nama_sekolah' => 'MTsN 7 Kampar', 'status_sekolah' => 'Negeri', 'kabupaten' => 'Kab. Kampar', 'acuan_ded' => 'DED+MTSN+7+KAMPAR (Dokumen Perencana)'],
                ['paket_id' => 2, 'nama_sekolah' => 'MTsN 5 Rokan Hulu', 'status_sekolah' => 'Negeri', 'kabupaten' => 'Kab. Rokan Hulu', 'acuan_ded' => 'DED+MTSN+5+ROHUL (Dokumen Perencana)'],
                ['paket_id' => 2, 'nama_sekolah' => 'MTsS Pekan Tebih', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Rokan Hulu', 'acuan_ded' => 'DED+MTSS+PEKAN+TEBIH (Dokumen Perencana)'],
                ['paket_id' => 2, 'nama_sekolah' => 'MIS Simpang Kubu', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Kampar', 'acuan_ded' => 'DED+MIS+SIMPANG+KUBU (Dokumen Perencana)'],

                // Paket 3 (6)
                ['paket_id' => 3, 'nama_sekolah' => 'MTsS Miftahul Huda', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Pelalawan', 'acuan_ded' => 'DED+MTSS+MIFTAHUL+HUDA (Dokumen Perencana)'],
                ['paket_id' => 3, 'nama_sekolah' => 'MAS Madinatun Najah', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Indragiri Hulu', 'acuan_ded' => 'DED+MAS+MADINATUN+NAJAH (Dokumen Perencana)'],
                ['paket_id' => 3, 'nama_sekolah' => 'MAS Nurul Islam', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kota Dumai', 'acuan_ded' => 'DED+MAS+NURUL+ISLAM (Dokumen Perencana)'],
                ['paket_id' => 3, 'nama_sekolah' => 'MTsS Al-Anshor', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Bengkalis (Kec. Rupat)', 'acuan_ded' => 'DED+MTSS+AL+ANSHOR (Dokumen Perencana)'],
                ['paket_id' => 3, 'nama_sekolah' => 'MTsS Al-Falah', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Bengkalis (Kec. Rupat)', 'acuan_ded' => 'DED+MTSS+AL+FALAH+RUPAT (Dokumen Perencana)'],
                ['paket_id' => 3, 'nama_sekolah' => 'MAS Fakhrul Islam', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Bengkalis (Kec. Rupat)', 'acuan_ded' => 'DED+MAS+FAKHRUL+ISLAM (Dokumen Perencana)'],

                // Paket 4 (11)
                ['paket_id' => 4, 'nama_sekolah' => 'MIS Tarbiyah Islamiyah', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Kep. Meranti', 'acuan_ded' => 'DED+MIS+TARBIYAH+ISLAMIYAH.pdf'],
                ['paket_id' => 4, 'nama_sekolah' => 'MIS Raudhatut Thalibin', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Kep. Meranti', 'acuan_ded' => 'DED+MIS+RAUDHATUT+THALIBIN.pdf'],
                ['paket_id' => 4, 'nama_sekolah' => 'MTsS An-Nur', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Kep. Meranti', 'acuan_ded' => 'DED+MTsS+ANNUR.pdf'],
                ['paket_id' => 4, 'nama_sekolah' => 'MTsS Miftahul Mubtadin', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Kep. Meranti', 'acuan_ded' => 'DED+MTsS+MIFTAHUL+MUBTADIIN.pdf'],
                ['paket_id' => 4, 'nama_sekolah' => 'MTsS Raudhatul Hidayah', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Kep. Meranti', 'acuan_ded' => 'DED+MTsS+RAUDHATUL+HIDAYAH.pdf'],
                ['paket_id' => 4, 'nama_sekolah' => 'MTsS Raudhatut Thalibin', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Kep. Meranti', 'acuan_ded' => 'DED+MTsS+RAUDHATUT+THALIBIN.pdf'],
                ['paket_id' => 4, 'nama_sekolah' => 'MAS Raudhatul Hidayah', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Kep. Meranti', 'acuan_ded' => 'DED+MTsS+RAUDHATUL+HIDAYAH.pdf'],
                ['paket_id' => 4, 'nama_sekolah' => 'MAS Raudhatut Thalibin', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Kep. Meranti', 'acuan_ded' => 'DED+MAS+RAUDHATUT+THALIBIN.pdf'],
                ['paket_id' => 4, 'nama_sekolah' => 'MTsS Al-Falah', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Kep. Meranti', 'acuan_ded' => 'DED+MTsS+ALFALAH (Meranti)'],
                ['paket_id' => 4, 'nama_sekolah' => 'MTsS Hidayatul Mutaalim', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Kep. Meranti', 'acuan_ded' => 'DED+MTsS+HIDAYATUL+MUTAALLIM.pdf'],
                ['paket_id' => 4, 'nama_sekolah' => 'MAS Hidayatul Muttaallim Mengkirau', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Kep. Meranti', 'acuan_ded' => 'DED+MAS+HIDAYATUL+MUTAALLIM+MENGKIRAU.pdf'],

                // Paket 5 (6)
                ['paket_id' => 5, 'nama_sekolah' => 'MTsS Al-Huda Bengkalis', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Bengkalis (Kec. Bengkalis)', 'acuan_ded' => 'DED+MTsS+Al+Huda.pdf'],
                ['paket_id' => 5, 'nama_sekolah' => 'MTsS Ar Rosyidiyah', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Bengkalis (Kec. Bengkalis)', 'acuan_ded' => 'DED+MTss+Ar+Rosyidiyah.pdf'],
                ['paket_id' => 5, 'nama_sekolah' => 'MTsS Nurul Jadid Pedekik', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Bengkalis (Kec. Bengkalis)', 'acuan_ded' => 'DED+MTsS+NURUL+JADID+PEDEKIK.pdf'],
                ['paket_id' => 5, 'nama_sekolah' => 'MTsS Tajhiz Diniyah', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Bengkalis (Kec. Bengkalis)', 'acuan_ded' => 'DED+MTsS+Tajhid+Diniyah.pdf'],
                ['paket_id' => 5, 'nama_sekolah' => 'MAS Ar Rosyidiyah', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Bengkalis (Kec. Bengkalis)', 'acuan_ded' => 'DED+MAS+Ar+Rosyidiyah.pdf'],
                ['paket_id' => 5, 'nama_sekolah' => 'MAS Darussalam', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Bengkalis (Kec. Bengkalis)', 'acuan_ded' => 'DED+MAS+Darus+Salam.pdf'],

                // Paket 6 (8)
                ['paket_id' => 6, 'nama_sekolah' => 'MIS Darul Aiman Muntai', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Bengkalis (Kec. Bantan)', 'acuan_ded' => 'DED+MIS+DARUL+AIMAN+MUNTAI.pdf'],
                ['paket_id' => 6, 'nama_sekolah' => 'MTsS Al-Falah Jangkang', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Bengkalis (Kec. Bantan)', 'acuan_ded' => 'DED+MTsS+AL+FALAH+JANGKANG.pdf'],
                ['paket_id' => 6, 'nama_sekolah' => 'MTsS Al Irsyadiyah Muntai', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Bengkalis (Kec. Bantan)', 'acuan_ded' => 'DED+MTsS+AL+IRSYADIYAH+MUNTAI.pdf'],
                ['paket_id' => 6, 'nama_sekolah' => 'MTsS Darul Aiman Muntai', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Bengkalis (Kec. Bantan)', 'acuan_ded' => 'DED+MTsS+DARUL+AIMAN+MUNTAI.pdf'],
                ['paket_id' => 6, 'nama_sekolah' => 'MTsS Miftahul Ulum Bantan Air', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Bengkalis (Kec. Bantan)', 'acuan_ded' => 'DED+MTsS+MIFTAHUL+ULUM+BANTAN+AIR.pdf'],
                ['paket_id' => 6, 'nama_sekolah' => 'MTsS Nurul Hidayah Bantan Tua', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Bengkalis (Kec. Bantan)', 'acuan_ded' => 'DED+MTsS+NURUL+HIDAYAH+BANTAN+TUA.pdf'],
                ['paket_id' => 6, 'nama_sekolah' => 'MAS Miftahul Jannah Selat Baru', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Bengkalis (Kec. Bantan)', 'acuan_ded' => 'DED+MAS+MIFTAHUL+JANNAH+SELAT+BARU.pdf'],
                ['paket_id' => 6, 'nama_sekolah' => 'MAS Darul Aiman Muntai', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Bengkalis (Kec. Bantan)', 'acuan_ded' => 'DED+MAS+DARUL+AIMAN+MUNTAI.pdf'],

                // Paket 7 (7)
                ['paket_id' => 7, 'nama_sekolah' => 'MIN 1 Kota Dumai', 'status_sekolah' => 'Negeri', 'kabupaten' => 'Kota Dumai', 'acuan_ded' => 'DED+MIN+1+KOTA+DUMAI.pdf'],
                ['paket_id' => 7, 'nama_sekolah' => 'MTsN 4 Kuantan Singingi', 'status_sekolah' => 'Negeri', 'kabupaten' => 'Kab. Kuantan Singingi', 'acuan_ded' => 'DED+MTSN+4+KUANSING.pdf'],
                ['paket_id' => 7, 'nama_sekolah' => 'MIN 1 Rokan Hilir', 'status_sekolah' => 'Negeri', 'kabupaten' => 'Kab. Rokan Hilir', 'acuan_ded' => 'DED+MIN+1+ROKAN+HILIR.pdf'],
                ['paket_id' => 7, 'nama_sekolah' => 'MIN 1 Bengkalis', 'status_sekolah' => 'Negeri', 'kabupaten' => 'Kab. Bengkalis', 'acuan_ded' => 'DED+MIN+1+BENGKALIS.pdf'],
                ['paket_id' => 7, 'nama_sekolah' => 'MTsN 2 Bengkalis', 'status_sekolah' => 'Negeri', 'kabupaten' => 'Kab. Bengkalis', 'acuan_ded' => 'DED+MTsN+2+BENGKALIS.pdf'],
                ['paket_id' => 7, 'nama_sekolah' => 'MTsN 5 Bengkalis', 'status_sekolah' => 'Negeri', 'kabupaten' => 'Kab. Bengkalis', 'acuan_ded' => 'DED+MTSN+5+BENGKALIS.pdf'],
                ['paket_id' => 7, 'nama_sekolah' => 'MAN 1 Bengkalis', 'status_sekolah' => 'Negeri', 'kabupaten' => 'Kab. Bengkalis', 'acuan_ded' => 'DED+MAN+1+BENGKALIS.pdf'],

                // Paket 8 (4)
                ['paket_id' => 8, 'nama_sekolah' => 'MIN 2 Indragiri Hilir', 'status_sekolah' => 'Negeri', 'kabupaten' => 'Kab. Indragiri Hilir', 'acuan_ded' => 'DED+MIN+2+INHIL.pdf'],
                ['paket_id' => 8, 'nama_sekolah' => 'MAN 3 Indragiri Hilir', 'status_sekolah' => 'Negeri', 'kabupaten' => 'Kab. Indragiri Hilir', 'acuan_ded' => 'DED+MAN+3+INHIL.pdf'],
                ['paket_id' => 8, 'nama_sekolah' => 'MTsN 1 Kepulauan Meranti', 'status_sekolah' => 'Negeri', 'kabupaten' => 'Kab. Kepulauan Meranti', 'acuan_ded' => 'DED+MTsN+1+MERANTI (Dokumen Perencana)'],
                ['paket_id' => 8, 'nama_sekolah' => 'MAN 2 Kepulauan Meranti', 'status_sekolah' => 'Negeri', 'kabupaten' => 'Kab. Kepulauan Meranti', 'acuan_ded' => 'DED+MAN+2+KEPULAUAN+MERANTI.pdf'],

                // Paket 9 (9)
                ['paket_id' => 9, 'nama_sekolah' => 'MIS Al-Falah', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Rokan Hilir', 'acuan_ded' => 'DED+MIS+ALFALAH.pdf'],
                ['paket_id' => 9, 'nama_sekolah' => 'MIS Al-Husin', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Rokan Hilir', 'acuan_ded' => 'DED+MIS+ALHUSEN.pdf'],
                ['paket_id' => 9, 'nama_sekolah' => 'MIS Al-Qisimiyah', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Rokan Hilir', 'acuan_ded' => 'DED+MIS+AL+QOSIMIYAH.pdf'],
                ['paket_id' => 9, 'nama_sekolah' => 'MIS Al-Washiliyah', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Rokan Hilir', 'acuan_ded' => 'DED+MIS+AL+WAHSLIYAH.pdf'],
                ['paket_id' => 9, 'nama_sekolah' => 'MIS Ishlahiyah', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Rokan Hilir', 'acuan_ded' => 'DED+MIS+ISLAHIYAH (1).pdf'],
                ['paket_id' => 9, 'nama_sekolah' => 'MTsS Al-Adalah', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Rokan Hilir', 'acuan_ded' => 'DED+MTSs+AL+ADALAH.pdf'],
                ['paket_id' => 9, 'nama_sekolah' => 'MTsS Datuk Batu Hampar', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Rokan Hilir', 'acuan_ded' => 'DED+MTSS+DATUK+BATU+HAMPAR.pdf'],
                ['paket_id' => 9, 'nama_sekolah' => 'MTsS Ishlahiyah', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Rokan Hilir', 'acuan_ded' => 'DED+MTSs+ISLAHIYAH (1).pdf'],
                ['paket_id' => 9, 'nama_sekolah' => 'MAS Ishlahiyah Panipahan', 'status_sekolah' => 'Swasta', 'kabupaten' => 'Kab. Rokan Hilir', 'acuan_ded' => 'DED+MAS+ISLAHIYAH (1).pdf'],
            ];
            foreach ($sekolahData as &$s) {
                $s['created_at'] = $now;
                $s['updated_at'] = $now;
            }
            unset($s);
            $db->table('trn_bmn_madrasah_sekolah')->insertBatch($sekolahData);
        }

        // Seed Peralatan Mesin (ASP)
        if ($db->table('trn_bmn_madrasah_asp')->countAllResults() === 0) {
            $aspData = [
                // Paket 1 (6 item)
                ['paket_id' => 1, 'no_urut' => 2, 'nama_barang' => 'Lemari Besi', 'jenis_bmn' => 'Lemari Besi/Metal', 'kode_bmn' => '3050104001', 'merk_tipe' => 'LEMARI LAK 08A TM', 'tahun' => '2025', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 27, 'harga_satuan' => 4438930.00, 'total_nilai' => 119851110.00],
                ['paket_id' => 1, 'no_urut' => 3, 'nama_barang' => 'White Board', 'jenis_bmn' => 'White Board', 'kode_bmn' => '3050105010', 'merk_tipe' => 'Papan Tulis - PT 120E', 'tahun' => '2025', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 27, 'harga_satuan' => 1518930.00, 'total_nilai' => 41011110.00],
                ['paket_id' => 1, 'no_urut' => 4, 'nama_barang' => 'Meja Siswa', 'jenis_bmn' => 'Meja Kerja Besi/Metal', 'kode_bmn' => '3050201001', 'merk_tipe' => 'Meja Siswa MS 04B TM', 'tahun' => '2025', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 916, 'harga_satuan' => 1165420.00, 'total_nilai' => 1067524720.00],
                ['paket_id' => 1, 'no_urut' => 5, 'nama_barang' => 'Meja Guru', 'jenis_bmn' => 'Meja Kerja Besi/Metal', 'kode_bmn' => '3050201002', 'merk_tipe' => 'MEJA GURU - MGS 01A TM', 'tahun' => '2025', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 27, 'harga_satuan' => 2173932.00, 'total_nilai' => 58696164.00],
                ['paket_id' => 1, 'no_urut' => 6, 'nama_barang' => 'Kursi Guru', 'jenis_bmn' => 'Kursi Besi/Metal', 'kode_bmn' => '3050201003', 'merk_tipe' => 'KURSI KERJA - KUR 14E TM', 'tahun' => '2025', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 27, 'harga_satuan' => 2278935.00, 'total_nilai' => 61531245.00],
                ['paket_id' => 1, 'no_urut' => 7, 'nama_barang' => 'Kursi Siswa', 'jenis_bmn' => 'Kursi Besi/Metal', 'kode_bmn' => '3050201003', 'merk_tipe' => 'Kursi Siswa KS 05 TM', 'tahun' => '2025', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 916, 'harga_satuan' => 821920.00, 'total_nilai' => 752878720.00],

                // Paket 2 (6 item)
                ['paket_id' => 2, 'no_urut' => 2, 'nama_barang' => 'Lemari Besi', 'jenis_bmn' => 'Lemari Besi/Metal', 'kode_bmn' => '3050104001', 'merk_tipe' => 'RAK BUKU VIVENTE VHCM 02-2', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 56, 'harga_satuan' => 6565008.00, 'total_nilai' => 367640448.00],
                ['paket_id' => 2, 'no_urut' => 3, 'nama_barang' => 'White Board', 'jenis_bmn' => 'White Board', 'kode_bmn' => '3050105010', 'merk_tipe' => 'VIVENTE WHITEBOARD VWBP 01', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 56, 'harga_satuan' => 1730690.00, 'total_nilai' => 96918640.00],
                ['paket_id' => 2, 'no_urut' => 4, 'nama_barang' => 'Meja Siswa', 'jenis_bmn' => 'Meja Kerja Besi/Metal', 'kode_bmn' => '3050201001', 'merk_tipe' => 'VIVENTE MEJA SISWA VSCD 37-01 PL', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 1183, 'harga_satuan' => 1147629.00, 'total_nilai' => 1357645107.00],
                ['paket_id' => 2, 'no_urut' => 5, 'nama_barang' => 'Meja Guru', 'jenis_bmn' => 'Meja Kerja Kayu', 'kode_bmn' => '3050201002', 'merk_tipe' => 'VIVENTE MEJA GURU VTED 12 HL-A PL', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 184, 'harga_satuan' => 2297434.00, 'total_nilai' => 422727856.00],
                ['paket_id' => 2, 'no_urut' => 6, 'nama_barang' => 'Kursi Guru', 'jenis_bmn' => 'Kursi Besi/Metal', 'kode_bmn' => '3050201003', 'merk_tipe' => 'KURSI VIVENTE HADAP V.950', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 184, 'harga_satuan' => 2251688.00, 'total_nilai' => 414310592.00],
                ['paket_id' => 2, 'no_urut' => 7, 'nama_barang' => 'Kursi Siswa', 'jenis_bmn' => 'Kursi Besi/Metal', 'kode_bmn' => '3050201003', 'merk_tipe' => 'VIVENTE KURSI SISWA VKS 32 PL', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 1183, 'harga_satuan' => 1047005.00, 'total_nilai' => 1238606915.00],

                // Paket 3 (10 item)
                ['paket_id' => 3, 'no_urut' => 2, 'nama_barang' => 'Kursi Guru', 'jenis_bmn' => 'Kursi Besi/Metal', 'kode_bmn' => '3050201003', 'merk_tipe' => 'Valleno Kursi Hadap VALE - KHD 04', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 89, 'harga_satuan' => 2152150.00, 'total_nilai' => 191541350.00],
                ['paket_id' => 3, 'no_urut' => 3, 'nama_barang' => 'Kursi Siswa', 'jenis_bmn' => 'Kursi Besi/Metal', 'kode_bmn' => '3050201003', 'merk_tipe' => 'Valleno Kursi Siswa VALE - KSK 43', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 18, 'harga_satuan' => 1005549.00, 'total_nilai' => 18099882.00],
                ['paket_id' => 3, 'no_urut' => 4, 'nama_barang' => 'Meja Siswa (Kayu)', 'jenis_bmn' => 'Meja Kerja Kayu', 'kode_bmn' => '3050201002', 'merk_tipe' => 'Valleno Meja Siswa VALE - STB61', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 18, 'harga_satuan' => 3244149.00, 'total_nilai' => 58394682.00],
                ['paket_id' => 3, 'no_urut' => 5, 'nama_barang' => 'Lemari Besi', 'jenis_bmn' => 'Lemari Besi/Metal', 'kode_bmn' => '3050104001', 'merk_tipe' => 'Valleno Filing Kabinet VALE - FCB 12', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 33, 'harga_satuan' => 5974149.00, 'total_nilai' => 197146917.00],
                ['paket_id' => 3, 'no_urut' => 6, 'nama_barang' => 'Kursi Siswa', 'jenis_bmn' => 'Kursi Besi/Metal', 'kode_bmn' => '3050201003', 'merk_tipe' => 'Valleno Kursi Siswa VALE - KSK 43', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 470, 'harga_satuan' => 1005549.00, 'total_nilai' => 472608030.00],
                ['paket_id' => 3, 'no_urut' => 7, 'nama_barang' => 'Meja Siswa', 'jenis_bmn' => 'Meja Kerja Besi/Metal', 'kode_bmn' => '3050201001', 'merk_tipe' => 'Valleno Meja SIswa VALE - STB 66', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 452, 'harga_satuan' => 1192100.00, 'total_nilai' => 538829200.00],
                ['paket_id' => 3, 'no_urut' => 8, 'nama_barang' => 'Meja Guru', 'jenis_bmn' => 'Meja Kerja Kayu', 'kode_bmn' => '3050201002', 'merk_tipe' => 'Valleno Meja Guru VALE - MGR 25', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 138, 'harga_satuan' => 2617675.00, 'total_nilai' => 361239150.00],
                ['paket_id' => 3, 'no_urut' => 9, 'nama_barang' => 'Kursi Guru', 'jenis_bmn' => 'Kursi Fiber Glas/Plastik', 'kode_bmn' => '3050201020', 'merk_tipe' => 'Valleno Kursi Guru VALE - TCC 24 M', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 50, 'harga_satuan' => 2334148.00, 'total_nilai' => 116707400.00],
                ['paket_id' => 3, 'no_urut' => 10, 'nama_barang' => 'Meja Guru', 'jenis_bmn' => 'Meja Kerja Kayu', 'kode_bmn' => '3050201002', 'merk_tipe' => 'Valleno Meja Guru VALE - MGR 25', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 50, 'harga_satuan' => 2789149.00, 'total_nilai' => 139457450.00],
                ['paket_id' => 3, 'no_urut' => 11, 'nama_barang' => 'White Board', 'jenis_bmn' => 'White Board', 'kode_bmn' => '3050105010', 'merk_tipe' => 'Valleno Whiteboard VALE - PTP 01', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 33, 'harga_satuan' => 1742649.00, 'total_nilai' => 57507417.00],

                // Paket 4 (8 item)
                ['paket_id' => 4, 'no_urut' => 2, 'nama_barang' => 'Lemari Besi', 'jenis_bmn' => 'Lemari Besi/Metal', 'kode_bmn' => '3050104001', 'merk_tipe' => 'YOOM - LEMARI BUKU HCC 81', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 171, 'harga_satuan' => 1299886.00, 'total_nilai' => 222280506.00],
                ['paket_id' => 4, 'no_urut' => 3, 'nama_barang' => 'White Board', 'jenis_bmn' => 'White Board', 'kode_bmn' => '3050105010', 'merk_tipe' => 'YOOM - WHITEBOARD WBB 01', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 50, 'harga_satuan' => 2189642.00, 'total_nilai' => 109482100.00],
                ['paket_id' => 4, 'no_urut' => 4, 'nama_barang' => 'Meja Siswa', 'jenis_bmn' => 'Meja Kerja Besi/Metal', 'kode_bmn' => '3050201001', 'merk_tipe' => 'YOOM - MEJA SISWA MSS 73', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 567, 'harga_satuan' => 1124231.00, 'total_nilai' => 637438977.00],
                ['paket_id' => 4, 'no_urut' => 5, 'nama_barang' => 'Meja Guru', 'jenis_bmn' => 'Meja Kerja Kayu', 'kode_bmn' => '3050201002', 'merk_tipe' => 'YOOM - MEJA GURU MGR 37', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 115, 'harga_satuan' => 4516555.00, 'total_nilai' => 519403825.00],
                ['paket_id' => 4, 'no_urut' => 6, 'nama_barang' => 'Kursi Guru (Kayu)', 'jenis_bmn' => 'Meja Kerja Kayu', 'kode_bmn' => '3050201002', 'merk_tipe' => 'YOOM - MEJA GURU MGR 37', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 16, 'harga_satuan' => 3037450.00, 'total_nilai' => 48599200.00],
                ['paket_id' => 4, 'no_urut' => 7, 'nama_barang' => 'Kursi Siswa', 'jenis_bmn' => 'Kursi Besi/Metal', 'kode_bmn' => '3050201003', 'merk_tipe' => 'YOOM - KURSI SISWA KRS 43', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 627, 'harga_satuan' => 1056969.00, 'total_nilai' => 662719563.00],
                ['paket_id' => 4, 'no_urut' => 8, 'nama_barang' => 'Kursi Guru', 'jenis_bmn' => 'Kursi Besi/Metal', 'kode_bmn' => '3050201003', 'merk_tipe' => 'YOOM - KURSI GURU KGG 24', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 153, 'harga_satuan' => 2204685.00, 'total_nilai' => 337316805.00],
                ['paket_id' => 4, 'no_urut' => 9, 'nama_barang' => 'Kursi Guru (Tipe B)', 'jenis_bmn' => 'Kursi Besi/Metal', 'kode_bmn' => '3050201003', 'merk_tipe' => 'YOOM - KURSI GURU KGG 24', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 16, 'harga_satuan' => 2671248.00, 'total_nilai' => 42739968.00],

                // Paket 5 (6 item)
                ['paket_id' => 5, 'no_urut' => 1, 'nama_barang' => 'Lemari Besi', 'jenis_bmn' => 'Lemari Besi/Metal', 'kode_bmn' => '3050104001', 'merk_tipe' => 'RAK BUKU VIVENTE VHCM 02-2', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 38, 'harga_satuan' => 6549002.00, 'total_nilai' => 248862076.00],
                ['paket_id' => 5, 'no_urut' => 2, 'nama_barang' => 'White Board', 'jenis_bmn' => 'White Board', 'kode_bmn' => '3050105010', 'merk_tipe' => 'VIVENTE WHITEBOARD VWBP 01', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 44, 'harga_satuan' => 1730688.00, 'total_nilai' => 76150272.00],
                ['paket_id' => 5, 'no_urut' => 3, 'nama_barang' => 'Meja Siswa', 'jenis_bmn' => 'Meja Kerja Besi/Metal', 'kode_bmn' => '3050201001', 'merk_tipe' => 'VIVENTE MEJA SISWA VSCD 37-01 PL', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 697, 'harga_satuan' => 1171050.00, 'total_nilai' => 816221850.00],
                ['paket_id' => 5, 'no_urut' => 4, 'nama_barang' => 'Meja Guru', 'jenis_bmn' => 'Meja Kerja Kayu', 'kode_bmn' => '3050201002', 'merk_tipe' => 'VIVENTE MEJA GURU VTED 12 HL-A PL', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 115, 'harga_satuan' => 2297434.00, 'total_nilai' => 264204910.00],
                ['paket_id' => 5, 'no_urut' => 5, 'nama_barang' => 'Kursi Guru', 'jenis_bmn' => 'Kursi Besi/Metal', 'kode_bmn' => '3050201003', 'merk_tipe' => 'KURSI VIVENTE HADAP V.950', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 115, 'harga_satuan' => 2251477.00, 'total_nilai' => 258919855.00],
                ['paket_id' => 5, 'no_urut' => 6, 'nama_barang' => 'Kursi Siswa', 'jenis_bmn' => 'Kursi Besi/Metal', 'kode_bmn' => '3050201003', 'merk_tipe' => 'VIVENTE KURSI SISWA VKS 32 PL', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 697, 'harga_satuan' => 1038482.00, 'total_nilai' => 723821954.00],

                // Paket 6 (6 item)
                ['paket_id' => 6, 'no_urut' => 2, 'nama_barang' => 'Lemari Besi', 'jenis_bmn' => 'Lemari Besi/Metal', 'kode_bmn' => '3050104001', 'merk_tipe' => 'RAK BUKU VIVENTE VHCM 02-2', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 31, 'harga_satuan' => 6549002.00, 'total_nilai' => 203019062.00],
                ['paket_id' => 6, 'no_urut' => 3, 'nama_barang' => 'White Board', 'jenis_bmn' => 'White Board', 'kode_bmn' => '3050105010', 'merk_tipe' => 'VIVENTE WHITEBOARD VWBP 01', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 50, 'harga_satuan' => 1730686.00, 'total_nilai' => 86534300.00],
                ['paket_id' => 6, 'no_urut' => 4, 'nama_barang' => 'Meja Siswa', 'jenis_bmn' => 'Meja Kerja Besi/Metal', 'kode_bmn' => '3050201001', 'merk_tipe' => 'VIVENTE MEJA SISWA VSCD 37-01 PL', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 539, 'harga_satuan' => 1171050.00, 'total_nilai' => 631195950.00],
                ['paket_id' => 6, 'no_urut' => 5, 'nama_barang' => 'Meja Guru', 'jenis_bmn' => 'Meja Kerja Kayu', 'kode_bmn' => '3050201002', 'merk_tipe' => 'VIVENTE MEJA GURU VTED 12 HL-A PL', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 115, 'harga_satuan' => 2297435.00, 'total_nilai' => 264205025.00],
                ['paket_id' => 6, 'no_urut' => 6, 'nama_barang' => 'Kursi Guru', 'jenis_bmn' => 'Kursi Besi/Metal', 'kode_bmn' => '3050201003', 'merk_tipe' => 'KURSI VIVENTE HADAP V.950', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 115, 'harga_satuan' => 2251675.00, 'total_nilai' => 258942625.00],
                ['paket_id' => 6, 'no_urut' => 7, 'nama_barang' => 'Kursi Siswa', 'jenis_bmn' => 'Kursi Besi/Metal', 'kode_bmn' => '3050201003', 'merk_tipe' => 'VIVENTE KURSI SISWA VKS 32 PL', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 539, 'harga_satuan' => 1054874.00, 'total_nilai' => 568577086.00],

                // Paket 7 (6 item)
                ['paket_id' => 7, 'no_urut' => 2, 'nama_barang' => 'Lemari Besi', 'jenis_bmn' => 'Lemari Besi/Metal', 'kode_bmn' => '3050104001', 'merk_tipe' => 'RAK BUKU VIVENTE VHCM 02-2', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 41, 'harga_satuan' => 6549000.00, 'total_nilai' => 268509000.00],
                ['paket_id' => 7, 'no_urut' => 3, 'nama_barang' => 'White Board', 'jenis_bmn' => 'White Board', 'kode_bmn' => '3050105010', 'merk_tipe' => 'VIVENTE WHITEBOARD VWBP 01', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 49, 'harga_satuan' => 1730694.00, 'total_nilai' => 84804006.00],
                ['paket_id' => 7, 'no_urut' => 4, 'nama_barang' => 'Meja Siswa', 'jenis_bmn' => 'Meja Kerja Besi/Metal', 'kode_bmn' => '3050201001', 'merk_tipe' => 'VIVENTE MEJA SISWA VSCD 37-01 PL', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 1363, 'harga_satuan' => 1114463.00, 'total_nilai' => 1519013069.00],
                ['paket_id' => 7, 'no_urut' => 5, 'nama_barang' => 'Meja Guru', 'jenis_bmn' => 'Meja Kerja Kayu', 'kode_bmn' => '3050201002', 'merk_tipe' => 'VIVENTE MEJA GURU VTED 12 HL-A PL', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 80, 'harga_satuan' => 2344320.00, 'total_nilai' => 187545600.00],
                ['paket_id' => 7, 'no_urut' => 6, 'nama_barang' => 'Kursi Guru', 'jenis_bmn' => 'Kursi Besi/Metal', 'kode_bmn' => '3050201003', 'merk_tipe' => 'KURSI VIVENTE HADAP V.950', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 80, 'harga_satuan' => 2317698.00, 'total_nilai' => 185415840.00],
                ['paket_id' => 7, 'no_urut' => 7, 'nama_barang' => 'Kursi Siswa', 'jenis_bmn' => 'Kursi Besi/Metal', 'kode_bmn' => '3050201003', 'merk_tipe' => 'VIVENTE KURSI SISWA VKS 32 PL', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 1363, 'harga_satuan' => 1033065.00, 'total_nilai' => 1408067595.00],

                // Paket 8 (8 item)
                ['paket_id' => 8, 'no_urut' => 2, 'nama_barang' => 'Lemari Besi', 'jenis_bmn' => 'Lemari Besi/Metal', 'kode_bmn' => '3050104001', 'merk_tipe' => 'RAK BUKU VIVENTE VHCM 02-2', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 33, 'harga_satuan' => 6549004.00, 'total_nilai' => 216117132.00],
                ['paket_id' => 8, 'no_urut' => 3, 'nama_barang' => 'White Board', 'jenis_bmn' => 'White Board', 'kode_bmn' => '3050105010', 'merk_tipe' => 'VIVENTE WHITEBOARD VWBP 01', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 53, 'harga_satuan' => 1766014.00, 'total_nilai' => 93598742.00],
                ['paket_id' => 8, 'no_urut' => 4, 'nama_barang' => 'Meja Siswa', 'jenis_bmn' => 'Meja Kerja Besi/Metal', 'kode_bmn' => '3050201001', 'merk_tipe' => 'VIVENTE MEJA SISWA VSCD 37-01 PL', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 1089, 'harga_satuan' => 1100709.00, 'total_nilai' => 1198672101.00],
                ['paket_id' => 8, 'no_urut' => 5, 'nama_barang' => 'Meja Guru (18 Unit)', 'jenis_bmn' => 'Meja Kerja Kayu', 'kode_bmn' => '3050201002', 'merk_tipe' => 'VIVENTE MEJA GURU VTED 12 HL-A PL', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 18, 'harga_satuan' => 3050280.00, 'total_nilai' => 54905040.00],
                ['paket_id' => 8, 'no_urut' => 6, 'nama_barang' => 'Meja Guru (119 Unit)', 'jenis_bmn' => 'Meja Kerja Kayu', 'kode_bmn' => '3050201002', 'merk_tipe' => 'VIVENTE MEJA GURU VTED 12 HL-A PL', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 119, 'harga_satuan' => 2886000.00, 'total_nilai' => 343434000.00],
                ['paket_id' => 8, 'no_urut' => 7, 'nama_barang' => 'Kursi Guru', 'jenis_bmn' => 'Kursi Besi/Metal', 'kode_bmn' => '3050201003', 'merk_tipe' => 'KURSI VIVENTE HADAP V.950', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 119, 'harga_satuan' => 2364999.00, 'total_nilai' => 281434881.00],
                ['paket_id' => 8, 'no_urut' => 8, 'nama_barang' => 'Kursi Siswa', 'jenis_bmn' => 'Kursi Besi/Metal', 'kode_bmn' => '3050201003', 'merk_tipe' => 'VIVENTE KURSI SISWA VKS 32 PL', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 1089, 'harga_satuan' => 1021200.00, 'total_nilai' => 1112086800.00],
                ['paket_id' => 8, 'no_urut' => 9, 'nama_barang' => 'Kursi Guru / Meeting', 'jenis_bmn' => 'Kursi Fiber Glas/Plastik', 'kode_bmn' => '3050201020', 'merk_tipe' => 'KURSI MEETING VIVENTE FORTUNE III GAR TP', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 18, 'harga_satuan' => 2458651.00, 'total_nilai' => 44255718.00],

                // Paket 9 (6 item)
                ['paket_id' => 9, 'no_urut' => 1, 'nama_barang' => 'Lemari Besi', 'jenis_bmn' => 'Lemari Besi/Metal', 'kode_bmn' => '3050104001', 'merk_tipe' => 'YOOM - LEMARI BUKU HCC 81', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 15, 'harga_satuan' => 6795414.00, 'total_nilai' => 101931210.00],
                ['paket_id' => 9, 'no_urut' => 2, 'nama_barang' => 'White Board', 'jenis_bmn' => 'White Board', 'kode_bmn' => '3050105010', 'merk_tipe' => 'YOOM - WHITEBOARD WBB 01', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 39, 'harga_satuan' => 2082794.00, 'total_nilai' => 81228966.00],
                ['paket_id' => 9, 'no_urut' => 3, 'nama_barang' => 'Meja Siswa', 'jenis_bmn' => 'Meja Kerja Besi/Metal', 'kode_bmn' => '3050201001', 'merk_tipe' => 'YOOM - MEJA SISWA MSS 73', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 1072, 'harga_satuan' => 1134185.00, 'total_nilai' => 1215846320.00],
                ['paket_id' => 9, 'no_urut' => 4, 'nama_barang' => 'Meja Guru', 'jenis_bmn' => 'Meja Kerja Kayu', 'kode_bmn' => '3050201002', 'merk_tipe' => 'YOOM - MEJA GURU MGR 37', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 111, 'harga_satuan' => 3064347.00, 'total_nilai' => 340142517.00],
                ['paket_id' => 9, 'no_urut' => 5, 'nama_barang' => 'Kursi Siswa', 'jenis_bmn' => 'Kursi Besi/Metal', 'kode_bmn' => '3050201003', 'merk_tipe' => 'YOOM - KURSI SISWA KRS 43', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 1072, 'harga_satuan' => 1066327.00, 'total_nilai' => 1143102544.00],
                ['paket_id' => 9, 'no_urut' => 6, 'nama_barang' => 'Kursi Guru', 'jenis_bmn' => 'Kursi Besi/Metal', 'kode_bmn' => '3050201003', 'merk_tipe' => 'YOOM - KURSI GURU KGG 24', 'tahun' => '2026', 'kondisi_baik' => 1, 'satuan' => 'Unit', 'jumlah' => 111, 'harga_satuan' => 2224208.00, 'total_nilai' => 246887088.00],
            ];
            foreach ($aspData as &$item) {
                $item['created_at'] = $now;
                $item['updated_at'] = $now;
            }
            unset($item);
            $db->table('trn_bmn_madrasah_asp')->insertBatch($aspData);
        }
    }

    public function down()
    {
        $forge = \Config\Database::forge();
        $forge->dropTable('trn_bmn_madrasah_asp', true);
        $forge->dropTable('trn_bmn_madrasah_sekolah', true);
        $forge->dropTable('trn_bmn_madrasah_paket', true);
    }
}
