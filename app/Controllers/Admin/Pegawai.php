<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MstJabatanModel;
use App\Models\MstPegawaiModel;
use CodeIgniter\HTTP\RedirectResponse;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
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

            $items = $this->applyMasaKerjaDefaults($items);
        }

        return view('admin/master/pegawai', [
            'pageTitle' => 'Master Pegawai',
            'items' => $items,
            'jabatan_utama_options' => $jabatanOptions['utama'],
            'jabatan_perbendaharaan_options' => $jabatanOptions['perbendaharaan'],
            'can_add' => $canManage && (bool) ($menuPermissions['add'] ?? false),
            'can_edit' => $canManage && (bool) ($menuPermissions['edit'] ?? false),
            'can_import' => $canManage && (bool) ($menuPermissions['import'] ?? false),
            'can_export' => (bool) ($menuPermissions['export'] ?? false),
            'table_ready' => $this->isPegawaiTableReady(),
        ]);
    }

    public function export()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->isPegawaiTableReady()) {
            return redirect()->to('/admin/master/pegawai')->with('error', 'Tabel pegawai belum tersedia. Jalankan migration.');
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! (bool) ($menuPermissions['export'] ?? false)) {
            return redirect()->to('/admin/master/pegawai')->with('error', 'Anda tidak memiliki izin export pada menu Pegawai.');
        }

        $items = db_connect()
            ->table('mst_pegawai p')
            ->select('p.*, ju.jabatan AS jabatan_utama_label, jp.jabatan AS jabatan_perbendaharaan_label')
            ->join('mst_jabatan ju', 'ju.id = p.jabatan_utama_id', 'left')
            ->join('mst_jabatan jp', 'jp.id = p.jabatan_perbendaharaan_id', 'left')
            ->orderBy('p.nama', 'ASC')
            ->get()
            ->getResultArray();

        $items = $this->applyMasaKerjaDefaults($items);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Daftar Pegawai');

        $sheet->mergeCells('A1:L1');
        $sheet->mergeCells('A2:L2');
        $sheet->mergeCells('A3:L3');
        $sheet->mergeCells('A4:L4');

        $sheet->setCellValue('A1', 'DAFTAR PEGAWAI');
        $sheet->setCellValue('A2', 'SATUAN KERJA PELAKSANAAN PRASARANA STRATEGIS RIAU');
        $sheet->setCellValue('A3', 'DIREKTORAT JENDERAL PRASARANA STRATEGIS');
        $sheet->setCellValue('A4', 'KEMENTERIAN PEKERJAAN UMUM');

        $sheet->setCellValue('A6', 'NO.');
        $sheet->setCellValue('B6', 'FOTO');
        $sheet->setCellValue('C6', 'NAMA');
        $sheet->setCellValue('D6', 'NIP');
        $sheet->setCellValue('E6', 'EMAIL');
        $sheet->setCellValue('F6', 'JABATAN');
        $sheet->setCellValue('H6', 'ESELON');
        $sheet->setCellValue('I6', 'GOLONGAN');
        $sheet->setCellValue('J6', 'MASA KERJA');
        $sheet->setCellValue('K6', 'JENIS PEGAWAI');
        $sheet->setCellValue('L6', 'STATUS');

        $sheet->mergeCells('A6:A7');
        $sheet->mergeCells('B6:B7');
        $sheet->mergeCells('C6:C7');
        $sheet->mergeCells('D6:D7');
        $sheet->mergeCells('E6:E7');
        $sheet->mergeCells('F6:G6');
        $sheet->mergeCells('H6:H7');
        $sheet->mergeCells('I6:I7');
        $sheet->mergeCells('J6:J7');
        $sheet->mergeCells('K6:K7');
        $sheet->mergeCells('L6:L7');
        $sheet->setCellValue('F7', 'JABATAN FUNGSIONAL / PELAKSANA');
        $sheet->setCellValue('G7', 'JABATAN PERBENDAHARAAN');

        $sheet->setCellValue('A8', '1');
        $sheet->setCellValue('B8', '2');
        $sheet->setCellValue('C8', '3');
        $sheet->setCellValue('D8', '4');
        $sheet->setCellValue('E8', '5');
        $sheet->mergeCells('F8:G8');
        $sheet->setCellValue('F8', '6');
        $sheet->setCellValue('H8', '7');
        $sheet->setCellValue('I8', '8');
        $sheet->setCellValue('J8', '9');
        $sheet->setCellValue('K8', '10');
        $sheet->setCellValue('L8', '11');

        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(16);
        $sheet->getColumnDimension('C')->setWidth(32);
        $sheet->getColumnDimension('D')->setWidth(24);
        $sheet->getColumnDimension('E')->setWidth(28);
        $sheet->getColumnDimension('F')->setWidth(42);
        $sheet->getColumnDimension('G')->setWidth(42);
        $sheet->getColumnDimension('H')->setWidth(14);
        $sheet->getColumnDimension('I')->setWidth(18);
        $sheet->getColumnDimension('J')->setWidth(16);
        $sheet->getColumnDimension('K')->setWidth(18);
        $sheet->getColumnDimension('L')->setWidth(16);

        $sheet->getStyle('A1:L4')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A1:L4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:L4')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(28);
        $sheet->getRowDimension(2)->setRowHeight(28);
        $sheet->getRowDimension(3)->setRowHeight(28);
        $sheet->getRowDimension(4)->setRowHeight(28);

        $sheet->getStyle('A6:L8')->getFont()->setBold(true);
        $sheet->getStyle('A6:L8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A6:L8')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A6:L8')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A6:L8')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEAEAEA');
        $sheet->getRowDimension(6)->setRowHeight(30);
        $sheet->getRowDimension(7)->setRowHeight(28);
        $sheet->getRowDimension(8)->setRowHeight(22);

        $row = 9;
        $no = 1;
        $tempExportPhotos = [];
        foreach ($items as $item) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('C' . $row, (string) ($item['nama'] ?? ''));
            $sheet->setCellValue('D' . $row, (string) ($item['nip'] ?? ''));
            $sheet->setCellValue('E' . $row, (string) ($item['email'] ?? ''));
            $sheet->setCellValue('F' . $row, (string) ($item['jabatan_utama_label'] ?? ''));
            $sheet->setCellValue('G' . $row, (string) ($item['jabatan_perbendaharaan_label'] ?? ''));
            $sheet->setCellValue('H' . $row, (string) ($item['eselon'] ?? ''));
            $sheet->setCellValue('I' . $row, (string) ($item['golongan'] ?? ''));
            $sheet->setCellValue('J' . $row, (string) ($item['masa_kerja'] ?? ''));
            $jpRaw = strtolower(trim((string) ($item['jenis_pegawai'] ?? 'pns')));
            $jpLabel = match ($jpRaw) {
                'konsultan' => 'KONSULTAN INDIVIDUAL',
                'security' => 'SECURITY',
                'cleaning_service' => 'CLEANING SERVICE',
                'ppnpn' => 'PPNPN',
                'pppk' => 'PPPK',
                'cpns' => 'CPNS',
                'lainnya' => 'LAINNYA',
                default => strtoupper($jpRaw),
            };
            $sheet->setCellValue('K' . $row, $jpLabel);
            $sheet->setCellValue('L' . $row, (int) ($item['is_active'] ?? 1) === 1 ? 'AKTIF' : 'NONAKTIF');

            $sheet->getRowDimension($row)->setRowHeight(82);

            $fotoPath = trim((string) ($item['foto'] ?? ''));
            if ($fotoPath !== '') {
                $absolutePath = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($fotoPath, '/'));
                if (is_file($absolutePath)) {
                    $exportPhotoPath = $this->prepareFotoForExcelExport($absolutePath, $tempExportPhotos);
                    $drawing = new Drawing();
                    $drawing->setPath($exportPhotoPath ?? $absolutePath);
                    $drawing->setCoordinates('B' . $row);
                    $drawing->setHeight(72);
                    $drawing->setOffsetX(8);
                    $drawing->setOffsetY(4);
                    $drawing->setWorksheet($sheet);
                }
            }

            $row++;
        }

        $lastRow = max(9, $row - 1);
        $sheet->getStyle('A9:L' . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
        $sheet->getStyle('A9:L' . $lastRow)->getAlignment()->setWrapText(true);
        $sheet->getStyle('A9:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B9:B' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D9:D' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('H9:L' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('A6:L' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A6:L' . $lastRow)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle('A6:L' . $lastRow)->getBorders()->getOutline()->getColor()->setARGB('FF1E3A8A');

        $footerRow = $lastRow + 1;
        $sheet->setCellValue('L' . $footerRow, 'Page 1');
        $sheet->getStyle('L' . $footerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('L' . $footerRow)->getFont()->setItalic(true)->getColor()->setARGB('FF8A8A8A');

        $tmpFile = tempnam(sys_get_temp_dir(), 'pegawai_export_');
        if ($tmpFile === false) {
            return redirect()->to('/admin/master/pegawai')->with('error', 'Gagal menyiapkan file export.');
        }

        $xlsxFile = $tmpFile . '.xlsx';
        @rename($tmpFile, $xlsxFile);

        $writer = new Xlsx($spreadsheet);
        try {
            $writer->save($xlsxFile);
        } finally {
            foreach ($tempExportPhotos as $tempPhotoPath) {
                if (is_string($tempPhotoPath) && $tempPhotoPath !== '' && is_file($tempPhotoPath)) {
                    @unlink($tempPhotoPath);
                }
            }
        }

        return $this->response->download($xlsxFile, null)->setFileName('daftar_pegawai.xlsx');
    }

    private function prepareFotoForExcelExport(string $sourcePath, array &$tempFiles): ?string
    {
        if (! is_file($sourcePath) || ! extension_loaded('gd')) {
            return null;
        }

        $imageInfo = @getimagesize($sourcePath);
        if (! is_array($imageInfo)) {
            return null;
        }

        $width = (int) ($imageInfo[0] ?? 0);
        $height = (int) ($imageInfo[1] ?? 0);
        $type = (int) ($imageInfo[2] ?? 0);
        if ($width <= 0 || $height <= 0) {
            return null;
        }

        $sourceImage = $this->createGdImageFromFile($sourcePath, $type);
        if ($sourceImage === false) {
            return null;
        }

        $maxWidth = 220;
        $maxHeight = 220;
        $ratio = min($maxWidth / $width, $maxHeight / $height, 1);
        $targetWidth = max(1, (int) floor($width * $ratio));
        $targetHeight = max(1, (int) floor($height * $ratio));

        $targetImage = imagecreatetruecolor($targetWidth, $targetHeight);
        if ($targetImage === false) {
            imagedestroy($sourceImage);
            return null;
        }

        $white = imagecolorallocate($targetImage, 255, 255, 255);
        imagefill($targetImage, 0, 0, $white);

        imagecopyresampled(
            $targetImage,
            $sourceImage,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $width,
            $height
        );

        imagedestroy($sourceImage);

        $tmpFile = tempnam(sys_get_temp_dir(), 'pegawai_export_foto_');
        if ($tmpFile === false) {
            imagedestroy($targetImage);
            return null;
        }

        $tmpJpg = $tmpFile . '.jpg';
        @rename($tmpFile, $tmpJpg);

        $saved = imagejpeg($targetImage, $tmpJpg, 60);
        imagedestroy($targetImage);

        if ($saved !== true) {
            if (is_file($tmpJpg)) {
                @unlink($tmpJpg);
            }
            return null;
        }

        $tempFiles[] = $tmpJpg;
        return $tmpJpg;
    }

    private function createGdImageFromFile(string $sourcePath, int $type)
    {
        return match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG => @imagecreatefrompng($sourcePath),
            IMAGETYPE_GIF => @imagecreatefromgif($sourcePath),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourcePath) : false,
            default => false,
        };
    }

    public function create()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki akses ke menu ini.',
                    'csrf_hash' => csrf_hash(),
                ]);
            }
            return $forbidden;
        }

        if (! $this->isPegawaiTableReady()) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Tabel pegawai belum tersedia. Jalankan migration.',
                    'csrf_hash' => csrf_hash(),
                ]);
            }
            return redirect()->to('/admin/master/pegawai')->with('error', 'Tabel pegawai belum tersedia. Jalankan migration.');
        }

        if (! $this->canManageMasterData()) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki akses untuk menambah data pegawai.',
                    'csrf_hash' => csrf_hash(),
                ]);
            }
            return redirect()->to('/admin/master/pegawai')->with('error', 'Anda tidak memiliki akses untuk menambah data pegawai.');
        }

        $rules = [
            'nip' => 'required|max_length[30]',
            'nama' => 'required|max_length[150]',
            'email' => 'permit_empty|valid_email|max_length[255]',
            'jenis_pegawai' => 'required|in_list[cpns,pns,pppk,ppnpn,konsultan,security,cleaning_service,lainnya]',
            'jabatan_utama_id' => 'permit_empty|integer',
            'jabatan_perbendaharaan_id' => 'permit_empty|integer',
            'eselon' => 'permit_empty|max_length[50]',
            'golongan' => 'permit_empty|max_length[50]',
            'masa_kerja' => 'permit_empty|max_length[50]',
            'is_active' => 'required|in_list[0,1]',
        ];

        $fotoFile = $this->request->getFile('foto');
        if ($fotoFile && $fotoFile->isValid()) {
            $rules['foto'] = 'is_image[foto]|max_size[foto,2048]|mime_in[foto,image/jpg,image/jpeg,image/png,image/webp]';
        }

        if (! $this->validate($rules)) {
            $errors = $this->validator->getErrors();
            $errorMsg = 'Data pegawai belum valid: ' . implode(', ', $errors);
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => $errorMsg,
                    'errors' => $errors,
                    'csrf_hash' => csrf_hash(),
                ]);
            }
            return redirect()->to('/admin/master/pegawai')->withInput()->with('error', $errorMsg);
        }

        $model = new MstPegawaiModel();
        $nip = trim((string) $this->request->getPost('nip'));
        if ($model->where('nip', $nip)->countAllResults() > 0) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'NIP sudah terdaftar.',
                    'csrf_hash' => csrf_hash(),
                ]);
            }
            return redirect()->to('/admin/master/pegawai')->withInput()->with('error', 'NIP sudah terdaftar.');
        }

        $jabatanOptions = $this->resolveJabatanOptions();
        $jabatanUtamaRaw = $this->request->getPost('jabatan_utama_id');
        $jabatanUtamaId = ($jabatanUtamaRaw !== null && $jabatanUtamaRaw !== '' && (int) $jabatanUtamaRaw > 0) ? (int) $jabatanUtamaRaw : null;
        $jabatanPerbendaharaanId = (int) ($this->request->getPost('jabatan_perbendaharaan_id') ?: 0);

        if ($jabatanUtamaId !== null && ! isset($jabatanOptions['utama_lookup'][$jabatanUtamaId])) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Jabatan utama tidak valid.',
                    'csrf_hash' => csrf_hash(),
                ]);
            }
            return redirect()->to('/admin/master/pegawai')->withInput()->with('error', 'Jabatan utama tidak valid.');
        }

        if ($jabatanPerbendaharaanId > 0 && ! isset($jabatanOptions['perbendaharaan_lookup'][$jabatanPerbendaharaanId])) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Jabatan perbendaharaan tidak valid.',
                    'csrf_hash' => csrf_hash(),
                ]);
            }
            return redirect()->to('/admin/master/pegawai')->withInput()->with('error', 'Jabatan perbendaharaan tidak valid.');
        }

        $fotoPath = $this->handleFotoUpload('foto', null);

        $now = date('Y-m-d H:i:s');
        $username = (string) (session()->get('username') ?? 'system');

        $model->insert([
            'nip' => $nip,
            'nama' => trim((string) $this->request->getPost('nama')),
            'email' => $this->nullableString($this->request->getPost('email')),
            'jenis_pegawai' => strtolower(trim((string) $this->request->getPost('jenis_pegawai'))),
            'foto' => $fotoPath,
            'jabatan_utama_id' => $jabatanUtamaId,
            'jabatan_perbendaharaan_id' => $jabatanPerbendaharaanId > 0 ? $jabatanPerbendaharaanId : null,
            'eselon' => $this->nullableString($this->request->getPost('eselon')),
            'golongan' => $this->nullableString($this->request->getPost('golongan')),
            'masa_kerja' => $this->resolveMasaKerjaFromNip($nip) ?? $this->nullableString($this->request->getPost('masa_kerja')),
            'is_active' => (int) $this->request->getPost('is_active'),
            'created_by' => $username,
            'created_date' => $now,
            'updated_by' => $username,
            'updated_date' => $now,
        ]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Data pegawai berhasil ditambahkan.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        return redirect()->to('/admin/master/pegawai')->with('message', 'Data pegawai berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki akses ke menu ini.',
                    'csrf_hash' => csrf_hash(),
                ]);
            }
            return $forbidden;
        }

        if (! $this->isPegawaiTableReady()) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Tabel pegawai belum tersedia.',
                    'csrf_hash' => csrf_hash(),
                ]);
            }
            return redirect()->to('/admin/master/pegawai')->with('error', 'Tabel pegawai belum tersedia.');
        }

        if (! $this->canManageMasterData()) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki akses untuk mengubah data pegawai.',
                    'csrf_hash' => csrf_hash(),
                ]);
            }
            return redirect()->to('/admin/master/pegawai')->with('error', 'Anda tidak memiliki akses untuk mengubah data pegawai.');
        }

        $rules = [
            'nip' => 'required|max_length[30]',
            'nama' => 'required|max_length[150]',
            'email' => 'permit_empty|valid_email|max_length[255]',
            'jenis_pegawai' => 'required|in_list[cpns,pns,pppk,ppnpn,konsultan,security,cleaning_service,lainnya]',
            'jabatan_utama_id' => 'permit_empty|integer',
            'jabatan_perbendaharaan_id' => 'permit_empty|integer',
            'eselon' => 'permit_empty|max_length[50]',
            'golongan' => 'permit_empty|max_length[50]',
            'masa_kerja' => 'permit_empty|max_length[50]',
            'is_active' => 'required|in_list[0,1]',
        ];

        $fotoFile = $this->request->getFile('foto');
        if ($fotoFile && $fotoFile->isValid()) {
            $rules['foto'] = 'is_image[foto]|max_size[foto,2048]|mime_in[foto,image/jpg,image/jpeg,image/png,image/webp]';
        }

        if (! $this->validate($rules)) {
            $errors = $this->validator->getErrors();
            $errorMsg = 'Data pegawai belum valid: ' . implode(', ', $errors);
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => $errorMsg,
                    'errors' => $errors,
                    'csrf_hash' => csrf_hash(),
                ]);
            }
            return redirect()->to('/admin/master/pegawai')->withInput()->with('error', $errorMsg);
        }

        $model = new MstPegawaiModel();
        $existing = $model->find($id);
        if (! is_array($existing)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Data pegawai tidak ditemukan.',
                    'csrf_hash' => csrf_hash(),
                ]);
            }
            return redirect()->to('/admin/master/pegawai')->with('error', 'Data pegawai tidak ditemukan.');
        }

        $nip = trim((string) $this->request->getPost('nip'));
        $duplicate = $model->where('nip', $nip)->where('id !=', $id)->countAllResults();
        if ($duplicate > 0) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'NIP sudah digunakan oleh data lain.',
                    'csrf_hash' => csrf_hash(),
                ]);
            }
            return redirect()->to('/admin/master/pegawai')->withInput()->with('error', 'NIP sudah digunakan oleh data lain.');
        }

        $jabatanOptions = $this->resolveJabatanOptions();
        $jabatanUtamaRaw = $this->request->getPost('jabatan_utama_id');
        $jabatanUtamaId = ($jabatanUtamaRaw !== null && $jabatanUtamaRaw !== '' && (int) $jabatanUtamaRaw > 0) ? (int) $jabatanUtamaRaw : null;
        $jabatanPerbendaharaanId = (int) ($this->request->getPost('jabatan_perbendaharaan_id') ?: 0);

        if ($jabatanUtamaId !== null && ! isset($jabatanOptions['utama_lookup'][$jabatanUtamaId])) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Jabatan utama tidak valid.',
                    'csrf_hash' => csrf_hash(),
                ]);
            }
            return redirect()->to('/admin/master/pegawai')->withInput()->with('error', 'Jabatan utama tidak valid.');
        }

        if ($jabatanPerbendaharaanId > 0 && ! isset($jabatanOptions['perbendaharaan_lookup'][$jabatanPerbendaharaanId])) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Jabatan perbendaharaan tidak valid.',
                    'csrf_hash' => csrf_hash(),
                ]);
            }
            return redirect()->to('/admin/master/pegawai')->withInput()->with('error', 'Jabatan perbendaharaan tidak valid.');
        }

        $fotoPath = $this->handleFotoUpload('foto', (string) ($existing['foto'] ?? ''));

        $model->update($id, [
            'nip' => $nip,
            'nama' => trim((string) $this->request->getPost('nama')),
            'email' => $this->nullableString($this->request->getPost('email')),
            'jenis_pegawai' => strtolower(trim((string) $this->request->getPost('jenis_pegawai'))),
            'foto' => $fotoPath,
            'jabatan_utama_id' => $jabatanUtamaId,
            'jabatan_perbendaharaan_id' => $jabatanPerbendaharaanId > 0 ? $jabatanPerbendaharaanId : null,
            'eselon' => $this->nullableString($this->request->getPost('eselon')),
            'golongan' => $this->nullableString($this->request->getPost('golongan')),
            'masa_kerja' => $this->resolveMasaKerjaFromNip($nip) ?? $this->nullableString($this->request->getPost('masa_kerja')),
            'is_active' => (int) $this->request->getPost('is_active'),
            'updated_by' => (string) (session()->get('username') ?? 'system'),
            'updated_date' => date('Y-m-d H:i:s'),
        ]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Data pegawai berhasil diperbarui.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        return redirect()->to('/admin/master/pegawai')->with('message', 'Data pegawai berhasil diperbarui.');
    }

    public function updateStatus(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki akses ke menu ini.',
                    'csrf_hash' => csrf_hash(),
                ]);
            }
            return $forbidden;
        }

        if (! $this->canManageMasterData()) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki akses untuk mengubah status pegawai.',
                    'csrf_hash' => csrf_hash(),
                ]);
            }
            return redirect()->to('/admin/master/pegawai')->with('error', 'Anda tidak memiliki akses untuk mengubah status pegawai.');
        }

        $status = (int) $this->request->getPost('is_active');
        if (! in_array($status, [0, 1], true)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Status pegawai tidak valid.',
                    'csrf_hash' => csrf_hash(),
                ]);
            }
            return redirect()->to('/admin/master/pegawai')->with('error', 'Status pegawai tidak valid.');
        }

        $model = new MstPegawaiModel();
        $existing = $model->find($id);
        if (! is_array($existing)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Data pegawai tidak ditemukan.',
                    'csrf_hash' => csrf_hash(),
                ]);
            }
            return redirect()->to('/admin/master/pegawai')->with('error', 'Data pegawai tidak ditemukan.');
        }

        $model->update($id, [
            'is_active' => $status,
            'updated_by' => (string) (session()->get('username') ?? 'system'),
            'updated_date' => date('Y-m-d H:i:s'),
        ]);

        $message = $status === 1 ? 'Pegawai berhasil diaktifkan.' : 'Pegawai berhasil dinonaktifkan.';
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => $message,
                'csrf_hash' => csrf_hash(),
            ]);
        }

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
        $sheet->setCellValue('C1', 'email');
        $sheet->setCellValue('D1', 'jabatan_utama');
        $sheet->setCellValue('E1', 'jabatan_perbendaharaan');
        $sheet->setCellValue('F1', 'eselon');
        $sheet->setCellValue('G1', 'golongan');
        $sheet->setCellValue('H1', 'masa_kerja');
        $sheet->setCellValue('I1', 'status');
        $sheet->setCellValue('J1', 'jenis_pegawai');

        $sheet->setCellValue('A2', '198301012008011001');
        $sheet->setCellValue('B2', 'Nama Pegawai');
        $sheet->setCellValue('C2', 'pegawai@example.com');
        $sheet->setCellValue('D2', 'Analis Kepegawaian');
        $sheet->setCellValue('E2', 'Bendahara Pengeluaran');
        $sheet->setCellValue('F2', 'III.a');
        $sheet->setCellValue('G2', 'III/b');
        $sheet->setCellValue('H2', '10 Tahun');
        $sheet->setCellValue('I2', 'aktif');
        $sheet->setCellValue('J2', 'pns');

        foreach (range('A', 'J') as $col) {
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

        foreach (['nip', 'nama', 'jabatan_utama', 'jenis_pegawai'] as $requiredHeader) {
            if (! in_array($requiredHeader, array_values($headers), true)) {
                return redirect()->to('/admin/master/pegawai')->with('error', 'Kolom wajib tidak ditemukan: ' . $requiredHeader . '.');
            }
        }

        $jabatanByName = $this->buildJabatanLookupByName();

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
            $jabatanUtamaName = trim((string) ($rowData['jabatan_utama'] ?? ''));
            $jabatanPerbendName = trim((string) ($rowData['jabatan_perbendaharaan'] ?? ''));
            $rawJenis = strtolower(trim((string) ($rowData['jenis_pegawai'] ?? '')));
            $jenisPegawai = match ($rawJenis) {
                'konsultan', 'konsultan individual', 'ki' => 'konsultan',
                'security', 'satpam', 'tenaga keamanan' => 'security',
                'cleaning service', 'cleaning_service', 'cs', 'tenaga kebersihan' => 'cleaning_service',
                'ppnpn', 'honor', 'honorer', 'non pns', 'non-pns' => 'ppnpn',
                'pns' => 'pns',
                'cpns' => 'cpns',
                'pppk' => 'pppk',
                'lainnya', 'other', 'non-asn', 'non asn' => 'lainnya',
                default => $rawJenis,
            };

            if ($nip === '' || $nama === '') {
                $skipped++;
                continue;
            }

            if (! in_array($jenisPegawai, ['cpns', 'pns', 'pppk', 'ppnpn', 'konsultan', 'security', 'cleaning_service', 'lainnya'], true)) {
                $skipped++;
                continue;
            }

            $jabatanUtamaId = null;
            if ($jabatanUtamaName !== '') {
                $jabatanUtamaId = $this->resolveOrCreateJabatanId($jabatanUtamaName, $jabatanByName, $username, $now);
            }

            $jabatanPerbendId = null;
            if ($jabatanPerbendName !== '') {
                $jabatanPerbendId = $this->resolveOrCreateJabatanId($jabatanPerbendName, $jabatanByName, $username, $now);
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
                'email' => $this->nullableString($rowData['email'] ?? ''),
                'jenis_pegawai' => $jenisPegawai,
                'jabatan_utama_id' => $jabatanUtamaId,
                'jabatan_perbendaharaan_id' => $jabatanPerbendId,
                'eselon' => $this->nullableString($rowData['eselon'] ?? ''),
                'golongan' => $this->nullableString($rowData['golongan'] ?? ''),
                'masa_kerja' => $this->resolveMasaKerjaFromNip($nip) ?? $this->nullableString($rowData['masa_kerja'] ?? ''),
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

    private function buildJabatanLookupByName(): array
    {
        $rows = (new MstJabatanModel())
            ->select('id, jabatan')
            ->findAll();

        $lookup = [];
        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            $jabatan = trim((string) ($row['jabatan'] ?? ''));
            if ($id <= 0 || $jabatan === '') {
                continue;
            }

            $lookup[strtolower($jabatan)] = $id;
        }

        return $lookup;
    }

    private function applyMasaKerjaDefaults(array $items): array
    {
        foreach ($items as &$item) {
            if (! is_array($item)) {
                continue;
            }

            $computedMasaKerja = $this->resolveMasaKerjaFromNip((string) ($item['nip'] ?? ''));
            if ($computedMasaKerja !== null) {
                $item['masa_kerja'] = $computedMasaKerja;
            }
        }

        return $items;
    }

    private function resolveMasaKerjaFromNip(string $nip): ?string
    {
        $digits = preg_replace('/\D+/', '', $nip) ?? '';
        if (strlen($digits) < 14) {
            return null;
        }

        $yearPart = substr($digits, 8, 4);
        $monthPart = substr($digits, 12, 2);

        $year = (int) $yearPart;
        $month = (int) $monthPart;

        if ($year < 1950 || $year > (int) date('Y') || $month < 1 || $month > 12) {
            return null;
        }

        $tmtDate = \DateTimeImmutable::createFromFormat('Y-m-d', sprintf('%04d-%02d-01', $year, $month));
        if (! $tmtDate || $tmtDate->format('Y-m-d') !== sprintf('%04d-%02d-01', $year, $month)) {
            return null;
        }

        $today = new \DateTimeImmutable('today');
        if ($tmtDate > $today) {
            return null;
        }

        $diff = $tmtDate->diff($today);
        $parts = [];
        if ($diff->y > 0) {
            $parts[] = $diff->y . ' Tahun';
        }
        if ($diff->m > 0) {
            $parts[] = $diff->m . ' Bulan';
        }

        if ($parts === []) {
            $parts[] = '0 Bulan';
        }

        return implode(' ', $parts);
    }

    private function resolveOrCreateJabatanId(string $jabatanName, array &$lookup, string $username, string $timestamp): ?int
    {
        $jabatanName = trim($jabatanName);
        if ($jabatanName === '') {
            return null;
        }

        $key = strtolower($jabatanName);
        if (isset($lookup[$key])) {
            return (int) $lookup[$key];
        }

        $model = new MstJabatanModel();
        $existing = $model
            ->select('id')
            ->where('LOWER(jabatan)', $key)
            ->first();

        if (is_array($existing) && (int) ($existing['id'] ?? 0) > 0) {
            $lookup[$key] = (int) $existing['id'];
            return (int) $existing['id'];
        }

        $insertId = $model->insert([
            'jabatan' => $jabatanName,
            'jenis_jabatan' => 'Fungsional',
            'deskripsi_jabatan' => null,
            'is_active' => 1,
            'created_by' => $username,
            'created_date' => $timestamp,
            'updated_by' => $username,
            'updated_date' => $timestamp,
        ]);

        if (is_numeric($insertId) && (int) $insertId > 0) {
            $lookup[$key] = (int) $insertId;
            return (int) $insertId;
        }

        $existing = $model
            ->select('id')
            ->where('LOWER(jabatan)', $key)
            ->first();

        if (is_array($existing) && (int) ($existing['id'] ?? 0) > 0) {
            $lookup[$key] = (int) $existing['id'];
            return (int) $existing['id'];
        }

        return null;
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
            'jenis_pegawai', 'jenis', 'jenis_kepegawaian' => 'jenis_pegawai',
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