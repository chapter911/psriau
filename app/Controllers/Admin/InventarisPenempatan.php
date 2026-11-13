<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;

class InventarisPenempatan extends BaseController
{
    private const MENU_LINK = 'admin/inventaris/penempatan';

    public function index()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $db = db_connect();
        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        $canAdd    = (bool) ($menuPermissions['add'] ?? false);
        $canEdit   = (bool) ($menuPermissions['edit'] ?? false);
        $canDelete = (bool) ($menuPermissions['delete'] ?? false);
        $canExport = (bool) ($menuPermissions['export'] ?? false);

        $isDataTableAjax = $this->request->getGet('draw') !== null || ($this->request->isAJAX() && $this->request->getGet('length') !== null);

        if ($isDataTableAjax) {
            $draw   = max(0, (int) ($this->request->getGet('draw') ?? 1));
            $start  = max(0, (int) ($this->request->getGet('start') ?? 0));
            $length = (int) ($this->request->getGet('length') ?? 10);
            if ($length < 1 || $length > 250) {
                $length = 10;
            }

            $searchParam = $this->request->getGet('search');
            $globalSearch = trim((string) (is_array($searchParam) ? ($searchParam['value'] ?? '') : $searchParam));

            $filterPaketId   = trim((string) $this->request->getGet('paket_id'));
            $filterSekolahId = trim((string) $this->request->getGet('sekolah_id'));
            $filterStatus    = trim((string) $this->request->getGet('status'));
            $keyword         = trim((string) $this->request->getGet('keyword'));

            $searchVal = $globalSearch !== '' ? $globalSearch : $keyword;

            $recordsTotal = (int) $db->table('trn_inventaris_satker')->countAllResults();

            $builder = $db->table('trn_inventaris_satker i')
                ->select('i.id, i.kode_register, i.kode_barang, i.nup, i.nama_barang, i.merk_tipe, i.tahun_perolehan, i.kondisi,
                          COALESCE(i.status_penempatan, "belum_ditempatkan") AS status_penempatan,
                          i.sekolah_id, s.nama AS nama_sekolah, s.paket_id, mp.nama_paket,
                          i.gedung_id, g.nama_gedung,
                          i.ruangan_id, r.nama_ruangan,
                          i.tanggal_penempatan, i.petugas_penempatan')
                ->join('mst_sekolah s', 's.npsn = i.sekolah_id', 'left')
                ->join('mst_paket mp', 'mp.id = s.paket_id', 'left')
                ->join('mst_gedung g', 'g.id = i.gedung_id', 'left')
                ->join('mst_ruangan r', 'r.id = i.ruangan_id', 'left');

            if ($filterPaketId !== '' && $filterPaketId !== '*') {
                $builder->where('s.paket_id', (int) $filterPaketId);
            }
            if ($filterSekolahId !== '' && $filterSekolahId !== '*') {
                $builder->where('i.sekolah_id', (int) $filterSekolahId);
            }
            if ($filterStatus !== '' && $filterStatus !== '*') {
                $builder->where('i.status_penempatan', $filterStatus);
            }
            if ($searchVal !== '') {
                $builder->groupStart()
                    ->like('i.nama_barang', $searchVal)
                    ->orLike('i.kode_barang', $searchVal)
                    ->orLike('i.nup', $searchVal)
                    ->orLike('i.kode_register', $searchVal)
                    ->orLike('i.merk_tipe', $searchVal)
                    ->orLike('s.nama', $searchVal)
                    ->orLike('g.nama_gedung', $searchVal)
                    ->orLike('r.nama_ruangan', $searchVal)
                    ->groupEnd();
            }

            // Hitung recordsFiltered
            $recordsFiltered = (int) $builder->countAllResults(false);

            // Sorting dari DataTables jika ada
            $orderParam = $this->request->getGet('order');
            if (is_array($orderParam) && ! empty($orderParam[0]['column'])) {
                $colIdx = (int) $orderParam[0]['column'];
                $dir    = strtolower((string) ($orderParam[0]['dir'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';
                $colMap = [
                    1 => 'i.kode_barang',
                    2 => 'i.kode_register',
                    3 => 'i.nama_barang',
                    4 => 'i.kondisi',
                    5 => 'i.status_penempatan',
                    6 => 's.nama',
                    7 => 'i.tanggal_penempatan',
                ];
                $sortCol = $colMap[$colIdx] ?? 'i.id';
                $builder->orderBy($sortCol, $dir);
            } else {
                $builder->orderBy('i.id', 'DESC');
            }

            $rows = $builder->limit($length, $start)->get()->getResultArray();

            // Fetch foto untuk baris yang ada di halaman ini saja
            $rowIds = array_filter(array_column($rows, 'id'));
            $fotoMap = [];
            if (! empty($rowIds)) {
                $penempatanRows = $db->table('trn_bmn_penempatan')
                    ->whereIn('inventaris_id', $rowIds)
                    ->where('status', 'aktif')
                    ->orderBy('id', 'DESC')
                    ->get()
                    ->getResultArray();

                foreach ($penempatanRows as $p) {
                    $invId = (int) $p['inventaris_id'];
                    if (! isset($fotoMap[$invId])) {
                        $fotoMap[$invId] = $p;
                    }
                }
            }

            $data = [];
            foreach ($rows as $idx => $it) {
                $invId = (int) $it['id'];
                $isPlaced = ($it['status_penempatan'] === 'sudah_ditempatkan');
                $p = $fotoMap[$invId] ?? null;

                // 0. No
                $noHtml = '<div class="text-center font-weight-bold">' . ($start + $idx + 1) . '</div>';

                // 1. Kode & NUP
                $kodeNupHtml = '<span class="badge badge-light border">Kd: ' . esc($it['kode_barang']) . '</span><br>'
                             . '<span class="badge badge-warning">NUP: ' . esc($it['nup']) . '</span>';

                // 2. Kode Register (QR)
                if (! empty($it['kode_register'])) {
                    $qrHtml = '<code class="text-dark bg-light px-2 py-1 rounded" style="font-size: 0.82rem;">'
                            . '<i class="fas fa-qrcode mr-1 text-primary"></i>' . esc($it['kode_register'])
                            . '</code>';
                } else {
                    $qrHtml = '<span class="text-muted">-</span>';
                }

                // 3. Nama Barang / Merk
                $namaHtml = '<strong>' . esc($it['nama_barang']) . '</strong>';
                if (! empty($it['merk_tipe'])) {
                    $namaHtml .= '<div class="text-muted text-xs">' . esc($it['merk_tipe']) . ' (Thn ' . esc($it['tahun_perolehan'] ?? '-') . ')</div>';
                }

                // 4. Kondisi
                $kondisi = strtolower($it['kondisi'] ?? 'baik');
                $badgeClass = ($kondisi === 'baik') ? 'badge-success' : (($kondisi === 'rusak_ringan') ? 'badge-warning' : 'badge-danger');
                $kondisiHtml = '<span class="badge ' . $badgeClass . '">' . strtoupper(str_replace('_', ' ', $kondisi)) . '</span>';

                // 5. Status
                if ($isPlaced) {
                    $statusHtml = '<span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> Ditempatkan</span>';
                } else {
                    $statusHtml = '<span class="badge badge-secondary">Belum Ditempatkan</span>';
                }

                // 6. Lokasi Fisik
                if ($isPlaced && ! empty($it['nama_sekolah'])) {
                    $lokasiHtml = '<div class="font-weight-bold text-dark"><i class="fas fa-school mr-1 text-info"></i> ' . esc($it['nama_sekolah']) . '</div>'
                                . '<div class="text-muted text-xs">'
                                . '<i class="fas fa-building mr-1"></i> ' . esc($it['nama_gedung'] ?? '-') . ' &bull; '
                                . '<i class="fas fa-door-open mr-1"></i> ' . esc($it['nama_ruangan'] ?? '-')
                                . '</div>';
                } else {
                    $lokasiHtml = '<span class="text-muted font-italic">- Belum Dialokasikan -</span>';
                }

                // 7. Tanggal Penempatan
                if (! empty($it['tanggal_penempatan'])) {
                    $tglHtml = date('d/m/Y H:i', strtotime($it['tanggal_penempatan']))
                             . '<div class="text-muted text-xs">Oleh: ' . esc($it['petugas_penempatan'] ?? 'Mobile') . '</div>';
                } else {
                    $tglHtml = '<span class="text-muted">-</span>';
                }

                // 8. Dokumentasi Foto
                $fotoBtn = '';
                if (! empty($p)) {
                    $asli1 = ! empty($p['foto_asli_1']) ? base_url($p['foto_asli_1']) : '';
                    $asli2 = ! empty($p['foto_asli_2']) ? base_url($p['foto_asli_2']) : '';
                    $asli3 = ! empty($p['foto_asli_3']) ? base_url($p['foto_asli_3']) : '';
                    $asli4 = ! empty($p['foto_asli_4']) ? base_url($p['foto_asli_4']) : '';
                    $wm1   = ! empty($p['foto_wm_1']) ? base_url($p['foto_wm_1']) : '';
                    $wm2   = ! empty($p['foto_wm_2']) ? base_url($p['foto_wm_2']) : '';
                    $wm3   = ! empty($p['foto_wm_3']) ? base_url($p['foto_wm_3']) : '';
                    $wm4   = ! empty($p['foto_wm_4']) ? base_url($p['foto_wm_4']) : '';

                    $fotoBtn = '<button type="button" class="btn btn-xs btn-outline-primary mr-1 btn-view-foto" '
                             . 'data-asli-1="' . esc($asli1, 'attr') . '" '
                             . 'data-asli-2="' . esc($asli2, 'attr') . '" '
                             . 'data-asli-3="' . esc($asli3, 'attr') . '" '
                             . 'data-asli-4="' . esc($asli4, 'attr') . '" '
                             . 'data-wm-1="' . esc($wm1, 'attr') . '" '
                             . 'data-wm-2="' . esc($wm2, 'attr') . '" '
                             . 'data-wm-3="' . esc($wm3, 'attr') . '" '
                             . 'data-wm-4="' . esc($wm4, 'attr') . '" '
                             . 'data-title="' . esc($it['nama_barang'], 'attr') . '" '
                             . 'title="Lihat Foto Dokumentasi">'
                             . '<i class="fas fa-images"></i>'
                             . '</button>';
                }

                // Tombol Pindah Lokasi (Relokasi)
                $pindahBtn = '';
                if ($canEdit) {
                    $lokasiTxt = (! empty($it['nama_sekolah']) ? $it['nama_sekolah'] : 'Belum Ditempatkan')
                               . ' / ' . (! empty($it['nama_gedung']) ? $it['nama_gedung'] : '-')
                               . ' / ' . (! empty($it['nama_ruangan']) ? $it['nama_ruangan'] : '-');

                    $pindahBtn = '<button type="button" class="btn btn-xs btn-outline-info mr-1 btn-pindah-lokasi" '
                               . 'data-id="' . $invId . '" '
                               . 'data-nama="' . esc($it['nama_barang'], 'attr') . '" '
                               . 'data-kode="' . esc($it['kode_barang'], 'attr') . '" '
                               . 'data-nup="' . esc($it['nup'], 'attr') . '" '
                               . 'data-sekolah-id="' . esc($it['sekolah_id'] ?? '', 'attr') . '" '
                               . 'data-gedung-id="' . esc($it['gedung_id'] ?? '', 'attr') . '" '
                               . 'data-ruangan-id="' . esc($it['ruangan_id'] ?? '', 'attr') . '" '
                               . 'data-kondisi="' . esc(strtolower($it['kondisi'] ?? 'baik'), 'attr') . '" '
                               . 'data-lokasi-text="' . esc($lokasiTxt, 'attr') . '" '
                               . 'data-is-placed="' . ($isPlaced ? '1' : '0') . '" '
                               . 'title="Pindahkan Lokasi Penempatan">'
                               . '<i class="fas fa-exchange-alt"></i> Pindah'
                               . '</button>';
                }

                // Tombol Hapus Lokasi Penempatan
                $hapusLokasiBtn = '';
                if ($canDelete && $isPlaced) {
                    $hapusLokasiBtn = '<button type="button" class="btn btn-xs btn-outline-danger btn-hapus-lokasi" '
                                    . 'data-id="' . $invId . '" '
                                    . 'data-nama="' . esc($it['nama_barang'], 'attr') . '" '
                                    . 'data-nup="' . esc($it['nup'], 'attr') . '" '
                                    . 'title="Hapus Lokasi Penempatan (Kembalikan ke status Belum Ditempatkan)">'
                                    . '<i class="fas fa-map-marker-slash"></i> Hapus'
                                    . '</button>';
                }

                $aksiHtml = '<div class="text-nowrap text-center">' . $fotoBtn . $pindahBtn . $hapusLokasiBtn . '</div>';

                $data[] = [
                    'no'           => $noHtml,
                    'kode_nup'     => $kodeNupHtml,
                    'qr_code'      => $qrHtml,
                    'nama_barang'  => $namaHtml,
                    'kondisi'      => $kondisiHtml,
                    'status'       => $statusHtml,
                    'lokasi'       => $lokasiHtml,
                    'tanggal'      => $tglHtml,
                    'aksi'         => $aksiHtml,
                    'aksi_foto'    => $aksiHtml,
                ];
            }

            return $this->response->setJSON([
                'draw'            => $draw,
                'recordsTotal'    => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data'            => $data,
            ]);
        }

        // Request biasa (tampilkan kerangka halaman langsung instan)
        $pakets   = $db->table('mst_paket')->where('is_active', 1)->orderBy('nama_paket', 'ASC')->get()->getResultArray();
        $sekolahs = $db->table('mst_sekolah')->orderBy('nama', 'ASC')->get()->getResultArray();

        $totalAset  = (int) $db->table('trn_inventaris_satker')->countAllResults();
        $totalSudah = (int) $db->table('trn_inventaris_satker')->where('status_penempatan', 'sudah_ditempatkan')->countAllResults();
        $totalBelum = (int) $db->table('trn_inventaris_satker')->where('status_penempatan !=', 'sudah_ditempatkan')->countAllResults();

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);

        return view('admin/inventaris/penempatan/index', [
            'pageTitle'       => 'Penempatan & Distribusi Aset BMN',
            'pakets'          => $pakets,
            'sekolahs'        => $sekolahs,
            'totalAset'       => $totalAset,
            'totalSudah'      => $totalSudah,
            'totalBelum'      => $totalBelum,
            'can_add'         => (bool) ($menuPermissions['add'] ?? false),
            'can_edit'        => (bool) ($menuPermissions['edit'] ?? false),
            'can_delete'      => (bool) ($menuPermissions['delete'] ?? false),
            'can_export'      => (bool) ($menuPermissions['export'] ?? false),
        ]);
    }

    public function createGedung()
    {
        $sekolahId  = (int) $this->request->getPost('sekolah_id');
        $namaGedung = trim((string) $this->request->getPost('nama_gedung'));

        if ($sekolahId > 0 && $namaGedung !== '') {
            $db = db_connect();
            $db->table('mst_gedung')->insert([
                'sekolah_id'  => $sekolahId,
                'nama_gedung' => $namaGedung,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);
            session()->setFlashdata('success', 'Gedung baru berhasil ditambahkan.');
        } else {
            session()->setFlashdata('error', 'Gagal menambahkan gedung. Data tidak lengkap.');
        }

        return redirect()->back();
    }

    public function createRuangan()
    {
        $gedungId    = (int) $this->request->getPost('gedung_id');
        $namaRuangan = trim((string) $this->request->getPost('nama_ruangan'));

        if ($gedungId > 0 && $namaRuangan !== '') {
            $db = db_connect();
            $gedung = $db->table('mst_gedung')->where('id', $gedungId)->get()->getRowArray();
            $sekolahId = $gedung ? (int) $gedung['sekolah_id'] : null;

            $db->table('mst_ruangan')->insert([
                'kode_ruangan' => 'R-' . time(),
                'nama_ruangan' => $namaRuangan,
                'gedung_id'    => $gedungId,
                'sekolah_id'   => $sekolahId,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);
            session()->setFlashdata('success', 'Ruangan baru berhasil ditambahkan.');
        } else {
            session()->setFlashdata('error', 'Gagal menambahkan ruangan. Data tidak lengkap.');
        }

        return redirect()->back();
    }

    public function simpan()
    {
        $menuPermissions = $this->resolveMenuPermissions('admin/inventaris/penempatan');
        if (empty($menuPermissions['edit']) && empty($menuPermissions['add'])) {
            session()->setFlashdata('error', 'Anda tidak memiliki hak akses untuk mengubah penempatan.');
            return redirect()->back();
        }

        $idInventaris = (int) $this->request->getPost('id_inventaris');
        $idSekolah    = (int) $this->request->getPost('id_sekolah');
        $idGedung     = (int) $this->request->getPost('id_gedung') ?: null;
        $idRuangan    = (int) $this->request->getPost('id_ruangan') ?: null;
        $kondisi      = trim((string) $this->request->getPost('kondisi')) ?: 'Baik';
        $petugasNama  = trim((string) $this->request->getPost('petugas_nama')) ?: (session()->get('nama') ?? 'Admin');
        $keterangan   = trim((string) $this->request->getPost('keterangan'));

        if ($idInventaris <= 0 || $idSekolah <= 0) {
            session()->setFlashdata('error', 'Aset BMN dan Sekolah Tujuan wajib dipilih.');
            return redirect()->back();
        }

        $db = db_connect();
        $aset = $db->table('trn_inventaris_satker')->where('id', $idInventaris)->get()->getRowArray();
        if (! $aset) {
            session()->setFlashdata('error', 'Data Aset BMN tidak ditemukan.');
            return redirect()->back();
        }

        // Tandai penempatan sebelumnya sebagai relokasi
        $db->table('trn_bmn_penempatan')
            ->where('inventaris_id', $idInventaris)
            ->where('status', 'aktif')
            ->update([
                'status'     => 'relokasi',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        // Simpan foto jika diunggah via form web
        $fotoPaths = [];
        $files = $this->request->getFiles();
        if (isset($files['foto_inventaris']) && is_array($files['foto_inventaris'])) {
            $uploadDir = FCPATH . 'uploads/bmn/asli/';
            if (! is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            $i = 0;
            foreach ($files['foto_inventaris'] as $file) {
                if ($file->isValid() && ! $file->hasMoved() && $i < 4) {
                    $newName = $file->getRandomName();
                    $file->move($uploadDir, $newName);
                    $fotoPaths[] = 'uploads/bmn/asli/' . $newName;
                    $i++;
                }
            }
        }

        $dataHistory = [
            'inventaris_id'      => $idInventaris,
            'sekolah_id'         => $idSekolah,
            'gedung_id'          => $idGedung,
            'ruangan_id'         => $idRuangan,
            'kondisi'            => $kondisi,
            'catatan'            => $keterangan,
            'petugas_nama'       => $petugasNama,
            'tanggal_penempatan' => date('Y-m-d H:i:s'),
            'status'             => 'aktif',
            'foto_asli_1'        => $fotoPaths[0] ?? null,
            'foto_asli_2'        => $fotoPaths[1] ?? null,
            'foto_asli_3'        => $fotoPaths[2] ?? null,
            'foto_asli_4'        => $fotoPaths[3] ?? null,
            'created_at'         => date('Y-m-d H:i:s'),
            'updated_at'         => date('Y-m-d H:i:s'),
            'created_by'         => session()->get('id_user') ?? session()->get('user_id'),
        ];
        $db->table('trn_bmn_penempatan')->insert($dataHistory);

        // Update di trn_inventaris_satker
        $db->table('trn_inventaris_satker')->where('id', $idInventaris)->update([
            'sekolah_id'         => $idSekolah,
            'gedung_id'          => $idGedung,
            'ruangan_id'         => $idRuangan,
            'status_penempatan'  => 'ditempatkan',
            'tanggal_penempatan' => date('Y-m-d H:i:s'),
            'petugas_penempatan' => $petugasNama,
            'kondisi'            => $kondisi,
            'updated_at'         => date('Y-m-d H:i:s'),
        ]);

        session()->setFlashdata('success', 'Penempatan aset BMN berhasil diperbarui.');
        return redirect()->to(base_url('admin/inventaris/penempatan'));
    }

    public function detail($id = null)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID aset tidak valid.']);
        }

        $db = db_connect();
        $aset = $db->table('trn_inventaris_satker i')
            ->select('i.id as id_inventaris, i.kode_barang, i.nup, i.kode_register, i.nama_barang, i.merk_tipe, i.kondisi, i.tahun_perolehan, s.nama_sekolah, g.nama_gedung, r.nama_ruangan')
            ->join('mst_sekolah s', 's.id = i.sekolah_id', 'left')
            ->join('mst_gedung g', 'g.id = i.gedung_id', 'left')
            ->join('mst_ruangan r', 'r.id = i.ruangan_id', 'left')
            ->where('i.id', $id)
            ->get()->getRowArray();

        if (! $aset) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data Aset tidak ditemukan.']);
        }

        $histories = $db->table('trn_bmn_penempatan p')
            ->select('p.*, s.nama_sekolah, g.nama_gedung, r.nama_ruangan')
            ->join('mst_sekolah s', 's.id = p.sekolah_id', 'left')
            ->join('mst_gedung g', 'g.id = p.gedung_id', 'left')
            ->join('mst_ruangan r', 'r.id = p.ruangan_id', 'left')
            ->where('p.inventaris_id', $id)
            ->orderBy('p.id', 'DESC')
            ->get()->getResultArray();

        $formattedHistory = [];
        foreach ($histories as $h) {
            $fotos = [];
            for ($k = 1; $k <= 4; $k++) {
                if (! empty($h['foto_wm_' . $k])) {
                    $fotos[] = $h['foto_wm_' . $k];
                } elseif (! empty($h['foto_asli_' . $k])) {
                    $fotos[] = $h['foto_asli_' . $k];
                }
            }
            $formattedHistory[] = [
                'id'           => $h['id'],
                'nama_sekolah' => $h['nama_sekolah'] ?? '-',
                'nama_gedung'  => $h['nama_gedung'] ?? '-',
                'nama_ruangan' => $h['nama_ruangan'] ?? '-',
                'kondisi'      => $h['kondisi'] ?? '-',
                'keterangan'   => $h['catatan'] ?? '',
                'petugas_nama' => $h['petugas_nama'] ?? '-',
                'status'       => $h['status'],
                'created_at'   => $h['created_at'],
                'foto_json'    => json_encode($fotos),
            ];
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'aset'    => $aset,
            'history' => $formattedHistory,
        ]);
    }

    /**
     * AJAX: Ambil daftar gedung berdasarkan sekolah (NPSN / ID).
     */
    public function ajaxGedung($sekolahId = null)
    {
        $db = db_connect();
        $builder = $db->table('mst_gedung');
        if (! empty($sekolahId)) {
            $builder->where('sekolah_id', $sekolahId);
        }
        $gedungList = $builder->orderBy('nama_gedung', 'ASC')->get()->getResultArray();

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $gedungList,
        ]);
    }

    /**
     * AJAX: Ambil daftar ruangan berdasarkan gedung_id.
     */
    public function ajaxRuangan($gedungId = null)
    {
        $db = db_connect();
        $builder = $db->table('mst_ruangan');
        if (! empty($gedungId)) {
            $builder->where('gedung_id', (int) $gedungId);
        }
        $ruanganList = $builder->orderBy('nama_ruangan', 'ASC')->get()->getResultArray();

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $ruanganList,
        ]);
    }

    /**
     * AJAX: Pindah / Relokasi Lokasi Penempatan Aset BMN.
     */
    public function pindahLokasi()
    {
        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        if (empty($menuPermissions['edit'])) {
            return $this->response->setStatusCode(403)->setJSON([
                'status'  => 'error',
                'message' => 'Anda tidak memiliki hak akses (Fitur Edit) untuk memindahkan lokasi aset.',
            ]);
        }

        $idInventaris = (int) $this->request->getPost('inventaris_id');
        $idSekolah    = trim((string) $this->request->getPost('sekolah_id'));
        $gedungParam  = trim((string) $this->request->getPost('gedung_id'));
        $ruanganParam = trim((string) $this->request->getPost('ruangan_id'));
        $namaGedungBaru  = trim((string) $this->request->getPost('nama_gedung_baru'));
        $namaRuanganBaru = trim((string) $this->request->getPost('nama_ruangan_baru'));
        $kondisi      = trim((string) $this->request->getPost('kondisi')) ?: 'baik';
        $catatan      = trim((string) $this->request->getPost('catatan'));
        $petugasNama  = session()->get('nama') ?? (session()->get('username') ?? 'Admin Web');

        if ($idInventaris <= 0 || $idSekolah === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Aset BMN dan Sekolah sasaran wajib dipilih.',
            ]);
        }

        $db = db_connect();
        $aset = $db->table('trn_inventaris_satker')->where('id', $idInventaris)->get()->getRowArray();
        if (! $aset) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Data Aset BMN tidak ditemukan.',
            ]);
        }

        // Resolusi Gedung
        $finalGedungId = null;
        if ($gedungParam === 'baru' && $namaGedungBaru !== '') {
            $nowG = date('Y-m-d H:i:s');
            $db->table('mst_gedung')->insert([
                'sekolah_id'  => $idSekolah,
                'nama_gedung' => $namaGedungBaru,
                'created_at'  => $nowG,
                'updated_at'  => $nowG,
                'created_by'  => session()->get('id_user') ?? session()->get('user_id'),
            ]);
            $finalGedungId = (int) $db->insertID();
        } elseif ($gedungParam !== '' && $gedungParam !== 'luar_gedung' && $gedungParam !== '0') {
            $finalGedungId = (int) $gedungParam;
        }

        // Resolusi Ruangan
        $finalRuanganId = null;
        if ($ruanganParam === 'baru' && $namaRuanganBaru !== '') {
            $nowR = date('Y-m-d H:i:s');
            $db->table('mst_ruangan')->insert([
                'kode_ruangan' => 'R-' . time(),
                'nama_ruangan' => $namaRuanganBaru,
                'gedung_id'    => $finalGedungId,
                'sekolah_id'   => $idSekolah,
                'created_at'   => $nowR,
                'updated_at'   => $nowR,
            ]);
            $finalRuanganId = (int) $db->insertID();
        } elseif ($ruanganParam !== '' && $ruanganParam !== 'tanpa_ruangan' && $ruanganParam !== '0') {
            $finalRuanganId = (int) $ruanganParam;
        }

        // Ambil penempatan aktif terakhir untuk mempertahankan foto lama jika ada
        $lastPenempatan = $db->table('trn_bmn_penempatan')
            ->where('inventaris_id', $idInventaris)
            ->where('status', 'aktif')
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();

        // Tandai penempatan sebelumnya sebagai relokasi
        $db->table('trn_bmn_penempatan')
            ->where('inventaris_id', $idInventaris)
            ->where('status', 'aktif')
            ->update([
                'status'     => 'relokasi',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        // Catat entri riwayat baru di trn_bmn_penempatan
        $now = date('Y-m-d H:i:s');
        $db->table('trn_bmn_penempatan')->insert([
            'inventaris_id'      => $idInventaris,
            'sekolah_id'         => $idSekolah,
            'gedung_id'          => $finalGedungId,
            'ruangan_id'         => $finalRuanganId,
            'kondisi'            => strtolower($kondisi),
            'catatan'            => $catatan ?: 'Pemindahan lokasi melalui Web Admin',
            'petugas_nama'       => $petugasNama,
            'tanggal_penempatan' => $now,
            'status'             => 'aktif',
            'foto_asli_1'        => $lastPenempatan['foto_asli_1'] ?? null,
            'foto_asli_2'        => $lastPenempatan['foto_asli_2'] ?? null,
            'foto_asli_3'        => $lastPenempatan['foto_asli_3'] ?? null,
            'foto_asli_4'        => $lastPenempatan['foto_asli_4'] ?? null,
            'foto_wm_1'          => $lastPenempatan['foto_wm_1'] ?? null,
            'foto_wm_2'          => $lastPenempatan['foto_wm_2'] ?? null,
            'foto_wm_3'          => $lastPenempatan['foto_wm_3'] ?? null,
            'foto_wm_4'          => $lastPenempatan['foto_wm_4'] ?? null,
            'created_at'         => $now,
            'updated_at'         => $now,
            'created_by'         => session()->get('id_user') ?? session()->get('user_id'),
        ]);

        // Perbarui trn_inventaris_satker
        $db->table('trn_inventaris_satker')->where('id', $idInventaris)->update([
            'sekolah_id'         => $idSekolah,
            'gedung_id'          => $finalGedungId,
            'ruangan_id'         => $finalRuanganId,
            'status_penempatan'  => 'sudah_ditempatkan',
            'kondisi'            => strtolower($kondisi),
            'tanggal_penempatan' => $now,
            'petugas_penempatan' => $petugasNama,
            'updated_at'         => $now,
        ]);

        return $this->response->setJSON([
            'status'    => 'success',
            'message'   => 'Lokasi aset berhasil dipindahkan ke lokasi baru.',
            'csrf_hash' => csrf_hash(),
        ]);
    }

    /**
     * AJAX: Hapus Lokasi Penempatan Aset BMN (Reset status ke Belum Ditempatkan).
     */
    public function hapusLokasi($id = null)
    {
        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        if (empty($menuPermissions['delete'])) {
            return $this->response->setStatusCode(403)->setJSON([
                'status'    => 'error',
                'message'   => 'Anda tidak memiliki hak akses (Fitur Delete) untuk menghapus lokasi penempatan.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        $id = (int) $id;
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'    => 'error',
                'message'   => 'ID aset tidak valid.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        $db = db_connect();
        $aset = $db->table('trn_inventaris_satker')->where('id', $id)->get()->getRowArray();
        if (! $aset) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'    => 'error',
                'message'   => 'Data Aset BMN tidak ditemukan.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        $petugas = session()->get('nama') ?? (session()->get('username') ?? 'Admin Web');
        $now = date('Y-m-d H:i:s');

        // Nonaktifkan record penempatan aktif di trn_bmn_penempatan
        $db->table('trn_bmn_penempatan')
            ->where('inventaris_id', $id)
            ->where('status', 'aktif')
            ->update([
                'status'     => 'dihapus',
                'catatan'    => 'Lokasi penempatan dibatalkan/dihapus oleh ' . $petugas . ' pada ' . date('d/m/Y H:i'),
                'updated_at' => $now,
            ]);

        // Reset kolom penempatan di trn_inventaris_satker
        $db->table('trn_inventaris_satker')->where('id', $id)->update([
            'sekolah_id'         => null,
            'gedung_id'          => null,
            'ruangan_id'         => null,
            'status_penempatan'  => 'belum_ditempatkan',
            'tanggal_penempatan' => null,
            'petugas_penempatan' => null,
            'updated_at'         => $now,
        ]);

        return $this->response->setJSON([
            'status'    => 'success',
            'message'   => 'Lokasi penempatan aset berhasil dihapus. Status aset kembali menjadi Belum Ditempatkan.',
            'csrf_hash' => csrf_hash(),
        ]);
    }

    /**
     * AJAX: Hitung ulang ringkasan KPI aset penempatan.
     */
    public function ajaxKpi()
    {
        $db = db_connect();
        $totalAset  = (int) $db->table('trn_inventaris_satker')->countAllResults();
        $totalSudah = (int) $db->table('trn_inventaris_satker')->where('status_penempatan', 'sudah_ditempatkan')->countAllResults();
        $totalBelum = (int) $db->table('trn_inventaris_satker')->where('status_penempatan !=', 'sudah_ditempatkan')->countAllResults();

        return $this->response->setJSON([
            'status'     => 'success',
            'totalAset'  => number_format($totalAset),
            'totalSudah' => number_format($totalSudah),
            'totalBelum' => number_format($totalBelum),
        ]);
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
            return true;
        }

        $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';
        $row = $db->table('menu_akses')
            ->where($roleColumn, $roleId)
            ->where('menu_id', $menuId)
            ->get()
            ->getRowArray();

        return $row !== null;
    }

    private function resolveMenuPermissions(string $menuLink): array
    {
        $db = db_connect();
        if (! $db->tableExists('menu_akses')) {
            return ['add' => true, 'edit' => true, 'delete' => true, 'export' => true];
        }

        $roleId = $this->resolveRoleId((string) session()->get('role'), $db);
        if ($roleId === null) {
            return ['add' => false, 'edit' => false, 'delete' => false, 'export' => false];
        }

        $menuId = $this->resolveMenuIdByLink($menuLink, $db);
        if ($menuId === null) {
            return ['add' => true, 'edit' => true, 'delete' => true, 'export' => true];
        }

        $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';
        $row = $db->table('menu_akses')
            ->where($roleColumn, $roleId)
            ->where('menu_id', $menuId)
            ->get()
            ->getRowArray();

        return [
            'add'    => ! empty($row['FiturAdd']),
            'edit'   => ! empty($row['FiturEdit']),
            'delete' => ! empty($row['FiturDelete']),
            'export' => ! empty($row['FiturExport']),
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

            $builder = $db->table('access_roles')->select('id');
            if ($db->fieldExists('role_key', 'access_roles')) {
                $builder->whereIn('role_key', array_values(array_unique($variants)));
            } elseif ($db->fieldExists('name', 'access_roles')) {
                $builder->whereIn('name', array_values(array_unique($variants)));
            }

            if ($db->fieldExists('is_active', 'access_roles')) {
                $builder->where('is_active', 1);
            }

            $row = $builder->orderBy('id', 'ASC')->get()->getRowArray();
            if (is_array($row) && isset($row['id'])) {
                return (int) $row['id'];
            }
        }

        if ($normalized === 'admin' || $normalized === 'superadmin' || $normalized === 'super administrator' || $normalized === 'administrator') {
            return 1;
        }

        return 2;
    }

    private function resolveMenuIdByLink(string $menuLink, $db): ?string
    {
        $normalized = trim(strtolower($menuLink), '/');

        if ($db->tableExists('menu_lv2')) {
            $row = $db->table('menu_lv2')
                ->select('id')
                ->where('LOWER(TRIM(link))', $normalized)
                ->get()
                ->getRowArray();

            if (is_array($row) && isset($row['id'])) {
                return (string) $row['id'];
            }
        }

        if ($db->tableExists('menu_lv1')) {
            $row = $db->table('menu_lv1')
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
