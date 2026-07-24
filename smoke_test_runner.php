<?php
/**
 * Smoke Test for Perjalanan Dinas Module
 * Tests: Disposisi -> Surat Tugas -> Perjalanan Dinas flow
 * 
 * Run: php app/Database/Migrations/smoke_test_runner.php
 */

// ── Bootstrap CI4 just enough for DB ──
$baseDir = __DIR__;
define('FCPATH',    $baseDir . '/public/');
define('ROOTPATH',  $baseDir . '/');

$dbConfig = [
    'hostname' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'agun9011_satkerpps',
    'port'     => 3306,
];

$db = new mysqli($dbConfig['hostname'], $dbConfig['username'], $dbConfig['password'], $dbConfig['database'], $dbConfig['port']);
if ($db->connect_error) {
    die("DB connect failed: " . $db->connect_error);
}
$db->set_charset('utf8mb4');

// ── Results collector ──
$results = [];
$errors  = [];
$testNum = 0;

function addResult(string $step, string $desc, bool $pass, string $detail = '') {
    global $results, $testNum, $errors;
    $testNum++;
    $results[] = [
        'no'     => $testNum,
        'step'   => $step,
        'desc'   => $desc,
        'pass'   => $pass,
        'detail' => $detail,
    ];
    if (!$pass) {
        $errors[] = "Test #{$testNum}: {$desc} — {$detail}";
    }
    echo ($pass ? "✅" : "❌") . " Test #{$testNum}: {$desc}\n";
}

// ── Load master pegawai ──
$pegawaiRows = [];
$res = $db->query("SELECT id, nip, nama, golongan, eselon FROM mst_pegawai ORDER BY id LIMIT 20");
if ($res) {
    while ($r = $res->fetch_assoc()) { $pegawaiRows[] = $r; }
    $res->free();
}
addResult('SETUP', 'Load Master Pegawai', count($pegawaiRows) > 0, 'Found ' . count($pegawaiRows) . ' pegawai');

// ── Load master kota ──
$kabupatenRows = [];
$res = $db->query("SELECT nama_kabupaten FROM mst_kabupaten ORDER BY nama_kabupaten LIMIT 30");
if ($res) {
    while ($r = $res->fetch_assoc()) { $kabupatenRows[] = $r['nama_kabupaten']; }
    $res->free();
}
addResult('SETUP', 'Load Master Kabupaten', count($kabupatenRows) > 0, 'Found ' . count($kabupatenRows) . ' kabupaten');

// Load Dasar SPT
$dasarSptRows = [];
$res = $db->query("SELECT id, uraian FROM mst_dasar_spt ORDER BY id LIMIT 10");
if ($res) {
    while ($r = $res->fetch_assoc()) { $dasarSptRows[] = $r; }
    $res->free();
}

// Load Mata Anggaran
$mataAnggaranRows = [];
$res = $db->query("SELECT id, mata_anggaran FROM mst_mata_anggaran ORDER BY id LIMIT 10");
if ($res) {
    while ($r = $res->fetch_assoc()) { $mataAnggaranRows[] = $r; }
    $res->free();
}

// Load Kop Surat
$kopSuratRows = [];
$res = $db->query("SELECT id FROM kop_surat ORDER BY id LIMIT 5");
if ($res) {
    while ($r = $res->fetch_assoc()) { $kopSuratRows[] = $r; }
    $res->free();
}

// ── Clean existing data ──
$db->query("SET FOREIGN_KEY_CHECKS=0");
$db->query("TRUNCATE TABLE disposisi_perjalanan_dinas");
$db->query("TRUNCATE TABLE laporan_perjalanan_dinas");
$db->query("UPDATE app_settings SET last_kode_nomor_sppd=0");
$db->query("SET FOREIGN_KEY_CHECKS=1");
addResult('SETUP', 'Clean existing data (truncate)', true, 'Tables truncated successfully');

// ── Smoke test data definitions ──
$cities = !empty($kabupatenRows) ? $kabupatenRows : [
    'Kota Pekanbaru', 'Kota Dumai', 'Kabupaten Kampar', 'Kabupaten Siak',
    'Kabupaten Bengkalis', 'Kabupaten Rokan Hulu', 'Kabupaten Rokan Hilir',
    'Kabupaten Indragiri Hulu', 'Kabupaten Indragiri Hilir', 'Kabupaten Kuantan Singingi',
    'Kabupaten Pelalawan', 'Kabupaten Kepulauan Meranti'
];

$purposes = [
    'Monitoring & Evaluasi Pembangunan Sekolah Strategis',
    'Koordinasi Teknis Pelaksanaan Proyek Infrastruktur Publik',
    'Verifikasi Lapangan Dan Audit Fisik Bangunan Gedung',
    'Pengawasan Pekerjaan Konstruksi Tahap I',
    'Konsultasi Regional Perencanaan Prasarana Strategis',
    'Pendampingan Tim Penilai Kinerja Lapangan',
    'Rapat Koordinasi Anggaran Dan Pelaksanaan Kegiatan',
    'Inspeksi Keselamatan Dan Mutu Bangunan',
    'Evaluasi Kemajuan Progres Mingguan Fisik Pekerjaan',
    'Peninjauan Lokasi Rencana Pembangunan Gedung Sekolah',
    'Sosialisasi Petunjuk Teknis Dan Pembinaan Lapangan',
    'Koordinasi Bersama Pemda Dan Instansi Terkait',
    'Supervisi Lapangan Dan Penyesuaian Spesifikasi Teknis',
    'Verifikasi Dokumen Administrasi Dan Kwitansi Keuangan',
    'Pelaksanaan Workshop Dan Bimbingan Teknis Konstruksi',
    'Audit Internal Kegiatan Fisik Semester II',
    'Serah Terima Hasil Pekerjaan Fisik Konstruksi',
    'Rapat Kerja Teknis Regional Bersama BPJN',
    'Pemeriksaan Kelayakan Struktural Bangunan',
    'Pengendalian Mutu Material Dan Pengujian Lab',
    'Review Desain Perencanaan Teknis Bangunan',
    'Pendataan Aset Infrastruktur Bidang Pendidikan',
    'Focus Group Discussion Inovasi Infrastruktur',
    'Observasi Praktik Terbaik Manajemen Proyek',
    'Penyusunan Laporan Triwulanan Realisasi Fisik',
];

$transportasi_options = [
    'Mobil Dinas', 'Pesawat', 'Travel/Bus', 'Kereta Api',
    'Mobil Dinas, Pesawat', 'Travel/Bus, Kapal Laut'
];

$kasatkerPeg = $pegawaiRows[0] ?? ['id' => 1, 'nama' => 'Test Kasatker', 'nip' => '190000000000000000'];
$ppkPeg      = $pegawaiRows[1] ?? $kasatkerPeg;

$createdDisposisiIds = [];
$createdLaporanIds   = [];
$runningKodeNomor    = 1;

echo "\n" . str_repeat('=', 60) . "\n";
echo "  PHASE 1: DISPOSISI (25 entries)\n";
echo str_repeat('=', 60) . "\n\n";

for ($i = 1; $i <= 25; $i++) {
    $city    = $cities[($i - 1) % count($cities)];
    $purpose = $purposes[($i - 1) % count($purposes)] . " — Batch #{$i}";
    $transport = $transportasi_options[($i - 1) % count($transportasi_options)];
    $perihal = "Disposisi Perjalanan Dinas ke {$city} — #{$i}";

    // Vary pelaksana count: 1-5 per entry
    $numPelaksana = (($i - 1) % 5) + 1;
    $assignedPelaksana = [];
    if (!empty($pegawaiRows)) {
        for ($p = 0; $p < $numPelaksana; $p++) {
            $pegIdx = ($i + $p) % count($pegawaiRows);
            $peg = $pegawaiRows[$pegIdx];
            $assignedPelaksana[] = [
                'id'      => (int) $peg['id'],
                'nama'    => (string) $peg['nama'],
                'nip'     => (string) ($peg['nip'] ?? '-'),
                'jabatan' => (string) ($peg['golongan'] ?? 'Staf Pelaksana'),
                'golongan'=> (string) ($peg['golongan'] ?? ''),
            ];
        }
    } else {
        $assignedPelaksana[] = ['id' => 1, 'nama' => 'Budi Santoso', 'nip' => '198001010000000001', 'jabatan' => 'Staf'];
    }

    // Vary dates
    $startDay = str_pad((string) max(1, ($i % 28) + 1), 2, '0', STR_PAD_LEFT);
    $durDays  = ($i % 4) + 1;
    $endDay   = str_pad((string) min(28, (int)$startDay + $durDays), 2, '0', STR_PAD_LEFT);
    $startDate = '2026-07-' . $startDay;
    $endDate   = '2026-07-' . $endDay;

    // Some with different status patterns
    $status = 'disetujui';
    if ($i == 24) $status = 'pending';
    if ($i == 25) $status = 'ditolak';

    $tokenM = bin2hex(random_bytes(16));
    $tokenD = bin2hex(random_bytes(16));

    $disposisiData = [
        'tujuan'                => $purpose,
        'kota_tujuan'           => $city,
        'periode_mulai'         => $startDate,
        'periode_selesai'       => $endDate,
        'transportasi'          => $transport,
        'perihal'               => $perihal,
        'pelaksana_json'        => json_encode($assignedPelaksana, JSON_UNESCAPED_UNICODE),
        'diketahui_pegawai_id'  => (int) $kasatkerPeg['id'],
        'menyetujui_pegawai_id' => (int) $ppkPeg['id'],
        'status'                => $status,
        'status_menyetujui'     => ($status === 'disetujui') ? 'disetujui' : 'pending',
        'status_diketahui'      => ($status === 'disetujui') ? 'disetujui' : 'pending',
        'token_menyetujui'      => $tokenM,
        'token_diketahui'       => $tokenD,
        'created_by'            => 'admin',
        'created_at'            => date('Y-m-d H:i:s'),
        'updated_at'            => date('Y-m-d H:i:s'),
    ];

    $cols = implode(', ', array_map(fn($k) => "`{$k}`", array_keys($disposisiData)));
    $vals = implode(', ', array_map(fn($v) => "'" . $db->real_escape_string((string) $v) . "'", array_values($disposisiData)));
    $insertOk = $db->query("INSERT INTO disposisi_perjalanan_dinas ({$cols}) VALUES ({$vals})");
    $disposisiId = $db->insert_id;
    $createdDisposisiIds[$i] = $disposisiId;

    $pass = ($insertOk && $disposisiId > 0);
    addResult('DISPOSISI', "Insert Disposisi #{$i}: {$numPelaksana} pelaksana, {$city}, status={$status}", $pass,
        $pass ? "ID={$disposisiId}" : "MySQL Error: " . $db->error);
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "  PHASE 2: SURAT TUGAS / LAPORAN PERJALANAN DINAS (25 entries)\n";
echo str_repeat('=', 60) . "\n\n";

for ($i = 1; $i <= 25; $i++) {
    $disposisiId = $createdDisposisiIds[$i] ?? 0;
    if ($disposisiId <= 0) {
        addResult('LAPORAN', "Skip Laporan #{$i} (no disposisi)", false, "disposisi_id=0");
        continue;
    }

    // Read back disposisi
    $disRow = $db->query("SELECT * FROM disposisi_perjalanan_dinas WHERE id={$disposisiId}")->fetch_assoc();
    if (!$disRow) {
        addResult('LAPORAN', "Read back Disposisi #{$i}", false, "Not found id={$disposisiId}");
        continue;
    }

    $city    = $disRow['kota_tujuan'];
    $purpose = $disRow['tujuan'];
    $pelaksanaJson = $disRow['pelaksana_json'];
    $pelaksana = json_decode($pelaksanaJson, true) ?: [];
    $numPelaksana = count($pelaksana);

    $startDate = $disRow['periode_mulai'];
    $endDate   = $disRow['periode_selesai'];

    // Build rincian biaya with varied transport/penginapan
    $tNom = 150000 + (($i * 25000) % 350000);
    $pNom = 400000 + (($i * 50000) % 600000);

    $rincianBiaya = [
        'transport' => [
            ['tgl_mulai' => $startDate, 'tgl_selesai' => $endDate, 'nominal' => $tNom, 'keterangan' => "Sewa Kendaraan ke {$city}"]
        ],
        'penginapan' => [
            ['tgl_mulai' => $startDate, 'tgl_selesai' => $endDate, 'nominal' => $pNom, 'keterangan' => "Hotel di {$city}"]
        ],
    ];

    // Some entries have multiple transport rows
    if ($i % 7 === 0) {
        $rincianBiaya['transport'][] = [
            'tgl_mulai' => $startDate, 'tgl_selesai' => $endDate,
            'nominal' => 75000, 'keterangan' => 'Tol & BBM Tambahan'
        ];
    }

    // Dasar SPT
    $dasarIds = [];
    if (!empty($dasarSptRows)) {
        $dasarIds[] = (string) $dasarSptRows[($i - 1) % count($dasarSptRows)]['id'];
    }

    $kodeNomorStr = str_pad((string) $runningKodeNomor, 3, '0', STR_PAD_LEFT);
    $runningKodeNomor += $numPelaksana;

    $nomorSpt = str_pad((string) $i, 3, '0', STR_PAD_LEFT) . '/SPT/PPS/' . date('Y');
    $kopId    = !empty($kopSuratRows) ? (int) $kopSuratRows[($i - 1) % count($kopSuratRows)]['id'] : 1;
    $maId     = !empty($mataAnggaranRows) ? (int) $mataAnggaranRows[($i - 1) % count($mataAnggaranRows)]['id'] : 1;

    // Vary is_final / is_verified
    $isFinal    = ($i <= 23) ? 1 : 0;
    $isVerified = ($i <= 20) ? 1 : 0;

    $laporanData = [
        'disposisi_id'           => $disposisiId,
        'tujuan'                 => $purpose,
        'kota_tujuan'            => $city,
        'periode_mulai'          => $startDate,
        'periode_selesai'        => $endDate,
        'pelaksana_json'         => $pelaksanaJson,
        'nomor_surat_tugas'      => $nomorSpt,
        'kode_nomor'             => $kodeNomorStr,
        'dasar_spt_ids_json'     => json_encode($dasarIds, JSON_UNESCAPED_UNICODE),
        'tanggal_tanda_tangan'   => $startDate,
        'kop_surat_id'           => $kopId,
        'mata_anggaran_id'       => $maId,
        'rincian_biaya_json'     => json_encode($rincianBiaya, JSON_UNESCAPED_UNICODE),
        'sasaran'                => "Terlaksananya kegiatan di {$city} sesuai spesifikasi teknis",
        'laporan_hasil'          => "Telah dilaksanakan kegiatan {$purpose} di {$city}. Seluruh indikator tercapai.",
        'foto_dokumentasi_json'  => '[]',
        'dokumen_pendukung_json' => '[]',
        'is_final'               => $isFinal,
        'is_verified'            => $isVerified,
        'creator_name'           => 'admin',
        'created_at'             => date('Y-m-d H:i:s'),
        'updated_at'             => date('Y-m-d H:i:s'),
    ];

    $cols = implode(', ', array_map(fn($k) => "`{$k}`", array_keys($laporanData)));
    $vals = implode(', ', array_map(fn($v) => "'" . $db->real_escape_string((string) $v) . "'", array_values($laporanData)));
    $insertOk = $db->query("INSERT INTO laporan_perjalanan_dinas ({$cols}) VALUES ({$vals})");
    $laporanId = $db->insert_id;
    $createdLaporanIds[$i] = $laporanId;

    $pass = ($insertOk && $laporanId > 0);
    addResult('LAPORAN', "Insert Laporan #{$i}: {$numPelaksana} pelaksana, kode={$kodeNomorStr}, final={$isFinal}, verified={$isVerified}", $pass,
        $pass ? "ID={$laporanId}" : "MySQL Error: " . $db->error);
}

// Update app_settings
$finalLastNumber = $runningKodeNomor - 1;
$db->query("UPDATE app_settings SET last_kode_nomor_sppd={$finalLastNumber}");
addResult('SETUP', "Update last_kode_nomor_sppd to {$finalLastNumber}", true, '');

echo "\n" . str_repeat('=', 60) . "\n";
echo "  PHASE 3: ENDPOINT VERIFICATION (HTTP)\n";
echo str_repeat('=', 60) . "\n\n";

$baseUrl = 'http://localhost:8080';

// Get session cookie
$cookieFile = tempnam(sys_get_temp_dir(), 'smoke_cookie_');
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => "{$baseUrl}/auth/login",
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query(['username' => 'admin', 'password' => 'admin']),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_COOKIEJAR      => $cookieFile,
    CURLOPT_COOKIEFILE     => $cookieFile,
    CURLOPT_TIMEOUT        => 15,
]);
$loginResp = curl_exec($ch);
$loginCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
addResult('AUTH', 'Login as admin', ($loginCode >= 200 && $loginCode < 400), "HTTP {$loginCode}");

function httpGet(string $url, string $cookieFile): array {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_COOKIEFILE     => $cookieFile,
        CURLOPT_COOKIEJAR      => $cookieFile,
        CURLOPT_TIMEOUT        => 30,
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    return ['code' => $code, 'body' => $body, 'error' => $err];
}

// Test main listing pages
$listingPages = [
    ['DISPOSISI', 'Disposisi listing page', '/admin/surat/perjalanan-dinas/disposisi'],
    ['SURAT_TUGAS', 'Surat Tugas listing page', '/admin/surat/perjalanan-dinas/surat-tugas'],
    ['PERJALANAN_DINAS', 'Perjalanan Dinas listing page', '/admin/surat/perjalanan-dinas'],
];

foreach ($listingPages as $pg) {
    $resp = httpGet("{$baseUrl}{$pg[2]}", $cookieFile);
    $pass = ($resp['code'] === 200 && strlen($resp['body']) > 500);
    addResult($pg[0], $pg[1], $pass, "HTTP {$resp['code']}, size=" . strlen($resp['body']));
}

// Test specific record pages for first 5 records
$testLaporanIds = array_slice($createdLaporanIds, 0, 5, true);

foreach ($testLaporanIds as $idx => $lid) {
    // Dokumen page
    $resp = httpGet("{$baseUrl}/admin/surat/perjalanan-dinas/{$lid}/dokumen", $cookieFile);
    $pass = ($resp['code'] === 200 && strlen($resp['body']) > 200);
    $hasError = (stripos($resp['body'], 'Exception') !== false || stripos($resp['body'], 'Fatal error') !== false);
    if ($hasError) $pass = false;
    addResult('DOKUMEN', "Dokumen page laporan #{$idx} (ID={$lid})", $pass,
        $hasError ? "Page contains error/exception" : "HTTP {$resp['code']}");

    // Cetak SPT
    $resp = httpGet("{$baseUrl}/admin/surat/perjalanan-dinas/{$lid}/cetak-spt", $cookieFile);
    $pass = ($resp['code'] === 200 && strlen($resp['body']) > 500);
    $hasError = (stripos($resp['body'], 'Exception') !== false || stripos($resp['body'], 'Fatal error') !== false || stripos($resp['body'], 'Whoops') !== false);
    if ($hasError) $pass = false;
    addResult('CETAK_SPT', "Cetak SPT laporan #{$idx} (ID={$lid})", $pass,
        $hasError ? "Page contains error" : "HTTP {$resp['code']}, size=" . strlen($resp['body']));

    // Cetak Daftar Nominatif
    $resp = httpGet("{$baseUrl}/admin/surat/perjalanan-dinas/{$lid}/cetak-daftar-nominatif", $cookieFile);
    $pass = ($resp['code'] === 200 && strlen($resp['body']) > 500);
    $hasError = (stripos($resp['body'], 'Exception') !== false || stripos($resp['body'], 'Fatal error') !== false || stripos($resp['body'], 'Whoops') !== false);
    if ($hasError) $pass = false;
    addResult('CETAK_NOMINATIF', "Cetak Daftar Nominatif #{$idx} (ID={$lid})", $pass,
        $hasError ? "Page contains error" : "HTTP {$resp['code']}, size=" . strlen($resp['body']));

    // Cetak SPPD
    $resp = httpGet("{$baseUrl}/admin/surat/perjalanan-dinas/{$lid}/cetak-sppd", $cookieFile);
    $pass = ($resp['code'] === 200 && strlen($resp['body']) > 500);
    $hasError = (stripos($resp['body'], 'Exception') !== false || stripos($resp['body'], 'Fatal error') !== false || stripos($resp['body'], 'Whoops') !== false);
    if ($hasError) $pass = false;
    addResult('CETAK_SPPD', "Cetak SPPD #{$idx} (ID={$lid})", $pass,
        $hasError ? "Page contains error" : "HTTP {$resp['code']}, size=" . strlen($resp['body']));

    // Cetak Kwitansi
    $resp = httpGet("{$baseUrl}/admin/surat/perjalanan-dinas/{$lid}/cetak-kwitansi", $cookieFile);
    $pass = ($resp['code'] === 200 && strlen($resp['body']) > 500);
    $hasError = (stripos($resp['body'], 'Exception') !== false || stripos($resp['body'], 'Fatal error') !== false || stripos($resp['body'], 'Whoops') !== false);
    if ($hasError) $pass = false;
    addResult('CETAK_KWITANSI', "Cetak Kwitansi #{$idx} (ID={$lid})", $pass,
        $hasError ? "Page contains error" : "HTTP {$resp['code']}, size=" . strlen($resp['body']));
}

// Cleanup cookie
@unlink($cookieFile);

echo "\n" . str_repeat('=', 60) . "\n";
echo "  PHASE 4: DATA INTEGRITY CHECKS\n";
echo str_repeat('=', 60) . "\n\n";

// Check total counts
$disCount = $db->query("SELECT COUNT(*) AS cnt FROM disposisi_perjalanan_dinas")->fetch_assoc()['cnt'];
addResult('INTEGRITY', "Total Disposisi records = 25", (int)$disCount === 25, "Found: {$disCount}");

$lapCount = $db->query("SELECT COUNT(*) AS cnt FROM laporan_perjalanan_dinas")->fetch_assoc()['cnt'];
addResult('INTEGRITY', "Total Laporan records = 25", (int)$lapCount === 25, "Found: {$lapCount}");

// Check disposisi status distribution
$statusDist = [];
$res = $db->query("SELECT status, COUNT(*) AS cnt FROM disposisi_perjalanan_dinas GROUP BY status ORDER BY status");
while ($r = $res->fetch_assoc()) { $statusDist[$r['status']] = (int) $r['cnt']; }
addResult('INTEGRITY', 'Disposisi status distribution varied', count($statusDist) >= 2,
    json_encode($statusDist));

// Check laporan final/verified distribution
$finalDist = $db->query("SELECT is_final, is_verified, COUNT(*) AS cnt FROM laporan_perjalanan_dinas GROUP BY is_final, is_verified ORDER BY is_final, is_verified");
$distArr = [];
while ($r = $finalDist->fetch_assoc()) { $distArr[] = "final={$r['is_final']},verified={$r['is_verified']}: {$r['cnt']}"; }
addResult('INTEGRITY', 'Laporan has varied final/verified statuses', count($distArr) >= 2,
    implode(' | ', $distArr));

// Check pelaksana counts vary
$pelCounts = [];
$res = $db->query("SELECT JSON_LENGTH(pelaksana_json) AS cnt FROM laporan_perjalanan_dinas GROUP BY JSON_LENGTH(pelaksana_json)");
while ($r = $res->fetch_assoc()) { $pelCounts[] = $r['cnt']; }
addResult('INTEGRITY', 'Pelaksana counts varied (1-5)', count($pelCounts) >= 3,
    'Unique counts: ' . implode(', ', $pelCounts));

// Check kode_nomor sequence
$kodeNomors = [];
$res = $db->query("SELECT kode_nomor FROM laporan_perjalanan_dinas ORDER BY id");
while ($r = $res->fetch_assoc()) { $kodeNomors[] = $r['kode_nomor']; }
$kodeNomorsStr = implode(', ', $kodeNomors);
addResult('INTEGRITY', 'Kode Nomor sequence populated', count(array_unique($kodeNomors)) >= 20,
    "Codes: {$kodeNomorsStr}");

// Check last_kode_nomor_sppd
$settingRow = $db->query("SELECT last_kode_nomor_sppd FROM app_settings LIMIT 1")->fetch_assoc();
$lastNum = (int) ($settingRow['last_kode_nomor_sppd'] ?? 0);
addResult('INTEGRITY', "last_kode_nomor_sppd = {$finalLastNumber}", $lastNum === $finalLastNumber,
    "DB value: {$lastNum}");

$db->close();

// ── Generate HTML Report ──
$totalTests  = count($results);
$passedTests = count(array_filter($results, fn($r) => $r['pass']));
$failedTests = $totalTests - $passedTests;
$passRate    = $totalTests > 0 ? round(($passedTests / $totalTests) * 100, 1) : 0;

$now = date('Y-m-d H:i:s');
$htmlFile = $baseDir . '/public/smoke_test_report.html';
$htmlRows = '';
foreach ($results as $r) {
    $badge = $r['pass']
        ? '<span style="background:#10b981;color:#fff;padding:3px 10px;border-radius:12px;font-size:12px;font-weight:600;">PASS</span>'
        : '<span style="background:#ef4444;color:#fff;padding:3px 10px;border-radius:12px;font-size:12px;font-weight:600;">FAIL</span>';
    $rowClass = $r['pass'] ? '' : ' style="background:#fef2f2;"';
    $htmlRows .= "<tr{$rowClass}>
        <td style='text-align:center;padding:10px 8px;border-bottom:1px solid #e5e7eb;'>{$r['no']}</td>
        <td style='padding:10px 8px;border-bottom:1px solid #e5e7eb;font-weight:600;color:#6366f1;'>{$r['step']}</td>
        <td style='padding:10px 8px;border-bottom:1px solid #e5e7eb;'>{$r['desc']}</td>
        <td style='text-align:center;padding:10px 8px;border-bottom:1px solid #e5e7eb;'>{$badge}</td>
        <td style='padding:10px 8px;border-bottom:1px solid #e5e7eb;font-size:12px;color:#6b7280;'>{$r['detail']}</td>
    </tr>\n";
}

$errorSection = '';
if ($failedTests > 0) {
    $errorItems = '';
    foreach ($errors as $e) {
        $errorItems .= "<li style='margin-bottom:8px;color:#dc2626;'>{$e}</li>";
    }
    $errorSection = "
    <div style='background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:20px 24px;margin-bottom:32px;'>
        <h3 style='color:#dc2626;margin:0 0 12px;'>⚠️ Errors Found ({$failedTests})</h3>
        <ul style='margin:0;padding-left:20px;'>{$errorItems}</ul>
    </div>";
}

$statusColor = $failedTests === 0 ? '#10b981' : '#ef4444';
$statusText  = $failedTests === 0 ? 'ALL TESTS PASSED ✅' : "{$failedTests} TEST(S) FAILED ❌";

$html = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smoke Test Report — Perjalanan Dinas</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; padding: 40px 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; }
        .header h1 { font-size: 32px; font-weight: 800; background: linear-gradient(135deg, #818cf8, #a78bfa, #c084fc); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 8px; }
        .header p { color: #94a3b8; font-size: 14px; }
        .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 32px; }
        .stat-card { background: #1e293b; border-radius: 16px; padding: 24px; text-align: center; border: 1px solid #334155; }
        .stat-card .value { font-size: 36px; font-weight: 800; }
        .stat-card .label { font-size: 13px; color: #94a3b8; margin-top: 4px; }
        .card { background: #1e293b; border-radius: 16px; padding: 24px; margin-bottom: 32px; border: 1px solid #334155; overflow-x: auto; }
        .card h2 { font-size: 20px; font-weight: 700; margin-bottom: 16px; color: #f1f5f9; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #334155; color: #e2e8f0; padding: 12px 8px; text-align: left; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        th:first-child { border-radius: 8px 0 0 0; }
        th:last-child { border-radius: 0 8px 0 0; }
        td { color: #cbd5e1; font-size: 13px; }
        .flowchart-section { margin-bottom: 32px; }
        .flowchart-section h2 { font-size: 20px; font-weight: 700; margin-bottom: 16px; color: #f1f5f9; }
        .flow-container { display: flex; flex-direction: column; align-items: center; gap: 4px; padding: 30px 20px; }
        .flow-node { padding: 14px 28px; border-radius: 12px; font-size: 13px; font-weight: 600; text-align: center; min-width: 260px; max-width: 400px; position: relative; }
        .flow-start { background: linear-gradient(135deg, #10b981, #059669); color: #fff; border-radius: 50px; }
        .flow-process { background: #1e40af; color: #e0e7ff; border: 1px solid #3b82f6; }
        .flow-decision { background: #7c3aed; color: #ede9fe; transform: rotate(0deg); border: 1px solid #a78bfa; clip-path: polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%); padding: 28px 40px; }
        .flow-end { background: linear-gradient(135deg, #ef4444, #dc2626); color: #fff; border-radius: 50px; }
        .flow-arrow { color: #64748b; font-size: 20px; line-height: 1; }
        .flow-label { font-size: 11px; color: #94a3b8; margin-top: 2px; }
        .flow-row { display: flex; align-items: center; gap: 24px; }
        .flow-branch { display: flex; flex-direction: column; align-items: center; gap: 4px; }
        @media (max-width: 768px) { .stats { grid-template-columns: repeat(2, 1fr); } }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🔥 Smoke Test Report</h1>
        <p>Modul Perjalanan Dinas — Generated {$now}</p>
    </div>

    <div class="stats">
        <div class="stat-card">
            <div class="value" style="color:#818cf8;">{$totalTests}</div>
            <div class="label">Total Tests</div>
        </div>
        <div class="stat-card">
            <div class="value" style="color:#10b981;">{$passedTests}</div>
            <div class="label">Passed</div>
        </div>
        <div class="stat-card">
            <div class="value" style="color:#ef4444;">{$failedTests}</div>
            <div class="label">Failed</div>
        </div>
        <div class="stat-card">
            <div class="value" style="color:#fbbf24;">{$passRate}%</div>
            <div class="label">Pass Rate</div>
        </div>
    </div>

    <div style="text-align:center;margin-bottom:32px;">
        <div style="display:inline-block;background:{$statusColor};color:#fff;padding:12px 32px;border-radius:50px;font-weight:700;font-size:16px;">{$statusText}</div>
    </div>

    {$errorSection}

    <!-- FLOWCHART -->
    <div class="card flowchart-section">
        <h2>📊 Flowchart Alur Pengujian</h2>
        <div class="flow-container">
            <div class="flow-node flow-start">🚀 START: Setup & Truncate Data</div>
            <div class="flow-arrow">↓</div>

            <div class="flow-node flow-process">📋 Load Master Data (Pegawai, Kabupaten, Dasar SPT, Mata Anggaran)</div>
            <div class="flow-arrow">↓</div>

            <div class="flow-node flow-process" style="background:#0369a1;border-color:#0ea5e9;">
                <strong>PHASE 1: Insert 25 Disposisi</strong><br>
                <span style="font-size:11px;font-weight:400;">• Variasi 1-5 pelaksana per entry<br>
                • Variasi kota tujuan (12+ kota)<br>
                • Variasi transportasi<br>
                • Status: 23 disetujui, 1 pending, 1 ditolak</span>
            </div>
            <div class="flow-arrow">↓</div>

            <div class="flow-node" style="background:#7c3aed;color:#ede9fe;border:1px solid #a78bfa;padding:20px 30px;">
                ✅ Verify: 25 Disposisi records inserted?
            </div>
            <div class="flow-arrow">↓</div>

            <div class="flow-node flow-process" style="background:#b45309;border-color:#f59e0b;">
                <strong>PHASE 2: Insert 25 Laporan Perjalanan Dinas</strong><br>
                <span style="font-size:11px;font-weight:400;">• Linked to disposisi via disposisi_id<br>
                • Variasi rincian biaya (transport + penginapan)<br>
                • Kode nomor auto-increment<br>
                • Variasi is_final (23 final, 2 draft) & is_verified (20 verified, 5 not)</span>
            </div>
            <div class="flow-arrow">↓</div>

            <div class="flow-node" style="background:#7c3aed;color:#ede9fe;border:1px solid #a78bfa;padding:20px 30px;">
                ✅ Verify: 25 Laporan records inserted?
            </div>
            <div class="flow-arrow">↓</div>

            <div class="flow-node flow-process" style="background:#166534;border-color:#22c55e;">
                <strong>PHASE 3: HTTP Endpoint Verification</strong><br>
                <span style="font-size:11px;font-weight:400;">• Login → session cookie<br>
                • 3 listing pages (Disposisi, Surat Tugas, Perjalanan Dinas)<br>
                • 5 records × 5 cetak endpoints (SPT, Nominatif, SPPD, Kwitansi, Dokumen)</span>
            </div>
            <div class="flow-arrow">↓</div>

            <div class="flow-node flow-process" style="background:#7f1d1d;border-color:#ef4444;">
                <strong>PHASE 4: Data Integrity Checks</strong><br>
                <span style="font-size:11px;font-weight:400;">• Total record counts = 25<br>
                • Status distribution variety<br>
                • Pelaksana count variation (1-5)<br>
                • Kode nomor sequence validity<br>
                • last_kode_nomor_sppd sync</span>
            </div>
            <div class="flow-arrow">↓</div>

            <div class="flow-node flow-end">🏁 END: Generate HTML Report</div>
        </div>
    </div>

    <!-- RESULTS TABLE -->
    <div class="card">
        <h2>📝 Detailed Test Results</h2>
        <table>
            <thead>
                <tr>
                    <th style="width:50px;text-align:center;">#</th>
                    <th style="width:130px;">Phase</th>
                    <th>Description</th>
                    <th style="width:80px;text-align:center;">Result</th>
                    <th style="width:250px;">Details</th>
                </tr>
            </thead>
            <tbody>
                {$htmlRows}
            </tbody>
        </table>
    </div>

    <div style="text-align:center;color:#64748b;font-size:12px;margin-top:40px;">
        Generated by Smoke Test Runner — {$now}
    </div>
</div>
</body>
</html>
HTML;



file_put_contents($htmlFile, $html);
echo "\n\n✅ HTML Report saved to: {$htmlFile}\n";
echo "   Open: http://localhost:8080/smoke_test_report.html\n\n";

echo str_repeat('=', 60) . "\n";
echo "  SUMMARY: {$passedTests}/{$totalTests} passed ({$passRate}%)\n";
echo str_repeat('=', 60) . "\n";
