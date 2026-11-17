<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\BmnSekolahRakyatModel;
use App\Models\BmnSekolahRakyatFotoModel;
use App\Models\BmnSekolahRakyatDasarPerhitunganModel;
use App\Libraries\TimeMarkWatermarkService;
use CodeIgniter\HTTP\RedirectResponse;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class InventarisSekolahRakyat extends BaseController
{
    private const MENU_BARANG_LINK = 'admin/inventaris/sekolah-rakyat/barang';
    private const MENU_FOTO_LINK   = 'admin/inventaris/sekolah-rakyat/foto';
    private const MENU_REKAP_LINK  = 'admin/inventaris/sekolah-rakyat/rekapitulasi';

    protected BmnSekolahRakyatModel $bmnModel;
    protected BmnSekolahRakyatFotoModel $fotoModel;
    protected BmnSekolahRakyatDasarPerhitunganModel $dasarModel;

    public function __construct()
    {
        $this->bmnModel   = new BmnSekolahRakyatModel();
        $this->fotoModel  = new BmnSekolahRakyatFotoModel();
        $this->dasarModel = new BmnSekolahRakyatDasarPerhitunganModel();
    }

    /**
     * Submenu 1: Daftar Barang BMN Sekolah Rakyat
     */
    public function barang()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_BARANG_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $permissions = $this->resolveMenuPermissions(self::MENU_BARANG_LINK);

        $filterKelompok   = trim((string) $this->request->getGet('kelompok'));
        $filterLokasi     = trim((string) $this->request->getGet('lokasi'));
        $filterStatusFoto = trim((string) $this->request->getGet('status_foto'));
        $filterKondisi    = trim((string) $this->request->getGet('kondisi'));
        $keyword          = trim((string) $this->request->getGet('keyword'));

        $builder = $this->bmnModel->builder()
            ->select('trn_bmn_sekolah_rakyat.*, COUNT(f.id) AS foto_count, MIN(f.foto_watermark) AS sample_foto_wm, MIN(f.foto_asli) AS sample_foto_asli')
            ->join('trn_bmn_sekolah_rakyat_foto f', 'f.bmn_id = trn_bmn_sekolah_rakyat.id', 'left')
            ->groupBy('trn_bmn_sekolah_rakyat.id');

        if ($filterKelompok !== '' && $filterKelompok !== '*') {
            $builder->where('trn_bmn_sekolah_rakyat.kelompok_bmn', $filterKelompok);
        }
        if ($filterLokasi !== '' && $filterLokasi !== '*') {
            $builder->where('trn_bmn_sekolah_rakyat.lokasi', $filterLokasi);
        }
        if ($filterKondisi !== '' && $filterKondisi !== '*') {
            $builder->where('trn_bmn_sekolah_rakyat.kondisi', $filterKondisi);
        }
        if ($filterStatusFoto === 'sudah') {
            $builder->having('foto_count >', 0);
        } elseif ($filterStatusFoto === 'belum') {
            $builder->having('foto_count =', 0);
        }

        if ($keyword !== '') {
            $builder->groupStart()
                ->like('trn_bmn_sekolah_rakyat.kode_bmn', $keyword)
                ->orLike('trn_bmn_sekolah_rakyat.nup', $keyword)
                ->orLike('trn_bmn_sekolah_rakyat.jenis_bmn', $keyword)
                ->orLike('trn_bmn_sekolah_rakyat.spesifikasi', $keyword)
                ->orLike('trn_bmn_sekolah_rakyat.lokasi', $keyword)
                ->groupEnd();
        }

        $items = $builder->orderBy('trn_bmn_sekolah_rakyat.id', 'ASC')->get()->getResultArray();

        $stats = $this->bmnModel->getSummaryStats();
        $lokasiList = $this->bmnModel->distinct()->select('lokasi')->orderBy('lokasi', 'ASC')->findAll();
        $kelompokList = [
            'I. Peralatan dan Mesin',
            'II. Gedung dan Bangunan',
            'III. Jalan, Irigasi dan Jaringan',
        ];

        return view('admin/inventaris/sekolah_rakyat/barang', [
            'pageTitle'        => 'Daftar BMN Sekolah Rakyat',
            'items'            => $items,
            'stats'            => $stats,
            'lokasiList'       => array_column($lokasiList, 'lokasi'),
            'kelompokList'     => $kelompokList,
            'filterKelompok'   => $filterKelompok,
            'filterLokasi'     => $filterLokasi,
            'filterStatusFoto' => $filterStatusFoto,
            'filterKondisi'    => $filterKondisi,
            'keyword'          => $keyword,
            'canAdd'           => (bool) ($permissions['add'] ?? false),
            'canEdit'          => (bool) ($permissions['edit'] ?? false),
            'canDelete'        => (bool) ($permissions['delete'] ?? false),
            'canExport'        => (bool) ($permissions['export'] ?? false),
            'canImport'        => (bool) ($permissions['import'] ?? false),
        ]);
    }

    /**
     * Tambah Barang Baru
     */
    public function tambahBarang()
    {
        $permissions = $this->resolveMenuPermissions(self::MENU_BARANG_LINK);
        if (! ($permissions['add'] ?? false)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses untuk menambah data.');
        }

        $kelompok = trim((string) $this->request->getPost('kelompok_bmn'));
        $kodeBmn  = trim((string) $this->request->getPost('kode_bmn'));
        $jenisBmn = trim((string) $this->request->getPost('jenis_bmn'));
        $lokasi   = trim((string) $this->request->getPost('lokasi'));
        $spek     = trim((string) $this->request->getPost('spesifikasi'));
        $satuan   = trim((string) $this->request->getPost('satuan')) ?: 'Unit';
        $nup      = trim((string) $this->request->getPost('nup')) ?: null;
        $kondisi  = trim((string) $this->request->getPost('kondisi')) ?: 'Baik';
        $ket      = trim((string) $this->request->getPost('keterangan')) ?: null;

        $jumlah   = (float) ($this->request->getPost('jumlah') ?? 1);
        $nilai    = (float) str_replace(['.', ','], ['', '.'], (string) $this->request->getPost('nilai_perolehan'));
        $total    = $jumlah * $nilai;

        // Auto calculate no_urut per kelompok
        $lastRow = $this->bmnModel->where('kelompok_bmn', $kelompok)->orderBy('no_urut', 'DESC')->first();
        $nextNo = $lastRow ? ((int) $lastRow['no_urut'] + 1) : 1;

        $insertData = [
            'kelompok_bmn'          => $kelompok,
            'no_urut'               => $nextNo,
            'kode_bmn'              => $kodeBmn,
            'nup'                   => $nup,
            'jenis_bmn'             => $jenisBmn,
            'spesifikasi'           => $spek,
            'jumlah'                => $jumlah,
            'satuan'                => $satuan,
            'nilai_perolehan'       => $nilai,
            'total_nilai_perolehan' => $total,
            'kondisi'               => $kondisi,
            'lokasi'                => $lokasi,
            'keterangan'            => $ket,
            'created_by'            => (int) (session()->get('id') ?? 1),
        ];

        $this->bmnModel->insert($insertData);

        return redirect()->to(site_url(self::MENU_BARANG_LINK))->with('success', 'Barang BMN Sekolah Rakyat berhasil ditambahkan.');
    }

    /**
     * Ubah Barang BMN
     */
    public function ubahBarang(int $id)
    {
        $permissions = $this->resolveMenuPermissions(self::MENU_BARANG_LINK);
        if (! ($permissions['edit'] ?? false)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses untuk mengubah data.');
        }

        $existing = $this->bmnModel->find($id);
        if (! $existing) {
            return redirect()->back()->with('error', 'Data barang tidak ditemukan.');
        }

        $kelompok = trim((string) $this->request->getPost('kelompok_bmn')) ?: $existing['kelompok_bmn'];
        $kodeBmn  = trim((string) $this->request->getPost('kode_bmn')) ?: $existing['kode_bmn'];
        $jenisBmn = trim((string) $this->request->getPost('jenis_bmn')) ?: $existing['jenis_bmn'];
        $lokasi   = trim((string) $this->request->getPost('lokasi')) ?: $existing['lokasi'];
        $spek     = trim((string) $this->request->getPost('spesifikasi'));
        $satuan   = trim((string) $this->request->getPost('satuan')) ?: $existing['satuan'];
        $nup      = trim((string) $this->request->getPost('nup')) ?: null;
        $kondisi  = trim((string) $this->request->getPost('kondisi')) ?: $existing['kondisi'];
        $ket      = trim((string) $this->request->getPost('keterangan')) ?: null;

        $jumlah   = (float) ($this->request->getPost('jumlah') ?? $existing['jumlah']);
        $rawNilai = (string) $this->request->getPost('nilai_perolehan');
        $nilai    = $rawNilai !== '' ? (float) str_replace(['.', ','], ['', '.'], $rawNilai) : (float) $existing['nilai_perolehan'];
        $total    = $jumlah * $nilai;

        $updateData = [
            'kelompok_bmn'          => $kelompok,
            'kode_bmn'              => $kodeBmn,
            'nup'                   => $nup,
            'jenis_bmn'             => $jenisBmn,
            'spesifikasi'           => $spek,
            'jumlah'                => $jumlah,
            'satuan'                => $satuan,
            'nilai_perolehan'       => $nilai,
            'total_nilai_perolehan' => $total,
            'kondisi'               => $kondisi,
            'lokasi'                => $lokasi,
            'keterangan'            => $ket,
            'updated_by'            => (int) (session()->get('id') ?? 1),
        ];

        $this->bmnModel->update($id, $updateData);

        return redirect()->to(site_url(self::MENU_BARANG_LINK))->with('success', 'Data barang berhasil diperbarui.');
    }

    /**
     * Hapus Barang BMN
     */
    public function hapusBarang(int $id)
    {
        $permissions = $this->resolveMenuPermissions(self::MENU_BARANG_LINK);
        if (! ($permissions['delete'] ?? false)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'error' => 'Akses ditolak.']);
            }
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses untuk menghapus data.');
        }

        $existing = $this->bmnModel->find($id);
        if (! $existing) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'error' => 'Data tidak ditemukan.']);
            }
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        // Hapus file-file foto terkait
        $fotos = $this->fotoModel->getFotoByBmnId($id);
        foreach ($fotos as $f) {
            if (! empty($f['foto_asli']) && is_file(FCPATH . $f['foto_asli'])) {
                @unlink(FCPATH . $f['foto_asli']);
            }
            if (! empty($f['foto_watermark']) && is_file(FCPATH . $f['foto_watermark'])) {
                @unlink(FCPATH . $f['foto_watermark']);
            }
        }
        $this->fotoModel->where('bmn_id', $id)->delete();

        $this->bmnModel->delete($id);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'message' => 'Barang berhasil dihapus.']);
        }

        return redirect()->to(site_url(self::MENU_BARANG_LINK))->with('success', 'Barang BMN berhasil dihapus.');
    }

    /**
     * Update NUP Cepat via AJAX
     */
    public function updateNupAjax(int $id)
    {
        $permissions = $this->resolveMenuPermissions(self::MENU_BARANG_LINK);
        if (! ($permissions['edit'] ?? false)) {
            return $this->response->setJSON(['success' => false, 'error' => 'Akses ditolak.']);
        }

        $nup = trim((string) $this->request->getPost('nup'));
        $this->bmnModel->update($id, ['nup' => $nup ?: null]);

        return $this->response->setJSON(['success' => true, 'message' => 'NUP berhasil diperbarui.']);
    }

    /**
     * Detail Barang AJAX (termasuk foto-foto)
     */
    public function detailBarangAjax(int $id)
    {
        $item = $this->bmnModel->find($id);
        if (! $item) {
            return $this->response->setJSON(['success' => false, 'error' => 'Data barang tidak ditemukan.']);
        }

        $fotos = $this->fotoModel->getFotoByBmnId($id);

        return $this->response->setJSON([
            'success' => true,
            'item'    => $item,
            'fotos'   => $fotos,
        ]);
    }

    /**
     * Submenu 2: Dokumentasi Foto Lapangan (Berdasarkan Lokasi Gedung & Material/Jenis BMN)
     */
    public function foto()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_FOTO_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $lokasiStats    = $this->bmnModel->getLokasiStats();
        $filterLokasi   = trim((string) $this->request->getGet('lokasi'));
        $filterMaterial = trim((string) $this->request->getGet('material'));
        $filterStatus   = trim((string) $this->request->getGet('status_foto'));

        // Jika filter lokasi kosong, default ke lokasi pertama
        if ($filterLokasi === '' && ! empty($lokasiStats)) {
            $filterLokasi = $lokasiStats[0]['lokasi'];
        }

        // Ambil daftar material/jenis barang yang ada di lokasi gedung ini
        $materialList = [];
        if ($filterLokasi !== '') {
            $materialList = $this->bmnModel
                ->where('lokasi', $filterLokasi)
                ->select('jenis_bmn, COUNT(id) AS total_item')
                ->groupBy('jenis_bmn')
                ->orderBy('jenis_bmn', 'ASC')
                ->findAll();
        }

        $builder = $this->bmnModel->builder()
            ->select('trn_bmn_sekolah_rakyat.*, COUNT(f.id) AS foto_count')
            ->join('trn_bmn_sekolah_rakyat_foto f', 'f.bmn_id = trn_bmn_sekolah_rakyat.id', 'left')
            ->groupBy('trn_bmn_sekolah_rakyat.id');

        if ($filterLokasi !== '' && $filterLokasi !== '*') {
            $builder->where('trn_bmn_sekolah_rakyat.lokasi', $filterLokasi);
        }
        if ($filterMaterial !== '' && $filterMaterial !== '*') {
            $builder->where('trn_bmn_sekolah_rakyat.jenis_bmn', $filterMaterial);
        }
        if ($filterStatus === 'sudah') {
            $builder->having('foto_count >', 0);
        } elseif ($filterStatus === 'belum') {
            $builder->having('foto_count =', 0);
        }

        $items = $builder->orderBy('trn_bmn_sekolah_rakyat.jenis_bmn', 'ASC')
            ->orderBy('trn_bmn_sekolah_rakyat.no_urut', 'ASC')
            ->get()->getResultArray();

        // Ambil semua foto untuk item-item ini
        $itemIds = array_column($items, 'id');
        $fotosByItem = [];
        if (! empty($itemIds)) {
            $allFotos = $this->fotoModel->whereIn('bmn_id', $itemIds)->orderBy('id', 'ASC')->findAll();
            foreach ($allFotos as $f) {
                $fotosByItem[$f['bmn_id']][] = $f;
            }
        }

        $permissions = $this->resolveMenuPermissions(self::MENU_FOTO_LINK);

        return view('admin/inventaris/sekolah_rakyat/foto', [
            'pageTitle'      => 'Dokumentasi Foto BMN Sekolah Rakyat',
            'lokasiStats'    => $lokasiStats,
            'filterLokasi'   => $filterLokasi,
            'filterMaterial' => $filterMaterial,
            'filterStatus'   => $filterStatus,
            'materialList'   => $materialList,
            'items'          => $items,
            'fotosByItem'    => $fotosByItem,
            'canAdd'         => (bool) ($permissions['add'] ?? false),
            'canEdit'        => (bool) ($permissions['edit'] ?? false),
            'canDelete'      => (bool) ($permissions['delete'] ?? false),
        ]);
    }

    /**
     * Upload & Simpan Foto BMN dengan Watermark TimeMark Otomatis
     */
    public function uploadFotoAjax(int $bmnId)
    {
        $permissions = $this->resolveMenuPermissions(self::MENU_FOTO_LINK);
        if (! ($permissions['add'] ?? false) && ! ($permissions['edit'] ?? false)) {
            return $this->response->setJSON(['success' => false, 'error' => 'Akses ditolak: sesi login Anda tidak memiliki izin.']);
        }

        $bmn = $this->bmnModel->find($bmnId);
        if (! $bmn) {
            return $this->response->setJSON(['success' => false, 'error' => 'Data barang tidak ditemukan.']);
        }

        $judulFoto = trim((string) $this->request->getPost('judul_foto')) ?: 'Foto Dokumentasi';
        $ket       = trim((string) $this->request->getPost('keterangan')) ?: null;
        $lat       = trim((string) $this->request->getPost('latitude'));
        $lng       = trim((string) $this->request->getPost('longitude'));
        $imageData = (string) $this->request->getPost('image_data');
        $file      = $this->request->getFile('image_file');

        $origDir = FCPATH . 'uploads/bmn_sekolah_rakyat/original';
        $wmDir   = FCPATH . 'uploads/bmn_sekolah_rakyat/watermarked';
        if (! is_dir($origDir)) {
            @mkdir($origDir, 0775, true);
        }
        if (! is_dir($wmDir)) {
            @mkdir($wmDir, 0775, true);
        }

        $randStr = substr(md5(uniqid((string) mt_rand(), true)), 0, 8);
        $baseFilename = 'sr_' . $bmnId . '_' . time() . '_' . $randStr . '.jpg';
        $origPath = $origDir . DIRECTORY_SEPARATOR . $baseFilename;
        $wmPath   = $wmDir . DIRECTORY_SEPARATOR . $baseFilename;

        if (! empty($imageData) && strpos($imageData, 'data:image') === 0) {
            $parts = explode(',', $imageData);
            $rawBinary = base64_decode(end($parts));
            if ($rawBinary === false || $rawBinary === '') {
                return $this->response->setJSON(['success' => false, 'error' => 'Data gambar kamera tidak valid.']);
            }
            file_put_contents($origPath, $rawBinary);
        } elseif ($file && $file->isValid() && ! $file->hasMoved()) {
            $file->move($origDir, $baseFilename);
        } else {
            return $this->response->setJSON(['success' => false, 'error' => 'Tidak ada berkas gambar yang dikirim.']);
        }

        // Koordinat format
        $coords = '';
        if ($lat !== '' && $lng !== '') {
            $coords = $lat . ', ' . $lng;
        } else {
            // Default Koordinat Komplek Sport Center Kuansing
            $coords = '-0.5342° S, 101.5492° E';
        }

        // Watermark Note
        $notes = $bmn['jenis_bmn'] . "\n" . ($bmn['spesifikasi'] ?: '') . "\nLokasi: " . $bmn['lokasi'] . " | " . $judulFoto;
        if (! empty($bmn['nup'])) {
            $notes .= " | NUP: " . $bmn['nup'];
        }

        // Apply TimeMark Watermark
        $wmService = new TimeMarkWatermarkService();
        $wmRes = $wmService->processImage($origPath, [
            'template'       => 'classic',
            'use_exif_time'  => false,
            'location'       => 'Pembangunan Sekolah Rakyat Riau (Kuansing)',
            'coordinates'    => $coords,
            'notes'          => $notes,
            'output_quality' => 88,
        ]);

        if (! empty($wmRes['success']) && ! empty($wmRes['data_uri'])) {
            $parts = explode(',', $wmRes['data_uri']);
            file_put_contents($wmPath, base64_decode(end($parts)));
            $wmDbPath = 'uploads/bmn_sekolah_rakyat/watermarked/' . $baseFilename;
        } else {
            // Fallback gunakan file asli jika watermark service tidak menghasilkan gambar
            $wmDbPath = 'uploads/bmn_sekolah_rakyat/original/' . $baseFilename;
        }

        $fotoData = [
            'bmn_id'         => $bmnId,
            'judul_foto'     => $judulFoto,
            'foto_asli'      => 'uploads/bmn_sekolah_rakyat/original/' . $baseFilename,
            'foto_watermark' => $wmDbPath,
            'latitude'       => $lat ?: null,
            'longitude'      => $lng ?: null,
            'taken_at'       => date('Y-m-d H:i:s'),
            'keterangan'     => $ket,
            'created_by'     => (int) (session()->get('id') ?? 1),
        ];

        $fotoId = $this->fotoModel->insert($fotoData);
        $fotoData['id'] = $fotoId;

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Foto dokumentasi berhasil diunggah dengan watermark.',
            'foto'    => $fotoData,
        ]);
    }

    /**
     * Hapus Foto via AJAX
     */
    public function hapusFotoAjax(int $fotoId)
    {
        $permissions = $this->resolveMenuPermissions(self::MENU_FOTO_LINK);
        if (! ($permissions['delete'] ?? false)) {
            return $this->response->setJSON(['success' => false, 'error' => 'Akses ditolak: Anda tidak memiliki izin menghapus foto.']);
        }

        $foto = $this->fotoModel->find($fotoId);
        if (! $foto) {
            return $this->response->setJSON(['success' => false, 'error' => 'Foto tidak ditemukan.']);
        }

        if (! empty($foto['foto_asli']) && is_file(FCPATH . $foto['foto_asli'])) {
            @unlink(FCPATH . $foto['foto_asli']);
        }
        if (! empty($foto['foto_watermark']) && is_file(FCPATH . $foto['foto_watermark'])) {
            @unlink(FCPATH . $foto['foto_watermark']);
        }

        $this->fotoModel->delete($fotoId);

        return $this->response->setJSON(['success' => true, 'message' => 'Foto berhasil dihapus.']);
    }

    /**
     * Submenu 3: Rekapitulasi & Kertas Kerja Nilai Perolehan
     */
    public function rekapitulasi()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_REKAP_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $dasarItems = $this->dasarModel->orderBy('no_urut', 'ASC')->findAll();
        $stats = $this->bmnModel->getSummaryStats();
        $lokasiStats = $this->bmnModel->getLokasiStats();

        // Rekapitulasi per kelompok BMN
        $db = $this->bmnModel->db;
        $kelompokSummary = $db->table('trn_bmn_sekolah_rakyat')
            ->select('kelompok_bmn, COUNT(id) AS total_item, SUM(jumlah) AS total_unit, SUM(total_nilai_perolehan) AS total_nilai')
            ->groupBy('kelompok_bmn')
            ->orderBy('kelompok_bmn', 'ASC')
            ->get()
            ->getResultArray();

        $permissions = $this->resolveMenuPermissions(self::MENU_REKAP_LINK);

        return view('admin/inventaris/sekolah_rakyat/rekapitulasi', [
            'pageTitle'       => 'Rekapitulasi BMN Sekolah Rakyat',
            'dasarItems'      => $dasarItems,
            'stats'           => $stats,
            'lokasiStats'     => $lokasiStats,
            'kelompokSummary' => $kelompokSummary,
            'canExport'       => (bool) ($permissions['export'] ?? false),
        ]);
    }

    /**
     * Export Excel Kertas Kerja Rincian BMN (Lengkap 13 Kolom + Lampiran Foto Barang + Sheet Dasar Perhitungan)
     */
    public function exportExcel()
    {
        $permissions = $this->resolveMenuPermissions(self::MENU_BARANG_LINK);
        if (! ($permissions['export'] ?? false)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses untuk ekspor data.');
        }

        $filterLokasi = trim((string) $this->request->getGet('lokasi'));

        $spreadsheet = new Spreadsheet();

        // -------------------------------------------------------------
        // SHEET 1: Kertas Kerja BMN
        // -------------------------------------------------------------
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Kertas Kerja BMN');

        // Header Metadata Proyek (Baris 1 - 7)
        $sheet->setCellValue('A1', 'KERTAS KERJA RINCIAN PENDETAILAN BMN HASIL PEKERJAAN KONSTRUKSI DAN PENGADAAN MEBEL/PERALATAN');
        $sheet->setCellValue('A2', 'Pekerjaan');
        $sheet->setCellValue('D2', ': Pembangunan Sekolah Rakyat Provinsi Riau');
        $sheet->setCellValue('A3', 'Lokasi');
        $sheet->setCellValue('D3', ': Komplek Sport Center, Kel. Sungai Jering, Kec. Kuantan Tengah, Kab. Kuantan Singingi, Riau');
        $sheet->setCellValue('A4', 'Satuan Kerja');
        $sheet->setCellValue('D4', ': Pelaksanaan Prasarana Strategis Riau');
        $sheet->setCellValue('A5', 'Kontrak');
        $sheet->setCellValue('D5', ': HK.02.01/PPS-RIAU/FSK-SR/2025/01 tanggal 24 Desember 2025 ');
        $sheet->setCellValue('A6', 'Nilai Kontrak (Addendum V)');
        $sheet->setCellValue('D6', ': Rp239.445.195.000,- (termasuk PPN 11%)');
        $sheet->setCellValue('A7', 'Penyedia Jasa');
        $sheet->setCellValue('D7', ': PT. Duta Rama – PT. Gala Karya, KSO');

        if ($filterLokasi !== '' && $filterLokasi !== '*') {
            $sheet->setCellValue('A8', 'Filter Lokasi Gedung');
            $sheet->setCellValue('D8', ': ' . $filterLokasi);
            $sheet->getStyle('A8:D8')->getFont()->setBold(true)->getColor()->setRGB('C00000');
        }

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A2:A7')->getFont()->setBold(true);

        // Header Tabel Kolom (Baris 9) - 13 Kolom Lengkap
        $headers = [
            'A9' => 'No',
            'B9' => 'Kode BMN',
            'C9' => 'NUP',
            'D9' => 'Jenis BMN',
            'E9' => 'Merk / Type / Spesifikasi Material Teknis',
            'F9' => 'Jml',
            'G9' => 'Sat',
            'H9' => 'Nilai Perolehan (Rp)',
            'I9' => 'Total Nilai Perolehan (Rp)',
            'J9' => 'Kondisi',
            'K9' => 'Foto Barang',
            'L9' => 'Lokasi',
            'M9' => 'Keterangan',
        ];

        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

        $sheet->getStyle('A9:M9')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F497D']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);
        $sheet->getRowDimension(9)->setRowHeight(28);

        // Ambil Data Barang
        $builder = $this->bmnModel->builder();
        if ($filterLokasi !== '' && $filterLokasi !== '*') {
            $builder->where('trn_bmn_sekolah_rakyat.lokasi', $filterLokasi);
        }
        $items = $builder->orderBy('trn_bmn_sekolah_rakyat.id', 'ASC')->get()->getResultArray();

        // Ambil Foto-Foto Barang
        $itemIds = array_column($items, 'id');
        $fotosByItem = [];
        if (! empty($itemIds)) {
            $fotos = $this->fotoModel->whereIn('bmn_id', $itemIds)->orderBy('id', 'ASC')->findAll();
            foreach ($fotos as $f) {
                $fotosByItem[$f['bmn_id']][] = $f;
            }
        }

        $rowIdx = 10;
        $currentKelompok = '';
        $kelompokStartRow = 10;
        $kelompokTotal = 0;
        $grandTotal = 0;

        $thinBorder = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D0D0D0'],
                ],
            ],
        ];

        foreach ($items as $it) {
            // Deteksi pergantian kelompok
            if ($it['kelompok_bmn'] !== $currentKelompok) {
                // Tulis Subtotal kelompok sebelumnya jika ada
                if ($currentKelompok !== '' && ($filterLokasi === '' || $filterLokasi === '*')) {
                    $sheet->setCellValue('E' . $rowIdx, 'Jumlah ' . $this->getNamaKelompokClean($currentKelompok));
                    $sheet->setCellValue('I' . $rowIdx, $kelompokTotal);
                    $sheet->getStyle('A' . $rowIdx . ':M' . $rowIdx)->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']],
                    ]);
                    $sheet->getStyle('I' . $rowIdx)->getNumberFormat()->setFormatCode('#,##0');
                    $sheet->getStyle('A' . $rowIdx . ':M' . $rowIdx)->applyFromArray($thinBorder);
                    $rowIdx++;
                }

                $currentKelompok = $it['kelompok_bmn'];
                $kelompokTotal = 0;

                // Header Kelompok
                $sheet->setCellValue('A' . $rowIdx, $this->getKelompokRomawi($currentKelompok));
                $sheet->setCellValue('B' . $rowIdx, strtoupper($currentKelompok));
                $sheet->getStyle('A' . $rowIdx . ':M' . $rowIdx)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
                ]);
                $sheet->getStyle('A' . $rowIdx . ':M' . $rowIdx)->applyFromArray($thinBorder);
                $rowIdx++;
            }

            $kelompokTotal += (float) $it['total_nilai_perolehan'];
            $grandTotal += (float) $it['total_nilai_perolehan'];

            // Data Cells
            $sheet->setCellValue('A' . $rowIdx, $it['no_urut']);
            $sheet->setCellValue('B' . $rowIdx, $it['kode_bmn']);
            $sheet->setCellValue('C' . $rowIdx, $it['nup'] ?: '');
            $sheet->setCellValue('D' . $rowIdx, $it['jenis_bmn']);
            $sheet->setCellValue('E' . $rowIdx, $it['spesifikasi']);
            $sheet->setCellValue('F' . $rowIdx, $it['jumlah']);
            $sheet->setCellValue('G' . $rowIdx, $it['satuan']);
            $sheet->setCellValue('H' . $rowIdx, $it['nilai_perolehan']);
            $sheet->setCellValue('I' . $rowIdx, $it['total_nilai_perolehan']);
            $sheet->setCellValue('J' . $rowIdx, $it['kondisi'] ?: 'Baik');
            $sheet->setCellValue('L' . $rowIdx, $it['lokasi']);
            $sheet->setCellValue('M' . $rowIdx, $it['keterangan'] ?: '');

            // Formatting Angka & Perataan
            $sheet->getStyle('A' . $rowIdx)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $rowIdx)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $rowIdx)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $rowIdx)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('G' . $rowIdx)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('J' . $rowIdx)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H' . $rowIdx . ':I' . $rowIdx)->getNumberFormat()->setFormatCode('#,##0');

            // Baris Kuning untuk item nilai 0 (seperti Gedung UKS dan Gudang sesuai format asli)
            if ((float) $it['total_nilai_perolehan'] == 0) {
                $sheet->getStyle('A' . $rowIdx . ':M' . $rowIdx)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
                ]);
            }

            // SEMATKAN FOTO BARANG DI KOLOM K
            $hasDrawing = false;
            if (! empty($fotosByItem[$it['id']])) {
                $firstFoto = $fotosByItem[$it['id']][0];
                $imgPath = '';
                if (! empty($firstFoto['foto_watermark']) && is_file(FCPATH . $firstFoto['foto_watermark'])) {
                    $imgPath = FCPATH . $firstFoto['foto_watermark'];
                } elseif (! empty($firstFoto['foto_asli']) && is_file(FCPATH . $firstFoto['foto_asli'])) {
                    $imgPath = FCPATH . $firstFoto['foto_asli'];
                }

                if ($imgPath !== '') {
                    try {
                        $drawing = new Drawing();
                        $drawing->setName('Foto_' . $it['id']);
                        $drawing->setDescription($it['jenis_bmn'] . ' - ' . $it['spesifikasi']);
                        $drawing->setPath($imgPath);
                        $drawing->setHeight(65);
                        $drawing->setCoordinates('K' . $rowIdx);
                        $drawing->setOffsetX(8);
                        $drawing->setOffsetY(4);
                        $drawing->setWorksheet($sheet);
                        $sheet->getRowDimension($rowIdx)->setRowHeight(58);
                        $hasDrawing = true;
                    } catch (\Throwable $e) {
                        $hasDrawing = false;
                    }
                }
            }

            if (! $hasDrawing) {
                $sheet->setCellValue('K' . $rowIdx, '-');
                $sheet->getStyle('K' . $rowIdx)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getRowDimension($rowIdx)->setRowHeight(22);
            }

            $sheet->getStyle('A' . $rowIdx . ':M' . $rowIdx)->applyFromArray($thinBorder);
            $rowIdx++;
        }

        // Subtotal kelompok terakhir
        if ($currentKelompok !== '' && ($filterLokasi === '' || $filterLokasi === '*')) {
            $sheet->setCellValue('E' . $rowIdx, 'Jumlah ' . $this->getNamaKelompokClean($currentKelompok));
            $sheet->setCellValue('I' . $rowIdx, $kelompokTotal);
            $sheet->getStyle('A' . $rowIdx . ':M' . $rowIdx)->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']],
            ]);
            $sheet->getStyle('I' . $rowIdx)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('A' . $rowIdx . ':M' . $rowIdx)->applyFromArray($thinBorder);
            $rowIdx++;
        }

        // TOTAL, KONTROL & SELISIH
        $sheet->setCellValue('A' . $rowIdx, 'TOTAL');
        $sheet->setCellValue('I' . $rowIdx, $grandTotal);
        $sheet->getStyle('A' . $rowIdx . ':M' . $rowIdx)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
        ]);
        $sheet->getStyle('I' . $rowIdx)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('A' . $rowIdx . ':M' . $rowIdx)->applyFromArray($thinBorder);
        $rowIdx++;

        if ($filterLokasi === '' || $filterLokasi === '*') {
            $sheet->setCellValue('E' . $rowIdx, 'Kontrol: nilai kontrak Addendum V (dibulatkan)');
            $sheet->setCellValue('I' . $rowIdx, 239445195000);
            $sheet->getStyle('I' . $rowIdx)->getNumberFormat()->setFormatCode('#,##0');
            $rowIdx++;

            $sheet->setCellValue('E' . $rowIdx, 'Selisih');
            $sheet->setCellValue('I' . $rowIdx, $grandTotal - 239445195000);
            $sheet->getStyle('I' . $rowIdx)->getNumberFormat()->setFormatCode('#,##0');
            $rowIdx += 2;

            // LEMBAR TANDA TANGAN 3 PIHAK
            $sheet->setCellValue('J' . $rowIdx, 'Dikeluarkan di : Kuantan Singingi');
            $rowIdx++;
            $sheet->setCellValue('J' . $rowIdx, 'pada tanggal  : ……………………… 2026');
            $rowIdx += 2;

            $sheet->setCellValue('B' . $rowIdx, 'Penyedia Jasa Konstruksi');
            $sheet->setCellValue('F' . $rowIdx, 'Konsultan Pengawas');
            $sheet->setCellValue('J' . $rowIdx, 'Pejabat Pembuat Komitmen');
            $sheet->getStyle('B' . $rowIdx . ':J' . $rowIdx)->getFont()->setBold(true);
            $rowIdx++;

            $sheet->setCellValue('B' . $rowIdx, 'PT. Duta Rama – PT. Gala Karya, KSO');
            $sheet->setCellValue('F' . $rowIdx, "PT. Biro Arsitek dan Insinjur Sangkuriang KSO\nPT. Astadipati Duta Harindo");
            $sheet->setCellValue('J' . $rowIdx, 'Pelaksanaan Prasarana Strategis');
            $sheet->getStyle('F' . $rowIdx)->getAlignment()->setWrapText(true);
            $rowIdx += 4;

            $sheet->setCellValue('B' . $rowIdx, 'Zaynudin Agus Pujianto, S.T.');
            $sheet->setCellValue('F' . $rowIdx, 'Kurniawan Destika, S.T., M.T.');
            $sheet->setCellValue('J' . $rowIdx, 'Nurhidayat Nugroho, S.Ars.');
            $sheet->getStyle('B' . $rowIdx . ':J' . $rowIdx)->getFont()->setBold(true)->setUnderline(true);
            $rowIdx++;

            $sheet->setCellValue('B' . $rowIdx, 'Project Manager');
            $sheet->setCellValue('F' . $rowIdx, 'Team Leader');
            $sheet->setCellValue('J' . $rowIdx, 'NIP. 19901221 201802 1 001');
            $rowIdx += 3;

            // PENJELASAN TAMBAHAN
            $sheet->setCellValue('A' . $rowIdx, 'Penjelasan Tambahan:');
            $sheet->getStyle('A' . $rowIdx)->getFont()->setBold(true);
            $rowIdx++;
            $sheet->setCellValue('A' . $rowIdx, '- Merk/Type/Spesifikasi Material Teknis untuk kelompok Peralatan dan Mesin diisi sesuai uraian item RAB Addendum V; untuk kelompok Gedung dan Bangunan serta Jalan, Irigasi dan Jaringan diisi peruntukan fungsi bangunan/prasarana.');
            $rowIdx++;
            $sheet->setCellValue('A' . $rowIdx, '- Nilai perolehan berupa bilangan bulat (tanpa desimal), sudah termasuk PPN 11% dan alokasi biaya umum (lihat sheet Dasar Perhitungan).');
            $rowIdx++;
            $sheet->setCellValue('A' . $rowIdx, '- Peralatan berupa sprinkler, exhaust fan, pompa air, sound system dan utilitas lain yang menempel pada gedung dicatat bersama gedung tempat peralatan tersebut berada.');
            $rowIdx++;
            $sheet->setCellValue('A' . $rowIdx, '- Rumah Pompa + GWT, Rumah Genset, Bangunan IPAL, TPS dan Jalan Khusus Kompleks dicatat tersendiri sesuai kodefikasi BMN masing-masing.');
            $rowIdx++;
            $sheet->setCellValue('A' . $rowIdx, '- Baris berwarna kuning: aset pada format yang tidak terdapat dalam lingkup RAB Addendum V (nilai 0). Foto Barang terlampir otomatis dari dokumentasi fisik lapangan.');
            $rowIdx++;
        }

        // Atur Lebar Kolom Sheet 1
        $colWidths = [
            'A' => 6,
            'B' => 16,
            'C' => 8,
            'D' => 28,
            'E' => 46,
            'F' => 9,
            'G' => 8,
            'H' => 16,
            'I' => 18,
            'J' => 10,
            'K' => 18, // Kolom Foto Barang
            'L' => 22,
            'M' => 38,
        ];
        foreach ($colWidths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        // -------------------------------------------------------------
        // SHEET 2: Dasar Perhitungan (Jika Ekspor Seluruh Aset)
        // -------------------------------------------------------------
        if ($filterLokasi === '' || $filterLokasi === '*') {
            $sheet2 = $spreadsheet->createSheet();
            $sheet2->setTitle('Dasar Perhitungan');

            $sheet2->setCellValue('A1', 'DASAR PERHITUNGAN NILAI PEROLEHAN GEDUNG, BANGUNAN, JALAN DAN JARINGAN');
            $sheet2->setCellValue('A2', 'Sumber: RAB Addendum V (Daftar Kuantitas dan Harga Addendum-5) Pembangunan Sekolah Rakyat Provinsi Riau; nilai sebelum PPN per sub pekerjaan, setelah dikurangi furnitur dan peralatan yang dicatat terpisah pada kelompok Peralatan dan Mesin.');
            $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(11);

            $sheet2->setCellValue('A4', 'Biaya umum yang dialokasikan proporsional (sebelum PPN)');
            $sheet2->getStyle('A4')->getFont()->setBold(true);

            $sheet2->setCellValue('A5', 'Kode');
            $sheet2->setCellValue('B5', 'Uraian');
            $sheet2->setCellValue('C5', 'Nilai (Rp)');
            $sheet2->getStyle('A5:C5')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F497D']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            $biayaUmumRows = [
                ['A', 'SMKK (di luar APAR)', 529246583],
                ['B.1', 'Pematangan Lahan', 4724406658],
                ['B.2', 'Pekerjaan Persiapan', 585650000],
                ['B.3', 'Mobilisasi/Demobilisasi Alat Berat', 111500000],
                ['D.17', 'Pekerjaan Pengujian', 209900000],
                ['E.16', 'Penyambungan Daya PLN & Perijinan', 316202500],
            ];

            $r2 = 6;
            foreach ($biayaUmumRows as $bu) {
                $sheet2->setCellValue('A' . $r2, $bu[0]);
                $sheet2->setCellValue('B' . $r2, $bu[1]);
                $sheet2->setCellValue('C' . $r2, $bu[2]);
                $sheet2->getStyle('C' . $r2)->getNumberFormat()->setFormatCode('#,##0');
                $sheet2->getStyle('A' . $r2 . ':C' . $r2)->applyFromArray($thinBorder);
                $r2++;
            }

            $sheet2->setCellValue('B' . $r2, 'Jumlah biaya umum');
            $sheet2->setCellValue('C' . $r2, 6476905741);
            $sheet2->getStyle('B' . $r2 . ':C' . $r2)->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
            ]);
            $sheet2->getStyle('C' . $r2)->getNumberFormat()->setFormatCode('#,##0');
            $sheet2->getStyle('A' . $r2 . ':C' . $r2)->applyFromArray($thinBorder);
            $r2 += 2;

            $sheet2->setCellValue('B' . $r2, 'Nilai kontrak Addendum V (dibulatkan, termasuk PPN)');
            $sheet2->setCellValue('C' . $r2, 239445195000);
            $sheet2->getStyle('C' . $r2)->getNumberFormat()->setFormatCode('#,##0');
            $r2++;

            $sheet2->setCellValue('B' . $r2, 'PPN');
            $sheet2->setCellValue('C' . $r2, 0.11);
            $sheet2->getStyle('C' . $r2)->getNumberFormat()->setFormatCode('0%');
            $r2 += 2;

            // Header Tabel Rincian 33 Aset
            $headers2 = [
                'A' . $r2 => 'No',
                'B' . $r2 => 'Aset (Merk/Type/Spesifikasi)',
                'C' . $r2 => 'Komponen RAB Addendum V',
                'D' . $r2 => 'Jml',
                'E' . $r2 => 'Sat',
                'F' . $r2 => 'Nilai RAB sebelum PPN (Rp)',
                'G' . $r2 => 'Alokasi Biaya Umum (Rp)',
                'H' . $r2 => 'Subtotal (Rp)',
                'I' . $r2 => 'PPN 11% (Rp)',
                'J' . $r2 => 'Total termasuk PPN (Rp)',
                'K' . $r2 => 'Nilai Perolehan per Unit (Rp)',
                'L' . $r2 => 'Total Nilai Perolehan (Rp)',
            ];
            foreach ($headers2 as $cell => $val) {
                $sheet2->setCellValue($cell, $val);
            }
            $sheet2->getStyle('A' . $r2 . ':L' . $r2)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F497D']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            ]);
            $sheet2->getRowDimension($r2)->setRowHeight(28);
            $r2++;

            $dasarItems = $this->dasarModel->orderBy('no_urut', 'ASC')->findAll();
            foreach ($dasarItems as $d) {
                $sheet2->setCellValue('A' . $r2, $d['no_urut']);
                $sheet2->setCellValue('B' . $r2, $d['nama_aset']);
                $sheet2->setCellValue('C' . $r2, $d['komponen_rab']);
                $sheet2->setCellValue('D' . $r2, $d['jumlah']);
                $sheet2->setCellValue('E' . $r2, $d['satuan']);
                $sheet2->setCellValue('F' . $r2, $d['nilai_rab'] ?? 0);
                $sheet2->setCellValue('G' . $r2, $d['alokasi_biaya_umum'] ?? 0);
                $sheet2->setCellValue('H' . $r2, $d['subtotal'] ?? 0);
                $sheet2->setCellValue('I' . $r2, $d['ppn'] ?? 0);
                $sheet2->setCellValue('J' . $r2, $d['total_termasuk_ppn'] ?? 0);
                $sheet2->setCellValue('K' . $r2, $d['nilai_perolehan_unit'] ?? 0);
                $sheet2->setCellValue('L' . $r2, $d['total_nilai_perolehan'] ?? 0);

                $sheet2->getStyle('A' . $r2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet2->getStyle('D' . $r2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet2->getStyle('E' . $r2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet2->getStyle('F' . $r2 . ':L' . $r2)->getNumberFormat()->setFormatCode('#,##0');

                if ((float) $d['total_nilai_perolehan'] == 0) {
                    $sheet2->getStyle('A' . $r2 . ':L' . $r2)->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
                    ]);
                }

                $sheet2->getStyle('A' . $r2 . ':L' . $r2)->applyFromArray($thinBorder);
                $r2++;
            }

            // Atur Lebar Kolom Sheet 2
            $s2Widths = [
                'A' => 6,
                'B' => 38,
                'C' => 22,
                'D' => 8,
                'E' => 8,
                'F' => 18,
                'G' => 18,
                'H' => 18,
                'I' => 18,
                'J' => 20,
                'K' => 20,
                'L' => 20,
            ];
            foreach ($s2Widths as $col => $w) {
                $sheet2->getColumnDimension($col)->setWidth($w);
            }
        }

        // Set Sheet Aktif kembali ke Sheet 1
        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);
        $filename = 'Kertas_Kerja_Rincian_BMN_Sekolah_Rakyat_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    /**
     * Helper formatting nama kelompok BMN
     */
    private function getNamaKelompokClean(string $kelompok): string
    {
        $k = strtolower(trim($kelompok));
        if (strpos($k, 'peralatan') !== false) {
            return 'Peralatan dan Mesin';
        }
        if (strpos($k, 'gedung') !== false) {
            return 'Gedung dan Bangunan';
        }
        if (strpos($k, 'jalan') !== false || strpos($k, 'jaringan') !== false) {
            return 'Jalan, Irigasi dan Jaringan';
        }
        return $kelompok;
    }

    private function getKelompokRomawi(string $kelompok): string
    {
        $k = strtolower(trim($kelompok));
        if (strpos($k, 'peralatan') !== false) {
            return 'I.';
        }
        if (strpos($k, 'gedung') !== false) {
            return 'II.';
        }
        if (strpos($k, 'jalan') !== false || strpos($k, 'jaringan') !== false) {
            return 'III.';
        }
        return '';
    }

    /**
     * Cetak PDF Rekapitulasi & Rincian BMN Sekolah Rakyat
     */
    public function cetakPdf()
    {
        $permissions = $this->resolveMenuPermissions(self::MENU_REKAP_LINK);
        if (! ($permissions['export'] ?? false)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses cetak dokumen.');
        }

        $items = $this->bmnModel->orderBy('id', 'ASC')->findAll();
        $stats = $this->bmnModel->getSummaryStats();
        $dasarItems = $this->dasarModel->orderBy('no_urut', 'ASC')->findAll();

        $html = view('admin/inventaris/sekolah_rakyat/pdf_rekapitulasi', [
            'items'      => $items,
            'stats'      => $stats,
            'dasarItems' => $dasarItems,
        ]);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $dompdf->stream('Rekapitulasi_BMN_Sekolah_Rakyat.pdf', ['Attachment' => false]);
        exit;
    }

    /**
     * Helper Keamanan: Cek Izin Akses Menu
     */
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
            if ($db->tableExists($table)) {
                $row = $db->table($table)
                    ->select('id')
                    ->where('LOWER(TRIM(link))', $normalized)
                    ->get()
                    ->getRowArray();

                if (is_array($row) && isset($row['id'])) {
                    return (string) $row['id'];
                }
            }
        }

        return null;
    }
}
