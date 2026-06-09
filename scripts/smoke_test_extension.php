<?php
/**
 * SIMAK Smoke Test - Extension / Top-Up
 *
 * Continues from existing contracts created by the single smoke test.
 * - Konstruksi ID: 9
 * - Konsultasi ID: 2
 *
 * Calculates real denominator from live percentages, then adds enough
 * rows to push Lengkap > 20% and Belum Sesuai > 5%.
 */

define('BASE_URL', 'https://satkerpps-riau.online');
define('TIMEOUT', 60);
define('ADMIN_USERNAME', '199011092025061005');
define('ADMIN_PASSWORD', '123456');

$cookiesFile = __DIR__ . '/../writable/smoke_test_ext_cookies.txt';
if (file_exists($cookiesFile)) unlink($cookiesFile);

$dummyFile = __DIR__ . '/../writable/dummy_smoke_single.pdf';
if (!file_exists($dummyFile)) {
    file_put_contents($dummyFile, '%PDF-1.4 Dummy smoke test file content.');
}

// -------------------------------------------------------
// Helpers
// -------------------------------------------------------
function queryDb(string $sql, array $params = [], string $types = '') {
    $db = new mysqli('satkerpps-riau.online', 'agun9011_satkerpps', '9w:wxJn|K', 'agun9011_satkerpps');
    if ($db->connect_error) throw new Exception("DB: " . $db->connect_error);
    if (empty($params)) {
        $res = $db->query($sql);
        if ($res === false) { $e=$db->error; $db->close(); throw new Exception("SQL: $e\n$sql"); }
        if ($res === true)  { $db->close(); return true; }
        $data=[];
        while($row=$res->fetch_assoc()) $data[]=$row;
        $res->free(); $db->close(); return $data;
    } else {
        $stmt=$db->prepare($sql);
        if(!$stmt){$e=$db->error;$db->close();throw new Exception("Prep: $e");}
        $stmt->bind_param($types,...$params);
        $stmt->execute();
        if($stmt->error){$e=$stmt->error;$stmt->close();$db->close();throw new Exception("Exec: $e");}
        $res=$stmt->get_result();
        if($res){$data=[];while($row=$res->fetch_assoc())$data[]=$row;$res->free();$stmt->close();$db->close();return $data;}
        $stmt->close();$db->close();return true;
    }
}

function getCsrf(): string {
    global $cookiesFile;
    if (!file_exists($cookiesFile)) return '';
    $c = file_get_contents($cookiesFile);
    return preg_match('/csrf_cookie_name\s+([a-f0-9]+)/', $c, $m) ? $m[1] : '';
}

function req(string $url, string $method='GET', $fields=null): array {
    global $cookiesFile;
    $ch=curl_init();
    curl_setopt_array($ch,[
        CURLOPT_URL=>$url, CURLOPT_RETURNTRANSFER=>true, CURLOPT_FOLLOWLOCATION=>true,
        CURLOPT_TIMEOUT=>TIMEOUT, CURLOPT_COOKIEFILE=>$cookiesFile, CURLOPT_COOKIEJAR=>$cookiesFile,
        CURLOPT_USERAGENT=>'Mozilla/5.0 SmokeExt/1.0', CURLOPT_SSL_VERIFYPEER=>false,
        CURLOPT_SSL_VERIFYHOST=>0, CURLOPT_HEADER=>true,
    ]);
    if($method==='POST'){curl_setopt($ch,CURLOPT_POST,true);if($fields!==null)curl_setopt($ch,CURLOPT_POSTFIELDS,$fields);}
    $r=curl_exec($ch);
    if($r===false){$e=curl_error($ch);curl_close($ch);throw new Exception("CURL: $e");}
    $hs=curl_getinfo($ch,CURLINFO_HEADER_SIZE);
    $code=curl_getinfo($ch,CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code'=>$code,'body'=>substr($r,$hs),'headers'=>substr($r,0,$hs)];
}

function uploadShare(string $token, int $rowNo, string $type, string $method='file'): void {
    global $dummyFile;
    $csrf=getCsrf();
    $url=BASE_URL."/simak/share/{$token}/upload";
    $fields=['row_no'=>$rowNo,'tipe_dokumen'=>$type,'upload_method'=>$method,'csrf_test_name'=>$csrf];
    if($method==='file'){
        $fields['dokumen_file']=new CURLFile($dummyFile,'application/pdf','dummy_smoke.pdf');
        req($url,'POST',$fields);
    } else {
        $fields['keterangan']='Dokumen memang tidak ada';
        req($url,'POST',http_build_query($fields));
    }
}

function verifyDoc(string $cat, int $simakId, int $rowNo, string $type, string $kel, string $ver): void {
    $csrf=getCsrf();
    $url=BASE_URL."/admin/kontrak/simak/".strtolower($cat)."/{$simakId}/verifikasi/upload";
    req($url,'POST',http_build_query([
        'row_no'=>$rowNo,'tipe_dokumen'=>$type,'kelengkapan_dokumen'=>$kel,
        'verifikasi_ki'=>$ver,'keterangan'=>"Ext smoke $ver",'pic'=>'Smoke Ext Script','csrf_test_name'=>$csrf,
    ]));
}

function buildTree(array $rows): array {
    $map=[];
    foreach($rows as $r){$r['children']=[];$map[(int)$r['id']]=$r;}
    $roots=[];
    foreach($map as $id=>$r){
        $pid=(int)($r['parent_id']??0);
        if($pid>0&&isset($map[$pid])){$map[$pid]['children'][]=&$map[$id];continue;}
        $roots[]=&$map[$id];
    }
    return $roots;
}

function getLeafRows(string $table): array {
    $rows=queryDb("SELECT id,parent_id,row_no,has_question,has_draft,is_hidden_share,ordering FROM $table WHERE is_active=1 ORDER BY ordering ASC,id ASC");
    if(empty($rows)) return [];
    $tree=buildTree($rows);
    $leafs=[];
    $walk=function(array $items) use(&$walk,&$leafs){
        foreach($items as $item){
            if((int)($item['is_hidden_share']??0)===1) continue;
            $children=$item['children']??[];
            if((int)($item['has_question']??0)===1&&empty($children))
                $leafs[]=['row_no'=>(int)$item['row_no'],'has_draft'=>(bool)$item['has_draft']];
            if(!empty($children)) $walk($children);
        }
    };
    $walk($tree);
    return $leafs;
}

function getAlreadyVerifiedRows(string $table, int $simakId): array {
    // Returns all row_nos that already have a verified entry
    $rows = queryDb("SELECT DISTINCT row_no FROM $table WHERE simak_id = ?", [$simakId], 'i');
    return array_column($rows, 'row_no');
}

function getStatsFromPage(string $url): array {
    $res = req($url);
    $lengkap = 0.0; $bs = 0.0;
    if (preg_match_all('/<div class="kelengkapan-value">([\d,.]+)%<\/div>/i', $res['body'], $m)) {
        $lengkap = (float)str_replace(',', '.', $m[1][0] ?? '0');
        $bs      = (float)str_replace(',', '.', $m[1][1] ?? '0');
    }
    return ['lengkap' => $lengkap, 'bs' => $bs];
}

// -------------------------------------------------------
// MAIN
// -------------------------------------------------------
echo "====================================================\n";
echo "   SIMAK SMOKE TEST - EXTENSION (Top-Up)\n";
echo "====================================================\n\n";

// Login
echo "[1] Logging in...\n";
req(BASE_URL.'/masuk','GET');
$csrf=getCsrf();
$r=req(BASE_URL.'/masuk','POST',http_build_query(['username'=>ADMIN_USERNAME,'password'=>ADMIN_PASSWORD,'csrf_test_name'=>$csrf]));
if(strpos($r['body'],'id="username"')!==false){echo "[-] Login FAILED.\n";exit(1);}
echo "[+] Logged in.\n\n";

// Get all leaf rows
$konLeaves  = getLeafRows('mst_simak_konstruksi_item');
$konsLeaves = getLeafRows('mst_simak_konsultasi_item');
$konFinalOnly  = array_values(array_filter($konLeaves,  fn($r)=>!$r['has_draft']));
$konsFinalOnly = array_values(array_filter($konsLeaves, fn($r)=>!$r['has_draft']));

// Existing IDs
$konId  = 9;
$konsId = 2;

// Get share tokens
$dbShare = queryDb("SELECT share_token FROM trn_kontrak_simak_share WHERE simak_id=? AND is_active=1 LIMIT 1",[$konId],'i');
$konToken = $dbShare[0]['share_token'] ?? '';
$dbShare2 = queryDb("SELECT share_token FROM trn_kontrak_simak_konsultasi_share WHERE simak_id=? AND is_active=1 LIMIT 1",[$konsId],'i');
$konsToken = $dbShare2[0]['share_token'] ?? '';

echo "[+] Konstruksi ID: $konId, Token: ".substr($konToken,0,16)."...\n";
echo "[+] Konsultasi  ID: $konsId, Token: ".substr($konsToken,0,16)."...\n\n";

// ====================================================
// KONSTRUKSI - check current stats and top up
// ====================================================
echo "===== [KONSTRUKSI] TOP-UP =====\n";
$konStats = getStatsFromPage(BASE_URL."/admin/kontrak/simak/konstruksi/{$konId}");
echo "[+] Current stats: Lengkap={$konStats['lengkap']}%, Belum Sesuai={$konStats['bs']}%\n";

// Calculate denominator from live stats (31 sesuai / lengkap% = total)
$konDenom = ($konStats['lengkap'] > 0) ? round(31 / ($konStats['lengkap'] / 100)) : 425;
echo "[+] Estimated denominator: $konDenom\n";

// Calculate how many more rows we need
$konNeedLengkap = max(0, (int)ceil($konDenom * 0.21) - 31); // already have 31 sesuai
$konNeedBS      = max(0, (int)ceil($konDenom * 0.06) - 9);  // already have 9 tidak_sesuai

echo "[+] Need $konNeedLengkap more LENGKAP rows and $konNeedBS more BELUM SESUAI rows\n";

// Get already used row numbers
$alreadyUsed = getAlreadyVerifiedRows('trn_kontrak_simak_verifikasi_dokumen', $konId);
echo "[+] Already verified ".count($alreadyUsed)." rows.\n";

// Get remaining available final-only rows
$remainRows = array_values(array_filter($konFinalOnly, fn($r) => !in_array($r['row_no'], $alreadyUsed)));
echo "[+] ".count($remainRows)." rows still available.\n\n";

// Upload LENGKAP (sesuai) rows
if ($konNeedLengkap > 0) {
    $lenRows = array_slice($remainRows, 0, $konNeedLengkap);
    echo "[2] Uploading $konNeedLengkap more LENGKAP rows...\n";
    foreach ($lenRows as $idx => $r) {
        $method = ($idx % 3 === 2) ? 'none' : 'file';
        $kel    = ($method === 'none') ? 'tidak' : 'ada';
        uploadShare($konToken, $r['row_no'], 'final', $method);
        verifyDoc('konstruksi', $konId, $r['row_no'], 'final', $kel, 'sesuai');
        echo "  [+] Row {$r['row_no']} -> Sesuai ({$method})\n";
    }
    $remainRows = array_slice($remainRows, $konNeedLengkap);
}

// Upload BELUM SESUAI (tidak_sesuai) rows
if ($konNeedBS > 0) {
    $bsRows = array_slice($remainRows, 0, $konNeedBS);
    echo "[3] Uploading $konNeedBS more BELUM SESUAI rows...\n";
    foreach ($bsRows as $idx => $r) {
        $method = ($idx % 2 === 0) ? 'file' : 'none';
        $kel    = ($method === 'none') ? 'tidak' : 'ada';
        uploadShare($konToken, $r['row_no'], 'final', $method);
        verifyDoc('konstruksi', $konId, $r['row_no'], 'final', $kel, 'tidak_sesuai');
        echo "  [+] Row {$r['row_no']} -> Tidak Sesuai ({$method})\n";
    }
}

// Verify final stats
echo "[4] Checking final Konstruksi stats...\n";
$konFinal = getStatsFromPage(BASE_URL."/admin/kontrak/simak/konstruksi/{$konId}");
echo "[+] FINAL: Lengkap={$konFinal['lengkap']}%, Belum Sesuai={$konFinal['bs']}%\n";
if ($konFinal['lengkap'] > 20.0 && $konFinal['bs'] > 5.0) {
    echo "[SUCCESS] Konstruksi targets MET!\n\n";
} else {
    echo "[WARNING] Still not met. Consider running again.\n\n";
}

// ====================================================
// KONSULTASI - check current stats and top up
// ====================================================
echo "===== [KONSULTASI] TOP-UP =====\n";
$konsStats = getStatsFromPage(BASE_URL."/admin/kontrak/simak/konsultasi/{$konsId}");
echo "[+] Current stats: Lengkap={$konsStats['lengkap']}%, Belum Sesuai={$konsStats['bs']}%\n";

$konsDenom = ($konsStats['lengkap'] > 0) ? round(17 / ($konsStats['lengkap'] / 100)) : 258;
echo "[+] Estimated denominator: $konsDenom\n";

$konsNeedLengkap = max(0, (int)ceil($konsDenom * 0.21) - 17);
$konsNeedBS      = max(0, (int)ceil($konsDenom * 0.06) - 5);

echo "[+] Need $konsNeedLengkap more LENGKAP rows and $konsNeedBS more BELUM SESUAI rows\n";

$alreadyUsedKons = getAlreadyVerifiedRows('trn_kontrak_simak_konsultasi_verifikasi_dokumen', $konsId);
echo "[+] Already verified ".count($alreadyUsedKons)." rows.\n";

$remainKonsRows = array_values(array_filter($konsFinalOnly, fn($r) => !in_array($r['row_no'], $alreadyUsedKons)));
echo "[+] ".count($remainKonsRows)." rows still available.\n\n";

if ($konsNeedLengkap > 0) {
    $lenRows = array_slice($remainKonsRows, 0, $konsNeedLengkap);
    echo "[5] Uploading $konsNeedLengkap more LENGKAP rows...\n";
    foreach ($lenRows as $idx => $r) {
        $method = ($idx % 3 === 2) ? 'none' : 'file';
        $kel    = ($method === 'none') ? 'tidak' : 'ada';
        uploadShare($konsToken, $r['row_no'], 'final', $method);
        verifyDoc('konsultasi', $konsId, $r['row_no'], 'final', $kel, 'sesuai');
        echo "  [+] Row {$r['row_no']} -> Sesuai ({$method})\n";
    }
    $remainKonsRows = array_slice($remainKonsRows, $konsNeedLengkap);
}

if ($konsNeedBS > 0) {
    $bsRows = array_slice($remainKonsRows, 0, $konsNeedBS);
    echo "[6] Uploading $konsNeedBS more BELUM SESUAI rows...\n";
    foreach ($bsRows as $idx => $r) {
        $method = ($idx % 2 === 0) ? 'file' : 'none';
        $kel    = ($method === 'none') ? 'tidak' : 'ada';
        uploadShare($konsToken, $r['row_no'], 'final', $method);
        verifyDoc('konsultasi', $konsId, $r['row_no'], 'final', $kel, 'tidak_sesuai');
        echo "  [+] Row {$r['row_no']} -> Tidak Sesuai ({$method})\n";
    }
}

echo "[7] Checking final Konsultasi stats...\n";
$konsFinal = getStatsFromPage(BASE_URL."/admin/kontrak/simak/konsultasi/{$konsId}");
echo "[+] FINAL: Lengkap={$konsFinal['lengkap']}%, Belum Sesuai={$konsFinal['bs']}%\n";
if ($konsFinal['lengkap'] > 20.0 && $konsFinal['bs'] > 5.0) {
    echo "[SUCCESS] Konsultasi targets MET!\n\n";
} else {
    echo "[WARNING] Still not met.\n\n";
}

// ====================================================
// SUMMARY & DELETE SQL
// ====================================================
echo "====================================================\n";
echo "   FINAL SUMMARY\n";
echo "====================================================\n";
echo "Konstruksi (ID {$konId}): Lengkap={$konFinal['lengkap'] }% (>20%?), BS={$konFinal['bs']}% (>5%?)\n";
echo "Konsultasi (ID {$konsId}): Lengkap={$konsFinal['lengkap']}% (>20%?), BS={$konsFinal['bs']}% (>5%?)\n\n";

$konNomor  = 'SMOKE/KON/1/20260609171843';
$konsNomor = 'SMOKE/KONS/1/20260609171843';

$sql = <<<SQL
-- ============================================================
-- DELETE SMOKE TEST DATA
-- Konstruksi ID: {$konId}   | Nomor: {$konNomor}
-- Konsultasi ID: {$konsId}  | Nomor: {$konsNomor}
-- ============================================================

-- 1. Delete Konstruksi verification documents
DELETE FROM trn_kontrak_simak_verifikasi_dokumen
WHERE simak_id = {$konId};

-- 2. Delete Konstruksi share links
DELETE FROM trn_kontrak_simak_share
WHERE simak_id = {$konId};

-- 3. Delete Konstruksi contract
DELETE FROM trn_kontrak_simak
WHERE id = {$konId}
  AND nomor_kontrak = '{$konNomor}';

-- 4. Delete Konsultasi verification documents
DELETE FROM trn_kontrak_simak_konsultasi_verifikasi_dokumen
WHERE simak_id = {$konsId};

-- 5. Delete Konsultasi share links
DELETE FROM trn_kontrak_simak_konsultasi_share
WHERE simak_id = {$konsId};

-- 6. Delete Konsultasi contract
DELETE FROM trn_kontrak_simak_konsultasi
WHERE id = {$konsId}
  AND nomor_kontrak = '{$konsNomor}';

-- Verify cleanup:
SELECT 'trn_kontrak_simak' AS tbl, COUNT(*) AS remaining
  FROM trn_kontrak_simak WHERE nomor_kontrak = '{$konNomor}'
UNION ALL
SELECT 'trn_kontrak_simak_konsultasi', COUNT(*)
  FROM trn_kontrak_simak_konsultasi WHERE nomor_kontrak = '{$konsNomor}';

SQL;

echo "====================================================\n";
echo "   SQL TO DELETE SMOKE TEST DATA\n";
echo "====================================================\n\n";
echo $sql;

$sqlFile = __DIR__ . "/../writable/smoke_test_delete_final.sql";
file_put_contents($sqlFile, $sql);
echo "\n[+] SQL saved to: $sqlFile\n";
