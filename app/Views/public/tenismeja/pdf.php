<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title); ?></title>
    <style>
        @page {
            size: A4 landscape;
            margin: 12mm 15mm 12mm 15mm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9pt;
            color: #1e293b;
            line-height: 1.35;
        }

        /* KOP SURAT */
        .kop-table {
            width: 100%;
            border-bottom: 2.5px solid #002244;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .kop-logo {
            width: 65px;
            height: auto;
            text-align: center;
            vertical-align: middle;
        }

        .kop-logo img {
            width: 58px;
            height: auto;
        }

        .kop-text {
            text-align: center;
            vertical-align: middle;
            color: #002244;
        }

        .kop-text h2 {
            font-size: 11pt;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .kop-text h1 {
            font-size: 13pt;
            font-weight: 800;
            margin: 1px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0b3c74;
        }

        .kop-text p {
            font-size: 8pt;
            margin: 2px 0 0 0;
            color: #475569;
        }

        /* DOCUMENT TITLE */
        .doc-title-block {
            text-align: center;
            margin-bottom: 12px;
        }

        .doc-title-block h3 {
            font-size: 12pt;
            font-weight: 800;
            color: #002244;
            text-transform: uppercase;
            margin: 0;
        }

        .doc-title-block p {
            font-size: 8.5pt;
            color: #64748b;
            margin-top: 2px;
        }

        /* META TABLE */
        .meta-info-table {
            width: 100%;
            margin-bottom: 14px;
            border-collapse: collapse;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
        }

        .meta-info-table td {
            padding: 5px 10px;
            font-size: 8.5pt;
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
            margin-bottom: 14px;
            border-collapse: collapse;
        }

        .podium-cell {
            border: 1.5px solid #cbd5e1;
            padding: 8px 12px;
            text-align: center;
            border-radius: 6px;
        }

        .podium-cell.gold {
            background-color: #fef9c3;
            border-color: #eab308;
        }

        .podium-cell.silver {
            background-color: #f1f5f9;
            border-color: #94a3b8;
        }

        .podium-cell.bronze {
            background-color: #ffedd5;
            border-color: #f97316;
        }

        .podium-title {
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 2px;
        }

        .podium-name {
            font-size: 10pt;
            font-weight: 800;
            color: #0f172a;
        }

        /* BRACKET TABLE REPRESENTATION */
        .bracket-pdf-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 4px;
            margin-bottom: 16px;
        }

        .bracket-pdf-th {
            background-color: #002244;
            color: #f7a81b;
            font-size: 8.5pt;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            padding: 6px 8px;
            border-radius: 4px;
        }

        .match-box-pdf {
            background-color: #ffffff;
            border: 1px solid #94a3b8;
            border-radius: 4px;
            padding: 4px 6px;
            font-size: 8pt;
            margin-bottom: 6px;
        }

        .match-box-pdf.is-final {
            border: 1.5px solid #d97706;
            background-color: #fffbeb;
        }

        .match-box-header {
            font-size: 7pt;
            font-weight: bold;
            color: #64748b;
            border-bottom: 0.5px solid #e2e8f0;
            padding-bottom: 2px;
            margin-bottom: 3px;
        }

        .match-box-row {
            display: block;
            margin-bottom: 2px;
        }

        .match-box-row.winner {
            font-weight: bold;
            color: #065f46;
            background-color: #d1fae5;
            border-radius: 2px;
            padding: 1px 3px;
        }

        .match-score-badge {
            float: right;
            font-weight: bold;
        }

        /* SCHEDULE & RESULTS TABLE */
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 8pt;
        }

        .results-table th {
            background-color: #0b3c74;
            color: #ffffff;
            font-weight: bold;
            padding: 6px 8px;
            text-align: left;
            border: 1px solid #002244;
        }

        .results-table td {
            padding: 5px 8px;
            border: 1px solid #cbd5e1;
        }

        .results-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        /* SIGNATURE SECTION */
        .signature-table {
            width: 100%;
            margin-top: 14px;
            border-collapse: collapse;
        }

        .signature-table td {
            vertical-align: top;
            width: 50%;
            font-size: 8.5pt;
        }

        .sig-box {
            text-align: center;
        }

        .sig-space {
            height: 45px;
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
        <p><?= esc($tournament['title'] ?? 'Turnamen Tenis Meja'); ?> • Kategori: <?= esc($tournament['category'] ?? 'Tunggal Putra'); ?></p>
    </div>

    <!-- METADATA TURNAMEN -->
    <table class="meta-info-table">
        <tr>
            <td class="meta-label">Nama Turnamen:</td>
            <td class="meta-val"><?= esc($tournament['title'] ?? '-'); ?></td>
            <td class="meta-label">Total Peserta:</td>
            <td class="meta-val"><?= (int) ($tournament['participant_count'] ?? 0); ?> Peserta / Balai</td>
        </tr>
        <tr>
            <td class="meta-label">Kategori Laga:</td>
            <td class="meta-val"><?= esc($tournament['category'] ?? 'Tunggal Putra'); ?></td>
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
        $thirdPlace = $podium['third_place'] ?? null;
    ?>
    <table class="podium-pdf-table">
        <tr>
            <td class="podium-cell gold" style="width: 50%;">
                <div class="podium-title">JUARA 1 (CHAMPION)</div>
                <div class="podium-name"><?= esc($champion['name'] ?? '- Belum Ditentukan -'); ?></div>
            </td>
            <td class="podium-cell silver" style="width: 50%;">
                <div class="podium-title">JUARA 2 (RUNNER-UP)</div>
                <div class="podium-name"><?= esc($runnerUp['name'] ?? '- Belum Ditentukan -'); ?></div>
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
                    <?php foreach ($matches as $m): ?>
                        <?php
                            $p1 = $m['player1'] ?? null;
                            $p2 = $m['player2'] ?? null;
                            $isP1Winner = ! empty($m['winner']) && ! empty($p1) && $m['winner']['name'] === $p1['name'];
                            $isP2Winner = ! empty($m['winner']) && ! empty($p2) && $m['winner']['name'] === $p2['name'];
                        ?>
                        <div class="match-box-pdf <?= $isFinal ? 'is-final' : ''; ?>">
                            <div class="match-box-header">Match #<?= esc($m['match_number']); ?></div>

                            <div class="match-box-row <?= $isP1Winner ? 'winner' : ''; ?>">
                                <?= esc($p1['name'] ?? '- Menunggu -'); ?>
                                <?php if ($m['score1'] !== null): ?>
                                    <span class="match-score-badge"><?= (int) $m['score1']; ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="match-box-row <?= $isP2Winner ? 'winner' : ''; ?>">
                                <?= esc($p2['name'] ?? '- Menunggu -'); ?>
                                <?php if ($m['score2'] !== null): ?>
                                    <span class="match-score-badge"><?= (int) $m['score2']; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </td>
            <?php endfor; ?>
        </tr>
    </table>
    <?php endif; ?>

    <!-- JADWAL & REKAP PERTANDINGAN -->
    <div style="font-size: 9.5pt; font-weight: bold; color: #002244; margin-bottom: 6px; text-transform: uppercase;">
        Daftar Pertandingan & Hasil Pertandingan
    </div>
    <table class="results-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 18%;">Babak</th>
                <th style="width: 27%;">Peserta 1</th>
                <th style="width: 8%; text-align: center;">Skor</th>
                <th style="width: 27%;">Peserta 2</th>
                <th style="width: 15%;">Pemenang</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $matchIdx = 1;
                foreach ($rounds as $r => $roundData):
                    foreach ($roundData['matches'] as $m):
            ?>
            <tr>
                <td style="text-align: center;"><?= $matchIdx++; ?></td>
                <td><?= esc($roundData['name'] ?? ('Babak ' . $r)); ?></td>
                <td>
                    <strong><?= esc($m['player1']['name'] ?? '-'); ?></strong>
                </td>
                <td style="text-align: center; font-weight: bold;">
                    <?= ($m['score1'] !== null && $m['score2'] !== null) ? ($m['score1'] . ' - ' . $m['score2']) : '-'; ?>
                </td>
                <td>
                    <strong><?= esc($m['player2']['name'] ?? '-'); ?></strong>
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
