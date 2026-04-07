<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EventModel;

class Event extends BaseController
{
    public function index(): string
    {
        $events = (new EventModel())->orderBy('event_date', 'DESC')->findAll();

        return view('admin/events/index', ['events' => $events]);
    }

    public function create()
    {
        if ($this->request->getMethod() === 'POST') {
            return $this->saveData();
        }

        return view('admin/events/form', [
            'event'    => null,
            'pageTitle' => 'Tambah Acara',
            'actionUrl' => '/admin/acara/tambah',
        ]);
    }

    public function edit(int $id)
    {
        $model = new EventModel();
        $event = $model->find($id);

        if (! $event) {
            return redirect()->to('/admin/acara')->with('error', 'Acara tidak ditemukan.');
        }

        if ($this->request->getMethod() === 'POST') {
            return $this->saveData($id, $event);
        }

        return view('admin/events/form', [
            'event'     => $event,
            'pageTitle' => 'Ubah Acara',
            'actionUrl' => '/admin/acara/' . $id . '/ubah',
        ]);
    }

    public function delete(int $id)
    {
        $model = new EventModel();
        $event = $model->find($id);

        if ($event) {
            $this->deleteLocalImage($event['image_url'] ?? null);
            $model->delete($id);
        }

        return redirect()->to('/admin/acara')->with('message', 'Acara berhasil dihapus.');
    }

    private function saveData(?int $id = null, ?array $existing = null)
    {
        $rules = [
            'title'      => 'required|min_length[5]',
            'summary'    => 'required|min_length[10]',
            'content'    => 'required|min_length[20]',
            'event_date' => 'permit_empty|valid_date',
            'image_file' => 'if_exist|is_image[image_file]|max_size[image_file,4096]|mime_in[image_file,image/jpg,image/jpeg,image/png,image/webp,image/svg+xml]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Data acara belum valid.');
        }

        $model = new EventModel();
        $title = (string) $this->request->getPost('title');
        $slug  = $this->buildUniqueSlug($title, $id);
        $imagePath = $this->uploadImage('image_file', 'events');

        if ($imagePath !== null && is_array($existing)) {
            $this->deleteLocalImage($existing['image_url'] ?? null);
        }

        $payload = [
            'title'        => $title,
            'slug'         => $slug,
            'summary'      => $this->request->getPost('summary'),
            'content'      => $this->request->getPost('content'),
            'event_date'   => $this->request->getPost('event_date') ?: null,
            'location'     => $this->request->getPost('location'),
            'image_url'    => $imagePath ?? ($existing['image_url'] ?? null),
            'is_published' => (int) ($this->request->getPost('is_published') ? 1 : 0),
        ];

        if ($id === null) {
            $model->insert($payload);
            return redirect()->to('/admin/acara')->with('message', 'Acara berhasil ditambahkan.');
        }

        $model->update($id, $payload);

        return redirect()->to('/admin/acara')->with('message', 'Acara berhasil diperbarui.');
    }

    private function buildUniqueSlug(string $title, ?int $id = null): string
    {
        $model    = new EventModel();
        $baseSlug = url_title($title, '-', true);
        $slug     = $baseSlug;
        $counter  = 1;

        while (true) {
            $existing = $model->where('slug', $slug)->first();
            if (! $existing || ($id !== null && (int) $existing['id'] === $id)) {
                return $slug;
            }
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
    }

    private function uploadImage(string $fieldName, string $directory): ?string
    {
        $file = $this->request->getFile($fieldName);

        if (! $file || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if (! $file->isValid() || $file->hasMoved()) {
            return null;
        }

        $targetDir = FCPATH . 'uploads/' . $directory;
        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $newName = $file->getRandomName();
        $file->move($targetDir, $newName);

        return '/uploads/' . $directory . '/' . $newName;
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
