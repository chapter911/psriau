<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title); ?></title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 14mm 10mm 14mm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8.5pt;
            color: #1e293b;
            line-height: 1.3;
        }

        /* KOP SURAT */
        .kop-table {
            width: 100%;
            border-bottom: 2.5px solid #002244;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }

        .kop-logo {
            width: 60px;
            height: auto;
            text-align: center;
            vertical-align: middle;
        }

        .kop-logo img {
            width: 52px;
            height: auto;
        }

        .kop-text {
            text-align: center;
            vertical-align: middle;
            color: #002244;
        }

        .kop-text h2 {
            font-size: 10.5pt;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .kop-text h1 {
            font-size: 12pt;
            font-weight: 800;
            margin: 1px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0b3c74;
        }

        .kop-text p {
            font-size: 7.5pt;
            margin: 2px 0 0 0;
            color: #475569;
        }

        /* DOCUMENT TITLE */
        .doc-title-block {
            text-align: center;
            margin-bottom: 10px;
        }

        .doc-title-block h3 {
            font-size: 11pt;
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
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 8pt;
            margin-top: 3px;
        }

        .category-tag.putri {
            background-color: #be185d;
        }

        /* META TABLE */
        .meta-info-table {
            width: 100%;
            margin-bottom: 10px;
            border-collapse: collapse;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
        }

        .meta-info-table td {
            padding: 4px 8px;
            font-size: 8pt;
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
            margin-bottom: 10px;
            border-collapse: collapse;
        }

        .podium-cell {
            border: 1.5px solid #cbd5e1;
            padding: 6px 10px;
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
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 2px;
        }

        .podium-name {
            font-size: 9.5pt;
            font-weight: 800;
            color: #0f172a;
        }

        /* BRACKET TABLE REPRESENTATION */
        .bracket-pdf-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px 3px;
            margin-bottom: 12px;
        }

        .bracket-pdf-th {
            background-color: #002244;
            color: #f7a81b;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            padding: 5px 6px;
            border-radius: 4px;
        }

        .match-box-pdf {
            background-color: #ffffff;
            border: 1.5px solid #94a3b8;
            border-left: 4px solid #0b3c74;
            border-radius: 4px;
            padding: 5px 6px;
            font-size: 7.5pt;
            margin-bottom: 7px;
        }

        .match-box-pdf.has-winner {
            border-left: 4px solid #10b981;
        }

        .match-box-pdf.is-final {
            border: 1.5px solid #d97706;
            border-left: 4px solid #f59e0b;
            background-color: #fffbeb;
        }

        .match-box-header {
            font-size: 6.8pt;
            font-weight: bold;
            color: #64748b;
            border-bottom: 0.5px solid #e2e8f0;
            padding-bottom: 2px;
            margin-bottom: 2px;
        }

        .match-box-row {
            display: block;
            margin-bottom: 2px;
            line-height: 1.25;
        }

        .match-box-row.winner {
            font-weight: bold;
            color: #065f46;
            background-color: #d1fae5;
            border-radius: 2px;
            padding: 1px 3px;
        }

        .balai-sub {
            font-size: 6.8pt;
            color: #64748b;
            display: block;
        }

        .match-score-badge {
            float: right;
            font-weight: bold;
            font-size: 8.5pt;
            color: #002244;
        }

        /* SCHEDULE & RESULTS TABLE */
        .results-table {
            width: 100%;
            border-collapse: collapse;
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

        /* SIGNATURE SECTION */
        .signature-table {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
        }

        .signature-table td {
            vertical-align: top;
            width: 50%;
            font-size: 8pt;
        }

        .sig-box {
            text-align: center;
        }

        .sig-space {
            height: 40px;
        }

        .sig-name {
            font-weight: bold;
            text-decoration: underline;
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
                <?php else: ?>
                    <img src="<?= esc($logoUrl); ?>" alt="Logo PUPR">
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
        <span class="category-tag <?= ($category ?? '') === 'putri' ? 'putri' : ''; ?>">
            TURNAMEN <?= esc($categoryLabel ?? 'PUTRA'); ?>
        </span>
    </div>

    <!-- METADATA TURNAMEN -->
    <table class="meta-info-table">
        <tr>
            <td class="meta-label">Nama Turnamen:</td>
            <td class="meta-val"><?= esc($tournament['title'] ?? '-'); ?></td>
            <td class="meta-label">Total Peserta:</td>
            <td class="meta-val"><?= (int) ($tournament['participant_count'] ?? 0); ?> Peserta</td>
        </tr>
        <tr>
            <td class="meta-label">Kategori:</td>
            <td class="meta-val">Turnamen Tenis Meja <?= esc($categoryLabel ?? 'Putra'); ?></td>
            <td class="meta-label">Status Turnamen:</td>
            <td class="meta-val"><?= ($tournament['status'] ?? '') === 'completed' ? 'SELESAI' : 'SEDANG BERLANGSUNG'; ?></td>
        </tr>
    </table>

    <!-- PODIUM JUARA -->
    <?php
        $bracket = $tournament['bracket_json'] ?? [];
        $podium = $bracket['podium'] ?? [];
        $champion = $podium['champion'] ?? null;
        $runnerUp = $podium['runner_up'] ?? null;
    ?>
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

    <!-- BAGAN TURNAMEN TABEL -->
    <?php
        $rounds = $bracket['rounds'] ?? [];
        $totalRounds = (int) ($bracket['total_rounds'] ?? count($rounds));
    ?>
    <?php if (! empty($rounds)): ?>
    <table class="bracket-pdf-table">
        <tr>
            <?php for ($r = 1; $r <= $totalRounds; $r++): ?>
                <?php $roundData = $rounds[$r] ?? []; ?>
                <th class="bracket-pdf-th" style="width: <?= round(100 / $totalRounds); ?>%;">
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
                    <?php if ((int)($tournament['participant_count'] ?? 0) === 5 && $r === 2): ?>
                        <?php
                            $m1 = $rounds[1]['matches'][0] ?? null;
                            $m1Winner = $m1['winner'] ?? null;
                        ?>
                        <div class="match-box-pdf" style="background-color: #f0fdf4; border-style: dashed; border-color: #86efac; margin-bottom: 6px;">
                            <div class="match-box-header" style="background-color: #dcfce7; color: #166534;">
                                Menang Bye Ronde 2
                                <span style="float: right; color: #15803d; font-weight: bold;">Lolos &rarr; FINAL</span>
                            </div>
                            <div class="match-box-row" style="font-weight: bold; color: #166534;">
                                <span><?= esc($m1Winner['name'] ?? 'Pemenang Match #1 (A vs B)'); ?></span>
                                <?php if (! empty($m1Winner['balai'])): ?>
                                    <span class="balai-sub"><?= esc($m1Winner['balai']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php foreach ($matches as $m): ?>
                        <?php
                            $p1 = $m['player1'] ?? null;
                            $p2 = $m['player2'] ?? null;
                            $isP1Winner = ! empty($m['winner']) && ! empty($p1) && $m['winner']['name'] === $p1['name'];
                            $isP2Winner = ! empty($m['winner']) && ! empty($p2) && $m['winner']['name'] === $p2['name'];
                            $hasWinner = ($isP1Winner || $isP2Winner);
                            $isDirectToFinal = (! empty($m['next_match_id']) && str_starts_with($m['next_match_id'], 'R' . $totalRounds));
                        ?>
                        <div class="match-box-pdf <?= $isFinal ? 'is-final' : ''; ?> <?= $hasWinner ? 'has-winner' : ''; ?>">
                            <div class="match-box-header">
                                Match #<?= esc($m['match_number']); ?>
                                <?php if ($isFinal): ?>
                                    <span style="float: right; color: #d97706; font-weight: bold;">FINAL</span>
                                <?php elseif ($isDirectToFinal): ?>
                                    <span style="float: right; color: #0284c7; font-weight: bold;">Lolos &rarr; FINAL (Bye)</span>
                                <?php elseif (! empty($m['next_match_id'])): ?>
                                    <span style="float: right; color: #0b3c74; font-weight: bold;">Lolos &rarr; Semi Final</span>
                                <?php endif; ?>
                            </div>

                            <!-- P1 -->
                            <div class="match-box-row <?= $isP1Winner ? 'winner' : ''; ?>">
                                <span><?= esc($p1['name'] ?? '- Menunggu -'); ?></span>
                                <?php if ($m['score1'] !== null): ?>
                                    <span class="match-score-badge"><?= (int) $m['score1']; ?></span>
                                <?php endif; ?>
                                <?php if (! empty($p1['balai']) && ! empty($p1['name']) && $p1['name'] !== $p1['balai']): ?>
                                    <span class="balai-sub"><?= esc($p1['balai']); ?></span>
                                <?php endif; ?>
                            </div>

                            <!-- P2 -->
                            <div class="match-box-row <?= $isP2Winner ? 'winner' : ''; ?>">
                                <span><?= esc($p2['name'] ?? '- Menunggu -'); ?></span>
                                <?php if ($m['score2'] !== null): ?>
                                    <span class="match-score-badge"><?= (int) $m['score2']; ?></span>
                                <?php endif; ?>
                                <?php if (! empty($p2['balai']) && ! empty($p2['name']) && $p2['name'] !== $p2['balai'] && empty($p2['is_bye'])): ?>
                                    <span class="balai-sub"><?= esc($p2['balai']); ?></span>
                                <?php endif; ?>
                            </div>

                            <?php if (! empty($m['note'])): ?>
                                <div style="font-size: 6.5pt; color: #1e40af; font-weight: bold; margin-top: 3px; border-top: 0.5px dashed #cbd5e1; padding-top: 2px;">
                                    * <?= esc($m['note']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <?php if ((int)($tournament['participant_count'] ?? 0) === 5 && $r === 1 && ! empty($tournament['participants_json'][4])): ?>
                        <?php $p5 = $tournament['participants_json'][4]; ?>
                        <div class="match-box-pdf" style="background-color: #fefce8; border-style: dashed; border-color: #fde047; margin-top: 6px;">
                            <div class="match-box-header" style="background-color: #fef9c3; color: #854d0e;">
                                Menang Bye Ronde 1
                                <span style="float: right; color: #a16207; font-weight: bold;">Lolos &rarr; SEMI FINAL</span>
                            </div>
                            <div class="match-box-row" style="font-weight: bold; color: #854d0e;">
                                <span><?= esc($p5['name'] ?? 'Peserta 5'); ?></span>
                                <?php if (! empty($p5['balai'])): ?>
                                    <span class="balai-sub"><?= esc($p5['balai']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </td>
            <?php endfor; ?>
        </tr>
    </table>
    <?php endif; ?>

    <!-- JADWAL & REKAP PERTANDINGAN -->
    <div style="font-size: 8.5pt; font-weight: bold; color: #002244; margin-bottom: 4px; text-transform: uppercase;">
        Daftar Pertandingan &amp; Hasil Lengkap
    </div>
    <table class="results-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 16%;">Babak</th>
                <th style="width: 29%;">Peserta 1 (Nama &amp; Balai)</th>
                <th style="width: 8%; text-align: center;">Skor</th>
                <th style="width: 29%;">Peserta 2 (Nama &amp; Balai)</th>
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

    <!-- LEMBAR PENGESAHAN / TANDA TANGAN -->
    <table class="signature-table">
        <tr>
            <td class="sig-box">
                <p>Mengetahui,<br><strong>Kepala Satuan Kerja Pelaksanaan Prasarana Permukiman Provinsi Riau</strong></p>
                <div class="sig-space"></div>
                <p class="sig-name">M. SYAIFUDDIN, S.T., M.T.</p>
                <p>NIP. 19780512 200502 1 002</p>
            </td>
            <td class="sig-box">
                <p>Pekanbaru, <?= esc($printDate); ?><br><strong>Panitia Pelaksana Turnamen Tenis Meja</strong></p>
                <div class="sig-space"></div>
                <p class="sig-name">PANITIA PERTANDINGAN</p>
                <p>Keluarga Besar Satker PPS Riau</p>
            </td>
        </tr>
    </table>

</body>
</html>
