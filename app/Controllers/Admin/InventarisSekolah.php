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

        $activeTab       = trim((string) $this->request->getGet('tab'));
        if (empty($activeTab) || $activeTab === 'rekap') {
            $activeTab = 'riau-all';
        }

        $filterPaketId   = trim((string) $this->request->getGet('paket_id'));
        $filterStatus    = trim((string) $this->request->getGet('status'));
        $filterKabupaten = trim((string) $this->request->getGet('kabupaten'));
        $filterBarang    = trim((string) $this->request->getGet('barang'));
        $keyword         = trim((string) $this->request->getGet('keyword'));

        // KPI Stats
        $stats = $this->bmnMadrasahModel->getSummaryStats();

        // Data Paket PHTC
        $allPakets = $this->bmnMadrasahModel->getPaketList();
        $paketByNo = [];
        foreach ($allPakets as $p) {
            $paketByNo[(int) $p['no_paket']] = $p;
        }

        $paketList = $this->bmnMadrasahModel->getPaketList($filterPaketId ? (int) $filterPaketId : null);

        // Data Madrasah Sasaran & DED
        $allSekolah = $this->bmnMadrasahModel->getSekolahList();
        $sekolahByNo = [];
        foreach ($allSekolah as $s) {
            $sekolahByNo[(int) $s['no_paket']][] = $s;
        }

        $sekolahList = $this->bmnMadrasahModel->getSekolahList(
            $filterPaketId,
            $filterStatus,
            $filterKabupaten,
            $keyword
        );

        // Data ASP (Peralatan & Mesin)
        $allAsp = $this->bmnMadrasahModel->getAspList();
        $aspByNo = [];
        foreach ($allAsp as $a) {
            $aspByNo[(int) $a['no_paket']][] = $a;
        }

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
            'allPakets'        => $allPakets,
            'paketByNo'        => $paketByNo,
            'sekolahByNo'      => $sekolahByNo,
            'aspByNo'          => $aspByNo,
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
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E79']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
        ];
        $dataBorder = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D0D0D0']]],
        ];
        $subtotalStyle = [
            'font' => ['bold' => true, 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
        ];
        $totalStyle = [
            'font' => ['bold' => true, 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B4C6E7']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '000000']]],
        ];

        $pakets = $this->bmnMadrasahModel->getPaketList();
        $sekolahAll = $this->bmnMadrasahModel->getSekolahList();
        $aspAll = $this->bmnMadrasahModel->getAspList();

        $sekolahByNo = [];
        foreach ($sekolahAll as $s) {
            $sekolahByNo[(int) $s['no_paket']][] = $s;
        }
        $aspByNo = [];
        foreach ($aspAll as $a) {
            $aspByNo[(int) $a['no_paket']][] = $a;
        }

        // ---------------------------------------------------------------------
        // SHEET 1: Riau All
        // ---------------------------------------------------------------------
        $sheetAll = $spreadsheet->getActiveSheet();
        $sheetAll->setTitle('Riau All');
        $this->buildRiauAllSheet($sheetAll, $pakets, $headerStyle, $dataBorder, $subtotalStyle, $totalStyle);

        // ---------------------------------------------------------------------
        // SHEET 2: Rekap PHTC
        // ---------------------------------------------------------------------
        $sheetRekap = $spreadsheet->createSheet();
        $sheetRekap->setTitle('Rekap PHTC');
        $this->buildRekapPhtcSheet($sheetRekap, $pakets, $headerStyle, $dataBorder, $totalStyle);

        // ---------------------------------------------------------------------
        // SHEETS 3..11: Riau 1 s/d Riau 9
        // ---------------------------------------------------------------------
        foreach ($pakets as $p) {
            $no = (int) $p['no_paket'];
            $sheetPaket = $spreadsheet->createSheet();
            $sheetPaket->setTitle('Riau ' . $no);
            $this->buildPaketSheet(
                $sheetPaket,
                $p,
                $sekolahByNo[$no] ?? [],
                $aspByNo[$no] ?? [],
                $headerStyle,
                $dataBorder,
                $subtotalStyle,
                $totalStyle
            );
        }

        // ---------------------------------------------------------------------
        // SHEET 12: DED_DAN_RAB_FISIK
        // ---------------------------------------------------------------------
        $sheetDed = $spreadsheet->createSheet();
        $sheetDed->setTitle('DED_DAN_RAB_FISIK');
        $this->buildDedDanRabSheet($sheetDed, $sekolahAll, $headerStyle, $dataBorder);

        // ---------------------------------------------------------------------
        // SHEET 13: KATALOG_MEUBELAIR_ASP
        // ---------------------------------------------------------------------
        $sheetAsp = $spreadsheet->createSheet();
        $sheetAsp->setTitle('KATALOG_MEUBELAIR_ASP');
        $this->buildKatalogAspSheet($sheetAsp, $aspAll, $headerStyle, $dataBorder, $totalStyle);

        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'RINCIAN_PHTC_PPS-Riau_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    private function buildRiauAllSheet($sheet, array $pakets, array $headerStyle, array $dataBorder, array $subtotalStyle, array $totalStyle)
    {
        $sheet->setCellValue('B1', 'RINCIAN HASIL PENELITIAN BARANG MILIK NEGARA');
        $sheet->setCellValue('B2', 'Riau');
        $sheet->setCellValue('B4', 'PEKERJAAN REHABILITASI DAN RENOVASI MADRASAH (PHTC)');
        $sheet->getStyle('B1:B4')->getFont()->setBold(true);

        $headers = [
            'B6' => 'No',
            'C6' => 'Uraian',
            'D6' => 'Tahun ',
            'E6' => 'Jenis BMN',
            'F6' => 'Kode BMN',
            'G6' => 'Merk/Type',
            'H6' => 'Baik',
            'I6' => 'RR',
            'J6' => 'RB',
            'K6' => 'Satuan',
            'L6' => 'Jumlah Barang/Luas',
            'M6' => 'Harga Satuan Kontrak Fisik (Rp)',
            'N6' => 'Total Nilai Kontrak Fisik (Rp)',
            'O6' => 'DED ',
            'P6' => 'MK/Supervisi',
            'Q6' => 'Nilai Perolehan',
            'R6' => 'Keterangan',
        ];
        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }
        $sheet->getStyle('B6:R6')->applyFromArray($headerStyle);
        $sheet->getRowDimension(6)->setRowHeight(26);

        $r = 7;
        $totalAtr = 0;
        foreach ($pakets as $p) {
            $sheet->setCellValue('B' . $r, $p['no_paket']);
            $sheet->setCellValue('C' . $r, 'REHABILITASI DAN RENOVASI MADRASAH PHTC PROVINSI RIAU ' . $p['no_paket']);
            $sheet->setCellValue('D' . $r, $p['tahun_anggaran']);
            $sheet->setCellValue('E' . $r, $p['jenis_bmn']);
            $sheet->setCellValue('F' . $r, $p['kode_bmn']);
            $sheet->setCellValue('G' . $r, '-');
            $sheet->setCellValue('H' . $r, 1);
            $sheet->setCellValue('I' . $r, 0);
            $sheet->setCellValue('J' . $r, 0);
            $sheet->setCellValue('K' . $r, 'Unit');
            $sheet->setCellValue('L' . $r, 1);
            $sheet->setCellValue('M' . $r, (float) $p['nilai_kontrak_fisik']);
            $sheet->setCellValue('N' . $r, (float) $p['nilai_kontrak_fisik']);
            $sheet->setCellValue('P' . $r, (float) $p['nilai_mk_supervisi']);

            $sheet->getStyle("B{$r}:R{$r}")->applyFromArray($dataBorder);
            $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$r}:K{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("M{$r}:N{$r}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("P{$r}")->getNumberFormat()->setFormatCode('#,##0');

            $totalAtr += (float) $p['nilai_kontrak_fisik'];
            $r++;
        }

        // Row 16: Jumlah ATR
        $sheet->setCellValue('B' . $r, 'Jumlah ATR');
        $sheet->setCellValue('L' . $r, count($pakets));
        $sheet->setCellValue('N' . $r, $totalAtr);
        $sheet->getStyle("B{$r}:R{$r}")->applyFromArray($subtotalStyle);
        $sheet->getStyle("L{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("N{$r}")->getNumberFormat()->setFormatCode('#,##0');
        $r += 5; // baris 21

        // Row 21: Jumlah Peralatan Mesin (ASP)
        $sheet->setCellValue('B' . $r, 'Jumlah Peralatan Mesin (ASP)');
        $sheet->setCellValue('L' . $r, 0);
        $sheet->setCellValue('N' . $r, 0);
        $sheet->getStyle("B{$r}:R{$r}")->applyFromArray($subtotalStyle);
        $sheet->getStyle("L{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("N{$r}")->getNumberFormat()->setFormatCode('#,##0');
        $r++; // baris 22

        // Row 22: TOTAL ATR + PM
        $sheet->setCellValue('B' . $r, 'TOTAL ATR + PM');
        $sheet->setCellValue('L' . $r, count($pakets));
        $sheet->setCellValue('N' . $r, $totalAtr);
        $sheet->getStyle("B{$r}:R{$r}")->applyFromArray($totalStyle);
        $sheet->getStyle("L{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("N{$r}")->getNumberFormat()->setFormatCode('#,##0');

        foreach (range('B', 'R') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getColumnDimension('A')->setWidth(3);
    }

    private function buildRekapPhtcSheet($sheet, array $pakets, array $headerStyle, array $dataBorder, array $totalStyle)
    {
        $headers = [
            'A1' => 'No',
            'B1' => 'Lokasi',
            'C1' => 'Jumlah ATR (Rp)',
            'D1' => 'Jumlah ASP (Rp)',
            'E1' => 'Total ATR + PM (Rp)',
        ];
        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }
        $sheet->getStyle('A1:E1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(25);

        $r = 2;
        $totalAtr = 0; $totalAsp = 0; $totalGab = 0;
        foreach ($pakets as $p) {
            $sheet->setCellValue('A' . $r, $p['no_paket']);
            $sheet->setCellValue('B' . $r, $p['wilayah']);
            $sheet->setCellValue('C' . $r, (float) $p['nilai_kontrak_fisik']);
            $sheet->setCellValue('D' . $r, (float) $p['total_asp_nilai']);
            $sheet->setCellValue('E' . $r, (float) $p['total_gabungan']);

            $sheet->getStyle("A{$r}:E{$r}")->applyFromArray($dataBorder);
            $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$r}:E{$r}")->getNumberFormat()->setFormatCode('#,##0');

            $totalAtr += (float) $p['nilai_kontrak_fisik'];
            $totalAsp += (float) $p['total_asp_nilai'];
            $totalGab += (float) $p['total_gabungan'];
            $r++;
        }

        // Row Total
        $sheet->setCellValue('A' . $r, 'TOTAL');
        $sheet->mergeCells("A{$r}:B{$r}");
        $sheet->setCellValue('C' . $r, $totalAtr);
        $sheet->setCellValue('D' . $r, $totalAsp);
        $sheet->setCellValue('E' . $r, $totalGab);
        $sheet->getStyle("A{$r}:E{$r}")->applyFromArray($totalStyle);
        $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("C{$r}:E{$r}")->getNumberFormat()->setFormatCode('#,##0');

        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    private function buildPaketSheet($sheet, array $paket, array $sekolahList, array $aspList, array $headerStyle, array $dataBorder, array $subtotalStyle, array $totalStyle)
    {
        $sheet->setCellValue('B1', 'RINCIAN HASIL PENELITIAN BARANG MILIK NEGARA');
        $sheet->setCellValue('B2', $paket['wilayah']);
        $sheet->setCellValue('B4', 'PEKERJAAN REHABILITASI DAN RENOVASI MADRASAH (PHTC)');
        $sheet->getStyle('B1:B4')->getFont()->setBold(true);

        $headers = [
            'B6' => 'No',
            'C6' => 'Uraian',
            'D6' => 'Tahun ',
            'E6' => 'Jenis BMN',
            'F6' => 'Kode BMN',
            'G6' => 'Merk/Type',
            'H6' => 'Baik',
            'I6' => 'RR',
            'J6' => 'RB',
            'K6' => 'Satuan',
            'L6' => 'Jumlah Barang/Luas',
            'M6' => 'Harga Satuan Kontrak Fisik (Rp)',
            'N6' => 'Total Nilai Kontrak Fisik (Rp)',
            'O6' => 'DED ',
            'P6' => 'MK/Supervisi',
            'Q6' => 'Nilai Perolehan',
            'R6' => 'Keterangan',
        ];
        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }
        $sheet->getStyle('B6:R6')->applyFromArray($headerStyle);
        $sheet->getRowDimension(6)->setRowHeight(26);

        // Baris 7: Item ATR
        $r = 7;
        $sheet->setCellValue('B' . $r, 1);
        $sheet->setCellValue('C' . $r, 'Rehabilitasi Dan Renovasi Madrasah PHTC Provinsi Riau ' . $paket['no_paket']);
        $sheet->setCellValue('D' . $r, $paket['tahun_anggaran']);
        $sheet->setCellValue('E' . $r, $paket['jenis_bmn']);
        $sheet->setCellValue('F' . $r, $paket['kode_bmn']);
        $sheet->setCellValue('G' . $r, '-');
        $sheet->setCellValue('H' . $r, 1);
        $sheet->setCellValue('I' . $r, 0);
        $sheet->setCellValue('J' . $r, 0);
        $sheet->setCellValue('K' . $r, 'Unit');
        $sheet->setCellValue('L' . $r, 1);
        $sheet->setCellValue('M' . $r, (float) $paket['nilai_kontrak_fisik']);
        $sheet->setCellValue('N' . $r, (float) $paket['nilai_kontrak_fisik']);
        $sheet->setCellValue('P' . $r, (float) $paket['nilai_mk_supervisi']);

        $sheet->getStyle("B{$r}:R{$r}")->applyFromArray($dataBorder);
        $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("D{$r}:K{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("M{$r}:N{$r}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("P{$r}")->getNumberFormat()->setFormatCode('#,##0');
        $r++;

        // Sub-baris Sekolah Sasaran
        foreach ($sekolahList as $s) {
            $sheet->setCellValue('C' . $r, $s['nama_sekolah']);
            $sheet->setCellValue('R' . $r, $s['status_sekolah']);
            $sheet->getStyle("B{$r}:R{$r}")->applyFromArray($dataBorder);
            $sheet->getStyle("C{$r}")->getAlignment()->setIndent(2);
            $sheet->getStyle("R{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $r++;
        }

        // Subtotal ATR
        $sheet->setCellValue('B' . $r, 'Jumlah ATR');
        $sheet->setCellValue('L' . $r, 1);
        $sheet->setCellValue('N' . $r, (float) $paket['nilai_kontrak_fisik']);
        $sheet->getStyle("B{$r}:R{$r}")->applyFromArray($subtotalStyle);
        $sheet->getStyle("L{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("N{$r}")->getNumberFormat()->setFormatCode('#,##0');
        $r++;

        // Item ASP Meubelair
        $noAsp = 2;
        $volAsp = 0;
        $nilaiAsp = 0;
        foreach ($aspList as $a) {
            $sheet->setCellValue('B' . $r, $noAsp++);
            $sheet->setCellValue('C' . $r, $a['nama_barang']);
            $sheet->setCellValue('D' . $r, $a['tahun']);
            $sheet->setCellValue('E' . $r, $a['jenis_bmn']);
            $sheet->setCellValue('F' . $r, $a['kode_bmn']);
            $sheet->setCellValue('G' . $r, $a['merk_tipe']);
            $sheet->setCellValue('H' . $r, (int) $a['kondisi_baik']);
            $sheet->setCellValue('I' . $r, 0);
            $sheet->setCellValue('J' . $r, 0);
            $sheet->setCellValue('K' . $r, $a['satuan']);
            $sheet->setCellValue('L' . $r, (int) $a['jumlah']);
            $sheet->setCellValue('M' . $r, (float) $a['harga_satuan']);
            $sheet->setCellValue('N' . $r, (float) $a['total_nilai']);

            $sheet->getStyle("B{$r}:R{$r}")->applyFromArray($dataBorder);
            $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$r}:K{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("L{$r}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("M{$r}:N{$r}")->getNumberFormat()->setFormatCode('#,##0');

            $volAsp += (int) $a['jumlah'];
            $nilaiAsp += (float) $a['total_nilai'];
            $r++;
        }

        // Subtotal ASP
        $sheet->setCellValue('B' . $r, 'Jumlah Peralatan Mesin (ASP)');
        $sheet->setCellValue('L' . $r, $volAsp);
        $sheet->setCellValue('N' . $r, $nilaiAsp);
        $sheet->getStyle("B{$r}:R{$r}")->applyFromArray($subtotalStyle);
        $sheet->getStyle("L{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("N{$r}")->getNumberFormat()->setFormatCode('#,##0');
        $r++;

        // TOTAL ATR + PM
        $sheet->setCellValue('B' . $r, 'TOTAL ATR + PM');
        $sheet->setCellValue('L' . $r, 1 + $volAsp);
        $sheet->setCellValue('N' . $r, (float) $paket['nilai_kontrak_fisik'] + $nilaiAsp);
        $sheet->getStyle("B{$r}:R{$r}")->applyFromArray($totalStyle);
        $sheet->getStyle("L{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("N{$r}")->getNumberFormat()->setFormatCode('#,##0');

        foreach (range('B', 'R') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getColumnDimension('A')->setWidth(3);
    }

    private function buildDedDanRabSheet($sheet, array $sekolahAll, array $headerStyle, array $dataBorder)
    {
        $headers = [
            'A1' => 'No',
            'B1' => 'Paket',
            'C1' => 'Nama Madrasah / Sekolah',
            'D1' => 'Status',
            'E1' => 'Kabupaten / Kota',
            'F1' => 'Nilai Fisik ATR (Rp)',
            'G1' => 'Acuan Dokumen DED (Perencanaan)',
            'H1' => 'Acuan Dokumen RAB Fisik (Final / Adendum)',
        ];
        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }
        $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(25);

        $r = 2;
        $no = 1;
        foreach ($sekolahAll as $s) {
            $sheet->setCellValue('A' . $r, $no++);
            $sheet->setCellValue('B' . $r, 'Paket ' . $s['no_paket']);
            $sheet->setCellValue('C' . $r, $s['nama_sekolah']);
            $sheet->setCellValue('D' . $r, $s['status_sekolah']);
            $sheet->setCellValue('E' . $r, $s['kabupaten']);
            $sheet->setCellValue('F' . $r, (float) ($s['nilai_rab_fisik'] ?? 0));
            $sheet->setCellValue('G' . $r, $s['acuan_ded'] ?: '-');
            $sheet->setCellValue('H' . $r, $s['acuan_rab_fisik'] ?: '-');

            $sheet->getStyle("A{$r}:H{$r}")->applyFromArray($dataBorder);
            $sheet->getStyle("A{$r}:B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$r}")->getNumberFormat()->setFormatCode('#,##0');
            $r++;
        }
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    private function buildKatalogAspSheet($sheet, array $aspAll, array $headerStyle, array $dataBorder, array $totalStyle)
    {
        $headers = [
            'A1' => 'No',
            'B1' => 'Paket',
            'C1' => 'Uraian Barang BMN',
            'D1' => 'Tahun',
            'E1' => 'Jenis BMN',
            'F1' => 'Kode BMN',
            'G1' => 'Merk / Tipe Spesifikasi',
            'H1' => 'Kondisi Baik',
            'I1' => 'Satuan',
            'J1' => 'Volume (Unit)',
            'K1' => 'Harga Satuan Fisik (Rp)',
            'L1' => 'Total Nilai Kontrak Fisik (Rp)',
        ];
        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }
        $sheet->getStyle('A1:L1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(25);

        $r = 2;
        $no = 1;
        $totalVol = 0;
        $totalNilai = 0;
        foreach ($aspAll as $a) {
            $sheet->setCellValue('A' . $r, $no++);
            $sheet->setCellValue('B' . $r, 'Paket ' . $a['no_paket']);
            $sheet->setCellValue('C' . $r, $a['nama_barang']);
            $sheet->setCellValue('D' . $r, $a['tahun']);
            $sheet->setCellValue('E' . $r, $a['jenis_bmn']);
            $sheet->setCellValue('F' . $r, $a['kode_bmn']);
            $sheet->setCellValue('G' . $r, $a['merk_tipe']);
            $sheet->setCellValue('H' . $r, (int) $a['kondisi_baik']);
            $sheet->setCellValue('I' . $r, $a['satuan']);
            $sheet->setCellValue('J' . $r, (int) $a['jumlah']);
            $sheet->setCellValue('K' . $r, (float) $a['harga_satuan']);
            $sheet->setCellValue('L' . $r, (float) $a['total_nilai']);

            $sheet->getStyle("A{$r}:L{$r}")->applyFromArray($dataBorder);
            $sheet->getStyle("A{$r}:B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$r}:F{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("H{$r}:I{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("J{$r}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("K{$r}:L{$r}")->getNumberFormat()->setFormatCode('#,##0');

            $totalVol += (int) $a['jumlah'];
            $totalNilai += (float) $a['total_nilai'];
            $r++;
        }

        // Total
        $sheet->setCellValue('A' . $r, 'TOTAL KESELURUHAN PERALATAN & MESIN (ASP)');
        $sheet->mergeCells("A{$r}:I{$r}");
        $sheet->setCellValue('J' . $r, $totalVol);
        $sheet->setCellValue('L' . $r, $totalNilai);
        $sheet->getStyle("A{$r}:L{$r}")->applyFromArray($totalStyle);
        $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("J{$r}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("L{$r}")->getNumberFormat()->setFormatCode('#,##0');

        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
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
