<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\InventarisPemeliharaanModel;
use App\Models\InventarisPemeliharaanItemModel;
use App\Models\InventarisPemeliharaanFotoModel;
use App\Models\InventarisSatkerModel;
use App\Models\InventarisPengaturanModel;
use App\Libraries\TimeMarkWatermarkService;
use CodeIgniter\HTTP\RedirectResponse;
use Dompdf\Dompdf;
use Dompdf\Options;

class InventarisPemeliharaan extends BaseController
{
    private const MENU_LINK = 'admin/inventaris/pemeliharaan';

    private function checkAccess(): ?RedirectResponse
    {
        return $this->denyIfNoMenuAccess(self::MENU_LINK);
    }

    private function resolvePermissions(): array
    {
        return $this->resolveMenuPermissions(self::MENU_LINK);
    }

    public function index()
    {
        $forbidden = $this->checkAccess();
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $permissions = $this->resolvePermissions();
        $model = new InventarisPemeliharaanModel();

        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $perPage = 15;
        $offset = ($page - 1) * $perPage;

        $filters = [
            'q'     => trim((string) ($this->request->getGet('q') ?? '')),
            'tahun' => trim((string) ($this->request->getGet('tahun') ?? '')),
            'jenis' => trim((string) ($this->request->getGet('jenis') ?? '')),
        ];

        $list = $model->getList($filters, $perPage, $offset);
        $totalRows = $model->countFiltered($filters);
        $totalPages = (int) ceil($totalRows / $perPage);

        // Ambil daftar tahun surat yang ada untuk filter
        $db = db_connect();
        $tahunList = [];
        if ($db->tableExists('trn_inventaris_pemeliharaan')) {
            $thRows = $db->table('trn_inventaris_pemeliharaan')
                ->select('DISTINCT YEAR(tanggal_surat) as thn', false)
                ->orderBy('thn', 'DESC')
                ->get()
                ->getResultArray();
            $tahunList = array_filter(array_column($thRows, 'thn'));
        }

        $data = [
            'title'       => 'Surat Pemeliharaan Aset',
            'list'        => $list,
            'filters'     => $filters,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'totalRows'   => $totalRows,
            'perPage'     => $perPage,
            'tahunList'   => $tahunList,
            'permissions' => $permissions,
        ];

        return view('admin/inventaris/pemeliharaan/index', $data);
    }

    public function create()
    {
        $forbidden = $this->checkAccess();
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $permissions = $this->resolvePermissions();
        if (empty($permissions['can_add'])) {
            return redirect()->to('/admin/inventaris/pemeliharaan')->with('error', 'Anda tidak memiliki hak akses untuk menambah surat pemeliharaan.');
        }

        $pengaturanModel = new InventarisPengaturanModel();
        $kasatker = $pengaturanModel->getKasatkerByDate(date('Y-m-d'));
        $kopSuratList = $pengaturanModel->getKopSuratList();

        // Cek jika ada item awal yang dikirim dari shortcut Daftar Barang (via ?ids=1,2,3)
        $preselectedItems = [];
        $idsParam = trim((string) ($this->request->getGet('ids') ?? ''));
        if ($idsParam !== '') {
            $ids = array_filter(array_map('intval', explode(',', $idsParam)));
            if (! empty($ids)) {
                $satkerModel = new InventarisSatkerModel();
                $rows = $satkerModel->whereIn('id', $ids)->findAll();
                foreach ($rows as $r) {
                    $preselectedItems[] = [
                        'inventaris_id'   => (int) $r['id'],
                        'jenis_barang'    => 'MESIN PERALATAN NON TIK',
                        'kode_barang'     => (string) ($r['kode_barang'] ?? ''),
                        'nama_barang'     => clean_inventaris_text((string) ($r['nama_barang'] ?? '')),
                        'merk_tipe'       => clean_inventaris_text((string) ($r['merk_tipe'] ?? ($r['merk'] ?? ''))),
                        'nup'             => (string) ($r['nup'] ?? ''),
                        'kondisi'         => (string) ($r['kondisi'] ?? 'Baik'),
                        'tahun_perolehan' => (string) ($r['tahun_perolehan'] ?? date('Y')),
                    ];
                }
            }
        }

        $data = [
            'title'            => 'Buat Surat Pemeliharaan Aset Baru',
            'isEdit'           => false,
            'pemeliharaan'     => [
                'id'                 => 0,
                'no_surat'           => '',
                'judul_surat'        => 'SURAT KETERANGAN PEMELIHARAAN PERALATAN & MESIN TIK',
                'jenis_pemeliharaan' => 'TIK',
                'tanggal_surat'      => date('Y-m-d'),
                'tempat_surat'       => 'Pekanbaru',
                'nama_kasatker'      => $kasatker['nama'] ?? '',
                'nip_kasatker'       => $kasatker['nip'] ?? '',
                'jabatan_kasatker'   => $kasatker['jabatan'] ?? 'Kepala Satuan Kerja Pelaksanaan Prasarana Strategis Riau',
                'unit_kerja'         => 'Satuan Kerja Pelaksanaan Prasarana Strategis Riau',
                'kop_surat_id'       => null,
                'alasan_poin_a'      => 'BMN masih dalam kondisi baik dan masih layak untuk digunakan;',
                'alasan_poin_b'      => "1. Memiliki spesifikasi dan fungsi yang masih sesuai dengan kebutuhan operasional unit kerja/masih digunakan secara rutin dan memiliki peran penting dalam mendukung kelancaran kegiatan operasional unit kerja.\n2. Pemeliharaan rutin memastikan aset tetap dalam kondisi prima (siap pakai) untuk mendukung kelancaran pelayanan publik dan tugas fungsi pemerintah tanpa kendala teknis.\n3. Secara teknis, perawatan berkala jauh lebih murah dan efisien dibanding mendanai perbaikan berat atau penggantian total akibat kerusakan fatal (kerusakan struktural/sistem).",
                'keterangan'         => '',
            ],
            'items'            => $preselectedItems,
            'fotos'            => [],
            'kopSuratList'     => $kopSuratList,
            'permissions'      => $permissions,
        ];

        return view('admin/inventaris/pemeliharaan/form', $data);
    }

    public function store()
    {
        $forbidden = $this->checkAccess();
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $permissions = $this->resolvePermissions();
        if (empty($permissions['can_add'])) {
            return redirect()->to('/admin/inventaris/pemeliharaan')->with('error', 'Anda tidak memiliki hak akses untuk menambah surat pemeliharaan.');
        }

        $noSurat = trim((string) $this->request->getPost('no_surat'));
        $tanggalSurat = trim((string) $this->request->getPost('tanggal_surat'));
        $judulSurat = trim((string) ($this->request->getPost('judul_surat') ?: 'SURAT KETERANGAN PEMELIHARAAN PERALATAN & MESIN TIK'));
        $rawItemsJson = (string) $this->request->getPost('items_json');
        $items = json_decode($rawItemsJson, true);

        if ($noSurat === '' || $tanggalSurat === '') {
            return redirect()->back()->withInput()->with('error', 'Nomor surat dan tanggal surat wajib diisi.');
        }

        if (empty($items) || ! is_array($items)) {
            return redirect()->back()->withInput()->with('error', 'Pilih minimal satu barang inventaris untuk dimasukkan ke dalam surat pemeliharaan.');
        }

        $userId = (int) (session()->get('id') ?? session()->get('user_id') ?? 0);

        $db = db_connect();
        $db->transStart();

        $headerData = [
            'no_surat'           => $noSurat,
            'judul_surat'        => $judulSurat,
            'jenis_pemeliharaan' => trim((string) ($this->request->getPost('jenis_pemeliharaan') ?: 'TIK')),
            'tanggal_surat'      => $tanggalSurat,
            'tempat_surat'       => trim((string) ($this->request->getPost('tempat_surat') ?: 'Pekanbaru')),
            'nama_kasatker'      => trim((string) $this->request->getPost('nama_kasatker')),
            'nip_kasatker'       => trim((string) $this->request->getPost('nip_kasatker')),
            'jabatan_kasatker'   => trim((string) $this->request->getPost('jabatan_kasatker')),
            'unit_kerja'         => trim((string) ($this->request->getPost('unit_kerja') ?: 'Satuan Kerja Pelaksanaan Prasarana Strategis Riau')),
            'kop_surat_id'       => ! empty($this->request->getPost('kop_surat_id')) ? (int) $this->request->getPost('kop_surat_id') : null,
            'alasan_poin_a'      => trim((string) $this->request->getPost('alasan_poin_a')),
            'alasan_poin_b'      => trim((string) $this->request->getPost('alasan_poin_b')),
            'keterangan'         => trim((string) $this->request->getPost('keterangan')),
            'total_item'         => count($items),
            'created_by'         => $userId,
            'updated_by'         => $userId,
        ];

        $pemeliharaanModel = new InventarisPemeliharaanModel();
        $pemeliharaanId = (int) $pemeliharaanModel->insert($headerData);

        // 1. Simpan Items
        $itemModel = new InventarisPemeliharaanItemModel();
        $kelompokMap = []; // kelompok_key => ['nama_barang' => ..., 'nups' => []]

        foreach ($items as $idx => $it) {
            $namaBarang = clean_inventaris_text((string) ($it['nama_barang'] ?? ''));
            $merkTipe   = clean_inventaris_text((string) ($it['merk_tipe'] ?? ''));
            $nup        = trim((string) ($it['nup'] ?? ''));
            
            // Generate kelompok key berdasarkan kode_barang + nama_barang + merk_tipe
            $kKey = md5(trim((string) ($it['kode_barang'] ?? '')) . '::' . $namaBarang . '::' . $merkTipe);

            $itemModel->insert([
                'pemeliharaan_id' => $pemeliharaanId,
                'inventaris_id'   => ! empty($it['inventaris_id']) ? (int) $it['inventaris_id'] : null,
                'jenis_barang'    => trim((string) ($it['jenis_barang'] ?? 'MESIN PERALATAN NON TIK')),
                'kode_barang'     => trim((string) ($it['kode_barang'] ?? '')),
                'nama_barang'     => $namaBarang,
                'merk_tipe'       => $merkTipe,
                'nup'             => $nup,
                'kondisi'         => trim((string) ($it['kondisi'] ?? 'Baik')),
                'tahun_perolehan' => trim((string) ($it['tahun_perolehan'] ?? '')),
                'kelompok_key'    => $kKey,
                'urutan'          => $idx + 1,
            ]);

            if (! isset($kelompokMap[$kKey])) {
                $kelompokMap[$kKey] = [
                    'nama_barang' => $namaBarang,
                    'merk_tipe'   => $merkTipe,
                    'nups'        => [],
                ];
            }
            if ($nup !== '') {
                $kelompokMap[$kKey]['nups'][] = $nup;
            }
        }

        // 2. Simpan Kelompok Foto & Proses Upload
        $fotoModel = new InventarisPemeliharaanFotoModel();
        $uploadDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'inventaris' . DIRECTORY_SEPARATOR . 'pemeliharaan';
        if (! is_dir($uploadDir)) {
            @mkdir($uploadDir, 0775, true);
        }

        $fIndex = 1;
        foreach ($kelompokMap as $kKey => $kData) {
            $nupGabungan = format_nup_range_summary($kData['nups']);

            $fotoPaths = [
                'foto_depan'    => null,
                'foto_atas'     => null,
                'foto_bawah'    => null,
                'foto_belakang' => null,
            ];

            foreach (['foto_depan', 'foto_atas', 'foto_bawah', 'foto_belakang'] as $pos) {
                $base64Input = $this->request->getPost('foto_base64_' . $kKey . '_' . str_replace('foto_', '', $pos));
                if (! empty($base64Input) && strpos($base64Input, 'data:image') === 0) {
                    $savedRelPath = $this->saveBase64Image($base64Input, $pemeliharaanId, $kKey, $pos, $uploadDir);
                    if ($savedRelPath) {
                        $fotoPaths[$pos] = $savedRelPath;
                    }
                } else {
                    $inputName = 'foto_' . $kKey . '_' . str_replace('foto_', '', $pos);
                    $file = $this->request->getFile($inputName);

                    if ($file && $file->isValid() && ! $file->hasMoved()) {
                        $savedRelPath = $this->processAndSaveUploadedImage($file, $pemeliharaanId, $kKey, $pos, $uploadDir);
                        if ($savedRelPath) {
                            $fotoPaths[$pos] = $savedRelPath;
                        }
                    }
                }
            }

            $fotoModel->insert([
                'pemeliharaan_id' => $pemeliharaanId,
                'kelompok_key'    => $kKey,
                'nama_barang'     => $kData['nama_barang'],
                'nup_gabungan'    => $nupGabungan,
                'foto_depan'      => $fotoPaths['foto_depan'],
                'foto_atas'       => $fotoPaths['foto_atas'],
                'foto_bawah'      => $fotoPaths['foto_bawah'],
                'foto_belakang'   => $fotoPaths['foto_belakang'],
                'urutan'          => $fIndex++,
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan surat pemeliharaan. Terjadi kesalahan pada database.');
        }

        return redirect()->to('/admin/inventaris/pemeliharaan')->with('success', 'Surat Keterangan Pemeliharaan berhasil dibuat.');
    }

    public function edit(int $id)
    {
        $forbidden = $this->checkAccess();
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $permissions = $this->resolvePermissions();
        if (empty($permissions['can_edit'])) {
            return redirect()->to('/admin/inventaris/pemeliharaan')->with('error', 'Anda tidak memiliki hak akses untuk mengubah surat pemeliharaan.');
        }

        $model = new InventarisPemeliharaanModel();
        $detail = $model->getDetail($id);

        if (! $detail) {
            return redirect()->to('/admin/inventaris/pemeliharaan')->with('error', 'Data surat pemeliharaan tidak ditemukan.');
        }

        $pengaturanModel = new InventarisPengaturanModel();
        $kopSuratList = $pengaturanModel->getKopSuratList();

        $data = [
            'title'        => 'Ubah Surat Pemeliharaan Aset',
            'isEdit'       => true,
            'pemeliharaan' => $detail,
            'items'        => $detail['items'] ?? [],
            'fotos'        => $detail['fotos'] ?? [],
            'kopSuratList' => $kopSuratList,
            'permissions'  => $permissions,
        ];

        return view('admin/inventaris/pemeliharaan/form', $data);
    }

    public function update(int $id)
    {
        $forbidden = $this->checkAccess();
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $permissions = $this->resolvePermissions();
        if (empty($permissions['can_edit'])) {
            return redirect()->to('/admin/inventaris/pemeliharaan')->with('error', 'Anda tidak memiliki hak akses untuk mengubah surat pemeliharaan.');
        }

        $model = new InventarisPemeliharaanModel();
        $existing = $model->getDetail($id);
        if (! $existing) {
            return redirect()->to('/admin/inventaris/pemeliharaan')->with('error', 'Data surat pemeliharaan tidak ditemukan.');
        }

        $noSurat = trim((string) $this->request->getPost('no_surat'));
        $tanggalSurat = trim((string) $this->request->getPost('tanggal_surat'));
        $judulSurat = trim((string) ($this->request->getPost('judul_surat') ?: 'SURAT KETERANGAN PEMELIHARAAN PERALATAN & MESIN TIK'));
        $rawItemsJson = (string) $this->request->getPost('items_json');
        $items = json_decode($rawItemsJson, true);

        if ($noSurat === '' || $tanggalSurat === '') {
            return redirect()->back()->withInput()->with('error', 'Nomor surat dan tanggal surat wajib diisi.');
        }

        if (empty($items) || ! is_array($items)) {
            return redirect()->back()->withInput()->with('error', 'Pilih minimal satu barang inventaris untuk dimasukkan ke dalam surat pemeliharaan.');
        }

        $userId = (int) (session()->get('id') ?? session()->get('user_id') ?? 0);

        $db = db_connect();
        $db->transStart();

        $headerData = [
            'no_surat'           => $noSurat,
            'judul_surat'        => $judulSurat,
            'jenis_pemeliharaan' => trim((string) ($this->request->getPost('jenis_pemeliharaan') ?: 'TIK')),
            'tanggal_surat'      => $tanggalSurat,
            'tempat_surat'       => trim((string) ($this->request->getPost('tempat_surat') ?: 'Pekanbaru')),
            'nama_kasatker'      => trim((string) $this->request->getPost('nama_kasatker')),
            'nip_kasatker'       => trim((string) $this->request->getPost('nip_kasatker')),
            'jabatan_kasatker'   => trim((string) $this->request->getPost('jabatan_kasatker')),
            'unit_kerja'         => trim((string) ($this->request->getPost('unit_kerja') ?: 'Satuan Kerja Pelaksanaan Prasarana Strategis Riau')),
            'kop_surat_id'       => ! empty($this->request->getPost('kop_surat_id')) ? (int) $this->request->getPost('kop_surat_id') : null,
            'alasan_poin_a'      => trim((string) $this->request->getPost('alasan_poin_a')),
            'alasan_poin_b'      => trim((string) $this->request->getPost('alasan_poin_b')),
            'keterangan'         => trim((string) $this->request->getPost('keterangan')),
            'total_item'         => count($items),
            'updated_by'         => $userId,
        ];

        $model->update($id, $headerData);

        // 1. Sinkronisasi Items: Hapus item lama, insert item baru
        $itemModel = new InventarisPemeliharaanItemModel();
        $itemModel->where('pemeliharaan_id', $id)->delete();

        $kelompokMap = [];
        foreach ($items as $idx => $it) {
            $namaBarang = clean_inventaris_text((string) ($it['nama_barang'] ?? ''));
            $merkTipe   = clean_inventaris_text((string) ($it['merk_tipe'] ?? ''));
            $nup        = trim((string) ($it['nup'] ?? ''));
            $kKey       = md5(trim((string) ($it['kode_barang'] ?? '')) . '::' . $namaBarang . '::' . $merkTipe);

            $itemModel->insert([
                'pemeliharaan_id' => $id,
                'inventaris_id'   => ! empty($it['inventaris_id']) ? (int) $it['inventaris_id'] : null,
                'jenis_barang'    => trim((string) ($it['jenis_barang'] ?? 'MESIN PERALATAN NON TIK')),
                'kode_barang'     => trim((string) ($it['kode_barang'] ?? '')),
                'nama_barang'     => $namaBarang,
                'merk_tipe'       => $merkTipe,
                'nup'             => $nup,
                'kondisi'         => trim((string) ($it['kondisi'] ?? 'Baik')),
                'tahun_perolehan' => trim((string) ($it['tahun_perolehan'] ?? '')),
                'kelompok_key'    => $kKey,
                'urutan'          => $idx + 1,
            ]);

            if (! isset($kelompokMap[$kKey])) {
                $kelompokMap[$kKey] = [
                    'nama_barang' => $namaBarang,
                    'merk_tipe'   => $merkTipe,
                    'nups'        => [],
                ];
            }
            if ($nup !== '') {
                $kelompokMap[$kKey]['nups'][] = $nup;
            }
        }

        // 2. Sinkronisasi Foto: Pertahankan foto yang sudah ada jika tidak di-reupload
        $fotoModel = new InventarisPemeliharaanFotoModel();
        $oldFotos = $fotoModel->where('pemeliharaan_id', $id)->findAll();
        $oldFotoByGroup = [];
        foreach ($oldFotos as $of) {
            $oldFotoByGroup[$of['kelompok_key']] = $of;
        }

        $uploadDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'inventaris' . DIRECTORY_SEPARATOR . 'pemeliharaan';
        if (! is_dir($uploadDir)) {
            @mkdir($uploadDir, 0775, true);
        }

        $newGroupKeys = array_keys($kelompokMap);

        // Hapus foto dari kelompok yang tidak digunakan lagi
        foreach ($oldFotoByGroup as $oldKey => $oldRow) {
            if (! in_array($oldKey, $newGroupKeys, true)) {
                foreach (['foto_depan', 'foto_atas', 'foto_bawah', 'foto_belakang'] as $pos) {
                    if (! empty($oldRow[$pos])) {
                        $fullOld = FCPATH . ltrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $oldRow[$pos]), DIRECTORY_SEPARATOR);
                        if (is_file($fullOld)) {
                            @unlink($fullOld);
                        }
                    }
                }
                $fotoModel->delete($oldRow['id']);
            }
        }

        $fIndex = 1;
        foreach ($kelompokMap as $kKey => $kData) {
            $nupGabungan = format_nup_range_summary($kData['nups']);
            $existingFoto = $oldFotoByGroup[$kKey] ?? null;

            $fotoPaths = [
                'foto_depan'    => $existingFoto['foto_depan'] ?? null,
                'foto_atas'     => $existingFoto['foto_atas'] ?? null,
                'foto_bawah'    => $existingFoto['foto_bawah'] ?? null,
                'foto_belakang' => $existingFoto['foto_belakang'] ?? null,
            ];

            foreach (['foto_depan', 'foto_atas', 'foto_bawah', 'foto_belakang'] as $pos) {
                // Cek jika ada request hapus foto lama via hidden input 'hapus_foto_{kKey}_{pos}'
                $hapusCheck = $this->request->getPost('hapus_' . $pos . '_' . $kKey);
                if ($hapusCheck === '1' && ! empty($fotoPaths[$pos])) {
                    $delPath = FCPATH . ltrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $fotoPaths[$pos]), DIRECTORY_SEPARATOR);
                    if (is_file($delPath)) {
                        @unlink($delPath);
                    }
                    $fotoPaths[$pos] = null;
                }

                $base64Input = $this->request->getPost('foto_base64_' . $kKey . '_' . str_replace('foto_', '', $pos));
                if (! empty($base64Input) && strpos($base64Input, 'data:image') === 0) {
                    // Hapus file lama jika ada
                    if (! empty($fotoPaths[$pos])) {
                        $delPath = FCPATH . ltrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $fotoPaths[$pos]), DIRECTORY_SEPARATOR);
                        if (is_file($delPath)) {
                            @unlink($delPath);
                        }
                    }
                    $savedRelPath = $this->saveBase64Image($base64Input, $id, $kKey, $pos, $uploadDir);
                    if ($savedRelPath) {
                        $fotoPaths[$pos] = $savedRelPath;
                    }
                } else {
                    $inputName = 'foto_' . $kKey . '_' . str_replace('foto_', '', $pos);
                    $file = $this->request->getFile($inputName);

                    if ($file && $file->isValid() && ! $file->hasMoved()) {
                        // Hapus file lama jika ada
                        if (! empty($fotoPaths[$pos])) {
                            $delPath = FCPATH . ltrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $fotoPaths[$pos]), DIRECTORY_SEPARATOR);
                            if (is_file($delPath)) {
                                @unlink($delPath);
                            }
                        }

                        $savedRelPath = $this->processAndSaveUploadedImage($file, $id, $kKey, $pos, $uploadDir);
                        if ($savedRelPath) {
                            $fotoPaths[$pos] = $savedRelPath;
                        }
                    }
                }
            }

            if ($existingFoto) {
                $fotoModel->update($existingFoto['id'], [
                    'nama_barang'   => $kData['nama_barang'],
                    'nup_gabungan'  => $nupGabungan,
                    'foto_depan'    => $fotoPaths['foto_depan'],
                    'foto_atas'     => $fotoPaths['foto_atas'],
                    'foto_bawah'    => $fotoPaths['foto_bawah'],
                    'foto_belakang' => $fotoPaths['foto_belakang'],
                    'urutan'        => $fIndex++,
                ]);
            } else {
                $fotoModel->insert([
                    'pemeliharaan_id' => $id,
                    'kelompok_key'    => $kKey,
                    'nama_barang'     => $kData['nama_barang'],
                    'nup_gabungan'    => $nupGabungan,
                    'foto_depan'      => $fotoPaths['foto_depan'],
                    'foto_atas'       => $fotoPaths['foto_atas'],
                    'foto_bawah'      => $fotoPaths['foto_bawah'],
                    'foto_belakang'   => $fotoPaths['foto_belakang'],
                    'urutan'          => $fIndex++,
                ]);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui surat pemeliharaan. Terjadi kesalahan pada database.');
        }

        return redirect()->to('/admin/inventaris/pemeliharaan')->with('success', 'Surat Keterangan Pemeliharaan berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        $forbidden = $this->checkAccess();
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $permissions = $this->resolvePermissions();
        if (empty($permissions['can_delete'])) {
            return redirect()->to('/admin/inventaris/pemeliharaan')->with('error', 'Anda tidak memiliki hak akses untuk menghapus surat pemeliharaan.');
        }

        $model = new InventarisPemeliharaanModel();
        $existing = $model->find($id);

        if (! $existing) {
            return redirect()->to('/admin/inventaris/pemeliharaan')->with('error', 'Data surat pemeliharaan tidak ditemukan.');
        }

        $model->deleteWithChildren($id);

        return redirect()->to('/admin/inventaris/pemeliharaan')->with('success', 'Surat pemeliharaan nomor ' . esc($existing['no_surat']) . ' berhasil dihapus.');
    }

    public function cetakPdf(int $id)
    {
        $forbidden = $this->checkAccess();
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $permissions = $this->resolvePermissions();
        if (empty($permissions['can_export'])) {
            return redirect()->to('/admin/inventaris/pemeliharaan')->with('error', 'Anda tidak memiliki hak akses untuk mengekspor/mencetak dokumen ini.');
        }

        $model = new InventarisPemeliharaanModel();
        $pemeliharaan = $model->getDetail($id);

        if (! $pemeliharaan) {
            return redirect()->to('/admin/inventaris/pemeliharaan')->with('error', 'Data surat pemeliharaan tidak ditemukan.');
        }

        // Kop Surat Base64
        $kopSuratId = ! empty($pemeliharaan['kop_surat_id']) ? (int) $pemeliharaan['kop_surat_id'] : null;
        $tglSurat = $pemeliharaan['tanggal_surat'] ?? date('Y-m-d');
        $kopSuratImg = '';

        if (function_exists('inventaris_kop_surat_img_tag')) {
            $kopSuratImg = inventaris_kop_surat_img_tag($kopSuratId, $tglSurat, 'width: 100%; max-height: 110px; object-fit: contain;', 'Kop Surat Instansi');
        }

        // Tanggal Format Indonesia (contoh: 26 Oktober 2026)
        $bulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $tglParts = explode('-', (string) $pemeliharaan['tanggal_surat']);
        $tglIndo = count($tglParts) === 3
            ? ((int) $tglParts[2]) . ' ' . ($bulanIndo[(int) $tglParts[1]] ?? '') . ' ' . $tglParts[0]
            : (string) $pemeliharaan['tanggal_surat'];

        // Konversi foto lampiran ke base64 agar aman di Dompdf
        $fotos = $pemeliharaan['fotos'] ?? [];
        foreach ($fotos as &$f) {
            foreach (['foto_depan', 'foto_atas', 'foto_bawah', 'foto_belakang'] as $pos) {
                $f[$pos . '_base64'] = '';
                if (! empty($f[$pos])) {
                    $cleanPos = ltrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $f[$pos]), DIRECTORY_SEPARATOR);
                    $fullPath = FCPATH . $cleanPos;
                    if (is_file($fullPath)) {
                        $mime = @mime_content_type($fullPath) ?: 'image/jpeg';
                        $binary = @file_get_contents($fullPath);
                        if ($binary !== false) {
                            $f[$pos . '_base64'] = 'data:' . $mime . ';base64,' . base64_encode($binary);
                        }
                    }
                }
            }
        }
        unset($f);

        $data = [
            'p'           => $pemeliharaan,
            'items'       => $pemeliharaan['items'] ?? [],
            'fotos'       => $fotos,
            'kopSuratImg' => $kopSuratImg,
            'tglIndo'     => $tglIndo,
        ];

        $html = view('admin/inventaris/pemeliharaan/cetak_pdf', $data);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $cleanNoSurat = preg_replace('/[^a-zA-Z0-9_\-]/', '_', (string) $pemeliharaan['no_surat']);
        $filename = 'Surat_Pemeliharaan_' . $cleanNoSurat . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    public function ajaxSearchBarang()
    {
        try {
            $q = trim((string) ($this->request->getGet('q') ?? ''));
            $kategori = trim((string) ($this->request->getGet('kategori') ?? ''));
            $ruanganId = trim((string) ($this->request->getGet('ruangan_id') ?? ''));

            $satkerModel = new InventarisSatkerModel();
            $builder = $satkerModel->builder();

            if ($q !== '') {
                $builder->groupStart()
                    ->like('nama_barang', $q)
                    ->orLike('kode_barang', $q)
                    ->orLike('merk_tipe', $q)
                    ->orLike('merk', $q)
                    ->orLike('nup', $q)
                ->groupEnd();
            }

            if ($kategori !== '' && $kategori !== 'all') {
                $builder->where('kategori', $kategori);
            }

            if ($ruanganId !== '' && $ruanganId !== 'all') {
                $builder->where('ruangan_id', (int) $ruanganId);
            }

            $rows = $builder->orderBy('nama_barang', 'ASC')
                ->orderBy('CAST(NULLIF(nup, "") AS UNSIGNED)', 'ASC', false)
                ->limit(100)
                ->get()
                ->getResultArray();

            $results = [];
            foreach ($rows as $r) {
                $namaClean = clean_inventaris_text((string) ($r['nama_barang'] ?? ''));
                $merkClean = clean_inventaris_text((string) ($r['merk_tipe'] ?? ($r['merk'] ?? '')));
                $results[] = [
                    'id'              => (int) $r['id'],
                    'kode_barang'     => (string) ($r['kode_barang'] ?? ''),
                    'nama_barang'     => $namaClean,
                    'merk_tipe'       => $merkClean,
                    'nup'             => (string) ($r['nup'] ?? ''),
                    'kondisi'         => (string) ($r['kondisi'] ?? 'Baik'),
                    'tahun_perolehan' => (string) ($r['tahun_perolehan'] ?? date('Y')),
                    'lokasi_ruangan'  => (string) ($r['lokasi_ruangan'] ?? '-'),
                    'jenis_barang'    => 'MESIN PERALATAN NON TIK',
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'data'    => $results,
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[ajaxSearchBarang] ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * Memproses file upload gambar, mengonversi HEIC jika ada, dan me-resize agar optimal untuk Dompdf.
     */
    private function processAndSaveUploadedImage($file, int $pemeliharaanId, string $kKey, string $pos, string $uploadDir): ?string
    {
        $ext = strtolower($file->getClientExtension());
        $randStr = substr(md5(uniqid((string) mt_rand(), true)), 0, 8);
        $tempTarget = $uploadDir . DIRECTORY_SEPARATOR . 'tmp_' . $pemeliharaanId . '_' . $randStr . '.' . $ext;

        $file->move($uploadDir, basename($tempTarget));
        $actualPath = $tempTarget;

        // Cek jika format HEIC
        if (in_array($ext, ['heic', 'heif'], true)) {
            $wmService = new TimeMarkWatermarkService();
            if ($wmService->isHeicFile($actualPath)) {
                $convertedJpg = $wmService->convertHeicToJpeg($actualPath);
                if ($convertedJpg && is_file($convertedJpg)) {
                    @unlink($actualPath);
                    $actualPath = $convertedJpg;
                    $ext = 'jpg';
                }
            }
        }

        // Final target filename
        $finalFilename = 'pmlh_' . $pemeliharaanId . '_' . substr($kKey, 0, 8) . '_' . str_replace('foto_', '', $pos) . '_' . time() . '_' . $randStr . '.jpg';
        $finalPath = $uploadDir . DIRECTORY_SEPARATOR . $finalFilename;

        // Resize gambar jika resolusi terlalu besar (max width 1280px) menggunakan GD
        $resized = $this->resizeImageGd($actualPath, $finalPath, 1280, 85);
        if ($resized) {
            if ($actualPath !== $finalPath && is_file($actualPath)) {
                @unlink($actualPath);
            }
            return 'uploads/inventaris/pemeliharaan/' . $finalFilename;
        }

        // Fallback jika resize gagal: rename langsung
        @rename($actualPath, $finalPath);
        return 'uploads/inventaris/pemeliharaan/' . $finalFilename;
    }

    private function resizeImageGd(string $srcPath, string $destPath, int $maxDimension = 1280, int $quality = 85): bool
    {
        if (! is_file($srcPath) || ! extension_loaded('gd')) {
            return false;
        }

        $info = @getimagesize($srcPath);
        if (! $info) {
            return false;
        }

        $width = $info[0];
        $height = $info[1];
        $type = $info[2];

        switch ($type) {
            case IMAGETYPE_JPEG:
                $img = @imagecreatefromjpeg($srcPath);
                break;
            case IMAGETYPE_PNG:
                $img = @imagecreatefrompng($srcPath);
                break;
            case IMAGETYPE_WEBP:
                $img = @imagecreatefromwebp($srcPath);
                break;
            default:
                return false;
        }

        if (! $img) {
            return false;
        }

        // Hitung skala baru jika dimensi melebihi batas
        if ($width > $maxDimension || $height > $maxDimension) {
            if ($width >= $height) {
                $newWidth = $maxDimension;
                $newHeight = (int) round(($height / $width) * $maxDimension);
            } else {
                $newHeight = $maxDimension;
                $newWidth = (int) round(($width / $height) * $maxDimension);
            }

            $newImg = imagecreatetruecolor($newWidth, $newHeight);

            // Handle transparency background jika PNG/WEBP
            $bg = imagecolorallocate($newImg, 255, 255, 255);
            imagefilledrectangle($newImg, 0, 0, $newWidth, $newHeight, $bg);

            imagecopyresampled($newImg, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($img);
            $img = $newImg;
        }

        $saved = @imagejpeg($img, $destPath, $quality);
        imagedestroy($img);

        return $saved;
    }

    /**
     * Menerapkan watermark resmi gaya TimeMark pada foto pemotretan langsung kamera.
     */
    public function applyWatermark()
    {
        $forbidden = $this->checkAccess();
        if ($forbidden instanceof RedirectResponse) {
            return $this->response->setJSON(['success' => false, 'error' => 'Akses ditolak: sesi login Anda tidak memiliki izin.']);
        }

        $namaBarang = clean_inventaris_text((string) $this->request->getPost('nama_barang'));
        $merkTipe   = clean_inventaris_text((string) $this->request->getPost('merk_tipe'));
        $nup        = trim((string) $this->request->getPost('nup'));
        $sudut      = trim((string) $this->request->getPost('sudut')); // e.g. "Tampak Depan"
        $coords     = trim((string) $this->request->getPost('coordinates'));
        $imageData  = (string) $this->request->getPost('image_data');
        $file       = $this->request->getFile('image_file');

        $tempDir = FCPATH . 'do_not_upload' . DIRECTORY_SEPARATOR . 'temp';
        if (! is_dir($tempDir)) {
            @mkdir($tempDir, 0775, true);
        }

        $tempSource = $tempDir . DIRECTORY_SEPARATOR . 'wm_src_' . uniqid((string) mt_rand(), true) . '.jpg';

        if (! empty($imageData) && strpos($imageData, 'data:image') === 0) {
            $parts = explode(',', $imageData);
            $rawBinary = base64_decode(end($parts));
            if ($rawBinary === false || $rawBinary === '') {
                return $this->response->setJSON(['success' => false, 'error' => 'Data gambar kamera tidak valid.']);
            }
            file_put_contents($tempSource, $rawBinary);
        } elseif ($file && $file->isValid() && ! $file->hasMoved()) {
            $file->move($tempDir, basename($tempSource));
        } else {
            return $this->response->setJSON(['success' => false, 'error' => 'Tidak ada gambar yang dikirim untuk diproses.']);
        }

        $notes = $namaBarang . ($merkTipe ? " ({$merkTipe})" : "") . " - {$sudut}";
        if ($nup !== '') {
            $notes .= "\nNUP: " . $nup;
        }

        $wmService = new TimeMarkWatermarkService();
        $wmRes = $wmService->processImage($tempSource, [
            'template'        => 'classic',
            'use_exif_time'   => false, // Waktu saat ini (real-time pemotretan)
            'location'        => 'Satuan Kerja Pelaksanaan Prasarana Strategis Riau',
            'coordinates'     => ($coords !== '') ? $coords : '0.4851° N, 101.4485° E',
            'notes'           => $notes,
            'output_quality'  => 88,
        ]);

        if (is_file($tempSource)) {
            @unlink($tempSource);
        }

        if (! empty($wmRes['success']) && ! empty($wmRes['data_uri'])) {
            return $this->response->setJSON([
                'success'  => true,
                'data_uri' => $wmRes['data_uri'],
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'error'   => $wmRes['error'] ?? 'Gagal memproses watermark pada foto.',
        ]);
    }

    /**
     * Menyimpan data URI base64 dari kamera langsung ke disk dalam format JPEG.
     */
    private function saveBase64Image(string $base64Data, int $pemeliharaanId, string $kKey, string $pos, string $uploadDir): ?string
    {
        $parts = explode(',', $base64Data);
        $binary = base64_decode(end($parts));
        if ($binary === false || $binary === '') {
            return null;
        }

        $randStr = substr(md5(uniqid((string) mt_rand(), true)), 0, 8);
        $finalFilename = 'pmlh_' . $pemeliharaanId . '_' . substr($kKey, 0, 8) . '_' . str_replace('foto_', '', $pos) . '_' . time() . '_' . $randStr . '.jpg';
        $finalPath = $uploadDir . DIRECTORY_SEPARATOR . $finalFilename;

        file_put_contents($finalPath, $binary);

        return 'uploads/inventaris/pemeliharaan/' . $finalFilename;
    }

    private function denyIfNoMenuAccess(string $menuLink): ?RedirectResponse
    {
        if (! $this->hasMenuAccess($menuLink)) {
            return redirect()->to('/admin')->with('error', 'Akses ditolak: Anda tidak memiliki izin untuk membuka modul ini.');
        }

        return null;
    }

    private function hasMenuAccess(string $menuLink): bool
    {
        $role = (string) (session()->get('role') ?? '');
        $db = db_connect();
        $roleId = $this->resolveRoleId($role, $db);

        if ($roleId === 1 || $roleId === 2) {
            return true;
        }

        if (! $db->tableExists('menu_akses')) {
            return true;
        }

        $menuId = $this->resolveMenuIdByLink($menuLink, $db);
        if ($menuId === null) {
            return true;
        }

        $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';
        $count = (int) $db->table('menu_akses')
            ->where($roleColumn, $roleId)
            ->where('menu_id', $menuId)
            ->countAllResults();

        return $count > 0;
    }

    private function resolveMenuPermissions(string $menuLink): array
    {
        $role = (string) (session()->get('role') ?? '');
        $db = db_connect();
        $roleId = $this->resolveRoleId($role, $db);

        $perms = [
            'can_add'      => in_array($roleId, [1, 2], true),
            'can_edit'     => in_array($roleId, [1, 2], true),
            'can_delete'   => in_array($roleId, [1, 2], true),
            'can_export'   => in_array($roleId, [1, 2], true),
            'can_import'   => in_array($roleId, [1, 2], true),
            'can_approval' => in_array($roleId, [1, 2], true),
        ];

        if (! $db->tableExists('menu_akses') || $roleId === null) {
            return $perms;
        }

        $menuId = $this->resolveMenuIdByLink($menuLink, $db);
        if ($menuId === null) {
            return $perms;
        }

        $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';
        $row = $db->table('menu_akses')
            ->where($roleColumn, $roleId)
            ->where('menu_id', $menuId)
            ->get()
            ->getRowArray();

        if (! $row) {
            return $perms;
        }

        return [
            'can_add'      => (bool) ($row['FiturAdd'] ?? false),
            'can_edit'     => (bool) ($row['FiturEdit'] ?? false),
            'can_delete'   => (bool) ($row['FiturDelete'] ?? false),
            'can_export'   => (bool) ($row['FiturExport'] ?? false),
            'can_import'   => (bool) ($row['FiturImport'] ?? false),
            'can_approval' => (bool) ($row['FiturApproval'] ?? false),
        ];
    }

    private function resolveRoleId(string $role, $db): ?int
    {
        $normalized = strtolower(trim($role));

        if ($db->tableExists('access_roles')) {
            $variants = [$normalized];
            if ($normalized === 'super administrator' || $normalized === 'superadministrator') {
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

        if ($db->tableExists('groups')) {
            $group = $db->table('groups')
                ->select('id')
                ->where('name', $normalized)
                ->get()
                ->getRowArray();

            if (is_array($group) && ! empty($group['id'])) {
                return (int) $group['id'];
            }
        }

        $map = [
            'super_administrator' => 1,
            'super administrator' => 1,
            'superadmin'          => 1,
            'super-admin'         => 1,
            'admin'               => 2,
            'administrator'       => 2,
            'verifikator'         => 3,
            'staf'                => 4,
            'kepala_satker'       => 5,
        ];

        return $map[$normalized] ?? null;
    }

    private function resolveMenuIdByLink(string $menuLink, $db): ?string
    {
        $cleanLink = trim(strtolower($menuLink), '/');

        foreach (['menu_lv3', 'menu_lv2', 'menu_lv1'] as $table) {
            if (! $db->tableExists($table)) {
                continue;
            }

            $row = $db->table($table)
                ->select('id')
                ->where('LOWER(TRIM(link))', $cleanLink)
                ->get()
                ->getRowArray();

            if (is_array($row) && ! empty($row['id'])) {
                return (string) $row['id'];
            }
        }

        return '11-08';
    }
}
