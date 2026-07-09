<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\RabGedungDetailModel;
use App\Models\MstSekolahModel;
use App\Models\MstPaketModel;
use CodeIgniter\HTTP\RedirectResponse;

class RabGedung extends BaseController
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

    // 1. Initial Page: Lists all schools with stats
    public function index()
    {
        $deny = $this->denyIfNoMenuAccess('admin/laporan/rab-gedung');
        if ($deny !== null) {
            return $deny;
        }

        $filterPaketId = $this->request->getGet('paket_id');
        $db = db_connect();

        $sekolahsBuilder = $db->table('mst_sekolah s')
            ->select('s.npsn, s.nama, s.kecamatan, s.kabupaten, 
                      COUNT(DISTINCT r.gedung) as total_gedung, 
                      COUNT(r.id) as total_items,
                      MIN(r.paket_id) as paket_id,
                      MIN(mp.nama_paket) as nama_paket')
            ->join('trn_rab_gedung_detail r', 'r.sekolah_npsn = s.npsn', 'inner')
            ->join('mst_paket mp', 'mp.id = r.paket_id', 'left')
            ->groupBy('s.npsn, s.nama, s.kecamatan, s.kabupaten')
            ->orderBy('s.nama', 'ASC');

        $sekolahs = $sekolahsBuilder->get()->getResultArray();

        $paketModel = new MstPaketModel();
        $pakets = $paketModel->where('is_active', 1)->orderBy('nama_paket', 'ASC')->findAll();

        $permissions = $this->resolveMenuPermissions('admin/laporan/rab-gedung');

        return view('admin/laporan/rab_gedung', [
            'sekolahs' => $sekolahs,
            'can_import' => $permissions['import'],
            'can_edit' => $permissions['edit'],
            'pakets' => $pakets,
            'filter_paket_id' => $filterPaketId,
        ]);
    }

    // 2. School Detail Page: Shows detailed DataTable for this school
    public function detail(int $npsn)
    {
        $deny = $this->denyIfNoMenuAccess('admin/laporan/rab-gedung');
        if ($deny !== null) {
            return $deny;
        }

        $sekolahModel = new MstSekolahModel();
        $sekolah = $sekolahModel->find($npsn);
        if (!$sekolah) {
            return redirect()->to(site_url('admin/laporan/rab-gedung'))->with('error', 'Sekolah tidak ditemukan.');
        }

        $db = db_connect();
        $gedungRows = $db->table('trn_rab_gedung_detail')
            ->select('gedung')
            ->distinct()
            ->where('sekolah_npsn', $npsn)
            ->where('gedung IS NOT NULL')
            ->where('gedung !=', '')
            ->orderBy('gedung', 'ASC')
            ->get()
            ->getResultArray();

        $kategori1Rows = $db->table('trn_rab_gedung_detail')
            ->select('kategori_1')
            ->distinct()
            ->where('sekolah_npsn', $npsn)
            ->where('kategori_1 IS NOT NULL')
            ->where('kategori_1 !=', '')
            ->orderBy('kategori_1', 'ASC')
            ->get()
            ->getResultArray();

        $kategori2Rows = $db->table('trn_rab_gedung_detail')
            ->select('kategori_2')
            ->distinct()
            ->where('sekolah_npsn', $npsn)
            ->where('kategori_2 IS NOT NULL')
            ->where('kategori_2 !=', '')
            ->orderBy('kategori_2', 'ASC')
            ->get()
            ->getResultArray();

        // Also fetch all schools for the add/edit dropdown just in case,
        // but default it to this school.
        $allSekolahs = $sekolahModel->orderBy('nama', 'ASC')->findAll();

        // Calculate summary totals
        $sums = $db->table('trn_rab_gedung_detail')
            ->select('SUM(kontrak_jumlah_harga) as total_kontrak, 
                      SUM(mc_nol_jumlah_harga) as total_mcnol, 
                      SUM(tambah_jumlah_harga) as total_tambah, 
                      SUM(kurang_jumlah_harga) as total_kurang')
            ->where('sekolah_npsn', $npsn)
            ->get()
            ->getRowArray();

        $paketModel = new MstPaketModel();
        $pakets = $paketModel->where('is_active', 1)->orderBy('nama_paket', 'ASC')->findAll();

        $sekolahPaket = $db->table('trn_rab_gedung_detail')
            ->select('mst_paket.nama_paket')
            ->join('mst_paket', 'mst_paket.id = trn_rab_gedung_detail.paket_id', 'left')
            ->where('trn_rab_gedung_detail.sekolah_npsn', $npsn)
            ->where('trn_rab_gedung_detail.paket_id IS NOT NULL')
            ->limit(1)
            ->get()
            ->getRowArray();
        $paketNama = $sekolahPaket['nama_paket'] ?? null;

        $permissions = $this->resolveMenuPermissions('admin/laporan/rab-gedung');

        return view('admin/laporan/rab_gedung_detail', [
            'sekolah' => $sekolah,
            'sekolahs' => $allSekolahs,
            'gedungs' => array_column($gedungRows, 'gedung'),
            'kategori_1s' => array_column($kategori1Rows, 'kategori_1'),
            'kategori_2s' => array_column($kategori2Rows, 'kategori_2'),
            'total_kontrak' => (float)($sums['total_kontrak'] ?? 0),
            'total_mcnol' => (float)($sums['total_mcnol'] ?? 0),
            'total_tambah' => (float)($sums['total_tambah'] ?? 0),
            'total_kurang' => (float)($sums['total_kurang'] ?? 0),
            'can_add' => $permissions['add'],
            'can_edit' => $permissions['edit'],
            'can_delete' => $permissions['delete'],
            'can_import' => $permissions['import'],
            'pakets' => $pakets,
            'paket_nama' => $paketNama,
        ]);
    }

    public function data()
    {
        if (!$this->hasMenuAccess('admin/laporan/rab-gedung')) {
            return $this->response->setJSON([
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Akses ditolak.'
            ]);
        }

        $queryFactory = function(bool $forSums = false) {
            $builder = db_connect()->table('trn_rab_gedung_detail');
            if (!$forSums) {
                $builder->select('trn_rab_gedung_detail.*, mst_paket.nama_paket');
            }
            return $builder->join('mst_paket', 'mst_paket.id = trn_rab_gedung_detail.paket_id', 'left');
        };

        $filterApplier = function($builder) {
            $npsn = $this->request->getGet('sekolah_npsn');
            $gedung = $this->request->getGet('gedung');
            $paketId = $this->request->getGet('paket_id');
            $kategori1 = $this->request->getGet('kategori_1');
            $kategori2 = $this->request->getGet('kategori_2');
            if ($npsn !== null && $npsn !== '') {
                $builder->where('trn_rab_gedung_detail.sekolah_npsn', $npsn);
            }
            if ($gedung !== null && $gedung !== '') {
                $builder->where('trn_rab_gedung_detail.gedung', $gedung);
            }
            if ($paketId !== null && $paketId !== '' && $paketId !== 'all') {
                $builder->where('trn_rab_gedung_detail.paket_id', $paketId);
            }
            if ($kategori1 !== null && $kategori1 !== '' && $kategori1 !== 'all') {
                $builder->where('trn_rab_gedung_detail.kategori_1', $kategori1);
            }
            if ($kategori2 !== null && $kategori2 !== '' && $kategori2 !== 'all') {
                $builder->where('trn_rab_gedung_detail.kategori_2', $kategori2);
            }
        };

        $searchColumns = ['nama_sekolah', 'gedung', 'kategori_1', 'kategori_2', 'no_urut', 'uraian', 'satuan', 'mst_paket.nama_paket'];
        $orderColumns = [
            'id',
            'gedung',
            'kategori_1',
            'kategori_2',
            'uraian',
            'satuan',
            'kontrak_volume',
            'kontrak_harga_satuan',
            'kontrak_jumlah_harga',
            'mc_nol_volume',
            'mc_nol_jumlah_harga',
            'tambah_volume',
            'tambah_jumlah_harga',
            'kurang_volume',
            'kurang_jumlah_harga'
        ];

        $rowMapper = function($row) {
            $row['kontrak_volume_formatted'] = $row['kontrak_volume'] !== null ? number_format($row['kontrak_volume'], 2, ',', '.') : '-';
            $row['kontrak_harga_satuan_formatted'] = $row['kontrak_harga_satuan'] !== null ? 'Rp ' . number_format($row['kontrak_harga_satuan'], 0, ',', '.') : '-';
            $row['kontrak_jumlah_harga_formatted'] = $row['kontrak_jumlah_harga'] !== null ? 'Rp ' . number_format($row['kontrak_jumlah_harga'], 0, ',', '.') : '-';
            
            $row['mc_nol_volume_formatted'] = $row['mc_nol_volume'] !== null ? number_format($row['mc_nol_volume'], 2, ',', '.') : '-';
            $row['mc_nol_jumlah_harga_formatted'] = $row['mc_nol_jumlah_harga'] !== null ? 'Rp ' . number_format($row['mc_nol_jumlah_harga'], 0, ',', '.') : '-';
            
            $row['tambah_volume_formatted'] = $row['tambah_volume'] !== null && $row['tambah_volume'] > 0 ? number_format($row['tambah_volume'], 2, ',', '.') : '-';
            $row['tambah_jumlah_harga_formatted'] = $row['tambah_jumlah_harga'] !== null && $row['tambah_jumlah_harga'] > 0 ? 'Rp ' . number_format($row['tambah_jumlah_harga'], 0, ',', '.') : '-';
            
            $row['kurang_volume_formatted'] = $row['kurang_volume'] !== null && $row['kurang_volume'] > 0 ? number_format($row['kurang_volume'], 2, ',', '.') : '-';
            $row['kurang_jumlah_harga_formatted'] = $row['kurang_jumlah_harga'] !== null && $row['kurang_jumlah_harga'] > 0 ? 'Rp ' . number_format($row['kurang_jumlah_harga'], 0, ',', '.') : '-';
            
            $row['bobot_persen_formatted'] = $row['bobot_persen'] !== null ? number_format($row['bobot_persen'], 4, ',', '.') . '%' : '-';
            
            $row['uraian_escaped'] = esc($row['uraian']);
            $row['gedung_escaped'] = esc($row['gedung']);
            $row['kategori_1_escaped'] = esc($row['kategori_1']);
            $row['kategori_2_escaped'] = esc($row['kategori_2']);
            $row['nama_paket_escaped'] = esc($row['nama_paket'] ?? '');
            
            return $row;
        };

        return $this->respondDataTable($queryFactory, $filterApplier, $searchColumns, $orderColumns, $rowMapper);
    }

    public function create()
    {
        $permissions = $this->resolveMenuPermissions('admin/laporan/rab-gedung');
        if (!$permissions['add']) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menambah data.');
        }

        $npsn = $this->request->getPost('sekolah_npsn');
        $sekolahModel = new MstSekolahModel();
        $sekolah = $sekolahModel->find($npsn);
        $namaSekolah = $sekolah ? $sekolah['nama'] : '';

        $kontrakVolume = $this->request->getPost('kontrak_volume') !== '' ? (float)$this->request->getPost('kontrak_volume') : null;
        $kontrakHargaSatuan = $this->request->getPost('kontrak_harga_satuan') !== '' ? (float)$this->request->getPost('kontrak_harga_satuan') : null;
        $mcNolVolume = $this->request->getPost('mc_nol_volume') !== '' ? (float)$this->request->getPost('mc_nol_volume') : null;

        $derived = $this->calculateDerivedFields($kontrakVolume, $kontrakHargaSatuan, $mcNolVolume);

        $data = [
            'sekolah_npsn'         => $npsn ?: null,
            'paket_id'             => $this->request->getPost('paket_id') !== '' ? (int)$this->request->getPost('paket_id') : null,
            'nama_sekolah'         => $namaSekolah,
            'pekerjaan_utama'      => $this->request->getPost('pekerjaan_utama'),
            'gedung'               => $this->request->getPost('gedung'),
            'kategori_1'           => $this->request->getPost('kategori_1'),
            'kategori_2'           => $this->request->getPost('kategori_2'),
            'no_urut'              => $this->request->getPost('no_urut'),
            'uraian'               => $this->request->getPost('uraian'),
            'satuan'               => $this->request->getPost('satuan'),
            'kontrak_volume'       => $kontrakVolume,
            'kontrak_harga_satuan' => $kontrakHargaSatuan,
            'kontrak_jumlah_harga' => $derived['kontrak_jumlah_harga'],
            'tambah_volume'        => $derived['tambah_volume'],
            'tambah_jumlah_harga'  => $derived['tambah_jumlah_harga'],
            'kurang_volume'        => $derived['kurang_volume'],
            'kurang_jumlah_harga'  => $derived['kurang_jumlah_harga'],
            'mc_nol_volume'        => $mcNolVolume,
            'mc_nol_jumlah_harga'  => $derived['mc_nol_jumlah_harga'],
            'bobot_persen'         => $this->request->getPost('bobot_persen') !== '' ? (float)$this->request->getPost('bobot_persen') : null,
            'prestasi_persen'      => $this->request->getPost('prestasi_persen') !== '' ? (float)$this->request->getPost('prestasi_persen') : null,
        ];

        $model = new RabGedungDetailModel();
        if ($model->insert($data)) {
            $redirectUrl = $npsn ? site_url('admin/laporan/rab-gedung/detail/' . $npsn) : site_url('admin/laporan/rab-gedung');
            return redirect()->to($redirectUrl)->with('success', 'Data RAB berhasil ditambahkan.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal menambahkan data.');
    }

    public function edit(int $id)
    {
        $permissions = $this->resolveMenuPermissions('admin/laporan/rab-gedung');
        if (!$permissions['edit']) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengubah data.');
        }

        $model = new RabGedungDetailModel();
        $existing = $model->find($id);
        if (!$existing) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        $npsn = $this->request->getPost('sekolah_npsn');
        $sekolahModel = new MstSekolahModel();
        $sekolah = $sekolahModel->find($npsn);
        $namaSekolah = $sekolah ? $sekolah['nama'] : '';

        $kontrakVolume = $this->request->getPost('kontrak_volume') !== '' ? (float)$this->request->getPost('kontrak_volume') : null;
        $kontrakHargaSatuan = $this->request->getPost('kontrak_harga_satuan') !== '' ? (float)$this->request->getPost('kontrak_harga_satuan') : null;
        $mcNolVolume = $this->request->getPost('mc_nol_volume') !== '' ? (float)$this->request->getPost('mc_nol_volume') : null;

        $derived = $this->calculateDerivedFields($kontrakVolume, $kontrakHargaSatuan, $mcNolVolume);

        $data = [
            'sekolah_npsn'         => $npsn ?: null,
            'paket_id'             => $this->request->getPost('paket_id') !== '' ? (int)$this->request->getPost('paket_id') : null,
            'nama_sekolah'         => $namaSekolah,
            'pekerjaan_utama'      => $this->request->getPost('pekerjaan_utama'),
            'gedung'               => $this->request->getPost('gedung'),
            'kategori_1'           => $this->request->getPost('kategori_1'),
            'kategori_2'           => $this->request->getPost('kategori_2'),
            'no_urut'              => $this->request->getPost('no_urut'),
            'uraian'               => $this->request->getPost('uraian'),
            'satuan'               => $this->request->getPost('satuan'),
            'kontrak_volume'       => $kontrakVolume,
            'kontrak_harga_satuan' => $kontrakHargaSatuan,
            'kontrak_jumlah_harga' => $derived['kontrak_jumlah_harga'],
            'tambah_volume'        => $derived['tambah_volume'],
            'tambah_jumlah_harga'  => $derived['tambah_jumlah_harga'],
            'kurang_volume'        => $derived['kurang_volume'],
            'kurang_jumlah_harga'  => $derived['kurang_jumlah_harga'],
            'mc_nol_volume'        => $mcNolVolume,
            'mc_nol_jumlah_harga'  => $derived['mc_nol_jumlah_harga'],
            'bobot_persen'         => $this->request->getPost('bobot_persen') !== '' ? (float)$this->request->getPost('bobot_persen') : null,
            'prestasi_persen'      => $this->request->getPost('prestasi_persen') !== '' ? (float)$this->request->getPost('prestasi_persen') : null,
        ];

        if ($model->update($id, $data)) {
            $redirectUrl = $npsn ? site_url('admin/laporan/rab-gedung/detail/' . $npsn) : site_url('admin/laporan/rab-gedung');
            return redirect()->to($redirectUrl)->with('success', 'Data RAB berhasil diperbarui.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data.');
    }

    public function delete(int $id)
    {
        $permissions = $this->resolveMenuPermissions('admin/laporan/rab-gedung');
        if (!$permissions['delete']) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk menghapus data.'
            ]);
        }

        $model = new RabGedungDetailModel();
        if ($model->delete($id)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Data RAB berhasil dihapus.'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Gagal menghapus data.'
        ]);
    }

    public function import()
    {
        $permissions = $this->resolveMenuPermissions('admin/laporan/rab-gedung');
        if (!$permissions['import']) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk import.');
        }

        $paketId = $this->request->getPost('paket_id') !== '' ? (int)$this->request->getPost('paket_id') : null;
        if ($paketId === null) {
            return redirect()->back()->with('error', 'Silakan pilih paket terlebih dahulu.');
        }

        $file = $this->request->getFile('file_excel');
        if (! $file || ! $file->isValid()) {
            return redirect()->back()->with('error', 'File tidak valid.');
        }

        $clearData = $this->request->getPost('clear_data') === '1';
        $tempPath = $file->getTempName();

        try {
            if (!class_exists('\\PhpOffice\\PhpSpreadsheet\\IOFactory')) {
                return redirect()->back()->with('error', 'PhpSpreadsheet tidak tersedia.');
            }

            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($tempPath);
            $reader->setReadDataOnly(true);
            $reader->setLoadSheetsOnly(["RAB PER GEDUNG"]);
            
            $spreadsheet = $reader->load($tempPath);
            $sheet = $spreadsheet->getSheetByName("RAB PER GEDUNG");
            
            if ($sheet === null) {
                return redirect()->back()->with('error', 'Sheet "RAB PER GEDUNG" tidak ditemukan pada file Excel.');
            }

            $highestRow = $sheet->getHighestRow();

            $npsnMap = [
                'MTsS  Al Falah Jangkang' => 60729635,
                'MTsS Nurul Hidayah Bantan Tua' => 60730126,
                'MAS Miftahul Jannah Selat Baru' => 69725480,
                'MTSS  Al Irsyadiyah Muntai ' => 60730131,
                'MTSS Darul Aiman Muntai ' => 60730132,
                'MAS Darul Aiman Muntai ' => 69725486,
                'MIS DARUL AIMAN MUNTAI' => 69725290,
                'MTSS Miftahul Ulum Bantan Air' => 60730125
            ];

            $currentLocation = null;
            $currentPekerjaanUtama = null;
            $currentGedung = null;
            $currentCategory1 = null;
            $currentCategory2 = null;

            $itemsToInsert = [];
            $totalParsed = 0;

            $isRoman = function ($str) {
                return preg_match('/^[IVXLCDM]+$/i', trim($str));
            };

            $isTotalRow = function ($str) {
                $strLower = strtolower(trim($str));
                return strpos($strLower, 'jumlah') === 0 || strpos($strLower, 'total') === 0 || strpos($strLower, '=+') === 0;
            };

            $getNumericValue = function ($col, $row) use ($sheet) {
                $cell = $sheet->getCell($col . $row);
                $val = $cell->getValue();
                if (is_string($val) && strpos($val, '=') === 0) {
                    $val = $cell->getOldCalculatedValue();
                }
                if ($val === null || $val === '' || $val === ' ') {
                    return null;
                }
                if (is_numeric($val)) {
                    return (float) $val;
                }
                return null;
            };

            $getStringValue = function ($col, $row) use ($sheet) {
                $cell = $sheet->getCell($col . $row);
                $val = $cell->getValue();
                if (is_string($val) && strpos($val, '=') === 0) {
                    $val = $cell->getOldCalculatedValue();
                }
                return $val !== null ? trim((string)$val) : '';
            };

            for ($row = 1; $row <= $highestRow; $row++) {
                $valA = $getStringValue("A", $row);
                $valB = $getStringValue("B", $row);
                $valD = $getStringValue("D", $row);
                $valE = $getStringValue("E", $row);
                
                if (strtolower($valA) === 'pekerjaan') {
                    $currentPekerjaanUtama = $valD;
                    continue;
                }
                
                if (strtolower($valA) === 'lokasi') {
                    $currentLocation = $valD;
                    $currentGedung = null;
                    $currentCategory1 = null;
                    $currentCategory2 = null;
                    continue;
                }
                
                if ($row <= 14 || $currentLocation === null) {
                    continue;
                }
                
                if ($valA === 'NO' || $valB === 'JENIS PEKERJAAN' || ($valA === '' && $valB === '')) {
                    continue;
                }
                
                if ($isTotalRow($valB) || $isTotalRow($valA)) {
                    continue;
                }
                
                if (preg_match('/^[A-Z]$/', $valA) && !$isRoman($valA)) {
                    $currentGedung = $valB;
                    $currentCategory1 = null;
                    $currentCategory2 = null;
                    continue;
                }
                
                if ($currentGedung === null) {
                    continue;
                }
                
                if ($isRoman($valA) || preg_match('/^[IVXLCDM]+\.\d+$/i', $valA)) {
                    $currentCategory1 = $valB;
                    $currentCategory2 = null;
                    continue;
                }
                
                $valF = $sheet->getCell("F" . $row)->getValue();
                $valG = $sheet->getCell("G" . $row)->getValue();
                if ($valA !== '' && $valE === '' && ($valF === null || $valF === '' || $valF == 0) && ($valG === null || $valG === '' || $valG == 0)) {
                    $currentCategory2 = $valB;
                    continue;
                }
                
                if ($valB !== '') {
                    $sekolahNpsn = $npsnMap[$currentLocation] ?? null;
                    if ($sekolahNpsn === null) {
                        $cleanName = preg_replace('/\s+/', ' ', strtolower(trim($currentLocation)));
                        foreach ($npsnMap as $key => $npsn) {
                            $cleanKey = preg_replace('/\s+/', ' ', strtolower(trim($key)));
                            if ($cleanKey === $cleanName) {
                                $sekolahNpsn = $npsn;
                                break;
                            }
                        }
                    }

                    $itemsToInsert[] = [
                        'sekolah_npsn'         => $sekolahNpsn,
                        'paket_id'             => $paketId,
                        'nama_sekolah'         => $currentLocation,
                        'pekerjaan_utama'      => $currentPekerjaanUtama,
                        'gedung'               => $currentGedung,
                        'kategori_1'           => $currentCategory1,
                        'kategori_2'           => $currentCategory2,
                        'no_urut'              => $valA,
                        'uraian'               => $getStringValue('B', $row),
                        'satuan'               => $valE,
                        'kontrak_volume'       => $getNumericValue('F', $row),
                        'kontrak_harga_satuan' => $getNumericValue('G', $row),
                        'kontrak_jumlah_harga' => $getNumericValue('H', $row),
                        'tambah_volume'        => $getNumericValue('I', $row),
                        'tambah_jumlah_harga'  => $getNumericValue('J', $row),
                        'kurang_volume'        => $getNumericValue('K', $row),
                        'kurang_jumlah_harga'  => $getNumericValue('L', $row),
                        'mc_nol_volume'        => $getNumericValue('M', $row),
                        'mc_nol_jumlah_harga'  => $getNumericValue('N', $row),
                        'bobot_persen'         => $getNumericValue('O', $row),
                        'prestasi_persen'      => $getNumericValue('P', $row),
                        'created_at'           => date('Y-m-d H:i:s'),
                        'updated_at'           => date('Y-m-d H:i:s'),
                    ];
                    
                    $totalParsed++;
                }
            }

            $model = new RabGedungDetailModel();
            if ($clearData) {
                $model->truncate();
            }

            $chunks = array_chunk($itemsToInsert, 200);
            foreach ($chunks as $chunk) {
                $model->insertBatch($chunk);
            }

            return redirect()->back()->with('success', 'Import berhasil! Total ' . $totalParsed . ' baris dimasukkan ke database.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error saat import: ' . $e->getMessage());
        }
    }

    private function calculateDerivedFields(?float $kontrakVolume, ?float $kontrakHargaSatuan, ?float $mcNolVolume): array
    {
        $kontrakJumlahHarga = null;
        $mcNolJumlahHarga = null;
        $tambahVolume = null;
        $tambahJumlahHarga = null;
        $kurangVolume = null;
        $kurangJumlahHarga = null;

        if ($kontrakHargaSatuan !== null) {
            if ($kontrakVolume !== null) {
                $kontrakJumlahHarga = $kontrakVolume * $kontrakHargaSatuan;
            }
            if ($mcNolVolume !== null) {
                $mcNolJumlahHarga = $mcNolVolume * $kontrakHargaSatuan;
            }

            if ($kontrakVolume !== null && $mcNolVolume !== null) {
                $diff = $mcNolVolume - $kontrakVolume;
                if ($diff > 0) {
                    $tambahVolume = $diff;
                    $tambahJumlahHarga = $tambahVolume * $kontrakHargaSatuan;
                    $kurangVolume = 0.0;
                    $kurangJumlahHarga = 0.0;
                } elseif ($diff < 0) {
                    $kurangVolume = abs($diff);
                    $kurangJumlahHarga = $kurangVolume * $kontrakHargaSatuan;
                    $tambahVolume = 0.0;
                    $tambahJumlahHarga = 0.0;
                } else {
                    $tambahVolume = 0.0;
                    $tambahJumlahHarga = 0.0;
                    $kurangVolume = 0.0;
                    $kurangJumlahHarga = 0.0;
                }
            }
        }

        return [
            'kontrak_jumlah_harga' => $kontrakJumlahHarga,
            'mc_nol_jumlah_harga'  => $mcNolJumlahHarga,
            'tambah_volume'        => $tambahVolume,
            'tambah_jumlah_harga'  => $tambahJumlahHarga,
            'kurang_volume'        => $kurangVolume,
            'kurang_jumlah_harga'  => $kurangJumlahHarga,
        ];
    }

    private function respondDataTable(callable $queryFactory, callable $filterApplier, array $searchColumns, array $orderColumns, ?callable $rowMapper = null)
    {
        try {
            $draw = $this->getDataTableDraw();
            $start = $this->getDataTableStart();
            $length = $this->getDataTableLength();
            $search = $this->getDataTableSearchTerm();
            $orderIndex = $this->getDataTableOrderColumnIndex();
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

            // Calculate sum totals on the filtered query
            $sumsBuilder = $queryFactory(true);
            $filterApplier($sumsBuilder);
            $this->applyDataTableSearch($sumsBuilder, $searchColumns, $search);
            $sums = $sumsBuilder->select('SUM(kontrak_jumlah_harga) as sum_kontrak,
                                         SUM(mc_nol_jumlah_harga) as sum_mcnol,
                                         SUM(tambah_jumlah_harga) as sum_tambah,
                                         SUM(kurang_jumlah_harga) as sum_kurang,
                                         SUM(kontrak_volume) as sum_kontrak_vol,
                                         SUM(mc_nol_volume) as sum_mcnol_vol,
                                         SUM(tambah_volume) as sum_tambah_vol,
                                         SUM(kurang_volume) as sum_kurang_vol')
                                ->get()
                                ->getRowArray();

            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $rows,
                'sums' => [
                    'kontrak_jumlah_harga' => (float)($sums['sum_kontrak'] ?? 0),
                    'mc_nol_jumlah_harga'  => (float)($sums['sum_mcnol'] ?? 0),
                    'tambah_jumlah_harga'  => (float)($sums['sum_tambah'] ?? 0),
                    'kurang_jumlah_harga'  => (float)($sums['sum_kurang'] ?? 0),
                    'kontrak_volume'       => (float)($sums['sum_kontrak_vol'] ?? 0),
                    'mc_nol_volume'        => (float)($sums['sum_mcnol_vol'] ?? 0),
                    'tambah_volume'        => (float)($sums['sum_tambah_vol'] ?? 0),
                    'kurang_volume'        => (float)($sums['sum_kurang_vol'] ?? 0),
                ]
            ]);
        } catch (\Throwable $exception) {
            log_message('error', 'DataTable RAB Per Gedung gagal dimuat: {message}', [
                'message' => $exception->getMessage(),
            ]);

            return $this->response->setJSON([
                'draw' => $this->getDataTableDraw(),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Gagal memuat data RAB.',
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

    public function updateSekolahPaket(int $npsn)
    {
        $permissions = $this->resolveMenuPermissions('admin/laporan/rab-gedung');
        if (!$permissions['edit']) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengubah data.');
        }

        $paketId = $this->request->getPost('paket_id');
        $paketIdVal = ($paketId !== null && $paketId !== '') ? (int)$paketId : null;

        $db = db_connect();
        $db->table('trn_rab_gedung_detail')
            ->where('sekolah_npsn', $npsn)
            ->update(['paket_id' => $paketIdVal]);

        return redirect()->back()->with('success', 'Paket sekolah berhasil diperbarui.');
    }
}
