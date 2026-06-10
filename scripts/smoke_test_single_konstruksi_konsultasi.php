<?php
/**
 * SIMAK Single-Contract Smoke Test for Konstruksi and Konsultasi
 *
 * Target:
 *   - 1 new Konstruksi contract
 *   - 1 new Konsultasi contract
 *   - Lengkap > 20%
 *   - Belum Sesuai > 5%
 *
 * At the end, prints SQL DELETE queries to clean up all test data.
 */

define('BASE_URL', 'https://satkerpps-riau.online');
define('TIMEOUT', 60);

// Admin credentials
define('ADMIN_USERNAME', '199011092025061005');
define('ADMIN_PASSWORD', '123456');

$cookiesFile = __DIR__ . '/../writable/smoke_test_single_cookies.txt';

// Remove stale cookies
if (file_exists($cookiesFile)) {
    unlink($cookiesFile);
}

// Create dummy files
$dummyFile = __DIR__ . '/../writable/dummy_smoke_single.pdf';
if (!file_exists($dummyFile)) {
    file_put_contents($dummyFile, '%PDF-1.4 Dummy smoke test file content for upload testing.');
}

// -------------------------------------------------------
// DB Helper
// -------------------------------------------------------
function queryDb(string $sql, array $params = [], string $types = '') {
    $db = new mysqli('satkerpps-riau.online', 'agun9011_satkerpps', '9w:wxJn|K', 'agun9011_satkerpps');
    if ($db->connect_error) {
        throw new Exception("DB connect failed: " . $db->connect_error);
    }
    if (empty($params)) {
        $res = $db->query($sql);
        if ($res === false) {
            $err = $db->error; $db->close();
            throw new Exception("SQL Error: $err | SQL: $sql");
        }
        if ($res === true) { $db->close(); return true; }
        $data = [];
        while ($row = $res->fetch_assoc()) $data[] = $row;
        $res->free(); $db->close();
        return $data;
    } else {
        $stmt = $db->prepare($sql);
        if (!$stmt) { $err = $db->error; $db->close(); throw new Exception("Prepare Error: $err"); }
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        if ($stmt->error) { $err = $stmt->error; $stmt->close(); $db->close(); throw new Exception("Execute Error: $err"); }
        $res = $stmt->get_result();
        if ($res) {
            $data = [];
            while ($row = $res->fetch_assoc()) $data[] = $row;
            $res->free(); $stmt->close(); $db->close();
            return $data;
        }
        $stmt->close(); $db->close();
        return true;
    }
}

// -------------------------------------------------------
// CURL Helper
// -------------------------------------------------------
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

// -------------------------------------------------------
// Build leaf rows from template
// -------------------------------------------------------
function buildTree(array $rows): array {
    $map = [];
    foreach ($rows as $r) { $r['children'] = []; $map[(int)$r['id']] = $r; }
    $roots = [];
    foreach ($map as $id => $r) {
        $pid = (int)($r['parent_id'] ?? 0);
        if ($pid > 0 && isset($map[$pid])) { $map[$pid]['children'][] = &$map[$id]; continue; }
        $roots[] = &$map[$id];
    }
    return $roots;
}

function getLeafRows(string $table): array {
    $rows = queryDb("SELECT id, parent_id, row_no, has_question, has_draft, is_hidden_share, ordering FROM $table WHERE is_active = 1 ORDER BY ordering ASC, id ASC");
    if (empty($rows)) return [];
    $tree = buildTree($rows);
    $leafs = [];
    $walk = function(array $items) use (&$walk, &$leafs) {
        foreach ($items as $item) {
            if ((int)($item['is_hidden_share'] ?? 0) === 1) continue;
            $children = $item['children'] ?? [];
            if ((int)($item['has_question'] ?? 0) === 1 && empty($children)) {
                $leafs[] = ['row_no' => (int)$item['row_no'], 'has_draft' => (bool)$item['has_draft']];
            }
            if (!empty($children)) $walk($children);
        }
    };
    $walk($tree, );
    return $leafs;
}

// -------------------------------------------------------
// Upload share doc
// -------------------------------------------------------
function uploadShare(string $token, int $rowNo, string $type, string $method = 'file'): void {
    global $dummyFile;
    $csrf = getCsrf(dirname(__FILE__) . '/../writable/smoke_test_single_cookies.txt');
    $url  = BASE_URL . "/simak/share/{$token}/upload";
    $fields = ['row_no' => $rowNo, 'tipe_dokumen' => $type, 'upload_method' => $method, 'csrf_test_name' => $csrf];

    if ($method === 'file') {
        $fields['dokumen_file'] = new CURLFile($dummyFile, 'application/pdf', 'dummy_smoke.pdf');
        req($url, 'POST', $fields);
    } else {
        // 'none'
        $fields['keterangan'] = 'Dokumen memang tidak ada';
        req($url, 'POST', http_build_query($fields));
    }
}

// -------------------------------------------------------
// Admin verify (with file when kel='ada')
// -------------------------------------------------------
function verifyDoc(string $cat, int $simakId, int $rowNo, string $type, string $kel, string $ver): void {
    global $dummyFile;
    $csrf = getCsrf(dirname(__FILE__) . '/../writable/smoke_test_single_cookies.txt');
    $url  = BASE_URL . "/admin/kontrak/simak/" . strtolower($cat) . "/{$simakId}/verifikasi/upload";

    if ($kel === 'ada') {
        // Must send file as multipart when kelengkapan_dokumen=ada
        $fields = [
            'row_no'              => $rowNo,
            'tipe_dokumen'        => $type,
            'kelengkapan_dokumen' => $kel,
            'verifikasi_ki'       => $ver,
            'keterangan'          => "Smoke test verify $ver",
            'pic'                 => 'Smoke Test Script',
            'csrf_test_name'      => $csrf,
            'dokumen_file'        => new CURLFile($dummyFile, 'application/pdf', 'smoke_verif.pdf'),
        ];
        req($url, 'POST', $fields);
    } else {
        // kel='tidak': no file needed, send as url-encoded
        req($url, 'POST', http_build_query([
            'row_no'              => $rowNo,
            'tipe_dokumen'        => $type,
            'kelengkapan_dokumen' => $kel,
            'verifikasi_ki'       => $ver,
            'keterangan'          => "Smoke test verify $ver - dokumen memang tidak ada",
            'pic'                 => 'Smoke Test Script',
            'csrf_test_name'      => $csrf,
        ]));
    }
}

// -------------------------------------------------------
// MAIN
// -------------------------------------------------------
echo "====================================================\n";
echo "   SIMAK SMOKE TEST - Single Konstruksi + Konsultasi\n";
echo "====================================================\n\n";

// Login
echo "[1] Logging in as Admin...\n";
req(BASE_URL . '/masuk', 'GET');
$csrf = getCsrf($cookiesFile);
$loginRes = req(BASE_URL . '/masuk', 'POST', http_build_query([
    'username'       => ADMIN_USERNAME,
    'password'       => ADMIN_PASSWORD,
    'csrf_test_name' => $csrf,
]));
if (strpos($loginRes['body'], 'id="username"') !== false || strpos($loginRes['body'], 'Masuk Admin') !== false) {
    echo "[-] Login FAILED. Check credentials.\n"; exit(1);
}
echo "[+] Login successful.\n\n";

// Get leaf rows
echo "[2] Reading template rows...\n";
$konLeaves  = getLeafRows('mst_simak_konstruksi_item');
$konsLeaves = getLeafRows('mst_simak_konsultasi_item');

$konFinalOnly  = array_values(array_filter($konLeaves,  fn($r) => !$r['has_draft']));
$konsDraftRows = array_values(array_filter($konsLeaves, fn($r) => $r['has_draft']));
$konsFinalOnly = array_values(array_filter($konsLeaves, fn($r) => !$r['has_draft']));
$konDraftRows  = array_values(array_filter($konLeaves,  fn($r) => $r['has_draft']));

$totalKon  = count($konLeaves);
$totalKons = count($konsLeaves);
echo "[+] Konstruksi: $totalKon leaf rows (" . count($konDraftRows) . " draft-enabled, " . count($konFinalOnly) . " final-only)\n";
echo "[+] Konsultasi: $totalKons leaf rows (" . count($konsDraftRows) . " draft-enabled, " . count($konsFinalOnly) . " final-only)\n\n";

// Calculate required rows for targets (> 20% lengkap, > 5% belum sesuai)
// We aim for ~22% lengkap and ~6% belum sesuai to safely exceed targets
$konLengkapTarget  = (int)ceil($totalKon  * 0.22); // ~22%
$konBSTarget       = (int)ceil($totalKon  * 0.06); // ~6%
$konsLengkapTarget = (int)ceil($totalKons * 0.22);
$konsBSTarget      = (int)ceil($totalKons * 0.06);

echo "[+] Konstruksi targets: Lengkap={$konLengkapTarget} rows, BelumSesuai={$konBSTarget} rows\n";
echo "[+] Konsultasi targets: Lengkap={$konsLengkapTarget} rows, BelumSesuai={$konsBSTarget} rows\n\n";

$timestamp = date('YmdHis');
$deleteSQLBlocks = [];

// ====================================================
// KONSTRUKSI
// ====================================================
echo "===== [KONSTRUKSI] =====\n";
$konNomor = "SMOKE/KON/1/{$timestamp}";
$konNama  = "Smoke Test Konstruksi - {$timestamp}";

echo "[3] Creating Konstruksi contract...\n";
$csrf = getCsrf($cookiesFile);
req(BASE_URL . '/admin/kontrak/simak/konstruksi/tambah', 'POST', http_build_query([
    'satker'                          => 'Perencanaan Prasarana Strategis',
    'ppk_nip'                         => '199012212018021001',
    'ppk_nama'                        => 'Nurhidayat Nugroho, S.Ars',
    'nama_paket'                      => $konNama,
    'tahun_anggaran'                  => '2026 - 2027',
    'penyedia'                        => 'CV Smoke Konstruksi Test',
    'penyedia_jasa_konsultansi'       => 'CV Smoke Konstruksi Test',
    'nomor_kontrak'                   => $konNomor,
    'nilai_kontrak'                   => '500000000',
    'nilai_kontrak_jasa_konsultansi'  => '100000000',
    'jenis_pekerjaan_jasa_konsultansi'=> 'perencanaan',
    'masa_pelaksanaan'                => 'syc',
    'pagu_anggaran'                   => '1000000000',
    'metode_pemilihan'                => 'seleksi',
    'email_responden_1'               => 'agung.justik@gmail.com',
    'email_responden_2'               => '',
    'csrf_test_name'                  => $csrf,
]));

$dbRow = queryDb("SELECT id FROM trn_kontrak_simak WHERE nomor_kontrak = ?", [$konNomor], 's');
if (empty($dbRow)) { echo "[-] Failed to create Konstruksi contract.\n"; exit(1); }
$konId = (int)$dbRow[0]['id'];
echo "[+] Konstruksi contract created. ID: $konId\n";

// Generate share link
echo "[4] Generating share link...\n";
$csrf = getCsrf($cookiesFile);
req(BASE_URL . "/admin/kontrak/simak/konstruksi/{$konId}/share", 'POST', http_build_query([
    'duration' => '1week', 'csrf_test_name' => $csrf,
]));
$dbShare = queryDb("SELECT share_token FROM trn_kontrak_simak_share WHERE simak_id = ? AND is_active = 1 LIMIT 1", [$konId], 'i');
if (empty($dbShare)) { echo "[-] Failed to get share token.\n"; exit(1); }
$konToken = $dbShare[0]['share_token'];
echo "[+] Share token: $konToken\n";
echo "[+] Share URL: " . BASE_URL . "/simak/share/{$konToken}\n\n";

// Upload & verify rows for Lengkap (use final-only rows)
echo "[5] Uploading {$konLengkapTarget} final-only rows as LENGKAP (Sesuai)...\n";
$konLenRows = array_slice($konFinalOnly, 0, $konLengkapTarget);
foreach ($konLenRows as $idx => $r) {
    $method = ($idx % 3 === 2) ? 'none' : 'file';
    $kel    = ($method === 'none') ? 'tidak' : 'ada';
    uploadShare($konToken, $r['row_no'], 'final', $method);
    verifyDoc('konstruksi', $konId, $r['row_no'], 'final', $kel, 'sesuai');
    echo "  [+] Row {$r['row_no']} -> Sesuai ({$method})\n";
}

// Upload & verify rows for Belum Sesuai
echo "[6] Uploading {$konBSTarget} final-only rows as BELUM SESUAI (Tidak Sesuai)...\n";
$konBSRows = array_slice($konFinalOnly, $konLengkapTarget, $konBSTarget);
foreach ($konBSRows as $idx => $r) {
    $method = ($idx % 2 === 0) ? 'file' : 'none';
    $kel    = ($method === 'none') ? 'tidak' : 'ada';
    uploadShare($konToken, $r['row_no'], 'final', $method);
    verifyDoc('konstruksi', $konId, $r['row_no'], 'final', $kel, 'tidak_sesuai');
    echo "  [+] Row {$r['row_no']} -> Tidak Sesuai ({$method})\n";
}

// Verify stats
echo "[7] Checking Konstruksi compliance stats...\n";
$detailRes = req(BASE_URL . "/admin/kontrak/simak/konstruksi/{$konId}");
$konLengkap = 0.0; $konBS = 0.0;
if (preg_match_all('/<div class="kelengkapan-value">([\d,.]+)%<\/div>/i', $detailRes['body'], $m)) {
    $konLengkap = (float)str_replace(',', '.', $m[1][0] ?? '0');
    $konBS      = (float)str_replace(',', '.', $m[1][1] ?? '0');
}
echo "[+] Konstruksi - Lengkap: {$konLengkap}%, Belum Sesuai: {$konBS}%\n";
if ($konLengkap > 20.0 && $konBS > 5.0) {
    echo "[SUCCESS] Konstruksi targets MET!\n\n";
} else {
    echo "[WARNING] Konstruksi targets NOT met. Lengkap={$konLengkap}% (need >20%), BelumSesuai={$konBS}% (need >5%)\n\n";
}

// ====================================================
// KONSULTASI
// ====================================================
echo "===== [KONSULTASI] =====\n";
$konsNomor = "SMOKE/KONS/1/{$timestamp}";
$konsNama  = "Smoke Test Konsultasi - {$timestamp}";

echo "[8] Creating Konsultasi contract...\n";
$csrf = getCsrf($cookiesFile);
req(BASE_URL . '/admin/kontrak/simak/konsultasi/tambah', 'POST', http_build_query([
    'satker'                          => 'Perencanaan Prasarana Strategis',
    'ppk_nip'                         => '199012212018021001',
    'ppk_nama'                        => 'Nurhidayat Nugroho, S.Ars',
    'nama_paket'                      => $konsNama,
    'tahun_anggaran'                  => '2026 - 2027',
    'penyedia'                        => 'PT Smoke Konsultasi Test',
    'nomor_kontrak'                   => $konsNomor,
    'nilai_kontrak'                   => '200000000',
    'jenis_pekerjaan_jasa_konsultansi'=> 'perencanaan',
    'masa_pelaksanaan'                => 'syc',
    'pagu_anggaran'                   => '350000000',
    'metode_pemilihan'                => 'seleksi',
    'email_responden_1'               => 'agung.justik@gmail.com',
    'email_responden_2'               => '',
    'csrf_test_name'                  => $csrf,
]));

$dbRow = queryDb("SELECT id FROM trn_kontrak_simak_konsultasi WHERE nomor_kontrak = ?", [$konsNomor], 's');
if (empty($dbRow)) { echo "[-] Failed to create Konsultasi contract.\n"; exit(1); }
$konsId = (int)$dbRow[0]['id'];
echo "[+] Konsultasi contract created. ID: $konsId\n";

// Generate share link
echo "[9] Generating share link...\n";
$csrf = getCsrf($cookiesFile);
req(BASE_URL . "/admin/kontrak/simak/konsultasi/{$konsId}/share", 'POST', http_build_query([
    'duration' => '1week', 'csrf_test_name' => $csrf,
]));
$dbShare = queryDb("SELECT share_token FROM trn_kontrak_simak_konsultasi_share WHERE simak_id = ? AND is_active = 1 LIMIT 1", [$konsId], 'i');
if (empty($dbShare)) { echo "[-] Failed to get share token.\n"; exit(1); }
$konsToken = $dbShare[0]['share_token'];
echo "[+] Share token: $konsToken\n";
echo "[+] Share URL: " . BASE_URL . "/simak/share/{$konsToken}\n\n";

// Upload & verify rows for Lengkap
echo "[10] Uploading {$konsLengkapTarget} final-only rows as LENGKAP (Sesuai)...\n";
$konsLenRows = array_slice($konsFinalOnly, 0, $konsLengkapTarget);
foreach ($konsLenRows as $idx => $r) {
    $method = ($idx % 3 === 2) ? 'none' : 'file';
    $kel    = ($method === 'none') ? 'tidak' : 'ada';
    uploadShare($konsToken, $r['row_no'], 'final', $method);
    verifyDoc('konsultasi', $konsId, $r['row_no'], 'final', $kel, 'sesuai');
    echo "  [+] Row {$r['row_no']} -> Sesuai ({$method})\n";
}

// Upload & verify rows for Belum Sesuai
echo "[11] Uploading {$konsBSTarget} final-only rows as BELUM SESUAI (Tidak Sesuai)...\n";
$konsBSRows = array_slice($konsFinalOnly, $konsLengkapTarget, $konsBSTarget);
foreach ($konsBSRows as $idx => $r) {
    $method = ($idx % 2 === 0) ? 'file' : 'none';
    $kel    = ($method === 'none') ? 'tidak' : 'ada';
    uploadShare($konsToken, $r['row_no'], 'final', $method);
    verifyDoc('konsultasi', $konsId, $r['row_no'], 'final', $kel, 'tidak_sesuai');
    echo "  [+] Row {$r['row_no']} -> Tidak Sesuai ({$method})\n";
}

// Verify stats
echo "[12] Checking Konsultasi compliance stats...\n";
$detailRes = req(BASE_URL . "/admin/kontrak/simak/konsultasi/{$konsId}");
$konsLengkap = 0.0; $konsBS = 0.0;
if (preg_match_all('/<div class="kelengkapan-value">([\d,.]+)%<\/div>/i', $detailRes['body'], $m)) {
    $konsLengkap = (float)str_replace(',', '.', $m[1][0] ?? '0');
    $konsBS      = (float)str_replace(',', '.', $m[1][1] ?? '0');
}
echo "[+] Konsultasi - Lengkap: {$konsLengkap}%, Belum Sesuai: {$konsBS}%\n";
if ($konsLengkap > 20.0 && $konsBS > 5.0) {
    echo "[SUCCESS] Konsultasi targets MET!\n\n";
} else {
    echo "[WARNING] Konsultasi targets NOT met. Lengkap={$konsLengkap}% (need >20%), BelumSesuai={$konsBS}% (need >5%)\n\n";
}

// ====================================================
// PRINT DELETION SQL
// ====================================================
echo "====================================================\n";
echo "   SMOKE TEST COMPLETED\n";
echo "====================================================\n\n";

echo "Konstruksi Contract ID : {$konId}\n";
echo "Konstruksi Share Token : {$konToken}\n";
echo "Konsultasi Contract ID : {$konsId}\n";
echo "Konsultasi Share Token : {$konsToken}\n\n";

echo "====================================================\n";
echo "   SQL QUERIES TO DELETE SMOKE TEST DATA\n";
echo "====================================================\n\n";

$sql = <<<SQL
-- ============================================================
-- DELETE SMOKE TEST DATA
-- Konstruksi ID: {$konId}  |  Nomor: {$konNomor}
-- Konsultasi ID: {$konsId}  |  Nomor: {$konsNomor}
-- Generated: {$timestamp}
-- ============================================================

-- 1. Delete Konstruksi verification documents
DELETE FROM trn_kontrak_simak_verifikasi_dokumen
WHERE simak_id = {$konId};

-- 2. Delete Konstruksi share links
DELETE FROM trn_kontrak_simak_share
WHERE simak_id = {$konId};

-- 3. Delete Konstruksi contract record
DELETE FROM trn_kontrak_simak
WHERE id = {$konId}
  AND nomor_kontrak = '{$konNomor}';

-- 4. Delete Konsultasi verification documents
DELETE FROM trn_kontrak_simak_konsultasi_verifikasi_dokumen
WHERE simak_id = {$konsId};

-- 5. Delete Konsultasi share links
DELETE FROM trn_kontrak_simak_konsultasi_share
WHERE simak_id = {$konsId};

-- 6. Delete Konsultasi contract record
DELETE FROM trn_kontrak_simak_konsultasi
WHERE id = {$konsId}
  AND nomor_kontrak = '{$konsNomor}';

-- Verify nothing remains:
SELECT 'trn_kontrak_simak' AS tbl, COUNT(*) AS remaining FROM trn_kontrak_simak WHERE nomor_kontrak = '{$konNomor}'
UNION ALL
SELECT 'trn_kontrak_simak_konsultasi', COUNT(*) FROM trn_kontrak_simak_konsultasi WHERE nomor_kontrak = '{$konsNomor}';

SQL;

echo $sql;

// Save SQL to file
$sqlFile = __DIR__ . "/../writable/smoke_test_delete_{$timestamp}.sql";
file_put_contents($sqlFile, $sql);
echo "\n[+] SQL saved to: $sqlFile\n";
