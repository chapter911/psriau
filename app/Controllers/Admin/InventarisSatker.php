<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\InventarisSatkerModel;
use CodeIgniter\HTTP\RedirectResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Dompdf\Dompdf;
use Dompdf\Options;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class InventarisSatker extends BaseController
{
    private const MENU_LINK = 'admin/inventaris/barang';
    private const MENU_LINK_ALT = 'admin/inventaris/satker';

    /**
     * Tabel Standar Kodifikasi Baku Barang Milik Negara (BMN) Kementerian Keuangan
     * Menjamin Baris Tengah pada Stiker BMN menampilkan nama baku kodifikasi resmi BMN.
     */
    private const KODIFIKASI_BMN = [
        '3050104001' => 'Lemari Besi/Metal',
        '3050104003' => 'Rak Besi',
        '3050104007' => 'Brandkas',
        '3050105010' => 'White Board',
        '3050105015' => 'Alat Penghancur Kertas',
        '3050105096' => 'Papan Tulis Kaca',
        '3050201001' => 'Meja Kerja Besi/Metal',
        '3050201002' => 'Meja Kerja Kayu',
        '3050201003' => 'Kursi Besi/Metal',
        '3050201008' => 'Meja Rapat',
        '3050201020' => 'Kursi Fiber Glas/Plastik',
        '3050201033' => 'Sofa',
        '3050203005' => 'Air Cleaner',
        '3050204004' => 'A.C. Split',
        '3050204007' => 'Exhause Fan',
        '3050206002' => 'Televisi',
        '3050206007' => 'Loudspeaker',
        '3050206014' => 'Microphone',
        '3050206036' => 'Dispenser',
        '3060101056' => 'Battery Charger (Peralatan Studio Audio)',
        '3060102045' => 'Tripod Camera',
        '3060102128' => 'Camera Digital',
        '3060102132' => 'Video Conference',
        '3060102170' => 'Gimbal Tripod',
        '3060105047' => 'Kamera Udara',
        '3100102001' => 'P.C Unit',
        '3100102003' => 'Note Book',
        '3100102009' => 'Tablet PC',
        '3100203003' => 'Printer (Peralatan Personal Komputer)',
        '3100203004' => 'Scanner (Peralatan Personal Komputer)',
        '3100203017' => 'External/ Portable Hardisk',
        '6070501001' => 'Aset Tetap Lainnya Dalam Renovasi',
        '7010101005' => 'Aset Tetap Lainnya Dalam Pengerjaan',
    ];

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

        $isDataTableAjax = $this->request->getGet('draw') !== null || ($this->request->isAJAX() && $this->request->getGet('length') !== null);

        if ($isDataTableAjax) {
            $menuPermissions = $this->resolvePermissions();
            $canEdit   = (bool) ($menuPermissions['edit'] ?? false);
            $canDelete = (bool) ($menuPermissions['delete'] ?? false);
            $canExport = (bool) ($menuPermissions['export'] ?? false);

            $draw   = max(0, (int) ($this->request->getGet('draw') ?? 1));
            $start  = max(0, (int) ($this->request->getGet('start') ?? 0));
            $length = (int) ($this->request->getGet('length') ?? 25);
            if ($length < 1 || $length > 500) {
                $length = 25;
            }

            $searchParam = $this->request->getGet('search');
            $globalSearch = trim((string) (is_array($searchParam) ? ($searchParam['value'] ?? '') : $searchParam));

            $filterPeruntukan = strtolower(trim((string) ($this->request->getGet('peruntukan') ?? '')));
            $filterKategori   = trim((string) ($this->request->getGet('kategori') ?? ''));
            $filterKondisi    = trim((string) ($this->request->getGet('kondisi') ?? ''));
            $filterLokasi     = trim((string) ($this->request->getGet('lokasi') ?? ''));
            $searchKeyword    = trim((string) ($this->request->getGet('keyword') ?? ''));

            $builder = $db->table('trn_inventaris_satker');
            $recordsTotal = (int) $db->table('trn_inventaris_satker')->countAllResults();

            // Filters
            if ($filterPeruntukan === 'kantor') {
                $builder->where('peruntukan', 'kantor');
            } elseif ($filterPeruntukan === 'mobiler') {
                $builder->where('peruntukan', 'mobiler');
            } elseif ($filterPeruntukan === 'lainnya') {
                $builder->where('peruntukan', 'lainnya');
            } elseif ($filterPeruntukan === 'belum_ditentukan') {
                $builder->groupStart()
                    ->where('peruntukan', 'belum_ditentukan')
                    ->orWhere('peruntukan IS NULL', null, false)
                    ->orWhere('peruntukan', '')
                ->groupEnd();
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

            $term = $globalSearch !== '' ? $globalSearch : $searchKeyword;
            if ($term !== '') {
                $builder->groupStart()
                    ->like('nama_barang', $term)
                    ->orLike('kode_barang', $term)
                    ->orLike('nup', $term)
                    ->orLike('kode_register', $term)
                    ->orLike('merk', $term)
                    ->orLike('tipe', $term)
                    ->orLike('merk_tipe', $term)
                    ->orLike('lokasi_ruangan', $term)
                    ->orLike('kategori', $term)
                    ->orLike('keterangan', $term)
                    ->groupEnd();
            }

            $recordsFiltered = (int) $builder->countAllResults(false);

            // Ordering
            $orderParam = $this->request->getGet('order');
            $orderColIdx = (int) ($orderParam[0]['column'] ?? 1);
            $orderDir    = strtolower((string) ($orderParam[0]['dir'] ?? 'asc')) === 'desc' ? 'DESC' : 'ASC';

            $columnsMap = [
                0 => 'id',
                1 => 'kode_barang',
                2 => 'nup',
                3 => 'peruntukan',
                4 => 'nama_barang',
                5 => 'kategori',
                6 => 'merk_tipe',
                7 => 'jumlah',
                8 => 'kondisi',
                9 => 'lokasi_ruangan',
                10 => 'tahun_perolehan',
            ];

            if ($orderColIdx === 2) {
                $builder->orderBy('CAST(NULLIF(nup, "") AS UNSIGNED)', $orderDir, false)
                    ->orderBy('nup', $orderDir);
            } elseif ($orderColIdx === 1) {
                $builder->orderBy('kode_barang', $orderDir)
                    ->orderBy('CAST(NULLIF(nup, "") AS UNSIGNED)', 'ASC', false)
                    ->orderBy('nup', 'ASC');
            } elseif (isset($columnsMap[$orderColIdx])) {
                $builder->orderBy($columnsMap[$orderColIdx], $orderDir)
                    ->orderBy('CAST(NULLIF(nup, "") AS UNSIGNED)', 'ASC', false);
            } else {
                $builder->orderBy('kode_barang', 'ASC')
                    ->orderBy('CAST(NULLIF(nup, "") AS UNSIGNED)', 'ASC', false)
                    ->orderBy('nup', 'ASC');
            }

            $rows = $builder->limit($length, $start)->get()->getResultArray();

            $data = [];
            foreach ($rows as $idx => $row) {
                $no = $start + $idx + 1;
                $id = (int) $row['id'];
                $kodeBarang = esc((string) $row['kode_barang']);
                $nupStr = (string) ($row['nup'] ?? '');
                $nupEsc = esc($nupStr);

                $nupBadge = ($nupStr !== '')
                    ? '<span class="badge badge-light border font-mono font-weight-bold px-2 py-1">' . $nupEsc . '</span>'
                    : '<span class="text-muted">-</span>';

                $peruntukanVal = (string) ($row['peruntukan'] ?? 'belum_ditentukan');
                if ($peruntukanVal === 'mobiler') {
                    $peruntukanBadge = '<span class="badge badge-warning text-dark font-weight-bold px-2 py-1" title="Diperuntukkan untuk Mobiler Sekolah"><i class="fas fa-school mr-1"></i> Mobiler</span>';
                } elseif ($peruntukanVal === 'lainnya') {
                    $peruntukanBadge = '<span class="badge font-weight-bold px-2 py-1" style="background-color: #ede9fe; color: #6d28d9; border: 1px solid #ddd6fe;" title="Diperuntukkan untuk Item Lainnya"><i class="fas fa-layer-group mr-1"></i> Item Lainnya</span>';
                } elseif ($peruntukanVal === 'kantor') {
                    $peruntukanBadge = '<span class="badge badge-primary font-weight-bold px-2 py-1" title="Diperuntukkan untuk Kantor Satker"><i class="fas fa-building mr-1"></i> Kantor</span>';
                } else {
                    $peruntukanBadge = '<span class="badge font-weight-bold px-2 py-1" style="background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;" title="Peruntukan Belum Ditentukan / Belum Berlokasi"><i class="fas fa-question-circle mr-1"></i> Belum Ditentukan</span>';
                }

                $cleanNama = esc(clean_inventaris_text($row['nama_barang']));
                $namaHtml = '<div class="font-weight-bold text-primary">' . $cleanNama . '</div>';
                if (! empty($row['keterangan'])) {
                    $namaHtml .= '<small class="text-muted d-block mt-0.5"><i class="fas fa-info-circle mr-1"></i>' . esc($row['keterangan']) . '</small>';
                }

                $kategoriBadge = '<span class="badge badge-light border px-2 py-1">' . esc((string) $row['kategori']) . '</span>';
                $merkTipeHtml = esc(clean_inventaris_text($row['merk_tipe'] ?: '-'));
                $jumlahHtml = number_format((int) ($row['jumlah'] ?? 1)) . ' <small class="text-muted font-weight-normal">' . esc((string) ($row['satuan'] ?? 'Unit')) . '</small>';

                $kondisiVal = (string) ($row['kondisi'] ?? 'baik');
                if ($kondisiVal === 'baik') {
                    $kondisiBadge = '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Baik</span>';
                } elseif ($kondisiVal === 'rusak_ringan') {
                    $kondisiBadge = '<span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-exclamation-triangle mr-1"></i>Rusak Ringan</span>';
                } else {
                    $kondisiBadge = '<span class="badge badge-danger px-2 py-1"><i class="fas fa-times-circle mr-1"></i>Rusak Berat</span>';
                }

                $lokasiStr = $row['lokasi_ruangan'] ?: 'Belum ditentukan';
                $lokasiHtml = '<i class="fas fa-map-marker-alt text-muted mr-1.5" style="font-size: 0.8rem;"></i> ' . esc($lokasiStr);
                $tahunStr = esc((string) ($row['tahun_perolehan'] ?: '-'));

                $checkboxHtml = '<input type="checkbox" class="check-sticker-item" value="' . $id . '" title="Pilih aset ini untuk cetak sticker" style="cursor: pointer;"> <span class="text-muted small ml-1">' . $no . '</span>';

                $actionsHtml = '';
                if ($canExport || $canEdit || $canDelete) {
                    $actionsHtml .= '<div class="btn-group btn-group-sm" role="group">';
                    if ($canExport) {
                        $actionsHtml .= '<a href="' . site_url('admin/inventaris/barang/cetak-sticker?id=' . $id) . '" target="_blank" class="btn btn-info btn-sm" title="Cetak Sticker BMN (1 Baris 2 Sticker)"><i class="fas fa-qrcode"></i></a>';
                    }
                    if ($canEdit) {
                        $actionsHtml .= '<button type="button" class="btn btn-warning btn-sm btn-edit-inventaris" '
                            . 'data-toggle="modal" data-target="#modal-edit-inventaris" '
                            . 'data-id="' . $id . '" '
                            . 'data-kode="' . esc((string) $row['kode_barang'], 'attr') . '" '
                            . 'data-nup="' . esc((string) ($row['nup'] ?? ''), 'attr') . '" '
                            . 'data-register="' . esc((string) ($row['kode_register'] ?? ''), 'attr') . '" '
                            . 'data-peruntukan="' . esc((string) ($row['peruntukan'] ?? 'kantor'), 'attr') . '" '
                            . 'data-nama="' . esc(clean_inventaris_text($row['nama_barang']), 'attr') . '" '
                            . 'data-kategori="' . esc((string) $row['kategori'], 'attr') . '" '
                            . 'data-merk="' . esc(clean_inventaris_text($row['merk_tipe'] ?? ''), 'attr') . '" '
                            . 'data-jumlah="' . esc((string) ($row['jumlah'] ?? 1), 'attr') . '" '
                            . 'data-satuan="' . esc((string) ($row['satuan'] ?? 'Unit'), 'attr') . '" '
                            . 'data-kondisi="' . esc((string) $row['kondisi'], 'attr') . '" '
                            . 'data-lokasi="' . esc((string) $row['lokasi_ruangan'], 'attr') . '" '
                            . 'data-tahun="' . esc((string) ($row['tahun_perolehan'] ?? ''), 'attr') . '" '
                            . 'data-keterangan="' . esc((string) ($row['keterangan'] ?? ''), 'attr') . '" '
                            . 'title="Edit Barang"><i class="fas fa-edit"></i></button>';
                    }
                    if ($canDelete) {
                        $actionsHtml .= '<button type="button" class="btn btn-danger btn-sm btn-delete-inventaris" '
                            . 'data-toggle="modal" data-target="#modal-delete-inventaris" '
                            . 'data-id="' . $id . '" '
                            . 'data-nama="' . esc((string) $row['nama_barang'], 'attr') . '" '
                            . 'title="Hapus Barang"><i class="fas fa-trash"></i></button>';
                    }
                    $actionsHtml .= '</div>';
                }

                $data[] = [
                    'id'                  => $id,
                    'checkbox_html'       => $checkboxHtml,
                    'kode_barang'         => $kodeBarang,
                    'nup_html'            => $nupBadge,
                    'peruntukan_badge'    => $peruntukanBadge,
                    'nama_barang_html'    => $namaHtml,
                    'kategori_badge'      => $kategoriBadge,
                    'merk_tipe_html'      => $merkTipeHtml,
                    'jumlah_html'         => $jumlahHtml,
                    'kondisi_badge'       => $kondisiBadge,
                    'lokasi_ruangan_html' => $lokasiHtml,
                    'tahun_perolehan'     => $tahunStr,
                    'action_html'         => $actionsHtml,
                ];
            }

            return $this->response->setJSON([
                'draw'            => $draw,
                'recordsTotal'    => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data'            => $data,
            ]);
        }

        // Auto-heal: Pastikan seluruh data aset yang peruntukannya masih kosong / NULL di-defaultkan ke 'belum_ditentukan'
        if ($db->fieldExists('peruntukan', 'trn_inventaris_satker')) {
            $db->table('trn_inventaris_satker')
                ->groupStart()
                    ->where('peruntukan IS NULL', null, false)
                    ->orWhere('peruntukan', '')
                ->groupEnd()
                ->update(['peruntukan' => 'belum_ditentukan']);
        }

        $filterPeruntukan = strtolower(trim((string) $this->request->getGet('peruntukan')));
        $filterKategori   = trim((string) $this->request->getGet('kategori'));
        $filterKondisi    = trim((string) $this->request->getGet('kondisi'));
        $filterLokasi     = trim((string) $this->request->getGet('lokasi'));
        $searchKeyword    = trim((string) $this->request->getGet('keyword'));

        // Client-side payload is empty because DataTables Server-Side AJAX will asynchronously load pages
        $items = [];

        // Get unique dropdown options

        // Unique item list for bulk update modal (dikelompokkan berdasarkan kode_barang, nama_barang, dan merk_tipe)
        $uniqueKodeBarangList = $db->table('trn_inventaris_satker')
            ->select('kode_barang, nama_barang, merk_tipe,
                      COUNT(id) AS total_unit,
                      MIN(CAST(NULLIF(nup, "") AS UNSIGNED)) AS min_nup,
                      MAX(CAST(NULLIF(nup, "") AS UNSIGNED)) AS max_nup,
                      SUM(CASE WHEN (ruangan_id IS NULL OR ruangan_id = 0 OR lokasi_ruangan = "Belum berlokasi" OR peruntukan = "belum_ditentukan") THEN 1 ELSE 0 END) AS unallocated_unit,
                      MIN(CASE WHEN (ruangan_id IS NULL OR ruangan_id = 0 OR lokasi_ruangan = "Belum berlokasi" OR peruntukan = "belum_ditentukan") THEN CAST(NULLIF(nup, "") AS UNSIGNED) ELSE NULL END) AS unallocated_min_nup,
                      MAX(CASE WHEN (ruangan_id IS NULL OR ruangan_id = 0 OR lokasi_ruangan = "Belum berlokasi" OR peruntukan = "belum_ditentukan") THEN CAST(NULLIF(nup, "") AS UNSIGNED) ELSE NULL END) AS unallocated_max_nup')
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

        $masterRuanganList = $db->table('mst_ruangan')
            ->select('id, kode_ruangan, nama_ruangan, lokasi_lantai')
            ->orderBy('kode_ruangan', 'ASC')
            ->orderBy('nama_ruangan', 'ASC')
            ->get()
            ->getResultArray();

        $mergedRuangan = array_merge(
            array_column($masterRuanganList, 'nama_ruangan'),
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
        $totalKantor  = (int) $db->table('trn_inventaris_satker')->where('peruntukan', 'kantor')->countAllResults();
        $totalBelum   = (int) $db->table('trn_inventaris_satker')
            ->groupStart()
                ->where('peruntukan', 'belum_ditentukan')
                ->orWhere('peruntukan IS NULL', null, false)
                ->orWhere('peruntukan', '')
            ->groupEnd()
            ->countAllResults();
        $totalMobiler = (int) $db->table('trn_inventaris_satker')->where('peruntukan', 'mobiler')->countAllResults();
        $totalLainnya = (int) $db->table('trn_inventaris_satker')->where('peruntukan', 'lainnya')->countAllResults();
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
            'masterRuanganList'    => $masterRuanganList,
            'satuanOptions'        => $satuanOptions,
            'uniqueKodeBarangList' => $uniqueKodeBarangList,
            'filterPeruntukan'     => $filterPeruntukan,
            'filterKategori'       => $filterKategori,
            'filterKondisi'        => $filterKondisi,
            'filterLokasi'         => $filterLokasi,
            'searchKeyword'        => $searchKeyword,
            'summary'              => [
                'total'                  => $totalItems,
                'kantor'                 => $totalKantor,
                'belum_ditentukan'       => $totalBelum,
                'mobiler'                => $totalMobiler,
                'lainnya'                => $totalLainnya,
                'total_kantor'           => $totalKantor,
                'total_belum_ditentukan' => $totalBelum,
                'total_mobiler'          => $totalMobiler,
                'total_lainnya'          => $totalLainnya,
                'baik'                   => $totalBaik,
                'rusak_ringan'           => $totalRingan,
                'rusak_berat'            => $totalBerat,
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
            'peruntukan'     => 'permit_empty|in_list[kantor,mobiler,lainnya,belum_ditentukan]',
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
        $peruntukan = in_array(strtolower(trim((string) $this->request->getPost('peruntukan'))), ['kantor', 'mobiler', 'lainnya', 'belum_ditentukan'], true) ? strtolower(trim((string) $this->request->getPost('peruntukan'))) : 'belum_ditentukan';

        $model->insert([
            'kode_barang'     => trim((string) $this->request->getPost('kode_barang')),
            'nup'             => trim((string) $this->request->getPost('nup')) ?: null,
            'kode_register'   => trim((string) $this->request->getPost('kode_register')) ?: null,
            'nama_barang'     => clean_inventaris_text((string) $this->request->getPost('nama_barang')),
            'kategori'        => trim((string) $this->request->getPost('kategori')),
            'merk_tipe'       => clean_inventaris_text((string) $this->request->getPost('merk_tipe')) ?: null,
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
            'peruntukan'     => 'permit_empty|in_list[kantor,mobiler,lainnya,belum_ditentukan]',
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
        $peruntukan = in_array(strtolower(trim((string) $this->request->getPost('peruntukan'))), ['kantor', 'mobiler', 'lainnya', 'belum_ditentukan'], true) ? strtolower(trim((string) $this->request->getPost('peruntukan'))) : 'belum_ditentukan';

        $model->update($id, [
            'kode_barang'     => trim((string) $this->request->getPost('kode_barang')),
            'nup'             => trim((string) $this->request->getPost('nup')) ?: null,
            'kode_register'   => trim((string) $this->request->getPost('kode_register')) ?: null,
            'nama_barang'     => clean_inventaris_text((string) $this->request->getPost('nama_barang')),
            'kategori'        => trim((string) $this->request->getPost('kategori')),
            'merk_tipe'       => clean_inventaris_text((string) $this->request->getPost('merk_tipe')) ?: null,
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

        $rawKode    = trim((string) ($this->request->getPost('kode_barang') ?: $this->request->getPost('kode_barang_select')));
        $namaBarang = trim((string) $this->request->getPost('nama_barang'));
        $merkTipe   = trim((string) $this->request->getPost('merk_tipe'));
        $kodeBarang = $rawKode;

        if (strpos($rawKode, ':::') !== false) {
            $parts = explode(':::', $rawKode);
            $kodeBarang = trim($parts[0] ?? '');
            if ($namaBarang === '') {
                $namaBarang = trim($parts[1] ?? '');
            }
            if ($merkTipe === '') {
                $merkTipe = trim($parts[2] ?? '');
            }
        }

        $isAll = ($kodeBarang === 'ALL' || $rawKode === 'ALL');

        $lingkupNup = strtolower(trim((string) $this->request->getPost('lingkup_nup')));
        if (! in_array($lingkupNup, ['semua', 'sebagian'], true) || $isAll) {
            $lingkupNup = 'semua';
        }
        $peruntukan = strtolower(trim((string) $this->request->getPost('peruntukan')));

        if ($kodeBarang === '') {
            return redirect()->to('/admin/inventaris/barang')->with('error', 'Silakan pilih Kode Barang terlebih dahulu.');
        }

        if (! in_array($peruntukan, ['kantor', 'mobiler', 'lainnya', 'belum_ditentukan'], true)) {
            return redirect()->to('/admin/inventaris/barang')->with('error', 'Pilihan peruntukan tidak valid. Pilih "Kantor", "Belum Ditentukan", "Mobiler", atau "Item Lainnya".');
        }

        $db = db_connect();
        $userId = (int) (session()->get('userId') ?? 0);
        $filterSumber = strtolower(trim((string) ($this->request->getPost('filter_sumber') ?? 'belum')));
        if (! in_array($filterSumber, ['belum', 'semua'], true)) {
            $filterSumber = 'belum';
        }

        $targetRuanganId = (int) $this->request->getPost('target_ruangan_id');
        $targetRoom = null;
        if ($peruntukan === 'kantor' && $targetRuanganId > 0) {
            $targetRoom = $db->table('mst_ruangan')->where('id', $targetRuanganId)->get()->getRowArray();
        }

        $labelTarget = match ($peruntukan) {
            'mobiler'          => 'Mobiler (Sekolah)',
            'lainnya'          => 'Item Lainnya',
            'kantor'           => 'Kantor (Satker)',
            default            => 'Belum Ditentukan',
        };

        $builder = $db->table('trn_inventaris_satker');

        if (! $isAll) {
            $builder->where('kode_barang', $kodeBarang);

            if ($namaBarang !== '') {
                $builder->where('nama_barang', $namaBarang);
            }
            if ($merkTipe !== '') {
                $builder->where('merk_tipe', $merkTipe);
            }

            if ($lingkupNup === 'sebagian') {
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
            }
        }

        if ($filterSumber === 'belum') {
            $builder->groupStart()
                ->where('ruangan_id IS NULL', null, false)
                ->orWhere('ruangan_id', 0)
                ->orWhere('lokasi_ruangan', 'Belum berlokasi')
                ->orWhere('peruntukan', 'belum_ditentukan')
            ->groupEnd();
        }

        // Hitung total unit yang memenuhi kriteria sebelum di-update
        $count = $builder->countAllResults(false);

        if ($count <= 0) {
            $msg = ($filterSumber === 'belum')
                ? "Tidak ditemukan data aset yang belum berlokasi pada kriteria yang dipilih."
                : "Tidak ditemukan data aset yang cocok dengan kriteria yang dipilih.";
            return redirect()->to('/admin/inventaris/barang')->with('error', $msg);
        }

        $updateData = [
            'peruntukan' => $peruntukan,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $userId ?: null,
        ];

        if ($peruntukan === 'kantor') {
            if ($targetRoom) {
                $updateData['ruangan_id']     = (int) $targetRoom['id'];
                $updateData['lokasi_ruangan'] = $targetRoom['nama_ruangan'];
            }
        } else {
            $updateData['ruangan_id']     = null;
            $updateData['lokasi_ruangan'] = 'Belum berlokasi';
        }

        $builder->update($updateData);

        $ruanganWording = ($peruntukan === 'kantor' && $targetRoom)
            ? " dan langsung dialokasikan ke ruangan \"{$targetRoom['nama_ruangan']}\""
            : "";

        if ($isAll) {
            $sumberText = ($filterSumber === 'belum') ? 'belum berlokasi' : 'keseluruhan';
            $msgSuccess = "Berhasil memperbarui seluruh ({$count} unit) aset {$sumberText} menjadi peruntukan \"{$labelTarget}\"{$ruanganWording}.";
        } else {
            $namaSample = $namaBarang ?: $kodeBarang;
            $merkWording = ! empty($merkTipe) ? " [{$merkTipe}]" : '';
            $lingkupWording = ($lingkupNup === 'semua')
                ? "seluruh ({$count} unit) aset"
                : "{$count} unit aset (NUP {$nupAwal} s.d {$nupAkhir})";
            $msgSuccess = "Berhasil memperbarui {$lingkupWording} \"{$namaSample}\"{$merkWording} (Kode: {$kodeBarang}) menjadi peruntukan \"{$labelTarget}\"{$ruanganWording}.";
        }

        return redirect()->to('/admin/inventaris/barang')->with('message', $msgSuccess);
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

        $onlyUnallocated = (int) ($this->request->getGet('only_unallocated') ?? 0);
        $db = db_connect();

        // Kasus khusus: Pilihan SEMUA BARANG (ALL)
        if ($kode === 'ALL') {
            $builder = $db->table('trn_inventaris_satker');
            if ($onlyUnallocated === 1) {
                $builder->groupStart()
                    ->where('ruangan_id IS NULL', null, false)
                    ->orWhere('ruangan_id', 0)
                    ->orWhere('lokasi_ruangan', 'Belum berlokasi')
                    ->orWhere('peruntukan', 'belum_ditentukan')
                ->groupEnd();
            }

            $row = $builder->select('
                COUNT(id) AS total_unit,
                MIN(CAST(NULLIF(nup, "") AS UNSIGNED)) AS min_nup,
                MAX(CAST(NULLIF(nup, "") AS UNSIGNED)) AS max_nup,
                SUM(CASE WHEN peruntukan = "kantor" THEN 1 ELSE 0 END) AS count_kantor,
                SUM(CASE WHEN peruntukan = "belum_ditentukan" OR peruntukan IS NULL OR peruntukan = "" THEN 1 ELSE 0 END) AS count_belum,
                SUM(CASE WHEN peruntukan = "mobiler" THEN 1 ELSE 0 END) AS count_mobiler,
                SUM(CASE WHEN peruntukan = "lainnya" THEN 1 ELSE 0 END) AS count_lainnya
            ')->get()->getRowArray();

            return $this->response->setJSON([
                'status'  => 'success',
                'success' => true,
                'data'    => [
                    'kode_barang'   => 'ALL',
                    'nama_barang'   => 'Semua Jenis Barang',
                    'merk_tipe'     => '',
                    'total_unit'    => (int) ($row['total_unit'] ?? 0),
                    'min_nup'       => (int) ($row['min_nup'] ?? 1),
                    'max_nup'       => (int) ($row['max_nup'] ?? 1),
                    'count_kantor'  => (int) ($row['count_kantor'] ?? 0),
                    'count_belum'   => (int) ($row['count_belum'] ?? 0),
                    'count_mobiler' => (int) ($row['count_mobiler'] ?? 0),
                    'count_lainnya' => (int) ($row['count_lainnya'] ?? 0),
                    'is_all'        => true,
                ],
            ]);
        }
        $builder = $db->table('trn_inventaris_satker')
            ->select('kode_barang, nama_barang, merk_tipe, COUNT(id) AS total_unit,
                      MIN(CAST(NULLIF(nup, "") AS UNSIGNED)) AS min_nup,
                      MAX(CAST(NULLIF(nup, "") AS UNSIGNED)) AS max_nup,
                      SUM(CASE WHEN peruntukan = "kantor" THEN 1 ELSE 0 END) AS count_kantor,
                      SUM(CASE WHEN peruntukan = "belum_ditentukan" OR peruntukan IS NULL OR peruntukan = "" THEN 1 ELSE 0 END) AS count_belum,
                      SUM(CASE WHEN peruntukan = "mobiler" THEN 1 ELSE 0 END) AS count_mobiler,
                      SUM(CASE WHEN peruntukan = "lainnya" THEN 1 ELSE 0 END) AS count_lainnya')
            ->where('kode_barang', $kode);

        if ($onlyUnallocated === 1) {
            $builder->groupStart()
                ->where('ruangan_id IS NULL', null, false)
                ->orWhere('ruangan_id', 0)
                ->orWhere('lokasi_ruangan', 'Belum berlokasi')
                ->orWhere('peruntukan', 'belum_ditentukan')
            ->groupEnd();
        }

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
                'count_belum'   => (int) ($row['count_belum'] ?? 0),
                'count_mobiler' => (int) ($row['count_mobiler'] ?? 0),
                'count_lainnya' => (int) ($row['count_lainnya'] ?? 0),
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
            $tempFilePath = $file->getTempName();

            $db = db_connect();
            $userId = (int) (session()->get('userId') ?? 0);
            $now = date('Y-m-d H:i:s');

            // 1. Normalisasi Helper
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

            // 2. Muat Data Yang Sudah Ada di Database untuk Matching Cepat
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

            // 3. Transaksi Database & Batch Upsert
            $db->transBegin();

            $importedCount = 0;
            $updatedCount  = 0;
            $insertBatch   = [];
            $updateBatch   = [];
            $batchSize     = 250;

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

            // Helper untuk memproses 1 baris data aset
            $processRow = function (array $rowVals) use (
                &$importedCount, &$updatedCount, &$insertBatch, &$updateBatch,
                &$usedRegisters, &$existingMap, $batchSize, $db, &$colMap,
                $userId, $now, $normalizeKode, $normalizeNup
            ): void {
                $getVal = static function ($field) use ($rowVals, &$colMap): string {
                    $col = $colMap[$field] ?? '';
                    return ($col !== '' && isset($rowVals[$col])) ? trim((string) $rowVals[$col]) : '';
                };

                $kodeBarang   = $getVal('kode_barang');
                $nup          = $getVal('nup');
                $namaBarang   = clean_inventaris_text($getVal('nama_barang'));
                $kodeRegister = $getVal('kode_register');

                if ($kodeBarang === '' && $namaBarang === '') {
                    return;
                }
                if ($kodeBarang === '') {
                    return;
                }

                $kategori     = $getVal('kategori') ?: 'Peralatan Kantor';
                $statusBmn    = $getVal('status_bmn') ?: 'Aktif';
                $merk         = clean_inventaris_text($getVal('merk'));
                $tipe         = clean_inventaris_text($getVal('tipe'));
                $merkTipe     = clean_inventaris_text($getVal('merk_tipe'));
                $rawKondisi   = $getVal('kondisi');
                $noPsp        = $getVal('no_psp');
                $lokasiRuang  = $getVal('lokasi_ruangan');
                $peruntukanIn = strtolower($getVal('peruntukan'));
                $satuanIn     = $getVal('satuan');
                $jumlahIn     = (int) $getVal('jumlah');
                $keteranganIn = $getVal('keterangan');

                // Parsing nilai finansial
                $colNilaiPerolehan = $colMap['nilai_perolehan'] ?? '';
                $rawNilaiPerolehan = ($colNilaiPerolehan !== '' && isset($rowVals[$colNilaiPerolehan])) ? $rowVals[$colNilaiPerolehan] : 0;
                $nilaiPerolehan = is_numeric($rawNilaiPerolehan) ? (float) $rawNilaiPerolehan : (float) preg_replace('/[^\d.]/', '', (string) $rawNilaiPerolehan);

                $colNilaiBuku = $colMap['nilai_buku'] ?? '';
                $rawNilaiBuku = ($colNilaiBuku !== '' && isset($rowVals[$colNilaiBuku])) ? $rowVals[$colNilaiBuku] : 0;
                $nilaiBuku = is_numeric($rawNilaiBuku) ? (float) $rawNilaiBuku : (float) preg_replace('/[^\d.]/', '', (string) $rawNilaiBuku);

                // Format merk_tipe jika tidak tersedia kolom gabungan
                if ($merkTipe === '') {
                    if ($merk !== '' && $tipe !== '') {
                        if (strcasecmp($merk, $tipe) === 0) {
                            $merkTipe = $merk;
                        } elseif (stripos($tipe, $merk) !== false) {
                            $merkTipe = $tipe;
                        } elseif (stripos($merk, $tipe) !== false) {
                            $merkTipe = $merk;
                        } else {
                            $merkTipe = clean_inventaris_text($merk . ' ' . $tipe);
                        }
                    } elseif ($merk !== '') {
                        $merkTipe = $merk;
                    } else {
                        $merkTipe = $tipe;
                    }
                }
                $merkTipe = clean_inventaris_text($merkTipe);

                // Standarisasi kodifikasi baku BMN (pulihkan nama umum aset resmi jika tersimpan merk duplikat)
                if (isset(self::KODIFIKASI_BMN[$kodeBarang])) {
                    $namaBaku = self::KODIFIKASI_BMN[$kodeBarang];
                    if (strcasecmp($namaBarang, $namaBaku) !== 0) {
                        if ($merkTipe === '' || strcasecmp($merkTipe, $namaBarang) === 0) {
                            $merkTipe = $namaBarang;
                        }
                        $namaBarang = $namaBaku;
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
                $rawTgl = ($colTgl !== '' && isset($rowVals[$colTgl])) ? $rowVals[$colTgl] : null;

                $colTahun = $colMap['tahun_perolehan'] ?? '';
                $rawTahun = ($colTahun !== '' && isset($rowVals[$colTahun])) ? trim((string) $rowVals[$colTahun]) : '';

                $tglPerolehan   = null;
                $tahunPerolehan = is_numeric($rawTahun) ? (int) $rawTahun : null;

                if (is_numeric($rawTgl) && $rawTgl > 10000 && $rawTgl < 100000) {
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
                $mapKey   = $normalizeKode($kodeBarang) . '___' . $cleanNup;

                // ATURAN UPSERT CERDAS:
                // 1) Cocokkan berdasarkan kode_register fisik jika tersedia di DB
                // 2) Atau cocokkan berdasarkan kombinasi kode_barang + NUP
                $existingId = null;
                $existingItem = null;

                if ($kodeRegister !== '' && isset($usedRegisters[$kodeRegister])) {
                    $existingId = $usedRegisters[$kodeRegister];
                    foreach ($existingMap as $mk => $item) {
                        if ($item['id'] === $existingId) {
                            $existingItem = $item;
                            break;
                        }
                    }
                } elseif (isset($existingMap[$mapKey])) {
                    $candidate = $existingMap[$mapKey];
                    if ($kodeRegister !== '' && $candidate['kode_register'] !== '' && $candidate['kode_register'] !== $kodeRegister) {
                        $existingId = null; // Aset berbeda dari sub-satker lain dengan NUP sama
                    } else {
                        $existingId = $candidate['id'];
                        $existingItem = $candidate;
                    }
                }

                if ($existingId !== null && $existingItem !== null) {
                    // DATA COCOK -> UPDATE ASET YANG SUDAH ADA
                    $updateData = [
                        'id'              => $existingId,
                        'nama_barang'     => $namaBarang,
                        'kategori'        => $kategori,
                        'kondisi'         => $kondisi,
                        'nilai_perolehan' => $nilaiPerolehan,
                        'nilai_buku'      => $nilaiBuku,
                        'status_bmn'      => $statusBmn,
                        'updated_at'      => $now,
                        'updated_by'      => $userId ?: null,
                        'merk'            => $merk ?: null,
                        'tipe'            => $tipe ?: null,
                        'merk_tipe'       => $merkTipe ?: null,
                        'satuan'          => $satuanIn ?: 'Buah',
                        'jumlah'          => 1, // Standar BMN: 1 Kode Barang + 1 NUP = 1 Unit Fisik
                        'no_psp'          => $noPsp ?: null,
                        'tahun_perolehan' => $tahunPerolehan,
                        'tgl_perolehan'   => $tglPerolehan,
                        'keterangan'      => $keteranganIn ?: null,
                    ];

                    if (in_array($peruntukanIn, ['kantor', 'mobiler', 'lainnya', 'belum_ditentukan', 'item lainnya', 'item_lainnya'], true)) {
                        $updateData['peruntukan'] = ($peruntukanIn === 'item lainnya' || $peruntukanIn === 'item_lainnya') ? 'lainnya' : $peruntukanIn;
                    }

                    if (empty($existingItem['ruangan_id']) && $lokasiRuang !== '') {
                        $updateData['lokasi_ruangan'] = $lokasiRuang;
                    }

                    if ($kodeRegister !== '') {
                        if (! isset($usedRegisters[$kodeRegister]) || $usedRegisters[$kodeRegister] === $existingId) {
                            $updateData['kode_register'] = $kodeRegister;
                            $usedRegisters[$kodeRegister] = $existingId;
                        }
                    }

                    $updateBatch[] = $updateData;
                    $updatedCount++;

                    if (count($updateBatch) >= $batchSize) {
                        $db->table('trn_inventaris_satker')->updateBatch($updateBatch, 'id');
                        $updateBatch = [];
                    }
                } else {
                    // DATA BARU -> INSERT KE DATABASE
                    $regToInsert = null;
                    if ($kodeRegister !== '' && ! isset($usedRegisters[$kodeRegister])) {
                        $regToInsert = $kodeRegister;
                    }

                    $finalPeruntukan = in_array($peruntukanIn, ['kantor', 'mobiler', 'lainnya', 'belum_ditentukan', 'item lainnya', 'item_lainnya'], true)
                        ? (($peruntukanIn === 'item lainnya' || $peruntukanIn === 'item_lainnya') ? 'lainnya' : $peruntukanIn)
                        : 'belum_ditentukan';

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
                        'peruntukan'      => $finalPeruntukan,
                        'no_psp'          => $noPsp ?: null,
                        'tahun_perolehan' => $tahunPerolehan,
                        'tgl_perolehan'   => $tglPerolehan,
                        'keterangan'      => $keteranganIn ?: null,
                        'created_at'      => $now,
                        'updated_at'      => $now,
                        'created_by'      => $userId ?: null,
                        'updated_by'      => $userId ?: null,
                    ];

                    $insertBatch[] = $insertData;
                    $importedCount++;

                    if ($regToInsert !== null) {
                        $usedRegisters[$regToInsert] = true;
                    }

                    if (count($insertBatch) >= $batchSize) {
                        $db->table('trn_inventaris_satker')->insertBatch($insertBatch);
                        $insertBatch = [];
                    }
                }
            };

            $stream = $this->streamXlsxRows($tempFilePath);
            if ($stream !== null) {
                // Jalur 1: High-Speed Streaming XMLReader (~30MB RAM, < 3 detik untuk puluhan ribu aset)
                $startRow = 3;
                $headerDetected = false;

                foreach ($stream as $r => $rowVals) {
                    if (! $headerDetected && $r <= 10) {
                        $detected = [];
                        foreach ($rowVals as $col => $val) {
                            $vLower = strtolower(trim((string) $val));
                            if (preg_match('/^kode\s*barang$|^kd_?brg$/i', $vLower)) {
                                $detected['kode_barang'] = $col;
                            } elseif (preg_match('/^nup$|^no\.?\s*nup$/i', $vLower)) {
                                $detected['nup'] = $col;
                            } elseif (preg_match('/^kode\s*register$|^no\.?\s*register$/i', $vLower)) {
                                $detected['kode_register'] = $col;
                            } elseif (preg_match('/^nama\s*barang$|^uraian\s*barang$/i', $vLower)) {
                                $detected['nama_barang'] = $col;
                            } elseif (preg_match('/kategori|jenis\s*bmn/i', $vLower)) {
                                $detected['kategori'] = $col;
                            } elseif (preg_match('/merk\s*[\/\-]?\s*tipe/i', $vLower)) {
                                $detected['merk_tipe'] = $col;
                            } elseif (preg_match('/^merk$/i', $vLower)) {
                                $detected['merk'] = $col;
                            } elseif (preg_match('/^tipe$|^type$/i', $vLower)) {
                                $detected['tipe'] = $col;
                            } elseif (preg_match('/peruntukan/i', $vLower)) {
                                $detected['peruntukan'] = $col;
                            } elseif (preg_match('/kondisi/i', $vLower)) {
                                $detected['kondisi'] = $col;
                            } elseif (preg_match('/lokasi\s*ruang|lokasi|ruangan/i', $vLower)) {
                                $detected['lokasi_ruangan'] = $col;
                            } elseif (preg_match('/^(jumlah|kuantum|qty)(\s*barang)?$/i', $vLower) && ! preg_match('/foto|lantai|kamar|orang|pintu/i', $vLower)) {
                                $detected['jumlah'] = $col;
                            } elseif (preg_match('/satuan/i', $vLower)) {
                                $detected['satuan'] = $col;
                            } elseif (preg_match('/nilai\s*perolehan/i', $vLower)) {
                                $detected['nilai_perolehan'] = $col;
                            } elseif (preg_match('/nilai\s*buku/i', $vLower)) {
                                $detected['nilai_buku'] = $col;
                            } elseif (preg_match('/^status\s*bmn$|^status$/i', $vLower) && ! preg_match('/idle|sbsn|kemitraan|penggunaan|pmk/i', $vLower)) {
                                $detected['status_bmn'] = $col;
                            } elseif (preg_match('/no\.?\s*psp/i', $vLower)) {
                                $detected['no_psp'] = $col;
                            } elseif (preg_match('/tanggal\s*perolehan|tgl\s*perolehan/i', $vLower)) {
                                $detected['tgl_perolehan'] = $col;
                            } elseif (preg_match('/tahun\s*perolehan|^tahun$/i', $vLower)) {
                                $detected['tahun_perolehan'] = $col;
                            } elseif (preg_match('/keterangan|catatan/i', $vLower)) {
                                $detected['keterangan'] = $col;
                            }
                        }

                        if (isset($detected['kode_barang']) && (isset($detected['nama_barang']) || isset($detected['nup']))) {
                            $colMap = array_merge($colMap, $detected);
                            $startRow = $r + 1;
                            $headerDetected = true;
                            continue;
                        }
                    }

                    if ($r < $startRow) {
                        continue;
                    }

                    $processRow($rowVals);
                }
            } else {
                // Jalur 2: Fallback PhpSpreadsheet (untuk file .xls legacy binary)
                $reader = IOFactory::createReaderForFile($tempFilePath);
                if (method_exists($reader, 'setParseHuge')) {
                    $reader->setParseHuge(true);
                }
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($tempFilePath);
                $sheet = $spreadsheet->getSheetByName('Master Aset') ?: $spreadsheet->getActiveSheet();
                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestColumn();
                $highestColIdx = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);

                $startRow = 3;
                for ($row = 1; $row <= min(10, $highestRow); $row++) {
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
                        if (preg_match('/^kode\s*barang$|^kd_?brg$/i', $val)) {
                            $detected['kode_barang'] = $letter;
                        } elseif (preg_match('/^nup$|^no\.?\s*nup$/i', $val)) {
                            $detected['nup'] = $letter;
                        } elseif (preg_match('/^kode\s*register$|^no\.?\s*register$/i', $val)) {
                            $detected['kode_register'] = $letter;
                        } elseif (preg_match('/^nama\s*barang$|^uraian\s*barang$/i', $val)) {
                            $detected['nama_barang'] = $letter;
                        }
                    }

                    if (isset($detected['kode_barang']) && (isset($detected['nama_barang']) || isset($detected['nup']))) {
                        $colMap = array_merge($colMap, $detected);
                        $startRow = $row + 1;
                        break;
                    }
                }

                $neededCols = array_values(array_unique(array_filter($colMap)));
                for ($r = $startRow; $r <= $highestRow; $r++) {
                    $rowVals = [];
                    foreach ($neededCols as $colLetter) {
                        $rowVals[$colLetter] = $sheet->getCell($colLetter . $r)->getValue();
                    }
                    $processRow($rowVals);
                }
                unset($spreadsheet, $sheet, $reader);
            }

            // Flush sisa batch
            if (! empty($insertBatch)) {
                $db->table('trn_inventaris_satker')->insertBatch($insertBatch);
                $insertBatch = [];
            }
            if (! empty($updateBatch)) {
                $db->table('trn_inventaris_satker')->updateBatch($updateBatch, 'id');
                $updateBatch = [];
            }

            $totalProses = $importedCount + $updatedCount;
            if ($totalProses < 1) {
                $db->transRollback();
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'File Excel tidak memiliki data aset yang cukup.'])->setStatusCode(400);
                }
                return redirect()->to('/admin/inventaris/barang')->with('error', 'File Excel tidak memiliki data aset yang cukup.');
            }

            $db->transCommit();

            unset($existingRows, $existingMap, $usedRegisters);
            gc_collect_cycles();

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
            if (isset($db) && $db instanceof \CodeIgniter\Database\BaseConnection) {
                try {
                    $db->transRollback();
                } catch (\Throwable $te) {
                    // ignore
                }
            }

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

        if (in_array($filterPeruntukan, ['kantor', 'mobiler', 'lainnya', 'belum_ditentukan'], true)) {
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
            'L6' => 'TAHUN',
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

            $peruntukanLabel = match ($item['peruntukan'] ?? 'belum_ditentukan') {
                'kantor'           => 'Kantor (Satker)',
                'mobiler'          => 'Mobiler (Sekolah)',
                'lainnya'          => 'Item Lainnya',
                default            => 'Belum Ditentukan',
            };

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

    /**
     * Cetak Sticker Barcode / QR BMN (Ukuran presisi 10cm x 4cm: atas 1.5cm, bawah 2.5cm, QR 2cm x 2cm)
     * Format sesuai stiker resmi Kementerian Pekerjaan Umum.
     */
    public function cetakSticker()
    {
        $forbidden = $this->checkAccess();
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $perms = $this->resolvePermissions();
        if (! $perms['export']) {
            return redirect()->to(site_url(self::MENU_LINK))->with('error', 'Anda tidak memiliki hak akses untuk mencetak sticker.');
        }

        $db = db_connect();
        $builder = $db->table('trn_inventaris_satker');

        // Parameter pilihan orientasi (portrait atau landscape)
        $orientation = strtolower(trim((string) ($this->request->getPost('orientation') ?? $this->request->getGet('orientation') ?? 'portrait')));
        if ($orientation !== 'landscape') {
            $orientation = 'portrait';
        }

        // Parameter pilihan ukuran kertas (A4 atau A3)
        $paper = strtoupper(trim((string) ($this->request->getPost('paper') ?? $this->request->getGet('paper') ?? 'A4')));
        if (! in_array($paper, ['A4', 'A3'], true)) {
            $paper = 'A4';
        }

        // Parameter input fleksibel
        $singleId  = $this->request->getGet('id') ?? $this->request->getPost('id');
        $rawIds    = $this->request->getPost('ids') ?? $this->request->getGet('ids');
        $kodeBarangParam = trim((string) ($this->request->getPost('kode_barang') ?? $this->request->getGet('kode_barang')));
        $nupStart  = $this->request->getPost('nup_start') ?? $this->request->getGet('nup_start');
        $nupEnd    = $this->request->getPost('nup_end') ?? $this->request->getGet('nup_end');

        // Parameter filter aktif
        $peruntukan = trim((string) ($this->request->getPost('peruntukan') ?? $this->request->getGet('peruntukan')));
        $kategori   = trim((string) ($this->request->getPost('kategori') ?? $this->request->getGet('kategori')));
        $kondisi    = trim((string) ($this->request->getPost('kondisi') ?? $this->request->getGet('kondisi')));
        $lokasi     = trim((string) ($this->request->getPost('lokasi') ?? $this->request->getGet('lokasi')));
        $search     = trim((string) ($this->request->getPost('search') ?? $this->request->getGet('search')));

        if (! empty($singleId)) {
            $builder->where('id', (int) $singleId);
        } elseif (! empty($rawIds)) {
            $ids = is_array($rawIds) ? $rawIds : explode(',', (string) $rawIds);
            $ids = array_filter(array_map('intval', $ids));
            if (! empty($ids)) {
                $builder->whereIn('id', $ids);
            }
        } else {
            if ($kodeBarangParam !== '') {
                $builder->where('kode_barang', $kodeBarangParam);
            }
            if ($nupStart !== null && $nupStart !== '') {
                $builder->where('CAST(nup AS UNSIGNED) >=', (int) $nupStart);
            }
            if ($nupEnd !== null && $nupEnd !== '') {
                $builder->where('CAST(nup AS UNSIGNED) <=', (int) $nupEnd);
            }
            if ($peruntukan !== '') {
                $builder->where('peruntukan', $peruntukan);
            }
            if ($kategori !== '') {
                $builder->where('kategori', $kategori);
            }
            if ($kondisi !== '') {
                $builder->where('kondisi', $kondisi);
            }
            if ($lokasi !== '') {
                $builder->where('lokasi_ruangan', $lokasi);
            }
            if ($search !== '') {
                $builder->groupStart()
                    ->like('kode_barang', $search)
                    ->orLike('nama_barang', $search)
                    ->orLike('nup', $search)
                    ->orLike('merk_tipe', $search)
                    ->orLike('kode_register', $search)
                    ->groupEnd();
            }
        }

        $items = $builder
            ->orderBy('kode_barang', 'ASC')
            ->orderBy('CAST(nup AS UNSIGNED)', 'ASC')
            ->get()
            ->getResultArray();

        if (empty($items)) {
            return redirect()->to(site_url(self::MENU_LINK))->with('error', 'Tidak ada data aset yang ditemukan untuk dicetak stikernya.');
        }

        // Pastikan namespace chillerlan terdaftar di autoloader (baik via Composer maupun ThirdParty)
        \CodeIgniter\Config\Services::autoloader()->addNamespace([
            'chillerlan\QRCode'   => APPPATH . 'ThirdParty/chillerlan/php-qrcode/src',
            'chillerlan\Settings' => APPPATH . 'ThirdParty/chillerlan/php-settings-container/src',
        ]);

        // Inisialisasi konfigurasi generator QR Code (GD PNG atau SVG fallback)
        $qrOptions = null;
        if (class_exists(QROptions::class) && class_exists(QRCode::class)) {
            try {
                $outputInterface = extension_loaded('gd')
                    ? \chillerlan\QRCode\Output\QRGdImagePNG::class
                    : \chillerlan\QRCode\Output\QRMarkupSVG::class;

                $qrOptions = new QROptions([
                    'outputInterface' => $outputInterface,
                    'eccLevel'        => \chillerlan\QRCode\Common\EccLevel::L,
                    'scale'           => 6,
                    'margin'          => 0,
                ]);
            } catch (\Throwable $e) {
                log_message('error', 'Gagal inisialisasi QR Code: ' . $e->getMessage());
            }
        }

        // Helper untuk membersihkan duplikasi teks (e.g. "X X" menjadi "X")
        $deduplicate = static function (string $str): string {
            return clean_inventaris_text($str);
        };

        $stickerData = [];
        foreach ($items as $item) {
            $kb = trim((string) ($item['kode_barang'] ?? ''));
            $reg = trim((string) ($item['kode_register'] ?? ''));
            if ($reg !== '') {
                // Sederhana & tajam: #{Kode Register} agar modul QR besar dan mudah dideteksi kamera
                $qrContent = '#' . $reg;
            } else {
                // Fallback jika belum memiliki kode register: #{Kode Barang}.{NUP}
                $qrContent = '#' . $kb . '.' . $item['nup'];
            }

            $qrBase64 = '';
            if ($qrOptions !== null) {
                try {
                    // PENTING: Instansiasi baru per item agar data segments tidak terakumulasi antar item
                    $qrBase64 = (new QRCode($qrOptions))->render($qrContent);
                } catch (\Throwable $e) {
                    $qrBase64 = '';
                }
            }

            // 1. Resolusi Nama Baku Barang BMN (Baris Tengah)
            // Prioritaskan tabel kodifikasi baku BMN jika terdaftar, atau bersihkan teks dari database
            $rawNama = trim((string) ($item['nama_barang'] ?? ''));
            $rawNamaDedup = $deduplicate($rawNama);
            if (isset(self::KODIFIKASI_BMN[$kb])) {
                $namaBaku = self::KODIFIKASI_BMN[$kb];
            } elseif ($rawNamaDedup !== '') {
                $namaBaku = $rawNamaDedup;
            } else {
                $namaBaku = '-';
            }

            // 2. Resolusi Deskripsi Spesifik / Merk / Tipe Fisik (Baris Bawah)
            $merkTipeDisplay = trim((string) ($item['merk_tipe'] ?? ''));
            if ($merkTipeDisplay === '') {
                $merkTipeDisplay = trim((string) ($item['merk'] ?? ''));
            }
            if ($merkTipeDisplay === '') {
                $merkTipeDisplay = trim((string) ($item['tipe'] ?? ''));
            }
            if ($merkTipeDisplay === '' && $rawNamaDedup !== '' && $rawNamaDedup !== $namaBaku) {
                $merkTipeDisplay = $rawNamaDedup;
            }
            $merkTipeDisplay = $deduplicate($merkTipeDisplay);

            $stickerData[] = [
                'id'              => $item['id'],
                'kode_barang'     => $kb,
                'nup'             => $item['nup'],
                'kode_register'   => $reg,
                'nama_barang'     => $namaBaku,
                'merk_tipe'       => $merkTipeDisplay ?: '-',
                'tahun_perolehan' => ! empty($item['tahun_perolehan']) ? $item['tahun_perolehan'] : date('Y'),
                'qr_base64'       => $qrBase64,
            ];
        }

        // Logo PU Base64
        $logoPath = FCPATH . 'uploads/branding/1774740768_77e8482499660c14c637.png';
        if (! file_exists($logoPath)) {
            $logoPath = FCPATH . 'uploads/branding/1774773296_62f922407f66fb004e77.jpg';
        }
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : '';

        $data = [
            'stickers'    => $stickerData,
            'logoBase64'  => $logoBase64,
            'kodeUakpb'   => '145060900691285000KP',
            'orientation' => $orientation,
            'paper'       => $paper,
        ];

        $html = view('admin/inventaris/satker/pdf_sticker', $data);

        $dompdfOptions = new Options();
        $dompdfOptions->setIsRemoteEnabled(true);
        $dompdfOptions->setIsHtml5ParserEnabled(true);

        $dompdf = new Dompdf($dompdfOptions);
        $dompdf->loadHtml($html);
        $dompdf->setPaper($paper, $orientation);
        $dompdf->render();

        $filename = 'Sticker_BMN_' . date('Ymd_His') . '.pdf';
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    /**
     * High-speed streaming generator for XLSX files using XMLReader & ZipArchive.
     * Memory consumption is strictly ~30-40MB even for files with >15,000 assets and >35MB XML.
     * Yields [$rowNumber => $rowArrayByColumnLetter].
     * Returns null if file cannot be opened as an XLSX zip archive.
     */
    private function streamXlsxRows(string $tempFilePath): ?\Generator
    {
        $zip = new \ZipArchive();
        if ($zip->open($tempFilePath) !== true) {
            return null;
        }

        // 1. Resolve sheet file path (prefer 'Master Aset')
        $sheetFile = 'xl/worksheets/sheet1.xml';
        $wbXml = $zip->getFromName('xl/workbook.xml');
        $wbRels = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if ($wbXml && $wbRels) {
            $sxWb = @simplexml_load_string($wbXml);
            $sxRels = @simplexml_load_string($wbRels);
            if ($sxWb && $sxRels) {
                $rId = '';
                foreach ($sxWb->sheets->sheet as $s) {
                    if (strcasecmp((string) $s['name'], 'Master Aset') === 0) {
                        $rId = (string) $s->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
                        break;
                    }
                }
                if (! $rId && isset($sxWb->sheets->sheet[0])) {
                    $rId = (string) $sxWb->sheets->sheet[0]->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
                }
                if ($rId) {
                    foreach ($sxRels->Relationship as $rel) {
                        if ((string) $rel['Id'] === $rId) {
                            $target = (string) $rel['Target'];
                            $sheetFile = str_starts_with($target, 'xl/') ? $target : ('xl/' . ltrim($target, '/'));
                            break;
                        }
                    }
                }
            }
        }

        // 2. Read shared strings if available
        $sharedStrings = [];
        $ssStream = $zip->getStream('xl/sharedStrings.xml');
        if ($ssStream) {
            $tmpSS = tempnam(sys_get_temp_dir(), 'ps_ss_');
            file_put_contents($tmpSS, stream_get_contents($ssStream));
            fclose($ssStream);

            $xmlSS = new \XMLReader();
            $xmlSS->open($tmpSS, null, LIBXML_PARSEHUGE);
            $currStr = '';
            while ($xmlSS->read()) {
                if ($xmlSS->nodeType == \XMLReader::ELEMENT && $xmlSS->name == 'si') {
                    $currStr = '';
                } elseif ($xmlSS->nodeType == \XMLReader::ELEMENT && $xmlSS->name == 't') {
                    $currStr .= $xmlSS->readString();
                } elseif ($xmlSS->nodeType == \XMLReader::END_ELEMENT && $xmlSS->name == 'si') {
                    $sharedStrings[] = $currStr;
                }
            }
            $xmlSS->close();
            @unlink($tmpSS);
        }

        // 3. Stream worksheet XML
        $sheetStream = $zip->getStream($sheetFile);
        if (! $sheetStream) {
            $zip->close();
            return null;
        }

        $tmpSheet = tempnam(sys_get_temp_dir(), 'ps_sh_');
        file_put_contents($tmpSheet, stream_get_contents($sheetStream));
        fclose($sheetStream);
        $zip->close();

        $xml = new \XMLReader();
        $xml->open($tmpSheet, null, LIBXML_PARSEHUGE);

        try {
            while ($xml->read()) {
                if ($xml->nodeType == \XMLReader::ELEMENT && $xml->name == 'row') {
                    $rowNum = (int) $xml->getAttribute('r');
                    $rowXml = new \SimpleXMLElement($xml->readOuterXml());
                    $rowVals = [];
                    foreach ($rowXml->c as $c) {
                        $ref = (string) $c['r'];
                        $col = preg_replace('/\d+/', '', $ref);
                        $t = (string) $c['t'];
                        $val = '';
                        if ($t === 's') {
                            $idx = (int) $c->v;
                            $val = $sharedStrings[$idx] ?? '';
                        } elseif ($t === 'inlineStr' && isset($c->is->t)) {
                            $val = (string) $c->is->t;
                        } elseif (isset($c->v)) {
                            $val = (string) $c->v;
                        }
                        $rowVals[$col] = $val;
                    }
                    yield $rowNum => $rowVals;
                }
            }
        } finally {
            $xml->close();
            @unlink($tmpSheet);
        }
    }
}
