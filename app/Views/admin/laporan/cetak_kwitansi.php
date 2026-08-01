<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rincian & Kwitansi Perjalanan Dinas</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 0;
            line-height: 1.3;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-weight-bold { font-weight: bold; }
        
        .page-break { page-break-after: always; }
        
        /* Kop Surat */
        .kop-surat-img {
            width: 100%;
            max-height: 120px;
            object-fit: contain;
            margin-bottom: 10px;
        }

        /* RINCI Table */
        .rinci-header {
            margin-bottom: 15px;
        }
        .rinci-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .rinci-table th, .rinci-table td {
            border: 1.5px solid #000;
            padding: 3px 4px;
            vertical-align: top;
        }
        .rinci-table th {
            text-align: center;
            font-weight: bold;
        }
        .rinci-footer-table {
            width: 100%;
            border: none;
            margin-top: 10px;
        }
        .rinci-footer-table td {
            vertical-align: top;
            padding: 2px;
        }
        
        .rampung-table {
            width: 100%;
            border: none;
            margin-top: 20px;
        }
        .rampung-table td {
            vertical-align: top;
            padding: 2px;
        }

        /* Nested table inside TD without borders */
        .nested-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }
        .nested-table td {
            border: none !important;
            padding: 1px 0 !important;
        }

        /* KWITANSI styles */
        .kwitansi-header-table {
            width: 45%;
            margin-bottom: 15px;
            float: right;
            border-collapse: collapse;
        }
        .kwitansi-header-table td {
            border: 1.5px solid #000;
            padding: 2px 4px;
        }
        .kwitansi-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            text-decoration: underline;
            margin-top: 60px;
            margin-bottom: 20px;
            letter-spacing: 2px;
            clear: both;
        }
        .kwitansi-body-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .kwitansi-body-table td {
            vertical-align: top;
            padding: 4px;
        }
        .kwitansi-ttd-table {
            width: 100%;
            margin-top: 30px;
        }
        .kwitansi-ttd-table td {
            vertical-align: top;
            text-align: center;
            width: 50%;
        }
    </style>
</head>
<body>

    <?php 
    // Format Date Function
    $formatDate = function($dateStr) {
        if (empty($dateStr)) return '-';
        $ts = strtotime($dateStr);
        if (!$ts) return $dateStr;
        
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        return date('d', $ts) . ' ' . $months[(int)date('n', $ts)] . ' ' . date('Y', $ts);
    };
    
    $tglBerangkat = $formatDate($row['periode_mulai'] ?? '');
    $tglKembali = $formatDate($row['periode_selesai'] ?? '');
    
    // Days calc
    $days = 0;
    if (!empty($row['periode_mulai']) && !empty($row['periode_selesai'])) {
        $start = new \DateTime($row['periode_mulai']);
        $end = new \DateTime($row['periode_selesai']);
        $days = $start->diff($end)->days + 1;
    }
    
    $nomorSurat = esc($row['nomor_surat_tugas'] ?? '-');
    $nomorSPD = str_replace('SPT', 'SPD', $nomorSurat);
    $kotaTujuan = esc($row['kota_tujuan'] ?? '-');
    $tujuanMaksud = esc($row['tujuan'] ?? '-');
    $tanggalTtd = $formatDate($row['tanggal_tanda_tangan'] ?? date('Y-m-d'));
    
    $kopSuratImgUrl = '';
    if (!empty($kop_surat['image_url'])) {
        $path = FCPATH . ltrim($kop_surat['image_url'], '/');
        if (file_exists($path)) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $data = file_get_contents($path);
            $kopSuratImgUrl = 'data:image/' . $ext . ';base64,' . base64_encode($data);
        }
    }
    
    $totalPelaksana = count($pelaksana);
    if ($totalPelaksana === 0) {
        $pelaksana[] = ['nama' => '-', 'nip' => '-', 'jabatan' => '-'];
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
        // Cost calc
        $jabatanStr = trim((string)($utama['jabatan'] ?? ''));
        $jabUpper = strtoupper($jabatanStr);
        $tarifPenginapan = $biaya_master['penginapan_e4'] ?? 0;
        if (strpos($jabUpper, 'ESELON I') !== false && strpos($jabUpper, 'ESELON II') === false && strpos($jabUpper, 'ESELON III') === false) {
            $tarifPenginapan = $biaya_master['penginapan_e1'] ?? 0;
        } elseif (strpos($jabUpper, 'ESELON II') !== false && strpos($jabUpper, 'ESELON III') === false) {
            $tarifPenginapan = $biaya_master['penginapan_e2'] ?? 0;
        } elseif (strpos($jabUpper, 'ESELON III') !== false) {
            $tarifPenginapan = $biaya_master['penginapan_e3'] ?? 0;
        }
        
        $rincianBiaya = json_decode((string)($row['rincian_biaya_json'] ?? '{}'), true) ?: [];

        // 1. Transport Calculation (Multi-row):
        $transportList = $rincianBiaya['transport'] ?? [];
        if (!is_array($transportList) && isset($rincianBiaya['transport_start_date'])) {
            $transportList = [[
                'tgl_mulai'   => $rincianBiaya['transport_start_date'] ?? '',
                'tgl_selesai' => $rincianBiaya['transport_end_date'] ?? '',
                'nominal'     => (int) ($rincianBiaya['transport_nominal'] ?? 0),
                'keterangan'  => '',
            ]];
        }

        $calcTransport = 0;
        $transportDetails = [];
        if (is_array($transportList) && count($transportList) > 0) {
            foreach ($transportList as $tItem) {
                $tStart = $tItem['tgl_mulai'] ?? '';
                $tEnd   = $tItem['tgl_selesai'] ?? '';
                $tNom   = (int) ($tItem['nominal'] ?? 0);
                $tKet   = trim((string) ($tItem['keterangan'] ?? ''));
                $tJenis = trim((string) ($tItem['jenis'] ?? ''));
                $tIsLumpsum = !empty($tItem['is_lumpsum']);

                $tDays = 0;
                if (!empty($tStart) && !empty($tEnd)) {
                    try {
                        $d1 = new \DateTime($tStart);
                        $d2 = new \DateTime($tEnd);
                        $tDays = max(0, $d1->diff($d2)->days + 1);
                    } catch (\Throwable $e) {}
                }
                
                $rate = $tNom;
                
                if ($tIsLumpsum) {
                    $sub = $rate;
                } else {
                    $sub = ($tDays > 0) ? ($tDays * $rate) : $rate;
                }
                
                $calcTransport += $sub;

                if ($tDays > 0 || $rate > 0 || $tIsLumpsum) {
                    $descParts = [];
                    if ($tJenis !== '') {
                        $descParts[] = $tJenis;
                    }
                    if ($tKet !== '') {
                        $descParts[] = $tKet;
                    }
                    
                    if (!empty($descParts)) {
                        $desc = esc(implode(' - ', $descParts));
                    } else {
                        if ($tIsLumpsum) {
                            $desc = 'Transport (PP)';
                        } elseif ($tDays > 0) {
                            $desc = $tDays . ' hari x Rp ' . number_format($rate, 0, ',', '.');
                        } else {
                            $desc = 'Transport';
                        }
                    }
                    $transportDetails[] = [
                        'desc' => $desc,
                        'sub' => $sub
                    ];
                }
            }
        }

        // 2. Uang Harian Calculation (Multi-row):
        $harianList = $rincianBiaya['uang_harian'] ?? [];
        if (!is_array($harianList)) {
            $harianList = [];
        }

        $calcHarian = 0;
        $harianDetails = [];
        if (is_array($harianList) && count($harianList) > 0) {
            foreach ($harianList as $hItem) {
                $hStart = $hItem['tgl_mulai'] ?? '';
                $hEnd   = $hItem['tgl_selesai'] ?? '';
                $hNom   = isset($hItem['nominal']) ? (int) $hItem['nominal'] : 0;
                $hKet   = trim((string) ($hItem['keterangan'] ?? ''));

                $hDays = 0;
                if (!empty($hStart) && !empty($hEnd)) {
                    try {
                        $d1 = new \DateTime($hStart);
                        $d2 = new \DateTime($hEnd);
                        $hDays = max(0, $d1->diff($d2)->days + 1);
                    } catch (\Throwable $e) {}
                }
                
                $rate = $hNom > 0 ? $hNom : (int) ($biaya_master['harian'] ?? 0);
                // Assume 1 day if not set but nominal is set
                if ($hDays == 0 && $rate > 0) $hDays = 1;
                $sub = $hDays * $rate;
                $calcHarian += $sub;

                if ($hDays > 0 || $rate > 0) {
                    $harianDetails[] = [
                        'days' => $hDays,
                        'rate' => $rate,
                        'sub' => $sub,
                        'ket' => esc($hKet)
                    ];
                }
            }
        } else {
            $hDays = max(0, $days);
            $rate = (int) ($biaya_master['harian'] ?? 0);
            $calcHarian = $hDays * $rate;
            $harianDetails[] = [
                'days' => $hDays,
                'rate' => $rate,
                'sub' => $calcHarian,
                'ket' => ''
            ];
        }

        // 3. Penginapan Calculation (Multi-row):
        $penginapanList = $rincianBiaya['penginapan'] ?? [];
        if (!is_array($penginapanList) && isset($rincianBiaya['penginapan_start_date'])) {
            $penginapanList = [[
                'tgl_mulai'   => $rincianBiaya['penginapan_start_date'] ?? '',
                'tgl_selesai' => $rincianBiaya['penginapan_end_date'] ?? '',
                'nominal'     => isset($rincianBiaya['penginapan_nominal']) ? (int) $rincianBiaya['penginapan_nominal'] : null,
                'keterangan'  => '',
            ]];
        }

        $calcPenginapan = 0;
        $penginapanDetails = [];
        if (is_array($penginapanList) && count($penginapanList) > 0) {
            foreach ($penginapanList as $pItem) {
                $pStart = $pItem['tgl_mulai'] ?? '';
                $pEnd   = $pItem['tgl_selesai'] ?? '';
                $pNomInput = isset($pItem['nominal']) && $pItem['nominal'] !== null && $pItem['nominal'] !== '' ? (int) $pItem['nominal'] : null;
                $pKet   = trim((string) ($pItem['keterangan'] ?? ''));

                $pNights = 0;
                if (!empty($pStart) && !empty($pEnd)) {
                    try {
                        $d1 = new \DateTime($pStart);
                        $d2 = new \DateTime($pEnd);
                        $pNights = max(0, $d1->diff($d2)->days);
                    } catch (\Throwable $e) {}
                } else {
                    $pNights = max(0, $days - 1);
                }

                if ($pNomInput !== null && $pNomInput >= 0) {
                    $rate = $pNomInput;
                } else {
                    $rate = (int) ($tarifPenginapan * 0.3);
                }
                
                // fallback to 1 night if nights=0 but rate>0
                if ($pNights == 0 && $rate > 0) $pNights = 1;

                $sub = $pNights * $rate;
                $calcPenginapan += $sub;

                if ($pNights > 0 || $rate > 0) {
                    $penginapanDetails[] = [
                        'nights' => $pNights,
                        'rate' => $rate,
                        'sub' => $sub,
                        'ket' => esc($pKet)
                    ];
                }
            }
        } else {
            $pNights = max(0, $days - 1);
            $rate = (int) ($tarifPenginapan * 0.3);
            $calcPenginapan = $pNights * $rate;
            $penginapanDetails[] = [
                'nights' => $pNights,
                'rate' => $rate,
                'sub' => $calcPenginapan,
                'ket' => ''
            ];
        }

        $calcTotal = $calcHarian + $calcTransport + $calcPenginapan;
        $terbilangText = $terbilangHelper($calcTotal);
        
        $baseKodeStr = trim((string)($row['kode_nomor'] ?? '1'));
        if (preg_match('/^(\d+)(.*)$/', $baseKodeStr, $m)) {
            $numVal = (int) $m[1] + $index;
            $padLen = max(3, strlen($m[1]));
            $displayKodeNomor = str_pad((string) $numVal, $padLen, '0', STR_PAD_LEFT) . $m[2];
        } else {
            $displayKodeNomor = $baseKodeStr;
        }
        ?>

        <!-- ======================= RINCI PAGE ======================= -->
        
        <!-- Note: Header without Kop Surat to match PDF -->
        <div class="rinci-header text-center">
            <strong style="font-size: 14px;">RINCIAN BIAYA PERJALANAN DINAS</strong><br>
            LAMPIRAN SPD NOMOR : <?= $nomorSPD; ?><br>
            TANGGAL : <?= strtoupper($tanggalTtd); ?>
        </div>

        <table class="rinci-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 50%;">RINCIAN BIAYA</th>
                    <th style="width: 25%;">JUMLAH</th>
                    <th style="width: 20%;">KETERANGAN</th>
                </tr>
            </thead>
            <tbody>
                <!-- 1. BIAYA TRANSPORT -->
                <tr>
                    <td class="text-center">1</td>
                    <td>
                        <strong>BIAYA TRANSPORT :</strong><br>
                        <?php if (empty($transportDetails)): ?>
                            <br>
                        <?php else: ?>
                            <table class="nested-table">
                                <?php foreach ($transportDetails as $td): ?>
                                <tr>
                                    <td style="width: 50%;"><?= $td['desc']; ?></td>
                                    <td style="width: 15%;">Rp.</td>
                                    <td style="width: 35%; text-align: right;"><?= number_format($td['sub'], 0, ',', '.'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </table>
                        <?php endif; ?>
                    </td>
                    <td style="vertical-align: top;">
                        <div style="float: left;">Rp.</div>
                        <div style="float: right;"><?= number_format($calcTransport, 0, ',', '.'); ?></div>
                        <div style="clear: both;"></div>
                    </td>
                    <td></td>
                </tr>

                <!-- 2. UANG HARIAN -->
                <tr>
                    <td class="text-center">2</td>
                    <td>
                        <strong>UANG HARIAN</strong><br>
                        Uang Makan, Uang Transport Lokal, Uang Saku selama :<br>
                        <?php if (empty($harianDetails)): ?>
                            <br>
                        <?php else: ?>
                            <table class="nested-table">
                                <?php foreach ($harianDetails as $hd): ?>
                                <tr>
                                    <td style="width: 15%;">&nbsp; <?= $hd['days']; ?> hari</td>
                                    <td style="width: 5%;">x</td>
                                    <td style="width: 10%;">Rp</td>
                                    <td style="width: 25%; text-align: right;"><?= number_format($hd['rate'], 0, ',', '.'); ?></td>
                                    <td style="width: 10%; text-align: center;">Rp</td>
                                    <td style="width: 35%; text-align: right;"><?= number_format($hd['sub'], 0, ',', '.'); ?></td>
                                </tr>
                                <?php if ($hd['ket'] !== ''): ?>
                                <tr>
                                    <td colspan="6" style="padding-left: 10px !important; font-style: italic; color: #555;">(<?= $hd['ket']; ?>)</td>
                                </tr>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </table>
                        <?php endif; ?>
                    </td>
                    <td style="vertical-align: top;">
                        <div style="float: left;">Rp.</div>
                        <div style="float: right;"><?= number_format($calcHarian, 0, ',', '.'); ?></div>
                        <div style="clear: both;"></div>
                    </td>
                    <td></td>
                </tr>

                <!-- 3. UANG PENGINAPAN -->
                <tr>
                    <td class="text-center">3</td>
                    <td>
                        <strong>UANG PENGINAPAN</strong><br>
                        Uang penginapan selama :<br>
                        <?php if (empty($penginapanDetails)): ?>
                            <br>
                        <?php else: ?>
                            <table class="nested-table">
                                <?php foreach ($penginapanDetails as $pd): ?>
                                <tr>
                                    <td style="width: 15%;">&nbsp; <?= $pd['nights']; ?> malam</td>
                                    <td style="width: 5%;">x</td>
                                    <td style="width: 10%;">Rp</td>
                                    <td style="width: 25%; text-align: right;"><?= number_format($pd['rate'], 0, ',', '.'); ?></td>
                                    <td style="width: 10%; text-align: center;">Rp</td>
                                    <td style="width: 35%; text-align: right;"><?= number_format($pd['sub'], 0, ',', '.'); ?></td>
                                </tr>
                                <?php if ($pd['ket'] !== ''): ?>
                                <tr>
                                    <td colspan="6" style="padding-left: 10px !important; font-style: italic; color: #555;">(<?= $pd['ket']; ?>)</td>
                                </tr>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </table>
                        <?php endif; ?>
                    </td>
                    <td style="vertical-align: top;">
                        <div style="float: left;">Rp.</div>
                        <div style="float: right;"><?= number_format($calcPenginapan, 0, ',', '.'); ?></div>
                        <div style="clear: both;"></div>
                    </td>
                    <td></td>
                </tr>

                <!-- JUMLAH & TERBILANG -->
                <tr>
                    <td colspan="2"><strong>JUMLAH :</strong></td>
                    <td class="font-weight-bold" style="border-bottom: 1.5px solid #000;">
                        <div style="float: left;">Rp.</div>
                        <div style="float: right;"><?= number_format($calcTotal, 0, ',', '.'); ?></div>
                        <div style="clear: both;"></div>
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <strong>TERBILANG :</strong> &nbsp;&nbsp;&nbsp; <?= $terbilangText; ?>
                    </td>
                    <td style="background-color: #d3d3d3; -webkit-print-color-adjust: exact;"></td>
                </tr>
            </tbody>
        </table>

        <!-- Signatures Table 1 -->
        <table class="rinci-footer-table">
            <tr>
                <td style="width: 50%;">
                    Telah dibayar uang sebesar<br><br>
                    <table style="width: 100%; border: none; margin: 0; padding: 0;">
                        <tr><td style="width: 10%; padding: 0;">Rp.</td><td style="width: 90%; text-align: left; padding: 0;"><?= number_format($calcTotal, 0, ',', '.'); ?></td></tr>
                    </table><br><br>
                    Pekanbaru, &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?= $tanggalTtd; ?><br>
                    Bendahara Pengeluaran,<br>
                    <div style="height: 60px;"></div>
                    <span style="text-decoration: underline;" class="font-weight-bold">KH. SRI HANDAYANI, S.Si., M.T.</span><br>
                    NIP. 19820402 201412 2 002
                </td>
                <td style="width: 50%; text-align: center;">
                    Pekanbaru, &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?= $tanggalTtd; ?><br>
                    Telah terima sejumlah uang sebesar:<br><br>
                    <table style="width: 100%; border: none; margin: 0; padding: 0;">
                        <tr><td style="width: 25%; text-align: right; padding: 0;">Rp.</td><td style="width: 75%; text-align: left; padding: 0; padding-left: 20px;"><?= number_format($calcTotal, 0, ',', '.'); ?></td></tr>
                    </table><br><br>
                    Yang Menerima :<br>
                    <div style="height: 60px;"></div>
                    <span style="text-decoration: underline;" class="font-weight-bold"><?= strtoupper(esc($utama['nama'])); ?></span>
                    <?php if (should_show_nip($utama) && !empty($utama['nip'])): ?>
                        <br>NIP. <?= esc($utama['nip']); ?>
                    <?php endif; ?>
                </td>
            </tr>
        </table>

        <div style="border-top: 2px solid #000; margin: 20px 0 10px 0;"></div>

        <!-- RAMPUNG Table -->
        <div class="text-center font-weight-bold" style="letter-spacing: 2px; text-decoration: underline; margin-bottom: 15px;">
            P E R H I T U N G A N   S P D   R A M P U N G
        </div>
        <table class="rampung-table">
            <tr>
                <td style="width: 60%;">
                    Ditetapkan Sejumlah...................................................................<br>
                    Yang dibayar semula ..................................................................<br>
                    Sisa kurang / Lebih ..................................................................
                </td>
                <td style="width: 40%;">
                    <table style="width: 100%; border: none;">
                        <tr>
                            <td style="width: 20%; padding: 0;">Rp</td>
                            <td style="width: 80%; text-align: right; padding: 0;"><?= number_format($calcTotal, 0, ',', '.'); ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 0;">Rp</td>
                            <td style="text-align: right; border-bottom: 1px solid #000; padding: 0;">-</td>
                        </tr>
                        <tr>
                            <td style="padding: 0;">Rp</td>
                            <td style="text-align: right; padding: 0;">-</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <table class="rampung-table" style="margin-top: 30px;">
            <tr>
                <td style="width: 50%;"></td>
                <td style="width: 50%; text-align: center;">
                    Pejabat Pembuat Komitmen<br>
                    Pelaksanaan Prasarana Strategis<br>
                    <div style="height: 60px;"></div>
                    <span style="text-decoration: underline;" class="font-weight-bold">NURHIDAYAT NUGROHO, S.Ars.</span><br>
                    NIP. 19901221 201802 1 001
                </td>
            </tr>
        </table>

        <div class="page-break"></div>

        <!-- ======================= KWITANSI PAGE ======================= -->
        
        <?php if ($kopSuratImgUrl): ?>
            <img src="<?= $kopSuratImgUrl; ?>" class="kop-surat-img" alt="Kop Surat">
        <?php endif; ?>

        <div style="border-top: 1px solid #000; border-bottom: 2px solid #000; height: 1px;"></div>
<div style="border-left: 1.5px solid #000; border-right: 1.5px solid #000; border-bottom: 1.5px solid #000; padding: 15px; margin-top: 0; min-height: 800px; position: relative;">

        <table class="kwitansi-header-table">
            <tr>
                <td style="width: 35%;">Tahun Anggaran</td>
                <td style="width: 65%;">2026</td>
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

        <div class="kwitansi-title" style="margin-top: 40px; margin-bottom: 25px;">K U I T A N S I</div>

        <table class="kwitansi-body-table">
            <tr>
                <td style="width: 25%; padding-bottom: 8px;">Sudah di terima dari</td>
                <td style="width: 3%; padding-bottom: 8px;">:</td>
                <td style="width: 72%; padding-bottom: 8px;">PEJABAT PEMBUAT KOMITMEN PELAKSANAAN PRASARANA STRATEGIS</td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px;">Jumlah Uang</td>
                <td style="padding-bottom: 8px;">:</td>
                <td class="font-weight-bold" style="padding-bottom: 8px;">
                    <span style="display: inline-block; width: 30px;">Rp.</span> 
                    <?= number_format($calcTotal, 0, ',', '.'); ?>
                </td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px;">Terbilang</td>
                <td style="padding-bottom: 8px;">:</td>
                <td style="padding-bottom: 8px;"><span style="font-style: italic; font-weight: bold;"><?= $terbilangText; ?></span></td>
            </tr>
            <tr>
                <td>Untuk Pembayaran</td>
                <td>:</td>
                <td style="text-align: justify; line-height: 1.5;">
                    Perjalanan Dinas a.n. <?= esc($utama['nama']); ?> <?= esc($utama['jabatan']); ?> dalam rangka <?= $tujuanMaksud; ?> Lokasi <?= $kotaTujuan; ?>, sesuai dengan Peraturan Menteri Keuangan RI Nomor 119 Tahun 2023 Tanggal 15 November 2023, sebagaimana daftar perincian terlampir.
                </td>
            </tr>
            <tr>
                <td colspan="3"><br>Berdasarkan SPD</td>
            </tr>
            <tr>
                <td>Nomor</td>
                <td>:</td>
                <td><?= $nomorSPD; ?></td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td>:</td>
                <td><?= strtoupper($tanggalTtd); ?></td>
            </tr>
            <tr>
                <td>Untuk perjalanan dinas dari</td>
                <td>:</td>
                <td>Pekanbaru - <?= $kotaTujuan; ?></td>
            </tr>
            <tr>
                <td>Berangkat dari tanggal</td>
                <td>:</td>
                <td><?= $tglBerangkat; ?> s/d <?= $tglKembali; ?></td>
            </tr>
        </table>

        <br>
        <table class="kwitansi-ttd-table">
            <tr>
                <td>
                    An. Kuasa Pengguna Anggaran<br>
                    Pejabat Pembuat Komitmen<br>
                    Pelaksanaan Prasarana Strategis
                    
                    <div style="height: 60px;"></div>
                    
                    <span style="text-decoration: underline;" class="font-weight-bold">NURHIDAYAT NUGROHO, S.Ars.</span>
                    <?php if (should_show_nip(['nip' => '19901221 201802 1 001'])): ?>
                        <br>NIP. 19901221 201802 1 001
                    <?php endif; ?>
                </td>
                <td>
                    Pekanbaru, &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?= $tanggalTtd; ?><br>
                    Kepala Satuan Kerja<br>
                    Pelaksanaan Prasarana Strategis Riau
                    
                    <div style="height: 60px;"></div>
                    
                    <span style="text-decoration: underline;" class="font-weight-bold"><?= strtoupper(esc($utama['nama'])); ?></span>
                    <?php if (should_show_nip($utama) && !empty($utama['nip'])): ?>
                        <br>NIP. <?= esc($utama['nip']); ?>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
        
        </div> <!-- End of kwitansi-wrapper -->
        
        <?php if ($index < $totalPelaksana - 1): ?>
            <div class="page-break"></div>
        <?php endif; ?>
    <?php endforeach; ?>

</body>
</html>
