<?php
$db = new mysqli('satkerpps-riau.online', 'agun9011_satkerpps', '9w:wxJn|K', 'agun9011_satkerpps');
if ($db->connect_error) {
    die("DB connect failed: " . $db->connect_error);
}

// 1. Konstruksi
$res = $db->query("SELECT id, nomor_kontrak FROM trn_kontrak_simak WHERE nomor_kontrak LIKE 'SMOKE/%'");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $id = $row['id'];
        $nomor = $row['nomor_kontrak'];
        echo "Cleaning up Konstruksi contract ID: $id ($nomor)...\n";
        $db->query("DELETE FROM trn_kontrak_simak_verifikasi_dokumen WHERE simak_id = $id");
        $db->query("DELETE FROM trn_kontrak_simak_verifikasi WHERE simak_id = $id");
        $db->query("DELETE FROM trn_kontrak_simak_share WHERE simak_id = $id");
        $db->query("DELETE FROM trn_kontrak_simak WHERE id = $id");
    }
}

// 2. Konsultasi
$res = $db->query("SELECT id, nomor_kontrak FROM trn_kontrak_simak_konsultasi WHERE nomor_kontrak LIKE 'SMOKE/%'");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $id = $row['id'];
        $nomor = $row['nomor_kontrak'];
        echo "Cleaning up Konsultasi contract ID: $id ($nomor)...\n";
        $db->query("DELETE FROM trn_kontrak_simak_konsultasi_verifikasi_dokumen WHERE simak_id = $id");
        $db->query("DELETE FROM trn_kontrak_simak_konsultasi_verifikasi WHERE simak_id = $id");
        $db->query("DELETE FROM trn_kontrak_simak_konsultasi_share WHERE simak_id = $id");
        $db->query("DELETE FROM trn_kontrak_simak_konsultasi WHERE id = $id");
    }
}

$db->close();
echo "\n[DONE] Cleanup complete.\n";
