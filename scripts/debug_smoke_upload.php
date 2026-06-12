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
    ]);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($fields !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    }
    $response = curl_exec($ch);
    if ($response === false) { $e = curl_error($ch); curl_close($ch); throw new Exception("CURL Error: $e"); }
    $hs = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'body' => substr($response, $hs), 'headers' => substr($response, 0, $hs)];
}

// Check login first
req(BASE_URL . '/masuk', 'GET');
$csrf = getCsrf($cookiesFile);
$loginRes = req(BASE_URL . '/masuk', 'POST', http_build_query([
    'username'       => '199011092025061005',
    'password'       => '123456',
    'csrf_test_name' => $csrf,
]));

$csrf = getCsrf($cookiesFile);
echo "CSRF Token after login: $csrf\n";

// Let's try to do a verification on ID 22 (Konstruksi) row 53 with file upload
$dummyFile = __DIR__ . '/../writable/dummy_smoke_single.pdf';
if (!file_exists($dummyFile)) {
    file_put_contents($dummyFile, '%PDF-1.4 Dummy file');
}

$url = BASE_URL . "/admin/kontrak/simak/konstruksi/22/verifikasi/upload";
$fields = [
    'row_no'              => 53,
    'tipe_dokumen'        => 'final',
    'kelengkapan_dokumen' => 'ada',
    'verifikasi_ki'       => 'sesuai',
    'keterangan'          => "Smoke test verify",
    'pic'                 => 'Smoke Test Script',
    'csrf_test_name'      => $csrf,
    'dokumen_file'        => new CURLFile($dummyFile, 'application/pdf', 'smoke_verif.pdf'),
];

echo "Uploading to $url...\n";
$res = req($url, 'POST', $fields);
echo "HTTP Code: " . $res['code'] . "\n";
echo "Response headers:\n" . $res['headers'] . "\n";
if (strpos($res['body'], 'alert-danger') !== false) {
    echo "Found alert-danger in response!\n";
    if (preg_match('/<div class="alert alert-danger"[^>]*>(.*?)<\/div>/s', $res['body'], $m)) {
        echo "Alert error: " . trim(strip_tags($m[1])) . "\n";
    }
}
if (preg_match('/<div class="invalid-feedback"[^>]*>(.*?)<\/div>/s', $res['body'], $m)) {
    echo "Validation error: " . trim(strip_tags($m[1])) . "\n";
}
echo "Length of body: " . strlen($res['body']) . "\n";
file_put_contents(__DIR__ . '/../writable/debug_upload_response.html', $res['body']);
echo "Saved response body to writable/debug_upload_response.html\n";
