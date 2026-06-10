<?php
/**
 * Trigger git pull on production via the admin API endpoint
 */
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
if (strpos($res['body'], 'id="username"') !== false) {
    echo "[-] Login FAILED\n"; exit(1);
}
echo "[+] Login OK\n";

// Git pull
echo "Triggering git pull...\n";
$csrf = getCsrf();
$res = req(BASE_URL . '/admin/pengaturan/application/git-pull', 'POST', http_build_query([
    'csrf_test_name' => $csrf,
]));
echo "HTTP " . $res['code'] . "\n";
// Print relevant portion of body (JSON or redirect)
$body = $res['body'];
if (strlen($body) > 500) $body = substr($body, 0, 500) . '...';
echo $body . "\n";
