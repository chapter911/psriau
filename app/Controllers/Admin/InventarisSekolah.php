<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\BmnMadrasahModel;
use App\Models\MstSekolahModel;
use App\Models\MstPaketModel;
use CodeIgniter\HTTP\RedirectResponse;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class InventarisSekolah extends BaseController
{
    private const MENU_LINK = 'admin/inventaris/sekolah';

    protected BmnMadrasahModel $bmnMadrasahModel;

    public function __construct()
    {
        $this->bmnMadrasahModel = new BmnMadrasahModel();
    }

    public function index()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);

        $activeTab       = trim((string) $this->request->getGet('tab')) ?: 'rekap';
        $filterPaketId   = trim((string) $this->request->getGet('paket_id'));
        $filterStatus    = trim((string) $this->request->getGet('status'));
        $filterKabupaten = trim((string) $this->request->getGet('kabupaten'));
        $filterBarang    = trim((string) $this->request->getGet('barang'));
        $keyword         = trim((string) $this->request->getGet('keyword'));

        // KPI Stats
        $stats = $this->bmnMadrasahModel->getSummaryStats();

        // Data Paket PHTC
        $paketList = $this->bmnMadrasahModel->getPaketList($filterPaketId ? (int) $filterPaketId : null);

        // Data Madrasah Sasaran & DED
        $sekolahList = $this->bmnMadrasahModel->getSekolahList(
            $filterPaketId,
            $filterStatus,
            $filterKabupaten,
            $keyword
        );

        // Data ASP (Peralatan & Mesin)
        $aspList = $this->bmnMadrasahModel->getAspList(
            $filterPaketId,
            $filterBarang,
            $keyword
        );

        // Data Master Sekolah (MstSekolah yang sudah ada)
        $db = db_connect();
        $builderMaster = $db->table('mst_sekolah s')
            ->select('s.npsn, s.nama, s.jenis, s.nsm, s.kabupaten, s.kecamatan, s.paket_id, mp.nama_paket AS nama_paket')
            ->join('mst_paket mp', 'mp.id = s.paket_id', 'left');

        if ($filterPaketId !== '' && $filterPaketId !== '*') {
            $builderMaster->where('s.paket_id', (int) $filterPaketId);
        }
        if ($filterKabupaten !== '' && $filterKabupaten !== '*') {
            $builderMaster->where('s.kabupaten', $filterKabupaten);
        }
        if ($keyword !== '') {
            $builderMaster->groupStart()
                ->like('s.nama', $keyword)
                ->orLike('s.npsn', $keyword)
                ->orLike('s.kecamatan', $keyword)
                ->groupEnd();
        }

        $masterItems = $builderMaster->orderBy('s.nama', 'ASC')->get()->getResultArray();

        // Dropdown options
        $paketModel = new MstPaketModel();
        $pakets = $paketModel->where('is_active', 1)->orderBy('id', 'ASC')->findAll();

        $kabupatenOptions = $this->bmnMadrasahModel->getDistinctKabupatenSekolah();
        $barangOptions    = $this->bmnMadrasahModel->getDistinctBarangAsp();

        return view('admin/inventaris/sekolah/index', [
            'pageTitle'        => 'Inventaris Sarpras & Hasil Penelitian BMN Madrasah (PHTC)',
            'activeTab'        => $activeTab,
            'stats'            => $stats,
            'paketList'        => $paketList,
            'sekolahList'      => $sekolahList,
            'aspList'          => $aspList,
            'masterItems'      => $masterItems,
            'pakets'           => $pakets,
            'kabupatenOptions' => $kabupatenOptions,
            'barangOptions'    => $barangOptions,
            'filterPaketId'    => $filterPaketId,
            'filterStatus'     => $filterStatus,
            'filterKabupaten'  => $filterKabupaten,
            'filterBarang'     => $filterBarang,
            'keyword'          => $keyword,
            'can_add'          => (bool) ($menuPermissions['add'] ?? false),
            'can_edit'         => (bool) ($menuPermissions['edit'] ?? false),
            'can_export'       => (bool) ($menuPermissions['export'] ?? false),
        ]);
    }

    /**
     * Export Excel Hasil Penelitian BMN Madrasah PHTC
     */
    public function exportExcel()
    {
        $permissions = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! ($permissions['export'] ?? false)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses untuk ekspor data.');
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('Satker Pelaksanaan Prasarana Strategis Riau')
            ->setTitle('Rincian Hasil Penelitian BMN Madrasah PHTC')
            ->setSubject('Hasil Penelitian BMN PHTC Riau 1 s/d 9');

        // Styles
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '003366']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
        ];
        $dataBorder = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D0D0D0']]],
        ];
        $totalStyle = [
            'font' => ['bold' => true, 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E6F0FA']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
        ];

        // ---------------------------------------------------------------------
        // SHEET 1: REKAPITULASI PAKET BMN PHTC
        // ---------------------------------------------------------------------
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('REKAP_BMN_PHTC');

        $sheet1->setCellValue('A1', 'RINCIAN HASIL PENELITIAN BARANG MILIK NEGARA (BMN)');
        $sheet1->setCellValue('A2', 'PEKERJAAN REHABILITASI DAN RENOVASI MADRASAH (PHTC) PROVINSI RIAU');
        $sheet1->setCellValue('A3', 'Satker Pelaksanaan Prasarana Strategis Provinsi Riau - Tahun Anggaran 2025/2026');
        $sheet1->getStyle('A1:A3')->getFont()->setBold(true);
        $sheet1->getStyle('A1')->getFont()->setSize(14);
        $sheet1->getStyle('A2')->getFont()->setSize(11);

        $headers1 = [
            'A5' => 'No',
            'B5' => 'Uraian Paket Kegiatan',
            'C5' => 'Tahun',
            'D5' => 'Kode BMN',
            'E5' => 'Jenis BMN',
            'F5' => 'Wilayah / Lokasi',
            'G5' => 'Jml Madrasah',
            'H5' => 'Kontrak Fisik (ATR) (Rp)',
            'I5' => 'MK / Supervisi (Rp)',
            'J5' => 'Peralatan & Mesin (ASP) (Rp)',
            'K5' => 'Total ATR + ASP (Rp)',
        ];
        foreach ($headers1 as $col => $title) {
            $sheet1->setCellValue($col, $title);
        }
        $sheet1->getStyle('A5:K5')->applyFromArray($headerStyle);
        $sheet1->getRowDimension(5)->setRowHeight(28);

        $pakets = $this->bmnMadrasahModel->getPaketList();
        $r1 = 6;
        $totalFisik = 0;
        $totalMk = 0;
        $totalAsp = 0;
        $totalGab = 0;

        foreach ($pakets as $p) {
            $sheet1->setCellValue('A' . $r1, $p['no_paket']);
            $sheet1->setCellValue('B' . $r1, $p['nama_paket']);
            $sheet1->setCellValue('C' . $r1, $p['tahun_anggaran']);
            $sheet1->setCellValue('D' . $r1, $p['kode_bmn']);
            $sheet1->setCellValue('E' . $r1, $p['jenis_bmn']);
            $sheet1->setCellValue('F' . $r1, $p['wilayah']);
            $sheet1->setCellValue('G' . $r1, (int) $p['jumlah_madrasah']);
            $sheet1->setCellValue('H' . $r1, (float) $p['nilai_kontrak_fisik']);
            $sheet1->setCellValue('I' . $r1, (float) $p['nilai_mk_supervisi']);
            $sheet1->setCellValue('J' . $r1, (float) $p['total_asp_nilai']);
            $sheet1->setCellValue('K' . $r1, (float) $p['total_gabungan']);

            $sheet1->getStyle("A{$r1}:K{$r1}")->applyFromArray($dataBorder);
            $sheet1->getStyle("A{$r1}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle("C{$r1}:D{$r1}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle("G{$r1}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle("H{$r1}:K{$r1}")->getNumberFormat()->setFormatCode('#,##0');

            $totalFisik += (float) $p['nilai_kontrak_fisik'];
            $totalMk    += (float) $p['nilai_mk_supervisi'];
            $totalAsp   += (float) $p['total_asp_nilai'];
            $totalGab   += (float) $p['total_gabungan'];
            $r1++;
        }

        // Row Total
        $sheet1->setCellValue('A' . $r1, 'TOTAL KESELURUHAN (PAKET 1 - 9)');
        $sheet1->mergeCells("A{$r1}:F{$r1}");
        $sheet1->setCellValue('G' . $r1, count($this->bmnMadrasahModel->getSekolahList()));
        $sheet1->setCellValue('H' . $r1, $totalFisik);
        $sheet1->setCellValue('I' . $r1, $totalMk);
        $sheet1->setCellValue('J' . $r1, $totalAsp);
        $sheet1->setCellValue('K' . $r1, $totalGab);

        $sheet1->getStyle("A{$r1}:K{$r1}")->applyFromArray($totalStyle);
        $sheet1->getStyle("A{$r1}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet1->getStyle("G{$r1}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet1->getStyle("H{$r1}:K{$r1}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet1->getRowDimension($r1)->setRowHeight(24);

        foreach (range('A', 'K') as $col) {
            $sheet1->getColumnDimension($col)->setAutoSize(true);
        }

        // ---------------------------------------------------------------------
        // SHEET 2: DAFTAR MADRASAH & DED
        // ---------------------------------------------------------------------
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('MADRASAH_DAN_DED');

        $sheet2->setCellValue('A1', 'DAFTAR MADRASAH SASARAN & STATUS ACUAN DOKUMEN DED');
        $sheet2->setCellValue('A2', 'Pekerjaan Rehabilitasi & Renovasi Madrasah PHTC Provinsi Riau (Paket 1 - 9)');
        $sheet2->getStyle('A1:A2')->getFont()->setBold(true);
        $sheet2->getStyle('A1')->getFont()->setSize(13);

        $headers2 = [
            'A4' => 'No',
            'B4' => 'Paket Kegiatan',
            'C4' => 'Nama Madrasah / Sekolah',
            'D4' => 'Status',
            'E4' => 'Kabupaten / Kota',
            'F4' => 'Status Acuan Dokumen DED (Perencanaan)',
        ];
        foreach ($headers2 as $col => $title) {
            $sheet2->setCellValue($col, $title);
        }
        $sheet2->getStyle('A4:F4')->applyFromArray($headerStyle);
        $sheet2->getRowDimension(4)->setRowHeight(25);

        $sekolahRows = $this->bmnMadrasahModel->getSekolahList();
        $r2 = 5;
        $no2 = 1;
        foreach ($sekolahRows as $s) {
            $sheet2->setCellValue('A' . $r2, $no2++);
            $sheet2->setCellValue('B' . $r2, 'Paket ' . $s['no_paket']);
            $sheet2->setCellValue('C' . $r2, $s['nama_sekolah']);
            $sheet2->setCellValue('D' . $r2, $s['status_sekolah']);
            $sheet2->setCellValue('E' . $r2, $s['kabupaten']);
            $sheet2->setCellValue('F' . $r2, $s['acuan_ded']);

            $sheet2->getStyle("A{$r2}:F{$r2}")->applyFromArray($dataBorder);
            $sheet2->getStyle("A{$r2}:B{$r2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle("D{$r2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $r2++;
        }
        foreach (range('A', 'F') as $col) {
            $sheet2->getColumnDimension($col)->setAutoSize(true);
        }

        // ---------------------------------------------------------------------
        // SHEET 3: RINCIAN PERALATAN & MESIN (ASP / MEUBELAIR)
        // ---------------------------------------------------------------------
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('RINCIAN_ASP_MEUBELAIR');

        $sheet3->setCellValue('A1', 'RINCIAN HASIL PENELITIAN BMN - PERALATAN DAN MESIN (ASP / MEUBELAIR)');
        $sheet3->setCellValue('A2', 'Pengadaan Meubelair Sarana Ruang Kelas & Guru - Madrasah PHTC Provinsi Riau');
        $sheet3->getStyle('A1:A2')->getFont()->setBold(true);
        $sheet3->getStyle('A1')->getFont()->setSize(13);

        $headers3 = [
            'A4' => 'No',
            'B4' => 'Paket',
            'C4' => 'Uraian Barang BMN',
            'D4' => 'Tahun',
            'E4' => 'Jenis BMN',
            'F4' => 'Kode BMN',
            'G4' => 'Merk / Tipe Spesifikasi',
            'H4' => 'Kondisi Baik',
            'I4' => 'Satuan',
            'J4' => 'Volume (Unit)',
            'K4' => 'Harga Satuan Fisik (Rp)',
            'L4' => 'Total Nilai Kontrak Fisik (Rp)',
        ];
        foreach ($headers3 as $col => $title) {
            $sheet3->setCellValue($col, $title);
        }
        $sheet3->getStyle('A4:L4')->applyFromArray($headerStyle);
        $sheet3->getRowDimension(4)->setRowHeight(25);

        $aspRows = $this->bmnMadrasahModel->getAspList();
        $r3 = 5;
        $no3 = 1;
        $totalVol = 0;
        $totalNilaiAsp = 0;

        foreach ($aspRows as $a) {
            $sheet3->setCellValue('A' . $r3, $no3++);
            $sheet3->setCellValue('B' . $r3, 'Paket ' . $a['no_paket']);
            $sheet3->setCellValue('C' . $r3, $a['nama_barang']);
            $sheet3->setCellValue('D' . $r3, $a['tahun']);
            $sheet3->setCellValue('E' . $r3, $a['jenis_bmn']);
            $sheet3->setCellValue('F' . $r3, $a['kode_bmn']);
            $sheet3->setCellValue('G' . $r3, $a['merk_tipe']);
            $sheet3->setCellValue('H' . $r3, $a['kondisi_baik']);
            $sheet3->setCellValue('I' . $r3, $a['satuan']);
            $sheet3->setCellValue('J' . $r3, (int) $a['jumlah']);
            $sheet3->setCellValue('K' . $r3, (float) $a['harga_satuan']);
            $sheet3->setCellValue('L' . $r3, (float) $a['total_nilai']);

            $sheet3->getStyle("A{$r3}:L{$r3}")->applyFromArray($dataBorder);
            $sheet3->getStyle("A{$r3}:B{$r3}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet3->getStyle("D{$r3}:F{$r3}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet3->getStyle("H{$r3}:I{$r3}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet3->getStyle("J{$r3}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet3->getStyle("J{$r3}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet3->getStyle("K{$r3}:L{$r3}")->getNumberFormat()->setFormatCode('#,##0');

            $totalVol      += (int) $a['jumlah'];
            $totalNilaiAsp += (float) $a['total_nilai'];
            $r3++;
        }

        // Total ASP
        $sheet3->setCellValue('A' . $r3, 'TOTAL KESELURUHAN PERALATAN & MESIN (ASP)');
        $sheet3->mergeCells("A{$r3}:I{$r3}");
        $sheet3->setCellValue('J' . $r3, $totalVol);
        $sheet3->setCellValue('L' . $r3, $totalNilaiAsp);

        $sheet3->getStyle("A{$r3}:L{$r3}")->applyFromArray($totalStyle);
        $sheet3->getStyle("A{$r3}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet3->getStyle("J{$r3}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet3->getStyle("L{$r3}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet3->getRowDimension($r3)->setRowHeight(24);

        foreach (range('A', 'L') as $col) {
            $sheet3->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'Hasil_Penelitian_BMN_Madrasah_PHTC_Riau_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Cetak PDF Dokumen Rekapitulasi Hasil Penelitian BMN Madrasah PHTC
     */
    public function cetakPdf()
    {
        $permissions = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! ($permissions['export'] ?? false)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses cetak dokumen.');
        }

        $stats       = $this->bmnMadrasahModel->getSummaryStats();
        $paketList   = $this->bmnMadrasahModel->getPaketList();
        $sekolahList = $this->bmnMadrasahModel->getSekolahList();
        $aspList     = $this->bmnMadrasahModel->getAspList();

        $html = view('admin/inventaris/sekolah/pdf_rekapitulasi', [
            'stats'       => $stats,
            'paketList'   => $paketList,
            'sekolahList' => $sekolahList,
            'aspList'     => $aspList,
        ]);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = 'Hasil_Penelitian_BMN_Madrasah_PHTC_Riau_' . date('Ymd') . '.pdf';
        $dompdf->stream($filename, ['Attachment' => false]);
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
