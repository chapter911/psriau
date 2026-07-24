<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MstMataAnggaranModel;
use CodeIgniter\HTTP\RedirectResponse;

class MataAnggaran extends BaseController
{
    private const MENU_LINK = 'admin/master/mata-anggaran';

    public function index()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $items = (new MstMataAnggaranModel())
            ->orderBy('status', 'ASC') // 'aktif' first
            ->orderBy('id', 'DESC')
            ->findAll();

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        $canManage = $this->canManageMasterData();

        return view('admin/master/mata_anggaran', [
            'pageTitle' => 'Master Mata Anggaran',
            'items'     => $items,
            'can_add'    => $canManage && (bool) ($menuPermissions['add'] ?? false),
            'can_edit'   => $canManage && (bool) ($menuPermissions['edit'] ?? false),
            'can_delete' => $canManage && (bool) ($menuPermissions['delete'] ?? false),
        ]);
    }

    public function create()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/mata-anggaran')->with('error', 'Anda tidak memiliki akses untuk menambah data mata anggaran.');
        }

        $mataAnggaran = trim((string) $this->request->getPost('mata_anggaran'));
        $berlakuDari = trim((string) $this->request->getPost('berlaku_dari'));
        $berlakuHingga = trim((string) $this->request->getPost('berlaku_hingga'));
        $status = trim((string) $this->request->getPost('status'));

        if ($status !== 'aktif') {
            $status = 'tidak_aktif';
        }

        if ($mataAnggaran === '') {
            return redirect()->to('/admin/master/mata-anggaran')->withInput()->with('error', 'Mata anggaran wajib diisi.');
        }

        if ($berlakuDari === '') {
            return redirect()->to('/admin/master/mata-anggaran')->withInput()->with('error', 'Periode berlaku dari wajib diisi.');
        }

        $model = new MstMataAnggaranModel();

        // 1. Uniqueness check for mata_anggaran
        $existingName = $model->where('LOWER(mata_anggaran)', strtolower($mataAnggaran))->first();
        if ($existingName !== null) {
            return redirect()->to('/admin/master/mata-anggaran')->withInput()->with('error', 'Mata anggaran "' . esc($mataAnggaran) . '" sudah ada. Mata anggaran tidak boleh sama dengan mata anggaran lainnya.');
        }

        // 2. Validate berlaku_hingga
        $finalBerlakuHingga = null;
        if ($berlakuHingga !== '') {
            if (strtotime($berlakuHingga) < strtotime($berlakuDari)) {
                return redirect()->to('/admin/master/mata-anggaran')->withInput()->with('error', 'Periode hingga tidak boleh lebih awal dari periode dari.');
            }
            $finalBerlakuHingga = $berlakuHingga;
        } else {
            // Check constraint: Only 1 record can have empty berlaku_hingga
            $existingNull = $model->where('berlaku_hingga IS NULL')->first();

            if ($existingNull !== null) {
                return redirect()->to('/admin/master/mata-anggaran')->withInput()->with('error', 'Hanya 1 mata anggaran yang periode hingganya boleh dikosongkan. Data mata anggaran "' . esc($existingNull['mata_anggaran']) . '" saat ini tidak memiliki periode hingga.');
            }
        }

        // 3. Handle active status constraint (only 1 active)
        if ($status === 'aktif') {
            $this->deactivateOthers(null);
        }

        $now = date('Y-m-d H:i:s');
        $username = (string) (session()->get('username') ?? 'system');

        $model->insert([
            'mata_anggaran'  => $mataAnggaran,
            'berlaku_dari'   => $berlakuDari,
            'berlaku_hingga' => $finalBerlakuHingga,
            'status'         => $status,
            'created_by'     => $username,
            'created_date'   => $now,
            'updated_by'     => $username,
            'updated_date'   => $now,
        ]);

        return redirect()->to('/admin/master/mata-anggaran')->with('message', 'Data mata anggaran berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/mata-anggaran')->with('error', 'Anda tidak memiliki akses untuk mengubah data mata anggaran.');
        }

        $model = new MstMataAnggaranModel();
        $existing = $model->find($id);

        if (! is_array($existing)) {
            return redirect()->to('/admin/master/mata-anggaran')->with('error', 'Data mata anggaran tidak ditemukan.');
        }

        $mataAnggaran = trim((string) $this->request->getPost('mata_anggaran'));
        $berlakuDari = trim((string) $this->request->getPost('berlaku_dari'));
        $berlakuHingga = trim((string) $this->request->getPost('berlaku_hingga'));
        $status = trim((string) $this->request->getPost('status'));

        if ($status !== 'aktif') {
            $status = 'tidak_aktif';
        }

        if ($mataAnggaran === '') {
            return redirect()->to('/admin/master/mata-anggaran')->withInput()->with('error', 'Mata anggaran wajib diisi.');
        }

        if ($berlakuDari === '') {
            return redirect()->to('/admin/master/mata-anggaran')->withInput()->with('error', 'Periode berlaku dari wajib diisi.');
        }

        // 1. Uniqueness check for mata_anggaran excluding current id
        $duplicateName = $model->where('id !=', $id)
            ->where('LOWER(mata_anggaran)', strtolower($mataAnggaran))
            ->first();

        if ($duplicateName !== null) {
            return redirect()->to('/admin/master/mata-anggaran')->withInput()->with('error', 'Mata anggaran "' . esc($mataAnggaran) . '" sudah ada pada data lain. Mata anggaran tidak boleh sama.');
        }

        // 2. Validate berlaku_hingga
        $finalBerlakuHingga = null;
        if ($berlakuHingga !== '') {
            if (strtotime($berlakuHingga) < strtotime($berlakuDari)) {
                return redirect()->to('/admin/master/mata-anggaran')->withInput()->with('error', 'Periode hingga tidak boleh lebih awal dari periode dari.');
            }
            $finalBerlakuHingga = $berlakuHingga;
        } else {
            // Check constraint: Only 1 record can have empty berlaku_hingga (excluding current id)
            $existingNull = $model->where('id !=', $id)->where('berlaku_hingga IS NULL')->first();

            if ($existingNull !== null) {
                return redirect()->to('/admin/master/mata-anggaran')->withInput()->with('error', 'Hanya 1 mata anggaran yang periode hingganya boleh dikosongkan. Data mata anggaran "' . esc($existingNull['mata_anggaran']) . '" saat ini tidak memiliki periode hingga.');
            }
        }

        // 3. Handle active status constraint (only 1 active)
        if ($status === 'aktif') {
            $this->deactivateOthers($id);
        }

        $username = (string) (session()->get('username') ?? 'system');

        $model->update($id, [
            'mata_anggaran'  => $mataAnggaran,
            'berlaku_dari'   => $berlakuDari,
            'berlaku_hingga' => $finalBerlakuHingga,
            'status'         => $status,
            'updated_by'     => $username,
            'updated_date'   => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/admin/master/mata-anggaran')->with('message', 'Data mata anggaran berhasil diperbarui.');
    }

    public function updateStatus(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/mata-anggaran')->with('error', 'Anda tidak memiliki akses untuk mengubah status mata anggaran.');
        }

        $model = new MstMataAnggaranModel();
        $existing = $model->find($id);

        if (! is_array($existing)) {
            return redirect()->to('/admin/master/mata-anggaran')->with('error', 'Data mata anggaran tidak ditemukan.');
        }

        $newStatus = trim((string) $this->request->getPost('status'));
        if ($newStatus !== 'aktif') {
            $newStatus = 'tidak_aktif';
        }

        if ($newStatus === 'aktif') {
            $this->deactivateOthers($id);
        }

        $username = (string) (session()->get('username') ?? 'system');

        $model->update($id, [
            'status'       => $newStatus,
            'updated_by'   => $username,
            'updated_date' => date('Y-m-d H:i:s'),
        ]);

        $message = $newStatus === 'aktif'
            ? 'Status mata anggaran berhasil diubah menjadi Aktif.'
            : 'Status mata anggaran berhasil diubah menjadi Tidak Aktif.';

        return redirect()->to('/admin/master/mata-anggaran')->with('message', $message);
    }

    public function delete(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/mata-anggaran')->with('error', 'Anda tidak memiliki akses untuk menghapus data mata anggaran.');
        }

        $model = new MstMataAnggaranModel();
        $existing = $model->find($id);

        if (! is_array($existing)) {
            return redirect()->to('/admin/master/mata-anggaran')->with('error', 'Data mata anggaran tidak ditemukan.');
        }

        $model->delete($id);

        return redirect()->to('/admin/master/mata-anggaran')->with('message', 'Data mata anggaran berhasil dihapus.');
    }

    private function deactivateOthers(?int $excludeId): void
    {
        $db = db_connect();
        $builder = $db->table('mst_mata_anggaran');
        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }
        $username = (string) (session()->get('username') ?? 'system');
        $builder->update([
            'status'       => 'tidak_aktif',
            'updated_by'   => $username,
            'updated_date' => date('Y-m-d H:i:s'),
        ]);
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
        if ($this->canManageMasterData()) {
            return true;
        }

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
