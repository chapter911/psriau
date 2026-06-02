<?php
require __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

function inlineLocalImages(string $html, string $baseDir): string
{
    return preg_replace_callback(
        '/<img\b[^>]*\bsrc="([^"]+)"[^>]*>/i',
        function (array $matches) use ($baseDir): string {
            $src = $matches[1];

            if (preg_match('#^(data:|https?:|//)#i', $src)) {
                return $matches[0];
            }

            $candidate = str_starts_with($src, '/') ? $src : $baseDir . '/' . $src;
            $path = realpath($candidate);

            if ($path === false || ! is_file($path)) {
                return $matches[0];
            }

            $mimeType = mime_content_type($path) ?: 'image/png';
            $dataUri = 'data:' . $mimeType . ';base64,' . base64_encode(file_get_contents($path));

            return str_replace($src, $dataUri, $matches[0]);
        },
        $html
    );
}

$jobs = [
    [
        'html' => __DIR__ . '/../writable/reports/panduan_admin_simak.html',
        'pdf' => __DIR__ . '/../writable/reports/panduan_admin_simak.pdf',
    ],
    [
        'html' => __DIR__ . '/../writable/reports/panduan_responden_simak.html',
        'pdf' => __DIR__ . '/../writable/reports/panduan_responden_simak.pdf',
    ],
];

$options = new Options();
$options->set('isRemoteEnabled', true);

foreach ($jobs as $job) {
    if (! file_exists($job['html'])) {
        fwrite(STDERR, "HTML not found: {$job['html']}\n");
        exit(1);
    }

    $html = file_get_contents($job['html']);
    $html = inlineLocalImages($html, dirname($job['html']));
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    file_put_contents($job['pdf'], $dompdf->output());
    echo "Wrote PDF to: {$job['pdf']}\n";
}