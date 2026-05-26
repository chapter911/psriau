<?php
require __DIR__ . '/../vendor/autoload.php';

// Path to the HTML report
$htmlFile = __DIR__ . '/../writable/reports/simak_share_verifikasi_kontinuitas_report.html';
if (! file_exists($htmlFile)) {
    echo "Report HTML not found: $htmlFile\n";
    exit(1);
}

$html = file_get_contents($htmlFile);

$options = new \Dompdf\Options();
$options->set('isRemoteEnabled', true);
$dompdf = new \Dompdf\Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$output = $dompdf->output();
$outFile = __DIR__ . '/../writable/reports/simak_share_verifikasi_kontinuitas_report.pdf';
file_put_contents($outFile, $output);
echo "Wrote PDF to: " . $outFile . "\n";
