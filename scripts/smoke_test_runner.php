<?php
/**
 * SIMAK Smoke Test Runner - Comprehensive End-to-End Test Suite
 *
 * Runs 10 test scenarios (5 Konstruksi, 5 Konsultasi) and compiles an HTML report.
 * Uses local DB access to verify states and get IDs/tokens instantly.
 * Performs real curl HTTP requests to verify the routing, validation, and session logic.
 */

// Configuration
define('BASE_URL', 'https://satkerpps-riau.online');
define('TIMEOUT', 30);

$cookiesFile = __DIR__ . '/../writable/smoke_test_cookies.txt';
if (file_exists($cookiesFile)) {
    unlink($cookiesFile);
}

// Ensure dummy files exist
$dummyDraft = __DIR__ . '/../writable/dummy_draft.xlsx';
$dummyFinal = __DIR__ . '/../writable/dummy_final.pdf';

// Helper for database queries
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

// Helper to make curl request
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

// Main execution array
$report = [
    'timestamp' => date('Y-m-d H:i:s'),
    'email_respondent' => 'agung.justik@gmail.com',
    'status_overall' => 'PENDING',
    'total_items' => 10,
    'passed' => 0,
    'failed' => 0,
    'items' => [],
];

echo "=== STARTING SIMAK SMOKE TEST ===\n";
echo "Date/Time: " . $report['timestamp'] . "\n";
echo "Target Site: " . BASE_URL . "\n";
echo "Respondent Email: " . $report['email_respondent'] . "\n\n";

// 1. Authenticate Admin
echo "1. Logging in as Administrator...\n";
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
    echo "  [FAIL] Login failed!\n";
    exit(1);
}
echo "  [SUCCESS] Logged in successfully. CSRF Token: " . substr($csrf, 0, 10) . "...\n\n";

// 2. Define Scenarios
// Minimum 10 items total: 5 Konstruksi (KON), 5 Konsultasi (KONS)
$konstruksiItems = [
    [
        'name' => 'Smoke Test Konstruksi 1 (Full Flow)',
        'nomor' => 'SMOKE/KON/' . date('YmdHis') . '/1',
        'scenarios' => [
            // upload draft file
            'upload_draft_ok' => ['row' => 53, 'type' => 'draft', 'method' => 'file', 'expect' => 'success'],
            // Try to upload final before draft is verified Sesuai (must fail)
            'upload_final_fail' => ['row' => 53, 'type' => 'final', 'method' => 'file', 'expect' => 'fail'],
            // Admin verifikasi draft as Sesuai
            'verify_draft_sesuai' => ['row' => 53, 'status' => 'sesuai', 'kel' => 'ada', 'expect' => 'success'],
            // Upload final file after draft verified Sesuai (must succeed)
            'upload_final_ok' => ['row' => 53, 'type' => 'final', 'method' => 'file', 'expect' => 'success'],
            // Admin verifikasi final as Sesuai
            'verify_final_sesuai' => ['row' => 53, 'status' => 'sesuai', 'kel' => 'ada', 'expect' => 'success'],
            // Upload final to non-draft row 182
            'upload_row182_ok' => ['row' => 182, 'type' => 'final', 'method' => 'file', 'expect' => 'success'],
            'verify_row182_sesuai' => ['row' => 182, 'status' => 'sesuai', 'kel' => 'ada', 'expect' => 'success'],
        ]
    ],
    [
        'name' => 'Smoke Test Konstruksi 2 (Drive Link & Verify Sesuai/Tidak Sesuai)',
        'nomor' => 'SMOKE/KON/' . date('YmdHis') . '/2',
        'scenarios' => [
            'upload_draft_ok' => ['row' => 53, 'type' => 'draft', 'method' => 'file', 'expect' => 'success'],
            'verify_draft_tidak_sesuai' => ['row' => 53, 'status' => 'tidak_sesuai', 'kel' => 'ada', 'expect' => 'success'],
            'upload_row182_link' => ['row' => 182, 'type' => 'final', 'method' => 'drive', 'expect' => 'success'],
            'verify_row182_sesuai' => ['row' => 182, 'status' => 'sesuai', 'kel' => 'ada', 'expect' => 'success'],
        ]
    ],
    [
        'name' => 'Smoke Test Konstruksi 3 (Missing Document & Verify)',
        'nomor' => 'SMOKE/KON/' . date('YmdHis') . '/3',
        'scenarios' => [
            'upload_draft_ok' => ['row' => 53, 'type' => 'draft', 'method' => 'file', 'expect' => 'success'],
            'verify_draft_sesuai' => ['row' => 53, 'status' => 'sesuai', 'kel' => 'ada', 'expect' => 'success'],
            'upload_row182_none' => ['row' => 182, 'type' => 'final', 'method' => 'none', 'expect' => 'success'],
            'verify_row182_tidak_sesuai' => ['row' => 182, 'status' => 'tidak_sesuai', 'kel' => 'tidak', 'expect' => 'success'],
        ]
    ],
    [
        'name' => 'Smoke Test Konstruksi 4 (Failures verification)',
        'nomor' => 'SMOKE/KON/' . date('YmdHis') . '/4',
        'scenarios' => [
            'upload_draft_ok' => ['row' => 53, 'type' => 'draft', 'method' => 'file', 'expect' => 'success'],
            'verify_draft_tidak_sesuai' => ['row' => 53, 'status' => 'tidak_sesuai', 'kel' => 'ada', 'expect' => 'success'],
            'upload_row182_ok' => ['row' => 182, 'type' => 'final', 'method' => 'file', 'expect' => 'success'],
            'verify_row182_tidak_sesuai' => ['row' => 182, 'status' => 'tidak_sesuai', 'kel' => 'ada', 'expect' => 'success'],
        ]
    ],
    [
        'name' => 'Smoke Test Konstruksi 5 (Mixed Scenario)',
        'nomor' => 'SMOKE/KON/' . date('YmdHis') . '/5',
        'scenarios' => [
            'upload_draft_none' => ['row' => 53, 'type' => 'draft', 'method' => 'none', 'expect' => 'success'],
            'verify_draft_tidak_sesuai' => ['row' => 53, 'status' => 'tidak_sesuai', 'kel' => 'tidak', 'expect' => 'success'],
            'upload_row182_link' => ['row' => 182, 'type' => 'final', 'method' => 'drive', 'expect' => 'success'],
            'verify_row182_sesuai' => ['row' => 182, 'status' => 'sesuai', 'kel' => 'ada', 'expect' => 'success'],
        ]
    ],
];

$konsultasiItems = [
    [
        'name' => 'Smoke Test Konsultasi 1 (Full Flow)',
        'nomor' => 'SMOKE/KONS/' . date('YmdHis') . '/1',
        'scenarios' => [
            'upload_draft_ok' => ['row' => 38, 'type' => 'draft', 'method' => 'file', 'expect' => 'success'],
            'upload_final_fail' => ['row' => 38, 'type' => 'final', 'method' => 'file', 'expect' => 'fail'],
            'verify_draft_sesuai' => ['row' => 38, 'status' => 'sesuai', 'kel' => 'ada', 'expect' => 'success'],
            'upload_final_ok' => ['row' => 38, 'type' => 'final', 'method' => 'file', 'expect' => 'success'],
            'verify_final_sesuai' => ['row' => 38, 'status' => 'sesuai', 'kel' => 'ada', 'expect' => 'success'],
            'upload_row40_ok' => ['row' => 40, 'type' => 'final', 'method' => 'file', 'expect' => 'success'],
            'verify_row40_sesuai' => ['row' => 40, 'status' => 'sesuai', 'kel' => 'ada', 'expect' => 'success'],
        ]
    ],
    [
        'name' => 'Smoke Test Konsultasi 2 (Drive Link & Verify Sesuai/Tidak Sesuai)',
        'nomor' => 'SMOKE/KONS/' . date('YmdHis') . '/2',
        'scenarios' => [
            'upload_draft_ok' => ['row' => 38, 'type' => 'draft', 'method' => 'file', 'expect' => 'success'],
            'verify_draft_tidak_sesuai' => ['row' => 38, 'status' => 'tidak_sesuai', 'kel' => 'ada', 'expect' => 'success'],
            'upload_row40_link' => ['row' => 40, 'type' => 'final', 'method' => 'drive', 'expect' => 'success'],
            'verify_row40_sesuai' => ['row' => 40, 'status' => 'sesuai', 'kel' => 'ada', 'expect' => 'success'],
        ]
    ],
    [
        'name' => 'Smoke Test Konsultasi 3 (Missing Document & Verify)',
        'nomor' => 'SMOKE/KONS/' . date('YmdHis') . '/3',
        'scenarios' => [
            'upload_draft_ok' => ['row' => 38, 'type' => 'draft', 'method' => 'file', 'expect' => 'success'],
            'verify_draft_sesuai' => ['row' => 38, 'status' => 'sesuai', 'kel' => 'ada', 'expect' => 'success'],
            'upload_row40_none' => ['row' => 40, 'type' => 'final', 'method' => 'none', 'expect' => 'success'],
            'verify_row40_tidak_sesuai' => ['row' => 40, 'status' => 'tidak_sesuai', 'kel' => 'tidak', 'expect' => 'success'],
        ]
    ],
    [
        'name' => 'Smoke Test Konsultasi 4 (Failures verification)',
        'nomor' => 'SMOKE/KONS/' . date('YmdHis') . '/4',
        'scenarios' => [
            'upload_draft_ok' => ['row' => 38, 'type' => 'draft', 'method' => 'file', 'expect' => 'success'],
            'verify_draft_tidak_sesuai' => ['row' => 38, 'status' => 'tidak_sesuai', 'kel' => 'ada', 'expect' => 'success'],
            'upload_row40_ok' => ['row' => 40, 'type' => 'final', 'method' => 'file', 'expect' => 'success'],
            'verify_row40_tidak_sesuai' => ['row' => 40, 'status' => 'tidak_sesuai', 'kel' => 'ada', 'expect' => 'success'],
        ]
    ],
    [
        'name' => 'Smoke Test Konsultasi 5 (Mixed Scenario)',
        'nomor' => 'SMOKE/KONS/' . date('YmdHis') . '/5',
        'scenarios' => [
            'upload_draft_none' => ['row' => 38, 'type' => 'draft', 'method' => 'none', 'expect' => 'success'],
            'verify_draft_tidak_sesuai' => ['row' => 38, 'status' => 'tidak_sesuai', 'kel' => 'tidak', 'expect' => 'success'],
            'upload_row40_link' => ['row' => 40, 'type' => 'final', 'method' => 'drive', 'expect' => 'success'],
            'verify_row40_sesuai' => ['row' => 40, 'status' => 'sesuai', 'kel' => 'ada', 'expect' => 'success'],
        ]
    ],
];

// Helper to update verification state payload
$verificationStates = []; // format: [$id => [$rowNo => ['kel' => ..., 'ver' => ..., 'ket' => ..., 'pic' => ...]]]

// Execute Konstruksi
echo "\n=== RUNNING KONSTRUKSI SMOKE TESTS ===\n";
foreach ($konstruksiItems as $i => $itemInfo) {
    $itemIndex = $i + 1;
    echo "\n------------------------------------------------------------\n";
    echo "Item #{$itemIndex}: {$itemInfo['name']}\n";
    echo "Nomor Kontrak: {$itemInfo['nomor']}\n";
    echo "------------------------------------------------------------\n";

    $itemReport = [
        'index' => $itemIndex,
        'category' => 'Konstruksi',
        'name' => $itemInfo['name'],
        'nomor_kontrak' => $itemInfo['nomor'],
        'id' => null,
        'share_token' => null,
        'share_url' => null,
        'status' => 'PASS',
        'steps' => [],
    ];

    try {
        // Step 1: Tambah Item Baru
        echo "  [STEP] Creating new item...\n";
        $csrf = getCsrfFromCookieJar($cookiesFile);
        $fields = [
            'satker' => 'Perencanaan Prasarana Strategis',
            'ppk_nip' => '199012212018021001',
            'ppk_nama' => 'Nurhidayat Nugroho, S.Ars',
            'nama_paket' => $itemInfo['name'],
            'tahun_anggaran' => '2026 - 2027',
            'penyedia' => 'CV Penyedia Konstruksi Smoke',
            'penyedia_jasa_konsultansi' => 'CV Penyedia Konstruksi Smoke',
            'nomor_kontrak' => $itemInfo['nomor'],
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
        
        // Fetch ID from database
        $dbRow = queryDb("SELECT id FROM trn_kontrak_simak WHERE nomor_kontrak = ?", [$itemInfo['nomor']], 's');
        if (empty($dbRow)) {
            throw new Exception("Failed to find created item in database.");
        }
        $simakId = (int) $dbRow[0]['id'];
        $itemReport['id'] = $simakId;
        echo "    - Item created with ID: {$simakId}\n";
        $itemReport['steps'][] = ['name' => 'Create Item', 'status' => 'PASS', 'message' => "Created item ID {$simakId}"];

        // Step 2: Generate Share Link
        echo "  [STEP] Generating share link...\n";
        $csrf = getCsrfFromCookieJar($cookiesFile);
        $shareFields = [
            'duration' => '1week',
            'csrf_test_name' => $csrf,
        ];
        $res = makeRequest(BASE_URL . "/admin/kontrak/simak/konstruksi/{$simakId}/share", 'POST', http_build_query($shareFields));
        
        $dbShare = queryDb("SELECT share_token FROM trn_kontrak_simak_share WHERE simak_id = ?", [$simakId], 'i');
        if (empty($dbShare)) {
            throw new Exception("Failed to find generated share token in database.");
        }
        $shareToken = $dbShare[0]['share_token'];
        $itemReport['share_token'] = $shareToken;
        $itemReport['share_url'] = BASE_URL . "/simak/share/" . $shareToken;
        echo "    - Share token: {$shareToken}\n";
        echo "    - Share URL: " . $itemReport['share_url'] . "\n";
        $itemReport['steps'][] = ['name' => 'Generate Share Link', 'status' => 'PASS', 'message' => "Token: " . substr($shareToken, 0, 10) . "..."];

        // Step 3: Run upload and verify scenarios
        $verificationStates[$simakId] = [];
        
        foreach ($itemInfo['scenarios'] as $stepCode => $stepConf) {
            $rowNo = $stepConf['row'];
            $csrf = getCsrfFromCookieJar($cookiesFile);
            
            if (isset($stepConf['type'])) {
                // Upload scenario
                $type = $stepConf['type']; // 'draft' or 'final'
                $method = $stepConf['method']; // 'file', 'drive', or 'none'
                $expect = $stepConf['expect'];
                
                echo "  [STEP] Upload public (Row {$rowNo}, Type: {$type}, Method: {$method}, Expect: {$expect})...\n";
                
                $uploadFields = [
                    'row_no' => $rowNo,
                    'upload_method' => $method,
                    'tipe_dokumen' => $type,
                    'csrf_test_name' => $csrf,
                ];
                
                if ($method === 'file') {
                    $localFile = ($type === 'draft') ? $dummyDraft : $dummyFinal;
                    $mime = ($type === 'draft') ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' : 'application/pdf';
                    $uploadFields['dokumen_file'] = new CURLFile($localFile, $mime, basename($localFile));
                } elseif ($method === 'drive') {
                    $uploadFields['google_drive_link'] = 'https://drive.google.com/drive/folders/1zOpJs4jCLi7FYS6SHxYXd3Y7oEA47Yia';
                } elseif ($method === 'none') {
                    $uploadFields['keterangan'] = 'Dokumen memang tidak ada dari pihak PPK';
                }
                
                $uploadUrl = BASE_URL . "/simak/share/{$shareToken}/upload";
                
                // Form boundary post for files
                if ($method === 'file') {
                    $res = makeRequest($uploadUrl, 'POST', $uploadFields);
                } else {
                    $res = makeRequest($uploadUrl, 'POST', http_build_query($uploadFields));
                }
                
                $successMsg = "berhasil";
                $isRedirect = ($res['code'] === 302 || $res['code'] === 303);
                
                if ($expect === 'success') {
                    // Check if redirect contains success message
                    $containsSuccess = (strpos($res['body'], $successMsg) !== false || strpos($res['headers'], $successMsg) !== false || $isRedirect);
                    // Double check database record
                    $dbDocs = queryDb("SELECT id FROM trn_kontrak_simak_verifikasi_dokumen WHERE simak_id = ? AND row_no = ? AND tipe_dokumen = ? ORDER BY id DESC LIMIT 1", [$simakId, $rowNo, $type], 'iis');
                    
                    if (!empty($dbDocs)) {
                        echo "    - [PASS] Upload successful (verified in DB)\n";
                        $itemReport['steps'][] = ['name' => "Upload {$type} Row {$rowNo}", 'status' => 'PASS', 'message' => "Uploaded successfully using {$method}"];
                    } else {
                        // Extract alert messages from HTML body
                        $errorDetail = '';
                        if (preg_match('/<div class="alert alert-danger"[^>]*>(.*?)<\/div>/s', $res['body'], $m)) {
                            $errorDetail = trim(strip_tags($m[1]));
                        } elseif (preg_match('/<div class="invalid-feedback"[^>]*>(.*?)<\/div>/s', $res['body'], $m)) {
                            $errorDetail = trim(strip_tags($m[1]));
                        }
                        
                        echo "    - [FAIL] Response code: {$res['code']}\n";
                        echo "    - [FAIL] Redirected to: {$res['url']}\n";
                        if ($errorDetail) {
                            echo "    - [FAIL] Alert message: {$errorDetail}\n";
                        }
                        // Save response body for inspection
                        $debugFile = __DIR__ . '/../writable/failed_upload_' . $stepCode . '.html';
                        file_put_contents($debugFile, $res['body']);
                        echo "    - [FAIL] Saved response body to: " . basename($debugFile) . "\n";
                        
                        throw new Exception("Upload scenario {$stepCode} failed (verified in DB) - " . ($errorDetail ?: "check response body"));
                    }
                } else {
                    // Expected failure (e.g. upload final before draft verified)
                    $dbDocs = queryDb("SELECT id FROM trn_kontrak_simak_verifikasi_dokumen WHERE simak_id = ? AND row_no = ? AND tipe_dokumen = ? ORDER BY id DESC LIMIT 1", [$simakId, $rowNo, $type], 'iis');
                    if (empty($dbDocs)) {
                        echo "    - [PASS] Upload failed as expected\n";
                        $itemReport['steps'][] = ['name' => "Blocked Final Row {$rowNo}", 'status' => 'PASS', 'message' => "Correctly blocked upload: Draft not verified Sesuai"];
                    } else {
                        throw new Exception("Constraint failed: final upload succeeded when it should have failed.");
                    }
                }
            } elseif (isset($stepConf['status'])) {
                // Admin Verification scenario
                $status = $stepConf['status']; // 'sesuai' or 'tidak_sesuai'
                $kel = $stepConf['kel']; // 'ada' or 'tidak'
                
                echo "  [STEP] Admin verification (Row {$rowNo}, Status: {$status}, Kelengkapan: {$kel})...\n";
                
                // Add/update verification state
                $verificationStates[$simakId][$rowNo] = [
                    'kel' => $kel,
                    'ver' => $status,
                    'ket' => 'Verified by Automated Smoke Test',
                    'pic' => 'Admin Smoke Test'
                ];
                
                // Build complete POST payload for verification
                $verifyFields = [
                    'csrf_test_name' => $csrf
                ];
                foreach ($verificationStates[$simakId] as $r => $st) {
                    $verifyFields["kelengkapan_dokumen[$r]"] = $st['kel'];
                    $verifyFields["verifikasi_ki[$r]"] = $st['ver'];
                    $verifyFields["keterangan[$r]"] = $st['ket'];
                    $verifyFields["pic[$r]"] = $st['pic'];
                }
                
                $verifyUrl = BASE_URL . "/admin/kontrak/simak/konstruksi/{$simakId}/verifikasi";
                $res = makeRequest($verifyUrl, 'POST', http_build_query($verifyFields));
                
                // Query database to verify
                $dbVerify = queryDb("SELECT verifikasi_ki FROM trn_kontrak_simak_verifikasi WHERE simak_id = ? AND row_no = ?", [$simakId, $rowNo], 'ii');
                if (!empty($dbVerify) && $dbVerify[0]['verifikasi_ki'] === $status) {
                    echo "    - [PASS] Verification saved as {$status}\n";
                    $itemReport['steps'][] = ['name' => "Verify Row {$rowNo}", 'status' => 'PASS', 'message' => "Marked {$status} successfully"];
                } else {
                    throw new Exception("Verification failed to save in database.");
                }
            }
        }
    } catch (Exception $e) {
        echo "  [ERROR] " . $e->getMessage() . "\n";
        $itemReport['status'] = 'FAIL';
        $itemReport['steps'][] = ['name' => 'Error', 'status' => 'FAIL', 'message' => $e->getMessage()];
    }

    if ($itemReport['status'] === 'PASS') {
        $report['passed']++;
    } else {
        $report['failed']++;
    }
    $report['items'][] = $itemReport;
}

// Execute Konsultasi
echo "\n=== RUNNING KONSULTASI SMOKE TESTS ===\n";
foreach ($konsultasiItems as $i => $itemInfo) {
    $itemIndex = $i + 6;
    echo "\n------------------------------------------------------------\n";
    echo "Item #{$itemIndex}: {$itemInfo['name']}\n";
    echo "Nomor Kontrak: {$itemInfo['nomor']}\n";
    echo "------------------------------------------------------------\n";

    $itemReport = [
        'index' => $itemIndex,
        'category' => 'Konsultasi',
        'name' => $itemInfo['name'],
        'nomor_kontrak' => $itemInfo['nomor'],
        'id' => null,
        'share_token' => null,
        'share_url' => null,
        'status' => 'PASS',
        'steps' => [],
    ];

    try {
        // Step 1: Tambah Item Baru
        echo "  [STEP] Creating new item...\n";
        $csrf = getCsrfFromCookieJar($cookiesFile);
        $fields = [
            'satker' => 'Perencanaan Prasarana Strategis',
            'ppk_nip' => '199012212018021001',
            'ppk_nama' => 'Nurhidayat Nugroho, S.Ars',
            'nama_paket' => $itemInfo['name'],
            'tahun_anggaran' => '2026 - 2027',
            'jenis_pekerjaan_jasa_konsultansi' => 'perencanaan',
            'masa_pelaksanaan' => 'syc',
            'pagu_anggaran' => '350000000',
            'penyedia' => 'PT Penyedia Konsultasi Smoke',
            'nomor_kontrak' => $itemInfo['nomor'],
            'nilai_kontrak' => '200000000',
            'metode_pemilihan' => 'seleksi',
            'email_responden_1' => 'agung.justik@gmail.com',
            'email_responden_2' => '',
            'csrf_test_name' => $csrf,
        ];
        
        $res = makeRequest(BASE_URL . '/admin/kontrak/simak/konsultasi/tambah', 'POST', http_build_query($fields));
        
        // Fetch ID from database
        $dbRow = queryDb("SELECT id FROM trn_kontrak_simak_konsultasi WHERE nomor_kontrak = ?", [$itemInfo['nomor']], 's');
        if (empty($dbRow)) {
            throw new Exception("Failed to find created item in database.");
        }
        $simakId = (int) $dbRow[0]['id'];
        $itemReport['id'] = $simakId;
        echo "    - Item created with ID: {$simakId}\n";
        $itemReport['steps'][] = ['name' => 'Create Item', 'status' => 'PASS', 'message' => "Created item ID {$simakId}"];

        // Step 2: Generate Share Link
        echo "  [STEP] Generating share link...\n";
        $csrf = getCsrfFromCookieJar($cookiesFile);
        $shareFields = [
            'duration' => '1week',
            'csrf_test_name' => $csrf,
        ];
        $res = makeRequest(BASE_URL . "/admin/kontrak/simak/konsultasi/{$simakId}/share", 'POST', http_build_query($shareFields));
        
        $dbShare = queryDb("SELECT share_token FROM trn_kontrak_simak_konsultasi_share WHERE simak_id = ?", [$simakId], 'i');
        if (empty($dbShare)) {
            throw new Exception("Failed to find generated share token in database.");
        }
        $shareToken = $dbShare[0]['share_token'];
        $itemReport['share_token'] = $shareToken;
        $itemReport['share_url'] = BASE_URL . "/simak/share/" . $shareToken;
        echo "    - Share token: {$shareToken}\n";
        echo "    - Share URL: " . $itemReport['share_url'] . "\n";
        $itemReport['steps'][] = ['name' => 'Generate Share Link', 'status' => 'PASS', 'message' => "Token: " . substr($shareToken, 0, 10) . "..."];

        // Step 3: Run upload and verify scenarios
        $verificationStates[$simakId] = [];
        
        foreach ($itemInfo['scenarios'] as $stepCode => $stepConf) {
            $rowNo = $stepConf['row'];
            $csrf = getCsrfFromCookieJar($cookiesFile);
            
            if (isset($stepConf['type'])) {
                // Upload scenario
                $type = $stepConf['type']; // 'draft' or 'final'
                $method = $stepConf['method']; // 'file', 'drive', or 'none'
                $expect = $stepConf['expect'];
                
                echo "  [STEP] Upload public (Row {$rowNo}, Type: {$type}, Method: {$method}, Expect: {$expect})...\n";
                
                $uploadFields = [
                    'row_no' => $rowNo,
                    'upload_method' => $method,
                    'tipe_dokumen' => $type,
                    'csrf_test_name' => $csrf,
                ];
                
                if ($method === 'file') {
                    $localFile = ($type === 'draft') ? $dummyDraft : $dummyFinal;
                    $mime = ($type === 'draft') ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' : 'application/pdf';
                    $uploadFields['dokumen_file'] = new CURLFile($localFile, $mime, basename($localFile));
                } elseif ($method === 'drive') {
                    $uploadFields['google_drive_link'] = 'https://drive.google.com/drive/folders/1zOpJs4jCLi7FYS6SHxYXd3Y7oEA47Yia';
                } elseif ($method === 'none') {
                    $uploadFields['keterangan'] = 'Dokumen memang tidak ada dari pihak PPK';
                }
                
                $uploadUrl = BASE_URL . "/simak/share/{$shareToken}/upload";
                
                // Form boundary post for files
                if ($method === 'file') {
                    $res = makeRequest($uploadUrl, 'POST', $uploadFields);
                } else {
                    $res = makeRequest($uploadUrl, 'POST', http_build_query($uploadFields));
                }
                
                $successMsg = "berhasil";
                $isRedirect = ($res['code'] === 302 || $res['code'] === 303);
                
                if ($expect === 'success') {
                    $dbDocs = queryDb("SELECT id FROM trn_kontrak_simak_konsultasi_verifikasi_dokumen WHERE simak_id = ? AND row_no = ? AND tipe_dokumen = ? ORDER BY id DESC LIMIT 1", [$simakId, $rowNo, $type], 'iis');
                    
                    if (!empty($dbDocs)) {
                        echo "    - [PASS] Upload successful (verified in DB)\n";
                        $itemReport['steps'][] = ['name' => "Upload {$type} Row {$rowNo}", 'status' => 'PASS', 'message' => "Uploaded successfully using {$method}"];
                    } else {
                        // Extract alert messages from HTML body
                        $errorDetail = '';
                        if (preg_match('/<div class="alert alert-danger"[^>]*>(.*?)<\/div>/s', $res['body'], $m)) {
                            $errorDetail = trim(strip_tags($m[1]));
                        } elseif (preg_match('/<div class="invalid-feedback"[^>]*>(.*?)<\/div>/s', $res['body'], $m)) {
                            $errorDetail = trim(strip_tags($m[1]));
                        }
                        
                        echo "    - [FAIL] Response code: {$res['code']}\n";
                        echo "    - [FAIL] Redirected to: {$res['url']}\n";
                        if ($errorDetail) {
                            echo "    - [FAIL] Alert message: {$errorDetail}\n";
                        }
                        // Save response body for inspection
                        $debugFile = __DIR__ . '/../writable/failed_upload_' . $stepCode . '.html';
                        file_put_contents($debugFile, $res['body']);
                        echo "    - [FAIL] Saved response body to: " . basename($debugFile) . "\n";
                        
                        throw new Exception("Upload scenario {$stepCode} failed (verified in DB) - " . ($errorDetail ?: "check response body"));
                    }
                } else {
                    $dbDocs = queryDb("SELECT id FROM trn_kontrak_simak_konsultasi_verifikasi_dokumen WHERE simak_id = ? AND row_no = ? AND tipe_dokumen = ? ORDER BY id DESC LIMIT 1", [$simakId, $rowNo, $type], 'iis');
                    if (empty($dbDocs)) {
                        echo "    - [PASS] Upload failed as expected\n";
                        $itemReport['steps'][] = ['name' => "Blocked Final Row {$rowNo}", 'status' => 'PASS', 'message' => "Correctly blocked upload: Draft not verified Sesuai"];
                    } else {
                        throw new Exception("Constraint failed: final upload succeeded when it should have failed.");
                    }
                }
            } elseif (isset($stepConf['status'])) {
                // Admin Verification scenario
                $status = $stepConf['status'];
                $kel = $stepConf['kel'];
                
                echo "  [STEP] Admin verification (Row {$rowNo}, Status: {$status}, Kelengkapan: {$kel})...\n";
                
                $verificationStates[$simakId][$rowNo] = [
                    'kel' => $kel,
                    'ver' => $status,
                    'ket' => 'Verified by Automated Smoke Test',
                    'pic' => 'Admin Smoke Test'
                ];
                
                $verifyFields = [
                    'csrf_test_name' => $csrf
                ];
                foreach ($verificationStates[$simakId] as $r => $st) {
                    $verifyFields["kelengkapan_dokumen[$r]"] = $st['kel'];
                    $verifyFields["verifikasi_ki[$r]"] = $st['ver'];
                    $verifyFields["keterangan[$r]"] = $st['ket'];
                    $verifyFields["pic[$r]"] = $st['pic'];
                }
                
                $verifyUrl = BASE_URL . "/admin/kontrak/simak/konsultasi/{$simakId}/verifikasi";
                $res = makeRequest($verifyUrl, 'POST', http_build_query($verifyFields));
                
                // Query database to verify
                $dbVerify = queryDb("SELECT verifikasi_ki FROM trn_kontrak_simak_konsultasi_verifikasi WHERE simak_id = ? AND row_no = ?", [$simakId, $rowNo], 'ii');
                if (!empty($dbVerify) && $dbVerify[0]['verifikasi_ki'] === $status) {
                    echo "    - [PASS] Verification saved as {$status}\n";
                    $itemReport['steps'][] = ['name' => "Verify Row {$rowNo}", 'status' => 'PASS', 'message' => "Marked {$status} successfully"];
                } else {
                    throw new Exception("Verification failed to save in database.");
                }
            }
        }
    } catch (Exception $e) {
        echo "  [ERROR] " . $e->getMessage() . "\n";
        $itemReport['status'] = 'FAIL';
        $itemReport['steps'][] = ['name' => 'Error', 'status' => 'FAIL', 'message' => $e->getMessage()];
    }

    if ($itemReport['status'] === 'PASS') {
        $report['passed']++;
    } else {
        $report['failed']++;
    }
    $report['items'][] = $itemReport;
}

// Set overall status
if ($report['failed'] === 0) {
    $report['status_overall'] = 'SUCCESS';
} elseif ($report['passed'] > 0) {
    $report['status_overall'] = 'MIXED';
} else {
    $report['status_overall'] = 'FAILED';
}

echo "\n=== ALL TESTS COMPLETED ===\n";
echo "Passed: {$report['passed']}, Failed: {$report['failed']}\n";
echo "Generating Report File...\n";

// Save JSON for report generation
file_put_contents(__DIR__ . '/../writable/smoke_test_results.json', json_encode($report, JSON_PRETTY_PRINT));
echo "JSON results saved to writable/smoke_test_results.json\n";
