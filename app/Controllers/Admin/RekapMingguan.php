<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LaporanRekapitulasiMingguanModel;
use App\Models\LaporanRekapitulasiMingguanSekolahModel;
use App\Models\LaporanRekapitulasiMingguanDetailModel;
use CodeIgniter\HTTP\RedirectResponse;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class RekapMingguan extends BaseController
{
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
            'add' => false,
            'edit' => false,
            'delete' => false,
            'export' => false,
            'import' => false,
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
            'add' => (bool) ((int) ($row['FiturAdd'] ?? 0)),
            'edit' => (bool) ((int) ($row['FiturEdit'] ?? 0)),
            'delete' => (bool) ((int) ($row['FiturDelete'] ?? 0)),
            'export' => (bool) ((int) ($row['FiturExport'] ?? 0)),
            'import' => (bool) ((int) ($row['FiturImport'] ?? 0)),
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

        return match ($normalized) {
            'admin' => 1,
            'editor' => 2,
            default => null,
        };
    }

    private function resolveMenuIdByLink(string $menuLink, $db): ?string
    {
        foreach (['menu_lv3', 'menu_lv2', 'menu_lv1'] as $table) {
            if (! $db->tableExists($table)) {
                continue;
            }

            $row = $db->table($table)
                ->select('id')
                ->where('LOWER(link)', strtolower($menuLink))
                ->get()
                ->getRowArray();

            if (is_array($row) && isset($row['id'])) {
                return (string) $row['id'];
            }
        }

        return null;
    }

    // LISTING PAGE
    public function index()
    {
        $deny = $this->denyIfNoMenuAccess('admin/laporan/rekap-mingguan');
        if ($deny !== null) {
            return $deny;
        }

        $filterPaketId = $this->request->getGet('paket_id');
        $db = db_connect();

        $builder = $db->table('laporan_rekapitulasi_mingguan r')
            ->select('r.*, mp.nama_paket')
            ->join('mst_paket mp', 'mp.id = r.paket_id', 'left')
            ->orderBy('r.minggu_ke', 'DESC');

        if ($filterPaketId !== null && $filterPaketId !== '' && $filterPaketId !== 'all') {
            $builder->where('r.paket_id', $filterPaketId);
        }

        $reports = $builder->get()->getResultArray();

        $paketModel = new \App\Models\MstPaketModel();
        $pakets = $paketModel->where('is_active', 1)->orderBy('nama_paket', 'ASC')->findAll();

        $permissions = $this->resolveMenuPermissions('admin/laporan/rekap-mingguan');

        return view('admin/laporan/rekap_mingguan_list', [
            'reports'          => $reports,
            'pakets'           => $pakets,
            'filter_paket_id'  => $filterPaketId,
            'can_import'       => $permissions['import'],
            'can_add'          => $permissions['add'],
            'can_edit'         => $permissions['edit'],
            'can_delete'       => $permissions['delete'],
        ]);
    }

    // SHOW REKAP TABLE
    public function show(int $id)
    {
        $deny = $this->denyIfNoMenuAccess('admin/laporan/rekap-mingguan');
        if ($deny !== null) {
            return $deny;
        }

        $db = db_connect();
        $rekap = $db->table('laporan_rekapitulasi_mingguan r')
            ->select('r.*, mp.nama_paket')
            ->join('mst_paket mp', 'mp.id = r.paket_id', 'left')
            ->where('r.id', $id)
            ->get()
            ->getRowArray();

        if (!$rekap) {
            return redirect()->to(site_url('admin/laporan/rekap-mingguan'))->with('error', 'Laporan rekapitulasi tidak ditemukan.');
        }

        $sekolahModel = new LaporanRekapitulasiMingguanSekolahModel();
        $sekolahs = $sekolahModel->where('rekapitulasi_mingguan_id', $id)->orderBy('id', 'ASC')->findAll();

        $permissions = $this->resolveMenuPermissions('admin/laporan/rekap-mingguan');

        return view('admin/laporan/rekap_mingguan_show', [
            'rekap'      => $rekap,
            'sekolahs'   => $sekolahs,
            'can_add'    => $permissions['add'],
            'can_edit'   => $permissions['edit'],
            'can_delete' => $permissions['delete'],
        ]);
    }

    // SHOW RAB DETAIL FOR A SPECIFIC SCHOOL
    public function detail(int $id)
    {
        $deny = $this->denyIfNoMenuAccess('admin/laporan/rekap-mingguan');
        if ($deny !== null) {
            return $deny;
        }

        $db = db_connect();
        $rekap = $db->table('laporan_rekapitulasi_mingguan r')
            ->select('r.*, mp.nama_paket')
            ->join('mst_paket mp', 'mp.id = r.paket_id', 'left')
            ->where('r.id', $id)
            ->get()
            ->getRowArray();

        if (!$rekap) {
            return redirect()->to(site_url('admin/laporan/rekap-mingguan'))->with('error', 'Laporan rekapitulasi tidak ditemukan.');
        }

        $sekolahName = $this->request->getGet('sekolah');
        if (empty($sekolahName)) {
            return redirect()->to(site_url('admin/laporan/rekap-mingguan/show/' . $id))->with('error', 'Nama sekolah wajib diisi.');
        }

        return view('admin/laporan/rekap_mingguan_detail', [
            'rekap'       => $rekap,
            'sekolahName' => $sekolahName,
        ]);
    }

    // DATATABLE API FOR DETAIL VIEW
    public function dataDetail(int $id)
    {
        if (!$this->hasMenuAccess('admin/laporan/rekap-mingguan')) {
            return $this->response->setJSON([
                'draw'            => 0,
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
                'error'           => 'Akses ditolak.'
            ]);
        }

        $sekolahName = $this->request->getGet('sekolah');

        $queryFactory = function(bool $forSums = false) use ($id, $sekolahName) {
            $builder = db_connect()->table('laporan_rekapitulasi_mingguan_detail');
            $builder->where('rekapitulasi_mingguan_id', $id)
                    ->where('nama_sekolah', $sekolahName);
            return $builder;
        };

        $filterApplier = function($builder) {};

        $searchColumns = ['no_urut', 'uraian', 'satuan'];
        $orderColumns  = ['id', 'no_urut', 'uraian', 'satuan', 'volume', 'harga_satuan', 'jumlah_harga', 'bobot', 'progres_minggu_lalu_bobot', 'progres_minggu_ini_bobot', 'progres_sampai_minggu_ini_bobot', 'progres_pekerjaan_persen', 'deviasi_progres', 'sisa_progres'];

        $rowMapper = function($row) {
            $row['volume_formatted'] = $row['volume'] !== null ? number_format($row['volume'], 2, ',', '.') : '-';
            $row['harga_satuan_formatted'] = $row['harga_satuan'] !== null ? 'Rp ' . number_format($row['harga_satuan'], 0, ',', '.') : '-';
            $row['jumlah_harga_formatted'] = $row['jumlah_harga'] !== null ? 'Rp ' . number_format($row['jumlah_harga'], 0, ',', '.') : '-';
            $row['bobot_formatted'] = $row['bobot'] !== null ? number_format($row['bobot'], 3, ',', '.') . '%' : '-';
            
            $row['progres_minggu_lalu_vol_formatted'] = $row['progres_minggu_lalu_vol'] !== null ? number_format($row['progres_minggu_lalu_vol'], 2, ',', '.') : '-';
            $row['progres_minggu_lalu_bobot_formatted'] = $row['progres_minggu_lalu_bobot'] !== null ? number_format($row['progres_minggu_lalu_bobot'], 3, ',', '.') . '%' : '-';
            
            $row['progres_minggu_ini_vol_formatted'] = $row['progres_minggu_ini_vol'] !== null ? number_format($row['progres_minggu_ini_vol'], 2, ',', '.') : '-';
            $row['progres_minggu_ini_bobot_formatted'] = $row['progres_minggu_ini_bobot'] !== null ? number_format($row['progres_minggu_ini_bobot'], 3, ',', '.') . '%' : '-';
            
            $row['progres_sampai_minggu_ini_vol_formatted'] = $row['progres_sampai_minggu_ini_vol'] !== null ? number_format($row['progres_sampai_minggu_ini_vol'], 2, ',', '.') : '-';
            $row['progres_sampai_minggu_ini_bobot_formatted'] = $row['progres_sampai_minggu_ini_bobot'] !== null ? number_format($row['progres_sampai_minggu_ini_bobot'], 3, ',', '.') . '%' : '-';
            
            $row['progres_pekerjaan_persen_formatted'] = $row['progres_pekerjaan_persen'] !== null ? number_format($row['progres_pekerjaan_persen'], 2, ',', '.') . '%' : '-';
            $row['deviasi_progres_formatted'] = $row['deviasi_progres'] !== null ? number_format($row['deviasi_progres'], 3, ',', '.') . '%' : '-';
            $row['sisa_progres_formatted'] = $row['sisa_progres'] !== null ? number_format($row['sisa_progres'], 3, ',', '.') . '%' : '-';

            $row['uraian_escaped'] = esc($row['uraian']);
            return $row;
        };

        return $this->respondDataTable($queryFactory, $filterApplier, $searchColumns, $orderColumns, $rowMapper);
    }

    // CRUD: CREATE Empty Weekly Recap Metadata
    public function create()
    {
        $permissions = $this->resolveMenuPermissions('admin/laporan/rekap-mingguan');
        if (!$permissions['add']) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menambah data.');
        }

        $mingguKe = $this->request->getPost('minggu_ke');
        $judul    = $this->request->getPost('judul');
        $paketId  = $this->request->getPost('paket_id');

        if (empty($mingguKe) || empty($judul)) {
            return redirect()->back()->withInput()->with('error', 'Minggu Ke dan Judul wajib diisi.');
        }

        $model = new LaporanRekapitulasiMingguanModel();
        $data = [
            'paket_id'  => $paketId !== '' ? (int)$paketId : null,
            'minggu_ke' => (int)$mingguKe,
            'judul'     => $judul,
        ];

        if ($model->insert($data)) {
            return redirect()->to(site_url('admin/laporan/rekap-mingguan'))->with('success', 'Metadata Laporan Mingguan berhasil dibuat. Silakan unggah berkas excel untuk mengisi data.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal membuat Laporan Mingguan.');
    }

    // CRUD: EDIT Weekly Recap Metadata or Manual Summary Table Update
    public function edit(int $id)
    {
        $permissions = $this->resolveMenuPermissions('admin/laporan/rekap-mingguan');
        if (!$permissions['edit']) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengubah data.');
        }

        $action = $this->request->getPost('action');
        
        if ($action === 'edit_meta') {
            $mingguKe = $this->request->getPost('minggu_ke');
            $judul    = $this->request->getPost('judul');
            $paketId  = $this->request->getPost('paket_id');

            if (empty($mingguKe) || empty($judul)) {
                return redirect()->back()->with('error', 'Minggu Ke dan Judul wajib diisi.');
            }

            $model = new LaporanRekapitulasiMingguanModel();
            $model->update($id, [
                'paket_id'  => $paketId !== '' ? (int)$paketId : null,
                'minggu_ke' => (int)$mingguKe,
                'judul'     => $judul,
            ]);

            return redirect()->to(site_url('admin/laporan/rekap-mingguan'))->with('success', 'Metadata Laporan Mingguan berhasil diperbarui.');
        }

        if ($action === 'edit_summary_item') {
            $itemId   = $this->request->getPost('item_id');
            $rencana  = $this->request->getPost('rencana');
            $progres  = $this->request->getPost('progres_minggu_ini');

            if (empty($itemId)) {
                return redirect()->back()->with('error', 'Item ID tidak valid.');
            }

            $sekolahModel = new LaporanRekapitulasiMingguanSekolahModel();
            $item = $sekolahModel->find($itemId);
            if (!$item) {
                return redirect()->back()->with('error', 'Item tidak ditemukan.');
            }

            $rencanaVal = $rencana !== '' ? (float)$rencana : $item['rencana'];
            $progresVal = $progres !== '' ? (float)$progres : $item['progres_minggu_ini'];
            
            // Recalculate based on new inputs
            $bobot = $item['bobot'];
            $mingguLalu = $item['progres_minggu_lalu'];
            $sampaiMingguIni = $mingguLalu + $progresVal;
            $deviasi = $bobot - $sampaiMingguIni;

            $sekolahModel->update($itemId, [
                'rencana' => $rencanaVal,
                'progres_minggu_ini' => $progresVal,
                'progres_sampai_minggu_ini' => $sampaiMingguIni,
                'deviasi' => $deviasi,
            ]);

            return redirect()->to(site_url('admin/laporan/rekap-mingguan/show/' . $id))->with('success', 'Data item pekerjaan berhasil diperbarui.');
        }

        return redirect()->back()->with('error', 'Aksi tidak dikenal.');
    }

    // CRUD: DELETE Weekly Recap
    public function delete(int $id)
    {
        $permissions = $this->resolveMenuPermissions('admin/laporan/rekap-mingguan');
        if (!$permissions['delete']) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk menghapus data.'
            ]);
        }

        $model = new LaporanRekapitulasiMingguanModel();
        $existing = $model->find($id);
        if (!$existing) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data tidak ditemukan.'
            ]);
        }

        if ($model->delete($id)) {
            // Note: Cascade deletes on foreign keys will remove the detail and summary items automatically
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Laporan Mingguan beserta rinciannya berhasil dihapus.'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Gagal menghapus data.'
        ]);
    }

    // EXCEL IMPORT & DYNAMIC REKAP CALCULATION
    public function import()
    {
        $permissions = $this->resolveMenuPermissions('admin/laporan/rekap-mingguan');
        if (!$permissions['import']) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk melakukan import.');
        }

        $mingguKe = $this->request->getPost('minggu_ke');
        $paketId  = $this->request->getPost('paket_id');

        if (empty($mingguKe)) {
            return redirect()->back()->with('error', 'Kolom Minggu Ke wajib diisi.');
        }
        if (empty($paketId)) {
            return redirect()->back()->with('error', 'Silakan pilih Paket terlebih dahulu.');
        }

        $file = $this->request->getFile('file_excel');
        if (! $file || ! $file->isValid()) {
            return redirect()->back()->with('error', 'File tidak valid.');
        }

        $tempPath = $file->getTempName();

        try {
            if (!class_exists('\\PhpOffice\\PhpSpreadsheet\\IOFactory')) {
                return redirect()->back()->with('error', 'PhpSpreadsheet tidak tersedia.');
            }

            // Find or create the weekly report row based on paket_id and minggu_ke
            $rekapModel = new LaporanRekapitulasiMingguanModel();
            $existingReport = $rekapModel->where('paket_id', (int)$paketId)
                                         ->where('minggu_ke', (int)$mingguKe)
                                         ->first();

            if ($existingReport) {
                $reportId = (int)$existingReport['id'];
            } else {
                $reportId = $rekapModel->insert([
                    'paket_id'  => (int)$paketId,
                    'minggu_ke' => (int)$mingguKe,
                    'judul'     => 'MINGGU ' . $mingguKe,
                ]);
            }

            // Optimize reader
            $reader = IOFactory::createReaderForFile($tempPath);
            $reader->setReadDataOnly(true);
            $reader->setLoadSheetsOnly(['RAB']);
            
            $spreadsheet = $reader->load($tempPath);
            $sheetRab = $spreadsheet->getSheetByName('RAB');

            if ($sheetRab === null) {
                return redirect()->back()->with('error', 'Sheet "RAB" wajib ada di dalam file Excel.');
            }

            // Step 1: Scan sheet RAB to find subtotal rows and identify schools/sections
            $highestRowRab = $sheetRab->getHighestRow();
            $schools = [];

            for ($r = 11; $r <= $highestRowRab; $r++) {
                // Get A and B cell values (with evaluations)
                $cellA = $sheetRab->getCell('A' . $r);
                $valA = $cellA->getValue();
                if (is_string($valA) && strpos($valA, '=') === 0) {
                    $valA = $cellA->getOldCalculatedValue();
                }

                $cellB = $sheetRab->getCell('B' . $r);
                $valB = $cellB->getValue();
                if (is_string($valB) && strpos($valB, '=') === 0) {
                    $valB = $cellB->getOldCalculatedValue();
                }

                $valAStr = trim((string)$valA);
                $valBStr = trim((string)$valB);

                $matchedVal = null;
                if (stripos($valAStr, 'JUMLAH ') === 0) {
                    $matchedVal = $valAStr;
                } elseif (stripos($valBStr, 'JUMLAH ') === 0) {
                    $matchedVal = $valBStr;
                }

                if ($matchedVal !== null) {
                    $upperVal = strtoupper($matchedVal);
                    if ($upperVal !== 'JUMLAH HARGA' && $upperVal !== 'JUMLAH TOTAL') {
                        // Extract school name: strip "JUMLAH " prefix
                        $schoolName = trim(substr($matchedVal, 7));
                        if (!empty($schoolName)) {
                            $schools[] = [
                                'name'         => $schoolName,
                                'subtotal_row' => $r,
                            ];
                        }
                    }
                }
            }

            if (empty($schools)) {
                return redirect()->back()->with('error', 'Tidak ditemukan subtotal jenis pekerjaan sekolah pada sheet RAB.');
            }

            // Sort schools by subtotal_row ascending to define row ranges
            usort($schools, function($a, $b) {
                return $a['subtotal_row'] <=> $b['subtotal_row'];
            });

            // Map row ranges: School i is from subtotal_row(i-1)+1 to subtotal_row(i)
            // For the first school, we start from row 11 (after standard headers in RAB)
            $schoolRanges = [];
            $prevSubtotal = 10;
            foreach ($schools as $index => $school) {
                // Generate a letter-based order number: A, B, C, etc.
                $noUrut = chr(65 + $index) . '.';
                $schoolRanges[] = [
                    'name'         => $school['name'],
                    'no_urut'      => $noUrut,
                    'subtotal_row' => $school['subtotal_row'],
                    'rencana'      => 0.0, // Default plans to 0.0 as we do not read Rekap sheet anymore
                    'start_row'    => $prevSubtotal + 1,
                    'end_row'      => $school['subtotal_row'],
                ];
                $prevSubtotal = $school['subtotal_row'];
            }

            // Helper to determine school name by row number in RAB
            $findSchoolForRow = function($rowNum) use ($schoolRanges) {
                foreach ($schoolRanges as $range) {
                    if ($rowNum >= $range['start_row'] && $rowNum <= $range['end_row']) {
                        return $range['name'];
                    }
                }
                return null;
            };

            // Step 2: Parse sheet RAB and insert details
            $highestRowRab = $sheetRab->getHighestRow();
            $detailItems = [];

            $getNumericValue = function ($col, $row) use ($sheetRab) {
                $cell = $sheetRab->getCell($col . $row);
                $val = $cell->getValue();
                if (is_string($val) && strpos($val, '=') === 0) {
                    $val = $cell->getOldCalculatedValue();
                }
                return ($val !== null && $val !== '' && is_numeric($val)) ? (float)$val : null;
            };

            $getStringValue = function ($col, $row) use ($sheetRab) {
                $cell = $sheetRab->getCell($col . $row);
                $val = $cell->getValue();
                if (is_string($val) && strpos($val, '=') === 0) {
                    $val = $cell->getOldCalculatedValue();
                }
                return ($val !== null && $val !== '') ? trim((string)$val) : null;
            };

            for ($row = 11; $row <= $highestRowRab; $row++) {
                $noUrut = $getStringValue('A', $row);
                $uraian = $getStringValue('B', $row);

                // Skip rows where both no_urut and uraian are empty
                if ($noUrut === null && $uraian === null) {
                    continue;
                }

                $schoolName = $findSchoolForRow($row);
                if ($schoolName === null) {
                    // Row is outside recognized ranges (e.g. metadata or spacing)
                    continue;
                }

                $detailItems[] = [
                    'rekapitulasi_mingguan_id'        => $reportId,
                    'nama_sekolah'                    => $schoolName,
                    'no_urut'                         => $noUrut,
                    'uraian'                          => $uraian,
                    'volume'                          => $getNumericValue('G', $row),
                    'satuan'                          => $getStringValue('H', $row),
                    'harga_satuan'                    => $getNumericValue('I', $row),
                    'jumlah_harga'                    => $getNumericValue('J', $row),
                    'bobot'                           => $getNumericValue('K', $row),
                    'progres_minggu_lalu_vol'         => $getNumericValue('L', $row),
                    'progres_minggu_lalu_bobot'        => $getNumericValue('M', $row),
                    'progres_minggu_ini_vol'          => $getNumericValue('N', $row),
                    'progres_minggu_ini_bobot'         => $getNumericValue('O', $row),
                    'progres_sampai_minggu_ini_vol'   => $getNumericValue('P', $row),
                    'progres_sampai_minggu_ini_bobot'  => $getNumericValue('Q', $row),
                    'progres_pekerjaan_persen'        => $getNumericValue('R', $row),
                    'deviasi_progres'                 => $getNumericValue('S', $row),
                    'sisa_progres'                    => $getNumericValue('T', $row),
                    'created_at'                      => date('Y-m-d H:i:s'),
                    'updated_at'                      => date('Y-m-d H:i:s'),
                ];
            }

            // Step 3: Insert everything and update summary
            $detailModel = new LaporanRekapitulasiMingguanDetailModel();
            $sekolahModel = new LaporanRekapitulasiMingguanSekolahModel();
            
            $db = db_connect();
            $db->transStart();

            // Clear previous items of this report
            $detailModel->where('rekapitulasi_mingguan_id', $reportId)->delete();
            $sekolahModel->where('rekapitulasi_mingguan_id', $reportId)->delete();

            // Bulk insert detail items (chunked to prevent query size limit)
            $chunks = array_chunk($detailItems, 200);
            foreach ($chunks as $chunk) {
                $detailModel->insertBatch($chunk);
            }

            // Insert school summaries calculated/read from subtotal rows in Excel
            $summaryItems = [];
            foreach ($schoolRanges as $range) {
                $row = $range['subtotal_row'];
                
                $jumlahHarga = $getNumericValue('J', $row);
                $bobot       = $getNumericValue('K', $row);
                $mingguLalu  = $getNumericValue('M', $row);
                $mingguIni   = $getNumericValue('O', $row);
                $sampaiIni   = ($mingguLalu !== null ? (float)$mingguLalu : 0.0) + ($mingguIni !== null ? (float)$mingguIni : 0.0);
                $rencana     = $range['rencana'];
                $deviasi     = $bobot !== null ? ($bobot - $sampaiIni) : null;

                $summaryItems[] = [
                    'rekapitulasi_mingguan_id'  => $reportId,
                    'no_urut'                   => $range['no_urut'],
                    'nama_sekolah'              => $range['name'],
                    'jumlah_harga'              => $jumlahHarga,
                    'bobot'                     => $bobot,
                    'progres_minggu_lalu'       => $mingguLalu,
                    'progres_minggu_ini'        => $mingguIni,
                    'progres_sampai_minggu_ini' => $sampaiIni,
                    'rencana'                   => $rencana,
                    'deviasi'                   => $deviasi,
                    'created_at'                => date('Y-m-d H:i:s'),
                    'updated_at'                => date('Y-m-d H:i:s'),
                ];
            }
            $sekolahModel->insertBatch($summaryItems);

            $db->transComplete();

            if ($db->transStatus() === false) {
                return redirect()->back()->with('error', 'Gagal memproses data impor.');
            }

            return redirect()->to(site_url('admin/laporan/rekap-mingguan/show/' . $reportId))->with('success', 'Laporan Mingguan berhasil diimpor.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // Helper functions for respondDataTable
    private function respondDataTable(callable $queryFactory, callable $filterApplier, array $searchColumns, array $orderColumns, ?callable $rowMapper = null)
    {
        try {
            $draw   = $this->getDataTableDraw();
            $start  = $this->getDataTableStart();
            $length = $this->getDataTableLength();
            $search = $this->getDataTableSearchTerm();
            
            $orderIndex     = $this->getDataTableOrderColumnIndex();
            $orderDirection = $this->getDataTableOrderDirection();

            $totalBuilder = $queryFactory();
            $filterApplier($totalBuilder);
            $recordsTotal = (int) $totalBuilder->countAllResults(false);

            $filteredBuilder = $queryFactory();
            $filterApplier($filteredBuilder);
            $this->applyDataTableSearch($filteredBuilder, $searchColumns, $search);
            $recordsFiltered = (int) $filteredBuilder->countAllResults(false);

            $orderColumn = $orderColumns[$orderIndex] ?? $orderColumns[0] ?? '';
            if ($orderColumn !== '') {
                $filteredBuilder->orderBy($orderColumn, $orderDirection);
            }

            $rows = $filteredBuilder->limit($length, $start)->get()->getResultArray();

            if ($rowMapper !== null) {
                $rows = array_map($rowMapper, $rows);
            }

            return $this->response->setJSON([
                'draw'            => $draw,
                'recordsTotal'    => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data'            => $rows
            ]);
        } catch (\Throwable $exception) {
            log_message('error', 'DataTable Rekapitulasi Mingguan gagal dimuat: ' . $exception->getMessage());
            return $this->response->setJSON([
                'draw'            => $this->getDataTableDraw(),
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
                'error'           => 'Gagal memuat rincian data.',
            ]);
        }
    }

    private function applyDataTableSearch($builder, array $columns, string $searchTerm): void
    {
        $searchTerm = trim($searchTerm);
        if ($searchTerm === '' || $columns === []) {
            return;
        }

        $builder->groupStart();
        foreach ($columns as $index => $column) {
            if ($index === 0) {
                $builder->like($column, $searchTerm);
                continue;
            }
            $builder->orLike($column, $searchTerm);
        }
        $builder->groupEnd();
    }

    private function getDataTableDraw(): int
    {
        return max(0, (int) $this->request->getGet('draw'));
    }

    private function getDataTableStart(): int
    {
        return max(0, (int) $this->request->getGet('start'));
    }

    private function getDataTableLength(): int
    {
        $length = (int) $this->request->getGet('length');
        return $length > 0 ? $length : 10;
    }

    private function getDataTableSearchTerm(): string
    {
        $search = $this->request->getGet('search');
        if (! is_array($search)) {
            return '';
        }
        return trim((string) ($search['value'] ?? ''));
    }

    private function getDataTableOrderColumnIndex(): int
    {
        $order = $this->request->getGet('order');
        if (! is_array($order) || $order === []) {
            return 0;
        }
        $first = $order[0] ?? [];
        if (! is_array($first)) {
            return 0;
        }
        return max(0, (int) ($first['column'] ?? 0));
    }

    private function getDataTableOrderDirection(): string
    {
        $order = $this->request->getGet('order');
        if (! is_array($order) || $order === []) {
            return 'ASC';
        }
        $first = $order[0] ?? [];
        if (! is_array($first)) {
            return 'ASC';
        }
        return strtolower((string) ($first['dir'] ?? 'asc')) === 'desc' ? 'DESC' : 'ASC';
    }

    // EXPORT TO EXCEL
    public function export(int $id)
    {
        $deny = $this->denyIfNoMenuAccess('admin/laporan/rekap-mingguan');
        if ($deny !== null) {
            return $deny;
        }

        $db = db_connect();
        $rekap = $db->table('laporan_rekapitulasi_mingguan r')
            ->select('r.*, mp.nama_paket')
            ->join('mst_paket mp', 'mp.id = r.paket_id', 'left')
            ->where('r.id', $id)
            ->get()
            ->getRowArray();

        if (!$rekap) {
            return redirect()->to(site_url('admin/laporan/rekap-mingguan'))->with('error', 'Laporan rekapitulasi tidak ditemukan.');
        }

        $sekolahModel = new LaporanRekapitulasiMingguanSekolahModel();
        $sekolahs = $sekolahModel->where('rekapitulasi_mingguan_id', $id)->orderBy('id', 'ASC')->findAll();

        // Create Spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekapitulasi');

        // Styles
        $titleStyle = [
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ]
        ];

        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2F3A45'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ]
            ]
        ];

        $dataStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ]
            ]
        ];

        $totalStyle = [
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F2F2F2'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ]
            ]
        ];

        $pembulatanStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '007BFF'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ]
            ]
        ];

        // Title Block
        $sheet->setCellValue('A1', 'REKAPITULASI LAPORAN BOBOT');
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->applyFromArray($titleStyle);

        $sheet->setCellValue('A2', 'PERIODE: MINGGU ' . $rekap['minggu_ke'] . ' - ' . strtoupper($rekap['judul']));
        $sheet->mergeCells('A2:I2');
        $sheet->getStyle('A2')->applyFromArray($titleStyle);

        $sheet->setCellValue('A3', 'PAKET: ' . strtoupper($rekap['nama_paket'] ?? 'TANPA PAKET'));
        $sheet->mergeCells('A3:I3');
        $sheet->getStyle('A3')->applyFromArray($titleStyle);

        // Header Row
        $headers = [
            'NO.', 
            'JENIS PEKERJAAN', 
            'JUMLAH HARGA ADD 2', 
            'BOBOT (%)', 
            'PROGRES MINGGU LALU', 
            'PROGRES MINGGU INI', 
            'PROGRES SAMPAI MINGGU INI', 
            'RENCANA', 
            'DEVIASI'
        ];
        
        $colIndex = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($colIndex . '5', $header);
            $sheet->getStyle($colIndex . '5')->applyFromArray($headerStyle);
            $colIndex++;
        }
        $sheet->getRowDimension('5')->setRowHeight(30);

        // Data Rows
        $rowNum = 6;
        $sumHarga = 0;
        $sumBobot = 0;
        $sumLalu = 0;
        $sumIni = 0;
        $sumSampai = 0;
        $sumRencana = 0;
        $sumDeviasi = 0;

        foreach ($sekolahs as $sekolah) {
            $sheet->setCellValue('A' . $rowNum, $sekolah['no_urut']);
            $sheet->setCellValue('B' . $rowNum, $sekolah['nama_sekolah']);
            $sheet->setCellValue('C' . $rowNum, $sekolah['jumlah_harga']);
            $sheet->setCellValue('D' . $rowNum, $sekolah['bobot'] / 100);
            $sheet->setCellValue('E' . $rowNum, $sekolah['progres_minggu_lalu'] / 100);
            $sheet->setCellValue('F' . $rowNum, $sekolah['progres_minggu_ini'] / 100);
            $sheet->setCellValue('G' . $rowNum, $sekolah['progres_sampai_minggu_ini'] / 100);
            $sheet->setCellValue('H' . $rowNum, $sekolah['rencana'] / 100);
            $sheet->setCellValue('I' . $rowNum, $sekolah['deviasi'] / 100);

            // Format numbers
            $sheet->getStyle('C' . $rowNum)->getNumberFormat()->setFormatCode('"Rp"#,##0');
            foreach (['D', 'E', 'F', 'G', 'H', 'I'] as $col) {
                $sheet->getStyle($col . $rowNum)->getNumberFormat()->setFormatCode('0.000%');
            }

            // Alignments
            $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('C' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            foreach (['D', 'E', 'F', 'G', 'H', 'I'] as $col) {
                $sheet->getStyle($col . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }

            $sheet->getStyle('A' . $rowNum . ':I' . $rowNum)->applyFromArray($dataStyle);

            $sumHarga += (float)$sekolah['jumlah_harga'];
            $sumBobot += (float)$sekolah['bobot'];
            $sumLalu += (float)$sekolah['progres_minggu_lalu'];
            $sumIni += (float)$sekolah['progres_minggu_ini'];
            $sumSampai += (float)$sekolah['progres_sampai_minggu_ini'];
            $sumRencana += (float)$sekolah['rencana'];
            $sumDeviasi += (float)$sekolah['deviasi'];

            $rowNum++;
        }

        // Totals (A)
        $sheet->setCellValue('A' . $rowNum, '');
        $sheet->setCellValue('B' . $rowNum, '(A) Jumlah Harga Pekerjaan (termasuk Biaya Umum dan Keuntungan)');
        $sheet->setCellValue('C' . $rowNum, $sumHarga);
        $sheet->setCellValue('D' . $rowNum, $sumBobot / 100);
        $sheet->setCellValue('E' . $rowNum, $sumLalu / 100);
        $sheet->setCellValue('F' . $rowNum, $sumIni / 100);
        $sheet->setCellValue('G' . $rowNum, $sumSampai / 100);
        $sheet->setCellValue('H' . $rowNum, $sumRencana / 100);
        $sheet->setCellValue('I' . $rowNum, $sumDeviasi / 100);

        $sheet->getStyle('C' . $rowNum)->getNumberFormat()->setFormatCode('"Rp"#,##0');
        foreach (['D', 'E', 'F', 'G', 'H', 'I'] as $col) {
            $sheet->getStyle($col . $rowNum)->getNumberFormat()->setFormatCode('0.000%');
        }

        $sheet->getStyle('A' . $rowNum . ':I' . $rowNum)->applyFromArray($totalStyle);
        $rowNum++;

        // PPn (B)
        $ppn = $sumHarga * 0.11;
        $sheet->setCellValue('B' . $rowNum, '(B) Pajak Pertambahan Nilai ( PPn ) = 11% x (A)');
        $sheet->setCellValue('C' . $rowNum, $ppn);
        $sheet->getStyle('C' . $rowNum)->getNumberFormat()->setFormatCode('"Rp"#,##0');
        $sheet->getStyle('A' . $rowNum . ':I' . $rowNum)->applyFromArray($totalStyle);
        $rowNum++;

        // Total (C)
        $totalVal = $sumHarga + $ppn;
        $sheet->setCellValue('B' . $rowNum, '(C) Jumlah Total Harga Pekerjaan = (A) + (B)');
        $sheet->setCellValue('C' . $rowNum, $totalVal);
        $sheet->getStyle('C' . $rowNum)->getNumberFormat()->setFormatCode('"Rp"#,##0');
        $sheet->getStyle('A' . $rowNum . ':I' . $rowNum)->applyFromArray($totalStyle);
        $rowNum++;

        // Pembulatan
        $pembulatan = round($totalVal, -2);
        $sheet->setCellValue('B' . $rowNum, 'PEMBULATAN');
        $sheet->setCellValue('C' . $rowNum, $pembulatan);
        $sheet->getStyle('C' . $rowNum)->getNumberFormat()->setFormatCode('"Rp"#,##0');
        $sheet->getStyle('A' . $rowNum . ':I' . $rowNum)->applyFromArray($pembulatanStyle);

        // Auto column widths
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Output file
        $fileName = 'Rekapitulasi_Minggu_' . $rekap['minggu_ke'] . '_' . str_replace(' ', '_', $rekap['nama_paket'] ?? 'Tanpa_Paket') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
