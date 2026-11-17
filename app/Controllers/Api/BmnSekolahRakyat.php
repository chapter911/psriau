<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\BmnSekolahRakyatModel;
use App\Models\BmnSekolahRakyatFotoModel;
use App\Models\BmnSekolahRakyatDasarPerhitunganModel;
use App\Libraries\TimeMarkWatermarkService;
use App\Services\ApiAuth;
use CodeIgniter\HTTP\ResponseInterface;

class BmnSekolahRakyat extends BaseController
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
     * Resolusi Hak Akses Pengguna berdasarkan tabel menu_akses
     */
    public function permissions()
    {
        $user = ApiAuth::getUser();
        if (! $user) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)->setJSON([
                'status'  => 'error',
                'message' => 'Sesi login tidak valid.',
            ]);
        }

        $barangPerms = [
            'view'   => ApiAuth::hasMenuAccess(self::MENU_BARANG_LINK, 'view'),
            'add'    => ApiAuth::hasMenuAccess(self::MENU_BARANG_LINK, 'add'),
            'edit'   => ApiAuth::hasMenuAccess(self::MENU_BARANG_LINK, 'edit'),
            'delete' => ApiAuth::hasMenuAccess(self::MENU_BARANG_LINK, 'delete'),
            'export' => ApiAuth::hasMenuAccess(self::MENU_BARANG_LINK, 'export'),
        ];

        $fotoPerms = [
            'view'   => ApiAuth::hasMenuAccess(self::MENU_FOTO_LINK, 'view'),
            'add'    => ApiAuth::hasMenuAccess(self::MENU_FOTO_LINK, 'add'),
            'edit'   => ApiAuth::hasMenuAccess(self::MENU_FOTO_LINK, 'edit'),
            'delete' => ApiAuth::hasMenuAccess(self::MENU_FOTO_LINK, 'delete'),
        ];

        $rekapPerms = [
            'view'   => ApiAuth::hasMenuAccess(self::MENU_REKAP_LINK, 'view'),
            'export' => ApiAuth::hasMenuAccess(self::MENU_REKAP_LINK, 'export'),
        ];

        $hasAnyAccess = $barangPerms['view'] || $fotoPerms['view'] || $rekapPerms['view'];

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => [
                'has_access'   => $hasAnyAccess,
                'barang'       => $barangPerms,
                'foto'         => $fotoPerms,
                'rekapitulasi' => $rekapPerms,
            ],
        ]);
    }

    /**
     * Daftar Barang BMN Sekolah Rakyat
     * URL: GET /api/bmn-sekolah-rakyat/barang
     */
    public function barang()
    {
        if (! ApiAuth::hasMenuAccess(self::MENU_BARANG_LINK, 'view')) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON([
                'status'  => 'error',
                'message' => 'Anda tidak memiliki hak akses melihat daftar barang BMN Sekolah Rakyat.',
            ]);
        }

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

        $permissions = [
            'can_add'    => ApiAuth::hasMenuAccess(self::MENU_BARANG_LINK, 'add'),
            'can_edit'   => ApiAuth::hasMenuAccess(self::MENU_BARANG_LINK, 'edit'),
            'can_delete' => ApiAuth::hasMenuAccess(self::MENU_BARANG_LINK, 'delete'),
            'can_export' => ApiAuth::hasMenuAccess(self::MENU_BARANG_LINK, 'export'),
        ];

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => [
                'items'         => $items,
                'stats'         => $stats,
                'lokasi_list'   => array_column($lokasiList, 'lokasi'),
                'kelompok_list' => $kelompokList,
                'permissions'   => $permissions,
            ],
        ]);
    }

    /**
     * Detail Barang + Foto-foto
     * URL: GET /api/bmn-sekolah-rakyat/barang/{id}
     */
    public function detailBarang(int $id)
    {
        if (! ApiAuth::hasMenuAccess(self::MENU_BARANG_LINK, 'view')) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON([
                'status'  => 'error',
                'message' => 'Akses ditolak.',
            ]);
        }

        $item = $this->bmnModel->find($id);
        if (! $item) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)->setJSON([
                'status'  => 'error',
                'message' => 'Data barang tidak ditemukan.',
            ]);
        }

        $fotos = $this->fotoModel->getFotoByBmnId($id);

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => [
                'item'  => $item,
                'fotos' => $fotos,
            ],
        ]);
    }

    /**
     * Tambah Barang Baru
     * URL: POST /api/bmn-sekolah-rakyat/barang
     */
    public function tambahBarang()
    {
        if (! ApiAuth::hasMenuAccess(self::MENU_BARANG_LINK, 'add')) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON([
                'status'  => 'error',
                'message' => 'Anda tidak memiliki hak akses untuk menambah barang.',
            ]);
        }

        $user = ApiAuth::getUser();
        $input = $this->request->getJSON(true) ?: $this->request->getPost();

        $kelompok = trim((string) ($input['kelompok_bmn'] ?? ''));
        $kodeBmn  = trim((string) ($input['kode_bmn'] ?? ''));
        $jenisBmn = trim((string) ($input['jenis_bmn'] ?? ''));
        $lokasi   = trim((string) ($input['lokasi'] ?? ''));
        $spek     = trim((string) ($input['spesifikasi'] ?? ''));
        $satuan   = trim((string) ($input['satuan'] ?? '')) ?: 'Unit';
        $nup      = trim((string) ($input['nup'] ?? '')) ?: null;
        $kondisi  = trim((string) ($input['kondisi'] ?? '')) ?: 'Baik';
        $ket      = trim((string) ($input['keterangan'] ?? '')) ?: null;

        if ($kelompok === '' || $kodeBmn === '' || $jenisBmn === '' || $lokasi === '') {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)->setJSON([
                'status'  => 'error',
                'message' => 'Kelompok BMN, Kode BMN, Jenis BMN, dan Lokasi wajib diisi.',
            ]);
        }

        $jumlah   = (float) ($input['jumlah'] ?? 1);
        $rawNilai = (string) ($input['nilai_perolehan'] ?? 0);
        $nilai    = (float) str_replace(['.', ','], ['', '.'], $rawNilai);
        $total    = $jumlah * $nilai;

        // Auto calculate no_urut per kelompok
        $lastRow = $this->bmnModel->where('kelompok_bmn', $kelompok)->orderBy('no_urut', 'DESC')->first();
        $nextNo  = $lastRow ? ((int) $lastRow['no_urut'] + 1) : 1;

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
            'created_by'            => (int) ($user['id'] ?? 1),
        ];

        $insertId = $this->bmnModel->insert($insertData);
        $createdItem = $this->bmnModel->find($insertId);

        return $this->response->setStatusCode(ResponseInterface::HTTP_CREATED)->setJSON([
            'status'  => 'success',
            'message' => 'Barang BMN Sekolah Rakyat berhasil ditambahkan.',
            'data'    => $createdItem,
        ]);
    }

    /**
     * Ubah Barang
     * URL: POST /api/bmn-sekolah-rakyat/barang/{id}/update
     */
    public function ubahBarang(int $id)
    {
        if (! ApiAuth::hasMenuAccess(self::MENU_BARANG_LINK, 'edit')) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON([
                'status'  => 'error',
                'message' => 'Anda tidak memiliki hak akses untuk mengubah data barang.',
            ]);
        }

        $existing = $this->bmnModel->find($id);
        if (! $existing) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)->setJSON([
                'status'  => 'error',
                'message' => 'Data barang tidak ditemukan.',
            ]);
        }

        $user = ApiAuth::getUser();
        $input = $this->request->getJSON(true) ?: $this->request->getPost();

        $kelompok = trim((string) ($input['kelompok_bmn'] ?? '')) ?: $existing['kelompok_bmn'];
        $kodeBmn  = trim((string) ($input['kode_bmn'] ?? '')) ?: $existing['kode_bmn'];
        $jenisBmn = trim((string) ($input['jenis_bmn'] ?? '')) ?: $existing['jenis_bmn'];
        $lokasi   = trim((string) ($input['lokasi'] ?? '')) ?: $existing['lokasi'];
        $spek     = isset($input['spesifikasi']) ? trim((string) $input['spesifikasi']) : $existing['spesifikasi'];
        $satuan   = trim((string) ($input['satuan'] ?? '')) ?: $existing['satuan'];
        $nup      = isset($input['nup']) ? (trim((string) $input['nup']) ?: null) : $existing['nup'];
        $kondisi  = trim((string) ($input['kondisi'] ?? '')) ?: $existing['kondisi'];
        $ket      = isset($input['keterangan']) ? (trim((string) $input['keterangan']) ?: null) : $existing['keterangan'];

        $jumlah   = isset($input['jumlah']) ? (float) $input['jumlah'] : (float) $existing['jumlah'];
        $rawNilai = (string) ($input['nilai_perolehan'] ?? '');
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
            'updated_by'            => (int) ($user['id'] ?? 1),
        ];

        $this->bmnModel->update($id, $updateData);
        $updated = $this->bmnModel->find($id);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Data barang berhasil diperbarui.',
            'data'    => $updated,
        ]);
    }

    /**
     * Hapus Barang
     * URL: POST /api/bmn-sekolah-rakyat/barang/{id}/delete
     */
    public function hapusBarang(int $id)
    {
        if (! ApiAuth::hasMenuAccess(self::MENU_BARANG_LINK, 'delete')) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON([
                'status'  => 'error',
                'message' => 'Anda tidak memiliki hak akses untuk menghapus data barang.',
            ]);
        }

        $existing = $this->bmnModel->find($id);
        if (! $existing) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)->setJSON([
                'status'  => 'error',
                'message' => 'Data barang tidak ditemukan.',
            ]);
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

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Barang BMN dan seluruh fotonya berhasil dihapus.',
        ]);
    }

    /**
     * Update NUP Cepat
     * URL: POST /api/bmn-sekolah-rakyat/barang/{id}/update-nup
     */
    public function updateNup(int $id)
    {
        if (! ApiAuth::hasMenuAccess(self::MENU_BARANG_LINK, 'edit')) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON([
                'status'  => 'error',
                'message' => 'Akses ditolak: Anda tidak memiliki izin mengedit data.',
            ]);
        }

        $existing = $this->bmnModel->find($id);
        if (! $existing) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)->setJSON([
                'status'  => 'error',
                'message' => 'Data barang tidak ditemukan.',
            ]);
        }

        $input = $this->request->getJSON(true) ?: $this->request->getPost();
        $nup = trim((string) ($input['nup'] ?? ''));

        $this->bmnModel->update($id, ['nup' => $nup ?: null]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'NUP berhasil diperbarui.',
            'nup'     => $nup ?: null,
        ]);
    }

    /**
     * Data Survei & Dokumentasi Foto
     * URL: GET /api/bmn-sekolah-rakyat/foto
     */
    public function foto()
    {
        if (! ApiAuth::hasMenuAccess(self::MENU_FOTO_LINK, 'view')) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON([
                'status'  => 'error',
                'message' => 'Anda tidak memiliki hak akses melihat menu foto BMN Sekolah Rakyat.',
            ]);
        }

        $lokasiStats = $this->bmnModel->getStatistikFotoPerLokasi();
        $defaultLokasi = ! empty($lokasiStats) ? $lokasiStats[0]['lokasi'] : '';

        $filterLokasi   = trim((string) $this->request->getGet('lokasi')) ?: $defaultLokasi;
        $filterMaterial = trim((string) $this->request->getGet('material'));
        $filterStatus   = trim((string) $this->request->getGet('status_foto'));

        $materialList = [];
        if ($filterLokasi !== '') {
            $materialList = $this->bmnModel->getMaterialListPerLokasi($filterLokasi);
        }

        $items = [];
        if ($filterLokasi !== '') {
            $items = $this->bmnModel->getItemsForFoto($filterLokasi, $filterMaterial, $filterStatus);
        }

        $permissions = [
            'can_add'    => ApiAuth::hasMenuAccess(self::MENU_FOTO_LINK, 'add'),
            'can_edit'   => ApiAuth::hasMenuAccess(self::MENU_FOTO_LINK, 'edit'),
            'can_delete' => ApiAuth::hasMenuAccess(self::MENU_FOTO_LINK, 'delete'),
        ];

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => [
                'lokasi_stats'    => $lokasiStats,
                'selected_lokasi' => $filterLokasi,
                'material_list'   => $materialList,
                'items'           => $items,
                'permissions'     => $permissions,
            ],
        ]);
    }

    /**
     * Upload & Simpan Foto BMN dengan Watermark TimeMark Otomatis
     * URL: POST /api/bmn-sekolah-rakyat/foto/{bmnId}/upload
     */
    public function uploadFoto(int $bmnId)
    {
        if (! ApiAuth::hasMenuAccess(self::MENU_FOTO_LINK, 'add') && ! ApiAuth::hasMenuAccess(self::MENU_FOTO_LINK, 'edit')) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON([
                'status'  => 'error',
                'message' => 'Akses ditolak: Anda tidak memiliki izin mengunggah foto.',
            ]);
        }

        $bmn = $this->bmnModel->find($bmnId);
        if (! $bmn) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)->setJSON([
                'status'  => 'error',
                'message' => 'Data barang tidak ditemukan.',
            ]);
        }

        $user = ApiAuth::getUser();
        $judulFoto = trim((string) $this->request->getPost('judul_foto')) ?: 'Foto Dokumentasi';
        $ket       = trim((string) $this->request->getPost('keterangan')) ?: null;
        $lat       = trim((string) $this->request->getPost('latitude'));
        $lng       = trim((string) $this->request->getPost('longitude'));
        $takenAt   = trim((string) $this->request->getPost('taken_at')) ?: date('Y-m-d H:i:s');
        $imageData = (string) $this->request->getPost('image_data');
        $file      = $this->request->getFile('image_file');
        $fileWm    = $this->request->getFile('image_watermarked');

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
                return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)->setJSON([
                    'status'  => 'error',
                    'message' => 'Data gambar tidak valid.',
                ]);
            }
            file_put_contents($origPath, $rawBinary);
        } elseif ($file && $file->isValid() && ! $file->hasMoved()) {
            $file->move($origDir, $baseFilename);
        } else {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)->setJSON([
                'status'  => 'error',
                'message' => 'Tidak ada berkas gambar yang dikirim.',
            ]);
        }

        // Jika mobile sudah mengirimkan versi watermarked langsung
        if ($fileWm && $fileWm->isValid() && ! $fileWm->hasMoved()) {
            $fileWm->move($wmDir, $baseFilename);
            $wmDbPath = 'uploads/bmn_sekolah_rakyat/watermarked/' . $baseFilename;
        } else {
            // Apply Watermark TimeMark di sisi server
            $coords = '';
            if ($lat !== '' && $lng !== '') {
                $coords = $lat . ', ' . $lng;
            } else {
                $coords = '-0.5342° S, 101.5492° E';
            }

            $notes = $bmn['jenis_bmn'] . "\n" . ($bmn['spesifikasi'] ?: '') . "\nLokasi: " . $bmn['lokasi'] . " | " . $judulFoto;
            if (! empty($bmn['nup'])) {
                $notes .= " | NUP: " . $bmn['nup'];
            }

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
                $wmDbPath = 'uploads/bmn_sekolah_rakyat/original/' . $baseFilename;
            }
        }

        $fotoData = [
            'bmn_id'         => $bmnId,
            'judul_foto'     => $judulFoto,
            'foto_asli'      => 'uploads/bmn_sekolah_rakyat/original/' . $baseFilename,
            'foto_watermark' => $wmDbPath,
            'latitude'       => $lat ?: null,
            'longitude'      => $lng ?: null,
            'taken_at'       => $takenAt,
            'keterangan'     => $ket,
            'created_by'     => (int) ($user['id'] ?? 1),
        ];

        $fotoId = $this->fotoModel->insert($fotoData);
        $fotoData['id'] = $fotoId;

        return $this->response->setStatusCode(ResponseInterface::HTTP_CREATED)->setJSON([
            'status'  => 'success',
            'message' => 'Foto dokumentasi berhasil diunggah dengan watermark.',
            'data'    => $fotoData,
        ]);
    }

    /**
     * Hapus Foto
     * URL: POST /api/bmn-sekolah-rakyat/foto/{fotoId}/delete
     */
    public function hapusFoto(int $fotoId)
    {
        if (! ApiAuth::hasMenuAccess(self::MENU_FOTO_LINK, 'delete')) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON([
                'status'  => 'error',
                'message' => 'Akses ditolak: Anda tidak memiliki izin menghapus foto.',
            ]);
        }

        $foto = $this->fotoModel->find($fotoId);
        if (! $foto) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)->setJSON([
                'status'  => 'error',
                'message' => 'Foto tidak ditemukan.',
            ]);
        }

        if (! empty($foto['foto_asli']) && is_file(FCPATH . $foto['foto_asli'])) {
            @unlink(FCPATH . $foto['foto_asli']);
        }
        if (! empty($foto['foto_watermark']) && is_file(FCPATH . $foto['foto_watermark'])) {
            @unlink(FCPATH . $foto['foto_watermark']);
        }

        $this->fotoModel->delete($fotoId);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Foto berhasil dihapus.',
        ]);
    }

    /**
     * Rekapitulasi & Kertas Kerja BMN Sekolah Rakyat
     * URL: GET /api/bmn-sekolah-rakyat/rekapitulasi
     */
    public function rekapitulasi()
    {
        if (! ApiAuth::hasMenuAccess(self::MENU_REKAP_LINK, 'view')) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON([
                'status'  => 'error',
                'message' => 'Anda tidak memiliki hak akses melihat rekapitulasi BMN Sekolah Rakyat.',
            ]);
        }

        $dasarItems = $this->dasarModel->orderBy('no_urut', 'ASC')->findAll();
        $stats = $this->bmnModel->getSummaryStats();
        $kelompokSummary = $this->bmnModel->getRekapitulasiKelompok();
        $lokasiSummary   = $this->bmnModel->getRekapitulasiLokasi();

        $permissions = [
            'can_export' => ApiAuth::hasMenuAccess(self::MENU_REKAP_LINK, 'export'),
        ];

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => [
                'stats'             => $stats,
                'kelompok_summary'  => $kelompokSummary,
                'lokasi_summary'    => $lokasiSummary,
                'dasar_perhitungan' => $dasarItems,
                'pdf_url'           => site_url('admin/inventaris/sekolah-rakyat/rekapitulasi/cetak-pdf'),
                'excel_url'         => site_url('admin/inventaris/sekolah-rakyat/barang/export-excel'),
                'permissions'       => $permissions,
            ],
        ]);
    }
}
