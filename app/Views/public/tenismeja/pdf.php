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
$isPortrait   = ($orientation ?? 'landscape') === 'portrait';

// Helper function untuk render kartu pertandingan di PDF (5 peserta / umum)
if (! function_exists('renderPdfMatchBox')) {
    function renderPdfMatchBox($m, $isFinal = false, $cardWidth = 260) {
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
        $headerColor = $isFinal ? '#92400e' : ($isBye ? '#065f46' : '#1e293b');

        if ($isBye) {
            $statusBadge = '<span style="background: #ecfdf5; color: #047857; border: 0.5px solid #a7f3d0; border-radius: 3px; padding: 1px 5px; font-weight: bold; font-size: 5.8pt;">' . ($isCompleted ? 'MENANG BYE' : 'MENUNGGU') . '</span>';
        } elseif ($isCompleted) {
            $statusBadge = '<span style="background: #ecfdf5; color: #047857; border: 0.5px solid #a7f3d0; border-radius: 3px; padding: 1px 5px; font-weight: bold; font-size: 5.8pt;">SELESAI</span>';
        } else {
            $statusBadge = '<span style="background: #fffbeb; color: #b45309; border: 0.5px solid #fde68a; border-radius: 3px; padding: 1px 5px; font-weight: bold; font-size: 5.8pt;">PENDING</span>';
        }

        $html = '<div style="background: ' . $cardBg . '; border: ' . $cardBorder . '; border-radius: 6px; font-size: ' . ($cardWidth < 230 ? '6.5pt' : '7.2pt') . '; width: ' . $cardWidth . 'px; box-sizing: border-box; overflow: hidden; font-family: Helvetica, Arial, sans-serif;">';
        
        // Header Kartu
        $html .= '<table style="width: 100%; border-collapse: collapse; background: ' . $headerBg . '; border-bottom: 1px solid #e2e8f0;">';
        $html .= '<tr>';
        $html .= '<td style="padding: 3px 6px; font-weight: bold; font-size: ' . ($cardWidth < 230 ? '6.5pt' : '7.2pt') . '; color: ' . $headerColor . ';">';
        $html .= ($isBye ? 'Menang Bye' : 'Match #' . esc($m['match_number']));
        if (! empty($m['group'])) {
            if ($m['group'] === 'atas') {
                $html .= ' <span style="background: #eff6ff; color: #1d4ed8; border: 0.5px solid #bfdbfe; border-radius: 3px; padding: 0 4px; font-size: 5.5pt;">Bagan Atas</span>';
            } elseif ($m['group'] === 'bawah') {
                $html .= ' <span style="background: #ecfdf5; color: #047857; border: 0.5px solid #a7f3d0; border-radius: 3px; padding: 0 4px; font-size: 5.5pt;">Bagan Bawah</span>';
            } elseif ($m['group'] === 'grand_final') {
                $html .= ' <span style="background: #fffbeb; color: #b45309; border: 0.5px solid #fde68a; border-radius: 3px; padding: 0 4px; font-size: 5.5pt;">Grand Final</span>';
            }
        }
        $html .= '</td>';
        $html .= '<td style="padding: 3px 6px; text-align: right;">' . $statusBadge . '</td>';
        $html .= '</tr>';
        $html .= '</table>';

        // Baris Peserta 1
        $p1Bg = $p1Win ? 'background: #ecfdf5;' : 'background: ' . $cardBg . ';';
        $seed1 = $p1 ? ($p1['seed'] ?? '1') : '-';
        $seed1Bg = $p1Win ? 'background: #10b981; color: #ffffff;' : 'background: #e2e8f0; color: #475569;';
        $html .= '<table style="width: 100%; border-collapse: collapse; ' . $p1Bg . ' border-bottom: 1px solid #f1f5f9;">';
        $html .= '<tr>';
        $html .= '<td style="width: 20px; padding: 3px 0 3px 6px; vertical-align: middle;">';
        $html .= '<div style="width: 16px; height: 16px; line-height: 16px; text-align: center; ' . $seed1Bg . ' font-size: 6.5pt; font-weight: bold; border-radius: 3px;">' . esc($seed1) . '</div>';
        $html .= '</td>';
        $html .= '<td style="padding: 3px 5px; vertical-align: middle;">';
        $html .= '<div style="font-size: ' . ($cardWidth < 230 ? '7pt' : '7.5pt') . '; font-weight: bold; color: ' . ($p1Win ? '#065f46' : '#0f172a') . ';">' . esc($p1['name'] ?? 'Menunggu Pemenang') . '</div>';
        if (! empty($p1['balai'])) {
            $html .= '<div style="margin-top: 1px;"><span style="background: #eff6ff; color: #1e40af; border: 0.5px solid #bfdbfe; border-radius: 3px; padding: 0 4px; font-size: 5.5pt;">' . esc($p1['balai']) . '</span></div>';
        }
        $html .= '</td>';
        $html .= '<td style="width: 44px; padding: 2px 6px 2px 2px; text-align: right; vertical-align: middle;">';
        if ($isBye && $p1) {
            $html .= '<span style="background: #ecfdf5; color: #065f46; font-size: 6pt; font-weight: bold; border: 0.5px solid #a7f3d0; border-radius: 3px; padding: 1.5px 4px;">Lolos</span>';
        } elseif ($m['score1'] !== null) {
            $html .= '<div style="display: inline-block; width: 22px; height: 18px; line-height: 18px; text-align: center; background: ' . ($p1Win ? '#d1fae5' : '#f8fafc') . '; border: 1px solid ' . ($p1Win ? '#10b981' : '#cbd5e1') . '; color: ' . ($p1Win ? '#065f46' : '#0f172a') . '; font-weight: bold; font-size: 7.8pt; border-radius: 3px; vertical-align: middle;">' . (int)$m['score1'] . '</div>';
            if ($p1Win) {
                $html .= '<span style="display: inline-block; width: 14px; height: 14px; line-height: 14px; text-align: center; background: #10b981; color: #ffffff; font-size: 6.5pt; border-radius: 50%; vertical-align: middle; margin-left: 2px;">✓</span>';
            }
        } else {
            $html .= '<div style="display: inline-block; width: 22px; height: 18px; line-height: 18px; text-align: center; background: #f8fafc; border: 1px solid #e2e8f0; color: #94a3b8; font-size: 7.2pt; border-radius: 3px; vertical-align: middle;">0</div>';
        }
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '</table>';

        // Baris Peserta 2
        $p2Bg = $p2Win ? 'background: #ecfdf5;' : 'background: ' . $cardBg . ';';
        $seed2 = $p2 ? ($p2['seed'] ?? '2') : '-';
        $seed2Bg = $p2Win ? 'background: #10b981; color: #ffffff;' : 'background: #e2e8f0; color: #475569;';
        $html .= '<table style="width: 100%; border-collapse: collapse; ' . $p2Bg . '">';
        $html .= '<tr>';
        $html .= '<td style="width: 20px; padding: 3px 0 3px 6px; vertical-align: middle;">';
        $html .= '<div style="width: 16px; height: 16px; line-height: 16px; text-align: center; ' . $seed2Bg . ' font-size: 6.5pt; font-weight: bold; border-radius: 3px;">' . esc($seed2) . '</div>';
        $html .= '</td>';
        $html .= '<td style="padding: 3px 5px; vertical-align: middle;">';
        if ($p2 && ! empty($p2['is_bye'])) {
            $html .= '<div style="font-size: 7.2pt; font-style: italic; color: #64748b;">(BYE) - Lolos Otomatis</div>';
        } else {
            $html .= '<div style="font-size: ' . ($cardWidth < 230 ? '7pt' : '7.5pt') . '; font-weight: bold; color: ' . ($p2Win ? '#065f46' : '#0f172a') . ';">' . esc($p2['name'] ?? 'Menunggu Pemenang') . '</div>';
            if (! empty($p2['balai'])) {
                $html .= '<div style="margin-top: 1px;"><span style="background: #eff6ff; color: #1e40af; border: 0.5px solid #bfdbfe; border-radius: 3px; padding: 0 4px; font-size: 5.5pt;">' . esc($p2['balai']) . '</span></div>';
            }
        }
        $html .= '</td>';
        $html .= '<td style="width: 44px; padding: 2px 6px 2px 2px; text-align: right; vertical-align: middle;">';
        if ($p2 && ! empty($p2['is_bye'])) {
            $html .= '<span style="background: #f1f5f9; color: #64748b; font-size: 5.8pt; border: 0.5px solid #cbd5e1; border-radius: 3px; padding: 1px 4px;">BYE</span>';
        } elseif ($m['score2'] !== null) {
            $html .= '<div style="display: inline-block; width: 22px; height: 18px; line-height: 18px; text-align: center; background: ' . ($p2Win ? '#d1fae5' : '#f8fafc') . '; border: 1px solid ' . ($p2Win ? '#10b981' : '#cbd5e1') . '; color: ' . ($p2Win ? '#065f46' : '#0f172a') . '; font-weight: bold; font-size: 7.8pt; border-radius: 3px; vertical-align: middle;">' . (int)$m['score2'] . '</div>';
            if ($p2Win) {
                $html .= '<span style="display: inline-block; width: 14px; height: 14px; line-height: 14px; text-align: center; background: #10b981; color: #ffffff; font-size: 6.5pt; border-radius: 50%; vertical-align: middle; margin-left: 2px;">✓</span>';
            }
        } else {
            $html .= '<div style="display: inline-block; width: 22px; height: 18px; line-height: 18px; text-align: center; background: #f8fafc; border: 1px solid #e2e8f0; color: #94a3b8; font-size: 7.2pt; border-radius: 3px; vertical-align: middle;">0</div>';
        }
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '</table>';

        // Catatan Alur
        if (! empty($m['note'])) {
            $html .= '<div style="font-size: 5.8pt; color: #1e40af; padding: 2.5px 6px; background: #f8fafc; border-top: 1px solid #f1f5f9;">* ' . esc($m['note']) . '</div>';
        }

        $html .= '</div>';
        return $html;
    }
}

// Helper function untuk render kartu pertandingan 10 peserta (kompak & selaras web) di PDF
if (! function_exists('renderPdfMatchBox10')) {
    function renderPdfMatchBox10($m, $isFinal = false, $isGrandFinal = false, $cardWidth = 165) {
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
            $statusBadge = '<span style="background: #ecfdf5; color: #047857; border: 0.5px solid #a7f3d0; border-radius: 2px; padding: 0.5px 3px; font-weight: bold; font-size: 5pt;">' . ($isCompleted ? 'MENANG BYE' : 'MENUNGGU') . '</span>';
        } elseif ($isCompleted) {
            $statusBadge = '<span style="background: #ecfdf5; color: #047857; border: 0.5px solid #a7f3d0; border-radius: 2px; padding: 0.5px 3px; font-weight: bold; font-size: 5pt;">SELESAI</span>';
        } else {
            $statusBadge = '<span style="background: #fffbeb; color: #b45309; border: 0.5px solid #fde68a; border-radius: 2px; padding: 0.5px 3px; font-weight: bold; font-size: 5pt;">PENDING</span>';
        }

        $html = '<div style="background: ' . $cardBg . '; border: ' . $cardBorder . '; border-radius: 5px; font-size: 6pt; width: ' . $cardWidth . 'px; box-sizing: border-box; overflow: hidden; font-family: Helvetica, Arial, sans-serif;">';
        
        // Header Kartu
        $html .= '<table style="width: 100%; border-collapse: collapse; background: ' . $headerBg . '; border-bottom: 0.5px solid #e2e8f0;">';
        $html .= '<tr>';
        $html .= '<td style="padding: 2px 4px; font-weight: bold; font-size: 5.6pt; color: ' . $headerColor . ';">';
        $html .= ($isBye ? 'Menang Bye' : 'Match #' . esc($m['match_number'])) . ' ' . $badgeGroup;
        $html .= '</td>';
        $html .= '<td style="padding: 2px 4px; text-align: right;">' . $statusBadge . '</td>';
        $html .= '</tr>';
        $html .= '</table>';

        // Baris Peserta 1
        $p1Bg = $p1Win ? 'background: #ecfdf5;' : 'background: ' . $cardBg . ';';
        $seed1 = $p1 ? ($p1['seed'] ?? '1') : '-';
        $seed1Bg = $p1Win ? 'background: #10b981; color: #ffffff;' : 'background: #e2e8f0; color: #475569;';
        $html .= '<table style="width: 100%; border-collapse: collapse; ' . $p1Bg . ' border-bottom: 0.5px solid #f1f5f9;">';
        $html .= '<tr>';
        $html .= '<td style="width: 15px; padding: 2px 0 2px 4px; vertical-align: middle;">';
        $html .= '<div style="width: 12px; height: 12px; line-height: 12px; text-align: center; ' . $seed1Bg . ' font-size: 5.2pt; font-weight: bold; border-radius: 2px;">' . esc($seed1) . '</div>';
        $html .= '</td>';
        $html .= '<td style="padding: 1.5px 3px; vertical-align: middle;">';
        $html .= '<div style="font-size: 5.8pt; font-weight: bold; color: ' . ($p1Win ? '#065f46' : '#1e293b') . ';">' . esc($p1['name'] ?? 'Menunggu Pemenang') . '</div>';
        if (! empty($p1['balai'])) {
            $html .= '<div style="margin-top: 0.5px;"><span style="background: #eff6ff; color: #1e40af; border: 0.5px solid #bfdbfe; border-radius: 2px; padding: 0 2px; font-size: 4.5pt;">' . esc($p1['balai']) . '</span></div>';
        }
        $html .= '</td>';
        $html .= '<td style="width: 28px; padding: 1.5px 4px 1.5px 1px; text-align: right; vertical-align: middle;">';
        if ($isBye && $p1) {
            $html .= '<span style="background: #ecfdf5; color: #065f46; font-size: 4.8pt; font-weight: bold; border: 0.5px solid #a7f3d0; border-radius: 2px; padding: 0.5px 3px;">Lolos</span>';
        } elseif ($m['score1'] !== null) {
            $html .= '<div style="display: inline-block; width: 16px; height: 13px; line-height: 13px; text-align: center; background: ' . ($p1Win ? '#d1fae5' : '#f8fafc') . '; border: 0.5px solid ' . ($p1Win ? '#10b981' : '#cbd5e1') . '; color: ' . ($p1Win ? '#065f46' : '#0f172a') . '; font-weight: bold; font-size: 6pt; border-radius: 2px; vertical-align: middle;">' . (int)$m['score1'] . '</div>';
            if ($p1Win) {
                $html .= '<span style="display: inline-block; width: 10px; height: 10px; line-height: 10px; text-align: center; background: #10b981; color: #ffffff; font-size: 5pt; border-radius: 50%; vertical-align: middle; margin-left: 1px;">✓</span>';
            }
        } else {
            $html .= '<div style="display: inline-block; width: 16px; height: 13px; line-height: 13px; text-align: center; background: #f8fafc; border: 0.5px solid #e2e8f0; color: #94a3b8; font-size: 5.5pt; border-radius: 2px; vertical-align: middle;">0</div>';
        }
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '</table>';

        // Baris Peserta 2
        $p2Bg = $p2Win ? 'background: #ecfdf5;' : 'background: ' . $cardBg . ';';
        $seed2 = $p2 ? ($p2['seed'] ?? '2') : '-';
        $seed2Bg = $p2Win ? 'background: #10b981; color: #ffffff;' : 'background: #e2e8f0; color: #475569;';
        $html .= '<table style="width: 100%; border-collapse: collapse; ' . $p2Bg . '">';
        $html .= '<tr>';
        $html .= '<td style="width: 15px; padding: 2px 0 2px 4px; vertical-align: middle;">';
        $html .= '<div style="width: 12px; height: 12px; line-height: 12px; text-align: center; ' . $seed2Bg . ' font-size: 5.2pt; font-weight: bold; border-radius: 2px;">' . esc($seed2) . '</div>';
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
        $html .= '<td style="width: 28px; padding: 1.5px 4px 1.5px 1px; text-align: right; vertical-align: middle;">';
        if ($p2 && ! empty($p2['is_bye'])) {
            $html .= '<span style="background: #f1f5f9; color: #64748b; font-size: 4.5pt; border: 0.5px solid #cbd5e1; border-radius: 2px; padding: 0.5px 2px;">BYE</span>';
        } elseif ($m['score2'] !== null) {
            $html .= '<div style="display: inline-block; width: 16px; height: 13px; line-height: 13px; text-align: center; background: ' . ($p2Win ? '#d1fae5' : '#f8fafc') . '; border: 0.5px solid ' . ($p2Win ? '#10b981' : '#cbd5e1') . '; color: ' . ($p2Win ? '#065f46' : '#0f172a') . '; font-weight: bold; font-size: 6pt; border-radius: 2px; vertical-align: middle;">' . (int)$m['score2'] . '</div>';
            if ($p2Win) {
                $html .= '<span style="display: inline-block; width: 10px; height: 10px; line-height: 10px; text-align: center; background: #10b981; color: #ffffff; font-size: 5pt; border-radius: 50%; vertical-align: middle; margin-left: 1px;">✓</span>';
            }
        } else {
            $html .= '<div style="display: inline-block; width: 16px; height: 13px; line-height: 13px; text-align: center; background: #f8fafc; border: 0.5px solid #e2e8f0; color: #94a3b8; font-size: 5.5pt; border-radius: 2px; vertical-align: middle;">0</div>';
        }
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '</table>';

        $html .= '</div>';
        return $html;
    }
}

$lineCol = '#0b3c74';

// Perhitungan Dimensi Parametrik Bagan Turnamen (Memanfaatkan Seluruh Lebar & Tinggi Kertas A4)
// 5 Peserta
$cW5       = $isPortrait ? 223 : 315;
$gap5      = $isPortrait ? 33 : 60;
$c5_0      = 0;
$c5_1      = $c5_0 + $cW5 + $gap5;
$c5_2      = $c5_1 + $cW5 + $gap5;
$canvasW5  = $c5_2 + $cW5;
$canvasH5  = $isPortrait ? 915 : 590;
$halfGap5  = (int) ($gap5 / 2);

// Koordinat Vertikal 5 Peserta (Terisi Penuh Atas-Bawah & Bebas Tumpang Tindih)
$y5_m1_top   = $isPortrait ? 56 : 46;
$y5_m2_top   = $isPortrait ? 376 : 240;
$y5_m1b_top  = $isPortrait ? 696 : 434;
$y5_midOff   = $isPortrait ? 92 : 66;
$y5_m1_cy    = $y5_m1_top + $y5_midOff;
$y5_m2_cy    = $y5_m2_top + $y5_midOff;
$y5_m1b_cy   = $y5_m1b_top + $y5_midOff;

$y5_m2b_top  = $y5_m1_top;
$y5_m2b_cy   = $y5_m1_cy;
$y5_m3_cy    = (int) (($y5_m2_cy + $y5_m1b_cy) / 2);
$y5_m3_top   = $y5_m3_cy - $y5_midOff;

$y5_m4_cy    = (int) (($y5_m2b_cy + $y5_m3_cy) / 2);
$y5_m4_top   = $y5_m4_cy - $y5_midOff;

// 10 Peserta (4 Kolom: Babak 1, Semifinal, Final Bagan, Grand Final)
$cW10      = $isPortrait ? 165 : 237;
$gap10     = $isPortrait ? 25 : 39;
$c0        = 0;
$c1        = $c0 + $cW10 + $gap10;
$c2        = $c1 + $cW10 + $gap10;
$c3        = $c2 + $cW10 + $gap10;
$canvasW10 = $c3 + $cW10;
$canvasH10 = $isPortrait ? 915 : 590;
$halfGap10 = (int) ($gap10 / 2);
$remGap10  = $gap10 - $halfGap10;

// Koordinat Vertikal 10 Peserta (Terisi Penuh Atas-Bawah & Presisi Garis)
$y10_tagA    = $isPortrait ? 34 : 28;
$y10_a1_top  = $isPortrait ? 56 : 46;
$y10_a2_top  = $isPortrait ? 190 : 134;
$y10_a3_top  = $isPortrait ? 324 : 222;
$y10_midOff  = $isPortrait ? 45 : 34;

$y10_a1_cy   = $y10_a1_top + $y10_midOff;
$y10_a2_cy   = $y10_a2_top + $y10_midOff;
$y10_a3_cy   = $y10_a3_top + $y10_midOff;

$y10_a_sf1_top = $y10_a1_top;
$y10_a_sf1_cy  = $y10_a1_cy;
$y10_a_sf2_cy  = (int) (($y10_a2_cy + $y10_a3_cy) / 2);
$y10_a_sf2_top = $y10_a_sf2_cy - $y10_midOff;

$y10_a_fin_cy  = (int) (($y10_a_sf1_cy + $y10_a_sf2_cy) / 2);
$y10_a_fin_top = $y10_a_fin_cy - $y10_midOff;

$y10_tagB    = $isPortrait ? 484 : 322;
$y10_b1_top  = $isPortrait ? 506 : 340;
$y10_b2_top  = $isPortrait ? 640 : 428;
$y10_b3_top  = $isPortrait ? 774 : 516;

$y10_b1_cy   = $y10_b1_top + $y10_midOff;
$y10_b2_cy   = $y10_b2_top + $y10_midOff;
$y10_b3_cy   = $y10_b3_top + $y10_midOff;

$y10_b_sf1_top = $y10_b1_top;
$y10_b_sf1_cy  = $y10_b1_cy;
$y10_b_sf2_cy  = (int) (($y10_b2_cy + $y10_b3_cy) / 2);
$y10_b_sf2_top = $y10_b_sf2_cy - $y10_midOff;

$y10_b_fin_cy  = (int) (($y10_b_sf1_cy + $y10_b_sf2_cy) / 2);
$y10_b_fin_top = $y10_b_fin_cy - $y10_midOff;

$y10_gf_cy   = (int) (($y10_a_fin_cy + $y10_b_fin_cy) / 2);
$y10_gf_top  = $y10_gf_cy - $y10_midOff;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title); ?></title>
    <style>
        @page {
            size: A4 <?= $isPortrait ? 'portrait' : 'landscape'; ?>;
            margin: 5mm 6mm;
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
            width: <?= $canvasW5; ?>px;
            height: <?= $canvasH5; ?>px;
            margin: 0 auto;
        }
        
        .round-header {
            position: absolute;
            top: 0px;
            width: <?= $cW5; ?>px;
            height: 30px;
            background: #002244;
            color: #ffffff;
            border-radius: 5px;
            text-align: center;
            box-sizing: border-box;
            padding-top: 3px;
        }

        .round-header .rh-title {
            font-size: <?= $isPortrait ? '7.2pt' : '8pt'; ?>;
            font-weight: 800;
            color: #f7a81b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .round-header .rh-sub {
            font-size: <?= $isPortrait ? '5.4pt' : '6pt'; ?>;
            color: #94a3b8;
            font-weight: normal;
        }
        
        .match-card-wrapper {
            position: absolute;
            width: <?= $cW5; ?>px;
        }
        
        .line-h {
            position: absolute;
            height: 2px;
            background-color: #0b3c74;
            font-size: 1px;
            line-height: 1px;
        }
        
        .line-v {
            position: absolute;
            width: 2px;
            background-color: #0b3c74;
            font-size: 1px;
            line-height: 1px;
        }
        
        .dot {
            position: absolute;
            width: 6px;
            height: 6px;
            background-color: #0b3c74;
            border-radius: 50%;
        }

        /* 10 PARTICIPANTS CANVAS (KOMPAK & RAPI PENUH KERTAS) */
        .canvas-10p {
            position: relative;
            width: <?= $canvasW10; ?>px;
            height: <?= $canvasH10; ?>px;
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
            width: <?= $cW10; ?>px;
            height: 24px;
            background: #002244;
            color: #ffffff;
            border-radius: 4px;
            text-align: center;
            box-sizing: border-box;
            padding-top: 2px;
        }

        .round-hdr-10p .rh-title {
            font-size: <?= $isPortrait ? '6.2pt' : '6.8pt'; ?>;
            font-weight: 800;
            color: #f7a81b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .round-hdr-10p .rh-sub {
            font-size: <?= $isPortrait ? '4.5pt' : '5pt'; ?>;
            color: #94a3b8;
            font-weight: normal;
        }

        .card-pos-10p {
            position: absolute;
            width: <?= $cW10; ?>px;
        }

        .line-h-10p {
            position: absolute;
            height: 2px;
            background-color: #0b3c74;
            font-size: 1px;
            line-height: 1px;
        }

        .line-v-10p {
            position: absolute;
            width: 2px;
            background-color: #0b3c74;
            font-size: 1px;
            line-height: 1px;
        }

        .dot-10p {
            position: absolute;
            width: 5px;
            height: 5px;
            background-color: #0b3c74;
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
            <div class="round-header" style="left: <?= $c5_0; ?>px;">
                <div class="rh-title">BABAK I</div>
                <div class="rh-sub">2 Pertandingan + 1 Bye</div>
            </div>
            <div class="round-header" style="left: <?= $c5_1; ?>px;">
                <div class="rh-title">SEMI FINAL</div>
                <div class="rh-sub">1 Pertandingan + 1 Bye</div>
            </div>
            <div class="round-header" style="left: <?= $c5_2; ?>px;">
                <div class="rh-title">FINAL (BYE VS PEMENANG SF)</div>
                <div class="rh-sub">Perebutan Juara 1 &amp; 2</div>
            </div>

            <!-- COLUMN 1: BABAK I MATCHES -->
            <div class="match-card-wrapper" style="left: <?= $c5_0; ?>px; top: <?= $y5_m1_top; ?>px;">
                <?= renderPdfMatchBox($m1, false, $cW5); ?>
            </div>
            
            <div class="match-card-wrapper" style="left: <?= $c5_0; ?>px; top: <?= $y5_m2_top; ?>px;">
                <?= renderPdfMatchBox($m2, false, $cW5); ?>
            </div>
            
            <div class="match-card-wrapper" style="left: <?= $c5_0; ?>px; top: <?= $y5_m1b_top; ?>px;">
                <?= renderPdfMatchBox($m1Bye, false, $cW5); ?>
            </div>

            <!-- CONNECTOR 1: BABAK I KE SEMI FINAL (X: c5_0+cW5 -> c5_1) -->
            <!-- Match 1 langsung ke Semi Final Bye (Garis Lurus) -->
            <div class="line-h" style="left: <?= ($c5_0 + $cW5); ?>px; top: <?= $y5_m1_cy; ?>px; width: <?= $gap5; ?>px;"></div>
            <div class="dot" style="left: <?= ($c5_1 - 3); ?>px; top: <?= ($y5_m1_cy - 2); ?>px;"></div>

            <!-- Match 2 dan Match 1Bye bersatu ke Match 3 -->
            <div class="line-h" style="left: <?= ($c5_0 + $cW5); ?>px; top: <?= $y5_m2_cy; ?>px; width: <?= $halfGap5; ?>px;"></div>
            <div class="line-h" style="left: <?= ($c5_0 + $cW5); ?>px; top: <?= $y5_m1b_cy; ?>px; width: <?= $halfGap5; ?>px;"></div>
            <div class="line-v" style="left: <?= ($c5_0 + $cW5 + $halfGap5); ?>px; top: <?= $y5_m2_cy; ?>px; height: <?= ($y5_m1b_cy - $y5_m2_cy); ?>px;"></div>
            <div class="line-h" style="left: <?= ($c5_0 + $cW5 + $halfGap5); ?>px; top: <?= $y5_m3_cy; ?>px; width: <?= ($gap5 - $halfGap5); ?>px;"></div>
            <div class="dot" style="left: <?= ($c5_1 - 3); ?>px; top: <?= ($y5_m3_cy - 2); ?>px;"></div>

            <!-- COLUMN 2: SEMI FINAL MATCHES -->
            <div class="match-card-wrapper" style="left: <?= $c5_1; ?>px; top: <?= $y5_m2b_top; ?>px;">
                <?= renderPdfMatchBox($m2Bye, false, $cW5); ?>
            </div>

            <div class="match-card-wrapper" style="left: <?= $c5_1; ?>px; top: <?= $y5_m3_top; ?>px;">
                <?= renderPdfMatchBox($m3, false, $cW5); ?>
            </div>

            <!-- CONNECTOR 2: SEMI FINAL KE FINAL (X: c5_1+cW5 -> c5_2) -->
            <!-- Semi Final Bye dan Match 3 bersatu ke Final Match 4 -->
            <div class="line-h" style="left: <?= ($c5_1 + $cW5); ?>px; top: <?= $y5_m2b_cy; ?>px; width: <?= $halfGap5; ?>px;"></div>
            <div class="line-h" style="left: <?= ($c5_1 + $cW5); ?>px; top: <?= $y5_m3_cy; ?>px; width: <?= $halfGap5; ?>px;"></div>
            <div class="line-v" style="left: <?= ($c5_1 + $cW5 + $halfGap5); ?>px; top: <?= $y5_m2b_cy; ?>px; height: <?= ($y5_m3_cy - $y5_m2b_cy); ?>px;"></div>
            <div class="line-h" style="left: <?= ($c5_1 + $cW5 + $halfGap5); ?>px; top: <?= $y5_m4_cy; ?>px; width: <?= ($gap5 - $halfGap5); ?>px;"></div>
            <div class="dot" style="left: <?= ($c5_2 - 3); ?>px; top: <?= ($y5_m4_cy - 2); ?>px;"></div>

            <!-- COLUMN 3: FINAL MATCH -->
            <div class="match-card-wrapper" style="left: <?= $c5_2; ?>px; top: <?= $y5_m4_top; ?>px;">
                <?= renderPdfMatchBox($m4, true, $cW5); ?>
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
            <div class="group-tag-10p tag-atas" style="left: <?= $c0; ?>px; top: <?= $y10_tagA; ?>px;">BAGAN ATAS (5 PESERTA)</div>
            <div class="group-tag-10p tag-bawah" style="left: <?= $c0; ?>px; top: <?= $y10_tagB; ?>px;">BAGAN BAWAH (5 PESERTA)</div>

            <!-- HEADER ROUNDS (X: c0, c1, c2, c3) -->
            <div class="round-hdr-10p" style="left: <?= $c0; ?>px; top: 0px;">
                <div class="rh-title">BABAK 1</div>
                <div class="rh-sub">4 Match + 2 Bye</div>
            </div>
            <div class="round-hdr-10p" style="left: <?= $c1; ?>px; top: 0px;">
                <div class="rh-title">SEMIFINAL</div>
                <div class="rh-sub">2 Match + 2 Bye</div>
            </div>
            <div class="round-hdr-10p" style="left: <?= $c2; ?>px; top: 0px;">
                <div class="rh-title">FINAL BAGAN</div>
                <div class="rh-sub">2 Pertandingan</div>
            </div>
            <div class="round-hdr-10p" style="left: <?= $c3; ?>px; top: 0px;">
                <div class="rh-title">GRAND FINAL</div>
                <div class="rh-sub">Juara Utama 1 &amp; 2</div>
            </div>

            <!-- ===================== BAGAN ATAS ===================== -->
            <div class="card-pos-10p" style="left: <?= $c0; ?>px; top: <?= $y10_a1_top; ?>px;">
                <?= renderPdfMatchBox10($m1, false, false, $cW10); ?>
            </div>
            <div class="card-pos-10p" style="left: <?= $c0; ?>px; top: <?= $y10_a2_top; ?>px;">
                <?= renderPdfMatchBox10($m2, false, false, $cW10); ?>
            </div>
            <div class="card-pos-10p" style="left: <?= $c0; ?>px; top: <?= $y10_a3_top; ?>px;">
                <?= renderPdfMatchBox10($m1Bye, false, false, $cW10); ?>
            </div>

            <!-- Connectors Bagan Atas: R1 -> R2 -->
            <div class="line-h-10p" style="left: <?= ($c0 + $cW10); ?>px; top: <?= $y10_a1_cy; ?>px; width: <?= $gap10; ?>px;"></div>
            <div class="dot-10p" style="left: <?= ($c1 - 2); ?>px; top: <?= ($y10_a1_cy - 2); ?>px;"></div>

            <div class="line-h-10p" style="left: <?= ($c0 + $cW10); ?>px; top: <?= $y10_a2_cy; ?>px; width: <?= $halfGap10; ?>px;"></div>
            <div class="line-h-10p" style="left: <?= ($c0 + $cW10); ?>px; top: <?= $y10_a3_cy; ?>px; width: <?= $halfGap10; ?>px;"></div>
            <div class="line-v-10p" style="left: <?= ($c0 + $cW10 + $halfGap10); ?>px; top: <?= $y10_a2_cy; ?>px; height: <?= ($y10_a3_cy - $y10_a2_cy); ?>px;"></div>
            <div class="line-h-10p" style="left: <?= ($c0 + $cW10 + $halfGap10); ?>px; top: <?= $y10_a_sf2_cy; ?>px; width: <?= $remGap10; ?>px;"></div>
            <div class="dot-10p" style="left: <?= ($c1 - 2); ?>px; top: <?= ($y10_a_sf2_cy - 2); ?>px;"></div>

            <!-- Round 2 Bagan Atas -->
            <div class="card-pos-10p" style="left: <?= $c1; ?>px; top: <?= $y10_a_sf1_top; ?>px;">
                <?= renderPdfMatchBox10($m2Bye, false, false, $cW10); ?>
            </div>
            <div class="card-pos-10p" style="left: <?= $c1; ?>px; top: <?= $y10_a_sf2_top; ?>px;">
                <?= renderPdfMatchBox10($m5, false, false, $cW10); ?>
            </div>

            <!-- Connectors Bagan Atas: R2 -> R3 -->
            <div class="line-h-10p" style="left: <?= ($c1 + $cW10); ?>px; top: <?= $y10_a_sf1_cy; ?>px; width: <?= $halfGap10; ?>px;"></div>
            <div class="line-h-10p" style="left: <?= ($c1 + $cW10); ?>px; top: <?= $y10_a_sf2_cy; ?>px; width: <?= $halfGap10; ?>px;"></div>
            <div class="line-v-10p" style="left: <?= ($c1 + $cW10 + $halfGap10); ?>px; top: <?= $y10_a_sf1_cy; ?>px; height: <?= ($y10_a_sf2_cy - $y10_a_sf1_cy); ?>px;"></div>
            <div class="line-h-10p" style="left: <?= ($c1 + $cW10 + $halfGap10); ?>px; top: <?= $y10_a_fin_cy; ?>px; width: <?= $remGap10; ?>px;"></div>
            <div class="dot-10p" style="left: <?= ($c2 - 2); ?>px; top: <?= ($y10_a_fin_cy - 2); ?>px;"></div>

            <!-- Round 3: M7 (Final Bagan Atas) -->
            <div class="card-pos-10p" style="left: <?= $c2; ?>px; top: <?= $y10_a_fin_top; ?>px;">
                <?= renderPdfMatchBox10($m7, true, false, $cW10); ?>
            </div>

            <!-- ===================== BAGAN BAWAH ===================== -->
            <div class="card-pos-10p" style="left: <?= $c0; ?>px; top: <?= $y10_b1_top; ?>px;">
                <?= renderPdfMatchBox10($m3, false, false, $cW10); ?>
            </div>
            <div class="card-pos-10p" style="left: <?= $c0; ?>px; top: <?= $y10_b2_top; ?>px;">
                <?= renderPdfMatchBox10($m4, false, false, $cW10); ?>
            </div>
            <div class="card-pos-10p" style="left: <?= $c0; ?>px; top: <?= $y10_b3_top; ?>px;">
                <?= renderPdfMatchBox10($m2Bye2, false, false, $cW10); ?>
            </div>

            <!-- Connectors Bagan Bawah: R1 -> R2 -->
            <div class="line-h-10p" style="left: <?= ($c0 + $cW10); ?>px; top: <?= $y10_b1_cy; ?>px; width: <?= $gap10; ?>px;"></div>
            <div class="dot-10p" style="left: <?= ($c1 - 2); ?>px; top: <?= ($y10_b1_cy - 2); ?>px;"></div>

            <div class="line-h-10p" style="left: <?= ($c0 + $cW10); ?>px; top: <?= $y10_b2_cy; ?>px; width: <?= $halfGap10; ?>px;"></div>
            <div class="line-h-10p" style="left: <?= ($c0 + $cW10); ?>px; top: <?= $y10_b3_cy; ?>px; width: <?= $halfGap10; ?>px;"></div>
            <div class="line-v-10p" style="left: <?= ($c0 + $cW10 + $halfGap10); ?>px; top: <?= $y10_b2_cy; ?>px; height: <?= ($y10_b3_cy - $y10_b2_cy); ?>px;"></div>
            <div class="line-h-10p" style="left: <?= ($c0 + $cW10 + $halfGap10); ?>px; top: <?= $y10_b_sf2_cy; ?>px; width: <?= $remGap10; ?>px;"></div>
            <div class="dot-10p" style="left: <?= ($c1 - 2); ?>px; top: <?= ($y10_b_sf2_cy - 2); ?>px;"></div>

            <!-- Round 2 Bagan Bawah -->
            <div class="card-pos-10p" style="left: <?= $c1; ?>px; top: <?= $y10_b_sf1_top; ?>px;">
                <?= renderPdfMatchBox10($m3Bye, false, false, $cW10); ?>
            </div>
            <div class="card-pos-10p" style="left: <?= $c1; ?>px; top: <?= $y10_b_sf2_top; ?>px;">
                <?= renderPdfMatchBox10($m6, false, false, $cW10); ?>
            </div>

            <!-- Connectors Bagan Bawah: R2 -> R3 -->
            <div class="line-h-10p" style="left: <?= ($c1 + $cW10); ?>px; top: <?= $y10_b_sf1_cy; ?>px; width: <?= $halfGap10; ?>px;"></div>
            <div class="line-h-10p" style="left: <?= ($c1 + $cW10); ?>px; top: <?= $y10_b_sf2_cy; ?>px; width: <?= $halfGap10; ?>px;"></div>
            <div class="line-v-10p" style="left: <?= ($c1 + $cW10 + $halfGap10); ?>px; top: <?= $y10_b_sf1_cy; ?>px; height: <?= ($y10_b_sf2_cy - $y10_b_sf1_cy); ?>px;"></div>
            <div class="line-h-10p" style="left: <?= ($c1 + $cW10 + $halfGap10); ?>px; top: <?= $y10_b_fin_cy; ?>px; width: <?= $remGap10; ?>px;"></div>
            <div class="dot-10p" style="left: <?= ($c2 - 2); ?>px; top: <?= ($y10_b_fin_cy - 2); ?>px;"></div>

            <!-- Round 3: M8 (Final Bagan Bawah) -->
            <div class="card-pos-10p" style="left: <?= $c2; ?>px; top: <?= $y10_b_fin_top; ?>px;">
                <?= renderPdfMatchBox10($m8, true, false, $cW10); ?>
            </div>

            <!-- ===================== GRAND FINAL ===================== -->
            <!-- Connectors Final Bagan -> Grand Final -->
            <div class="line-h-10p" style="left: <?= ($c2 + $cW10); ?>px; top: <?= $y10_a_fin_cy; ?>px; width: <?= $halfGap10; ?>px;"></div>
            <div class="line-h-10p" style="left: <?= ($c2 + $cW10); ?>px; top: <?= $y10_b_fin_cy; ?>px; width: <?= $halfGap10; ?>px;"></div>
            <div class="line-v-10p" style="left: <?= ($c2 + $cW10 + $halfGap10); ?>px; top: <?= $y10_a_fin_cy; ?>px; height: <?= ($y10_b_fin_cy - $y10_a_fin_cy); ?>px;"></div>
            <div class="line-h-10p" style="left: <?= ($c2 + $cW10 + $halfGap10); ?>px; top: <?= $y10_gf_cy; ?>px; width: <?= $remGap10; ?>px;"></div>
            <div class="dot-10p" style="left: <?= ($c3 - 2); ?>px; top: <?= ($y10_gf_cy - 2); ?>px;"></div>

            <!-- Round 4: M9 Grand Final -->
            <div class="card-pos-10p" style="left: <?= $c3; ?>px; top: <?= $y10_gf_top; ?>px;">
                <?= renderPdfMatchBox10($m9, true, true, $cW10); ?>
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

</body>
</html>
