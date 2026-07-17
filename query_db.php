<?php
$db = new \PDO('mysql:host=127.0.0.1;dbname=agun9011_satkerpps', 'root', '');
$stmt = $db->query("SELECT * FROM laporan_perjalanan_dinas ORDER BY id DESC LIMIT 1");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($row);
