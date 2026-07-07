<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MstSekolahModel;
use CodeIgniter\HTTP\RedirectResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class MasterSekolah extends BaseController
{
    private const MENU_LINK = 'admin/master/sekolah';

    public function index()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $items = (new MstSekolahModel())
            ->orderBy('nama', 'ASC')
            ->findAll();

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        $mapTypes = $this->getMapTypes();

        $canManage = $this->canManageMasterData();

        return view('admin/master/sekolah', [
            'pageTitle' => 'Master Sekolah',
            'items' => $items,
            'mapTypes' => $mapTypes,
            'mapDefaultId' => (int) ($mapTypes[0]['id'] ?? 1),
            'can_add' => $canManage && (bool) ($menuPermissions['add'] ?? false),
            'can_edit' => $canManage && (bool) ($menuPermissions['edit'] ?? false),
            'can_export' => (bool) ($menuPermissions['export'] ?? false),
        ]);
    }

    public function export()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! (bool) ($menuPermissions['export'] ?? false)) {
            return redirect()->to('/admin/master/sekolah')->with('error', 'Anda tidak memiliki izin export pada menu Sekolah.');
        }

        $items = (new MstSekolahModel())
            ->orderBy('nama', 'ASC')
            ->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Daftar Sekolah');

        $sheet->mergeCells('A1:I1');
        $sheet->mergeCells('A2:I2');
        $sheet->mergeCells('A3:I3');
        $sheet->mergeCells('A4:I4');

        $sheet->setCellValue('A1', 'DAFTAR SEKOLAH');
        $sheet->setCellValue('A2', 'SATUAN KERJA PELAKSANAAN PRASARANA STRATEGIS RIAU');
        $sheet->setCellValue('A3', 'DIREKTORAT JENDERAL PRASARANA STRATEGIS');
        $sheet->setCellValue('A4', 'KEMENTERIAN PEKERJAAN UMUM');

        $sheet->setCellValue('A6', 'NO.');
        $sheet->setCellValue('B6', 'NPSN');
        $sheet->setCellValue('C6', 'NAMA SEKOLAH');
        $sheet->setCellValue('D6', 'JENIS');
        $sheet->setCellValue('E6', 'NSM');
        $sheet->setCellValue('F6', 'KABUPATEN');
        $sheet->setCellValue('G6', 'KECAMATAN');
        $sheet->setCellValue('H6', 'LATITUDE');
        $sheet->setCellValue('I6', 'LONGITUDE');

        // Style the headers
        $sheet->getStyle('A1:I4')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A1:I4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:I4')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('A6:I6')->getFont()->setBold(true);
        $sheet->getStyle('A6:I6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A6:I6')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A6:I6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');

        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(35);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(25);
        $sheet->getColumnDimension('G')->setWidth(25);
        $sheet->getColumnDimension('H')->setWidth(18);
        $sheet->getColumnDimension('I')->setWidth(18);

        $row = 7;
        $no = 1;
        foreach ($items as $item) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValueExplicit('B' . $row, (string) ($item['npsn'] ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('C' . $row, (string) ($item['nama'] ?? ''));
            $sheet->setCellValue('D' . $row, (string) ($item['jenis'] ?? ''));
            $sheet->setCellValueExplicit('E' . $row, (string) ($item['nsm'] ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('F' . $row, (string) ($item['kabupaten'] ?? ''));
            $sheet->setCellValue('G' . $row, (string) ($item['kecamatan'] ?? ''));
            if ($item['latitude'] !== null && $item['latitude'] !== '') {
                $sheet->setCellValue('H' . $row, (float) $item['latitude']);
                $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('0.000000');
            } else {
                $sheet->setCellValue('H' . $row, '-');
            }

            if ($item['longitude'] !== null && $item['longitude'] !== '') {
                $sheet->setCellValue('I' . $row, (float) $item['longitude']);
                $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('0.000000');
            } else {
                $sheet->setCellValue('I' . $row, '-');
            }

            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $row++;
        }

        $lastRow = $row - 1;
        // Apply borders
        $sheet->getStyle('A6:I' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $tmpFile = tempnam(sys_get_temp_dir(), 'sekolah_export_');
        if ($tmpFile === false) {
            return redirect()->to('/admin/master/sekolah')->with('error', 'Gagal menyiapkan file export.');
        }

        $xlsxFile = $tmpFile . '.xlsx';
        @rename($tmpFile, $xlsxFile);

        $writer = new Xlsx($spreadsheet);
        $writer->save($xlsxFile);

        return $this->response->download($xlsxFile, null)->setFileName('daftar_sekolah.xlsx');
    }

    private function getMapTypes(): array
    {
        $db = db_connect();

        if ($db->tableExists('mst_map_type')) {
            $rows = $db->table('mst_map_type')
                ->select('id, map_name, map_script')
                ->orderBy('id', 'ASC')
                ->get()
                ->getResultArray();

            if ($rows !== []) {
                return array_map(static function (array $row): array {
                    return [
                        'id' => (int) ($row['id'] ?? 0),
                        'map_name' => (string) ($row['map_name'] ?? 'Leaflet Map'),
                        'map_script' => str_replace('http://', 'https://', (string) ($row['map_script'] ?? '')),
                    ];
                }, $rows);
            }
        }

        return [
            [
                'id' => 1,
                'map_name' => 'Leaflet Map',
                'map_script' => "L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(map);",
            ],
        ];
    }

    public function create()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/sekolah')->with('error', 'Anda tidak memiliki akses untuk menambah data sekolah.');
        }

        $rules = [
            'npsn' => 'required|numeric|max_length[20]',
            'nama' => 'required|max_length[255]',
            'jenis' => 'permit_empty|max_length[255]',
            'nsm' => 'permit_empty|numeric|max_length[20]',
            'kabupaten' => 'permit_empty|max_length[255]',
            'kecamatan' => 'permit_empty|max_length[255]',
            'latitude' => 'permit_empty|decimal',
            'longitude' => 'permit_empty|decimal',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/admin/master/sekolah')->withInput()->with('error', 'Data sekolah belum valid.');
        }

        $model = new MstSekolahModel();
        $npsn = (string) $this->request->getPost('npsn');

        if ($model->where('npsn', $npsn)->countAllResults() > 0) {
            return redirect()->to('/admin/master/sekolah')->withInput()->with('error', 'NPSN sudah terdaftar.');
        }

        $now = date('Y-m-d H:i:s');
        $username = (string) (session()->get('username') ?? 'system');

        $model->insert([
            'npsn' => $npsn,
            'nama' => trim((string) $this->request->getPost('nama')),
            'jenis' => trim((string) $this->request->getPost('jenis')),
            'nsm' => $this->nullableBigint($this->request->getPost('nsm')),
            'kabupaten' => trim((string) $this->request->getPost('kabupaten')),
            'kecamatan' => trim((string) $this->request->getPost('kecamatan')),
            'latitude' => $this->nullableFloat($this->request->getPost('latitude')),
            'longitude' => $this->nullableFloat($this->request->getPost('longitude')),
            'created_by' => $username,
            'created_date' => $now,
            'updated_by' => $username,
            'updated_date' => $now,
        ]);

        return redirect()->to('/admin/master/sekolah')->with('message', 'Data sekolah berhasil ditambahkan.');
    }

    public function edit(string $npsn)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/sekolah')->with('error', 'Anda tidak memiliki akses untuk mengubah data sekolah.');
        }

        $rules = [
            'npsn' => 'required|numeric|max_length[20]',
            'nama' => 'required|max_length[255]',
            'jenis' => 'permit_empty|max_length[255]',
            'nsm' => 'permit_empty|numeric|max_length[20]',
            'kabupaten' => 'permit_empty|max_length[255]',
            'kecamatan' => 'permit_empty|max_length[255]',
            'latitude' => 'permit_empty|decimal',
            'longitude' => 'permit_empty|decimal',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/admin/master/sekolah')->withInput()->with('error', 'Data sekolah belum valid.');
        }

        $model = new MstSekolahModel();
        $existing = $model->where('npsn', $npsn)->first();

        if (! is_array($existing)) {
            return redirect()->to('/admin/master/sekolah')->with('error', 'Data sekolah tidak ditemukan.');
        }

        $newNpsn = (string) $this->request->getPost('npsn');
        if ($newNpsn !== $npsn && $model->where('npsn', $newNpsn)->countAllResults() > 0) {
            return redirect()->to('/admin/master/sekolah')->withInput()->with('error', 'NPSN sudah digunakan oleh data lain.');
        }

        $username = (string) (session()->get('username') ?? 'system');

        $builder = db_connect()->table('mst_sekolah');
        $builder->where('npsn', $npsn)->update([
            'npsn' => $newNpsn,
            'nama' => trim((string) $this->request->getPost('nama')),
            'jenis' => trim((string) $this->request->getPost('jenis')),
            'nsm' => $this->nullableBigint($this->request->getPost('nsm')),
            'kabupaten' => trim((string) $this->request->getPost('kabupaten')),
            'kecamatan' => trim((string) $this->request->getPost('kecamatan')),
            'latitude' => $this->nullableFloat($this->request->getPost('latitude')),
            'longitude' => $this->nullableFloat($this->request->getPost('longitude')),
            'updated_by' => $username,
            'updated_date' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/admin/master/sekolah')->with('message', 'Data sekolah berhasil diperbarui.');
    }

    private function nullableBigint($value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function nullableFloat($value): ?float
    {
        $value = trim((string) $value);
        return $value === '' ? null : (float) $value;
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
