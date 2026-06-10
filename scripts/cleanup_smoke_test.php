<?php
// Cleanup script for smoke test data IDs 19 (konstruksi) and 11 (konsultasi)
$db = new mysqli('satkerpps-riau.online', 'agun9011_satkerpps', '9w:wxJn|K', 'agun9011_satkerpps');
if ($db->connect_error) {
    die("DB connect failed: " . $db->connect_error);
}

$queries = [
    "DELETE FROM trn_kontrak_simak_verifikasi_dokumen WHERE simak_id = 19",
    "DELETE FROM trn_kontrak_simak_verifikasi WHERE simak_id = 19",
    "DELETE FROM trn_kontrak_simak_share WHERE simak_id = 19",
    "DELETE FROM trn_kontrak_simak WHERE id = 19",
    "DELETE FROM trn_kontrak_simak_konsultasi_verifikasi_dokumen WHERE simak_id = 11",
    "DELETE FROM trn_kontrak_simak_konsultasi_verifikasi WHERE simak_id = 11",
    "DELETE FROM trn_kontrak_simak_konsultasi_share WHERE simak_id = 11",
    "DELETE FROM trn_kontrak_simak_konsultasi WHERE id = 11",
];

foreach ($queries as $sql) {
    if ($db->query($sql)) {
        echo "[OK] " . $sql . "\n";
    } else {
        echo "[ERR] " . $db->error . " | " . $sql . "\n";
    }
}

$db->close();
echo "\n[DONE] Cleanup complete.\n";
