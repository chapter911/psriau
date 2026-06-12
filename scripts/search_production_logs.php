<?php
define('BASE_URL', 'https://satkerpps-riau.online');
define('TIMEOUT', 60);

$cookiesFile = __DIR__ . '/../writable/smoke_test_single_cookies.txt';

function getCsrf(string $cookiesFile): string {
    if (!file_exists($cookiesFile)) return '';
    $content = file_get_contents($cookiesFile);
    if (preg_match('/csrf_cookie_name\s+([a-f0-9]+)/', $content, $m)) return $m[1];
    return '';
}

function req(string $url, string $method = 'GET', $fields = null): array {
    global $cookiesFile;
    $ch = curl_init();
    $headers = [
        'X-Requested-With: XMLHttpRequest'
    ];
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => TIMEOUT,
        CURLOPT_COOKIEFILE     => $cookiesFile,
        CURLOPT_COOKIEJAR      => $cookiesFile,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 SmokeTest/1.0',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_HEADER         => true,
        CURLOPT_HTTPHEADER     => $headers,
    ]);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($fields !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    }
    $response = curl_exec($ch);
    $hs = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'body' => substr($response, $hs), 'headers' => substr($response, 0, $hs)];
}

// Login as Admin
req(BASE_URL . '/masuk', 'GET');
$csrf = getCsrf($cookiesFile);
req(BASE_URL . '/masuk', 'POST', http_build_query([
    'username'       => '199011092025061005',
    'password'       => '123456',
    'csrf_test_name' => $csrf,
]));

// Fetch log file
$logFile = 'log-2026-06-12.log';
echo "=== Fetching complete log file: $logFile ===\n";
$res = req(BASE_URL . '/admin/pengaturan/application/error-logs?file=' . urlencode($logFile));
$logData = json_decode($res['body'], true);
if (isset($logData['data']['content'])) {
    $content = $logData['data']['content'];
    
    // Search for keywords
    $lines = explode("\n", $content);
    $matches = [];
    foreach ($lines as $i => $line) {
        if (stripos($line, 'sharedUploadSimakDokumen') !== false 
            || stripos($line, 'GoogleDriveService') !== false 
            || stripos($line, 'GoogleOAuthService') !== false 
            || stripos($line, 'upload') !== false 
            || stripos($line, 'OTP') !== false
            || stripos($line, 'email') !== false
            || stripos($line, 'CRITICAL') !== false) {
            $matches[] = ($i + 1) . ": " . $line;
        }
    }
    
    echo "Found " . count($matches) . " matching log lines:\n";
    // Print the last 100 matching lines
    $start = max(0, count($matches) - 100);
    for ($k = $start; $k < count($matches); $k++) {
        echo $matches[$k] . "\n";
    }
} else {
    echo "Failed to retrieve log content.\n";
}
