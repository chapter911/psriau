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

// 1. Visit share link (token: a252dd922ae1c52a7c02af34fd9b8481f40ed8c80b3c9e7e)
$token = 'a252dd922ae1c52a7c02af34fd9b8481f40ed8c80b3c9e7e';
$shareUrl = BASE_URL . "/simak/share/$token";
req($shareUrl, 'GET');
$csrf = getCsrf($cookiesFile);

// 2. Upload Google Drive Link to row 222
$uploadUrl = BASE_URL . "/simak/share/$token/upload";
$uploadFields = [
    'row_no' => 222,
    'tipe_dokumen' => 'final',
    'upload_method' => 'drive',
    'google_drive_link' => 'https://drive.google.com/drive/folders/1zOpJs4jCLi7FYS6SHxYXd3Y7oEA47Yia',
    'csrf_test_name' => $csrf,
];
echo "Uploading Google Drive Link to public share link...\n";
$resUpload = req($uploadUrl, 'POST', http_build_query($uploadFields));
echo "Upload HTTP Code: " . $resUpload['code'] . "\n";

// 3. Login as Admin
req(BASE_URL . '/masuk', 'GET');
$csrf = getCsrf($cookiesFile);
req(BASE_URL . '/masuk', 'POST', http_build_query([
    'username'       => '199011092025061005',
    'password'       => '123456',
    'csrf_test_name' => $csrf,
]));

// 4. Admin verify row 222 as Sesuai (without file upload)
$csrf = getCsrf($cookiesFile);
$verifyUrl = BASE_URL . "/admin/kontrak/simak/konstruksi/22/verifikasi/upload";
$verifyFields = [
    'row_no'              => 222,
    'tipe_dokumen'        => 'final',
    'kelengkapan_dokumen' => 'ada',
    'verifikasi_ki'       => 'sesuai',
    'keterangan'          => "Smoke test verify drive link",
    'pic'                 => 'Smoke Test Script',
    'csrf_test_name'      => $csrf,
];
echo "Verifying row 222 as Admin (no file)...\n";
$resVerify = req($verifyUrl, 'POST', http_build_query($verifyFields));
echo "Verify HTTP Code: " . $resVerify['code'] . "\n";
