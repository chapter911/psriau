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

    private function perjalananDinasDataTable()
    {
        $canEdit = $this->canManageLaporan();
        $role = strtolower((string) session()->get('role'));
        $isSuperAdmin = in_array($role, ['super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);
        $canUploadVerified = in_array($role, ['keuangan', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);
        
        $pegawaiRows = $this->loadPegawaiOptions();
        $currentPegawai = $this->resolveCurrentPegawai(
            (string) (session()->get('fullName') ?: session()->get('username') ?: session()->get('name')), 
            $pegawaiRows
        );
        $currentPegawaiId = $currentPegawai ? (int) $currentPegawai['id'] : 0;
        
        return $this->respondPerjalananDinasDataTable(
            fn () => db_connect()->table('laporan_perjalanan_dinas'),
            function ($builder): void {
                $startDate = trim((string) $this->request->getGet('filter_start_date'));
                $endDate = trim((string) $this->request->getGet('filter_end_date'));
                $kota = trim((string) $this->request->getGet('filter_kota'));
                $pelaksanaId = (int) $this->request->getGet('filter_pelaksana');

                if ($startDate !== '') {
                    $builder->where('periode_mulai >=', $startDate);
                }
                if ($endDate !== '') {
                    $builder->where('periode_selesai <=', $endDate);
                }
                if ($kota !== '') {
                    $builder->where('kota_tujuan', $kota);
                }
                if ($pelaksanaId > 0) {
                    $builder->groupStart()
                        ->like('pelaksana_json', '"id":' . $pelaksanaId . ',')
                        ->orLike('pelaksana_json', '"id":' . $pelaksanaId . '}')
                        ->groupEnd();
                }
            },
            ['nomor_surat_tugas', 'kota_tujuan', 'tujuan', 'sasaran', 'laporan_hasil', 'pelaksana_json', 'creator_name'],
            [
                0 => 'id',
                1 => 'tujuan',
                2 => 'kota_tujuan',
                3 => 'periode_mulai',
                4 => 'id',
                5 => 'id',
                6 => 'id',
            ],
            function (array $row) use ($canEdit, $canUploadVerified, $isSuperAdmin, $currentPegawaiId): array {
                $canVerifyRow = $isSuperAdmin;
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
                if ($isFinal === 1) {
                    $dokumenHtml .= '<a href="' . site_url('admin/surat/perjalanan-dinas/' . (int) ($row['id'] ?? 0) . '/dokumen') . '" class="btn btn-danger" title="Laporan Perjadin (PDF)" target="_blank" rel="noopener noreferrer"><i class="fas fa-file-pdf"></i></a>';
                    
                    if (! empty($row['verified_spt_path'])) {
                        $verifiedUrl = media_url($row['verified_spt_path']);
                        $ext = strtolower(pathinfo((string) $row['verified_spt_path'], PATHINFO_EXTENSION));
                        $icon = in_array($ext, ['jpg', 'jpeg', 'png'], true) ? 'fa-file-image' : 'fa-file-signature';
                        $btnClass = in_array($ext, ['jpg', 'jpeg', 'png'], true) ? 'btn-warning text-white' : 'btn-success';
                        $titleLabel = in_array($ext, ['jpg', 'jpeg', 'png'], true) ? 'Lihat Foto Verified SPT/Perjadin' : 'Unduh Verified SPT (PDF)';
                        $dokumenHtml .= '<a href="' . $verifiedUrl . '" class="btn ' . $btnClass . '" title="' . $titleLabel . '" target="_blank" rel="noopener noreferrer"><i class="fas ' . $icon . '"></i></a>';
                    }

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
                } else {
                    $dokumenHtml .= '<span class="badge badge-warning py-1 px-2 font-weight-bold" style="font-size:0.75rem;"><i class="fas fa-hourglass-start mr-1"></i> Belum Selesai</span>';
                }
                $dokumenHtml .= '</div>';
                
                $row['dokumen_html'] = $dokumenHtml;
                
                // Add conditional Upload Verified cell
                if ($canUploadVerified && $isFinal === 1) {
                    $existingFile = ! empty($row['verified_spt_path']) ? esc((string) $row['verified_spt_path'], 'attr') : '';
                    $nomorSurat = esc((string) ($row['nomor_surat_tugas'] ?? '-'), 'attr');
                    if ($existingFile !== '') {
                        $row['upload_verified_html'] = '<button type="button" class="btn btn-sm btn-success btn-upload-verified" data-id="' . (int) ($row['id'] ?? 0) . '" data-nomor="' . $nomorSurat . '" data-existing="' . $existingFile . '" title="Re-upload Verified Perjadin & SPT"><i class="fas fa-sync-alt"></i> Re-upload</button>';
                    } else {
                        $row['upload_verified_html'] = '<button type="button" class="btn btn-sm btn-primary btn-upload-verified" data-id="' . (int) ($row['id'] ?? 0) . '" data-nomor="' . $nomorSurat . '" data-existing="" title="Upload Verified Perjadin & SPT"><i class="fas fa-upload"></i> Upload</button>';
                    }
                } else {
                    $row['upload_verified_html'] = '<span class="text-muted">-</span>';
                }
                
                if ($canEdit) {
                    $row['action_html'] = '<div class="d-flex justify-content-center align-items-center" style="gap: 5px; white-space: nowrap;">';
                    $row['action_html'] .= '<a href="' . site_url('admin/surat/perjalanan-dinas/' . (int) ($row['id'] ?? 0) . '/ubah') . '" class="btn btn-sm btn-outline-primary" title="Ubah Data"><i class="fas fa-pen"></i></a>';
                    $row['action_html'] .= '<button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-id="' . (int) ($row['id'] ?? 0) . '" title="Hapus"><i class="fas fa-trash"></i></button>';
                    $row['action_html'] .= '</div>';
                } else {
                    $row['action_html'] = '<span class="text-muted">-</span>';
                }

                // Verification column cell HTML
                $isVerified = (int) ($row['is_verified'] ?? 0);
                $dasarSptIds = json_decode((string) ($row['dasar_spt_ids_json'] ?? '[]'), true) ?: [];
                $tglTtd = trim((string) ($row['tanggal_tanda_tangan'] ?? ''));
                $nomorSurat = trim((string) ($row['nomor_surat_tugas'] ?? ''));

                $verificationStatusHtml = '';
                if ($isFinal === 0) {
                    $verificationStatusHtml = '<span class="text-muted">-</span>';
                } elseif ($isVerified === 1) {
                    $formattedTtd = $tglTtd !== '' ? tanggal_indonesia($tglTtd) : '-';
                    $badgeTitle = "Nomor: " . esc($nomorSurat) . "\nTanggal: " . $formattedTtd;
                    
                    $verificationStatusHtml .= '<span class="badge badge-success px-2 py-1 shadow-sm" style="font-size:0.78rem; cursor:pointer;" title="' . esc($badgeTitle, 'attr') . '"><i class="fas fa-check-circle mr-1"></i> Terverifikasi</span>';
                    $verificationStatusHtml .= '<div class="mt-1 d-flex justify-content-center align-items-center" style="gap: 4px;">';
                    
                    // Button to print single Surat Tugas
                    $verificationStatusHtml .= '<a href="' . site_url('admin/surat/perjalanan-dinas/' . (int) $row['id'] . '/cetak-spt') . '" class="btn btn-xs btn-outline-danger px-2 font-weight-bold" title="Cetak Surat Tugas (PDF)" target="_blank"><i class="fas fa-file-pdf"></i> SPT</a>';
                    
                    if ($canVerifyRow) {
                        // Button to edit verification
                        $verificationStatusHtml .= '<button type="button" class="btn btn-xs btn-outline-warning px-2 btn-verify-spt" data-id="' . (int) $row['id'] . '" data-nomor="' . esc($nomorSurat, 'attr') . '" data-dasar="' . esc(json_encode($dasarSptIds), 'attr') . '" data-tgl="' . esc($tglTtd, 'attr') . '" title="Ubah Verifikasi"><i class="fas fa-edit"></i> Edit</button>';
                    }
                    
                    $verificationStatusHtml .= '</div>';
                } else {
                    $verificationStatusHtml .= '<span class="badge badge-warning px-2 py-1 shadow-sm text-dark" style="font-size:0.78rem;"><i class="fas fa-clock mr-1"></i> Belum Verifikasi</span>';
                    if ($canVerifyRow) {
                        $verificationStatusHtml .= '<div class="mt-1">';
                        $verificationStatusHtml .= '<button type="button" class="btn btn-xs btn-primary px-3 btn-verify-spt" data-id="' . (int) $row['id'] . '" data-nomor="' . esc($nomorSurat, 'attr') . '" data-dasar="[]" data-tgl="" title="Lakukan Verifikasi"><i class="fas fa-check-double mr-1"></i> Verifikasi</button>';
                        $verificationStatusHtml .= '</div>';
                    }
                }
                
                $row['verification_status_html'] = $verificationStatusHtml;

                return $row;
            }
        );
    }

    public function perjalananDinasVerify(int $id)
    {
        $model = new LaporanPerjalananDinasModel();
        $row = $model->find($id);
        if (! is_array($row)) {
            return redirect()->to(site_url('admin/surat/perjalanan-dinas'))->with('error', 'Data laporan tidak ditemukan.');
        }

        $role = strtolower((string) session()->get('role'));
        $isSuperAdmin = in_array($role, ['super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);
        
        $pegawaiRows = $this->loadPegawaiOptions();
        $currentPegawai = $this->resolveCurrentPegawai(
            (string) (session()->get('fullName') ?: session()->get('username') ?: session()->get('name')), 
            $pegawaiRows
        );
        $currentPegawaiId = $currentPegawai ? (int) $currentPegawai['id'] : 0;

        $canVerifyRow = $isSuperAdmin;
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
            return redirect()->to(site_url('admin/surat/perjalanan-dinas'))->with('error', 'Anda tidak memiliki hak akses untuk memverifikasi laporan perjalanan dinas.');
        }

        $nomorSurat = trim((string) $this->request->getPost('nomor_surat_tugas'));
        $dasarIds = $this->request->getPost('dasar_spt_ids') ?: [];
        $tglTtd = trim((string) $this->request->getPost('tanggal_tanda_tangan'));

        if ($nomorSurat === '') {
            return redirect()->to(site_url('admin/surat/perjalanan-dinas'))->with('error', 'Nomor Surat Tugas wajib diisi.');
        }

        if ($tglTtd === '') {
            return redirect()->to(site_url('admin/surat/perjalanan-dinas'))->with('error', 'Tanggal tanda tangan wajib diisi.');
        }

        $model->update($id, [
            'nomor_surat_tugas' => $nomorSurat,
            'dasar_spt_ids_json' => json_encode(array_map('intval', (array) $dasarIds)),
            'tanggal_tanda_tangan' => $tglTtd,
            'is_verified' => 1,
        ]);

        return redirect()->to(site_url('admin/surat/perjalanan-dinas'))->with('success', 'Laporan perjalanan dinas berhasil diverifikasi.');
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

        if ((int) ($row['is_verified'] ?? 0) !== 1) {
            return redirect()->to(site_url('admin/surat/perjalanan-dinas'))->with('error', 'Laporan perjalanan dinas belum diverifikasi.');
        }

        $dasarIds = json_decode((string) ($row['dasar_spt_ids_json'] ?? '[]'), true) ?: [];
        $dasarRows = [];
        if ($dasarIds !== []) {
            $dasarRows = (new \App\Models\MstDasarSptModel())
                ->whereIn('id', $dasarIds)
                ->orderBy('id', 'ASC')
                ->findAll();
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
        $builder = $model->where('is_verified', 1);
        $builder->where('periode_mulai >=', $startDate);
        $builder->where('periode_selesai <=', $endDate);
        
        $rows = $builder->orderBy('tanggal_tanda_tangan', 'ASC')
                        ->orderBy('periode_mulai', 'ASC')
                        ->findAll();

        if ($rows === []) {
            return redirect()->to(site_url('admin/surat/perjalanan-dinas'))->with('error', 'Tidak ada data perjalanan dinas terverifikasi dalam periode tersebut.');
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
                if (isset($dasarLookup[(int) $dId])) {
                    $dasarRows[] = $dasarLookup[(int) $dId];
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
                'pelaksana' => $this->decodeJsonArray((string) ($row['pelaksana_json'] ?? '[]')),
                'tanggal_tanda_tangan' => (string) ($row['tanggal_tanda_tangan'] ?? ''),
                'diketahui_oleh' => $this->decodeJsonObject((string) ($row['diketahui_oleh_json'] ?? '{}')),
                'dasar_spt' => $dasarRows,
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
            'pelaksana' => $this->decodeJsonArray((string) ($row['pelaksana_json'] ?? '[]')),
            'tanggal_tanda_tangan' => (string) ($row['tanggal_tanda_tangan'] ?? ''),
            'diketahui_oleh' => $this->decodeJsonObject((string) ($row['diketahui_oleh_json'] ?? '{}')),
            'dasar_spt' => $dasarRows,
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

        $role = strtolower((string) session()->get('role'));
        $canUploadVerified = in_array($role, ['keuangan', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);
        if (! $canUploadVerified) {
            return redirect()->to(site_url('admin/surat/perjalanan-dinas'))->with('error', 'Anda tidak memiliki hak akses untuk mengupload verified perjadin & SPT.');
        }

        $model = new LaporanPerjalananDinasModel();
        $row = $model->find($id);
        if (! is_array($row)) {
            return redirect()->to(site_url('admin/surat/perjalanan-dinas'))->with('error', 'Data laporan tidak ditemukan.');
        }

        $file = $this->request->getFile('verified_spt');
        if (! $file || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return redirect()->to(site_url('admin/surat/perjalanan-dinas'))->with('error', 'Silakan pilih file terlebih dahulu.');
        }

        if (! $file->isValid()) {
            return redirect()->to(site_url('admin/surat/perjalanan-dinas'))->with('error', 'File tidak valid: ' . $file->getErrorString());
        }

        $ext = strtolower($file->getClientExtension());
        if (! in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'], true)) {
            return redirect()->to(site_url('admin/surat/perjalanan-dinas'))->with('error', 'Ekstensi file tidak diizinkan. Hanya menerima PDF, JPG, JPEG, PNG.');
        }

        if ($file->getSize() > 10 * 1024 * 1024) {
            return redirect()->to(site_url('admin/surat/perjalanan-dinas'))->with('error', 'Ukuran file maksimal adalah 10MB.');
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

            return redirect()->to(site_url('admin/surat/perjalanan-dinas'))->with('success', 'File verified perjadin & SPT berhasil diupload.');
        }

        return redirect()->to(site_url('admin/surat/perjalanan-dinas'))->with('error', 'Gagal memindahkan file ke direktori upload.');
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
            'pelaksana' => $this->decodeJsonArray((string) ($row['pelaksana_json'] ?? '[]')),
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
                'form_action' => site_url('admin/surat/perjalanan-dinas/' . $id . '/ubah'),
                'is_edit' => true,
                'submit_label_primary' => 'Simpan Perubahan',
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
        foreach (['nomor_surat_tugas', 'periode_mulai', 'periode_selesai', 'kota_tujuan', 'tujuan', 'sasaran', 'laporan_hasil'] as $requiredField) {
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
        if (is_array($existingDocKeterangan)) {
            foreach ($existingDocs as $idx => &$doc) {
                if (is_array($doc)) {
                    $doc['keterangan'] = isset($existingDocKeterangan[$idx]) ? trim((string) $existingDocKeterangan[$idx]) : ($doc['keterangan'] ?? '');
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
                'form_action' => site_url('admin/surat/perjalanan-dinas/' . $id . '/ubah'),
                'is_edit' => true,
                'submit_label_primary' => 'Simpan Perubahan',
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
        if (! $ok) {
            return redirect()->to(site_url('admin/surat/perjalanan-dinas/' . $id . '/ubah'))->with('error', 'Gagal memperbarui laporan.');
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
        foreach (['nomor_surat_tugas', 'periode_mulai', 'periode_selesai', 'kota_tujuan', 'tujuan', 'sasaran', 'laporan_hasil'] as $requiredField) {
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

        // Map draft/existing document descriptions (using original indices)
        $existingDocKeterangan = $this->request->getPost('existing_dokumen_keterangan');
        if (is_array($existingDocKeterangan)) {
            foreach ($draftFiles as $idx => &$doc) {
                if (is_array($doc)) {
                    $doc['keterangan'] = isset($existingDocKeterangan[$idx]) ? trim((string) $existingDocKeterangan[$idx]) : ($doc['keterangan'] ?? '');
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

            return redirect()->to(site_url('admin/surat/perjalanan-dinas'))->with('success', 'Laporan berhasil disimpan.');
        }

        // Fallback: if save mode not recognized, redirect back with notice.
        return redirect()->to(site_url('admin/surat/perjalanan-dinas/buat'))->with('error', 'Aksi simpan tidak valid.');
    }

    private function loadPegawaiOptions(): array
    {
        if (! db_connect()->tableExists('mst_pegawai')) {
            return [];
        }

        $rows = (new MstPegawaiModel())
            ->select('mst_pegawai.id, mst_pegawai.nip, mst_pegawai.nama, mst_pegawai.jabatan_utama_id, ju.jabatan AS jabatan_label, mst_pegawai.is_active')
            ->join('mst_jabatan ju', 'ju.id = mst_pegawai.jabatan_utama_id', 'left')
            ->where('mst_pegawai.is_active', 1)
            ->orderBy('mst_pegawai.nama', 'ASC')
            ->orderBy('mst_pegawai.nip', 'ASC')
            ->findAll();

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
                'id' => (int) ($row['id'] ?? 0),
                'nama' => (string) ($row['nama'] ?? ''),
                'nip' => (string) ($row['nip'] ?? ''),
                'jabatan' => (string) ($row['jabatan_label'] ?? ''),
            ];
        }

        return $rows;
    }

    private function findPegawaiById(array $pegawaiRows, int $id): ?array
    {
        foreach ($pegawaiRows as $row) {
            if ((int) ($row['id'] ?? 0) === $id) {
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
            default => 'Error upload tidak dikenal (code: ' . $errorCode . ').',
        };
    }
}
