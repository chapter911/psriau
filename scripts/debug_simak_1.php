<?php
// Bootstrap CodeIgniter 4
define('FCPATH', __DIR__ . '/../public/');
require __DIR__ . '/../app/Config/Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . '/bootstrap.php';

$db = \Config\Database::connect();

$id = 1;
$type = 'konstruksi';

$kontrakController = new \App\Controllers\Admin\Kontrak();

// Reflection to call private/protected methods/properties
$reflection = new \ReflectionClass($kontrakController);

$getTemplateItems = $reflection->getMethod('getSimakTemplateItems');
$getTemplateItems->setAccessible(true);
$templateItems = $getTemplateItems->invoke($kontrakController, $type, false);

$verifikasiBuilder = $db->table('trn_kontrak_simak_verifikasi')
    ->where('simak_id', $id);
$verifikasiRows = $verifikasiBuilder->get()->getResultArray();
$verifikasiByRow = [];
foreach ($verifikasiRows as $r) {
    $verifikasiByRow[(int)$r['row_no']] = $r;
}

$dokumenBuilder = $db->table('trn_kontrak_simak_verifikasi_dokumen')
    ->where('simak_id', $id)
    ->orderBy('id', 'DESC');
$dokumenRows = $dokumenBuilder->get()->getResultArray();
$dokumenByRow = [];
foreach ($dokumenRows as $doc) {
    $rowNo = (int)$doc['row_no'];
    $dokumenByRow[$rowNo][] = $doc;
}

$resolveStatus = $reflection->getMethod('resolveSimakRowStatus');
$resolveStatus->setAccessible(true);

echo "Total template items: " . count($templateItems) . "\n";
$leafCount = 0;
$statusCounts = [];
$pendingDetails = [];

foreach ($templateItems as $item) {
    if (($item['is_leaf'] ?? false) !== true) {
        continue;
    }
    $leafCount++;
    $rowNo = (int)($item['row_no'] ?? 0);
    $verRow = $verifikasiByRow[$rowNo] ?? null;
    $docRows = $dokumenByRow[$rowNo] ?? [];
    
    $status = $resolveStatus->invoke($kontrakController, $item, $verRow, $docRows);
    
    if (!isset($statusCounts[$status])) {
        $statusCounts[$status] = 0;
    }
    $statusCounts[$status]++;
    
    if ($status === 'belum_verifikasi') {
        $pendingDetails[] = [
            'row_no' => $rowNo,
            'kode' => $item['display_no'] ?? '',
            'uraian' => $item['uraian'] ?? '',
            'has_draft' => $item['has_draft'] ?? false,
            'ver_row' => $verRow,
            'doc_rows' => $docRows
        ];
    }
}

echo "Total Leaf Rows: $leafCount\n";
echo "Status counts:\n";
print_r($statusCounts);

echo "\n--- Pending Verification Details ---\n";
foreach ($pendingDetails as $pd) {
    echo "Row No: {$pd['row_no']} | Kode: {$pd['kode']} | Uraian: {$pd['uraian']} | Has Draft: " . ($pd['has_draft'] ? 'Yes' : 'No') . "\n";
    if ($pd['ver_row']) {
        echo "  Verifikasi Row: kelengkapan=" . $pd['ver_row']['kelengkapan_dokumen'] . ", verifikasi_ki=" . $pd['ver_row']['verifikasi_ki'] . ", keterangan=" . $pd['ver_row']['keterangan'] . "\n";
    } else {
        echo "  Verifikasi Row: (none)\n";
    }
    if ($pd['doc_rows']) {
        echo "  Documents:\n";
        foreach ($pd['doc_rows'] as $doc) {
            echo "    - ID: {$doc['id']} | Type: {$doc['tipe_dokumen']} | Path: {$doc['file_relative_path']} | Verifikasi KI: {$doc['verifikasi_ki']}\n";
        }
    } else {
        echo "  Documents: (none)\n";
    }
    echo "\n";
}
