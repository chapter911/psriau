<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LaporanHarianReportModel;
use App\Models\LaporanHarianTitleModel;
use App\Models\LaporanMingguanReportModel;
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
        ];
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

    private function decodeJsonArray(string $json): array
    {
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
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

    private function canViewLaporan(): bool
    {
        $role = strtolower((string) session()->get('role'));
        return in_array($role, ['admin', 'editor', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);
    }

    private function canManageLaporan(): bool
    {
        $role = strtolower((string) session()->get('role'));
        return in_array($role, ['admin', 'editor', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);
    }
}
