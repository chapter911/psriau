<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LaporanHarianReportModel;
use App\Models\LaporanHarianTitleModel;
use App\Models\LaporanMingguanReportModel;
use App\Models\MstPegawaiModel;
use App\Models\LaporanPerjalananDinasModel;
use CodeIgniter\HTTP\RedirectResponse;

class Laporan extends BaseController
{
    private const WEEKLY_MAX_FILE_SIZE_BYTES = 10485760; // 10 MB
    private const DAILY_MAX_FILE_SIZE_BYTES = 10485760; // 10 MB per image

    public function index()
    {
        return redirect()->to(site_url('admin/laporan/harian'));
    }

    public function harian()
    {
        if (! $this->canViewLaporan()) {
            return redirect()->to(site_url('/admin'));
        }

        $titleModel = new LaporanHarianTitleModel();
        $titles = $titleModel->orderBy('ordering', 'ASC')->orderBy('id', 'ASC')->findAll();

        $statsRows = db_connect()->table('laporan_harian_reports')
            ->select('sekolah_id, COUNT(*) AS total_reports, MAX(report_date) AS last_report_date')
            ->groupBy('sekolah_id')
            ->get()
            ->getResultArray();

        $titleStats = [];
        foreach ($statsRows as $row) {
            $titleStats[(int) ($row['sekolah_id'] ?? 0)] = [
                'total_reports' => (int) ($row['total_reports'] ?? 0),
                'last_report_date' => (string) ($row['last_report_date'] ?? ''),
            ];
        }

        return view('admin/laporan/harian', [
            'title' => 'Laporan Harian',
            'titles' => $titles,
            'title_stats' => $titleStats,
            'can_edit' => $this->canManageLaporan(),
        ]);
    }

    public function harianDetail(int $sekolahId)
    {
        if (! $this->canViewLaporan()) {
            return redirect()->to(site_url('/admin'));
        }

        $title = (new LaporanHarianTitleModel())->find($sekolahId);
        if (! is_array($title)) {
            return redirect()->to(site_url('admin/laporan/harian'))->with('error', 'Data sekolah tidak ditemukan.');
        }

        $rows = db_connect()->table('laporan_harian_reports r')
            ->select('r.*, t.name AS sekolah_name')
            ->join('laporan_sekolah t', 't.id = r.sekolah_id', 'left')
            ->where('r.sekolah_id', $sekolahId)
            ->orderBy('r.report_date', 'DESC')
            ->orderBy('r.id', 'DESC')
            ->get()
            ->getResultArray();

        $reports = array_map(static function (array $row): array {
            $row['sections'] = json_decode((string) ($row['sections_json'] ?? '[]'), true);
            if (! is_array($row['sections'])) {
                $row['sections'] = [];
            }

            $row['photos'] = json_decode((string) ($row['photo_paths_json'] ?? '[]'), true);
            if (! is_array($row['photos'])) {
                $row['photos'] = [];
            }

            return $row;
        }, $rows);

        return view('admin/laporan/harian_detail', [
            'title' => 'Detail Laporan Harian',
            'selected_title' => $title,
            'reports' => $reports,
            'can_edit' => $this->canManageLaporan(),
        ]);
    }

    public function createHarianTitle(): RedirectResponse
    {
        if (! $this->canManageLaporan()) {
            return redirect()->to(site_url('admin/laporan/harian'))->with('error', 'Anda tidak memiliki akses untuk mengubah data sekolah.');
        }

        $name = trim((string) $this->request->getPost('name'));
        if ($name === '') {
            return redirect()->to(site_url('admin/laporan/harian'))->with('error', 'Nama sekolah wajib diisi.');
        }

        $model = new LaporanHarianTitleModel();
        $existing = $model->where('LOWER(name)', strtolower($name))->first();
        if (is_array($existing)) {
            return redirect()->to(site_url('admin/laporan/harian'))->with('error', 'Data sekolah sudah ada.');
        }

        $maxRow = db_connect()->table('laporan_sekolah')->selectMax('ordering', 'max_ordering')->get()->getRowArray();
        $model->insert([
            'name' => $name,
            'ordering' => ((int) ($maxRow['max_ordering'] ?? 0)) + 1,
            'is_active' => 1,
        ]);

        return redirect()->to(site_url('admin/laporan/harian'))->with('success', 'Data sekolah berhasil ditambahkan.');
    }

    public function deleteHarianTitle(int $id): RedirectResponse
    {
        if (! $this->canManageLaporan()) {
            return redirect()->to(site_url('admin/laporan/harian'))->with('error', 'Anda tidak memiliki akses untuk menghapus data sekolah.');
        }

        $db = db_connect();
        $usedDaily = $db->table('laporan_harian_reports')->where('sekolah_id', $id)->countAllResults();
        $usedWeekly = $db->table('laporan_mingguan_reports')->where('sekolah_id', $id)->countAllResults();
        if ($usedDaily > 0 || $usedWeekly > 0) {
            return redirect()->to(site_url('admin/laporan/harian'))->with('error', 'Data sekolah tidak dapat dihapus karena masih digunakan.');
        }

        $db->table('laporan_sekolah')->where('id', $id)->delete();

        return redirect()->to(site_url('admin/laporan/harian'))->with('success', 'Data sekolah berhasil dihapus.');
    }

    public function createHarian(): RedirectResponse
    {
        if (! $this->canManageLaporan()) {
            $sekolahId = (int) $this->request->getPost('sekolah_id');
            return redirect()->to($this->dailyRedirectUrl($sekolahId))->with('error', 'Anda tidak memiliki akses untuk menambah laporan harian.');
        }

        $reportId = (int) $this->request->getPost('report_id');
        $payload = $this->buildHarianPayload();
        if ($payload instanceof RedirectResponse) {
            return $payload;
        }

        $reportModel = new LaporanHarianReportModel();
        $db = db_connect();

        if ($reportId > 0) {
            $existing = $reportModel->find($reportId);
            if (! is_array($existing)) {
                return redirect()->to(site_url('admin/laporan/harian'))->with('error', 'Laporan harian tidak ditemukan.');
            }

            $payload['updated_at'] = date('Y-m-d H:i:s');
            $uploadResult = $this->uploadDailyPhotos('photos');
            if ($uploadResult['error'] !== null) {
                return redirect()->to($this->dailyRedirectUrl((int) ($payload['sekolah_id'] ?? 0)))->withInput()->with('error', $uploadResult['error']);
            }

            $newPhotos = $uploadResult['photos'];
            if ($newPhotos !== []) {
                $existingPhotos = $this->decodeJsonArray((string) ($existing['photo_paths_json'] ?? '[]'));
                $payload['photo_paths_json'] = json_encode(array_values(array_merge($existingPhotos, $newPhotos)), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }

            $reportModel->update($reportId, $payload);
            return redirect()->to($this->dailyRedirectUrl((int) ($payload['sekolah_id'] ?? 0)))->with('success', 'Laporan harian berhasil diperbarui.');
        }

        $uploadResult = $this->uploadDailyPhotos('photos');
        if ($uploadResult['error'] !== null) {
            return redirect()->to($this->dailyRedirectUrl((int) ($payload['sekolah_id'] ?? 0)))->withInput()->with('error', $uploadResult['error']);
        }

        $photos = $uploadResult['photos'];
        $payload['photo_paths_json'] = json_encode($photos, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $payload['created_at'] = date('Y-m-d H:i:s');
        $payload['updated_at'] = date('Y-m-d H:i:s');
        $reportModel->insert($payload);

        return redirect()->to($this->dailyRedirectUrl((int) ($payload['sekolah_id'] ?? 0)))->with('success', 'Laporan harian berhasil ditambahkan.');
    }

    public function deleteHarian(int $id): RedirectResponse
    {
        if (! $this->canManageLaporan()) {
            return redirect()->to(site_url('admin/laporan/harian'))->with('error', 'Anda tidak memiliki akses untuk menghapus laporan harian.');
        }

        $reportModel = new LaporanHarianReportModel();
        $report = $reportModel->find($id);
        if (! is_array($report)) {
            return redirect()->to(site_url('admin/laporan/harian'))->with('error', 'Laporan harian tidak ditemukan.');
        }

        $titleId = (int) ($report['sekolah_id'] ?? 0);

        $this->deleteStoredFiles($this->decodeJsonArray((string) ($report['photo_paths_json'] ?? '[]')));
        $reportModel->delete($id);

        return redirect()->to($this->dailyRedirectUrl($titleId))->with('success', 'Laporan harian berhasil dihapus.');
    }

    public function mingguan()
    {
        if (! $this->canViewLaporan()) {
            return redirect()->to(site_url('/admin'));
        }

        $titles = (new LaporanHarianTitleModel())
            ->where('is_active', 1)
            ->orderBy('ordering', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();

        $rows = db_connect()->table('laporan_mingguan_reports r')
            ->select('r.*, t.name AS sekolah_name')
            ->join('laporan_sekolah t', 't.id = r.sekolah_id', 'left')
            ->orderBy('r.period_start', 'DESC')
            ->orderBy('r.id', 'DESC')
            ->get()
            ->getResultArray();

        $historyMap = $this->getMingguanHistoryMap($rows);

        return view('admin/laporan/mingguan', [
            'title' => 'Laporan Mingguan',
            'titles' => $titles,
            'reports' => $rows,
            'history_map' => $historyMap,
            'can_edit' => $this->canManageLaporan(),
        ]);
    }

    public function createMingguan(): RedirectResponse
    {
        if (! $this->canManageLaporan()) {
            return redirect()->to(site_url('admin/laporan/mingguan'))->with('error', 'Anda tidak memiliki akses untuk menambah laporan mingguan.');
        }

        $reportId = (int) $this->request->getPost('report_id');
        $titleId = (int) $this->request->getPost('sekolah_id');
        $periodDate = $this->normalizeDateValue((string) $this->request->getPost('period_date'));
        $description = trim((string) $this->request->getPost('description'));

        if ($titleId <= 0 || $periodDate === null) {
            return redirect()->to(site_url('admin/laporan/mingguan'))->withInput()->with('error', 'Sekolah dan periode wajib diisi.');
        }

        $periodStart = $this->startOfWeek($periodDate);
        $periodEnd = $this->endOfWeek($periodDate);

        $title = (new LaporanHarianTitleModel())->find($titleId);
        if (! is_array($title)) {
            return redirect()->to(site_url('admin/laporan/mingguan'))->withInput()->with('error', 'Sekolah tidak valid.');
        }

        $fileInfo = null;
        if ($reportId > 0) {
            $existing = (new LaporanMingguanReportModel())->find($reportId);
            if (! is_array($existing)) {
                return redirect()->to(site_url('admin/laporan/mingguan'))->with('error', 'Laporan mingguan tidak ditemukan.');
            }

            $this->storeMingguanHistory($existing);

            $fileInfo = $this->uploadWeeklyFile('report_file');
            if ($fileInfo === null) {
                $uploadMessage = $this->weeklyUploadMessage('report_file', false);
                if ($uploadMessage !== null) {
                    return redirect()->to(site_url('admin/laporan/mingguan'))->withInput()->with('error', $uploadMessage);
                }

                $fileInfo = [
                    'file_path' => (string) ($existing['file_path'] ?? ''),
                    'file_name' => (string) ($existing['file_name'] ?? ''),
                ];
            } else {
                $this->deleteStoredFile((string) ($existing['file_path'] ?? ''));
            }
        } else {
            $fileInfo = $this->uploadWeeklyFile('report_file');
            if ($fileInfo === null) {
                return redirect()->to(site_url('admin/laporan/mingguan'))->withInput()->with('error', $this->weeklyUploadMessage('report_file', true));
            }
        }

        $payload = [
            'sekolah_id' => $titleId,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'description' => $description !== '' ? $description : null,
            'file_path' => (string) $fileInfo['file_path'],
            'file_name' => (string) $fileInfo['file_name'],
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $model = new LaporanMingguanReportModel();

        if ($reportId > 0) {
            $model->update($reportId, $payload);
            return redirect()->to(site_url('admin/laporan/mingguan'))->with('success', 'Laporan mingguan berhasil diperbarui.');
        }

        $payload['created_at'] = date('Y-m-d H:i:s');
        $model->insert($payload);

        return redirect()->to(site_url('admin/laporan/mingguan'))->with('success', 'Laporan mingguan berhasil ditambahkan.');
    }

    public function perjalananDinas()
    {
        if (! $this->canViewLaporan()) {
            if ($this->request->isAJAX() || $this->isDataTableRequest()) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => 'Akses ditolak.',
                ]);
            }
            return redirect()->to(site_url('/admin'));
        }

        if ($this->request->isAJAX() || $this->isDataTableRequest()) {
            return $this->perjalananDinasDataTable();
        }

        $role = strtolower((string) session()->get('role'));
        $canUploadVerified = in_array($role, ['keuangan', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);
        $dasarSptOptions = (new \App\Models\MstDasarSptModel())->orderBy('id', 'ASC')->findAll();
        $pegawaiRows = $this->loadPegawaiOptions();
        $currentPegawai = $this->resolveCurrentPegawai(
            (string) (session()->get('fullName') ?: session()->get('username') ?: session()->get('name')), 
            $pegawaiRows
        );
        $currentPegawaiId = $currentPegawai ? (int) $currentPegawai['id'] : 0;

        return view('admin/laporan/perjalanan_dinas', [
            'title' => 'Laporan Perjalanan Dinas',
            'can_edit' => $this->canManageLaporan(),
            'can_upload_verified' => $canUploadVerified,
            'can_verify' => $this->canVerifyLaporan() || ($currentPegawaiId > 0),
            'kabupaten_options' => $this->loadKabupatenOptions(),
            'pegawai_options' => $pegawaiRows,
            'dasar_spt_options' => $dasarSptOptions,
        ]);
    }

    public function suratTugas()
    {
        if (! $this->canViewLaporan()) {
            if ($this->request->isAJAX() || $this->isDataTableRequest()) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => 'Akses ditolak.',
                ]);
            }
            return redirect()->to(site_url('/admin'));
        }

        if ($this->request->isAJAX() || $this->isDataTableRequest()) {
            return $this->perjalananDinasDataTable();
        }

        $dasarSptOptions = (new \App\Models\MstDasarSptModel())->orderBy('id', 'ASC')->findAll();
        $pegawaiRows = $this->loadPegawaiOptions();
        $currentPegawai = $this->resolveCurrentPegawai(
            (string) (session()->get('fullName') ?: session()->get('username') ?: session()->get('name')), 
            $pegawaiRows
        );
        $currentPegawaiId = $currentPegawai ? (int) $currentPegawai['id'] : 0;

        $kopSuratList = [];
        $db = db_connect();
        if ($db->tableExists('kop_surat')) {
            $kopSuratList = $db->table('kop_surat')->orderBy('is_active', 'DESC')->orderBy('id', 'DESC')->get()->getResultArray();
        }

        $transportasiList = [];
        if ($db->tableExists('mst_transportasi')) {
            $transportasiList = $db->table('mst_transportasi')->orderBy('nama_transportasi', 'ASC')->get()->getResultArray();
        }

        $mataAnggaranList = [];
        if ($db->tableExists('mst_mata_anggaran')) {
            $mataAnggaranList = $db->table('mst_mata_anggaran')->orderBy('status', 'ASC')->orderBy('id', 'DESC')->get()->getResultArray();
        }

        return view('admin/laporan/surat_tugas', [
            'title' => 'Surat Tugas (SPT)',
            'can_edit' => $this->canManageLaporan(),
            'can_verify' => $this->canVerifyLaporan() || ($currentPegawaiId > 0),
            'kabupaten_options' => $this->loadKabupatenOptions(),
            'pegawai_options' => $pegawaiRows,
            'dasar_spt_options' => $dasarSptOptions,
            'kop_surat_list' => $kopSuratList,
            'mata_anggaran_list' => $mataAnggaranList,
            'transportasi_list' => $transportasiList,
            'last_kode_nomor' => $this->getLastKodeNomorSetting(),
        ]);
    }

    private function perjalananDinasDataTable()
    {
        $canEdit = $this->canManageLaporan();
        $canVerifyLaporan = $this->canVerifyLaporan();
        $role = strtolower((string) session()->get('role'));
        $canUploadVerified = in_array($role, ['keuangan', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);
        $isFullAccessRole = in_array($role, ['keuangan', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);
        
        $pegawaiRows = $this->loadPegawaiOptions();
        $sessionUsername = trim((string) session()->get('username'));
        $sessionFullName = trim((string) (session()->get('fullName') ?: session()->get('name')));

        $currentPegawai = $this->resolveCurrentPegawai($sessionFullName, $pegawaiRows) 
                       ?? $this->resolveCurrentPegawai($sessionUsername, $pegawaiRows);
        $currentPegawaiId = $currentPegawai ? (int) $currentPegawai['id'] : 0;
        
        return $this->respondPerjalananDinasDataTable(
            fn () => db_connect()->table('laporan_perjalanan_dinas')
                ->select('laporan_perjalanan_dinas.*')
                ->join('disposisi_perjalanan_dinas', 'disposisi_perjalanan_dinas.id = laporan_perjalanan_dinas.disposisi_id')
                ->where('disposisi_perjalanan_dinas.status', 'disetujui'),
            function ($builder) use ($isFullAccessRole, $currentPegawaiId, $currentPegawai, $sessionUsername, $sessionFullName): void {
                // If user is not Keuangan or Super Administrator, limit visibility to own data (where user is pelaksana or creator)
                if (! $isFullAccessRole) {
                    $builder->groupStart();
                    $hasCondition = false;
                    
                    if ($currentPegawaiId > 0) {
                        $builder->like('laporan_perjalanan_dinas.pelaksana_json', '"id":' . $currentPegawaiId . ',')
                                ->orLike('laporan_perjalanan_dinas.pelaksana_json', '"id":' . $currentPegawaiId . '}')
                                ->orLike('laporan_perjalanan_dinas.pelaksana_json', '"id":"' . $currentPegawaiId . '"');
                        $hasCondition = true;
                    }
                    if ($currentPegawai && ! empty($currentPegawai['nip'])) {
                        $builder->orLike('laporan_perjalanan_dinas.pelaksana_json', '"nip":"' . $currentPegawai['nip'] . '"')
                                ->orLike('laporan_perjalanan_dinas.pelaksana_json', $currentPegawai['nip']);
                        $hasCondition = true;
                    }
                    if ($sessionUsername !== '') {
                        $builder->orWhere('laporan_perjalanan_dinas.creator_name', $sessionUsername);
                        $hasCondition = true;
                    }
                    if ($sessionFullName !== '') {
                        $builder->orWhere('laporan_perjalanan_dinas.creator_name', $sessionFullName);
                        $hasCondition = true;
                    }

                    if (! $hasCondition) {
                        // Fallback if no matching pegawai or session identity found: return no data
                        $builder->where('1 = 0');
                    }
                    $builder->groupEnd();
                }

                $startDate = trim((string) $this->request->getGet('filter_start_date'));
                $endDate = trim((string) $this->request->getGet('filter_end_date'));
                $statusFilter = trim((string) $this->request->getGet('filter_status'));
                $kota = trim((string) $this->request->getGet('filter_kota'));
                $pelaksanaId = (int) $this->request->getGet('filter_pelaksana');

                if ($startDate !== '') {
                    $builder->where('laporan_perjalanan_dinas.periode_mulai >=', $startDate);
                }
                if ($endDate !== '') {
                    $builder->where('laporan_perjalanan_dinas.periode_selesai <=', $endDate);
                }
                if ($statusFilter !== '') {
                    if ($statusFilter === 'selesai' || $statusFilter === '1') {
                        $builder->where('laporan_perjalanan_dinas.is_final', 1);
                    } elseif ($statusFilter === 'belum' || $statusFilter === '0') {
                        $builder->where('laporan_perjalanan_dinas.is_final', 0);
                    } elseif ($statusFilter === 'terverifikasi') {
                        $builder->where('laporan_perjalanan_dinas.status_verifikasi', 'terverifikasi');
                    } elseif ($statusFilter === 'belum_verifikasi') {
                        $builder->groupStart()
                            ->where('laporan_perjalanan_dinas.status_verifikasi !=', 'terverifikasi')
                            ->orWhere('laporan_perjalanan_dinas.status_verifikasi IS NULL')
                            ->groupEnd();
                    }
                }
                if ($kota !== '') {
                    $builder->where('laporan_perjalanan_dinas.kota_tujuan', $kota);
                }
                if ($pelaksanaId > 0) {
                    $builder->groupStart()
                        ->like('laporan_perjalanan_dinas.pelaksana_json', '"id":' . $pelaksanaId . ',')
                        ->orLike('laporan_perjalanan_dinas.pelaksana_json', '"id":' . $pelaksanaId . '}')
                        ->groupEnd();
                }
            },
            ['laporan_perjalanan_dinas.nomor_surat_tugas', 'laporan_perjalanan_dinas.kota_tujuan', 'laporan_perjalanan_dinas.tujuan', 'laporan_perjalanan_dinas.sasaran', 'laporan_perjalanan_dinas.laporan_hasil', 'laporan_perjalanan_dinas.pelaksana_json', 'laporan_perjalanan_dinas.creator_name'],
            [
                0 => 'laporan_perjalanan_dinas.id',
                1 => 'laporan_perjalanan_dinas.tujuan',
                2 => 'laporan_perjalanan_dinas.kota_tujuan',
                3 => 'laporan_perjalanan_dinas.periode_mulai',
                4 => 'laporan_perjalanan_dinas.id',
                5 => 'laporan_perjalanan_dinas.id',
                6 => 'laporan_perjalanan_dinas.id',
            ],
            function (array $row) use ($canEdit, $canUploadVerified, $canVerifyLaporan, $currentPegawaiId): array {
                $canVerifyRow = $canVerifyLaporan;
                if (! $canVerifyRow && $currentPegawaiId > 0 && ! empty($row['disposisi_id'])) {
                    $disposisi = db_connect()->table('disposisi_perjalanan_dinas')
                        ->select('menyetujui_pegawai_id, diketahui_pegawai_id')
                        ->where('id', $row['disposisi_id'])
                        ->get()
                        ->getRowArray();
                    if ($disposisi) {
                        $ppkId = (int) ($disposisi['menyetujui_pegawai_id'] ?? 0);
                        $satkerId = (int) ($disposisi['diketahui_pegawai_id'] ?? 0);
                        if ($currentPegawaiId === $ppkId || $currentPegawaiId === $satkerId) {
                            $canVerifyRow = true;
                        }
                    }
                }
                $pelaksanaRows = json_decode((string) ($row['pelaksana_json'] ?? '[]'), true);
                if (! is_array($pelaksanaRows)) {
                    $pelaksanaRows = [];
                }

                $pelaksanaNames = array_values(array_filter(array_map(static function ($item): string {
                    if (! is_array($item)) {
                        return '';
                    }
                    return trim((string) ($item['nama'] ?? ''));
                }, $pelaksanaRows), static fn (string $name): bool => $name !== ''));

                if ($pelaksanaNames !== []) {
                    $numberedNames = [];
                    $tooltipNames = [];
                    foreach ($pelaksanaNames as $idx => $pName) {
                        $num = $idx + 1;
                        $numberedNames[] = $num . '. ' . $pName;
                        $tooltipNames[] = $num . '. ' . $pName;
                    }
                    $singleLineText = implode(', ', $numberedNames);
                    $tooltipText = implode('<br>', $tooltipNames);
                    $row['pelaksana_names_html'] = '<div class="pelaksana-single-line" data-toggle="tooltip" data-html="true" data-placement="top" title="' . esc($tooltipText, 'attr') . '">' . esc($singleLineText) . '</div>';
                } else {
                    $row['pelaksana_names_html'] = '-';
                }

                $periodeMulai = trim((string) ($row['periode_mulai'] ?? ''));
                $periodeSelesai = trim((string) ($row['periode_selesai'] ?? ''));
                $periodeLabel = trim($periodeMulai . ' s.d ' . $periodeSelesai);
                if ($periodeMulai === '' && $periodeSelesai === '') {
                    $periodeLabel = '-';
                }

                $row['periode'] = $periodeLabel;
                $row['pelaksana_names_label'] = implode(', ', $pelaksanaNames) ?: '-';

                $isFinal = (int) ($row['is_final'] ?? 0);
                $row['is_final'] = $isFinal;

                // Differentiate Laporan Perjadin (dynamic PDF), Verified SPT, and Dokumen Pendukung
                $dokumenHtml = '<div class="doc-btn-group">';

                // 1. Uploaded Signed SPT / Verified File (always show if uploaded)
                if (! empty($row['verified_spt_path'])) {
                    $verifiedUrl = media_url($row['verified_spt_path']);
                    $ext = strtolower(pathinfo((string) $row['verified_spt_path'], PATHINFO_EXTENSION));
                    $icon = in_array($ext, ['jpg', 'jpeg', 'png'], true) ? 'fa-file-image' : 'fa-file-signature';
                    $btnClass = in_array($ext, ['jpg', 'jpeg', 'png'], true) ? 'btn-warning text-white' : 'btn-success';
                    $titleLabel = in_array($ext, ['jpg', 'jpeg', 'png'], true) ? 'Lihat Foto Verified SPT/Perjadin' : 'Unduh Verified SPT (PDF)';
                    $dokumenHtml .= '<a href="' . $verifiedUrl . '" class="btn ' . $btnClass . '" title="' . $titleLabel . '" target="_blank" rel="noopener noreferrer"><i class="fas ' . $icon . '"></i></a>';
                }

                // 2. Dynamic Laporan Perjadin PDF (if final)
                if ($isFinal === 1) {
                    $dokumenHtml .= '<a href="' . site_url('admin/surat/perjalanan-dinas/' . (int) ($row['id'] ?? 0) . '/dokumen') . '" class="btn btn-danger" title="Laporan Perjadin (PDF)" target="_blank" rel="noopener noreferrer"><i class="fas fa-file-pdf"></i></a>';
                }

                // 3. Supporting Documents (dokumen_pendukung_json)
                $docs = json_decode((string) ($row['dokumen_pendukung_json'] ?? '[]'), true);
                if (is_array($docs) && $docs !== []) {
                    foreach ($docs as $doc) {
                        $filePath = trim((string) ($doc['file_path'] ?? ''));
                        $fileName = trim((string) ($doc['name'] ?? 'File'));
                        if ($filePath !== '') {
                            $docUrl = media_url($filePath);
                            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

                            $icon = 'fa-paperclip';
                            $btnClass = 'btn-info';
                            if ($ext === 'pdf') {
                                $icon = 'fa-file-pdf';
                                $btnClass = 'btn-danger';
                            } elseif (in_array($ext, ['doc', 'docx'], true)) {
                                $icon = 'fa-file-word';
                                $btnClass = 'btn-primary';
                            } elseif (in_array($ext, ['xls', 'xlsx'], true)) {
                                $icon = 'fa-file-excel';
                                $btnClass = 'btn-success';
                            } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'], true)) {
                                $icon = 'fa-file-image';
                                $btnClass = 'btn-warning text-white';
                            }

                            $keterangan = trim((string) ($doc['keterangan'] ?? ''));
                            $titleLabel = 'File Pendukung: ' . $fileName;
                            if ($keterangan !== '') {
                                $titleLabel .= ' - ' . $keterangan;
                            }
                            $dokumenHtml .= '<a href="' . $docUrl . '" class="btn ' . $btnClass . '" title="' . esc($titleLabel, 'attr') . '" target="_blank" rel="noopener noreferrer"><i class="fas ' . $icon . '"></i></a>';
                        }
                    }
                }

                if (empty($row['verified_spt_path']) && $isFinal === 0 && (empty($docs) || ! is_array($docs))) {
                    $dokumenHtml .= '<span class="badge badge-warning py-1 px-2 font-weight-bold" style="font-size:0.75rem;"><i class="fas fa-hourglass-start mr-1"></i> Belum Selesai</span>';
                }
                $dokumenHtml .= '</div>';
                
                $row['dokumen_html'] = $dokumenHtml;

                // Dokumen SPT (Upload SPT TTD file from Surat Tugas)
                if (! empty($row['verified_spt_path'])) {
                    $verifiedUrl = media_url($row['verified_spt_path']);
                    $ext = strtolower(pathinfo((string) $row['verified_spt_path'], PATHINFO_EXTENSION));
                    $icon = in_array($ext, ['jpg', 'jpeg', 'png'], true) ? 'fa-file-image' : 'fa-file-pdf';
                    $btnClass = in_array($ext, ['jpg', 'jpeg', 'png'], true) ? 'btn-warning text-white' : 'btn-success text-white';
                    $dokumenSptHtml = '<a href="' . $verifiedUrl . '" class="btn btn-sm ' . $btnClass . ' shadow-sm font-weight-bold" target="_blank" rel="noopener noreferrer" title="Unduh / Lihat Dokumen SPT (TTD)"><i class="fas ' . $icon . ' mr-1"></i> Unduh SPT</a>';
                } else {
                    $dokumenSptHtml = '<span class="badge badge-light text-muted border px-2 py-1" style="font-size: 0.8rem; font-weight: normal;"><i class="fas fa-minus mr-1"></i> Belum Upload</span>';
                }
                $row['dokumen_spt_html'] = $dokumenSptHtml;
                
                // Upload SPT TTD (PDF) column rendering
                $existingVerifiedFile = ! empty($row['verified_spt_path']) ? esc((string) $row['verified_spt_path'], 'attr') : '';
                $nomorSuratAttr = esc((string) ($row['nomor_surat_tugas'] ?? '-'), 'attr');
                $uploadVerifiedUrl = site_url('admin/surat/perjalanan-dinas/' . (int) ($row['id'] ?? 0) . '/upload-verified');

                if ($existingVerifiedFile !== '') {
                    $verifiedUrl = media_url($row['verified_spt_path']);
                    $uploadSptTtdHtml = '<div class="btn-group btn-group-sm d-inline-flex" role="group">' .
                        '<a href="' . $verifiedUrl . '" class="btn btn-xs btn-success text-white btn-table-action shadow-sm" target="_blank" rel="noopener noreferrer" title="Lihat SPT TTD (PDF)"><i class="fas fa-file-pdf mr-1"></i> Lihat PDF</a>' .
                        '<button type="button" class="btn btn-xs btn-outline-primary btn-upload-spt-pdf" data-url="' . $uploadVerifiedUrl . '" data-nomor="' . $nomorSuratAttr . '" title="Upload Ulang SPT TTD (PDF)" style="height:38px;"><i class="fas fa-sync-alt"></i></button>' .
                        '</div>';
                } else {
                    $uploadSptTtdHtml = '<button type="button" class="btn btn-xs btn-primary btn-upload-spt-pdf btn-table-action shadow-sm" data-url="' . $uploadVerifiedUrl . '" data-nomor="' . $nomorSuratAttr . '" title="Upload SPT TTD (Wajib PDF)"><i class="fas fa-upload mr-1"></i> Upload PDF</button>';
                }
                $row['upload_spt_ttd_html'] = $uploadSptTtdHtml;
                $row['upload_verified_html'] = $uploadSptTtdHtml;
                
                if ($canEdit) {
                    $row['action_html'] = '<div class="d-flex justify-content-center align-items-center" style="gap: 5px; white-space: nowrap;">';
                    $row['action_html'] .= '<a href="javascript:void(0)" onclick="openEditModal(\'' . site_url('admin/surat/perjalanan-dinas/' . (int) ($row['id'] ?? 0) . '/ubah') . '?modal=1\')" class="btn btn-sm btn-outline-primary" title="Ubah Data"><i class="fas fa-pen"></i></a>';
                    $row['action_html'] .= '</div>';
                } else {
                    $row['action_html'] = '<span class="text-muted">-</span>';
                }

                // Verification column cell HTML
                $isVerified = (int) ($row['is_verified'] ?? 0);
                $dasarSptIds = json_decode((string) ($row['dasar_spt_ids_json'] ?? '[]'), true) ?: [];
                $tglTtd = trim((string) ($row['tanggal_tanda_tangan'] ?? ''));
                $nomorSurat = trim((string) ($row['nomor_surat_tugas'] ?? ''));

                $dasarTexts = [];
                if ($dasarSptIds !== []) {
                    $numericIds = [];
                    $customTexts = [];
                    foreach ($dasarSptIds as $val) {
                        if (is_numeric($val)) {
                            $numericIds[] = (int) $val;
                        } else {
                            $customTexts[] = (string) $val;
                        }
                    }

                    if ($numericIds !== []) {
                        $dbDasar = (new \App\Models\MstDasarSptModel())
                            ->whereIn('id', $numericIds)
                            ->orderBy('id', 'ASC')
                            ->findAll();
                        foreach ($dbDasar as $dbD) {
                            $dasarTexts[] = $dbD['uraian'];
                        }
                    }

                    foreach ($customTexts as $textVal) {
                        $dasarTexts[] = $textVal;
                    }
                }

                if ($dasarTexts === []) {
                    $allMasterDasar = (new \App\Models\MstDasarSptModel())->orderBy('id', 'ASC')->findAll();
                    foreach ($allMasterDasar as $mD) {
                        if (! empty($mD['uraian'])) {
                            $dasarTexts[] = $mD['uraian'];
                        }
                    }
                }

                $statusVerifikasiHtml = '';
                if ($isFinal === 0) {
                    $statusVerifikasiHtml = '<span class="badge badge-secondary px-2 py-1 shadow-sm" style="font-size:0.78rem;"><i class="fas fa-hourglass-start mr-1"></i> Belum Selesai</span>';
                } elseif ($isVerified === 1) {
                    $formattedTtd = $tglTtd !== '' ? tanggal_indonesia($tglTtd) : '-';
                    $badgeTitle = "Nomor: " . esc($nomorSurat) . "\nTanggal: " . $formattedTtd;
                    $statusVerifikasiHtml = '<span class="badge badge-success px-2 py-1 shadow-sm" style="font-size:0.78rem; cursor:pointer;" title="' . esc($badgeTitle, 'attr') . '"><i class="fas fa-check-circle mr-1"></i> Terverifikasi</span>';
                } else {
                    $statusVerifikasiHtml = '<span class="badge badge-warning px-2 py-1 shadow-sm text-dark" style="font-size:0.78rem;"><i class="fas fa-clock mr-1"></i> Belum Verifikasi</span>';
                }

                $fileSptHtml = '<a href="' . site_url('admin/surat/perjalanan-dinas/' . (int) $row['id'] . '/cetak-spt') . '" class="btn btn-xs btn-danger text-white btn-table-action shadow-sm" title="Cetak Surat Tugas (PDF)" target="_blank"><i class="fas fa-file-pdf mr-1"></i> Cetak SPT</a>';

                $kopSuratIdAttr = (int) ($row['kop_surat_id'] ?? 0);
                $mataAnggaranIdAttr = (int) ($row['mata_anggaran_id'] ?? 0);
                $rincianBiayaAttr = esc((string) ($row['rincian_biaya_json'] ?? '{}'), 'attr');
                $kodeNomorAttr = esc((string) ($row['kode_nomor'] ?? ''), 'attr');
                $aksiSptHtml = '';
                $updateButtonHtml = '';
                if ($canVerifyRow) {
                    $dasarAttr = esc(json_encode($dasarTexts), 'attr');
                    $tglAttr = $tglTtd !== '' ? esc($tglTtd, 'attr') : date('Y-m-d');
                    $kotaTujuanAttr = esc((string) ($row['kota_tujuan'] ?? ''), 'attr');
                    $tujuanAttr = esc((string) ($row['tujuan'] ?? ''), 'attr');
                    $periodeAttr = esc((string) ($row['periode'] ?? ''), 'attr');
                    $tglMulaiAttr = esc((string) ($row['periode_mulai'] ?? ''), 'attr');
                    $tglSelesaiAttr = esc((string) ($row['periode_selesai'] ?? ''), 'attr');
                    $biayaMasterRow = $this->getBiayaMasterForKota($row['kota_tujuan'] ?? '');
                    $defHarian = $biayaMasterRow['harian'] ?? 0;
                    $defPenginapan = (int)(($biayaMasterRow['penginapan_e4'] ?? 0) * 0.3);
                    $pelaksanaAttr = esc((string) ($row['pelaksana_names_label'] ?? ''), 'attr');
                    $updateButtonHtml = '<button type="button" class="btn btn-xs btn-warning text-dark btn-verify-spt btn-table-action shadow-sm" data-id="' . (int) $row['id'] . '" data-nomor="' . esc($nomorSurat, 'attr') . '" data-kode-nomor="' . $kodeNomorAttr . '" data-dasar="' . $dasarAttr . '" data-tgl="' . $tglAttr . '" data-kop-surat-id="' . $kopSuratIdAttr . '" data-mata-anggaran-id="' . $mataAnggaranIdAttr . '" data-rincian-biaya="' . $rincianBiayaAttr . '" data-kota="' . $kotaTujuanAttr . '" data-tujuan="' . $tujuanAttr . '" data-periode="' . $periodeAttr . '" data-tgl-mulai="' . $tglMulaiAttr . '" data-tgl-selesai="' . $tglSelesaiAttr . '" data-def-harian="' . $defHarian . '" data-def-penginapan="' . $defPenginapan . '" data-pelaksana="' . $pelaksanaAttr . '" title="Update Verifikasi"><i class="fas fa-edit mr-1"></i> Update</button>';
                    
                    $aksiSptHtml = $updateButtonHtml;
                } else {
                    $aksiSptHtml = '<span class="text-muted" style="font-size:0.8rem;"><i class="fas fa-lock mr-1"></i> No Access</span>';
                }

                $verificationStatusHtml = $statusVerifikasiHtml . '<div class="mt-1 d-flex justify-content-center align-items-center" style="gap: 4px;">' . $fileSptHtml;
                if ($canVerifyRow) {
                    $verificationStatusHtml .= $updateButtonHtml;
                }
                $verificationStatusHtml .= '</div>';

                $row['verification_status_html'] = $verificationStatusHtml;
                $row['status_verifikasi_html'] = $statusVerifikasiHtml;
                $row['file_spt_html'] = $fileSptHtml;
                
                $row['daftar_nominatif_html'] = '<a href="' . site_url('admin/surat/perjalanan-dinas/' . (int) $row['id'] . '/cetak-daftar-nominatif') . '" class="btn btn-xs btn-success text-white btn-table-action shadow-sm" title="Cetak Daftar Nominatif (PDF)" target="_blank"><i class="fas fa-file-pdf mr-1"></i> Cetak Nominatif</a>';
                
                $row['sppd_html'] = '<a href="' . site_url('admin/surat/perjalanan-dinas/' . (int) $row['id'] . '/cetak-sppd') . '" class="btn btn-xs btn-primary text-white btn-table-action shadow-sm" title="Cetak SPPD (PDF)" target="_blank"><i class="fas fa-file-pdf mr-1"></i> Cetak SPPD</a>';
                
                $row['kwitansi_html'] = '<a href="' . site_url('admin/surat/perjalanan-dinas/' . (int) $row['id'] . '/cetak-kwitansi') . '" class="btn btn-xs btn-info text-white btn-table-action shadow-sm" title="Cetak Kwitansi (Excel)" target="_blank"><i class="fas fa-file-excel mr-1"></i> Cetak Kwitansi</a>';
                
                $row['aksi_spt_html'] = $aksiSptHtml;

                return $row;
            }
        );
    }

    public function perjalananDinasVerify(int $id)
    {
        $model = new LaporanPerjalananDinasModel();
        $row = $model->find($id);
        if (! is_array($row)) {
            if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data laporan tidak ditemukan.']);
        }
        return redirect()->back()->with('error', 'Data laporan tidak ditemukan.');
        }

        $role = strtolower((string) session()->get('role'));
        $isSuperAdmin = in_array($role, ['super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);
        
        $pegawaiRows = $this->loadPegawaiOptions();
        $currentPegawai = $this->resolveCurrentPegawai(
            (string) (session()->get('fullName') ?: session()->get('username') ?: session()->get('name')), 
            $pegawaiRows
        );
        $currentPegawaiId = $currentPegawai ? (int) $currentPegawai['id'] : 0;

        $canVerifyRow = $this->canVerifyLaporan();
        if (! $canVerifyRow && $currentPegawaiId > 0 && ! empty($row['disposisi_id'])) {
            $disposisi = db_connect()->table('disposisi_perjalanan_dinas')
                ->select('menyetujui_pegawai_id, diketahui_pegawai_id')
                ->where('id', $row['disposisi_id'])
                ->get()
                ->getRowArray();
            if ($disposisi) {
                $ppkId = (int) ($disposisi['menyetujui_pegawai_id'] ?? 0);
                $satkerId = (int) ($disposisi['diketahui_pegawai_id'] ?? 0);
                if ($currentPegawaiId === $ppkId || $currentPegawaiId === $satkerId) {
                    $canVerifyRow = true;
                }
            }
        }

        if (! $canVerifyRow) {
            if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Anda tidak memiliki hak akses untuk memverifikasi laporan perjalanan dinas.']);
        }
        return redirect()->back()->with('error', 'Anda tidak memiliki hak akses untuk memverifikasi laporan perjalanan dinas.');
        }

        $nomorSurat = trim((string) $this->request->getPost('nomor_surat_tugas'));
        $dasarInputs = $this->request->getPost('dasar_spt') ?: [];
        if (is_array($dasarInputs)) {
            $dasarInputs = array_values(array_filter(array_map('trim', $dasarInputs), static fn($val) => $val !== ''));
        } else {
            $dasarInputs = [];
        }
        $tglTtd = trim((string) $this->request->getPost('tanggal_tanda_tangan'));
        $kopSuratId = (int) $this->request->getPost('kop_surat_id');
        
        $mataAnggaranInput = trim((string) $this->request->getPost('mata_anggaran_id'));
        $mataAnggaranId = (int) $mataAnggaranInput;

        if ($mataAnggaranInput !== '' && (string) $mataAnggaranId !== $mataAnggaranInput) {
            $db = db_connect();
            $db->table('mst_mata_anggaran')->insert([
                'mata_anggaran' => $mataAnggaranInput,
                'status'        => 'Aktif',
                'created_by'    => (string) (session()->get('username') ?: 'admin'),
                'created_date'  => date('Y-m-d H:i:s'),
            ]);
            $mataAnggaranId = $db->insertID();
        }

        if ($nomorSurat === '' && ($this->request->getPost('tab_action') === 'all' || $this->request->getPost('tab_action') === 'tab1')) {
            if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Nomor Surat Tugas wajib diisi.']);
        }
        return redirect()->back()->with('error', 'Nomor Surat Tugas wajib diisi.');
        }

        if ($tglTtd === '' && ($this->request->getPost('tab_action') === 'all' || $this->request->getPost('tab_action') === 'tab1')) {
            if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tanggal tanda tangan wajib diisi.']);
        }
        return redirect()->back()->with('error', 'Tanggal tanda tangan wajib diisi.');
        }

        $uangHarianStarts   = $this->request->getPost('uang_harian_start_date') ?: [];
        $uangHarianEnds     = $this->request->getPost('uang_harian_end_date') ?: [];
        $uangHarianNominals = $this->request->getPost('uang_harian_nominal') ?: [];
        $uangHarianKets     = $this->request->getPost('uang_harian_ket') ?: [];

        $uangHarianList = [];
        if (is_array($uangHarianStarts)) {
            foreach ($uangHarianStarts as $idx => $uStart) {
                $uStart = trim((string) $uStart);
                $uEnd   = trim((string) ($uangHarianEnds[$idx] ?? ''));
                $uNomRaw = preg_replace('/\D/', '', (string) ($uangHarianNominals[$idx] ?? ''));
                $uNom   = $uNomRaw !== '' ? (int) $uNomRaw : 0;
                $uKet   = trim((string) ($uangHarianKets[$idx] ?? ''));
                if ($uStart !== '' || $uEnd !== '' || $uNom > 0 || $uKet !== '') {
                    $uangHarianList[] = [
                        'tgl_mulai'   => $uStart,
                        'tgl_selesai' => $uEnd,
                        'nominal'     => $uNom,
                        'keterangan'  => $uKet,
                    ];
                }
            }
        }

        $transportStarts   = $this->request->getPost('transport_start_date') ?: [];
        $transportEnds     = $this->request->getPost('transport_end_date') ?: [];
        $transportNominals = $this->request->getPost('transport_nominal') ?: [];
        $transportKets     = $this->request->getPost('transport_ket') ?: [];
        $transportJenis    = $this->request->getPost('transport_jenis') ?: [];
        $transportLumpsum  = $this->request->getPost('transport_is_lumpsum') ?: [];

        $transportList = [];
        if (is_array($transportStarts)) {
            foreach ($transportStarts as $idx => $tStart) {
                $tStart = trim((string) $tStart);
                $tEnd   = trim((string) ($transportEnds[$idx] ?? ''));
                $tNomRaw = preg_replace('/\D/', '', (string) ($transportNominals[$idx] ?? ''));
                $tNom   = $tNomRaw !== '' ? (int) $tNomRaw : 0;
                $tKet   = trim((string) ($transportKets[$idx] ?? ''));
                $tJenis = trim((string) ($transportJenis[$idx] ?? ''));
                $tIsLumpsum = !empty($transportLumpsum[$idx]) ? true : false;
                
                if ($tStart !== '' || $tEnd !== '' || $tNom > 0 || $tKet !== '' || $tJenis !== '') {
                    $transportList[] = [
                        'tgl_mulai'   => $tStart,
                        'tgl_selesai' => $tEnd,
                        'nominal'     => $tNom,
                        'jenis'       => $tJenis,
                        'keterangan'  => $tKet,
                        'is_lumpsum'  => $tIsLumpsum,
                    ];
                }
            }
        }

        $penginapanStarts   = $this->request->getPost('penginapan_start_date') ?: [];
        $penginapanEnds     = $this->request->getPost('penginapan_end_date') ?: [];
        $penginapanNominals = $this->request->getPost('penginapan_nominal') ?: [];
        $penginapanKets     = $this->request->getPost('penginapan_ket') ?: [];

        $penginapanList = [];
        if (is_array($penginapanStarts)) {
            foreach ($penginapanStarts as $idx => $pStart) {
                $pStart = trim((string) $pStart);
                $pEnd   = trim((string) ($penginapanEnds[$idx] ?? ''));
                $pNomRaw = isset($penginapanNominals[$idx]) ? preg_replace('/\D/', '', (string) $penginapanNominals[$idx]) : '';
                $pNomVal = $pNomRaw !== '' ? (int) $pNomRaw : null;
                $pKet   = trim((string) ($penginapanKets[$idx] ?? ''));
                if ($pStart !== '' || $pEnd !== '' || $pNomVal !== null || $pKet !== '') {
                    $penginapanList[] = [
                        'tgl_mulai'   => $pStart,
                        'tgl_selesai' => $pEnd,
                        'nominal'     => $pNomVal,
                        'keterangan'  => $pKet,
                    ];
                }
            }
        }

        $rincianBiaya = [
            'uang_harian' => $uangHarianList,
            'transport'   => $transportList,
            'penginapan'  => $penginapanList,
        ];

        $kodeNomorInput = trim((string) $this->request->getPost('kode_nomor'));

        $updateData = ['is_verified' => 1];
        $targetTab = $this->request->getPost('tab_action') ?: 'all';
        
        if ($targetTab === 'all' || $targetTab === 'tab1') {
            $updateData['nomor_surat_tugas'] = $nomorSurat;
            $updateData['dasar_spt_ids_json'] = json_encode($dasarInputs, JSON_UNESCAPED_UNICODE);
            $updateData['tanggal_tanda_tangan'] = $tglTtd;
        }
        
        if ($targetTab === 'all' || $targetTab === 'tab2') {
            $updateData['rincian_biaya_json'] = json_encode($rincianBiaya, JSON_UNESCAPED_UNICODE);
        }
        if ($kopSuratId > 0) {
            $updateData['kop_surat_id'] = $kopSuratId;
        }
        if ($mataAnggaranId > 0) {
            $updateData['mata_anggaran_id'] = $mataAnggaranId;
        }

        $this->ensureKodeNomorAssigned($row, $kodeNomorInput);
        $updateData['kode_nomor'] = $row['kode_nomor'];

        $model->update($id, $updateData);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Surat Tugas (SPT) berhasil diverifikasi.']);
        }
        return redirect()->back()->with('success', 'Surat Tugas (SPT) berhasil diverifikasi.');
    }

    public function perjalananDinasCetakSpt(int $id)
    {
        if (! $this->canViewLaporan()) {
            return redirect()->to(site_url('/admin'));
        }

        $row = (new LaporanPerjalananDinasModel())->find($id);
        if (! is_array($row)) {
            return redirect()->to(site_url('admin/surat/perjalanan-dinas'))->with('error', 'Data laporan tidak ditemukan.');
        }

        $dasarIds = json_decode((string) ($row['dasar_spt_ids_json'] ?? '[]'), true) ?: [];
        $dasarRows = [];
        if ($dasarIds !== []) {
            $numericIds = [];
            $customTexts = [];
            foreach ($dasarIds as $val) {
                if (is_numeric($val)) {
                    $numericIds[] = (int) $val;
                } else {
                    $customTexts[] = (string) $val;
                }
            }

            if ($numericIds !== []) {
                $dasarRows = (new \App\Models\MstDasarSptModel())
                    ->whereIn('id', $numericIds)
                    ->orderBy('id', 'ASC')
                    ->findAll();
            }

            foreach ($customTexts as $textVal) {
                $dasarRows[] = [
                    'id' => 0,
                    'uraian' => $textVal
                ];
            }
        }

        $html = $this->renderSuratTugasHtml($row, $dasarRows);

        $dompdfOptions = new \Dompdf\Options();
        $dompdfOptions->set('isRemoteEnabled', true);
        $dompdfOptions->set('isHtml5ParserEnabled', true);

        $dompdf = new \Dompdf\Dompdf($dompdfOptions);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="surat_tugas_' . $id . '.pdf"')
            ->setBody($dompdf->output());
    }

    public function perjalananDinasCetakDaftarNominatif(int $id)
    {
        if (! $this->canViewLaporan()) {
            return redirect()->to(site_url('/admin'));
        }

        $row = (new LaporanPerjalananDinasModel())->find($id);
        if (! is_array($row)) {
            return redirect()->to(site_url('admin/surat/perjalanan-dinas'))->with('error', 'Data laporan tidak ditemukan.');
        }

        $pelaksana = $this->sortPelaksanaByStrukturOrganisasi(json_decode((string) ($row['pelaksana_json'] ?? '[]'), true) ?: []);

        $db = \Config\Database::connect();
        if (! empty($pelaksana) && $db->tableExists('mst_pegawai')) {
            $pegawaiIds = array_filter(array_column($pelaksana, 'id'));
            if (! empty($pegawaiIds)) {
                $pegawaiDb = $db->table('mst_pegawai')
                    ->select('id, golongan')
                    ->whereIn('id', $pegawaiIds)
                    ->get()->getResultArray();
                $golMap = [];
                foreach ($pegawaiDb as $pDb) {
                    $golMap[(int) $pDb['id']] = (string) ($pDb['golongan'] ?? '');
                }
                foreach ($pelaksana as &$pItem) {
                    $pid = (int) ($pItem['id'] ?? 0);
                    if (isset($golMap[$pid]) && $golMap[$pid] !== '') {
                        $pItem['golongan'] = $golMap[$pid];
                    }
                }
                unset($pItem);
            }
        }
        $kotaTujuan = $row['kota_tujuan'] ?? '';
        $biayaMaster = $this->getBiayaMasterForKota($kotaTujuan);

        $mataAnggaranText = $this->resolveMataAnggaran($row);

        $html = view('admin/laporan/cetak_daftar_nominatif', [
            'row' => $row,
            'pelaksana' => $pelaksana,
            'biaya_master' => $biayaMaster,
            'mata_anggaran' => $mataAnggaranText,
        ]);

        $dompdfOptions = new \Dompdf\Options();
        $dompdfOptions->set('isRemoteEnabled', true);
        $dompdfOptions->set('isHtml5ParserEnabled', true);

        $dompdf = new \Dompdf\Dompdf($dompdfOptions);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="daftar_nominatif_' . $id . '.pdf"')
            ->setBody($dompdf->output());
    }

    public function perjalananDinasCetakSppd(int $id)
    {
        if (! $this->canViewLaporan()) {
            return redirect()->to(site_url('/admin'));
        }

        $row = (new LaporanPerjalananDinasModel())->find($id);
        if (! is_array($row)) {
            return redirect()->to(site_url('admin/surat/perjalanan-dinas'))->with('error', 'Data laporan tidak ditemukan.');
        }

        $this->ensureKodeNomorAssigned($row);

        $pelaksana = $this->sortPelaksanaByStrukturOrganisasi(json_decode((string) ($row['pelaksana_json'] ?? '[]'), true) ?: []);
        $tujuan = $row['tujuan'] ?? '';
        $kotaTujuan = $row['kota_tujuan'] ?? '';
        $periodeMulai = $row['periode_mulai'] ?? '';
        $periodeSelesai = $row['periode_selesai'] ?? '';

        $db = \Config\Database::connect();
        if (! empty($row['disposisi_id']) && $db->tableExists('disposisi_perjalanan_dinas')) {
            $disposisiRow = $db->table('disposisi_perjalanan_dinas')
                ->select('perihal, transportasi')
                ->where('id', $row['disposisi_id'])
                ->get()
                ->getRowArray();
            if (is_array($disposisiRow)) {
                if (! empty($disposisiRow['perihal'])) {
                    $row['perihal_disposisi'] = $disposisiRow['perihal'];
                }
                if (! empty($disposisiRow['transportasi'])) {
                    $row['transportasi'] = $disposisiRow['transportasi'];
                }
            }
        }

        if (! empty($pelaksana) && $db->tableExists('mst_pegawai')) {
            $pegawaiIds = array_filter(array_column($pelaksana, 'id'));
            if (! empty($pegawaiIds)) {
                $pegawaiDb = $db->table('mst_pegawai')
                    ->select('id, golongan, jenis_pegawai')
                    ->whereIn('id', $pegawaiIds)
                    ->get()->getResultArray();
                $pegMap = [];
                foreach ($pegawaiDb as $pDb) {
                    $pegMap[(int) $pDb['id']] = $pDb;
                }
                foreach ($pelaksana as &$pItem) {
                    $pid = (int) ($pItem['id'] ?? 0);
                    if (isset($pegMap[$pid])) {
                        if (empty($pItem['golongan']) && ! empty($pegMap[$pid]['golongan'])) {
                            $pItem['golongan'] = $pegMap[$pid]['golongan'];
                        }
                        if (empty($pItem['jenis_pegawai']) && ! empty($pegMap[$pid]['jenis_pegawai'])) {
                            $pItem['jenis_pegawai'] = $pegMap[$pid]['jenis_pegawai'];
                        }
                    }
                }
                unset($pItem);
            }
        }

        // Fetch kop_surat
        $db = \Config\Database::connect();
        $kopSuratId = (int) ($row['kop_surat_id'] ?? 0);
        $kopSurat = null;
        if ($kopSuratId > 0 && $db->tableExists('kop_surat')) {
            $kopSurat = $db->table('kop_surat')->where('id', $kopSuratId)->get()->getRowArray();
        }
        if (!$kopSurat && $db->tableExists('kop_surat')) {
            $kopSurat = $db->table('kop_surat')->where('is_active', 1)->orderBy('id', 'DESC')->get()->getRowArray();
        }

        $mataAnggaranText = $this->resolveMataAnggaran($row);

        $html = view('admin/laporan/cetak_sppd', [
            'row' => $row,
            'pelaksana' => $pelaksana,
            'kop_surat' => $kopSurat,
            'mata_anggaran' => $mataAnggaranText,
        ]);

        $dompdfOptions = new \Dompdf\Options();
        $dompdfOptions->set('isRemoteEnabled', true);
        $dompdfOptions->set('isHtml5ParserEnabled', true);

        $dompdf = new \Dompdf\Dompdf($dompdfOptions);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="sppd_' . $id . '.pdf"')
            ->setBody($dompdf->output());
    }

    public function perjalananDinasCetakKwitansi(int $id)
    {
        if (! $this->canViewLaporan()) {
            return redirect()->to(site_url('/admin'));
        }

        $row = (new LaporanPerjalananDinasModel())->find($id);
        if (! is_array($row)) {
            return redirect()->to(site_url('admin/surat/perjalanan-dinas'))->with('error', 'Data laporan tidak ditemukan.');
        }

        $this->ensureKodeNomorAssigned($row);

        $pelaksana = $this->sortPelaksanaByStrukturOrganisasi(json_decode((string) ($row['pelaksana_json'] ?? '[]'), true) ?: []);
        $kotaTujuan = $row['kota_tujuan'] ?? '';

        // Fetch kop_surat
        $db = \Config\Database::connect();
        $kopSuratId = (int) ($row['kop_surat_id'] ?? 0);
        $kopSurat = null;
        if ($kopSuratId > 0 && $db->tableExists('kop_surat')) {
            $kopSurat = $db->table('kop_surat')->where('id', $kopSuratId)->get()->getRowArray();
        }
        if (! $kopSurat && $db->tableExists('kop_surat')) {
            $kopSurat = $db->table('kop_surat')->where('is_active', 1)->orderBy('id', 'DESC')->get()->getRowArray();
        }

        $biayaMaster = $this->getBiayaMasterForKota($kotaTujuan);
        $mataAnggaranText = $this->resolveMataAnggaran($row);

        $isDownload = ($this->request->getGet('download') === '1' || $this->request->getGet('export') === 'excel');

        if ($isDownload) {
            $spreadsheet = $this->buildKwitansiSpreadsheet($row, $pelaksana, $biayaMaster, $mataAnggaranText, $kopSurat);

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
            
            ob_start();
            $writer->save('php://output');
            $excelData = ob_get_clean();

            return $this->response
                ->setHeader('Content-Type', 'application/vnd.ms-excel')
                ->setHeader('Content-Disposition', 'attachment; filename="kwitansi_' . $id . '.xls"')
                ->setHeader('Cache-Control', 'max-age=0')
                ->setBody($excelData);
        }

        // Return HTML Preview Mode in Browser
        $formatDateIndo = function ($dateStr, $leadingZero = true) {
            if (empty($dateStr)) return '-';
            $ts = strtotime($dateStr);
            if (!$ts) return $dateStr;
            $months = [
                1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
                5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',
                9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
            ];
            $dayFmt = $leadingZero ? 'd' : 'j';
            return date($dayFmt, $ts) . ' ' . $months[(int)date('n', $ts)] . ' ' . date('Y', $ts);
        };

        $formatNip = function ($nip) {
            $nip = preg_replace('/\s+/', '', (string)$nip);
            if (strlen($nip) === 18) {
                return substr($nip,0,8).' '.substr($nip,8,6).' '.substr($nip,14,1).' '.substr($nip,15,3);
            }
            return $nip;
        };

        $tglBerangkat = $formatDateIndo($row['periode_mulai'] ?? '', false);
        $tglKembali   = $formatDateIndo($row['periode_selesai'] ?? '', false);

        $days = 0;
        if (!empty($row['periode_mulai']) && !empty($row['periode_selesai'])) {
            try {
                $start = new \DateTime($row['periode_mulai']);
                $end   = new \DateTime($row['periode_selesai']);
                $days  = $start->diff($end)->days + 1;
            } catch (\Throwable $e) {}
        }

        $tanggalTtdRaw = !empty($row['tanggal_tanda_tangan'])
            ? (string)$row['tanggal_tanda_tangan']
            : '';
        if (empty($tanggalTtdRaw) && !empty($row['disposisi_id'])) {
            $dbDisp = \Config\Database::connect();
            if ($dbDisp->tableExists('disposisi_perjalanan_dinas')) {
                $dispRow = $dbDisp->table('disposisi_perjalanan_dinas')->select('created_at')->where('id', $row['disposisi_id'])->get()->getRowArray();
                if (!empty($dispRow['created_at'])) {
                    $tanggalTtdRaw = date('Y-m-d', strtotime($dispRow['created_at']));
                }
            }
        }
        if (empty($tanggalTtdRaw) && !empty($row['created_at'])) {
            $tanggalTtdRaw = date('Y-m-d', strtotime($row['created_at']));
        }
        if (empty($tanggalTtdRaw)) {
            $tanggalTtdRaw = date('Y-m-d');
        }

        $tanggalTtdUpper = strtoupper($formatDateIndo($tanggalTtdRaw, true));
        
        $tglDisposisiRaw = '';
        if (!empty($row['disposisi_id'])) {
            $dbDisp = \Config\Database::connect();
            if ($dbDisp->tableExists('disposisi_perjalanan_dinas')) {
                $dispRow = $dbDisp->table('disposisi_perjalanan_dinas')->select('created_at')->where('id', $row['disposisi_id'])->get()->getRowArray();
                if (!empty($dispRow['created_at'])) {
                    $tglDisposisiRaw = date('Y-m-d', strtotime($dispRow['created_at']));
                }
            }
        }
        if (empty($tglDisposisiRaw) && !empty($row['created_at'])) {
            $tglDisposisiRaw = date('Y-m-d', strtotime($row['created_at']));
        }
        if (empty($tglDisposisiRaw)) {
            $tglDisposisiRaw = date('Y-m-d');
        }

        $tsDisp = strtotime($tglDisposisiRaw) ?: time();
        $tsTtd  = strtotime($tanggalTtdRaw) ?: time();
        $monthsIndo = [
            1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
            5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',
            9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
        ];
        $bulanTahunStr = $monthsIndo[(int)date('n', $tsDisp)] . ' ' . date('Y', $tsDisp);
        $tahunAnggaranStr = date('Y', $tsTtd);

        $utama = $pelaksana[0] ?? ['nama' => '-', 'nip' => '-', 'jabatan' => '-'];
        $namaUtama = $this->formatNamaGelar((string)($utama['nama'] ?? '-'));
        $nipUtama  = $formatNip($utama['nip'] ?? '');
        $jabatanUtama = (string)($utama['jabatan'] ?? '');

        $jenisPegUtama = strtolower(trim((string)($utama['jenis_pegawai'] ?? '')));
        if (empty($jenisPegUtama) && !empty($utama['id'])) {
            $dbPeg = \Config\Database::connect();
            if ($dbPeg->tableExists('mst_pegawai')) {
                $pegRow = $dbPeg->table('mst_pegawai')->select('jenis_pegawai')->where('id', $utama['id'])->get()->getRowArray();
                if (!empty($pegRow['jenis_pegawai'])) {
                    $jenisPegUtama = strtolower(trim((string)$pegRow['jenis_pegawai']));
                }
            }
        }
        $nipLabelUtama = (strpos($jenisPegUtama, 'pppk') !== false) ? 'NIPPPK. ' : 'NIP. ';

        $nomorSPD = !empty($row['kode_nomor']) 
            ? (string)$row['kode_nomor'] 
            : (!empty($row['nomor_surat_tugas']) 
                ? str_replace('SPT', 'SPD', (string)$row['nomor_surat_tugas']) 
                : '-');

        // Cost calculations
        $jabUpper = strtoupper($jabatanUtama);
        $tarifPenginapan = $biayaMaster['penginapan_e4'] ?? 0;
        if (strpos($jabUpper, 'ESELON I') !== false && strpos($jabUpper, 'ESELON II') === false && strpos($jabUpper, 'ESELON III') === false) {
            $tarifPenginapan = $biayaMaster['penginapan_e1'] ?? 0;
        } elseif (strpos($jabUpper, 'ESELON II') !== false && strpos($jabUpper, 'ESELON III') === false) {
            $tarifPenginapan = $biayaMaster['penginapan_e2'] ?? 0;
        } elseif (strpos($jabUpper, 'ESELON III') !== false) {
            $tarifPenginapan = $biayaMaster['penginapan_e3'] ?? 0;
        }

        $rincianBiaya = json_decode((string)($row['rincian_biaya_json'] ?? '{}'), true) ?: [];

        // 1. Transport
        $transportList = $rincianBiaya['transport'] ?? [];
        if (!is_array($transportList) && isset($rincianBiaya['transport_start_date'])) {
            $transportList = [[
                'tgl_mulai'   => $rincianBiaya['transport_start_date'] ?? '',
                'tgl_selesai' => $rincianBiaya['transport_end_date'] ?? '',
                'nominal'     => (int)($rincianBiaya['transport_nominal'] ?? 0),
                'keterangan'  => '',
            ]];
        }

        $transportItems = [];
        if (is_array($transportList) && count($transportList) > 0) {
            foreach ($transportList as $tItem) {
                $tStart     = $tItem['tgl_mulai'] ?? '';
                $tEnd       = $tItem['tgl_selesai'] ?? '';
                $tNom       = (int)($tItem['nominal'] ?? 0);
                $tKet       = trim((string)($tItem['keterangan'] ?? ''));
                $tJenis     = trim((string)($tItem['jenis'] ?? ''));
                $tIsLumpsum = !empty($tItem['is_lumpsum']);

                $tDays = 0;
                if (!empty($tStart) && !empty($tEnd)) {
                    try {
                        $d1    = new \DateTime($tStart);
                        $d2    = new \DateTime($tEnd);
                        $tDays = max(0, $d1->diff($d2)->days + 1);
                    } catch (\Throwable $e) {}
                }

                $rate = $tNom;
                $sub  = $tIsLumpsum ? $rate : (($tDays > 0) ? ($tDays * $rate) : $rate);

                $jenisLow = strtolower($tJenis);
                $ketLow   = strtolower($tKet);
                $isTravel = (strpos($jenisLow, 'travel') !== false) || (strpos($ketLow, 'travel') !== false) || ($tJenis === '' && ($tKet === '' || strpos($ketLow, 'kampar') !== false || strpos($ketLow, 'pekanbaru') !== false));

                $tKetFormatted = $tKet;
                if ($isTravel) {
                    $dest = !empty($tKet) ? $tKet : (!empty($kotaTujuan) ? $kotaTujuan : 'Tujuan');
                    $destClean = preg_replace('/^travel\s+/i', '', $dest);
                    $destClean = preg_replace('/^pekanbaru\s*-\s*/i', '', $destClean);
                    $destClean = preg_replace('/\s*\(?pp\)?$/i', '', $destClean);
                    $destClean = trim($destClean);
                    $tKetFormatted = 'Travel Pekanbaru - ' . $destClean . ' (PP)';
                }

                if ($tDays > 0 || $rate > 0 || $tIsLumpsum) {
                    $transportItems[] = [
                        'jenis'   => $tJenis,
                        'ket'     => $tKetFormatted,
                        'days'    => $tDays,
                        'rate'    => $rate,
                        'sub'     => $sub,
                        'lumpsum' => $tIsLumpsum,
                    ];
                }
            }
        }

        // Group transport by jenis
        $transportGroups = [];
        foreach ($transportItems as $ti) {
            $jenis    = $ti['jenis'];
            $jenisLow = strtolower($jenis);
            if (strpos($jenisLow, 'pesawat') !== false) {
                $gKey = 'Pesawat Udara';
            } elseif (strpos($jenisLow, 'taksi') !== false || strpos($jenisLow, 'taxi') !== false) {
                $gKey = 'Taxi';
            } elseif (strpos($jenisLow, 'travel') !== false) {
                $gKey = 'Travel';
            } elseif (strpos($jenisLow, 'sewa') !== false) {
                $gKey = 'Sewa Kendaraan';
            } elseif (strpos($jenisLow, 'darat') !== false) {
                $gKey = 'Transport Darat';
            } elseif (strpos($jenisLow, 'laut') !== false || strpos($jenisLow, 'kapal') !== false) {
                $gKey = 'Transport Laut';
            } elseif ($jenis !== '') {
                $gKey = $jenis;
            } else {
                $gKey = $ti['ket'] !== '' ? $ti['ket'] : 'Transport';
            }
            if (!isset($transportGroups[$gKey])) {
                $transportGroups[$gKey] = ['label' => $gKey, 'rows' => [], 'exact_subtotal' => 0];
            }
            $transportGroups[$gKey]['rows'][]         = $ti;
            $transportGroups[$gKey]['exact_subtotal'] += $ti['sub'];
        }

        $calcTransport = 0;
        foreach ($transportGroups as $gKey => $grp) {
            $exact   = $grp['exact_subtotal'];
            $rounded = (int)(floor($exact / 100) * 100);
            $transportGroups[$gKey]['rounded_subtotal'] = $rounded;
            $transportGroups[$gKey]['has_rounded'] = ($rounded !== $exact);
            $calcTransport += $rounded;
        }

        // 2. Uang Harian
        $harianList = $rincianBiaya['uang_harian'] ?? [];
        if (!is_array($harianList)) $harianList = [];

        $calcHarian    = 0;
        $harianDetails = [];
        if (is_array($harianList) && count($harianList) > 0) {
            foreach ($harianList as $hItem) {
                $hStart = $hItem['tgl_mulai'] ?? '';
                $hEnd   = $hItem['tgl_selesai'] ?? '';
                $hNom   = isset($hItem['nominal']) ? (int)$hItem['nominal'] : 0;
                $hKet   = trim((string)($hItem['keterangan'] ?? ''));
                $hDays  = 0;
                if (!empty($hStart) && !empty($hEnd)) {
                    try {
                        $d1    = new \DateTime($hStart);
                        $d2    = new \DateTime($hEnd);
                        $hDays = max(0, $d1->diff($d2)->days + 1);
                    } catch (\Throwable $e) {}
                }
                $rate = $hNom > 0 ? $hNom : (int)($biayaMaster['harian'] ?? 0);
                if ($hDays == 0 && $rate > 0) $hDays = 1;
                $sub  = $hDays * $rate;
                $calcHarian += $sub;
                if ($hDays > 0 || $rate > 0) {
                    $harianDetails[] = ['days' => $hDays, 'rate' => $rate, 'sub' => $sub, 'ket' => $hKet];
                }
            }
        } else {
            $hDays = max(0, $days);
            $rate  = (int)($biayaMaster['harian'] ?? 0);
            $calcHarian = $hDays * $rate;
            $harianDetails[] = ['days' => $hDays, 'rate' => $rate, 'sub' => $calcHarian, 'ket' => ''];
        }

        // 3. Penginapan
        $penginapanList = $rincianBiaya['penginapan'] ?? [];
        if (!is_array($penginapanList) && isset($rincianBiaya['penginapan_start_date'])) {
            $penginapanList = [[
                'tgl_mulai'   => $rincianBiaya['penginapan_start_date'] ?? '',
                'tgl_selesai' => $rincianBiaya['penginapan_end_date'] ?? '',
                'nominal'     => isset($rincianBiaya['penginapan_nominal']) ? (int)$rincianBiaya['penginapan_nominal'] : null,
                'keterangan'  => '',
            ]];
        }

        $calcPenginapan    = 0;
        $penginapanDetails = [];
        if (is_array($penginapanList) && count($penginapanList) > 0) {
            foreach ($penginapanList as $pItem) {
                $pStart    = $pItem['tgl_mulai'] ?? '';
                $pEnd      = $pItem['tgl_selesai'] ?? '';
                $pNomInput = isset($pItem['nominal']) && $pItem['nominal'] !== null && $pItem['nominal'] !== '' ? (int)$pItem['nominal'] : null;
                $pKet      = trim((string)($pItem['keterangan'] ?? ''));
                $pNights   = 0;
                if (!empty($pStart) && !empty($pEnd)) {
                    try {
                        $d1      = new \DateTime($pStart);
                        $d2      = new \DateTime($pEnd);
                        $pNights = max(0, $d1->diff($d2)->days);
                    } catch (\Throwable $e) {}
                } else {
                    $pNights = max(0, $days - 1);
                }
                $rate = $pNomInput !== null && $pNomInput >= 0 ? $pNomInput : (int)($tarifPenginapan * 0.3);
                if ($pNights == 0 && $rate > 0) $pNights = 1;
                $sub = $pNights * $rate;
                $calcPenginapan += $sub;
                if ($pNights > 0 || $rate > 0) {
                    $penginapanDetails[] = ['nights' => $pNights, 'rate' => $rate, 'sub' => $sub, 'ket' => $pKet];
                }
            }
        } else {
            $pNights = max(0, $days - 1);
            $rate    = (int)($tarifPenginapan * 0.3);
            $calcPenginapan = $pNights * $rate;
            $penginapanDetails[] = ['nights' => $pNights, 'rate' => $rate, 'sub' => $calcPenginapan, 'ket' => ''];
        }

        $totalBiaya = $calcHarian + $calcTransport + $calcPenginapan;

        $terbilangIndoFunc = function ($number) use (&$terbilangIndoFunc) {
            $number = (float)$number;
            $abs = abs($number);
            $words = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];
            $temp = "";
            if ($abs < 12) {
                $temp = " " . $words[(int)$abs];
            } else if ($abs < 20) {
                $temp = $terbilangIndoFunc($abs - 10) . " Belas";
            } else if ($abs < 100) {
                $temp = $terbilangIndoFunc((int)($abs / 10)) . " Puluh " . $terbilangIndoFunc($abs % 10);
            } else if ($abs < 200) {
                $temp = " Seratus " . $terbilangIndoFunc($abs - 100);
            } else if ($abs < 1000) {
                $temp = $terbilangIndoFunc((int)($abs / 100)) . " Ratus " . $terbilangIndoFunc($abs % 100);
            } else if ($abs < 2000) {
                $temp = " Seribu " . $terbilangIndoFunc($abs - 1000);
            } else if ($abs < 1000000) {
                $temp = $terbilangIndoFunc((int)($abs / 1000)) . " Ribu " . $terbilangIndoFunc($abs % 1000);
            } else if ($abs < 1000000000) {
                $temp = $terbilangIndoFunc((int)($abs / 1000000)) . " Juta " . $terbilangIndoFunc($abs % 1000000);
            } else if ($abs < 1000000000000) {
                $temp = $terbilangIndoFunc((int)($abs / 1000000000)) . " Milyar " . $terbilangIndoFunc(fmod($abs, 1000000000));
            } else if ($abs < 1000000000000000) {
                $temp = $terbilangIndoFunc((int)($abs / 1000000000000)) . " Trilyun " . $terbilangIndoFunc(fmod($abs, 1000000000000));
            }
            return preg_replace('/\s+/', ' ', trim($temp));
        };

        // Resolve Dasar SPT text for Kwitansi
        $dasarSptIds = json_decode((string) ($row['dasar_spt_ids_json'] ?? '[]'), true) ?: [];
        $dasarTexts = [];
        if (!empty($dasarSptIds)) {
            $numericIds = array_filter($dasarSptIds, 'is_numeric');
            $customTexts = array_diff($dasarSptIds, $numericIds);
            if (!empty($numericIds)) {
                $dbDasar = (new \App\Models\MstDasarSptModel())->whereIn('id', $numericIds)->orderBy('id', 'ASC')->findAll();
                foreach ($dbDasar as $dD) {
                    if (!empty($dD['uraian'])) $dasarTexts[] = $dD['uraian'];
                }
            }
            foreach ($customTexts as $cT) {
                if (!empty($cT)) $dasarTexts[] = $cT;
            }
        }

        $dasarSptStr = '';
        if (!empty($dasarTexts)) {
            $dasarSptStr = implode('; ', $dasarTexts);
        } elseif (!empty($row['nomor_surat_tugas'])) {
            $dasarSptStr = 'Surat Tugas Nomor: ' . $row['nomor_surat_tugas'];
        }

        $perihalText = !empty($row['perihal_disposisi']) ? $row['perihal_disposisi'] : (!empty($row['perihal']) ? $row['perihal'] : ($row['tujuan'] ?? '-'));
        $fullPembayaranText = "Perjalanan Dinas a.n. " . $namaUtama . " " . $jabatanUtama . " dalam rangka " . $perihalText;
        if (!empty($dasarSptStr)) {
            $fullPembayaranText .= ", sesuai dengan " . $dasarSptStr;
        }
        $fullPembayaranText .= ", sebagaimana daftar perincian terlampir.";

        $terbilangText = $terbilangIndoFunc($totalBiaya) . ' Rupiah,-';

        $kopSuratPath = '';
        if (!empty($kopSurat['image_url'])) {
            $p = FCPATH . ltrim($kopSurat['image_url'], '/');
            if (file_exists($p)) {
                $kopSuratPath = $p;
            }
        }

        return view('admin/laporan/cetak_kwitansi_excel_preview', [
            'row' => $row,
            'pelaksana' => $pelaksana,
            'kop_surat' => $kopSurat,
            'kop_surat_path' => $kopSuratPath,
            'biaya_master' => $biayaMaster,
            'mata_anggaran' => $mataAnggaranText,
            'tgl_berangkat' => $tglBerangkat,
            'tgl_kembali' => $tglKembali,
            'tanggal_ttd_upper' => $tanggalTtdUpper,
            'bulan_tahun_str' => $bulanTahunStr,
            'tahun_anggaran_str' => $tahunAnggaranStr,
            'nama_utama' => $namaUtama,
            'nip_utama' => $nipUtama,
            'nip_label_utama' => $nipLabelUtama,
            'jabatan_utama' => $jabatanUtama,
            'jabatan_utama_line1' => $this->splitJabatanTwoLines($jabatanUtama)[0],
            'jabatan_utama_line2' => $this->splitJabatanTwoLines($jabatanUtama)[1],
            'calc_transport' => $calcTransport,
            'calc_harian' => $calcHarian,
            'calc_penginapan' => $calcPenginapan,
            'total_biaya' => $totalBiaya,
            'terbilang_text' => $terbilangText,
            'full_pembayaran_text' => $fullPembayaranText,
            'nomor_spd' => $nomorSPD,
            'transport_groups' => $transportGroups,
            'harian_details' => $harianDetails,
            'penginapan_details' => $penginapanDetails,
            'bendahara_nama' => 'KH. SRI HANDAYANI, S.Si., M.T.',
            'bendahara_nip' => 'NIP. 19820402 201412 2 002',
        ]);
    }

    private function buildKwitansiSpreadsheet(array $row, array $pelaksana, array $biayaMaster, string $mataAnggaranText, ?array $kopSurat = null): \PhpOffice\PhpSpreadsheet\Spreadsheet
    {
        $terbilangIndo = function ($number) use (&$terbilangIndo) {
            $number = (float)$number;
            $abs = abs($number);
            $words = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];
            $temp = "";
            if ($abs < 12) {
                $temp = " " . $words[(int)$abs];
            } else if ($abs < 20) {
                $temp = $terbilangIndo($abs - 10) . " Belas";
            } else if ($abs < 100) {
                $temp = $terbilangIndo((int)($abs / 10)) . " Puluh " . $terbilangIndo($abs % 10);
            } else if ($abs < 200) {
                $temp = " Seratus " . $terbilangIndo($abs - 100);
            } else if ($abs < 1000) {
                $temp = $terbilangIndo((int)($abs / 100)) . " Ratus " . $terbilangIndo($abs % 100);
            } else if ($abs < 2000) {
                $temp = " Seribu " . $terbilangIndo($abs - 1000);
            } else if ($abs < 1000000) {
                $temp = $terbilangIndo((int)($abs / 1000)) . " Ribu " . $terbilangIndo($abs % 1000);
            } else if ($abs < 1000000000) {
                $temp = $terbilangIndo((int)($abs / 1000000)) . " Juta " . $terbilangIndo($abs % 1000000);
            } else if ($abs < 1000000000000) {
                $temp = $terbilangIndo((int)($abs / 1000000000)) . " Milyar " . $terbilangIndo(fmod($abs, 1000000000));
            } else if ($abs < 1000000000000000) {
                $temp = $terbilangIndo((int)($abs / 1000000000000)) . " Trilyun " . $terbilangIndo(fmod($abs, 1000000000000));
            }
            return preg_replace('/\s+/', ' ', trim($temp));
        };

        $formatDateIndo = function ($dateStr, $leadingZero = true) {
            if (empty($dateStr)) return '-';
            $ts = strtotime($dateStr);
            if (!$ts) return $dateStr;
            $months = [
                1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
                5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',
                9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
            ];
            $dayFmt = $leadingZero ? 'd' : 'j';
            return date($dayFmt, $ts) . ' ' . $months[(int)date('n', $ts)] . ' ' . date('Y', $ts);
        };

        $formatNip = function ($nip) {
            $nip = preg_replace('/\s+/', '', (string)$nip);
            if (strlen($nip) === 18) {
                return substr($nip,0,8).' '.substr($nip,8,6).' '.substr($nip,14,1).' '.substr($nip,15,3);
            }
            return $nip;
        };

        $tglBerangkat = $formatDateIndo($row['periode_mulai'] ?? '', false);
        $tglKembali   = $formatDateIndo($row['periode_selesai'] ?? '', false);

        $days = 0;
        if (!empty($row['periode_mulai']) && !empty($row['periode_selesai'])) {
            try {
                $start = new \DateTime($row['periode_mulai']);
                $end   = new \DateTime($row['periode_selesai']);
                $days  = $start->diff($end)->days + 1;
            } catch (\Throwable $e) {}
        }

        $nomorSurat  = (string)($row['nomor_surat_tugas'] ?? '-');
        $nomorSPD    = !empty($row['kode_nomor']) 
            ? (string)$row['kode_nomor'] 
            : (!empty($row['nomor_surat_tugas']) 
                ? str_replace('SPT', 'SPD', $nomorSurat) 
                : '-');
        $kotaTujuan  = (string)($row['kota_tujuan'] ?? '-');
        $tujuanMaksud = !empty($row['perihal_disposisi']) ? (string)$row['perihal_disposisi'] : (!empty($row['perihal']) ? (string)$row['perihal'] : (string)($row['tujuan'] ?? '-'));

        $tanggalTtdRaw = !empty($row['tanggal_tanda_tangan'])
            ? (string)$row['tanggal_tanda_tangan']
            : '';
        if (empty($tanggalTtdRaw) && !empty($row['disposisi_id'])) {
            $dbDisp = \Config\Database::connect();
            if ($dbDisp->tableExists('disposisi_perjalanan_dinas')) {
                $dispRow = $dbDisp->table('disposisi_perjalanan_dinas')->select('created_at')->where('id', $row['disposisi_id'])->get()->getRowArray();
                if (!empty($dispRow['created_at'])) {
                    $tanggalTtdRaw = date('Y-m-d', strtotime($dispRow['created_at']));
                }
            }
        }
        if (empty($tanggalTtdRaw) && !empty($row['created_at'])) {
            $tanggalTtdRaw = date('Y-m-d', strtotime($row['created_at']));
        }
        if (empty($tanggalTtdRaw)) {
            $tanggalTtdRaw = date('Y-m-d');
        }

        $tanggalTtdUpper = strtoupper($formatDateIndo($tanggalTtdRaw, true));
        
        $tglDisposisiRaw = '';
        if (!empty($row['disposisi_id'])) {
            $dbDisp = \Config\Database::connect();
            if ($dbDisp->tableExists('disposisi_perjalanan_dinas')) {
                $dispRow = $dbDisp->table('disposisi_perjalanan_dinas')->select('created_at')->where('id', $row['disposisi_id'])->get()->getRowArray();
                if (!empty($dispRow['created_at'])) {
                    $tglDisposisiRaw = date('Y-m-d', strtotime($dispRow['created_at']));
                }
            }
        }
        if (empty($tglDisposisiRaw) && !empty($row['created_at'])) {
            $tglDisposisiRaw = date('Y-m-d', strtotime($row['created_at']));
        }
        if (empty($tglDisposisiRaw)) {
            $tglDisposisiRaw = date('Y-m-d');
        }

        $tsDisp = strtotime($tglDisposisiRaw) ?: time();
        $tsTtd  = strtotime($tanggalTtdRaw) ?: time();
        $monthsIndo = [
            1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
            5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',
            9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
        ];
        $bulanTahunStr = $monthsIndo[(int)date('n', $tsDisp)] . ' ' . date('Y', $tsDisp);
        $tahunAnggaranStr = date('Y', $tsTtd);

        $utama = $pelaksana[0] ?? ['nama' => '-', 'nip' => '-', 'jabatan' => '-'];
        $namaUtama = $this->formatNamaGelar((string)($utama['nama'] ?? '-'));
        $nipUtama  = $formatNip($utama['nip'] ?? '');
        $jabatanUtama = (string)($utama['jabatan'] ?? '');

        $jenisPegUtama = strtolower(trim((string)($utama['jenis_pegawai'] ?? '')));
        if (empty($jenisPegUtama) && !empty($utama['id'])) {
            $dbPeg = \Config\Database::connect();
            if ($dbPeg->tableExists('mst_pegawai')) {
                $pegRow = $dbPeg->table('mst_pegawai')->select('jenis_pegawai')->where('id', $utama['id'])->get()->getRowArray();
                if (!empty($pegRow['jenis_pegawai'])) {
                    $jenisPegUtama = strtolower(trim((string)$pegRow['jenis_pegawai']));
                }
            }
        }
        $nipLabelUtama = (strpos($jenisPegUtama, 'pppk') !== false) ? 'NIPPPK. ' : 'NIP. ';

        // Cost calculations
        $jabUpper = strtoupper($jabatanUtama);
        $tarifPenginapan = $biayaMaster['penginapan_e4'] ?? 0;
        if (strpos($jabUpper, 'ESELON I') !== false && strpos($jabUpper, 'ESELON II') === false && strpos($jabUpper, 'ESELON III') === false) {
            $tarifPenginapan = $biayaMaster['penginapan_e1'] ?? 0;
        } elseif (strpos($jabUpper, 'ESELON II') !== false && strpos($jabUpper, 'ESELON III') === false) {
            $tarifPenginapan = $biayaMaster['penginapan_e2'] ?? 0;
        } elseif (strpos($jabUpper, 'ESELON III') !== false) {
            $tarifPenginapan = $biayaMaster['penginapan_e3'] ?? 0;
        }

        $rincianBiaya = json_decode((string)($row['rincian_biaya_json'] ?? '{}'), true) ?: [];

        // 1. Transport
        $transportList = $rincianBiaya['transport'] ?? [];
        if (!is_array($transportList) && isset($rincianBiaya['transport_start_date'])) {
            $transportList = [[
                'tgl_mulai'   => $rincianBiaya['transport_start_date'] ?? '',
                'tgl_selesai' => $rincianBiaya['transport_end_date'] ?? '',
                'nominal'     => (int)($rincianBiaya['transport_nominal'] ?? 0),
                'keterangan'  => '',
            ]];
        }

        $transportItems = [];
        if (is_array($transportList) && count($transportList) > 0) {
            foreach ($transportList as $tItem) {
                $tStart     = $tItem['tgl_mulai'] ?? '';
                $tEnd       = $tItem['tgl_selesai'] ?? '';
                $tNom       = (int)($tItem['nominal'] ?? 0);
                $tKet       = trim((string)($tItem['keterangan'] ?? ''));
                $tJenis     = trim((string)($tItem['jenis'] ?? ''));
                $tIsLumpsum = !empty($tItem['is_lumpsum']);

                $tDays = 0;
                if (!empty($tStart) && !empty($tEnd)) {
                    try {
                        $d1    = new \DateTime($tStart);
                        $d2    = new \DateTime($tEnd);
                        $tDays = max(0, $d1->diff($d2)->days + 1);
                    } catch (\Throwable $e) {}
                }

                $rate = $tNom;
                $sub  = $tIsLumpsum ? $rate : (($tDays > 0) ? ($tDays * $rate) : $rate);

                $jenisLow = strtolower($tJenis);
                $ketLow   = strtolower($tKet);
                $isTravel = (strpos($jenisLow, 'travel') !== false) || (strpos($ketLow, 'travel') !== false) || ($tJenis === '' && ($tKet === '' || strpos($ketLow, 'kampar') !== false || strpos($ketLow, 'pekanbaru') !== false));

                $tKetFormatted = $tKet;
                if ($isTravel) {
                    $dest = !empty($tKet) ? $tKet : (!empty($kotaTujuan) ? $kotaTujuan : 'Tujuan');
                    $destClean = preg_replace('/^travel\s+/i', '', $dest);
                    $destClean = preg_replace('/^pekanbaru\s*-\s*/i', '', $destClean);
                    $destClean = preg_replace('/\s*\(?pp\)?$/i', '', $destClean);
                    $destClean = trim($destClean);
                    $tKetFormatted = 'Travel Pekanbaru - ' . $destClean . ' (PP)';
                }

                if ($tDays > 0 || $rate > 0 || $tIsLumpsum) {
                    $transportItems[] = [
                        'jenis'   => $tJenis,
                        'ket'     => $tKetFormatted,
                        'days'    => $tDays,
                        'rate'    => $rate,
                        'sub'     => $sub,
                        'lumpsum' => $tIsLumpsum,
                    ];
                }
            }
        }

        // Group transport by jenis
        $transportGroups = [];
        foreach ($transportItems as $ti) {
            $jenis    = $ti['jenis'];
            $jenisLow = strtolower($jenis);
            if (strpos($jenisLow, 'pesawat') !== false) {
                $gKey = 'Pesawat Udara';
            } elseif (strpos($jenisLow, 'taksi') !== false || strpos($jenisLow, 'taxi') !== false) {
                $gKey = 'Taxi';
            } elseif (strpos($jenisLow, 'travel') !== false) {
                $gKey = 'Travel';
            } elseif (strpos($jenisLow, 'sewa') !== false) {
                $gKey = 'Sewa Kendaraan';
            } elseif (strpos($jenisLow, 'darat') !== false) {
                $gKey = 'Transport Darat';
            } elseif (strpos($jenisLow, 'laut') !== false || strpos($jenisLow, 'kapal') !== false) {
                $gKey = 'Transport Laut';
            } elseif ($jenis !== '') {
                $gKey = $jenis;
            } else {
                $gKey = $ti['ket'] !== '' ? $ti['ket'] : 'Transport';
            }
            if (!isset($transportGroups[$gKey])) {
                $transportGroups[$gKey] = ['label' => $gKey, 'rows' => [], 'exact_subtotal' => 0];
            }
            $transportGroups[$gKey]['rows'][]         = $ti;
            $transportGroups[$gKey]['exact_subtotal'] += $ti['sub'];
        }

        $calcTransport = 0;
        foreach ($transportGroups as $gKey => $grp) {
            $exact   = $grp['exact_subtotal'];
            $rounded = (int)(floor($exact / 100) * 100);
            $transportGroups[$gKey]['rounded_subtotal'] = $rounded;
            $transportGroups[$gKey]['has_rounded'] = ($rounded !== $exact);
            $calcTransport += $rounded;
        }

        // 2. Uang Harian
        $harianList = $rincianBiaya['uang_harian'] ?? [];
        if (!is_array($harianList)) $harianList = [];

        $calcHarian    = 0;
        $harianDetails = [];
        if (is_array($harianList) && count($harianList) > 0) {
            foreach ($harianList as $hItem) {
                $hStart = $hItem['tgl_mulai'] ?? '';
                $hEnd   = $hItem['tgl_selesai'] ?? '';
                $hNom   = isset($hItem['nominal']) ? (int)$hItem['nominal'] : 0;
                $hKet   = trim((string)($hItem['keterangan'] ?? ''));
                $hDays  = 0;
                if (!empty($hStart) && !empty($hEnd)) {
                    try {
                        $d1    = new \DateTime($hStart);
                        $d2    = new \DateTime($hEnd);
                        $hDays = max(0, $d1->diff($d2)->days + 1);
                    } catch (\Throwable $e) {}
                }
                $rate = $hNom > 0 ? $hNom : (int)($biayaMaster['harian'] ?? 0);
                if ($hDays == 0 && $rate > 0) $hDays = 1;
                $sub  = $hDays * $rate;
                $calcHarian += $sub;
                if ($hDays > 0 || $rate > 0) {
                    $harianDetails[] = ['days' => $hDays, 'rate' => $rate, 'sub' => $sub, 'ket' => $hKet];
                }
            }
        } else {
            $hDays = max(0, $days);
            $rate  = (int)($biayaMaster['harian'] ?? 0);
            $calcHarian = $hDays * $rate;
            $harianDetails[] = ['days' => $hDays, 'rate' => $rate, 'sub' => $calcHarian, 'ket' => ''];
        }

        // 3. Penginapan
        $penginapanList = $rincianBiaya['penginapan'] ?? [];
        if (!is_array($penginapanList) && isset($rincianBiaya['penginapan_start_date'])) {
            $penginapanList = [[
                'tgl_mulai'   => $rincianBiaya['penginapan_start_date'] ?? '',
                'tgl_selesai' => $rincianBiaya['penginapan_end_date'] ?? '',
                'nominal'     => isset($rincianBiaya['penginapan_nominal']) ? (int)$rincianBiaya['penginapan_nominal'] : null,
                'keterangan'  => '',
            ]];
        }

        $calcPenginapan    = 0;
        $penginapanDetails = [];
        if (is_array($penginapanList) && count($penginapanList) > 0) {
            foreach ($penginapanList as $pItem) {
                $pStart    = $pItem['tgl_mulai'] ?? '';
                $pEnd      = $pItem['tgl_selesai'] ?? '';
                $pNomInput = isset($pItem['nominal']) && $pItem['nominal'] !== null && $pItem['nominal'] !== '' ? (int)$pItem['nominal'] : null;
                $pKet      = trim((string)($pItem['keterangan'] ?? ''));
                $pNights   = 0;
                if (!empty($pStart) && !empty($pEnd)) {
                    try {
                        $d1      = new \DateTime($pStart);
                        $d2      = new \DateTime($pEnd);
                        $pNights = max(0, $d1->diff($d2)->days);
                    } catch (\Throwable $e) {}
                } else {
                    $pNights = max(0, $days - 1);
                }
                $rate = $pNomInput !== null && $pNomInput >= 0 ? $pNomInput : (int)($tarifPenginapan * 0.3);
                if ($pNights == 0 && $rate > 0) $pNights = 1;
                $sub = $pNights * $rate;
                $calcPenginapan += $sub;
                if ($pNights > 0 || $rate > 0) {
                    $penginapanDetails[] = ['nights' => $pNights, 'rate' => $rate, 'sub' => $sub, 'ket' => $pKet];
                }
            }
        } else {
            $pNights = max(0, $days - 1);
            $rate    = (int)($tarifPenginapan * 0.3);
            $calcPenginapan = $pNights * $rate;
            $penginapanDetails[] = ['nights' => $pNights, 'rate' => $rate, 'sub' => $calcPenginapan, 'ket' => ''];
        }

        $totalBiaya = $calcHarian + $calcTransport + $calcPenginapan;

        // PPK & Bendahara Names & NIPs
        $ppkNama = 'NURHIDAYAT NUGROHO, S.Ars.';
        $ppkNip  = 'NIP. 19901221 201802 1 001';
        $ppkJabatanLine1 = 'Pejabat Pembuat Komitmen';
        $ppkJabatanLine2 = 'Pelaksanaan Prasarana Strategis ';

        $bendaharaNama = 'KH. SRI HANDAYANI, S.Si., M.T.';
        $bendaharaNip  = 'NIP. 19820402 201412 2 002';

        // Start Spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // ---------------------------------------------------------
        // SHEET 0: RINCI
        // ---------------------------------------------------------
        $sheetRinci = $spreadsheet->getActiveSheet();
        $sheetRinci->setTitle('RINCI');
        $sheetRinci->setShowGridLines(true);

        // Page Break Preview Mode & Print Setup for RINCI
        $sheetRinci->getSheetView()->setView(\PhpOffice\PhpSpreadsheet\Worksheet\SheetView::SHEETVIEW_PAGE_BREAK_PREVIEW);
        $sheetRinci->getPageSetup()->setPrintArea('C1:O58');
        $sheetRinci->getPageSetup()->setFitToPage(true);
        $sheetRinci->getPageSetup()->setFitToWidth(1);
        $sheetRinci->getPageSetup()->setFitToHeight(1);
        $sheetRinci->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
        $sheetRinci->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

        $colWidthsRinci = [
            'A' => 12.66, 'B' => 0.83, 'C' => 4.66, 'D' => 4.50, 'E' => 6.83,
            'F' => 5.16,  'G' => 3.83, 'H' => 12.50, 'I' => 4.33, 'J' => 17.66,
            'K' => 14.83, 'L' => 4.16, 'M' => 16.33, 'N' => 3.83, 'O' => 12.66
        ];
        foreach ($colWidthsRinci as $col => $w) {
            $sheetRinci->getColumnDimension($col)->setWidth($w);
        }

        for ($r = 1; $r <= 57; $r++) {
            $sheetRinci->getRowDimension($r)->setRowHeight($r <= 3 ? 18.75 : 15.0);
        }

        $sheetRinci->getStyle('A1:O57')->getFont()->setName('Tahoma')->setSize(11);

        // Title
        $sheetRinci->mergeCells('C1:O1');
        $sheetRinci->setCellValue('C1', 'RINCIAN BIAYA PERJALANAN DINAS');
        $sheetRinci->getStyle('C1')->getFont()->setSize(16)->setBold(true);
        $sheetRinci->getStyle('C1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheetRinci->mergeCells('C2:O2');
        $sheetRinci->setCellValue('C2', 'LAMPIRAN SPD NOMOR : ' . $nomorSPD);
        $sheetRinci->getStyle('C2')->getFont()->setSize(12);
        $sheetRinci->getStyle('C2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheetRinci->mergeCells('C3:O3');
        $sheetRinci->setCellValue('C3', 'TANGGAL : ' . $tanggalTtdUpper);
        $sheetRinci->getStyle('C3')->getFont()->setSize(12);
        $sheetRinci->getStyle('C3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Table Header Row 5
        $sheetRinci->setCellValue('C5', 'No');
        $sheetRinci->mergeCells('D5:K5');
        $sheetRinci->setCellValue('D5', 'RINCIAN BIAYA');
        $sheetRinci->mergeCells('L5:M5');
        $sheetRinci->setCellValue('L5', 'JUMLAH');
        $sheetRinci->mergeCells('N5:O5');
        $sheetRinci->setCellValue('N5', 'KETERANGAN');

        $hdrStyle = [
            'font' => ['bold' => true, 'size' => 11],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF0F2F5'],
            ],
            'borders' => [
                'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                'left' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                'right' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            ]
        ];
        $sheetRinci->getStyle('C5')->applyFromArray($hdrStyle);
        $sheetRinci->getStyle('D5:K5')->applyFromArray($hdrStyle);
        $sheetRinci->getStyle('L5:M5')->applyFromArray($hdrStyle);
        $sheetRinci->getStyle('N5:O5')->applyFromArray($hdrStyle);

        // Row 6 borders
        $sheetRinci->getStyle('C6')->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheetRinci->getStyle('C6')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheetRinci->getStyle('L6:M6')->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheetRinci->getStyle('L6')->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheetRinci->getStyle('M6')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheetRinci->getStyle('N6:O6')->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheetRinci->getStyle('N6')->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheetRinci->getStyle('O6')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Dynamic items mapping
        // Item 1: Transport
        $sheetRinci->setCellValue('C7', '1');
        $sheetRinci->getStyle('C7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheetRinci->setCellValue('D7', 'BIAYA TRANSPORT :');
        $sheetRinci->getStyle('D7')->getFont()->setBold(true);
        $sheetRinci->setCellValue('L7', 'Rp.');
        $sheetRinci->getStyle('L7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheetRinci->setCellValue('M7', (int)$calcTransport);
        $sheetRinci->getStyle('M7')->getNumberFormat()->setFormatCode('_(* #,##0_);_(* \(#,##0\);_(* "-"_);_(@_)');

        $curRow = 8;
        $multiGroup = count($transportGroups) > 1;

        if (empty($transportGroups)) {
            // No transport rows
        } elseif (!$multiGroup) {
            $onlyGroup = reset($transportGroups);
            foreach ($onlyGroup['rows'] as $tRow) {
                $desc = $tRow['ket'] !== '' ? $tRow['ket'] : (!empty($tRow['jenis']) ? $tRow['jenis'] : 'Transport');
                $sheetRinci->setCellValue('D' . $curRow, $desc);
                $sheetRinci->setCellValue('I' . $curRow, 'Rp.');
                $sheetRinci->setCellValue('J' . $curRow, (int)$tRow['sub']);
                $sheetRinci->getStyle('J' . $curRow)->getNumberFormat()->setFormatCode('_(* #,##0_);_(* \(#,##0\);_(* "-"_);_(@_)');
                $curRow++;
            }
        } else {
            foreach ($transportGroups as $gLabel => $grp) {
                $sheetRinci->setCellValue('D' . $curRow, $gLabel . ':');
                $sheetRinci->getStyle('D' . $curRow)->getFont()->setBold(true);
                $curRow++;

                $grpRows = $grp['rows'];
                $subStartRow = $curRow;
                foreach ($grpRows as $idx => $tRow) {
                    $desc = $tRow['ket'] !== '' ? $tRow['ket'] : $gLabel;
                    $sheetRinci->setCellValue('D' . $curRow, $desc);
                    $sheetRinci->setCellValue('I' . $curRow, 'Rp.');
                    $sheetRinci->setCellValue('J' . $curRow, (int)$tRow['sub']);
                    $sheetRinci->getStyle('J' . $curRow)->getNumberFormat()->setFormatCode('_(* #,##0_);_(* \(#,##0\);_(* "-"_);_(@_)');
                    if ($idx === count($grpRows) - 1 && count($grpRows) > 1) {
                        $sheetRinci->getStyle('J' . $curRow)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                    }
                    $curRow++;
                }
                if (count($grpRows) > 1) {
                    $subEndRow = $curRow - 1;
                    $sheetRinci->setCellValue('J' . $curRow, "=SUM(J{$subStartRow}:J{$subEndRow})");
                    $sheetRinci->getStyle('J' . $curRow)->getNumberFormat()->setFormatCode('_(* #,##0_);_(* \(#,##0\);_(* "-"_);_(@_)');
                    $sheetRinci->setCellValue('K' . $curRow, (int)($grp['rounded_subtotal'] ?? 0));
                    $sheetRinci->getStyle('K' . $curRow)->getFont()->setBold(true);
                    $sheetRinci->getStyle('K' . $curRow)->getNumberFormat()->setFormatCode('_(* #,##0_);_(* \(#,##0\);_(* "-"_);_(@_)');
                    $curRow++;
                }
            }
        }

        // Item 2: Uang Harian
        $curRow++; // 1 row separator
        $harianHeaderRow = $curRow;
        $sheetRinci->setCellValue('C' . $harianHeaderRow, '2');
        $sheetRinci->getStyle('C' . $harianHeaderRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheetRinci->setCellValue('D' . $harianHeaderRow, 'UANG HARIAN');
        $sheetRinci->getStyle('D' . $harianHeaderRow)->getFont()->setBold(true);
        $sheetRinci->setCellValue('L' . $harianHeaderRow, 'Rp.');
        $sheetRinci->getStyle('L' . $harianHeaderRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        $curRow++;
        $sheetRinci->setCellValue('D' . $curRow, 'Uang Makan, Uang Transport Lokal, Uang Saku selama :');
        $curRow++;

        $harianDetailsStart = $curRow;
        if (!empty($harianDetails)) {
            foreach ($harianDetails as $hd) {
                $sheetRinci->setCellValue('D' . $curRow, (int)($hd['days'] ?? 0));
                $sheetRinci->getStyle('D' . $curRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheetRinci->setCellValue('E' . $curRow, 'hari');
                $sheetRinci->getStyle('E' . $curRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheetRinci->setCellValue('F' . $curRow, 'x');
                $sheetRinci->getStyle('F' . $curRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheetRinci->setCellValue('G' . $curRow, 'Rp');
                $sheetRinci->getStyle('G' . $curRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheetRinci->setCellValue('H' . $curRow, (int)($hd['rate'] ?? 0));
                $sheetRinci->getStyle('H' . $curRow)->getNumberFormat()->setFormatCode('_(* #,##0_);_(* \(#,##0\);_(* "-"_);_(@_)');
                $sheetRinci->setCellValue('I' . $curRow, 'Rp');
                $sheetRinci->getStyle('I' . $curRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheetRinci->setCellValue('J' . $curRow, "=D{$curRow}*H{$curRow}");
                $sheetRinci->getStyle('J' . $curRow)->getNumberFormat()->setFormatCode('_(* #,##0_);_(* \(#,##0\);_(* "-"_);_(@_)');
                $curRow++;
            }
            $harianDetailsEnd = $curRow - 1;
            $sheetRinci->setCellValue('M' . $harianHeaderRow, "=SUM(J{$harianDetailsStart}:J{$harianDetailsEnd})");
        } else {
            $sheetRinci->setCellValue('M' . $harianHeaderRow, 0);
        }
        $sheetRinci->getStyle('M' . $harianHeaderRow)->getNumberFormat()->setFormatCode('_(* #,##0_);_(* \(#,##0\);_(* "-"_);_(@_)');

        // Item 3: Uang Penginapan
        $curRow++; // 1 row separator
        $penginapanHeaderRow = $curRow;
        $sheetRinci->setCellValue('C' . $penginapanHeaderRow, '3');
        $sheetRinci->getStyle('C' . $penginapanHeaderRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheetRinci->setCellValue('D' . $penginapanHeaderRow, 'UANG PENGINAPAN');
        $sheetRinci->getStyle('D' . $penginapanHeaderRow)->getFont()->setBold(true);
        $sheetRinci->setCellValue('L' . $penginapanHeaderRow, 'Rp.');
        $sheetRinci->getStyle('L' . $penginapanHeaderRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        $curRow++;
        $sheetRinci->setCellValue('D' . $curRow, 'Uang penginapan selama :');
        $curRow++;

        $penginapanDetailsStart = $curRow;
        if (!empty($penginapanDetails)) {
            foreach ($penginapanDetails as $pd) {
                $pNights = (int)($pd['nights'] ?? 0);
                $pRate   = ($pNights === 0) ? 0 : (int)($pd['rate'] ?? 0);
                $sheetRinci->setCellValue('D' . $curRow, $pNights);
                $sheetRinci->getStyle('D' . $curRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheetRinci->setCellValue('E' . $curRow, 'malam');
                $sheetRinci->getStyle('E' . $curRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheetRinci->setCellValue('F' . $curRow, 'x');
                $sheetRinci->getStyle('F' . $curRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheetRinci->setCellValue('G' . $curRow, 'Rp');
                $sheetRinci->getStyle('G' . $curRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheetRinci->setCellValue('H' . $curRow, $pRate);
                $sheetRinci->getStyle('H' . $curRow)->getNumberFormat()->setFormatCode('_(* #,##0_);_(* \(#,##0\);_(* "-"_);_(@_)');
                $sheetRinci->setCellValue('I' . $curRow, 'Rp');
                $sheetRinci->getStyle('I' . $curRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheetRinci->setCellValue('J' . $curRow, "=D{$curRow}*H{$curRow}");
                $sheetRinci->getStyle('J' . $curRow)->getNumberFormat()->setFormatCode('_(* #,##0_);_(* \(#,##0\);_(* "-"_);_(@_)');
                $curRow++;
            }
            $penginapanDetailsEnd = $curRow - 1;
            $sheetRinci->setCellValue('M' . $penginapanHeaderRow, "=SUM(J{$penginapanDetailsStart}:J{$penginapanDetailsEnd})");
        } else {
            $sheetRinci->setCellValue('M' . $penginapanHeaderRow, 0);
        }
        $sheetRinci->getStyle('M' . $penginapanHeaderRow)->getNumberFormat()->setFormatCode('_(* #,##0_);_(* \(#,##0\);_(* "-"_);_(@_)');

        $curRow++; // 1 row separator before JUMLAH

        // Outer table borders for C7:O($curRow - 1)
        $bodyEndRow = $curRow - 1;
        for ($r = 7; $r <= $bodyEndRow; $r++) {
            $sheetRinci->getStyle("C$r")->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $sheetRinci->getStyle("C$r")->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $sheetRinci->getStyle("D$r")->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $sheetRinci->getStyle("L$r")->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $sheetRinci->getStyle("M$r")->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $sheetRinci->getStyle("N$r")->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $sheetRinci->getStyle("O$r")->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        }

        // JUMLAH Row
        $totalRow = $curRow;
        $sheetRinci->setCellValue('D' . $totalRow, 'JUMLAH :');
        $sheetRinci->getStyle('D' . $totalRow)->getFont()->setBold(true);
        $sheetRinci->getStyle("C{$totalRow}:O{$totalRow}")->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheetRinci->getStyle("C{$totalRow}:O{$totalRow}")->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheetRinci->getStyle('C' . $totalRow)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheetRinci->getStyle('C' . $totalRow)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheetRinci->getStyle('D' . $totalRow)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $sheetRinci->setCellValue('L' . $totalRow, 'Rp.');
        $sheetRinci->getStyle('L' . $totalRow)->getFont()->setBold(true);
        $sheetRinci->getStyle('L' . $totalRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheetRinci->getStyle('L' . $totalRow)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $sheetRinci->setCellValue('M' . $totalRow, "=M7+M{$harianHeaderRow}+M{$penginapanHeaderRow}");
        $sheetRinci->getStyle('M' . $totalRow)->getFont()->setBold(true);
        $sheetRinci->getStyle('M' . $totalRow)->getNumberFormat()->setFormatCode('_(* #,##0_);_(* \(#,##0\);_(* "-"_);_(@_)');
        $sheetRinci->getStyle('M' . $totalRow)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $sheetRinci->getStyle('N' . $totalRow)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheetRinci->getStyle('O' . $totalRow)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $curRow++;

        // TERBILANG Row
        $terbilangRow = $curRow;
        $sheetRinci->setCellValue('D' . $terbilangRow, 'TERBILANG : ');
        $sheetRinci->getStyle('D' . $terbilangRow)->getFont()->setBold(true);
        
        $terbilangText = $terbilangIndo($totalBiaya) . ' Rupiah,-';
        $sheetRinci->setCellValue('G' . $terbilangRow, $terbilangText);
        $sheetRinci->getStyle('G' . $terbilangRow)->getFont()->setBold(true);

        $sheetRinci->getStyle("C{$terbilangRow}:O{$terbilangRow}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $sheetRinci->getStyle("C{$terbilangRow}:O{$terbilangRow}")->getFill()->getStartColor()->setARGB('FFF0F2F5');
        $sheetRinci->getStyle("C{$terbilangRow}:O{$terbilangRow}")->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheetRinci->getStyle("C{$terbilangRow}:O{$terbilangRow}")->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheetRinci->getStyle('C' . $terbilangRow)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheetRinci->getStyle('C' . $terbilangRow)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheetRinci->getStyle('E' . $terbilangRow)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheetRinci->getStyle('K' . $terbilangRow)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheetRinci->getStyle('M' . $terbilangRow)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheetRinci->getStyle('O' . $terbilangRow)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $curRow += 2; // spacing before Signatures

        // Signatures Section
        $sigRow1 = $curRow;
        $sheetRinci->mergeCells("C{$sigRow1}:H{$sigRow1}");
        $sheetRinci->setCellValue('C' . $sigRow1, 'Telah dibayar uang sebesar');
        $sheetRinci->getStyle('C' . $sigRow1)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheetRinci->mergeCells("L{$sigRow1}:O{$sigRow1}");
        $sheetRinci->setCellValue('L' . $sigRow1, 'Pekanbaru,        ' . $bulanTahunStr);
        $sheetRinci->getStyle('L' . $sigRow1)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sigRow2 = $sigRow1 + 1;
        $sheetRinci->setCellValue('L' . $sigRow2, 'Telah terima sejumlah uang sebesar:');

        $sigRow3 = $sigRow2 + 1;
        $sheetRinci->setCellValue('D' . $sigRow3, 'Rp.');
        $sheetRinci->getStyle('D' . $sigRow3)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheetRinci->mergeCells("E{$sigRow3}:G{$sigRow3}");
        $sheetRinci->setCellValue('E' . $sigRow3, "=M{$totalRow}");
        $sheetRinci->getStyle('E' . $sigRow3)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheetRinci->getStyle('E' . $sigRow3)->getNumberFormat()->setFormatCode('_(* #,##0_);_(* \(#,##0\);_(* "-"_);_(@_)');

        $sigRow4 = $sigRow3 + 1;
        $sheetRinci->setCellValue('L' . $sigRow4, 'Rp.');
        $sheetRinci->getStyle('L' . $sigRow4)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheetRinci->setCellValue('M' . $sigRow4, "=E{$sigRow3}");
        $sheetRinci->getStyle('M' . $sigRow4)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheetRinci->getStyle('M' . $sigRow4)->getNumberFormat()->setFormatCode('_(* #,##0_);_(* \(#,##0\);_(* "-"_);_(@_)');

        $sigRow5 = $sigRow4 + 1;
        $sheetRinci->mergeCells("C{$sigRow5}:H{$sigRow5}");
        $sheetRinci->setCellValue('C' . $sigRow5, "=L{$sigRow1}");
        $sheetRinci->getStyle('C' . $sigRow5)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sigRow6 = $sigRow5 + 1;
        $sheetRinci->mergeCells("C{$sigRow6}:H{$sigRow6}");
        $sheetRinci->setCellValue('C' . $sigRow6, 'Bendahara Pengeluaran,');
        $sheetRinci->getStyle('C' . $sigRow6)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheetRinci->mergeCells("K{$sigRow6}:O{$sigRow6}");
        $sheetRinci->setCellValue('K' . $sigRow6, 'Yang Menerima :');
        $sheetRinci->getStyle('K' . $sigRow6)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sigRow7 = $sigRow6 + 4;
        $sheetRinci->mergeCells("C{$sigRow7}:H{$sigRow7}");
        $sheetRinci->setCellValue('C' . $sigRow7, $bendaharaNama);
        $sheetRinci->getStyle('C' . $sigRow7)->getFont()->setBold(true)->setUnderline(true);
        $sheetRinci->getStyle('C' . $sigRow7)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheetRinci->mergeCells("K{$sigRow7}:O{$sigRow7}");
        $sheetRinci->setCellValue('K' . $sigRow7, $namaUtama);
        $sheetRinci->getStyle('K' . $sigRow7)->getFont()->setBold(true)->setUnderline(true);
        $sheetRinci->getStyle('K' . $sigRow7)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sigRow8 = $sigRow7 + 1;
        $sheetRinci->mergeCells("C{$sigRow8}:H{$sigRow8}");
        $sheetRinci->setCellValue('C' . $sigRow8, $bendaharaNip);
        $sheetRinci->getStyle('C' . $sigRow8)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheetRinci->mergeCells("K{$sigRow8}:O{$sigRow8}");
        $sheetRinci->setCellValue('K' . $sigRow8, $nipLabelUtama . $nipUtama);
        $sheetRinci->getStyle('K' . $sigRow8)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Separator line
        $sepRow = $sigRow8 + 1;
        $sheetRinci->getStyle("C{$sepRow}:O{$sepRow}")->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

        // Perhitungan SPD Rampung Title
        $rampTitleRow = $sepRow + 2;
        $sheetRinci->mergeCells("C{$rampTitleRow}:O{$rampTitleRow}");
        $sheetRinci->setCellValue('C' . $rampTitleRow, 'P E R H I T U N G A N    S P D  R A M P U N G ');
        $sheetRinci->getStyle('C' . $rampTitleRow)->getFont()->setSize(12)->setBold(true)->setUnderline(true);
        $sheetRinci->getStyle('C' . $rampTitleRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Rampung Details
        $rDetails1 = $rampTitleRow + 2;
        $sheetRinci->setCellValue('C' . $rDetails1, 'Ditetapkan Sejumlah……………………………………………………………….');
        $sheetRinci->setCellValue('M' . $rDetails1, "=M{$sigRow4}");
        $sheetRinci->getStyle('M' . $rDetails1)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');

        $rDetails2 = $rDetails1 + 1;
        $sheetRinci->setCellValue('C' . $rDetails2, 'Yang dibayar semula ……………………………………………………………….');
        $sheetRinci->setCellValue('M' . $rDetails2, 0);
        $sheetRinci->getStyle('M' . $rDetails2)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
        $sheetRinci->getStyle('M' . $rDetails2)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $rDetails3 = $rDetails2 + 1;
        $sheetRinci->setCellValue('C' . $rDetails3, 'Sisa kurang / Lebih    ……………………………………………………………..');
        $sheetRinci->setCellValue('M' . $rDetails3, 0);
        $sheetRinci->getStyle('M' . $rDetails3)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');

        // PPK Signatures
        $ppkRow1 = $rDetails3 + 3;
        $sheetRinci->mergeCells("K{$ppkRow1}:O{$ppkRow1}");
        $sheetRinci->setCellValue('K' . $ppkRow1, $ppkJabatanLine1);
        $sheetRinci->getStyle('K' . $ppkRow1)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $ppkRow2 = $ppkRow1 + 1;
        $sheetRinci->mergeCells("K{$ppkRow2}:O{$ppkRow2}");
        $sheetRinci->setCellValue('K' . $ppkRow2, $ppkJabatanLine2);
        $sheetRinci->getStyle('K' . $ppkRow2)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $ppkRow3 = $ppkRow2 + 5;
        $sheetRinci->mergeCells("K{$ppkRow3}:O{$ppkRow3}");
        $sheetRinci->setCellValue('K' . $ppkRow3, $ppkNama);
        $sheetRinci->getStyle('K' . $ppkRow3)->getFont()->setBold(true)->setUnderline(true);
        $sheetRinci->getStyle('K' . $ppkRow3)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $ppkRow4 = $ppkRow3 + 1;
        $sheetRinci->mergeCells("K{$ppkRow4}:O{$ppkRow4}");
        $sheetRinci->setCellValue('K' . $ppkRow4, $ppkNip);
        $sheetRinci->getStyle('K' . $ppkRow4)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheetRinci->getPageSetup()->setPrintArea('C1:O' . $ppkRow4);

        // ---------------------------------------------------------
        // SHEET 1: KWITANSI
        // ---------------------------------------------------------
        $sheetKwitansi = $spreadsheet->createSheet();
        $sheetKwitansi->setTitle('KWITANSI');
        $sheetKwitansi->setShowGridLines(true);

        // Page Break Preview Mode & Print Setup for KWITANSI
        $sheetKwitansi->getSheetView()->setView(\PhpOffice\PhpSpreadsheet\Worksheet\SheetView::SHEETVIEW_PAGE_BREAK_PREVIEW);
        $sheetKwitansi->getPageSetup()->setPrintArea('B1:T52');
        $sheetKwitansi->getPageSetup()->setFitToPage(true);
        $sheetKwitansi->getPageSetup()->setFitToWidth(1);
        $sheetKwitansi->getPageSetup()->setFitToHeight(1);
        $sheetKwitansi->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
        $sheetKwitansi->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

        // Embed Kop Surat Drawing at C2 if available
        $kopImagePath = '';
        if (!empty($kopSurat['image_url'])) {
            $p = FCPATH . ltrim($kopSurat['image_url'], '/');
            if (file_exists($p)) {
                $kopImagePath = $p;
            }
        }
        if (empty($kopImagePath) && file_exists('do_not_upload/temp/extracted_kop.jpg')) {
            $kopImagePath = 'do_not_upload/temp/extracted_kop.jpg';
        }

        if (!empty($kopImagePath) && file_exists($kopImagePath)) {
            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setName('Kop Surat');
            $drawing->setDescription('Kop Surat');
            $drawing->setPath($kopImagePath);
            $drawing->setCoordinates('C2');
            $drawing->setWidthAndHeight(960, 152);
            $drawing->setOffsetX(5);
            $drawing->setOffsetY(10);
            $drawing->setWorksheet($sheetKwitansi);
        }

        $colWidthsKwitansi = [
            'A' => 1.16,  'B' => 0.66,  'C' => 4.33,  'D' => 9.33,  'E' => 19.83,
            'F' => 2.00,  'G' => 4.50,  'H' => 5.33,  'I' => 19.50, 'J' => 2.50,
            'K' => 4.00,  'L' => 10.00, 'M' => 12.00, 'N' => 3.16,  'O' => 2.66,
            'P' => 6.50,  'Q' => 8.00,  'R' => 6.00,  'S' => 6.00,  'T' => 13.00
        ];
        foreach ($colWidthsKwitansi as $col => $w) {
            $sheetKwitansi->getColumnDimension($col)->setWidth($w);
        }

        $rowHeightsKwitansi = [
            1 => 6.75, 8 => 22.50, 9 => 18.00, 10 => 30.75, 11 => 30.75, 12 => 30.75,
            13 => 18.00, 14 => 18.00, 15 => 28.00, 16 => 18.00, 17 => 18.00, 18 => 18.00,
            19 => 18.00, 20 => 18.00, 21 => 18.00, 22 => 37.50, 23 => 18.00, 24 => 18.00,
            25 => 104.25, 26 => 27.75, 27 => 19.00, 28 => 18.00, 29 => 18.00, 30 => 17.50,
            31 => 21.00, 32 => 35.25, 33 => 18.00, 34 => 18.00, 35 => 18.00, 36 => 18.00,
            37 => 18.00, 38 => 18.00, 39 => 18.00, 40 => 18.00, 41 => 18.00, 42 => 18.00,
            43 => 18.00, 44 => 18.00, 45 => 19.00
        ];
        foreach ($rowHeightsKwitansi as $r => $h) {
            $sheetKwitansi->getRowDimension($r)->setRowHeight($h);
        }

        $sheetKwitansi->getStyle('A1:T48')->getFont()->setName('Arial')->setSize(11);

        // Outer Medium Border Box B9:T45
        $sheetKwitansi->getStyle('B9:T9')->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
        $sheetKwitansi->getStyle('B9:B45')->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
        $sheetKwitansi->getStyle('T9:T45')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
        $sheetKwitansi->getStyle('B45:T45')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

        // Top Right Header Box K10:T12
        $sheetKwitansi->mergeCells('K10:O10');
        $sheetKwitansi->setCellValue('K10', 'Tahun Anggaran');
        $sheetKwitansi->getStyle('K10')->getFont()->setSize(14);
        $sheetKwitansi->getStyle('K10')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheetKwitansi->getStyle('K10:O10')->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheetKwitansi->getStyle('K10:O10')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheetKwitansi->getStyle('K10')->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $sheetKwitansi->mergeCells('P10:T10');
        $sheetKwitansi->setCellValue('P10', $tahunAnggaranStr);
        $sheetKwitansi->getStyle('P10')->getFont()->setSize(14);
        $sheetKwitansi->getStyle('P10')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheetKwitansi->getStyle('P10:T10')->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheetKwitansi->getStyle('P10:T10')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheetKwitansi->getStyle('P10')->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $sheetKwitansi->mergeCells('K11:O11');
        $sheetKwitansi->setCellValue('K11', 'Nomor Bukti');
        $sheetKwitansi->getStyle('K11')->getFont()->setSize(14);
        $sheetKwitansi->getStyle('K11')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheetKwitansi->getStyle('K11:O11')->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheetKwitansi->getStyle('K11:O11')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheetKwitansi->getStyle('K11')->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $sheetKwitansi->mergeCells('P11:T11');
        $sheetKwitansi->setCellValue('P11', '');
        $sheetKwitansi->getStyle('P11')->getFont()->setSize(14)->setBold(true);
        $sheetKwitansi->getStyle('P11:T11')->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheetKwitansi->getStyle('P11:T11')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheetKwitansi->getStyle('P11')->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $sheetKwitansi->mergeCells('K12:O12');
        $sheetKwitansi->setCellValue('K12', 'Mata Anggaran');
        $sheetKwitansi->getStyle('K12')->getFont()->setSize(14);
        $sheetKwitansi->getStyle('K12')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheetKwitansi->getStyle('K12:O12')->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheetKwitansi->getStyle('K12:O12')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheetKwitansi->getStyle('K12')->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $sheetKwitansi->mergeCells('P12:T12');
        $sheetKwitansi->setCellValue('P12', $mataAnggaranText);
        $sheetKwitansi->getStyle('P12')->getFont()->setSize(14)->setBold(true);
        $sheetKwitansi->getStyle('P12')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheetKwitansi->getStyle('P12:T12')->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheetKwitansi->getStyle('P12:T12')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheetKwitansi->getStyle('P12')->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Title Row 15
        $sheetKwitansi->mergeCells('B15:R15');
        $sheetKwitansi->setCellValue('B15', 'K U I T A N S I');
        $sheetKwitansi->getStyle('B15')->getFont()->setSize(22)->setBold(true)->setUnderline(true);
        $sheetKwitansi->getStyle('B15')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Body Row 18
        $sheetKwitansi->setCellValue('C18', 'Sudah di terima dari');
        $sheetKwitansi->getStyle('C18')->getFont()->setSize(14);
        $sheetKwitansi->setCellValue('F18', ':');
        $sheetKwitansi->getStyle('F18')->getFont()->setSize(14);
        $sheetKwitansi->setCellValue('G18', 'PEJABAT PEMBUAT KOMITMEN PELAKSANAAN PRASARANA STRATEGIS');
        $sheetKwitansi->getStyle('G18')->getFont()->setSize(14)->setBold(true);

        // Row 20
        $sheetKwitansi->setCellValue('C20', 'Jumlah Uang');
        $sheetKwitansi->getStyle('C20')->getFont()->setSize(14);
        $sheetKwitansi->getStyle('C20')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        $sheetKwitansi->setCellValue('F20', ':');
        $sheetKwitansi->getStyle('F20')->getFont()->setSize(14);
        $sheetKwitansi->getStyle('F20')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        $sheetKwitansi->setCellValue('G20', 'Rp.');
        $sheetKwitansi->getStyle('G20')->getFont()->setSize(14)->setBold(true);
        $sheetKwitansi->getStyle('G20')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        $sheetKwitansi->mergeCells('H20:I20');
        $sheetKwitansi->setCellValue('H20', "=RINCI!M{$totalRow}");
        $sheetKwitansi->getStyle('H20')->getFont()->setSize(14)->setBold(true);
        $sheetKwitansi->getStyle('H20')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheetKwitansi->getStyle('H20')->getNumberFormat()->setFormatCode('_(* #,##0_);_(* \(#,##0\);_(* "-"_);_(@_)');

        $sheetKwitansi->setCellValue('P20', ' ');
        $sheetKwitansi->getStyle('P20')->getFont()->setSize(14);

        // Row 22 Terbilang
        $sheetKwitansi->setCellValue('C22', 'Terbilang');
        $sheetKwitansi->getStyle('C22')->getFont()->setSize(14);
        $sheetKwitansi->getStyle('C22')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        $sheetKwitansi->setCellValue('F22', ':');
        $sheetKwitansi->getStyle('F22')->getFont()->setSize(14);
        $sheetKwitansi->getStyle('F22')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        $sheetKwitansi->mergeCells('G22:T22');
        $sheetKwitansi->setCellValue('G22', $terbilangText);
        $sheetKwitansi->getStyle('G22')->getFont()->setSize(14)->setBold(true)->setItalic(true);
        $sheetKwitansi->getStyle('G22')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)->setWrapText(true);

        // Row 24-26 Untuk Pembayaran
        $sheetKwitansi->setCellValue('C24', 'Untuk Pembayaran');
        $sheetKwitansi->getStyle('C24')->getFont()->setSize(14);
        $sheetKwitansi->setCellValue('F24', ':');
        $sheetKwitansi->getStyle('F24')->getFont()->setSize(14);

        // Resolve Dasar SPT text
        $dasarSptIds = json_decode((string) ($row['dasar_spt_ids_json'] ?? '[]'), true) ?: [];
        $dasarTexts = [];
        if (!empty($dasarSptIds)) {
            $numericIds = array_filter($dasarSptIds, 'is_numeric');
            $customTexts = array_diff($dasarSptIds, $numericIds);
            if (!empty($numericIds)) {
                $dbDasar = (new \App\Models\MstDasarSptModel())->whereIn('id', $numericIds)->orderBy('id', 'ASC')->findAll();
                foreach ($dbDasar as $dD) {
                    if (!empty($dD['uraian'])) $dasarTexts[] = $dD['uraian'];
                }
            }
            foreach ($customTexts as $cT) {
                if (!empty($cT)) $dasarTexts[] = $cT;
            }
        }

        $dasarSptStr = '';
        if (!empty($dasarTexts)) {
            $dasarSptStr = implode('; ', $dasarTexts);
        } elseif (!empty($row['nomor_surat_tugas'])) {
            $dasarSptStr = 'Surat Tugas Nomor: ' . $row['nomor_surat_tugas'];
        }

        $fullPembayaranText = "Perjalanan Dinas a.n. " . $namaUtama . " " . $jabatanUtama . " dalam rangka " . $tujuanMaksud;
        if (!empty($dasarSptStr)) {
            $fullPembayaranText .= ", sesuai dengan " . $dasarSptStr;
        }
        $fullPembayaranText .= ", sebagaimana daftar perincian terlampir.";

        $sheetKwitansi->mergeCells('G24:T26');
        $sheetKwitansi->setCellValue('G24', $fullPembayaranText);
        $sheetKwitansi->getStyle('G24')->getFont()->setSize(14);
        $sheetKwitansi->getStyle('G24')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_JUSTIFY)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP)->setWrapText(true);

        // Row 27-31 SPD details
        $sheetKwitansi->setCellValue('C27', 'Berdasarkan SPD');
        $sheetKwitansi->getStyle('C27')->getFont()->setSize(14);

        $sheetKwitansi->setCellValue('G27', ' ');
        $sheetKwitansi->getStyle('G27')->getFont()->setSize(14);
        $sheetKwitansi->getStyle('G27')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_JUSTIFY);

        $sheetKwitansi->setCellValue('C28', 'Nomor');
        $sheetKwitansi->getStyle('C28')->getFont()->setSize(14);
        $sheetKwitansi->setCellValue('F28', ':');
        $sheetKwitansi->getStyle('F28')->getFont()->setSize(14);
        $sheetKwitansi->setCellValue('G28', $nomorSPD);
        $sheetKwitansi->getStyle('G28')->getFont()->setName('Tahoma')->setSize(14);

        $sheetKwitansi->setCellValue('C29', 'Tanggal');
        $sheetKwitansi->getStyle('C29')->getFont()->setSize(14);
        $sheetKwitansi->setCellValue('F29', ':');
        $sheetKwitansi->getStyle('F29')->getFont()->setSize(14);
        $sheetKwitansi->mergeCells('G29:T29');
        $sheetKwitansi->setCellValue('G29', '=RIGHT(RINCI!C3,12)');
        $sheetKwitansi->getStyle('G29')->getFont()->setSize(14);
        $sheetKwitansi->getStyle('G29')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        $sheetKwitansi->setCellValue('C30', 'Untuk perjalanan dinas dari');
        $sheetKwitansi->getStyle('C30')->getFont()->setSize(14);
        $sheetKwitansi->setCellValue('F30', ':');
        $sheetKwitansi->getStyle('F30')->getFont()->setSize(14);
        $sheetKwitansi->mergeCells('G30:T30');
        $sheetKwitansi->setCellValue('G30', 'Pekanbaru - ' . $kotaTujuan);
        $sheetKwitansi->getStyle('G30')->getFont()->setSize(14);
        $sheetKwitansi->getStyle('G30')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        $sheetKwitansi->setCellValue('C31', 'Berangkat dari tanggal');
        $sheetKwitansi->getStyle('C31')->getFont()->setSize(14);
        $sheetKwitansi->setCellValue('F31', ':');
        $sheetKwitansi->getStyle('F31')->getFont()->setSize(14);
        $sheetKwitansi->mergeCells('G31:T31');
        $sheetKwitansi->setCellValue('G31', ($tglBerangkat === $tglKembali) ? $tglBerangkat : ($tglBerangkat . ' s/d ' . $tglKembali));
        $sheetKwitansi->getStyle('G31')->getFont()->setSize(14);
        $sheetKwitansi->getStyle('G31')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        // Signatures Section Rows 34-42
        $sheetKwitansi->mergeCells('B34:I34');
        $sheetKwitansi->setCellValue('B34', 'An. Kuasa Pengguna Anggaran');
        $sheetKwitansi->getStyle('B34')->getFont()->setSize(14);
        $sheetKwitansi->getStyle('B34')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheetKwitansi->mergeCells('L34:T34');
        $sheetKwitansi->setCellValue('L34', 'Pekanbaru,        ' . $bulanTahunStr);
        $sheetKwitansi->getStyle('L34')->getFont()->setSize(14);
        $sheetKwitansi->getStyle('L34')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $ppkNama = 'NURHIDAYAT NUGROHO, S.Ars.';
        $ppkNip  = 'NIP. 19901221 201802 1 001';

        $sheetKwitansi->mergeCells('B35:I35');
        $sheetKwitansi->setCellValue('B35', 'Pejabat Pembuat Komitmen');
        $sheetKwitansi->getStyle('B35')->getFont()->setSize(14);
        $sheetKwitansi->getStyle('B35')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        list($jab1, $jab2) = $this->splitJabatanTwoLines($jabatanUtama);

        $sheetKwitansi->mergeCells('L35:T35');
        $sheetKwitansi->setCellValue('L35', $jab1);
        $sheetKwitansi->getStyle('L35')->getFont()->setSize(14);
        $sheetKwitansi->getStyle('L35')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheetKwitansi->mergeCells('B36:I36');
        $sheetKwitansi->setCellValue('B36', 'Pelaksanaan Prasarana Strategis');
        $sheetKwitansi->getStyle('B36')->getFont()->setSize(14);
        $sheetKwitansi->getStyle('B36')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheetKwitansi->mergeCells('L36:T36');
        $sheetKwitansi->setCellValue('L36', $jab2);
        $sheetKwitansi->getStyle('L36')->getFont()->setSize(14);
        $sheetKwitansi->getStyle('L36')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheetKwitansi->mergeCells('B41:I41');
        $sheetKwitansi->setCellValue('B41', $ppkNama);
        $sheetKwitansi->getStyle('B41')->getFont()->setSize(14)->setBold(true)->setUnderline(true);
        $sheetKwitansi->getStyle('B41')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheetKwitansi->mergeCells('L41:T41');
        $sheetKwitansi->setCellValue('L41', $namaUtama);
        $sheetKwitansi->getStyle('L41')->getFont()->setSize(14)->setBold(true)->setUnderline(true);
        $sheetKwitansi->getStyle('L41')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheetKwitansi->mergeCells('B42:I42');
        $sheetKwitansi->setCellValue('B42', $ppkNip);
        $sheetKwitansi->getStyle('B42')->getFont()->setSize(14);
        $sheetKwitansi->getStyle('B42')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheetKwitansi->mergeCells('M42:S42');
        $sheetKwitansi->setCellValue('M42', $nipLabelUtama . $nipUtama);
        $sheetKwitansi->getStyle('M42')->getFont()->setSize(14);
        $sheetKwitansi->getStyle('M42')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Enable Sheet Protection (Lock sheet against accidental editing)
        $sheetRinci->getProtection()->setSheet(true);
        $sheetRinci->getProtection()->setPassword('psriau');

        $sheetKwitansi->getProtection()->setSheet(true);
        $sheetKwitansi->getProtection()->setPassword('psriau');

        // Reset active sheet to RINCI (index 0)
        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    public function perjalananDinasCetakPeriode()
    {
        if (! $this->canViewLaporan()) {
            return redirect()->to(site_url('/admin'));
        }

        $startDate = trim((string) $this->request->getGet('start_date'));
        $endDate = trim((string) $this->request->getGet('end_date'));

        if ($startDate === '' || $endDate === '') {
            return redirect()->to(site_url('admin/surat/perjalanan-dinas'))->with('error', 'Silakan pilih tanggal awal dan tanggal akhir cetak.');
        }

        $model = new LaporanPerjalananDinasModel();
        $builder = $model->where('periode_mulai >=', $startDate);
        $builder->where('periode_selesai <=', $endDate);
        
        $rows = $builder->orderBy('periode_mulai', 'ASC')
                        ->orderBy('id', 'ASC')
                        ->findAll();

        if ($rows === []) {
            return redirect()->to(site_url('admin/surat/perjalanan-dinas'))->with('error', 'Tidak ada data perjalanan dinas dalam periode tersebut.');
        }

        $dasarSptModel = new \App\Models\MstDasarSptModel();
        $allDasar = $dasarSptModel->findAll();
        $dasarLookup = [];
        foreach ($allDasar as $d) {
            $dasarLookup[(int) $d['id']] = $d;
        }

        $records = [];
        foreach ($rows as $row) {
            $dasarIds = json_decode((string) ($row['dasar_spt_ids_json'] ?? '[]'), true) ?: [];
            $dasarRows = [];
            foreach ($dasarIds as $dId) {
                if (is_numeric($dId)) {
                    if (isset($dasarLookup[(int) $dId])) {
                        $dasarRows[] = $dasarLookup[(int) $dId];
                    }
                } else {
                    $dasarRows[] = [
                        'id' => 0,
                        'uraian' => (string) $dId
                    ];
                }
            }

            $periodeMulai = trim((string) ($row['periode_mulai'] ?? ''));
            $periodeSelesai = trim((string) ($row['periode_selesai'] ?? ''));
            
            $durationDays = 0;
            $durationWord = '';
            if ($periodeMulai !== '' && $periodeSelesai !== '') {
                try {
                    $start = new \DateTime($periodeMulai);
                    $end = new \DateTime($periodeSelesai);
                    $durationDays = $start->diff($end)->days + 1;
                    $durationWord = function_exists('terbilang_angka') ? terbilang_angka($durationDays) : '';
                } catch (\Throwable $e) {}
            }

            $records[] = [
                'nomor_surat_tugas' => (string) ($row['nomor_surat_tugas'] ?? ''),
                'periode_mulai' => $periodeMulai,
                'periode_selesai' => $periodeSelesai,
                'duration_days' => $durationDays,
                'duration_word' => $durationWord,
                'kota_tujuan' => (string) ($row['kota_tujuan'] ?? ''),
                'tujuan' => (string) ($row['tujuan'] ?? ''),
                'sasaran' => (string) ($row['sasaran'] ?? ''),
                'pelaksana' => $this->sortPelaksanaByStrukturOrganisasi($this->decodeJsonArray((string) ($row['pelaksana_json'] ?? '[]'))),
                'tanggal_tanda_tangan' => (string) ($row['tanggal_tanda_tangan'] ?? ''),
                'diketahui_oleh' => $this->decodeJsonObject((string) ($row['diketahui_oleh_json'] ?? '{}')),
                'dasar_spt' => $dasarRows,
                'kop_surat_id' => isset($row['kop_surat_id']) ? (int) $row['kop_surat_id'] : null,
                'mata_anggaran' => $this->resolveMataAnggaran($row),
            ];
        }

        $html = view('admin/laporan/surat_tugas_bulk_pdf', [
            'records' => $records,
        ]);

        $dompdfOptions = new \Dompdf\Options();
        $dompdfOptions->set('isRemoteEnabled', true);
        $dompdfOptions->set('isHtml5ParserEnabled', true);

        $dompdf = new \Dompdf\Dompdf($dompdfOptions);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="rekap_surat_tugas_' . $startDate . '_to_' . $endDate . '.pdf"')
            ->setBody($dompdf->output());
    }

    private function renderSuratTugasHtml(array $row, array $dasarRows): string
    {
        $periodeMulai = trim((string) ($row['periode_mulai'] ?? ''));
        $periodeSelesai = trim((string) ($row['periode_selesai'] ?? ''));
        
        $durationDays = 0;
        $durationWord = '';
        if ($periodeMulai !== '' && $periodeSelesai !== '') {
            try {
                $start = new \DateTime($periodeMulai);
                $end = new \DateTime($periodeSelesai);
                $durationDays = $start->diff($end)->days + 1;
                $durationWord = function_exists('terbilang_angka') ? terbilang_angka($durationDays) : '';
            } catch (\Throwable $e) {}
        }

        $data = [
            'nomor_surat_tugas' => (string) ($row['nomor_surat_tugas'] ?? ''),
            'periode_mulai' => $periodeMulai,
            'periode_selesai' => $periodeSelesai,
            'duration_days' => $durationDays,
            'duration_word' => $durationWord,
            'kota_tujuan' => (string) ($row['kota_tujuan'] ?? ''),
            'tujuan' => (string) ($row['tujuan'] ?? ''),
            'sasaran' => (string) ($row['sasaran'] ?? ''),
            'pelaksana' => $this->sortPelaksanaByStrukturOrganisasi($this->decodeJsonArray((string) ($row['pelaksana_json'] ?? '[]'))),
            'tanggal_tanda_tangan' => (string) ($row['tanggal_tanda_tangan'] ?? ''),
            'diketahui_oleh' => $this->decodeJsonObject((string) ($row['diketahui_oleh_json'] ?? '{}')),
            'dasar_spt' => $dasarRows,
            'mata_anggaran' => $this->resolveMataAnggaran($row),
        ];

        return view('admin/laporan/surat_tugas_pdf', [
            'data' => $data,
        ]);
    }

    protected function canVerifyLaporan(): bool
    {
        $role = strtolower((string) session()->get('role'));
        return in_array($role, ['admin', 'editor', 'keuangan', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);
    }

    public function perjalananDinasHapus(int $id): RedirectResponse
    {
        if (! $this->canManageLaporan()) {
            return redirect()->to(site_url('admin/surat/perjalanan-dinas'))->with('error', 'Anda tidak memiliki akses untuk menghapus laporan perjalanan dinas.');
        }

        $db = db_connect();
        $row = $db->table('laporan_perjalanan_dinas')->where('id', $id)->get()->getRowArray();
        if (! is_array($row)) {
            return redirect()->to(site_url('admin/surat/perjalanan-dinas'))->with('error', 'Laporan perjalanan dinas tidak ditemukan.');
        }

        // Delete verified file if exists
        if (! empty($row['verified_spt_path'])) {
            $this->deleteStoredFiles([$row['verified_spt_path']]);
        }

        // Delete support docs if exists
        $docs = json_decode((string) ($row['dokumen_pendukung_json'] ?? '[]'), true);
        if (is_array($docs) && $docs !== []) {
            $paths = [];
            foreach ($docs as $doc) {
                if (! empty($doc['file_path'])) {
                    $paths[] = $doc['file_path'];
                }
            }
            if ($paths !== []) {
                $this->deleteStoredFiles($paths);
            }
        }

        if (! empty($row['disposisi_id'])) {
            $db->table('disposisi_perjalanan_dinas')->where('id', $row['disposisi_id'])->delete();
        }

        $db->table('laporan_perjalanan_dinas')->where('id', $id)->delete();

        return redirect()->to(site_url('admin/surat/perjalanan-dinas'))->with('success', 'Laporan perjalanan dinas berhasil dihapus.');
    }

    private function respondPerjalananDinasDataTable(callable $queryFactory, callable $filterApplier, array $searchColumns, array $orderColumns, ?callable $rowMapper = null)
    {
        try {
            $draw = $this->getDataTableDraw();
            $start = $this->getDataTableStart();
            $length = $this->getDataTableLength();
            $search = $this->getDataTableSearchTerm();
            $orderIndex = $this->getDataTableOrderColumnIndex();
            $orderDirection = $this->getDataTableOrderDirection();

            $totalBuilder = $queryFactory();
            $filterApplier($totalBuilder);
            $recordsTotal = (int) $totalBuilder->countAllResults(false);

            $filteredBuilder = $queryFactory();
            $filterApplier($filteredBuilder);
            $this->applyDataTableSearch($filteredBuilder, $searchColumns, $search);
            $recordsFiltered = (int) $filteredBuilder->countAllResults(false);

            $orderColumn = $orderColumns[$orderIndex] ?? $orderColumns[0] ?? '';
            if ($orderColumn !== '') {
                $filteredBuilder->orderBy($orderColumn, $orderDirection);
            }

            $rows = $filteredBuilder->limit($length, $start)->get()->getResultArray();

            if ($rowMapper !== null) {
                $rows = array_map($rowMapper, $rows);
            }

            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $rows,
            ]);
        } catch (\Throwable $exception) {
            log_message('error', 'DataTable perjalanan dinas gagal dimuat: ' . $exception->getMessage());

            return $this->response->setJSON([
                'draw' => $this->getDataTableDraw(),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Gagal memuat data perjalanan dinas.',
            ]);
        }
    }

    private function applyDataTableSearch($builder, array $columns, string $searchTerm): void
    {
        $searchTerm = trim($searchTerm);
        if ($searchTerm === '' || $columns === []) {
            return;
        }

        $builder->groupStart();
        foreach ($columns as $index => $column) {
            if ($index === 0) {
                $builder->like($column, $searchTerm);
                continue;
            }

            $builder->orLike($column, $searchTerm);
        }
        $builder->groupEnd();
    }

    private function getDataTableDraw(): int
    {
        return max(0, (int) $this->request->getGet('draw'));
    }

    private function getDataTableStart(): int
    {
        return max(0, (int) $this->request->getGet('start'));
    }

    private function getDataTableLength(): int
    {
        $length = (int) $this->request->getGet('length');

        return $length > 0 ? $length : 10;
    }

    private function getDataTableSearchTerm(): string
    {
        $search = $this->request->getGet('search');
        if (! is_array($search)) {
            return '';
        }

        return trim((string) ($search['value'] ?? ''));
    }

    private function getDataTableOrderColumnIndex(): int
    {
        $order = $this->request->getGet('order');
        if (! is_array($order) || $order === []) {
            return 0;
        }

        $first = $order[0] ?? [];
        if (! is_array($first)) {
            return 0;
        }

        return max(0, (int) ($first['column'] ?? 0));
    }

    private function getDataTableOrderDirection(): string
    {
        $order = $this->request->getGet('order');
        if (! is_array($order) || $order === []) {
            return 'DESC';
        }

        $first = $order[0] ?? [];
        if (! is_array($first)) {
            return 'DESC';
        }

        return strtolower((string) ($first['dir'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';
    }

    private function isDataTableRequest(): bool
    {
        return $this->request->getGet('draw') !== null
            && $this->request->getGet('start') !== null
            && $this->request->getGet('length') !== null;
    }

    private function writeSmokeLog(string $label, $payload): void
    {
        $logDir = WRITEPATH . 'logs';
        if (! is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        $file = $logDir . DIRECTORY_SEPARATOR . 'smoke_test.log';
        $entry = date('Y-m-d H:i:s') . ' | ' . $label . ' | ' . json_encode($payload, JSON_UNESCAPED_UNICODE) . PHP_EOL;

        // Write to file
        $result1 = @file_put_contents($file, $entry, FILE_APPEND | LOCK_EX);

        // Also write to PHP error log for backup
        $logLine = 'SMOKE_LOG [' . $label . ']: ' . json_encode($payload, JSON_UNESCAPED_UNICODE);
        error_log($logLine);
    }
    public function perjalananDinasUploadVerified(int $id)
    {
        if (! $this->canViewLaporan()) {
            return redirect()->to(site_url('/admin'));
        }

        $model = new LaporanPerjalananDinasModel();
        $row = $model->find($id);
        if (! is_array($row)) {
            if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data laporan tidak ditemukan.']);
        }
        return redirect()->back()->with('error', 'Data laporan tidak ditemukan.');
        }

        $file = $this->request->getFile('verified_spt');
        if (! $file || $file->getError() === UPLOAD_ERR_NO_FILE) {
            if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Silakan pilih file terlebih dahulu.']);
        }
        return redirect()->back()->with('error', 'Silakan pilih file terlebih dahulu.');
        }

        if (! $file->isValid()) {
            return redirect()->back()->with('error', 'File tidak valid: ' . $file->getErrorString());
        }

        $ext = strtolower($file->getClientExtension());
        if ($ext !== 'pdf') {
            if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Ekstensi file tidak diizinkan. File SPT yang sudah ditandatangani WAJIB dalam format PDF.']);
        }
        return redirect()->back()->with('error', 'Ekstensi file tidak diizinkan. File SPT yang sudah ditandatangani WAJIB dalam format PDF.');
        }

        if ($file->getSize() > 10 * 1024 * 1024) {
            if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Ukuran file maksimal adalah 10MB.']);
        }
        return redirect()->back()->with('error', 'Ukuran file maksimal adalah 10MB.');
        }

        $uploadDir = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'verified_perjadin';
        if (! is_dir($uploadDir)) {
            @mkdir($uploadDir, 0775, true);
        }

        $newName = $file->getRandomName();
        if ($file->move($uploadDir, $newName, true)) {
            $oldPath = trim((string) ($row['verified_spt_path'] ?? ''));
            if ($oldPath !== '') {
                $oldFullPath = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($oldPath, '/');
                if (is_file($oldFullPath)) {
                    @unlink($oldFullPath);
                }
            }

            $model->update($id, [
                'verified_spt_path' => 'uploads/verified_perjadin/' . $newName
            ]);

            if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'File SPT yang sudah ditandatangani (PDF) berhasil diupload.']);
        }
        return redirect()->back()->with('success', 'File SPT yang sudah ditandatangani (PDF) berhasil diupload.');
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memindahkan file ke direktori upload.']);
        }
        return redirect()->back()->with('error', 'Gagal memindahkan file ke direktori upload.');
    }

    public function perjalananDinasDokumen(int $id)
    {
        if (! $this->canViewLaporan()) {
            return redirect()->to(site_url('/admin'));
        }

        if (! db_connect()->tableExists('laporan_perjalanan_dinas')) {
            return redirect()->to(site_url('admin/surat/perjalanan-dinas'))->with('error', 'Tabel laporan perjalanan dinas belum tersedia.');
        }

        $row = (new LaporanPerjalananDinasModel())->find($id);
        if (! is_array($row)) {
            return redirect()->to(site_url('admin/surat/perjalanan-dinas'))->with('error', 'Data laporan tidak ditemukan.');
        }

        $data = [
            'nomor_surat_tugas' => (string) ($row['nomor_surat_tugas'] ?? ''),
            'periode_mulai' => (string) ($row['periode_mulai'] ?? ''),
            'periode_selesai' => (string) ($row['periode_selesai'] ?? ''),
            'kota_tujuan' => (string) ($row['kota_tujuan'] ?? ''),
            'tujuan' => (string) ($row['tujuan'] ?? ''),
            'sasaran' => (string) ($row['sasaran'] ?? ''),
            'laporan_hasil' => (string) ($row['laporan_hasil'] ?? ''),
            'pelaksana' => $this->sortPelaksanaByStrukturOrganisasi($this->decodeJsonArray((string) ($row['pelaksana_json'] ?? '[]'))),
            'foto_dokumentasi' => $this->decodeJsonArray((string) ($row['foto_dokumentasi_json'] ?? '[]')),
            'creator_name' => (string) ($row['creator_name'] ?? ''),
            'creator_pegawai' => $this->decodeJsonObject((string) ($row['creator_pegawai_json'] ?? '{}')),
            'diketahui_oleh' => $this->decodeJsonObject((string) ($row['diketahui_oleh_json'] ?? '{}')),
        ];

        $html = view('admin/laporan/perjalanan_dinas_pdf', [
            'data' => $data,
        ]);

        $dompdfOptions = new \Dompdf\Options();
        $dompdfOptions->set('isRemoteEnabled', true);
        $dompdfOptions->set('isHtml5ParserEnabled', true);

        $dompdf = new \Dompdf\Dompdf($dompdfOptions);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="laporan_perjalanan_dinas_' . $id . '.pdf"')
            ->setBody($dompdf->output());
    }

    public function perjalananDinasEdit(int $id)
    {
        if (! $this->canViewLaporan()) {
            return redirect()->to(site_url('/admin'));
        }

        if (! $this->canManageLaporan()) {
            return redirect()->to(site_url('admin/surat/perjalanan-dinas'))->with('error', 'Anda tidak memiliki akses untuk mengubah laporan perjalanan dinas.');
        }

        if (! db_connect()->tableExists('laporan_perjalanan_dinas')) {
            return redirect()->to(site_url('admin/surat/perjalanan-dinas'))->with('error', 'Tabel laporan perjalanan dinas belum tersedia.');
        }

        $model = new LaporanPerjalananDinasModel();
        $existing = $model->find($id);
        if (! is_array($existing)) {
            return redirect()->to(site_url('admin/surat/perjalanan-dinas'))->with('error', 'Data laporan tidak ditemukan.');
        }

        $pegawaiRows = $this->loadPegawaiOptions();
        $defaultApprover = $this->resolveDefaultApprover($pegawaiRows);
        $creatorPegawai = $this->resolveCurrentPegawai((string) (session()->get('fullName') ?: session()->get('username') ?: session()->get('name')), $pegawaiRows);

        $storedPelaksana = $this->decodeJsonArray((string) ($existing['pelaksana_json'] ?? '[]'));
        $storedDiketahui = $this->decodeJsonObject((string) ($existing['diketahui_oleh_json'] ?? '{}'));
        $existingPhotos = $this->decodeJsonArray((string) ($existing['foto_dokumentasi_json'] ?? '[]'));
        $existingDocs = $this->decodeJsonArray((string) ($existing['dokumen_pendukung_json'] ?? '[]'));

        $currentInput = [
            'nomor_surat_tugas' => trim((string) ($existing['nomor_surat_tugas'] ?? '')),
            'periode_mulai' => trim((string) ($existing['periode_mulai'] ?? '')),
            'periode_selesai' => trim((string) ($existing['periode_selesai'] ?? '')),
            'kota_tujuan' => trim((string) ($existing['kota_tujuan'] ?? '')),
            'tujuan' => trim((string) ($existing['tujuan'] ?? '')),
            'sasaran' => trim((string) ($existing['sasaran'] ?? '')),
            'laporan_hasil' => trim((string) ($existing['laporan_hasil'] ?? '')),
            'diketahui_oleh_id' => $this->resolvePegawaiIdByProfile($pegawaiRows, $storedDiketahui) ?? (int) ($defaultApprover['id'] ?? 0),
            'pelaksana_id' => $this->resolvePegawaiIdsByProfiles($pegawaiRows, $storedPelaksana),
        ];

        if (strtolower((string) $this->request->getMethod()) !== 'post') {
            $this->writeSmokeLog('perjalananDinasEdit.GET', [
                'id' => $id,
                'existing' => $existing,
            ]);

            return view('admin/laporan/perjalanan_dinas_buat', [
                'title' => 'Ubah Laporan Perjalanan Dinas',
                'pegawai_options' => $pegawaiRows,
                'kabupaten_options' => $this->loadKabupatenOptions(),
                'default_approver_id' => $defaultApprover['id'] ?? null,
                'default_approver_label' => $defaultApprover['label'] ?? '',
                'creator_name' => trim((string) ($existing['creator_name'] ?? session()->get('fullName') ?: session()->get('username') ?: session()->get('name') ?: 'system')),
                'creator_pegawai' => $creatorPegawai,
                'current_input' => $currentInput,
                'form_error' => null,
                'form_action' => site_url('admin/surat/perjalanan-dinas/' . $id . '/ubah') . ($this->request->getGet('modal') == 1 ? '?modal=1' : ''),
                'is_edit' => true,
                'is_modal' => $this->request->getGet('modal') == 1,
                'submit_label_primary' => 'Simpan',
                'existing_foto_dokumentasi' => $existingPhotos,
                'existing_dokumen_pendukung' => $existingDocs,
            ]);
        }

        if ($this->isPostBodyTooLarge()) {
            return redirect()->to(site_url('admin/surat/perjalanan-dinas/' . $id . '/ubah'))
                ->with('error', 'Upload gagal: ukuran data melebihi batas server (post_max_size). Kurangi jumlah atau ukuran foto dan coba lagi.');
        }

        $currentInput = [
            'foto_dokumentasi' => [],
            'nomor_surat_tugas' => trim((string) $this->request->getPost('nomor_surat_tugas')),
            'periode_mulai' => trim((string) $this->request->getPost('periode_mulai')),
            'periode_selesai' => trim((string) $this->request->getPost('periode_selesai')),
            'kota_tujuan' => trim((string) $this->request->getPost('kota_tujuan')),
            'tujuan' => trim((string) $this->request->getPost('tujuan')),
            'sasaran' => trim((string) $this->request->getPost('sasaran')),
            'laporan_hasil' => trim((string) $this->request->getPost('laporan_hasil')),
            'diketahui_oleh_id' => (int) $this->request->getPost('diketahui_oleh_id'),
            'pelaksana_id' => array_values(array_filter(array_map('intval', (array) $this->request->getPost('pelaksana_id')), static fn (int $itemId): bool => $itemId > 0)),
        ];

        $errors = [];
        foreach (['periode_mulai', 'periode_selesai', 'kota_tujuan', 'tujuan', 'sasaran', 'laporan_hasil'] as $requiredField) {
            if ($currentInput[$requiredField] === '') {
                $errors[] = ucfirst(str_replace('_', ' ', $requiredField)) . ' wajib diisi.';
            }
        }

        if ($currentInput['periode_mulai'] !== '' && $currentInput['periode_selesai'] !== '' && strtotime($currentInput['periode_mulai']) !== false && strtotime($currentInput['periode_selesai']) !== false && strtotime($currentInput['periode_mulai']) > strtotime($currentInput['periode_selesai'])) {
            $errors[] = 'Periode mulai tidak boleh lebih besar dari periode selesai.';
        }

        $allowedPegawaiIds = array_map(static fn (array $row): int => (int) ($row['id'] ?? 0), $pegawaiRows);
        $currentInput['pelaksana_id'] = array_values(array_unique(array_values(array_filter($currentInput['pelaksana_id'], static fn (int $itemId): bool => in_array($itemId, $allowedPegawaiIds, true)))));

        if ($currentInput['pelaksana_id'] === []) {
            $errors[] = 'Minimal 1 pelaksana wajib dipilih.';
        }

        if (! in_array($currentInput['diketahui_oleh_id'], $allowedPegawaiIds, true)) {
            $currentInput['diketahui_oleh_id'] = (int) ($defaultApprover['id'] ?? 0);
        }

        if ($currentInput['diketahui_oleh_id'] <= 0) {
            $errors[] = 'Data diketahui oleh wajib dipilih.';
        }

        $existingPhotos = $this->decodeJsonArray((string) ($existing['foto_dokumentasi_json'] ?? '[]'));
        $existingDocs = $this->decodeJsonArray((string) ($existing['dokumen_pendukung_json'] ?? '[]'));

        // Map existing photo descriptions (using original indices)
        $existingKeterangan = $this->request->getPost('existing_foto_keterangan');
        if (is_array($existingKeterangan)) {
            foreach ($existingPhotos as $idx => &$photo) {
                if (is_array($photo)) {
                    $photo['keterangan'] = isset($existingKeterangan[$idx]) ? trim((string) $existingKeterangan[$idx]) : ($photo['keterangan'] ?? '');
                }
            }
            unset($photo);
        }

        // Map existing document descriptions (using original indices)
        $existingDocKeterangan = $this->request->getPost('existing_dokumen_keterangan');
        $existingDocTransportasi = $this->request->getPost('existing_dokumen_transportasi');
        if (is_array($existingDocKeterangan)) {
            foreach ($existingDocs as $idx => &$doc) {
                if (is_array($doc)) {
                    $doc['keterangan'] = isset($existingDocKeterangan[$idx]) ? trim((string) $existingDocKeterangan[$idx]) : ($doc['keterangan'] ?? '');
                    if (is_array($existingDocTransportasi) && isset($existingDocTransportasi[$idx])) {
                        $doc['transportasi'] = trim((string) $existingDocTransportasi[$idx]);
                    }
                }
            }
            unset($doc);
        }

        // Filter foto existing yang dihapus oleh user, hapus juga file fisiknya
        $removedIndices = [];
        $removedRaw = trim((string) $this->request->getPost('removed_foto_indices'));
        if ($removedRaw !== '') {
            $decoded = json_decode($removedRaw, true);
            if (is_array($decoded)) {
                $removedIndices = array_values($decoded);
            }
        }
        if ($removedIndices !== []) {
            foreach ($removedIndices as $removedIdx) {
                $removedPhoto = $existingPhotos[$removedIdx] ?? null;
                if ($removedPhoto !== null) {
                    $this->deletePerjalananDinasPhotoFile($removedPhoto);
                }
            }
            $existingPhotos = array_values(array_filter($existingPhotos, static fn ($key): bool => ! in_array($key, $removedIndices, true), ARRAY_FILTER_USE_KEY));
        }

        // Filter dokumen pendukung existing yang dihapus oleh user, hapus file fisiknya
        $removedFileIndices = [];
        $removedFileRaw = trim((string) $this->request->getPost('removed_file_indices'));
        if ($removedFileRaw !== '') {
            $decoded = json_decode($removedFileRaw, true);
            if (is_array($decoded)) {
                $removedFileIndices = array_values($decoded);
            }
        }
        if ($removedFileIndices !== []) {
            foreach ($removedFileIndices as $removedFileIdx) {
                $removedFile = $existingDocs[$removedFileIdx] ?? null;
                if ($removedFile !== null) {
                    $this->deletePerjalananDinasFile($removedFile);
                }
            }
            $existingDocs = array_values(array_filter($existingDocs, static fn ($key): bool => ! in_array($key, $removedFileIndices, true), ARRAY_FILTER_USE_KEY));
        }

        // Upload foto baru ke file fisik
        $uploadResult = $this->uploadPerjalananDinasPhotos('foto_dokumentasi');
        if ($uploadResult['error'] !== null) {
            $errors[] = $uploadResult['error'];
        }
        $newPhotos = $uploadResult['photos'];
        $photos = array_values(array_merge($existingPhotos, $newPhotos));

        // Upload file pendukung baru ke file fisik
        $uploadFilesResult = $this->uploadPerjalananDinasFiles('dokumen_pendukung');
        if ($uploadFilesResult['error'] !== null) {
            $errors[] = $uploadFilesResult['error'];
        }
        $newDocs = $uploadFilesResult['files'];
        $docs = array_values(array_merge($existingDocs, $newDocs));

        if ($errors !== []) {
            return view('admin/laporan/perjalanan_dinas_buat', [
                'title' => 'Ubah Laporan Perjalanan Dinas',
                'pegawai_options' => $pegawaiRows,
                'kabupaten_options' => $this->loadKabupatenOptions(),
                'default_approver_id' => $defaultApprover['id'] ?? null,
                'default_approver_label' => $defaultApprover['label'] ?? '',
                'creator_name' => trim((string) ($existing['creator_name'] ?? session()->get('fullName') ?: session()->get('username') ?: session()->get('name') ?: 'system')),
                'creator_pegawai' => $creatorPegawai,
                'current_input' => $currentInput,
                'form_error' => implode(' ', $errors),
                'form_action' => site_url('admin/surat/perjalanan-dinas/' . $id . '/ubah') . ($this->request->getGet('modal') == 1 ? '?modal=1' : ''),
                'is_edit' => true,
                'is_modal' => $this->request->getGet('modal') == 1,
                'submit_label_primary' => 'Simpan',
                'existing_foto_dokumentasi' => $photos,
                'existing_dokumen_pendukung' => $docs,
            ]);
        }

        $payload = [
            'nomor_surat_tugas' => $currentInput['nomor_surat_tugas'],
            'periode_mulai' => $currentInput['periode_mulai'] !== '' ? $currentInput['periode_mulai'] : null,
            'periode_selesai' => $currentInput['periode_selesai'] !== '' ? $currentInput['periode_selesai'] : null,
            'kota_tujuan' => $currentInput['kota_tujuan'],
            'tujuan' => $currentInput['tujuan'],
            'sasaran' => $currentInput['sasaran'],
            'laporan_hasil' => $currentInput['laporan_hasil'],
            'pelaksana_json' => json_encode($this->buildPegawaiRowsByIds($pegawaiRows, $currentInput['pelaksana_id']), JSON_UNESCAPED_UNICODE),
            'foto_dokumentasi_json' => json_encode($photos, JSON_UNESCAPED_UNICODE),
            'dokumen_pendukung_json' => json_encode($docs, JSON_UNESCAPED_UNICODE),
            'creator_name' => trim((string) ($existing['creator_name'] ?? session()->get('fullName') ?: session()->get('username') ?: session()->get('name') ?: 'system')),
            'creator_pegawai_json' => json_encode($creatorPegawai, JSON_UNESCAPED_UNICODE),
            'diketahui_oleh_json' => json_encode($this->findPegawaiById($pegawaiRows, $currentInput['diketahui_oleh_id']) ?? [], JSON_UNESCAPED_UNICODE),
            'is_final' => strtolower(trim((string) $this->request->getPost('save_mode'))) === 'draft' ? 0 : 1,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $ok = $model->update($id, $payload);
        $isModal = ($this->request->getGet('modal') == 1 || $this->request->getPost('is_modal') == 1);
        if (! $ok) {
            $errUrl = site_url('admin/surat/perjalanan-dinas/' . $id . '/ubah') . ($isModal ? '?modal=1' : '');
            return redirect()->to($errUrl)->with('error', 'Gagal memperbarui laporan.');
        }

        if ($isModal) {
            return redirect()->to(site_url('admin/surat/perjalanan-dinas/' . $id . '/ubah?modal=1&saved=1'))->with('success', 'Laporan berhasil diperbarui.');
        }

        return redirect()->to(site_url('admin/surat/perjalanan-dinas'))->with('success', 'Laporan berhasil diperbarui.');
    }

    public function perjalananDinasBuat()
    {
        if (! $this->canViewLaporan()) {
            return redirect()->to(site_url('/admin'));
        }

        $pegawaiRows = $this->loadPegawaiOptions();
        $defaultApprover = $this->resolveDefaultApprover($pegawaiRows);
        $creatorPegawai = $this->resolveCurrentPegawai((string) (session()->get('fullName') ?: session()->get('username') ?: session()->get('name')), $pegawaiRows);
        $draftData = session()->get('laporan_perjalanan_dinas_draft');
        if (! is_array($draftData)) {
            $draftData = [];
        }

        if (strtolower((string) $this->request->getMethod()) !== 'post') {
            $this->writeSmokeLog('perjalananDinasBuat.GET', [
                'draft' => $draftData,
            ]);
            return view('admin/laporan/perjalanan_dinas_buat', [
                'title' => 'Buat Laporan Perjalanan Dinas',
                'pegawai_options' => $pegawaiRows,
                'kabupaten_options' => $this->loadKabupatenOptions(),
                'default_approver_id' => $defaultApprover['id'] ?? null,
                'default_approver_label' => $defaultApprover['label'] ?? '',
                'creator_name' => trim((string) ($draftData['creator_name'] ?? session()->get('fullName') ?: session()->get('username') ?: session()->get('name') ?: 'system')),
                'creator_pegawai' => $draftData['creator_pegawai'] ?? $creatorPegawai,
                'is_modal' => $this->request->getGet('modal') == 1,
                'current_input' => [
                    'nomor_surat_tugas' => (string) ($draftData['nomor_surat_tugas'] ?? ''),
                    'periode_mulai' => (string) ($draftData['periode_mulai'] ?? ''),
                    'periode_selesai' => (string) ($draftData['periode_selesai'] ?? ''),
                    'kota_tujuan' => (string) ($draftData['kota_tujuan'] ?? ''),
                    'tujuan' => (string) ($draftData['tujuan'] ?? ''),
                    'sasaran' => (string) ($draftData['sasaran'] ?? ''),
                    'laporan_hasil' => (string) ($draftData['laporan_hasil'] ?? ''),
                    'diketahui_oleh_id' => (int) ($draftData['diketahui_oleh']['id'] ?? 0),
                    'pelaksana_id' => array_values(array_filter(array_map(static fn ($item): int => (int) ($item['id'] ?? 0), (array) ($draftData['pelaksana'] ?? [])), static fn (int $itemId): bool => $itemId > 0)),
                ],
                'form_error' => null,
                'existing_foto_dokumentasi' => $draftData['foto_dokumentasi'] ?? [],
                'existing_dokumen_pendukung' => $draftData['dokumen_pendukung'] ?? [],
            ]);
        }

        if ($this->isPostBodyTooLarge()) {
            return redirect()->to(site_url('admin/surat/perjalanan-dinas/buat'))
                ->with('error', 'Upload gagal: ukuran data melebihi batas server (post_max_size). Kurangi jumlah atau ukuran foto dan coba lagi.');
        }

        $currentInput = [
            'nomor_surat_tugas' => trim((string) $this->request->getPost('nomor_surat_tugas')),
            'periode_mulai' => trim((string) $this->request->getPost('periode_mulai')),
            'periode_selesai' => trim((string) $this->request->getPost('periode_selesai')),
            'kota_tujuan' => trim((string) $this->request->getPost('kota_tujuan')),
            'tujuan' => trim((string) $this->request->getPost('tujuan')),
            'sasaran' => trim((string) $this->request->getPost('sasaran')),
            'laporan_hasil' => trim((string) $this->request->getPost('laporan_hasil')),
            'diketahui_oleh_id' => (int) $this->request->getPost('diketahui_oleh_id'),
            'pelaksana_id' => array_values(array_filter(array_map('intval', (array) $this->request->getPost('pelaksana_id')), static fn (int $id): bool => $id > 0)),
        ];

        $errors = [];
        foreach (['periode_mulai', 'periode_selesai', 'kota_tujuan', 'tujuan', 'sasaran', 'laporan_hasil'] as $requiredField) {
            if ($currentInput[$requiredField] === '') {
                $errors[] = ucfirst(str_replace('_', ' ', $requiredField)) . ' wajib diisi.';
            }
        }

        if ($currentInput['periode_mulai'] !== '' && $currentInput['periode_selesai'] !== '' && strtotime($currentInput['periode_mulai']) !== false && strtotime($currentInput['periode_selesai']) !== false && strtotime($currentInput['periode_mulai']) > strtotime($currentInput['periode_selesai'])) {
            $errors[] = 'Periode mulai tidak boleh lebih besar dari periode selesai.';
        }

        if ($currentInput['pelaksana_id'] === []) {
            $errors[] = 'Minimal 1 pelaksana wajib dipilih.';
        }

        $allowedPegawaiIds = array_map(static fn (array $row): int => (int) ($row['id'] ?? 0), $pegawaiRows);
        $currentInput['pelaksana_id'] = array_values(array_unique(array_values(array_filter($currentInput['pelaksana_id'], static fn (int $id): bool => in_array($id, $allowedPegawaiIds, true)))));

        if ($currentInput['pelaksana_id'] === []) {
            $errors[] = 'Pelaksana yang dipilih tidak valid.';
        }

        if (! in_array($currentInput['diketahui_oleh_id'], $allowedPegawaiIds, true)) {
            $currentInput['diketahui_oleh_id'] = (int) ($defaultApprover['id'] ?? 0);
        }

        if ($currentInput['diketahui_oleh_id'] <= 0) {
            $errors[] = 'Data diketahui oleh wajib dipilih.';
        }

        // Draft foto (dari sesi sebelumnya) — filter yang dihapus user
        $draftPhotos = array_values(array_filter((array) ($draftData['foto_dokumentasi'] ?? []), 'is_array'));

        // Map draft/existing photo descriptions (using original indices)
        $existingKeterangan = $this->request->getPost('existing_foto_keterangan');
        if (is_array($existingKeterangan)) {
            foreach ($draftPhotos as $idx => &$photo) {
                if (is_array($photo)) {
                    $photo['keterangan'] = isset($existingKeterangan[$idx]) ? trim((string) $existingKeterangan[$idx]) : ($photo['keterangan'] ?? '');
                }
            }
            unset($photo);
        }

        $removedIndices = [];
        $removedRaw = trim((string) $this->request->getPost('removed_foto_indices'));
        if ($removedRaw !== '') {
            $decoded = json_decode($removedRaw, true);
            if (is_array($decoded)) {
                $removedIndices = array_values($decoded);
            }
        }
        if ($removedIndices !== []) {
            foreach ($removedIndices as $removedIdx) {
                $removedPhoto = $draftPhotos[$removedIdx] ?? null;
                if ($removedPhoto !== null) {
                    $this->deletePerjalananDinasPhotoFile($removedPhoto);
                }
            }
            $draftPhotos = array_values(array_filter($draftPhotos, static fn ($key): bool => ! in_array($key, $removedIndices, true), ARRAY_FILTER_USE_KEY));
        }

        // Draft dokumen pendukung (dari sesi sebelumnya) — filter yang dihapus user
        $draftFiles = array_values(array_filter((array) ($draftData['dokumen_pendukung'] ?? []), 'is_array'));

        // Map existing document descriptions (using original indices)
        $existingDocKeterangan = $this->request->getPost('existing_dokumen_keterangan');
        $existingDocTransportasi = $this->request->getPost('existing_dokumen_transportasi');
        if (is_array($existingDocKeterangan)) {
            foreach ($draftFiles as $idx => &$doc) {
                if (is_array($doc)) {
                    $doc['keterangan'] = isset($existingDocKeterangan[$idx]) ? trim((string) $existingDocKeterangan[$idx]) : ($doc['keterangan'] ?? '');
                    if (is_array($existingDocTransportasi) && isset($existingDocTransportasi[$idx])) {
                        $doc['transportasi'] = trim((string) $existingDocTransportasi[$idx]);
                    }
                }
            }
            unset($doc);
        }

        $removedFileIndices = [];
        $removedFileRaw = trim((string) $this->request->getPost('removed_file_indices'));
        if ($removedFileRaw !== '') {
            $decoded = json_decode($removedFileRaw, true);
            if (is_array($decoded)) {
                $removedFileIndices = array_values($decoded);
            }
        }
        if ($removedFileIndices !== []) {
            foreach ($removedFileIndices as $removedFileIdx) {
                $removedFile = $draftFiles[$removedFileIdx] ?? null;
                if ($removedFile !== null) {
                    $this->deletePerjalananDinasFile($removedFile);
                }
            }
            $draftFiles = array_values(array_filter($draftFiles, static fn ($key): bool => ! in_array($key, $removedFileIndices, true), ARRAY_FILTER_USE_KEY));
        }

        // Upload foto baru ke file fisik
        $uploadResult = $this->uploadPerjalananDinasPhotos('foto_dokumentasi');
        if ($uploadResult['error'] !== null) {
            $errors[] = $uploadResult['error'];
        }
        $photos = $uploadResult['photos'];

        // Upload file pendukung baru ke file fisik
        $uploadFilesResult = $this->uploadPerjalananDinasFiles('dokumen_pendukung');
        if ($uploadFilesResult['error'] !== null) {
            $errors[] = $uploadFilesResult['error'];
        }
        $newDocs = $uploadFilesResult['files'];

        if ($errors !== []) {
            return view('admin/laporan/perjalanan_dinas_buat', [
                'title' => 'Buat Laporan Perjalanan Dinas',
                'pegawai_options' => $pegawaiRows,
                'kabupaten_options' => $this->loadKabupatenOptions(),
                'default_approver_id' => $defaultApprover['id'] ?? null,
                'default_approver_label' => $defaultApprover['label'] ?? '',
                'creator_name' => trim((string) (session()->get('fullName') ?: session()->get('username') ?: session()->get('name') ?: 'system')),
                'creator_pegawai' => $creatorPegawai,
                'current_input' => $currentInput,
                'form_error' => implode(' ', $errors),
                'existing_foto_dokumentasi' => array_values(array_merge($draftPhotos, $photos)),
                'existing_dokumen_pendukung' => array_values(array_merge($draftFiles, $newDocs)),
            ]);
        }

        $data = [
            'nomor_surat_tugas' => $currentInput['nomor_surat_tugas'],
            'periode_mulai' => $currentInput['periode_mulai'],
            'periode_selesai' => $currentInput['periode_selesai'],
            'kota_tujuan' => $currentInput['kota_tujuan'],
            'tujuan' => $currentInput['tujuan'],
            'sasaran' => $currentInput['sasaran'],
            'laporan_hasil' => $currentInput['laporan_hasil'],
            'pelaksana' => $this->buildPegawaiRowsByIds($pegawaiRows, $currentInput['pelaksana_id']),
            'foto_dokumentasi' => array_values(array_merge($draftPhotos, $photos)),
            'dokumen_pendukung' => array_values(array_merge($draftFiles, $newDocs)),
            'creator_name' => trim((string) (session()->get('fullName') ?: session()->get('username') ?: session()->get('name') ?: 'system')),
            'creator_pegawai' => $creatorPegawai,
            'diketahui_oleh' => $this->findPegawaiById($pegawaiRows, $currentInput['diketahui_oleh_id']) ?? [
                'nama' => (string) ($defaultApprover['label'] ?? '-'),
                'nip' => '',
                'jabatan' => '',
            ],
        ];

        $saveMode = strtolower(trim((string) $this->request->getPost('save_mode')));
        if ($saveMode === 'draft') {
            // Simpan draft di sesi — foto & file sudah di-upload ke file fisik
            session()->set('laporan_perjalanan_dinas_draft', $data);
            return redirect()->to(site_url('admin/surat/perjalanan-dinas/buat'))->with('success', 'Draft laporan berhasil disimpan.');
        }

        if ($saveMode === 'final') {
            // Persist final submission to DB
            if (! db_connect()->tableExists('laporan_perjalanan_dinas')) {
                return redirect()->to(site_url('admin/surat/perjalanan-dinas/buat'))->with('error', 'Tabel penyimpanan laporan belum tersedia.');
            }

            $model = new LaporanPerjalananDinasModel();

            $row = [
                'nomor_surat_tugas' => $data['nomor_surat_tugas'],
                'periode_mulai' => $data['periode_mulai'] !== '' ? $data['periode_mulai'] : null,
                'periode_selesai' => $data['periode_selesai'] !== '' ? $data['periode_selesai'] : null,
                'kota_tujuan' => $data['kota_tujuan'],
                'tujuan' => $data['tujuan'],
                'sasaran' => $data['sasaran'],
                'laporan_hasil' => $data['laporan_hasil'],
                'pelaksana_json' => json_encode($data['pelaksana'], JSON_UNESCAPED_UNICODE),
                'foto_dokumentasi_json' => json_encode($data['foto_dokumentasi'], JSON_UNESCAPED_UNICODE),
                'dokumen_pendukung_json' => json_encode($data['dokumen_pendukung'], JSON_UNESCAPED_UNICODE),
                'creator_name' => $data['creator_name'],
                'creator_pegawai_json' => json_encode($data['creator_pegawai'], JSON_UNESCAPED_UNICODE),
                'diketahui_oleh_json' => json_encode($data['diketahui_oleh'], JSON_UNESCAPED_UNICODE),
                'is_final' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            $insertId = $model->insert($row);
            if ($insertId === false) {
                // Jika DB gagal, hapus file yang baru di-upload agar tidak orphan
                foreach ($photos as $p) {
                    $this->deletePerjalananDinasPhotoFile($p);
                }
                foreach ($newDocs as $d) {
                    $this->deletePerjalananDinasFile($d);
                }
                return redirect()->to(site_url('admin/surat/perjalanan-dinas/buat'))->with('error', 'Gagal menyimpan laporan. Silakan coba lagi.');
            }

            session()->remove('laporan_perjalanan_dinas_draft');

            $isModal = ($this->request->getGet('modal') == 1 || $this->request->getPost('is_modal') == 1);
            if ($isModal) {
                return redirect()->to(site_url('admin/surat/perjalanan-dinas/buat?modal=1&saved=1'))->with('success', 'Laporan berhasil disimpan.');
            }

            return redirect()->to(site_url('admin/surat/perjalanan-dinas'))->with('success', 'Laporan berhasil disimpan.');
        }

        // Fallback: if save mode not recognized, redirect back with notice.
        return redirect()->to(site_url('admin/surat/perjalanan-dinas/buat'))->with('error', 'Aksi simpan tidak valid.');
    }

    private function sortPelaksanaByStrukturOrganisasi(array $pelaksanaList): array
    {
        if (empty($pelaksanaList)) {
            return [];
        }

        $orderMap = (new \App\Models\StrukturOrganisasiModel())->getPegawaiOrderMap();
        $idMap = $orderMap['id_map'] ?? [];
        $nipMap = $orderMap['nip_map'] ?? [];
        $namaMap = $orderMap['nama_map'] ?? [];

        usort($pelaksanaList, static function ($a, $b) use ($idMap, $nipMap, $namaMap) {
            $idA = (int) ($a['id'] ?? 0);
            $idB = (int) ($b['id'] ?? 0);

            $nipA = trim((string) ($a['nip'] ?? ''));
            $nipB = trim((string) ($b['nip'] ?? ''));

            $namaA = strtolower(trim((string) ($a['nama'] ?? '')));
            $namaB = strtolower(trim((string) ($b['nama'] ?? '')));

            $rankA = $idMap[$idA] ?? ($nipA !== '' ? ($nipMap[$nipA] ?? null) : null) ?? ($namaA !== '' ? ($namaMap[$namaA] ?? null) : null) ?? 999999;
            $rankB = $idMap[$idB] ?? ($nipB !== '' ? ($nipMap[$nipB] ?? null) : null) ?? ($namaB !== '' ? ($namaMap[$namaB] ?? null) : null) ?? 999999;

            if ($rankA !== $rankB) {
                return $rankA <=> $rankB;
            }

            return strcmp($namaA, $namaB);
        });

        return array_values($pelaksanaList);
    }

    private function loadPegawaiOptions(): array
    {
        if (! db_connect()->tableExists('mst_pegawai')) {
            return [];
        }

        $rows = (new MstPegawaiModel())
            ->select('mst_pegawai.id, mst_pegawai.nip, mst_pegawai.nama, mst_pegawai.golongan, mst_pegawai.jabatan_utama_id, ju.jabatan AS jabatan_label, mst_pegawai.is_active')
            ->join('mst_jabatan ju', 'ju.id = mst_pegawai.jabatan_utama_id', 'left')
            ->where('mst_pegawai.is_active', 1)
            ->findAll();

        $rows = $this->sortPelaksanaByStrukturOrganisasi($rows);

        return array_map(static function (array $row): array {
            $display = trim((string) ($row['nama'] ?? 'Pegawai'));
            $nip = trim((string) ($row['nip'] ?? ''));
            $jabatan = trim((string) ($row['jabatan_label'] ?? ''));

            if ($nip !== '') {
                $display .= ' | NIP ' . $nip;
            }
            if ($jabatan !== '') {
                $display .= ' | ' . $jabatan;
            }

            $row['display_label'] = $display;
            return $row;
        }, $rows);
    }

    private function decodeJsonArray(string $json): array
    {
        $decoded = json_decode($json, true);
        return is_array($decoded) ? array_values($decoded) : [];
    }

    private function decodeJsonObject(string $json): array
    {
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function resolvePegawaiIdByProfile(array $pegawaiRows, array $profile): ?int
    {
        $profileNip = trim((string) ($profile['nip'] ?? ''));
        $profileNama = strtolower(trim((string) ($profile['nama'] ?? '')));

        foreach ($pegawaiRows as $row) {
            $rowNip = trim((string) ($row['nip'] ?? ''));
            $rowNama = strtolower(trim((string) ($row['nama'] ?? '')));

            if ($profileNip !== '' && $rowNip !== '' && $profileNip === $rowNip) {
                return (int) ($row['id'] ?? 0);
            }

            if ($profileNama !== '' && $rowNama !== '' && $profileNama === $rowNama) {
                return (int) ($row['id'] ?? 0);
            }
        }

        return null;
    }

    private function resolvePegawaiIdsByProfiles(array $pegawaiRows, array $profiles): array
    {
        $ids = [];
        foreach ($profiles as $profile) {
            if (! is_array($profile)) {
                continue;
            }

            $id = $this->resolvePegawaiIdByProfile($pegawaiRows, $profile);
            if ($id !== null && $id > 0) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    private function loadKabupatenOptions(): array
    {
        $db = db_connect();

        if ($db->tableExists('mst_kabupaten')) {
            $rows = $db->table('mst_kabupaten')
                ->select('nama_kabupaten')
                ->where('nama_kabupaten IS NOT NULL', null, false)
                ->where('nama_kabupaten !=', '')
                ->groupBy('nama_kabupaten')
                ->orderBy('nama_kabupaten', 'ASC')
                ->get()
                ->getResultArray();

            $options = array_values(array_filter(array_map(static function (array $row): string {
                return trim((string) ($row['nama_kabupaten'] ?? ''));
            }, $rows), static fn (string $value): bool => $value !== ''));

            if ($options !== []) {
                return $options;
            }
        }

        if (! $db->tableExists('mst_sekolah')) {
            return [];
        }

        $rows = $db->table('mst_sekolah')
            ->select('kabupaten')
            ->where('kabupaten IS NOT NULL', null, false)
            ->where('kabupaten !=', '')
            ->groupBy('kabupaten')
            ->orderBy('kabupaten', 'ASC')
            ->get()
            ->getResultArray();

        return array_values(array_filter(array_map(static function (array $row): string {
            return trim((string) ($row['kabupaten'] ?? ''));
        }, $rows), static fn (string $value): bool => $value !== ''));
    }

    private function resolveCurrentPegawai(string $sessionValue, array $pegawaiRows): ?array
    {
        $needle = strtolower(trim($sessionValue));
        if ($needle === '') {
            return null;
        }

        foreach ($pegawaiRows as $row) {
            $nama = strtolower(trim((string) ($row['nama'] ?? '')));
            $nip = strtolower(trim((string) ($row['nip'] ?? '')));
            if ($nama === $needle || $nip === $needle) {
                return [
                    'id' => (int) ($row['id'] ?? 0),
                    'nama' => (string) ($row['nama'] ?? ''),
                    'nip' => (string) ($row['nip'] ?? ''),
                    'jabatan' => (string) ($row['jabatan_label'] ?? ''),
                ];
            }
        }

        return null;
    }

    private function resolveDefaultApprover(array $pegawaiRows): array
    {
        $targetNip = '198002142014121002';

        foreach ($pegawaiRows as $row) {
            if (trim((string) ($row['nip'] ?? '')) === $targetNip) {
                return [
                    'id' => (int) ($row['id'] ?? 0),
                    'label' => (string) ($row['display_label'] ?? $row['nama'] ?? $targetNip),
                ];
            }
        }

        $fallback = $pegawaiRows[0] ?? null;
        if (is_array($fallback)) {
            return [
                'id' => (int) ($fallback['id'] ?? 0),
                'label' => (string) ($fallback['display_label'] ?? $fallback['nama'] ?? ''),
            ];
        }

        return ['id' => null, 'label' => ''];
    }

    private function buildPegawaiRowsByIds(array $pegawaiRows, array $ids): array
    {
        $rowsById = [];
        foreach ($pegawaiRows as $row) {
            $rowsById[(int) ($row['id'] ?? 0)] = $row;
        }

        $rows = [];
        foreach ($ids as $id) {
            if (! isset($rowsById[(int) $id])) {
                continue;
            }

            $row = $rowsById[(int) $id];
            $rows[] = [
                'id'            => (int) ($row['id'] ?? 0),
                'nama'          => (string) ($row['nama'] ?? ''),
                'nip'           => (string) ($row['nip'] ?? ''),
                'jabatan'       => (string) ($row['jabatan_label'] ?? ''),
                'golongan'      => (string) ($row['golongan'] ?? ''),
                'jenis_pegawai' => strtolower(trim((string) ($row['jenis_pegawai'] ?? 'pns'))),
            ];
        }

        return $this->sortPelaksanaByStrukturOrganisasi($rows);
    }

    private function findPegawaiById(array $pegawaiRows, int $id): ?array
    {
        foreach ($pegawaiRows as $row) {
            if ((int) ($row['id'] ?? 0) === $id) {
                return [
                    'id'            => (int) ($row['id'] ?? 0),
                    'nama'          => (string) ($row['nama'] ?? ''),
                    'nip'           => (string) ($row['nip'] ?? ''),
                    'jabatan'       => (string) ($row['jabatan_label'] ?? ''),
                    'jenis_pegawai' => strtolower(trim((string) ($row['jenis_pegawai'] ?? 'pns'))),
                ];
            }
        }

        return null;
    }

    private function getMingguanHistoryMap(array $reports): array
    {
        if ($reports === []) {
            return [];
        }

        $reportIds = array_values(array_filter(array_map(static fn (array $row): int => (int) ($row['id'] ?? 0), $reports), static fn (int $id): bool => $id > 0));
        if ($reportIds === []) {
            return [];
        }

        $table = db_connect()->table('laporan_mingguan_histories');
        if (! db_connect()->tableExists('laporan_mingguan_histories')) {
            return [];
        }

        $rows = $table
            ->whereIn('laporan_mingguan_id', $reportIds)
            ->orderBy('changed_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $reportId = (int) ($row['laporan_mingguan_id'] ?? 0);
            if ($reportId <= 0) {
                continue;
            }

            if (! isset($map[$reportId])) {
                $map[$reportId] = [];
            }

            $map[$reportId][] = $row;
        }

        return $map;
    }

    private function storeMingguanHistory(array $existing): void
    {
        if (! db_connect()->tableExists('laporan_mingguan_histories')) {
            return;
        }

        $reportId = (int) ($existing['id'] ?? 0);
        if ($reportId <= 0) {
            return;
        }

        $changedBy = trim((string) session()->get('username'));
        if ($changedBy === '') {
            $changedBy = strtolower((string) session()->get('role')) ?: 'unknown';
        }

        db_connect()->table('laporan_mingguan_histories')->insert([
            'laporan_mingguan_id' => $reportId,
            'sekolah_id' => (int) ($existing['sekolah_id'] ?? 0),
            'period_start' => (string) ($existing['period_start'] ?? ''),
            'period_end' => (string) ($existing['period_end'] ?? ''),
            'description' => (string) ($existing['description'] ?? ''),
            'file_path' => (string) ($existing['file_path'] ?? ''),
            'file_name' => (string) ($existing['file_name'] ?? ''),
            'changed_by' => $changedBy,
            'changed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function deleteMingguan(int $id): RedirectResponse
    {
        return redirect()->to(site_url('admin/laporan/mingguan'))->with('error', 'Fitur hapus laporan mingguan dinonaktifkan. Silakan gunakan Edit, riwayat perubahan tetap tersimpan.');
    }

    private function buildHarianPayload(): array|RedirectResponse
    {
        $titleId = (int) $this->request->getPost('sekolah_id');
        $reportDate = $this->normalizeDateValue((string) $this->request->getPost('report_date'));
        $latitude = $this->normalizeCoordinateValue((string) $this->request->getPost('latitude'), -90, 90);
        $longitude = $this->normalizeCoordinateValue((string) $this->request->getPost('longitude'), -180, 180);

        if ($this->isLocalhostRequest()) {
            $latitude ??= $this->randomCoordinate(-90, 90);
            $longitude ??= $this->randomCoordinate(-180, 180);
        }

        if ($titleId <= 0 || $reportDate === null) {
            return redirect()->to($this->dailyRedirectUrl($titleId))->withInput()->with('error', 'Sekolah dan tanggal laporan wajib diisi.');
        }

        $title = (new LaporanHarianTitleModel())->find($titleId);
        if (! is_array($title)) {
            return redirect()->to($this->dailyRedirectUrl($titleId))->withInput()->with('error', 'Sekolah tidak valid.');
        }

        $sections = $this->buildSectionsFromRequest();
        if ($sections === []) {
            return redirect()->to($this->dailyRedirectUrl($titleId))->withInput()->with('error', 'Minimal satu blok pekerjaan harus diisi.');
        }

        if ($latitude === null || $longitude === null) {
            return redirect()->to($this->dailyRedirectUrl($titleId))->withInput()->with('error', 'Koordinat lokasi wajib diambil terlebih dahulu.');
        }

        return [
            'sekolah_id' => $titleId,
            'report_date' => $reportDate,
            'sections_json' => json_encode($sections, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'personil_pekerja' => trim((string) $this->request->getPost('personil_pekerja')) ?: null,
            'personil_tukang' => trim((string) $this->request->getPost('personil_tukang')) ?: null,
            'cuaca_cerah' => trim((string) $this->request->getPost('cuaca_cerah')) ?: null,
            'cuaca_hujan' => trim((string) $this->request->getPost('cuaca_hujan')) ?: null,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'input_device' => $this->resolveInputDevice(),
        ];
    }

    private function resolveInputDevice(): string
    {
        $userAgent = strtolower((string) $this->request->getServer('HTTP_USER_AGENT'));

        if ($userAgent !== '' && preg_match('/android|iphone|ipod|ipad|mobile|windows phone|blackberry|opera mini/', $userAgent)) {
            return 'mobile';
        }

        return 'laptop';
    }

    private function buildSectionsFromRequest(): array
    {
        $titles = (array) $this->request->getPost('section_title');
        $items = (array) $this->request->getPost('section_items');
        $sections = [];

        foreach ($titles as $index => $title) {
            $sectionTitle = trim((string) $title);
            $sectionItems = trim((string) ($items[$index] ?? ''));

            if ($sectionTitle === '' && $sectionItems === '') {
                continue;
            }

            $lines = preg_split('/\r\n|\r|\n/', $sectionItems) ?: [];
            $normalizedItems = [];
            foreach ($lines as $line) {
                $line = trim((string) $line);
                if ($line !== '') {
                    $normalizedItems[] = $line;
                }
            }

            if ($sectionTitle === '' || $normalizedItems === []) {
                continue;
            }

            $sections[] = [
                'title' => $sectionTitle,
                'items' => $normalizedItems,
            ];
        }

        return $sections;
    }

    private function uploadPerjalananDinasPhotos(string $fieldName): array
    {
        if ($this->isPostBodyTooLarge()) {
            return [
                'photos' => [],
                'error' => 'Upload gagal karena batas server lebih kecil dari total foto yang diunggah. Perbesar post_max_size/upload_max_filesize di server, lalu coba lagi.',
            ];
        }

        $files = $this->request->getFileMultiple($fieldName);
        if (! is_array($files) || $files === []) {
            $files = $this->request->getFileMultiple($fieldName . '[]');
        }

        if (! is_array($files) || $files === []) {
            $singleFile = $this->request->getFile($fieldName);
            if (is_array($singleFile)) {
                $files = $singleFile;
            } elseif ($singleFile !== null) {
                $files = [$singleFile];
            }
        }

        if (! is_array($files) || $files === []) {
            return ['photos' => [], 'error' => null];
        }

        $flatFiles = [];
        array_walk_recursive($files, static function ($file) use (&$flatFiles): void {
            $flatFiles[] = $file;
        });

        $uploadDir = FCPATH . 'uploads/laporan/perjalanan_dinas';
        if (! is_dir($uploadDir) && ! @mkdir($uploadDir, 0775, true) && ! is_dir($uploadDir)) {
            return ['photos' => [], 'error' => 'Folder upload foto tidak dapat dibuat di server.'];
        }

        if (! is_writable($uploadDir)) {
            return ['photos' => [], 'error' => 'Folder upload foto tidak dapat ditulis. Periksa permission folder uploads/laporan/perjalanan_dinas.'];
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif'];
        $result = [];
        $newKeterangan = (array) $this->request->getPost('foto_keterangan');
        $keteranganIndex = 0;

        foreach ($flatFiles as $file) {
            if (! $file || $file->getError() === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $desc = '';
            if (isset($newKeterangan[$keteranganIndex])) {
                $desc = trim((string) $newKeterangan[$keteranganIndex]);
            }
            $keteranganIndex++;

            $error = (int) $file->getError();
            if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
                return ['photos' => [], 'error' => 'Ukuran foto terlalu besar. Maksimal 10MB per foto.'];
            }

            if ($error !== UPLOAD_ERR_OK || ! $file->isValid()) {
                return ['photos' => [], 'error' => 'Upload foto gagal. Silakan pilih ulang foto dan coba lagi.'];
            }

            if ((int) $file->getSize() > self::DAILY_MAX_FILE_SIZE_BYTES) {
                return ['photos' => [], 'error' => 'Ukuran foto terlalu besar. Maksimal 10MB per foto.'];
            }

            $clientExtension = strtolower((string) $file->getClientExtension());
            $mimeType = strtolower((string) $file->getMimeType());
            $isAllowedImage = in_array($clientExtension, $allowedExtensions, true) || str_starts_with($mimeType, 'image/');

            if (! $isAllowedImage) {
                return ['photos' => [], 'error' => 'Format foto tidak didukung. Gunakan JPG, JPEG, PNG, WEBP, atau HEIC.'];
            }

            $extension = in_array($clientExtension, $allowedExtensions, true) ? $clientExtension : 'jpg';

            try {
                $newName = date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
            } catch (\Throwable $e) {
                return ['photos' => [], 'error' => 'Gagal menyiapkan nama file upload. Silakan coba lagi.'];
            }

            try {
                $file->move($uploadDir, $newName);
            } catch (\Throwable $e) {
                return ['photos' => [], 'error' => 'Gagal menyimpan foto ke server. Periksa permission folder uploads/laporan/perjalanan_dinas.'];
            }

            if (! is_file($uploadDir . DIRECTORY_SEPARATOR . $newName)) {
                return ['photos' => [], 'error' => 'Foto tidak tersimpan di server. Silakan coba lagi.'];
            }

            $result[] = [
                'file_path' => '/uploads/laporan/perjalanan_dinas/' . $newName,
                'name'      => $file->getClientName(),
                'keterangan'=> $desc,
            ];
        }

        return ['photos' => $result, 'error' => null];
    }

    /**
     * Hapus file fisik foto perjalanan dinas dari disk.
     * Aman untuk dipanggil dengan data lama (format base64 / tidak ada file_path).
     */
    private function deletePerjalananDinasPhotoFile(array $photo): void
    {
        $filePath = trim((string) ($photo['file_path'] ?? ''));
        if ($filePath === '') {
            return;
        }

        // Pastikan path terbatas dalam folder upload perjalanan dinas
        $safePath = ltrim($filePath, '/\\');
        if (strpos($safePath, 'uploads/laporan/perjalanan_dinas/') !== 0) {
            return;
        }

        $absPath = FCPATH . $safePath;
        if (is_file($absPath)) {
            @unlink($absPath);
        }
    }

    private function uploadPerjalananDinasFiles(string $fieldName): array
    {
        if ($this->isPostBodyTooLarge()) {
            return [
                'files' => [],
                'error' => 'Upload gagal karena batas server lebih kecil dari total file yang diunggah. Perbesar post_max_size/upload_max_filesize di server, lalu coba lagi.',
            ];
        }

        $files = $this->request->getFileMultiple($fieldName);
        if (! is_array($files) || $files === []) {
            $files = $this->request->getFileMultiple($fieldName . '[]');
        }

        if (! is_array($files) || $files === []) {
            $singleFile = $this->request->getFile($fieldName);
            if (is_array($singleFile)) {
                $files = $singleFile;
            } elseif ($singleFile !== null) {
                $files = [$singleFile];
            }
        }

        if (! is_array($files) || $files === []) {
            return ['files' => [], 'error' => null];
        }

        $flatFiles = [];
        array_walk_recursive($files, static function ($file) use (&$flatFiles): void {
            $flatFiles[] = $file;
        });

        $uploadDir = FCPATH . 'uploads/laporan/perjalanan_dinas_files';
        if (! is_dir($uploadDir) && ! @mkdir($uploadDir, 0775, true) && ! is_dir($uploadDir)) {
            return ['files' => [], 'error' => 'Folder upload dokumen tidak dapat dibuat di server.'];
        }

        if (! is_writable($uploadDir)) {
            return ['files' => [], 'error' => 'Folder upload dokumen tidak dapat ditulis. Periksa permission folder uploads/laporan/perjalanan_dinas_files.'];
        }

        $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip', 'rar'];
        $result = [];
        $newKeterangan = (array) $this->request->getPost('dokumen_keterangan');
        $newTransportasi = (array) $this->request->getPost('dokumen_transportasi');
        $keteranganIndex = 0;

        foreach ($flatFiles as $file) {
            if (! $file || $file->getError() === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $desc = '';
            if (isset($newKeterangan[$keteranganIndex])) {
                $desc = trim((string) $newKeterangan[$keteranganIndex]);
            }
            $trans = '';
            if (isset($newTransportasi[$keteranganIndex])) {
                $trans = trim((string) $newTransportasi[$keteranganIndex]);
            }
            $keteranganIndex++;

            $error = (int) $file->getError();
            if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
                return ['files' => [], 'error' => 'Ukuran file terlalu besar. Maksimal 10MB per file.'];
            }

            if ($error !== UPLOAD_ERR_OK || ! $file->isValid()) {
                return ['files' => [], 'error' => 'Upload file gagal. Silakan pilih ulang file dan coba lagi.'];
            }

            if ((int) $file->getSize() > self::DAILY_MAX_FILE_SIZE_BYTES) {
                return ['files' => [], 'error' => 'Ukuran file terlalu besar. Maksimal 10MB per file.'];
            }

            $clientExtension = strtolower((string) $file->getClientExtension());
            $isAllowedFile = in_array($clientExtension, $allowedExtensions, true);

            if (! $isAllowedFile) {
                return ['files' => [], 'error' => 'Format file tidak didukung. Gunakan PDF, JPG, JPEG, PNG, DOC, DOCX, XLS, XLSX, TXT, ZIP, atau RAR.'];
            }

            try {
                $newName = date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $clientExtension;
            } catch (\Throwable $e) {
                return ['files' => [], 'error' => 'Gagal menyiapkan nama file upload. Silakan coba lagi.'];
            }

            try {
                $file->move($uploadDir, $newName);
            } catch (\Throwable $e) {
                return ['files' => [], 'error' => 'Gagal menyimpan file ke server. Periksa permission folder uploads/laporan/perjalanan_dinas_files.'];
            }

            if (! is_file($uploadDir . DIRECTORY_SEPARATOR . $newName)) {
                return ['files' => [], 'error' => 'File tidak tersimpan di server. Silakan coba lagi.'];
            }

            $result[] = [
                'file_path'  => '/uploads/laporan/perjalanan_dinas_files/' . $newName,
                'name'       => $file->getClientName(),
                'keterangan' => $desc,
                'transportasi' => $trans,
            ];
        }

        return ['files' => $result, 'error' => null];
    }

    private function deletePerjalananDinasFile(array $fileInfo): void
    {
        $filePath = trim((string) ($fileInfo['file_path'] ?? ''));
        if ($filePath === '') {
            return;
        }

        $safePath = ltrim($filePath, '/\\');
        if (strpos($safePath, 'uploads/laporan/perjalanan_dinas_files/') !== 0) {
            return;
        }

        $absPath = FCPATH . $safePath;
        if (is_file($absPath)) {
            @unlink($absPath);
        }
    }

    private function uploadDailyPhotos(string $fieldName): array
    {
        if ($this->isPostBodyTooLarge()) {
            return [
                'photos' => [],
                'error' => 'Upload gagal karena batas server lebih kecil dari total foto yang diunggah. Perbesar post_max_size/upload_max_filesize di server, lalu coba lagi.',
            ];
        }

        $files = $this->request->getFileMultiple($fieldName);
        if (! is_array($files) || $files === []) {
            $files = $this->request->getFileMultiple($fieldName . '[]');
        }

        if (! is_array($files) || $files === []) {
            $singleFile = $this->request->getFile($fieldName);
            if (is_array($singleFile)) {
                $files = $singleFile;
            } elseif ($singleFile !== null) {
                $files = [$singleFile];
            }
        }

        if (! is_array($files) || $files === []) {
            return [
                'photos' => [],
                'error' => null,
            ];
        }

        $flatFiles = [];
        array_walk_recursive($files, static function ($file) use (&$flatFiles): void {
            $flatFiles[] = $file;
        });

        $result = [];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif'];
        foreach ($flatFiles as $file) {
            if (! $file || $file->getError() === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $error = (int) $file->getError();
            if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
                return [
                    'photos' => [],
                    'error' => 'Ukuran foto terlalu besar. Maksimal 10MB per foto.',
                ];
            }

            if ($error !== UPLOAD_ERR_OK || ! $file->isValid()) {
                return [
                    'photos' => [],
                    'error' => 'Upload foto gagal. Silakan pilih ulang foto dan coba lagi.',
                ];
            }

            if ((int) $file->getSize() > self::DAILY_MAX_FILE_SIZE_BYTES) {
                return [
                    'photos' => [],
                    'error' => 'Ukuran foto terlalu besar. Maksimal 10MB per foto.',
                ];
            }

            $clientExtension = strtolower((string) $file->getClientExtension());
            $mimeType = strtolower((string) $file->getMimeType());
            $isAllowedImage = in_array($clientExtension, $allowedExtensions, true) || str_starts_with($mimeType, 'image/');

            if (! $isAllowedImage) {
                return [
                    'photos' => [],
                    'error' => 'Format foto tidak didukung. Gunakan JPG, JPEG, PNG, WEBP, atau HEIC.',
                ];
            }

            $directory = FCPATH . 'uploads/laporan/harian';
            if (! is_dir($directory) && ! @mkdir($directory, 0775, true) && ! is_dir($directory)) {
                return [
                    'photos' => [],
                    'error' => 'Folder upload foto tidak dapat dibuat di server.',
                ];
            }

            if (! is_writable($directory)) {
                return [
                    'photos' => [],
                    'error' => 'Folder upload foto tidak dapat ditulis. Periksa permission folder uploads/laporan/harian.',
                ];
            }

            $extension = $clientExtension;
            if (! in_array($extension, $allowedExtensions, true)) {
                $extension = strtolower((string) $file->getExtension());
            }

            if (! in_array($extension, $allowedExtensions, true)) {
                $extension = 'jpg';
            }

            try {
                $newName = date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
            } catch (\Throwable $e) {
                return [
                    'photos' => [],
                    'error' => 'Gagal menyiapkan nama file upload. Silakan coba lagi.',
                ];
            }

            try {
                $file->move($directory, $newName);
            } catch (\Throwable $e) {
                return [
                    'photos' => [],
                    'error' => 'Gagal menyimpan foto ke server. Periksa permission folder uploads/laporan/harian.',
                ];
            }

            if (! is_file($directory . DIRECTORY_SEPARATOR . $newName)) {
                return [
                    'photos' => [],
                    'error' => 'Foto tidak tersimpan di server. Silakan coba lagi.',
                ];
            }

            $result[] = '/uploads/laporan/harian/' . $newName;
        }

        return [
            'photos' => $result,
            'error' => null,
        ];
    }

    private function uploadWeeklyFile(string $fieldName): ?array
    {
        $file = $this->request->getFile($fieldName);
        if (! $file || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if (! $file->isValid()) {
            return null;
        }

        if ($file->getSize() > self::WEEKLY_MAX_FILE_SIZE_BYTES) {
            return null;
        }

        $extension = strtolower((string) $file->getExtension());
        if (! in_array($extension, ['pdf', 'ppt', 'pptx'], true)) {
            return null;
        }

        $directory = FCPATH . 'uploads/laporan/mingguan';
        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $newName = date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        $file->move($directory, $newName);

        return [
            'file_path' => '/uploads/laporan/mingguan/' . $newName,
            'file_name' => $file->getClientName(),
        ];
    }

    private function weeklyUploadMessage(string $fieldName, bool $required): string|null
    {
        $file = $this->request->getFile($fieldName);
        if (! $file) {
            if ($this->isPostBodyTooLarge()) {
                return 'Upload gagal karena batas server lebih kecil dari file yang diunggah. Perbesar post_max_size/upload_max_filesize di server, lalu coba lagi.';
            }

            return $required ? 'File laporan mingguan wajib diunggah.' : null;
        }

        $error = $file->getError();
        if ($error === UPLOAD_ERR_NO_FILE) {
            if ($this->isPostBodyTooLarge()) {
                return 'Upload gagal karena batas server lebih kecil dari file yang diunggah. Perbesar post_max_size/upload_max_filesize di server, lalu coba lagi.';
            }

            return $required ? 'File laporan mingguan wajib diunggah.' : null;
        }

        if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
            return 'Ukuran file terlalu besar. Maksimal 10MB.';
        }

        if ($error !== UPLOAD_ERR_OK) {
            return 'Upload file gagal. Silakan coba lagi.';
        }

        $extension = strtolower((string) $file->getExtension());
        if (! in_array($extension, ['pdf', 'ppt', 'pptx'], true)) {
            return 'Format file harus PDF, PPT, atau PPTX.';
        }

        if ($file->getSize() > self::WEEKLY_MAX_FILE_SIZE_BYTES) {
            return 'Ukuran file terlalu besar. Maksimal 10MB.';
        }

        return 'Upload file gagal. Silakan coba lagi.';
    }

    private function isPostBodyTooLarge(): bool
    {
        $postMax = $this->iniSizeToBytes((string) ini_get('post_max_size'));
        $contentLength = (int) $this->request->getServer('CONTENT_LENGTH');

        return $postMax > 0 && $contentLength > 0 && $contentLength > $postMax;
    }

    private function iniSizeToBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '') {
            return 0;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        if ($number <= 0) {
            return 0;
        }

        switch ($unit) {
            case 'g':
                return (int) round($number * 1024 * 1024 * 1024);
            case 'm':
                return (int) round($number * 1024 * 1024);
            case 'k':
                return (int) round($number * 1024);
            default:
                return (int) round($number);
        }
    }

    private function deleteStoredFiles(array $paths): void
    {
        foreach ($paths as $path) {
            $this->deleteStoredFile((string) $path);
        }
    }

    private function deleteStoredFile(string $path): void
    {
        $path = trim($path);
        if ($path === '' || strpos($path, '/uploads/') !== 0) {
            return;
        }

        $filePath = FCPATH . ltrim($path, '/');
        if (is_file($filePath)) {
            @unlink($filePath);
        }
    }

    private function normalizeDateValue(string $date): ?string
    {
        $date = trim($date);
        if ($date === '') {
            return null;
        }

        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d', $timestamp);
    }

    private function startOfWeek(string $date): string
    {
        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return $date;
        }

        $dayOfWeek = (int) date('N', $timestamp);
        return date('Y-m-d', strtotime('-' . ($dayOfWeek - 1) . ' days', $timestamp));
    }

    private function endOfWeek(string $date): string
    {
        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return $date;
        }

        $dayOfWeek = (int) date('N', $timestamp);
        return date('Y-m-d', strtotime('+' . (7 - $dayOfWeek) . ' days', $timestamp));
    }

    private function normalizeCoordinateValue(string $value, float $min, float $max): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $number = filter_var($value, FILTER_VALIDATE_FLOAT);
        if ($number === false) {
            return null;
        }

        $number = (float) $number;
        if ($number < $min || $number > $max) {
            return null;
        }

        return number_format($number, 7, '.', '');
    }

    private function randomCoordinate(float $min, float $max): string
    {
        $random = $min + (mt_rand() / mt_getrandmax()) * ($max - $min);
        return number_format($random, 7, '.', '');
    }

    private function isLocalhostRequest(): bool
    {
        $host = strtolower((string) $this->request->getServer('HTTP_HOST'));
        $host = trim(explode(':', $host)[0] ?? $host);

        return in_array($host, ['localhost', '127.0.0.1', '::1'], true);
    }

    private function dailyRedirectUrl(int $titleId): string
    {
        if ($titleId > 0) {
            return site_url('admin/laporan/harian/' . $titleId);
        }

        return site_url('admin/laporan/harian');
    }

    protected function canViewLaporan(): bool
    {
        $role = strtolower((string) session()->get('role'));
        return in_array($role, ['admin', 'editor', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);
    }

    protected function canManageLaporan(): bool
    {
        $role = strtolower((string) session()->get('role'));
        return in_array($role, ['admin', 'editor', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);
    }

    private function getUploadErrorMessage(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE => 'Ukuran file melebihi batas upload_max_filesize server.',
            UPLOAD_ERR_FORM_SIZE => 'Ukuran file melebihi batas MAX_FILE_SIZE pada form.',
            UPLOAD_ERR_PARTIAL => 'File hanya terunggah sebagian. Silakan coba lagi.',
            UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diunggah.',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary upload tidak ditemukan.',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk.',
            UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh ekstensi PHP.',
            default => 'Terjadi kesalahan saat unggah file.',
        };
    }

    private function resolveMataAnggaran(array $row): string
    {
        $db = db_connect();
        if (! $db->tableExists('mst_mata_anggaran')) {
            return '';
        }

        $mataAnggaranId = (int) ($row['mata_anggaran_id'] ?? 0);
        if ($mataAnggaranId > 0) {
            $ma = $db->table('mst_mata_anggaran')->where('id', $mataAnggaranId)->get()->getRowArray();
            if (is_array($ma) && ! empty($ma['mata_anggaran'])) {
                return (string) $ma['mata_anggaran'];
            }
        }

        return '';
    }

    public function setLastKodeNomor()
    {
        if (! $this->canVerifyLaporan()) {
            if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Anda tidak memiliki hak akses untuk mengatur nomor terakhir.']);
        }
        return redirect()->back()->with('error', 'Anda tidak memiliki hak akses untuk mengatur nomor terakhir.');
        }

        $lastNumberRaw = trim((string) $this->request->getPost('last_number'));
        $lastNumber = (int) preg_replace('/\D/', '', $lastNumberRaw);

        $this->resequenceAllReports($lastNumber);

        $nextFormat = str_pad((string) ($lastNumber + 1), 3, '0', STR_PAD_LEFT);
        return redirect()->back()->with('success', 'Nomor Terakhir SPPD/Kwitansi berhasil disimpan (' . $lastNumber . '). Seluruh data laporan dari yang paling awal telah diurutkan berurutan mulai dari nomor ' . $nextFormat . '.');
    }

    private function resequenceAllReports(int $lastNumberBefore): void
    {
        $db = \Config\Database::connect();
        if (! $db->tableExists('laporan_perjalanan_dinas')) {
            return;
        }

        $reports = $db->table('laporan_perjalanan_dinas')
            ->select('id, pelaksana_json')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        if (empty($reports)) {
            if ($db->tableExists('app_settings')) {
                $existing = $db->table('app_settings')->get()->getRowArray();
                if (is_array($existing)) {
                    $db->table('app_settings')->update(['last_kode_nomor_sppd' => $lastNumberBefore]);
                } else {
                    $db->table('app_settings')->insert(['last_kode_nomor_sppd' => $lastNumberBefore]);
                }
            }
            return;
        }

        $runningNum = $lastNumberBefore + 1;

        foreach ($reports as $rep) {
            $repId = (int) $rep['id'];
            $pelaksanaList = json_decode((string) ($rep['pelaksana_json'] ?? '[]'), true) ?: [];
            $totalPelaksana = max(1, count($pelaksanaList));

            $formattedKode = str_pad((string) $runningNum, 3, '0', STR_PAD_LEFT);
            $db->table('laporan_perjalanan_dinas')->where('id', $repId)->update(['kode_nomor' => $formattedKode]);

            $runningNum += $totalPelaksana;
        }

        $finalLastNumber = $runningNum - 1;
        if ($db->tableExists('app_settings')) {
            $existing = $db->table('app_settings')->get()->getRowArray();
            if (is_array($existing)) {
                $db->table('app_settings')->update(['last_kode_nomor_sppd' => $finalLastNumber]);
            } else {
                $db->table('app_settings')->insert(['last_kode_nomor_sppd' => $finalLastNumber]);
            }
        }
    }

    private function getLastKodeNomorSetting(): int
    {
        $db = \Config\Database::connect();
        if ($db->tableExists('app_settings')) {
            $settingRow = $db->table('app_settings')->select('last_kode_nomor_sppd')->get()->getRowArray();
            if (is_array($settingRow) && isset($settingRow['last_kode_nomor_sppd'])) {
                return (int) $settingRow['last_kode_nomor_sppd'];
            }
        }
        return 0;
    }

    private function ensureKodeNomorAssigned(array &$row, string $customKodeInput = ''): void
    {
        $id = (int) ($row['id'] ?? 0);
        if ($id <= 0) {
            return;
        }

        $pelaksanaList = json_decode((string) ($row['pelaksana_json'] ?? '[]'), true) ?: [];
        $totalPelaksana = max(1, count($pelaksanaList));
        $year = ! empty($row['periode_mulai']) && strtotime((string) $row['periode_mulai']) !== false
            ? date('Y', strtotime((string) $row['periode_mulai']))
            : date('Y');

        $kodeToUse = trim($customKodeInput !== '' ? $customKodeInput : (string) ($row['kode_nomor'] ?? ''));
        $db = \Config\Database::connect();

        // If kode is completely empty OR if it's just a suffix like "/SPD/SATKER/PPS-RIAU/2026" without leading digits:
        if ($kodeToUse === '' || preg_match('/^\/[A-Za-z]/', $kodeToUse)) {
            $suffix = ($kodeToUse !== '' && strpos($kodeToUse, '/') !== false)
                ? substr($kodeToUse, strpos($kodeToUse, '/'))
                : '/SPD/SATKER/PPS-RIAU/' . $year;

            $lastSettingNumber = $this->getLastKodeNomorSetting();
            $maxDbNumber = 0;
            if ($db->tableExists('laporan_perjalanan_dinas')) {
                $maxRow = $db->query("SELECT MAX(CAST(kode_nomor AS UNSIGNED)) AS max_num FROM laporan_perjalanan_dinas WHERE kode_nomor REGEXP '^[0-9]+'")->getRowArray();
                if (is_array($maxRow) && isset($maxRow['max_num'])) {
                    $maxDbNumber = (int) $maxRow['max_num'];
                }
            }

            $startNumber = max($lastSettingNumber, $maxDbNumber) + 1;
            $formattedNumber = str_pad((string) $startNumber, 3, '0', STR_PAD_LEFT);
            $fullKode = $formattedNumber . $suffix;

            $db->table('laporan_perjalanan_dinas')->where('id', $id)->update(['kode_nomor' => $fullKode]);

            $endNumber = $startNumber + $totalPelaksana - 1;
            if ($db->tableExists('app_settings')) {
                $db->table('app_settings')->update(['last_kode_nomor_sppd' => $endNumber]);
            }

            $row['kode_nomor'] = $fullKode;
        } else {
            // User provided a full kode with front digits or number (e.g. "016/SPD/SATKER/PPS-RIAU/2026" or "016")
            $fullKode = $kodeToUse;
            if (is_numeric($kodeToUse)) {
                $fullKode = str_pad($kodeToUse, 3, '0', STR_PAD_LEFT) . '/SPD/SATKER/PPS-RIAU/' . $year;
            }

            $db->table('laporan_perjalanan_dinas')->where('id', $id)->update(['kode_nomor' => $fullKode]);
            $row['kode_nomor'] = $fullKode;

            if (preg_match('/^(\d+)/', $fullKode, $m)) {
                $startNum = (int) $m[1];
                $endNumber = $startNum + $totalPelaksana - 1;
                $currentSetting = $this->getLastKodeNomorSetting();
                if ($endNumber > $currentSetting && $db->tableExists('app_settings')) {
                    $db->table('app_settings')->update(['last_kode_nomor_sppd' => $endNumber]);
                }
            }
        }
    }

    private function getBiayaMasterForKota(string $kotaTujuan): array
    {
        $db = \Config\Database::connect();
        $biayaMaster = [
            'harian' => 0,
            'penginapan_e1' => 0,
            'penginapan_e2' => 0,
            'penginapan_e3' => 0,
            'penginapan_e4' => 0,
        ];

        $cleanCity = trim(preg_replace('/^(kota|kabupaten|kab\.)\s+/i', '', $kotaTujuan));

        $kabupaten = null;
        if ($db->tableExists('mst_kabupaten')) {
            $kabupaten = $db->table('mst_kabupaten')->where('nama_kabupaten', $kotaTujuan)->get()->getRowArray();
            if (! $kabupaten && $cleanCity !== '') {
                $kabupaten = $db->table('mst_kabupaten')->like('nama_kabupaten', $cleanCity)->get()->getRowArray();
            }
        }

        if ($kabupaten) {
            $provCode = $kabupaten['kode_provinsi'];
            if ($db->tableExists('mst_biaya_harian')) {
                $mstHarian = $db->table('mst_biaya_harian')->where('provinsi_kode', $provCode)->where('is_active', 1)->get()->getRowArray();
                if ($mstHarian) {
                    $biayaMaster['harian'] = (int) $mstHarian['luar_kota'];
                }
            }
            if ($db->tableExists('mst_biaya_penginapan')) {
                $mstPenginapan = $db->table('mst_biaya_penginapan')->where('provinsi_kode', $provCode)->where('is_active', 1)->get()->getRowArray();
                if ($mstPenginapan) {
                    $biayaMaster['penginapan_e1'] = (int) $mstPenginapan['tarif_eselon1'];
                    $biayaMaster['penginapan_e2'] = (int) $mstPenginapan['tarif_eselon2'];
                    $biayaMaster['penginapan_e3'] = (int) $mstPenginapan['tarif_eselon3'];
                    $biayaMaster['penginapan_e4'] = (int) $mstPenginapan['tarif_eselon4'];
                }
            }
        }

        if ($biayaMaster['harian'] <= 0 && $db->tableExists('mst_biaya_harian')) {
            $defaultHarian = $db->table('mst_biaya_harian')->where('is_active', 1)->get()->getRowArray();
            if ($defaultHarian) {
                $biayaMaster['harian'] = (int) ($defaultHarian['luar_kota'] ?? 370000);
            } else {
                $biayaMaster['harian'] = 370000;
            }
        } elseif ($biayaMaster['harian'] <= 0) {
            $biayaMaster['harian'] = 370000;
        }

        if ($biayaMaster['penginapan_e4'] <= 0) {
            $biayaMaster['penginapan_e1'] = 4000000;
            $biayaMaster['penginapan_e2'] = 1500000;
            $biayaMaster['penginapan_e3'] = 800000;
            $biayaMaster['penginapan_e4'] = 500000;
        }

        return $biayaMaster;
    }

    private function formatNamaGelar(string $nama): string
    {
        $nama = trim($nama);
        if ($nama === '' || $nama === '-') {
            return '-';
        }
        if (strpos($nama, ',') !== false) {
            $parts = explode(',', $nama, 2);
            return strtoupper(trim($parts[0])) . ', ' . trim($parts[1]);
        }
        return strtoupper($nama);
    }

    private function splitJabatanTwoLines(string $jabatan): array
    {
        $jabatan = trim($jabatan);
        if (strlen($jabatan) <= 32) {
            return [$jabatan, ''];
        }
        $words = explode(' ', $jabatan);
        if (count($words) <= 1) {
            return [$jabatan, ''];
        }
        $mid = (int)ceil(strlen($jabatan) / 2);
        $line1 = '';
        $line2 = '';
        foreach ($words as $word) {
            if ($line1 === '' || (strlen($line1) + strlen($word) + 1) <= ($mid + 5)) {
                $line1 .= ($line1 === '' ? '' : ' ') . $word;
            } else {
                $line2 .= ($line2 === '' ? '' : ' ') . $word;
            }
        }
        return [trim($line1), trim($line2)];
    }
}
