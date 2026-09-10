<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\InventarisTidakTerdataModel;
use App\Models\MstRuanganModel;
use CodeIgniter\HTTP\RedirectResponse;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class InventarisTidakTerdata extends BaseController
{
    private const MENU_LINK = 'admin/inventaris/tidak-terdata';

    public function index()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $model = new InventarisTidakTerdataModel();
        $ruanganModel = new MstRuanganModel();

        $filters = [
            'ruangan_id' => $this->request->getGet('ruangan_id'),
            'kondisi'    => $this->request->getGet('kondisi'),
            'q'          => trim((string) ($this->request->getGet('q') ?? '')),
        ];

        $items = $model->getWithRelations($filters);
        $summary = $model->getSummaryStats();
        $ruanganList = $ruanganModel->orderBy('nama_ruangan', 'ASC')->findAll();
        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);

        return view('admin/inventaris/tidak_terdata/index', [
            'pageTitle'        => 'Aset Tidak Terdata',
            'items'            => $items,
            'summary'          => $summary,
            'ruanganList'      => $ruanganList,
            'filters'          => $filters,
            'menuPermissions'  => $menuPermissions,
        ]);
    }

    public function store()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! ($menuPermissions['add'] ?? false)) {
            return redirect()->to('/admin/inventaris/tidak-terdata')->with('error', 'Anda tidak memiliki hak akses untuk menambah data.');
        }

        $rules = [
            'nama_barang' => 'required|min_length[2]|max_length[150]',
            'jumlah'      => 'required|is_natural_no_zero',
            'satuan'      => 'permit_empty|max_length[50]',
            'kondisi'     => 'required|in_list[baik,rusak_ringan,rusak_berat]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/admin/inventaris/tidak-terdata')
                ->withInput()
                ->with('error', implode(' ', $this->validator->getErrors()));
        }

        $ruanganId = $this->request->getPost('ruangan_id');
        $ruanganId = (! empty($ruanganId) && is_numeric($ruanganId)) ? (int) $ruanganId : null;

        $lokasiPenempatan = trim((string) ($this->request->getPost('lokasi_penempatan') ?? ''));
        if ($ruanganId !== null && empty($lokasiPenempatan)) {
            $ruangan = (new MstRuanganModel())->find($ruanganId);
            if ($ruangan) {
                $lokasiPenempatan = $ruangan['nama_ruangan'];
            }
        }

        $data = [
            'nama_barang'       => trim((string) $this->request->getPost('nama_barang')),
            'jumlah'            => (int) $this->request->getPost('jumlah'),
            'satuan'            => trim((string) ($this->request->getPost('satuan') ?: 'Buah')),
            'merk_tipe'         => trim((string) ($this->request->getPost('merk_tipe') ?? '')) ?: null,
            'tahun_perolehan'   => trim((string) ($this->request->getPost('tahun_perolehan') ?? '')) ?: null,
            'ruangan_id'        => $ruanganId,
            'lokasi_penempatan' => $lokasiPenempatan ?: null,
            'kondisi'           => $this->request->getPost('kondisi'),
            'keterangan'        => trim((string) ($this->request->getPost('keterangan') ?? '')) ?: null,
            'petugas_nama'      => trim((string) ($this->request->getPost('petugas_nama') ?? '')) ?: 'Hendrick Bastiar',
            'petugas_nip'       => trim((string) ($this->request->getPost('petugas_nip') ?? '')) ?: '197810162025211023',
            'created_by'        => session()->get('user_id') ?? 1,
            'updated_by'        => session()->get('user_id') ?? 1,
        ];

        $model = new InventarisTidakTerdataModel();
        $model->insert($data);

        return redirect()->to('/admin/inventaris/tidak-terdata')->with('success', 'Data aset tidak terdata berhasil ditambahkan.');
    }

    public function update($id = null)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! ($menuPermissions['edit'] ?? false)) {
            return redirect()->to('/admin/inventaris/tidak-terdata')->with('error', 'Anda tidak memiliki hak akses untuk mengubah data.');
        }

        $id = (int) $id;
        if ($id <= 0) {
            return redirect()->to('/admin/inventaris/tidak-terdata')->with('error', 'ID data tidak valid.');
        }

        $model = new InventarisTidakTerdataModel();
        $existing = $model->find($id);
        if (! $existing) {
            return redirect()->to('/admin/inventaris/tidak-terdata')->with('error', 'Data tidak ditemukan.');
        }

        $rules = [
            'nama_barang' => 'required|min_length[2]|max_length[150]',
            'jumlah'      => 'required|is_natural_no_zero',
            'satuan'      => 'permit_empty|max_length[50]',
            'kondisi'     => 'required|in_list[baik,rusak_ringan,rusak_berat]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/admin/inventaris/tidak-terdata')
                ->withInput()
                ->with('error', implode(' ', $this->validator->getErrors()));
        }

        $ruanganId = $this->request->getPost('ruangan_id');
        $ruanganId = (! empty($ruanganId) && is_numeric($ruanganId)) ? (int) $ruanganId : null;

        $lokasiPenempatan = trim((string) ($this->request->getPost('lokasi_penempatan') ?? ''));
        if ($ruanganId !== null && empty($lokasiPenempatan)) {
            $ruangan = (new MstRuanganModel())->find($ruanganId);
            if ($ruangan) {
                $lokasiPenempatan = $ruangan['nama_ruangan'];
            }
        }

        $data = [
            'nama_barang'       => trim((string) $this->request->getPost('nama_barang')),
            'jumlah'            => (int) $this->request->getPost('jumlah'),
            'satuan'            => trim((string) ($this->request->getPost('satuan') ?: 'Buah')),
            'merk_tipe'         => trim((string) ($this->request->getPost('merk_tipe') ?? '')) ?: null,
            'tahun_perolehan'   => trim((string) ($this->request->getPost('tahun_perolehan') ?? '')) ?: null,
            'ruangan_id'        => $ruanganId,
            'lokasi_penempatan' => $lokasiPenempatan ?: null,
            'kondisi'           => $this->request->getPost('kondisi'),
            'keterangan'        => trim((string) ($this->request->getPost('keterangan') ?? '')) ?: null,
            'petugas_nama'      => trim((string) ($this->request->getPost('petugas_nama') ?? '')) ?: 'Hendrick Bastiar',
            'petugas_nip'       => trim((string) ($this->request->getPost('petugas_nip') ?? '')) ?: '197810162025211023',
            'updated_by'        => session()->get('user_id') ?? 1,
        ];

        $model->update($id, $data);

        return redirect()->to('/admin/inventaris/tidak-terdata')->with('success', 'Data aset tidak terdata berhasil diperbarui.');
    }

    public function delete($id = null)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! ($menuPermissions['delete'] ?? false)) {
            return redirect()->to('/admin/inventaris/tidak-terdata')->with('error', 'Anda tidak memiliki hak akses untuk menghapus data.');
        }

        $id = (int) $id;
        if ($id <= 0) {
            return redirect()->to('/admin/inventaris/tidak-terdata')->with('error', 'ID data tidak valid.');
        }

        $model = new InventarisTidakTerdataModel();
        $existing = $model->find($id);
        if (! $existing) {
            return redirect()->to('/admin/inventaris/tidak-terdata')->with('error', 'Data tidak ditemukan.');
        }

        $model->delete($id);

        return redirect()->to('/admin/inventaris/tidak-terdata')->with('success', 'Data aset tidak terdata berhasil dihapus.');
    }

    public function cetakPdf()
    {
        return $this->generatePdfResponse('inline');
    }

    public function exportPdf()
    {
        return $this->generatePdfResponse('attachment');
    }

    private function generatePdfResponse(string $disposition = 'inline')
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! ($menuPermissions['export'] ?? false)) {
            return redirect()->to('/admin/inventaris/tidak-terdata')->with('error', 'Anda tidak memiliki hak akses untuk mengekspor data.');
        }

        $model = new InventarisTidakTerdataModel();
        $filters = [
            'ruangan_id' => $this->request->getGet('ruangan_id'),
            'kondisi'    => $this->request->getGet('kondisi'),
            'q'          => trim((string) ($this->request->getGet('q') ?? '')),
        ];

        $items = $model->getWithRelations($filters);
        $summary = $model->getSummaryStats();

        // Cari logo PU untuk kop
        $logoPuPath = FCPATH . 'uploads/branding/1774740768_77e8482499660c14c637.png';
        if (! file_exists($logoPuPath)) {
            $logoPuPath = FCPATH . 'assets/images/pu-logo.png';
        }
        $logoPuBase64 = '';
        if (file_exists($logoPuPath)) {
            $logoPuBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPuPath));
        }

        $data = [
            'items'        => $items,
            'summary'      => $summary,
            'logoPuBase64' => $logoPuBase64,
            'tanggalCetak' => date('d F Y'),
        ];

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $html = view('admin/inventaris/tidak_terdata/pdf_tidak_terdata', $data);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'Daftar_Aset_Tidak_Terdata_' . date('Ymd_His') . '.pdf';

        if (ob_get_length()) {
            ob_clean();
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', $disposition . '; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    public function exportExcel()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! ($menuPermissions['export'] ?? false)) {
            return redirect()->to('/admin/inventaris/tidak-terdata')->with('error', 'Anda tidak memiliki hak akses untuk mengekspor data.');
        }

        $model = new InventarisTidakTerdataModel();
        $filters = [
            'ruangan_id' => $this->request->getGet('ruangan_id'),
            'kondisi'    => $this->request->getGet('kondisi'),
            'q'          => trim((string) ($this->request->getGet('q') ?? '')),
        ];

        $items = $model->getWithRelations($filters);
        $summary = $model->getSummaryStats();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Aset Tidak Terdata');

        // Header Title
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'DAFTAR ASET TIDAK TERDATA');
        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A2', 'SATUAN KERJA PELAKSANAAN PRASARANA PERMUKIMAN STRATEGIS PROVINSI RIAU');
        $sheet->mergeCells('A3:H3');
        $sheet->setCellValue('A3', 'Tanggal Unduh: ' . date('d/m/Y H:i') . ' WIB | Total: ' . $summary['total_item'] . ' Item (' . $summary['total_buah'] . ' Buah)');

        $sheet->getStyle('A1:H2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(9);
        $sheet->getStyle('A1:H3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Column Headers
        $headers = [
            'A5' => 'NO',
            'B5' => 'NAMA BARANG',
            'C5' => 'BUAH (QTY)',
            'D5' => 'MERK / TYPE',
            'E5' => 'TAHUN PEROLEHAN',
            'F5' => 'LOKASI ASET',
            'G5' => 'KONDISI',
            'H5' => 'KETERANGAN',
        ];

        foreach ($headers as $cell => $title) {
            $sheet->setCellValue($cell, $title);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E3A8A'], // PU Navy Blue
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
            ],
        ];
        $sheet->getStyle('A5:H5')->applyFromArray($headerStyle);
        $sheet->getRowDimension(5)->setRowHeight(28);

        // Data Rows
        $rowNum = 6;
        $no = 1;
        $totalBuah = 0;

        foreach ($items as $item) {
            $sheet->setCellValue('A' . $rowNum, $no);
            $sheet->setCellValue('B' . $rowNum, $item['nama_barang']);
            $sheet->setCellValue('C' . $rowNum, (int) $item['jumlah']);
            $sheet->setCellValue('D' . $rowNum, $item['merk_tipe'] ?: '-');
            $sheet->setCellValue('E' . $rowNum, $item['tahun_perolehan'] ?: '-');

            // Lokasi Aset
            $lokasi = $item['nama_ruangan'] ?? $item['lokasi_penempatan'] ?? '-';
            $sheet->setCellValue('F' . $rowNum, $lokasi);

            // Kondisi
            $kondisiText = ucfirst(str_replace('_', ' ', $item['kondisi']));
            $sheet->setCellValue('G' . $rowNum, $kondisiText);

            $sheet->setCellValue('H' . $rowNum, $item['keterangan'] ?: '-');

            $rowStyle = [
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']],
                ],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ];
            $sheet->getStyle('A' . $rowNum . ':H' . $rowNum)->applyFromArray($rowStyle);

            $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $totalBuah += (int) $item['jumlah'];
            $rowNum++;
            $no++;
        }

        // Summary Row
        $sheet->setCellValue('A' . $rowNum, 'TOTAL');
        $sheet->mergeCells('A' . $rowNum . ':B' . $rowNum);
        $sheet->setCellValue('C' . $rowNum, $totalBuah);
        $sheet->setCellValue('D' . $rowNum, '');
        $sheet->mergeCells('D' . $rowNum . ':H' . $rowNum);

        $totalStyle = [
            'font' => ['bold' => true, 'size' => 10],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FEF08A'], // Soft yellow highlight
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
            ],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ];
        $sheet->getStyle('A' . $rowNum . ':H' . $rowNum)->applyFromArray($totalStyle);
        $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension($rowNum)->setRowHeight(24);

        // Signatures Block
        $signRow = $rowNum + 3;
        $sheet->setCellValue('F' . $signRow, 'Pekanbaru, ' . date('d F Y'));
        $sheet->setCellValue('F' . ($signRow + 1), 'Petugas Aset Tetap');
        $sheet->setCellValue('F' . ($signRow + 2), 'Satker Pelaksanaan Prasarana Strategis Riau');

        $petugasNama = ! empty($items[0]['petugas_nama']) ? $items[0]['petugas_nama'] : 'Hendrick Bastiar';
        $petugasNip  = ! empty($items[0]['petugas_nip']) ? $items[0]['petugas_nip'] : '197810162025211023';

        $sheet->setCellValue('F' . ($signRow + 6), $petugasNama);
        $sheet->setCellValue('F' . ($signRow + 7), 'NIP. ' . $petugasNip);

        $sheet->getStyle('F' . ($signRow + 6))->getFont()->setBold(true)->setUnderline(true);
        $sheet->getStyle('F' . $signRow . ':F' . ($signRow + 7))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Auto width
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Daftar_Aset_Tidak_Terdata_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
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
