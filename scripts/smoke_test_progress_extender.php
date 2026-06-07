<?php
/**
 * SIMAK Smoke Test Progress Extender
 *
 * Extends the verification progress using valid visible leaf rows.
 */

define('BASE_URL', 'https://satkerpps-riau.online');
define('TIMEOUT', 30);

$cookiesFile = __DIR__ . '/../writable/smoke_test_cookies.txt';

// Dummy file paths
$dummyDraft = __DIR__ . '/../writable/dummy_draft.xlsx';
$dummyFinal = __DIR__ . '/../writable/dummy_final.pdf';

// Ensure dummy files exist
if (!file_exists($dummyDraft)) {
    file_put_contents($dummyDraft, "Dummy Excel Draft Content");
}
if (!file_exists($dummyFinal)) {
    file_put_contents($dummyFinal, "Dummy PDF Final Content");
}

// DB Helper
function queryDb(string $sql, array $params = [], string $types = '') {
    $hostname = 'satkerpps-riau.online';
    $database = 'agun9011_satkerpps';
    $username = 'agun9011_satkerpps';
    $password = '9w:wxJn|K';

    $db = new mysqli($hostname, $username, $password, $database);
    if ($db->connect_error) {
        throw new Exception("Database connection failed: " . $db->connect_error);
    }
    
    if (empty($params)) {
        $res = $db->query($sql);
        if (!$res) {
            $err = $db->error;
            $db->close();
            throw new Exception("SQL Error: " . $err);
        }
        if ($res === true) {
            $db->close();
            return true;
        }
        $data = [];
        while ($row = $res->fetch_assoc()) {
            $data[] = $row;
        }
        $res->free();
        $db->close();
        return $data;
    } else {
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            $err = $db->error;
            $db->close();
            throw new Exception("SQL Prepare Error: " . $err);
        }
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        if ($stmt->error) {
            $err = $stmt->error;
            $stmt->close();
            $db->close();
            throw new Exception("SQL Execute Error: " . $err);
        }
        $res = $stmt->get_result();
        if ($res) {
            $data = [];
            while ($row = $res->fetch_assoc()) {
                $data[] = $row;
            }
            $res->free();
            $stmt->close();
            $db->close();
            return $data;
        }
        $stmt->close();
        $db->close();
        return true;
    }
}

// CSRF helper
function getCsrfFromCookieJar(string $cookiesFile): string {
    if (!file_exists($cookiesFile)) {
        return '';
    }
    $content = file_get_contents($cookiesFile);
    if (preg_match('/csrf_cookie_name\s+([a-f0-9]+)/', $content, $matches)) {
        return $matches[1];
    }
    return '';
}

// Request helper
function makeRequest(string $url, string $method = 'GET', $fields = null, array $headers = []) {
    global $cookiesFile;
    $ch = curl_init();
    
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => TIMEOUT,
        CURLOPT_COOKIEFILE => $cookiesFile,
        CURLOPT_COOKIEJAR => $cookiesFile,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_HEADER => true,
    ];

    if ($method === 'POST') {
        $options[CURLOPT_POST] = true;
        if ($fields !== null) {
            $options[CURLOPT_POSTFIELDS] = $fields;
        }
    } else {
        $options[CURLOPT_HTTPGET] = true;
    }

    if (!empty($headers)) {
        $options[CURLOPT_HTTPHEADER] = $headers;
    }

    curl_setopt_array($ch, $options);
    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'code' => $httpCode,
        'headers' => substr($response, 0, $headerSize),
        'body' => substr($response, $headerSize),
    ];
}

echo "=== INITIATING SMOKE TEST PROGRESS EXTENDER ===\n";

// 1. Admin Login
echo "Logging in as Administrator...\n";
$loginUrl = BASE_URL . '/masuk';
makeRequest($loginUrl, 'GET');
$csrf = getCsrfFromCookieJar($cookiesFile);

$loginFields = [
    'username' => '199011092025061005',
    'password' => '123456',
    'csrf_test_name' => $csrf,
];
$res = makeRequest($loginUrl, 'POST', http_build_query($loginFields));
$csrf = getCsrfFromCookieJar($cookiesFile);

if (strpos($res['body'], 'Masuk Admin') !== false || strpos($res['body'], 'id="username"') !== false) {
    echo "  [FAIL] Admin login failed!\n";
    exit(1);
}
echo "  [SUCCESS] Admin logged in successfully.\n\n";

// 2. Fetch share tokens for target contracts
echo "Fetching share tokens...\n";
$dbKon = queryDb("SELECT share_token FROM trn_kontrak_simak_share WHERE simak_id = 20 AND is_active = 1 LIMIT 1");
if (empty($dbKon)) {
    die("Error: Share token for Konstruksi ID 20 not found in database.\n");
}
$tokenKon = $dbKon[0]['share_token'];
echo "  Konstruksi (ID 20) Share Token: $tokenKon\n";

$dbKons = queryDb("SELECT share_token FROM trn_kontrak_simak_konsultasi_share WHERE simak_id = 16 AND is_active = 1 LIMIT 1");
if (empty($dbKons)) {
    die("Error: Share token for Konsultasi ID 16 not found in database.\n");
}
$tokenKons = $dbKons[0]['share_token'];
echo "  Konsultasi (ID 16) Share Token: $tokenKons\n\n";

// Targets definitions
// Only use visible leaf rows
$konstruksiTargets = [
    'lengkap' => [
        // Already verified as sesuai: Row 182 (1 item)
        // We need 16 more to reach 17 items (17/157 = 10.82%)
        ['row' => 64, 'method' => 'none'],
        ['row' => 72, 'method' => 'none'],
        ['row' => 75, 'method' => 'none'],
        ['row' => 90, 'method' => 'none'],
        ['row' => 100, 'method' => 'none'],
        ['row' => 105, 'method' => 'none'],
        ['row' => 122, 'method' => 'none'],
        ['row' => 151, 'method' => 'none'],
        ['row' => 199, 'method' => 'none'],
        ['row' => 208, 'method' => 'none'],
        ['row' => 58, 'method' => 'none'],
        ['row' => 65, 'method' => 'none'],
        ['row' => 101, 'method' => 'none'],
        ['row' => 106, 'method' => 'none'],
        ['row' => 123, 'method' => 'none'],
        ['row' => 124, 'method' => 'none']
    ],
    'tidak_sesuai' => [
        // Already verified as tidak_sesuai: Row 53 (draft), Row 55 (final), Row 57 (final) (3 items)
        // We need 2 more to reach 5 items (5/157 = 3.18%)
        ['row' => 210, 'method' => 'none'],
        ['row' => 66, 'method' => 'none']
    ]
];

$konsultasiTargets = [
    'lengkap' => [
        // Already verified as sesuai: Row 40, 43, 44, 46 (4 items)
        // We need 6 more to reach 10 items (10/84 = 11.9%)
        ['row' => 50, 'method' => 'none'],
        ['row' => 51, 'method' => 'none'],
        ['row' => 52, 'method' => 'none'],
        ['row' => 53, 'method' => 'none'],
        ['row' => 54, 'method' => 'none'],
        ['row' => 55, 'method' => 'none']
    ],
    'tidak_sesuai' => [
        // Already verified as tidak_sesuai: Row 38 (draft), 47 (final), 49 (final) (3 items)
        // We need 0 more to reach 3 items (3/84 = 3.57%).
    ]
];

// Helper to run public uploads and admin verifications
function extendProgress(string $type, int $simakId, string $shareToken, array $targets) {
    global $cookiesFile, $dummyFinal;
    
    echo "--- Extending Progress for $type (ID: $simakId) ---\n";
    
    // Process Lengkap targets
    foreach ($targets['lengkap'] as $tgt) {
        $row = $tgt['row'];
        $method = $tgt['method'];
        
        echo "  [Lengkap] Uploading Row $row via share link...\n";
        $csrf = getCsrfFromCookieJar($cookiesFile);
        $uploadUrl = BASE_URL . "/simak/share/{$shareToken}/upload";
        
        $fields = [
            'row_no' => $row,
            'upload_method' => $method,
            'tipe_dokumen' => 'final',
            'csrf_test_name' => $csrf
        ];
        
        if ($method === 'file') {
            $fields['dokumen_file'] = new CURLFile($dummyFinal, 'application/pdf', 'dummy_final.pdf');
            $res = makeRequest($uploadUrl, 'POST', $fields);
        } else {
            $fields['keterangan'] = 'Dokumen pendukung lengkap dan diupload oleh responder';
            $res = makeRequest($uploadUrl, 'POST', http_build_query($fields));
        }
        
        // Admin Verification (Sesuai)
        echo "  [Lengkap] Verifying Row $row as Sesuai...\n";
        $csrf = getCsrfFromCookieJar($cookiesFile);
        $verifyUrl = BASE_URL . "/admin/kontrak/simak/" . ($type === 'Konstruksi' ? 'konstruksi' : 'konsultasi') . "/{$simakId}/verifikasi/upload";
        
        // Verify document
        $verifyFields = [
            'row_no' => $row,
            'tipe_dokumen' => 'final',
            'kelengkapan_dokumen' => 'ada',
            'verifikasi_ki' => 'sesuai',
            'keterangan' => 'Verified Sesuai by Extender Script',
            'pic' => 'Admin Progress Extender',
            'csrf_test_name' => $csrf
        ];
        $res = makeRequest($verifyUrl, 'POST', http_build_query($verifyFields));
    }
    
    // Process Tidak Sesuai targets
    foreach ($targets['tidak_sesuai'] as $tgt) {
        $row = $tgt['row'];
        $method = $tgt['method'];
        
        echo "  [Tidak Sesuai] Uploading Row $row via share link...\n";
        $csrf = getCsrfFromCookieJar($cookiesFile);
        $uploadUrl = BASE_URL . "/simak/share/{$shareToken}/upload";
        
        $fields = [
            'row_no' => $row,
            'upload_method' => $method,
            'tipe_dokumen' => 'final',
            'csrf_test_name' => $csrf
        ];
        
        if ($method === 'file') {
            $fields['dokumen_file'] = new CURLFile($dummyFinal, 'application/pdf', 'dummy_final.pdf');
            $res = makeRequest($uploadUrl, 'POST', $fields);
        } else {
            $fields['keterangan'] = 'Dokumen sementara belum lengkap';
            $res = makeRequest($uploadUrl, 'POST', http_build_query($fields));
        }
        
        // Admin Verification (Tidak Sesuai)
        echo "  [Tidak Sesuai] Verifying Row $row as Tidak Sesuai...\n";
        $csrf = getCsrfFromCookieJar($cookiesFile);
        $verifyUrl = BASE_URL . "/admin/kontrak/simak/" . ($type === 'Konstruksi' ? 'konstruksi' : 'konsultasi') . "/{$simakId}/verifikasi/upload";
        
        $verifyFields = [
            'row_no' => $row,
            'tipe_dokumen' => 'final',
            'kelengkapan_dokumen' => 'ada',
            'verifikasi_ki' => 'tidak_sesuai',
            'keterangan' => 'Verified Tidak Sesuai by Extender Script',
            'pic' => 'Admin Progress Extender',
            'csrf_test_name' => $csrf
        ];
        $res = makeRequest($verifyUrl, 'POST', http_build_query($verifyFields));
    }
    
    echo "Finished process for $type (ID: $simakId)\n\n";
}

// Execute extender
extendProgress('Konstruksi', 20, $tokenKon, $konstruksiTargets);
extendProgress('Konsultasi', 16, $tokenKons, $konsultasiTargets);

echo "=== PROGRESS EXTENDED SUCCESSFULLY ===\n";
