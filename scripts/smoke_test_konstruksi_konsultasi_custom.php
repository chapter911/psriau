<?php
/**
 * SIMAK Smoke Test for Konstruksi and Konsultasi
 * Input 3 new data points for Konstruksi and 3 new data points for Konsultasi.
 * Targets:
 *   - Lengkap > 20%
 *   - Belum Sesuai > 4%
 * Alur:
 *   - Input data via public share link
 *   - Verifikasi data via admin
 */

define('BASE_URL', 'https://satkerpps-riau.online');
define('TIMEOUT', 60);

$cookiesFile = __DIR__ . '/../writable/smoke_test_custom_cookies.txt';
if (file_exists($cookiesFile)) {
    unlink($cookiesFile);
}

// Database helper
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

// CSRF extraction from Cookie Jar file
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

// Curl HTTP request helper
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
    if ($response === false) {
        $err = curl_error($ch);
        curl_close($ch);
        throw new Exception("CURL Error visiting $url: " . $err);
    }
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);

    $headerStr = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);

    return [
        'code' => $httpCode,
        'headers' => $headerStr,
        'body' => $body,
        'url' => $effectiveUrl,
    ];
}

echo "========================================================\n";
echo "    SIMAK CUSTOM SMOKE TEST: KONSTRUKSI & KONSULTASI     \n";
echo "========================================================\n\n";

// 1. Login Admin
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
    echo "[-] Admin login failed!\n";
    exit(1);
}
echo "[+] Admin logged in successfully.\n\n";

// Define targeted leaf rows
// Konstruksi (Total 157 leaf rows):
// - Lengkap > 20% -> Need at least 32 leaf rows (32 / 157 = 20.38%)
// - Belum Sesuai > 4% -> Need at least 7 leaf rows (7 / 157 = 4.46%)
$konLengkapRows = [
    75, 76, 77, 78, 79, 80, 81, 82, 83, 85, 
    86, 87, 90, 91, 92, 93, 94, 95, 96, 97, 
    100, 101, 103, 105, 106, 107, 108, 110, 
    111, 112, 113, 114
];
$konBelumSesuaiRows = [115, 116, 117, 119, 122, 123, 124];

// Konsultasi (Total 84 leaf rows):
// - Lengkap > 20% -> Need at least 17 leaf rows (17 / 84 = 20.24%)
// - Belum Sesuai > 4% -> Need at least 4 leaf rows (4 / 84 = 4.76%)
$konsLengkapRows = [
    60, 61, 62, 64, 65, 67, 47, 49, 50, 51, 
    52, 53, 54, 55, 56, 57, 59
];
$konsBelumSesuaiRows = [40, 43, 44, 46];

$timestamp = date('YmdHis');

// ==================== RUN KONSTRUKSI ====================
echo "==================== SUITE 1: KONSTRUKSI ====================\n";

$konstruksiContracts = [
    [
        'name' => "Smoke Test Konstruksi 1 - $timestamp",
        'nomor' => "SMOKE/KON/CUST/1/$timestamp",
    ],
    [
        'name' => "Smoke Test Konstruksi 2 - $timestamp",
        'nomor' => "SMOKE/KON/CUST/2/$timestamp",
    ],
    [
        'name' => "Smoke Test Konstruksi 3 - $timestamp",
        'nomor' => "SMOKE/KON/CUST/3/$timestamp",
    ],
];

foreach ($konstruksiContracts as $idx => $contract) {
    $cNum = $idx + 1;
    echo "\n[Konstruksi Contract #$cNum] Creating Contract: {$contract['name']} ({$contract['nomor']})\n";

    // 1. Create contract via Admin
    $csrf = getCsrfFromCookieJar($cookiesFile);
    $fields = [
        'satker' => 'Perencanaan Prasarana Strategis',
        'ppk_nip' => '199012212018021001',
        'ppk_nama' => 'Nurhidayat Nugroho, S.Ars',
        'nama_paket' => $contract['name'],
        'tahun_anggaran' => '2026 - 2027',
        'penyedia' => 'CV Penyedia Konstruksi Smoke',
        'penyedia_jasa_konsultansi' => 'CV Penyedia Konstruksi Smoke',
        'nomor_kontrak' => $contract['nomor'],
        'nilai_kontrak' => '500000000',
        'nilai_kontrak_jasa_konsultansi' => '250000000',
        'jenis_pekerjaan_jasa_konsultansi' => 'perencanaan',
        'masa_pelaksanaan' => 'syc',
        'pagu_anggaran' => '1000000000',
        'metode_pemilihan' => 'seleksi',
        'email_responden_1' => 'agung.justik@gmail.com',
        'email_responden_2' => '',
        'csrf_test_name' => $csrf,
    ];
    makeRequest(BASE_URL . '/admin/kontrak/simak/konstruksi/tambah', 'POST', http_build_query($fields));

    // Get the Simak ID from Database
    $dbRow = queryDb("SELECT id FROM trn_kontrak_simak WHERE nomor_kontrak = ?", [$contract['nomor']], 's');
    if (empty($dbRow)) {
        echo "[-] Failed to find created contract in DB!\n";
        exit(1);
    }
    $simakId = (int) $dbRow[0]['id'];
    echo "[+] Created contract successfully. Simak ID: $simakId\n";

    // 2. Generate Share Link via Admin
    echo "[Konstruksi Contract #$cNum] Generating share link...\n";
    $csrf = getCsrfFromCookieJar($cookiesFile);
    $shareFields = [
        'duration' => '1week',
        'csrf_test_name' => $csrf,
    ];
    makeRequest(BASE_URL . "/admin/kontrak/simak/konstruksi/{$simakId}/share", 'POST', http_build_query($shareFields));

    // Get Share Token from Database
    $dbShare = queryDb("SELECT share_token FROM trn_kontrak_simak_share WHERE simak_id = ? AND is_active = 1 LIMIT 1", [$simakId], 'i');
    if (empty($dbShare)) {
        echo "[-] Failed to find share token in DB!\n";
        exit(1);
    }
    $shareToken = $dbShare[0]['share_token'];
    echo "[+] Share link generated. Token: $shareToken\n";

    // 3. Upload responses via Share Link (OTP Bypassed)
    echo "[Konstruksi Contract #$cNum] Uploading responses via public share link...\n";
    $uploadUrl = BASE_URL . "/simak/share/{$shareToken}/upload";

    // A. Upload target Lengkap rows (32 rows)
    foreach ($konLengkapRows as $row) {
        $csrf = getCsrfFromCookieJar($cookiesFile);
        $uploadFields = [
            'row_no' => $row,
            'upload_method' => 'drive',
            'tipe_dokumen' => 'final',
            'google_drive_link' => 'https://drive.google.com/drive/folders/1zOpJs4jCLi7FYS6SHxYXd3Y7oEA47Yia',
            'csrf_test_name' => $csrf,
        ];
        makeRequest($uploadUrl, 'POST', http_build_query($uploadFields));
    }

    // B. Upload target Belum Sesuai rows (7 rows)
    foreach ($konBelumSesuaiRows as $row) {
        $csrf = getCsrfFromCookieJar($cookiesFile);
        $uploadFields = [
            'row_no' => $row,
            'upload_method' => 'drive',
            'tipe_dokumen' => 'final',
            'google_drive_link' => 'https://drive.google.com/drive/folders/1zOpJs4jCLi7FYS6SHxYXd3Y7oEA47Yia',
            'csrf_test_name' => $csrf,
        ];
        makeRequest($uploadUrl, 'POST', http_build_query($uploadFields));
    }
    echo "[+] Uploaded all responses via public share link.\n";

    // 4. Perform Verifications via Admin
    echo "[Konstruksi Contract #$cNum] Performing admin verifications...\n";
    $verifyUrl = BASE_URL . "/admin/kontrak/simak/konstruksi/{$simakId}/verifikasi/upload";

    // A. Verify Lengkap rows as Sesuai
    foreach ($konLengkapRows as $row) {
        $csrf = getCsrfFromCookieJar($cookiesFile);
        $verifyFields = [
            'row_no' => $row,
            'tipe_dokumen' => 'final',
            'kelengkapan_dokumen' => 'ada',
            'verifikasi_ki' => 'sesuai',
            'keterangan' => 'Smoke Test - Lengkap Sesuai',
            'pic' => 'Admin Smoke Test',
            'csrf_test_name' => $csrf,
        ];
        makeRequest($verifyUrl, 'POST', http_build_query($verifyFields));
    }

    // B. Verify Belum Sesuai rows as Tidak Sesuai
    foreach ($konBelumSesuaiRows as $row) {
        $csrf = getCsrfFromCookieJar($cookiesFile);
        $verifyFields = [
            'row_no' => $row,
            'tipe_dokumen' => 'final',
            'kelengkapan_dokumen' => 'ada',
            'verifikasi_ki' => 'tidak_sesuai',
            'keterangan' => 'Smoke Test - Belum Sesuai Tidak Sesuai',
            'pic' => 'Admin Smoke Test',
            'csrf_test_name' => $csrf,
        ];
        makeRequest($verifyUrl, 'POST', http_build_query($verifyFields));
    }
    echo "[+] Verifications completed.\n";

    // 5. Verify live percentages
    $detailUrl = BASE_URL . "/admin/kontrak/simak/konstruksi/{$simakId}";
    $detailRes = makeRequest($detailUrl, 'GET');
    
    $lengkapPercentage = 0.0;
    $belumSesuaiPercentage = 0.0;
    
    if (preg_match_all('/<div class="kelengkapan-value">([\d,.]+)%<\/div>/i', $detailRes['body'], $matches)) {
        if (isset($matches[1][0])) {
            $lengkapPercentage = (float) str_replace(',', '.', $matches[1][0]);
        }
        if (isset($matches[1][1])) {
            $belumSesuaiPercentage = (float) str_replace(',', '.', $matches[1][1]);
        }
    }
    
    echo "[+] Live percentages for Konstruksi Contract #$cNum: Lengkap = {$lengkapPercentage}%, Belum Sesuai = {$belumSesuaiPercentage}%\n";
    
    // DB Calculation
    $dbData = queryDb("SELECT row_no, kelengkapan_dokumen, verifikasi_ki FROM trn_kontrak_simak_verifikasi WHERE simak_id = ?", [$simakId], 'i');
    $dbLengkap = 0;
    $dbBelumSesuai = 0;
    foreach ($dbData as $row) {
        if ($row['verifikasi_ki'] === 'sesuai') {
            $dbLengkap++;
        } elseif ($row['verifikasi_ki'] === 'belum_sesuai') {
            $dbBelumSesuai++;
        }
    }
    $calcLengkap = round(($dbLengkap / 157) * 100, 2);
    $calcBelumSesuai = round(($dbBelumSesuai / 157) * 100, 2);
    echo "    - DB Calculations: Lengkap = {$calcLengkap}% (Count: $dbLengkap/157), Belum Sesuai = {$calcBelumSesuai}% (Count: $dbBelumSesuai/157)\n";

    if ($calcLengkap > 20 && $calcBelumSesuai > 4) {
        echo "[+] [SUCCESS] Targets met for Konstruksi Contract #$cNum\n";
    } else {
        echo "[-] [FAIL] Targets NOT met for Konstruksi Contract #$cNum\n";
        exit(1);
    }
}


// ==================== RUN KONSULTASI ====================
echo "\n==================== SUITE 2: KONSULTASI ====================\n";

$konsultasiContracts = [
    [
        'name' => "Smoke Test Konsultasi 1 - $timestamp",
        'nomor' => "SMOKE/KONS/CUST/1/$timestamp",
    ],
    [
        'name' => "Smoke Test Konsultasi 2 - $timestamp",
        'nomor' => "SMOKE/KONS/CUST/2/$timestamp",
    ],
    [
        'name' => "Smoke Test Konsultasi 3 - $timestamp",
        'nomor' => "SMOKE/KONS/CUST/3/$timestamp",
    ],
];

foreach ($konsultasiContracts as $idx => $contract) {
    $cNum = $idx + 1;
    echo "\n[Konsultasi Contract #$cNum] Creating Contract: {$contract['name']} ({$contract['nomor']})\n";

    // 1. Create contract via Admin
    $csrf = getCsrfFromCookieJar($cookiesFile);
    $fields = [
        'satker' => 'Perencanaan Prasarana Strategis',
        'ppk_nip' => '199012212018021001',
        'ppk_nama' => 'Nurhidayat Nugroho, S.Ars',
        'nama_paket' => $contract['name'],
        'tahun_anggaran' => '2026 - 2027',
        'jenis_pekerjaan_jasa_konsultansi' => 'perencanaan',
        'masa_pelaksanaan' => 'syc',
        'pagu_anggaran' => '350000000',
        'penyedia' => 'PT Penyedia Konsultasi Smoke',
        'nomor_kontrak' => $contract['nomor'],
        'nilai_kontrak' => '200000000',
        'metode_pemilihan' => 'seleksi',
        'email_responden_1' => 'agung.justik@gmail.com',
        'email_responden_2' => '',
        'csrf_test_name' => $csrf,
    ];
    makeRequest(BASE_URL . '/admin/kontrak/simak/konsultasi/tambah', 'POST', http_build_query($fields));

    // Get the Simak ID from Database
    $dbRow = queryDb("SELECT id FROM trn_kontrak_simak_konsultasi WHERE nomor_kontrak = ?", [$contract['nomor']], 's');
    if (empty($dbRow)) {
        echo "[-] Failed to find created contract in DB!\n";
        exit(1);
    }
    $simakId = (int) $dbRow[0]['id'];
    echo "[+] Created contract successfully. Simak ID: $simakId\n";

    // 2. Generate Share Link via Admin
    echo "[Konsultasi Contract #$cNum] Generating share link...\n";
    $csrf = getCsrfFromCookieJar($cookiesFile);
    $shareFields = [
        'duration' => '1week',
        'csrf_test_name' => $csrf,
    ];
    makeRequest(BASE_URL . "/admin/kontrak/simak/konsultasi/{$simakId}/share", 'POST', http_build_query($shareFields));

    // Get Share Token from Database
    $dbShare = queryDb("SELECT share_token FROM trn_kontrak_simak_konsultasi_share WHERE simak_id = ? AND is_active = 1 LIMIT 1", [$simakId], 'i');
    if (empty($dbShare)) {
        echo "[-] Failed to find share token in DB!\n";
        exit(1);
    }
    $shareToken = $dbShare[0]['share_token'];
    echo "[+] Share link generated. Token: $shareToken\n";

    // 3. Upload responses via Share Link (OTP Bypassed)
    echo "[Konsultasi Contract #$cNum] Uploading responses via public share link...\n";
    $uploadUrl = BASE_URL . "/simak/share/{$shareToken}/upload";

    // A. Upload target Lengkap rows (17 rows)
    foreach ($konsLengkapRows as $row) {
        $csrf = getCsrfFromCookieJar($cookiesFile);
        $uploadFields = [
            'row_no' => $row,
            'upload_method' => 'drive',
            'tipe_dokumen' => 'final',
            'google_drive_link' => 'https://drive.google.com/drive/folders/1zOpJs4jCLi7FYS6SHxYXd3Y7oEA47Yia',
            'csrf_test_name' => $csrf,
        ];
        makeRequest($uploadUrl, 'POST', http_build_query($uploadFields));
    }

    // B. Upload target Belum Sesuai rows (4 rows)
    foreach ($konsBelumSesuaiRows as $row) {
        $csrf = getCsrfFromCookieJar($cookiesFile);
        $uploadFields = [
            'row_no' => $row,
            'upload_method' => 'drive',
            'tipe_dokumen' => 'final',
            'google_drive_link' => 'https://drive.google.com/drive/folders/1zOpJs4jCLi7FYS6SHxYXd3Y7oEA47Yia',
            'csrf_test_name' => $csrf,
        ];
        makeRequest($uploadUrl, 'POST', http_build_query($uploadFields));
    }
    echo "[+] Uploaded all responses via public share link.\n";

    // 4. Perform Verifications via Admin
    echo "[Konsultasi Contract #$cNum] Performing admin verifications...\n";
    $verifyUrl = BASE_URL . "/admin/kontrak/simak/konsultasi/{$simakId}/verifikasi/upload";

    // A. Verify Lengkap rows as Sesuai
    foreach ($konsLengkapRows as $row) {
        $csrf = getCsrfFromCookieJar($cookiesFile);
        $verifyFields = [
            'row_no' => $row,
            'tipe_dokumen' => 'final',
            'kelengkapan_dokumen' => 'ada',
            'verifikasi_ki' => 'sesuai',
            'keterangan' => 'Smoke Test - Lengkap Sesuai',
            'pic' => 'Admin Smoke Test',
            'csrf_test_name' => $csrf,
        ];
        makeRequest($verifyUrl, 'POST', http_build_query($verifyFields));
    }

    // B. Verify Belum Sesuai rows as Tidak Sesuai
    foreach ($konsBelumSesuaiRows as $row) {
        $csrf = getCsrfFromCookieJar($cookiesFile);
        $verifyFields = [
            'row_no' => $row,
            'tipe_dokumen' => 'final',
            'kelengkapan_dokumen' => 'ada',
            'verifikasi_ki' => 'tidak_sesuai',
            'keterangan' => 'Smoke Test - Belum Sesuai Tidak Sesuai',
            'pic' => 'Admin Smoke Test',
            'csrf_test_name' => $csrf,
        ];
        makeRequest($verifyUrl, 'POST', http_build_query($verifyFields));
    }
    echo "[+] Verifications completed.\n";

    // 5. Verify live percentages
    $detailUrl = BASE_URL . "/admin/kontrak/simak/konsultasi/{$simakId}";
    $detailRes = makeRequest($detailUrl, 'GET');
    
    $lengkapPercentage = 0.0;
    $belumSesuaiPercentage = 0.0;
    
    if (preg_match_all('/<div class="kelengkapan-value">([\d,.]+)%<\/div>/i', $detailRes['body'], $matches)) {
        if (isset($matches[1][0])) {
            $lengkapPercentage = (float) str_replace(',', '.', $matches[1][0]);
        }
        if (isset($matches[1][1])) {
            $belumSesuaiPercentage = (float) str_replace(',', '.', $matches[1][1]);
        }
    }
    
    echo "[+] Live percentages for Konsultasi Contract #$cNum: Lengkap = {$lengkapPercentage}%, Belum Sesuai = {$belumSesuaiPercentage}%\n";
    
    // DB Calculation
    $dbData = queryDb("SELECT row_no, kelengkapan_dokumen, verifikasi_ki FROM trn_kontrak_simak_konsultasi_verifikasi WHERE simak_id = ?", [$simakId], 'i');
    $dbLengkap = 0;
    $dbBelumSesuai = 0;
    foreach ($dbData as $row) {
        if ($row['verifikasi_ki'] === 'sesuai') {
            $dbLengkap++;
        } elseif ($row['verifikasi_ki'] === 'belum_sesuai') {
            $dbBelumSesuai++;
        }
    }
    $calcLengkap = round(($dbLengkap / 84) * 100, 2);
    $calcBelumSesuai = round(($dbBelumSesuai / 84) * 100, 2);
    echo "    - DB Calculations: Lengkap = {$calcLengkap}% (Count: $dbLengkap/84), Belum Sesuai = {$calcBelumSesuai}% (Count: $dbBelumSesuai/84)\n";

    if ($calcLengkap > 20 && $calcBelumSesuai > 4) {
        echo "[+] [SUCCESS] Targets met for Konsultasi Contract #$cNum\n";
    } else {
        echo "[-] [FAIL] Targets NOT met for Konsultasi Contract #$cNum\n";
        exit(1);
    }
}

echo "\n========================================================\n";
echo "           ALL SMOKE TESTS PASSED SUCCESSFULLY!          \n";
echo "========================================================\n";

if (file_exists($cookiesFile)) {
    unlink($cookiesFile);
}
