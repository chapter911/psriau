<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE);
require 'vendor/autoload.php';
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('do_not_upload/LEMBAR SPPD kuansing - ppk.xlsx');
$worksheet = $spreadsheet->getSheetByName('Bendahara');
if (!$worksheet) {
    echo "Sheet 'Bendahara' not found. Available sheets:\n";
    foreach ($spreadsheet->getSheetNames() as $name) {
        echo "- $name\n";
    }
} else {
    $rows = $worksheet->toArray();
    foreach ($rows as $row) {
        $rowStr = implode(' | ', array_map(function($val) { return str_replace("\n", " ", (string)$val); }, $row));
        if (trim(str_replace('|', '', $rowStr)) !== '') {
            echo $rowStr . "\n";
        }
    }
}
