<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ArticleModel;
use App\Models\EventModel;
use App\Models\HomeSlideModel;

class Dashboard extends BaseController
{
    public function index(): string
    {
        $eventModel   = new EventModel();
        $articleModel = new ArticleModel();
        $slideModel   = new HomeSlideModel();
        $db = db_connect();

        $eventCount = $eventModel->countAllResults();
        $eventPublishedCount = (new EventModel())->where('is_published', 1)->countAllResults();
        $eventDraftCount = max(0, $eventCount - $eventPublishedCount);

        $articleCount = $articleModel->countAllResults();
        $articlePublishedCount = (new ArticleModel())->where('is_published', 1)->countAllResults();
        $articleDraftCount = max(0, $articleCount - $articlePublishedCount);

        $slideCount = $slideModel->countAllResults();
        $slideActiveCount = (new HomeSlideModel())->where('is_active', 1)->countAllResults();

        $latestEvents = (new EventModel())
            ->select('title, event_date, is_published')
            ->orderBy('updated_at', 'DESC')
            ->findAll(5);

        $latestInstagramPosts = (new ArticleModel())
            ->select('title, content, published_at, is_published')
            ->orderBy('updated_at', 'DESC')
            ->findAll(5);

        $mapTypes = $this->getMapTypes($db);

        $kabupatenOptions = [];
        $kecamatanOptions = [];
        $klasifikasiOptions = [];

        if ($db->tableExists('mst_sekolah')) {
            $kabupatenRows = $db->table('mst_sekolah')
                ->select('kabupaten')
                ->where('kabupaten IS NOT NULL', null, false)
                ->where('kabupaten !=', '')
                ->groupBy('kabupaten')
                ->orderBy('kabupaten', 'ASC')
                ->get()
                ->getResultArray();

            $kecamatanRows = $db->table('mst_sekolah')
                ->select('kecamatan')
                ->where('kecamatan IS NOT NULL', null, false)
                ->where('kecamatan !=', '')
                ->groupBy('kecamatan')
                ->orderBy('kecamatan', 'ASC')
                ->get()
                ->getResultArray();

            $kabupatenOptions = array_values(array_map(static fn (array $row): string => (string) ($row['kabupaten'] ?? ''), $kabupatenRows));
            $kecamatanOptions = array_values(array_map(static fn (array $row): string => (string) ($row['kecamatan'] ?? ''), $kecamatanRows));
        }

        if ($db->tableExists('trn_survey_sekolah')) {
            $klasifikasiRows = $db->table('trn_survey_sekolah')
                ->select('survey_klasifikasi_kerusakan')
                ->where('survey_klasifikasi_kerusakan IS NOT NULL', null, false)
                ->where('survey_klasifikasi_kerusakan !=', '')
                ->groupBy('survey_klasifikasi_kerusakan')
                ->orderBy('survey_klasifikasi_kerusakan', 'ASC')
                ->get()
                ->getResultArray();

            $klasifikasiOptions = array_values(array_map(static fn (array $row): string => (string) ($row['survey_klasifikasi_kerusakan'] ?? ''), $klasifikasiRows));
        }

        return view('admin/dashboard', [
            'pageTitle' => 'Dashboard Admin',
            'eventCount' => $eventCount,
            'eventPublishedCount' => $eventPublishedCount,
            'eventDraftCount' => $eventDraftCount,
            'articleCount' => $articleCount,
            'articlePublishedCount' => $articlePublishedCount,
            'articleDraftCount' => $articleDraftCount,
            'slideCount' => $slideCount,
            'slideActiveCount' => $slideActiveCount,
            'latestEvents' => $latestEvents,
            'latestInstagramPosts' => $latestInstagramPosts,
            'mapTypes' => $mapTypes,
            'mapDefaultId' => (int) ($mapTypes[0]['id'] ?? 1),
            'kabupatenOptions' => $kabupatenOptions,
            'kecamatanOptions' => $kecamatanOptions,
            'klasifikasiOptions' => $klasifikasiOptions,
        ]);
    }

    public function mapData()
    {
        $db = db_connect();

        if (! $db->tableExists('mst_sekolah')) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Tabel mst_sekolah belum tersedia.',
            ]);
        }

        $mapTypes = $this->getMapTypes($db);
        $selectedMapTypeId = (int) $this->request->getGet('map_type');
        if ($selectedMapTypeId <= 0) {
            $selectedMapTypeId = (int) ($mapTypes[0]['id'] ?? 1);
        }

        $selectedMapType = $mapTypes[0] ?? ['id' => 1, 'map_name' => 'Leaflet Map', 'map_script' => ''];
        foreach ($mapTypes as $mapType) {
            if ((int) ($mapType['id'] ?? 0) === $selectedMapTypeId) {
                $selectedMapType = $mapType;
                break;
            }
        }

        $hasSurveyTable = $db->tableExists('trn_survey_sekolah');
        $klasifikasi = trim((string) $this->request->getGet('klasifikasi'));

        if (! $hasSurveyTable && $klasifikasi !== '' && $klasifikasi !== '*') {
            return $this->response->setJSON([
                'status' => 'ok',
                'total' => 0,
                'markers' => [],
                'map_type' => $selectedMapType,
                'hasSurveyTable' => false,
            ]);
        }

        $builder = $db->table('mst_sekolah')
            ->select('mst_sekolah.npsn, mst_sekolah.nama, mst_sekolah.jenis, mst_sekolah.nsm, mst_sekolah.kabupaten, mst_sekolah.kecamatan, mst_sekolah.latitude, mst_sekolah.longitude');

        if ($hasSurveyTable) {
            $latestSurveySubQuery = "SELECT t1.npsn, t1.periode, t1.survey_klasifikasi_kerusakan, t1.survey_tingat_kerusakan, t1.status_lahan, t1.status_penanganan, t1.ekspos_status\n                FROM trn_survey_sekolah t1\n                INNER JOIN (\n                    SELECT npsn, MAX(periode) AS max_periode\n                    FROM trn_survey_sekolah\n                    GROUP BY npsn\n                ) latest ON latest.npsn = t1.npsn AND latest.max_periode = t1.periode";

            $builder
                ->select('survey_latest.periode, survey_latest.survey_klasifikasi_kerusakan, survey_latest.survey_tingat_kerusakan, survey_latest.status_lahan, survey_latest.status_penanganan, survey_latest.ekspos_status')
                ->join('(' . $latestSurveySubQuery . ') survey_latest', 'survey_latest.npsn = mst_sekolah.npsn', 'left', false);
        }

        $npsn = trim((string) $this->request->getGet('npsn'));
        if ($npsn !== '') {
            $builder->where('mst_sekolah.npsn', $npsn);
        }

        $nama = trim((string) $this->request->getGet('nama'));
        if ($nama !== '') {
            $builder->like('mst_sekolah.nama', $nama);
        }

        $kabupaten = trim((string) $this->request->getGet('kabupaten'));
        if ($kabupaten !== '' && $kabupaten !== '*') {
            $builder->where('mst_sekolah.kabupaten', $kabupaten);
        }

        $kecamatan = trim((string) $this->request->getGet('kecamatan'));
        if ($kecamatan !== '' && $kecamatan !== '*') {
            $builder->where('mst_sekolah.kecamatan', $kecamatan);
        }

        if ($hasSurveyTable && $klasifikasi !== '' && $klasifikasi !== '*') {
            if ($klasifikasi === 'non_klasifikasi') {
                $builder
                    ->groupStart()
                    ->where('survey_latest.survey_klasifikasi_kerusakan IS NULL', null, false)
                    ->orWhere('survey_latest.survey_klasifikasi_kerusakan', '')
                    ->groupEnd();
            } else {
                $builder->where('survey_latest.survey_klasifikasi_kerusakan', $klasifikasi);
            }
        }

        $rows = $builder
            ->orderBy('mst_sekolah.nama', 'ASC')
            ->get()
            ->getResultArray();

        $markers = [];
        foreach ($rows as $row) {
            $lat = is_numeric($row['latitude'] ?? null) ? (float) $row['latitude'] : null;
            $lng = is_numeric($row['longitude'] ?? null) ? (float) $row['longitude'] : null;

            if ($lat === null || $lng === null) {
                continue;
            }

            $markers[] = [
                'npsn' => (string) ($row['npsn'] ?? ''),
                'nama' => (string) ($row['nama'] ?? '-'),
                'jenis' => (string) ($row['jenis'] ?? '-'),
                'nsm' => (string) ($row['nsm'] ?? '-'),
                'kabupaten' => (string) ($row['kabupaten'] ?? '-'),
                'kecamatan' => (string) ($row['kecamatan'] ?? '-'),
                'latitude' => $lat,
                'longitude' => $lng,
                'periode' => (string) ($row['periode'] ?? '-'),
                'survey_klasifikasi_kerusakan' => (string) ($row['survey_klasifikasi_kerusakan'] ?? ''),
                'survey_tingat_kerusakan' => (string) ($row['survey_tingat_kerusakan'] ?? ''),
                'status_lahan' => (string) ($row['status_lahan'] ?? ''),
                'status_penanganan' => (string) ($row['status_penanganan'] ?? ''),
                'ekspos_status' => (string) ($row['ekspos_status'] ?? ''),
            ];
        }

        return $this->response->setJSON([
            'status' => 'ok',
            'total' => count($markers),
            'markers' => $markers,
            'map_type' => $selectedMapType,
            'hasSurveyTable' => $hasSurveyTable,
        ]);
    }

    private function getMapTypes($db): array
    {
        if ($db->tableExists('mst_map_type')) {
            $rows = $db->table('mst_map_type')
                ->select('id, map_name, map_script')
                ->orderBy('id', 'ASC')
                ->get()
                ->getResultArray();

            if ($rows !== []) {
                return array_map(static function (array $row): array {
                    $script = str_replace('http://', 'https://', (string) ($row['map_script'] ?? ''));

                    return [
                        'id' => (int) ($row['id'] ?? 0),
                        'map_name' => (string) ($row['map_name'] ?? 'Leaflet Map'),
                        'map_script' => $script,
                    ];
                }, $rows);
            }
        }

        return [
            [
                'id' => 1,
                'map_name' => 'Leaflet Map',
                'map_script' => "L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(map);",
            ],
        ];
    }
}
