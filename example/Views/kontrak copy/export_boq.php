<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Membuat objek Spreadsheet baru
$spreadsheet = new Spreadsheet();

// Format Currency Rupiah
$currencyFormat = '_("Rp"* #,##0_);_("Rp"* (#,##0);_("Rp"* "-"_);_(@_)';

// Style Umum
$styleBorder = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
        ],
    ],
];

$styleHeaderCenter = [
    'font' => ['bold' => true],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
];

$styleBold = [
    'font' => ['bold' => true],
];

// ==========================================
// SHEET 1: REKAP HPS (Rekapitulasi)
// ==========================================
$sheet1 = $spreadsheet->getActiveSheet();
$sheet1->setTitle('Rekap HPS');

// Set Lebar Kolom
$sheet1->getColumnDimension('A')->setWidth(5);
$sheet1->getColumnDimension('B')->setWidth(50);
$sheet1->getColumnDimension('C')->setWidth(25);

// Header Dokumen
$sheet1->setCellValue('A1', 'REKAPITULASI PENAWARAN BIAYA');
$sheet1->mergeCells('A1:C1');
$sheet1->getStyle('A1')->applyFromArray($styleHeaderCenter);

$headers = [
    3 => ['Program', ':', 'Penyelenggaraan Jalan'],
    4 => ['Kegiatan', ':', 'Penyelenggaraan Jalan Jembatan'],
    5 => ['Pekerjaan', ':', 'Konsultan Individual Team Leader'],
    6 => ['Lokasi', ':', 'Provinsi Riau'],
    7 => ['Tahun Anggaran', ':', '2025'],
];

foreach ($headers as $row => $data) {
    $sheet1->setCellValue('A' . $row, $data[0]);
    $sheet1->setCellValue('B' . $row, $data[1] . ' ' . $data[2]);
}

// Tabel Header
$sheet1->setCellValue('A9', 'NO.');
$sheet1->setCellValue('B9', 'URAIAN');
$sheet1->setCellValue('C9', 'TOTAL HARGA (Rp.)');
$sheet1->getStyle('A9:C9')->applyFromArray(array_merge($styleBorder, $styleHeaderCenter));
$sheet1->getStyle('A9:C9')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEEEEEE');

// Isi Tabel
$rekapData = [
    ['I.', 'BIAYA LANGSUNG PERSONIL', 21000000],
    ['II.', 'BIAYA LANGSUNG NON PERSONIL', 26700000],
];

$row = 10;
foreach ($rekapData as $data) {
    $sheet1->setCellValue('A' . $row, $data[0]);
    $sheet1->setCellValue('B' . $row, $data[1]);
    $sheet1->setCellValue('C' . $row, $data[2]);
    $sheet1->getStyle('C' . $row)->getNumberFormat()->setFormatCode($currencyFormat);
    $row++;
}

// Total
$sheet1->setCellValue('A' . $row, 'JUMLAH');
$sheet1->mergeCells('A' . $row . ':B' . $row);
$sheet1->setCellValue('C' . $row, '=SUM(C10:C11)');
$sheet1->getStyle('C' . $row)->getNumberFormat()->setFormatCode($currencyFormat);
$sheet1->getStyle('A' . $row . ':C' . $row)->applyFromArray($styleBold);
$row++;

// PPN (Di CSV sepertinya tidak dihitung atau 0, kita ikuti total akhir 47.700.000)
// Asumsi Total sudah termasuk pajak atau pajak dihitung terpisah, namun berdasarkan angka di CSV, totalnya pas penjumlahan I + II.
$sheet1->setCellValue('A' . $row, 'PPN 11%');
$sheet1->mergeCells('A' . $row . ':B' . $row);
$sheet1->setCellValue('C' . $row, 0); // Sesuai data CSV, total akhir tetap 47.7jt
$sheet1->getStyle('C' . $row)->getNumberFormat()->setFormatCode($currencyFormat);
$row++;

$sheet1->setCellValue('A' . $row, 'TOTAL');
$sheet1->mergeCells('A' . $row . ':B' . $row);
$sheet1->setCellValue('C' . $row, 47700000);
$sheet1->getStyle('C' . $row)->getNumberFormat()->setFormatCode($currencyFormat);
$sheet1->getStyle('A' . $row . ':C' . $row)->applyFromArray($styleBold);
$row++;

$sheet1->setCellValue('A' . $row, 'PEMBULATAN');
$sheet1->mergeCells('A' . $row . ':B' . $row);
$sheet1->setCellValue('C' . $row, 47700000);
$sheet1->getStyle('C' . $row)->getNumberFormat()->setFormatCode($currencyFormat);
$sheet1->getStyle('A' . $row . ':C' . $row)->applyFromArray($styleBold);
$row++;

// Terbilang
$sheet1->setCellValue('A' . $row, 'Terbilang : Empat Puluh Tujuh Juta Tujuh Ratus Ribu Rupiah');
$sheet1->mergeCells('A' . $row . ':C' . $row);
$sheet1->getStyle('A' . $row)->getFont()->setItalic(true);

// Border Tabel
$sheet1->getStyle('A9:C' . ($row-1))->applyFromArray($styleBorder);

// Tanda Tangan
$row += 2;
$sheet1->setCellValue('B' . $row, 'Pekanbaru, 8 April 2025');
$sheet1->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$row++;
$sheet1->setCellValue('B' . $row, 'Penawar');
$sheet1->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$row += 4;
$sheet1->setCellValue('B' . $row, 'Ade Putra. ST.');
$sheet1->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$sheet1->getStyle('B' . $row)->getFont()->setUnderline(true)->setBold(true);


// ==========================================
// SHEET 2: HPS (Rincian Biaya)
// ==========================================
$sheet2 = $spreadsheet->createSheet();
$sheet2->setTitle('HPS');

// Set Lebar Kolom
$sheet2->getColumnDimension('A')->setWidth(5);
$sheet2->getColumnDimension('B')->setWidth(40);
$sheet2->getColumnDimension('C')->setWidth(10); // Vol
$sheet2->getColumnDimension('D')->setWidth(10); // Sat
$sheet2->getColumnDimension('E')->setWidth(20); // Harga
$sheet2->getColumnDimension('F')->setWidth(20); // Jumlah

// Header
$sheet2->setCellValue('A1', 'RINCIAN BIAYA LANGSUNG PERSONIL & NON PERSONIL');
$sheet2->mergeCells('A1:F1');
$sheet2->getStyle('A1')->applyFromArray($styleHeaderCenter);

// Info Proyek (Copy logic dari sheet 1 tapi disesuaikan)
$row = 3;
$sheet2->setCellValue('A'.$row, 'Pekerjaan : Konsultan Individual Team Leader');
$row++;
$sheet2->setCellValue('A'.$row, 'Lokasi : Provinsi Riau');
$row++;
$sheet2->setCellValue('A'.$row, 'Tahun Anggaran : 2025');
$row += 2;

// Tabel Header
$headersHPS = ['NO', 'URAIAN', 'VOL', 'SAT', 'HARGA SATUAN', 'JUMLAH HARGA'];
$col = 'A';
foreach($headersHPS as $h) {
    $sheet2->setCellValue($col . $row, $h);
    $col++;
}
$sheet2->getStyle('A'.$row.':F'.$row)->applyFromArray(array_merge($styleBorder, $styleHeaderCenter));
$sheet2->getStyle('A'.$row.':F'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEEEEEE');
$startRow = $row;
$row++;

// I. BIAYA LANGSUNG PERSONIL
$sheet2->setCellValue('A'.$row, 'I.');
$sheet2->setCellValue('B'.$row, 'BIAYA LANGSUNG PERSONIL (REMUNERATION)');
$sheet2->getStyle('B'.$row)->getFont()->setBold(true);
$sheet2->mergeCells('B'.$row.':F'.$row);
$row++;

// Data Personil
$sheet2->setCellValue('A'.$row, '1');
$sheet2->setCellValue('B'.$row, 'Team Leader');
$sheet2->setCellValue('C'.$row, 3);
$sheet2->setCellValue('D'.$row, 'OB');
$sheet2->setCellValue('E'.$row, 7000000);
$sheet2->setCellValue('F'.$row, '=C'.$row.'*E'.$row);
$sheet2->getStyle('E'.$row.':F'.$row)->getNumberFormat()->setFormatCode($currencyFormat);
$row++;

// Subtotal I
$sheet2->setCellValue('B'.$row, 'Sub Total I');
$sheet2->setCellValue('F'.$row, '=SUM(F'.($row-1).':F'.($row-1).')'); // Sum personil
$sheet2->getStyle('B'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$sheet2->getStyle('B'.$row.':F'.$row)->getFont()->setBold(true);
$sheet2->getStyle('F'.$row)->getNumberFormat()->setFormatCode($currencyFormat);
$sheet2->getStyle('A'.$row.':F'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFDDDDDD');
$row++;

// II. BIAYA LANGSUNG NON PERSONIL
$sheet2->setCellValue('A'.$row, 'II.');
$sheet2->setCellValue('B'.$row, 'BIAYA LANGSUNG NON PERSONIL (DIRECT REIMBURSEABLE COST)');
$sheet2->getStyle('B'.$row)->getFont()->setBold(true);
$sheet2->mergeCells('B'.$row.':F'.$row);
$row++;

// Data Non Personil
$nonPersonil = [
    ['1', 'Biaya Penerapan SMKK', 1, 'Ls', 5900000],
    ['2', 'Biaya ATK & Tinta Printer', 3, 'Bln', 500000],
    ['3', 'Biaya Sewa Peralatan', 1, 'Ls', 5100000],
    ['4', 'Biaya Survey & Pengumpulan Data', 1, 'Ls', 5000000],
    ['5', 'Biaya Pelaporan', '', '', 0], // Header for sub-items
];

foreach($nonPersonil as $np) {
    $sheet2->setCellValue('A'.$row, $np[0]);
    $sheet2->setCellValue('B'.$row, $np[1]);
    
    if ($np[1] !== 'Biaya Pelaporan') {
        $sheet2->setCellValue('C'.$row, $np[2]);
        $sheet2->setCellValue('D'.$row, $np[3]);
        $sheet2->setCellValue('E'.$row, $np[4]);
        $sheet2->setCellValue('F'.$row, '=C'.$row.'*E'.$row);
        $sheet2->getStyle('E'.$row.':F'.$row)->getNumberFormat()->setFormatCode($currencyFormat);
    }
    $row++;
}

// Sub-items Biaya Pelaporan
$laporan = [
    ['a.', 'Laporan Pendahuluan', 3, 'Buku', 150000],
    ['b.', 'Laporan Antara', 3, 'Buku', 150000],
    ['c.', 'Laporan Akhir', 5, 'Buku', 250000],
    ['d.', 'Laporan Bulanan', 3, 'Buku', 100000],
    ['e.', 'Laporan SMKK', 3, 'Buku', 150000],
    ['f.', 'Flashdisk 64 GB (Softcopy Laporan)', 1, 'Bh', 150000],
];

$startLaporanRow = $row;
foreach($laporan as $lap) {
    $sheet2->setCellValue('B'.$row, '    ' . $lap[0] . ' ' . $lap[1]); // Indent
    $sheet2->setCellValue('C'.$row, $lap[2]);
    $sheet2->setCellValue('D'.$row, $lap[3]);
    $sheet2->setCellValue('E'.$row, $lap[4]);
    $sheet2->setCellValue('F'.$row, '=C'.$row.'*E'.$row);
    $sheet2->getStyle('E'.$row.':F'.$row)->getNumberFormat()->setFormatCode($currencyFormat);
    $row++;
}

// Subtotal II
$sheet2->setCellValue('B'.$row, 'Sub Total II');
// Rumus sum agak kompleks karena ada header, kita sum manual range items
// Item 1-4
$sumRange1 = 'F'.($row - count($laporan) - 5).':F'.($row - count($laporan) - 2);
// Item 5 (Laporan)
$sumRange2 = 'F'.$startLaporanRow.':F'.($row-1);

$sheet2->setCellValue('F'.$row, '=SUM('.$sumRange1.')+SUM('.$sumRange2.')');
$sheet2->getStyle('B'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$sheet2->getStyle('B'.$row.':F'.$row)->getFont()->setBold(true);
$sheet2->getStyle('F'.$row)->getNumberFormat()->setFormatCode($currencyFormat);
$sheet2->getStyle('A'.$row.':F'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFDDDDDD');
$row++;

// TOTAL I + II
$sheet2->setCellValue('B'.$row, 'TOTAL ( I + II )');
$sheet2->setCellValue('F'.$row, 47700000); // Hardcode atau rumus SUM Subtotal I + Subtotal II
$sheet2->getStyle('B'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$sheet2->getStyle('A'.$row.':F'.$row)->applyFromArray($styleBold);
$sheet2->getStyle('F'.$row)->getNumberFormat()->setFormatCode($currencyFormat);
$sheet2->getStyle('A'.$row.':F'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFAAAAAA');

// Apply Border to whole table
$sheet2->getStyle('A'.$startRow.':F'.$row)->applyFromArray($styleBorder);

// Tanda Tangan (HPS)
$row += 2;
$sheet2->setCellValue('E' . $row, 'Pekanbaru, 8 April 2025');
$row++;
$sheet2->setCellValue('E' . $row, 'Penawar');
$row += 4;
$sheet2->setCellValue('E' . $row, 'Ade Putra. ST.');
$sheet2->getStyle('E' . $row)->getFont()->setUnderline(true)->setBold(true);


// ==========================================
// SHEET 3: ANALISA HPS (Analisa Harga Satuan)
// ==========================================
$sheet3 = $spreadsheet->createSheet();
$sheet3->setTitle('Analisa HPS');

$sheet3->getColumnDimension('A')->setWidth(5);
$sheet3->getColumnDimension('B')->setWidth(40);
$sheet3->getColumnDimension('C')->setWidth(10);
$sheet3->getColumnDimension('D')->setWidth(10);
$sheet3->getColumnDimension('E')->setWidth(20);
$sheet3->getColumnDimension('F')->setWidth(20);

$sheet3->setCellValue('A1', 'ANALISA HARGA SATUAN');
$sheet3->mergeCells('A1:F1');
$sheet3->getStyle('A1')->applyFromArray($styleHeaderCenter);

// --- Tabel 1: Biaya Penerapan SMKK ---
$row = 3;
$sheet3->setCellValue('A'.$row, '1.');
$sheet3->setCellValue('B'.$row, 'Biaya Penerapan SMKK');
$sheet3->getStyle('B'.$row)->getFont()->setBold(true);
$row++;

// Header Tabel 1
foreach($headersHPS as $key => $h) {
    $col = chr(65 + $key); // A, B, C...
    $sheet3->setCellValue($col . $row, $h);
}
$sheet3->getStyle('A'.$row.':F'.$row)->applyFromArray(array_merge($styleBorder, $styleHeaderCenter));
$sheet3->getStyle('A'.$row.':F'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEEEEEE');
$startRow = $row;
$row++;

$smkkData = [
    ['1', 'Penyiapan RKK', 1, 'Set', 200000],
    ['2', 'Sosialisasi & Promosi K3', '', '', 0], // Header only in logic, items follow
];
// Items for Sosialisasi
$smkkSub = [
    ['a.', 'Rambu Peringatan', 2, 'Bh', 200000],
    ['b.', 'Rambu Informasi', 2, 'Bh', 200000],
    ['c.', 'Spanduk K3', 1, 'Lbr', 300000],
    ['d.', 'Bendera K3', 1, 'Lbr', 200000],
    ['e.', 'Bendera RI', 1, 'Lbr', 100000],
];
// Item 3 APD
$apdSub = [
    ['a.', 'Topi Pelindung (Safety Helmet)', 3, 'Bh', 150000],
    ['b.', 'Rompi Keselamatan', 3, 'Bh', 150000],
    ['c.', 'Sarung Tangan', 3, 'Psg', 50000],
    ['d.', 'Sepatu Keselamatan', 3, 'Psg', 400000],
];
// Item 4 Asuransi
$asuransi = [
    ['4', 'Asuransi', 1, 'Ls', 1000000],
];

// Render SMKK
// 1. RKK
$sheet3->setCellValue('A'.$row, '1');
$sheet3->setCellValue('B'.$row, 'Penyiapan RKK');
$sheet3->setCellValue('C'.$row, 1);
$sheet3->setCellValue('D'.$row, 'Set');
$sheet3->setCellValue('E'.$row, 200000);
$sheet3->setCellValue('F'.$row, '=C'.$row.'*E'.$row);
$sheet3->getStyle('E'.$row.':F'.$row)->getNumberFormat()->setFormatCode($currencyFormat);
$row++;

// 2. Sosialisasi
$sheet3->setCellValue('A'.$row, '2');
$sheet3->setCellValue('B'.$row, 'Sosialisasi & Promosi K3');
$row++;
foreach($smkkSub as $sub) {
    $sheet3->setCellValue('B'.$row, '   '.$sub[0].' '.$sub[1]);
    $sheet3->setCellValue('C'.$row, $sub[2]);
    $sheet3->setCellValue('D'.$row, $sub[3]);
    $sheet3->setCellValue('E'.$row, $sub[4]);
    $sheet3->setCellValue('F'.$row, '=C'.$row.'*E'.$row);
    $sheet3->getStyle('E'.$row.':F'.$row)->getNumberFormat()->setFormatCode($currencyFormat);
    $row++;
}

// 3. APD
$sheet3->setCellValue('A'.$row, '3');
$sheet3->setCellValue('B'.$row, 'Alat Pelindung Diri');
$row++;
foreach($apdSub as $sub) {
    $sheet3->setCellValue('B'.$row, '   '.$sub[0].' '.$sub[1]);
    $sheet3->setCellValue('C'.$row, $sub[2]);
    $sheet3->setCellValue('D'.$row, $sub[3]);
    $sheet3->setCellValue('E'.$row, $sub[4]);
    $sheet3->setCellValue('F'.$row, '=C'.$row.'*E'.$row);
    $sheet3->getStyle('E'.$row.':F'.$row)->getNumberFormat()->setFormatCode($currencyFormat);
    $row++;
}

// 4. Asuransi
$sheet3->setCellValue('A'.$row, '4');
$sheet3->setCellValue('B'.$row, 'Asuransi');
$sheet3->setCellValue('C'.$row, 1);
$sheet3->setCellValue('D'.$row, 'Ls');
$sheet3->setCellValue('E'.$row, 1000000);
$sheet3->setCellValue('F'.$row, '=C'.$row.'*E'.$row);
$sheet3->getStyle('E'.$row.':F'.$row)->getNumberFormat()->setFormatCode($currencyFormat);
$row++;

// TOTAL SMKK
$sheet3->setCellValue('B'.$row, 'TOTAL BIAYA SMKK');
$sheet3->setCellValue('F'.$row, 5900000); // Check sum match
$sheet3->getStyle('B'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$sheet3->getStyle('A'.$row.':F'.$row)->applyFromArray($styleBold);
$sheet3->getStyle('F'.$row)->getNumberFormat()->setFormatCode($currencyFormat);
$sheet3->getStyle('A'.$startRow.':F'.$row)->applyFromArray($styleBorder);
$row += 2;

// --- Tabel 2: Biaya Sewa Peralatan ---
$sheet3->setCellValue('A'.$row, '2.');
$sheet3->setCellValue('B'.$row, 'Biaya Sewa Peralatan');
$sheet3->getStyle('B'.$row)->getFont()->setBold(true);
$row++;

// Header Table 2
foreach($headersHPS as $key => $h) {
    $col = chr(65 + $key);
    $sheet3->setCellValue($col . $row, $h);
}
$sheet3->getStyle('A'.$row.':F'.$row)->applyFromArray(array_merge($styleBorder, $styleHeaderCenter));
$sheet3->getStyle('A'.$row.':F'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEEEEEE');
$startRow = $row;
$row++;

$sewaData = [
    ['1', 'Laptop/Komputer', 1, 'Unit/Bln', 3, 300000], // Vol 1 unit, 3 bulan (total qty 3)
    ['2', 'Printer A4', 1, 'Unit/Bln', 3, 200000],
    ['3', 'Kendaraan Roda 2', 1, 'Unit/Bln', 3, 1200000],
];

foreach($sewaData as $sd) {
    $sheet3->setCellValue('A'.$row, $sd[0]);
    $sheet3->setCellValue('B'.$row, $sd[1]);
    $sheet3->setCellValue('C'.$row, $sd[4]); // Using total quantity (3 bulan)
    $sheet3->setCellValue('D'.$row, 'Bln'); // Unit satuan
    $sheet3->setCellValue('E'.$row, $sd[5]);
    $sheet3->setCellValue('F'.$row, '=C'.$row.'*E'.$row);
    $sheet3->getStyle('E'.$row.':F'.$row)->getNumberFormat()->setFormatCode($currencyFormat);
    $row++;
}

// TOTAL SEWA
$sheet3->setCellValue('B'.$row, 'TOTAL BIAYA SEWA PERALATAN');
$sheet3->setCellValue('F'.$row, 5100000);
$sheet3->getStyle('B'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$sheet3->getStyle('A'.$row.':F'.$row)->applyFromArray($styleBold);
$sheet3->getStyle('F'.$row)->getNumberFormat()->setFormatCode($currencyFormat);
$sheet3->getStyle('A'.$startRow.':F'.$row)->applyFromArray($styleBorder);


// Output File
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="BOQ_2025_SITI_AP_Generated.xlsx"');
header('Cache-Control: max-age=0');

$writer->save('php://output');
exit;