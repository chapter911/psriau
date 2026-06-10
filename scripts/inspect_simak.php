<?php
$db = new mysqli('satkerpps-riau.online', 'agun9011_satkerpps', '9w:wxJn|K', 'agun9011_satkerpps');
if ($db->connect_error) {
    die("DB connect failed: " . $db->connect_error);
}

echo "=== KONSTRUKSI VERIFIKASI (ID 18) ===\n";
$res = $db->query("SELECT row_no, kelengkapan_dokumen, verifikasi_ki FROM trn_kontrak_simak_verifikasi WHERE simak_id = 18");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}

echo "\n=== KONSTRUKSI DOKUMEN (ID 18) ===\n";
$res = $db->query("SELECT id, row_no, tipe_dokumen, kelengkapan_dokumen, verifikasi_ki, file_original_name FROM trn_kontrak_simak_verifikasi_dokumen WHERE simak_id = 18");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}

$db->close();
