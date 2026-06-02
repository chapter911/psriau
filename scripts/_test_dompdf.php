<?php
require __DIR__ . '/../vendor/autoload.php';
$html = '<html><body><h1>PDF Test</h1><p>This is a test.</p></body></html>';
$options = new Dompdf\Options();
$options->set('isRemoteEnabled', true);
$dom = new Dompdf\Dompdf($options);
$dom->loadHtml($html);
$dom->setPaper('A4','portrait');
$dom->render();
file_put_contents(__DIR__ . '/../writable/reports/test_dom.pdf', $dom->output());
echo "ok\n";
