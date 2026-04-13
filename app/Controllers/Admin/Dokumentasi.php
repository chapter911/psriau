<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\KegiatanLapanganModel;
use App\Models\KegiatanLapanganPhotoModel;

class Dokumentasi extends BaseController
{
    private const MAX_PHOTOS = 50;

    public function index(): string
    {
        $filterTitle = trim((string) $this->request->getGet('title'));
        $filterDate = trim((string) $this->request->getGet('date'));
        $filterLocation = trim((string) $this->request->getGet('location'));

        return view('admin/dokumentasi/index', [
            'pageTitle' => 'Kegiatan Lapangan',
            'filters' => [
                'title' => $filterTitle,
                'date' => $filterDate,
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
        $filterDate = trim((string) $this->request->getGet('date'));
        $filterLocation = trim((string) $this->request->getGet('location'));
        $globalSearch = trim((string) ($this->request->getGet('search')['value'] ?? ''));

        $orderColumns = [
            0 => 'title',
            1 => 'activity_date',
            2 => 'location',
            4 => 'created_by',
            6 => 'id',
        ];

        $orderColumnIndex = (int) ($this->request->getGet('order')[0]['column'] ?? 1);
        $orderDirection = strtolower((string) ($this->request->getGet('order')[0]['dir'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';
        $orderBy = $orderColumns[$orderColumnIndex] ?? 'activity_date';

        $applyFilters = static function ($builder) use ($filterTitle, $filterDate, $filterLocation, $globalSearch): void {
            if ($filterTitle !== '') {
                $builder->like('title', $filterTitle);
            }

            if ($filterDate !== '') {
                $builder->where('activity_date', $filterDate);
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
        }

        $data = [];
        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            $photos = $photosByActivity[$id] ?? [];

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

    public function downloadZip(int $id)
    {
        $activityModel = new KegiatanLapanganModel();
        $photoModel = new KegiatanLapanganPhotoModel();

        $activity = $activityModel->find($id);
        if (! $activity) {
            return redirect()->to('/admin/dokumentasi/kegiatan-lapangan')->with('error', 'Kegiatan lapangan tidak ditemukan.');
        }

        $photos = $photoModel
            ->where('activity_id', $id)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        if ($photos === []) {
            return redirect()->to('/admin/dokumentasi/kegiatan-lapangan')->with('error', 'Tidak ada foto untuk diunduh.');
        }

        if (! class_exists('ZipArchive')) {
            return redirect()->to('/admin/dokumentasi/kegiatan-lapangan')->with('error', 'Ekstensi ZIP tidak tersedia di server.');
        }

        $downloadDir = WRITEPATH . 'downloads';
        if (! is_dir($downloadDir) && ! @mkdir($downloadDir, 0775, true) && ! is_dir($downloadDir)) {
            return redirect()->to('/admin/dokumentasi/kegiatan-lapangan')->with('error', 'Folder sementara unduhan tidak dapat dibuat.');
        }

        $zipTempPath = $downloadDir . DIRECTORY_SEPARATOR . 'kegiatan_lapangan_' . $id . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.zip';

        $zip = new \ZipArchive();
        $openResult = $zip->open($zipTempPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        if ($openResult !== true) {
            return redirect()->to('/admin/dokumentasi/kegiatan-lapangan')->with('error', 'Gagal membuat file ZIP.');
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
            return redirect()->to('/admin/dokumentasi/kegiatan-lapangan')->with('error', 'Tidak ada file foto valid yang dapat dimasukkan ke ZIP.');
        }

        $safeTitle = strtolower(trim((string) ($activity['title'] ?? 'kegiatan-lapangan')));
        $safeTitle = preg_replace('/[^A-Za-z0-9]+/', '-', $safeTitle) ?: 'kegiatan-lapangan';
        $downloadName = 'dokumentasi-' . $safeTitle . '-' . date('Ymd') . '.zip';

        register_shutdown_function(static function () use ($zipTempPath): void {
            if (is_file($zipTempPath)) {
                @unlink($zipTempPath);
            }
        });

        return $this->response->download($zipTempPath, null)->setFileName($downloadName);
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

        $existingPhotoRows = [];
        if ($id !== null) {
            $existingPhotoRows = $photoModel
                ->where('activity_id', $id)
                ->orderBy('sort_order', 'ASC')
                ->orderBy('id', 'ASC')
                ->findAll();
        }

        $uploadedPhotos = $this->processUploadedPhotos('activity_photos', count($existingPhotoRows));
        if ($uploadedPhotos['error'] !== null) {
            return $isAjax
                ? $this->response->setStatusCode(422)->setJSON([
                    'status' => 'error',
                    'message' => $uploadedPhotos['error'],
                ])
                : redirect()->back()->withInput()->with('error', $uploadedPhotos['error']);
        }

        if ($id === null && $uploadedPhotos['photos'] === []) {
            return $isAjax
                ? $this->response->setStatusCode(422)->setJSON([
                    'status' => 'error',
                    'message' => 'Minimal satu foto kegiatan harus dipilih.',
                ])
                : redirect()->back()->withInput()->with('error', 'Minimal satu foto kegiatan harus dipilih.');
        }

        if ($id !== null && $existingPhotoRows === [] && $uploadedPhotos['photos'] === []) {
            return $isAjax
                ? $this->response->setStatusCode(422)->setJSON([
                    'status' => 'error',
                    'message' => 'Minimal satu foto kegiatan harus dipilih.',
                ])
                : redirect()->back()->withInput()->with('error', 'Minimal satu foto kegiatan harus dipilih.');
        }

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
            $this->storeActivityPhotos($newId, $uploadedPhotos['photos'], 1);

            return $isAjax
                ? $this->response->setJSON([
                    'status' => 'ok',
                    'message' => 'Kegiatan lapangan berhasil ditambahkan.',
                    'redirect' => site_url('/admin/dokumentasi/kegiatan-lapangan'),
                ])
                : redirect()->to('/admin/dokumentasi/kegiatan-lapangan')->with('message', 'Kegiatan lapangan berhasil ditambahkan.');
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

    private function processUploadedPhotos(string $fieldName, int $existingPhotoCount = 0): array
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

        $directory = FCPATH . 'uploads/dokumentasi/kegiatan-lapangan';
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
        foreach ($flatFiles as $file) {
            if (! is_object($file) || ! method_exists($file, 'getError')) {
                continue;
            }

            if ($file->getError() === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if (! $file->isValid() || $file->hasMoved()) {
                return [
                    'photos' => [],
                    'error' => 'Salah satu foto tidak valid. Silakan pilih ulang foto.',
                ];
            }

            $mimeType = strtolower((string) $file->getMimeType());
            if (! str_starts_with($mimeType, 'image/')) {
                return [
                    'photos' => [],
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
                return [
                    'photos' => [],
                    'error' => 'Gagal membuat nama file foto.',
                ];
            }

            try {
                $file->move($directory, $newName);
            } catch (\Throwable $e) {
                return [
                    'photos' => [],
                    'error' => 'Gagal menyimpan foto ke server.',
                ];
            }

            if (! is_file($directory . DIRECTORY_SEPARATOR . $newName)) {
                return [
                    'photos' => [],
                    'error' => 'Foto tidak tersimpan dengan benar di server.',
                ];
            }

            $result[] = [
                'photo_path' => '/uploads/dokumentasi/kegiatan-lapangan/' . $newName,
                'photo_name' => (string) ($file->getClientName() ?: $newName),
            ];
        }

        return [
            'photos' => $result,
            'error' => null,
        ];
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