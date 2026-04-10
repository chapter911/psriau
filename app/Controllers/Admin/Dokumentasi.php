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
        $activityModel = new KegiatanLapanganModel();
        $photoModel = new KegiatanLapanganPhotoModel();

        $activities = $activityModel
            ->orderBy('activity_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll();

        $activityIds = array_values(array_filter(array_map(static fn (array $row): string => (string) ($row['id'] ?? ''), $activities)));
        $photosByActivity = [];

        if ($activityIds !== []) {
            $photos = $photoModel
                ->whereIn('activity_id', $activityIds)
                ->orderBy('sort_order', 'ASC')
                ->orderBy('id', 'ASC')
                ->findAll();

            foreach ($photos as $photo) {
                $activityId = (string) ($photo['activity_id'] ?? '');
                $photosByActivity[$activityId][] = $photo;
            }
        }

        foreach ($activities as &$activity) {
            $activityId = (string) ($activity['id'] ?? '');
            $activityPhotos = $photosByActivity[$activityId] ?? [];
            $activity['photos'] = $activityPhotos;
            $activity['photo_count'] = count($activityPhotos);
            $activity['cover_photo'] = $activityPhotos[0]['photo_path'] ?? null;
        }
        unset($activity);

        return view('admin/dokumentasi/index', [
            'activities' => $activities,
            'pageTitle' => 'Kegiatan Lapangan',
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

    private function saveData(?int $id = null, ?array $existing = null)
    {
        $isAjax = $this->request->isAJAX();
        $activityTitle = trim((string) $this->request->getPost('title'));
        $activityDate = trim((string) $this->request->getPost('activity_date'));
        $location = trim((string) $this->request->getPost('location'));

        if ($activityTitle === '' || mb_strlen($activityTitle) < 5 || $location === '' || mb_strlen($location) < 3) {
            return $isAjax
                ? $this->response->setStatusCode(422)->setJSON([
                    'status' => 'error',
                    'message' => 'Data kegiatan belum lengkap.',
                ])
                : redirect()->back()->withInput()->with('error', 'Data kegiatan belum lengkap.');
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