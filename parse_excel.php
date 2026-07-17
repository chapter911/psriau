<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE);
require 'vendor/autoload.php';
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('do_not_upload/kwitansi kuansing - ppk.xls');
foreach (['RINCI', 'KWITANSI'] as $sheet) {
    echo "=== $sheet ===\n";
    $worksheet = $spreadsheet->getSheetByName($sheet);
    if (!$worksheet) continue;
    $rows = $worksheet->toArray();
    for ($i = 0; $i < 30; $i++) {
        if (!isset($rows[$i])) break;
        $row = $rows[$i];
        $line = [];
        foreach ($row as $col) {
            $val = trim(str_replace(["\n", "\r"], ' ', (string)$col));
            if ($val !== '') {
                $line[] = $val;
            }
        }
        if (!empty($line)) {
            echo implode(' | ', $line) . "\n";
        }
    }
}
