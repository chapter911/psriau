<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rincian &amp; Kwitansi Perjalanan Dinas</title>
    <?php
    $tahomaRegPath = FCPATH . 'assets/fonts/tahoma/Tahoma.ttf';
    $tahomaBoldPath = FCPATH . 'assets/fonts/tahoma/Tahoma-Bold.ttf';
    $tahomaRegBase64 = file_exists($tahomaRegPath) ? 'data:font/truetype;charset=utf-8;base64,' . base64_encode(file_get_contents($tahomaRegPath)) : '';
    $tahomaBoldBase64 = file_exists($tahomaBoldPath) ? 'data:font/truetype;charset=utf-8;base64,' . base64_encode(file_get_contents($tahomaBoldPath)) : '';
    ?>
    <style>
        @font-face {
            font-family: 'Tahoma';
            font-style: normal;
            font-weight: normal;
            src: url('<?= $tahomaRegBase64 ?>') format('truetype');
        }
        @font-face {
            font-family: 'Tahoma';
            font-style: normal;
            font-weight: bold;
            src: url('<?= $tahomaBoldBase64 ?>') format('truetype');
        }

        body {
            font-family: 'Tahoma', sans-serif;
            font-size: 11.7px; /* -> 8.76pt */
            margin: 0;
            padding: 18px 20px;
            line-height: 1.4;
        }
        .text-center { text-align: center; }
        .text-left   { text-align: left; }
        .text-right  { text-align: right; }
        .font-bold   { font-weight: bold; }

        .page-break { page-break-after: always; }

        /* Kop Surat */
        .kop-surat-img {
            width: 100%;
            max-height: 150px;
            object-fit: contain;
            margin-bottom: 0;
            display: block;
        }

        /* ============================
           RINCI TABLE
        ============================ */
        .rinci-header {
            margin-bottom: 10px;
            text-align: center;
            font-size: 12.8px; /* -> 9.60pt */
        }
        .rinci-header strong {
            font-size: 17.1px; /* -> 12.84pt */
        }

        .rinci-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .rinci-table th,
        .rinci-table td {
            border: 1px solid #000;
            padding: 4px 5px;
            vertical-align: top;
        }
        .rinci-table th {
            text-align: center;
            font-weight: bold;
            background: transparent;
        }
        /* No border on outer rinci-table so inner table can control borders */
        .rinci-table .no-border-outer {
            border-top: none;
            border-bottom: none;
        }

        /* Nested tables inside rinci TD — no borders */
        .nested-table {
            width: 100%;
            border-collapse: collapse;
        }
        .nested-table td {
            border: none !important;
            padding: 1px 2px !important;
            vertical-align: top;
        }
        /* Underline on specific cells for last item in transport group */
        .nested-table td.underline-td {
            border-bottom: 1px solid #000 !important;
        }

        /* JUMLAH column — two-column mini table */
        .jumlah-inner {
            width: 100%;
            border-collapse: collapse;
        }
        .jumlah-inner td {
            border: none !important;
            padding: 1px 2px !important;
            vertical-align: top;
        }

        /* Footer signature area */
        .rinci-footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        .rinci-footer-table td {
            border: none;
            vertical-align: top;
            padding: 2px 4px;
        }

        /* RAMPUNG section */
        .rampung-separator {
            border-top: 2px solid #000;
            margin: 10px 0 6px 0;
        }
        .rampung-title {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            letter-spacing: 3px;
            margin-bottom: 6px;
            font-size: 11px;
        }
        .rampung-table {
            width: 100%;
            border-collapse: collapse;
        }
        .rampung-table td {
            border: none;
            padding: 1px 4px;
            vertical-align: top;
        }

        /* ============================
           KWITANSI PAGE
        ============================ */

        /* Box wrapping the kwitansi content */
        .kwitansi-box {
            border: 1px solid #000;
            border-top: none;
            padding: 15px 20px;
            min-height: 700px;
            position: relative;
        }

        /* Info table top-right (Tahun Anggaran etc.) */
        .kwitansi-info-table {
            width: 44%;
            float: right;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .kwitansi-info-table td {
            border: 1px solid #000;
            padding: 3px 5px;
            font-size: 11px;
            white-space: nowrap;
        }

        /* Title */
        .kwitansi-title {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            text-decoration: underline;
            letter-spacing: 3px;
            margin-top: 10px;
            margin-bottom: 18px;
            clear: both;
        }

        /* Body rows */
        .kwitansi-body-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .kwitansi-body-table td {
            vertical-align: top;
            padding: 4px 4px;
        }
        .kwitansi-body-table .label-col {
            white-space: nowrap;
        }

        /* TTD table */
        .kwitansi-ttd-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }
        .kwitansi-ttd-table td {
            vertical-align: top;
            text-align: center;
            width: 50%;
            padding: 2px 4px;
        }
    </style>
</head>
<body>

    <?php 
    // ─── Helpers ─────────────────────────────────────────────────────────────
    $formatDate = function($dateStr) {
        if (empty($dateStr)) return '-';
        $ts = strtotime($dateStr);
        if (!$ts) return $dateStr;
        $months = [
            1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
            5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',
            9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
        ];
        return date('d', $ts) . ' ' . $months[(int)date('n', $ts)] . ' ' . date('Y', $ts);
    };

    // Format NIP with proper spacing: 8-6-1-3 format
    $formatNip = function($nip) {
        $nip = preg_replace('/\s+/', '', (string)$nip); // strip existing spaces
        if (strlen($nip) === 18) {
            return substr($nip,0,8).' '.substr($nip,8,6).' '.substr($nip,14,1).' '.substr($nip,15,3);
        }
        return $nip;
    };

    $tglBerangkat = $formatDate($row['periode_mulai'] ?? '');
    $tglKembali   = $formatDate($row['periode_selesai'] ?? '');

    $days = 0;
    if (!empty($row['periode_mulai']) && !empty($row['periode_selesai'])) {
        $start = new \DateTime($row['periode_mulai']);
        $end   = new \DateTime($row['periode_selesai']);
        $days  = $start->diff($end)->days + 1;
    }

    $nomorSurat  = esc($row['nomor_surat_tugas'] ?? '-');
    $nomorSPD    = !empty($row['kode_nomor']) ? esc($row['kode_nomor']) : (!empty($row['nomor_surat_tugas']) ? str_replace('SPT', 'SPD', $nomorSurat) : '-');
    $kotaTujuan  = esc($row['kota_tujuan'] ?? '-');
    $rawMaksud   = !empty($row['perihal_disposisi']) ? $row['perihal_disposisi'] : (!empty($row['perihal']) ? $row['perihal'] : ($row['tujuan'] ?? '-'));
    $tujuanMaksud = esc($rawMaksud);
    $tanggalTtdRaw = !empty($row['tanggal_tanda_tangan'])
        ? (string)$row['tanggal_tanda_tangan']
        : '';
    if (empty($tanggalTtdRaw) && !empty($row['disposisi_id'])) {
        $dbDisp = \Config\Database::connect();
        if ($dbDisp->tableExists('disposisi_perjalanan_dinas')) {
            $dispRow = $dbDisp->table('disposisi_perjalanan_dinas')->select('created_at')->where('id', $row['disposisi_id'])->get()->getRowArray();
            if (!empty($dispRow['created_at'])) {
                $tanggalTtdRaw = date('Y-m-d', strtotime($dispRow['created_at']));
            }
        }
    }
    if (empty($tanggalTtdRaw) && !empty($row['created_at'])) {
        $tanggalTtdRaw = date('Y-m-d', strtotime($row['created_at']));
    }
    $tanggalTtd = $formatDate($tanggalTtdRaw);

    // Resolve Dasar SPT text
    $dasarSptIds = json_decode((string) ($row['dasar_spt_ids_json'] ?? '[]'), true) ?: [];
    $dasarTexts = [];
    if (!empty($dasarSptIds)) {
        $numericIds = array_filter($dasarSptIds, 'is_numeric');
        $customTexts = array_diff($dasarSptIds, $numericIds);
        if (!empty($numericIds)) {
            $dbDasar = (new \App\Models\MstDasarSptModel())->whereIn('id', $numericIds)->orderBy('id', 'ASC')->findAll();
            foreach ($dbDasar as $dD) {
                if (!empty($dD['uraian'])) $dasarTexts[] = $dD['uraian'];
            }
        }
        foreach ($customTexts as $cT) {
            if (!empty($cT)) $dasarTexts[] = $cT;
        }
    }

    $dasarSptStr = '';
    if (!empty($dasarTexts)) {
        $dasarSptStr = implode('; ', $dasarTexts);
    } elseif (!empty($row['nomor_surat_tugas'])) {
        $dasarSptStr = 'Surat Tugas Nomor: ' . $row['nomor_surat_tugas'];
    }

    // Kop Surat base64
    $kopSuratImgUrl = '';
    if (!empty($kop_surat['image_url'])) {
        $path = FCPATH . ltrim($kop_surat['image_url'], '/');
        if (file_exists($path)) {
            $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $data = file_get_contents($path);
            $kopSuratImgUrl = 'data:image/' . $ext . ';base64,' . base64_encode($data);
        }
    }

    $totalPelaksana = count($pelaksana);
    if ($totalPelaksana === 0) {
        $pelaksana[]    = ['nama' => '-', 'nip' => '-', 'jabatan' => '-'];
        $totalPelaksana = 1;
    }

    $terbilangHelper = function($angka) {
        if (function_exists('terbilang_angka')) {
            return ucwords(terbilang_angka($angka)) . ' Rupiah,-';
        }
        return '- Rupiah,-';
    };
    ?>

    <?php foreach ($pelaksana as $index => $utama): ?>
        <?php
        // ─── Cost Calculations ────────────────────────────────────────────────
        $jabatanStr     = trim((string)($utama['jabatan'] ?? ''));
        $jabUpper       = strtoupper($jabatanStr);
        $tarifPenginapan = $biaya_master['penginapan_e4'] ?? 0;
        if (strpos($jabUpper, 'ESELON I') !== false && strpos($jabUpper, 'ESELON II') === false && strpos($jabUpper, 'ESELON III') === false) {
            $tarifPenginapan = $biaya_master['penginapan_e1'] ?? 0;
        } elseif (strpos($jabUpper, 'ESELON II') !== false && strpos($jabUpper, 'ESELON III') === false) {
            $tarifPenginapan = $biaya_master['penginapan_e2'] ?? 0;
        } elseif (strpos($jabUpper, 'ESELON III') !== false) {
            $tarifPenginapan = $biaya_master['penginapan_e3'] ?? 0;
        }

        $rincianBiaya = json_decode((string)($row['rincian_biaya_json'] ?? '{}'), true) ?: [];

        // 1. Transport
        $transportList = $rincianBiaya['transport'] ?? [];
        if (!is_array($transportList) && isset($rincianBiaya['transport_start_date'])) {
            $transportList = [[
                'tgl_mulai'   => $rincianBiaya['transport_start_date'] ?? '',
                'tgl_selesai' => $rincianBiaya['transport_end_date'] ?? '',
                'nominal'     => (int)($rincianBiaya['transport_nominal'] ?? 0),
                'keterangan'  => '',
            ]];
        }

        $calcTransport  = 0;
        $transportItems = [];
        if (is_array($transportList) && count($transportList) > 0) {
            foreach ($transportList as $tItem) {
                $tStart     = $tItem['tgl_mulai'] ?? '';
                $tEnd       = $tItem['tgl_selesai'] ?? '';
                $tNom       = (int)($tItem['nominal'] ?? 0);
                $tKet       = trim((string)($tItem['keterangan'] ?? ''));
                $tJenis     = trim((string)($tItem['jenis'] ?? ''));
                $tIsLumpsum = !empty($tItem['is_lumpsum']);

                $tDays = 0;
                if (!empty($tStart) && !empty($tEnd)) {
                    try {
                        $d1    = new \DateTime($tStart);
                        $d2    = new \DateTime($tEnd);
                        $tDays = max(0, $d1->diff($d2)->days + 1);
                    } catch (\Throwable $e) {}
                }

                $rate = $tNom;
                $sub  = $tIsLumpsum ? $rate : (($tDays > 0) ? ($tDays * $rate) : $rate);
                $calcTransport += $sub;

                $jenisLow = strtolower($tJenis);
                $ketLow   = strtolower($tKet);
                $isTravel = (strpos($jenisLow, 'travel') !== false) || (strpos($ketLow, 'travel') !== false) || ($tJenis === '' && ($tKet === '' || strpos($ketLow, 'kampar') !== false || strpos($ketLow, 'pekanbaru') !== false));

                $tKetFormatted = $tKet;
                if ($isTravel) {
                    $dest = !empty($tKet) ? $tKet : (!empty($kotaTujuan) ? $kotaTujuan : 'Tujuan');
                    $destClean = preg_replace('/^travel\s+/i', '', $dest);
                    $destClean = preg_replace('/^pekanbaru\s*-\s*/i', '', $destClean);
                    $destClean = preg_replace('/\s*\(?pp\)?$/i', '', $destClean);
                    $destClean = trim($destClean);
                    $tKetFormatted = 'Travel Pekanbaru - ' . $destClean . ' (PP)';
                }

                if ($tDays > 0 || $rate > 0 || $tIsLumpsum) {
                    $transportItems[] = [
                        'jenis'   => $tJenis,
                        'ket'     => $tKetFormatted,
                        'days'    => $tDays,
                        'rate'    => $rate,
                        'sub'     => $sub,
                        'lumpsum' => $tIsLumpsum,
                    ];
                }
            }
        }

        // Group transport by jenis
        $transportGroups = [];
        foreach ($transportItems as $ti) {
            $jenis    = $ti['jenis'];
            $jenisLow = strtolower($jenis);
            if (strpos($jenisLow, 'pesawat') !== false) {
                $gKey = 'Pesawat Udara';
            } elseif (strpos($jenisLow, 'taksi') !== false || strpos($jenisLow, 'taxi') !== false) {
                $gKey = 'Taxi';
            } elseif ($jenis !== '') {
                $gKey = $jenis;
            } else {
                $gKey = $ti['ket'] !== '' ? $ti['ket'] : 'Transport';
            }
            if (!isset($transportGroups[$gKey])) {
                $transportGroups[$gKey] = ['label' => $gKey, 'rows' => [], 'exact_subtotal' => 0];
            }
            $transportGroups[$gKey]['rows'][]         = $ti;
            $transportGroups[$gKey]['exact_subtotal'] += $ti['sub'];
        }
        // Compute rounded_subtotal per group (floor to nearest 100)
        // and recalculate calcTransport using rounded values
        $calcTransport = 0;
        foreach ($transportGroups as $gKey => $grp) {
            $exact   = $grp['exact_subtotal'];
            $rounded = (int)(floor($exact / 100) * 100);
            $transportGroups[$gKey]['rounded_subtotal'] = $rounded;
            // If exact equals rounded, only show one row; use exact as-is
            $transportGroups[$gKey]['has_rounded'] = ($rounded !== $exact);
            $calcTransport += $rounded;
        }
        $multiGroup = count($transportGroups) > 1;

        // 2. Uang Harian
        $harianList   = $rincianBiaya['uang_harian'] ?? [];
        if (!is_array($harianList)) $harianList = [];

        $calcHarian    = 0;
        $harianDetails = [];
        if (is_array($harianList) && count($harianList) > 0) {
            foreach ($harianList as $hItem) {
                $hStart = $hItem['tgl_mulai'] ?? '';
                $hEnd   = $hItem['tgl_selesai'] ?? '';
                $hNom   = isset($hItem['nominal']) ? (int)$hItem['nominal'] : 0;
                $hKet   = trim((string)($hItem['keterangan'] ?? ''));
                $hDays  = 0;
                if (!empty($hStart) && !empty($hEnd)) {
                    try {
                        $d1    = new \DateTime($hStart);
                        $d2    = new \DateTime($hEnd);
                        $hDays = max(0, $d1->diff($d2)->days + 1);
                    } catch (\Throwable $e) {}
                }
                $rate   = $hNom > 0 ? $hNom : (int)($biaya_master['harian'] ?? 0);
                if ($hDays == 0 && $rate > 0) $hDays = 1;
                $sub    = $hDays * $rate;
                $calcHarian += $sub;
                if ($hDays > 0 || $rate > 0) {
                    $harianDetails[] = ['days' => $hDays, 'rate' => $rate, 'sub' => $sub, 'ket' => esc($hKet)];
                }
            }
        } else {
            $hDays = max(0, $days);
            $rate  = (int)($biaya_master['harian'] ?? 0);
            $calcHarian = $hDays * $rate;
            $harianDetails[] = ['days' => $hDays, 'rate' => $rate, 'sub' => $calcHarian, 'ket' => ''];
        }

        // 3. Penginapan
        $penginapanList = $rincianBiaya['penginapan'] ?? [];
        if (!is_array($penginapanList) && isset($rincianBiaya['penginapan_start_date'])) {
            $penginapanList = [[
                'tgl_mulai'   => $rincianBiaya['penginapan_start_date'] ?? '',
                'tgl_selesai' => $rincianBiaya['penginapan_end_date'] ?? '',
                'nominal'     => isset($rincianBiaya['penginapan_nominal']) ? (int)$rincianBiaya['penginapan_nominal'] : null,
                'keterangan'  => '',
            ]];
        }

        $calcPenginapan    = 0;
        $penginapanDetails = [];
        if (is_array($penginapanList) && count($penginapanList) > 0) {
            foreach ($penginapanList as $pItem) {
                $pStart    = $pItem['tgl_mulai'] ?? '';
                $pEnd      = $pItem['tgl_selesai'] ?? '';
                $pNomInput = isset($pItem['nominal']) && $pItem['nominal'] !== null && $pItem['nominal'] !== '' ? (int)$pItem['nominal'] : null;
                $pKet      = trim((string)($pItem['keterangan'] ?? ''));
                $pNights   = 0;
                if (!empty($pStart) && !empty($pEnd)) {
                    try {
                        $d1      = new \DateTime($pStart);
                        $d2      = new \DateTime($pEnd);
                        $pNights = max(0, $d1->diff($d2)->days);
                    } catch (\Throwable $e) {}
                } else {
                    $pNights = max(0, $days - 1);
                }
                $rate = $pNomInput !== null && $pNomInput >= 0 ? $pNomInput : (int)($tarifPenginapan * 0.3);
                if ($pNights == 0 && $rate > 0) $pNights = 1;
                $sub = $pNights * $rate;
                $calcPenginapan += $sub;
                if ($pNights > 0 || $rate > 0) {
                    $penginapanDetails[] = ['nights' => $pNights, 'rate' => $rate, 'sub' => $sub, 'ket' => esc($pKet)];
                }
            }
        } else {
            $pNights = max(0, $days - 1);
            $rate    = (int)($tarifPenginapan * 0.3);
            $calcPenginapan = $pNights * $rate;
            $penginapanDetails[] = ['nights' => $pNights, 'rate' => $rate, 'sub' => $calcPenginapan, 'ket' => ''];
        }

        $calcTotal     = $calcHarian + $calcTransport + $calcPenginapan;
        $terbilangText = $terbilangHelper($calcTotal);

        $baseKodeStr = trim((string)($row['kode_nomor'] ?? '1'));
        if (preg_match('/^(\d+)(.*)$/', $baseKodeStr, $m)) {
            $numVal           = (int)$m[1] + $index;
            $padLen           = max(3, strlen($m[1]));
            $displayKodeNomor = str_pad((string)$numVal, $padLen, '0', STR_PAD_LEFT) . $m[2];
        } else {
            $displayKodeNomor = $baseKodeStr;
        }

        $nipUtama = $formatNip($utama['nip'] ?? '');
        $jenisPegUtama = strtolower(trim((string)($utama['jenis_pegawai'] ?? '')));
        if (empty($jenisPegUtama) && !empty($utama['id'])) {
            $dbPeg = \Config\Database::connect();
            if ($dbPeg->tableExists('mst_pegawai')) {
                $pegRow = $dbPeg->table('mst_pegawai')->select('jenis_pegawai')->where('id', $utama['id'])->get()->getRowArray();
                if (!empty($pegRow['jenis_pegawai'])) {
                    $jenisPegUtama = strtolower(trim((string)$pegRow['jenis_pegawai']));
                }
            }
        }
        $formatNamaGelar = function($n) {
            $n = trim((string)$n);
            if ($n === '' || $n === '-') return '-';
            if (strpos($n, ',') !== false) {
                $p = explode(',', $n, 2);
                return strtoupper(trim($p[0])) . ', ' . trim($p[1]);
            }
            return strtoupper($n);
        };
        ?>

        <!-- ========================= RINCI PAGE ========================= -->

        <div class="rinci-header">
            <strong>RINCIAN BIAYA PERJALANAN DINAS</strong><br>
            LAMPIRAN SPD NOMOR : <?= $nomorSPD; ?><br>
            TANGGAL : <?= strtoupper($tanggalTtd); ?>
        </div>

        <table class="rinci-table">
            <thead>
                <tr>
                    <th style="width:5%;">No</th>
                    <th style="width:53%;">RINCIAN BIAYA</th>
                    <th style="width:24%;">JUMLAH</th>
                    <th style="width:18%;">KETERANGAN</th>
                </tr>
            </thead>
            <tbody>

                <!-- 1. BIAYA TRANSPORT -->
                <tr>
                    <td class="text-center" style="font-weight:bold;">1</td>
                    <td>
                        <strong>BIAYA TRANSPORT :</strong><br>
                        <?php if (empty($transportGroups)): ?>
                            <br>
                        <?php elseif (!$multiGroup): ?>
                            <!-- Single group: flat listing without group header -->
                            <?php $onlyGroup = reset($transportGroups); ?>
                            <?php $onlyRows  = $onlyGroup['rows']; ?>
                            <table class="nested-table">
                                <?php foreach ($onlyRows as $ri => $ti): ?>
                                <?php
                                    $isLast = ($ri === count($onlyRows) - 1);
                                    $desc   = $ti['ket'] !== '' ? esc($ti['ket']) : ($ti['lumpsum'] ? 'Transport (PP)' : ($ti['days'] > 0 ? $ti['days'] . ' hari x Rp ' . number_format($ti['rate'], 0, ',', '.') : 'Transport'));
                                    $ulCls = $isLast ? 'underline-td' : '';
                                ?>
                                <tr>
                                    <td style="width:48%;"><?= $desc; ?></td>
                                    <td class="<?= $ulCls; ?>" style="width:12%;">Rp.</td>
                                    <td class="<?= $ulCls; ?>" style="width:40%; text-align:right;"><?= number_format($ti['sub'], 0, ',', '.'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </table>
                        <?php else: ?>
                            <!-- Multi-group: group headers (NOT bold), items, underline on last, then exact+rounded subtotals -->
                            <table class="nested-table">
                                <?php foreach ($transportGroups as $gLabel => $grp): ?>
                                <!-- Group header — plain text, no space before colon -->
                                <tr>
                                    <td colspan="4" style="padding-top:2px !important;"><?= esc($gLabel); ?>:</td>
                                </tr>
                                <?php
                                    $grpRows    = $grp['rows'];
                                    $exactTotal = $grp['exact_subtotal'];
                                    $rndTotal   = $grp['rounded_subtotal'];
                                    $hasRnd     = $grp['has_rounded'];
                                ?>
                                <?php foreach ($grpRows as $ri => $ti): ?>
                                <?php
                                    $dest    = $ti['ket'] !== '' ? esc($ti['ket']) : '';
                                    $isLast  = ($ri === count($grpRows) - 1);
                                    $ulCls   = $isLast ? 'underline-td' : '';
                                ?>
                                <tr>
                                    <td style="width:45%; padding-left:6px !important;"><?= $dest; ?></td>
                                    <td class="<?= $ulCls; ?>" style="width:10%;">Rp.</td>
                                    <td class="<?= $ulCls; ?>" style="width:25%; text-align:right;"><?= number_format($ti['sub'], 0, ',', '.'); ?></td>
                                    <td style="width:20%;"></td>
                                </tr>
                                <?php endforeach; ?>
                                <!-- Exact subtotal row (plain, right-aligned under nominals) -->
                                <tr>
                                    <td colspan="2"></td>
                                    <td style="width:25%; text-align:right; padding-top:1px !important;"><?= number_format($exactTotal, 0, ',', '.'); ?></td>
                                    <td style="width:20%;"></td>
                                </tr>
                                <?php if ($hasRnd): ?>
                                <!-- Rounded official subtotal (bold, far right edge) -->
                                <tr>
                                    <td colspan="3"></td>
                                    <td style="width:20%; text-align:right; font-weight:bold; padding-bottom:4px !important;"><?= number_format($rndTotal, 0, ',', '.'); ?></td>
                                </tr>
                                <?php else: ?>
                                <!-- No rounding needed, just spacing -->
                                <tr><td colspan="4" style="padding-bottom:3px !important;"></td></tr>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </table>
                        <?php endif; ?>
                    </td>
                    <!-- JUMLAH column: Rp. left, amount right -->
                    <td style="vertical-align:top;">
                        <table class="jumlah-inner">
                            <tr>
                                <td style="width:22%;">Rp.</td>
                                <td style="width:78%; text-align:right;"><?= number_format($calcTransport, 0, ',', '.'); ?></td>
                            </tr>
                        </table>
                    </td>
                    <td></td>
                </tr>

                <!-- 2. UANG HARIAN -->
                <tr>
                    <td class="text-center" style="font-weight:bold;">2</td>
                    <td>
                        <strong>UANG HARIAN</strong><br>
                        Uang Makan, Uang Transport Lokal, Uang Saku selama :<br>
                        <?php if (empty($harianDetails)): ?>
                            <br>
                        <?php else: ?>
                            <table class="nested-table">
                                <?php foreach ($harianDetails as $hd): ?>
                                <tr>
                                    <td style="width:10%;"><?= $hd['days']; ?></td>
                                    <td style="width:8%;">hari</td>
                                    <td style="width:5%;">x</td>
                                    <td style="width:7%;">Rp</td>
                                    <td style="width:32%; text-align:right;"><?= number_format($hd['rate'], 0, ',', '.'); ?></td>
                                    <td style="width:8%;">Rp</td>
                                    <td style="width:30%; text-align:right;"><?= number_format($hd['sub'], 0, ',', '.'); ?></td>
                                </tr>
                                <?php if ($hd['ket'] !== ''): ?>
                                <tr>
                                    <td colspan="7" style="padding-left:12px !important; font-style:italic; color:#555;">(<?= $hd['ket']; ?>)</td>
                                </tr>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </table>
                        <?php endif; ?>
                    </td>
                    <td style="vertical-align:top;">
                        <table class="jumlah-inner">
                            <tr>
                                <td style="width:22%;">Rp.</td>
                                <td style="width:78%; text-align:right;"><?= number_format($calcHarian, 0, ',', '.'); ?></td>
                            </tr>
                        </table>
                    </td>
                    <td></td>
                </tr>

                <!-- 3. UANG PENGINAPAN -->
                <tr>
                    <td class="text-center" style="font-weight:bold;">3</td>
                    <td>
                        <strong>UANG PENGINAPAN</strong><br>
                        Uang penginapan selama :<br>
                        <?php if (empty($penginapanDetails)): ?>
                            <br>
                        <?php else: ?>
                            <table class="nested-table">
                                <?php foreach ($penginapanDetails as $pd): ?>
                                <tr>
                                    <td style="width:10%;"><?= $pd['nights']; ?></td>
                                    <td style="width:8%;">malam</td>
                                    <td style="width:5%;">x</td>
                                    <td style="width:7%;">Rp</td>
                                     <td style="width:32%; text-align:right;"><?= ((int)($pd['nights'] ?? 0) === 0) ? '-' : number_format($pd['rate'], 0, ',', '.'); ?></td>
                                    <td style="width:8%;">Rp</td>
                                    <td style="width:30%; text-align:right;"><?= number_format($pd['sub'], 0, ',', '.'); ?></td>
                                </tr>
                                <?php if ($pd['ket'] !== ''): ?>
                                <tr>
                                    <td colspan="7" style="padding-left:12px !important; font-style:italic; color:#555;">(<?= $pd['ket']; ?>)</td>
                                </tr>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </table>
                        <?php endif; ?>
                    </td>
                    <td style="vertical-align:top;">
                        <table class="jumlah-inner">
                            <tr>
                                <td style="width:22%;">Rp.</td>
                                <td style="width:78%; text-align:right;"><?= number_format($calcPenginapan, 0, ',', '.'); ?></td>
                            </tr>
                        </table>
                    </td>
                    <td></td>
                </tr>

                <!-- JUMLAH row -->
                <tr>
                    <td colspan="2" class="font-bold">JUMLAH :</td>
                    <td style="border-bottom: 2px solid #000;">
                        <table class="jumlah-inner" style="font-weight:bold;">
                            <tr>
                                <td style="width:22%;">Rp.</td>
                                <td style="width:78%; text-align:right;"><?= number_format($calcTotal, 0, ',', '.'); ?></td>
                            </tr>
                        </table>
                    </td>
                    <td></td>
                </tr>

                <!-- TERBILANG row -->
                <tr>
                    <td colspan="3" style="font-size:11px;">
                        <strong>TERBILANG :</strong>&nbsp;&nbsp; <?= $terbilangText; ?>
                    </td>
                    <td></td>
                </tr>

            </tbody>
        </table>

        <!-- Signatures: Bendahara (left) & Yang Menerima (right) -->
        <table class="rinci-footer-table">
            <tr>
                <!-- LEFT: Bendahara -->
                <td style="width:48%; vertical-align:top;">
                    Telah dibayar uang sebesar<br><br>
                    Rp.&nbsp;&nbsp;&nbsp;&nbsp; <?= number_format($calcTotal, 0, ',', '.'); ?><br><br>
                    Pekanbaru, &nbsp;&nbsp;&nbsp; <?= $tanggalTtd; ?><br>
                    Bendahara Pengeluaran,<br>
                    <div style="height:65px;"></div>
                    <span style="text-decoration:underline;" class="font-bold">KH. SRI HANDAYANI, S.Si., M.T.</span><br>
                    NIP. 19820402 201412 2 002
                </td>

                <!-- RIGHT: Yang Menerima -->
                <td style="width:52%; vertical-align:top; text-align:left; padding-left:30px;">
                    Pekanbaru, &nbsp;&nbsp;&nbsp; <?= $tanggalTtd; ?><br>
                    Telah terima sejumlah uang sebesar:<br><br>
                    Rp.&nbsp;&nbsp;&nbsp;&nbsp; <?= number_format($calcTotal, 0, ',', '.'); ?><br><br>
                    Yang Menerima :<br>
                    <div style="height:65px;"></div>
                    <span style="text-decoration:underline;" class="font-bold"><?= esc($formatNamaGelar($utama['nama'])); ?></span>
                    <?php if (should_show_nip($utama) && !empty($utama['nip'])): ?>
                        <br><?= $nipLabelUtama; ?><?= $nipUtama; ?>
                    <?php endif; ?>
                </td>
            </tr>
        </table>

        <!-- RAMPUNG Separator -->
        <div class="rampung-separator"></div>

        <!-- RAMPUNG Title -->
        <div class="rampung-title">P E R H I T U N G A N &nbsp; S P D &nbsp; R A M P U N G</div>

        <!-- RAMPUNG Body -->
        <table class="rampung-table">
            <tr>
                <td style="width:60%;">
                    <table style="width:100%; border:none; border-collapse:collapse;">
                        <tr>
                            <td style="border:none; padding:1px 0; white-space:nowrap;">Ditetapkan Sejumlah</td>
                            <td style="border:none; padding:1px 4px;">………………………………………………………………………</td>
                        </tr>
                        <tr>
                            <td style="border:none; padding:1px 0; white-space:nowrap;">Yang dibayar semula</td>
                            <td style="border:none; padding:1px 4px;">………………………………………………………………………</td>
                        </tr>
                        <tr>
                            <td style="border:none; padding:1px 0; white-space:nowrap;">Sisa kurang / Lebih</td>
                            <td style="border:none; padding:1px 4px;">………………………………………………………………………</td>
                        </tr>
                    </table>
                </td>
                <td style="width:40%; vertical-align:top;">
                    <table style="width:100%; border:none; border-collapse:collapse;">
                        <tr>
                            <td style="width:15%; padding:1px 4px; border:none;">Rp</td>
                            <td style="width:85%; text-align:right; padding:1px 4px; border:none;"><?= number_format($calcTotal, 0, ',', '.'); ?></td>
                        </tr>
                        <tr>
                            <td style="padding:1px 4px; border:none;">Rp</td>
                            <td style="text-align:right; border-bottom:1px solid #000 !important; padding:1px 4px;">-</td>
                        </tr>
                        <tr>
                            <td style="padding:1px 4px; border:none;">Rp</td>
                            <td style="text-align:right; padding:1px 4px; border:none;">-</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- RAMPUNG TTD: PPK only (right side) -->
        <table class="rampung-table" style="margin-top:20px;">
            <tr>
                <td style="width:50%;"></td>
                <td style="width:50%; text-align:center;">
                    Pejabat Pembuat Komitmen<br>
                    Pelaksanaan Prasarana Strategis<br>
                    <div style="height:62px;"></div>
                    <span style="text-decoration:underline;" class="font-bold">NURHIDAYAT NUGROHO, S.Ars.</span><br>
                    NIP. 19901221 201802 1 001
                </td>
            </tr>
        </table>

        <div class="page-break"></div>

        <!-- ========================= KWITANSI PAGE ========================= -->

        <?php if ($kopSuratImgUrl): ?>
            <img src="<?= $kopSuratImgUrl; ?>" class="kop-surat-img" alt="Kop Surat">
        <?php endif; ?>

        <!-- Double border separator below kop -->
        <div style="border-top:2px solid #000; margin:0;"></div>
        <div style="border-top:1px solid #000; margin:3px 0 0 0;"></div>

        <!-- Box wrapping kuitansi body -->
        <div class="kwitansi-box">

            <!-- Info table: top right -->
            <table class="kwitansi-info-table">
                <tr>
                    <td style="width:40%;">Tahun Anggaran</td>
                    <td style="width:60%;"><?= date('Y'); ?></td>
                </tr>
                <tr>
                    <td>Nomor Bukti</td>
                    <td><?= esc($displayKodeNomor); ?></td>
                </tr>
                <tr>
                    <td>Mata Anggaran</td>
                    <td><?= esc($mata_anggaran ?? ''); ?></td>
                </tr>
            </table>

            <!-- Title -->
            <div class="kwitansi-title">K U I T A N S I</div>

            <!-- Body rows -->
            <table class="kwitansi-body-table">
                <tr>
                    <td class="label-col" style="width:25%; padding-bottom:10px;">Sudah di terima dari</td>
                    <td style="width:3%; padding-bottom:10px;">:</td>
                    <td style="width:72%; padding-bottom:10px; font-weight:bold;">PEJABAT PEMBUAT KOMITMEN PELAKSANAAN PRASARANA STRATEGIS</td>
                </tr>
                <tr>
                    <td class="label-col" style="padding-bottom:10px;">Jumlah Uang</td>
                    <td style="padding-bottom:10px;">:</td>
                    <td style="padding-bottom:10px;" class="font-bold">
                        Rp.&nbsp;&nbsp;&nbsp; <?= number_format($calcTotal, 0, ',', '.'); ?>
                    </td>
                </tr>
                <tr>
                    <td class="label-col" style="padding-bottom:12px;">Terbilang</td>
                    <td style="padding-bottom:12px;">:</td>
                    <td style="padding-bottom:12px;"><span style="font-style:italic; font-weight:bold;"><?= $terbilangText; ?></span></td>
                </tr>
                <tr>
                    <td class="label-col" style="padding-bottom:12px; vertical-align:top;">Untuk Pembayaran</td>
                    <td style="padding-bottom:12px; vertical-align:top;">:</td>
                    <td style="padding-bottom:12px; text-align:justify; line-height:1.6;">
                        Perjalanan Dinas a.n. <?= esc($formatNamaGelar($utama['nama'])); ?> <?= esc($utama['jabatan']); ?> dalam rangka <?= $tujuanMaksud; ?><?= !empty($dasarSptStr) ? (', sesuai dengan ' . esc($dasarSptStr)) : '' ?>, sebagaimana daftar perincian terlampir.
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="padding-top:6px; padding-bottom:2px;">Berdasarkan SPD</td>
                </tr>
                <tr>
                    <td class="label-col" style="padding-left:8px;">Nomor</td>
                    <td>:</td>
                    <td><?= $nomorSPD; ?></td>
                </tr>
                <tr>
                    <td class="label-col" style="padding-left:8px; padding-bottom:3px;">Tanggal</td>
                    <td style="padding-bottom:3px;">:</td>
                    <td style="padding-bottom:3px;"><?= strtoupper($tanggalTtd); ?></td>
                </tr>
                <tr>
                    <td class="label-col" style="padding-left:8px; padding-bottom:3px;">Untuk perjalanan dinas dari</td>
                    <td style="padding-bottom:3px;">:</td>
                    <td style="padding-bottom:3px;">Pekanbaru - <?= $kotaTujuan; ?></td>
                </tr>
                <tr>
                    <td class="label-col" style="padding-left:8px;">Berangkat dari tanggal</td>
                    <td>:</td>
                    <td><?= ($tglBerangkat === $tglKembali) ? $tglBerangkat : ($tglBerangkat . ' s/d ' . $tglKembali); ?></td>
                </tr>
            </table>

            <!-- TTD -->
            <table class="kwitansi-ttd-table">
                <tr>
                    <!-- LEFT: PPK -->
                    <td style="text-align:center;">
                        An. Kuasa Pengguna Anggaran<br>
                        Pejabat Pembuat Komitmen<br>
                        Pelaksanaan Prasarana Strategis
                        <div style="height:65px;"></div>
                        <span style="text-decoration:underline;" class="font-bold">NURHIDAYAT NUGROHO, S.Ars.</span><br>
                        NIP. 19901221 201802 1 001
                    </td>
                    <!-- RIGHT: Kepala Satker -->
                    <td style="text-align:center;">
                        <?php
                        $jabWords = explode(' ', trim((string)$jabatanUtama));
                        $jLen = strlen(trim((string)$jabatanUtama));
                        if ($jLen > 32 && count($jabWords) > 1) {
                            $jMid = (int)ceil($jLen / 2);
                            $jLine1 = ''; $jLine2 = '';
                            foreach ($jabWords as $w) {
                                if ($jLine1 === '' || (strlen($jLine1) + strlen($w) + 1) <= ($jMid + 5)) {
                                    $jLine1 .= ($jLine1 === '' ? '' : ' ') . $w;
                                } else {
                                    $jLine2 .= ($jLine2 === '' ? '' : ' ') . $w;
                                }
                            }
                            echo esc($jLine1) . '<br>' . esc($jLine2) . '<br>';
                        } else {
                            echo esc($jabatanUtama) . '<br>';
                        }
                        ?>
                        <div style="height:65px;"></div>
                        <span style="text-decoration:underline;" class="font-bold"><?= esc($formatNamaGelar($utama['nama'])); ?></span>
                        <?php if (should_show_nip($utama) && !empty($utama['nip'])): ?>
                            <br><?= $nipLabelUtama; ?><?= $nipUtama; ?>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>

        </div><!-- /.kwitansi-box -->

        <?php if ($index < $totalPelaksana - 1): ?>
            <div class="page-break"></div>
        <?php endif; ?>
    <?php endforeach; ?>

</body>
</html>
