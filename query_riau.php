<?php
$db = new \PDO('mysql:host=127.0.0.1;dbname=agun9011_satkerpps', 'root', '');
$stmt = $db->query("SELECT kode_provinsi FROM mst_kabupaten WHERE nama_kabupaten LIKE '%KUANTAN SINGINGI%' LIMIT 1");
$prov = $stmt->fetchColumn();
echo "Prov: $prov\n";
if ($prov) {
    $stmt = $db->query("SELECT * FROM mst_biaya_harian WHERE provinsi_kode = '$prov'");
    print_r($stmt->fetch(PDO::FETCH_ASSOC));
    
    $stmt = $db->query("SELECT * FROM mst_biaya_penginapan WHERE provinsi_kode = '$prov'");
    print_r($stmt->fetch(PDO::FETCH_ASSOC));
}
