<?php
/**
 * Google Drive OAuth Diagnostic
 *
 * Menguji koneksi OAuth ke Google Drive dan menghasilkan log lengkap.
 */

require_once __DIR__ . '/../vendor/autoload.php';

define('ROOTPATH', __DIR__ . '/../');

// Baca .env manual
$envFile = ROOTPATH . '.env';
$envVars = [];
if (file_exists($envFile)) {
    foreach (file($envFile) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (str_contains($line, '=')) {
            [$key, $val] = explode('=', $line, 2);
            $envVars[trim($key)] = trim(trim($val), " \t'\"");
        }
    }
}

$clientId     = $envVars['GOOGLE_CLIENT_ID'] ?? '';
$clientSecret = $envVars['GOOGLE_CLIENT_SECRET'] ?? '';
$folderId     = $envVars['GOOGLE_DRIVE_UPLOAD_FOLDER_ID'] ?? '';
$folderUrl    = $envVars['GOOGLE_DRIVE_UPLOAD_FOLDER_URL'] ?? '';
$baseUrl      = $envVars["app.baseURL"] ?? 'http://localhost:8080/';
$baseUrl      = trim($baseUrl, " \t'\"");
$redirectUri  = rtrim($baseUrl, '/') . '/oauth/callback';

$timestamp = date('Y-m-d H:i:s');
$logLines  = [];

$log = function(string $level, string $msg) use (&$logLines) {
    $line = "[{$level}] {$msg}";
    echo $line . PHP_EOL;
    $logLines[] = date('Y-m-d H:i:s') . ' ' . $line;
};

$log('INFO', '================================================');
$log('INFO', '  Google Drive OAuth Diagnostic');
$log('INFO', "  Run at: {$timestamp}");
$log('INFO', '================================================');

// -------------------------------------------------------
// 1. Cek Konfigurasi
// -------------------------------------------------------
$log('INFO', '');
$log('INFO', '[1] Cek konfigurasi OAuth ...');
$log('INFO', "  Client ID     : " . (empty($clientId) ? 'KOSONG!' : substr($clientId, 0, 30) . '...'));
$log('INFO', "  Client Secret : " . (empty($clientSecret) ? 'KOSONG!' : 'ADA ✓'));
$log('INFO', "  Folder ID     : " . (empty($folderId) ? 'KOSONG!' : $folderId));
$log('INFO', "  Base URL      : " . $baseUrl);
$log('INFO', "  Redirect URI  : " . $redirectUri);

if (empty($clientId) || empty($clientSecret)) {
    $log('ERROR', '  OAuth credentials belum diset di .env!');
    $log('ERROR', '  Pastikan GOOGLE_CLIENT_ID dan GOOGLE_CLIENT_SECRET sudah ada.');
}

// -------------------------------------------------------
// 2. Cek Token File
// -------------------------------------------------------
$log('INFO', '');
$log('INFO', '[2] Cek OAuth token file ...');

$tokenPath = ROOTPATH . 'writable/google_access_token.json';
$log('INFO', "  Token path    : {$tokenPath}");

if (file_exists($tokenPath)) {
    $token = json_decode(file_get_contents($tokenPath), true);
    $log('INFO', '  Token file   : ADA ✓');
    $log('INFO', '  Has access_token: ' . (isset($token['access_token']) ? 'Ya' : 'Tidak'));
    $log('INFO', '  Has refresh_token: ' . (isset($token['refresh_token']) ? 'Ya' : 'Tidak'));

    if (isset($token['expiry'])) {
        $expiry = date('Y-m-d H:i:s', $token['expiry']);
        $log('INFO', "  Token expires : {$expiry}");
    }
} else {
    $log('WARNING', '  Token file   : TIDAK ADA');
    $log('INFO', '  Anda perlu login OAuth terlebih dahulu.');
}

// -------------------------------------------------------
// 3. Cek Folder Akses
// -------------------------------------------------------
$log('INFO', '');
$log('INFO', '[3] Cek akses folder Google Drive ...');

if (!empty($folderId) && file_exists($tokenPath)) {
    try {
        $token = json_decode(file_get_contents($tokenPath), true);

        $client = new \Google\Client();
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setAccessToken($token);

        $service = new \Google\Service\Drive($client);

        $folder = $service->files->get($folderId, [
            'fields' => 'id, name, mimeType, shared',
        ]);

        $log('INFO', '  [+] Folder berhasil diakses!');
        $log('INFO', "      Nama folder : " . $folder->getName());
        $log('INFO', "      MIME type   : " . $folder->getMimeType());
        $log('INFO', "      Shared      : " . ($folder->getShared() ? 'Ya' : 'Tidak'));

    } catch (\Throwable $e) {
        $log('ERROR', '  Gagal mengakses folder: ' . $e->getMessage());
    }
} elseif (empty($folderId)) {
    $log('ERROR', '  GOOGLE_DRIVE_UPLOAD_FOLDER_ID kosong!');
} else {
    $log('INFO', '  Dilewati karena token belum ada.');
}

// -------------------------------------------------------
// 4. Test Upload
// -------------------------------------------------------
$log('INFO', '');
$log('INFO', '[4] Test upload file ...');

if (!empty($folderId) && file_exists($tokenPath)) {
    try {
        $token = json_decode(file_get_contents($tokenPath), true);

        $client = new \Google\Client();
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setAccessToken($token);

        $service = new \Google\Service\Drive($client);

        // Buat file test
        $testContent = "OAuth Test\nTimestamp: {$timestamp}";
        $testFileName = 'OAUTH_TEST_' . date('YmdHis') . '.txt';

        $fileMetadata = new \Google\Service\Drive\DriveFile([
            'name'    => $testFileName,
            'parents' => [$folderId],
        ]);

        $file = $service->files->create($fileMetadata, [
            'data'       => $testContent,
            'mimeType'   => 'text/plain',
            'uploadType' => 'multipart',
            'fields'     => 'id, webViewLink, name',
        ]);

        $log('INFO', '  [+] UPLOAD BERHASIL!');
        $log('INFO', '      File name    : ' . $file->getName());
        $log('INFO', '      Web View Link : ' . $file->getWebViewLink());
        $log('INFO', '  >>> Hapus file test tersebut dari Google Drive Anda <<<');

    } catch (\Throwable $e) {
        $log('ERROR', '  Upload GAGAL: ' . $e->getMessage());
    }
} else {
    $log('INFO', '  Dilewati karena token belum ada.');
}

// -------------------------------------------------------
// 5. Langkah Selanjutnya
// -------------------------------------------------------
$log('INFO', '');
$log('INFO', '[5] Langkah selanjutnya ...');

if (!file_exists($tokenPath)) {
    $log('INFO', '  ================================================');
    $log('INFO', '  IKUTI LANGKAH BERIKUT:');
    $log('INFO', '  ================================================');
    $log('INFO', '');
    $log('INFO', '  1. Pastikan Google Drive API sudah ENABLE');
    $log('INFO', '     Buka: https://console.cloud.google.com/');
    $log('INFO', '     -> APIs& Services -> Library -> Google Drive API -> Enable');
    $log('INFO', '');
    $log('INFO', '  2. Pastikan redirect URI sudah diset di Google Cloud Console:');
    $log('INFO', "     URI: {$redirectUri}");
    $log('INFO', '     Buka: https://console.cloud.google.com/');
    $log('INFO', '     -> APIs& Services -> Credentials');
    $log('INFO', '     -> Edit OAuth 2.0 Client ID');
    $log('INFO', '     -> Authorized redirect URIs -> Add URI');
    $log('INFO', '     -> Masukkan URI di atas');
    $log('INFO', '');
    $log('INFO', '  3. Buka browser dan kunjungi:');
    $log('INFO', "     {$redirectUri}");
    $log('INFO', '     ATAU klik link ini untuk login OAuth:');
    $log('INFO', '');
    $log('INFO', ' 4. Setelah login, Anda akan diarahkan ke halaman success');
    $log('INFO', '');
    $log('INFO', '  5. Jalankan diagnostic ini lagi untuk verifikasi:');
    $log('INFO', '     php scripts/gdrive_oauth_diagnostic.php');
} else {
    $log('INFO', '  OAuth sudah tersetting. Upload sudah bisa dilakukan.');
}

// -------------------------------------------------------
// 6. Simpan Log
// -------------------------------------------------------
$log('INFO', '');
$log('INFO', '[6] Menyimpan log ...');

$logFilePath = ROOTPATH . 'writable/logs/gdrive_oauth_diagnostic_' . date('Ymd_His') . '.log';
file_put_contents($logFilePath, implode(PHP_EOL, $logLines) . PHP_EOL);
$log('INFO', "  Log tersimpan: {$logFilePath}");
$log('INFO', '');
$log('INFO', '================================================');
$log('INFO', '  Diagnostic selesai.');
$log('INFO', '================================================');
