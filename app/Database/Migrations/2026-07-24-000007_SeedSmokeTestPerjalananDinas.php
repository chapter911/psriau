<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SeedSmokeTestPerjalananDinas extends Migration
{
    public function up()
    {
        $db = $this->db;
        $db->disableForeignKeyChecks();

        // 1. Load Master Data
        $pegawaiRows = [];
        if ($db->tableExists('mst_pegawai')) {
            $pegawaiRows = $db->table('mst_pegawai')->select('id, nip, nama, golongan')->get()->getResultArray();
        }

        $kopSuratRows = [];
        if ($db->tableExists('kop_surat')) {
            $kopSuratRows = $db->table('kop_surat')->select('id')->get()->getResultArray();
        }

        $mataAnggaranRows = [];
        if ($db->tableExists('mst_mata_anggaran')) {
            $mataAnggaranRows = $db->table('mst_mata_anggaran')->select('id, mata_anggaran')->get()->getResultArray();
        }

        $dasarSptRows = [];
        if ($db->tableExists('mst_dasar_spt')) {
            $dasarSptRows = $db->table('mst_dasar_spt')->select('id, uraian')->get()->getResultArray();
        }

        $cities = [
            'Kota Pekanbaru', 'Kota Dumai', 'Kabupaten Kampar', 'Kabupaten Siak',
            'Kabupaten Bengkalis', 'Kabupaten Rokan Hulu', 'Kabupaten Rokan Hilir',
            'Kabupaten Indragiri Hulu', 'Kabupaten Indragiri Hilir', 'Kabupaten Kuantan Singingi',
            'Kabupaten Pelalawan', 'Kabupaten Kepulauan Meranti', 'Kota Jakarta Pusat',
            'Kota Bandung', 'Kota Medan', 'Kota Padang', 'Kota Batam', 'Kota Surabaya'
        ];

        $purposes = [
            'Monitoring & Evaluasi Pembangunan Sekolah Strategis',
            'Koordinasi Teknis Pelaksanaan Proyek Infrastruktur Publik',
            'Verifikasi Lapangan Dan Audit Fisik Bangunan Gedung',
            'Pengawasan Pekerjaan Konstruksi Tahap I',
            'Konsultasi Regional Perencanaan Prasarana Strategis',
            'Pendampingan Tim Penilai Kinerja Lapangan',
            'Rapat Koordinasi Anggaran Dan Pelaksanaan Kegiatan',
            'Inspeksi Keselamatan Dan Mutu Bangunan',
            'Evaluasi Kemajuan Progres Mingguan Fisik Pekerjaan',
            'Peninjauan Lokasi Rencana Pembangunan Gedung Sekolah',
            'Sosialisasi Petunjuk Teknis Dan Pembinaan Lapangan',
            'Koordinasi Bersama Pemdaerkah Dan Instansi Terkait',
            'Supervisi Lapangan Dan Penyesuaian Spesifikasi Teknis',
            'Verifikasi Dokumen Administrasi Dan Kwitansi Keuangan',
            'Pelaksanaan Workshop Dan Bimbingan Teknis Konstruksi'
        ];

        $creatorUser = 'admin';

        // Clean tables first
        if ($db->tableExists('laporan_perjalanan_dinas')) {
            $db->table('laporan_perjalanan_dinas')->truncate();
        }
        if ($db->tableExists('disposisi_perjalanan_dinas')) {
            $db->table('disposisi_perjalanan_dinas')->truncate();
        }

        $kasatkerPeg = !empty($pegawaiRows) ? $pegawaiRows[0] : ['id' => 1, 'nama' => 'Drs. H. Ahmad Fauzi, M.Si', 'nip' => '197501012000031001'];
        $ppkPeg     = count($pegawaiRows) > 1 ? $pegawaiRows[1] : $kasatkerPeg;

        $runningKodeNomor = 1;

        for ($i = 1; $i <= 25; $i++) {
            $city = $cities[($i - 1) % count($cities)];
            $purpose = $purposes[($i - 1) % count($purposes)] . ' - Paket #' . $i;

            // Pick 1 to 5 pelaksana
            $numPelaksana = ($i % 5) + 1; // 1, 2, 3, 4, 5 pelaksana
            $assignedPelaksana = [];

            if (!empty($pegawaiRows)) {
                for ($p = 0; $p < $numPelaksana; $p++) {
                    $pegIdx = ($i + $p) % count($pegawaiRows);
                    $assignedPelaksana[] = [
                        'id' => (int) $pegawaiRows[$pegIdx]['id'],
                        'nama' => (string) $pegawaiRows[$pegIdx]['nama'],
                        'nip' => (string) ($pegawaiRows[$pegIdx]['nip'] ?? '-'),
                        'jabatan' => (string) ($pegawaiRows[$pegIdx]['jabatan'] ?? 'Staf Pelaksana'),
                    ];
                }
            } else {
                $assignedPelaksana[] = [
                    'id' => 1,
                    'nama' => 'Budi Santoso, S.T.',
                    'nip' => '198805122015031002',
                    'jabatan' => 'Penata Muda (III/a)',
                ];
            }

            // Dates
            $startDay = str_pad((string) (($i % 25) + 1), 2, '0', STR_PAD_LEFT);
            $startDate = '2026-07-' . $startDay;
            $endDay = str_pad((string) min(28, (int)$startDay + ($i % 4) + 1), 2, '0', STR_PAD_LEFT);
            $endDate = '2026-07-' . $endDay;

            // 1. Insert Disposisi
            $disposisiData = [
                'tujuan' => $purpose,
                'kota_tujuan' => $city,
                'periode_mulai' => $startDate,
                'periode_selesai' => $endDate,
                'pelaksana_json' => json_encode($assignedPelaksana, JSON_UNESCAPED_UNICODE),
                'diketahui_pegawai_id' => (int) ($kasatkerPeg['id'] ?? 1),
                'menyetujui_pegawai_id' => (int) ($ppkPeg['id'] ?? 1),
                'status' => 'disetujui',
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            $db->table('disposisi_perjalanan_dinas')->insert($disposisiData);
            $disposisiId = $db->insertID();

            // Calculate transport & penginapan
            $tNom = 150000 + (($i * 25000) % 350000);
            $pNom = 400000 + (($i * 50000) % 600000);

            $rincianBiaya = [
                'transport' => [
                    [
                        'tgl_mulai' => $startDate,
                        'tgl_selesai' => $endDate,
                        'nominal' => $tNom,
                        'keterangan' => 'Sewa Kendaraan & Bensin ' . $city,
                    ]
                ],
                'penginapan' => [
                    [
                        'tgl_mulai' => $startDate,
                        'tgl_selesai' => $endDate,
                        'nominal' => $pNom,
                        'keterangan' => 'Hotel Transit ' . $city,
                    ]
                ],
            ];

            // Dasar SPT IDs
            $dasarIds = [];
            if (!empty($dasarSptRows)) {
                $dIdx1 = ($i - 1) % count($dasarSptRows);
                $dasarIds[] = (string) $dasarSptRows[$dIdx1]['id'];
            } else {
                $dasarIds[] = 'Surat Perintah Kepala Satuan Kerja PPS Kementerian PU No. ' . $i . '/ST/2026';
            }

            // Kode Nomor (Formatted 3 digits)
            $kodeNomorStr = str_pad((string) $runningKodeNomor, 3, '0', STR_PAD_LEFT);
            $runningKodeNomor += count($assignedPelaksana);

            $nomorSpt = str_pad((string) $i, 3, '0', STR_PAD_LEFT) . '/SPT/PPS/' . date('Y');

            $kopId = !empty($kopSuratRows) ? (int) $kopSuratRows[($i - 1) % count($kopSuratRows)]['id'] : 1;
            $maId  = !empty($mataAnggaranRows) ? (int) $mataAnggaranRows[($i - 1) % count($mataAnggaranRows)]['id'] : 1;

            // 2. Insert Laporan Perjalanan Dinas
            $laporanData = [
                'disposisi_id' => $disposisiId,
                'tujuan' => $purpose,
                'kota_tujuan' => $city,
                'periode_mulai' => $startDate,
                'periode_selesai' => $endDate,
                'pelaksana_json' => json_encode($assignedPelaksana, JSON_UNESCAPED_UNICODE),
                'nomor_surat_tugas' => $nomorSpt,
                'kode_nomor' => $kodeNomorStr,
                'dasar_spt_ids_json' => json_encode($dasarIds, JSON_UNESCAPED_UNICODE),
                'tanggal_tanda_tangan' => $startDate,
                'kop_surat_id' => $kopId,
                'mata_anggaran_id' => $maId,
                'rincian_biaya_json' => json_encode($rincianBiaya, JSON_UNESCAPED_UNICODE),
                'sasaran' => 'Terlaksananya peninjauan lokasi dan verifikasi fisik di ' . $city,
                'laporan_hasil' => 'Telah dilakukan koordinasi dan peninjauan lapangan di ' . $city . '. Hasil pemeriksaan menunjukkan seluruh indikator pekerjaan berjalan sesuai spesifikasi teknis dan jadwal.',
                'foto_dokumentasi_json' => json_encode([
                    [
                        'name' => 'Foto Kegiatan 1 - ' . $city,
                        'file_path' => 'uploads/laporan/perjalanan_dinas/sample_photo.jpg',
                        'keterangan' => 'Peninjauan Lokasi Proyek di ' . $city,
                    ]
                ], JSON_UNESCAPED_UNICODE),
                'dokumen_pendukung_json' => json_encode([
                    [
                        'name' => 'Struk Transportasi ' . $city,
                        'file_path' => 'uploads/laporan/perjalanan_dinas/sample_ticket.pdf',
                        'keterangan' => 'Struk Tol & BBM Perjalanan Dinas',
                    ]
                ], JSON_UNESCAPED_UNICODE),
                'is_final' => 1,
                'is_verified' => 1,
                'creator_name' => $creatorUser,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $db->table('laporan_perjalanan_dinas')->insert($laporanData);
        }

        // Update app_settings last_kode_nomor_sppd to final used number
        $finalLastNumber = $runningKodeNomor - 1;
        if ($db->tableExists('app_settings')) {
            $existing = $db->table('app_settings')->get()->getRowArray();
            if (is_array($existing)) {
                $db->table('app_settings')->update(['last_kode_nomor_sppd' => $finalLastNumber]);
            } else {
                $db->table('app_settings')->insert(['last_kode_nomor_sppd' => $finalLastNumber]);
            }
        }

        $db->enableForeignKeyChecks();
    }

    public function down()
    {
    }
}
