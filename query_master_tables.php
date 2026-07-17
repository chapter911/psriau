<?php
$db = new \PDO('mysql:host=127.0.0.1;dbname=agun9011_satkerpps', 'root', '');
foreach (['mst_biaya_harian', 'mst_biaya_penginapan', 'mst_biaya_transportasi'] as $table) {
    echo "TABLE: $table\n";
    $stmt = $db->query("SHOW COLUMNS FROM $table");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo " - {$row['Field']} ({$row['Type']})\n";
    }
    $stmt = $db->query("SELECT * FROM $table LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        print_r($row);
    }
}
