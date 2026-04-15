<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\KegiatanLapanganModel;
use App\Models\KegiatanLapanganPhotoModel;
use App\Models\KegiatanLapanganShareModel;

class Dokumentasi extends BaseController
{
    private const MAX_PHOTOS = 50;

    public function index(): string
    {
        $filterTitle = trim((string) $this->request->getGet('title'));
        $filterDateFrom = trim((string) $this->request->getGet('date_from'));
        $filterDateTo = trim((string) $this->request->getGet('date_to'));
        $filterLocation = trim((string) $this->request->getGet('location'));

        return view('admin/dokumentasi/index', [
            'pageTitle' => 'Kegiatan Lapangan',
            'filters' => [
                'title' => $filterTitle,
                'date_from' => $filterDateFrom,
                'date_to' => $filterDateTo,
                'location' => $filterLocation,
            ],
        ]);
    }

    public function dataTable()
    {
        $db = db_connect();

        $draw = (int) $this->request->getGet('draw');
        $start = max(0, (int) $this->request->getGet('start'));
        $length = (int) $this->request->getGet('length');
        if ($length <= 0) {
            $length = 10;
        }

        $filterTitle = trim((string) $this->request->getGet('title'));
        $filterDateFrom = trim((string) $this->request->getGet('date_from'));
        $filterDateTo = trim((string) $this->request->getGet('date_to'));
        $filterLocation = trim((string) $this->request->getGet('location'));
        $globalSearch = trim((string) ($this->request->getGet('search')['value'] ?? ''));

        $orderColumns = [
            0 => 'title',
            1 => 'activity_date',
            2 => 'location',
            4 => 'created_by',
            8 => 'id',
        ];

        $orderColumnIndex = (int) ($this->request->getGet('order')[0]['column'] ?? 1);
        $orderDirection = strtolower((string) ($this->request->getGet('order')[0]['dir'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';
        $orderBy = $orderColumns[$orderColumnIndex] ?? 'activity_date';

        $applyFilters = static function ($builder) use ($filterTitle, $filterDateFrom, $filterDateTo, $filterLocation, $globalSearch): void {
            if ($filterTitle !== '') {
                $builder->like('title', $filterTitle);
            }

            if ($filterDateFrom !== '' && $filterDateTo !== '') {
                $builder->where('activity_date >=', $filterDateFrom)
                    ->where('activity_date <=', $filterDateTo);
            } elseif ($filterDateFrom !== '') {
                $builder->where('activity_date >=', $filterDateFrom);
            } elseif ($filterDateTo !== '') {
                $builder->where('activity_date <=', $filterDateTo);
            }

            if ($filterLocation !== '') {
                $builder->like('location', $filterLocation);
            }

            if ($globalSearch !== '') {
                $builder->groupStart()
                    ->like('title', $globalSearch)
                    ->orLike('location', $globalSearch)
                    ->orLike('created_by', $globalSearch)
                    ->groupEnd();
            }
        };

        $recordsTotal = (int) $db->table('kegiatan_lapangan')->countAllResults();

        $filteredCountBuilder = $db->table('kegiatan_lapangan');
        $applyFilters($filteredCountBuilder);
        $recordsFiltered = (int) $filteredCountBuilder->countAllResults();

        $dataBuilder = $db->table('kegiatan_lapangan');
        $applyFilters($dataBuilder);
        $rows = $dataBuilder
            ->orderBy($orderBy, $orderDirection)
            ->orderBy('id', 'DESC')
            ->get($length, $start)
            ->getResultArray();

        $activityIds = array_values(array_filter(array_map(static fn (array $row): int => (int) ($row['id'] ?? 0), $rows)));
        $photosByActivity = [];
        $sharesByActivity = [];

        if ($activityIds !== []) {
            $photoRows = $db->table('kegiatan_lapangan_photos')
                ->whereIn('activity_id', $activityIds)
                ->orderBy('sort_order', 'ASC')
                ->orderBy('id', 'ASC')
                ->get()
                ->getResultArray();

            foreach ($photoRows as $photoRow) {
                $activityId = (int) ($photoRow['activity_id'] ?? 0);
                if ($activityId <= 0) {
                    continue;
                }

                $photosByActivity[$activityId][] = [
                    'path' => (string) ($photoRow['photo_path'] ?? ''),
                    'name' => (string) ($photoRow['photo_name'] ?? 'Foto kegiatan'),
                ];
            }

            $shareRows = $db->table('kegiatan_lapangan_shares')
                ->whereIn('activity_id', $activityIds)
                ->get()
                ->getResultArray();

            foreach ($shareRows as $shareRow) {
                $activityId = (int) ($shareRow['activity_id'] ?? 0);
                if ($activityId <= 0) {
                    continue;
                }

                $sharesByActivity[$activityId] = $shareRow;
            }
        }

        $data = [];
        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            $photos = $photosByActivity[$id] ?? [];
            $share = $sharesByActivity[$id] ?? null;
            $shareToken = is_array($share) ? (string) ($share['share_token'] ?? '') : '';
            $shareExpiresAtRaw = is_array($share) ? (string) ($share['expires_at'] ?? '') : '';
            $shareExpiresAt = $this->extractDateOnly($shareExpiresAtRaw);
            $shareIsActive = $shareToken !== '' && $this->isShareActiveByDate($shareExpiresAtRaw);
            $shareStatus = 'none';
            if ($shareToken !== '') {
                if ($shareExpiresAtRaw === '') {
                    $shareStatus = 'permanent';
                } elseif ($this->isShareActiveByDate($shareExpiresAtRaw)) {
                    $shareStatus = 'active';
                } else {
                    $shareStatus = 'expired';
                }
            }

            $data[] = [
                'id' => $id,
                'title' => (string) ($row['title'] ?? '-'),
                'activity_date' => (string) ($row['activity_date'] ?? '-'),
                'location' => (string) ($row['location'] ?? '-'),
                'created_by' => (string) ($row['created_by'] ?? '-'),
                'photos' => $photos,
                'photo_count' => count($photos),
                'cover_photo' => $photos[0]['path'] ?? '',
                'download_zip_url' => site_url('/admin/dokumentasi/kegiatan-lapangan/' . $id . '/download-zip'),
                'share_create_url' => site_url('/admin/dokumentasi/kegiatan-lapangan/' . $id . '/share'),
                'share_deactivate_url' => site_url('/admin/dokumentasi/kegiatan-lapangan/' . $id . '/share/deactivate'),
                'share_public_url' => $shareToken !== '' ? site_url('/kegiatan-lapangan/share/' . $shareToken) : null,
                'share_expires_at' => $shareExpiresAt !== '' ? $shareExpiresAt : null,
                'share_is_active' => $shareIsActive,
                'share_status' => $shareStatus,
                'edit_url' => site_url('/admin/dokumentasi/kegiatan-lapangan/' . $id . '/ubah'),
                'delete_url' => site_url('/admin/dokumentasi/kegiatan-lapangan/' . $id . '/hapus'),
            ];
        }

        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function create()
    {
        if ($this->request->getMethod() === 'POST') {
            return $this->saveData();
        }

        return view('admin/dokumentasi/form', [
            'activity' => null,
            'photos' => [],
            'pageTitle' => 'Tambah Kegiatan Lapangan',
            'actionUrl' => '/admin/dokumentasi/kegiatan-lapangan/tambah',
            'existingPhotoCount' => 0,
        ]);
    }

    public function edit(int $id)
    {
        $activityModel = new KegiatanLapanganModel();
        $photoModel = new KegiatanLapanganPhotoModel();

        $activity = $activityModel->find($id);
        if (! $activity) {
            return redirect()->to('/admin/dokumentasi/kegiatan-lapangan')->with('error', 'Kegiatan lapangan tidak ditemukan.');
        }

        if ($this->request->getMethod() === 'POST') {
            return $this->saveData($id, $activity);
        }

        $photos = $photoModel
            ->where('activity_id', $id)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        return view('admin/dokumentasi/form', [
            'activity' => $activity,
            'photos' => $photos,
            'pageTitle' => 'Ubah Kegiatan Lapangan',
            'actionUrl' => '/admin/dokumentasi/kegiatan-lapangan/' . $id . '/ubah',
            'existingPhotoCount' => count($photos),
        ]);
    }

    public function delete(int $id)
    {
        $activityModel = new KegiatanLapanganModel();
        $photoModel = new KegiatanLapanganPhotoModel();

        $activity = $activityModel->find($id);
        if ($activity) {
            $photos = $photoModel->where('activity_id', $id)->findAll();
            foreach ($photos as $photo) {
                $this->deleteLocalImage($photo['photo_path'] ?? null);
            }

            $photoModel->where('activity_id', $id)->delete();
            $activityModel->delete($id);
        }

        return redirect()->to('/admin/dokumentasi/kegiatan-lapangan')->with('message', 'Kegiatan lapangan berhasil dihapus.');
    }

    public function createShare(int $id)
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Permintaan tidak valid.',
            ]);
        }

        $activityModel = new KegiatanLapanganModel();
        $shareModel = new KegiatanLapanganShareModel();

        $activity = $activityModel->find($id);
        if (! $activity) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Kegiatan lapangan tidak ditemukan.',
            ]);
        }

        $duration = trim((string) $this->request->getPost('duration'));
        $durationMap = [
            '1day' => '+1 day',
            '1week' => '+1 week',
            '1month' => '+1 month',
            'permanent' => null,
        ];

        if (! array_key_exists($duration, $durationMap)) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'message' => 'Durasi berbagi tidak valid.',
            ]);
        }

        $expiresAt = null;
        $expiresAtDate = null;
        if ($durationMap[$duration] !== null) {
            $expiresAtDate = date('Y-m-d', strtotime($durationMap[$duration]));
            $expiresAt = $expiresAtDate . ' 23:59:59';
        }

        $existingShare = $shareModel->where('activity_id', $id)->first();
        $token = is_array($existingShare) ? (string) ($existingShare['share_token'] ?? '') : '';
        if ($token === '') {
            $token = $this->generateShareToken();
            if ($token === null) {
                return $this->response->setStatusCode(500)->setJSON([
                    'status' => 'error',
                    'message' => 'Gagal membuat tautan berbagi.',
                ]);
            }
        }

        $sharePayload = [
            'activity_id' => $id,
            'share_token' => $token,
            'expires_at' => $expiresAt,
            'created_by_user_id' => (int) (session()->get('userId') ?: 0) ?: null,
        ];

        if ($existingShare) {
            $shareModel->update((int) $existingShare['id'], $sharePayload);
        } else {
            $shareModel->insert($sharePayload);
        }

        $isUpdate = (bool) $existingShare;

        return $this->response->setJSON([
            'status' => 'ok',
            'message' => $isUpdate ? 'Durasi tautan berbagi berhasil diperbarui.' : 'Tautan berbagi berhasil dibuat.',
            'share_url' => site_url('/kegiatan-lapangan/share/' . $token),
            'activity_date' => (string) ($activity['activity_date'] ?? ''),
            'expires_at' => $expiresAtDate,
            'is_update' => $isUpdate,
            'csrf_token' => csrf_token(),
            'csrf_hash' => csrf_hash(),
        ]);
    }

    public function deactivateShare(int $id)
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Permintaan tidak valid.',
            ]);
        }

        $shareModel = new KegiatanLapanganShareModel();
        $share = $shareModel->where('activity_id', $id)->first();

        if (! is_array($share)) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Link berbagi tidak ditemukan.',
            ]);
        }

        $shareModel->update((int) $share['id'], [
            'expires_at' => date('Y-m-d', strtotime('-1 day')) . ' 23:59:59',
        ]);

        return $this->response->setJSON([
            'status' => 'ok',
            'message' => 'Link berbagi berhasil dinonaktifkan.',
            'csrf_token' => csrf_token(),
            'csrf_hash' => csrf_hash(),
        ]);
    }

    public function sharedGallery(string $token): string
    {
        $sharedActivity = $this->resolveSharedActivity($token);
        if ($sharedActivity === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Tautan berbagi tidak valid atau sudah kedaluwarsa.');
        }

        return view('public/kegiatan_lapangan_share', [
            'title' => 'Galeri Kegiatan Lapangan',
            'activity' => $sharedActivity['activity'],
            'photos' => $sharedActivity['photos'],
            'shareToken' => $token,
            'expiresAt' => $this->extractDateOnly((string) ($sharedActivity['share']['expires_at'] ?? '')),
        ]);
    }

    public function sharedDownloadZip(string $token)
    {
        $sharedActivity = $this->resolveSharedActivity($token);
        if ($sharedActivity === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Tautan berbagi tidak valid atau sudah kedaluwarsa.');
        }

        $zipResult = $this->buildActivityZip((int) ($sharedActivity['activity']['id'] ?? 0), (string) ($sharedActivity['activity']['title'] ?? 'kegiatan-lapangan'));
        if ($zipResult['error'] !== null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound($zipResult['error']);
        }

        $zipPath = (string) ($zipResult['path'] ?? '');
        $downloadName = (string) ($zipResult['name'] ?? 'dokumentasi-kegiatan.zip');

        register_shutdown_function(static function () use ($zipPath): void {
            if (is_file($zipPath)) {
                @unlink($zipPath);
            }
        });

        return $this->response->download($zipPath, null)->setFileName($downloadName);
    }

    public function sharedDownloadPhoto(string $token, int $photoId)
    {
        $sharedActivity = $this->resolveSharedActivity($token);
        if ($sharedActivity === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Tautan berbagi tidak valid atau sudah kedaluwarsa.');
        }

        $photoModel = new KegiatanLapanganPhotoModel();
        $photo = $photoModel
            ->where('id', $photoId)
            ->where('activity_id', (int) ($sharedActivity['activity']['id'] ?? 0))
            ->first();

        if (! is_array($photo)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Foto tidak ditemukan.');
        }

        $photoPath = (string) ($photo['photo_path'] ?? '');
        if ($photoPath === '' || strpos($photoPath, '/uploads/') !== 0) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Foto tidak valid.');
        }

        $absolutePath = FCPATH . ltrim($photoPath, '/');
        if (! is_file($absolutePath)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('File foto tidak ditemukan.');
        }

        $fileName = trim((string) ($photo['photo_name'] ?? ''));
        if ($fileName === '') {
            $fileName = basename($absolutePath);
        }

        return $this->response->download($absolutePath, null)->setFileName($fileName);
    }

    public function downloadZip(int $id)
    {
        $activityModel = new KegiatanLapanganModel();

        $activity = $activityModel->find($id);
        if (! $activity) {
            return redirect()->to('/admin/dokumentasi/kegiatan-lapangan')->with('error', 'Kegiatan lapangan tidak ditemukan.');
        }

        $zipResult = $this->buildActivityZip((int) $id, (string) ($activity['title'] ?? 'kegiatan-lapangan'));
        if ($zipResult['error'] !== null) {
            return redirect()->to('/admin/dokumentasi/kegiatan-lapangan')->with('error', (string) $zipResult['error']);
        }

        $zipPath = (string) ($zipResult['path'] ?? '');
        $downloadName = (string) ($zipResult['name'] ?? 'dokumentasi-kegiatan.zip');

        register_shutdown_function(static function () use ($zipPath): void {
            if (is_file($zipPath)) {
                @unlink($zipPath);
            }
        });

        return $this->response->download($zipPath, null)->setFileName($downloadName);
    }

    private function generateShareToken(): ?string
    {
        try {
            return bin2hex(random_bytes(24));
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function resolveSharedActivity(string $token): ?array
    {
        $token = trim($token);
        if ($token === '') {
            return null;
        }

        $shareModel = new KegiatanLapanganShareModel();
        $activityModel = new KegiatanLapanganModel();
        $photoModel = new KegiatanLapanganPhotoModel();

        $share = $shareModel->where('share_token', $token)->first();
        if (! is_array($share)) {
            return null;
        }

        $expiresAt = (string) ($share['expires_at'] ?? '');
        if (! $this->isShareActiveByDate($expiresAt)) {
            return null;
        }

        $activityId = (int) ($share['activity_id'] ?? 0);
        if ($activityId <= 0) {
            return null;
        }

        $activity = $activityModel->find($activityId);
        if (! is_array($activity)) {
            return null;
        }

        $photos = $photoModel
            ->where('activity_id', $activityId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        return [
            'share' => $share,
            'activity' => $activity,
            'photos' => $photos,
        ];
    }

    private function buildActivityZip(int $activityId, string $activityTitle): array
    {
        if ($activityId <= 0) {
            return [
                'path' => null,
                'name' => null,
                'error' => 'Kegiatan tidak valid.',
            ];
        }

        $photoModel = new KegiatanLapanganPhotoModel();
        $photos = $photoModel
            ->where('activity_id', $activityId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        if ($photos === []) {
            return [
                'path' => null,
                'name' => null,
                'error' => 'Tidak ada foto untuk diunduh.',
            ];
        }

        if (! class_exists('ZipArchive')) {
            return [
                'path' => null,
                'name' => null,
                'error' => 'Ekstensi ZIP tidak tersedia di server.',
            ];
        }

        $downloadDir = WRITEPATH . 'downloads';
        if (! is_dir($downloadDir) && ! @mkdir($downloadDir, 0775, true) && ! is_dir($downloadDir)) {
            return [
                'path' => null,
                'name' => null,
                'error' => 'Folder sementara unduhan tidak dapat dibuat.',
            ];
        }

        try {
            $randomSuffix = bin2hex(random_bytes(4));
        } catch (\Throwable $e) {
            $randomSuffix = (string) mt_rand(1000, 9999);
        }

        $zipTempPath = $downloadDir . DIRECTORY_SEPARATOR . 'kegiatan_lapangan_' . $activityId . '_' . date('YmdHis') . '_' . $randomSuffix . '.zip';

        $zip = new \ZipArchive();
        $openResult = $zip->open($zipTempPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        if ($openResult !== true) {
            return [
                'path' => null,
                'name' => null,
                'error' => 'Gagal membuat file ZIP.',
            ];
        }

        $addedFiles = 0;
        $usedNames = [];
        foreach ($photos as $index => $photo) {
            $photoPath = (string) ($photo['photo_path'] ?? '');
            if ($photoPath === '' || strpos($photoPath, '/uploads/') !== 0) {
                continue;
            }

            $absolutePath = FCPATH . ltrim($photoPath, '/');
            if (! is_file($absolutePath)) {
                continue;
            }

            $originalName = trim((string) ($photo['photo_name'] ?? ''));
            $baseName = $originalName !== '' ? $originalName : basename($absolutePath);
            $baseName = preg_replace('/[^A-Za-z0-9._-]/', '_', $baseName) ?: ('foto_' . ($index + 1));

            $zipEntryName = $baseName;
            $counter = 1;
            while (isset($usedNames[$zipEntryName])) {
                $ext = pathinfo($baseName, PATHINFO_EXTENSION);
                $nameOnly = pathinfo($baseName, PATHINFO_FILENAME);
                $zipEntryName = $nameOnly . '_' . $counter . ($ext !== '' ? '.' . $ext : '');
                $counter++;
            }

            $usedNames[$zipEntryName] = true;

            if ($zip->addFile($absolutePath, $zipEntryName)) {
                $addedFiles++;
            }
        }

        $zip->close();

        if ($addedFiles === 0) {
            @unlink($zipTempPath);
            return [
                'path' => null,
                'name' => null,
                'error' => 'Tidak ada file foto valid yang dapat dimasukkan ke ZIP.',
            ];
        }

        $safeTitle = strtolower(trim($activityTitle !== '' ? $activityTitle : 'kegiatan-lapangan'));
        $safeTitle = preg_replace('/[^A-Za-z0-9]+/', '-', $safeTitle) ?: 'kegiatan-lapangan';

        return [
            'path' => $zipTempPath,
            'name' => 'dokumentasi-' . $safeTitle . '-' . date('Ymd') . '.zip',
            'error' => null,
        ];
    }

    private function saveData(?int $id = null, ?array $existing = null)
    {
        $isAjax = $this->request->isAJAX();
        $activityTitle = trim((string) $this->request->getPost('title'));
        $activityDate = trim((string) $this->request->getPost('activity_date'));
        $location = trim((string) $this->request->getPost('location'));

        $validationErrors = [];
        if ($activityTitle === '') {
            $validationErrors['title'] = 'Judul kegiatan wajib diisi.';
        } elseif (mb_strlen($activityTitle) < 3) {
            $validationErrors['title'] = 'Judul kegiatan minimal 3 karakter.';
        }

        if ($location === '') {
            $validationErrors['location'] = 'Lokasi kegiatan wajib diisi.';
        } elseif (mb_strlen($location) < 3) {
            $validationErrors['location'] = 'Lokasi kegiatan minimal 3 karakter.';
        }

        if ($validationErrors !== []) {
            $message = array_values($validationErrors)[0];
            return $isAjax
                ? $this->response->setStatusCode(422)->setJSON([
                    'status' => 'error',
                    'message' => $message,
                    'errors' => $validationErrors,
                ])
                : redirect()->back()->withInput()->with('error', $message);
        }

        if ($activityDate !== '' && ! $this->validateDate($activityDate)) {
            return $isAjax
                ? $this->response->setStatusCode(422)->setJSON([
                    'status' => 'error',
                    'message' => 'Tanggal kegiatan tidak valid.',
                ])
                : redirect()->back()->withInput()->with('error', 'Tanggal kegiatan tidak valid.');
        }

        $activityModel = new KegiatanLapanganModel();
        $photoModel = new KegiatanLapanganPhotoModel();

        $creatorName = trim((string) (session()->get('fullName') ?: session()->get('username') ?: session()->get('name') ?: 'system'));
        $creatorUserId = (int) session()->get('userId');

        $payload = [
            'title' => $activityTitle,
            'activity_date' => $activityDate !== '' ? $activityDate : null,
            'location' => $location,
            'created_by' => $creatorName,
            'created_by_user_id' => $creatorUserId > 0 ? $creatorUserId : null,
        ];

        if ($id === null) {
            $activityModel->insert($payload);
            $newId = (int) $activityModel->getInsertID();

            $uploadedPhotos = $this->processUploadedPhotos('activity_photos', 0, $newId, $activityTitle);
            if ($uploadedPhotos['error'] !== null) {
                $this->cleanupUploadedPhotos($uploadedPhotos['photos'] ?? []);
                $activityModel->delete($newId);

                return $isAjax
                    ? $this->response->setStatusCode(422)->setJSON([
                        'status' => 'error',
                        'message' => $uploadedPhotos['error'],
                    ])
                    : redirect()->back()->withInput()->with('error', $uploadedPhotos['error']);
            }

            if (($uploadedPhotos['photos'] ?? []) === []) {
                $activityModel->delete($newId);
                return $isAjax
                    ? $this->response->setStatusCode(422)->setJSON([
                        'status' => 'error',
                        'message' => 'Minimal satu foto kegiatan harus dipilih.',
                    ])
                    : redirect()->back()->withInput()->with('error', 'Minimal satu foto kegiatan harus dipilih.');
            }

            $this->storeActivityPhotos($newId, $uploadedPhotos['photos'], 1);

            return $isAjax
                ? $this->response->setJSON([
                    'status' => 'ok',
                    'message' => 'Kegiatan lapangan berhasil ditambahkan.',
                    'redirect' => site_url('/admin/dokumentasi/kegiatan-lapangan'),
                ])
                : redirect()->to('/admin/dokumentasi/kegiatan-lapangan')->with('message', 'Kegiatan lapangan berhasil ditambahkan.');
        }

        $existingPhotoRows = $photoModel
            ->where('activity_id', $id)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        $uploadedPhotos = $this->processUploadedPhotos('activity_photos', count($existingPhotoRows), $id, $activityTitle);
        if ($uploadedPhotos['error'] !== null) {
            $this->cleanupUploadedPhotos($uploadedPhotos['photos'] ?? []);

            return $isAjax
                ? $this->response->setStatusCode(422)->setJSON([
                    'status' => 'error',
                    'message' => $uploadedPhotos['error'],
                ])
                : redirect()->back()->withInput()->with('error', $uploadedPhotos['error']);
        }

        if ($existingPhotoRows === [] && ($uploadedPhotos['photos'] ?? []) === []) {
            return $isAjax
                ? $this->response->setStatusCode(422)->setJSON([
                    'status' => 'error',
                    'message' => 'Minimal satu foto kegiatan harus dipilih.',
                ])
                : redirect()->back()->withInput()->with('error', 'Minimal satu foto kegiatan harus dipilih.');
        }

        $activityModel->update($id, $payload);
        $this->storeActivityPhotos($id, $uploadedPhotos['photos'], count($existingPhotoRows) + 1);

        return $isAjax
            ? $this->response->setJSON([
                'status' => 'ok',
                'message' => 'Kegiatan lapangan berhasil diperbarui.',
                'redirect' => site_url('/admin/dokumentasi/kegiatan-lapangan'),
            ])
            : redirect()->to('/admin/dokumentasi/kegiatan-lapangan')->with('message', 'Kegiatan lapangan berhasil diperbarui.');
    }

    private function processUploadedPhotos(string $fieldName, int $existingPhotoCount = 0, int $activityId = 0, ?string $activityTitle = null): array
    {
        $files = $this->request->getFileMultiple($fieldName);
        if (! is_array($files) || $files === []) {
            $files = $this->request->getFileMultiple($fieldName . '[]');
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

        $flatFiles = array_values(array_filter($flatFiles, static fn ($file): bool => $file !== null));

        if ($flatFiles === []) {
            return [
                'photos' => [],
                'error' => null,
            ];
        }

        $remainingSlots = self::MAX_PHOTOS - $existingPhotoCount;
        if ($remainingSlots <= 0) {
            return [
                'photos' => [],
                'error' => 'Maksimal 50 foto untuk satu kegiatan sudah tercapai. Hapus foto lama terlebih dahulu jika ingin mengganti.',
            ];
        }

        if (count($flatFiles) > $remainingSlots) {
            return [
                'photos' => [],
                'error' => 'Foto yang dipilih melebihi batas. Maksimal tersisa ' . $remainingSlots . ' foto lagi.',
            ];
        }

        if ($activityId <= 0) {
            return [
                'photos' => [],
                'error' => 'Kegiatan tidak valid untuk proses upload foto.',
            ];
        }

        $folderConfig = $this->resolveActivityUploadFolder($activityId, $activityTitle);
        $directory = $folderConfig['absolute'];
        $publicBasePath = $folderConfig['public'];

        if (! is_dir($directory) && ! @mkdir($directory, 0775, true) && ! is_dir($directory)) {
            return [
                'photos' => [],
                'error' => 'Folder upload tidak dapat dibuat di server.',
            ];
        }

        if (! is_writable($directory)) {
            return [
                'photos' => [],
                'error' => 'Folder upload tidak dapat ditulis. Periksa permission uploads/dokumentasi/kegiatan-lapangan.',
            ];
        }

        $result = [];
        $movedFiles = [];
        foreach ($flatFiles as $file) {
            if (! is_object($file) || ! method_exists($file, 'getError')) {
                continue;
            }

            if ($file->getError() === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if (! $file->isValid() || $file->hasMoved()) {
                $this->cleanupUploadedPhotos($movedFiles);
                return [
                    'photos' => $movedFiles,
                    'error' => 'Salah satu foto tidak valid. Silakan pilih ulang foto.',
                ];
            }

            $mimeType = strtolower((string) $file->getMimeType());
            if (! str_starts_with($mimeType, 'image/')) {
                $this->cleanupUploadedPhotos($movedFiles);
                return [
                    'photos' => $movedFiles,
                    'error' => 'Semua file harus berupa gambar.',
                ];
            }

            $extension = strtolower((string) $file->getClientExtension());
            if ($extension === '') {
                $extension = 'jpg';
            }

            if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $extension = 'jpg';
            }

            try {
                $newName = date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
            } catch (\Throwable $e) {
                $this->cleanupUploadedPhotos($movedFiles);
                return [
                    'photos' => $movedFiles,
                    'error' => 'Gagal membuat nama file foto.',
                ];
            }

            try {
                $file->move($directory, $newName);
            } catch (\Throwable $e) {
                $this->cleanupUploadedPhotos($movedFiles);
                return [
                    'photos' => $movedFiles,
                    'error' => 'Gagal menyimpan foto ke server.',
                ];
            }

            if (! is_file($directory . DIRECTORY_SEPARATOR . $newName)) {
                $this->cleanupUploadedPhotos($movedFiles);
                return [
                    'photos' => $movedFiles,
                    'error' => 'Foto tidak tersimpan dengan benar di server.',
                ];
            }

            $uploadedPhoto = [
                'photo_path' => $publicBasePath . '/' . $newName,
                'photo_name' => (string) ($file->getClientName() ?: $newName),
            ];

            $result[] = $uploadedPhoto;
            $movedFiles[] = $uploadedPhoto;
        }

        return [
            'photos' => $result,
            'error' => null,
        ];
    }

    private function resolveActivityUploadFolder(int $activityId, ?string $activityTitle = null): array
    {
        $year = date('Y');
        $month = date('m');
        $activityCode = 'kegiatan-' . str_pad((string) $activityId, 6, '0', STR_PAD_LEFT);
        $activitySlug = $this->slugifyFolderName($activityTitle ?: 'kegiatan-lapangan');

        $relative = 'uploads/dokumentasi/kegiatan-lapangan/' . $year . '/' . $month . '/' . $activityCode . '-' . $activitySlug;

        return [
            'absolute' => FCPATH . $relative,
            'public' => '/' . $relative,
        ];
    }

    private function slugifyFolderName(string $text): string
    {
        $slug = strtolower(trim($text));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        $slug = trim($slug, '-');

        if ($slug === '') {
            return 'kegiatan';
        }

        return substr($slug, 0, 48);
    }

    private function cleanupUploadedPhotos(array $photos): void
    {
        foreach ($photos as $photo) {
            $this->deleteLocalImage((string) ($photo['photo_path'] ?? ''));
        }
    }

    private function storeActivityPhotos(int $activityId, array $photos, int $startingOrder): void
    {
        if ($activityId <= 0 || $photos === []) {
            return;
        }

        $photoModel = new KegiatanLapanganPhotoModel();
        $order = $startingOrder;

        foreach ($photos as $photo) {
            $photoModel->insert([
                'activity_id' => $activityId,
                'photo_path' => (string) ($photo['photo_path'] ?? ''),
                'photo_name' => (string) ($photo['photo_name'] ?? ''),
                'sort_order' => $order++,
            ]);
        }
    }

    private function validateDate(string $date): bool
    {
        $dateTime = \DateTime::createFromFormat('Y-m-d', $date);
        return $dateTime instanceof \DateTime && $dateTime->format('Y-m-d') === $date;
    }

    private function extractDateOnly(string $dateTime): string
    {
        $trimmed = trim($dateTime);
        if ($trimmed === '') {
            return '';
        }

        $timestamp = strtotime($trimmed);
        if ($timestamp === false) {
            return '';
        }

        return date('Y-m-d', $timestamp);
    }

    private function isShareActiveByDate(string $expiresAt): bool
    {
        $dateOnly = $this->extractDateOnly($expiresAt);
        if ($dateOnly === '') {
            return true;
        }

        return $dateOnly >= date('Y-m-d');
    }

    private function deleteLocalImage(?string $path): void
    {
        if (empty($path) || strpos($path, '/uploads/') !== 0) {
            return;
        }

        $filePath = FCPATH . ltrim($path, '/');
        if (is_file($filePath)) {
            unlink($filePath);
        }
    }
}