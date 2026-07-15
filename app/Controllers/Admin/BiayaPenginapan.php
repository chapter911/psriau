<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MstBiayaPenginapanModel;
use App\Models\MstProvinsiModel;
use CodeIgniter\HTTP\RedirectResponse;

class BiayaPenginapan extends BaseController
{
    private const MENU_LINK = 'admin/master/biaya/penginapan';

    public function index()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $items = (new MstBiayaPenginapanModel())
            ->orderBy('nama_provinsi', 'ASC')
            ->orderBy('tarif', 'DESC')
            ->findAll();

        $provinsiOptions = (new MstProvinsiModel())->orderBy('nama_provinsi', 'ASC')->findAll();

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        $canManage = $this->canManageMasterData();

        return view('admin/master/biaya_penginapan', [
            'pageTitle'       => 'Master Biaya Penginapan',
            'items'           => $items,
            'provinsiOptions' => $provinsiOptions,
            'can_add'         => $canManage && (bool) ($menuPermissions['add'] ?? false),
            'can_edit'        => $canManage && (bool) ($menuPermissions['edit'] ?? false),
            'can_delete'      => $canManage && (bool) ($menuPermissions['delete'] ?? false),
        ]);
    }

    public function create()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/biaya/penginapan')->with('error', 'Anda tidak memiliki akses untuk menambah data.');
        }

        $rules = [
            'nama_provinsi' => 'required',
            'level_pejabat' => 'required',
            'tarif'         => 'required|numeric',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/admin/master/biaya/penginapan')->withInput()->with('error', 'Provinsi, Level Jabatan wajib diisi, dan Tarif harus berupa angka.');
        }

        $model = new MstBiayaPenginapanModel();
        $namaProv = trim((string) $this->request->getPost('nama_provinsi'));
        $level = trim((string) $this->request->getPost('level_pejabat'));

        // Check uniqueness
        $existing = $model->where('nama_provinsi', $namaProv)->where('level_pejabat', $level)->first();
        if (is_array($existing)) {
            return redirect()->to('/admin/master/biaya/penginapan')->withInput()->with('error', "Tarif penginapan untuk Provinsi {$namaProv} dengan Level {$level} sudah terdaftar.");
        }

        $now = date('Y-m-d H:i:s');
        $username = (string) (session()->get('username') ?? 'system');
        $kodeProv = $this->request->getPost('kode_provinsi');

        $model->insert([
            'kode_provinsi' => empty($kodeProv) ? null : $kodeProv,
            'nama_provinsi' => $namaProv,
            'level_pejabat' => $level,
            'tarif'         => (float) $this->request->getPost('tarif'),
            'created_by'    => $username,
            'created_date'  => $now,
            'updated_by'    => $username,
            'updated_date'  => $now,
        ]);

        return redirect()->to('/admin/master/biaya/penginapan')->with('message', 'Data biaya penginapan berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/biaya/penginapan')->with('error', 'Anda tidak memiliki akses untuk mengubah data.');
        }

        $rules = [
            'nama_provinsi' => 'required',
            'level_pejabat' => 'required',
            'tarif'         => 'required|numeric',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/admin/master/biaya/penginapan')->withInput()->with('error', 'Provinsi, Level Jabatan wajib diisi, dan Tarif harus berupa angka.');
        }

        $model = new MstBiayaPenginapanModel();
        $existing = $model->find($id);

        if (! is_array($existing)) {
            return redirect()->to('/admin/master/biaya/penginapan')->with('error', 'Data tidak ditemukan.');
        }

        $namaProv = trim((string) $this->request->getPost('nama_provinsi'));
        $level = trim((string) $this->request->getPost('level_pejabat'));

        // Check uniqueness excluding current record
        $duplicate = $model->where('nama_provinsi', $namaProv)
            ->where('level_pejabat', $level)
            ->where('id !=', $id)
            ->first();

        if (is_array($duplicate)) {
            return redirect()->to('/admin/master/biaya/penginapan')->withInput()->with('error', "Tarif penginapan untuk Provinsi {$namaProv} dengan Level {$level} sudah terdaftar pada data lain.");
        }

        $username = (string) (session()->get('username') ?? 'system');
        $kodeProv = $this->request->getPost('kode_provinsi');

        $model->update($id, [
            'kode_provinsi' => empty($kodeProv) ? null : $kodeProv,
            'nama_provinsi' => $namaProv,
            'level_pejabat' => $level,
            'tarif'         => (float) $this->request->getPost('tarif'),
            'updated_by'    => $username,
            'updated_date'  => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/admin/master/biaya/penginapan')->with('message', 'Data biaya penginapan berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/biaya/penginapan')->with('error', 'Anda tidak memiliki akses untuk menghapus data.');
        }

        $model = new MstBiayaPenginapanModel();
        $existing = $model->find($id);

        if (! is_array($existing)) {
            return redirect()->to('/admin/master/biaya/penginapan')->with('error', 'Data tidak ditemukan.');
        }

        $model->delete($id);

        return redirect()->to('/admin/master/biaya/penginapan')->with('message', 'Data biaya penginapan berhasil dihapus.');
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
            'add'      => false,
            'edit'     => false,
            'delete'   => false,
            'export'   => false,
            'import'   => false,
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
            'add'      => (bool) ((int) ($row['FiturAdd'] ?? 0)),
            'edit'     => (bool) ((int) ($row['FiturEdit'] ?? 0)),
            'delete'   => (bool) ((int) ($row['FiturDelete'] ?? 0)),
            'export'   => (bool) ((int) ($row['FiturExport'] ?? 0)),
            'import'   => (bool) ((int) ($row['FiturImport'] ?? 0)),
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
            'admin'  => 1,
            'editor' => 2,
            default  => null,
        };
    }

    private function resolveMenuIdByLink(string $menuLink, $db): ?string
    {
        foreach (['menu_lv3', 'menu_lv2', 'menu_lv1'] as $table) {
            if (! $db->tableExists($table) || ! $db->fieldExists('link', $table)) {
                continue;
            }

            $row = $db->table($table)
                ->select('id')
                ->where('LOWER(link)', strtolower(trim($menuLink)))
                ->orderBy('id', 'ASC')
                ->get()
                ->getRowArray();

            if (is_array($row) && isset($row['id'])) {
                return (string) $row['id'];
            }
        }

        return null;
    }
}
