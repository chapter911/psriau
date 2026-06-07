<?php
/**
 * Custom Smoke Test Runner for Konstruksi & Konsultasi contracts.
 * Creates 3 new Konstruksi and 3 new Konsultasi contracts, then verifies them
 * to hit the required progress thresholds:
 * - lengkap > 10%
 * - Belum Sesuai > 4%
 */

define('BASE_URL', 'https://satkerpps-riau.online');
define('TIMEOUT', 30);

$cookiesFile = __DIR__ . '/../writable/custom_smoke_test_cookies.txt';
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
echo "         SIMAK SMOKE TEST CUSTOM RUNNER                 \n";
echo "========================================================\n\n";

// 1. Login as Admin
echo "Logging in as Admin...\n";
$loginUrl = BASE_URL . '/masuk';
$res = makeRequest($loginUrl, 'GET');
$csrf = getCsrfFromCookieJar($cookiesFile);

$loginFields = [
    'username' => '199011092025061005',
    'password' => '123456',
    'csrf_test_name' => $csrf,
];
$res = makeRequest($loginUrl, 'POST', http_build_query($loginFields));
$csrf = getCsrfFromCookieJar($cookiesFile);

if (strpos($res['body'], 'Masuk Admin') !== false || strpos($res['body'], 'id="username"') !== false) {
    echo "[-] Login failed! Check credentials.\n";
    exit(1);
}
echo "[+] Logged in successfully. CSRF Token: " . substr($csrf, 0, 10) . "...\n\n";


// Define Leaf Rows for Konstruksi & Konsultasi
// Konstruksi (157 leaf rows):
// lengkap > 10% (at least 17 rows): 28, 30, 31, 32, 33, 34, 36, 37, 38, 39, 40, 42, 43, 45, 47, 48, 49
// Belum Sesuai > 4% (at least 7 rows): 50, 53, 55, 57, 58, 59, 61
$konLengkapRows = [28, 30, 31, 32, 33, 34, 36, 37, 38, 39, 40, 42, 43, 45, 47, 48, 49];
$konBelumSesuaiRows = [50, 53, 55, 57, 58, 59, 61];

// Konsultasi (84 leaf rows):
// lengkap > 10% (at least 10 rows): 28, 30, 31, 32, 33, 34, 35, 36, 38, 39
// Belum Sesuai > 4% (at least 4 rows): 40, 43, 44, 46
$konsLengkapRows = [28, 32, 33, 34, 35, 36, 47, 49, 50, 51];
$konsBelumSesuaiRows = [40, 43, 44, 46];



// ==================== PART 1: KONSTRUKSI ====================
echo "==================== PART 1: KONSTRUKSI ====================\n";

$timestamp = date('YmdHis');
$konstruksiContracts = [
    [
        'name' => "Smoke Test Konstruksi A (Lengkap > 10%, Belum Sesuai > 4%) - $timestamp",
        'nomor' => "SMOKE/KON/CUSTOM/A/$timestamp",
    ],
    [
        'name' => "Smoke Test Konstruksi B (Lengkap > 10%, Belum Sesuai > 4%) - $timestamp",
        'nomor' => "SMOKE/KON/CUSTOM/B/$timestamp",
    ],
    [
        'name' => "Smoke Test Konstruksi C (Lengkap > 10%, Belum Sesuai > 4%) - $timestamp",
    ],
];

// Add nomor to C using a slight delay to ensure uniqueness if timestamp matches
$konstruksiContracts[2]['nomor'] = "SMOKE/KON/CUSTOM/C/" . ($timestamp + 1);

foreach ($konstruksiContracts as $i => $contract) {
    $idx = $i + 1;
    echo "\n[Konstruksi #$idx] Creating: {$contract['name']} ({$contract['nomor']})\n";
    
    // Add Contract
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
    
    $res = makeRequest(BASE_URL . '/admin/kontrak/simak/konstruksi/tambah', 'POST', http_build_query($fields));
    
    // Fetch contract ID from DB
    $dbRow = queryDb("SELECT id FROM trn_kontrak_simak WHERE nomor_kontrak = ?", [$contract['nomor']], 's');
    if (empty($dbRow)) {
        echo "[-] Failed to find created contract in DB!\n";
        exit(1);
    }
    $simakId = (int) $dbRow[0]['id'];
    echo "[+] Created contract successfully. Simak ID: $simakId\n";
    
    // Perform Verification
    echo "[Konstruksi #$idx] Performing verification...\n";
    $csrf = getCsrfFromCookieJar($cookiesFile);
    
    $verifyFields = [
        'csrf_test_name' => $csrf,
    ];
    
    // Set Lengkap rows (kelengkapan_dokumen = tidak, verifikasi_ki = sesuai)
    foreach ($konLengkapRows as $row) {
        $verifyFields["kelengkapan_dokumen[$row]"] = 'tidak';
        $verifyFields["verifikasi_ki[$row]"] = 'sesuai';
        $verifyFields["keterangan[$row]"] = 'Smoke Test - Lengkap (>10%)';
        $verifyFields["pic[$row]"] = 'Smoke Test';
    }
    
    // Set Belum Sesuai rows (verifikasi_ki = tidak_sesuai)
    foreach ($konBelumSesuaiRows as $row) {
        $verifyFields["kelengkapan_dokumen[$row]"] = 'ada';
        $verifyFields["verifikasi_ki[$row]"] = 'tidak_sesuai';
        $verifyFields["keterangan[$row]"] = 'Smoke Test - Belum Sesuai (>4%)';
        $verifyFields["pic[$row]"] = 'Smoke Test';
    }
    
    $verifyUrl = BASE_URL . "/admin/kontrak/simak/konstruksi/{$simakId}/verifikasi";
    $res = makeRequest($verifyUrl, 'POST', http_build_query($verifyFields));
    
    // Check if verifications are in DB
    $dbVerify = queryDb("SELECT count(*) as count FROM trn_kontrak_simak_verifikasi WHERE simak_id = ?", [$simakId], 'i');
    $insertedCount = (int) $dbVerify[0]['count'];
    $expectedCount = count($konLengkapRows) + count($konBelumSesuaiRows);
    if ($insertedCount < $expectedCount) {
        echo "[-] Database verification count mismatch. Inserted: $insertedCount, Expected: $expectedCount\n";
        exit(1);
    }
    
    // Verify percentages on the index page or via curl detail page
    $detailUrl = BASE_URL . "/admin/kontrak/simak/konstruksi/{$simakId}";
    $detailRes = makeRequest($detailUrl, 'GET');
    
    // Check percentages using regex or string search in html
    // Let's inspect the HTML response for the percentages
    $lengkapPercentage = 0.0;
    $belumSesuaiPercentage = 0.0;
    
    if (preg_match('/Lengkap\s+([\d,.]+)\s*%/i', $detailRes['body'], $matches)) {
        $lengkapPercentage = (float) str_replace(',', '.', $matches[1]);
    }
    if (preg_match('/Belum Sesuai\s+([\d,.]+)\s*%/i', $detailRes['body'], $matches)) {
        $belumSesuaiPercentage = (float) str_replace(',', '.', $matches[1]);
    }
    
    // Alternatively look for HSL or template items kelengkapan-value classes
    // e.g. <div class="kelengkapan-value">10,83%</div>
    if (preg_match_all('/<div class="kelengkapan-value">([\d,.]+)%<\/div>/i', $detailRes['body'], $matches)) {
        if (isset($matches[1][0])) {
            $lengkapPercentage = (float) str_replace(',', '.', $matches[1][0]);
        }
        if (isset($matches[1][1])) {
            $belumSesuaiPercentage = (float) str_replace(',', '.', $matches[1][1]);
        }
    }
    
    echo "[+] Verification saved. Live Percentages: Lengkap = {$lengkapPercentage}%, Belum Sesuai = {$belumSesuaiPercentage}%\n";
    
    // Double check DB calculations using formula to verify
    $dbData = queryDb("SELECT row_no, kelengkapan_dokumen, verifikasi_ki FROM trn_kontrak_simak_verifikasi WHERE simak_id = ?", [$simakId], 'i');
    $dbLengkap = 0;
    $dbBelumSesuai = 0;
    foreach ($dbData as $row) {
        if ($row['kelengkapan_dokumen'] === 'tidak' && $row['verifikasi_ki'] === 'sesuai') {
            $dbLengkap++;
        } elseif ($row['verifikasi_ki'] === 'tidak_sesuai') {
            $dbBelumSesuai++;
        }
    }
    $calcLengkap = round(($dbLengkap / 157) * 100, 2);
    $calcBelumSesuai = round(($dbBelumSesuai / 157) * 100, 2);
    echo "    - DB Calc: Lengkap = {$calcLengkap}% (Count: $dbLengkap/157), Belum Sesuai = {$calcBelumSesuai}% (Count: $dbBelumSesuai/157)\n";
    
    if ($calcLengkap > 10 && $calcBelumSesuai > 4) {
        echo "[+] Contract verified successfully! Targets met!\n";
    } else {
        echo "[-] Error: Targets NOT met!\n";
        exit(1);
    }
}


// ==================== PART 2: KONSULTASI ====================
echo "\n==================== PART 2: KONSULTASI ====================\n";

$konsultasiContracts = [
    [
        'name' => "Smoke Test Konsultasi A (Lengkap > 10%, Belum Sesuai > 4%) - $timestamp",
        'nomor' => "SMOKE/KONS/CUSTOM/A/$timestamp",
    ],
    [
        'name' => "Smoke Test Konsultasi B (Lengkap > 10%, Belum Sesuai > 4%) - $timestamp",
        'nomor' => "SMOKE/KONS/CUSTOM/B/$timestamp",
    ],
    [
        'name' => "Smoke Test Konsultasi C (Lengkap > 10%, Belum Sesuai > 4%) - $timestamp",
    ],
];
$konsultasiContracts[2]['nomor'] = "SMOKE/KONS/CUSTOM/C/" . ($timestamp + 1);

foreach ($konsultasiContracts as $i => $contract) {
    $idx = $i + 1;
    echo "\n[Konsultasi #$idx] Creating: {$contract['name']} ({$contract['nomor']})\n";
    
    // Add Contract
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
    
    $res = makeRequest(BASE_URL . '/admin/kontrak/simak/konsultasi/tambah', 'POST', http_build_query($fields));
    
    // Fetch contract ID from DB
    $dbRow = queryDb("SELECT id FROM trn_kontrak_simak_konsultasi WHERE nomor_kontrak = ?", [$contract['nomor']], 's');
    if (empty($dbRow)) {
        echo "[-] Failed to find created contract in DB!\n";
        exit(1);
    }
    $simakId = (int) $dbRow[0]['id'];
    echo "[+] Created contract successfully. Simak ID: $simakId\n";
    
    // Perform Verification
    echo "[Konsultasi #$idx] Performing verification...\n";
    $csrf = getCsrfFromCookieJar($cookiesFile);
    
    $verifyFields = [
        'csrf_test_name' => $csrf,
    ];
    
    // Set Lengkap rows (kelengkapan_dokumen = tidak, verifikasi_ki = sesuai)
    foreach ($konsLengkapRows as $row) {
        $verifyFields["kelengkapan_dokumen[$row]"] = 'tidak';
        $verifyFields["verifikasi_ki[$row]"] = 'sesuai';
        $verifyFields["keterangan[$row]"] = 'Smoke Test - Lengkap (>10%)';
        $verifyFields["pic[$row]"] = 'Smoke Test';
    }
    
    // Set Belum Sesuai rows (verifikasi_ki = tidak_sesuai)
    foreach ($konsBelumSesuaiRows as $row) {
        $verifyFields["kelengkapan_dokumen[$row]"] = 'ada';
        $verifyFields["verifikasi_ki[$row]"] = 'tidak_sesuai';
        $verifyFields["keterangan[$row]"] = 'Smoke Test - Belum Sesuai (>4%)';
        $verifyFields["pic[$row]"] = 'Smoke Test';
    }
    
    $verifyUrl = BASE_URL . "/admin/kontrak/simak/konsultasi/{$simakId}/verifikasi";
    $res = makeRequest($verifyUrl, 'POST', http_build_query($verifyFields));
    
    // Check if verifications are in DB
    $dbVerify = queryDb("SELECT count(*) as count FROM trn_kontrak_simak_konsultasi_verifikasi WHERE simak_id = ?", [$simakId], 'i');
    $insertedCount = (int) $dbVerify[0]['count'];
    $expectedCount = count($konsLengkapRows) + count($konsBelumSesuaiRows);
    if ($insertedCount < $expectedCount) {
        echo "[-] Database verification count mismatch. Inserted: $insertedCount, Expected: $expectedCount\n";
        exit(1);
    }
    
    // Verify percentages on the index page or via curl detail page
    $detailUrl = BASE_URL . "/admin/kontrak/simak/konsultasi/{$simakId}";
    $detailRes = makeRequest($detailUrl, 'GET');
    
    // Check percentages using regex or string search in html
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
    
    echo "[+] Verification saved. Live Percentages: Lengkap = {$lengkapPercentage}%, Belum Sesuai = {$belumSesuaiPercentage}%\n";
    
    // Double check DB calculations using formula to verify
    $dbData = queryDb("SELECT row_no, kelengkapan_dokumen, verifikasi_ki FROM trn_kontrak_simak_konsultasi_verifikasi WHERE simak_id = ?", [$simakId], 'i');
    $dbLengkap = 0;
    $dbBelumSesuai = 0;
    foreach ($dbData as $row) {
        if ($row['kelengkapan_dokumen'] === 'tidak' && $row['verifikasi_ki'] === 'sesuai') {
            $dbLengkap++;
        } elseif ($row['verifikasi_ki'] === 'tidak_sesuai') {
            $dbBelumSesuai++;
        }
    }
    $calcLengkap = round(($dbLengkap / 84) * 100, 2);
    $calcBelumSesuai = round(($dbBelumSesuai / 84) * 100, 2);
    echo "    - DB Calc: Lengkap = {$calcLengkap}% (Count: $dbLengkap/84), Belum Sesuai = {$calcBelumSesuai}% (Count: $dbBelumSesuai/84)\n";
    
    if ($calcLengkap > 10 && $calcBelumSesuai > 4) {
        echo "[+] Contract verified successfully! Targets met!\n";
    } else {
        echo "[-] Error: Targets NOT met!\n";
        exit(1);
    }
}

echo "\n========================================================\n";
echo "              SMOKE TEST COMPLETED SUCCESSFULLY           \n";
echo "========================================================\n";
