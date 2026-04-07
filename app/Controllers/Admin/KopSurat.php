<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\KopSuratModel;
use CodeIgniter\HTTP\RedirectResponse;

class KopSurat extends BaseController
{
    private const MENU_LINK_KOP_SURAT = 'admin/master/kop-surat';

    public function index()
    {
        $forbidden = $this->denyIfNoMenuAccess();
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $items = (new KopSuratModel())
            ->orderBy('is_active', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll();

        return view('admin/kop_surat/index', [
            'pageTitle' => 'Kop Surat',
            'items' => $items,
            'can_edit' => $this->canManageKopSurat(),
        ]);
    }

    public function create()
    {
        $forbidden = $this->denyIfNoMenuAccess();
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if ($this->request->getMethod() === 'POST') {
            return $this->saveData(null, null, $this->request->isAJAX());
        }

        return view('admin/kop_surat/form', [
            'pageTitle' => 'Tambah Kop Surat',
            'actionUrl' => '/admin/master/kop-surat/tambah',
            'item' => null,
        ]);
    }

    public function edit(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess();
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $model = new KopSuratModel();
        $item = $model->find($id);

        if (! is_array($item)) {
            return redirect()->to('/admin/master/kop-surat')->with('error', 'Data kop surat tidak ditemukan.');
        }

        if ($this->request->getMethod() === 'POST') {
            return $this->saveData($id, $item, false);
        }

        return view('admin/kop_surat/form', [
            'pageTitle' => 'Ubah Kop Surat',
            'actionUrl' => '/admin/master/kop-surat/' . $id . '/ubah',
            'item' => $item,
        ]);
    }

    public function delete(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess();
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->canManageKopSurat()) {
            return redirect()->to('/admin/master/kop-surat')->with('error', 'Anda tidak memiliki akses untuk menghapus kop surat.');
        }

        $model = new KopSuratModel();
        $item = $model->find($id);

        if (is_array($item)) {
            $this->deleteLocalImage($item['image_url'] ?? null);
            $model->delete($id);
        }

        return redirect()->to('/admin/master/kop-surat')->with('message', 'Kop surat berhasil dihapus.');
    }

    public function updateStatus(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess();
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->canManageKopSurat()) {
            return redirect()->to('/admin/master/kop-surat')->with('error', 'Anda tidak memiliki akses untuk mengubah status kop surat.');
        }

        $model = new KopSuratModel();
        $item = $model->find($id);

        if (! is_array($item)) {
            return redirect()->to('/admin/master/kop-surat')->with('error', 'Data kop surat tidak ditemukan.');
        }

        $isActive = (int) ($this->request->getPost('is_active') ? 1 : 0);

        if ($isActive === 0 && ! $this->hasOtherActive($id)) {
            return redirect()->to('/admin/master/kop-surat')->with('error', 'Minimal harus ada 1 kop surat aktif.');
        }

        if ($isActive === 1) {
            $this->deactivateOthers($id);
        }

        $model->update($id, [
            'is_active' => $isActive,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $message = $isActive === 1
            ? 'Kop surat berhasil diaktifkan.'
            : 'Kop surat berhasil dinonaktifkan.';

        return redirect()->to('/admin/master/kop-surat')->with('message', $message);
    }

    private function saveData(?int $id = null, ?array $existing = null, bool $isAJAX = false)
    {
        if (! $this->canManageKopSurat()) {
            $message = 'Anda tidak memiliki akses untuk menyimpan kop surat.';
            if ($isAJAX) {
                return $this->response->setJSON(['success' => false, 'message' => $message]);
            }
            return redirect()->to('/admin/master/kop-surat')->with('error', $message);
        }

        $rules = [
            'title' => 'required|min_length[3]',
            'image_file' => $id === null
                ? 'uploaded[image_file]|is_image[image_file]|max_size[image_file,4096]|mime_in[image_file,image/jpg,image/jpeg,image/png,image/webp,image/svg+xml]'
                : 'if_exist|is_image[image_file]|max_size[image_file,4096]|mime_in[image_file,image/jpg,image/jpeg,image/png,image/webp,image/svg+xml]',
            'description' => 'permit_empty|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            $errors = $this->validator->getErrors();
            $message = implode(', ', $errors);
            if ($isAJAX) {
                return $this->response->setJSON(['success' => false, 'message' => $message]);
            }
            return redirect()->back()->withInput()->with('error', 'Data kop surat belum valid.');
        }

        $imagePath = $this->uploadImage('image_file', 'kop_surat');
        if ($imagePath === null && $id === null) {
            $message = 'File kop surat wajib diunggah.';
            if ($isAJAX) {
                return $this->response->setJSON(['success' => false, 'message' => $message]);
            }
            return redirect()->back()->withInput()->with('error', $message);
        }

        if ($imagePath !== null && is_array($existing)) {
            $this->deleteLocalImage($existing['image_url'] ?? null);
        }

        $payload = [
            'title' => trim((string) $this->request->getPost('title')),
            'image_url' => $imagePath ?? ($existing['image_url'] ?? null),
            'description' => trim((string) $this->request->getPost('description')),
            'is_active' => (int) ($this->request->getPost('is_active') ? 1 : 0),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ((int) ($payload['is_active'] ?? 0) === 0 && ! $this->hasOtherActive($id)) {
            $payload['is_active'] = 1;
        }

        if ($id === null) {
            if ((int) ($payload['is_active'] ?? 0) === 1) {
                $this->deactivateOthers(null);
            }

            $payload['created_at'] = date('Y-m-d H:i:s');
            (new KopSuratModel())->insert($payload);
            $message = 'Kop surat berhasil ditambahkan.';
            if ($isAJAX) {
                return $this->response->setJSON(['success' => true, 'message' => $message]);
            }
            return redirect()->to('/admin/master/kop-surat')->with('message', $message);
        }

        if ((int) ($payload['is_active'] ?? 0) === 1) {
            $this->deactivateOthers($id);
        }

        (new KopSuratModel())->update($id, $payload);
        $message = 'Kop surat berhasil diperbarui.';
        if ($isAJAX) {
            return $this->response->setJSON(['success' => true, 'message' => $message]);
        }
        return redirect()->to('/admin/master/kop-surat')->with('message', $message);
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

    private function deactivateOthers(?int $excludeId = null): void
    {
        $model = new KopSuratModel();

        $query = $model->where('is_active', 1);
        if ($excludeId !== null) {
            $query = $query->where('id !=', $excludeId);
        }

        $activeItems = $query->findAll();
        foreach ($activeItems as $activeItem) {
            $currentId = (int) ($activeItem['id'] ?? 0);
            if ($currentId > 0) {
                $model->update($currentId, ['is_active' => 0]);
            }
        }
    }

    private function hasOtherActive(?int $excludeId = null): bool
    {
        $model = new KopSuratModel();

        $query = $model->where('is_active', 1);
        if ($excludeId !== null) {
            $query = $query->where('id !=', $excludeId);
        }

        return $query->countAllResults() > 0;
    }

    private function canManageKopSurat(): bool
    {
        $role = strtolower(trim((string) session()->get('role')));

        return in_array($role, ['admin', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);
    }

    private function denyIfNoMenuAccess(): ?RedirectResponse
    {
        if ($this->hasMenuAccess(self::MENU_LINK_KOP_SURAT)) {
            return null;
        }

        return redirect()->to('/forbidden?from=' . rawurlencode(self::MENU_LINK_KOP_SURAT));
    }

    private function hasMenuAccess(string $menuLink): bool
    {
        $db = db_connect();
        if (! $db->tableExists('menu_akses')) {
            return true;
        }

        $roleId = $this->resolveRoleId((string) session()->get('role'), $db);
        if ($roleId === null) {
            return false;
        }

        $menuId = $this->resolveMenuIdByLink($menuLink, $db);
        if ($menuId === null) {
            return false;
        }

        $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';

        return (int) $db->table('menu_akses')
            ->where($roleColumn, $roleId)
            ->where('menu_id', $menuId)
            ->countAllResults() > 0;
    }

    private function resolveRoleId(string $role, $db): ?int
    {
        $normalized = strtolower(trim($role));
        if ($normalized === '') {
            return null;
        }

        if ($db->tableExists('access_roles')) {
            $variants = [$normalized];
            if ($normalized === 'super administrator') {
                $variants[] = 'super_administrator';
                $variants[] = 'super-admin';
                $variants[] = 'superadmin';
            } elseif ($normalized === 'super_administrator' || $normalized === 'super-admin' || $normalized === 'superadmin') {
                $variants[] = 'super administrator';
                $variants[] = 'super_administrator';
                $variants[] = 'super-admin';
                $variants[] = 'superadmin';
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

        return match ($normalized) {
            'admin' => 1,
            'editor' => 2,
            default => null,
        };
    }

    private function resolveMenuIdByLink(string $menuLink, $db): ?string
    {
        $normalized = trim(strtolower($menuLink), '/');
        if ($normalized === '') {
            return null;
        }

        foreach (['menu_lv3', 'menu_lv2', 'menu_lv1'] as $table) {
            if (! $db->tableExists($table)) {
                continue;
            }

            $row = $db->table($table)
                ->select('id')
                ->where('LOWER(link)', $normalized)
                ->get()
                ->getRowArray();

            if (is_array($row) && isset($row['id'])) {
                return (string) $row['id'];
            }
        }

        return null;
    }
}
