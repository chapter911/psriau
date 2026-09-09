<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MstSekolahModel;
use App\Models\MstPaketModel;
use CodeIgniter\HTTP\RedirectResponse;

class InventarisSekolah extends BaseController
{
    private const MENU_LINK = 'admin/inventaris/sekolah';

    public function index()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $db = db_connect();
        $builder = $db->table('mst_sekolah s')
            ->select('s.npsn, s.nama, s.jenis, s.nsm, s.kabupaten, s.kecamatan, s.paket_id, mp.nama_paket AS nama_paket')
            ->join('mst_paket mp', 'mp.id = s.paket_id', 'left');

        $filterPaketId   = trim((string) $this->request->getGet('paket_id'));
        $filterKabupaten = trim((string) $this->request->getGet('kabupaten'));
        $keyword         = trim((string) $this->request->getGet('keyword'));

        if ($filterPaketId !== '' && $filterPaketId !== '*') {
            $builder->where('s.paket_id', (int) $filterPaketId);
        }
        if ($filterKabupaten !== '' && $filterKabupaten !== '*') {
            $builder->where('s.kabupaten', $filterKabupaten);
        }
        if ($keyword !== '') {
            $builder->groupStart()
                ->like('s.nama', $keyword)
                ->orLike('s.npsn', $keyword)
                ->orLike('s.kecamatan', $keyword)
                ->groupEnd();
        }

        $items = $builder->orderBy('s.nama', 'ASC')->get()->getResultArray();

        $paketModel = new MstPaketModel();
        $pakets = $paketModel->where('is_active', 1)->orderBy('nama_paket', 'ASC')->findAll();

        $kabupatenRows = $db->table('mst_sekolah')
            ->select('kabupaten')
            ->distinct()
            ->where('kabupaten IS NOT NULL', null, false)
            ->where('kabupaten !=', '')
            ->orderBy('kabupaten', 'ASC')
            ->get()
            ->getResultArray();

        $totalSekolah = $db->table('mst_sekolah')->countAllResults();
        $totalPaket   = count($pakets);

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);

        return view('admin/inventaris/sekolah/index', [
            'pageTitle'        => 'Inventaris Sarpras Sekolah',
            'items'            => $items,
            'pakets'           => $pakets,
            'kabupatens'       => array_column($kabupatenRows, 'kabupaten'),
            'filterPaketId'    => $filterPaketId,
            'filterKabupaten'  => $filterKabupaten,
            'keyword'          => $keyword,
            'totalSekolah'     => $totalSekolah,
            'totalPaket'       => $totalPaket,
            'can_add'          => (bool) ($menuPermissions['add'] ?? false),
            'can_edit'         => (bool) ($menuPermissions['edit'] ?? false),
            'can_export'       => (bool) ($menuPermissions['export'] ?? false),
        ]);
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

        return null;
    }

    private function resolveMenuIdByLink(string $menuLink, $db): ?string
    {
        $normalized = trim(strtolower($menuLink), '/');

        if ($db->tableExists('menu_lv2')) {
            $row = $db->table('menu_lv2')
                ->select('id')
                ->where('LOWER(TRIM(link))', $normalized)
                ->get()
                ->getRowArray();

            if (is_array($row) && isset($row['id'])) {
                return (string) $row['id'];
            }
        }

        if ($db->tableExists('menu_lv1')) {
            $row = $db->table('menu_lv1')
                ->select('id')
                ->where('LOWER(TRIM(link))', $normalized)
                ->get()
                ->getRowArray();

            if (is_array($row) && isset($row['id'])) {
                return (string) $row['id'];
            }
        }

        return null;
    }
}
