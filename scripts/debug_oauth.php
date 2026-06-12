<?php
$db = new mysqli('satkerpps-riau.online', 'agun9011_satkerpps', '9w:wxJn|K', 'agun9011_satkerpps');
if ($db->connect_error) {
    die("DB connect failed: " . $db->connect_error);
}

echo "=== SHOW TABLES LIKE %google% OR %oauth% ===\n";
$res = $db->query("SHOW TABLES");
while ($row = $res->fetch_row()) {
    $tbl = $row[0];
    if (strpos($tbl, 'google') !== false || strpos($tbl, 'oauth') !== false || strpos($tbl, 'token') !== false || strpos($tbl, 'config') !== false || strpos($tbl, 'setting') !== false) {
        echo "$tbl\n";
    }
}

echo "\n=== sys_settings or similar ===\n";
$res = $db->query("SHOW TABLES LIKE '%setting%'");
while ($row = $res->fetch_row()) {
    echo $row[0] . "\n";
}

echo "\n=== Querying trn_google_oauth if exists ===\n";
$res = $db->query("SHOW TABLES LIKE 'trn_google_oauth%'");
if ($res->num_rows > 0) {
    $tbl = $res->fetch_row()[0];
    $res2 = $db->query("SELECT * FROM $tbl");
    while ($row = $res2->fetch_assoc()) {
        // Obfuscate sensitive token parts
        if (isset($row['access_token'])) $row['access_token'] = substr($row['access_token'], 0, 10) . '...';
        if (isset($row['refresh_token'])) $row['refresh_token'] = substr($row['refresh_token'], 0, 10) . '...';
        print_r($row);
    }
} else {
    echo "No trn_google_oauth table found.\n";
}

$db->close();
