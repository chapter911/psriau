<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE);
require 'vendor/autoload.php';
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('do_not_upload/Daftar Nominatif.xlsx');
$worksheet = $spreadsheet->getActiveSheet();
$rows = $worksheet->toArray();
foreach ($rows as $row) {
    $rowStr = implode(' | ', array_map(function($val) { return str_replace("\n", " ", (string)$val); }, $row));
    if (trim(str_replace('|', '', $rowStr)) !== '') {
        echo $rowStr . "\n";
    }
}
