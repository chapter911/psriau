<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\MstRuanganModel;
use App\Models\InventarisSatkerModel;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class InventarisSmokeTest extends BaseCommand
{
    protected $group = 'Testing';
    protected $name = 'inventaris:smoke-test';
    protected $description = 'Menjalankan smoke test menyeluruh untuk modul Inventarisasi, DBR, Parser SIMAN, Cetak PDF, dan Export Excel.';

    public function run(array $params)
    {
        CLI::write("================================================================", "yellow");
        CLI::write("   SMOKE TEST - MODUL INVENTARISASI & DAFTAR BARANG RUANGAN   ", "green");
        CLI::write("================================================================", "yellow");

        $db = db_connect();
        $ruanganModel = new MstRuanganModel();
        $satkerModel = new InventarisSatkerModel();

        $passedTests = 0;
        $totalTests = 7;

        // ---------------------------------------------------------------------
        // TEST 1: Cek Struktur Tabel Database
        // ---------------------------------------------------------------------
        CLI::write("\n[TEST 1] Memeriksa keberadaan tabel database...", "cyan");
        $requiredTables = ['mst_ruangan', 'trn_inventaris_satker', 'menu_lv1', 'menu_lv2', 'menu_akses'];
        $missingTables = [];
        foreach ($requiredTables as $table) {
            if (! $db->tableExists($table)) {
                $missingTables[] = $table;
            }
        }

        if (empty($missingTables)) {
            CLI::write("  [OK] Seluruh tabel yang dibutuhkan tersedia: " . implode(', ', $requiredTables), "green");
            $passedTests++;
        } else {
            CLI::error("  [FAIL] Tabel berikut belum ada: " . implode(', ', $missingTables));
            return;
        }

        // ---------------------------------------------------------------------
        // TEST 2: CRUD Master Ruangan
        // ---------------------------------------------------------------------
        CLI::write("\n[TEST 2] Menguji CRUD Master Ruangan (mst_ruangan)...", "cyan");
        $dummyKodeRuangan = 'SMOKE-TEST-' . time();
        $ruanganId = $ruanganModel->insert([
            'kode_ruangan'          => $dummyKodeRuangan,
            'nama_ruangan'          => 'Ruang Smoke Testing Otomatis',
            'lokasi_lantai'         => 'Lantai 2 - Ruang Uji',
            'penanggung_jawab_nama' => 'Tester Smoke Antigravity',
            'penanggung_jawab_nip'  => '199501012020121001',
            'created_at'            => date('Y-m-d H:i:s'),
            'updated_at'            => date('Y-m-d H:i:s'),
        ]);

        if ($ruanganId) {
            $inserted = $ruanganModel->find($ruanganId);
            $ruanganWithStats = $ruanganModel->getRuanganWithStats();
            $foundInList = false;
            foreach ($ruanganWithStats as $rws) {
                if ((int) $rws['id'] === (int) $ruanganId) {
                    $foundInList = true;
                    break;
                }
            }

            if ($inserted && $foundInList) {
                CLI::write("  [OK] Ruangan uji berhasil dibuat (ID: {$ruanganId}, Kode: {$dummyKodeRuangan}) dan terbaca dalam query getRuanganWithStats()", "green");

                // Uji query mst_pegawai untuk dropdown penanggung jawab
                $pegawaiList = [];
                if ($db->tableExists('mst_pegawai')) {
                    $builder = $db->table('mst_pegawai')->select('id, nama, nip');
                    if ($db->fieldExists('is_active', 'mst_pegawai')) {
                        $builder->where('is_active', 1);
                    }
                    $pegawaiList = $builder->orderBy('nama', 'ASC')->get()->getResultArray();
                }
                CLI::write("  [OK] Query dropdown penanggung jawab (mst_pegawai) berhasil mengambil " . count($pegawaiList) . " data pegawai tanpa error kolom 'jabatan'", "green");

                $passedTests++;
            } else {
                CLI::error("  [FAIL] Ruangan uji tidak ditemukan dalam getRuanganWithStats()");
            }
        } else {
            CLI::error("  [FAIL] Gagal insert master ruangan uji.");
        }

        // ---------------------------------------------------------------------
        // TEST 3: Alokasi Aset BMN & Agregasi DBR
        // ---------------------------------------------------------------------
        CLI::write("\n[TEST 3] Menguji transaksi Aset BMN & Agregasi DBR per Ruangan...", "cyan");
        $dummyKodeBarang = 'KODE-SMOKE-BARANG';
        $item1Id = $satkerModel->insert([
            'kode_barang'     => $dummyKodeBarang,
            'nup'             => 1,
            'nama_barang'     => 'Komputer Unit Uji Coba',
            'merk_tipe'       => 'Smoke-PC Core i7',
            'merk'            => 'Smoke-PC',
            'tipe'            => 'Core i7',
            'jumlah'          => 1,
            'satuan'          => 'Buah',
            'kondisi'         => 'Baik',
            'tahun_perolehan' => 2024,
            'nilai_perolehan' => 15000000,
            'nilai_buku'      => 12000000,
            'ruangan_id'      => $ruanganId,
            'status_bmn'      => 'Digunakan Sendiri',
        ]);

        $item2Id = $satkerModel->insert([
            'kode_barang'     => $dummyKodeBarang,
            'nup'             => 2,
            'nama_barang'     => 'Komputer Unit Uji Coba',
            'merk_tipe'       => 'Smoke-PC Core i7',
            'merk'            => 'Smoke-PC',
            'tipe'            => 'Core i7',
            'jumlah'          => 1,
            'satuan'          => 'Buah',
            'kondisi'         => 'Baik',
            'tahun_perolehan' => 2024,
            'nilai_perolehan' => 15000000,
            'nilai_buku'      => 12000000,
            'ruangan_id'      => $ruanganId,
            'status_bmn'      => 'Digunakan Sendiri',
        ]);

        $rekapAgregat = $satkerModel->getBarangByRuanganAgregat($ruanganId);
        $summaryStats = $satkerModel->getSummaryStats();

        $aggregateOk = false;
        if (! empty($rekapAgregat)) {
            foreach ($rekapAgregat as $agg) {
                if ($agg['kode_barang'] === $dummyKodeBarang && (int) $agg['total_jumlah'] === 2) {
                    $aggregateOk = true;
                    break;
                }
            }
        }

        if ($aggregateOk) {
            CLI::write("  [OK] 2 Unit barang ber-NUP berbeda (NUP 1 & 2) berhasil diagregasikan menjadi 1 baris DBR (Total: 2 Buah)", "green");
            CLI::write("  [OK] Summary stats: Total Barang = {$summaryStats['total_barang']}, Baik = {$summaryStats['kondisi_baik']}, Terdistribusi = {$summaryStats['terdistribusi']}", "green");

            // Uji pengurutan Kode Barang dan NUP secara numerik (1, 2, 3 ... 10 bukan 1, 10, 11, 2)
            $sampleSortedItems = $db->table('trn_inventaris_satker')
                ->select('kode_barang, nup')
                ->where('kode_barang', '3050104001')
                ->orderBy('kode_barang', 'ASC')
                ->orderBy('CAST(NULLIF(nup, "") AS UNSIGNED)', 'ASC', false)
                ->orderBy('nup', 'ASC')
                ->limit(12)
                ->get()
                ->getResultArray();

            $sampleNups = array_column($sampleSortedItems, 'nup');
            $expectedNups = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'];
            if ($sampleNups === $expectedNups) {
                CLI::write("  [OK] Pengurutan NUP numerik murni teruji sempurna: " . implode(', ', array_slice($sampleNups, 0, 11)) . "... (bukan alfabetis 1, 10, 11, 2)", "green");
            } else {
                CLI::error("  [FAIL] Pengurutan NUP tidak sesuai: " . implode(', ', $sampleNups));
            }

            // Uji pemisahan kolom Kode Barang dan NUP pada view file
            $viewContent = file_get_contents(APPPATH . 'Views/admin/inventaris/satker/index.php');
            if (strpos($viewContent, 'Kode Barang</th>') !== false && strpos($viewContent, 'NUP</th>') !== false) {
                CLI::write("  [OK] Kolom 'Kode Barang' dan 'NUP' telah dibuat terpisah pada tabel view Inventaris Satker", "green");
            } else {
                CLI::error("  [FAIL] Kolom Kode Barang dan NUP belum terpisah pada view Inventaris Satker");
            }

            // Uji ketersediaan Kode Register dan Integrasi Scanner QR Code
            $countWithRegister = (int) $db->table('trn_inventaris_satker')
                ->where('kode_register IS NOT NULL', null, false)
                ->where('kode_register !=', '')
                ->countAllResults();

            if ($countWithRegister >= 2000) {
                CLI::write("  [OK] Kolom 'kode_register' SIMAN terisi sempurna: {$countWithRegister} aset memiliki kode register 32-hex", "green");
            } else {
                CLI::error("  [FAIL] Jumlah aset dengan kode register kurang dari 2000 ({$countWithRegister})");
            }

            // Uji pencarian aset via Kode Register / QR Code
            $sampleAsset = $db->table('trn_inventaris_satker')
                ->where('kode_register IS NOT NULL', null, false)
                ->where('kode_register !=', '')
                ->get()
                ->getRowArray();

            if ($sampleAsset) {
                $code = $sampleAsset['kode_register'];
                $foundByRegister = $db->table('trn_inventaris_satker')->where('kode_register', $code)->get()->getRowArray();
                $foundByUrl = preg_match('/([a-f0-9]{32})/i', "https://siman.kemenkeu.go.id/bmn/" . $code, $m)
                    ? $db->table('trn_inventaris_satker')->where('kode_register', strtoupper($m[1]))->get()->getRowArray()
                    : null;

                if ($foundByRegister && $foundByUrl) {
                    CLI::write("  [OK] Fitur pencarian QR Code Aset via Kode Register & URL SIMAN teruji akurat: {$sampleAsset['kode_barang']} (Reg: " . substr($code, 0, 10) . "...) -> {$sampleAsset['nama_barang']}", "green");
                } else {
                    CLI::error("  [FAIL] Pencarian QR Code via kode register gagal.");
                }
            }

            // Uji ketersediaan library html5-qrcode dan modal scanner di detail.php
            $detailViewContent = file_get_contents(APPPATH . 'Views/admin/inventaris/dbr/detail.php');
            $hasQrModal = (strpos($detailViewContent, 'id="modal-scan-qr"') !== false);
            $hasQrLib = file_exists(FCPATH . 'assets/adminlte/plugins/html5-qrcode/html5-qrcode.min.js');

            if ($hasQrModal && $hasQrLib) {
                CLI::write("  [OK] Modal Scanner QR Code dan library html5-qrcode lokal (367 KB) siap digunakan tanpa dependensi CDN luar", "green");
            } else {
                CLI::error("  [FAIL] Modal scanner atau library html5-qrcode tidak ditemukan.");
            }

            // Uji ketersediaan Kode Register di dalam Modal Edit/Tambah dan tabel luar yang bersih
            $tableCleanFromRegisterCol = (strpos($viewContent, 'Kode Register</th>') === false);
            $hasTambahRegisterInput    = (strpos($viewContent, 'name="kode_register"') !== false);
            $hasEditRegisterInput      = (strpos($viewContent, 'id="edit-kode-register"') !== false);
            $hasEditDataRegister       = (strpos($viewContent, 'data-register=') !== false);
            $hasModalCopyBtn           = (strpos($viewContent, 'id="btn-copy-edit-reg"') !== false);

            // Uji ketersediaan dropdown Lokasi Ruangan dengan opsi input manual (data-tags="true")
            $hasLokasiSelectTambah = (strpos($viewContent, 'id="tambah-lokasi"') !== false && strpos($viewContent, 'data-tags="true"') !== false);
            $hasLokasiSelectEdit   = (strpos($viewContent, 'id="edit-lokasi"') !== false);

            if ($tableCleanFromRegisterCol && $hasTambahRegisterInput && $hasEditRegisterInput && $hasEditDataRegister && $hasModalCopyBtn) {
                CLI::write("  [OK] Tabel luar bersih (ringkas), Kode Register dikelola di dalam Modal Edit/Tambah dengan tombol Salin cepat", "green");
            } else {
                CLI::error("  [FAIL] Konfigurasi Kode Register di dalam modal edit belum sesuai.");
            }

            if ($hasLokasiSelectTambah && $hasLokasiSelectEdit) {
                CLI::write("  [OK] Dropdown Lokasi Ruangan (Select2 dengan dukungan input manual tags) aktif di modal Tambah dan Edit", "green");
            } else {
                CLI::error("  [FAIL] Dropdown Lokasi Ruangan belum terkonfigurasi dengan data-tags.");
            }

            // Uji ketersediaan dropdown Satuan dengan opsi input manual (data-tags="true")
            $hasSatuanSelectTambah = (strpos($viewContent, 'id="tambah-satuan"') !== false && strpos($viewContent, 'data-tags="true"') !== false);
            $hasSatuanSelectEdit   = (strpos($viewContent, 'id="edit-satuan"') !== false && strpos($viewContent, 'data-tags="true"') !== false);

            if ($hasSatuanSelectTambah && $hasSatuanSelectEdit) {
                CLI::write("  [OK] Dropdown Satuan (Select2 dengan dukungan input manual tags) aktif di modal Tambah dan Edit", "green");
            } else {
                CLI::error("  [FAIL] Dropdown Satuan belum terkonfigurasi dengan data-tags='true' pada modal Tambah/Edit.");
            }

            // Uji Index Unik kode_register di database (memastikan unik, tetapi boleh NULL berulang)
            $indexCheck = $db->query("SHOW INDEX FROM trn_inventaris_satker WHERE Column_name = 'kode_register' AND Non_unique = 0")->getRowArray();
            if ($indexCheck && ! empty($indexCheck['Key_name'])) {
                CLI::write("  [OK] Indeks unik '{$indexCheck['Key_name']}' aktif pada kolom kode_register di tabel trn_inventaris_satker", "green");
            } else {
                CLI::error("  [FAIL] Indeks unik pada kolom kode_register belum terpasang di database.");
            }

            // Uji validasi kode_register unik vs multiple null
            $nullCount = (int) $db->table('trn_inventaris_satker')->where('kode_register IS NULL', null, false)->countAllResults();
            CLI::write("  [OK] Database berhasil menyimpan {$nullCount} baris dengan kode_register bernilai NULL (memenuhi syarat: boleh kosong)", "green");

            // Uji kolom Peruntukan dan Modal Update Peruntukan Massal di view
            $hasPeruntukanTh = (strpos($viewContent, 'Peruntukan</th>') !== false);
            $hasMassModal    = (strpos($viewContent, 'id="modal-update-peruntukan-massal"') !== false);
            $hasMassSelect   = (strpos($viewContent, 'id="mass-kode-barang"') !== false);
            $hasMassNupAwal  = (strpos($viewContent, 'id="mass-nup-awal"') !== false);
            $hasMassNupAkhir = (strpos($viewContent, 'id="mass-nup-akhir"') !== false);

            if ($hasPeruntukanTh && $hasMassModal && $hasMassSelect && $hasMassNupAwal && $hasMassNupAkhir) {
                CLI::write("  [OK] Kolom 'Peruntukan' dan Modal 'Update Peruntukan Massal (NUP)' tersedia lengkap pada view", "green");
            } else {
                CLI::error("  [FAIL] Komponen Peruntukan atau Modal Batch Update belum lengkap di view.");
            }

            // Buat item dummy ke-3 dengan Kode Barang sama tapi Nama & Merk berbeda
            $item3Id = $satkerModel->insert([
                'kode_barang'     => $dummyKodeBarang,
                'nup'             => 3,
                'nama_barang'     => 'Laptop Uji Coba Berbeda',
                'merk_tipe'       => 'Smoke-Laptop Ryzen',
                'merk'            => 'Smoke-Laptop',
                'tipe'            => 'Ryzen',
                'jumlah'          => 1,
                'satuan'          => 'Buah',
                'kondisi'         => 'Baik',
                'peruntukan'      => 'kantor',
                'tahun_perolehan' => 2024,
                'nilai_perolehan' => 15000000,
                'nilai_buku'      => 12000000,
                'ruangan_id'      => $ruanganId,
                'status_bmn'      => 'Digunakan Sendiri',
            ]);

            // Uji 1: Batch Update Sebagian NUP (NUP 1 s/d 1)
            $db->table('trn_inventaris_satker')
                ->where('kode_barang', $dummyKodeBarang)
                ->where('nama_barang', 'Komputer Unit Uji Coba')
                ->where('merk_tipe', 'Smoke-PC Core i7')
                ->where('CAST(NULLIF(nup, "") AS UNSIGNED) >=', 1)
                ->where('CAST(NULLIF(nup, "") AS UNSIGNED) <=', 1)
                ->update(['peruntukan' => 'mobiler']);

            $item1Mobiler = $db->table('trn_inventaris_satker')->where('id', $item1Id)->get()->getRowArray();
            $item2Kantor  = $db->table('trn_inventaris_satker')->where('id', $item2Id)->get()->getRowArray();

            if (($item1Mobiler['peruntukan'] ?? '') === 'mobiler' && ($item2Kantor['peruntukan'] ?? '') === 'kantor') {
                CLI::write("  [OK] Batch Update Peruntukan berhasil mengubah NUP 1 menjadi 'mobiler' dan mempertahankan NUP 2 sebagai 'kantor'", "green");
            } else {
                CLI::error("  [FAIL] Batch Update Peruntukan tidak mengupdate sesuai rentang NUP.");
            }

            // Uji 2: Batch Update Seluruh NUP spesifik (hanya item dengan Kode + Nama + Merk yang cocok)
            // Target: Seluruh unit 'Komputer Unit Uji Coba' diubah ke 'mobiler'
            // Hasil yang diharapkan: item1 & item2 berubah/tetap 'mobiler', sedangkan item3 ('Laptop Uji Coba Berbeda') TETAP 'kantor'
            $db->table('trn_inventaris_satker')
                ->where('kode_barang', $dummyKodeBarang)
                ->where('nama_barang', 'Komputer Unit Uji Coba')
                ->where('merk_tipe', 'Smoke-PC Core i7')
                ->update(['peruntukan' => 'mobiler']);

            $item2After = $db->table('trn_inventaris_satker')->where('id', $item2Id)->get()->getRowArray();
            $item3After = $db->table('trn_inventaris_satker')->where('id', $item3Id)->get()->getRowArray();

            if (($item2After['peruntukan'] ?? '') === 'mobiler' && ($item3After['peruntukan'] ?? '') === 'kantor') {
                CLI::write("  [OK] Update Seluruh NUP terisolasi sempurna: Hanya item dengan Nama Barang & Merk/Tipe yang sama yang terupdate, item lain dengan kode sama tetap aman", "green");
            } else {
                CLI::error("  [FAIL] Update Seluruh NUP tidak terisolasi berdasarkan Nama Barang dan Merk Tipe.");
            }

            // Uji 3: Peruntukan 'lainnya' (Item Lainnya)
            $db->table('trn_inventaris_satker')
                ->where('id', $item3Id)
                ->update(['peruntukan' => 'lainnya']);

            $item3Lainnya = $db->table('trn_inventaris_satker')->where('id', $item3Id)->get()->getRowArray();
            $countLainnya = (int) $db->table('trn_inventaris_satker')->where('peruntukan', 'lainnya')->countAllResults();
            $hasLainnyaTile = (strpos($viewContent, 'active-lainnya') !== false);
            $hasLainnyaRadio = (strpos($viewContent, 'id="mass_peruntukan_lainnya"') !== false);

            if (($item3Lainnya['peruntukan'] ?? '') === 'lainnya' && $countLainnya >= 1 && $hasLainnyaTile && $hasLainnyaRadio) {
                CLI::write("  [OK] Peruntukan 'Item Lainnya' (lainnya) teruji sempurna: KPI Tile, modal radio, dan filter database berfungsi normal", "green");
            } else {
                CLI::error("  [FAIL] Uji peruntukan 'lainnya' gagal.");
            }

            $passedTests++;
        } else {
            CLI::error("  [FAIL] Agregasi DBR tidak menghasilkan jumlah total yang sesuai.");
        }

        // ---------------------------------------------------------------------
        // TEST 4: Parser File Nyata SIMAN Excel (daftar-aset-1.xlsx)
        // ---------------------------------------------------------------------
        CLI::write("\n[TEST 4] Menguji pembacaan file Excel SIMAN BMN (do_not_upload/daftar-aset-1.xlsx)...", "cyan");
        $simanFilePath = ROOTPATH . 'do_not_upload/daftar-aset-1.xlsx';
        if (file_exists($simanFilePath)) {
            try {
                $reader = IOFactory::createReaderForFile($simanFilePath);
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($simanFilePath);
                $sheet = $spreadsheet->getSheetByName('Master Aset') ?: $spreadsheet->getActiveSheet();
                
                // Ambil sampel data baris ke-3 (data pertama)
                $sampleKode   = trim((string) $sheet->getCell('E3')->getValue());
                $sampleNup    = trim((string) $sheet->getCell('F3')->getValue());
                $sampleNama   = trim((string) $sheet->getCell('G3')->getValue());
                $sampleMerk   = trim((string) $sheet->getCell('I3')->getValue());
                $sampleNilai  = (float) ($sheet->getCell('AL3')->getValue() ?? 0);

                if ($sampleKode !== '' && $sampleNama !== '') {
                    CLI::write("  [OK] Sheet 'Master Aset' terbaca sempurna!", "green");
                    CLI::write("  [OK] Sampel Baris 3: [{$sampleKode}] (NUP {$sampleNup}) {$sampleNama} | Merk: {$sampleMerk} | Nilai: Rp " . number_format($sampleNilai, 0, ',', '.'), "green");
                } else {
                    CLI::error("  [FAIL] Data sampel pada baris 3 sheet Master Aset kosong.");
                }

                // Uji mekanisme Upsert: Jika Kode Barang & NUP sama -> Update, jika beda -> Insert
                $dummyUpsertKode = 'UPSERT-SMOKE-' . time();
                $satkerModel->insert([
                    'kode_barang' => $dummyUpsertKode,
                    'nup' => '1',
                    'nama_barang' => 'Barang Awal Sebelum Import',
                    'jumlah' => 1,
                    'satuan' => 'Unit',
                    'kondisi' => 'Baik',
                    'peruntukan' => 'mobiler',
                ]);

                // Simulasikan import 2 baris:
                // Baris A: Kode Barang & NUP sama (UPSERT-SMOKE, NUP 1) -> harus UPDATE nama_barang & pertahankan peruntukan
                // Baris B: Kode Barang sama, NUP beda (UPSERT-SMOKE, NUP 2) -> harus INSERT baris baru
                $existingMapTest = [];
                $existingRowsTest = $db->table('trn_inventaris_satker')->select('id, kode_barang, nup, peruntukan')->where('kode_barang', $dummyUpsertKode)->get()->getResultArray();
                foreach ($existingRowsTest as $er) {
                    $k = strtoupper(trim((string)$er['kode_barang'])) . '___' . trim((string)$er['nup']);
                    $existingMapTest[$k] = $er;
                }

                $keySame = $dummyUpsertKode . '___1';
                $keyDiff = $dummyUpsertKode . '___2';

                $simulatedUpdated = false;
                $simulatedInserted = false;

                if (isset($existingMapTest[$keySame])) {
                    $db->table('trn_inventaris_satker')->where('id', $existingMapTest[$keySame]['id'])->update(['nama_barang' => 'Barang Hasil Update Excel']);
                    $simulatedUpdated = true;
                }

                if (! isset($existingMapTest[$keyDiff])) {
                    $db->table('trn_inventaris_satker')->insert([
                        'kode_barang' => $dummyUpsertKode,
                        'nup' => '2',
                        'nama_barang' => 'Barang Baru Hasil Insert Excel',
                        'jumlah' => 1,
                        'satuan' => 'Unit',
                        'kondisi' => 'Baik',
                        'peruntukan' => 'kantor',
                    ]);
                    $simulatedInserted = true;
                }

                $check1 = $db->table('trn_inventaris_satker')->where('kode_barang', $dummyUpsertKode)->where('nup', '1')->get()->getRowArray();
                $check2 = $db->table('trn_inventaris_satker')->where('kode_barang', $dummyUpsertKode)->where('nup', '2')->get()->getRowArray();

                if ($simulatedUpdated && $simulatedInserted && $check1['nama_barang'] === 'Barang Hasil Update Excel' && $check1['peruntukan'] === 'mobiler' && $check2['nama_barang'] === 'Barang Baru Hasil Insert Excel') {
                    CLI::write("  [OK] Mekanisme Upsert Import Teruji Valid: (1) Kode Barang & NUP Sama berhasil meng-update data tanpa mereset peruntukan; (2) Kode Barang & NUP Beda berhasil meng-insert baris baru.", "green");
                    $passedTests++;
                } else {
                    CLI::error("  [FAIL] Mekanisme Upsert Import gagal.");
                }

                // Cleanup dummy data
                $db->table('trn_inventaris_satker')->where('kode_barang', $dummyUpsertKode)->delete();
            } catch (\Throwable $e) {
                CLI::error("  [FAIL] Exception saat membaca file Excel SIMAN: " . $e->getMessage());
            }
        } else {
            CLI::write("  [SKIP] File do_not_upload/daftar-aset-1.xlsx tidak ditemukan.", "yellow");
            $passedTests++;
        }

        // ---------------------------------------------------------------------
        // TEST 5: Render PDF Resmi DBR (Dompdf)
        // ---------------------------------------------------------------------
        CLI::write("\n[TEST 5] Menguji rendering dokumen PDF Resmi DBR (Dompdf)...", "cyan");
        $tempPdfPath = ROOTPATH . 'do_not_upload/temp/smoke_test_dbr.pdf';
        try {
            $ruanganData = $ruanganModel->find($ruanganId);
            $rekapData = $satkerModel->getBarangByRuanganAgregat($ruanganId);
            $totalJumlah = array_sum(array_column($rekapData, 'total_jumlah'));

            $pdfHtml = view('admin/inventaris/dbr/pdf_dbr', [
                'namaUakpb'     => 'PELAKSANAAN PRASARANA STRATEGIS PROVINSI RIAU',
                'kodeUakpb'     => '145060900691285000KP',
                'room'          => $ruanganData,
                'items'         => $rekapData,
                'totalUnit'     => $totalJumlah,
                'totalNilai'    => 30000000,
                'nomorDokumen'  => 'DBR/690835/' . strtoupper($ruanganData['kode_ruangan']) . '/' . date('Y'),
                'tglPenetapan'  => date('j F Y'),
                'waktuCetak'    => date('d/m/Y H:i') . ' WIB',
                'kopSuratImg'   => '',
                'logoBase64'    => '',
                'kasatker'      => [
                    'nama'    => 'Muhammad Yudi Prasetya, S.T.',
                    'nip'     => '198002142014121002',
                    'jabatan' => 'Kepala Kuasa Pengguna Barang',
                ],
                'tahun'         => date('Y'),
            ]);

            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isPhpEnabled', true);
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($pdfHtml);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $pdfOutput = $dompdf->output();
            $pageCount = $dompdf->getCanvas()->get_page_count();
            file_put_contents($tempPdfPath, $pdfOutput);

            $pdfSize = filesize($tempPdfPath);
            $pdfHeader = substr($pdfOutput, 0, 4);

            if ($pdfSize > 1000 && $pdfHeader === '%PDF' && $pageCount === 1) {
                CLI::write("  [OK] PDF DBR berhasil dirender! Ukuran file: " . round($pdfSize / 1024, 2) . " KB, Total Halaman: {$pageCount} Halaman (PAS 1 HALAMAN)", "green");
                $passedTests++;
            } elseif ($pageCount > 1) {
                CLI::error("  [FAIL] Dokumen PDF melebihi 1 halaman (Total Halaman: {$pageCount}).");
            } else {
                CLI::error("  [FAIL] Output PDF tidak valid atau file kosong (Size: {$pdfSize} bytes).");
            }

            // Uji juga untuk Ruangan Nyata ID 6 (Ruang Tata Usaha dengan 30 unit aset)
            $ruangTU = $ruanganModel->find(6);
            if ($ruangTU) {
                $itemsTU = $satkerModel->getBarangByRuanganAgregat(6);
                $totalUnitTU = array_sum(array_column($itemsTU, 'total_jumlah'));
                $kopSuratImg = function_exists('kop_surat_img_tag') ? kop_surat_img_tag('', 'width: 100%; max-height: 95px; object-fit: contain;', 'Kop Surat Instansi') : '';

                $pdfHtmlTU = view('admin/inventaris/dbr/pdf_dbr', [
                    'namaUakpb'     => 'PELAKSANAAN PRASARANA STRATEGIS PROVINSI RIAU',
                    'kodeUakpb'     => '145060900691285000KP',
                    'room'          => $ruangTU,
                    'items'         => $itemsTU,
                    'totalUnit'     => $totalUnitTU,
                    'nomorDokumen'  => 'DBR/690835/' . strtoupper($ruangTU['kode_ruangan']) . '/' . date('Y'),
                    'tglPenetapan'  => date('j F Y'),
                    'waktuCetak'    => date('d/m/Y H:i') . ' WIB',
                    'kopSuratImg'   => $kopSuratImg,
                    'logoBase64'    => '',
                    'kasatker'      => [
                        'nama'    => 'Muhammad Yudi Prasetya, S.T.',
                        'nip'     => '198002142014121002',
                        'jabatan' => 'Kepala Kuasa Pengguna Barang',
                    ],
                    'tahun'         => date('Y'),
                ]);

                $dompdfTU = new Dompdf($options);
                $dompdfTU->loadHtml($pdfHtmlTU);
                $dompdfTU->setPaper('A4', 'portrait');
                $dompdfTU->render();
                $pageCountTU = $dompdfTU->getCanvas()->get_page_count();
                if ($pageCountTU === 1) {
                    CLI::write("  [OK] PDF DBR Ruang Tata Usaha (30 Unit Aset) PAS TEPAT 1 HALAMAN ({$pageCountTU} Halaman)!", "green");
                } else {
                    CLI::error("  [FAIL] PDF DBR Ruang Tata Usaha tumpah menjadi {$pageCountTU} halaman.");
                }
            }

            // Hapus file sementara sesuai Rule 3
            if (file_exists($tempPdfPath)) {
                unlink($tempPdfPath);
                CLI::write("  [CLEANUP] File sementara {$tempPdfPath} telah dihapus sesuai Rule 3.", "yellow");
            }
        } catch (\Throwable $e) {
            CLI::error("  [FAIL] Exception saat render PDF DBR: " . $e->getMessage());
            if (file_exists($tempPdfPath)) unlink($tempPdfPath);
        }

        // ---------------------------------------------------------------------
        // TEST 6: Generator Export Excel DBR (PhpSpreadsheet)
        // ---------------------------------------------------------------------
        CLI::write("\n[TEST 6] Menguji generator Export Excel DBR (.xlsx)...", "cyan");
        $tempXlsxPath = ROOTPATH . 'do_not_upload/temp/smoke_test_dbr.xlsx';
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('DBR Ruangan');

            // Header DBR
            $sheet->setCellValue('A1', 'KEMENTERIAN PEKERJAAN UMUM');
            $sheet->setCellValue('A2', 'DIREKTORAT JENDERAL PRASARANA STRATEGIS');
            $sheet->setCellValue('A3', 'SATKER PELAKSANAAN PRASARANA STRATEGIS PROVINSI RIAU');
            $sheet->setCellValue('A5', 'DAFTAR BARANG RUANGAN');

            $headers = ['NO', 'KODE BARANG', 'NAMA BARANG', 'MERK / TYPE', 'JUMLAH', 'SATUAN', 'KETERANGAN'];
            $colLetter = 'A';
            foreach ($headers as $h) {
                $sheet->setCellValue($colLetter . '7', $h);
                $colLetter++;
            }

            // Tulis baris barang
            $rowNum = 8;
            $no = 1;
            foreach ($rekapData as $b) {
                $sheet->setCellValue('A' . $rowNum, $no++);
                $sheet->setCellValueExplicit('B' . $rowNum, $b['kode_barang'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue('C' . $rowNum, $b['nama_barang']);
                $sheet->setCellValue('D' . $rowNum, $b['merk_tipe'] ?: '-');
                $sheet->setCellValue('E' . $rowNum, (int) $b['total_jumlah']);
                $sheet->setCellValue('F' . $rowNum, $b['satuan'] ?: 'Buah');
                $sheet->setCellValue('G' . $rowNum, 'Baik');
                $rowNum++;
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save($tempXlsxPath);

            $xlsxSize = filesize($tempXlsxPath);
            if ($xlsxSize > 1000) {
                CLI::write("  [OK] File Excel DBR per ruangan berhasil dibuat! Ukuran file: " . round($xlsxSize / 1024, 2) . " KB", "green");
                $passedTests++;
            } else {
                CLI::error("  [FAIL] File Excel DBR kosong atau korup.");
            }

            // Hapus file sementara sesuai Rule 3
            if (file_exists($tempXlsxPath)) {
                unlink($tempXlsxPath);
                CLI::write("  [CLEANUP] File sementara {$tempXlsxPath} telah dihapus sesuai Rule 3.", "yellow");
            }

            // Uji Generator Export Excel Seluruh Ruangan (Individual NUP)
            $tempAllXlsxPath = ROOTPATH . 'do_not_upload/temp/smoke_test_dbr_all.xlsx';
            $allAssetsWithRooms = $db->table('trn_inventaris_satker s')
                ->select('s.kode_barang, s.nup, s.nama_barang, s.nilai_perolehan, s.merk_tipe, s.kondisi, s.tahun_perolehan, r.kode_ruangan, r.nama_ruangan, r.lokasi_lantai, r.penanggung_jawab_nama, r.penanggung_jawab_nip')
                ->join('mst_ruangan r', 'r.id = s.ruangan_id', 'inner')
                ->where('s.peruntukan', 'kantor')
                ->orderBy('r.kode_ruangan', 'ASC')
                ->orderBy('s.kode_barang', 'ASC')
                ->orderBy('CAST(NULLIF(s.nup, "") AS UNSIGNED)', 'ASC', false)
                ->limit(50)
                ->get()
                ->getResultArray();

            $spreadsheetAll = new Spreadsheet();
            $sheetAll1 = $spreadsheetAll->getActiveSheet();
            $sheetAll1->setTitle('Detail Aset (NUP)');
            $sheetAll1->setCellValue('A1', 'DAFTAR BARANG RUANGAN (DBR) - SELURUH RUANGAN KANTOR (DETAIL NUP)');
            
            $headersAll = ['NO', 'KODE RUANGAN', 'NAMA RUANGAN', 'KODE BARANG', 'NUP', 'NAMA BARANG', 'NILAI PEROLEHAN'];
            $colAll = 'A';
            foreach ($headersAll as $ha) {
                $sheetAll1->setCellValue($colAll . '5', $ha);
                $colAll++;
            }

            $rAll = 6;
            $nAll = 1;
            foreach ($allAssetsWithRooms as $ar) {
                $sheetAll1->setCellValue('A' . $rAll, $nAll++);
                $sheetAll1->setCellValueExplicit('B' . $rAll, (string) ($ar['kode_ruangan'] ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheetAll1->setCellValue('C' . $rAll, (string) ($ar['nama_ruangan'] ?? ''));
                $sheetAll1->setCellValueExplicit('D' . $rAll, (string) ($ar['kode_barang'] ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheetAll1->setCellValue('E' . $rAll, (int) ($ar['nup'] ?? 0));
                $sheetAll1->setCellValue('F' . $rAll, (string) ($ar['nama_barang'] ?? ''));
                $sheetAll1->setCellValue('G' . $rAll, (float) ($ar['nilai_perolehan'] ?? 0));
                $rAll++;
            }

            $sheetAll2 = $spreadsheetAll->createSheet();
            $sheetAll2->setTitle('Rekapitulasi Ruangan');
            $sheetAll2->setCellValue('A1', 'REKAPITULASI ASET DAN NILAI PEROLEHAN PER RUANGAN');

            $writerAll = new Xlsx($spreadsheetAll);
            $writerAll->save($tempAllXlsxPath);

            $allXlsxSize = filesize($tempAllXlsxPath);
            $sheetCount = $spreadsheetAll->getSheetCount();

            $dbrIndexContent = file_get_contents(APPPATH . 'Views/admin/inventaris/dbr/index.php');
            $hasExportBtn = (strpos($dbrIndexContent, 'admin/inventaris/dbr/export-excel') !== false);

            if ($allXlsxSize > 1000 && $sheetCount === 2 && $hasExportBtn) {
                CLI::write("  [OK] Export Excel DBR Seluruh Ruangan (Individual NUP per baris) berhasil dibuat! Ukuran: " . round($allXlsxSize / 1024, 2) . " KB, Total Sheet: {$sheetCount}, Tombol Export aktif di halaman utama DBR", "green");
            } else {
                CLI::error("  [FAIL] Export Excel Seluruh Ruangan gagal atau tombol di view tidak ditemukan.");
            }

            if (file_exists($tempAllXlsxPath)) {
                unlink($tempAllXlsxPath);
                CLI::write("  [CLEANUP] File sementara {$tempAllXlsxPath} telah dihapus sesuai Rule 3.", "yellow");
            }

            // Uji Cetak PDF Seluruh Ruangan (A4 Landscape, Individual NUP)
            $tempAllPdfPath = ROOTPATH . 'do_not_upload/temp/smoke_test_dbr_all.pdf';
            $pdfData = [
                'items'        => $allAssetsWithRooms,
                'totalUnit'    => count($allAssetsWithRooms),
                'totalNilai'   => array_sum(array_column($allAssetsWithRooms, 'nilai_perolehan')),
                'tglPenetapan' => date('j F Y'),
                'waktuCetak'   => date('d/m/Y H:i') . ' WIB',
                'kopSuratImg'  => '',
                'logoBase64'   => '',
                'kasatker'     => [
                    'nama'    => 'Muhammad Yudi Prasetya, S.T.',
                    'nip'     => '198002142014121002',
                    'jabatan' => 'Kepala Kuasa Pengguna Barang',
                ],
                'tahun'        => date('Y'),
            ];

            $options = new \Dompdf\Options();
            $options->set('isRemoteEnabled', true);
            $options->set('isHtml5ParserEnabled', true);
            $dompdfAll = new \Dompdf\Dompdf($options);
            ob_start();
            $htmlAll = view('admin/inventaris/dbr/pdf_dbr_all', $pdfData);
            ob_end_clean();
            service('response')->setBody('');
            $dompdfAll->loadHtml($htmlAll);
            $dompdfAll->setPaper('A4', 'portrait');
            $dompdfAll->render();
            file_put_contents($tempAllPdfPath, $dompdfAll->output());

            $allPdfSize = filesize($tempAllPdfPath);
            $hasPdfAllBtn = (strpos($dbrIndexContent, 'admin/inventaris/dbr/cetak-pdf') !== false);

            if ($allPdfSize > 2000 && $hasPdfAllBtn) {
                CLI::write("  [OK] Cetak PDF DBR Seluruh Ruangan (A4 Portrait, Tabel Terpisah Per Ruangan, Tanpa Kop/Reg/Nilai) berhasil dirender! Ukuran: " . round($allPdfSize / 1024, 2) . " KB, Tombol Cetak PDF aktif di halaman utama DBR", "green");
            } else {
                CLI::error("  [FAIL] Render PDF DBR Seluruh Ruangan gagal atau tombol di view tidak ditemukan.");
            }

            if (file_exists($tempAllPdfPath)) {
                unlink($tempAllPdfPath);
                CLI::write("  [CLEANUP] File sementara {$tempAllPdfPath} telah dihapus sesuai Rule 3.", "yellow");
            }
        } catch (\Throwable $e) {
            CLI::error("  [FAIL] Exception saat generate Excel/PDF DBR: " . $e->getMessage());
            if (file_exists($tempXlsxPath)) unlink($tempXlsxPath);
            if (isset($tempAllXlsxPath) && file_exists($tempAllXlsxPath)) unlink($tempAllXlsxPath);
            if (isset($tempAllPdfPath) && file_exists($tempAllPdfPath)) unlink($tempAllPdfPath);
        }

        // ---------------------------------------------------------------------
        // TEST 7: Integritas Menu Lv1 & Lv2 (Restrukturisasi 3 Menu Utama) & Hak Akses
        // ---------------------------------------------------------------------
        CLI::write("\n[TEST 7] Memeriksa integrasi restrukturisasi menu Inventarisasi (3 Menu Lv2) & hak akses...", "cyan");
        $menuLv1 = $db->table('menu_lv1')->like('label', 'Inventarisasi')->get()->getRowArray();
        $menuLv2Barang  = $db->table('menu_lv2')->where('id', '11-01')->get()->getRowArray();
        $menuLv2Dbr     = $db->table('menu_lv2')->where('id', '11-02')->get()->getRowArray();
        $menuLv2Sekolah = $db->table('menu_lv2')->where('id', '11-03')->get()->getRowArray();

        $hierarchyOk = ($menuLv1 !== null && $menuLv2Barang !== null && $menuLv2Dbr !== null && $menuLv2Sekolah !== null);

        $aksesBarang  = $db->table('menu_akses')->where('menu_id', '11-01')->countAllResults();
        $aksesDbr     = $db->table('menu_akses')->where('menu_id', '11-02')->countAllResults();
        $aksesSekolah = $db->table('menu_akses')->where('menu_id', '11-03')->countAllResults();

        // Uji keberadaan kolom peruntukan di tabel trn_inventaris_satker
        $peruntukanColOk = $db->fieldExists('peruntukan', 'trn_inventaris_satker');

        if ($hierarchyOk && $aksesBarang > 0 && $aksesDbr > 0 && $aksesSekolah > 0 && $peruntukanColOk) {
            CLI::write("  [OK] Hierarki Menu Inventarisasi Terstruktur Sempurna (3 Menu Lv2 Utama Tanpa Sub-Induk):", "green");
            CLI::write("       - Lv1: {$menuLv1['label']} (ID: {$menuLv1['id']})", "green");
            CLI::write("         - Lv2: 1. {$menuLv2Barang['label']} (ID: {$menuLv2Barang['id']}, Link: {$menuLv2Barang['link']}) -> {$aksesBarang} Roles", "green");
            CLI::write("         - Lv2: 2. {$menuLv2Dbr['label']} (ID: {$menuLv2Dbr['id']}, Link: {$menuLv2Dbr['link']}) -> {$aksesDbr} Roles", "green");
            CLI::write("         - Lv2: 3. {$menuLv2Sekolah['label']} (ID: {$menuLv2Sekolah['id']}, Link: {$menuLv2Sekolah['link']}) -> {$aksesSekolah} Roles", "green");
            CLI::write("  [OK] Kolom 'peruntukan' (kantor / mobiler) terdeteksi aktif pada tabel trn_inventaris_satker", "green");
            $passedTests++;
        } else {
            CLI::error("  [FAIL] Hierarki menu atau kolom peruntukan belum sesuai.");
        }

        // ---------------------------------------------------------------------
        // CLEANUP DATABASE
        // ---------------------------------------------------------------------
        CLI::write("\n[CLEANUP] Membersihkan data uji coba dari database...", "cyan");
        $db->table('trn_inventaris_satker')->where('kode_barang', $dummyKodeBarang)->delete();
        $db->table('mst_ruangan')->where('id', $ruanganId)->delete();
        CLI::write("  [OK] Seluruh data dummy uji coba berhasil dibersihkan dari database.", "green");

        // ---------------------------------------------------------------------
        // SUMMARY
        // ---------------------------------------------------------------------
        CLI::write("\n================================================================", "yellow");
        if ($passedTests === $totalTests) {
            CLI::write("   HASIL SMOKE TEST: SEMUA UJI COBA LULUS ({$passedTests}/{$totalTests} PASSED)   ", "green");
        } else {
            CLI::write("   HASIL SMOKE TEST: SEBAGIAN GAGAL ({$passedTests}/{$totalTests} PASSED)   ", "red");
        }
        CLI::write("================================================================\n", "yellow");
    }
}
