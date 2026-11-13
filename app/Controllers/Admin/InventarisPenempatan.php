<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;

class InventarisPenempatan extends BaseController
{
    private const MENU_LINK = 'admin/inventaris/penempatan';

    public function index()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $db = db_connect();

        $filterPaketId   = trim((string) $this->request->getGet('paket_id'));
        $filterSekolahId = trim((string) $this->request->getGet('sekolah_id'));
        $filterStatus    = trim((string) $this->request->getGet('status'));
        $keyword         = trim((string) $this->request->getGet('keyword'));

        $builder = $db->table('trn_inventaris_satker i')
            ->select('i.id, i.kode_register, i.kode_barang, i.nup, i.nama_barang, i.merk_tipe, i.tahun_perolehan, i.kondisi,
                      COALESCE(i.status_penempatan, "belum_ditempatkan") AS status_penempatan,
                      i.sekolah_id, s.nama AS nama_sekolah, s.paket_id, mp.nama_paket,
                      i.gedung_id, g.nama_gedung,
                      i.ruangan_id, r.nama_ruangan,
                      i.tanggal_penempatan, i.petugas_penempatan')
            ->join('mst_sekolah s', 's.npsn = i.sekolah_id', 'left')
            ->join('mst_paket mp', 'mp.id = s.paket_id', 'left')
            ->join('mst_gedung g', 'g.id = i.gedung_id', 'left')
            ->join('mst_ruangan r', 'r.id = i.ruangan_id', 'left');

        if ($filterPaketId !== '' && $filterPaketId !== '*') {
            $builder->where('s.paket_id', (int) $filterPaketId);
        }
        if ($filterSekolahId !== '' && $filterSekolahId !== '*') {
            $builder->where('i.sekolah_id', (int) $filterSekolahId);
        }
        if ($filterStatus !== '' && $filterStatus !== '*') {
            $builder->where('i.status_penempatan', $filterStatus);
        }
        if ($keyword !== '') {
            $builder->groupStart()
                ->like('i.nama_barang', $keyword)
                ->orLike('i.kode_barang', $keyword)
                ->orLike('i.nup', $keyword)
                ->orLike('i.kode_register', $keyword)
                ->orLike('i.merk_tipe', $keyword)
                ->orLike('s.nama', $keyword)
                ->orLike('g.nama_gedung', $keyword)
                ->orLike('r.nama_ruangan', $keyword)
                ->groupEnd();
        }

        $items = $builder->orderBy('i.id', 'DESC')->get()->getResultArray();

        // Ambil foto aktif untuk setiap item
        $itemIds = array_filter(array_column($items, 'id'));
        $fotoMap = [];
        if (! empty($itemIds)) {
            $penempatanRows = $db->table('trn_bmn_penempatan')
                ->whereIn('inventaris_id', $itemIds)
                ->where('status', 'aktif')
                ->orderBy('id', 'DESC')
                ->get()
                ->getResultArray();

            foreach ($penempatanRows as $p) {
                $invId = (int) $p['inventaris_id'];
                if (! isset($fotoMap[$invId])) {
                    $fotoMap[$invId] = $p;
                }
            }
        }

        foreach ($items as &$item) {
            $invId = (int) $item['id'];
            $item['penempatan'] = $fotoMap[$invId] ?? null;
        }

        $pakets = $db->table('mst_paket')->where('is_active', 1)->orderBy('nama_paket', 'ASC')->get()->getResultArray();
        $sekolahs = $db->table('mst_sekolah')->orderBy('nama', 'ASC')->get()->getResultArray();

        $totalAset  = (int) $db->table('trn_inventaris_satker')->countAllResults();
        $totalSudah = (int) $db->table('trn_inventaris_satker')->where('status_penempatan', 'sudah_ditempatkan')->countAllResults();
        $totalBelum = (int) $db->table('trn_inventaris_satker')->where('status_penempatan !=', 'sudah_ditempatkan')->countAllResults();

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);

        return view('admin/inventaris/penempatan/index', [
            'pageTitle'       => 'Penempatan & Distribusi Aset BMN',
            'items'           => $items,
            'pakets'          => $pakets,
            'sekolahs'        => $sekolahs,
            'filterPaketId'   => $filterPaketId,
            'filterSekolahId' => $filterSekolahId,
            'filterStatus'    => $filterStatus,
            'keyword'         => $keyword,
            'totalAset'       => $totalAset,
            'totalSudah'      => $totalSudah,
            'totalBelum'      => $totalBelum,
            'can_add'         => (bool) ($menuPermissions['add'] ?? false),
            'can_edit'        => (bool) ($menuPermissions['edit'] ?? false),
            'can_delete'      => (bool) ($menuPermissions['delete'] ?? false),
            'can_export'      => (bool) ($menuPermissions['export'] ?? false),
        ]);
    }

    public function createGedung()
    {
        $sekolahId  = (int) $this->request->getPost('sekolah_id');
        $namaGedung = trim((string) $this->request->getPost('nama_gedung'));

        if ($sekolahId > 0 && $namaGedung !== '') {
            $db = db_connect();
            $db->table('mst_gedung')->insert([
                'sekolah_id'  => $sekolahId,
                'nama_gedung' => $namaGedung,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);
            session()->setFlashdata('success', 'Gedung baru berhasil ditambahkan.');
        } else {
            session()->setFlashdata('error', 'Gagal menambahkan gedung. Data tidak lengkap.');
        }

        return redirect()->back();
    }

    public function createRuangan()
    {
        $gedungId    = (int) $this->request->getPost('gedung_id');
        $namaRuangan = trim((string) $this->request->getPost('nama_ruangan'));

        if ($gedungId > 0 && $namaRuangan !== '') {
            $db = db_connect();
            $gedung = $db->table('mst_gedung')->where('id', $gedungId)->get()->getRowArray();
            $sekolahId = $gedung ? (int) $gedung['sekolah_id'] : null;

            $db->table('mst_ruangan')->insert([
                'kode_ruangan' => 'R-' . time(),
                'nama_ruangan' => $namaRuangan,
                'gedung_id'    => $gedungId,
                'sekolah_id'   => $sekolahId,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);
            session()->setFlashdata('success', 'Ruangan baru berhasil ditambahkan.');
        } else {
            session()->setFlashdata('error', 'Gagal menambahkan ruangan. Data tidak lengkap.');
        }

        return redirect()->back();
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
            return true;
        }

        $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';
        $row = $db->table('menu_akses')
            ->where($roleColumn, $roleId)
            ->where('menu_id', $menuId)
            ->get()
            ->getRowArray();

        return $row !== null;
    }

    private function resolveMenuPermissions(string $menuLink): array
    {
        $db = db_connect();
        if (! $db->tableExists('menu_akses')) {
            return ['add' => true, 'edit' => true, 'delete' => true, 'export' => true];
        }

        $roleId = $this->resolveRoleId((string) session()->get('role'), $db);
        if ($roleId === null) {
            return ['add' => false, 'edit' => false, 'delete' => false, 'export' => false];
        }

        $menuId = $this->resolveMenuIdByLink($menuLink, $db);
        if ($menuId === null) {
            return ['add' => true, 'edit' => true, 'delete' => true, 'export' => true];
        }

        $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';
        $row = $db->table('menu_akses')
            ->where($roleColumn, $roleId)
            ->where('menu_id', $menuId)
            ->get()
            ->getRowArray();

        return [
            'add'    => ! empty($row['FiturAdd']),
            'edit'   => ! empty($row['FiturEdit']),
            'delete' => ! empty($row['FiturDelete']),
            'export' => ! empty($row['FiturExport']),
        ];
    }

    private function resolveRoleId(string $roleName, $db): ?int
    {
        $roleName = trim($roleName);
        if ($roleName === '') {
            return null;
        }

        if ($db->tableExists('access_roles')) {
            $row = $db->table('access_roles')
                ->select('id')
                ->where('role_name', $roleName)
                ->get()
                ->getRowArray();
            if ($row) {
                return (int) $row['id'];
            }
        }

        return in_array(strtolower($roleName), ['admin', 'administrator', 'superadmin'], true) ? 1 : 2;
    }

    private function resolveMenuIdByLink(string $menuLink, $db): ?string
    {
        foreach (['menu_lv3', 'menu_lv2', 'menu_lv1'] as $table) {
            if (! $db->tableExists($table)) {
                continue;
            }
            $row = $db->table($table)
                ->select('id')
                ->where('link', $menuLink)
                ->get()
                ->getRowArray();
            if ($row) {
                return (string) $row['id'];
            }
        }
        return null;
    }
}
