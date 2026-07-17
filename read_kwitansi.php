<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE);
require 'vendor/autoload.php';
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('do_not_upload/kwitansi kuansing - ppk.xls');

foreach (['RINCI', 'KWITANSI'] as $sheetName) {
    echo "=== SHEET: $sheetName ===\n";
    $worksheet = $spreadsheet->getSheetByName($sheetName);
    if (!$worksheet) {
        echo "Sheet not found.\n";
        continue;
    }
    $rows = $worksheet->toArray();
    foreach ($rows as $row) {
        $rowStr = implode(' | ', array_map(function($val) { return str_replace("\n", " ", (string)$val); }, $row));
        if (trim(str_replace('|', '', $rowStr)) !== '') {
            echo $rowStr . "\n";
        }
    }
}
