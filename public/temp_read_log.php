<?php
if (($_GET['auth'] ?? '') !== 'Antigravity999') {
    die('Unauthorized');
}
$path = __DIR__ . '/../writable/logs/simak_upload_raw.log';
if (file_exists($path)) {
    echo file_get_contents($path);
} else {
    echo "File not found at $path";
}

echo "\n\n=== Directory Listing of writable/logs ===\n";
$files = glob(__DIR__ . '/../writable/logs/*');
if (is_array($files)) {
    foreach ($files as $file) {
        echo basename($file) . " | size: " . filesize($file) . " | modified: " . date('Y-m-d H:i:s', filemtime($file)) . "\n";
    }
}
