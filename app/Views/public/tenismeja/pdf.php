<?php
/**
 * View PDF Export Bagan Pertandingan Tenis Meja
 * Format: A4 Landscape
 * 
 * Sesuai User Rules & Requirements:
 * - Bagan Turnamen sistem gugur dengan garis alir orthogonal dan rapi
 * - Mendukung struktur 5 peserta (Babak I, Semi Final, Final) dengan Match Bye eksplisit
 * - Tanpa tanda tangan (sesuai instruksi user)
 * - Bebas karakter unicode tanda panah yang menyebabkan tanda tanya (?) di Dompdf
 */

$bracket      = $tournament['bracket_json'] ?? [];
$rounds       = $bracket['rounds'] ?? [];
$totalRounds  = (int) ($bracket['total_rounds'] ?? count($rounds));
$podium       = $bracket['podium'] ?? [];
$champion     = $podium['champion'] ?? null;
$runnerUp     = $podium['runner_up'] ?? null;
$numParticipants = (int) ($tournament['participant_count'] ?? 0);

// Helper function untuk render kartu pertandingan di PDF
if (! function_exists('renderPdfMatchBox')) {
    function renderPdfMatchBox($m, $isFinal = false) {
        if (! $m) {
            return '';
        }
        $p1 = $m['player1'] ?? null;
        $p2 = $m['player2'] ?? null;
        $isBye = ! empty($m['is_bye']);
        $w = $m['winner'] ?? null;
        
        $p1Win = $w && $p1 && (! empty($p1['name'])) && ($w['name'] === $p1['name']);
        $p2Win = $w && $p2 && (! empty($p2['name'])) && ($w['name'] === $p2['name']);

        $borderCol = $isFinal ? '#d97706' : ($isBye ? '#10b981' : '#0b3c74');
        $bgCol = $isFinal ? '#fffbeb' : ($isBye ? '#f0fdf4' : '#ffffff');
        $headerBg = $isFinal ? '#fef3c7' : ($isBye ? '#dcfce7' : '#0b3c74');
        $headerText = $isFinal ? '#92400e' : ($isBye ? '#166534' : '#ffffff');
        $borderStyle = $isBye ? 'border: 1.5px dashed ' . $borderCol : 'border: 1.5px solid ' . $borderCol;

        $html = '<div style="' . $borderStyle . '; background: ' . $bgCol . '; border-radius: 4px; font-size: 7.2pt; width: 220px; box-sizing: border-box; overflow: hidden;">';
        
        // Header Kartu
        $html .= '<div style="background: ' . $headerBg . '; color: ' . $headerText . '; padding: 2.5px 6px; font-weight: bold; font-size: 6.8pt;">';
        $html .= 'Match #' . esc($m['match_number']);
        if ($isFinal) {
            $html .= '<span style="float: right; color: #b45309; font-weight: bold;">FINAL</span>';
        } elseif ($isBye) {
            $html .= '<span style="float: right; color: #15803d; font-weight: bold;">MENANG BYE</span>';
        } elseif (! empty($m['next_match_id']) && str_contains($m['next_match_id'], 'R3')) {
            $html .= '<span style="float: right; color: #0284c7; font-weight: bold;">Lolos (Final)</span>';
        } elseif (! empty($m['next_match_id'])) {
            $html .= '<span style="float: right; color: #0b3c74; font-weight: bold;">Lolos (Semi Final)</span>';
        }
        $html .= '</div>';

        // Baris Peserta 1
        $p1Bg = $p1Win ? 'background: #d1fae5; font-weight: bold; color: #065f46;' : '';
        $html .= '<div style="padding: 2.5px 6px; border-bottom: 0.5px solid #e2e8f0; ' . $p1Bg . '">';
        $html .= esc($p1['name'] ?? '- Menunggu -');
        if ($m['score1'] !== null) {
            $html .= '<span style="float: right; font-weight: bold; background: #e2e8f0; color: #002244; padding: 0 4px; border-radius: 2px;">' . (int)$m['score1'] . '</span>';
        } elseif ($isBye && $p1) {
            $html .= '<span style="float: right; font-weight: bold; color: #166534;">BYE</span>';
        }
        if (! empty($p1['balai']) && ! empty($p1['name']) && $p1['name'] !== $p1['balai']) {
            $html .= '<br><span style="font-size: 6.2pt; color: #64748b; font-weight: normal;">' . esc($p1['balai']) . '</span>';
        }
        $html .= '</div>';

        // Baris Peserta 2
        $p2Bg = $p2Win ? 'background: #d1fae5; font-weight: bold; color: #065f46;' : '';
        $html .= '<div style="padding: 2.5px 6px; ' . $p2Bg . '">';
        if ($p2 && ! empty($p2['is_bye'])) {
            $html .= '<span style="color: #64748b; font-style: italic;">(BYE) - Lolos Otomatis</span>';
        } else {
            $html .= esc($p2['name'] ?? '- Menunggu -');
        }
        if ($m['score2'] !== null) {
            $html .= '<span style="float: right; font-weight: bold; background: #e2e8f0; color: #002244; padding: 0 4px; border-radius: 2px;">' . (int)$m['score2'] . '</span>';
        }
        if ($p2 && ! empty($p2['balai']) && empty($p2['is_bye']) && $p2['name'] !== $p2['balai']) {
            $html .= '<br><span style="font-size: 6.2pt; color: #64748b; font-weight: normal;">' . esc($p2['balai']) . '</span>';
        }
        $html .= '</div>';

        // Catatan Alur
        if (! empty($m['note'])) {
            $html .= '<div style="font-size: 6pt; color: #1e40af; padding: 2px 6px; background: rgba(0,0,0,0.02); border-top: 0.5px dashed #cbd5e1;">* ' . esc($m['note']) . '</div>';
        }

        $html .= '</div>';
        return $html;
    }
}

// Helper function untuk render kartu pertandingan 10 peserta (kompak) di PDF
if (! function_exists('renderPdfMatchBox10')) {
    function renderPdfMatchBox10($m, $isFinal = false, $isGrandFinal = false) {
        if (! $m) {
            return '';
        }
        $p1 = $m['player1'] ?? null;
        $p2 = $m['player2'] ?? null;
        $isBye = ! empty($m['is_bye']);
        $w = $m['winner'] ?? null;
        
        $p1Win = $w && $p1 && (! empty($p1['name'])) && ($w['name'] === $p1['name']);
        $p2Win = $w && $p2 && (! empty($p2['name'])) && ($w['name'] === $p2['name']);

        $borderCol = $isGrandFinal ? '#1e3a8a' : ($isFinal ? '#d97706' : ($isBye ? '#10b981' : '#0b3c74'));
        $bgCol = $isGrandFinal ? '#eff6ff' : ($isFinal ? '#fffbeb' : ($isBye ? '#f0fdf4' : '#ffffff'));
        $headerBg = $isGrandFinal ? '#1e3a8a' : ($isFinal ? '#fef3c7' : ($isBye ? '#dcfce7' : '#0b3c74'));
        $headerText = $isGrandFinal ? '#ffffff' : ($isFinal ? '#92400e' : ($isBye ? '#166534' : '#ffffff'));
        $borderStyle = $isBye ? 'border: 1px dashed ' . $borderCol : 'border: 1px solid ' . $borderCol;

        $html = '<div style="' . $borderStyle . '; background: ' . $bgCol . '; border-radius: 3px; font-size: 6.2pt; width: 165px; box-sizing: border-box; overflow: hidden;">';
        
        // Header Kartu
        $html .= '<div style="background: ' . $headerBg . '; color: ' . $headerText . '; padding: 1.5px 4px; font-weight: bold; font-size: 5.8pt;">';
        $html .= 'Match #' . esc($m['match_number']);
        if ($isGrandFinal) {
            $html .= '<span style="float: right; color: #facc15; font-weight: bold;">GRAND FINAL</span>';
        } elseif ($isFinal) {
            $html .= '<span style="float: right; color: #b45309; font-weight: bold;">FINAL BAGAN</span>';
        } elseif ($isBye) {
            $html .= '<span style="float: right; color: #15803d; font-weight: bold;">MENANG BYE</span>';
        }
        $html .= '</div>';

        // Baris Peserta 1
        $p1Bg = $p1Win ? 'background: #d1fae5; font-weight: bold; color: #065f46;' : '';
        $html .= '<div style="padding: 1.5px 4px; border-bottom: 0.5px solid #e2e8f0; ' . $p1Bg . '">';
        $html .= esc($p1['name'] ?? '- Menunggu -');
        if ($m['score1'] !== null) {
            $html .= '<span style="float: right; font-weight: bold; background: #e2e8f0; color: #002244; padding: 0 3px; border-radius: 2px;">' . (int)$m['score1'] . '</span>';
        } elseif ($isBye && $p1) {
            $html .= '<span style="float: right; font-weight: bold; color: #166534;">BYE</span>';
        }
        if (! empty($p1['balai']) && ! empty($p1['name']) && $p1['name'] !== $p1['balai']) {
            $html .= '<br><span style="font-size: 5.2pt; color: #64748b; font-weight: normal;">' . esc($p1['balai']) . '</span>';
        }
        $html .= '</div>';

        // Baris Peserta 2
        $p2Bg = $p2Win ? 'background: #d1fae5; font-weight: bold; color: #065f46;' : '';
        $html .= '<div style="padding: 1.5px 4px; ' . $p2Bg . '">';
        if ($p2 && ! empty($p2['is_bye'])) {
            $html .= '<span style="color: #64748b; font-style: italic;">(BYE) - Lolos Otomatis</span>';
        } else {
            $html .= esc($p2['name'] ?? '- Menunggu -');
        }
        if ($m['score2'] !== null) {
            $html .= '<span style="float: right; font-weight: bold; background: #e2e8f0; color: #002244; padding: 0 3px; border-radius: 2px;">' . (int)$m['score2'] . '</span>';
        }
        if ($p2 && ! empty($p2['balai']) && empty($p2['is_bye']) && $p2['name'] !== $p2['balai']) {
            $html .= '<br><span style="font-size: 5.2pt; color: #64748b; font-weight: normal;">' . esc($p2['balai']) . '</span>';
        }
        $html .= '</div>';

        $html .= '</div>';
        return $html;
    }
}

$lineCol = '#0b3c74';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title); ?></title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 12mm 8mm 12mm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8pt;
            color: #1e293b;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }

        /* KOP SURAT */
        .kop-table {
            width: 100%;
            border-bottom: 2px solid #002244;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }

        .kop-logo {
            width: 50px;
            text-align: center;
            vertical-align: middle;
        }

        .kop-logo img {
            width: 44px;
            height: auto;
        }

        .kop-text {
            text-align: center;
            vertical-align: middle;
            color: #002244;
        }

        .kop-text h2 {
            font-size: 9.5pt;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
        }

        .kop-text h1 {
            font-size: 11pt;
            font-weight: 800;
            margin: 1px 0;
            text-transform: uppercase;
            color: #0b3c74;
        }

        .kop-text p {
            font-size: 7pt;
            margin: 1px 0 0 0;
            color: #475569;
        }

        /* DOCUMENT TITLE */
        .doc-title-block {
            text-align: center;
            margin-bottom: 6px;
        }

        .doc-title-block h3 {
            font-size: 10pt;
            font-weight: 800;
            color: #002244;
            text-transform: uppercase;
            margin: 0;
        }

        .category-tag {
            display: inline-block;
            background-color: #0b3c74;
            color: #ffffff;
            font-weight: bold;
            padding: 1px 8px;
            border-radius: 10px;
            font-size: 7.5pt;
            margin-top: 2px;
        }

        .category-tag.putri {
            background-color: #be185d;
        }

        /* META TABLE */
        .meta-info-table {
            width: 100%;
            margin-bottom: 6px;
            border-collapse: collapse;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
        }

        .meta-info-table td {
            padding: 3px 6px;
            font-size: 7.5pt;
        }

        .meta-label {
            font-weight: bold;
            color: #475569;
            width: 15%;
        }

        .meta-val {
            font-weight: 600;
            color: #0f172a;
            width: 35%;
        }

        /* PODIUM BANNER IN PDF */
        .podium-pdf-table {
            width: 100%;
            margin-bottom: 8px;
            border-collapse: collapse;
        }

        .podium-cell {
            border: 1.5px solid #cbd5e1;
            padding: 4px 8px;
            text-align: center;
            border-radius: 4px;
        }

        .podium-cell.gold {
            background-color: #fef9c3;
            border-color: #eab308;
        }

        .podium-cell.silver {
            background-color: #f1f5f9;
            border-color: #94a3b8;
        }

        .podium-title {
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 1px;
        }

        .podium-name {
            font-size: 9pt;
            font-weight: 800;
            color: #0f172a;
        }

        /* BRACKET CANVAS TREE */
        .bracket-canvas {
            position: relative;
            width: 780px;
            height: 310px;
            margin: 0 auto;
        }
        
        .round-header {
            position: absolute;
            top: 0px;
            width: 220px;
            height: 22px;
            line-height: 22px;
            background: #002244;
            color: #f7a81b;
            font-size: 7.8pt;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            border-radius: 3px;
        }
        
        .match-card-wrapper {
            position: absolute;
            width: 220px;
        }
        
        .line-h {
            position: absolute;
            height: 0px;
            border-top: 2px solid <?= $lineCol; ?>;
        }
        
        .line-v {
            position: absolute;
            width: 0px;
            border-left: 2px solid <?= $lineCol; ?>;
        }
        
        .dot {
            position: absolute;
            width: 5px;
            height: 5px;
            background: <?= $lineCol; ?>;
            border-radius: 50%;
        }

        /* 10 PARTICIPANTS CANVAS */
        .canvas-10p {
            position: relative;
            width: 780px;
            height: 395px;
            margin: 0 auto;
        }

        .group-tag-10p {
            position: absolute;
            font-size: 7.2pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .round-hdr-10p {
            position: absolute;
            width: 165px;
            height: 18px;
            line-height: 18px;
            background: #002244;
            color: #f7a81b;
            font-size: 6.8pt;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            border-radius: 3px;
        }

        .card-pos-10p {
            position: absolute;
            width: 165px;
        }

        .line-h-10p {
            position: absolute;
            height: 0px;
            border-top: 1.5px solid <?= $lineCol; ?>;
        }

        .line-v-10p {
            position: absolute;
            width: 0px;
            border-left: 1.5px solid <?= $lineCol; ?>;
        }

        .dot-10p {
            position: absolute;
            width: 4px;
            height: 4px;
            background: <?= $lineCol; ?>;
            border-radius: 50%;
        }

        /* FALLBACK GENERIC TABLE */
        .bracket-fallback-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px 3px;
            margin-bottom: 12px;
        }

        .bracket-fallback-th {
            background-color: #002244;
            color: #f7a81b;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            padding: 5px 6px;
            border-radius: 4px;
        }

        /* SCHEDULE & RESULTS TABLE */
        .page-break {
            page-break-before: always;
        }

        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 12px;
            font-size: 7.5pt;
        }

        .results-table th {
            background-color: #0b3c74;
            color: #ffffff;
            font-weight: bold;
            padding: 5px 6px;
            text-align: left;
            border: 1px solid #002244;
        }

        .results-table td {
            padding: 4px 6px;
            border: 1px solid #cbd5e1;
        }

        .results-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
    </style>
</head>
<body>

    <!-- KOP RESMI SATKER -->
    <table class="kop-table">
        <tr>
            <td class="kop-logo">
                <?php if (! empty($logoBase64)): ?>
                    <img src="<?= $logoBase64; ?>" alt="Logo PUPR">
                <?php elseif (! empty($logoUrl)): ?>
                    <img src="<?= esc($logoUrl); ?>" alt="Logo PUPR">
                <?php else: ?>
                    <div style="font-size: 18pt; font-weight: bold; color: #002244;">PUPR</div>
                <?php endif; ?>
            </td>
            <td class="kop-text">
                <h2><?= esc($officialName); ?></h2>
                <h1><?= esc($subHeader); ?></h1>
                <p>Jalan Pepaya No. 63, Jadirejo, Kec. Sukajadi, Kota Pekanbaru, Riau 28121</p>
            </td>
        </tr>
    </table>

    <!-- JUDUL DOKUMEN -->
    <div class="doc-title-block">
        <h3>BAGAN PERTANDINGAN TURNAMEN TENIS MEJA SISTEM GUGUR</h3>
        <span class="category-tag <?= in_array($category ?? '', ['putri', 'ganda_putri']) ? 'putri' : ''; ?>">
            TURNAMEN <?= esc($categoryLabel ?? 'PUTRA'); ?>
        </span>
    </div>

    <!-- METADATA TURNAMEN -->
    <table class="meta-info-table">
        <tr>
            <td class="meta-label">Nama Turnamen:</td>
            <td class="meta-val"><?= esc($tournament['title'] ?? '-'); ?></td>
            <td class="meta-label">Total Peserta:</td>
            <td class="meta-val"><?= $numParticipants; ?> Peserta</td>
        </tr>
        <tr>
            <td class="meta-label">Kategori:</td>
            <td class="meta-val">Turnamen Tenis Meja <?= esc($categoryLabel ?? 'Putra'); ?></td>
            <td class="meta-label">Status Turnamen:</td>
            <td class="meta-val"><?= ($tournament['status'] ?? '') === 'completed' ? 'SELESAI' : 'SEDANG BERLANGSUNG'; ?></td>
        </tr>
    </table>

    <!-- PODIUM JUARA -->
    <table class="podium-pdf-table">
        <tr>
            <td class="podium-cell gold" style="width: 50%;">
                <div class="podium-title">JUARA 1 (CHAMPION)</div>
                <div class="podium-name">
                    <?= esc($champion['name'] ?? '- Belum Ditentukan -'); ?>
                    <?php if (! empty($champion['balai'])): ?>
                        <span style="font-size: 7.5pt; font-weight: normal; color: #475569;">(<?= esc($champion['balai']); ?>)</span>
                    <?php endif; ?>
                </div>
            </td>
            <td class="podium-cell silver" style="width: 50%;">
                <div class="podium-title">JUARA 2 (RUNNER-UP)</div>
                <div class="podium-name">
                    <?= esc($runnerUp['name'] ?? '- Belum Ditentukan -'); ?>
                    <?php if (! empty($runnerUp['balai'])): ?>
                        <span style="font-size: 7.5pt; font-weight: normal; color: #475569;">(<?= esc($runnerUp['balai']); ?>)</span>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
    </table>

    <!-- BAGAN TURNAMEN TREE -->
    <?php if ($numParticipants === 5 && ! empty($rounds)): ?>
        <?php
            // Temukan match berdasarkan ID atau index
            $findMatchById = function($matchId) use ($rounds) {
                foreach ($rounds as $r) {
                    foreach ($r['matches'] as $m) {
                        if (($m['id'] ?? '') === $matchId) {
                            return $m;
                        }
                    }
                }
                return null;
            };

            $m1     = $findMatchById('R1_M0') ?: ($rounds[1]['matches'][0] ?? null);
            $m2     = $findMatchById('R1_M1') ?: ($rounds[1]['matches'][1] ?? null);
            $m1Bye  = $findMatchById('R1_M_BYE') ?: ($rounds[1]['matches'][2] ?? null);

            $m2Bye  = $findMatchById('R2_M_BYE') ?: ($rounds[2]['matches'][0] ?? null);
            $m3     = $findMatchById('R2_M0') ?: ($rounds[2]['matches'][1] ?? null);

            $m4     = $findMatchById('R3_M0') ?: ($rounds[3]['matches'][0] ?? null);
        ?>
        <div class="bracket-canvas">
            <!-- HEADER SETIAP BABAK -->
            <div class="round-header" style="left: 0px;">BABAK I</div>
            <div class="round-header" style="left: 280px;">SEMI FINAL</div>
            <div class="round-header" style="left: 560px;">FINAL (BYE VS PEMENANG SEMIFINAL)</div>

            <!-- COLUMN 1: BABAK I MATCHES -->
            <div class="match-card-wrapper" style="left: 0px; top: 32px;">
                <?= renderPdfMatchBox($m1); ?>
            </div>
            
            <div class="match-card-wrapper" style="left: 0px; top: 128px;">
                <?= renderPdfMatchBox($m2); ?>
            </div>
            
            <div class="match-card-wrapper" style="left: 0px; top: 224px;">
                <?= renderPdfMatchBox($m1Bye); ?>
            </div>

            <!-- CONNECTOR 1: BABAK I KE SEMI FINAL (X: 220 -> 280) -->
            <!-- Match 1 langsung ke Semi Final Bye (Garis Lurus Y = 66) -->
            <div class="line-h" style="left: 220px; top: 66px; width: 60px;"></div>
            <div class="dot" style="left: 278px; top: 64px;"></div>

            <!-- Match 2 (Y=162) dan Match 1Bye (Y=258) bersatu ke Match 3 (Y=210) -->
            <div class="line-h" style="left: 220px; top: 162px; width: 30px;"></div>
            <div class="line-h" style="left: 220px; top: 258px; width: 30px;"></div>
            <div class="line-v" style="left: 250px; top: 162px; height: 96px;"></div>
            <div class="line-h" style="left: 250px; top: 210px; width: 30px;"></div>
            <div class="dot" style="left: 278px; top: 208px;"></div>

            <!-- COLUMN 2: SEMI FINAL MATCHES -->
            <div class="match-card-wrapper" style="left: 280px; top: 32px;">
                <?= renderPdfMatchBox($m2Bye); ?>
            </div>

            <div class="match-card-wrapper" style="left: 280px; top: 176px;">
                <?= renderPdfMatchBox($m3); ?>
            </div>

            <!-- CONNECTOR 2: SEMI FINAL KE FINAL (X: 500 -> 560) -->
            <!-- Semi Final Bye (Y=66) dan Match 3 (Y=210) bersatu ke Final Match 4 (Y=138) -->
            <div class="line-h" style="left: 500px; top: 66px; width: 30px;"></div>
            <div class="line-h" style="left: 500px; top: 210px; width: 30px;"></div>
            <div class="line-v" style="left: 530px; top: 66px; height: 144px;"></div>
            <div class="line-h" style="left: 530px; top: 138px; width: 30px;"></div>
            <div class="dot" style="left: 558px; top: 136px;"></div>

            <!-- COLUMN 3: FINAL MATCH -->
            <div class="match-card-wrapper" style="left: 560px; top: 104px;">
                <?= renderPdfMatchBox($m4, true); ?>
            </div>
        </div>

    <?php elseif ($numParticipants === 10 && ! empty($rounds)): ?>
        <?php
            // Temukan match berdasarkan ID atau index
            $findMatchById = function($matchId) use ($rounds) {
                foreach ($rounds as $r) {
                    foreach ($r['matches'] as $m) {
                        if (($m['id'] ?? '') === $matchId) {
                            return $m;
                        }
                    }
                }
                return null;
            };

            // Bagan Atas
            $m1     = $findMatchById('R1_M0');
            $m2     = $findMatchById('R1_M1');
            $m1Bye  = $findMatchById('R1_M_BYE1');
            $m2Bye  = $findMatchById('R2_M_BYE1');
            $m5     = $findMatchById('R2_M0');
            $m7     = $findMatchById('R3_M0');

            // Bagan Bawah
            $m3     = $findMatchById('R1_M2');
            $m4     = $findMatchById('R1_M3');
            $m2Bye2 = $findMatchById('R1_M_BYE2');
            $m2ByeB = $findMatchById('R2_M_BYE2');
            $m6     = $findMatchById('R2_M1');
            $m8     = $findMatchById('R3_M1');

            // Grand Final
            $m9     = $findMatchById('R4_M0');
        ?>
        <div class="canvas-10p">
            <!-- COLUMN HEADERS -->
            <div class="round-hdr-10p" style="left: 0px; top: 0px;">BABAK 1</div>
            <div class="round-hdr-10p" style="left: 205px; top: 0px;">SEMIFINAL</div>
            <div class="round-hdr-10p" style="left: 410px; top: 0px;">FINAL BAGAN</div>
            <div class="round-hdr-10p" style="left: 615px; top: 0px; background: #1e3a8a; color: #facc15;">GRAND FINAL</div>

            <!-- SECTION LABELS -->
            <div class="group-tag-10p" style="left: 0px; top: 22px; color: #0284c7;">[BAGAN ATAS - 5 PESERTA]</div>
            <div class="group-tag-10p" style="left: 0px; top: 204px; color: #059669;">[BAGAN BAWAH - 5 PESERTA]</div>

            <!-- ==================== BAGAN ATAS ==================== -->
            <!-- Babak 1 -->
            <div class="card-pos-10p" style="left: 0px; top: 38px;"><?= renderPdfMatchBox10($m1); ?></div>
            <div class="card-pos-10p" style="left: 0px; top: 94px;"><?= renderPdfMatchBox10($m2); ?></div>
            <div class="card-pos-10p" style="left: 0px; top: 150px;"><?= renderPdfMatchBox10($m1Bye); ?></div>

            <!-- Conn 1 Atas -->
            <!-- M1 (Y=60) straight to M2Bye (Y=60) -->
            <div class="line-h-10p" style="left: 165px; top: 60px; width: 40px;"></div>
            <div class="dot-10p" style="left: 203px; top: 58px;"></div>

            <!-- M2 (Y=116) and M1Bye (Y=172) fork into M5 (Y=144) -->
            <div class="line-h-10p" style="left: 165px; top: 116px; width: 20px;"></div>
            <div class="line-h-10p" style="left: 165px; top: 172px; width: 20px;"></div>
            <div class="line-v-10p" style="left: 185px; top: 116px; height: 56px;"></div>
            <div class="line-h-10p" style="left: 185px; top: 144px; width: 20px;"></div>
            <div class="dot-10p" style="left: 203px; top: 142px;"></div>

            <!-- Semifinal Atas -->
            <div class="card-pos-10p" style="left: 205px; top: 38px;"><?= renderPdfMatchBox10($m2Bye); ?></div>
            <div class="card-pos-10p" style="left: 205px; top: 122px;"><?= renderPdfMatchBox10($m5); ?></div>

            <!-- Conn 2 Atas: M2Bye (Y=60) and M5 (Y=144) fork into Final Atas M7 (Y=102) -->
            <div class="line-h-10p" style="left: 370px; top: 60px; width: 20px;"></div>
            <div class="line-h-10p" style="left: 370px; top: 144px; width: 20px;"></div>
            <div class="line-v-10p" style="left: 390px; top: 60px; height: 84px;"></div>
            <div class="line-h-10p" style="left: 390px; top: 102px; width: 20px;"></div>
            <div class="dot-10p" style="left: 408px; top: 100px;"></div>

            <!-- Final Bagan Atas -->
            <div class="card-pos-10p" style="left: 410px; top: 80px;"><?= renderPdfMatchBox10($m7, true); ?></div>

            <!-- ==================== BAGAN BAWAH ==================== -->
            <!-- Babak 1 -->
            <div class="card-pos-10p" style="left: 0px; top: 220px;"><?= renderPdfMatchBox10($m3); ?></div>
            <div class="card-pos-10p" style="left: 0px; top: 276px;"><?= renderPdfMatchBox10($m4); ?></div>
            <div class="card-pos-10p" style="left: 0px; top: 332px;"><?= renderPdfMatchBox10($m2Bye2); ?></div>

            <!-- Conn 1 Bawah -->
            <!-- M3 (Y=242) straight to M2ByeB (Y=242) -->
            <div class="line-h-10p" style="left: 165px; top: 242px; width: 40px;"></div>
            <div class="dot-10p" style="left: 203px; top: 240px;"></div>

            <!-- M4 (Y=298) and M2Bye2 (Y=354) fork into M6 (Y=326) -->
            <div class="line-h-10p" style="left: 165px; top: 298px; width: 20px;"></div>
            <div class="line-h-10p" style="left: 165px; top: 354px; width: 20px;"></div>
            <div class="line-v-10p" style="left: 185px; top: 298px; height: 56px;"></div>
            <div class="line-h-10p" style="left: 185px; top: 326px; width: 20px;"></div>
            <div class="dot-10p" style="left: 203px; top: 324px;"></div>

            <!-- Semifinal Bawah -->
            <div class="card-pos-10p" style="left: 205px; top: 220px;"><?= renderPdfMatchBox10($m2ByeB); ?></div>
            <div class="card-pos-10p" style="left: 205px; top: 304px;"><?= renderPdfMatchBox10($m6); ?></div>

            <!-- Conn 2 Bawah: M2ByeB (Y=242) and M6 (Y=326) fork into Final Bawah M8 (Y=284) -->
            <div class="line-h-10p" style="left: 370px; top: 242px; width: 20px;"></div>
            <div class="line-h-10p" style="left: 370px; top: 326px; width: 20px;"></div>
            <div class="line-v-10p" style="left: 390px; top: 242px; height: 84px;"></div>
            <div class="line-h-10p" style="left: 390px; top: 284px; width: 20px;"></div>
            <div class="dot-10p" style="left: 408px; top: 282px;"></div>

            <!-- Final Bagan Bawah -->
            <div class="card-pos-10p" style="left: 410px; top: 262px;"><?= renderPdfMatchBox10($m8, true); ?></div>

            <!-- ==================== GRAND FINAL ==================== -->
            <!-- Conn 3: Final Atas (Y=102) and Final Bawah (Y=284) fork into Grand Final (Y=193) -->
            <div class="line-h-10p" style="left: 575px; top: 102px; width: 20px;"></div>
            <div class="line-h-10p" style="left: 575px; top: 284px; width: 20px;"></div>
            <div class="line-v-10p" style="left: 595px; top: 102px; height: 182px;"></div>
            <div class="line-h-10p" style="left: 595px; top: 193px; width: 20px;"></div>
            <div class="dot-10p" style="left: 613px; top: 191px;"></div>

            <!-- Grand Final Card -->
            <div class="card-pos-10p" style="left: 615px; top: 171px;"><?= renderPdfMatchBox10($m9, false, true); ?></div>
        </div>

    <?php elseif (! empty($rounds)): ?>
        <!-- FALLBACK UNTUK JUMLAH PESERTA LAIN -->
        <table class="bracket-fallback-table">
            <tr>
                <?php for ($r = 1; $r <= $totalRounds; $r++): ?>
                    <?php $roundData = $rounds[$r] ?? []; ?>
                    <th class="bracket-fallback-th" style="width: <?= round(100 / $totalRounds); ?>%;">
                        <?= esc($roundData['name'] ?? ('Babak ' . $r)); ?>
                    </th>
                <?php endfor; ?>
            </tr>
            <tr>
                <?php for ($r = 1; $r <= $totalRounds; $r++): ?>
                    <?php
                        $roundData = $rounds[$r] ?? [];
                        $matches = $roundData['matches'] ?? [];
                        $isFinal = ($r === $totalRounds);
                    ?>
                    <td style="vertical-align: top; width: <?= round(100 / $totalRounds); ?>%;">
                        <?php foreach ($matches as $m): ?>
                            <div style="margin-bottom: 8px;">
                                <?= renderPdfMatchBox($m, $isFinal); ?>
                            </div>
                        <?php endforeach; ?>
                    </td>
                <?php endfor; ?>
            </tr>
        </table>
    <?php endif; ?>

    <!-- HALAMAN 2: JADWAL & REKAP PERTANDINGAN LENGKAP -->
    <div class="page-break"></div>
    <div style="font-size: 8.5pt; font-weight: bold; color: #002244; margin-bottom: 4px; text-transform: uppercase;">
        Daftar Pertandingan &amp; Hasil Lengkap
    </div>
    <table class="results-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 18%;">Babak</th>
                <th style="width: 28%;">Peserta 1 (Nama &amp; Balai)</th>
                <th style="width: 8%; text-align: center;">Skor</th>
                <th style="width: 28%;">Peserta 2 (Nama &amp; Balai)</th>
                <th style="width: 13%;">Pemenang</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $matchIdx = 1;
                foreach ($rounds as $r => $roundData):
                    foreach ($roundData['matches'] as $m):
                        $p1 = $m['player1'] ?? null;
                        $p2 = $m['player2'] ?? null;
            ?>
            <tr>
                <td style="text-align: center;"><?= $matchIdx++; ?></td>
                <td><?= esc($roundData['name'] ?? ('Babak ' . $r)); ?></td>
                <td>
                    <strong><?= esc($p1['name'] ?? '-'); ?></strong>
                    <?php if (! empty($p1['balai']) && $p1['name'] !== $p1['balai']): ?>
                        <span style="color:#64748b;">(<?= esc($p1['balai']); ?>)</span>
                    <?php endif; ?>
                </td>
                <td style="text-align: center; font-weight: bold;">
                    <?= ($m['score1'] !== null && $m['score2'] !== null) ? ($m['score1'] . ' - ' . $m['score2']) : '-'; ?>
                </td>
                <td>
                    <strong><?= esc($p2['name'] ?? '-'); ?></strong>
                    <?php if (! empty($p2['balai']) && $p2['name'] !== $p2['balai'] && empty($p2['is_bye'])): ?>
                        <span style="color:#64748b;">(<?= esc($p2['balai']); ?>)</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (! empty($m['winner'])): ?>
                        <span style="color: #065f46; font-weight: bold;"><?= esc($m['winner']['name']); ?></span>
                    <?php else: ?>
                        <span style="color: #64748b;">Belum Selesai</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; endforeach; ?>
        </tbody>
    </table>

</body>
</html>
