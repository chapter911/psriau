<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

helper('custom');

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Evaluasi');

$headerStyle = [
	'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
	'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '366092']],
	'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
];

$sheet->getColumnDimension('A')->setWidth(5);
$sheet->getColumnDimension('B')->setWidth(30);
$sheet->getColumnDimension('C')->setWidth(15);
$sheet->getColumnDimension('D')->setWidth(15);
$sheet->getColumnDimension('E')->setWidth(15);

$sheet->setCellValue('A1', 'HASIL EVALUASI ADMINISTRASI');
$sheet->mergeCells('A1:E1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->setCellValue('A3', 'Paket');
$sheet->setCellValue('B3', ': ' . esc((string) ($data->nama_paket ?? '')));
$sheet->setCellValue('A4', 'Peserta');
$sheet->setCellValue('B4', ': ' . esc((string) ($data->nama ?? '')));
$sheet->setCellValue('A5', 'Tanggal');
$sheet->setCellValue('B5', ': ' . date('d-m-Y'));

$row = 7;
$sheet->setCellValue('A' . $row, 'NO.');
$sheet->setCellValue('B' . $row, 'KRITERIA');
$sheet->setCellValue('C' . $row, 'STATUS');
$sheet->setCellValue('D' . $row, 'CATATAN');
$sheet->setCellValue('E' . $row, 'SKOR');
$sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray($headerStyle);

$criteria = [
	'Kelengkapan Dokumen',
	'Keaslian Dokumen',
	'Kompetensi',
	'Pengalaman Kerja',
	'Referensi',
];

$row++;
foreach ($criteria as $idx => $criterion) {
	$sheet->setCellValue('A' . $row, $idx + 1);
	$sheet->setCellValue('B' . $row, $criterion);
	$sheet->setCellValue('C' . $row, 'Memenuhi');
	$sheet->setCellValue('D' . $row, '-');
	$sheet->setCellValue('E' . $row, 100);
	$row++;
}

$sheet->getStyle('A7:E' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

$sheet->setCellValue('B' . $row, 'TOTAL SKOR');
$sheet->setCellValue('E' . $row, '=SUM(E8:E' . ($row - 1) . ')');
$sheet->getStyle('B' . $row . ':E' . $row)->getFont()->setBold(true);

$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="evaluasi-' . $data->id . '.xlsx"');
header('Cache-Control: max-age=0');
$writer->save('php://output');
if (ob_get_level() > 0) {
	ob_end_clean();
}
exit;
