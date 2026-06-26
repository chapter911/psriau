<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MstPaketModel;
use CodeIgniter\HTTP\RedirectResponse;

class Paket extends BaseController
{
    private const MENU_LINK = 'admin/master/paket';

    public function index()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $items = (new MstPaketModel())
            ->orderBy('nama_paket', 'ASC')
            ->findAll();

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        $canManage = $this->canManageMasterData();

        return view('admin/master/paket', [
            'pageTitle' => 'Master Paket',
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
            return redirect()->to('/admin/master/paket')->with('error', 'Anda tidak memiliki akses untuk menambah data paket.');
        }

        $rules = [
            'nama_paket' => 'required|max_length[255]',
            'singkatan_paket' => 'required|max_length[50]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/admin/master/paket')->withInput()->with('error', 'Data paket belum valid.');
        }

        $namaPaket = trim((string) $this->request->getPost('nama_paket'));
        $singkatanPaket = trim((string) $this->request->getPost('singkatan_paket'));
        $model = new MstPaketModel();

        if ($model->where('LOWER(nama_paket)', strtolower($namaPaket))->countAllResults() > 0) {
            return redirect()->to('/admin/master/paket')->withInput()->with('error', 'Nama paket sudah terdaftar.');
        }

        if ($model->where('LOWER(singkatan_paket)', strtolower($singkatanPaket))->countAllResults() > 0) {
            return redirect()->to('/admin/master/paket')->withInput()->with('error', 'Singkatan paket sudah terdaftar.');
        }

        $now = date('Y-m-d H:i:s');
        $username = (string) (session()->get('username') ?? 'system');

        $model->insert([
            'nama_paket' => $namaPaket,
            'singkatan_paket' => $singkatanPaket,
            'is_active' => 1,
            'created_by' => $username,
            'created_date' => $now,
            'updated_by' => $username,
            'updated_date' => $now,
        ]);

        return redirect()->to('/admin/master/paket')->with('message', 'Data paket berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/paket')->with('error', 'Anda tidak memiliki akses untuk mengubah data paket.');
        }

        $rules = [
            'nama_paket' => 'required|max_length[255]',
            'singkatan_paket' => 'required|max_length[50]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/admin/master/paket')->withInput()->with('error', 'Data paket belum valid.');
        }

        $model = new MstPaketModel();
        $existing = $model->find($id);
        if (! is_array($existing)) {
            return redirect()->to('/admin/master/paket')->with('error', 'Data paket tidak ditemukan.');
        }

        $namaPaket = trim((string) $this->request->getPost('nama_paket'));
        $singkatanPaket = trim((string) $this->request->getPost('singkatan_paket'));

        $duplicateNama = $model
            ->where('LOWER(nama_paket)', strtolower($namaPaket))
            ->where('id !=', $id)
            ->countAllResults();

        if ($duplicateNama > 0) {
            return redirect()->to('/admin/master/paket')->withInput()->with('error', 'Nama paket sudah digunakan oleh data lain.');
        }

        $duplicateSingkatan = $model
            ->where('LOWER(singkatan_paket)', strtolower($singkatanPaket))
            ->where('id !=', $id)
            ->countAllResults();

        if ($duplicateSingkatan > 0) {
            return redirect()->to('/admin/master/paket')->withInput()->with('error', 'Singkatan paket sudah digunakan oleh data lain.');
        }

        $model->update($id, [
            'nama_paket' => $namaPaket,
            'singkatan_paket' => $singkatanPaket,
            'updated_by' => (string) (session()->get('username') ?? 'system'),
            'updated_date' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/admin/master/paket')->with('message', 'Data paket berhasil diperbarui.');
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
