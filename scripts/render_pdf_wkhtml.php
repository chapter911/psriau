<?php
require __DIR__ . '/../vendor/autoload.php';
// Minimal helper loader
if (is_file(__DIR__ . '/../app/Helpers/pdf_helper.php')) {
    require __DIR__ . '/../app/Helpers/pdf_helper.php';
}
if (is_file(__DIR__ . '/../app/Helpers/custom_helper.php')) {
    require __DIR__ . '/../app/Helpers/custom_helper.php';
}

$data = [
    'nomor_surat_tugas' => 'ST-2026/001',
    'periode_mulai' => '2026-05-01',
    'periode_selesai' => '2026-05-03',
    'kota_tujuan' => 'Tangerang Selatan',
    'tujuan' => 'Contoh tujuan',
    'sasaran' => 'Contoh sasaran',
    'laporan_hasil' => str_repeat('<p>Contoh isi laporan panjang untuk menguji pemecahan halaman.</p>', 40),
    'pelaksana' => [ ['nama'=>'Joni','nip'=>'123'] ],
    'diketahui_oleh' => ['nama'=>'Kabag','nip'=>'999']
];

// Render HTML view to temp file
ob_start();
extract(['data' => $data]);
include __DIR__ . '/../app/Views/admin/laporan/perjalanan_dinas_pdf_clean.php';
$html = ob_get_clean();
$htmlFile = '/tmp/perjalanan_clean.html';
file_put_contents($htmlFile, $html);

$pdfFile = '/tmp/perjalanan_clean.pdf';
$cmd = 'wkhtmltopdf --enable-local-file-access ' . escapeshellarg($htmlFile) . ' ' . escapeshellarg($pdfFile) . ' 2>&1';
exec($cmd, $out, $rc);
if ($rc !== 0) {
    echo "wkhtmltopdf failed (code=$rc). Output:\n" . implode("\n", $out) . "\n";
    echo "Install wkhtmltopdf (brew install wkhtmltopdf) or use headless Chrome.\n";
    exit(1);
}

echo "Wrote PDF to: $pdfFile\n";
