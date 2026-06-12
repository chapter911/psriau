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
        if ($fields !== null) {
            if (is_array($fields)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            }
        }
    }
    $response = curl_exec($ch);
    if ($response === false) { $e = curl_error($ch); curl_close($ch); throw new Exception("CURL Error: $e"); }
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

// Get error logs list/dates
echo "=== Fetching error log dates ===\n";
$res1 = req(BASE_URL . '/admin/pengaturan/application/error-log-dates');
echo "HTTP Code: " . $res1['code'] . "\n";
echo "Body:\n" . $res1['body'] . "\n\n";

$data = json_decode($res1['body'], true);
if (isset($data['data']) && is_array($data['data']) && count($data['data']) > 0) {
    $latestLogFile = $data['data'][0];
    echo "=== Fetching log file: $latestLogFile ===\n";
    $res2 = req(BASE_URL . '/admin/pengaturan/application/error-logs?file=' . urlencode($latestLogFile));
    echo "HTTP Code: " . $res2['code'] . "\n";
    $logData = json_decode($res2['body'], true);
    if (isset($logData['data']['content'])) {
        echo "Log Content (last 5000 chars):\n";
        echo substr($logData['data']['content'], -5000) . "\n";
    } else {
        echo "No content found in response:\n" . $res2['body'] . "\n";
    }
} else {
    echo "No log files available.\n";
}
