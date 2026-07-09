<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\MstPaketModel;
use CodeIgniter\HTTP\ResponseInterface;

class RabGedung extends BaseController
{
    /**
     * Get active packages list.
     * URL: GET /api/rab-gedung/paket
     */
    public function paket()
    {
        $paketModel = new MstPaketModel();
        $pakets = $paketModel->select('id, nama_paket, singkatan_paket')
                             ->where('is_active', 1)
                             ->orderBy('nama_paket', 'ASC')
                             ->findAll();

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $pakets
        ]);
    }

    /**
     * Get schools list (optionally filtered by paket_id).
     * URL: GET /api/rab-gedung/sekolah
     */
    public function sekolah()
    {
        $paketId = $this->request->getGet('paket_id');

        $db = db_connect();
        $builder = $db->table('mst_sekolah s')
            ->select('s.npsn, s.nama, s.kabupaten, s.kecamatan')
            ->join('trn_rab_gedung_detail r', 'r.sekolah_npsn = s.npsn', 'inner');

        if ($paketId !== null && $paketId !== '') {
            $builder->where('r.paket_id', $paketId);
        }

        $sekolahs = $builder->groupBy('s.npsn, s.nama, s.kabupaten, s.kecamatan')
                            ->orderBy('s.nama', 'ASC')
                            ->get()
                            ->getResultArray();

        foreach ($sekolahs as &$sekolah) {
            $sekolah['npsn'] = (int) $sekolah['npsn'];
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $sekolahs
        ]);
    }

    /**
     * Get distinct work items (pekerjaan_utama) for a school.
     * URL: GET /api/rab-gedung/pekerjaan
     */
    public function pekerjaan()
    {
        $npsn = $this->request->getGet('sekolah_npsn');
        $paketId = $this->request->getGet('paket_id');

        if ($npsn === null || $npsn === '') {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)->setJSON([
                'status'  => 'error',
                'message' => 'Parameter sekolah_npsn wajib diisi.'
            ]);
        }

        $db = db_connect();
        $builder = $db->table('trn_rab_gedung_detail')
            ->select('pekerjaan_utama')
            ->distinct()
            ->where('sekolah_npsn', $npsn)
            ->where('pekerjaan_utama IS NOT NULL')
            ->where('pekerjaan_utama !=', '');

        if ($paketId !== null && $paketId !== '') {
            $builder->where('paket_id', $paketId);
        }

        $rows = $builder->orderBy('pekerjaan_utama', 'ASC')->get()->getResultArray();
        $data = array_column($rows, 'pekerjaan_utama');

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $data
        ]);
    }

    /**
     * Get distinct categories (kategori_1) for a school & work item.
     * URL: GET /api/rab-gedung/kategori
     */
    public function kategori()
    {
        $npsn = $this->request->getGet('sekolah_npsn');
        $pekerjaanUtama = $this->request->getGet('pekerjaan_utama');
        $paketId = $this->request->getGet('paket_id');

        if ($npsn === null || $npsn === '' || $pekerjaanUtama === null || $pekerjaanUtama === '') {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)->setJSON([
                'status'  => 'error',
                'message' => 'Parameter sekolah_npsn dan pekerjaan_utama wajib diisi.'
            ]);
        }

        $db = db_connect();
        $builder = $db->table('trn_rab_gedung_detail')
            ->select('kategori_1')
            ->distinct()
            ->where('sekolah_npsn', $npsn)
            ->where('pekerjaan_utama', $pekerjaanUtama)
            ->where('kategori_1 IS NOT NULL')
            ->where('kategori_1 !=', '');

        if ($paketId !== null && $paketId !== '') {
            $builder->where('paket_id', $paketId);
        }

        $rows = $builder->orderBy('kategori_1', 'ASC')->get()->getResultArray();
        $data = array_column($rows, 'kategori_1');

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $data
        ]);
    }

    /**
     * Get distinct subcategories (kategori_2) for a school, work item & category.
     * URL: GET /api/rab-gedung/subkategori
     */
    public function subkategori()
    {
        $npsn = $this->request->getGet('sekolah_npsn');
        $pekerjaanUtama = $this->request->getGet('pekerjaan_utama');
        $kategori1 = $this->request->getGet('kategori_1');
        $paketId = $this->request->getGet('paket_id');

        if (
            $npsn === null || $npsn === '' ||
            $pekerjaanUtama === null || $pekerjaanUtama === '' ||
            $kategori1 === null || $kategori1 === ''
        ) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)->setJSON([
                'status'  => 'error',
                'message' => 'Parameter sekolah_npsn, pekerjaan_utama, dan kategori_1 wajib diisi.'
            ]);
        }

        $db = db_connect();
        $builder = $db->table('trn_rab_gedung_detail')
            ->select('kategori_2')
            ->distinct()
            ->where('sekolah_npsn', $npsn)
            ->where('pekerjaan_utama', $pekerjaanUtama)
            ->where('kategori_1', $kategori1)
            ->where('kategori_2 IS NOT NULL')
            ->where('kategori_2 !=', '');

        if ($paketId !== null && $paketId !== '') {
            $builder->where('paket_id', $paketId);
        }

        $rows = $builder->orderBy('kategori_2', 'ASC')->get()->getResultArray();
        $data = array_column($rows, 'kategori_2');

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $data
        ]);
    }

    /**
     * Get work details (uraian) that have MC 0 volume > 0.
     * URL: GET /api/rab-gedung/uraian
     */
    public function uraian()
    {
        $npsn = $this->request->getGet('sekolah_npsn');
        $pekerjaanUtama = $this->request->getGet('pekerjaan_utama');
        $kategori1 = $this->request->getGet('kategori_1');
        $kategori2 = $this->request->getGet('kategori_2');
        $paketId = $this->request->getGet('paket_id');

        if (
            $npsn === null || $npsn === '' ||
            $pekerjaanUtama === null || $pekerjaanUtama === '' ||
            $kategori1 === null || $kategori1 === '' ||
            $kategori2 === null || $kategori2 === ''
        ) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)->setJSON([
                'status'  => 'error',
                'message' => 'Parameter sekolah_npsn, pekerjaan_utama, kategori_1, dan kategori_2 wajib diisi.'
            ]);
        }

        $db = db_connect();
        $builder = $db->table('trn_rab_gedung_detail')
            ->select('id, no_urut, uraian, satuan, kontrak_volume, kontrak_harga_satuan, kontrak_jumlah_harga, mc_nol_volume, mc_nol_jumlah_harga, tambah_volume, tambah_jumlah_harga, kurang_volume, kurang_jumlah_harga, bobot_persen, prestasi_persen')
            ->where('sekolah_npsn', $npsn)
            ->where('pekerjaan_utama', $pekerjaanUtama)
            ->where('kategori_1', $kategori1)
            ->where('kategori_2', $kategori2)
            ->where('mc_nol_volume >', 0);

        if ($paketId !== null && $paketId !== '') {
            $builder->where('paket_id', $paketId);
        }

        $rows = $builder->orderBy('no_urut', 'ASC')->get()->getResultArray();

        foreach ($rows as &$row) {
            $row['id'] = (int) $row['id'];
            $row['kontrak_volume'] = $row['kontrak_volume'] !== null ? (float) $row['kontrak_volume'] : null;
            $row['kontrak_harga_satuan'] = $row['kontrak_harga_satuan'] !== null ? (float) $row['kontrak_harga_satuan'] : null;
            $row['kontrak_jumlah_harga'] = $row['kontrak_jumlah_harga'] !== null ? (float) $row['kontrak_jumlah_harga'] : null;
            $row['mc_nol_volume'] = $row['mc_nol_volume'] !== null ? (float) $row['mc_nol_volume'] : null;
            $row['mc_nol_jumlah_harga'] = $row['mc_nol_jumlah_harga'] !== null ? (float) $row['mc_nol_jumlah_harga'] : null;
            $row['tambah_volume'] = $row['tambah_volume'] !== null ? (float) $row['tambah_volume'] : null;
            $row['tambah_jumlah_harga'] = $row['tambah_jumlah_harga'] !== null ? (float) $row['tambah_jumlah_harga'] : null;
            $row['kurang_volume'] = $row['kurang_volume'] !== null ? (float) $row['kurang_volume'] : null;
            $row['kurang_jumlah_harga'] = $row['kurang_jumlah_harga'] !== null ? (float) $row['kurang_jumlah_harga'] : null;
            $row['bobot_persen'] = $row['bobot_persen'] !== null ? (float) $row['bobot_persen'] : null;
            $row['prestasi_persen'] = $row['prestasi_persen'] !== null ? (float) $row['prestasi_persen'] : null;
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $rows
        ]);
    }

    /**
     * Download all RAB Gedung data for a specific package.
     * URL: GET /api/rab-gedung/all
     */
    public function all()
    {
        $paketId = $this->request->getGet('paket_id');

        if ($paketId === null || $paketId === '') {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)->setJSON([
                'status'  => 'error',
                'message' => 'Parameter paket_id wajib diisi.'
            ]);
        }

        $paketModel = new MstPaketModel();
        $paket = $paketModel->select('id, nama_paket, singkatan_paket')
                            ->where('id', $paketId)
                            ->where('is_active', 1)
                            ->first();

        if (!$paket) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)->setJSON([
                'status'  => 'error',
                'message' => 'Paket tidak ditemukan atau nonaktif.'
            ]);
        }

        $db = db_connect();

        // 1. Get all schools associated with this package
        $sekolahs = $db->table('mst_sekolah s')
            ->select('s.npsn, s.nama, s.kabupaten, s.kecamatan')
            ->join('trn_rab_gedung_detail r', 'r.sekolah_npsn = s.npsn', 'inner')
            ->where('r.paket_id', $paketId)
            ->groupBy('s.npsn, s.nama, s.kabupaten, s.kecamatan')
            ->orderBy('s.nama', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($sekolahs as &$sekolah) {
            $sekolah['npsn'] = (int) $sekolah['npsn'];
        }

        // 2. Get all details associated with this package where mc_nol_volume > 0
        $details = $db->table('trn_rab_gedung_detail')
            ->select('id, sekolah_npsn, pekerjaan_utama, gedung, kategori_1, kategori_2, no_urut, uraian, satuan, kontrak_volume, kontrak_harga_satuan, kontrak_jumlah_harga, mc_nol_volume, mc_nol_jumlah_harga, tambah_volume, tambah_jumlah_harga, kurang_volume, kurang_jumlah_harga, bobot_persen, prestasi_persen')
            ->where('paket_id', $paketId)
            ->where('mc_nol_volume >', 0)
            ->orderBy('sekolah_npsn', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($details as &$row) {
            $row['id'] = (int) $row['id'];
            $row['sekolah_npsn'] = (int) $row['sekolah_npsn'];
            $row['kontrak_volume'] = $row['kontrak_volume'] !== null ? (float) $row['kontrak_volume'] : null;
            $row['kontrak_harga_satuan'] = $row['kontrak_harga_satuan'] !== null ? (float) $row['kontrak_harga_satuan'] : null;
            $row['kontrak_jumlah_harga'] = $row['kontrak_jumlah_harga'] !== null ? (float) $row['kontrak_jumlah_harga'] : null;
            $row['mc_nol_volume'] = $row['mc_nol_volume'] !== null ? (float) $row['mc_nol_volume'] : null;
            $row['mc_nol_jumlah_harga'] = $row['mc_nol_jumlah_harga'] !== null ? (float) $row['mc_nol_jumlah_harga'] : null;
            $row['tambah_volume'] = $row['tambah_volume'] !== null ? (float) $row['tambah_volume'] : null;
            $row['tambah_jumlah_harga'] = $row['tambah_jumlah_harga'] !== null ? (float) $row['tambah_jumlah_harga'] : null;
            $row['kurang_volume'] = $row['kurang_volume'] !== null ? (float) $row['kurang_volume'] : null;
            $row['kurang_jumlah_harga'] = $row['kurang_jumlah_harga'] !== null ? (float) $row['kurang_jumlah_harga'] : null;
            $row['bobot_persen'] = $row['bobot_persen'] !== null ? (float) $row['bobot_persen'] : null;
            $row['prestasi_persen'] = $row['prestasi_persen'] !== null ? (float) $row['prestasi_persen'] : null;
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => [
                'paket'   => [
                    'id'              => (int) $paket['id'],
                    'nama_paket'      => $paket['nama_paket'],
                    'singkatan_paket' => $paket['singkatan_paket']
                ],
                'sekolah' => $sekolahs,
                'detail'  => $details
            ]
        ]);
    }
}

