<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rincian & Kwitansi Perjalanan Dinas</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11.5px;
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
            border: 1px solid #000;
            padding: 5px;
            vertical-align: top;
        }
        .rinci-table th {
            text-align: center;
            font-weight: bold;
        }
        .rinci-footer-table {
            width: 100%;
            border: none;
        }
        .rinci-footer-table td {
            vertical-align: top;
            padding: 5px;
        }

        /* KWITANSI styles */
        .kwitansi-header-table {
            width: 50%;
            margin-bottom: 15px;
        }
        .kwitansi-header-table td {
            padding: 2px;
        }
        .kwitansi-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 20px;
            letter-spacing: 2px;
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
            return ucwords(terbilang_angka($angka)) . ' Rupiah';
        }
        return '- Rupiah';
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

                $tDays = 0;
                if (!empty($tStart) && !empty($tEnd)) {
                    try {
                        $d1 = new \DateTime($tStart);
                        $d2 = new \DateTime($tEnd);
                        $tDays = max(0, $d1->diff($d2)->days + 1);
                    } catch (\Throwable $e) {}
                }
                $sub = $tDays * $tNom;
                $calcTransport += $sub;

                if ($tDays > 0 || $tNom > 0) {
                    $labelStr = $tDays . ' hari x Rp ' . number_format($tNom, 0, ',', '.');
                    if ($tKet !== '') {
                        $labelStr .= ' (' . esc($tKet) . ')';
                    }
                    $transportDetails[] = $labelStr;
                }
            }
        }

        // 2. Penginapan Calculation (Multi-row):
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

                $sub = $pNights * $rate;
                $calcPenginapan += $sub;

                if ($pNights > 0 || $rate > 0) {
                    $labelStr = $pNights . ' malam x Rp ' . number_format($rate, 0, ',', '.');
                    if ($pKet !== '') {
                        $labelStr .= ' (' . esc($pKet) . ')';
                    }
                    $penginapanDetails[] = $labelStr;
                }
            }
        } else {
            $pNights = max(0, $days - 1);
            $rate = (int) ($tarifPenginapan * 0.3);
            $calcPenginapan = $pNights * $rate;
            $penginapanDetails[] = $pNights . ' malam x Rp ' . number_format($rate, 0, ',', '.');
        }

        $calcHarian = ($biaya_master['harian'] ?? 0) * $days;
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
        <?php if ($kopSuratImgUrl): ?>
            <img src="<?= $kopSuratImgUrl; ?>" class="kop-surat-img" alt="Kop Surat">
        <?php endif; ?>

        <div class="rinci-header">
            <span style="text-decoration: underline;" class="font-weight-bold">RINCIAN BIAYA PERJALANAN DINAS</span><br>
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
                <tr>
                    <td class="text-center">1</td>
                    <td>
                        BIAYA TRANSPORT :<br>
                        Sewa Kendaraan<br>
                        <?php if (!empty($transportDetails)): ?>
                            <?php foreach ($transportDetails as $td): ?>
                                <?= $td; ?><br>
                            <?php endforeach; ?>
                        <?php else: ?>
                            0 hari x Rp 0
                        <?php endif; ?>
                    </td>
                    <td class="text-right">
                        <br><br>
                        Rp <?= number_format($calcTransport, 0, ',', '.'); ?>
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td class="text-center">2</td>
                    <td>
                        UANG HARIAN<br>
                        Uang Makan, Uang Transport Lokal, Uang Saku selama :<br>
                        <?= $days; ?> hari x Rp <?= number_format($biaya_master['harian'] ?? 0, 0, ',', '.'); ?>
                    </td>
                    <td class="text-right">
                        <br><br>
                        Rp <?= number_format($calcHarian, 0, ',', '.'); ?>
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td class="text-center">3</td>
                    <td>
                        UANG PENGINAPAN<br>
                        Uang penginapan selama :<br>
                        <?php foreach ($penginapanDetails as $pd): ?>
                            <?= $pd; ?><br>
                        <?php endforeach; ?>
                    </td>
                    <td class="text-right">
                        <br><br>
                        Rp <?= number_format($calcPenginapan, 0, ',', '.'); ?>
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="2" class="text-right font-weight-bold">JUMLAH :</td>
                    <td class="text-right font-weight-bold">Rp <?= number_format($calcTotal, 0, ',', '.'); ?></td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="4">
                        <div style="padding: 10px 0;">
                            <strong>TERBILANG :</strong> <span style="font-style: italic;"><?= $terbilangText; ?></span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <table class="rinci-footer-table">
            <tr>
                <td style="width: 50%;">
                    Telah dibayar uang sebesar<br>
                    Rp <?= number_format($calcTotal, 0, ',', '.'); ?><br><br><br>
                    Bendahara Pengeluaran,<br>
                    <div style="height: 60px;"></div>
                    <strong>................................................</strong><br>
                    NIP.
                </td>
                <td style="width: 50%;">
                    Pekanbaru, <?= $tanggalTtd; ?><br>
                    Telah terima sejumlah uang sebesar:<br>
                    Rp <?= number_format($calcTotal, 0, ',', '.'); ?><br><br>
                    Yang Menerima :<br>
                    <div style="height: 60px;"></div>
                    <strong><?= strtoupper(esc($utama['nama'])); ?></strong><br>
                    NIP. <?= esc($utama['nip']); ?>
                </td>
            </tr>
        </table>

        <div class="page-break"></div>

        <!-- ======================= KWITANSI PAGE ======================= -->
        
        <?php if ($kopSuratImgUrl): ?>
            <img src="<?= $kopSuratImgUrl; ?>" class="kop-surat-img" alt="Kop Surat">
        <?php endif; ?>

        <table class="kwitansi-header-table">
            <tr>
                <td style="width: 40%;">Tahun Anggaran</td>
                <td style="width: 5%;">:</td>
                <td style="width: 55%;">2026</td>
            </tr>
            <tr>
                <td>Nomor Bukti</td>
                <td>:</td>
                <td><?= esc($displayKodeNomor); ?></td>
            </tr>
            <tr>
                <td>Mata Anggaran</td>
                <td>:</td>
                <td><?= esc($mata_anggaran ?? ''); ?></td>
            </tr>
        </table>

        <div class="kwitansi-title">K U I T A N S I</div>

        <table class="kwitansi-body-table">
            <tr>
                <td style="width: 25%;">Sudah di terima dari</td>
                <td style="width: 3%;">:</td>
                <td style="width: 72%;">PEJABAT PEMBUAT KOMITMEN PELAKSANAAN PRASARANA STRATEGIS</td>
            </tr>
            <tr>
                <td>Jumlah Uang</td>
                <td>:</td>
                <td class="font-weight-bold">Rp. <?= number_format($calcTotal, 0, ',', '.'); ?></td>
            </tr>
            <tr>
                <td>Terbilang</td>
                <td>:</td>
                <td style="font-style: italic;"><?= $terbilangText; ?></td>
            </tr>
            <tr>
                <td>Untuk Pembayaran</td>
                <td>:</td>
                <td style="text-align: justify; line-height: 1.5;">
                    Perjalanan Dinas a.n. <?= esc($utama['nama']); ?> <?= esc($utama['jabatan']); ?> dalam rangka <?= $tujuanMaksud; ?> Lokasi <?= $kotaTujuan; ?>, sesuai dengan Peraturan Menteri Keuangan RI Nomor 119 Tahun 2023 Tanggal 15 November 2023, sebagaimana daftar perincian terlampir.
                </td>
            </tr>
            <tr>
                <td colspan="3"><br><span class="font-weight-bold">Berdasarkan SPD</span></td>
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
                    
                    <span style="text-decoration: underline;" class="font-weight-bold">NURHIDAYAT NUGROHO, S.Ars.</span><br>
                    NIP. 19901221 201802 1 001
                </td>
                <td>
                    Pekanbaru, <?= $tanggalTtd; ?><br>
                    Yang Menerima,<br>
                    <br>
                    
                    <div style="height: 60px;"></div>
                    
                    <span style="text-decoration: underline;" class="font-weight-bold"><?= strtoupper(esc($utama['nama'])); ?></span><br>
                    <?= !empty($utama['nip']) ? 'NIP. ' . esc($utama['nip']) : 'NIP. -'; ?>
                </td>
            </tr>
        </table>
        
        <?php if ($index < $totalPelaksana - 1): ?>
            <div class="page-break"></div>
        <?php endif; ?>
    <?php endforeach; ?>

</body>
</html>
