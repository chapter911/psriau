<?php
define('BASE_URL', 'https://satkerpps-riau.online');
define('TIMEOUT', 30);
define('ADMIN_USERNAME', '199011092025061005');
define('ADMIN_PASSWORD', '123456');

$cookiesFile = sys_get_temp_dir() . '/deploy_cookies.txt';
if (file_exists($cookiesFile)) unlink($cookiesFile);

function req(string $url, string $method = 'GET', $fields = null): array {
    global $cookiesFile;
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => TIMEOUT,
        CURLOPT_COOKIEFILE     => $cookiesFile,
        CURLOPT_COOKIEJAR      => $cookiesFile,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 Deploy/1.0',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_HEADER         => true,
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

function getCsrf(): string {
    global $cookiesFile;
    if (!file_exists($cookiesFile)) return '';
    $content = file_get_contents($cookiesFile);
    if (preg_match('/csrf_cookie_name\s+([a-f0-9]+)/', $content, $m)) return $m[1];
    return '';
}

// Login
echo "Logging in...\n";
req(BASE_URL . '/masuk', 'GET');
$csrf = getCsrf();
$res = req(BASE_URL . '/masuk', 'POST', http_build_query([
    'username' => ADMIN_USERNAME,
    'password' => ADMIN_PASSWORD,
    'csrf_test_name' => $csrf,
]));

// Git pull
echo "Triggering git pull...\n";
$csrf = getCsrf();
$res = req(BASE_URL . '/admin/pengaturan/application/git-pull', 'POST', http_build_query([
    'csrf_test_name' => $csrf,
]));

$body = $res['body'];

// Extract alert boxes or command output from the page
if (preg_match('/<div class="alert alert-([^"]+)"[^>]*>(.*?)<\/div>/s', $body, $matches)) {
    echo "Alert Class: " . $matches[1] . "\n";
    echo "Alert Message: " . trim(strip_tags($matches[2])) . "\n";
} else {
    echo "No alert box found.\n";
}

if (preg_match('/<pre[^>]*>(.*?)<\/pre>/s', $body, $matches)) {
    echo "Command output:\n" . trim(strip_tags($matches[1])) . "\n";
} else {
    echo "No command output pre-tag found.\n";
}

// Print if specific keywords are found
if (stripos($body, 'Git pull selesai') !== false) {
    echo "Result: Git pull selesai!\n";
} elseif (stripos($body, 'Git pull gagal') !== false) {
    echo "Result: Git pull gagal!\n";
} else {
    echo "Keyword 'Git pull' status not found in body.\n";
}

file_put_contents(__DIR__ . '/../writable/git_pull_response.html', $body);
echo "Saved response html to writable/git_pull_response.html\n";
