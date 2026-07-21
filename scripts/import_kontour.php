<?php

/**
 * Import Kontur Shapefile ke Database MySQL
 *
 * Reads Shapefile (.shp/.dbf) from do_not_upload/ and saves
 * pre-processed GeoJSON per kabupaten to mst_kontur_geojson table.
 *
 * Usage: php scripts/import_kontour.php
 *
 * For detail level (5m), uses temp file streaming to avoid memory exhaustion.
 */

// ─── Bootstrap CodeIgniter ──────────────────────────────────────
$rootDir = dirname(__DIR__);
require_once $rootDir . '/vendor/autoload.php';

use CodeIgniter\Boot;
use Config\Paths;

define('FCPATH', $rootDir . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
chdir(FCPATH);
require FCPATH . '../app/Config/Paths.php';
$paths = new Paths();
require $paths->systemDirectory . '/Boot.php';
Boot::bootSpark($paths);

ini_set('memory_limit', '4G');
set_time_limit(0);

// ─── Configuration ──────────────────────────────────────────────
$sourceDir = $rootDir . '/do_not_upload';
$tempDir   = $rootDir . '/writable/contour_temp';
if (!is_dir($tempDir)) {
    mkdir($tempDir, 0777, true);
}

$levels = [
    'overview' => ['interval' => 50,  'tolerance' => 0.001],
    'medium'   => ['interval' => 25,  'tolerance' => 0.0003],
    'detail'   => ['interval' => 5,   'tolerance' => 0.00005],
];

// Max features per DB chunk for detail level
$detailChunkSize = 30000;

// ─── Shapefile Streaming Functions ──────────────────────────────

function openDbf(string $path): array
{
    $fh = fopen($path, 'rb');
    fread($fh, 1); fread($fh, 3);
    $numRecords = unpack('V', fread($fh, 4))[1];
    $headerSize = unpack('v', fread($fh, 2))[1];
    $recordSize = unpack('v', fread($fh, 2))[1];
    fread($fh, 20);

    $fields = [];
    for ($i = 0; $i < intdiv($headerSize - 33, 32); $i++) {
        $name = rtrim(fread($fh, 11), "\x00");
        fread($fh, 1); fread($fh, 4);
        $length = unpack('C', fread($fh, 1))[1];
        fread($fh, 15);
        $fields[] = ['name' => $name, 'length' => $length];
    }
    fseek($fh, $headerSize);
    return ['fh' => $fh, 'numRecords' => $numRecords, 'recordSize' => $recordSize, 'fields' => $fields];
}

function openShp(string $path): array
{
    $fh = fopen($path, 'rb');
    fread($fh, 4); fread($fh, 20);
    $fileLength = unpack('N', fread($fh, 4))[1] * 2;
    fread($fh, 72);
    return ['fh' => $fh, 'fileLength' => $fileLength, 'offset' => 100];
}

function readNext(array &$dbf, array &$shp): ?array
{
    $data = fread($dbf['fh'], $dbf['recordSize']);
    if ($data === false || strlen($data) < $dbf['recordSize']) return null;

    $contour = null;
    $off = 1;
    foreach ($dbf['fields'] as $f) {
        if ($f['name'] === 'Contour') $contour = (float) trim(substr($data, $off, $f['length']));
        $off += $f['length'];
    }

    if ($shp['offset'] >= $shp['fileLength']) return null;
    $hdr = fread($shp['fh'], 8);
    if (!$hdr || strlen($hdr) < 8) return null;
    $cLen = unpack('N', substr($hdr, 4, 4))[1] * 2;
    $shp['offset'] += 8;
    if ($cLen <= 0) return null;
    $content = fread($shp['fh'], $cLen);
    $shp['offset'] += $cLen;
    if (!$content || strlen($content) < 4) return null;

    $st = unpack('V', substr($content, 0, 4))[1];
    if ($st !== 3 && $st !== 13) return ['c' => $contour, 'g' => null];

    $nParts  = unpack('V', substr($content, 36, 4))[1];
    $nPoints = unpack('V', substr($content, 40, 4))[1];
    $parts = [];
    for ($p = 0; $p < $nParts; $p++) $parts[] = unpack('V', substr($content, 44 + $p * 4, 4))[1];

    $po = 44 + $nParts * 4;
    $coords = [];
    for ($i = 0; $i < $nPoints; $i++) {
        $x = unpack('d', substr($content, $po + $i * 16, 8))[1];
        $y = unpack('d', substr($content, $po + $i * 16 + 8, 8))[1];
        $coords[] = [round($x, 5), round($y, 5)];
    }

    $ml = [];
    for ($p = 0; $p < $nParts; $p++) {
        $s = $parts[$p];
        $e = ($p + 1 < $nParts) ? $parts[$p + 1] : $nPoints;
        $ml[] = array_slice($coords, $s, $e - $s);
    }

    $g = ($nParts === 1) ? ['type' => 'LineString', 'coordinates' => $ml[0]]
                         : ['type' => 'MultiLineString', 'coordinates' => $ml];
    return ['c' => $contour, 'g' => $g];
}

// ─── Douglas-Peucker ───────────────────────────────────────────

function dp(array $pts, float $eps): array
{
    if (count($pts) <= 2) return $pts;
    $dmax = 0; $idx = 0; $end = count($pts) - 1;
    $ax = $pts[0][0]; $ay = $pts[0][1];
    $bx = $pts[$end][0]; $by = $pts[$end][1];
    $dx = $bx - $ax; $dy = $by - $ay;
    $lenSq = $dx * $dx + $dy * $dy;
    for ($i = 1; $i < $end; $i++) {
        if ($lenSq == 0) {
            $d = sqrt(($pts[$i][0] - $ax) ** 2 + ($pts[$i][1] - $ay) ** 2);
        } else {
            $t = max(0, min(1, (($pts[$i][0] - $ax) * $dx + ($pts[$i][1] - $ay) * $dy) / $lenSq));
            $d = sqrt(($pts[$i][0] - ($ax + $t * $dx)) ** 2 + ($pts[$i][1] - ($ay + $t * $dy)) ** 2);
        }
        if ($d > $dmax) { $idx = $i; $dmax = $d; }
    }
    if ($dmax > $eps) {
        $l = dp(array_slice($pts, 0, $idx + 1), $eps);
        $r = dp(array_slice($pts, $idx), $eps);
        array_pop($l);
        return array_merge($l, $r);
    }
    return [$pts[0], $pts[$end]];
}

function simplify(array $g, float $t): array
{
    if ($t <= 0) return $g;
    if ($g['type'] === 'LineString') {
        $s = dp($g['coordinates'], $t);
        if (count($s) >= 2) $g['coordinates'] = $s;
    } else {
        $nc = [];
        foreach ($g['coordinates'] as $ln) { $s = dp($ln, $t); if (count($s) >= 2) $nc[] = $s; }
        if (!empty($nc)) $g['coordinates'] = $nc;
    }
    return $g;
}

function ptCnt(array $g): int
{
    if ($g['type'] === 'LineString') return count($g['coordinates']);
    $c = 0; foreach ($g['coordinates'] as $l) $c += count($l); return $c;
}

function gBBox(array $g): array
{
    $r = [PHP_FLOAT_MAX, PHP_FLOAT_MAX, -PHP_FLOAT_MAX, -PHP_FLOAT_MAX];
    $cl = ($g['type'] === 'MultiLineString') ? $g['coordinates'] : [$g['coordinates']];
    foreach ($cl as $ln) foreach ($ln as $pt) {
        $r[0] = min($r[0], $pt[1]); $r[1] = min($r[1], $pt[0]);
        $r[2] = max($r[2], $pt[1]); $r[3] = max($r[3], $pt[0]);
    }
    return $r;
}

function saveToDb($db, string $kab, string $level, int $interval, array $features, int $maxPkt): int
{
    $count = count($features);
    if ($count === 0) return 0;

    $bbox = [PHP_FLOAT_MAX, PHP_FLOAT_MAX, -PHP_FLOAT_MAX, -PHP_FLOAT_MAX];
    $minE = PHP_FLOAT_MAX; $maxE = -PHP_FLOAT_MAX;
    foreach ($features as $f) {
        $gb = gBBox($f['geometry']);
        $bbox[0] = min($bbox[0], $gb[0]); $bbox[1] = min($bbox[1], $gb[1]);
        $bbox[2] = max($bbox[2], $gb[2]); $bbox[3] = max($bbox[3], $gb[3]);
        $minE = min($minE, $f['properties']['Contour']);
        $maxE = max($maxE, $f['properties']['Contour']);
    }

    $json = json_encode(['type' => 'FeatureCollection', 'features' => $features], JSON_UNESCAPED_UNICODE);
    $size = strlen($json);

    if ($size > $maxPkt * 0.85) {
        echo "      ⚠️  Too large (" . number_format($size / 1048576, 1) . "MB), skipping\n";
        return 0;
    }

    try {
        $db->table('mst_kontur_geojson')->insert([
            'kabupaten' => $kab, 'detail_level' => $level, 'contour_interval' => $interval,
            'min_elevation' => $minE, 'max_elevation' => $maxE, 'feature_count' => $count,
            'bbox_min_lat' => $bbox[0], 'bbox_min_lng' => $bbox[1],
            'bbox_max_lat' => $bbox[2], 'bbox_max_lng' => $bbox[3],
            'geojson_data' => $json, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return 1;
    } catch (\Throwable $e) {
        echo "      ❌ " . $e->getMessage() . "\n";
        return 0;
    }
}

// ─── Main Process ───────────────────────────────────────────────

echo "=== Import Kontur Shapefile ke Database ===\n\n";
$db = db_connect();

$pktRes = $db->query("SHOW VARIABLES LIKE 'max_allowed_packet'")->getRowArray();
$maxPkt = (int) ($pktRes['Value'] ?? 0);
echo "MySQL max_allowed_packet: " . number_format($maxPkt / 1024 / 1024, 1) . " MB\n";

$shpFiles = glob($sourceDir . '/Kontur *.shp');
sort($shpFiles);
echo "Ditemukan " . count($shpFiles) . " file shapefile.\n\n";

$totalInserted = 0;

foreach ($shpFiles as $shpFile) {
    $baseName  = pathinfo($shpFile, PATHINFO_FILENAME);
    $kabupaten = str_replace('Kontur ', '', $baseName);
    $dbfFile   = $sourceDir . '/' . $baseName . '.dbf';
    if (!file_exists($dbfFile)) continue;

    // Check if already processed
    $existing = $db->table('mst_kontur_geojson')->where('kabupaten', $kabupaten)->countAllResults();
    if ($existing > 0) {
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "📂 $kabupaten (Sudah diproses, skip)\n";
        continue;
    }

    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📂 $kabupaten\n";

    foreach ($levels as $level => $cfg) {
        $interval  = $cfg['interval'];
        $tolerance = $cfg['tolerance'];

        echo "   📊 Level '$level' ({$interval}m)... ";
        $t0 = microtime(true);

        $dbfCtx = openDbf($dbfFile);
        $shpCtx = openShp($shpFile);
        $numRec = $dbfCtx['numRecords'];

        if ($level === 'detail') {
            // ── Detail level: stream to chunks ──────────────────
            $chunk    = [];
            $chunkNum = 0;
            $totalFeat = 0;

            for ($r = 0; $r < $numRec; $r++) {
                $rec = readNext($dbfCtx, $shpCtx);
                if ($rec === null) break;
                if ($rec['g'] === null) continue;
                if (fmod($rec['c'], $interval) != 0) continue;

                $geom = simplify($rec['g'], $tolerance);
                if (ptCnt($geom) < 2) continue;

                $chunk[] = [
                    'type' => 'Feature',
                    'properties' => ['Contour' => $rec['c']],
                    'geometry' => $geom,
                ];
                $totalFeat++;

                if (count($chunk) >= $detailChunkSize) {
                    $chunkNum++;
                    $saved = saveToDb($db, $kabupaten, $level, $interval, $chunk, $maxPkt);
                    $totalInserted += $saved;
                    if ($saved) echo "      💾 Chunk $chunkNum (" . count($chunk) . " feat)\n";
                    $chunk = [];
                    gc_collect_cycles();
                }
            }

            // Save remaining
            if (!empty($chunk)) {
                $chunkNum++;
                $saved = saveToDb($db, $kabupaten, $level, $interval, $chunk, $maxPkt);
                $totalInserted += $saved;
                if ($saved) echo "      💾 Chunk $chunkNum (" . count($chunk) . " feat)\n";
                $chunk = [];
            }

            fclose($dbfCtx['fh']); fclose($shpCtx['fh']);
            $elapsed = microtime(true) - $t0;
            echo "   ✓ detail: $totalFeat feat total, $chunkNum chunks (" . number_format($elapsed, 1) . "s)\n";

        } else {
            // ── Overview/Medium: collect all then save ──────────
            $features = [];
            for ($r = 0; $r < $numRec; $r++) {
                $rec = readNext($dbfCtx, $shpCtx);
                if ($rec === null) break;
                if ($rec['g'] === null) continue;
                if (fmod($rec['c'], $interval) != 0) continue;
                $geom = simplify($rec['g'], $tolerance);
                if (ptCnt($geom) < 2) continue;
                $features[] = [
                    'type' => 'Feature',
                    'properties' => ['Contour' => $rec['c']],
                    'geometry' => $geom,
                ];
            }

            fclose($dbfCtx['fh']); fclose($shpCtx['fh']);
            $count = count($features);
            $elapsed = microtime(true) - $t0;

            if ($count === 0) {
                echo "kosong (" . number_format($elapsed, 1) . "s)\n";
            } else {
                $saved = saveToDb($db, $kabupaten, $level, $interval, $features, $maxPkt);
                $totalInserted += $saved;

                $json = json_encode(['type' => 'FeatureCollection', 'features' => $features], JSON_UNESCAPED_UNICODE);
                $sizeStr = strlen($json) >= 1048576 ? number_format(strlen($json) / 1048576, 1) . 'MB' : number_format(strlen($json) / 1024, 0) . 'KB';
                echo "$count feat, $sizeStr (" . number_format($elapsed, 1) . "s)\n";
                if ($saved) echo "      💾 Tersimpan\n";
                unset($json);
            }
            unset($features);
        }

        gc_collect_cycles();
    }

    $mem = round(memory_get_usage(true) / 1024 / 1024);
    echo "   ✅ Selesai (RAM: {$mem}MB)\n\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🎉 Import selesai! Total $totalInserted records disimpan.\n\n";

$summary = $db->table('mst_kontur_geojson')
    ->select('kabupaten, detail_level, contour_interval, feature_count, LENGTH(geojson_data) as data_size')
    ->orderBy('kabupaten', 'ASC')
    ->orderBy('contour_interval', 'DESC')
    ->get()->getResultArray();

echo str_pad('Kabupaten', 20) . str_pad('Level', 10) . str_pad('Int', 6) . str_pad('Features', 12) . "Ukuran\n";
echo str_repeat('─', 60) . "\n";
foreach ($summary as $row) {
    $sz = (int) $row['data_size'];
    echo str_pad($row['kabupaten'], 20) . str_pad($row['detail_level'], 10)
        . str_pad($row['contour_interval'] . 'm', 6) . str_pad(number_format($row['feature_count']), 12)
        . ($sz >= 1048576 ? number_format($sz / 1048576, 1) . ' MB' : number_format($sz / 1024, 0) . ' KB') . "\n";
}
echo "\nSelesai ✓\n";
