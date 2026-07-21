<?php
define('ENVIRONMENT', 'development');
require 'public/index.php';
$db = db_connect();
$q = $db->table('mst_kontur_geojson')->limit(1)->get();
if (method_exists($q, 'getUnbufferedRow')) {
    $row = $q->getUnbufferedRow('array');
    echo "Unbuffered row exists. ID: " . $row['id'];
} else {
    echo "No getUnbufferedRow";
}
