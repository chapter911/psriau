<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\KegiatanLapanganModel;
use App\Models\KegiatanLapanganPhotoModel;
use App\Models\KegiatanLapanganShareModel;

class Dokumentasi extends BaseController
{
    private const MAX_PHOTOS = 200;

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

    public function watermarkFoto()
    {
        if (! $this->canAccessWatermarkMenu()) {
            return redirect()->to('/admin')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman Watermark Foto.');
        }

        $permissions = $this->getWatermarkPermissions();

        return view('admin/dokumentasi/watermark_foto', [
            'pageTitle'   => 'Watermark Foto (TimeMark Style)',
            'permissions' => $permissions,
        ]);
    }

    /**
     * AJAX endpoint untuk mengekstrak metadata EXIF (waktu asli & koordinat) dari foto secara instan.
     */
    public function extractMeta()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Permintaan tidak valid.',
            ]);
        }

        $foto = $this->request->getFile('foto');
        if ($foto === null || ! $foto->isValid()) {
            return $this->response->setJSON([
                'status'    => 'error',
                'has_exif'  => false,
                'message'   => 'File tidak valid atau tidak terbaca.',
                'csrf_token'=> csrf_token(),
                'csrf_hash' => csrf_hash(),
            ]);
        }

        $tempDir = ROOTPATH . 'do_not_upload/temp/meta_' . bin2hex(random_bytes(4));
        if (! is_dir($tempDir)) {
            @mkdir($tempDir, 0775, true);
        }

        $tempPath = $tempDir . DIRECTORY_SEPARATOR . 'sample.' . strtolower($foto->getClientExtension());
        try {
            $foto->move($tempDir, 'sample.' . strtolower($foto->getClientExtension()));

            $service = new \App\Libraries\TimeMarkWatermarkService();
            $metaPath = $tempPath;
            if ($service->isHeicFile($tempPath)) {
                $converted = $service->convertHeicToJpeg($tempPath);
                if ($converted !== null) {
                    $metaPath = $converted;
                }
            }

            // Baca EXIF dari file asli (termasuk HEIC) atau hasil konversi
            $exif = $service->readExifData($tempPath);
            if (! is_array($exif) || empty($exif)) {
                $exif = $service->readExifData($metaPath);
            }

            $hasExif = is_array($exif) && (! empty($exif['DateTimeOriginal']) || ! empty($exif['DateTime']));
            $metaDt = $service->resolveDateTime($exif, $metaPath, true, true, 0, 0);
            $coordsDms = is_array($exif) ? $service->extractGpsCoordinates($exif) : null;
            $coordsDec = is_array($exif) ? $service->extractGpsDecimal($exif) : null;
            $exifDesc = is_array($exif) && ! empty($exif['ImageDescription']) ? trim((string) $exif['ImageDescription']) : null;

            // Buat pratinjau thumbnail JPEG (maks 900px) agar Live Simulator di browser dapat langsung menampilkan foto,
            // termasuk untuk format iPhone HEIC yang dikonversi di sisi server.
            $previewDataUri = null;
            if (is_file($metaPath)) {
                $rawImg = @file_get_contents($metaPath);
                if ($rawImg !== false) {
                    $thumbRes = @imagecreatefromstring($rawImg);
                    if ($thumbRes !== false) {
                        $tw = imagesx($thumbRes);
                        $th = imagesy($thumbRes);
                        $maxDim = 900;
                        if ($tw > $maxDim || $th > $maxDim) {
                            $scale = min($maxDim / $tw, $maxDim / $th);
                            $nw = (int) max(1, $tw * $scale);
                            $nh = (int) max(1, $th * $scale);
                            $resized = imagecreatetruecolor($nw, $nh);
                            imagecopyresampled($resized, $thumbRes, 0, 0, 0, 0, $nw, $nh, $tw, $th);
                            imagedestroy($thumbRes);
                            $thumbRes = $resized;
                        }
                        ob_start();
                        imagejpeg($thumbRes, null, 78);
                        $previewDataUri = 'data:image/jpeg;base64,' . base64_encode(ob_get_clean());
                        imagedestroy($thumbRes);
                    }
                }
            }

            return $this->response->setJSON([
                'status'           => 'ok',
                'has_exif'         => $hasExif,
                'has_gps'          => ($coordsDec !== null || ($coordsDms !== null && $coordsDms !== '')),
                'date'             => $metaDt['raw_ymd'],
                'time'             => $metaDt['time'],
                'day'              => $metaDt['day'],
                'lat'              => $coordsDec['lat'] ?? null,
                'lng'              => $coordsDec['lng'] ?? null,
                'coordinates'      => $coordsDec ? ($coordsDec['lat'] . ', ' . $coordsDec['lng']) : ($coordsDms ?? ''),
                'formatted_coords' => $coordsDms ?? '',
                'description'      => $exifDesc,
                'preview_data_uri' => $previewDataUri,
                'csrf_token'       => csrf_token(),
                'csrf_hash'        => csrf_hash(),
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'status'    => 'error',
                'has_exif'  => false,
                'message'   => $e->getMessage(),
                'csrf_token'=> csrf_token(),
                'csrf_hash' => csrf_hash(),
            ]);
        } finally {
            $this->cleanUpDirectory($tempDir);
        }
    }

    /**
     * Memproses watermark foto (single image, multiple images, atau file ZIP).
     */
    public function prosesWatermark()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Permintaan tidak valid.',
            ]);
        }

        $sendError = function (int $code, string $message) {
            return $this->response->setStatusCode($code)->setJSON([
                'status'     => 'error',
                'message'    => $message,
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        };

        // 0. Tingkatkan batas waktu eksekusi dan memori untuk menangani pemrosesan batch foto berukuran besar
        @set_time_limit(600);
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', '600');

        // Deteksi jika ukuran request melebihi batas post_max_size PHP (di mana PHP mengosongkan $_POST dan $_FILES)
        $contentLength = (int) ($this->request->getServer('CONTENT_LENGTH') ?? 0);
        if ($contentLength > 0 && empty($_POST) && empty($_FILES)) {
            $postMaxSize = ini_get('post_max_size') ?: '100M';
            $mbSize = round($contentLength / (1024 * 1024), 1);
            return $sendError(413, "Ukuran berkas ({$mbSize} MB) melebihi batas upload maksimal server (post_max_size: {$postMaxSize}). Silakan unggah foto dalam batch yang lebih kecil (misal 20–30 foto per file ZIP) atau tingkatkan batas post_max_size di cPanel hosting.");
        }

        // 1. Cek Hak Akses menu_akses
        if (! $this->canAccessWatermarkMenu()) {
            return $sendError(403, 'Akses ditolak. Anda tidak memiliki izin untuk mengakses menu Watermark Foto.');
        }

        $perms = $this->getWatermarkPermissions();
        if (! $perms['add'] && ! $perms['edit']) {
            return $sendError(403, 'Akses ditolak. Role Anda tidak memiliki izin (FiturAdd/FiturEdit) untuk memproses watermark.');
        }

        // 2. Ambil Parameter Form
        $template = trim((string) $this->request->getPost('template'));
        if (! in_array($template, ['classic', 'table', 'badge', 'strip'], true)) {
            $template = 'classic';
        }

        $useExifDate = (int) ($this->request->getPost('use_exif_date') ?? 1) === 1;
        $useExifTime = (int) ($this->request->getPost('use_exif_time') ?? 1) === 1;
        $useExifLocation = (int) ($this->request->getPost('use_exif_location') ?? 1) === 1;
        $updateExif = (int) ($this->request->getPost('update_exif') ?? 1) === 1;
        $offsetHours = (int) ($this->request->getPost('offset_hours') ?? 0);
        $offsetMinutes = (int) ($this->request->getPost('offset_minutes') ?? 0);
        $manualDate = trim((string) $this->request->getPost('manual_date'));
        $manualTime = trim((string) $this->request->getPost('manual_time'));
        $lokasi = trim((string) $this->request->getPost('lokasi'));
        $koordinat = trim((string) $this->request->getPost('koordinat'));
        $catatan = trim((string) $this->request->getPost('catatan'));
        $groupByCoords = (int) ($this->request->getPost('group_by_coords') ?? 0) === 1;
        $clusterRadius = (float) ($this->request->getPost('cluster_radius') ?? 100.0);
        $fotoPaths = (array) ($this->request->getPost('foto_paths') ?? []);

        // Jika opsi ambil koordinat EXIF tidak aktif dan lokasi kosong, gunakan default
        if (! $useExifLocation && $lokasi === '' && $koordinat === '') {
            $lokasi = 'Kantor Balai PPS Riau';
        }

        // 3. Siapkan Direktori Kerja Sementara di do_not_upload/temp (Rule #3)
        $workId = bin2hex(random_bytes(6));
        $tempWorkingDir = ROOTPATH . 'do_not_upload/temp/watermark_' . $workId;
        if (! is_dir($tempWorkingDir) && ! @mkdir($tempWorkingDir, 0775, true) && ! is_dir($tempWorkingDir)) {
            return $sendError(500, 'Gagal membuat folder kerja sementara.');
        }

        $inputDir = $tempWorkingDir . DIRECTORY_SEPARATOR . 'input';
        $extractDir = $tempWorkingDir . DIRECTORY_SEPARATOR . 'extract';
        @mkdir($inputDir, 0775, true);
        @mkdir($extractDir, 0775, true);

        // 4. Periksa File Logo Opsional
        $customLogoPath = null;
        $logoFile = $this->request->getFile('logo');
        if ($logoFile !== null && $logoFile->isValid() && ! $logoFile->hasMoved()) {
            $logoExt = strtolower($logoFile->getClientExtension());
            if (in_array($logoExt, ['png', 'jpg', 'jpeg', 'webp'], true)) {
                $logoName = 'logo_' . $workId . '.' . $logoExt;
                $logoFile->move($tempWorkingDir, $logoName);
                $customLogoPath = $tempWorkingDir . DIRECTORY_SEPARATOR . $logoName;
            }
        }

        // 5. Kumpulkan File Gambar (Single, Multiple, atau ZIP)
        $imageFilesToProcess = [];

        try {
            // Cek jika ada file ZIP yang diupload
            $zipFile = $this->request->getFile('zip_file');
            $fotoSingle = $this->request->getFile('foto');
            $fotosMultiple = $this->request->getFileMultiple('fotos');

            $isZip = false;
            $zipToExtract = null;

            if ($zipFile !== null && $zipFile->isValid() && strtolower($zipFile->getClientExtension()) === 'zip') {
                $isZip = true;
                $zipToExtract = $zipFile;
            } elseif ($fotoSingle !== null && $fotoSingle->isValid() && strtolower($fotoSingle->getClientExtension()) === 'zip') {
                $isZip = true;
                $zipToExtract = $fotoSingle;
            }

            if ($isZip && $zipToExtract !== null) {
                // Periksa kapasitas hard disk tersisa sebelum mengekstrak berkas besar
                $freeSpace = @disk_free_space($tempWorkingDir);
                if ($freeSpace !== false && $freeSpace < 250 * 1024 * 1024) {
                    $freeMb = round($freeSpace / (1024 * 1024), 1);
                    return $sendError(507, "Ruang penyimpanan hard disk tersisa sangat sedikit ({$freeMb} MB). Sistem membutuhkan minimal 250–500 MB ruang kosong untuk mengekstrak dan memproses foto. Silakan kosongkan penyimpanan disk (misal kosongkan Trash atau berkas sementara) terlebih dahulu.");
                }

                // Ekstraksi ZIP
                $zipPath = $tempWorkingDir . DIRECTORY_SEPARATOR . 'source.zip';
                $zipToExtract->move($tempWorkingDir, 'source.zip');

                $zip = new \ZipArchive();
                if ($zip->open($zipPath) === true) {
                    $zip->extractTo($extractDir);
                    $zip->close();
                    // Hapus source.zip segera setelah diekstrak untuk menghemat ruang disk (Rule #3)
                    @unlink($zipPath);
                } else {
                    return $sendError(422, 'Gagal membuka file ZIP. Pastikan file arsip ZIP tidak rusak.');
                }

                // Cari semua file gambar dalam hasil ekstrak (abaikan __MACOSX dan .DS_Store)
                $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($extractDir, \FilesystemIterator::SKIP_DOTS));
                foreach ($iterator as $item) {
                    if ($item->isFile()) {
                        $filePath = $item->getPathname();
                        if (strpos($filePath, '__MACOSX') !== false || strpos($item->getFilename(), '._') === 0 || $item->getFilename() === '.DS_Store') {
                            continue;
                        }
                        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif'], true)) {
                            // Ekstrak relative path dari folder induk ZIP untuk mempertahankan sub-sub folder asli
                            $rel = substr($filePath, strlen($extractDir));
                            $rel = str_replace('\\', '/', $rel);
                            $rel = ltrim($rel, '/');
                            $subDir = dirname($rel);
                            if ($subDir === '.' || $subDir === '/') {
                                $subDir = null;
                            }

                            $imageFilesToProcess[] = [
                                'path'          => $filePath,
                                'original_name' => $item->getFilename(),
                                'sub_dir'       => $subDir,
                                'relative_path' => $rel,
                            ];
                        }
                    }
                }
            } elseif (! empty($fotosMultiple)) {
                // Multiple Files Upload
                $idx = 0;
                foreach ($fotosMultiple as $multiFile) {
                    if ($multiFile->isValid() && ! $multiFile->hasMoved()) {
                        $ext = strtolower($multiFile->getClientExtension());
                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif'], true)) {
                            $name = 'photo_' . (++$idx) . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
                            $multiFile->move($inputDir, $name);

                            // Periksa jika ada path relatif dari pemilihan folder (webkitRelativePath / dropzone)
                            $subDir = null;
                            $relPath = $multiFile->getClientName();
                            if (! empty($fotoPaths[$idx - 1])) {
                                $customRel = str_replace('\\', '/', trim((string) $fotoPaths[$idx - 1]));
                                $customRel = ltrim($customRel, '/');
                                $d = dirname($customRel);
                                if ($d !== '.' && $d !== '/') {
                                    $subDir = $d;
                                    $relPath = $customRel;
                                }
                            }

                            $imageFilesToProcess[] = [
                                'path'          => $inputDir . DIRECTORY_SEPARATOR . $name,
                                'original_name' => $multiFile->getClientName(),
                                'sub_dir'       => $subDir,
                                'relative_path' => $relPath,
                            ];
                        }
                    }
                }
            } elseif ($fotoSingle !== null && $fotoSingle->isValid() && ! $fotoSingle->hasMoved()) {
                // Single File Upload
                $ext = strtolower($fotoSingle->getClientExtension());
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif'], true)) {
                    $name = 'single_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    $fotoSingle->move($inputDir, $name);

                    $subDir = null;
                    $singleRelPath = trim((string) ($this->request->getPost('foto_path') ?? ''));
                    if ($singleRelPath !== '') {
                        $customRel = str_replace('\\', '/', $singleRelPath);
                        $customRel = ltrim($customRel, '/');
                        $d = dirname($customRel);
                        if ($d !== '.' && $d !== '/') {
                            $subDir = $d;
                        }
                    }

                    $imageFilesToProcess[] = [
                        'path'          => $inputDir . DIRECTORY_SEPARATOR . $name,
                        'original_name' => $fotoSingle->getClientName(),
                        'sub_dir'       => $subDir,
                        'relative_path' => $singleRelPath !== '' ? $singleRelPath : $fotoSingle->getClientName(),
                    ];
                }
            }

            if (empty($imageFilesToProcess)) {
                return $sendError(422, 'Tidak ada foto valid yang ditemukan. Format yang didukung: JPG, PNG, WebP, HEIC/HEIF, atau ZIP berisi gambar.');
            }

            // 6. Proses Watermark untuk Setiap Gambar Menggunakan TimeMarkWatermarkService
            $watermarkService = new \App\Libraries\TimeMarkWatermarkService();
            $clustersSummary = [];

            // Jika fitur pengelompokan berdasarkan koordinat GPS diaktifkan
            if ($groupByCoords && ! empty($imageFilesToProcess)) {
                $clusteringResult = $watermarkService->clusterImagesByCoordinates(
                    $imageFilesToProcess,
                    $clusterRadius,
                    $useExifLocation,
                    $koordinat !== '' ? $koordinat : null
                );
                $imageFilesToProcess = $clusteringResult['items'];
                $clustersSummary = $clusteringResult['clusters'];
            }

            $processedResults = [];
            $totalImages = count($imageFilesToProcess);
            $isBatchMode = ($totalImages > 1 || $isZip || $groupByCoords);
            $lastFailError = null;

            $outputTempDir = $tempWorkingDir . DIRECTORY_SEPARATOR . 'output';
            @mkdir($outputTempDir, 0775, true);

            $usedFileNames = [];

            foreach ($imageFilesToProcess as $index => $item) {
                $sourceImgPath = $item['path'];
                $origName = $item['original_name'];
                $subDir = $item['sub_dir'] ?? null;
                $baseNameWithoutExt = pathinfo($origName, PATHINFO_FILENAME);
                $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

                $targetExt = in_array($ext, ['png', 'jpg', 'jpeg', 'webp'], true) ? $ext : 'jpg';
                $cleanBaseName = preg_replace('/^watermark[_\-\s]*/i', '', $baseNameWithoutExt);
                $candidateName = ($cleanBaseName !== '' ? $cleanBaseName : 'foto') . '.' . $targetExt;

                // Cegah tabrakan nama jika ada foto dengan nama yang sama di subfolder yang sama
                $pathKey = ($subDir !== null && $subDir !== '') ? ($subDir . '/' . $candidateName) : $candidateName;
                if (isset($usedFileNames[$pathKey])) {
                    $usedFileNames[$pathKey]++;
                    $outFileName = ($cleanBaseName !== '' ? $cleanBaseName : 'foto') . '_' . $usedFileNames[$pathKey] . '.' . $targetExt;
                } else {
                    $usedFileNames[$pathKey] = 1;
                    $outFileName = $candidateName;
                }

                // Tentukan lokasi file output sementara dan struktur path di dalam arsip ZIP
                if ($subDir !== null && $subDir !== '') {
                    $targetOutputDir = $outputTempDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $subDir);
                    if (! is_dir($targetOutputDir)) {
                        @mkdir($targetOutputDir, 0775, true);
                    }
                    $outTempPath = $targetOutputDir . DIRECTORY_SEPARATOR . $outFileName;
                    $relativeZipPath = $subDir . '/' . $outFileName;
                } else {
                    $outTempPath = $outputTempDir . DIRECTORY_SEPARATOR . $outFileName;
                    $relativeZipPath = $outFileName;
                }

                $needFullUri = (! $isBatchMode && $totalImages === 1);

                $processOptions = [
                    'template'          => $template,
                    'logo_path'         => $customLogoPath,
                    'manual_date'       => $manualDate,
                    'manual_time'       => $manualTime,
                    'offset_hours'      => $offsetHours,
                    'offset_minutes'    => $offsetMinutes,
                    'use_exif_date'     => $useExifDate,
                    'use_exif_time'     => $useExifTime,
                    'use_exif_location' => $useExifLocation,
                    'update_exif'       => $updateExif,
                    'location'          => $lokasi,
                    'coordinates'       => $koordinat !== '' ? $koordinat : null,
                    'notes'             => $catatan !== '' ? $catatan : null,
                    'save_to_path'      => $outTempPath,
                    'output_quality'    => 92,
                    'need_full_uri'     => $needFullUri,
                    'generate_thumb'    => true,
                    'thumb_width'       => 360,
                    'thumb_quality'     => 68,
                ];

                $res = $watermarkService->processImage($sourceImgPath, $processOptions);

                // Jika berkas berasal dari ekstrak ZIP sementara, hapus source file yang sudah selesai diproses
                // untuk menghemat ruang disk seketika (Rule #3)
                if ($isZip && is_file($sourceImgPath)) {
                    @unlink($sourceImgPath);
                }

                if (! $res['success']) {
                    $lastFailError = $res['error'] ?? ('Gagal memproses foto ' . $origName);
                    continue;
                }

                $processedResults[] = [
                    'file_name'        => $outFileName,
                    'relative_zip_path'=> $relativeZipPath,
                    'sub_dir'          => $subDir,
                    'cluster_id'       => $item['cluster_id'] ?? null,
                    'cluster_name'     => $item['cluster_name'] ?? null,
                    'orig_name'        => $origName,
                    'data_uri'         => $res['thumb_data_uri'] ?? $res['data_uri'], // Thumbnail responsif (~8 KB) selalu disertakan untuk galeri visual instan
                    'full_uri'         => $res['data_uri'] ?? null,
                    'meta'             => $res['meta'],
                    'temp_path'        => $outTempPath,
                ];

                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
            }

            if (empty($processedResults)) {
                $errMsg = $lastFailError ?? 'Gagal memproses watermark pada foto-foto yang diunggah.';
                return $sendError(422, $errMsg);
            }

            // 8. Buat ZIP Download untuk Batch Mode (Mempertahankan Struktur Subfolder Asli & Pengelompokan Lokasi)
            $zipDownloadUrl = null;
            $batchToken = null;
            if ($isBatchMode || count($processedResults) > 1) {
                $batchToken = date('YmdHis') . '_' . bin2hex(random_bytes(4));
                $downloadsDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'watermark_hasil' . DIRECTORY_SEPARATOR . 'downloads';
                if (! is_dir($downloadsDir)) {
                    @mkdir($downloadsDir, 0775, true);
                }

                $zipOutPath = $downloadsDir . DIRECTORY_SEPARATOR . 'watermark_batch_' . $batchToken . '.zip';
                $zipBatch = new \ZipArchive();
                if ($zipBatch->open($zipOutPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                    foreach ($processedResults as $pRes) {
                        if (is_file($pRes['temp_path'])) {
                            $zipEntryName = $pRes['relative_zip_path'] ?? $pRes['file_name'];
                            $zipBatch->addFile($pRes['temp_path'], $zipEntryName);
                        }
                    }
                    $zipBatch->close();
                    $zipDownloadUrl = site_url('/admin/dokumentasi/watermark-foto/download-batch-zip/' . $batchToken);
                }
            }

            // Single Preview Data
            $singleImageData = null;
            if (! $isBatchMode && isset($processedResults[0])) {
                $singleImageData = $processedResults[0]['full_uri'] ?? $processedResults[0]['data_uri'];
            }

            return $this->response->setJSON([
                'status'           => 'ok',
                'message'          => count($processedResults) . ' foto berhasil diberi watermark ala TimeMark!' . ($groupByCoords ? ' (Dikelompokkan ke ' . count($clustersSummary) . ' folder lokasi)' : ''),
                'mode'             => $isBatchMode ? 'batch' : 'single',
                'total_processed'  => count($processedResults),
                'is_grouped'       => $groupByCoords,
                'cluster_radius'   => $clusterRadius,
                'clusters'         => $clustersSummary,
                'image_data'       => $singleImageData,
                'download_zip_url' => $zipDownloadUrl,
                'images'           => array_map(static fn ($r) => [
                    'file_name'    => $r['file_name'],
                    'relative_path'=> $r['relative_zip_path'] ?? $r['file_name'],
                    'sub_dir'      => $r['sub_dir'] ?? null,
                    'cluster_id'   => $r['cluster_id'] ?? null,
                    'cluster_name' => $r['cluster_name'] ?? null,
                    'orig_name'    => $r['orig_name'],
                    'data_uri'     => $r['data_uri'],
                    'view_url'     => $batchToken ? site_url('/admin/dokumentasi/watermark-foto/download-batch-item/' . $batchToken . '?entry=' . rawurlencode($r['relative_zip_path'] ?? $r['file_name'])) : null,
                    'download_url' => $batchToken ? site_url('/admin/dokumentasi/watermark-foto/download-batch-item/' . $batchToken . '?download=1&entry=' . rawurlencode($r['relative_zip_path'] ?? $r['file_name'])) : null,
                    'date'         => $r['meta']['datetime']['date'] ?? '',
                    'time'         => $r['meta']['datetime']['time'] ?? '',
                    'day'          => $r['meta']['datetime']['day'] ?? '',
                ], $processedResults),
                'csrf_token'       => csrf_token(),
                'csrf_hash'        => csrf_hash(),
            ]);
        } catch (\Throwable $e) {
            return $sendError(500, 'Terjadi kendala saat memproses watermark: ' . $e->getMessage());
        } finally {
            // Bersihkan semua folder kerja sementara di do_not_upload/temp (Rule #3)
            $this->cleanUpDirectory($tempWorkingDir);
        }
    }

    /**
     * Download hasil pemrosesan foto dalam bentuk ZIP.
     */
    public function downloadBatchZip(string $token)
    {
        if (! $this->canAccessWatermarkMenu()) {
            return redirect()->to('/admin/dashboard')->with('error', 'Akses ditolak (Rule #4).');
        }

        if (! preg_match('/^[a-zA-Z0-9_]+$/', $token)) {
            return redirect()->to('/admin/dokumentasi/watermark-foto')->with('error', 'Token download tidak valid.');
        }

        $zipPath = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'watermark_hasil' . DIRECTORY_SEPARATOR . 'downloads' . DIRECTORY_SEPARATOR . 'watermark_batch_' . $token . '.zip';
        if (! is_file($zipPath) || ! is_readable($zipPath)) {
            return redirect()->to('/admin/dokumentasi/watermark-foto')->with('error', 'File download ZIP sudah kedaluwarsa atau tidak ditemukan.');
        }

        $downloadName = 'foto-timemark-' . date('YmdHis') . '.zip';
        return $this->response->download($zipPath, null)->setFileName($downloadName);
    }

    /**
     * Preview atau download satu berkas foto spesifik dari arsip batch ZIP (mendukung subfolder).
     */
    public function downloadBatchItem(string $token, string $fileName = '')
    {
        if (! $this->canAccessWatermarkMenu()) {
            return redirect()->to('/admin/dashboard')->with('error', 'Akses ditolak (Rule #4).');
        }

        if (! preg_match('/^[a-zA-Z0-9_]+$/', $token)) {
            return redirect()->to('/admin/dokumentasi/watermark-foto')->with('error', 'Token download tidak valid.');
        }

        $zipPath = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'watermark_hasil' . DIRECTORY_SEPARATOR . 'downloads' . DIRECTORY_SEPARATOR . 'watermark_batch_' . $token . '.zip';
        if (! is_file($zipPath) || ! is_readable($zipPath)) {
            return redirect()->to('/admin/dokumentasi/watermark-foto')->with('error', 'Arsip foto sudah kedaluwarsa atau tidak ditemukan.');
        }

        $rawEntry = (string) ($this->request->getGet('entry') ?? ($fileName !== '' ? $fileName : $this->request->getGet('file')));
        $rawEntry = str_replace('\\', '/', rawurldecode($rawEntry));
        $parts = array_filter(explode('/', $rawEntry), static fn ($p) => $p !== '' && $p !== '.' && $p !== '..');
        $cleanEntry = implode('/', $parts);

        if ($cleanEntry === '') {
            return redirect()->to('/admin/dokumentasi/watermark-foto')->with('error', 'Nama berkas tidak valid.');
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipPath) === true) {
            $content = $zip->getFromName($cleanEntry);

            // Fallback: cari berdasarkan basename jika path relatif berubah
            if ($content === false) {
                $base = basename($cleanEntry);
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $stat = $zip->statIndex($i);
                    if ($stat && basename($stat['name']) === $base) {
                        $content = $zip->getFromIndex($i);
                        $cleanEntry = $stat['name'];
                        break;
                    }
                }
            }

            $zip->close();

            if ($content !== false) {
                $ext = strtolower(pathinfo($cleanEntry, PATHINFO_EXTENSION));
                $contentType = match ($ext) {
                    'png' => 'image/png',
                    'webp' => 'image/webp',
                    default => 'image/jpeg',
                };

                $downloadFileName = basename($cleanEntry);
                $disposition = ($this->request->getGet('download') === '1') ? 'attachment' : 'inline';

                return $this->response
                    ->setHeader('Content-Type', $contentType)
                    ->setHeader('Content-Disposition', $disposition . '; filename="' . $downloadFileName . '"')
                    ->setHeader('Cache-Control', 'private, max-age=86400')
                    ->setBody($content);
            }
        }

        return redirect()->to('/admin/dokumentasi/watermark-foto')->with('error', 'Foto tidak ditemukan di dalam arsip.');
    }

    /**
     * Memproses foto secara bertahap / parsial (chunked processing) untuk menu Watermark Foto.
     * Mencegah timeout, memory limit, dan connection reset saat memproses puluhan/ratusan foto.
     */
    public function prosesWatermarkChunk()
    {
        $sendError = function (int $statusCode, string $message, ?string $csrfHash = null) {
            return $this->response->setStatusCode($statusCode)->setJSON([
                'status'     => 'error',
                'message'    => $message,
                'csrf_token' => csrf_token(),
                'csrf_hash'  => $csrfHash ?? csrf_hash(),
            ]);
        };

        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        if (! $this->canAccessWatermarkMenu()) {
            return $sendError(403, 'Akses ditolak. Anda tidak memiliki izin untuk mengakses menu Watermark Foto.');
        }

        $perms = $this->getWatermarkPermissions();
        if (! $perms['add'] && ! $perms['edit']) {
            return $sendError(403, 'Akses ditolak. Role Anda tidak memiliki izin untuk memproses foto.');
        }

        $batchId = trim((string) $this->request->getPost('batch_id'));
        if (! preg_match('/^[a-zA-Z0-9_\-]+$/', $batchId)) {
            return $sendError(422, 'ID Batch pemrosesan tidak valid.');
        }

        $chunkIndex = (int) ($this->request->getPost('chunk_index') ?? 0);
        $totalChunks = (int) ($this->request->getPost('total_chunks') ?? 1);
        $isLastChunk = (int) ($this->request->getPost('is_last_chunk') ?? 0) === 1;

        // Parameter Form
        $template = trim((string) $this->request->getPost('template'));
        if (! in_array($template, ['classic', 'table', 'badge', 'strip'], true)) {
            $template = 'classic';
        }

        $useExifDate = (int) ($this->request->getPost('use_exif_date') ?? 1) === 1;
        $useExifTime = (int) ($this->request->getPost('use_exif_time') ?? 1) === 1;
        $useExifLocation = (int) ($this->request->getPost('use_exif_location') ?? 1) === 1;
        $updateExif = (int) ($this->request->getPost('update_exif') ?? 1) === 1;
        $groupByCoords = (int) ($this->request->getPost('group_by_coords') ?? 0) === 1;
        $clusterRadius = (float) ($this->request->getPost('cluster_radius') ?? 100.0);
        $offsetHours = (int) ($this->request->getPost('offset_hours') ?? 0);
        $offsetMinutes = (int) ($this->request->getPost('offset_minutes') ?? 0);
        $manualDate = trim((string) $this->request->getPost('manual_date'));
        $manualTime = trim((string) $this->request->getPost('manual_time'));
        $lokasi = trim((string) $this->request->getPost('lokasi'));
        $koordinat = trim((string) $this->request->getPost('koordinat'));
        $catatan = trim((string) $this->request->getPost('catatan'));
        $fotoPaths = (array) ($this->request->getPost('foto_paths') ?? []);

        if (! $useExifLocation && $lokasi === '' && $koordinat === '') {
            $lokasi = 'Kantor Balai PPS Riau';
        }

        $tempWorkingDir = ROOTPATH . 'do_not_upload/temp/watermark_' . $batchId;
        $inputDir = $tempWorkingDir . DIRECTORY_SEPARATOR . 'input';
        $outputTempDir = $tempWorkingDir . DIRECTORY_SEPARATOR . 'output';
        $manifestPath = $tempWorkingDir . DIRECTORY_SEPARATOR . 'manifest.json';

        if (! is_dir($tempWorkingDir)) {
            @mkdir($tempWorkingDir, 0775, true);
            @mkdir($inputDir, 0775, true);
            @mkdir($outputTempDir, 0775, true);
        }

        // Custom logo
        $logoFile = $this->request->getFile('logo');
        $customLogoPath = null;
        $existingLogo = $tempWorkingDir . DIRECTORY_SEPARATOR . 'custom_logo';
        if ($logoFile !== null && $logoFile->isValid() && ! $logoFile->hasMoved()) {
            $logoExt = strtolower($logoFile->getClientExtension());
            if (in_array($logoExt, ['png', 'jpg', 'jpeg', 'webp'], true)) {
                $logoFile->move($tempWorkingDir, 'custom_logo.' . $logoExt);
                $customLogoPath = $tempWorkingDir . DIRECTORY_SEPARATOR . 'custom_logo.' . $logoExt;
            }
        } elseif (is_file($existingLogo . '.png')) {
            $customLogoPath = $existingLogo . '.png';
        } elseif (is_file($existingLogo . '.jpg')) {
            $customLogoPath = $existingLogo . '.jpg';
        }

        // Read manifest
        $manifest = ['items' => []];
        if (is_file($manifestPath)) {
            $manifestRaw = @file_get_contents($manifestPath);
            if ($manifestRaw) {
                $decoded = json_decode($manifestRaw, true);
                if (is_array($decoded)) {
                    $manifest = $decoded;
                }
            }
        }

        // Process files in this chunk
        $fotosMultiple = $this->request->getFileMultiple('fotos');
        $fotoSingle = $this->request->getFile('foto');
        $filesToProcess = [];

        if (! empty($fotosMultiple)) {
            $fIdx = 0;
            foreach ($fotosMultiple as $mFile) {
                if ($mFile->isValid() && ! $mFile->hasMoved()) {
                    $ext = strtolower($mFile->getClientExtension());
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif'], true)) {
                        $sName = 'chunk_' . $chunkIndex . '_' . (++$fIdx) . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
                        $mFile->move($inputDir, $sName);

                        $subDir = null;
                        $relPath = $mFile->getClientName();
                        if (! empty($fotoPaths[$fIdx - 1])) {
                            $customRel = str_replace('\\', '/', trim((string) $fotoPaths[$fIdx - 1]));
                            $customRel = ltrim($customRel, '/');
                            $d = dirname($customRel);
                            if ($d !== '.' && $d !== '/') {
                                $subDir = $d;
                                $relPath = $customRel;
                            }
                        }

                        $filesToProcess[] = [
                            'path'          => $inputDir . DIRECTORY_SEPARATOR . $sName,
                            'original_name' => $mFile->getClientName(),
                            'sub_dir'       => $subDir,
                            'relative_path' => $relPath,
                        ];
                    }
                }
            }
        } elseif ($fotoSingle !== null && $fotoSingle->isValid() && ! $fotoSingle->hasMoved()) {
            $ext = strtolower($fotoSingle->getClientExtension());
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif'], true)) {
                $sName = 'single_chunk_' . $chunkIndex . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
                $fotoSingle->move($inputDir, $sName);
                $singleRel = trim((string) ($this->request->getPost('foto_path') ?? ''));
                $subDir = null;
                if ($singleRel !== '') {
                    $customRel = str_replace('\\', '/', $singleRel);
                    $customRel = ltrim($customRel, '/');
                    $d = dirname($customRel);
                    if ($d !== '.' && $d !== '/') {
                        $subDir = $d;
                    }
                }
                $filesToProcess[] = [
                    'path'          => $inputDir . DIRECTORY_SEPARATOR . $sName,
                    'original_name' => $fotoSingle->getClientName(),
                    'sub_dir'       => $subDir,
                    'relative_path' => $singleRel !== '' ? $singleRel : $fotoSingle->getClientName(),
                ];
            }
        }

        $watermarkService = new \App\Libraries\TimeMarkWatermarkService();

        foreach ($filesToProcess as $item) {
            $sourceImgPath = $item['path'];
            $origName = $item['original_name'];
            $baseNameWithoutExt = pathinfo($origName, PATHINFO_FILENAME);
            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            $targetExt = in_array($ext, ['png', 'jpg', 'jpeg', 'webp'], true) ? $ext : 'jpg';
            $cleanBaseName = preg_replace('/^watermark[_\-\s]*/i', '', $baseNameWithoutExt);
            $outFileName = ($cleanBaseName !== '' ? $cleanBaseName : 'foto') . '_' . bin2hex(random_bytes(3)) . '.' . $targetExt;
            $outTempPath = $outputTempDir . DIRECTORY_SEPARATOR . $outFileName;

            $processOptions = [
                'template'          => $template,
                'logo_path'         => $customLogoPath,
                'manual_date'       => $manualDate,
                'manual_time'       => $manualTime,
                'offset_hours'      => $offsetHours,
                'offset_minutes'    => $offsetMinutes,
                'use_exif_date'     => $useExifDate,
                'use_exif_time'     => $useExifTime,
                'use_exif_location' => $useExifLocation,
                'update_exif'       => $updateExif,
                'location'          => $lokasi,
                'coordinates'       => $koordinat !== '' ? $koordinat : null,
                'notes'             => $catatan !== '' ? $catatan : null,
                'save_to_path'      => $outTempPath,
                'output_quality'    => 92,
                'need_full_uri'     => false,
                'generate_thumb'    => true,
                'thumb_width'       => 360,
                'thumb_quality'     => 68,
            ];

            $res = $watermarkService->processImage($sourceImgPath, $processOptions);
            @unlink($sourceImgPath); // Immediate cleanup of input source file (Rule #3)

            if ($res['success']) {
                $manifest['items'][] = [
                    'file_name'        => $outFileName,
                    'orig_name'        => $origName,
                    'sub_dir'          => $item['sub_dir'],
                    'relative_path'    => $item['relative_path'],
                    'data_uri'         => $res['thumb_data_uri'] ?? $res['data_uri'],
                    'meta'             => $res['meta'],
                    'temp_path'        => $outTempPath,
                ];
            }
        }

        // Save updated manifest
        file_put_contents($manifestPath, json_encode($manifest));

        // If NOT last chunk, reply with progress
        if (! $isLastChunk) {
            return $this->response->setJSON([
                'status'           => 'ok',
                'is_finished'      => false,
                'chunk_index'      => $chunkIndex,
                'processed_count'  => count($manifest['items']),
                'total_chunks'     => $totalChunks,
                'csrf_token'       => csrf_token(),
                'csrf_hash'        => csrf_hash(),
            ]);
        }

        // --- FINAL STAGE (is_last_chunk = true) ---
        try {
            $allItems = $manifest['items'];
            if (empty($allItems)) {
                return $sendError(422, 'Tidak ada foto yang berhasil diproses.');
            }

            $clustersSummary = [];

            // Jika fitur pengelompokan koordinat GPS aktif:
            if ($groupByCoords) {
                $itemsForClustering = array_map(static fn ($it) => [
                    'path'          => $it['temp_path'],
                    'original_name' => $it['orig_name'],
                    'sub_dir'       => $it['sub_dir'],
                    'relative_path' => $it['relative_path'],
                    '_raw_item'     => $it,
                ], $allItems);

                $clusteringResult = $watermarkService->clusterImagesByCoordinates(
                    $itemsForClustering,
                    $clusterRadius,
                    $useExifLocation,
                    $koordinat !== '' ? $koordinat : null
                );

                $clustersSummary = $clusteringResult['clusters'];
                $newAllItems = [];

                foreach ($clusteringResult['items'] as $cItem) {
                    $raw = $cItem['_raw_item'];
                    $folderName = $cItem['sub_dir'];
                    $targetDir = $outputTempDir . DIRECTORY_SEPARATOR . $folderName;
                    if (! is_dir($targetDir)) {
                        @mkdir($targetDir, 0775, true);
                    }
                    $cleanTargetName = preg_replace('/^watermark[_\-\s]*/i', '', $raw['orig_name']);
                    $finalPath = $targetDir . DIRECTORY_SEPARATOR . $cleanTargetName;

                    if (is_file($raw['temp_path'])) {
                        @rename($raw['temp_path'], $finalPath);
                    }

                    $raw['temp_path'] = $finalPath;
                    $raw['sub_dir'] = $folderName;
                    $raw['relative_zip_path'] = $folderName . '/' . $cleanTargetName;
                    $raw['cluster_id'] = $cItem['cluster_id'];
                    $raw['cluster_name'] = $cItem['cluster_name'];
                    $newAllItems[] = $raw;
                }
                $allItems = $newAllItems;
            } else {
                foreach ($allItems as &$it) {
                    $cleanTargetName = preg_replace('/^watermark[_\-\s]*/i', '', $it['orig_name']);
                    $it['relative_zip_path'] = (! empty($it['sub_dir'])) ? ($it['sub_dir'] . '/' . $cleanTargetName) : $cleanTargetName;
                }
                unset($it);
            }

            // Create ZIP archive
            $batchToken = date('YmdHis') . '_' . bin2hex(random_bytes(4));
            $downloadsDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'watermark_hasil' . DIRECTORY_SEPARATOR . 'downloads';
            if (! is_dir($downloadsDir)) {
                @mkdir($downloadsDir, 0775, true);
            }

            $zipOutPath = $downloadsDir . DIRECTORY_SEPARATOR . 'watermark_batch_' . $batchToken . '.zip';
            $zipBatch = new \ZipArchive();
            if ($zipBatch->open($zipOutPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                foreach ($allItems as $pRes) {
                    if (is_file($pRes['temp_path'])) {
                        $zipEntryName = $pRes['relative_zip_path'] ?? $pRes['file_name'];
                        $zipBatch->addFile($pRes['temp_path'], $zipEntryName);
                    }
                }
                $zipBatch->close();
            }

            $zipDownloadUrl = site_url('/admin/dokumentasi/watermark-foto/download-batch-zip/' . $batchToken);

            return $this->response->setJSON([
                'status'           => 'ok',
                'is_finished'      => true,
                'message'          => count($allItems) . ' foto berhasil diproses secara bertahap!' . ($groupByCoords ? ' (' . count($clustersSummary) . ' folder lokasi)' : ''),
                'mode'             => 'batch',
                'total_processed'  => count($allItems),
                'is_grouped'       => $groupByCoords,
                'cluster_radius'   => $clusterRadius,
                'clusters'         => $clustersSummary,
                'download_zip_url' => $zipDownloadUrl,
                'images'           => array_map(static fn ($r) => [
                    'file_name'    => basename($r['temp_path']),
                    'relative_path'=> $r['relative_zip_path'] ?? basename($r['temp_path']),
                    'sub_dir'      => $r['sub_dir'] ?? null,
                    'cluster_id'   => $r['cluster_id'] ?? null,
                    'cluster_name' => $r['cluster_name'] ?? null,
                    'orig_name'    => $r['orig_name'],
                    'data_uri'     => $r['data_uri'],
                    'view_url'     => site_url('/admin/dokumentasi/watermark-foto/download-batch-item/' . $batchToken . '?entry=' . rawurlencode($r['relative_zip_path'] ?? basename($r['temp_path']))),
                    'download_url' => site_url('/admin/dokumentasi/watermark-foto/download-batch-item/' . $batchToken . '?download=1&entry=' . rawurlencode($r['relative_zip_path'] ?? basename($r['temp_path']))),
                    'date'         => $r['meta']['datetime']['date'] ?? '',
                    'time'         => $r['meta']['datetime']['time'] ?? '',
                    'day'          => $r['meta']['datetime']['day'] ?? '',
                ], $allItems),
                'csrf_token'       => csrf_token(),
                'csrf_hash'        => csrf_hash(),
            ]);
        } finally {
            $this->cleanUpDirectory($tempWorkingDir); // Rule #3 clean temporary files
        }
    }

    // =========================================================================
    // MODUL: ORGANISIR & PENGELOMPOKAN FOTO GPS (TANPA WATERMARK)
    // =========================================================================

    /**
     * Tampilan utama menu Organisir Foto GPS.
     */
    public function organisirFotoGps()
    {
        if (! $this->canAccessOrganisirMenu()) {
            return redirect()->to('/admin/dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengakses menu Organisir Foto GPS (Rule #4).');
        }

        $perms = $this->getOrganisirPermissions();

        $data = [
            'title'       => 'Organisir & Pengelompokan Foto GPS',
            'subtitle'    => 'Kelompokkan berkas foto kegiatan lapangan ke dalam sub-folder lokasi secara otomatis berdasarkan koordinat GPS kamera asli (100% Asli, Bersih & Tanpa Watermark)',
            'permissions' => $perms,
        ];

        return view('admin/dokumentasi/organisir_foto_gps', $data);
    }

    /**
     * Pemrosesan chunked / bertahap untuk Organisir Foto GPS (Lossless, Tanpa Watermark).
     */
    public function prosesOrganisirGpsChunk()
    {
        $sendError = function (int $statusCode, string $message, ?string $csrfHash = null) {
            return $this->response->setStatusCode($statusCode)->setJSON([
                'status'     => 'error',
                'message'    => $message,
                'csrf_token' => csrf_token(),
                'csrf_hash'  => $csrfHash ?? csrf_hash(),
            ]);
        };

        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        if (! $this->canAccessOrganisirMenu()) {
            return $sendError(403, 'Akses ditolak (Rule #4).');
        }

        $perms = $this->getOrganisirPermissions();
        if (! $perms['add'] && ! $perms['edit']) {
            return $sendError(403, 'Role Anda tidak memiliki izin untuk memproses foto.');
        }

        $batchId = trim((string) $this->request->getPost('batch_id'));
        if (! preg_match('/^[a-zA-Z0-9_\-]+$/', $batchId)) {
            return $sendError(422, 'ID Batch tidak valid.');
        }

        $chunkIndex = (int) ($this->request->getPost('chunk_index') ?? 0);
        $totalChunks = (int) ($this->request->getPost('total_chunks') ?? 1);
        $isLastChunk = (int) ($this->request->getPost('is_last_chunk') ?? 0) === 1;
        $clusterRadius = (float) ($this->request->getPost('cluster_radius') ?? 100.0);
        $fotoPaths = (array) ($this->request->getPost('foto_paths') ?? []);

        $tempWorkingDir = ROOTPATH . 'do_not_upload/temp/organisir_' . $batchId;
        $sourceDir = $tempWorkingDir . DIRECTORY_SEPARATOR . 'source';
        $manifestPath = $tempWorkingDir . DIRECTORY_SEPARATOR . 'manifest.json';

        if (! is_dir($tempWorkingDir)) {
            @mkdir($tempWorkingDir, 0775, true);
            @mkdir($sourceDir, 0775, true);
        }

        $manifest = ['items' => []];
        if (is_file($manifestPath)) {
            $raw = @file_get_contents($manifestPath);
            if ($raw) {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) $manifest = $decoded;
            }
        }

        $fotosMultiple = $this->request->getFileMultiple('fotos');
        $fotoSingle = $this->request->getFile('foto');
        $watermarkService = new \App\Libraries\TimeMarkWatermarkService();

        if (! empty($fotosMultiple)) {
            $fIdx = 0;
            foreach ($fotosMultiple as $mFile) {
                if ($mFile->isValid() && ! $mFile->hasMoved()) {
                    $ext = strtolower($mFile->getClientExtension());
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif'], true)) {
                        $safeName = 'org_' . $chunkIndex . '_' . (++$fIdx) . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
                        $mFile->move($sourceDir, $safeName);
                        $savedPath = $sourceDir . DIRECTORY_SEPARATOR . $safeName;

                        $relPath = $mFile->getClientName();
                        $subDir = null;
                        if (! empty($fotoPaths[$fIdx - 1])) {
                            $customRel = str_replace('\\', '/', trim((string) $fotoPaths[$fIdx - 1]));
                            $customRel = ltrim($customRel, '/');
                            $d = dirname($customRel);
                            if ($d !== '.' && $d !== '/') {
                                $subDir = $d;
                                $relPath = $customRel;
                            }
                        }

                        $manifest['items'][] = [
                            'path'          => $savedPath,
                            'original_name' => $mFile->getClientName(),
                            'sub_dir'       => $subDir,
                            'relative_path' => $relPath,
                        ];
                    }
                }
            }
        } elseif ($fotoSingle !== null && $fotoSingle->isValid() && ! $fotoSingle->hasMoved()) {
            $ext = strtolower($fotoSingle->getClientExtension());
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif'], true)) {
                $safeName = 'org_single_' . $chunkIndex . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
                $fotoSingle->move($sourceDir, $safeName);
                $savedPath = $sourceDir . DIRECTORY_SEPARATOR . $safeName;
                $singleRel = trim((string) ($this->request->getPost('foto_path') ?? ''));

                $manifest['items'][] = [
                    'path'          => $savedPath,
                    'original_name' => $fotoSingle->getClientName(),
                    'sub_dir'       => null,
                    'relative_path' => $singleRel !== '' ? $singleRel : $fotoSingle->getClientName(),
                ];
            }
        }

        file_put_contents($manifestPath, json_encode($manifest));

        if (! $isLastChunk) {
            return $this->response->setJSON([
                'status'          => 'ok',
                'is_finished'     => false,
                'chunk_index'     => $chunkIndex,
                'processed_count' => count($manifest['items']),
                'total_chunks'    => $totalChunks,
                'csrf_token'      => csrf_token(),
                'csrf_hash'       => csrf_hash(),
            ]);
        }

        // Final stage: Cluster by coordinates, copy files losslessly, and build ZIP
        try {
            $allItems = $manifest['items'];
            if (empty($allItems)) {
                return $sendError(422, 'Tidak ada foto valid yang ditemukan.');
            }

            $clusteringResult = $watermarkService->clusterImagesByCoordinates(
                $allItems,
                $clusterRadius,
                true,
                null
            );

            $outputDir = $tempWorkingDir . DIRECTORY_SEPARATOR . 'output';
            @mkdir($outputDir, 0775, true);

            $processedResults = [];
            $batchToken = date('YmdHis') . '_' . bin2hex(random_bytes(4));

            foreach ($clusteringResult['items'] as $cItem) {
                $clusterFolder = $cItem['sub_dir'];
                $targetClusterDir = $outputDir . DIRECTORY_SEPARATOR . $clusterFolder;
                if (! is_dir($targetClusterDir)) {
                    @mkdir($targetClusterDir, 0775, true);
                }

                $cleanBaseName = $cItem['original_name'];
                $destPath = $targetClusterDir . DIRECTORY_SEPARATOR . $cleanBaseName;

                // Handle collision if duplicate filename in same cluster
                if (is_file($destPath)) {
                    $ext = pathinfo($cleanBaseName, PATHINFO_EXTENSION);
                    $fn = pathinfo($cleanBaseName, PATHINFO_FILENAME);
                    $destPath = $targetClusterDir . DIRECTORY_SEPARATOR . $fn . '_' . bin2hex(random_bytes(2)) . '.' . $ext;
                    $cleanBaseName = basename($destPath);
                }

                // Copy losslessly (preserving 100% exact pixels and EXIF)
                if (is_file($cItem['path'])) {
                    @copy($cItem['path'], $destPath);
                    @unlink($cItem['path']); // free space immediately (Rule #3)
                }

                // Generate lightweight thumbnail for UI gallery
                $thumbDataUri = $watermarkService->generateThumbnailDataUri($destPath, 360, 68);

                $processedResults[] = [
                    'file_name'        => $cleanBaseName,
                    'relative_zip_path'=> $clusterFolder . '/' . $cleanBaseName,
                    'sub_dir'          => $clusterFolder,
                    'cluster_id'       => $cItem['cluster_id'],
                    'cluster_name'     => $cItem['cluster_name'],
                    'orig_name'        => $cItem['original_name'],
                    'data_uri'         => $thumbDataUri,
                    'temp_path'        => $destPath,
                ];
            }

            // Create ZIP
            $downloadsDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'organisir_hasil' . DIRECTORY_SEPARATOR . 'downloads';
            if (! is_dir($downloadsDir)) {
                @mkdir($downloadsDir, 0775, true);
            }

            $zipOutPath = $downloadsDir . DIRECTORY_SEPARATOR . 'organisir_batch_' . $batchToken . '.zip';
            $zipBatch = new \ZipArchive();
            if ($zipBatch->open($zipOutPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                foreach ($processedResults as $pRes) {
                    if (is_file($pRes['temp_path'])) {
                        $zipBatch->addFile($pRes['temp_path'], $pRes['relative_zip_path']);
                    }
                }
                $zipBatch->close();
            }

            $zipDownloadUrl = site_url('/admin/dokumentasi/organisir-foto-gps/download-batch-zip/' . $batchToken);

            return $this->response->setJSON([
                'status'           => 'ok',
                'is_finished'      => true,
                'message'          => count($processedResults) . ' foto berhasil dikelompokkan ke ' . count($clusteringResult['clusters']) . ' folder lokasi (Tanpa Watermark)!',
                'mode'             => 'batch',
                'total_processed'  => count($processedResults),
                'is_grouped'       => true,
                'cluster_radius'   => $clusterRadius,
                'clusters'         => $clusteringResult['clusters'],
                'download_zip_url' => $zipDownloadUrl,
                'images'           => array_map(static fn ($r) => [
                    'file_name'    => $r['file_name'],
                    'relative_path'=> $r['relative_zip_path'],
                    'sub_dir'      => $r['sub_dir'],
                    'cluster_id'   => $r['cluster_id'],
                    'cluster_name' => $r['cluster_name'],
                    'orig_name'    => $r['orig_name'],
                    'data_uri'     => $r['data_uri'],
                    'view_url'     => site_url('/admin/dokumentasi/organisir-foto-gps/download-batch-item/' . $batchToken . '?entry=' . rawurlencode($r['relative_zip_path'])),
                    'download_url' => site_url('/admin/dokumentasi/organisir-foto-gps/download-batch-item/' . $batchToken . '?download=1&entry=' . rawurlencode($r['relative_zip_path'])),
                ], $processedResults),
                'csrf_token'       => csrf_token(),
                'csrf_hash'        => csrf_hash(),
            ]);
        } finally {
            $this->cleanUpDirectory($tempWorkingDir); // Rule #3
        }
    }

    /**
     * Download hasil arsip ZIP dari menu Organisir Foto GPS.
     */
    public function downloadOrganisirZip(string $token)
    {
        if (! $this->canAccessOrganisirMenu()) {
            return redirect()->to('/admin/dashboard')->with('error', 'Akses ditolak (Rule #4).');
        }

        if (! preg_match('/^[a-zA-Z0-9_]+$/', $token)) {
            return redirect()->to('/admin/dokumentasi/organisir-foto-gps')->with('error', 'Token download tidak valid.');
        }

        $zipPath = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'organisir_hasil' . DIRECTORY_SEPARATOR . 'downloads' . DIRECTORY_SEPARATOR . 'organisir_batch_' . $token . '.zip';
        if (! is_file($zipPath) || ! is_readable($zipPath)) {
            return redirect()->to('/admin/dokumentasi/organisir-foto-gps')->with('error', 'File download ZIP sudah kedaluwarsa atau tidak ditemukan.');
        }

        $downloadName = 'foto-terorganisir-gps-' . date('YmdHis') . '.zip';
        return $this->response->download($zipPath, null)->setFileName($downloadName);
    }

    /**
     * Preview atau download satu berkas foto dari arsip Organisir Foto GPS.
     */
    public function downloadOrganisirItem(string $token, string $fileName = '')
    {
        if (! $this->canAccessOrganisirMenu()) {
            return redirect()->to('/admin/dashboard')->with('error', 'Akses ditolak (Rule #4).');
        }

        if (! preg_match('/^[a-zA-Z0-9_]+$/', $token)) {
            return redirect()->to('/admin/dokumentasi/organisir-foto-gps')->with('error', 'Token download tidak valid.');
        }

        $zipPath = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'organisir_hasil' . DIRECTORY_SEPARATOR . 'downloads' . DIRECTORY_SEPARATOR . 'organisir_batch_' . $token . '.zip';
        if (! is_file($zipPath) || ! is_readable($zipPath)) {
            return redirect()->to('/admin/dokumentasi/organisir-foto-gps')->with('error', 'Arsip foto tidak ditemukan.');
        }

        $rawEntry = (string) ($this->request->getGet('entry') ?? ($fileName !== '' ? $fileName : $this->request->getGet('file')));
        $rawEntry = str_replace('\\', '/', rawurldecode($rawEntry));
        $parts = array_filter(explode('/', $rawEntry), static fn ($p) => $p !== '' && $p !== '.' && $p !== '..');
        $cleanEntry = implode('/', $parts);

        if ($cleanEntry === '') {
            return redirect()->to('/admin/dokumentasi/organisir-foto-gps')->with('error', 'Nama berkas tidak valid.');
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipPath) === true) {
            $content = $zip->getFromName($cleanEntry);
            if ($content === false) {
                $base = basename($cleanEntry);
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $stat = $zip->statIndex($i);
                    if ($stat && basename($stat['name']) === $base) {
                        $content = $zip->getFromIndex($i);
                        break;
                    }
                }
            }
            $zip->close();

            if ($content !== false) {
                $ext = strtolower(pathinfo($cleanEntry, PATHINFO_EXTENSION));
                $contentType = match ($ext) {
                    'png' => 'image/png',
                    'webp' => 'image/webp',
                    default => 'image/jpeg',
                };

                $downloadFileName = basename($cleanEntry);
                $disposition = ($this->request->getGet('download') === '1') ? 'attachment' : 'inline';

                return $this->response
                    ->setHeader('Content-Type', $contentType)
                    ->setHeader('Content-Disposition', $disposition . '; filename="' . $downloadFileName . '"')
                    ->setHeader('Cache-Control', 'private, max-age=86400')
                    ->setBody($content);
            }
        }

        return redirect()->to('/admin/dokumentasi/organisir-foto-gps')->with('error', 'Foto tidak ditemukan.');
    }

    private function canAccessOrganisirMenu(): bool
    {
        $db = db_connect();
        if (! $db->tableExists('menu_akses')) {
            return true;
        }

        $role = (string) (session()->get('role') ?? '');
        $roleId = $this->resolveWatermarkRoleId($role, $db);
        if ($roleId === null) {
            return false;
        }

        $menuId = $this->resolveWatermarkMenuId('admin/dokumentasi/organisir-foto-gps', $db);
        if ($menuId === null) {
            return true;
        }

        $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';
        return (int) $db->table('menu_akses')
            ->where($roleColumn, $roleId)
            ->where('menu_id', $menuId)
            ->countAllResults() > 0;
    }

    private function getOrganisirPermissions(): array
    {
        $default = [
            'add'      => true,
            'edit'     => true,
            'export'   => true,
            'delete'   => false,
            'import'   => false,
            'approval' => false,
        ];

        $rawRole = (string) (session()->get('role') ?? '');
        $normalizedRole = strtolower(trim($rawRole));

        if (in_array($normalizedRole, ['super administrator', 'super_administrator', 'superadmin', 'super-admin', 'admin', 'administrator'], true)) {
            return $default;
        }

        $db = db_connect();
        if (! $db->tableExists('menu_akses')) {
            return $default;
        }

        $roleId = $this->resolveWatermarkRoleId($rawRole, $db);
        $menuId = $this->resolveWatermarkMenuId('admin/dokumentasi/organisir-foto-gps', $db);
        if ($roleId === null || $menuId === null) {
            return $default;
        }

        $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';
        $row = $db->table('menu_akses')
            ->select('FiturAdd, FiturEdit, FiturDelete, FiturExport, FiturImport, FiturApproval')
            ->where($roleColumn, $roleId)
            ->where('menu_id', $menuId)
            ->get()
            ->getRowArray();

        if (! is_array($row)) {
            return $default;
        }

        return [
            'add'      => (int) ($row['FiturAdd'] ?? 0) === 1,
            'edit'     => (int) ($row['FiturEdit'] ?? 0) === 1,
            'export'   => (int) ($row['FiturExport'] ?? 0) === 1,
            'delete'   => (int) ($row['FiturDelete'] ?? 0) === 1,
            'import'   => (int) ($row['FiturImport'] ?? 0) === 1,
            'approval' => (int) ($row['FiturApproval'] ?? 0) === 1,
        ];
    }

    // =========================================================================
    // HAK AKSES & PERMISSION HELPER (RULE #4)
    // =========================================================================

    private function canAccessWatermarkMenu(): bool
    {
        $db = db_connect();
        if (! $db->tableExists('menu_akses')) {
            return true;
        }

        $role = (string) (session()->get('role') ?? '');
        $roleId = $this->resolveWatermarkRoleId($role, $db);
        if ($roleId === null) {
            return false;
        }

        $menuId = $this->resolveWatermarkMenuId('admin/dokumentasi/watermark-foto', $db);
        if ($menuId === null) {
            return true;
        }

        $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';
        return (int) $db->table('menu_akses')
            ->where($roleColumn, $roleId)
            ->where('menu_id', $menuId)
            ->countAllResults() > 0;
    }

    private function getWatermarkPermissions(): array
    {
        $default = [
            'add'      => true,
            'edit'     => true,
            'export'   => true,
            'delete'   => false,
            'import'   => false,
            'approval' => false,
        ];

        $rawRole = (string) (session()->get('role') ?? '');
        $normalizedRole = strtolower(trim($rawRole));

        if (in_array($normalizedRole, ['super administrator', 'super_administrator', 'superadmin', 'super-admin', 'admin', 'administrator'], true)) {
            return $default;
        }

        $db = db_connect();
        if (! $db->tableExists('menu_akses')) {
            return $default;
        }

        $roleId = $this->resolveWatermarkRoleId($rawRole, $db);
        $menuId = $this->resolveWatermarkMenuId('admin/dokumentasi/watermark-foto', $db);
        if ($roleId === null || $menuId === null) {
            return $default;
        }

        $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';
        $row = $db->table('menu_akses')
            ->select('FiturAdd, FiturEdit, FiturDelete, FiturExport, FiturImport, FiturApproval')
            ->where($roleColumn, $roleId)
            ->where('menu_id', $menuId)
            ->get()
            ->getRowArray();

        if (! is_array($row)) {
            return $default;
        }

        return [
            'add'      => (int) ($row['FiturAdd'] ?? 0) === 1,
            'edit'     => (int) ($row['FiturEdit'] ?? 0) === 1,
            'export'   => (int) ($row['FiturExport'] ?? 0) === 1,
            'delete'   => (int) ($row['FiturDelete'] ?? 0) === 1,
            'import'   => (int) ($row['FiturImport'] ?? 0) === 1,
            'approval' => (int) ($row['FiturApproval'] ?? 0) === 1,
        ];
    }

    private function resolveWatermarkRoleId(string $role, $db): ?int
    {
        $normalized = strtolower(trim($role));
        if ($normalized === '') {
            return null;
        }

        try {
            if ($db->tableExists('access_roles')) {
                $variants = [$normalized];
                if (strpos($normalized, 'super') !== false) {
                    $variants[] = 'super administrator';
                    $variants[] = 'super_administrator';
                    $variants[] = 'superadmin';
                    $variants[] = 'super-admin';
                }

                $row = $db->table('access_roles')
                    ->select('id')
                    ->whereIn('role_key', array_values(array_unique($variants)))
                    ->where('is_active', 1)
                    ->orderBy('id', 'ASC')
                    ->get()
                    ->getRowArray();

                if (is_array($row) && isset($row['id'])) {
                    return (int) $row['id'];
                }
            }
        } catch (\Throwable $e) {
            // Fallback
        }

        if (in_array($normalized, ['admin', 'administrator'], true)) {
            return 1;
        }
        if ($normalized === 'editor') {
            return 2;
        }

        return null;
    }

    private function resolveWatermarkMenuId(string $menuLink, $db): ?string
    {
        $normalizedLink = trim(strtolower($menuLink), '/');
        $tables = ['menu_lv2', 'menu_lv1', 'menu_lv3'];

        foreach ($tables as $tbl) {
            if (! $db->tableExists($tbl)) {
                continue;
            }

            $row = $db->table($tbl)
                ->select('id')
                ->where('LOWER(link)', $normalizedLink)
                ->get()
                ->getRowArray();

            if (is_array($row) && isset($row['id'])) {
                return (string) $row['id'];
            }
        }

        return null;
    }

    private function cleanUpDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        try {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($files as $fileinfo) {
                if ($fileinfo->isDir()) {
                    @rmdir($fileinfo->getRealPath());
                } else {
                    @unlink($fileinfo->getRealPath());
                }
            }
            @rmdir($dir);
        } catch (\Throwable $e) {
            // Ignore clean-up failures
        }
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