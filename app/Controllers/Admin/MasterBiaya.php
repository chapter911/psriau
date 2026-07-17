<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MstBiayaTransportasiModel;
use App\Models\MstBiayaPenginapanModel;
use App\Models\MstBiayaHarianModel;
use App\Models\MstProvinsiModel;
use CodeIgniter\HTTP\RedirectResponse;

class MasterBiaya extends BaseController
{
    // ============================================================
    // BIAYA TRANSPORTASI
    // ============================================================

    private const LINK_TRANSPORTASI = 'admin/master/biaya/transportasi';
    private const LINK_PENGINAPAN   = 'admin/master/biaya/penginapan';
    private const LINK_HARIAN       = 'admin/master/biaya/harian';

    public function transportasiIndex()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::LINK_TRANSPORTASI);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $items = (new MstBiayaTransportasiModel())
            ->select('mst_biaya_transportasi.*, mst_provinsi.nama_provinsi')
            ->join('mst_provinsi', 'mst_provinsi.kode_provinsi = mst_biaya_transportasi.provinsi_kode', 'left')
            ->orderBy('mst_provinsi.nama_provinsi', 'ASC')
            ->findAll();

        $provinsis       = (new MstProvinsiModel())->orderBy('nama_provinsi', 'ASC')->findAll();
        $menuPermissions = $this->resolveMenuPermissions(self::LINK_TRANSPORTASI);
        $canManage       = $this->canManageMasterData();

        return view('admin/master/biaya_transportasi', [
            'pageTitle'   => 'Biaya Transportasi',
            'items'       => $items,
            'provinsis'   => $provinsis,
            'can_add'     => $canManage && (bool) ($menuPermissions['add'] ?? false),
            'can_edit'    => $canManage && (bool) ($menuPermissions['edit'] ?? false),
            'can_delete'  => $canManage && (bool) ($menuPermissions['delete'] ?? false),
        ]);
    }

    public function transportasiCreate()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::LINK_TRANSPORTASI);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/biaya/transportasi')->with('error', 'Anda tidak memiliki akses untuk menambah data.');
        }

        $rules = [
            'provinsi_kode'  => 'required',
            'besaran'        => 'required|numeric',
            'berlaku_mulai'  => 'required|valid_date',
            'berlaku_hingga' => 'permit_empty|valid_date',
        ];

        $postData = $this->request->getPost();
        if (isset($postData['besaran'])) {
            $postData['besaran'] = str_replace('.', '', (string) $postData['besaran']);
        }

        if (! $this->validateData($postData, $rules)) {
            return redirect()->to('/admin/master/biaya/transportasi')->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $now      = date('Y-m-d H:i:s');
        $username = (string) (session()->get('username') ?? 'system');

        (new MstBiayaTransportasiModel())->insert([
            'provinsi_kode'  => trim((string) $postData['provinsi_kode']),
            'berlaku_mulai'  => $postData['berlaku_mulai'],
            'berlaku_hingga' => $postData['berlaku_hingga'] ?: null,
            'is_active'      => (int) $postData['is_active'],
            'satuan'         => trim((string) $postData['satuan']) ?: 'Orang/Kali',
            'besaran'        => (int) $postData['besaran'],
            'created_by'     => $username,
            'created_date'  => $now,
            'updated_by'    => $username,
            'updated_date'  => $now,
        ]);

        return redirect()->to('/admin/master/biaya/transportasi')->with('message', 'Data biaya transportasi berhasil ditambahkan.');
    }

    public function transportasiEdit(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::LINK_TRANSPORTASI);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/biaya/transportasi')->with('error', 'Anda tidak memiliki akses untuk mengubah data.');
        }

        $model    = new MstBiayaTransportasiModel();
        $existing = $model->find($id);

        if (! is_array($existing)) {
            return redirect()->to('/admin/master/biaya/transportasi')->with('error', 'Data tidak ditemukan.');
        }

        $rules = [
            'provinsi_kode'  => 'required',
            'besaran'        => 'required|numeric',
            'berlaku_mulai'  => 'required|valid_date',
            'berlaku_hingga' => 'permit_empty|valid_date',
        ];

        $postData = $this->request->getPost();
        if (isset($postData['besaran'])) {
            $postData['besaran'] = str_replace('.', '', (string) $postData['besaran']);
        }

        if (! $this->validateData($postData, $rules)) {
            return redirect()->to('/admin/master/biaya/transportasi')->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $model->update($id, [
            'provinsi_kode'  => trim((string) $postData['provinsi_kode']),
            'berlaku_mulai'  => $postData['berlaku_mulai'],
            'berlaku_hingga' => $postData['berlaku_hingga'] ?: null,
            'is_active'      => (int) $postData['is_active'],
            'satuan'         => trim((string) $postData['satuan']) ?: 'Orang/Kali',
            'besaran'        => (int) $postData['besaran'],
            'updated_by'     => (string) (session()->get('username') ?? 'system'),
            'updated_date'  => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/admin/master/biaya/transportasi')->with('message', 'Data biaya transportasi berhasil diperbarui.');
    }

    public function transportasiDelete(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::LINK_TRANSPORTASI);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/biaya/transportasi')->with('error', 'Anda tidak memiliki akses untuk menghapus data.');
        }

        $model    = new MstBiayaTransportasiModel();
        $existing = $model->find($id);

        if (! is_array($existing)) {
            return redirect()->to('/admin/master/biaya/transportasi')->with('error', 'Data tidak ditemukan.');
        }

        $model->delete($id);
        return redirect()->to('/admin/master/biaya/transportasi')->with('message', 'Data biaya transportasi berhasil dihapus.');
    }

    // ============================================================
    // BIAYA PENGINAPAN
    // ============================================================

    public function penginapanIndex()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::LINK_PENGINAPAN);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $items = (new MstBiayaPenginapanModel())
            ->select('mst_biaya_penginapan.*, mst_provinsi.nama_provinsi')
            ->join('mst_provinsi', 'mst_provinsi.kode_provinsi = mst_biaya_penginapan.provinsi_kode', 'left')
            ->orderBy('mst_provinsi.nama_provinsi', 'ASC')
            ->findAll();

        $provinsis       = (new MstProvinsiModel())->orderBy('nama_provinsi', 'ASC')->findAll();
        $menuPermissions = $this->resolveMenuPermissions(self::LINK_PENGINAPAN);
        $canManage       = $this->canManageMasterData();

        return view('admin/master/biaya_penginapan', [
            'pageTitle'   => 'Biaya Penginapan',
            'items'       => $items,
            'provinsis'   => $provinsis,
            'can_add'     => $canManage && (bool) ($menuPermissions['add'] ?? false),
            'can_edit'    => $canManage && (bool) ($menuPermissions['edit'] ?? false),
            'can_delete'  => $canManage && (bool) ($menuPermissions['delete'] ?? false),
        ]);
    }

    public function penginapanCreate()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::LINK_PENGINAPAN);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/biaya/penginapan')->with('error', 'Anda tidak memiliki akses untuk menambah data.');
        }

        $rules = [
            'provinsi_kode'  => 'required',
            'tarif_eselon1'  => 'required|numeric',
            'tarif_eselon2'  => 'required|numeric',
            'tarif_eselon3'  => 'required|numeric',
            'tarif_eselon4'  => 'required|numeric',
            'berlaku_mulai'  => 'required|valid_date',
            'berlaku_hingga' => 'permit_empty|valid_date',
        ];

        $postData = $this->request->getPost();
        foreach (['tarif_eselon1', 'tarif_eselon2', 'tarif_eselon3', 'tarif_eselon4'] as $f) {
            if (isset($postData[$f])) {
                $postData[$f] = str_replace('.', '', (string) $postData[$f]);
            }
        }

        if (! $this->validateData($postData, $rules)) {
            return redirect()->to('/admin/master/biaya/penginapan')->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $now      = date('Y-m-d H:i:s');
        $username = (string) (session()->get('username') ?? 'system');

        (new MstBiayaPenginapanModel())->insert([
            'provinsi_kode'  => trim((string) $postData['provinsi_kode']),
            'berlaku_mulai'  => $postData['berlaku_mulai'],
            'berlaku_hingga' => $postData['berlaku_hingga'] ?: null,
            'is_active'      => (int) $postData['is_active'],
            'satuan'         => trim((string) $postData['satuan']) ?: 'OH',
            'tarif_eselon1'  => (int) $postData['tarif_eselon1'],
            'tarif_eselon2'  => (int) $postData['tarif_eselon2'],
            'tarif_eselon3'  => (int) $postData['tarif_eselon3'],
            'tarif_eselon4'  => (int) $postData['tarif_eselon4'],
            'created_by'     => $username,
            'created_date'  => $now,
            'updated_by'    => $username,
            'updated_date'  => $now,
        ]);

        return redirect()->to('/admin/master/biaya/penginapan')->with('message', 'Data biaya penginapan berhasil ditambahkan.');
    }

    public function penginapanEdit(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::LINK_PENGINAPAN);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/biaya/penginapan')->with('error', 'Anda tidak memiliki akses untuk mengubah data.');
        }

        $model    = new MstBiayaPenginapanModel();
        $existing = $model->find($id);

        if (! is_array($existing)) {
            return redirect()->to('/admin/master/biaya/penginapan')->with('error', 'Data tidak ditemukan.');
        }

        $rules = [
            'provinsi_kode'  => 'required',
            'tarif_eselon1'  => 'required|numeric',
            'tarif_eselon2'  => 'required|numeric',
            'tarif_eselon3'  => 'required|numeric',
            'tarif_eselon4'  => 'required|numeric',
            'berlaku_mulai'  => 'required|valid_date',
            'berlaku_hingga' => 'permit_empty|valid_date',
        ];

        $postData = $this->request->getPost();
        foreach (['tarif_eselon1', 'tarif_eselon2', 'tarif_eselon3', 'tarif_eselon4'] as $f) {
            if (isset($postData[$f])) {
                $postData[$f] = str_replace('.', '', (string) $postData[$f]);
            }
        }

        if (! $this->validateData($postData, $rules)) {
            return redirect()->to('/admin/master/biaya/penginapan')->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $model->update($id, [
            'provinsi_kode'  => trim((string) $postData['provinsi_kode']),
            'berlaku_mulai'  => $postData['berlaku_mulai'],
            'berlaku_hingga' => $postData['berlaku_hingga'] ?: null,
            'is_active'      => (int) $postData['is_active'],
            'satuan'         => trim((string) $postData['satuan']) ?: 'OH',
            'tarif_eselon1'  => (int) $postData['tarif_eselon1'],
            'tarif_eselon2'  => (int) $postData['tarif_eselon2'],
            'tarif_eselon3'  => (int) $postData['tarif_eselon3'],
            'tarif_eselon4'  => (int) $postData['tarif_eselon4'],
            'updated_by'     => (string) (session()->get('username') ?? 'system'),
            'updated_date'  => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/admin/master/biaya/penginapan')->with('message', 'Data biaya penginapan berhasil diperbarui.');
    }

    public function penginapanDelete(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::LINK_PENGINAPAN);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/biaya/penginapan')->with('error', 'Anda tidak memiliki akses untuk menghapus data.');
        }

        $model    = new MstBiayaPenginapanModel();
        $existing = $model->find($id);

        if (! is_array($existing)) {
            return redirect()->to('/admin/master/biaya/penginapan')->with('error', 'Data tidak ditemukan.');
        }

        $model->delete($id);
        return redirect()->to('/admin/master/biaya/penginapan')->with('message', 'Data biaya penginapan berhasil dihapus.');
    }

    // ============================================================
    // BIAYA HARIAN PERSONEL
    // ============================================================

    public function harianIndex()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::LINK_HARIAN);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $items = (new MstBiayaHarianModel())
            ->select('mst_biaya_harian.*, mst_provinsi.nama_provinsi')
            ->join('mst_provinsi', 'mst_provinsi.kode_provinsi = mst_biaya_harian.provinsi_kode', 'left')
            ->orderBy('mst_provinsi.nama_provinsi', 'ASC')
            ->findAll();

        $provinsis       = (new MstProvinsiModel())->orderBy('nama_provinsi', 'ASC')->findAll();
        $menuPermissions = $this->resolveMenuPermissions(self::LINK_HARIAN);
        $canManage       = $this->canManageMasterData();

        return view('admin/master/biaya_harian', [
            'pageTitle'   => 'Biaya Harian Personel',
            'items'       => $items,
            'provinsis'   => $provinsis,
            'can_add'     => $canManage && (bool) ($menuPermissions['add'] ?? false),
            'can_edit'    => $canManage && (bool) ($menuPermissions['edit'] ?? false),
            'can_delete'  => $canManage && (bool) ($menuPermissions['delete'] ?? false),
        ]);
    }

    public function harianCreate()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::LINK_HARIAN);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/biaya/harian')->with('error', 'Anda tidak memiliki akses untuk menambah data.');
        }

        $rules = [
            'provinsi_kode'  => 'required',
            'luar_kota'      => 'required|numeric',
            'dalam_kota'     => 'required|numeric',
            'diklat'         => 'required|numeric',
            'berlaku_mulai'  => 'required|valid_date',
            'berlaku_hingga' => 'permit_empty|valid_date',
        ];

        $postData = $this->request->getPost();
        foreach (['luar_kota', 'dalam_kota', 'diklat'] as $f) {
            if (isset($postData[$f])) {
                $postData[$f] = str_replace('.', '', (string) $postData[$f]);
            }
        }

        if (! $this->validateData($postData, $rules)) {
            return redirect()->to('/admin/master/biaya/harian')->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $now      = date('Y-m-d H:i:s');
        $username = (string) (session()->get('username') ?? 'system');

        (new MstBiayaHarianModel())->insert([
            'provinsi_kode'  => trim((string) $postData['provinsi_kode']),
            'berlaku_mulai'  => $postData['berlaku_mulai'],
            'berlaku_hingga' => $postData['berlaku_hingga'] ?: null,
            'is_active'      => (int) $postData['is_active'],
            'satuan'         => trim((string) $postData['satuan']) ?: 'OH',
            'luar_kota'      => (int) $postData['luar_kota'],
            'dalam_kota'     => (int) $postData['dalam_kota'],
            'diklat'         => (int) $postData['diklat'],
            'created_by'     => $username,
            'created_date'  => $now,
            'updated_by'    => $username,
            'updated_date'  => $now,
        ]);

        return redirect()->to('/admin/master/biaya/harian')->with('message', 'Data biaya harian personel berhasil ditambahkan.');
    }

    public function harianEdit(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::LINK_HARIAN);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/biaya/harian')->with('error', 'Anda tidak memiliki akses untuk mengubah data.');
        }

        $model    = new MstBiayaHarianModel();
        $existing = $model->find($id);

        if (! is_array($existing)) {
            return redirect()->to('/admin/master/biaya/harian')->with('error', 'Data tidak ditemukan.');
        }

        $rules = [
            'provinsi_kode'  => 'required',
            'luar_kota'      => 'required|numeric',
            'dalam_kota'     => 'required|numeric',
            'diklat'         => 'required|numeric',
            'berlaku_mulai'  => 'required|valid_date',
            'berlaku_hingga' => 'permit_empty|valid_date',
        ];

        $postData = $this->request->getPost();
        foreach (['luar_kota', 'dalam_kota', 'diklat'] as $f) {
            if (isset($postData[$f])) {
                $postData[$f] = str_replace('.', '', (string) $postData[$f]);
            }
        }

        if (! $this->validateData($postData, $rules)) {
            return redirect()->to('/admin/master/biaya/harian')->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $model->update($id, [
            'provinsi_kode'  => trim((string) $postData['provinsi_kode']),
            'berlaku_mulai'  => $postData['berlaku_mulai'],
            'berlaku_hingga' => $postData['berlaku_hingga'] ?: null,
            'is_active'      => (int) $postData['is_active'],
            'satuan'         => trim((string) $postData['satuan']) ?: 'OH',
            'luar_kota'      => (int) $postData['luar_kota'],
            'dalam_kota'     => (int) $postData['dalam_kota'],
            'diklat'         => (int) $postData['diklat'],
            'updated_by'     => (string) (session()->get('username') ?? 'system'),
            'updated_date'  => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/admin/master/biaya/harian')->with('message', 'Data biaya harian personel berhasil diperbarui.');
    }

    public function harianDelete(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::LINK_HARIAN);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/biaya/harian')->with('error', 'Anda tidak memiliki akses untuk menghapus data.');
        }

        $model    = new MstBiayaHarianModel();
        $existing = $model->find($id);

        if (! is_array($existing)) {
            return redirect()->to('/admin/master/biaya/harian')->with('error', 'Data tidak ditemukan.');
        }

        $model->delete($id);
        return redirect()->to('/admin/master/biaya/harian')->with('message', 'Data biaya harian personel berhasil dihapus.');
    }

    // ============================================================
    // PRIVATE HELPERS
    // ============================================================

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
        $default = ['add' => false, 'edit' => false, 'delete' => false, 'export' => false, 'import' => false, 'approval' => false];

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
            if (in_array($normalized, ['super administrator', 'super_administrator', 'super-admin', 'superadmin'], true)) {
                $variants = ['super administrator', 'super_administrator', 'super-admin', 'superadmin'];
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
