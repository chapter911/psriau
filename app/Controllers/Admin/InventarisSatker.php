<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\InventarisSatkerModel;
use CodeIgniter\HTTP\RedirectResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class InventarisSatker extends BaseController
{
    private const MENU_LINK = 'admin/inventaris/satker';

    public function index()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $model = new InventarisSatkerModel();
        $builder = $model->builder();

        $filterKategori = trim((string) $this->request->getGet('kategori'));
        $filterKondisi  = trim((string) $this->request->getGet('kondisi'));
        $filterLokasi   = trim((string) $this->request->getGet('lokasi'));
        $searchKeyword  = trim((string) $this->request->getGet('keyword'));

        if ($filterKategori !== '' && $filterKategori !== '*') {
            $builder->where('kategori', $filterKategori);
        }
        if ($filterKondisi !== '' && $filterKondisi !== '*') {
            $builder->where('kondisi', $filterKondisi);
        }
        if ($filterLokasi !== '' && $filterLokasi !== '*') {
            $builder->where('lokasi_ruangan', $filterLokasi);
        }
        if ($searchKeyword !== '') {
            $builder->groupStart()
                ->like('nama_barang', $searchKeyword)
                ->orLike('kode_barang', $searchKeyword)
                ->orLike('nup', $searchKeyword)
                ->orLike('kode_register', $searchKeyword)
                ->orLike('merk_tipe', $searchKeyword)
                ->groupEnd();
        }

        $items = $builder->orderBy('kode_barang', 'ASC')
            ->orderBy('CAST(NULLIF(nup, "") AS UNSIGNED)', 'ASC', false)
            ->orderBy('nup', 'ASC')
            ->get()
            ->getResultArray();

        // Get unique dropdown options
        $db = db_connect();
        $kategoriList = $db->table('trn_inventaris_satker')
            ->select('kategori')
            ->distinct()
            ->where('kategori !=', '')
            ->orderBy('kategori', 'ASC')
            ->get()
            ->getResultArray();

        $lokasiList = $db->table('trn_inventaris_satker')
            ->select('lokasi_ruangan')
            ->distinct()
            ->where('lokasi_ruangan !=', '')
            ->orderBy('lokasi_ruangan', 'ASC')
            ->get()
            ->getResultArray();

        $masterRuangan = $db->table('mst_ruangan')
            ->select('nama_ruangan')
            ->orderBy('nama_ruangan', 'ASC')
            ->get()
            ->getResultArray();

        $mergedRuangan = array_merge(
            array_column($masterRuangan, 'nama_ruangan'),
            array_column($lokasiList, 'lokasi_ruangan')
        );
        $ruanganOptions = array_values(array_unique(array_filter(array_map('trim', $mergedRuangan))));
        sort($ruanganOptions);

        $defaultSatuans = ['Unit', 'Buah', 'Set', 'Pcs', 'Lembar', 'Paket', 'Kotak', 'Roll', 'Meter', 'Batang'];
        $dbSatuans = $db->table('trn_inventaris_satker')
            ->select('satuan')
            ->distinct()
            ->where('satuan !=', '')
            ->get()
            ->getResultArray();
        $mergedSatuans = array_merge($defaultSatuans, array_column($dbSatuans, 'satuan'));
        $satuanOptions = array_values(array_unique(array_filter(array_map('trim', $mergedSatuans))));

        // Summary counts
        $totalItems = $db->table('trn_inventaris_satker')->countAllResults();
        $totalBaik  = $db->table('trn_inventaris_satker')->where('kondisi', 'baik')->countAllResults();
        $totalRingan = $db->table('trn_inventaris_satker')->where('kondisi', 'rusak_ringan')->countAllResults();
        $totalBerat = $db->table('trn_inventaris_satker')->where('kondisi', 'rusak_berat')->countAllResults();

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);

        return view('admin/inventaris/satker/index', [
            'pageTitle'        => 'Inventaris Satker',
            'items'            => $items,
            'kategoriList'     => array_column($kategoriList, 'kategori'),
            'lokasiList'       => array_column($lokasiList, 'lokasi_ruangan'),
            'ruanganOptions'   => $ruanganOptions,
            'satuanOptions'    => $satuanOptions,
            'filterKategori'   => $filterKategori,
            'filterKondisi'    => $filterKondisi,
            'filterLokasi'     => $filterLokasi,
            'searchKeyword'    => $searchKeyword,
            'summary'          => [
                'total'        => $totalItems,
                'baik'         => $totalBaik,
                'rusak_ringan' => $totalRingan,
                'rusak_berat'  => $totalBerat,
            ],
            'can_add'          => (bool) ($menuPermissions['add'] ?? false),
            'can_edit'         => (bool) ($menuPermissions['edit'] ?? false),
            'can_delete'       => (bool) ($menuPermissions['delete'] ?? false),
            'can_export'       => (bool) ($menuPermissions['export'] ?? false),
            'can_import'       => (bool) ($menuPermissions['import'] ?? false),
        ]);
    }

    public function create()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! (bool) ($menuPermissions['add'] ?? false)) {
            return redirect()->to('/admin/inventaris/satker')->with('error', 'Anda tidak memiliki hak akses untuk menambah data inventaris.');
        }

        $rules = [
            'kode_barang'    => 'required|max_length[50]',
            'nup'            => 'permit_empty|max_length[50]',
            'kode_register'  => 'permit_empty|max_length[100]|is_unique[trn_inventaris_satker.kode_register]',
            'nama_barang'    => 'required|max_length[255]',
            'kategori'       => 'required|max_length[100]',
            'jumlah'         => 'required|integer|greater_than[0]',
            'satuan'         => 'required|max_length[50]',
            'kondisi'        => 'required|in_list[baik,rusak_ringan,rusak_berat]',
            'lokasi_ruangan' => 'required|max_length[150]',
        ];

        $customMessages = [
            'kode_register' => [
                'is_unique' => 'Kode Register sudah digunakan oleh aset lain. Kode Register harus unik.',
            ],
        ];

        if (! $this->validate($rules, $customMessages)) {
            $errors = $this->validator->getErrors();
            $firstError = reset($errors) ?: 'Gagal menambahkan data: mohon periksa inputan formulir Anda.';
            return redirect()->to('/admin/inventaris/satker')
                ->withInput()
                ->with('error', $firstError);
        }

        $model = new InventarisSatkerModel();
        $userId = (int) (session()->get('userId') ?? 0);

        $model->insert([
            'kode_barang'     => trim((string) $this->request->getPost('kode_barang')),
            'nup'             => trim((string) $this->request->getPost('nup')) ?: null,
            'kode_register'   => trim((string) $this->request->getPost('kode_register')) ?: null,
            'nama_barang'     => trim((string) $this->request->getPost('nama_barang')),
            'kategori'        => trim((string) $this->request->getPost('kategori')),
            'merk_tipe'       => trim((string) $this->request->getPost('merk_tipe')) ?: null,
            'jumlah'          => (int) $this->request->getPost('jumlah'),
            'satuan'          => trim((string) $this->request->getPost('satuan')),
            'kondisi'         => trim((string) $this->request->getPost('kondisi')),
            'lokasi_ruangan'  => trim((string) $this->request->getPost('lokasi_ruangan')),
            'tahun_perolehan' => (int) $this->request->getPost('tahun_perolehan') ?: null,
            'keterangan'      => trim((string) $this->request->getPost('keterangan')) ?: null,
            'created_by'      => $userId ?: null,
            'updated_by'      => $userId ?: null,
        ]);

        return redirect()->to('/admin/inventaris/satker')->with('message', 'Data inventaris barang berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! (bool) ($menuPermissions['edit'] ?? false)) {
            return redirect()->to('/admin/inventaris/satker')->with('error', 'Anda tidak memiliki hak akses untuk mengubah data inventaris.');
        }

        $model = new InventarisSatkerModel();
        $existing = $model->find($id);
        if (! is_array($existing)) {
            return redirect()->to('/admin/inventaris/satker')->with('error', 'Data inventaris tidak ditemukan.');
        }

        $rules = [
            'kode_barang'    => 'required|max_length[50]',
            'nup'            => 'permit_empty|max_length[50]',
            'kode_register'  => "permit_empty|max_length[100]|is_unique[trn_inventaris_satker.kode_register,id,{$id}]",
            'nama_barang'    => 'required|max_length[255]',
            'kategori'       => 'required|max_length[100]',
            'jumlah'         => 'required|integer|greater_than[0]',
            'satuan'         => 'required|max_length[50]',
            'kondisi'        => 'required|in_list[baik,rusak_ringan,rusak_berat]',
            'lokasi_ruangan' => 'required|max_length[150]',
        ];

        $customMessages = [
            'kode_register' => [
                'is_unique' => 'Kode Register sudah digunakan oleh aset lain. Kode Register harus unik.',
            ],
        ];

        if (! $this->validate($rules, $customMessages)) {
            $errors = $this->validator->getErrors();
            $firstError = reset($errors) ?: 'Gagal memperbarui data: mohon periksa inputan formulir Anda.';
            return redirect()->to('/admin/inventaris/satker')
                ->withInput()
                ->with('error', $firstError);
        }

        $userId = (int) (session()->get('userId') ?? 0);

        $model->update($id, [
            'kode_barang'     => trim((string) $this->request->getPost('kode_barang')),
            'nup'             => trim((string) $this->request->getPost('nup')) ?: null,
            'kode_register'   => trim((string) $this->request->getPost('kode_register')) ?: null,
            'nama_barang'     => trim((string) $this->request->getPost('nama_barang')),
            'kategori'        => trim((string) $this->request->getPost('kategori')),
            'merk_tipe'       => trim((string) $this->request->getPost('merk_tipe')) ?: null,
            'jumlah'          => (int) $this->request->getPost('jumlah'),
            'satuan'          => trim((string) $this->request->getPost('satuan')),
            'kondisi'         => trim((string) $this->request->getPost('kondisi')),
            'lokasi_ruangan'  => trim((string) $this->request->getPost('lokasi_ruangan')),
            'tahun_perolehan' => (int) $this->request->getPost('tahun_perolehan') ?: null,
            'keterangan'      => trim((string) $this->request->getPost('keterangan')) ?: null,
            'updated_by'      => $userId ?: null,
        ]);

        return redirect()->to('/admin/inventaris/satker')->with('message', 'Data inventaris berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! (bool) ($menuPermissions['delete'] ?? false)) {
            return redirect()->to('/admin/inventaris/satker')->with('error', 'Anda tidak memiliki hak akses untuk menghapus data inventaris.');
        }

        $model = new InventarisSatkerModel();
        $existing = $model->find($id);
        if (! is_array($existing)) {
            return redirect()->to('/admin/inventaris/satker')->with('error', 'Data inventaris tidak ditemukan.');
        }

        $model->delete($id);

        return redirect()->to('/admin/inventaris/satker')->with('message', 'Data inventaris barang berhasil dihapus.');
    }

    public function importSiman()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! (bool) ($menuPermissions['import'] ?? false)) {
            return redirect()->to('/admin/inventaris/satker')->with('error', 'Anda tidak memiliki hak akses untuk mengimpor data inventaris.');
        }

        $file = $this->request->getFile('file_excel');
        if (! $file || ! $file->isValid()) {
            return redirect()->to('/admin/inventaris/satker')->with('error', 'Silakan pilih file Excel SIMAN yang valid untuk diunggah.');
        }

        $ext = strtolower($file->getClientExtension());
        if (! in_array($ext, ['xlsx', 'xls'], true)) {
            return redirect()->to('/admin/inventaris/satker')->with('error', 'Format file harus berupa .xlsx atau .xls.');
        }

        ini_set('memory_limit', '512M');
        set_time_limit(300);

        try {
            $spreadsheet = IOFactory::load($file->getTempName());
            $sheet = $spreadsheet->getSheetByName('Master Aset') ?: $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();

            if ($highestRow < 3) {
                return redirect()->to('/admin/inventaris/satker')->with('error', 'File Excel tidak memiliki data aset yang cukup.');
            }

            $db = db_connect();
            $userId = (int) (session()->get('userId') ?? 0);
            $now = date('Y-m-d H:i:s');

            $importedCount = 0;
            $updatedCount  = 0;

            // Preload existing (kode_barang, nup) map for speed
            $existingRows = $db->table('trn_inventaris_satker')
                ->select('id, kode_barang, nup, ruangan_id')
                ->get()
                ->getResultArray();

            $existingMap = [];
            foreach ($existingRows as $er) {
                $key = trim((string) $er['kode_barang']) . '___' . trim((string) $er['nup']);
                $existingMap[$key] = [
                    'id'         => (int) $er['id'],
                    'ruangan_id' => (int) ($er['ruangan_id'] ?? 0),
                ];
            }

            for ($r = 3; $r <= $highestRow; $r++) {
                $kodeBarang   = trim((string) $sheet->getCell('E' . $r)->getValue());
                $nup          = trim((string) $sheet->getCell('F' . $r)->getValue());
                $kodeRegister = trim((string) $sheet->getCell('BT' . $r)->getValue());
                $namaBarang   = trim((string) $sheet->getCell('G' . $r)->getValue());

                if ($kodeBarang === '' && $namaBarang === '') {
                    continue;
                }

                $kategori    = trim((string) $sheet->getCell('B' . $r)->getValue()) ?: 'Peralatan Kantor';
                $statusBmn   = trim((string) $sheet->getCell('H' . $r)->getValue()) ?: 'Aktif';
                $merk        = trim((string) $sheet->getCell('I' . $r)->getValue());
                $tipe        = trim((string) $sheet->getCell('J' . $r)->getValue());
                $rawKondisi  = trim((string) $sheet->getCell('K' . $r)->getValue());
                $rawTgl      = $sheet->getCell('AH' . $r)->getValue();
                $nilaiPerolehan = (float) ($sheet->getCell('AL' . $r)->getValue() ?? 0);
                $nilaiBuku      = (float) ($sheet->getCell('AN' . $r)->getValue() ?? 0);
                $noPsp       = trim((string) $sheet->getCell('AY' . $r)->getValue());
                $lokasiRuang = trim((string) $sheet->getCell('BU' . $r)->getValue()) ?: 'Kantor Satker PPS';

                // Format merk_tipe
                $merkTipe = '';
                if ($merk !== '' && $tipe !== '') {
                    $merkTipe = ($merk === $tipe) ? $merk : ($merk . ' ' . $tipe);
                } elseif ($merk !== '') {
                    $merkTipe = $merk;
                } else {
                    $merkTipe = $tipe;
                }

                // Format kondisi
                $kondisi = 'baik';
                $lk = strtolower($rawKondisi);
                if (strpos($lk, 'berat') !== false) {
                    $kondisi = 'rusak_berat';
                } elseif (strpos($lk, 'ringan') !== false) {
                    $kondisi = 'rusak_ringan';
                }

                // Format tanggal & tahun
                $tglPerolehan = null;
                $tahunPerolehan = null;
                if (is_numeric($rawTgl) && $rawTgl > 10000) {
                    try {
                        $dt = ExcelDate::excelToDateTimeObject($rawTgl);
                        $tglPerolehan = $dt->format('Y-m-d');
                        $tahunPerolehan = (int) $dt->format('Y');
                    } catch (\Throwable $e) {
                        // ignore
                    }
                } elseif (! empty($rawTgl)) {
                    $ts = strtotime((string) $rawTgl);
                    if ($ts !== false) {
                        $tglPerolehan = date('Y-m-d', $ts);
                        $tahunPerolehan = (int) date('Y', $ts);
                    }
                }

                $record = [
                    'kode_barang'     => $kodeBarang,
                    'nup'             => $nup ?: null,
                    'kode_register'   => $kodeRegister ?: null,
                    'nama_barang'     => $namaBarang,
                    'kategori'        => $kategori,
                    'merk'            => $merk ?: null,
                    'tipe'            => $tipe ?: null,
                    'merk_tipe'       => $merkTipe ?: null,
                    'jumlah'          => 1,
                    'satuan'          => 'Buah',
                    'kondisi'         => $kondisi,
                    'lokasi_ruangan'  => $lokasiRuang,
                    'nilai_perolehan' => $nilaiPerolehan,
                    'nilai_buku'      => $nilaiBuku,
                    'status_bmn'      => $statusBmn,
                    'no_psp'          => $noPsp ?: null,
                    'tahun_perolehan' => $tahunPerolehan,
                    'tgl_perolehan'   => $tglPerolehan,
                    'updated_at'      => $now,
                    'updated_by'      => $userId ?: null,
                ];

                $mapKey = $kodeBarang . '___' . $nup;
                if (isset($existingMap[$mapKey])) {
                    $existingItem = $existingMap[$mapKey];
                    if (! empty($existingItem['ruangan_id'])) {
                        unset($record['lokasi_ruangan']);
                    }
                    $db->table('trn_inventaris_satker')->where('id', $existingItem['id'])->update($record);
                    $updatedCount++;
                } else {
                    $record['created_at'] = $now;
                    $record['created_by'] = $userId ?: null;
                    $db->table('trn_inventaris_satker')->insert($record);
                    $newId = $db->insertID();
                    $existingMap[$mapKey] = [
                        'id'         => $newId,
                        'ruangan_id' => 0,
                    ];
                    $importedCount++;
                }
            }

            $totalProses = $importedCount + $updatedCount;
            return redirect()->to('/admin/inventaris/satker')
                ->with('message', "Proses import SIMAN selesai! {$importedCount} data baru ditambahkan dan {$updatedCount} data diperbarui (Total: {$totalProses} aset).");
        } catch (\Throwable $e) {
            return redirect()->to('/admin/inventaris/satker')
                ->with('error', 'Gagal memproses file Excel SIMAN: ' . $e->getMessage());
        }
    }

    public function export()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! (bool) ($menuPermissions['export'] ?? false)) {
            return redirect()->to('/admin/inventaris/satker')->with('error', 'Anda tidak memiliki izin untuk mengunduh laporan.');
        }

        $model = new InventarisSatkerModel();
        $builder = $model->builder();

        $filterKategori = trim((string) $this->request->getGet('kategori'));
        $filterKondisi  = trim((string) $this->request->getGet('kondisi'));
        $filterLokasi   = trim((string) $this->request->getGet('lokasi'));

        if ($filterKategori !== '' && $filterKategori !== '*') {
            $builder->where('kategori', $filterKategori);
        }
        if ($filterKondisi !== '' && $filterKondisi !== '*') {
            $builder->where('kondisi', $filterKondisi);
        }
        if ($filterLokasi !== '' && $filterLokasi !== '*') {
            $builder->where('lokasi_ruangan', $filterLokasi);
        }

        $items = $builder->orderBy('kode_barang', 'ASC')
            ->orderBy('CAST(NULLIF(nup, "") AS UNSIGNED)', 'ASC', false)
            ->orderBy('nup', 'ASC')
            ->get()
            ->getResultArray();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Inventaris Satker');

        // Header Title
        $sheet->mergeCells('A1:K1');
        $sheet->mergeCells('A2:K2');
        $sheet->mergeCells('A3:K3');
        $sheet->mergeCells('A4:K4');

        $sheet->setCellValue('A1', 'DAFTAR INVENTARISASI BARANG MILIK NEGARA / SARANA KANTOR');
        $sheet->setCellValue('A2', 'SATUAN KERJA PELAKSANAAN PRASARANA STRATEGIS RIAU');
        $sheet->setCellValue('A3', 'DIREKTORAT JENDERAL PRASARANA STRATEGIS');
        $sheet->setCellValue('A4', 'KEMENTERIAN PEKERJAAN UMUM');

        $sheet->getStyle('A1:K4')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A1:K4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:K4')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // Table Header
        $headers = [
            'A6' => 'NO.',
            'B6' => 'KODE BARANG',
            'C6' => 'NUP',
            'D6' => 'KODE REGISTER',
            'E6' => 'NAMA BARANG',
            'F6' => 'KATEGORI',
            'G6' => 'MERK / TIPE',
            'H6' => 'JUMLAH',
            'I6' => 'KONDISI',
            'J6' => 'LOKASI RUANGAN',
            'K6' => 'THN PEROLEHAN',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $sheet->getStyle('A6:K6')->getFont()->setBold(true);
        $sheet->getStyle('A6:K6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A6:K6')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A6:K6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');

        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(36);
        $sheet->getColumnDimension('E')->setWidth(35);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(25);
        $sheet->getColumnDimension('H')->setWidth(14);
        $sheet->getColumnDimension('I')->setWidth(16);
        $sheet->getColumnDimension('J')->setWidth(25);
        $sheet->getColumnDimension('K')->setWidth(16);

        $rowNum = 7;
        $no = 1;
        foreach ($items as $item) {
            $kondisiLabel = match ($item['kondisi']) {
                'baik'         => 'Baik',
                'rusak_ringan' => 'Rusak Ringan',
                'rusak_berat'  => 'Rusak Berat',
                default        => ucfirst($item['kondisi'] ?? '-'),
            };

            $sheet->setCellValue('A' . $rowNum, $no++);
            $sheet->setCellValueExplicit('B' . $rowNum, (string) ($item['kode_barang'] ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C' . $rowNum, (string) ($item['nup'] ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D' . $rowNum, (string) ($item['kode_register'] ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('E' . $rowNum, (string) ($item['nama_barang'] ?? ''));
            $sheet->setCellValue('F' . $rowNum, (string) ($item['kategori'] ?? ''));
            $sheet->setCellValue('G' . $rowNum, (string) ($item['merk_tipe'] ?? '-'));
            $sheet->setCellValue('H' . $rowNum, ($item['jumlah'] ?? 0) . ' ' . ($item['satuan'] ?? 'Unit'));
            $sheet->setCellValue('I' . $rowNum, $kondisiLabel);
            $sheet->setCellValue('J' . $rowNum, (string) ($item['lokasi_ruangan'] ?? ''));
            $sheet->setCellValue('K' . $rowNum, (string) ($item['tahun_perolehan'] ?? '-'));

            $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('I' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('K' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $rowNum++;
        }

        $lastRow = max(7, $rowNum - 1);
        $sheet->getStyle('A6:K' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $writer = new Xlsx($spreadsheet);
        $fileName = 'inventaris_satker_pps_riau_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    private function denyIfNoMenuAccess(string $menuLink): ?RedirectResponse
    {
        if ($this->hasMenuAccess($menuLink)) {
            return null;
        }

        return redirect()->to('/forbidden?from=' . rawurlencode($menuLink));
    }

    private function hasMenuAccess(string $menuLink): bool
    {
        $db = db_connect();
        if (! $db->tableExists('menu_akses')) {
            return true;
        }

        $roleId = $this->resolveRoleId((string) session()->get('role'), $db);
        if ($roleId === null) {
            return false;
        }

        $menuId = $this->resolveMenuIdByLink($menuLink, $db);
        if ($menuId === null) {
            return false;
        }

        $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';

        return (int) $db->table('menu_akses')
            ->where($roleColumn, $roleId)
            ->where('menu_id', $menuId)
            ->countAllResults() > 0;
    }

    private function resolveMenuPermissions(string $menuLink): array
    {
        $default = [
            'add'      => false,
            'edit'     => false,
            'delete'   => false,
            'export'   => false,
            'import'   => false,
            'approval' => false,
        ];

        $db = db_connect();
        if (! $db->tableExists('menu_akses')) {
            return $default;
        }

        $roleId = $this->resolveRoleId((string) session()->get('role'), $db);
        $menuId = $this->resolveMenuIdByLink($menuLink, $db);
        if ($roleId === null || $menuId === null) {
            return $default;
        }

        $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';
        $row = $db->table('menu_akses')
            ->select('FiturAdd, FiturEdit, FiturDelete, FiturExport, FiturImport, FiturApproval')
            ->where($roleColumn, $roleId)
            ->where('menu_id', $menuId)
            ->get()
            ->getRowArray();

        if (! is_array($row)) {
            return $default;
        }

        return [
            'add'      => (bool) ((int) ($row['FiturAdd'] ?? 0)),
            'edit'     => (bool) ((int) ($row['FiturEdit'] ?? 0)),
            'delete'   => (bool) ((int) ($row['FiturDelete'] ?? 0)),
            'export'   => (bool) ((int) ($row['FiturExport'] ?? 0)),
            'import'   => (bool) ((int) ($row['FiturImport'] ?? 0)),
            'approval' => (bool) ((int) ($row['FiturApproval'] ?? 0)),
        ];
    }

    private function resolveRoleId(string $role, $db): ?int
    {
        $normalized = strtolower(trim($role));
        if ($normalized === '') {
            return null;
        }

        if ($db->tableExists('access_roles')) {
            $variants = [$normalized];
            if ($normalized === 'super administrator') {
                $variants[] = 'super_administrator';
                $variants[] = 'super-admin';
                $variants[] = 'superadmin';
            } elseif ($normalized === 'super_administrator' || $normalized === 'super-admin' || $normalized === 'superadmin') {
                $variants[] = 'super administrator';
                $variants[] = 'super_administrator';
                $variants[] = 'super-admin';
                $variants[] = 'superadmin';
            }

            $row = $db->table('access_roles')
                ->select('id')
                ->whereIn('role_key', array_values(array_unique($variants)))
                ->where('is_active', 1)
                ->orderBy('id', 'ASC')
                ->get()
                ->getRowArray();

            if (is_array($row) && isset($row['id'])) {
                return (int) $row['id'];
            }
        }

        return null;
    }

    private function resolveMenuIdByLink(string $menuLink, $db): ?string
    {
        $normalized = trim(strtolower($menuLink), '/');

        foreach (['menu_lv3', 'menu_lv2', 'menu_lv1'] as $table) {
            if (! $db->tableExists($table)) {
                continue;
            }

            $row = $db->table($table)
                ->select('id')
                ->where('LOWER(TRIM(link))', $normalized)
                ->get()
                ->getRowArray();

            if (is_array($row) && isset($row['id'])) {
                return (string) $row['id'];
            }
        }

        return null;
    }
}
