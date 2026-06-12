<?php
$db = new mysqli('satkerpps-riau.online', 'agun9011_satkerpps', '9w:wxJn|K', 'agun9011_satkerpps');
if ($db->connect_error) {
    die("DB connect failed: " . $db->connect_error);
}

function dumpData($db, $table, $simakId) {
    echo "=== Data in table $table for simak_id = $simakId ===\n";
    $res = $db->query("SELECT * FROM $table WHERE simak_id = $simakId");
    if ($res) {
        $count = 0;
        while ($row = $res->fetch_assoc()) {
            $count++;
            print_r($row);
        }
        echo "Total rows: $count\n";
    } else {
        echo "Error: " . $db->error . "\n";
    }
    echo "\n";
}

dumpData($db, 'trn_kontrak_simak_share', 28);
dumpData($db, 'trn_kontrak_simak_verifikasi', 28);
dumpData($db, 'trn_kontrak_simak_verifikasi_dokumen', 28);

$db->close();
