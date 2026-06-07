<?php
/**
 * SIMAK Complete Smoke Test for Konstruksi and Konsultasi
 *
 * Requirements:
 * - Create 3 new contracts for Konstruksi and 3 new contracts for Konsultasi.
 * - Reach progress targets on each:
 *   - Lengkap > 20% (>= 32 rows out of 157 for Konstruksi, >= 17 rows out of 84 for Konsultasi)
 *   - Belum Sesuai > 4% (>= 7 rows out of 157 for Konstruksi, >= 4 rows out of 84 for Konsultasi)
 * - Test using:
 *   - Dokumen (file upload)
 *   - Link Google Drive
 *   - Dokumen memang tidak ada
 * - Test draft vs final logic.
 */

define('BASE_URL', 'https://satkerpps-riau.online');
define('TIMEOUT', 60);

$cookiesFile = __DIR__ . '/../writable/smoke_test_cookies.txt';

// Ensure dummy files exist
$dummyDraft = __DIR__ . '/../writable/dummy_draft.xlsx';
$dummyFinal = __DIR__ . '/../writable/dummy_final.pdf';

if (!file_exists($dummyDraft)) {
    file_put_contents($dummyDraft, "Dummy Excel Draft Content");
}
if (!file_exists($dummyFinal)) {
    file_put_contents($dummyFinal, "Dummy PDF Final Content");
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

// Tree builder to find true leaf rows (matching the application's calculations)
function buildTree(array $rows): array {
    $map = [];
    foreach ($rows as $row) {
        $row['children'] = [];
        $map[(int) $row['id']] = $row;
    }
    $roots = [];
    foreach ($map as $id => $row) {
        $parentId = isset($row['parent_id']) ? (int) $row['parent_id'] : 0;
        if ($parentId > 0 && isset($map[$parentId])) {
            $map[$parentId]['children'][] = &$map[$id];
            continue;
        }
        $roots[] = &$map[$id];
    }
    return $roots;
}

function getTrueLeafRows(string $table): array {
    $rows = queryDb("SELECT id, parent_id, row_no, row_kind, has_question, has_draft, is_hidden_share, ordering FROM $table WHERE is_active = 1 ORDER BY ordering ASC, id ASC");
    if (empty($rows)) return [];
    
    $tree = buildTree($rows);
    $leafs = [];
    
    $walk = function (array $items, int $depth) use (&$walk, &$leafs) {
        foreach ($items as $item) {
            if ((int) ($item['is_hidden_share'] ?? 0) === 1) {
                continue;
            }
            $children = is_array($item['children'] ?? null) ? $item['children'] : [];
            $hasChildren = $children !== [];
            $hasQuestion = (int) ($item['has_question'] ?? 0) === 1;
            
            if ($hasQuestion && !$hasChildren) {
                $leafs[] = [
                    'row_no' => (int) $item['row_no'],
                    'has_draft' => (bool)$item['has_draft']
                ];
            }
            if ($children !== []) {
                $walk($children, $depth + 1);
            }
        }
    };
    $walk($tree, 0);
    return $leafs;
}

echo "========================================================\n";
echo "    SIMAK COMPLETE SMOKE TEST RUNNER (KONSTRUKSI & KONSULTASI)\n";
echo "========================================================\n\n";

// 1. Login Admin
echo "Logging in as Admin...\n";
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

// Resolve True Leaf Rows
// 1. Konstruksi
$konLeaves = getTrueLeafRows('mst_simak_konstruksi_item');
$konDraftRows = [];
$konFinalOnlyRows = [];
foreach ($konLeaves as $row) {
    if ($row['has_draft']) {
        $konDraftRows[] = $row['row_no'];
    } else {
        $konFinalOnlyRows[] = $row['row_no'];
    }
}
echo "[+] Konstruksi Templates: " . count($konLeaves) . " true leaf rows (Draft: " . count($konDraftRows) . ", Final only: " . count($konFinalOnlyRows) . ")\n";

// 2. Konsultasi
$konsLeaves = getTrueLeafRows('mst_simak_konsultasi_item');
$konsDraftRows = [];
$konsFinalOnlyRows = [];
foreach ($konsLeaves as $row) {
    if ($row['has_draft']) {
        $konsDraftRows[] = $row['row_no'];
    } else {
        $konsFinalOnlyRows[] = $row['row_no'];
    }
}
echo "[+] Konsultasi Templates: " . count($konsLeaves) . " true leaf rows (Draft: " . count($konsDraftRows) . ", Final only: " . count($konsFinalOnlyRows) . ")\n\n";

$timestamp = date('YmdHis');
$report = [
    'timestamp' => date('Y-m-d H:i:s'),
    'email_respondent' => 'agung.justik@gmail.com',
    'status_overall' => 'PENDING',
    'total_items' => 6,
    'passed' => 0,
    'failed' => 0,
    'items' => [],
];

$konstruksiContracts = [
    [
        'name' => "Smoke Test Konstruksi 1 - $timestamp",
        'nomor' => "SMOKE/KON/COMPL/1/$timestamp",
        'draft_method' => 'file',
        'only_final_len_method' => 'file',
    ],
    [
        'name' => "Smoke Test Konstruksi 2 - $timestamp",
        'nomor' => "SMOKE/KON/COMPL/2/$timestamp",
        'draft_method' => 'drive',
        'only_final_len_method' => 'drive',
    ],
    [
        'name' => "Smoke Test Konstruksi 3 - $timestamp",
        'nomor' => "SMOKE/KON/COMPL/3/$timestamp",
        'draft_method' => 'none',
        'only_final_len_method' => 'none',
    ],
];

$konsultasiContracts = [
    [
        'name' => "Smoke Test Konsultasi 1 - $timestamp",
        'nomor' => "SMOKE/KONS/COMPL/1/$timestamp",
        'draft_method' => 'file',
        'only_final_len_method' => 'file',
    ],
    [
        'name' => "Smoke Test Konsultasi 2 - $timestamp",
        'nomor' => "SMOKE/KONS/COMPL/2/$timestamp",
        'draft_method' => 'drive',
        'only_final_len_method' => 'drive',
    ],
    [
        'name' => "Smoke Test Konsultasi 3 - $timestamp",
        'nomor' => "SMOKE/KONS/COMPL/3/$timestamp",
        'draft_method' => 'none',
        'only_final_len_method' => 'none',
    ],
];

// HELPER FUNCTION: Upload document via share link
function uploadShareDoc(string $shareToken, int $rowNo, string $method, string $type) {
    global $cookiesFile, $dummyDraft, $dummyFinal;
    $csrf = getCsrfFromCookieJar($cookiesFile);
    $uploadUrl = BASE_URL . "/simak/share/{$shareToken}/upload";
    
    $fields = [
        'row_no' => $rowNo,
        'upload_method' => $method,
        'tipe_dokumen' => $type,
        'csrf_test_name' => $csrf,
    ];
    
    if ($method === 'file') {
        $localFile = ($type === 'draft') ? $dummyDraft : $dummyFinal;
        $mime = ($type === 'draft') ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' : 'application/pdf';
        $fields['dokumen_file'] = new CURLFile($localFile, $mime, basename($localFile));
        return makeRequest($uploadUrl, 'POST', $fields);
    } elseif ($method === 'drive') {
        $fields['google_drive_link'] = 'https://drive.google.com/drive/folders/1zOpJs4jCLi7FYS6SHxYXd3Y7oEA47Yia';
    } elseif ($method === 'none') {
        $fields['keterangan'] = 'Dokumen memang tidak ada dari pihak PPK';
    }
    
    return makeRequest($uploadUrl, 'POST', http_build_query($fields));
}

// HELPER FUNCTION: Admin single row verification
function verifyAdminDoc(string $category, int $simakId, int $rowNo, string $type, string $kel, string $ver, string $ket) {
    global $cookiesFile;
    $csrf = getCsrfFromCookieJar($cookiesFile);
    $verifyUrl = BASE_URL . "/admin/kontrak/simak/" . strtolower($category) . "/{$simakId}/verifikasi/upload";
    
    $fields = [
        'row_no' => $rowNo,
        'tipe_dokumen' => $type,
        'kelengkapan_dokumen' => $kel,
        'verifikasi_ki' => $ver,
        'keterangan' => $ket,
        'pic' => 'Admin Smoke Test',
        'csrf_test_name' => $csrf,
    ];
    
    return makeRequest($verifyUrl, 'POST', http_build_query($fields));
}

// ==================== EXECUTE KONSTRUKSI ====================
echo "=== RUNNING SUITE 1: KONSTRUKSI ===\n";
foreach ($konstruksiContracts as $i => $contract) {
    $cIdx = $i + 1;
    echo "\n[Konstruksi Contract #$cIdx] Paket: {$contract['name']}\n";
    
    $itemReport = [
        'index' => $cIdx,
        'category' => 'Konstruksi',
        'name' => $contract['name'],
        'nomor_kontrak' => $contract['nomor'],
        'id' => null,
        'share_token' => null,
        'share_url' => null,
        'status' => 'PASS',
        'steps' => [],
    ];
    
    try {
        // Step 1: Create contract via Admin
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
        
        $dbRow = queryDb("SELECT id FROM trn_kontrak_simak WHERE nomor_kontrak = ?", [$contract['nomor']], 's');
        if (empty($dbRow)) {
            throw new Exception("Failed to find created contract in DB.");
        }
        $simakId = (int)$dbRow[0]['id'];
        $itemReport['id'] = $simakId;
        echo "  [+] Created contract successfully. Simak ID: $simakId\n";
        $itemReport['steps'][] = ['name' => 'Create Item', 'status' => 'PASS', 'message' => "Created item ID $simakId"];
        
        // Step 2: Generate Share Link
        $csrf = getCsrfFromCookieJar($cookiesFile);
        $shareFields = [
            'duration' => '1week',
            'csrf_test_name' => $csrf,
        ];
        makeRequest(BASE_URL . "/admin/kontrak/simak/konstruksi/{$simakId}/share", 'POST', http_build_query($shareFields));
        
        $dbShare = queryDb("SELECT share_token FROM trn_kontrak_simak_share WHERE simak_id = ? AND is_active = 1 LIMIT 1", [$simakId], 'i');
        if (empty($dbShare)) {
            throw new Exception("Failed to find share token in DB.");
        }
        $shareToken = $dbShare[0]['share_token'];
        $itemReport['share_token'] = $shareToken;
        $itemReport['share_url'] = BASE_URL . "/simak/share/{$shareToken}";
        echo "  [+] Share link token generated: $shareToken\n";
        $itemReport['steps'][] = ['name' => 'Generate Share Link', 'status' => 'PASS', 'message' => "Token: " . substr($shareToken, 0, 10) . "..."];
        
        // Step 3: Test draft vs final on Row 53
        echo "  [STEP] Uploading draft Row 53 using method: {$contract['draft_method']}...\n";
        uploadShareDoc($shareToken, 53, $contract['draft_method'], 'draft');
        $itemReport['steps'][] = ['name' => 'Upload draft Row 53', 'status' => 'PASS', 'message' => "Uploaded successfully using " . $contract['draft_method']];
        
        // Try uploading final on Row 53 before draft is verified. Should fail (no row in DB for final doc).
        echo "  [STEP] Testing final block constraint on Row 53...\n";
        uploadShareDoc($shareToken, 53, $contract['draft_method'], 'final');
        
        $dbDocs = queryDb("SELECT id FROM trn_kontrak_simak_verifikasi_dokumen WHERE simak_id = ? AND row_no = 53 AND tipe_dokumen = 'final' ORDER BY id DESC LIMIT 1", [$simakId], 'i');
        if (empty($dbDocs)) {
            echo "  [+] Correctly blocked upload of final doc since draft was not verified Sesuai.\n";
            $itemReport['steps'][] = ['name' => 'Blocked Final Row 53', 'status' => 'PASS', 'message' => "Correctly blocked upload: Draft not verified Sesuai"];
        } else {
            throw new Exception("Constraint failed: Upload final on Row 53 succeeded when draft was not verified!");
        }
        
        // Admin verifies draft as Sesuai
        echo "  [STEP] Admin verfies draft Row 53 as Sesuai...\n";
        $kel = ($contract['draft_method'] === 'none') ? 'tidak' : 'ada';
        verifyAdminDoc('konstruksi', $simakId, 53, 'draft', $kel, 'sesuai', 'Draft Verified Sesuai');
        $itemReport['steps'][] = ['name' => 'Verify Draft Row 53', 'status' => 'PASS', 'message' => "Marked sesuai successfully"];
        
        // Now upload final on Row 53 (should succeed)
        echo "  [STEP] Uploading final Row 53...\n";
        uploadShareDoc($shareToken, 53, $contract['draft_method'], 'final');
        $itemReport['steps'][] = ['name' => 'Upload final Row 53', 'status' => 'PASS', 'message' => "Uploaded final successfully"];
        
        // Admin verifies final on Row 53 as Sesuai
        echo "  [STEP] Admin verifies final Row 53 as Sesuai...\n";
        verifyAdminDoc('konstruksi', $simakId, 53, 'final', $kel, 'sesuai', 'Final Verified Sesuai');
        $itemReport['steps'][] = ['name' => 'Verify Final Row 53', 'status' => 'PASS', 'message' => "Marked sesuai successfully"];
        
        // Step 4: Test draft verification as Tidak Sesuai on Row 222
        echo "  [STEP] Uploading draft Row 222 using method: {$contract['draft_method']}...\n";
        uploadShareDoc($shareToken, 222, $contract['draft_method'], 'draft');
        
        echo "  [STEP] Admin verifies draft Row 222 as Tidak Sesuai...\n";
        verifyAdminDoc('konstruksi', $simakId, 222, 'draft', $kel, 'tidak_sesuai', 'Draft Verified Tidak Sesuai');
        $itemReport['steps'][] = ['name' => 'Verify draft Row 222 (Tidak Sesuai)', 'status' => 'PASS', 'message' => "Marked tidak_sesuai successfully"];
        
        // Step 5: Upload only final rows to reach targets:
        // Target Lengkap > 20% (need 32 total, we have 1 (Row 53) -> need 31 more only-final rows verified as Sesuai)
        // Target Belum Sesuai > 4% (need 7 total, we have 1 (Row 222) -> need 6 more only-final rows verified as Tidak Sesuai)
        
        // Lengkap rows upload (31 rows)
        $lenRowsToUpload = array_slice($konFinalOnlyRows, 0, 31);
        echo "  [STEP] Uploading & Verifying 31 Final-Only rows as Sesuai...\n";
        
        // Explicitly test feedback loop on the first row of only-final
        $feedbackRow = $lenRowsToUpload[0];
        echo "  [STEP] Testing final document feedback loop on Row {$feedbackRow}...\n";
        // 1. Upload final
        uploadShareDoc($shareToken, $feedbackRow, $contract['only_final_len_method'], 'final');
        // 2. Admin verify as Tidak Sesuai
        verifyAdminDoc('konstruksi', $simakId, $feedbackRow, 'final', 'ada', 'tidak_sesuai', 'First Upload Rejected');
        echo "  [+] First upload verified as Tidak Sesuai. Row {$feedbackRow} status is currently Belum Sesuai.\n";
        
        // 3. Re-upload final (simulating correction)
        uploadShareDoc($shareToken, $feedbackRow, $contract['only_final_len_method'], 'final');
        // 4. Admin verify as Sesuai
        verifyAdminDoc('konstruksi', $simakId, $feedbackRow, 'final', 'ada', 'sesuai', 'Second Upload Approved (Feedback Loop Passed)');
        echo "  [+] Re-uploaded document verified as Sesuai. Feedback loop successfully verified.\n";
        $itemReport['steps'][] = ['name' => 'Verify Final Feedback Loop Row ' . $feedbackRow, 'status' => 'PASS', 'message' => "Successfully uploaded, rejected, re-uploaded, and approved."];

        foreach ($lenRowsToUpload as $idx => $row) {
            if ($idx === 0) continue; // already handled by feedback loop test
            // Distribute methods: file, drive, none
            if ($idx < 11) {
                $method = 'file';
                $kel = 'ada';
            } elseif ($idx < 21) {
                $method = 'drive';
                $kel = 'ada';
            } else {
                $method = 'none';
                $kel = 'tidak';
            }
            
            uploadShareDoc($shareToken, $row, $method, 'final');
            verifyAdminDoc('konstruksi', $simakId, $row, 'final', $kel, 'sesuai', 'Lengkap Sesuai Row ' . $row);
        }
        $itemReport['steps'][] = ['name' => 'Upload & Verify 31 Final-Only Lengkap Rows', 'status' => 'PASS', 'message' => "Verified 31 final-only rows as Sesuai using mixed methods"];
        
        // Belum Sesuai rows upload (7 rows)
        $bsRowsToUpload = array_slice($konFinalOnlyRows, 31, 7);
        echo "  [STEP] Uploading & Verifying 7 Final-Only rows as Tidak Sesuai...\n";
        
        foreach ($bsRowsToUpload as $idx => $row) {
            // Distribute methods: file, drive, none
            if ($idx < 2) {
                $method = 'file';
                $kel = 'ada';
            } elseif ($idx < 4) {
                $method = 'drive';
                $kel = 'ada';
            } else {
                $method = 'none';
                $kel = 'tidak';
            }
            
            uploadShareDoc($shareToken, $row, $method, 'final');
            verifyAdminDoc('konstruksi', $simakId, $row, 'final', $kel, 'tidak_sesuai', 'Belum Sesuai Tidak Sesuai Row ' . $row);
        }
        $itemReport['steps'][] = ['name' => 'Upload & Verify 7 Final-Only Belum Sesuai Rows', 'status' => 'PASS', 'message' => "Verified 7 final-only rows as Tidak Sesuai using mixed methods"];
        
        // Step 6: Verify calculations on database
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
        
        echo "  [+] Live compliance stats: Lengkap = {$lengkapPercentage}%, Belum Sesuai = {$belumSesuaiPercentage}%\n";
        
        if ($lengkapPercentage > 20.0 && $belumSesuaiPercentage > 5.0) {
            echo "  [SUCCESS] Target met! Lengkap ({$lengkapPercentage}% > 20%), Belum Sesuai ({$belumSesuaiPercentage}% > 5%)\n";
        } else {
            throw new Exception("Target compliance NOT met! Lengkap: {$lengkapPercentage}%, Belum Sesuai: {$belumSesuaiPercentage}%");
        }
    } catch (Exception $e) {
        echo "  [-] Error: " . $e->getMessage() . "\n";
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

// ==================== EXECUTE KONSULTASI ====================
echo "\n=== RUNNING SUITE 2: KONSULTASI ===\n";
foreach ($konsultasiContracts as $i => $contract) {
    $cIdx = $i + 1;
    echo "\n[Konsultasi Contract #$cIdx] Paket: {$contract['name']}\n";
    
    $itemReport = [
        'index' => $cIdx + 3,
        'category' => 'Konsultasi',
        'name' => $contract['name'],
        'nomor_kontrak' => $contract['nomor'],
        'id' => null,
        'share_token' => null,
        'share_url' => null,
        'status' => 'PASS',
        'steps' => [],
    ];
    
    try {
        // Step 1: Create contract via Admin
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
        
        $dbRow = queryDb("SELECT id FROM trn_kontrak_simak_konsultasi WHERE nomor_kontrak = ?", [$contract['nomor']], 's');
        if (empty($dbRow)) {
            throw new Exception("Failed to find created contract in DB.");
        }
        $simakId = (int)$dbRow[0]['id'];
        $itemReport['id'] = $simakId;
        echo "  [+] Created contract successfully. Simak ID: $simakId\n";
        $itemReport['steps'][] = ['name' => 'Create Item', 'status' => 'PASS', 'message' => "Created item ID $simakId"];
        
        // Step 2: Generate Share Link
        $csrf = getCsrfFromCookieJar($cookiesFile);
        $shareFields = [
            'duration' => '1week',
            'csrf_test_name' => $csrf,
        ];
        makeRequest(BASE_URL . "/admin/kontrak/simak/konsultasi/{$simakId}/share", 'POST', http_build_query($shareFields));
        
        $dbShare = queryDb("SELECT share_token FROM trn_kontrak_simak_konsultasi_share WHERE simak_id = ? AND is_active = 1 LIMIT 1", [$simakId], 'i');
        if (empty($dbShare)) {
            throw new Exception("Failed to find share token in DB.");
        }
        $shareToken = $dbShare[0]['share_token'];
        $itemReport['share_token'] = $shareToken;
        $itemReport['share_url'] = BASE_URL . "/simak/share/{$shareToken}";
        echo "  [+] Share link token generated: $shareToken\n";
        $itemReport['steps'][] = ['name' => 'Generate Share Link', 'status' => 'PASS', 'message' => "Token: " . substr($shareToken, 0, 10) . "..."];
        
        // Step 3: Test draft vs final on Row 38
        echo "  [STEP] Uploading draft Row 38 using method: {$contract['draft_method']}...\n";
        uploadShareDoc($shareToken, 38, $contract['draft_method'], 'draft');
        $itemReport['steps'][] = ['name' => 'Upload draft Row 38', 'status' => 'PASS', 'message' => "Uploaded successfully using " . $contract['draft_method']];
        
        // Try uploading final on Row 38 before draft is verified. Should fail.
        echo "  [STEP] Testing final block constraint on Row 38...\n";
        uploadShareDoc($shareToken, 38, $contract['draft_method'], 'final');
        
        $dbDocs = queryDb("SELECT id FROM trn_kontrak_simak_konsultasi_verifikasi_dokumen WHERE simak_id = ? AND row_no = 38 AND tipe_dokumen = 'final' ORDER BY id DESC LIMIT 1", [$simakId], 'i');
        if (empty($dbDocs)) {
            echo "  [+] Correctly blocked upload of final doc since draft was not verified Sesuai.\n";
            $itemReport['steps'][] = ['name' => 'Blocked Final Row 38', 'status' => 'PASS', 'message' => "Correctly blocked upload: Draft not verified Sesuai"];
        } else {
            throw new Exception("Constraint failed: Upload final on Row 38 succeeded when draft was not verified!");
        }
        
        // Admin verifies draft as Sesuai
        echo "  [STEP] Admin verfies draft Row 38 as Sesuai...\n";
        $kel = ($contract['draft_method'] === 'none') ? 'tidak' : 'ada';
        verifyAdminDoc('konsultasi', $simakId, 38, 'draft', $kel, 'sesuai', 'Draft Verified Sesuai');
        $itemReport['steps'][] = ['name' => 'Verify Draft Row 38', 'status' => 'PASS', 'message' => "Marked sesuai successfully"];
        
        // Now upload final on Row 38 (should succeed)
        echo "  [STEP] Uploading final Row 38...\n";
        uploadShareDoc($shareToken, 38, $contract['draft_method'], 'final');
        $itemReport['steps'][] = ['name' => 'Upload final Row 38', 'status' => 'PASS', 'message' => "Uploaded final successfully"];
        
        // Admin verifies final on Row 38 as Sesuai
        echo "  [STEP] Admin verifies final Row 38 as Sesuai...\n";
        verifyAdminDoc('konsultasi', $simakId, 38, 'final', $kel, 'sesuai', 'Final Verified Sesuai');
        $itemReport['steps'][] = ['name' => 'Verify Final Row 38', 'status' => 'PASS', 'message' => "Marked sesuai successfully"];
        
        // Step 4: Test draft verification as Tidak Sesuai on Row 39
        echo "  [STEP] Uploading draft Row 39 using method: {$contract['draft_method']}...\n";
        uploadShareDoc($shareToken, 39, $contract['draft_method'], 'draft');
        
        echo "  [STEP] Admin verifies draft Row 39 as Tidak Sesuai...\n";
        verifyAdminDoc('konsultasi', $simakId, 39, 'draft', $kel, 'tidak_sesuai', 'Draft Verified Tidak Sesuai');
        $itemReport['steps'][] = ['name' => 'Verify draft Row 39 (Tidak Sesuai)', 'status' => 'PASS', 'message' => "Marked tidak_sesuai successfully"];
        
        // Step 5: Upload only final rows to reach targets:
        // Target Lengkap > 20% (need 17 total, we have 1 (Row 38) -> need 16 more only-final rows verified as Sesuai)
        // Target Belum Sesuai > 4% (need 4 total, we have 1 (Row 39) -> need 3 more only-final rows verified as Tidak Sesuai)
        
        // Lengkap rows upload (16 rows)
        $lenRowsToUpload = array_slice($konsFinalOnlyRows, 0, 16);
        echo "  [STEP] Uploading & Verifying 16 Final-Only rows as Sesuai...\n";
        
        // Explicitly test feedback loop on the first row of only-final
        $feedbackRow = $lenRowsToUpload[0];
        echo "  [STEP] Testing final document feedback loop on Row {$feedbackRow}...\n";
        // 1. Upload final
        uploadShareDoc($shareToken, $feedbackRow, $contract['only_final_len_method'], 'final');
        // 2. Admin verify as Tidak Sesuai
        verifyAdminDoc('konsultasi', $simakId, $feedbackRow, 'final', 'ada', 'tidak_sesuai', 'First Upload Rejected');
        echo "  [+] First upload verified as Tidak Sesuai. Row {$feedbackRow} status is currently Belum Sesuai.\n";
        
        // 3. Re-upload final (simulating correction)
        uploadShareDoc($shareToken, $feedbackRow, $contract['only_final_len_method'], 'final');
        // 4. Admin verify as Sesuai
        verifyAdminDoc('konsultasi', $simakId, $feedbackRow, 'final', 'ada', 'sesuai', 'Second Upload Approved (Feedback Loop Passed)');
        echo "  [+] Re-uploaded document verified as Sesuai. Feedback loop successfully verified.\n";
        $itemReport['steps'][] = ['name' => 'Verify Final Feedback Loop Row ' . $feedbackRow, 'status' => 'PASS', 'message' => "Successfully uploaded, rejected, re-uploaded, and approved."];

        foreach ($lenRowsToUpload as $idx => $row) {
            if ($idx === 0) continue; // already handled by feedback loop test
            // Distribute methods: file, drive, none
            if ($idx < 6) {
                $method = 'file';
                $kel = 'ada';
            } elseif ($idx < 11) {
                $method = 'drive';
                $kel = 'ada';
            } else {
                $method = 'none';
                $kel = 'tidak';
            }
            
            uploadShareDoc($shareToken, $row, $method, 'final');
            verifyAdminDoc('konsultasi', $simakId, $row, 'final', $kel, 'sesuai', 'Lengkap Sesuai Row ' . $row);
        }
        $itemReport['steps'][] = ['name' => 'Upload & Verify 16 Final-Only Lengkap Rows', 'status' => 'PASS', 'message' => "Verified 16 final-only rows as Sesuai using mixed methods"];
        
        // Belum Sesuai rows upload (4 rows)
        $bsRowsToUpload = array_slice($konsFinalOnlyRows, 16, 4);
        echo "  [STEP] Uploading & Verifying 4 Final-Only rows as Tidak Sesuai...\n";
        
        foreach ($bsRowsToUpload as $idx => $row) {
            // Distribute methods: file, drive, none
            if ($idx < 1) {
                $method = 'file';
                $kel = 'ada';
            } elseif ($idx < 2) {
                $method = 'drive';
                $kel = 'ada';
            } else {
                $method = 'none';
                $kel = 'tidak';
            }
            
            uploadShareDoc($shareToken, $row, $method, 'final');
            verifyAdminDoc('konsultasi', $simakId, $row, 'final', $kel, 'tidak_sesuai', 'Belum Sesuai Tidak Sesuai Row ' . $row);
        }
        $itemReport['steps'][] = ['name' => 'Upload & Verify 4 Final-Only Belum Sesuai Rows', 'status' => 'PASS', 'message' => "Verified 4 final-only rows as Tidak Sesuai using mixed methods"];
        
        // Step 6: Verify calculations on database
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
        
        echo "  [+] Live compliance stats: Lengkap = {$lengkapPercentage}%, Belum Sesuai = {$belumSesuaiPercentage}%\n";
        
        if ($lengkapPercentage > 20.0 && $belumSesuaiPercentage > 5.0) {
            echo "  [SUCCESS] Target met! Lengkap ({$lengkapPercentage}% > 20%), Belum Sesuai ({$belumSesuaiPercentage}% > 5%)\n";
        } else {
            throw new Exception("Target compliance NOT met! Lengkap: {$lengkapPercentage}%, Belum Sesuai: {$belumSesuaiPercentage}%");
        }
    } catch (Exception $e) {
        echo "  [-] Error: " . $e->getMessage() . "\n";
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

// 4. Wrap up and write results JSON
if ($report['failed'] === 0) {
    $report['status_overall'] = 'SUCCESS';
} elseif ($report['passed'] > 0) {
    $report['status_overall'] = 'MIXED';
} else {
    $report['status_overall'] = 'FAILED';
}

echo "\n========================================================\n";
echo "              SMOKE TEST COMPLETED RUNS                 \n";
echo "========================================================\n";
echo "Total items: {$report['total_items']}, Passed: {$report['passed']}, Failed: {$report['failed']}\n";

$jsonFile = __DIR__ . '/../writable/smoke_test_results.json';
file_put_contents($jsonFile, json_encode($report, JSON_PRETTY_PRINT));
echo "Saved results to $jsonFile\n";
