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
     * Export Excel Kertas Kerja Rincian BMN
     */
    public function exportExcel()
    {
        $permissions = $this->resolveMenuPermissions(self::MENU_BARANG_LINK);
        if (! ($permissions['export'] ?? false)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses untuk ekspor data.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Kertas Kerja BMN');

        // Header Dokumen
        $sheet->setCellValue('A1', 'KERTAS KERJA RINCIAN PENDETAILAN BMN HASIL PEKERJAAN KONSTRUKSI DAN PENGADAAN MEBEL/PERALATAN');
        $sheet->setCellValue('A2', 'Pekerjaan: Pembangunan Sekolah Rakyat Provinsi Riau');
        $sheet->setCellValue('A3', 'Lokasi: Komplek Sport Center, Kel. Sungai Jering, Kec. Kuantan Tengah, Kab. Kuantan Singingi, Riau');
        $sheet->setCellValue('A4', 'Satuan Kerja: Pelaksanaan Prasarana Strategis Riau');
        $sheet->setCellValue('A5', 'Kontrak: HK.02.01/PPS-RIAU/FSK-SR/2025/01 tanggal 24 Desember 2025');
        $sheet->setCellValue('A6', 'Nilai Kontrak (Addendum V): Rp239.445.195.000,- (termasuk PPN 11%)');
        $sheet->setCellValue('A7', 'Penyedia Jasa: PT. Duta Rama – PT. Gala Karya, KSO');

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);

        // Header Tabel Kolom
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
            'K9' => 'Lokasi',
            'L9' => 'Keterangan',
        ];

        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

        $sheet->getStyle('A9:L9')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F497D']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $items = $this->bmnModel->orderBy('id', 'ASC')->findAll();
        $rowIdx = 10;
        $currentKelompok = '';

        foreach ($items as $it) {
            if ($it['kelompok_bmn'] !== $currentKelompok) {
                $currentKelompok = $it['kelompok_bmn'];
                $sheet->setCellValue('A' . $rowIdx, $currentKelompok);
                $sheet->mergeCells('A' . $rowIdx . ':L' . $rowIdx);
                $sheet->getStyle('A' . $rowIdx)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
                ]);
                $rowIdx++;
            }

            $sheet->setCellValue('A' . $rowIdx, $it['no_urut']);
            $sheet->setCellValue('B' . $rowIdx, $it['kode_bmn']);
            $sheet->setCellValue('C' . $rowIdx, $it['nup'] ?: '-');
            $sheet->setCellValue('D' . $rowIdx, $it['jenis_bmn']);
            $sheet->setCellValue('E' . $rowIdx, $it['spesifikasi']);
            $sheet->setCellValue('F' . $rowIdx, $it['jumlah']);
            $sheet->setCellValue('G' . $rowIdx, $it['satuan']);
            $sheet->setCellValue('H' . $rowIdx, $it['nilai_perolehan']);
            $sheet->setCellValue('I' . $rowIdx, $it['total_nilai_perolehan']);
            $sheet->setCellValue('J' . $rowIdx, $it['kondisi']);
            $sheet->setCellValue('K' . $rowIdx, $it['lokasi']);
            $sheet->setCellValue('L' . $rowIdx, $it['keterangan'] ?: '');

            $sheet->getStyle('H' . $rowIdx . ':I' . $rowIdx)->getNumberFormat()->setFormatCode('#,##0');
            $rowIdx++;
        }

        // Auto width
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'Kertas_Kerja_BMN_Sekolah_Rakyat_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
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
