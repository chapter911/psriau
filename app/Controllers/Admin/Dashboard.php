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

        // School Statistics
        $schoolCount = 0;
        $schoolWithSurvey = 0;
        $damageClassification = [];
        if ($db->tableExists('mst_sekolah')) {
            try {
                $schoolCount = (int) $db->table('mst_sekolah')->countAllResults();
            } catch (\Throwable $e) {
                $schoolCount = 0;
            }

            if ($db->tableExists('trn_survey_sekolah')) {
                try {
                    $schoolWithSurvey = (int) $db->table('mst_sekolah')
                        ->join('trn_survey_sekolah', 'mst_sekolah.npsn = trn_survey_sekolah.npsn', 'inner')
                        ->select('DISTINCT mst_sekolah.npsn')
                        ->countAllResults();
                } catch (\Throwable $e) {
                    $schoolWithSurvey = 0;
                }

                try {
                    $damageClassification = $db->table('trn_survey_sekolah')
                        ->select('survey_klasifikasi_kerusakan, COUNT(*) as count')
                        ->where('survey_klasifikasi_kerusakan IS NOT NULL', null, false)
                        ->where('survey_klasifikasi_kerusakan !=', '')
                        ->groupBy('survey_klasifikasi_kerusakan')
                        ->orderBy('count', 'DESC')
                        ->get()
                        ->getResultArray() ?? [];
                } catch (\Throwable $e) {
                    $damageClassification = [];
                }
            }
        }

        // Report Statistics
        $harianReportCount = 0;
        $mingguanReportCount = 0;
        if ($db->tableExists('trn_laporan_harian')) {
            try {
                $harianReportCount = (int) $db->table('trn_laporan_harian')->countAllResults();
            } catch (\Throwable $e) {
                $harianReportCount = 0;
            }
        }
        if ($db->tableExists('trn_laporan_mingguan')) {
            try {
                $mingguanReportCount = (int) $db->table('trn_laporan_mingguan')->countAllResults();
            } catch (\Throwable $e) {
                $mingguanReportCount = 0;
            }
        }

        // SIMAK Konstruksi Document Data
        $konstruksiChartData = $this->getSimakDokumenChartData($db, 'konstruksi');

        // SIMAK Konsultasi Document Data
        $konsultasiChartData = $this->getSimakDokumenChartData($db, 'konsultasi');

        return view('admin/dashboard', [
            'pageTitle' => 'Dashboard Admin',
            // Schools
            'schoolCount' => $schoolCount,
            'schoolWithSurvey' => $schoolWithSurvey,
            'damageClassification' => $damageClassification,
            // Reports
            'harianReportCount' => $harianReportCount,
            'mingguanReportCount' => $mingguanReportCount,
            // SIMAK Charts
            'konstruksiChartData' => $konstruksiChartData,
            'konsultasiChartData' => $konsultasiChartData,
        ]);
    }

    /**
     * Get document completeness chart data for SIMAK Konstruksi or Konsultasi
     *
     * Based on the page display logic from Kontrak controller:
     * - Lengkap: All documents verified as 'sesuai'
     * - Belum Sesuai: Documents marked 'tidak_sesuai'
     * - Menunggu Verifikasi: Documents exist but not yet verified
     * - Belum Ada: No documents uploaded
     *
     * @param mixed $db
     * @param string $type 'konstruksi' or 'konsultasi'
     * @return array{labels: string[], ada: int[], tidak_ada: int[]}
     */
    private function getSimakDokumenChartData($db, string $type): array
    {
        $tableSimak = $type === 'konstruksi' ? 'trn_kontrak_simak' : 'trn_kontrak_simak_konsultasi';
        $tableVerifikasi = $type === 'konstruksi' ? 'trn_kontrak_simak_verifikasi' : 'trn_kontrak_simak_konsultasi_verifikasi';
        $tableDokumen = $type === 'konstruksi' ? 'trn_kontrak_simak_verifikasi_dokumen' : 'trn_kontrak_simak_konsultasi_verifikasi_dokumen';
        $tableTemplate = $type === 'konstruksi' ? 'mst_simak_konstruksi_item' : 'mst_simak_konsultasi_item';

        $result = [
            'labels' => [],
            'ada' => [],
            'tidak_ada' => [],
        ];

        // Check if required tables exist
        if (!$db->tableExists($tableSimak) || !$db->tableExists('mst_paket')) {
            return $result;
        }

        try {
            // Get distinct paket IDs that have kontrak records
            $mainTable = $tableSimak;
            $paketQuery = $db->table($mainTable)
                ->select($mainTable . '.paket_id, mp.nama_paket')
                ->join('mst_paket mp', 'mp.id = ' . $mainTable . '.paket_id', 'left')
                ->where($mainTable . '.paket_id IS NOT NULL', null, false)
                ->where('mp.nama_paket IS NOT NULL', null, false)
                ->where('mp.nama_paket !=', '')
                ->groupBy($mainTable . '.paket_id, mp.nama_paket')
                ->orderBy('mp.nama_paket', 'ASC')
                ->get()
                ->getResultArray();

            if (empty($paketQuery)) {
                return $result;
            }

            // Get template items (leaf rows only)
            $templateItems = [];
            if ($db->tableExists($tableTemplate)) {
                $templateQuery = $db->table($tableTemplate)
                    ->select('row_no, has_draft')
                    ->where('is_leaf', 1)
                    ->where('is_active', 1)
                    ->get()
                    ->getResultArray();

                foreach ($templateQuery as $item) {
                    $rowNo = (int) ($item['row_no'] ?? 0);
                    if ($rowNo > 0) {
                        $templateItems[$rowNo] = [
                            'has_draft' => (bool) ($item['has_draft'] ?? false),
                        ];
                    }
                }
            }

            // If no template items found, use verifikasi table directly
            $useTemplate = !empty($templateItems);

            foreach ($paketQuery as $paket) {
                $paketId = (int) ($paket['paket_id'] ?? 0);
                $paketNama = trim((string) ($paket['nama_paket'] ?? 'Tanpa Paket'));

                if ($paketId <= 0) {
                    continue;
                }

                // Get all SIMAK IDs for this paket
                $simakIds = $db->table($tableSimak)
                    ->select('id')
                    ->where('paket_id', $paketId)
                    ->get()
                    ->getResultArray();

                if (empty($simakIds)) {
                    continue;
                }

                $simakIdList = array_map(fn($row) => (int) $row['id'], $simakIds);

                if ($useTemplate && !empty($rowNos)) {
                    // Complex logic using template + verifikasi + dokumen
                    $adaCount = 0;
                    $tidakAdaCount = 0;

                    // Get row numbers to check
                    $rowNos = array_keys($templateItems);

                    // Get verifikasi data
                    $verifikasiData = [];
                    if ($db->tableExists($tableVerifikasi) && !empty($simakIdList) && !empty($rowNos)) {
                        $verifikasiQuery = $db->table($tableVerifikasi)
                            ->select('simak_id, row_no, kelengkapan_dokumen, verifikasi_ki')
                            ->whereIn('simak_id', $simakIdList)
                            ->whereIn('row_no', $rowNos)
                            ->get()
                            ->getResultArray();

                        foreach ($verifikasiQuery as $row) {
                            $key = ((int) $row['simak_id']) . '_' . ((int) $row['row_no']);
                            $verifikasiData[$key] = $row;
                        }
                    }

                    // Get dokumen data
                    $dokumenData = [];
                    if ($db->tableExists($tableDokumen) && !empty($simakIdList) && !empty($rowNos)) {
                        $dokumenQuery = $db->table($tableDokumen)
                            ->select('simak_id, row_no, tipe_dokumen, file_relative_path, file_stored_name, verifikasi_ki')
                            ->whereIn('simak_id', $simakIdList)
                            ->whereIn('row_no', $rowNos)
                            ->orderBy('row_no', 'ASC')
                            ->orderBy('id', 'DESC')
                            ->get()
                            ->getResultArray();

                        foreach ($dokumenQuery as $doc) {
                            $key = ((int) $doc['simak_id']) . '_' . ((int) $doc['row_no']);
                            if (!isset($dokumenData[$key])) {
                                $dokumenData[$key] = $doc;
                            }
                        }
                    }

                    // Count each SIMAK record
                    foreach ($simakIdList as $simakId) {
                        foreach ($rowNos as $rowNo) {
                            $vKey = $simakId . '_' . $rowNo;
                            $dKey = $simakId . '_' . $rowNo;

                            $verifikasiRow = $verifikasiData[$vKey] ?? null;
                            $dokumenRow = $dokumenData[$dKey] ?? null;

                            $status = $this->resolveSimpleRowStatus(
                                $templateItems[$rowNo]['has_draft'] ?? false,
                                $verifikasiRow,
                                $dokumenRow
                            );

                            // Ada = Lengkap (lengkap) + Menunggu Verifikasi (belum_verifikasi)
                            // Tidak Ada = Belum Sesuai (belum_sesuai) + Belum Ada (belum_ada)
                            if ($status === 'lengkap' || $status === 'belum_verifikasi') {
                                $adaCount++;
                            } else {
                                $tidakAdaCount++;
                            }
                        }
                    }

                    $result['labels'][] = $paketNama;
                    $result['ada'][] = $adaCount;
                    $result['tidak_ada'][] = $tidakAdaCount;
                } else {
                    // Fallback: Simple count from verifikasi table
                    $totalCount = $db->table($tableVerifikasi)
                        ->whereIn('simak_id', $simakIdList)
                        ->countAllResults();

                    $adaCount = 0;
                    if ($totalCount > 0) {
                        $adaCount = (int) $db->table($tableVerifikasi)
                            ->whereIn('simak_id', $simakIdList)
                            ->where('kelengkapan_dokumen', 'ada')
                            ->countAllResults();
                    }

                    $result['labels'][] = $paketNama;
                    $result['ada'][] = $adaCount;
                    $result['tidak_ada'][] = $totalCount - $adaCount;
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'SIMAK Chart Error: ' . $e->getMessage());
            return [
                'labels' => [],
                'ada' => [],
                'tidak_ada' => [],
            ];
        }

        return $result;
    }

    /**
     * Resolve single row status - simplified version of Kontrak controller logic
     */
    private function resolveSimpleRowStatus(bool $hasDraft, ?array $verifikasiRow, ?array $dokumenRow): string
    {
        $rowKelengkapan = strtolower(trim((string) ($verifikasiRow['kelengkapan_dokumen'] ?? '')));
        $rowVerifikasi = strtolower(trim((string) ($verifikasiRow['verifikasi_ki'] ?? '')));

        // Check dokumen row
        $docType = $dokumenRow ? strtolower(trim((string) ($dokumenRow['tipe_dokumen'] ?? 'final'))) : '';
        $docVerifikasi = $dokumenRow ? strtolower(trim((string) ($dokumenRow['verifikasi_ki'] ?? ''))) : '';
        $docHasFile = $dokumenRow && trim((string) ($dokumenRow['file_relative_path'] ?? '')) !== '';
        $docIsPlaceholder = $dokumenRow
            && trim((string) ($dokumenRow['file_relative_path'] ?? '')) === ''
            && trim((string) ($dokumenRow['file_stored_name'] ?? '')) === '';

        // For items with draft requirement
        if ($hasDraft) {
            // Draft verified as 'tidak_sesuai'
            if ($docType === 'draft' && $docVerifikasi === 'tidak_sesuai') {
                return 'belum_sesuai';
            }

            // Draft verified as 'sesuai'
            if ($docType === 'draft' && $docVerifikasi === 'sesuai') {
                // Check final dokumen
                // If we have the final dokumen row separately, check it
                if ($docType !== 'draft' && $docVerifikasi === 'sesuai') {
                    return 'lengkap';
                }
                if ($docType !== 'draft' && $docVerifikasi === 'tidak_sesuai') {
                    return 'belum_sesuai';
                }
                if ($docHasFile || $docIsPlaceholder) {
                    return 'belum_verifikasi';
                }
                return 'belum_ada';
            }

            // Row level verification
            if ($rowVerifikasi === 'tidak_sesuai') {
                return 'belum_sesuai';
            }
            if ($rowVerifikasi === 'sesuai') {
                return 'belum_ada';
            }
            if ($rowVerifikasi === 'belum_verifikasi') {
                return 'belum_verifikasi';
            }

            // If we have any document
            if ($docHasFile || $docIsPlaceholder || $dokumenRow !== null) {
                return 'belum_verifikasi';
            }

            return 'belum_ada';
        }

        // For items without draft requirement (final only)
        // If we have dokumen row
        if ($dokumenRow !== null) {
            if ($docVerifikasi === 'sesuai') {
                return 'lengkap';
            }
            if ($docVerifikasi === 'tidak_sesuai') {
                return 'belum_sesuai';
            }
            if ($docVerifikasi === 'belum_verifikasi' || $docIsPlaceholder || $docVerifikasi === '') {
                return 'belum_verifikasi';
            }
        }

        // Row level verification
        if ($rowKelengkapan === 'tidak' && $rowVerifikasi === 'sesuai') {
            return 'lengkap';
        }
        if ($rowVerifikasi === 'tidak_sesuai') {
            return 'belum_sesuai';
        }
        if ($rowVerifikasi === 'belum_verifikasi') {
            return 'belum_verifikasi';
        }
        if ($rowKelengkapan !== '' || $rowVerifikasi !== '') {
            return 'belum_ada';
        }

        return 'belum_ada';
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
