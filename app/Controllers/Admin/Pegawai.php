<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MstJabatanModel;
use App\Models\MstPegawaiModel;
use CodeIgniter\HTTP\RedirectResponse;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Pegawai extends BaseController
{
    private const MENU_LINK = 'admin/master/pegawai';

    public function index()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        $canManage = $this->canManageMasterData();
        $jabatanOptions = $this->resolveJabatanOptions();

        $items = [];
        if ($this->isPegawaiTableReady()) {
            $items = db_connect()
                ->table('mst_pegawai p')
                ->select('p.*, ju.jabatan AS jabatan_utama_label, jp.jabatan AS jabatan_perbendaharaan_label')
                ->join('mst_jabatan ju', 'ju.id = p.jabatan_utama_id', 'left')
                ->join('mst_jabatan jp', 'jp.id = p.jabatan_perbendaharaan_id', 'left')
                ->orderBy('p.nama', 'ASC')
                ->get()
                ->getResultArray();
        }

        return view('admin/master/pegawai', [
            'pageTitle' => 'Master Pegawai',
            'items' => $items,
            'jabatan_utama_options' => $jabatanOptions['utama'],
            'jabatan_perbendaharaan_options' => $jabatanOptions['perbendaharaan'],
            'can_add' => $canManage && (bool) ($menuPermissions['add'] ?? false),
            'can_edit' => $canManage && (bool) ($menuPermissions['edit'] ?? false),
            'can_import' => $canManage && (bool) ($menuPermissions['import'] ?? false),
            'table_ready' => $this->isPegawaiTableReady(),
        ]);
    }

    public function create()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->isPegawaiTableReady()) {
            return redirect()->to('/admin/master/pegawai')->with('error', 'Tabel pegawai belum tersedia. Jalankan migration.');
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/pegawai')->with('error', 'Anda tidak memiliki akses untuk menambah data pegawai.');
        }

        $rules = [
            'nip' => 'required|max_length[30]',
            'nama' => 'required|max_length[150]',
            'jabatan_utama_id' => 'required|integer',
            'jabatan_perbendaharaan_id' => 'permit_empty|integer',
            'eselon' => 'permit_empty|max_length[50]',
            'golongan' => 'permit_empty|max_length[50]',
            'masa_kerja' => 'permit_empty|max_length[50]',
            'is_active' => 'required|in_list[0,1]',
            'foto' => 'if_exist|is_image[foto]|max_size[foto,2048]|mime_in[foto,image/jpg,image/jpeg,image/png,image/webp]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/admin/master/pegawai')->withInput()->with('error', 'Data pegawai belum valid.');
        }

        $model = new MstPegawaiModel();
        $nip = trim((string) $this->request->getPost('nip'));
        if ($model->where('nip', $nip)->countAllResults() > 0) {
            return redirect()->to('/admin/master/pegawai')->withInput()->with('error', 'NIP sudah terdaftar.');
        }

        $jabatanOptions = $this->resolveJabatanOptions();
        $jabatanUtamaId = (int) $this->request->getPost('jabatan_utama_id');
        $jabatanPerbendaharaanId = (int) ($this->request->getPost('jabatan_perbendaharaan_id') ?: 0);

        if (! isset($jabatanOptions['utama_lookup'][$jabatanUtamaId])) {
            return redirect()->to('/admin/master/pegawai')->withInput()->with('error', 'Jabatan utama tidak valid.');
        }

        if ($jabatanPerbendaharaanId > 0 && ! isset($jabatanOptions['perbendaharaan_lookup'][$jabatanPerbendaharaanId])) {
            return redirect()->to('/admin/master/pegawai')->withInput()->with('error', 'Jabatan perbendaharaan tidak valid.');
        }

        $fotoPath = $this->handleFotoUpload('foto', null);

        $now = date('Y-m-d H:i:s');
        $username = (string) (session()->get('username') ?? 'system');

        $model->insert([
            'nip' => $nip,
            'nama' => trim((string) $this->request->getPost('nama')),
            'foto' => $fotoPath,
            'jabatan_utama_id' => $jabatanUtamaId,
            'jabatan_perbendaharaan_id' => $jabatanPerbendaharaanId > 0 ? $jabatanPerbendaharaanId : null,
            'eselon' => $this->nullableString($this->request->getPost('eselon')),
            'golongan' => $this->nullableString($this->request->getPost('golongan')),
            'masa_kerja' => $this->nullableString($this->request->getPost('masa_kerja')),
            'is_active' => (int) $this->request->getPost('is_active'),
            'created_by' => $username,
            'created_date' => $now,
            'updated_by' => $username,
            'updated_date' => $now,
        ]);

        return redirect()->to('/admin/master/pegawai')->with('message', 'Data pegawai berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->isPegawaiTableReady()) {
            return redirect()->to('/admin/master/pegawai')->with('error', 'Tabel pegawai belum tersedia.');
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/pegawai')->with('error', 'Anda tidak memiliki akses untuk mengubah data pegawai.');
        }

        $rules = [
            'nip' => 'required|max_length[30]',
            'nama' => 'required|max_length[150]',
            'jabatan_utama_id' => 'required|integer',
            'jabatan_perbendaharaan_id' => 'permit_empty|integer',
            'eselon' => 'permit_empty|max_length[50]',
            'golongan' => 'permit_empty|max_length[50]',
            'masa_kerja' => 'permit_empty|max_length[50]',
            'is_active' => 'required|in_list[0,1]',
            'foto' => 'if_exist|is_image[foto]|max_size[foto,2048]|mime_in[foto,image/jpg,image/jpeg,image/png,image/webp]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/admin/master/pegawai')->withInput()->with('error', 'Data pegawai belum valid.');
        }

        $model = new MstPegawaiModel();
        $existing = $model->find($id);
        if (! is_array($existing)) {
            return redirect()->to('/admin/master/pegawai')->with('error', 'Data pegawai tidak ditemukan.');
        }

        $nip = trim((string) $this->request->getPost('nip'));
        $duplicate = $model->where('nip', $nip)->where('id !=', $id)->countAllResults();
        if ($duplicate > 0) {
            return redirect()->to('/admin/master/pegawai')->withInput()->with('error', 'NIP sudah digunakan oleh data lain.');
        }

        $jabatanOptions = $this->resolveJabatanOptions();
        $jabatanUtamaId = (int) $this->request->getPost('jabatan_utama_id');
        $jabatanPerbendaharaanId = (int) ($this->request->getPost('jabatan_perbendaharaan_id') ?: 0);

        if (! isset($jabatanOptions['utama_lookup'][$jabatanUtamaId])) {
            return redirect()->to('/admin/master/pegawai')->withInput()->with('error', 'Jabatan utama tidak valid.');
        }

        if ($jabatanPerbendaharaanId > 0 && ! isset($jabatanOptions['perbendaharaan_lookup'][$jabatanPerbendaharaanId])) {
            return redirect()->to('/admin/master/pegawai')->withInput()->with('error', 'Jabatan perbendaharaan tidak valid.');
        }

        $fotoPath = $this->handleFotoUpload('foto', (string) ($existing['foto'] ?? ''));

        $model->update($id, [
            'nip' => $nip,
            'nama' => trim((string) $this->request->getPost('nama')),
            'foto' => $fotoPath,
            'jabatan_utama_id' => $jabatanUtamaId,
            'jabatan_perbendaharaan_id' => $jabatanPerbendaharaanId > 0 ? $jabatanPerbendaharaanId : null,
            'eselon' => $this->nullableString($this->request->getPost('eselon')),
            'golongan' => $this->nullableString($this->request->getPost('golongan')),
            'masa_kerja' => $this->nullableString($this->request->getPost('masa_kerja')),
            'is_active' => (int) $this->request->getPost('is_active'),
            'updated_by' => (string) (session()->get('username') ?? 'system'),
            'updated_date' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/admin/master/pegawai')->with('message', 'Data pegawai berhasil diperbarui.');
    }

    public function updateStatus(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/pegawai')->with('error', 'Anda tidak memiliki akses untuk mengubah status pegawai.');
        }

        $status = (int) $this->request->getPost('is_active');
        if (! in_array($status, [0, 1], true)) {
            return redirect()->to('/admin/master/pegawai')->with('error', 'Status pegawai tidak valid.');
        }

        $model = new MstPegawaiModel();
        $existing = $model->find($id);
        if (! is_array($existing)) {
            return redirect()->to('/admin/master/pegawai')->with('error', 'Data pegawai tidak ditemukan.');
        }

        $model->update($id, [
            'is_active' => $status,
            'updated_by' => (string) (session()->get('username') ?? 'system'),
            'updated_date' => date('Y-m-d H:i:s'),
        ]);

        $message = $status === 1 ? 'Pegawai berhasil diaktifkan.' : 'Pegawai berhasil dinonaktifkan.';
        return redirect()->to('/admin/master/pegawai')->with('message', $message);
    }

    public function downloadTemplate()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! $this->canManageMasterData() || ! (bool) ($menuPermissions['import'] ?? false)) {
            return redirect()->to('/admin/master/pegawai')->with('error', 'Anda tidak memiliki akses untuk mengunduh template import.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Pegawai');

        $sheet->setCellValue('A1', 'nip');
        $sheet->setCellValue('B1', 'nama');
        $sheet->setCellValue('C1', 'jabatan_utama');
        $sheet->setCellValue('D1', 'jabatan_perbendaharaan');
        $sheet->setCellValue('E1', 'eselon');
        $sheet->setCellValue('F1', 'golongan');
        $sheet->setCellValue('G1', 'masa_kerja');
        $sheet->setCellValue('H1', 'status');

        $sheet->setCellValue('A2', '198301012008011001');
        $sheet->setCellValue('B2', 'Nama Pegawai');
        $sheet->setCellValue('C2', 'Analis Kepegawaian');
        $sheet->setCellValue('D2', 'Bendahara Pengeluaran');
        $sheet->setCellValue('E2', 'III.a');
        $sheet->setCellValue('F2', 'III/b');
        $sheet->setCellValue('G2', '10 Tahun');
        $sheet->setCellValue('H2', 'aktif');

        foreach (range('A', 'H') as $col) {
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'pegawai_template_');
        if ($tmpFile === false) {
            return redirect()->to('/admin/master/pegawai')->with('error', 'Gagal menyiapkan file template.');
        }

        $xlsxFile = $tmpFile . '.xlsx';
        @rename($tmpFile, $xlsxFile);

        $writer = new Xlsx($spreadsheet);
        $writer->save($xlsxFile);

        return $this->response->download($xlsxFile, null)->setFileName('template_import_pegawai.xlsx');
    }

    public function import()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->isPegawaiTableReady()) {
            return redirect()->to('/admin/master/pegawai')->with('error', 'Tabel pegawai belum tersedia. Jalankan migration.');
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/pegawai')->with('error', 'Anda tidak memiliki akses untuk import data pegawai.');
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! (bool) ($menuPermissions['import'] ?? false)) {
            return redirect()->to('/admin/master/pegawai')->with('error', 'Anda tidak memiliki izin import pada menu Pegawai.');
        }

        $file = $this->request->getFile('file_excel');
        if (! $file || ! $file->isValid()) {
            return redirect()->to('/admin/master/pegawai')->with('error', 'File import tidak valid.');
        }

        $ext = strtolower((string) $file->getExtension());
        if (! in_array($ext, ['xls', 'xlsx'], true)) {
            return redirect()->to('/admin/master/pegawai')->with('error', 'Format file harus .xls atau .xlsx.');
        }

        try {
            $spreadsheet = IOFactory::load($file->getTempName());
        } catch (\Throwable $e) {
            return redirect()->to('/admin/master/pegawai')->with('error', 'File Excel gagal dibaca. Pastikan format file valid.');
        }

        $rows = $spreadsheet->getActiveSheet()->toArray('', true, true, true);
        if ($rows === []) {
            return redirect()->to('/admin/master/pegawai')->with('error', 'File Excel kosong.');
        }

        $headerRow = array_shift($rows);
        if (! is_array($headerRow) || $headerRow === []) {
            return redirect()->to('/admin/master/pegawai')->with('error', 'Header Excel tidak ditemukan.');
        }

        $headers = [];
        foreach ($headerRow as $column => $name) {
            $normalized = $this->normalizeExcelHeader((string) $name);
            if ($normalized !== '') {
                $headers[$column] = $normalized;
            }
        }

        if ($headers === []) {
            return redirect()->to('/admin/master/pegawai')->with('error', 'Header Excel tidak dikenali.');
        }

        foreach (['nip', 'nama', 'jabatan_utama'] as $requiredHeader) {
            if (! in_array($requiredHeader, array_values($headers), true)) {
                return redirect()->to('/admin/master/pegawai')->with('error', 'Kolom wajib tidak ditemukan: ' . $requiredHeader . '.');
            }
        }

        $jabatanOptions = $this->resolveJabatanOptions();
        $utamaByName = $jabatanOptions['utama_by_name'];
        $perbendByName = $jabatanOptions['perbendaharaan_by_name'];

        $model = new MstPegawaiModel();
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

            $nip = trim((string) ($rowData['nip'] ?? ''));
            $nama = trim((string) ($rowData['nama'] ?? ''));
            $jabatanUtamaName = strtolower(trim((string) ($rowData['jabatan_utama'] ?? '')));
            $jabatanPerbendName = strtolower(trim((string) ($rowData['jabatan_perbendaharaan'] ?? '')));

            if ($nip === '' || $nama === '' || $jabatanUtamaName === '') {
                $skipped++;
                continue;
            }

            $jabatanUtamaId = $utamaByName[$jabatanUtamaName] ?? null;
            if ($jabatanUtamaId === null) {
                $skipped++;
                continue;
            }

            $jabatanPerbendId = null;
            if ($jabatanPerbendName !== '') {
                $jabatanPerbendId = $perbendByName[$jabatanPerbendName] ?? null;
                if ($jabatanPerbendId === null) {
                    $skipped++;
                    continue;
                }
            }

            $statusRaw = strtolower(trim((string) ($rowData['status'] ?? '')));
            $isActive = 1;
            if ($statusRaw !== '') {
                if (in_array($statusRaw, ['0', 'nonaktif', 'non-active', 'inactive'], true)) {
                    $isActive = 0;
                } elseif (in_array($statusRaw, ['1', 'aktif', 'active'], true)) {
                    $isActive = 1;
                }
            }

            $payload = [
                'nip' => $nip,
                'nama' => $nama,
                'jabatan_utama_id' => $jabatanUtamaId,
                'jabatan_perbendaharaan_id' => $jabatanPerbendId,
                'eselon' => $this->nullableString($rowData['eselon'] ?? ''),
                'golongan' => $this->nullableString($rowData['golongan'] ?? ''),
                'masa_kerja' => $this->nullableString($rowData['masa_kerja'] ?? ''),
                'is_active' => $isActive,
                'updated_by' => $username,
                'updated_date' => $now,
            ];

            $existing = $model->where('nip', $nip)->first();
            if (is_array($existing)) {
                $model->update((int) $existing['id'], $payload);
                $updated++;
                continue;
            }

            $payload['foto'] = null;
            $payload['created_by'] = $username;
            $payload['created_date'] = $now;
            $model->insert($payload);
            $inserted++;
        }

        return redirect()->to('/admin/master/pegawai')->with('message', 'Import selesai. Data baru: ' . $inserted . ', diperbarui: ' . $updated . ', dilewati: ' . $skipped . '.');
    }

    private function resolveJabatanOptions(): array
    {
        $rows = (new MstJabatanModel())
            ->orderBy('jabatan', 'ASC')
            ->findAll();

        $utama = [];
        $perbendaharaan = [];
        $utamaLookup = [];
        $perbendaharaanLookup = [];
        $utamaByName = [];
        $perbendaharaanByName = [];

        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            $label = trim((string) ($row['jabatan'] ?? ''));
            $jenis = strtolower(trim((string) ($row['jenis_jabatan'] ?? '')));
            $isActive = (int) ($row['is_active'] ?? 1) === 1;

            if ($id <= 0 || $label === '') {
                continue;
            }

            $displayLabel = $isActive ? $label : ($label . ' (Nonaktif)');

            if (in_array($jenis, ['fungsional', 'pelaksana'], true)) {
                $utama[] = ['id' => $id, 'label' => $displayLabel];
                $utamaLookup[$id] = true;
                $utamaByName[strtolower($label)] = $id;
            }

            if ($jenis === 'perbendaharaan') {
                $perbendaharaan[] = ['id' => $id, 'label' => $displayLabel];
                $perbendaharaanLookup[$id] = true;
                $perbendaharaanByName[strtolower($label)] = $id;
            }
        }

        return [
            'utama' => $utama,
            'perbendaharaan' => $perbendaharaan,
            'utama_lookup' => $utamaLookup,
            'perbendaharaan_lookup' => $perbendaharaanLookup,
            'utama_by_name' => $utamaByName,
            'perbendaharaan_by_name' => $perbendaharaanByName,
        ];
    }

    private function handleFotoUpload(string $fieldName, ?string $oldPath): ?string
    {
        $file = $this->request->getFile($fieldName);
        if (! $file || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return $oldPath;
        }

        if (! $file->isValid()) {
            return $oldPath;
        }

        $uploadDir = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'pegawai';
        if (! is_dir($uploadDir)) {
            @mkdir($uploadDir, 0775, true);
        }

        $newName = $file->getRandomName();
        $file->move($uploadDir, $newName, true);

        if ($oldPath !== null && $oldPath !== '') {
            $this->deleteFotoFile($oldPath);
        }

        return 'uploads/pegawai/' . $newName;
    }

    private function deleteFotoFile(string $relativePath): void
    {
        $relativePath = ltrim(str_replace('..', '', $relativePath), '/');
        if ($relativePath === '' || strpos($relativePath, 'uploads/pegawai/') !== 0) {
            return;
        }

        $absolutePath = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }

    private function normalizeExcelHeader(string $value): string
    {
        $header = strtolower(trim($value));
        $header = str_replace(['-', '/', ' '], '_', $header);
        $header = preg_replace('/[^a-z0-9_]/', '', $header) ?? $header;

        return match ($header) {
            'nip' => 'nip',
            'nama', 'nama_pegawai' => 'nama',
            'jabatan_utama', 'jabatan', 'jabatan_fungsional_pelaksana' => 'jabatan_utama',
            'jabatan_perbendaharaan', 'perbendaharaan' => 'jabatan_perbendaharaan',
            'eselon' => 'eselon',
            'golongan' => 'golongan',
            'masa_kerja' => 'masa_kerja',
            'status', 'is_active', 'aktif' => 'status',
            default => $header,
        };
    }

    private function nullableString($value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function isPegawaiTableReady(): bool
    {
        return db_connect()->tableExists('mst_pegawai');
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