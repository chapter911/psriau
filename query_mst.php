<?php
$db = new \PDO('mysql:host=127.0.0.1;dbname=agun9011_satkerpps', 'root', '');
foreach (['mst_kabupaten', 'mst_provinsi'] as $table) {
    echo "TABLE: $table\n";
    $stmt = $db->query("SHOW COLUMNS FROM $table");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo " - {$row['Field']} ({$row['Type']})\n";
    }
}
