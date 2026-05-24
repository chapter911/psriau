<?php
require __DIR__ . '/../vendor/autoload.php';

// Minimal helpers used by the view
if (! function_exists('helper')) {
    function helper($name) { return true; }
}
if (! function_exists('esc')) {
    function esc($str, $context = null) { return htmlspecialchars((string) $str, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
}
if (! function_exists('tanggal_indonesia')) {
    function tanggal_indonesia($date) { return $date ?: date('Y-m-d'); }
}
if (! function_exists('kop_surat_img_tag')) {
    function kop_surat_img_tag($a='',$b='',$c='',$d=null) { return ''; }
}
if (! function_exists('base_url')) {
    function base_url($p='') { return $p; }
}

// Prepare sample data with long laporan_hasil
$long = "Data ringkas terkait kegiatan yang dikunjungi :<br><br>";
for ($i=0;$i<60;$i++) {
    $long .= ($i+1) . ". Perjalanan menuju lokasi kegiatan yang berada di Tanggerang Selatan Banten. Ini adalah paragraf contoh yang cukup panjang untuk menguji pemecahan halaman pada PDF. <br><br>";
}

$data = [
    'nomor_surat_tugas' => 'ST-2026/001',
    'periode_mulai' => '2026-05-01',
    'periode_selesai' => '2026-05-03',
    'kota_tujuan' => 'Tangerang Selatan',
    'tujuan' => 'ini isi tujuannya',
    'sasaran' => 'ini sasarannya lho',
    'laporan_hasil' => $long,
    'pelaksana' => [ ['nama'=>'Joni','jabatan'=>'Staf','nip'=>'123'], ['nama'=>'Budi','jabatan'=>'Kepala','nip'=>'456'] ],
    'foto_dokumentasi' => [],
    'creator_pegawai' => ['nama'=>'Creator','jabatan'=>'Admin','nip'=>'000'],
    'diketahui_oleh' => ['nama'=>'Kabag','jabatan'=>'Kepala Bagian','nip'=>'111'],
];

// Render view to HTML
ob_start();
try {
    $dataForView = ['data' => $data];
    extract($dataForView);
    include __DIR__ . '/../app/Views/admin/laporan/perjalanan_dinas_pdf.php';
    $html = ob_get_clean();
} catch (Throwable $e) {
    ob_end_clean();
    echo "Error rendering view: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

// Render with Dompdf
$options = new \Dompdf\Options();
$options->set('isRemoteEnabled', true);
$dompdf = new \Dompdf\Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$output = $dompdf->output();
$outFile = '/tmp/perjalanan_sample.pdf';
file_put_contents($outFile, $output);
echo "Wrote PDF to: " . $outFile . PHP_EOL;
