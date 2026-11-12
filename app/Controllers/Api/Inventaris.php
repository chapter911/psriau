<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\ApiAuth;
use CodeIgniter\HTTP\ResponseInterface;

class Inventaris extends BaseController
{
    /**
     * Daftar paket aktif beserta jumlah sekolah pada masing-masing paket.
     * URL: GET /api/inventaris/paket
     */
    public function paket()
    {
        if (! ApiAuth::hasMenuAccess(['admin/inventaris/sekolah', 'admin/inventaris'], 'view')) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses ke menu Inventarisasi.',
            ]);
        }

        $db = db_connect();
        $pakets = $db->table('mst_paket mp')
            ->select('mp.id, mp.nama_paket, mp.singkatan_paket, COUNT(s.npsn) AS jumlah_sekolah')
            ->join('mst_sekolah s', 's.paket_id = mp.id', 'left')
            ->where('mp.is_active', 1)
            ->groupBy('mp.id, mp.nama_paket, mp.singkatan_paket')
            ->orderBy('mp.nama_paket', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($pakets as &$paket) {
            $paket['id'] = (int) $paket['id'];
            $paket['jumlah_sekolah'] = (int) ($paket['jumlah_sekolah'] ?? 0);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $pakets,
        ]);
    }

    /**
     * Daftar sekolah (opsional filter paket_id, kabupaten, keyword).
     * URL: GET /api/inventaris/sekolah
     */
    public function sekolah()
    {
        if (! ApiAuth::hasMenuAccess(['admin/inventaris/sekolah', 'admin/inventaris'], 'view')) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses ke menu Inventarisasi.',
            ]);
        }

        $paketId = trim((string) $this->request->getGet('paket_id'));
        $kabupaten = trim((string) $this->request->getGet('kabupaten'));
        $keyword = trim((string) $this->request->getGet('keyword'));

        $db = db_connect();
        $builder = $db->table('mst_sekolah s')
            ->select('s.npsn, s.nama, s.jenis, s.nsm, s.kabupaten, s.kecamatan, s.paket_id, mp.nama_paket, mp.singkatan_paket')
            ->join('mst_paket mp', 'mp.id = s.paket_id', 'left');

        if ($paketId !== '' && $paketId !== '*') {
            $builder->where('s.paket_id', (int) $paketId);
        }

        if ($kabupaten !== '' && $kabupaten !== '*') {
            $builder->where('s.kabupaten', $kabupaten);
        }

        if ($keyword !== '') {
            $builder->groupStart()
                ->like('s.nama', $keyword)
                ->orLike('s.npsn', $keyword)
                ->orLike('s.kecamatan', $keyword)
                ->orLike('mp.nama_paket', $keyword)
                ->groupEnd();
        }

        $sekolahs = $builder->orderBy('s.nama', 'ASC')->get()->getResultArray();

        foreach ($sekolahs as &$sekolah) {
            $sekolah['npsn'] = (int) $sekolah['npsn'];
            $sekolah['paket_id'] = $sekolah['paket_id'] !== null ? (int) $sekolah['paket_id'] : null;
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $sekolahs,
        ]);
    }
}

