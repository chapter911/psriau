<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ArticleModel;
use App\Models\EventModel;
use App\Models\HomeSlideModel;
use App\Models\AuditHistoryModel;
use App\Models\LoginHistoryModel;

class Dashboard extends BaseController
{
    private const RIAU_PROVINCE_CODE = '14';

    public function index(): string
    {
        $db = db_connect();
        $eventModel   = new EventModel();
        $articleModel = new ArticleModel();
        $slideModel   = new HomeSlideModel();

        // Content Statistics
        $eventCount = $eventModel->countAllResults();
        $eventPublishedCount = (new EventModel())->where('is_published', 1)->countAllResults();
        $eventDraftCount = max(0, $eventCount - $eventPublishedCount);

        $articleCount = $articleModel->countAllResults();
        $articlePublishedCount = (new ArticleModel())->where('is_published', 1)->countAllResults();
        $articleDraftCount = max(0, $articleCount - $articlePublishedCount);

        $slideCount = $slideModel->countAllResults();
        $slideActiveCount = (new HomeSlideModel())->where('is_active', 1)->countAllResults();

        // School Statistics
        $schoolCount = 0;
        $schoolWithSurvey = 0;
        $damageClassification = [];
        if ($db->tableExists('mst_sekolah')) {
            $schoolCount = $db->table('mst_sekolah')->countAllResults();
            
            if ($db->tableExists('trn_survey_sekolah')) {
                $schoolWithSurvey = $db->table('mst_sekolah')
                    ->join('trn_survey_sekolah', 'mst_sekolah.npsn = trn_survey_sekolah.npsn', 'inner')
                    ->select('DISTINCT mst_sekolah.npsn')
                    ->countAllResults();

                $damageClassification = $db->table('trn_survey_sekolah')
                    ->select('survey_klasifikasi_kerusakan, COUNT(*) as count')
                    ->where('survey_klasifikasi_kerusakan IS NOT NULL', null, false)
                    ->where('survey_klasifikasi_kerusakan !=', '')
                    ->groupBy('survey_klasifikasi_kerusakan')
                    ->orderBy('count', 'DESC')
                    ->get()
                    ->getResultArray();
            }
        }

        // Report Statistics
        $harianReportCount = 0;
        $mingguanReportCount = 0;
        if ($db->tableExists('trn_laporan_harian')) {
            $harianReportCount = $db->table('trn_laporan_harian')->countAllResults();
        }
        if ($db->tableExists('trn_laporan_mingguan')) {
            $mingguanReportCount = $db->table('trn_laporan_mingguan')->countAllResults();
        }

        // Latest Activities
        $latestEvents = (new EventModel())
            ->select('title, event_date, is_published')
            ->orderBy('updated_at', 'DESC')
            ->findAll(5);

        $latestInstagramPosts = (new ArticleModel())
            ->select('title, content, published_at, is_published')
            ->orderBy('updated_at', 'DESC')
            ->findAll(5);

        $latestAudit = [];
        if ($db->tableExists('audit_histories')) {
            $latestAudit = $db->table('audit_histories')
                ->select('action, description, created_at, created_by')
                ->orderBy('created_at', 'DESC')
                ->limit(8)
                ->get()
                ->getResultArray();
        }

        $latestLogins = [];
        if ($db->tableExists('login_histories')) {
            $latestLogins = $db->table('login_histories')
                ->select('user_id, login_time')
                ->orderBy('login_time', 'DESC')
                ->limit(5)
                ->get()
                ->getResultArray();
        }

        // Wilayah Statistics
        $kabupatenCount = 0;
        $kecamatanCount = 0;
        if ($db->tableExists('mst_kabupaten')) {
            $kabupatenCount = $db->table('mst_kabupaten')->countAllResults();
        }
        if ($db->tableExists('mst_kecamatan')) {
            $kecamatanCount = $db->table('mst_kecamatan')->countAllResults();
        }

        return view('admin/dashboard', [
            'pageTitle' => 'Dashboard Admin',
            // Content
            'eventCount' => $eventCount,
            'eventPublishedCount' => $eventPublishedCount,
            'eventDraftCount' => $eventDraftCount,
            'articleCount' => $articleCount,
            'articlePublishedCount' => $articlePublishedCount,
            'articleDraftCount' => $articleDraftCount,
            'slideCount' => $slideCount,
            'slideActiveCount' => $slideActiveCount,
            // Schools
            'schoolCount' => $schoolCount,
            'schoolWithSurvey' => $schoolWithSurvey,
            'damageClassification' => $damageClassification,
            // Reports
            'harianReportCount' => $harianReportCount,
            'mingguanReportCount' => $mingguanReportCount,
            // Activities
            'latestEvents' => $latestEvents,
            'latestInstagramPosts' => $latestInstagramPosts,
            'latestAudit' => $latestAudit,
            'latestLogins' => $latestLogins,
            // Wilayah
            'kabupatenCount' => $kabupatenCount,
            'kecamatanCount' => $kecamatanCount,
        ]);
    }

    public function map(): string
    {
        $db = db_connect();

        $mapTypes = $this->getMapTypes($db);
        $kabupatenOptions = $this->getRiauKabupatenOptions($db);
        $kecamatanOptions = [];
        $klasifikasiOptions = [];

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

        return view('admin/map', [
            'pageTitle' => 'Map',
            'mapTypes' => $mapTypes,
            'mapDefaultId' => (int) ($mapTypes[0]['id'] ?? 1),
            'kabupatenOptions' => $kabupatenOptions,
            'kecamatanOptions' => $kecamatanOptions,
            'klasifikasiOptions' => $klasifikasiOptions,
        ]);
    }

    public function mapKecamatanOptions()
    {
        $kabupaten = trim((string) $this->request->getGet('kabupaten'));
        if ($kabupaten === '' || $kabupaten === '*') {
            return $this->response->setJSON([
                'status' => 'ok',
                'kecamatan' => [],
            ]);
        }

        $db = db_connect();
        $kecamatan = $this->getKecamatanByKabupaten($db, $kabupaten);

        return $this->response->setJSON([
            'status' => 'ok',
            'kecamatan' => $kecamatan,
        ]);
    }

    private function getKecamatanByKabupaten($db, string $kabupaten): array
    {
        $fromMaster = $this->getKecamatanByKabupatenFromMaster($db, $kabupaten);
        if ($fromMaster !== []) {
            return $fromMaster;
        }

        if (! $db->tableExists('mst_sekolah')) {
            return [];
        }

        $rows = $db->table('mst_sekolah')
            ->select('kecamatan')
            ->where('kabupaten', $kabupaten)
            ->where('kecamatan IS NOT NULL', null, false)
            ->where('kecamatan !=', '')
            ->groupBy('kecamatan')
            ->orderBy('kecamatan', 'ASC')
            ->get()
            ->getResultArray();

        return array_values(array_filter(array_map(static function (array $row): string {
            return trim((string) ($row['kecamatan'] ?? ''));
        }, $rows), static fn (string $value): bool => $value !== ''));
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
            $lat = $this->parseCoordinate($row['latitude'] ?? null);
            $lng = $this->parseCoordinate($row['longitude'] ?? null);

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

    public function mapDetail()
    {
        $npsn = trim((string) $this->request->getGet('npsn'));
        if ($npsn === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'message' => 'Parameter npsn wajib diisi.',
            ]);
        }

        $db = db_connect();
        if (! $db->tableExists('mst_sekolah')) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Tabel mst_sekolah belum tersedia.',
            ]);
        }

        $school = $db->table('mst_sekolah')
            ->select('npsn, nama, jenis, nsm, kabupaten, kecamatan, latitude, longitude')
            ->where('npsn', $npsn)
            ->get()
            ->getRowArray();

        if (! is_array($school)) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Data sekolah tidak ditemukan.',
            ]);
        }

        $survey = [];
        if ($db->tableExists('trn_survey_sekolah')) {
            $availableColumns = $db->getFieldNames('trn_survey_sekolah');
            $desiredColumns = [
                'id',
                'npsn',
                'periode',
                'emis_jumlah_siswa',
                'survey_jumlah_siswa',
                'survey_tingat_kerusakan',
                'survey_klasifikasi_kerusakan',
                'status_lahan',
                'status_penanganan',
                'ekspos_tingkat_kerusakan',
                'ekspos_klasifikasi_kerusakan',
                'ekspos_status',
            ];

            $columns = array_values(array_intersect($desiredColumns, $availableColumns));
            if ($columns !== []) {
                $builder = $db->table('trn_survey_sekolah')
                    ->select(implode(',', $columns))
                    ->where('npsn', $npsn);

                if (in_array('periode', $columns, true)) {
                    $builder->orderBy('periode', 'DESC');
                }
                if (in_array('id', $columns, true)) {
                    $builder->orderBy('id', 'DESC');
                }

                $survey = $builder->get()->getRowArray() ?? [];
            }
        }

        return $this->response->setJSON([
            'status' => 'ok',
            'school' => $school,
            'survey' => $survey,
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

    private function getRiauKabupatenOptions($db): array
    {
        $riauMasterKabupaten = $this->getRiauMasterKabupaten($db);
        if ($riauMasterKabupaten !== []) {
            return $riauMasterKabupaten;
        }

        if (! $db->tableExists('mst_sekolah')) {
            return [];
        }

        $kabupatenRows = $db->table('mst_sekolah')
            ->select('kabupaten')
            ->where('kabupaten IS NOT NULL', null, false)
            ->where('kabupaten !=', '')
            ->groupBy('kabupaten')
            ->orderBy('kabupaten', 'ASC')
            ->get()
            ->getResultArray();

        $sekolahKabupaten = array_values(array_filter(array_map(static function (array $row): string {
            return trim((string) ($row['kabupaten'] ?? ''));
        }, $kabupatenRows), static fn (string $value): bool => $value !== ''));

        return $sekolahKabupaten;
    }

    private function getRiauMasterKabupaten($db): array
    {
        if (! $db->tableExists('mst_kabupaten')) {
            return [];
        }

        $kodeProvinsiRiau = $this->detectRiauProvinceCode($db);
        if ($kodeProvinsiRiau === null) {
            return [];
        }

        $rows = $db->table('mst_kabupaten')
            ->select('nama_kabupaten')
            ->where('kode_provinsi', $kodeProvinsiRiau)
            ->where('nama_kabupaten IS NOT NULL', null, false)
            ->where('nama_kabupaten !=', '')
            ->groupBy('nama_kabupaten')
            ->orderBy('nama_kabupaten', 'ASC')
            ->get()
            ->getResultArray();

        return array_values(array_filter(array_map(static function (array $row): string {
            return trim((string) ($row['nama_kabupaten'] ?? ''));
        }, $rows), static fn (string $value): bool => $value !== ''));
    }

    private function detectRiauProvinceCode($db): ?string
    {
        if ($db->tableExists('mst_provinsi')) {
            $riau = $db->table('mst_provinsi')
                ->select('kode_provinsi')
                ->where('LOWER(TRIM(nama_provinsi))', 'riau')
                ->get()
                ->getRowArray();

            $kode = trim((string) ($riau['kode_provinsi'] ?? ''));
            if ($kode !== '') {
                return $kode;
            }
        }

        if ($db->tableExists('mst_kabupaten')) {
            $exists = $db->table('mst_kabupaten')
                ->where('kode_provinsi', self::RIAU_PROVINCE_CODE)
                ->countAllResults();

            if ($exists > 0) {
                return self::RIAU_PROVINCE_CODE;
            }
        }

        return null;
    }

    private function getKecamatanByKabupatenFromMaster($db, string $kabupaten): array
    {
        if (! $db->tableExists('mst_kabupaten') || ! $db->tableExists('mst_kecamatan')) {
            return [];
        }

        $kodeProvinsiRiau = $this->detectRiauProvinceCode($db);
        if ($kodeProvinsiRiau === null) {
            return [];
        }

        $kabupatenRows = $db->table('mst_kabupaten')
            ->select('kode_kabupaten, nama_kabupaten')
            ->where('kode_provinsi', $kodeProvinsiRiau)
            ->get()
            ->getResultArray();

        $selectedNormalized = $this->normalizeWilayahName($kabupaten);
        $kodeKabupaten = null;

        foreach ($kabupatenRows as $row) {
            $namaKabupaten = trim((string) ($row['nama_kabupaten'] ?? ''));
            $kode = trim((string) ($row['kode_kabupaten'] ?? ''));
            if ($namaKabupaten === '' || $kode === '') {
                continue;
            }

            $masterNormalized = $this->normalizeWilayahName($namaKabupaten);
            if ($masterNormalized === '') {
                continue;
            }

            if ($selectedNormalized === $masterNormalized || str_contains($selectedNormalized, $masterNormalized) || str_contains($masterNormalized, $selectedNormalized)) {
                $kodeKabupaten = $kode;
                break;
            }
        }

        if ($kodeKabupaten === null) {
            return [];
        }

        $rows = $db->table('mst_kecamatan')
            ->select('nama_kecamatan')
            ->where('kode_provinsi', $kodeProvinsiRiau)
            ->where('kode_kabupaten', $kodeKabupaten)
            ->where('nama_kecamatan IS NOT NULL', null, false)
            ->where('nama_kecamatan !=', '')
            ->groupBy('nama_kecamatan')
            ->orderBy('nama_kecamatan', 'ASC')
            ->get()
            ->getResultArray();

        return array_values(array_filter(array_map(static function (array $row): string {
            return trim((string) ($row['nama_kecamatan'] ?? ''));
        }, $rows), static fn (string $value): bool => $value !== ''));
    }

    private function normalizeWilayahName(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '', $value) ?? '';

        return $value;
    }

    private function parseCoordinate($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $normalized = str_replace(',', '.', trim((string) $value));
        if (! is_numeric($normalized)) {
            return null;
        }

        return (float) $normalized;
    }
}
