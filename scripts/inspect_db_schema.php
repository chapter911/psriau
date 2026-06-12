<?php
$db = new mysqli('satkerpps-riau.online', 'agun9011_satkerpps', '9w:wxJn|K', 'agun9011_satkerpps');
if ($db->connect_error) {
    die("DB connect failed: " . $db->connect_error);
}

function showColumns($db, $table) {
    echo "=== Columns for table: $table ===\n";
    $res = $db->query("SHOW COLUMNS FROM $table");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            echo "Field: {$row['Field']} | Type: {$row['Type']} | Null: {$row['Null']} | Key: {$row['Key']} | Default: {$row['Default']}\n";
        }
    } else {
        echo "Error: " . $db->error . "\n";
    }
    echo "\n";
}

showColumns($db, 'trn_kontrak_simak_verifikasi');
showColumns($db, 'trn_kontrak_simak_verifikasi_dokumen');
showColumns($db, 'trn_kontrak_simak_konsultasi_verifikasi');
showColumns($db, 'trn_kontrak_simak_konsultasi_verifikasi_dokumen');

$db->close();
