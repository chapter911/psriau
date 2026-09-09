<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\MstRuanganModel;
use App\Models\InventarisSatkerModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ImportSimanDbr extends BaseCommand
{
    protected $group = 'Inventaris';
    protected $name = 'inventaris:import-siman';
    protected $description = 'Mengimpor seluruh data aset dari daftar-aset-1.xlsx ke database dan mengalokasikannya ke DBR Rekap_Daftar_Barang_Ruangan_PU.xlsx.';

    public function run(array $params)
    {
        $db = db_connect();
        ini_set('memory_limit', '1024M');
        set_time_limit(600);

        CLI::write("================================================================", "yellow");
        CLI::write("   PROSES IMPORT ASET BMN SIMAN & SINKRONISASI DBR RUANGAN    ", "green");
        CLI::write("================================================================", "yellow");

        $simanFile = ROOTPATH . 'do_not_upload/daftar-aset-1.xlsx';
        $dbrFile   = ROOTPATH . 'do_not_upload/Rekap_Daftar_Barang_Ruangan_PU.xlsx';

        if (! file_exists($simanFile)) {
            CLI::error("File {$simanFile} tidak ditemukan!");
            return;
        }

        // =====================================================================
        // BAGIAN 1: Import Seluruh Data dari daftar-aset-1.xlsx
        // =====================================================================
        CLI::write("\n[LANGKAH 1] Membaca dan mengimpor data dari daftar-aset-1.xlsx...", "cyan");

        $reader = IOFactory::createReaderForFile($simanFile);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($simanFile);
        $sheet = $spreadsheet->getSheetByName('Master Aset') ?: $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        CLI::write("  Total baris terdeteksi pada sheet Master Aset: {$highestRow}", "yellow");

        // Preload map data existing di trn_inventaris_satker
        $existingRows = $db->table('trn_inventaris_satker')
            ->select('id, kode_barang, nup, ruangan_id')
            ->get()
            ->getResultArray();

        $existingMap = [];
        foreach ($existingRows as $er) {
            $key = trim((string) $er['kode_barang']) . '___' . (int) $er['nup'];
            $existingMap[$key] = [
                'id'         => (int) $er['id'],
                'ruangan_id' => (int) ($er['ruangan_id'] ?? 0),
            ];
        }

        $insertedCount = 0;
        $updatedCount  = 0;
        $now = date('Y-m-d H:i:s');
        $batchInsert = [];

        for ($r = 3; $r <= $highestRow; $r++) {
            $kodeBarang   = trim((string) $sheet->getCell('E' . $r)->getValue());
            $rawNup       = $sheet->getCell('F' . $r)->getValue();
            $kodeRegister = trim((string) $sheet->getCell('BT' . $r)->getValue());
            $namaBarang   = trim((string) $sheet->getCell('G' . $r)->getValue());

            if ($kodeBarang === '' && $namaBarang === '') {
                continue;
            }

            $nup = is_numeric($rawNup) ? (int) $rawNup : (int) preg_replace('/\D/', '', (string) $rawNup);

            $kategori   = trim((string) $sheet->getCell('B' . $r)->getValue()) ?: 'Peralatan dan Mesin';
            $statusBmn  = trim((string) $sheet->getCell('H' . $r)->getValue()) ?: 'Digunakan Sendiri';
            $merk       = trim((string) $sheet->getCell('I' . $r)->getValue());
            $tipe       = trim((string) $sheet->getCell('J' . $r)->getValue());
            $rawKondisi = trim((string) $sheet->getCell('K' . $r)->getValue());

            // Normalisasi kondisi
            $kondisi = 'Baik';
            if (stripos($rawKondisi, 'rusak ringan') !== false || strtoupper($rawKondisi) === 'RR') {
                $kondisi = 'Rusak Ringan';
            } elseif (stripos($rawKondisi, 'rusak berat') !== false || strtoupper($rawKondisi) === 'RB') {
                $kondisi = 'Rusak Berat';
            }

            // Normalisasi merk_tipe
            $merkTipe = '';
            if ($merk !== '' && $tipe !== '') {
                $merkTipe = ($merk === $tipe) ? $merk : ($merk . ' ' . $tipe);
            } elseif ($merk !== '') {
                $merkTipe = $merk;
            } else {
                $merkTipe = $tipe;
            }

            // Tanggal dan tahun perolehan
            $rawTgl = $sheet->getCell('AH' . $r)->getValue();
            $tglPerolehan = null;
            $tahunPerolehan = date('Y');

            if (is_numeric($rawTgl) && (float) $rawTgl > 1000) {
                try {
                    $dt = Date::excelToDateTimeObject((float) $rawTgl);
                    $tglPerolehan = $dt->format('Y-m-d');
                    $tahunPerolehan = (int) $dt->format('Y');
                } catch (\Throwable $e) {
                    $tglPerolehan = null;
                }
            } elseif (is_string($rawTgl) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawTgl)) {
                $tglPerolehan = $rawTgl;
                $tahunPerolehan = (int) substr($rawTgl, 0, 4);
            }

            $nilaiPerolehan = (float) ($sheet->getCell('AL' . $r)->getValue() ?? 0);
            $nilaiBuku      = (float) ($sheet->getCell('AN' . $r)->getValue() ?? 0);
            $noPsp          = trim((string) $sheet->getCell('AY' . $r)->getValue());

            // Tentukan satuan
            $satuan = 'Buah';
            if (stripos($kategori, 'angkutan') !== false || stripos($namaBarang, 'mobil') !== false || stripos($namaBarang, 'motor') !== false) {
                $satuan = 'Unit';
            } elseif (stripos($kategori, 'tanah') !== false) {
                $satuan = 'M2';
            } elseif (stripos($kategori, 'gedung') !== false || stripos($kategori, 'bangunan') !== false) {
                $satuan = 'Unit';
            }

            $record = [
                'kode_barang'     => $kodeBarang,
                'nup'             => $nup,
                'kode_register'   => $kodeRegister ?: null,
                'nama_barang'     => $namaBarang,
                'kategori'        => $kategori,
                'merk'            => $merk ?: null,
                'tipe'            => $tipe ?: null,
                'merk_tipe'       => $merkTipe ?: null,
                'jumlah'          => 1,
                'satuan'          => $satuan,
                'kondisi'         => $kondisi,
                'lokasi_ruangan'  => 'Kantor Satker PPS Riau',
                'nilai_perolehan' => $nilaiPerolehan,
                'nilai_buku'      => $nilaiBuku,
                'status_bmn'      => $statusBmn,
                'no_psp'          => $noPsp ?: null,
                'tahun_perolehan' => $tahunPerolehan,
                'tgl_perolehan'   => $tglPerolehan,
                'updated_at'      => $now,
            ];

            $lookupKey = $kodeBarang . '___' . $nup;
            if (isset($existingMap[$lookupKey])) {
                $existingItem = $existingMap[$lookupKey];
                if (! empty($existingItem['ruangan_id'])) {
                    unset($record['lokasi_ruangan']);
                }
                $db->table('trn_inventaris_satker')->where('id', $existingItem['id'])->update($record);
                $updatedCount++;
            } else {
                $record['created_at'] = $now;
                $batchInsert[] = $record;
                $insertedCount++;

                if (count($batchInsert) >= 200) {
                    $db->table('trn_inventaris_satker')->insertBatch($batchInsert);
                    $batchInsert = [];
                }
            }
        }

        if (! empty($batchInsert)) {
            $db->table('trn_inventaris_satker')->insertBatch($batchInsert);
        }

        CLI::write("  [OK] Berhasil mengimpor aset: {$insertedCount} data baru, {$updatedCount} data diperbarui!", "green");

        // =====================================================================
        // BAGIAN 2: Buat Master Ruangan & Alokasikan 30 Barang DBR
        // =====================================================================
        CLI::write("\n[LANGKAH 2] Memproses Master Ruangan dan mengalokasikan barang DBR...", "cyan");

        // Ambil penanggung jawab dari mst_pegawai jika ada
        $pjNama = 'Eko Prasetyo, S.T.';
        $pjNip  = '198504122010121003';
        $pjId   = null;

        if ($db->tableExists('mst_pegawai')) {
            $pegawai = $db->table('mst_pegawai')
                ->select('id, nama, nip')
                ->where('is_active', 1)
                ->orderBy('nama', 'ASC')
                ->get()
                ->getRowArray();

            if ($pegawai) {
                $pjNama = $pegawai['nama'];
                $pjNip  = $pegawai['nip'] ?: '198504122010121003';
                $pjId   = (int) $pegawai['id'];
            }
        }

        // Cari atau buat ruangan DBR
        $ruanganKode = 'RUANG-001';
        $ruanganNama = 'Ruang Tata Usaha & Staf Pelaksana';

        $existingRuangan = $db->table('mst_ruangan')
            ->where('kode_ruangan', $ruanganKode)
            ->orWhere('nama_ruangan', $ruanganNama)
            ->get()
            ->getRowArray();

        if ($existingRuangan) {
            $ruanganId = (int) $existingRuangan['id'];
            $db->table('mst_ruangan')->where('id', $ruanganId)->update([
                'penanggung_jawab_nama' => $pjNama,
                'penanggung_jawab_nip'  => $pjNip,
                'pegawai_id'            => $pjId,
                'lokasi_lantai'         => 'Lantai 1 - Gedung Kantor Satker PPS Riau',
                'updated_at'            => $now,
            ]);
            CLI::write("  Ruangan {$ruanganKode} ({$ruanganNama}) sudah ada (ID: {$ruanganId}), diperbarui penanggung jawab: {$pjNama}", "yellow");
        } else {
            $db->table('mst_ruangan')->insert([
                'kode_ruangan'          => $ruanganKode,
                'nama_ruangan'          => $ruanganNama,
                'lokasi_lantai'         => 'Lantai 1 - Gedung Kantor Satker PPS Riau',
                'pegawai_id'            => $pjId,
                'penanggung_jawab_nama' => $pjNama,
                'penanggung_jawab_nip'  => $pjNip,
                'keterangan'            => 'Ruangan DBR Utama hasil sinkronisasi Rekap_Daftar_Barang_Ruangan_PU.xlsx',
                'created_at'            => $now,
                'updated_at'            => $now,
            ]);
            $ruanganId = $db->insertID();
            CLI::write("  [OK] Ruangan baru berhasil dibuat: [{$ruanganKode}] {$ruanganNama} (ID: {$ruanganId})", "green");
        }

        // 7 Item target DBR sesuai Rekap_Daftar_Barang_Ruangan_PU.xlsx
        $dbrTargets = [
            [
                'kode_barang'   => '3050104001',
                'nama_barang'   => 'Lemari Besi/Metal',
                'merk_keyword'  => 'AS 88',
                'qty'           => 2,
            ],
            [
                'kode_barang'   => '3050201002',
                'nama_barang'   => 'Meja Kerja Kayu',
                'merk_keyword'  => 'Orbitrend',
                'qty'           => 12,
            ],
            [
                'kode_barang'   => '3050201020',
                'nama_barang'   => 'Kursi Fiber Glas/Plastik',
                'merk_keyword'  => 'Valmon',
                'qty'           => 12,
            ],
            [
                'kode_barang'   => '3100203003',
                'nama_barang'   => 'Printer (Peralatan Personal Komputer)',
                'merk_keyword'  => 'L3251',
                'qty'           => 1,
            ],
            [
                'kode_barang'   => '3050204004',
                'nama_barang'   => 'A.C. Split',
                'merk_keyword'  => 'QN18AKJ',
                'qty'           => 1,
            ],
            [
                'kode_barang'   => '3100102001',
                'nama_barang'   => 'P.C Unit',
                'merk_keyword'  => 'VN4/0016',
                'qty'           => 1,
            ],
            [
                'kode_barang'   => '3050104003',
                'nama_barang'   => 'Rak Besi',
                'merk_keyword'  => 'Arsip',
                'qty'           => 1,
            ],
        ];

        $totalAllocated = 0;

        foreach ($dbrTargets as $t) {
            $builder = $db->table('trn_inventaris_satker')
                ->where('kode_barang', $t['kode_barang']);

            if (! empty($t['merk_keyword'])) {
                $builder->like('merk_tipe', $t['merk_keyword']);
            }

            $matchedItems = $builder->orderBy('nup', 'ASC')
                ->limit($t['qty'])
                ->get()
                ->getResultArray();

            $allocatedForThis = 0;
            foreach ($matchedItems as $item) {
                $db->table('trn_inventaris_satker')
                    ->where('id', $item['id'])
                    ->update([
                        'ruangan_id'     => $ruanganId,
                        'lokasi_ruangan' => $ruanganNama,
                        'updated_at'     => $now,
                    ]);
                $allocatedForThis++;
                $totalAllocated++;
            }

            CLI::write("  - [{$t['kode_barang']}] {$t['nama_barang']} (Target: {$t['qty']}) -> Berhasil dialokasikan: {$allocatedForThis} unit", "green");
        }

        CLI::write("  [OK] Total {$totalAllocated} unit barang berhasil dialokasikan ke ruangan '{$ruanganNama}'!", "green");

        // =====================================================================
        // BAGIAN 3: Update file do_not_upload/Rekap_Daftar_Barang_Ruangan_PU.xlsx
        // =====================================================================
        if (file_exists($dbrFile)) {
            CLI::write("\n[LANGKAH 3] Memperbarui metadata ruangan pada Rekap_Daftar_Barang_Ruangan_PU.xlsx...", "cyan");
            try {
                $dbrSpreadsheet = IOFactory::load($dbrFile);
                $dbrSheet = $dbrSpreadsheet->getActiveSheet();

                // Isi Nama Ruangan dan Kode Ruangan
                $dbrSheet->setCellValue('E7', ': ' . $ruanganNama);
                $dbrSheet->setCellValue('E8', ': ' . $ruanganKode);

                // Isi Penanggung Jawab Ruangan pada kolom E28 dan E29
                $dbrSheet->setCellValue('E28', $pjNama);
                $dbrSheet->setCellValue('E29', 'NIP. ' . $pjNip);

                $writer = IOFactory::createWriter($dbrSpreadsheet, 'Xlsx');
                $writer->save($dbrFile);

                CLI::write("  [OK] File {$dbrFile} berhasil diperbarui dengan Kode Ruangan ({$ruanganKode}), Nama Ruangan, dan Penanggung Jawab ({$pjNama})!", "green");
            } catch (\Throwable $e) {
                CLI::error("  [FAIL] Gagal memperbarui file Excel DBR: " . $e->getMessage());
            }
        }

        // =====================================================================
        // SUMMARY
        // =====================================================================
        $satkerModel = new InventarisSatkerModel();
        $stats = $satkerModel->getSummaryStats();

        CLI::write("\n================================================================", "yellow");
        CLI::write("   RINGKASAN AKHIR INVENTARISASI SATKER PPS RIAU              ", "green");
        CLI::write("================================================================", "yellow");
        CLI::write("  Total Seluruh Barang Terdaftar: " . number_format($stats['total_barang']) . " Aset", "cyan");
        CLI::write("  Kondisi Baik                  : " . number_format($stats['kondisi_baik']) . " Aset", "cyan");
        CLI::write("  Kondisi Rusak Ringan          : " . number_format($stats['rusak_ringan']) . " Aset", "cyan");
        CLI::write("  Kondisi Rusak Berat           : " . number_format($stats['rusak_berat']) . " Aset", "cyan");
        CLI::write("  Sudah Masuk Ruangan (DBR)     : " . number_format($stats['terdistribusi']) . " Aset", "green");
        CLI::write("  Belum Masuk Ruangan           : " . number_format($stats['belum_terdistribusi']) . " Aset", "yellow");
        CLI::write("================================================================\n", "yellow");
    }
}
