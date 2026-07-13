<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;

class LaporanLapangan extends BaseController
{
    /**
     * Display list of school projects and their overall progress.
     * URL: GET /admin/laporan/lapangan
     */
    public function index()
    {
        $db = db_connect();
        
        $projects = $db->table('trn_rab_gedung_detail r')
            ->select('r.sekolah_npsn, s.nama as nama_sekolah, r.paket_id, pk.nama_paket')
            ->join('mst_sekolah s', 's.npsn = r.sekolah_npsn', 'inner')
            ->join('mst_paket pk', 'pk.id = r.paket_id', 'inner')
            ->groupBy('r.sekolah_npsn, s.nama, r.paket_id, pk.nama_paket')
            ->orderBy('pk.nama_paket', 'ASC')
            ->orderBy('s.nama', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($projects as &$project) {
            // Fetch all RAB items for this school and package
            $items = $db->table('trn_rab_gedung_detail')
                ->select('id, bobot_persen')
                ->where('sekolah_npsn', $project['sekolah_npsn'])
                ->where('paket_id', $project['paket_id'])
                ->get()
                ->getResultArray();

            $totalWeight = 0.0;
            $weightedProgress = 0.0;

            foreach ($items as $item) {
                $weight = (float)($item['bobot_persen'] ?? 0.0);
                $totalWeight += $weight;

                // Get the latest reported progress from laporan_lapangan_pekerjaan
                $latest = $db->table('laporan_lapangan_pekerjaan')
                    ->select('progres_persen')
                    ->where('rab_detail_id', $item['id'])
                    ->orderBy('tanggal', 'DESC')
                    ->orderBy('id', 'DESC')
                    ->limit(1)
                    ->get()
                    ->getRowArray();

                $progress = $latest ? (float)$latest['progres_persen'] : 0.0;
                $weightedProgress += $weight * $progress;
            }

            $project['progress_persen'] = $totalWeight > 0 ? round($weightedProgress / $totalWeight, 2) : 0.0;
            $project['total_pekerjaan'] = count($items);

            // Count distinct jobs that have been reported at least once
            $countRow = $db->table('laporan_lapangan_pekerjaan lp')
                ->select('COUNT(DISTINCT lp.rab_detail_id) as count')
                ->join('trn_rab_gedung_detail r', 'r.id = lp.rab_detail_id', 'inner')
                ->where('r.sekolah_npsn', $project['sekolah_npsn'])
                ->where('r.paket_id', $project['paket_id'])
                ->get()
                ->getRowArray();
            $project['reported_count'] = (int)($countRow['count'] ?? 0);
        }

        return view('admin/laporan/lapangan', [
            'title'    => 'Laporan Lapangan',
            'projects' => $projects,
        ]);
    }

    /**
     * Display details of a specific project (progress charts, work items, and daily logs).
     * URL: GET /admin/laporan/lapangan/detail/(:segment)/(:num)
     */
    public function detail($sekolahNpsn, $paketId)
    {
        $db = db_connect();

        $sekolah = $db->table('mst_sekolah')->where('npsn', $sekolahNpsn)->get()->getRowArray();
        $paket = $db->table('mst_paket')->where('id', $paketId)->get()->getRowArray();

        if (!$sekolah || !$paket) {
            throw PageNotFoundException::forPageNotFound('Proyek sekolah atau paket tidak ditemukan.');
        }

        // Fetch all work items (RAB details)
        $jobs = $db->table('trn_rab_gedung_detail r')
            ->select('r.*')
            ->where('r.sekolah_npsn', $sekolahNpsn)
            ->where('r.paket_id', $paketId)
            ->orderBy('r.no_urut', 'ASC')
            ->get()
            ->getResultArray();

        $totalWeight = 0.0;
        foreach ($jobs as $job) {
            $totalWeight += (float)($job['bobot_persen'] ?? 0.0);
        }

        // Calculate progress timeline chart data
        $dates = $db->table('laporan_lapangan_pekerjaan lp')
            ->select('lp.tanggal')
            ->join('trn_rab_gedung_detail r', 'r.id = lp.rab_detail_id', 'inner')
            ->where('r.sekolah_npsn', $sekolahNpsn)
            ->where('r.paket_id', $paketId)
            ->groupBy('lp.tanggal')
            ->orderBy('lp.tanggal', 'ASC')
            ->get()
            ->getResultArray();

        $chartLabels = [];
        $chartData = [];

        foreach ($dates as $dateRow) {
            $date = $dateRow['tanggal'];
            $weightedProgress = 0.0;

            foreach ($jobs as $job) {
                $weight = (float)($job['bobot_persen'] ?? 0.0);
                
                $report = $db->table('laporan_lapangan_pekerjaan')
                    ->select('progres_persen')
                    ->where('rab_detail_id', $job['id'])
                    ->where('tanggal <=', $date)
                    ->orderBy('tanggal', 'DESC')
                    ->orderBy('id', 'DESC')
                    ->limit(1)
                    ->get()
                    ->getRowArray();

                $progress = $report ? (float)$report['progres_persen'] : 0.0;
                $weightedProgress += $weight * $progress;
            }

            $chartLabels[] = date('d-m-Y', strtotime($date));
            $chartData[] = $totalWeight > 0 ? round($weightedProgress / $totalWeight, 2) : 0.0;
        }

        // Compile latest status and historical daily logs for each work item
        $weightedProgressSum = 0.0;
        foreach ($jobs as &$job) {
            $latest = $db->table('laporan_lapangan_pekerjaan')
                ->select('progres_persen, status_selesai')
                ->where('rab_detail_id', $job['id'])
                ->orderBy('tanggal', 'DESC')
                ->orderBy('id', 'DESC')
                ->limit(1)
                ->get()
                ->getRowArray();

            $job['latest_progress'] = $latest ? (float)$latest['progres_persen'] : 0.0;
            $job['status_selesai'] = $latest ? (int)$latest['status_selesai'] : 0;
            
            $weightedProgressSum += ((float)($job['bobot_persen'] ?? 0.0)) * $job['latest_progress'];

            // Fetch history of reports for this item
            $job['history'] = $db->table('laporan_lapangan_pekerjaan lp')
                ->select('lp.*, p.nama_pelapor, p.jam_mulai, p.jam_selesai')
                ->join('laporan_lapangan_proyek p', 'p.tanggal = lp.tanggal AND p.sekolah_npsn = ' . $db->escape($sekolahNpsn), 'left')
                ->where('lp.rab_detail_id', $job['id'])
                ->orderBy('lp.tanggal', 'DESC')
                ->orderBy('lp.id', 'DESC')
                ->get()
                ->getResultArray();
        }

        $overallProgress = $totalWeight > 0 ? round($weightedProgressSum / $totalWeight, 2) : 0.0;

        return view('admin/laporan/lapangan_detail', [
            'title'           => 'Detail Progress Proyek',
            'sekolah'         => $sekolah,
            'paket'           => $paket,
            'jobs'            => $jobs,
            'overallProgress' => $overallProgress,
            'chartLabels'     => $chartLabels,
            'chartData'       => $chartData,
        ]);
    }

    /**
     * Redirect legacy report ID URL to the new project detail view.
     * URL: GET /admin/laporan/lapangan/(:num)
     */
    public function detailLegacy($id)
    {
        $db = db_connect();
        $report = $db->table('laporan_lapangan_proyek')
            ->where('id', $id)
            ->get()
            ->getRowArray();

        if ($report) {
            return redirect()->to(site_url('admin/laporan/lapangan/detail/' . $report['sekolah_npsn'] . '/' . $report['paket_id']));
        }

        throw PageNotFoundException::forPageNotFound('Laporan lapangan tidak ditemukan.');
    }
}
