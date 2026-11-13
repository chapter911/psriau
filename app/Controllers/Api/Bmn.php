<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\ApiAuth;
use CodeIgniter\HTTP\ResponseInterface;

class Bmn extends BaseController
{
    /**
     * Mengambil daftar master aset BMN dari trn_inventaris_satker.
     * URL: GET /api/bmn/aset
     */
    public function aset()
    {
        if (! ApiAuth::hasMenuAccess(['admin/inventaris/penempatan', 'admin/inventaris/barang', 'admin/inventaris'], 'view')) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON([
                'status'  => 'error',
                'message' => 'Anda tidak memiliki akses ke modul BMN.',
            ]);
        }

        $keyword = trim((string) $this->request->getGet('keyword'));
        $status  = trim((string) $this->request->getGet('status')); // belum_ditempatkan, sudah_ditempatkan

        $db = db_connect();
        $builder = $db->table('trn_inventaris_satker i')
            ->select('i.id, i.kode_register, i.kode_barang, i.nup, i.nama_barang, i.merk_tipe, i.tahun_perolehan, i.kondisi, 
                      COALESCE(i.status_penempatan, "belum_ditempatkan") AS status_penempatan,
                      i.sekolah_id, s.nama AS nama_sekolah, s.paket_id, mp.nama_paket,
                      i.gedung_id, g.nama_gedung,
                      i.ruangan_id, r.nama_ruangan,
                      i.tanggal_penempatan, i.petugas_penempatan, i.keterangan AS catatan')
            ->join('mst_sekolah s', 's.npsn = i.sekolah_id', 'left')
            ->join('mst_paket mp', 'mp.id = s.paket_id', 'left')
            ->join('mst_gedung g', 'g.id = i.gedung_id', 'left')
            ->join('mst_ruangan r', 'r.id = i.ruangan_id', 'left');

        if ($status !== '' && $status !== 'semua') {
            $builder->where('i.status_penempatan', $status);
        }

        if ($keyword !== '') {
            $builder->groupStart()
                ->like('i.nama_barang', $keyword)
                ->orLike('i.kode_barang', $keyword)
                ->orLike('i.nup', $keyword)
                ->orLike('i.kode_register', $keyword)
                ->orLike('i.merk_tipe', $keyword)
                ->orLike('s.nama', $keyword)
                ->groupEnd();
        }

        $items = $builder->orderBy('i.id', 'ASC')->get()->getResultArray();

        // Ambil riwayat foto penempatan aktif untuk setiap aset yang sudah ditempatkan
        $inventarisIds = array_filter(array_column($items, 'id'));
        $fotoMap = [];
        if (! empty($inventarisIds)) {
            $penempatanRows = $db->table('trn_bmn_penempatan')
                ->whereIn('inventaris_id', $inventarisIds)
                ->where('status', 'aktif')
                ->orderBy('id', 'DESC')
                ->get()
                ->getResultArray();

            foreach ($penempatanRows as $p) {
                $invId = (int) $p['inventaris_id'];
                if (! isset($fotoMap[$invId])) {
                    $asli = [];
                    $wm = [];
                    for ($i = 1; $i <= 4; $i++) {
                        if (! empty($p['foto_asli_' . $i])) {
                            $asli[] = base_url($p['foto_asli_' . $i]);
                        }
                        if (! empty($p['foto_wm_' . $i])) {
                            $wm[] = base_url($p['foto_wm_' . $i]);
                        }
                    }
                    $fotoMap[$invId] = [
                        'asli' => $asli,
                        'wm'   => $wm,
                    ];
                }
            }
        }

        foreach ($items as &$item) {
            $invId = (int) $item['id'];
            $item['id']         = $invId;
            $item['sekolah_id'] = $item['sekolah_id'] !== null ? (int) $item['sekolah_id'] : null;
            $item['gedung_id']  = $item['gedung_id'] !== null ? (int) $item['gedung_id'] : null;
            $item['ruangan_id'] = $item['ruangan_id'] !== null ? (int) $item['ruangan_id'] : null;
            $item['paket_id']   = $item['paket_id'] !== null ? (int) $item['paket_id'] : null;
            $item['foto_asli_list']      = $fotoMap[$invId]['asli'] ?? [];
            $item['foto_watermark_list'] = $fotoMap[$invId]['wm'] ?? [];
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $items,
        ]);
    }

    /**
     * Mengambil daftar gedung per sekolah.
     * URL: GET /api/bmn/gedung?sekolah_id=123
     */
    public function gedung()
    {
        $sekolahId = (int) $this->request->getGet('sekolah_id');
        if ($sekolahId <= 0) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)->setJSON([
                'status'  => 'error',
                'message' => 'Parameter sekolah_id wajib disertakan.',
            ]);
        }

        $db = db_connect();
        $gedungList = $db->table('mst_gedung')
            ->where('sekolah_id', $sekolahId)
            ->orderBy('nama_gedung', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($gedungList as &$g) {
            $g['id']         = (int) $g['id'];
            $g['sekolah_id'] = (int) $g['sekolah_id'];
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $gedungList,
        ]);
    }

    /**
     * Menambah gedung baru dari mobile / web.
     * URL: POST /api/bmn/gedung
     */
    public function createGedung()
    {
        $sekolahId  = (int) $this->request->getPost('sekolah_id');
        $namaGedung = trim((string) $this->request->getPost('nama_gedung'));

        if ($sekolahId <= 0 || $namaGedung === '') {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)->setJSON([
                'status'  => 'error',
                'message' => 'Sekolah ID dan Nama Gedung tidak boleh kosong.',
            ]);
        }

        $db = db_connect();
        // Cek duplikasi di sekolah yang sama
        $existing = $db->table('mst_gedung')
            ->where('sekolah_id', $sekolahId)
            ->where('LOWER(nama_gedung)', strtolower($namaGedung))
            ->get()
            ->getRowArray();

        if ($existing) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Gedung sudah terdaftar sebelumnya.',
                'data'    => [
                    'id'          => (int) $existing['id'],
                    'sekolah_id'  => (int) $existing['sekolah_id'],
                    'nama_gedung' => $existing['nama_gedung'],
                ],
            ]);
        }

        $now = date('Y-m-d H:i:s');
        $insertData = [
            'sekolah_id'  => $sekolahId,
            'nama_gedung' => $namaGedung,
            'created_at'  => $now,
            'updated_at'  => $now,
        ];

        $db->table('mst_gedung')->insert($insertData);
        $newId = $db->insertID();

        return $this->response->setStatusCode(ResponseInterface::HTTP_CREATED)->setJSON([
            'status'  => 'success',
            'message' => 'Gedung baru berhasil disimpan.',
            'data'    => [
                'id'          => (int) $newId,
                'sekolah_id'  => $sekolahId,
                'nama_gedung' => $namaGedung,
            ],
        ]);
    }

    /**
     * Mengambil daftar ruangan per gedung.
     * URL: GET /api/bmn/ruangan?gedung_id=123
     */
    public function ruangan()
    {
        $gedungId = (int) $this->request->getGet('gedung_id');
        if ($gedungId <= 0) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)->setJSON([
                'status'  => 'error',
                'message' => 'Parameter gedung_id wajib disertakan.',
            ]);
        }

        $db = db_connect();
        $ruanganList = $db->table('mst_ruangan')
            ->where('gedung_id', $gedungId)
            ->orderBy('nama_ruangan', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($ruanganList as &$r) {
            $r['id']        = (int) $r['id'];
            $r['gedung_id'] = (int) $r['gedung_id'];
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $ruanganList,
        ]);
    }

    /**
     * Menambah ruangan baru dari mobile / web.
     * URL: POST /api/bmn/ruangan
     */
    public function createRuangan()
    {
        $gedungId    = (int) $this->request->getPost('gedung_id');
        $namaRuangan = trim((string) $this->request->getPost('nama_ruangan'));

        if ($gedungId <= 0 || $namaRuangan === '') {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)->setJSON([
                'status'  => 'error',
                'message' => 'Gedung ID dan Nama Ruangan tidak boleh kosong.',
            ]);
        }

        $db = db_connect();
        // Cek duplikasi di gedung yang sama
        $existing = $db->table('mst_ruangan')
            ->where('gedung_id', $gedungId)
            ->where('LOWER(nama_ruangan)', strtolower($namaRuangan))
            ->get()
            ->getRowArray();

        if ($existing) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Ruangan sudah terdaftar sebelumnya.',
                'data'    => [
                    'id'           => (int) $existing['id'],
                    'gedung_id'    => (int) $existing['gedung_id'],
                    'nama_ruangan' => $existing['nama_ruangan'],
                ],
            ]);
        }

        // Ambil sekolah_id dari gedung terkait
        $gedung = $db->table('mst_gedung')->where('id', $gedungId)->get()->getRowArray();
        $sekolahId = $gedung ? (int) $gedung['sekolah_id'] : null;

        $now = date('Y-m-d H:i:s');
        $insertData = [
            'kode_ruangan' => 'R-' . time(),
            'nama_ruangan' => $namaRuangan,
            'gedung_id'    => $gedungId,
            'sekolah_id'   => $sekolahId,
            'created_at'   => $now,
            'updated_at'   => $now,
        ];

        $db->table('mst_ruangan')->insert($insertData);
        $newId = $db->insertID();

        return $this->response->setStatusCode(ResponseInterface::HTTP_CREATED)->setJSON([
            'status'  => 'success',
            'message' => 'Ruangan baru berhasil disimpan.',
            'data'    => [
                'id'           => (int) $newId,
                'gedung_id'    => $gedungId,
                'nama_ruangan' => $namaRuangan,
            ],
        ]);
    }

    /**
     * Menerima penempatan / relokasi aset BMN + upload 1-4 foto asli & watermark.
     * URL: POST /api/bmn/penempatan
     */
    public function penempatan()
    {
        $bmnId             = (int) $this->request->getPost('bmn_id');
        $sekolahId         = (int) $this->request->getPost('sekolah_id');
        $gedungId          = (int) $this->request->getPost('gedung_id');
        $ruanganId         = (int) $this->request->getPost('ruangan_id');
        $namaGedungManual  = trim((string) $this->request->getPost('nama_gedung_manual'));
        $namaRuanganManual = trim((string) $this->request->getPost('nama_ruangan_manual'));
        $kondisi           = trim((string) $this->request->getPost('kondisi')) ?: 'baik';
        $catatan           = trim((string) $this->request->getPost('catatan'));
        $tanggalPenempatan = trim((string) $this->request->getPost('tanggal_penempatan')) ?: date('Y-m-d H:i:s');
        $petugas           = trim((string) $this->request->getPost('petugas_penempatan')) ?: 'Petugas Lapangan Mobile';

        if ($bmnId <= 0 || $sekolahId <= 0) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)->setJSON([
                'status'  => 'error',
                'message' => 'Harap lengkapi Aset BMN dan Sekolah sasaran penempatan.',
            ]);
        }

        $db = db_connect();

        // Dukung pembuatan/resolusi gedung jika dimasukkan secara manual
        if ($gedungId <= 0 && $namaGedungManual !== '') {
            $existingGedung = $db->table('mst_gedung')
                ->where('sekolah_id', $sekolahId)
                ->where('LOWER(nama_gedung)', strtolower($namaGedungManual))
                ->get()->getRowArray();
            if ($existingGedung) {
                $gedungId = (int) $existingGedung['id'];
            } else {
                $nowG = date('Y-m-d H:i:s');
                $db->table('mst_gedung')->insert([
                    'sekolah_id'  => $sekolahId,
                    'nama_gedung' => $namaGedungManual,
                    'created_at'  => $nowG,
                    'updated_at'  => $nowG,
                ]);
                $gedungId = (int) $db->insertID();
            }
        }

        // Dukung pembuatan/resolusi ruangan jika dimasukkan secara manual
        if ($ruanganId <= 0 && $namaRuanganManual !== '') {
            $builderRuangan = $db->table('mst_ruangan')
                ->where('LOWER(nama_ruangan)', strtolower($namaRuanganManual));
            if ($gedungId > 0) {
                $builderRuangan->where('gedung_id', $gedungId);
            } else {
                $builderRuangan->where('sekolah_id', $sekolahId);
            }
            $existingRuangan = $builderRuangan->get()->getRowArray();
            if ($existingRuangan) {
                $ruanganId = (int) $existingRuangan['id'];
            } else {
                $nowR = date('Y-m-d H:i:s');
                $db->table('mst_ruangan')->insert([
                    'kode_ruangan' => 'R-' . time(),
                    'nama_ruangan' => $namaRuanganManual,
                    'gedung_id'    => $gedungId > 0 ? $gedungId : null,
                    'sekolah_id'   => $sekolahId,
                    'created_at'   => $nowR,
                    'updated_at'   => $nowR,
                ]);
                $ruanganId = (int) $db->insertID();
            }
        }

        $gedungIdFinal  = $gedungId > 0 ? $gedungId : null;
        $ruanganIdFinal = $ruanganId > 0 ? $ruanganId : null;

        $aset = $db->table('trn_inventaris_satker')->where('id', $bmnId)->get()->getRowArray();
        if (! $aset) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)->setJSON([
                'status'  => 'error',
                'message' => 'Data Aset BMN tidak ditemukan.',
            ]);
        }

        // Tandai penempatan sebelumnya sebagai relokasi
        $db->table('trn_bmn_penempatan')
            ->where('inventaris_id', $bmnId)
            ->where('status', 'aktif')
            ->update(['status' => 'relokasi', 'updated_at' => date('Y-m-d H:i:s')]);

        // Simpan file foto (1 s.d. 4)
        $uploadDir = FCPATH . 'uploads/bmn/';
        if (! is_dir($uploadDir . 'asli/')) {
            mkdir($uploadDir . 'asli/', 0775, true);
        }
        if (! is_dir($uploadDir . 'watermark/')) {
            mkdir($uploadDir . 'watermark/', 0775, true);
        }

        $fotoAsliPaths = [];
        $fotoWmPaths   = [];

        for ($i = 0; $i < 4; $i++) {
            $fileAsli = $this->request->getFile('foto_asli_' . $i);
            if ($fileAsli && $fileAsli->isValid() && ! $fileAsli->hasMoved()) {
                $newNameAsli = 'asli_' . $bmnId . '_' . time() . '_' . $i . '.' . $fileAsli->getExtension();
                $fileAsli->move($uploadDir . 'asli/', $newNameAsli);
                $fotoAsliPaths[$i + 1] = 'uploads/bmn/asli/' . $newNameAsli;
            }

            $fileWm = $this->request->getFile('foto_watermark_' . $i);
            if ($fileWm && $fileWm->isValid() && ! $fileWm->hasMoved()) {
                $newNameWm = 'wm_' . $bmnId . '_' . time() . '_' . $i . '.' . $fileWm->getExtension();
                $fileWm->move($uploadDir . 'watermark/', $newNameWm);
                $fotoWmPaths[$i + 1] = 'uploads/bmn/watermark/' . $newNameWm;
            }
        }

        $now = date('Y-m-d H:i:s');

        // Insert ke trn_bmn_penempatan
        $penempatanData = [
            'inventaris_id'      => $bmnId,
            'sekolah_id'         => $sekolahId,
            'gedung_id'          => $gedungIdFinal,
            'ruangan_id'         => $ruanganIdFinal,
            'kondisi'            => strtolower($kondisi),
            'catatan'            => $catatan,
            'petugas_nama'       => $petugas,
            'tanggal_penempatan' => $tanggalPenempatan,
            'status'             => 'aktif',
            'foto_asli_1'        => $fotoAsliPaths[1] ?? null,
            'foto_asli_2'        => $fotoAsliPaths[2] ?? null,
            'foto_asli_3'        => $fotoAsliPaths[3] ?? null,
            'foto_asli_4'        => $fotoAsliPaths[4] ?? null,
            'foto_wm_1'          => $fotoWmPaths[1] ?? null,
            'foto_wm_2'          => $fotoWmPaths[2] ?? null,
            'foto_wm_3'          => $fotoWmPaths[3] ?? null,
            'foto_wm_4'          => $fotoWmPaths[4] ?? null,
            'created_at'         => $now,
            'updated_at'         => $now,
        ];

        $db->table('trn_bmn_penempatan')->insert($penempatanData);

        // Update trn_inventaris_satker
        $db->table('trn_inventaris_satker')->where('id', $bmnId)->update([
            'sekolah_id'         => $sekolahId,
            'gedung_id'          => $gedungIdFinal,
            'ruangan_id'         => $ruanganIdFinal,
            'kondisi'            => strtolower($kondisi),
            'status_penempatan'  => 'sudah_ditempatkan',
            'tanggal_penempatan' => $tanggalPenempatan,
            'petugas_penempatan' => $petugas,
            'updated_at'         => $now,
        ]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Penempatan Aset BMN berhasil dicatat.',
        ]);
    }
}
