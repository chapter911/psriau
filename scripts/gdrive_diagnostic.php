<?php
/**
 * Google Drive Upload Diagnostic & Log
 *
 * Menguji koneksi dan upload ke Google Drive secara langsung
 * dan menghasilkan log lengkap dengan penyebab kegagalan.
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

$jsonPath     = $envVars['GOOGLE_SERVICE_ACCOUNT_JSON_PATH'] ?? '';
$folderId     = $envVars['GOOGLE_DRIVE_UPLOAD_FOLDER_ID'] ?? '';
$folderUrl    = $envVars['GOOGLE_DRIVE_UPLOAD_FOLDER_URL'] ?? '';

// Resolve path
if ($jsonPath !== '' && $jsonPath[0] !== '/' && substr($jsonPath, 1, 2) !== ':\\') {
    $jsonPath = ROOTPATH . $jsonPath;
}

$timestamp = date('Y-m-d H:i:s');
$logLines  = [];

$log = function(string $level, string $msg) use (&$logLines) {
    $line = "[{$level}] {$msg}";
    echo $line . PHP_EOL;
    $logLines[] = date('Y-m-d H:i:s') . ' ' . $line;
};

$log('INFO', '================================================');
$log('INFO', '  Google Drive Upload Diagnostic');
$log('INFO', "  Run at: {$timestamp}");
$log('INFO', '================================================');

// -------------------------------------------------------
// 1. Cek File Kredensial
// -------------------------------------------------------
$log('INFO', '');
$log('INFO', '[1] Cek konfigurasi .env ...');
$log('INFO', "  GOOGLE_SERVICE_ACCOUNT_JSON_PATH = '{$jsonPath}'");
$log('INFO', "  GOOGLE_DRIVE_UPLOAD_FOLDER_ID    = '{$folderId}'");
$log('INFO', "  GOOGLE_DRIVE_UPLOAD_FOLDER_URL   = '{$folderUrl}'");

if (empty($jsonPath)) {
    $log('ERROR', '  GOOGLE_SERVICE_ACCOUNT_JSON_PATH kosong di .env!');
} elseif (!file_exists($jsonPath)) {
    $log('ERROR', "  File kredensial TIDAK DITEMUKAN: {$jsonPath}");
    $log('INFO',  "  PHP __DIR__  = " . __DIR__);
    $log('INFO',  "  ROOTPATH     = " . ROOTPATH);
    $log('INFO',  "  realpath()   = " . (realpath($jsonPath) ?: 'tidak ada'));
    // Coba cari di beberapa lokasi
    $candidates = [
        ROOTPATH . 'google-service-account.json',
        '/home/agun9011/public_html/google-service-account.json',
        '/home/agun9011/public_html/satkerpps/google-service-account.json',
        dirname(ROOTPATH) . '/google-service-account.json',
    ];
    $log('INFO', '  Mencoba lokasi kandidat:');
    foreach ($candidates as $c) {
        $exists = file_exists($c) ? 'ADA ✓' : 'tidak ada';
        $log('INFO', "    {$c} -> {$exists}");
    }
} else {
    $log('INFO', "  [+] File kredensial DITEMUKAN: {$jsonPath}");
    $log('INFO', "      Ukuran: " . filesize($jsonPath) . " bytes");
    $json = json_decode(file_get_contents($jsonPath), true);
    if (!is_array($json)) {
        $log('ERROR', '  File JSON tidak valid!');
    } else {
        $log('INFO', "      type              : " . ($json['type'] ?? '?'));
        $log('INFO', "      project_id        : " . ($json['project_id'] ?? '?'));
        $log('INFO', "      client_email      : " . ($json['client_email'] ?? '?'));
        $log('INFO', "      private_key_id    : " . ($json['private_key_id'] ?? '?'));
    }
}

// -------------------------------------------------------
// 2. Init Google Client
// -------------------------------------------------------
$log('INFO', '');
$log('INFO', '[2] Inisialisasi Google Client ...');

$client  = null;
$service = null;

if (file_exists($jsonPath)) {
    try {
        $client = new \Google\Client();
        $client->setAuthConfig($jsonPath);
        $client->addScope(\Google\Service\Drive::DRIVE);
        $client->addScope(\Google\Service\Drive::DRIVE_FILE);
        $service = new \Google\Service\Drive($client);
        $log('INFO', '  [+] Google Client berhasil diinisialisasi.');
    } catch (\Throwable $e) {
        $log('ERROR', '  Gagal init Google Client: ' . $e->getMessage());
    }
}

// -------------------------------------------------------
// 3. Cek Akses Folder Drive
// -------------------------------------------------------
$log('INFO', '');
$log('INFO', '[3] Cek akses ke folder Google Drive ...');

if ($service && !empty($folderId)) {
    try {
        $folder = $service->files->get($folderId, [
            'fields' => 'id, name, mimeType, capabilities, driveId, shared',
        ]);
        $log('INFO', '  [+] Folder berhasil diakses!');
        $log('INFO', "      Nama folder : " . $folder->getName());
        $log('INFO', "      MIME type   : " . $folder->getMimeType());
        $log('INFO', "      Drive ID    : " . ($folder->getDriveId() ?: '(My Drive - bukan Shared Drive)'));
        $log('INFO', "      Shared      : " . ($folder->getShared() ? 'Ya' : 'Tidak'));

        $caps = $folder->getCapabilities();
        if ($caps) {
            $canAdd = $caps->getCanAddChildren() ? 'Ya' : 'Tidak';
            $canUpload = $caps->getCanUploadFiles() ? 'Ya' : 'Tidak (PROBLEM!)';
            $log('INFO', "      canAddChildren  : {$canAdd}");
            $log('INFO', "      canUploadFiles  : {$canUpload}");
        }

        $driveId = $folder->getDriveId();
        if (empty($driveId)) {
            $log('WARNING', '  Folder berada di My Drive (bukan Shared Drive).');
            $log('WARNING', '  Service Account TIDAK memiliki storage quota untuk My Drive.');
            $log('WARNING', '  SOLUSI: Gunakan Shared Drive atau bagikan folder ke Service Account');
            $log('WARNING', "  dengan akses Editor, dan tambahkan supportsAllDrives=true.");
        } else {
            $log('INFO', '  [+] Folder berada di Shared Drive (Team Drive). Ini sudah benar!');
        }
    } catch (\Throwable $e) {
        $log('ERROR', '  Gagal mengakses folder: ' . $e->getMessage());
        if (str_contains($e->getMessage(), '404')) {
            $log('ERROR', '  Folder tidak ditemukan. Pastikan:');
            $log('ERROR', '  - GOOGLE_DRIVE_UPLOAD_FOLDER_ID benar');
            $log('ERROR', '  - Folder sudah di-share ke service account email');
        }
        if (str_contains($e->getMessage(), '403')) {
            $log('ERROR', '  Permission denied. Service Account belum diberi akses ke folder ini.');
        }
    }
} elseif (empty($folderId)) {
    $log('ERROR', '  GOOGLE_DRIVE_UPLOAD_FOLDER_ID kosong di .env!');
}

// -------------------------------------------------------
// 4. Coba Upload File Test
// -------------------------------------------------------
$log('INFO', '');
$log('INFO', '[4] Mencoba upload file test ke Google Drive ...');

if ($service && !empty($folderId)) {
    // Buat file dummy kecil
    $tmpFile = sys_get_temp_dir() . '/gdrive_test_' . time() . '.txt';
    file_put_contents($tmpFile, "Google Drive Upload Test\nTimestamp: {$timestamp}\nFile ini bisa dihapus.");

    try {
        $fileMetadata = new \Google\Service\Drive\DriveFile([
            'name'    => 'SMOKE_TEST_DELETE_ME_' . date('YmdHis') . '.txt',
            'parents' => [$folderId],
        ]);

        $optParams = [
            'data'       => file_get_contents($tmpFile),
            'mimeType'   => 'text/plain',
            'uploadType' => 'multipart',
            'fields'     => 'id, webViewLink, name',
        ];

        // Coba dengan supportsAllDrives=true dulu
        $optParams['supportsAllDrives'] = true;

        $file = $service->files->create($fileMetadata, $optParams);
        $log('INFO',  '  [+] UPLOAD BERHASIL!');
        $log('INFO',  '      File ID       : ' . $file->getId());
        $log('INFO',  '      File name     : ' . $file->getName());
        $log('INFO',  '      Web View Link : ' . $file->getWebViewLink());
        $log('INFO',  '  >>> Silakan hapus file test tersebut dari Google Drive Anda <<<');

    } catch (\Throwable $e) {
        $log('ERROR', '  Upload GAGAL: ' . $e->getMessage());

        // Parse error detail
        $errMsg = $e->getMessage();
        if (str_contains($errMsg, 'storage quota')) {
            $log('ERROR', '');
            $log('ERROR', '  DIAGNOSIS: Service Account tidak punya storage quota.');
            $log('ERROR', '  Ini terjadi karena folder tujuan adalah "My Drive" milik Google Account biasa.');
            $log('ERROR', '');
            $log('ERROR', '  SOLUSI A (Disarankan): Gunakan Shared Drive (Team Drive)');
            $log('ERROR', '    1. Buka drive.google.com');
            $log('ERROR', '    2. Di sidebar kiri, klik "Shared drives" atau "Drive bersama"');
            $log('ERROR', '    3. Buat Shared Drive baru');
            $log('ERROR', '    4. Di Shared Drive tersebut, undang service account email sebagai Member');
            $log('ERROR', "       Email SA: " . (json_decode(file_get_contents($jsonPath), true)['client_email'] ?? 'cek file JSON'));
            $log('ERROR', '    5. Buat folder baru di dalam Shared Drive tersebut');
            $log('ERROR', '    6. Copy folder ID dari URL dan update GOOGLE_DRIVE_UPLOAD_FOLDER_ID di .env');
            $log('ERROR', '');
            $log('ERROR', '  SOLUSI B: Ubah kepemilikan folder ke Service Account');
            $log('ERROR', '    Tidak disarankan karena Service Account tidak memiliki storage sendiri.');
            $log('ERROR', '');
            $log('ERROR', '  SOLUSI C (sementara): Simpan file di server lokal (tanpa Google Drive)');
            $log('ERROR', '    Update GoogleDriveService agar jika gagal upload ke Drive,');
            $log('ERROR', '    file tetap tersimpan di server lokal dan link mengarah ke local URL.');
        }

        if (str_contains($errMsg, '403')) {
            $log('ERROR', '  DIAGNOSIS: Permission denied (403).');
            $log('ERROR', '  Pastikan Service Account sudah diberi akses "Editor" ke folder Drive.');
        }

        if (str_contains($errMsg, '404')) {
            $log('ERROR', '  DIAGNOSIS: Folder tidak ditemukan (404).');
            $log('ERROR', "  Cek apakah GOOGLE_DRIVE_UPLOAD_FOLDER_ID = '{$folderId}' sudah benar.");
        }
    }

    if (file_exists($tmpFile)) unlink($tmpFile);
} else {
    $log('INFO', '  Dilewati karena service atau folderId tidak tersedia.');
}

// -------------------------------------------------------
// 5. Simpan Log ke File
// -------------------------------------------------------
$log('INFO', '');
$log('INFO', '[5] Menyimpan log ke file ...');

$logFilePath = ROOTPATH . 'writable/logs/gdrive_diagnostic_' . date('Ymd_His') . '.log';
file_put_contents($logFilePath, implode(PHP_EOL, $logLines) . PHP_EOL);
$log('INFO', "  Log tersimpan: {$logFilePath}");
$log('INFO', '');
$log('INFO', '================================================');
$log('INFO', '  Diagnostic selesai.');
$log('INFO', '================================================');
