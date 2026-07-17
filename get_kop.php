<?php
$db = new \PDO('mysql:host=127.0.0.1;dbname=agun9011_satkerpps', 'root', '');
$stmt = $db->query("SHOW COLUMNS FROM kop_surat");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) { echo $r['Field'] . "\n"; }
