<?php
/**
 * View PDF Export Bagan Pertandingan Tenis Meja
 * Format: A4 Landscape
 * 
 * Desain Tampilan Selaras & Identik dengan Portal Web:
 * https://satkerpps-riau.online/tenismeja
 * 
 * Sesuai User Rules & Requirements:
 * - Bagan Turnamen sistem gugur dengan garis alir orthogonal dan rapi
 * - Kartu pertandingan bernuansa web modern (header match, seed badge, nama, balai badge, dan box skor)
 * - Banner Topbar & Meta Turnamen dengan Podium Juara 1 & 2
 * - Mendukung bagan 5 peserta (Bagan Atas) dan 10 peserta (Gabungan Atas & Bawah -> Grand Final)
 * - Tanpa kolom tanda tangan (sesuai instruksi user)
 * - Bebas karakter unicode yang berpotensi corrupt pada Dompdf
 */

$bracket      = $tournament['bracket_json'] ?? [];
$rounds       = $bracket['rounds'] ?? [];
$totalRounds  = (int) ($bracket['total_rounds'] ?? count($rounds));
$podium       = $bracket['podium'] ?? [];
$champion     = $podium['champion'] ?? null;
$runnerUp     = $podium['runner_up'] ?? null;
$numParticipants = (int) ($tournament['participant_count'] ?? 0);

// Helper function untuk render kartu pertandingan di PDF (5 peserta / umum)
if (! function_exists('renderPdfMatchBox')) {
    function renderPdfMatchBox($m, $isFinal = false) {
        if (! $m) {
            return '';
        }
        $p1 = $m['player1'] ?? null;
        $p2 = $m['player2'] ?? null;
        $isBye = ! empty($m['is_bye']);
        $w = $m['winner'] ?? null;
        $isCompleted = ($m['status'] ?? '') === 'completed';
        
        $p1Win = $w && $p1 && (! empty($p1['name'])) && ($w['name'] === $p1['name']);
        $p2Win = $w && $p2 && (! empty($p2['name'])) && ($w['name'] === $p2['name']);

        $cardBorder = $isFinal ? '2px solid #f59e0b' : ($isBye ? '1.5px dashed #86efac' : '1.5px solid #cbd5e1');
        $cardBg = $isFinal ? '#fffdf7' : ($isBye ? '#fafdfb' : '#ffffff');
        $headerBg = $isFinal ? '#fef3c7' : ($isBye ? '#ecfdf5' : '#f1f5f9');
        $headerColor = $isFinal ? '#92400e' : ($isBye ? '#065f46' : '#475569');

        if ($isBye) {
            $statusText = $isCompleted ? 'MENANG BYE' : 'MENUNGGU';
            $statusColor = '#10b981';
        } elseif ($isCompleted) {
            $statusText = 'SELESAI';
            $statusColor = '#10b981';
        } else {
            $statusText = 'PENDING';
            $statusColor = '#f59e0b';
        }

        $html = '<div style="background: ' . $cardBg . '; border: ' . $cardBorder . '; border-radius: 6px; font-size: 7pt; width: 220px; box-sizing: border-box; overflow: hidden; font-family: Helvetica, Arial, sans-serif;">';
        
        // Header Kartu
        $html .= '<table style="width: 100%; border-collapse: collapse; background: ' . $headerBg . '; border-bottom: 1px solid #e2e8f0;">';
        $html .= '<tr>';
        $html .= '<td style="padding: 2.5px 6px; font-weight: bold; font-size: 6.8pt; color: ' . $headerColor . ';">';
        $html .= ($isBye ? 'Menang Bye' : 'Match #' . esc($m['match_number']));
        if (! empty($m['group'])) {
            if ($m['group'] === 'atas') {
                $html .= ' <span style="background: #eff6ff; color: #1d4ed8; border: 0.5px solid #bfdbfe; border-radius: 3px; padding: 0 4px; font-size: 5.2pt;">Bagan Atas</span>';
            } elseif ($m['group'] === 'bawah') {
                $html .= ' <span style="background: #ecfdf5; color: #047857; border: 0.5px solid #a7f3d0; border-radius: 3px; padding: 0 4px; font-size: 5.2pt;">Bagan Bawah</span>';
            } elseif ($m['group'] === 'grand_final') {
                $html .= ' <span style="background: #fffbeb; color: #b45309; border: 0.5px solid #fde68a; border-radius: 3px; padding: 0 4px; font-size: 5.2pt;">Grand Final</span>';
            }
        }
        $html .= '</td>';
        $html .= '<td style="padding: 2.5px 6px; font-weight: bold; font-size: 6.2pt; text-align: right; color: ' . $statusColor . ';">' . $statusText . '</td>';
        $html .= '</tr>';
        $html .= '</table>';

        // Baris Peserta 1
        $p1Bg = $p1Win ? 'background: #ecfdf5;' : 'background: ' . $cardBg . ';';
        $seed1 = $p1 ? ($p1['seed'] ?? '1') : '-';
        $html .= '<table style="width: 100%; border-collapse: collapse; ' . $p1Bg . ' border-bottom: 1px solid #f1f5f9;">';
        $html .= '<tr>';
        $html .= '<td style="width: 18px; padding: 3px 0 3px 5px; vertical-align: middle;">';
        $html .= '<div style="width: 15px; height: 15px; line-height: 15px; text-align: center; background: #e2e8f0; color: #475569; font-size: 6.2pt; font-weight: bold; border-radius: 3px;">' . esc($seed1) . '</div>';
        $html .= '</td>';
        $html .= '<td style="padding: 2.5px 4px; vertical-align: middle;">';
        $html .= '<div style="font-size: 7pt; font-weight: bold; color: ' . ($p1Win ? '#065f46' : '#1e293b') . ';">' . esc($p1['name'] ?? 'Menunggu Pemenang') . '</div>';
        if (! empty($p1['balai'])) {
            $html .= '<div style="margin-top: 1px;"><span style="background: #eff6ff; color: #1e40af; border: 0.5px solid #bfdbfe; border-radius: 3px; padding: 0 3px; font-size: 5.2pt;">' . esc($p1['balai']) . '</span></div>';
        }
        $html .= '</td>';
        $html .= '<td style="width: 32px; padding: 2px 5px 2px 2px; text-align: right; vertical-align: middle;">';
        if ($isBye && $p1) {
            $html .= '<span style="background: #ecfdf5; color: #065f46; font-size: 5.8pt; font-weight: bold; border: 0.5px solid #a7f3d0; border-radius: 3px; padding: 1px 4px;">Lolos</span>';
        } elseif ($m['score1'] !== null) {
            $html .= '<div style="display: inline-block; width: 20px; height: 16px; line-height: 16px; text-align: center; background: ' . ($p1Win ? '#d1fae5' : '#f8fafc') . '; border: 1px solid ' . ($p1Win ? '#10b981' : '#cbd5e1') . '; color: ' . ($p1Win ? '#065f46' : '#0f172a') . '; font-weight: bold; font-size: 7.2pt; border-radius: 3px;">' . (int)$m['score1'] . '</div>';
        } else {
            $html .= '<div style="display: inline-block; width: 20px; height: 16px; line-height: 16px; text-align: center; background: #f8fafc; border: 1px solid #e2e8f0; color: #94a3b8; font-size: 6.8pt; border-radius: 3px;">0</div>';
        }
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '</table>';

        // Baris Peserta 2
        $p2Bg = $p2Win ? 'background: #ecfdf5;' : 'background: ' . $cardBg . ';';
        $seed2 = $p2 ? ($p2['seed'] ?? '2') : '-';
        $html .= '<table style="width: 100%; border-collapse: collapse; ' . $p2Bg . '">';
        $html .= '<tr>';
        $html .= '<td style="width: 18px; padding: 3px 0 3px 5px; vertical-align: middle;">';
        $html .= '<div style="width: 15px; height: 15px; line-height: 15px; text-align: center; background: #e2e8f0; color: #475569; font-size: 6.2pt; font-weight: bold; border-radius: 3px;">' . esc($seed2) . '</div>';
        $html .= '</td>';
        $html .= '<td style="padding: 2.5px 4px; vertical-align: middle;">';
        if ($p2 && ! empty($p2['is_bye'])) {
            $html .= '<div style="font-size: 6.8pt; font-style: italic; color: #64748b;">(BYE) - Lolos Otomatis</div>';
        } else {
            $html .= '<div style="font-size: 7pt; font-weight: bold; color: ' . ($p2Win ? '#065f46' : '#1e293b') . ';">' . esc($p2['name'] ?? 'Menunggu Pemenang') . '</div>';
            if (! empty($p2['balai'])) {
                $html .= '<div style="margin-top: 1px;"><span style="background: #eff6ff; color: #1e40af; border: 0.5px solid #bfdbfe; border-radius: 3px; padding: 0 3px; font-size: 5.2pt;">' . esc($p2['balai']) . '</span></div>';
            }
        }
        $html .= '</td>';
        $html .= '<td style="width: 32px; padding: 2px 5px 2px 2px; text-align: right; vertical-align: middle;">';
        if ($p2 && ! empty($p2['is_bye'])) {
            $html .= '<span style="background: #f1f5f9; color: #64748b; font-size: 5.5pt; border: 0.5px solid #cbd5e1; border-radius: 3px; padding: 1px 3px;">BYE</span>';
        } elseif ($m['score2'] !== null) {
            $html .= '<div style="display: inline-block; width: 20px; height: 16px; line-height: 16px; text-align: center; background: ' . ($p2Win ? '#d1fae5' : '#f8fafc') . '; border: 1px solid ' . ($p2Win ? '#10b981' : '#cbd5e1') . '; color: ' . ($p2Win ? '#065f46' : '#0f172a') . '; font-weight: bold; font-size: 7.2pt; border-radius: 3px;">' . (int)$m['score2'] . '</div>';
        } else {
            $html .= '<div style="display: inline-block; width: 20px; height: 16px; line-height: 16px; text-align: center; background: #f8fafc; border: 1px solid #e2e8f0; color: #94a3b8; font-size: 6.8pt; border-radius: 3px;">0</div>';
        }
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '</table>';

        // Catatan Alur
        if (! empty($m['note'])) {
            $html .= '<div style="font-size: 5.5pt; color: #1e40af; padding: 2px 5px; background: #f8fafc; border-top: 1px solid #f1f5f9;">* ' . esc($m['note']) . '</div>';
        }

        $html .= '</div>';
        return $html;
    }
}

// Helper function untuk render kartu pertandingan 10 peserta (kompak & selaras web) di PDF
if (! function_exists('renderPdfMatchBox10')) {
    function renderPdfMatchBox10($m, $isFinal = false, $isGrandFinal = false) {
        if (! $m) {
            return '';
        }
        $p1 = $m['player1'] ?? null;
        $p2 = $m['player2'] ?? null;
        $isBye = ! empty($m['is_bye']);
        $w = $m['winner'] ?? null;
        $isCompleted = ($m['status'] ?? '') === 'completed';
        
        $p1Win = $w && $p1 && (! empty($p1['name'])) && ($w['name'] === $p1['name']);
        $p2Win = $w && $p2 && (! empty($p2['name'])) && ($w['name'] === $p2['name']);

        $cardBorder = $isGrandFinal ? '1.5px solid #f59e0b' : ($isFinal ? '1.5px solid #d97706' : ($isBye ? '1px dashed #86efac' : '1px solid #cbd5e1'));
        $cardBg = $isGrandFinal ? '#fffdf5' : ($isFinal ? '#fffdf7' : ($isBye ? '#fafdfb' : '#ffffff'));
        $headerBg = $isGrandFinal ? '#fef3c7' : ($isFinal ? '#fef3c7' : ($isBye ? '#ecfdf5' : '#f1f5f9'));
        $headerColor = $isGrandFinal ? '#92400e' : ($isFinal ? '#92400e' : ($isBye ? '#065f46' : '#475569'));

        if ($isGrandFinal) {
            $badgeGroup = '<span style="background: #fffbeb; color: #b45309; border: 0.5px solid #fde68a; border-radius: 2px; padding: 0 3px; font-size: 4.5pt; font-weight: bold;">Grand Final</span>';
        } elseif (($m['group'] ?? '') === 'atas') {
            $badgeGroup = '<span style="background: #eff6ff; color: #1d4ed8; border: 0.5px solid #bfdbfe; border-radius: 2px; padding: 0 3px; font-size: 4.5pt; font-weight: bold;">Bagan Atas</span>';
        } elseif (($m['group'] ?? '') === 'bawah') {
            $badgeGroup = '<span style="background: #ecfdf5; color: #047857; border: 0.5px solid #a7f3d0; border-radius: 2px; padding: 0 3px; font-size: 4.5pt; font-weight: bold;">Bagan Bawah</span>';
        } else {
            $badgeGroup = '';
        }

        if ($isBye) {
            $statusText = $isCompleted ? 'MENANG BYE' : 'MENUNGGU';
            $statusColor = '#10b981';
        } elseif ($isCompleted) {
            $statusText = 'SELESAI';
            $statusColor = '#10b981';
        } else {
            $statusText = 'PENDING';
            $statusColor = '#f59e0b';
        }

        $html = '<div style="background: ' . $cardBg . '; border: ' . $cardBorder . '; border-radius: 5px; font-size: 6pt; width: 165px; box-sizing: border-box; overflow: hidden; font-family: Helvetica, Arial, sans-serif;">';
        
        // Header Kartu
        $html .= '<table style="width: 100%; border-collapse: collapse; background: ' . $headerBg . '; border-bottom: 0.5px solid #e2e8f0;">';
        $html .= '<tr>';
        $html .= '<td style="padding: 1.5px 4px; font-weight: bold; font-size: 5.5pt; color: ' . $headerColor . ';">';
        $html .= ($isBye ? 'Menang Bye' : 'Match #' . esc($m['match_number'])) . ' ' . $badgeGroup;
        $html .= '</td>';
        $html .= '<td style="padding: 1.5px 4px; font-weight: bold; font-size: 5.2pt; text-align: right; color: ' . $statusColor . ';">' . $statusText . '</td>';
        $html .= '</tr>';
        $html .= '</table>';

        // Baris Peserta 1
        $p1Bg = $p1Win ? 'background: #ecfdf5;' : 'background: ' . $cardBg . ';';
        $seed1 = $p1 ? ($p1['seed'] ?? '1') : '-';
        $html .= '<table style="width: 100%; border-collapse: collapse; ' . $p1Bg . ' border-bottom: 0.5px solid #f1f5f9;">';
        $html .= '<tr>';
        $html .= '<td style="width: 14px; padding: 2px 0 2px 4px; vertical-align: middle;">';
        $html .= '<div style="width: 12px; height: 12px; line-height: 12px; text-align: center; background: #e2e8f0; color: #475569; font-size: 5.2pt; font-weight: bold; border-radius: 2px;">' . esc($seed1) . '</div>';
        $html .= '</td>';
        $html .= '<td style="padding: 1.5px 3px; vertical-align: middle;">';
        $html .= '<div style="font-size: 5.8pt; font-weight: bold; color: ' . ($p1Win ? '#065f46' : '#1e293b') . ';">' . esc($p1['name'] ?? 'Menunggu Pemenang') . '</div>';
        if (! empty($p1['balai'])) {
            $html .= '<div style="margin-top: 0.5px;"><span style="background: #eff6ff; color: #1e40af; border: 0.5px solid #bfdbfe; border-radius: 2px; padding: 0 2px; font-size: 4.5pt;">' . esc($p1['balai']) . '</span></div>';
        }
        $html .= '</td>';
        $html .= '<td style="width: 24px; padding: 1.5px 4px 1.5px 1px; text-align: right; vertical-align: middle;">';
        if ($isBye && $p1) {
            $html .= '<span style="background: #ecfdf5; color: #065f46; font-size: 4.8pt; font-weight: bold; border: 0.5px solid #a7f3d0; border-radius: 2px; padding: 0.5px 3px;">Lolos</span>';
        } elseif ($m['score1'] !== null) {
            $html .= '<div style="display: inline-block; width: 16px; height: 13px; line-height: 13px; text-align: center; background: ' . ($p1Win ? '#d1fae5' : '#f8fafc') . '; border: 0.5px solid ' . ($p1Win ? '#10b981' : '#cbd5e1') . '; color: ' . ($p1Win ? '#065f46' : '#0f172a') . '; font-weight: bold; font-size: 6pt; border-radius: 2px;">' . (int)$m['score1'] . '</div>';
        } else {
            $html .= '<div style="display: inline-block; width: 16px; height: 13px; line-height: 13px; text-align: center; background: #f8fafc; border: 0.5px solid #e2e8f0; color: #94a3b8; font-size: 5.5pt; border-radius: 2px;">0</div>';
        }
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '</table>';

        // Baris Peserta 2
        $p2Bg = $p2Win ? 'background: #ecfdf5;' : 'background: ' . $cardBg . ';';
        $seed2 = $p2 ? ($p2['seed'] ?? '2') : '-';
        $html .= '<table style="width: 100%; border-collapse: collapse; ' . $p2Bg . '">';
        $html .= '<tr>';
        $html .= '<td style="width: 14px; padding: 2px 0 2px 4px; vertical-align: middle;">';
        $html .= '<div style="width: 12px; height: 12px; line-height: 12px; text-align: center; background: #e2e8f0; color: #475569; font-size: 5.2pt; font-weight: bold; border-radius: 2px;">' . esc($seed2) . '</div>';
        $html .= '</td>';
        $html .= '<td style="padding: 1.5px 3px; vertical-align: middle;">';
        if ($p2 && ! empty($p2['is_bye'])) {
            $html .= '<div style="font-size: 5.5pt; font-style: italic; color: #64748b;">(BYE) - Lolos Otomatis</div>';
        } else {
            $html .= '<div style="font-size: 5.8pt; font-weight: bold; color: ' . ($p2Win ? '#065f46' : '#1e293b') . ';">' . esc($p2['name'] ?? 'Menunggu Pemenang') . '</div>';
            if (! empty($p2['balai'])) {
                $html .= '<div style="margin-top: 0.5px;"><span style="background: #eff6ff; color: #1e40af; border: 0.5px solid #bfdbfe; border-radius: 2px; padding: 0 2px; font-size: 4.5pt;">' . esc($p2['balai']) . '</span></div>';
            }
        }
        $html .= '</td>';
        $html .= '<td style="width: 24px; padding: 1.5px 4px 1.5px 1px; text-align: right; vertical-align: middle;">';
        if ($p2 && ! empty($p2['is_bye'])) {
            $html .= '<span style="background: #f1f5f9; color: #64748b; font-size: 4.5pt; border: 0.5px solid #cbd5e1; border-radius: 2px; padding: 0.5px 2px;">BYE</span>';
        } elseif ($m['score2'] !== null) {
            $html .= '<div style="display: inline-block; width: 16px; height: 13px; line-height: 13px; text-align: center; background: ' . ($p2Win ? '#d1fae5' : '#f8fafc') . '; border: 0.5px solid ' . ($p2Win ? '#10b981' : '#cbd5e1') . '; color: ' . ($p2Win ? '#065f46' : '#0f172a') . '; font-weight: bold; font-size: 6pt; border-radius: 2px;">' . (int)$m['score2'] . '</div>';
        } else {
            $html .= '<div style="display: inline-block; width: 16px; height: 13px; line-height: 13px; text-align: center; background: #f8fafc; border: 0.5px solid #e2e8f0; color: #94a3b8; font-size: 5.5pt; border-radius: 2px;">0</div>';
        }
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '</table>';

        $html .= '</div>';
        return $html;
    }
}

$lineCol = '#94a3b8';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title); ?></title>
    <style>
        @page {
            size: A4 landscape;
            margin: 6mm 10mm 6mm 10mm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8pt;
            color: #1e293b;
            line-height: 1.25;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }

        /* TOPBAR SELARAS WEB */
        .portal-topbar {
            background-color: #002244;
            color: #ffffff;
            padding: 6px 12px;
            border-radius: 6px;
            margin-bottom: 5px;
        }

        .portal-topbar table {
            width: 100%;
            border-collapse: collapse;
        }

        .topbar-logo-td {
            width: 32px;
            vertical-align: middle;
        }

        .topbar-logo-img {
            width: 26px;
            height: auto;
        }

        .topbar-title-td {
            vertical-align: middle;
        }

        .topbar-title-td h1 {
            font-size: 9.5pt;
            font-weight: 800;
            margin: 0;
            color: #ffffff;
            letter-spacing: 0.5px;
        }

        .topbar-title-td h1 span {
            color: #f7a81b;
        }

        .topbar-title-td p {
            font-size: 6.2pt;
            color: #cbd5e1;
            margin: 1px 0 0 0;
        }

        .topbar-info-td {
            text-align: right;
            vertical-align: middle;
        }

        .topbar-info-td .tagline {
            font-size: 6pt;
            color: #94a3b8;
        }

        .topbar-info-td .timestamp {
            font-size: 6.2pt;
            font-weight: bold;
            color: #f7a81b;
            margin-top: 1px;
        }

        /* TOURNAMENT BANNER CARD (SELARAS WEB) */
        .portal-meta-card {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 5px 8px;
            margin-bottom: 6px;
        }

        .portal-meta-card table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-title-h2 {
            font-size: 10pt;
            font-weight: 800;
            color: #002244;
            margin: 0 0 3px 0;
            text-transform: uppercase;
        }

        .pill-badge {
            display: inline-block;
            font-weight: bold;
            font-size: 6.5pt;
            padding: 1.5px 6px;
            border-radius: 8px;
            margin-right: 3px;
        }

        .pill-cat-putra {
            background-color: #eff6ff;
            color: #1d4ed8;
            border: 0.5px solid #bfdbfe;
        }

        .pill-cat-putri {
            background-color: #fdf2f8;
            color: #be185d;
            border: 0.5px solid #fbcfe8;
        }

        .pill-count {
            background-color: #f1f5f9;
            color: #475569;
            border: 0.5px solid #cbd5e1;
        }

        .pill-status-ongoing {
            background-color: #fffbeb;
            color: #b45309;
            border: 0.5px solid #fde68a;
        }

        .pill-status-completed {
            background-color: #ecfdf5;
            color: #047857;
            border: 0.5px solid #a7f3d0;
        }

        /* PODIUM MINI BOXES (SELARAS WEB) */
        .podium-mini-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 4px 0;
        }

        .podium-box {
            border-radius: 4px;
            padding: 3px 5px;
            text-align: center;
            width: 50%;
        }

        .podium-gold {
            background-color: #fef9c3;
            border: 1px solid #f59e0b;
        }

        .podium-silver {
            background-color: #f8fafc;
            border: 1px solid #94a3b8;
        }

        .podium-box-lbl {
            font-size: 5.2pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .podium-gold .podium-box-lbl {
            color: #b45309;
        }

        .podium-silver .podium-box-lbl {
            color: #64748b;
        }

        .podium-box-name {
            font-size: 7.2pt;
            font-weight: 800;
            color: #002244;
            margin-top: 1px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .podium-box-balai {
            font-size: 5.2pt;
            color: #64748b;
        }

        /* BRACKET CANVAS TREE (5 PESERTA) */
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
            height: 26px;
            background: #002244;
            color: #ffffff;
            border-radius: 5px;
            text-align: center;
            box-sizing: border-box;
            padding-top: 2.5px;
        }

        .round-header .rh-title {
            font-size: 7.5pt;
            font-weight: 800;
            color: #f7a81b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .round-header .rh-sub {
            font-size: 5.5pt;
            color: #94a3b8;
            font-weight: normal;
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
            background: #64748b;
            border-radius: 50%;
        }

        /* 10 PARTICIPANTS CANVAS (KOMPAK & RAFI) */
        .canvas-10p {
            position: relative;
            width: 780px;
            height: 395px;
            margin: 0 auto;
        }

        .group-tag-10p {
            position: absolute;
            font-size: 6.5pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 1.5px 6px;
            border-radius: 3px;
        }

        .tag-atas {
            background-color: #eff6ff;
            color: #1d4ed8;
            border: 0.5px solid #bfdbfe;
        }

        .tag-bawah {
            background-color: #ecfdf5;
            color: #047857;
            border: 0.5px solid #a7f3d0;
        }

        .round-hdr-10p {
            position: absolute;
            width: 165px;
            height: 24px;
            background: #002244;
            color: #ffffff;
            border-radius: 4px;
            text-align: center;
            box-sizing: border-box;
            padding-top: 2px;
        }

        .round-hdr-10p .rh-title {
            font-size: 6.8pt;
            font-weight: 800;
            color: #f7a81b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .round-hdr-10p .rh-sub {
            font-size: 5pt;
            color: #94a3b8;
            font-weight: normal;
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
            background: #64748b;
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

        /* SCHEDULE & RESULTS TABLE (HALAMAN 2) */
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
            background-color: #002244;
            color: #f7a81b;
            font-weight: bold;
            padding: 5px 6px;
            text-align: left;
            border: 1px solid #00172e;
            text-transform: uppercase;
            font-size: 7.2pt;
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

    <!-- TOPBAR SELARAS PORTAL WEB -->
    <div class="portal-topbar">
        <table>
            <tr>
                <td class="topbar-logo-td">
                    <?php if (! empty($logoBase64)): ?>
                        <img src="<?= $logoBase64; ?>" class="topbar-logo-img" alt="Logo PUPR">
                    <?php elseif (! empty($logoUrl)): ?>
                        <img src="<?= esc($logoUrl); ?>" class="topbar-logo-img" alt="Logo PUPR">
                    <?php else: ?>
                        <div style="width: 24px; height: 24px; line-height: 24px; text-align: center; background: #f7a81b; color: #002244; font-weight: bold; border-radius: 3px; font-size: 7pt;">PU</div>
                    <?php endif; ?>
                </td>
                <td class="topbar-title-td">
                    <h1>PORTAL TURNAMEN <span>TENIS MEJA</span></h1>
                    <p><?= esc($officialName); ?> &bull; <?= esc($subHeader); ?></p>
                </td>
                <td class="topbar-info-td">
                    <div class="tagline">Bagan Turnamen &amp; Live Score Antar Balai PUPR Riau</div>
                    <div class="timestamp"><?= esc($printDate); ?></div>
                </td>
            </tr>
        </table>
    </div>

    <!-- TOURNAMENT BANNER & PODIUM DISPLAY (SELARAS WEB) -->
    <div class="portal-meta-card">
        <table>
            <tr>
                <!-- SISI KIRI: JUDUL TURNAMEN & PILL BADGES -->
                <td style="vertical-align: middle; width: 56%;">
                    <div class="meta-title-h2">
                        <?= esc($tournament['title'] ?? 'Turnamen Tenis Meja Antar Balai PUPR'); ?>
                    </div>
                    <div>
                        <!-- Category Pill -->
                        <span class="pill-badge <?= in_array($category ?? '', ['putri', 'ganda_putri']) ? 'pill-cat-putri' : 'pill-cat-putra'; ?>">
                            Kategori <?= esc($categoryLabel ?? 'PUTRA'); ?>
                        </span>
                        <!-- Participant Count Pill -->
                        <span class="pill-badge pill-count">
                            <?= $numParticipants; ?> Peserta
                        </span>
                        <!-- Status Pill -->
                        <span class="pill-badge <?= ($tournament['status'] ?? '') === 'completed' ? 'pill-status-completed' : 'pill-status-ongoing'; ?>">
                            <?= ($tournament['status'] ?? '') === 'completed' ? 'Turnamen Selesai' : 'Sedang Berlangsung'; ?>
                        </span>
                    </div>
                </td>

                <!-- SISI KANAN: KARTU PODIUM JUARA (SELARAS WEB) -->
                <td style="vertical-align: middle; width: 44%;">
                    <table class="podium-mini-table">
                        <tr>
                            <!-- Juara 1 -->
                            <td class="podium-box podium-gold">
                                <div class="podium-box-lbl">Juara 1 (Champion)</div>
                                <div class="podium-box-name"><?= esc($champion['name'] ?? '- Menunggu Final -'); ?></div>
                                <?php if (! empty($champion['balai'])): ?>
                                    <div class="podium-box-balai">(<?= esc($champion['balai']); ?>)</div>
                                <?php endif; ?>
                            </td>
                            <!-- Juara 2 -->
                            <td class="podium-box podium-silver">
                                <div class="podium-box-lbl">Juara 2 (Runner-Up)</div>
                                <div class="podium-box-name"><?= esc($runnerUp['name'] ?? '- Menunggu Final -'); ?></div>
                                <?php if (! empty($runnerUp['balai'])): ?>
                                    <div class="podium-box-balai">(<?= esc($runnerUp['balai']); ?>)</div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

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
            <!-- HEADER SETIAP BABAK (SELARAS WEB) -->
            <div class="round-header" style="left: 0px;">
                <div class="rh-title">BABAK I</div>
                <div class="rh-sub">2 Pertandingan + 1 Bye</div>
            </div>
            <div class="round-header" style="left: 280px;">
                <div class="rh-title">SEMI FINAL</div>
                <div class="rh-sub">1 Pertandingan + 1 Bye</div>
            </div>
            <div class="round-header" style="left: 560px;">
                <div class="rh-title">FINAL (BYE VS PEMENANG SF)</div>
                <div class="rh-sub">Perebutan Juara 1 &amp; 2</div>
            </div>

            <!-- COLUMN 1: BABAK I MATCHES -->
            <div class="match-card-wrapper" style="left: 0px; top: 34px;">
                <?= renderPdfMatchBox($m1); ?>
            </div>
            
            <div class="match-card-wrapper" style="left: 0px; top: 126px;">
                <?= renderPdfMatchBox($m2); ?>
            </div>
            
            <div class="match-card-wrapper" style="left: 0px; top: 218px;">
                <?= renderPdfMatchBox($m1Bye); ?>
            </div>

            <!-- CONNECTOR 1: BABAK I KE SEMI FINAL (X: 220 -> 280) -->
            <!-- Match 1 langsung ke Semi Final Bye (Garis Lurus Y = 66) -->
            <div class="line-h" style="left: 220px; top: 66px; width: 60px;"></div>
            <div class="dot" style="left: 278px; top: 64px;"></div>

            <!-- Match 2 (Y=158) dan Match 1Bye (Y=250) bersatu ke Match 3 (Y=204) -->
            <div class="line-h" style="left: 220px; top: 158px; width: 30px;"></div>
            <div class="line-h" style="left: 220px; top: 250px; width: 30px;"></div>
            <div class="line-v" style="left: 250px; top: 158px; height: 92px;"></div>
            <div class="line-h" style="left: 250px; top: 204px; width: 30px;"></div>
            <div class="dot" style="left: 278px; top: 202px;"></div>

            <!-- COLUMN 2: SEMI FINAL MATCHES -->
            <div class="match-card-wrapper" style="left: 280px; top: 34px;">
                <?= renderPdfMatchBox($m2Bye); ?>
            </div>

            <div class="match-card-wrapper" style="left: 280px; top: 172px;">
                <?= renderPdfMatchBox($m3); ?>
            </div>

            <!-- CONNECTOR 2: SEMI FINAL KE FINAL (X: 500 -> 560) -->
            <!-- Semi Final Bye (Y=66) dan Match 3 (Y=204) bersatu ke Final Match 4 (Y=135) -->
            <div class="line-h" style="left: 500px; top: 66px; width: 30px;"></div>
            <div class="line-h" style="left: 500px; top: 204px; width: 30px;"></div>
            <div class="line-v" style="left: 530px; top: 66px; height: 138px;"></div>
            <div class="line-h" style="left: 530px; top: 135px; width: 30px;"></div>
            <div class="dot" style="left: 558px; top: 133px;"></div>

            <!-- COLUMN 3: FINAL MATCH -->
            <div class="match-card-wrapper" style="left: 560px; top: 103px;">
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
            $m3Bye  = $findMatchById('R2_M_BYE2');
            $m6     = $findMatchById('R2_M1');
            $m8     = $findMatchById('R3_M1');

            // Grand Final
            $m9     = $findMatchById('R4_M0');
        ?>
        <div class="canvas-10p">
            <!-- LABEL GRUP ATAS & BAWAH -->
            <div class="group-tag-10p tag-atas" style="left: 0px; top: 26px;">BAGAN ATAS (5 PESERTA)</div>
            <div class="group-tag-10p tag-bawah" style="left: 0px; top: 206px;">BAGAN BAWAH (5 PESERTA)</div>

            <!-- HEADER ROUNDS (X: 0, 205, 410, 615) -->
            <div class="round-hdr-10p" style="left: 0px; top: 0px;">
                <div class="rh-title">BABAK 1</div>
                <div class="rh-sub">4 Match + 2 Bye</div>
            </div>
            <div class="round-hdr-10p" style="left: 205px; top: 0px;">
                <div class="rh-title">SEMIFINAL</div>
                <div class="rh-sub">2 Match + 2 Bye</div>
            </div>
            <div class="round-hdr-10p" style="left: 410px; top: 0px;">
                <div class="rh-title">FINAL BAGAN</div>
                <div class="rh-sub">2 Pertandingan</div>
            </div>
            <div class="round-hdr-10p" style="left: 615px; top: 0px;">
                <div class="rh-title">GRAND FINAL</div>
                <div class="rh-sub">Juara Utama 1 &amp; 2</div>
            </div>

            <!-- ===================== BAGAN ATAS ===================== -->
            <!-- Round 1: M1 (top:42), M2 (top:98), M1Bye (top:154) -->
            <div class="card-pos-10p" style="left: 0px; top: 42px;">
                <?= renderPdfMatchBox10($m1); ?>
            </div>
            <div class="card-pos-10p" style="left: 0px; top: 98px;">
                <?= renderPdfMatchBox10($m2); ?>
            </div>
            <div class="card-pos-10p" style="left: 0px; top: 154px;">
                <?= renderPdfMatchBox10($m1Bye); ?>
            </div>

            <!-- Connectors Bagan Atas: R1 -> R2 (X: 165 -> 205) -->
            <!-- M1 (Y=63) -> M2Bye (Y=63) [Lurus] -->
            <div class="line-h-10p" style="left: 165px; top: 63px; width: 40px;"></div>
            <div class="dot-10p" style="left: 203px; top: 61px;"></div>

            <!-- M2 (Y=119) & M1Bye (Y=175) -> M5 (Y=147) -->
            <div class="line-h-10p" style="left: 165px; top: 119px; width: 20px;"></div>
            <div class="line-h-10p" style="left: 165px; top: 175px; width: 20px;"></div>
            <div class="line-v-10p" style="left: 185px; top: 119px; height: 56px;"></div>
            <div class="line-h-10p" style="left: 185px; top: 147px; width: 20px;"></div>
            <div class="dot-10p" style="left: 203px; top: 145px;"></div>

            <!-- Round 2: M2Bye (top:42), M5 (top:126) -->
            <div class="card-pos-10p" style="left: 205px; top: 42px;">
                <?= renderPdfMatchBox10($m2Bye); ?>
            </div>
            <div class="card-pos-10p" style="left: 205px; top: 126px;">
                <?= renderPdfMatchBox10($m5); ?>
            </div>

            <!-- Connectors Bagan Atas: R2 -> R3 (X: 370 -> 410) -->
            <!-- M2Bye (Y=63) & M5 (Y=147) -> M7 (Y=105) -->
            <div class="line-h-10p" style="left: 370px; top: 63px; width: 20px;"></div>
            <div class="line-h-10p" style="left: 370px; top: 147px; width: 20px;"></div>
            <div class="line-v-10p" style="left: 390px; top: 63px; height: 84px;"></div>
            <div class="line-h-10p" style="left: 390px; top: 105px; width: 20px;"></div>
            <div class="dot-10p" style="left: 408px; top: 103px;"></div>

            <!-- Round 3: M7 (Final Bagan Atas, top:84) -->
            <div class="card-pos-10p" style="left: 410px; top: 84px;">
                <?= renderPdfMatchBox10($m7, true); ?>
            </div>

            <!-- ===================== BAGAN BAWAH ===================== -->
            <!-- Round 1: M3 (top:222), M4 (top:278), M2Bye2 (top:334) -->
            <div class="card-pos-10p" style="left: 0px; top: 222px;">
                <?= renderPdfMatchBox10($m3); ?>
            </div>
            <div class="card-pos-10p" style="left: 0px; top: 278px;">
                <?= renderPdfMatchBox10($m4); ?>
            </div>
            <div class="card-pos-10p" style="left: 0px; top: 334px;">
                <?= renderPdfMatchBox10($m2Bye2); ?>
            </div>

            <!-- Connectors Bagan Bawah: R1 -> R2 (X: 165 -> 205) -->
            <!-- M3 (Y=243) -> M3Bye (Y=243) [Lurus] -->
            <div class="line-h-10p" style="left: 165px; top: 243px; width: 40px;"></div>
            <div class="dot-10p" style="left: 203px; top: 241px;"></div>

            <!-- M4 (Y=299) & M2Bye2 (Y=355) -> M6 (Y=327) -->
            <div class="line-h-10p" style="left: 165px; top: 299px; width: 20px;"></div>
            <div class="line-h-10p" style="left: 165px; top: 355px; width: 20px;"></div>
            <div class="line-v-10p" style="left: 185px; top: 299px; height: 56px;"></div>
            <div class="line-h-10p" style="left: 185px; top: 327px; width: 20px;"></div>
            <div class="dot-10p" style="left: 203px; top: 325px;"></div>

            <!-- Round 2: M3Bye (top:222), M6 (top:306) -->
            <div class="card-pos-10p" style="left: 205px; top: 222px;">
                <?= renderPdfMatchBox10($m3Bye); ?>
            </div>
            <div class="card-pos-10p" style="left: 205px; top: 306px;">
                <?= renderPdfMatchBox10($m6); ?>
            </div>

            <!-- Connectors Bagan Bawah: R2 -> R3 (X: 370 -> 410) -->
            <!-- M3Bye (Y=243) & M6 (Y=327) -> M8 (Y=285) -->
            <div class="line-h-10p" style="left: 370px; top: 243px; width: 20px;"></div>
            <div class="line-h-10p" style="left: 370px; top: 327px; width: 20px;"></div>
            <div class="line-v-10p" style="left: 390px; top: 243px; height: 84px;"></div>
            <div class="line-h-10p" style="left: 390px; top: 285px; width: 20px;"></div>
            <div class="dot-10p" style="left: 408px; top: 283px;"></div>

            <!-- Round 3: M8 (Final Bagan Bawah, top:264) -->
            <div class="card-pos-10p" style="left: 410px; top: 264px;">
                <?= renderPdfMatchBox10($m8, true); ?>
            </div>

            <!-- ===================== GRAND FINAL ===================== -->
            <!-- Connectors Final Bagan -> Grand Final (X: 575 -> 615) -->
            <!-- M7 (Y=105) & M8 (Y=285) -> M9 Grand Final (Y=195) -->
            <div class="line-h-10p" style="left: 575px; top: 105px; width: 20px;"></div>
            <div class="line-h-10p" style="left: 575px; top: 285px; width: 20px;"></div>
            <div class="line-v-10p" style="left: 595px; top: 105px; height: 180px;"></div>
            <div class="line-h-10p" style="left: 595px; top: 195px; width: 20px;"></div>
            <div class="dot-10p" style="left: 613px; top: 193px;"></div>

            <!-- Round 4: M9 Grand Final (top:174) -->
            <div class="card-pos-10p" style="left: 615px; top: 174px;">
                <?= renderPdfMatchBox10($m9, true, true); ?>
            </div>
        </div>

    <?php else: ?>
        <!-- FALLBACK GENERIC ROUNDS BRACKET -->
        <table class="bracket-fallback-table">
            <tr>
                <?php for ($r = 1; $r <= $totalRounds; $r++): ?>
                    <?php if (! empty($rounds[$r])): ?>
                        <th class="bracket-fallback-th">
                            <?= esc($rounds[$r]['name'] ?? ('Babak ' . $r)); ?>
                        </th>
                    <?php endif; ?>
                <?php endfor; ?>
            </tr>
            <tr>
                <?php for ($r = 1; $r <= $totalRounds; $r++): ?>
                    <?php if (! empty($rounds[$r])): ?>
                        <td style="vertical-align: top; width: <?= (100 / max(1, $totalRounds)); ?>%;">
                            <?php foreach ($rounds[$r]['matches'] as $m): ?>
                                <div style="margin-bottom: 8px;">
                                    <?= renderPdfMatchBox($m, ($r === $totalRounds)); ?>
                                </div>
                            <?php endforeach; ?>
                        </td>
                    <?php endif; ?>
                <?php endfor; ?>
            </tr>
        </table>
    <?php endif; ?>

    <!-- HALAMAN 2: DAFTAR PERTANDINGAN & REKAPITULASI LENGKAP -->
    <div class="page-break"></div>

    <!-- REPEAT TOPBAR ON PAGE 2 -->
    <div class="portal-topbar">
        <table>
            <tr>
                <td class="topbar-logo-td">
                    <?php if (! empty($logoBase64)): ?>
                        <img src="<?= $logoBase64; ?>" class="topbar-logo-img" alt="Logo PUPR">
                    <?php elseif (! empty($logoUrl)): ?>
                        <img src="<?= esc($logoUrl); ?>" class="topbar-logo-img" alt="Logo PUPR">
                    <?php else: ?>
                        <div style="width: 24px; height: 24px; line-height: 24px; text-align: center; background: #f7a81b; color: #002244; font-weight: bold; border-radius: 3px; font-size: 7pt;">PU</div>
                    <?php endif; ?>
                </td>
                <td class="topbar-title-td">
                    <h1>REKAPITULASI SKOR PERTANDINGAN &bull; <span><?= esc($categoryLabel ?? 'PUTRA'); ?></span></h1>
                    <p><?= esc($officialName); ?> &bull; <?= esc($subHeader); ?></p>
                </td>
                <td class="topbar-info-td">
                    <div class="tagline">Lembar Hasil &amp; Jadwal Turnamen Tenis Meja</div>
                    <div class="timestamp"><?= esc($printDate); ?></div>
                </td>
            </tr>
        </table>
    </div>

    <!-- DAFTAR LENGKAP PERTANDINGAN (HASIL & SKOR) -->
    <div style="margin-top: 8px; margin-bottom: 4px;">
        <span style="font-size: 8.5pt; font-weight: 800; color: #002244; text-transform: uppercase;">
            Jadwal &amp; Hasil Lengkap Pertandingan
        </span>
    </div>

    <table class="results-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No.</th>
                <th style="width: 14%;">Babak</th>
                <th style="width: 27%;">Peserta 1</th>
                <th style="width: 10%; text-align: center;">Skor</th>
                <th style="width: 27%;">Peserta 2</th>
                <th style="width: 17%;">Pemenang</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                $allMatches = [];
                foreach ($rounds as $rIdx => $rData) {
                    foreach ($rData['matches'] as $m) {
                        $allMatches[] = array_merge($m, ['round_display' => $rData['name'] ?? ('Babak ' . $rIdx)]);
                    }
                }
                
                $no = 1;
                foreach ($allMatches as $m):
                    $p1 = $m['player1'] ?? null;
                    $p2 = $m['player2'] ?? null;
                    $w  = $m['winner'] ?? null;
                    $isBye = ! empty($m['is_bye']);
            ?>
            <tr>
                <td style="text-align: center; font-weight: bold;"><?= $no++; ?></td>
                <td>
                    <span style="font-weight: bold; color: #002244;"><?= esc($m['round_display']); ?></span>
                    <br><span style="font-size: 6.2pt; color: #64748b;">Match #<?= esc($m['match_number']); ?></span>
                </td>
                <td>
                    <span style="font-weight: bold; color: #1e293b;"><?= esc($p1['name'] ?? '- Menunggu -'); ?></span>
                    <?php if (! empty($p1['balai'])): ?>
                        <br><span style="font-size: 6.2pt; color: #1e40af; background: #eff6ff; padding: 0.5px 3px; border-radius: 2px; border: 0.5px solid #bfdbfe;"><?= esc($p1['balai']); ?></span>
                    <?php endif; ?>
                </td>
                <td style="text-align: center; font-weight: bold; font-size: 8.5pt;">
                    <?php if ($isBye): ?>
                        <span style="color: #166534; font-size: 7pt; font-weight: bold;">(BYE)</span>
                    <?php elseif ($m['score1'] !== null && $m['score2'] !== null): ?>
                        <span style="color: #002244;"><?= (int)$m['score1']; ?> - <?= (int)$m['score2']; ?></span>
                    <?php else: ?>
                        <span style="color: #94a3b8; font-size: 7pt;">Belum Main</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($p2 && ! empty($p2['is_bye'])): ?>
                        <span style="font-style: italic; color: #64748b;">(BYE) - Lolos Otomatis</span>
                    <?php else: ?>
                        <span style="font-weight: bold; color: #1e293b;"><?= esc($p2['name'] ?? '- Menunggu -'); ?></span>
                        <?php if (! empty($p2['balai'])): ?>
                            <br><span style="font-size: 6.2pt; color: #1e40af; background: #eff6ff; padding: 0.5px 3px; border-radius: 2px; border: 0.5px solid #bfdbfe;"><?= esc($p2['balai']); ?></span>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($w && ! empty($w['name'])): ?>
                        <span style="font-weight: bold; color: #15803d;">
                            <?= esc($w['name']); ?>
                        </span>
                        <?php if (! empty($w['balai'])): ?>
                            <br><span style="font-size: 6.2pt; color: #64748b;">(<?= esc($w['balai']); ?>)</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span style="color: #94a3b8; font-style: italic;">- Belum Ditentukan -</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>
</html>
