<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\InventarisPengaturanModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

class InventarisPengaturan extends BaseController
{
    private const MENU_LINK = 'admin/inventaris/pengaturan';

    protected InventarisPengaturanModel $pengaturanModel;

    public function __construct()
    {
        $this->pengaturanModel = new InventarisPengaturanModel();
    }

    public function index()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $db = \Config\Database::connect();

        $kopSuratList = $this->pengaturanModel->getKopSuratList();
        $kasatkerList = $this->pengaturanModel->getKasatkerList();
        $delegasi     = $this->pengaturanModel->getDelegasi();

        // Ambil daftar pegawai untuk pilihan delegasi
        $pegawaiList = [];
        if ($db->tableExists('mst_pegawai')) {
            $pegawaiList = $db->table('mst_pegawai')
                ->select('mst_pegawai.id, mst_pegawai.nama, mst_pegawai.nip, ju.jabatan AS jabatan_label')
                ->join('mst_jabatan ju', 'ju.id = mst_pegawai.jabatan_utama_id', 'left')
                ->orderBy('mst_pegawai.nama', 'ASC')
                ->get()
                ->getResultArray();
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);

        return view('admin/inventaris/pengaturan/index', [
            'pageTitle'       => 'Pengaturan Dokumen & Pejabat BMN',
            'kopSuratList'    => $kopSuratList,
            'kasatkerList'    => $kasatkerList,
            'delegasi'        => $delegasi,
            'pegawaiList'     => $pegawaiList,
            'menuPermissions' => $menuPermissions,
        ]);
    }

    // ==========================================
    // ACTIONS: KOP SURAT INVENTARIS
    // ==========================================

    public function saveKopSurat()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        $id = (int) $this->request->getPost('id');

        if ($id > 0 && ! ($menuPermissions['edit'] ?? false)) {
            return redirect()->to(site_url(self::MENU_LINK))->with('error', 'Anda tidak memiliki hak akses untuk mengubah data.');
        }
        if ($id <= 0 && ! ($menuPermissions['add'] ?? false)) {
            return redirect()->to(site_url(self::MENU_LINK))->with('error', 'Anda tidak memiliki hak akses untuk menambah data.');
        }

        $db = \Config\Database::connect();
        $existing = $id > 0 ? $this->pengaturanModel->getKopSuratById($id) : null;

        $imagePath = $this->uploadImage('image_file', 'kop_surat_inventaris');
        if ($imagePath === null && $id <= 0) {
            return redirect()->back()->withInput()->with('error', 'File gambar Kop Surat wajib diunggah.');
        }

        if ($imagePath !== null && is_array($existing)) {
            $this->deleteLocalImage($existing['image_url'] ?? null);
        }

        $payload = [
            'nama_kop'       => trim((string) $this->request->getPost('nama_kop')),
            'image_url'      => $imagePath ?? ($existing['image_url'] ?? ''),
            'berlaku_dari'   => $this->request->getPost('berlaku_dari') ?: null,
            'berlaku_sampai' => $this->request->getPost('berlaku_sampai') ?: null,
            'is_active'      => (int) ($this->request->getPost('is_active') ? 1 : 0),
            'keterangan'     => trim((string) $this->request->getPost('keterangan')),
            'updated_at'     => date('Y-m-d H:i:s'),
        ];

        if (empty($payload['nama_kop'])) {
            return redirect()->back()->withInput()->with('error', 'Nama Kop Surat wajib diisi.');
        }

        if ($id > 0) {
            $db->table('cfg_inventaris_kop_surat')->where('id', $id)->update($payload);
            $msg = 'Kop Surat BMN berhasil diperbarui.';
        } else {
            $payload['created_at'] = date('Y-m-d H:i:s');
            $db->table('cfg_inventaris_kop_surat')->insert($payload);
            $msg = 'Kop Surat BMN baru berhasil ditambahkan.';
        }

        return redirect()->to(site_url(self::MENU_LINK . '?tab=kop'))->with('success', $msg);
    }

    public function deleteKopSurat(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! ($menuPermissions['delete'] ?? false)) {
            return redirect()->to(site_url(self::MENU_LINK))->with('error', 'Anda tidak memiliki hak akses untuk menghapus data.');
        }

        $db = \Config\Database::connect();
        $item = $this->pengaturanModel->getKopSuratById($id);

        if ($item) {
            $this->deleteLocalImage($item['image_url'] ?? null);
            $db->table('cfg_inventaris_kop_surat')->where('id', $id)->delete();
        }

        return redirect()->to(site_url(self::MENU_LINK . '?tab=kop'))->with('success', 'Kop Surat BMN berhasil dihapus.');
    }

    // ==========================================
    // ACTIONS: RIWAYAT KASATKER
    // ==========================================

    public function saveKasatker()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        $id = (int) $this->request->getPost('id');

        if ($id > 0 && ! ($menuPermissions['edit'] ?? false)) {
            return redirect()->to(site_url(self::MENU_LINK))->with('error', 'Anda tidak memiliki hak akses untuk mengubah data.');
        }
        if ($id <= 0 && ! ($menuPermissions['add'] ?? false)) {
            return redirect()->to(site_url(self::MENU_LINK))->with('error', 'Anda tidak memiliki hak akses untuk menambah data.');
        }

        $db = \Config\Database::connect();

        $payload = [
            'nama'            => trim((string) $this->request->getPost('nama')),
            'nip'             => trim((string) $this->request->getPost('nip')),
            'jabatan'         => trim((string) $this->request->getPost('jabatan')) ?: 'Kepala Satuan Kerja Pelaksanaan Prasarana Strategis Riau selaku Kuasa Pengguna Barang',
            'periode_mulai'   => $this->request->getPost('periode_mulai') ?: null,
            'periode_selesai' => $this->request->getPost('periode_selesai') ?: null,
            'status_jabatan'  => in_array($this->request->getPost('status_jabatan'), ['definitif', 'plt', 'plh'], true) ? $this->request->getPost('status_jabatan') : 'definitif',
            'is_active'       => (int) ($this->request->getPost('is_active') ? 1 : 0),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        if (empty($payload['nama'])) {
            return redirect()->back()->withInput()->with('error', 'Nama Pejabat Kasatker wajib diisi.');
        }

        if ($id > 0) {
            $db->table('cfg_inventaris_kasatker')->where('id', $id)->update($payload);
            $msg = 'Data riwayat Kasatker berhasil diperbarui.';
        } else {
            $payload['created_at'] = date('Y-m-d H:i:s');
            $db->table('cfg_inventaris_kasatker')->insert($payload);
            $msg = 'Pejabat Kasatker baru berhasil ditambahkan.';
        }

        return redirect()->to(site_url(self::MENU_LINK . '?tab=kasatker'))->with('success', $msg);
    }

    public function deleteKasatker(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! ($menuPermissions['delete'] ?? false)) {
            return redirect()->to(site_url(self::MENU_LINK))->with('error', 'Anda tidak memiliki hak akses untuk menghapus data.');
        }

        $db = \Config\Database::connect();
        $db->table('cfg_inventaris_kasatker')->where('id', $id)->delete();

        return redirect()->to(site_url(self::MENU_LINK . '?tab=kasatker'))->with('success', 'Riwayat Kasatker berhasil dihapus.');
    }

    // ==========================================
    // ACTIONS: DELEGASI PENANDATANGAN BMN
    // ==========================================

    public function saveDelegasi()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! ($menuPermissions['edit'] ?? false)) {
            return redirect()->to(site_url(self::MENU_LINK))->with('error', 'Anda tidak memiliki hak akses untuk mengubah delegasi.');
        }

        $db = \Config\Database::connect();

        $payload = [
            'pegawai_id'         => (int) $this->request->getPost('pegawai_id') ?: null,
            'nama'               => trim((string) $this->request->getPost('nama')),
            'nip'                => trim((string) $this->request->getPost('nip')),
            'jabatan_struktural' => trim((string) $this->request->getPost('jabatan_struktural')),
            'jabatan_bmn'        => trim((string) $this->request->getPost('jabatan_bmn')) ?: 'Pengurus Barang Pengguna',
            'format_ttd'         => trim((string) $this->request->getPost('format_ttd')),
            'is_active'          => (int) ($this->request->getPost('is_active') ? 1 : 0),
            'updated_at'         => date('Y-m-d H:i:s'),
        ];

        if (empty($payload['nama'])) {
            return redirect()->back()->withInput()->with('error', 'Nama Pejabat Delegasi BMN wajib diisi.');
        }

        $existing = $db->table('cfg_inventaris_delegasi')->get()->getRowArray();
        if ($existing) {
            $db->table('cfg_inventaris_delegasi')->where('id', $existing['id'])->update($payload);
        } else {
            $payload['created_at'] = date('Y-m-d H:i:s');
            $db->table('cfg_inventaris_delegasi')->insert($payload);
        }

        return redirect()->to(site_url(self::MENU_LINK . '?tab=delegasi'))->with('success', 'Pengaturan Delegasi Penandatangan BMN berhasil disimpan.');
    }

    // ==========================================
    // AJAX RESOLVER ENDPOINTS
    // ==========================================

    public function ajaxCheckConflict(): ResponseInterface
    {
        $peminjamNama  = trim((string) $this->request->getGet('nama'));
        $peminjamNip   = trim((string) $this->request->getGet('nip'));
        $tanggalPinjam = trim((string) $this->request->getGet('tanggal_pinjam'));

        $resolved = $this->pengaturanModel->resolvePihakPertama($peminjamNama, $peminjamNip, $tanggalPinjam);
        $matchedKop = $this->pengaturanModel->getKopSuratByDate($tanggalPinjam);

        return $this->response->setJSON([
            'is_delegasi' => $resolved['is_delegasi'],
            'resolved'    => $resolved,
            'kop_surat'   => $matchedKop,
        ]);
    }

    // ==========================================
    // HELPERS
    // ==========================================

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

    // ==========================================
    // MENU ACCESS & PERMISSIONS (User Rule 4)
    // ==========================================

    private function denyIfNoMenuAccess(string $menuLink): ?RedirectResponse
    {
        if ($this->hasMenuAccess($menuLink)) {
            return null;
        }

        return redirect()->to('/forbidden?from=' . rawurlencode($menuLink));
    }

    private function hasMenuAccess(string $menuLink): bool
    {
        $db = \Config\Database::connect();
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

        $db = \Config\Database::connect();
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

        foreach (['menu_lv3', 'menu_lv2', 'menu_lv1'] as $table) {
            if (! $db->tableExists($table)) {
                continue;
            }

            $row = $db->table($table)
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
