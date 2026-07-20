<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ArticleModel;
use App\Models\EventModel;
use App\Models\HomeSlideModel;
use App\Models\AuditHistoryModel;
use App\Models\LoginHistoryModel;
use App\Models\MstPaketModel;

class Dashboard extends BaseController
{
    private const RIAU_PROVINCE_CODE = '14';

    public function index(): string
    {
        $db = db_connect();

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
     * Exact implementation from Konstruksi controller
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

        if (!$db->tableExists('mst_paket')) {
            return $result;
        }

        try {
            // Get template items (leaf rows)
            $templateItems = [];
            if ($db->tableExists($tableTemplate)) {
                $templateColumns = $db->getFieldNames($tableTemplate);
                $leafColumn = null;
                $hasDraftColumn = null;

                if (in_array('is_leaf', $templateColumns, true)) {
                    $leafColumn = 'is_leaf';
                } elseif (in_array('has_question', $templateColumns, true)) {
                    $leafColumn = 'has_question';
                }

                if (in_array('has_draft', $templateColumns, true)) {
                    $hasDraftColumn = 'has_draft';
                }

                if ($leafColumn !== null) {
                    $selectCols = ['row_no'];
                    $selectSql = 'row_no';
                    if ($hasDraftColumn !== null) {
                        $selectSql .= ', ' . $hasDraftColumn;
                    }

                    $query = $db->table($tableTemplate)
                        ->select($selectSql)
                        ->where($leafColumn, 1);

                    $templateQuery = $query->get()->getResultArray();

                    foreach ($templateQuery as $item) {
                        $rowNo = (int) ($item['row_no'] ?? 0);
                        if ($rowNo > 0) {
                            $templateItems[$rowNo] = [
                                'has_draft' => $hasDraftColumn !== null ? (bool) ($item[$hasDraftColumn] ?? false) : false,
                            ];
                        }
                    }
                }
            }

            $leafRows = array_keys($templateItems);
            $totalLeafRows = count($leafRows);

            // Get ALL paket from mst_paket
            $paketQuery = $db->table('mst_paket')
                ->select('id, nama_paket')
                ->where('nama_paket IS NOT NULL', null, false)
                ->where('nama_paket !=', '')
                ->orderBy('nama_paket', 'ASC')
                ->get()
                ->getResultArray();

            if (empty($paketQuery)) {
                return $result;
            }

            if ($totalLeafRows === 0 || !$db->tableExists($tableVerifikasi)) {
                return $result;
            }

            foreach ($paketQuery as $paket) {
                $paketId = (int) ($paket['id'] ?? 0);
                $paketNama = trim((string) ($paket['nama_paket'] ?? 'Tanpa Paket'));

                if ($paketId <= 0) {
                    continue;
                }

                // Get all SIMAK IDs for this paket
                $simakIds = [];
                if ($db->tableExists($tableSimak)) {
                    $simakQuery = $db->table($tableSimak)
                        ->select('id')
                        ->where('paket_id', $paketId)
                        ->get()
                        ->getResultArray();
                    $simakIds = array_map(fn($row) => (int) $row['id'], $simakQuery);
                }

                if (empty($simakIds)) {
                    $result['labels'][] = $paketNama;
                    $result['ada'][] = 0;
                    $result['tidak_ada'][] = 100;
                    continue;
                }

                // Calculate per-SIMAK and average
                $totalAdaPercent = 0;
                $validSimakCount = 0;

                foreach ($simakIds as $simakId) {
                    // Get verifikasi data for this SIMAK
                    $verifikasiData = [];
                    $vQuery = $db->table($tableVerifikasi)
                        ->select('row_no, kelengkapan_dokumen, verifikasi_ki')
                        ->where('simak_id', $simakId)
                        ->whereIn('row_no', $leafRows)
                        ->get()
                        ->getResultArray();

                    foreach ($vQuery as $v) {
                        $verifikasiData[(int) $v['row_no']] = $v;
                    }

                    // Get dokumen data for this SIMAK
                    $dokumenData = [];
                    if ($db->tableExists($tableDokumen)) {
                        $dQuery = $db->table($tableDokumen)
                            ->select('row_no, tipe_dokumen, file_relative_path, file_stored_name, verifikasi_ki')
                            ->where('simak_id', $simakId)
                            ->whereIn('row_no', $leafRows)
                            ->orderBy('row_no', 'ASC')
                            ->orderBy('id', 'DESC')
                            ->get()
                            ->getResultArray();

                        foreach ($dQuery as $doc) {
                            $rowNo = (int) $doc['row_no'];
                            if (!isset($dokumenData[$rowNo])) {
                                $dokumenData[$rowNo] = [];
                            }
                            $dokumenData[$rowNo][] = $doc;
                        }
                    }

                    // Count statuses for each leaf row
                    $lengkapCount = 0;
                    $belumSesuaiCount = 0;
                    $belumVerifikasiCount = 0;
                    $belumAdaCount = 0;

                    foreach ($leafRows as $rowNo) {
                        $vRow = $verifikasiData[$rowNo] ?? null;
                        $dRows = $dokumenData[$rowNo] ?? [];

                        $status = $this->resolveSimakRowStatus(
                            $templateItems[$rowNo] ?? ['has_draft' => false],
                            $vRow,
                            $dRows
                        );

                        if ($status === 'lengkap') {
                            $lengkapCount++;
                        } elseif ($status === 'belum_sesuai') {
                            $belumSesuaiCount++;
                        } elseif ($status === 'belum_verifikasi') {
                            $belumVerifikasiCount++;
                        } else {
                            $belumAdaCount++;
                        }
                    }

                    // Calculate percentage for this SIMAK
                    $adaPercent = round((($lengkapCount + $belumVerifikasiCount) / $totalLeafRows) * 100, 2);
                    $adaPercent = min(100, max(0, $adaPercent));
                    $totalAdaPercent += $adaPercent;
                    $validSimakCount++;
                }

                // Average percentage across all SIMAK in this paket
                $avgAdaPercent = $validSimakCount > 0 ? round($totalAdaPercent / $validSimakCount, 2) : 0;
                $avgAdaPercent = min(100, max(0, $avgAdaPercent));
                $tidakAdaPercent = round(100 - $avgAdaPercent, 2);
                $tidakAdaPercent = min(100, max(0, $tidakAdaPercent));

                $result['labels'][] = $paketNama;
                $result['ada'][] = $avgAdaPercent;
                $result['tidak_ada'][] = $tidakAdaPercent;
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
     * Resolve row status - exact copy from Konstruksi controller
     */
    private function resolveSimakRowStatus(array $templateItem, ?array $verifikasiRow, array $dokumenRows): string
    {
        $hasDraft = (bool) ($templateItem['has_draft'] ?? false);
        $rowKelengkapan = strtolower(trim((string) ($verifikasiRow['kelengkapan_dokumen'] ?? '')));
        $rowVerifikasi = strtolower(trim((string) ($verifikasiRow['verifikasi_ki'] ?? '')));

        $draftDokumen = null;
        $finalDokumen = null;
        foreach ($dokumenRows as $docRow) {
            $docType = strtolower(trim((string) ($docRow['tipe_dokumen'] ?? 'final')));
            if ($docType === 'draft' && $draftDokumen === null) {
                $draftDokumen = $docRow;
            } elseif ($docType !== 'draft' && $finalDokumen === null) {
                $finalDokumen = $docRow;
            }
            if ($draftDokumen !== null && $finalDokumen !== null) {
                break;
            }
        }

        $draftVerifikasi = is_array($draftDokumen) ? strtolower(trim((string) ($draftDokumen['verifikasi_ki'] ?? ''))) : '';
        $finalVerifikasi = is_array($finalDokumen) ? strtolower(trim((string) ($finalDokumen['verifikasi_ki'] ?? ''))) : '';
        $draftHasFile = is_array($draftDokumen) && trim((string) ($draftDokumen['file_relative_path'] ?? '')) !== '';
        $finalHasFile = is_array($finalDokumen) && trim((string) ($finalDokumen['file_relative_path'] ?? '')) !== '';
        $draftNoFilePlaceholder = $hasDraft
            && is_array($draftDokumen)
            && trim((string) ($draftDokumen['file_relative_path'] ?? '')) === ''
            && trim((string) ($draftDokumen['file_stored_name'] ?? '')) === '';
        $finalNoFilePlaceholder = is_array($finalDokumen)
            && trim((string) ($finalDokumen['file_relative_path'] ?? '')) === ''
            && trim((string) ($finalDokumen['file_stored_name'] ?? '')) === '';

        if ($hasDraft) {
            if ($draftVerifikasi === 'tidak_sesuai') {
                return 'belum_sesuai';
            }
            if ($draftVerifikasi === 'sesuai') {
                if ($finalVerifikasi === 'sesuai') {
                    return 'lengkap';
                }
                if ($finalVerifikasi === 'tidak_sesuai') {
                    return 'belum_sesuai';
                }
                if ($finalHasFile || is_array($finalDokumen) || $finalNoFilePlaceholder) {
                    return 'belum_verifikasi';
                }
                return 'belum_ada';
            }
            if ($draftVerifikasi === 'belum_verifikasi' || ($draftDokumen !== null && $draftVerifikasi === '')) {
                return 'belum_verifikasi';
            }
            if ($rowVerifikasi === 'tidak_sesuai') {
                return 'belum_sesuai';
            }
            if ($rowVerifikasi === 'sesuai') {
                return 'belum_ada';
            }
            if ($rowVerifikasi === 'belum_verifikasi') {
                return 'belum_verifikasi';
            }
            if ($finalVerifikasi === 'sesuai') {
                return 'lengkap';
            }
            if ($finalVerifikasi === 'tidak_sesuai') {
                return 'belum_sesuai';
            }
            if ($draftHasFile || $draftNoFilePlaceholder || $draftDokumen !== null) {
                return 'belum_verifikasi';
            }
            return 'belum_ada';
        }

        if ($finalVerifikasi === 'sesuai') {
            return 'lengkap';
        }
        if ($finalVerifikasi === 'tidak_sesuai') {
            return 'belum_sesuai';
        }
        if ($finalNoFilePlaceholder || $finalVerifikasi === 'belum_verifikasi' || ($finalDokumen !== null && $finalVerifikasi === '')) {
            return 'belum_verifikasi';
        }
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

        $paketModel = new MstPaketModel();
        $paketOptions = $paketModel->where('is_active', 1)->orderBy('nama_paket', 'ASC')->findAll();

        $defaultMapTypeId = 1;
        foreach ($mapTypes as $mapType) {
            if (stripos((string) ($mapType['map_name'] ?? ''), 'hybrid') !== false) {
                $defaultMapTypeId = (int) ($mapType['id'] ?? 1);
                break;
            }
        }

        return view('admin/map', [
            'pageTitle' => 'Map',
            'mapTypes' => $mapTypes,
            'mapDefaultId' => $defaultMapTypeId,
            'kabupatenOptions' => $kabupatenOptions,
            'kecamatanOptions' => $kecamatanOptions,
            'klasifikasiOptions' => $klasifikasiOptions,
            'paketOptions' => $paketOptions,
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
            $defaultMapTypeId = 1;
            foreach ($mapTypes as $mapType) {
                if (stripos((string) ($mapType['map_name'] ?? ''), 'hybrid') !== false) {
                    $defaultMapTypeId = (int) ($mapType['id'] ?? 1);
                    break;
                }
            }
            $selectedMapTypeId = $defaultMapTypeId;
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
            ->select('mst_sekolah.npsn, mst_sekolah.nama, mst_sekolah.jenis, mst_sekolah.nsm, mst_sekolah.kabupaten, mst_sekolah.kecamatan, mst_sekolah.latitude, mst_sekolah.longitude')
            ->where('mst_sekolah.paket_id >', 0)
            ->where('mst_sekolah.paket_id IS NOT NULL', null, false);

        if ($hasSurveyTable) {
            $latestSurveySubQuery = "SELECT t1.npsn, t1.periode, t1.survey_klasifikasi_kerusakan, t1.survey_tingat_kerusakan, t1.status_lahan, t1.status_penanganan, t1.ekspos_status\n                FROM trn_survey_sekolah t1\n                INNER JOIN (\n                    SELECT npsn, MAX(periode) AS max_periode\n                    FROM trn_survey_sekolah\n                    GROUP BY npsn\n\n                ) latest ON latest.npsn = t1.npsn AND latest.max_periode = t1.periode";

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

        $paketId = (int) $this->request->getGet('paket_id');
        if ($paketId > 0) {
            $builder->where('mst_sekolah.paket_id', $paketId);
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
                'id', 'npsn', 'periode', 'emis_jumlah_siswa', 'survey_jumlah_siswa',
                'survey_tingat_kerusakan', 'survey_klasifikasi_kerusakan', 'status_lahan',
                'status_penanganan', 'ekspos_tingkat_kerusakan', 'ekspos_klasifikasi_kerusakan', 'ekspos_status',
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

        return array_values(array_filter(array_map(static function (array $row): string {
            return trim((string) ($row['kabupaten'] ?? ''));
        }, $kabupatenRows), static fn (string $value): bool => $value !== ''));
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
