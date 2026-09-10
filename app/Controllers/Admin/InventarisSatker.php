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
    private const MENU_LINK = 'admin/inventaris/barang';
    private const MENU_LINK_ALT = 'admin/inventaris/satker';

    private function checkAccess(): ?RedirectResponse
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            $forbiddenAlt = $this->denyIfNoMenuAccess(self::MENU_LINK_ALT);
            if ($forbiddenAlt instanceof RedirectResponse) {
                return $forbidden;
            }
        }
        return null;
    }

    private function resolvePermissions(): array
    {
        $p1 = $this->resolveMenuPermissions(self::MENU_LINK);
        $p2 = $this->resolveMenuPermissions(self::MENU_LINK_ALT);
        return [
            'add'      => ($p1['add'] ?? false) || ($p2['add'] ?? false),
            'edit'     => ($p1['edit'] ?? false) || ($p2['edit'] ?? false),
            'delete'   => ($p1['delete'] ?? false) || ($p2['delete'] ?? false),
            'export'   => ($p1['export'] ?? false) || ($p2['export'] ?? false),
            'import'   => ($p1['import'] ?? false) || ($p2['import'] ?? false),
            'approval' => ($p1['approval'] ?? false) || ($p2['approval'] ?? false),
        ];
    }

    public function index()
    {
        $forbidden = $this->checkAccess();
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $db = db_connect();

        // Auto-heal: Pastikan seluruh data aset yang peruntukannya masih kosong / NULL di-defaultkan ke 'kantor'
        if ($db->fieldExists('peruntukan', 'trn_inventaris_satker')) {
            $db->table('trn_inventaris_satker')
                ->groupStart()
                    ->where('peruntukan IS NULL', null, false)
                    ->orWhere('peruntukan', '')
                ->groupEnd()
                ->update(['peruntukan' => 'kantor']);
        }

        $model = new InventarisSatkerModel();
        $builder = $model->builder();

        $filterPeruntukan = strtolower(trim((string) $this->request->getGet('peruntukan')));
        $filterKategori   = trim((string) $this->request->getGet('kategori'));
        $filterKondisi    = trim((string) $this->request->getGet('kondisi'));
        $filterLokasi     = trim((string) $this->request->getGet('lokasi'));
        $searchKeyword    = trim((string) $this->request->getGet('keyword'));

        if ($filterPeruntukan === 'kantor') {
            $builder->groupStart()
                ->where('peruntukan', 'kantor')
                ->orWhere('peruntukan IS NULL', null, false)
                ->orWhere('peruntukan', '')
            ->groupEnd();
        } elseif ($filterPeruntukan === 'mobiler') {
            $builder->where('peruntukan', 'mobiler');
        }
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

        // Unique item list for bulk update modal (dikelompokkan berdasarkan kode_barang, nama_barang, dan merk_tipe)
        $uniqueKodeBarangList = $db->table('trn_inventaris_satker')
            ->select('kode_barang, nama_barang, merk_tipe, COUNT(id) AS total_unit, MIN(CAST(NULLIF(nup, "") AS UNSIGNED)) AS min_nup, MAX(CAST(NULLIF(nup, "") AS UNSIGNED)) AS max_nup')
            ->groupBy('kode_barang, nama_barang, merk_tipe')
            ->orderBy('kode_barang', 'ASC')
            ->orderBy('nama_barang', 'ASC')
            ->orderBy('merk_tipe', 'ASC')
            ->get()
            ->getResultArray();

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
        $totalItems   = (int) $db->table('trn_inventaris_satker')->countAllResults();
        $totalMobiler = (int) $db->table('trn_inventaris_satker')->where('peruntukan', 'mobiler')->countAllResults();
        $totalKantor  = (int) $db->table('trn_inventaris_satker')
            ->groupStart()
                ->where('peruntukan', 'kantor')
                ->orWhere('peruntukan IS NULL', null, false)
                ->orWhere('peruntukan', '')
            ->groupEnd()
            ->countAllResults();
        $totalBaik    = (int) $db->table('trn_inventaris_satker')->where('kondisi', 'baik')->countAllResults();
        $totalRingan  = (int) $db->table('trn_inventaris_satker')->where('kondisi', 'rusak_ringan')->countAllResults();
        $totalBerat   = (int) $db->table('trn_inventaris_satker')->where('kondisi', 'rusak_berat')->countAllResults();

        $menuPermissions = $this->resolvePermissions();

        return view('admin/inventaris/satker/index', [
            'pageTitle'            => 'Daftar Barang Inventarisasi',
            'items'                => $items,
            'kategoriList'         => array_column($kategoriList, 'kategori'),
            'lokasiList'           => array_column($lokasiList, 'lokasi_ruangan'),
            'ruanganOptions'       => $ruanganOptions,
            'satuanOptions'        => $satuanOptions,
            'uniqueKodeBarangList' => $uniqueKodeBarangList,
            'filterPeruntukan'     => $filterPeruntukan,
            'filterKategori'       => $filterKategori,
            'filterKondisi'        => $filterKondisi,
            'filterLokasi'         => $filterLokasi,
            'searchKeyword'        => $searchKeyword,
            'summary'              => [
                'total'         => $totalItems,
                'kantor'        => $totalKantor,
                'mobiler'       => $totalMobiler,
                'total_kantor'  => $totalKantor,
                'total_mobiler' => $totalMobiler,
                'baik'          => $totalBaik,
                'rusak_ringan'  => $totalRingan,
                'rusak_berat'   => $totalBerat,
            ],
            'can_add'              => (bool) ($menuPermissions['add'] ?? false),
            'can_edit'             => (bool) ($menuPermissions['edit'] ?? false),
            'can_delete'           => (bool) ($menuPermissions['delete'] ?? false),
            'can_export'           => (bool) ($menuPermissions['export'] ?? false),
            'can_import'           => (bool) ($menuPermissions['import'] ?? false),
        ]);
    }

    public function create()
    {
        $forbidden = $this->checkAccess();
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $menuPermissions = $this->resolvePermissions();
        if (! (bool) ($menuPermissions['add'] ?? false)) {
            return redirect()->to('/admin/inventaris/barang')->with('error', 'Anda tidak memiliki hak akses untuk menambah data inventaris.');
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
            'peruntukan'     => 'permit_empty|in_list[kantor,mobiler]',
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
            return redirect()->to('/admin/inventaris/barang')
                ->withInput()
                ->with('error', $firstError);
        }

        $model = new InventarisSatkerModel();
        $userId = (int) (session()->get('userId') ?? 0);
        $peruntukan = in_array(strtolower(trim((string) $this->request->getPost('peruntukan'))), ['kantor', 'mobiler'], true) ? strtolower(trim((string) $this->request->getPost('peruntukan'))) : 'kantor';

        $model->insert([
            'kode_barang'     => trim((string) $this->request->getPost('kode_barang')),
            'nup'             => trim((string) $this->request->getPost('nup')) ?: null,
            'kode_register'   => trim((string) $this->request->getPost('kode_register')) ?: null,
            'nama_barang'     => trim((string) $this->request->getPost('nama_barang')),
            'kategori'        => trim((string) $this->request->getPost('kategori')),
            'merk_tipe'       => trim((string) $this->request->getPost('merk_tipe')) ?: null,
            'jumlah'          => 1, // Standar BMN: 1 Kode Barang + 1 NUP = 1 Unit Fisik
            'satuan'          => trim((string) $this->request->getPost('satuan')),
            'kondisi'         => trim((string) $this->request->getPost('kondisi')),
            'peruntukan'      => $peruntukan,
            'lokasi_ruangan'  => trim((string) $this->request->getPost('lokasi_ruangan')),
            'tahun_perolehan' => (int) $this->request->getPost('tahun_perolehan') ?: null,
            'keterangan'      => trim((string) $this->request->getPost('keterangan')) ?: null,
            'created_by'      => $userId ?: null,
            'updated_by'      => $userId ?: null,
        ]);

        return redirect()->to('/admin/inventaris/barang')->with('message', 'Data inventaris barang berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $forbidden = $this->checkAccess();
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $menuPermissions = $this->resolvePermissions();
        if (! (bool) ($menuPermissions['edit'] ?? false)) {
            return redirect()->to('/admin/inventaris/barang')->with('error', 'Anda tidak memiliki hak akses untuk mengubah data inventaris.');
        }

        $model = new InventarisSatkerModel();
        $existing = $model->find($id);
        if (! is_array($existing)) {
            return redirect()->to('/admin/inventaris/barang')->with('error', 'Data inventaris tidak ditemukan.');
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
            'peruntukan'     => 'permit_empty|in_list[kantor,mobiler]',
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
            return redirect()->to('/admin/inventaris/barang')
                ->withInput()
                ->with('error', $firstError);
        }

        $userId = (int) (session()->get('userId') ?? 0);
        $peruntukan = in_array(strtolower(trim((string) $this->request->getPost('peruntukan'))), ['kantor', 'mobiler'], true) ? strtolower(trim((string) $this->request->getPost('peruntukan'))) : 'kantor';

        $model->update($id, [
            'kode_barang'     => trim((string) $this->request->getPost('kode_barang')),
            'nup'             => trim((string) $this->request->getPost('nup')) ?: null,
            'kode_register'   => trim((string) $this->request->getPost('kode_register')) ?: null,
            'nama_barang'     => trim((string) $this->request->getPost('nama_barang')),
            'kategori'        => trim((string) $this->request->getPost('kategori')),
            'merk_tipe'       => trim((string) $this->request->getPost('merk_tipe')) ?: null,
            'jumlah'          => 1, // Standar BMN: 1 Kode Barang + 1 NUP = 1 Unit Fisik
            'satuan'          => trim((string) $this->request->getPost('satuan')),
            'kondisi'         => trim((string) $this->request->getPost('kondisi')),
            'peruntukan'      => $peruntukan,
            'lokasi_ruangan'  => trim((string) $this->request->getPost('lokasi_ruangan')),
            'tahun_perolehan' => (int) $this->request->getPost('tahun_perolehan') ?: null,
            'keterangan'      => trim((string) $this->request->getPost('keterangan')) ?: null,
            'updated_by'      => $userId ?: null,
        ]);

        return redirect()->to('/admin/inventaris/barang')->with('message', 'Data inventaris berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        $forbidden = $this->checkAccess();
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $menuPermissions = $this->resolvePermissions();
        if (! (bool) ($menuPermissions['delete'] ?? false)) {
            return redirect()->to('/admin/inventaris/barang')->with('error', 'Anda tidak memiliki hak akses untuk menghapus data inventaris.');
        }

        $model = new InventarisSatkerModel();
        $existing = $model->find($id);
        if (! is_array($existing)) {
            return redirect()->to('/admin/inventaris/barang')->with('error', 'Data inventaris tidak ditemukan.');
        }

        $model->delete($id);

        return redirect()->to('/admin/inventaris/barang')->with('message', 'Data inventaris barang berhasil dihapus.');
    }

    public function updatePeruntukanMassal()
    {
        $forbidden = $this->checkAccess();
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $menuPermissions = $this->resolvePermissions();
        if (! (bool) ($menuPermissions['edit'] ?? false)) {
            return redirect()->to('/admin/inventaris/barang')->with('error', 'Anda tidak memiliki hak akses untuk mengubah data inventaris.');
        }

        $kodeBarang = trim((string) $this->request->getPost('kode_barang'));
        $namaBarang = trim((string) $this->request->getPost('nama_barang'));
        $merkTipe   = trim((string) $this->request->getPost('merk_tipe'));

        if (strpos($kodeBarang, ':::') !== false) {
            $parts = explode(':::', $kodeBarang);
            $kodeBarang = trim($parts[0] ?? '');
            if ($namaBarang === '') {
                $namaBarang = trim($parts[1] ?? '');
            }
            if ($merkTipe === '') {
                $merkTipe = trim($parts[2] ?? '');
            }
        }

        $lingkupNup = strtolower(trim((string) $this->request->getPost('lingkup_nup')));
        if (! in_array($lingkupNup, ['semua', 'sebagian'], true)) {
            $lingkupNup = 'semua';
        }
        $peruntukan = strtolower(trim((string) $this->request->getPost('peruntukan')));

        if ($kodeBarang === '') {
            return redirect()->to('/admin/inventaris/barang')->with('error', 'Silakan pilih Kode Barang terlebih dahulu.');
        }

        if (! in_array($peruntukan, ['kantor', 'mobiler'], true)) {
            return redirect()->to('/admin/inventaris/barang')->with('error', 'Pilihan peruntukan tidak valid. Pilih "Kantor" atau "Mobiler".');
        }

        $db = db_connect();
        $userId = (int) (session()->get('userId') ?? 0);
        $labelTarget = ($peruntukan === 'mobiler') ? 'Mobiler (Sekolah)' : 'Kantor (Satker)';

        $builder = $db->table('trn_inventaris_satker')
            ->select('id, nama_barang, merk_tipe, nup')
            ->where('kode_barang', $kodeBarang);

        if ($namaBarang !== '') {
            $builder->where('nama_barang', $namaBarang);
        }
        if ($merkTipe !== '') {
            $builder->where('merk_tipe', $merkTipe);
        }

        // 1. Opsi: Update SELURUH NUP (Hanya barang dengan Kode, Nama, dan Merk/Tipe yang sama)
        if ($lingkupNup === 'semua') {
            $targetRows = $builder->get()->getResultArray();
            $ids = array_column($targetRows, 'id');

            if (empty($ids)) {
                return redirect()->to('/admin/inventaris/barang')
                    ->with('error', "Tidak ditemukan data aset yang cocok dengan kriteria barang yang dipilih.");
            }

            $db->table('trn_inventaris_satker')
                ->whereIn('id', $ids)
                ->update([
                    'peruntukan' => $peruntukan,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'updated_by' => $userId ?: null,
                ]);

            $count = count($ids);
            $namaSample = $targetRows[0]['nama_barang'] ?? $namaBarang;
            $merkWording = ! empty($merkTipe) ? " [{$merkTipe}]" : (! empty($targetRows[0]['merk_tipe']) ? " [{$targetRows[0]['merk_tipe']}]" : '');

            return redirect()->to('/admin/inventaris/barang')->with(
                'message',
                "Berhasil memperbarui seluruh ({$count} unit) aset \"{$namaSample}\"{$merkWording} (Kode: {$kodeBarang}) menjadi peruntukan \"{$labelTarget}\"."
            );
        }

        // 2. Opsi: Update SEBAGIAN NUP (Rentang NUP Tertentu pada Kode, Nama, dan Merk/Tipe yang sama)
        $nupAwal  = (int) $this->request->getPost('nup_awal');
        $nupAkhir = (int) $this->request->getPost('nup_akhir');

        if ($nupAwal <= 0 || $nupAkhir <= 0) {
            return redirect()->to('/admin/inventaris/barang')->with('error', 'NUP Awal dan NUP Akhir harus berupa angka positif (minimal 1) untuk update sebagian NUP.');
        }

        if ($nupAwal > $nupAkhir) {
            return redirect()->to('/admin/inventaris/barang')->with('error', 'NUP Awal (' . $nupAwal . ') tidak boleh lebih besar dari NUP Akhir (' . $nupAkhir . ').');
        }

        $builder->where('CAST(NULLIF(nup, "") AS UNSIGNED) >=', $nupAwal, false)
                ->where('CAST(NULLIF(nup, "") AS UNSIGNED) <=', $nupAkhir, false);

        $targetRows = $builder->get()->getResultArray();
        $ids = array_column($targetRows, 'id');

        if (empty($ids)) {
            return redirect()->to('/admin/inventaris/barang')
                ->with('error', "Tidak ditemukan data aset yang cocok dalam rentang NUP {$nupAwal} s.d {$nupAkhir}.");
        }

        $db->table('trn_inventaris_satker')
            ->whereIn('id', $ids)
            ->update([
                'peruntukan' => $peruntukan,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => $userId ?: null,
            ]);

        $count = count($ids);
        $namaSample = $targetRows[0]['nama_barang'] ?? $namaBarang;
        $merkWording = ! empty($merkTipe) ? " [{$merkTipe}]" : (! empty($targetRows[0]['merk_tipe']) ? " [{$targetRows[0]['merk_tipe']}]" : '');

        return redirect()->to('/admin/inventaris/barang')->with(
            'message',
            "Berhasil memperbarui {$count} unit aset \"{$namaSample}\"{$merkWording} (Kode: {$kodeBarang}, NUP: {$nupAwal} s.d {$nupAkhir}) menjadi peruntukan \"{$labelTarget}\"."
        );
    }

    public function getNupRangeByKode()
    {
        $kode = trim((string) ($this->request->getGet('kode_barang') ?: $this->request->getGet('kode')));
        $nama = trim((string) $this->request->getGet('nama_barang'));
        $merk = trim((string) $this->request->getGet('merk_tipe'));

        if (strpos($kode, ':::') !== false) {
            $parts = explode(':::', $kode);
            $kode = trim($parts[0] ?? '');
            if ($nama === '') {
                $nama = trim($parts[1] ?? '');
            }
            if ($merk === '') {
                $merk = trim($parts[2] ?? '');
            }
        }

        if ($kode === '') {
            return $this->response->setJSON([
                'status'  => 'error',
                'success' => false,
                'message' => 'Parameter kode barang tidak boleh kosong.',
            ]);
        }

        $db = db_connect();
        $builder = $db->table('trn_inventaris_satker')
            ->select('kode_barang, nama_barang, merk_tipe, COUNT(id) AS total_unit,
                      MIN(CAST(NULLIF(nup, "") AS UNSIGNED)) AS min_nup,
                      MAX(CAST(NULLIF(nup, "") AS UNSIGNED)) AS max_nup,
                      SUM(CASE WHEN peruntukan = "kantor" THEN 1 ELSE 0 END) AS count_kantor,
                      SUM(CASE WHEN peruntukan = "mobiler" THEN 1 ELSE 0 END) AS count_mobiler')
            ->where('kode_barang', $kode);

        if ($nama !== '') {
            $builder->where('nama_barang', $nama);
        }
        if ($merk !== '') {
            $builder->where('merk_tipe', $merk);
        }

        $row = $builder->groupBy('kode_barang, nama_barang, merk_tipe')
            ->get()
            ->getRowArray();

        if (! $row) {
            return $this->response->setJSON([
                'status'  => 'not_found',
                'success' => false,
                'message' => "Data aset tidak ditemukan.",
            ]);
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'success' => true,
            'data'    => [
                'kode_barang'   => $row['kode_barang'],
                'nama_barang'   => $row['nama_barang'],
                'merk_tipe'     => $row['merk_tipe'] ?? '',
                'total_unit'    => (int) ($row['total_unit'] ?? 0),
                'min_nup'       => (int) ($row['min_nup'] ?? 1),
                'max_nup'       => (int) ($row['max_nup'] ?? 1),
                'count_kantor'  => (int) ($row['count_kantor'] ?? 0),
                'count_mobiler' => (int) ($row['count_mobiler'] ?? 0),
            ],
        ]);
    }

    public function importSiman()
    {
        $forbidden = $this->checkAccess();
        if ($forbidden instanceof RedirectResponse) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Sesi berakhir atau akses ditolak.'])->setStatusCode(403);
            }
            return $forbidden;
        }

        $menuPermissions = $this->resolvePermissions();
        if (! (bool) ($menuPermissions['import'] ?? false)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Anda tidak memiliki hak akses untuk mengimpor data inventaris.'])->setStatusCode(403);
            }
            return redirect()->to('/admin/inventaris/barang')->with('error', 'Anda tidak memiliki hak akses untuk mengimpor data inventaris.');
        }

        $file = $this->request->getFile('file_excel');
        if (! $file || ! $file->isValid()) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Silakan pilih file Excel SIMAN yang valid untuk diunggah.'])->setStatusCode(400);
            }
            return redirect()->to('/admin/inventaris/barang')->with('error', 'Silakan pilih file Excel SIMAN yang valid untuk diunggah.');
        }

        $ext = strtolower($file->getClientExtension());
        if (! in_array($ext, ['xlsx', 'xls'], true)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Format file harus berupa .xlsx atau .xls.'])->setStatusCode(400);
            }
            return redirect()->to('/admin/inventaris/barang')->with('error', 'Format file harus berupa .xlsx atau .xls.');
        }

        ini_set('memory_limit', '512M');
        set_time_limit(300);

        try {
            $spreadsheet = IOFactory::load($file->getTempName());
            $sheet = $spreadsheet->getSheetByName('Master Aset') ?: $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();

            if ($highestRow < 2) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'File Excel tidak memiliki data aset yang cukup.'])->setStatusCode(400);
                }
                return redirect()->to('/admin/inventaris/barang')->with('error', 'File Excel tidak memiliki data aset yang cukup.');
            }

            $db = db_connect();
            $userId = (int) (session()->get('userId') ?? 0);
            $now = date('Y-m-d H:i:s');

            // 1. Deteksi Baris Header & Mapping Kolom Dinamis (Mendukung File SIMAN Resmi & Template Ekspor Aplikasi)
            $colMap = [
                'kode_barang'     => 'E',
                'nup'             => 'F',
                'kode_register'   => 'BT',
                'nama_barang'     => 'G',
                'kategori'        => 'B',
                'status_bmn'      => 'H',
                'merk'            => 'I',
                'tipe'            => 'J',
                'merk_tipe'       => '',
                'kondisi'         => 'K',
                'tgl_perolehan'   => 'AH',
                'nilai_perolehan' => 'AL',
                'nilai_buku'      => 'AN',
                'no_psp'          => 'AY',
                'lokasi_ruangan'  => 'BU',
                'jumlah'          => '',
                'satuan'          => '',
                'peruntukan'      => '',
                'tahun_perolehan' => '',
                'keterangan'      => '',
            ];

            $startRow = 3; // Default untuk file resmi SIMAN Kemenkeu (header baris 1, data baris 3)

            $highestColumn = $sheet->getHighestColumn();
            $highestColIdx = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

            for ($row = 1; $row <= min(8, $highestRow); $row++) {
                $rowHeaders = [];
                for ($col = 1; $col <= $highestColIdx; $col++) {
                    $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                    $val = strtolower(trim((string) $sheet->getCell($letter . $row)->getValue()));
                    if ($val !== '') {
                        $rowHeaders[$letter] = $val;
                    }
                }

                $detected = [];
                foreach ($rowHeaders as $letter => $val) {
                    if (preg_match('/kode\s*barang|^kode$|kd_?brg/i', $val)) {
                        $detected['kode_barang'] = $letter;
                    } elseif (preg_match('/^nup$|no\.?\s*nup/i', $val)) {
                        $detected['nup'] = $letter;
                    } elseif (preg_match('/kode\s*register|no\.?\s*register/i', $val)) {
                        $detected['kode_register'] = $letter;
                    } elseif (preg_match('/nama\s*barang|^nama$|uraian\s*barang/i', $val)) {
                        $detected['nama_barang'] = $letter;
                    } elseif (preg_match('/kategori|jenis\s*bmn/i', $val)) {
                        $detected['kategori'] = $letter;
                    } elseif (preg_match('/merk\s*[\/\-]?\s*tipe/i', $val)) {
                        $detected['merk_tipe'] = $letter;
                    } elseif (preg_match('/^merk$/i', $val)) {
                        $detected['merk'] = $letter;
                    } elseif (preg_match('/^tipe$|^type$/i', $val)) {
                        $detected['tipe'] = $letter;
                    } elseif (preg_match('/peruntukan/i', $val)) {
                        $detected['peruntukan'] = $letter;
                    } elseif (preg_match('/kondisi/i', $val)) {
                        $detected['kondisi'] = $letter;
                    } elseif (preg_match('/lokasi\s*ruang|lokasi|ruangan/i', $val)) {
                        $detected['lokasi_ruangan'] = $letter;
                    } elseif (preg_match('/^(jumlah|kuantum|qty)(\s*barang)?$/i', $val) && ! preg_match('/foto|lantai|kamar|orang|pintu/i', $val)) {
                        $detected['jumlah'] = $letter;
                    } elseif (preg_match('/satuan/i', $val)) {
                        $detected['satuan'] = $letter;
                    } elseif (preg_match('/nilai\s*perolehan/i', $val)) {
                        $detected['nilai_perolehan'] = $letter;
                    } elseif (preg_match('/nilai\s*buku/i', $val)) {
                        $detected['nilai_buku'] = $letter;
                    } elseif (preg_match('/status\s*bmn|^status$/i', $val)) {
                        $detected['status_bmn'] = $letter;
                    } elseif (preg_match('/no\.?\s*psp/i', $val)) {
                        $detected['no_psp'] = $letter;
                    } elseif (preg_match('/tanggal\s*perolehan|tgl\s*perolehan/i', $val)) {
                        $detected['tgl_perolehan'] = $letter;
                    } elseif (preg_match('/tahun\s*perolehan|^tahun$/i', $val)) {
                        $detected['tahun_perolehan'] = $letter;
                    } elseif (preg_match('/keterangan|catatan/i', $val)) {
                        $detected['keterangan'] = $letter;
                    }
                }

                if (isset($detected['kode_barang']) && (isset($detected['nama_barang']) || isset($detected['nup']))) {
                    $colMap = array_merge($colMap, $detected);
                    $startRow = $row + 1;
                    if ($startRow <= $highestRow) {
                        $nextVal = trim((string) $sheet->getCell($colMap['kode_barang'] . $startRow)->getValue());
                        $nextName = ! empty($colMap['nama_barang']) ? trim((string) $sheet->getCell($colMap['nama_barang'] . $startRow)->getValue()) : '';
                        if ($nextVal === '' && $nextName === '') {
                            $startRow++;
                        }
                    }
                    break;
                }
            }

            // 2. Normalisasi Helper
            $normalizeKode = static function ($val): string {
                return strtoupper(trim((string) $val));
            };

            $normalizeNup = static function ($val): string {
                $n = trim((string) $val);
                if ($n === '') {
                    return '';
                }
                if (is_numeric($n)) {
                    return (string) ((int) $n);
                }
                return strtolower($n);
            };

            // 3. Muat Data Yang Sudah Ada di Database (Map Composite Key: kode_barang + nup)
            $existingRows = $db->table('trn_inventaris_satker')
                ->select('id, kode_barang, nup, kode_register, ruangan_id, lokasi_ruangan, peruntukan')
                ->get()
                ->getResultArray();

            $existingMap = [];
            $usedRegisters = [];

            foreach ($existingRows as $er) {
                $normKey = $normalizeKode($er['kode_barang']) . '___' . $normalizeNup($er['nup']);
                $existingMap[$normKey] = [
                    'id'             => (int) $er['id'],
                    'ruangan_id'     => (int) ($er['ruangan_id'] ?? 0),
                    'lokasi_ruangan' => (string) ($er['lokasi_ruangan'] ?? ''),
                    'peruntukan'     => (string) ($er['peruntukan'] ?? 'kantor'),
                    'kode_register'  => (string) ($er['kode_register'] ?? ''),
                ];

                $reg = trim((string) ($er['kode_register'] ?? ''));
                if ($reg !== '') {
                    $usedRegisters[$reg] = (int) $er['id'];
                }
            }

            $importedCount = 0;
            $updatedCount  = 0;

            for ($r = $startRow; $r <= $highestRow; $r++) {
                $getVal = static function ($field) use ($sheet, $colMap, $r): string {
                    $col = $colMap[$field] ?? '';
                    return ($col !== '') ? trim((string) $sheet->getCell($col . $r)->getValue()) : '';
                };

                $kodeBarang   = $getVal('kode_barang');
                $nup          = $getVal('nup');
                $namaBarang   = $getVal('nama_barang');
                $kodeRegister = $getVal('kode_register');

                if ($kodeBarang === '' && $namaBarang === '') {
                    continue;
                }
                if ($kodeBarang === '') {
                    continue;
                }

                $kategori     = $getVal('kategori') ?: 'Peralatan Kantor';
                $statusBmn    = $getVal('status_bmn') ?: 'Aktif';
                $merk         = $getVal('merk');
                $tipe         = $getVal('tipe');
                $merkTipe     = $getVal('merk_tipe');
                $rawKondisi   = $getVal('kondisi');
                $noPsp        = $getVal('no_psp');
                $lokasiRuang  = $getVal('lokasi_ruangan');
                $peruntukanIn = strtolower($getVal('peruntukan'));
                $satuanIn     = $getVal('satuan');
                $jumlahIn     = (int) $getVal('jumlah');
                $keteranganIn = $getVal('keterangan');

                // Parsing nilai finansial
                $colNilaiPerolehan = $colMap['nilai_perolehan'] ?? '';
                $nilaiPerolehan = ($colNilaiPerolehan !== '') ? (float) ($sheet->getCell($colNilaiPerolehan . $r)->getValue() ?? 0) : 0.0;

                $colNilaiBuku = $colMap['nilai_buku'] ?? '';
                $nilaiBuku = ($colNilaiBuku !== '') ? (float) ($sheet->getCell($colNilaiBuku . $r)->getValue() ?? 0) : 0.0;

                // Format merk_tipe jika tidak tersedia kolom gabungan
                if ($merkTipe === '') {
                    if ($merk !== '' && $tipe !== '') {
                        $merkTipe = ($merk === $tipe) ? $merk : ($merk . ' ' . $tipe);
                    } elseif ($merk !== '') {
                        $merkTipe = $merk;
                    } else {
                        $merkTipe = $tipe;
                    }
                }

                // Format kondisi fisik
                $kondisi = 'baik';
                $lk = strtolower($rawKondisi);
                if (strpos($lk, 'berat') !== false) {
                    $kondisi = 'rusak_berat';
                } elseif (strpos($lk, 'ringan') !== false) {
                    $kondisi = 'rusak_ringan';
                }

                // Format tanggal & tahun perolehan
                $colTgl = $colMap['tgl_perolehan'] ?? '';
                $rawTgl = ($colTgl !== '') ? $sheet->getCell($colTgl . $r)->getValue() : null;

                $colTahun = $colMap['tahun_perolehan'] ?? '';
                $rawTahun = ($colTahun !== '') ? trim((string) $sheet->getCell($colTahun . $r)->getValue()) : '';

                $tglPerolehan   = null;
                $tahunPerolehan = is_numeric($rawTahun) ? (int) $rawTahun : null;

                if (is_numeric($rawTgl) && $rawTgl > 10000) {
                    try {
                        $dt = ExcelDate::excelToDateTimeObject($rawTgl);
                        $tglPerolehan = $dt->format('Y-m-d');
                        if ($tahunPerolehan === null) {
                            $tahunPerolehan = (int) $dt->format('Y');
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }
                } elseif (! empty($rawTgl)) {
                    $ts = strtotime((string) $rawTgl);
                    if ($ts !== false) {
                        $tglPerolehan = date('Y-m-d', $ts);
                        if ($tahunPerolehan === null) {
                            $tahunPerolehan = (int) date('Y', $ts);
                        }
                    }
                }

                // Normalisasi NUP untuk matching dan penyimpanan
                $cleanNup = $normalizeNup($nup);
                $saveNup  = ($cleanNup !== '') ? $cleanNup : null;

                $mapKey = $normalizeKode($kodeBarang) . '___' . $cleanNup;

                // ATURAN UPSERT:
                // Jika Kode Barang dan NUP sama -> UPDATE SAJA
                // Jika Kode Barang atau NUP beda -> INSERT
                if (isset($existingMap[$mapKey])) {
                    // ========================================================
                    // KODE BARANG & NUP SAMA -> UPDATE SAJA
                    // ========================================================
                    $existingItem = $existingMap[$mapKey];
                    $existingId   = $existingItem['id'];

                    $updateData = [
                        'nama_barang'     => $namaBarang,
                        'kategori'        => $kategori,
                        'kondisi'         => $kondisi,
                        'nilai_perolehan' => $nilaiPerolehan,
                        'nilai_buku'      => $nilaiBuku,
                        'status_bmn'      => $statusBmn,
                        'updated_at'      => $now,
                        'updated_by'      => $userId ?: null,
                    ];

                    if ($merk !== '') $updateData['merk'] = $merk;
                    if ($tipe !== '') $updateData['tipe'] = $tipe;
                    if ($merkTipe !== '') $updateData['merk_tipe'] = $merkTipe;
                    if ($satuanIn !== '') $updateData['satuan'] = $satuanIn;
                    $updateData['jumlah'] = 1; // Standar BMN: 1 Kode Barang + 1 NUP = 1 Unit Fisik
                    if ($noPsp !== '') $updateData['no_psp'] = $noPsp;
                    if ($tahunPerolehan !== null) $updateData['tahun_perolehan'] = $tahunPerolehan;
                    if ($tglPerolehan !== null) $updateData['tgl_perolehan'] = $tglPerolehan;
                    if ($keteranganIn !== '') $updateData['keterangan'] = $keteranganIn;

                    // Peruntukan: update jika file excel secara eksplisit menyertakan kolom peruntukan ('kantor' atau 'mobiler')
                    if (in_array($peruntukanIn, ['kantor', 'mobiler'], true)) {
                        $updateData['peruntukan'] = $peruntukanIn;
                    }

                    // Lokasi ruangan: jika aset belum masuk ke ruangan DBR (ruangan_id kosong), perbarui lokasinya
                    if (empty($existingItem['ruangan_id']) && $lokasiRuang !== '') {
                        $updateData['lokasi_ruangan'] = $lokasiRuang;
                    }

                    // Kode register: update jika ada pada Excel dan tidak bentrok dengan ID aset lain
                    if ($kodeRegister !== '') {
                        if (! isset($usedRegisters[$kodeRegister]) || $usedRegisters[$kodeRegister] === $existingId) {
                            $updateData['kode_register'] = $kodeRegister;
                            $usedRegisters[$kodeRegister] = $existingId;
                        }
                    }

                    $db->table('trn_inventaris_satker')->where('id', $existingId)->update($updateData);
                    $updatedCount++;
                } else {
                    // ========================================================
                    // KODE BARANG / NUP BEDA -> INSERT DATA BARU
                    // ========================================================
                    $regToInsert = null;
                    if ($kodeRegister !== '' && ! isset($usedRegisters[$kodeRegister])) {
                        $regToInsert = $kodeRegister;
                    }

                    $insertData = [
                        'kode_barang'     => $kodeBarang,
                        'nup'             => $saveNup,
                        'kode_register'   => $regToInsert,
                        'nama_barang'     => $namaBarang,
                        'kategori'        => $kategori,
                        'merk'            => $merk ?: null,
                        'tipe'            => $tipe ?: null,
                        'merk_tipe'       => $merkTipe ?: null,
                        'jumlah'          => 1, // Standar BMN: 1 Kode Barang + 1 NUP = 1 Unit Fisik
                        'satuan'          => $satuanIn ?: 'Buah',
                        'kondisi'         => $kondisi,
                        'lokasi_ruangan'  => $lokasiRuang ?: 'Ruang Kantor Satker',
                        'nilai_perolehan' => $nilaiPerolehan,
                        'nilai_buku'      => $nilaiBuku,
                        'status_bmn'      => $statusBmn,
                        'peruntukan'      => in_array($peruntukanIn, ['kantor', 'mobiler'], true) ? $peruntukanIn : 'kantor',
                        'no_psp'          => $noPsp ?: null,
                        'tahun_perolehan' => $tahunPerolehan,
                        'tgl_perolehan'   => $tglPerolehan,
                        'keterangan'      => $keteranganIn ?: null,
                        'created_at'      => $now,
                        'updated_at'      => $now,
                        'created_by'      => $userId ?: null,
                        'updated_by'      => $userId ?: null,
                    ];

                    $db->table('trn_inventaris_satker')->insert($insertData);
                    $newId = (int) $db->insertID();

                    // Daftarkan ke map agar baris berikutnya jika sama di file yang sama akan di-update
                    $existingMap[$mapKey] = [
                        'id'             => $newId,
                        'ruangan_id'     => 0,
                        'lokasi_ruangan' => $insertData['lokasi_ruangan'],
                        'peruntukan'     => $insertData['peruntukan'],
                        'kode_register'  => (string) $regToInsert,
                    ];

                    if ($regToInsert !== null) {
                        $usedRegisters[$regToInsert] = $newId;
                    }

                    $importedCount++;
                }
            }

            $totalProses = $importedCount + $updatedCount;
            $successMsg  = "Proses import Excel selesai! {$importedCount} data baru ditambahkan dan {$updatedCount} data diperbarui berdasarkan kecocokan Kode Barang & NUP (Total: {$totalProses} aset).";

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'         => 'success',
                    'message'        => $successMsg,
                    'imported_count' => $importedCount,
                    'updated_count'  => $updatedCount,
                    'total_proses'   => $totalProses,
                    'csrfHash'       => csrf_hash(),
                ]);
            }

            return redirect()->to('/admin/inventaris/barang')->with('message', $successMsg);
        } catch (\Throwable $e) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'   => 'error',
                    'message'  => 'Gagal memproses file Excel SIMAN: ' . $e->getMessage(),
                    'csrfHash' => csrf_hash(),
                ])->setStatusCode(500);
            }
            return redirect()->to('/admin/inventaris/barang')
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
            return redirect()->to('/admin/inventaris/barang')->with('error', 'Anda tidak memiliki izin untuk mengunduh laporan.');
        }

        $model = new InventarisSatkerModel();
        $builder = $model->builder();

        $filterPeruntukan = strtolower(trim((string) $this->request->getGet('peruntukan')));
        $filterKategori   = trim((string) $this->request->getGet('kategori'));
        $filterKondisi    = trim((string) $this->request->getGet('kondisi'));
        $filterLokasi     = trim((string) $this->request->getGet('lokasi'));

        if (in_array($filterPeruntukan, ['kantor', 'mobiler'], true)) {
            $builder->where('peruntukan', $filterPeruntukan);
        }
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
        $sheet->setTitle('Daftar Barang BMN');

        // Header Title
        $sheet->mergeCells('A1:L1');
        $sheet->mergeCells('A2:L2');
        $sheet->mergeCells('A3:L3');
        $sheet->mergeCells('A4:L4');

        $sheet->setCellValue('A1', 'DAFTAR INVENTARISASI BARANG MILIK NEGARA / SARANA BMN');
        $sheet->setCellValue('A2', 'SATUAN KERJA PELAKSANAAN PRASARANA STRATEGIS RIAU');
        $sheet->setCellValue('A3', 'DIREKTORAT JENDERAL PRASARANA STRATEGIS');
        $sheet->setCellValue('A4', 'KEMENTERIAN PEKERJAAN UMUM');

        $sheet->getStyle('A1:L4')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A1:L4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:L4')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // Table Header
        $headers = [
            'A6' => 'NO.',
            'B6' => 'KODE BARANG',
            'C6' => 'NUP',
            'D6' => 'KODE REGISTER',
            'E6' => 'NAMA BARANG',
            'F6' => 'KATEGORI',
            'G6' => 'MERK / TIPE',
            'H6' => 'PERUNTUKAN',
            'I6' => 'JUMLAH',
            'J6' => 'KONDISI',
            'K6' => 'LOKASI RUANGAN',
            'L6' => 'THN PEROLEHAN',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $sheet->getStyle('A6:L6')->getFont()->setBold(true);
        $sheet->getStyle('A6:L6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A6:L6')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A6:L6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');

        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(36);
        $sheet->getColumnDimension('E')->setWidth(35);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(25);
        $sheet->getColumnDimension('H')->setWidth(18);
        $sheet->getColumnDimension('I')->setWidth(14);
        $sheet->getColumnDimension('J')->setWidth(16);
        $sheet->getColumnDimension('K')->setWidth(25);
        $sheet->getColumnDimension('L')->setWidth(16);

        $rowNum = 7;
        $no = 1;
        foreach ($items as $item) {
            $kondisiLabel = match ($item['kondisi']) {
                'baik'         => 'Baik',
                'rusak_ringan' => 'Rusak Ringan',
                'rusak_berat'  => 'Rusak Berat',
                default        => ucfirst($item['kondisi'] ?? '-'),
            };

            $peruntukanLabel = ($item['peruntukan'] ?? 'kantor') === 'mobiler' ? 'Mobiler (Sekolah)' : 'Kantor (Satker)';

            $sheet->setCellValue('A' . $rowNum, $no++);
            $sheet->setCellValueExplicit('B' . $rowNum, (string) ($item['kode_barang'] ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C' . $rowNum, (string) ($item['nup'] ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D' . $rowNum, (string) ($item['kode_register'] ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('E' . $rowNum, (string) ($item['nama_barang'] ?? ''));
            $sheet->setCellValue('F' . $rowNum, (string) ($item['kategori'] ?? ''));
            $sheet->setCellValue('G' . $rowNum, (string) ($item['merk_tipe'] ?? '-'));
            $sheet->setCellValue('H' . $rowNum, $peruntukanLabel);
            $sheet->setCellValue('I' . $rowNum, ($item['jumlah'] ?? 0) . ' ' . ($item['satuan'] ?? 'Unit'));
            $sheet->setCellValue('J' . $rowNum, $kondisiLabel);
            $sheet->setCellValue('K' . $rowNum, (string) ($item['lokasi_ruangan'] ?? ''));
            $sheet->setCellValue('L' . $rowNum, (string) ($item['tahun_perolehan'] ?? '-'));

            $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('I' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('J' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('L' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $rowNum++;
        }

        $lastRow = $rowNum - 1;
        if ($lastRow >= 7) {
            $sheet->getStyle('A6:L' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Daftar_Barang_BMN_' . date('Ymd_His') . '.xlsx';

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
