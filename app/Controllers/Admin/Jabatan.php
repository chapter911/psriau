<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MstJabatanModel;
use CodeIgniter\HTTP\RedirectResponse;

class Jabatan extends BaseController
{
    private const MENU_LINK = 'admin/master/jabatan';

    public function index()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $items = (new MstJabatanModel())
            ->orderBy('jabatan', 'ASC')
            ->findAll();

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        $canManage = $this->canManageMasterData();

        return view('admin/master/jabatan', [
            'pageTitle' => 'Master Jabatan',
            'items' => $items,
            'can_add' => $canManage && (bool) ($menuPermissions['add'] ?? false),
            'can_edit' => $canManage && (bool) ($menuPermissions['edit'] ?? false),
        ]);
    }

    public function create()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/jabatan')->with('error', 'Anda tidak memiliki akses untuk menambah data jabatan.');
        }

        $rules = [
            'jabatan' => 'required|max_length[150]',
            'jenis_jabatan' => 'required|in_list[Fungsional,Perbendaharaan,Pelaksana]',
            'deskripsi_jabatan' => 'permit_empty|max_length[1000]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/admin/master/jabatan')->withInput()->with('error', 'Data jabatan belum valid.');
        }

        $jabatan = trim((string) $this->request->getPost('jabatan'));
        $model = new MstJabatanModel();

        if ($model->where('LOWER(jabatan)', strtolower($jabatan))->countAllResults() > 0) {
            return redirect()->to('/admin/master/jabatan')->withInput()->with('error', 'Nama jabatan sudah terdaftar.');
        }

        $now = date('Y-m-d H:i:s');
        $username = (string) (session()->get('username') ?? 'system');

        $model->insert([
            'jabatan' => $jabatan,
            'jenis_jabatan' => trim((string) $this->request->getPost('jenis_jabatan')),
            'deskripsi_jabatan' => $this->nullableString($this->request->getPost('deskripsi_jabatan')),
            'is_active' => 1,
            'created_by' => $username,
            'created_date' => $now,
            'updated_by' => $username,
            'updated_date' => $now,
        ]);

        return redirect()->to('/admin/master/jabatan')->with('message', 'Data jabatan berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/jabatan')->with('error', 'Anda tidak memiliki akses untuk mengubah data jabatan.');
        }

        $rules = [
            'jabatan' => 'required|max_length[150]',
            'jenis_jabatan' => 'required|in_list[Fungsional,Perbendaharaan,Pelaksana]',
            'deskripsi_jabatan' => 'permit_empty|max_length[1000]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/admin/master/jabatan')->withInput()->with('error', 'Data jabatan belum valid.');
        }

        $model = new MstJabatanModel();
        $existing = $model->find($id);
        if (! is_array($existing)) {
            return redirect()->to('/admin/master/jabatan')->with('error', 'Data jabatan tidak ditemukan.');
        }

        $jabatan = trim((string) $this->request->getPost('jabatan'));
        $duplicate = $model
            ->where('LOWER(jabatan)', strtolower($jabatan))
            ->where('id !=', $id)
            ->countAllResults();

        if ($duplicate > 0) {
            return redirect()->to('/admin/master/jabatan')->withInput()->with('error', 'Nama jabatan sudah digunakan oleh data lain.');
        }

        $model->update($id, [
            'jabatan' => $jabatan,
            'jenis_jabatan' => trim((string) $this->request->getPost('jenis_jabatan')),
            'deskripsi_jabatan' => $this->nullableString($this->request->getPost('deskripsi_jabatan')),
            'updated_by' => (string) (session()->get('username') ?? 'system'),
            'updated_date' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/admin/master/jabatan')->with('message', 'Data jabatan berhasil diperbarui.');
    }

    public function updateStatus(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/jabatan')->with('error', 'Anda tidak memiliki akses untuk mengubah status jabatan.');
        }

        $status = (int) $this->request->getPost('is_active');
        if (! in_array($status, [0, 1], true)) {
            return redirect()->to('/admin/master/jabatan')->with('error', 'Status jabatan tidak valid.');
        }

        $model = new MstJabatanModel();
        $existing = $model->find($id);
        if (! is_array($existing)) {
            return redirect()->to('/admin/master/jabatan')->with('error', 'Data jabatan tidak ditemukan.');
        }

        $model->update($id, [
            'is_active' => $status,
            'updated_by' => (string) (session()->get('username') ?? 'system'),
            'updated_date' => date('Y-m-d H:i:s'),
        ]);

        $message = $status === 1 ? 'Jabatan berhasil diaktifkan.' : 'Jabatan berhasil dinonaktifkan.';
        return redirect()->to('/admin/master/jabatan')->with('message', $message);
    }

    private function nullableString($value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function canManageMasterData(): bool
    {
        $role = strtolower(trim((string) session()->get('role')));

        return in_array($role, ['admin', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);
    }

    private function denyIfNoMenuAccess(string $menuLink): ?RedirectResponse
    {
        if ($this->hasMenuAccess($menuLink)) {
            return null;
        }

        return redirect()->to('/forbidden?from=' . rawurlencode($menuLink));
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

    private function resolveMenuPermissions(string $menuLink): array
    {
        $default = [
            'add' => false,
            'edit' => false,
            'delete' => false,
            'export' => false,
            'import' => false,
            'approval' => false,
        ];

        $db = db_connect();
        if (! $db->tableExists('menu_akses')) {
            return $default;
        }

        $roleId = $this->resolveRoleId((string) session()->get('role'), $db);
        $menuId = $this->resolveMenuIdByLink($menuLink, $db);
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
            'add' => (bool) ((int) ($row['FiturAdd'] ?? 0)),
            'edit' => (bool) ((int) ($row['FiturEdit'] ?? 0)),
            'delete' => (bool) ((int) ($row['FiturDelete'] ?? 0)),
            'export' => (bool) ((int) ($row['FiturExport'] ?? 0)),
            'import' => (bool) ((int) ($row['FiturImport'] ?? 0)),
            'approval' => (bool) ((int) ($row['FiturApproval'] ?? 0)),
        ];
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
        foreach (['menu_lv3', 'menu_lv2', 'menu_lv1'] as $table) {
            if (! $db->tableExists($table)) {
                continue;
            }

            $row = $db->table($table)
                ->select('id')
                ->where('LOWER(link)', strtolower($menuLink))
                ->get()
                ->getRowArray();

            if (is_array($row) && isset($row['id'])) {
                return (string) $row['id'];
            }
        }

        return null;
    }
}