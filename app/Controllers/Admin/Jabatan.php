<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MstJabatanModel;
use CodeIgniter\HTTP\RedirectResponse;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
            'can_import' => $canManage && (bool) ($menuPermissions['import'] ?? false),
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

    public function downloadTemplate()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! $this->canManageMasterData() || ! (bool) ($menuPermissions['import'] ?? false)) {
            return redirect()->to('/admin/master/jabatan')->with('error', 'Anda tidak memiliki akses untuk mengunduh template import.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Jabatan');

        $sheet->setCellValue('A1', 'jabatan');
        $sheet->setCellValue('B1', 'jenis_jabatan');
        $sheet->setCellValue('C1', 'deskripsi_jabatan');
        $sheet->setCellValue('D1', 'status');

        $sheet->setCellValue('A2', 'Contoh Jabatan');
        $sheet->setCellValue('B2', 'Fungsional');
        $sheet->setCellValue('C2', 'Deskripsi singkat jabatan');
        $sheet->setCellValue('D2', 'aktif');

        foreach (['A', 'B', 'C', 'D'] as $col) {
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
        }
        $sheet->getColumnDimension('A')->setWidth(35);
        $sheet->getColumnDimension('B')->setWidth(22);
        $sheet->getColumnDimension('C')->setWidth(50);
        $sheet->getColumnDimension('D')->setWidth(14);

        $tmpFile = tempnam(sys_get_temp_dir(), 'jabatan_template_');
        if ($tmpFile === false) {
            return redirect()->to('/admin/master/jabatan')->with('error', 'Gagal menyiapkan file template.');
        }

        $xlsxFile = $tmpFile . '.xlsx';
        @rename($tmpFile, $xlsxFile);

        $writer = new Xlsx($spreadsheet);
        $writer->save($xlsxFile);

        return $this->response
            ->download($xlsxFile, null)
            ->setFileName('template_import_jabatan.xlsx');
    }

    public function import()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/jabatan')->with('error', 'Anda tidak memiliki akses untuk import data jabatan.');
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! (bool) ($menuPermissions['import'] ?? false)) {
            return redirect()->to('/admin/master/jabatan')->with('error', 'Anda tidak memiliki izin import pada menu Jabatan.');
        }

        $file = $this->request->getFile('file_excel');
        if (! $file || ! $file->isValid()) {
            return redirect()->to('/admin/master/jabatan')->with('error', 'File import tidak valid.');
        }

        $ext = strtolower((string) $file->getExtension());
        if (! in_array($ext, ['xls', 'xlsx'], true)) {
            return redirect()->to('/admin/master/jabatan')->with('error', 'Format file harus .xls atau .xlsx.');
        }

        try {
            $spreadsheet = IOFactory::load($file->getTempName());
        } catch (\Throwable $e) {
            return redirect()->to('/admin/master/jabatan')->with('error', 'File Excel gagal dibaca. Pastikan format file valid.');
        }

        $rows = $spreadsheet->getActiveSheet()->toArray('', true, true, true);
        if ($rows === []) {
            return redirect()->to('/admin/master/jabatan')->with('error', 'File Excel kosong.');
        }

        $headerRow = array_shift($rows);
        if (! is_array($headerRow) || $headerRow === []) {
            return redirect()->to('/admin/master/jabatan')->with('error', 'Header Excel tidak ditemukan.');
        }

        $headers = [];
        foreach ($headerRow as $column => $name) {
            $normalized = $this->normalizeExcelHeader((string) $name);
            if ($normalized !== '') {
                $headers[$column] = $normalized;
            }
        }

        if ($headers === []) {
            return redirect()->to('/admin/master/jabatan')->with('error', 'Header Excel tidak dikenali.');
        }

        $requiredHeaders = ['jabatan', 'jenis_jabatan'];
        foreach ($requiredHeaders as $requiredHeader) {
            if (! in_array($requiredHeader, array_values($headers), true)) {
                return redirect()->to('/admin/master/jabatan')->with('error', 'Kolom wajib tidak ditemukan: ' . $requiredHeader . '.');
            }
        }

        $allowedJenis = ['fungsional', 'perbendaharaan', 'pelaksana'];
        $model = new MstJabatanModel();
        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        $now = date('Y-m-d H:i:s');
        $username = (string) (session()->get('username') ?? 'system');

        foreach ($rows as $row) {
            if (! is_array($row)) {
                $skipped++;
                continue;
            }

            $rowData = [];
            foreach ($headers as $column => $headerName) {
                $rowData[$headerName] = trim((string) ($row[$column] ?? ''));
            }

            $jabatan = trim((string) ($rowData['jabatan'] ?? ''));
            $jenis = strtolower(trim((string) ($rowData['jenis_jabatan'] ?? '')));
            $deskripsi = trim((string) ($rowData['deskripsi_jabatan'] ?? ''));
            $statusRaw = strtolower(trim((string) ($rowData['status'] ?? '')));

            if ($jabatan === '' || $jenis === '' || ! in_array($jenis, $allowedJenis, true)) {
                $skipped++;
                continue;
            }

            $jenisLabel = ucfirst($jenis);
            if ($jenis === 'perbendaharaan') {
                $jenisLabel = 'Perbendaharaan';
            }

            $isActive = 1;
            if ($statusRaw !== '') {
                if (in_array($statusRaw, ['0', 'nonaktif', 'non-active', 'inactive'], true)) {
                    $isActive = 0;
                } elseif (in_array($statusRaw, ['1', 'aktif', 'active'], true)) {
                    $isActive = 1;
                }
            }

            $existing = $model
                ->where('LOWER(jabatan)', strtolower($jabatan))
                ->first();

            if (is_array($existing)) {
                $model->update((int) $existing['id'], [
                    'jabatan' => $jabatan,
                    'jenis_jabatan' => $jenisLabel,
                    'deskripsi_jabatan' => $deskripsi === '' ? null : $deskripsi,
                    'is_active' => $isActive,
                    'updated_by' => $username,
                    'updated_date' => $now,
                ]);
                $updated++;
                continue;
            }

            $model->insert([
                'jabatan' => $jabatan,
                'jenis_jabatan' => $jenisLabel,
                'deskripsi_jabatan' => $deskripsi === '' ? null : $deskripsi,
                'is_active' => $isActive,
                'created_by' => $username,
                'created_date' => $now,
                'updated_by' => $username,
                'updated_date' => $now,
            ]);
            $inserted++;
        }

        return redirect()->to('/admin/master/jabatan')->with('message', 'Import selesai. Data baru: ' . $inserted . ', diperbarui: ' . $updated . ', dilewati: ' . $skipped . '.');
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

    private function normalizeExcelHeader(string $value): string
    {
        $header = strtolower(trim($value));
        $header = str_replace(['-', '/', ' '], '_', $header);
        $header = preg_replace('/[^a-z0-9_]/', '', $header) ?? $header;

        return match ($header) {
            'jabatan', 'nama_jabatan', 'nama' => 'jabatan',
            'jenis_jabatan', 'jenis' => 'jenis_jabatan',
            'deskripsi_jabatan', 'deskripsi', 'keterangan' => 'deskripsi_jabatan',
            'status', 'is_active', 'aktif' => 'status',
            default => $header,
        };
    }
}