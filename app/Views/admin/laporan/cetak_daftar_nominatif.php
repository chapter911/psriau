<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Nominatif</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 0;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-weight-bold { font-weight: bold; }
        .title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 15px;
            font-size: 11px;
        }
        .info-table td {
            vertical-align: top;
            padding: 2px 0;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: top;
        }
        .data-table th {
            text-align: center;
            background-color: #f2f2f2;
        }
        .signature-table {
            width: 100%;
            margin-top: 30px;
            font-size: 11px;
        }
        .signature-table td {
            width: 33%;
            text-align: center;
            vertical-align: top;
        }
        .signature-box {
            height: 70px;
        }
        .no-border { border: none !important; }
    </style>
</head>
<body>

    <div class="title">DAFTAR NOMINATIF RINCIAN PERJALANAN DINAS (LS)</div>

    <table class="info-table">
        <tr>
            <td style="width: 120px;">SATKER</td>
            <td style="width: 10px;">:</td>
            <td>PELAKSANAAN PRASARANA STRATEGIS RIAU</td>
        </tr>
        <tr>
            <td>KODE SATKER</td>
            <td>:</td>
            <td>691285</td>
        </tr>
        <tr>
            <td>MAK</td>
            <td>:</td>
            <td><?= esc($mata_anggaran ?? ''); ?></td>
        </tr>
        <tr>
            <td>URAIAN</td>
            <td>:</td>
            <td><?= esc($row['tujuan'] ?? ''); ?></td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 20px;">NO</th>
                <th rowspan="2" style="width: 120px;">NAMA PEGAWAI / NIP</th>
                <th rowspan="2" style="width: 60px;">PANGKAT / GOL</th>
                <th rowspan="2" style="width: 80px;">TUJUAN</th>
                <th colspan="2">TANGGAL</th>
                <th rowspan="2" style="width: 60px;">LAMA PERJALANAN</th>
                <th colspan="3">BIAYA PERJALANAN</th>
                <th rowspan="2" style="width: 70px;">JUMLAH TOTAL</th>
            </tr>
            <tr>
                <th style="width: 60px;">BERANGKAT</th>
                <th style="width: 60px;">KEMBALI</th>
                <th style="width: 70px;">UANG HARIAN</th>
                <th style="width: 70px;">SEWA KENDARAAN</th>
                <th style="width: 70px;">PENGINAPAN</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1; 
            
            // Function to format date safely
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
                
                return date('j', $ts) . ' ' . $months[(int)date('n', $ts)] . ' ' . date('Y', $ts);
            };
            
            $tglBerangkat = $formatDate($row['periode_mulai'] ?? '');
            $tglKembali = $formatDate($row['periode_selesai'] ?? '');
            
            // Calculate Lama Perjalanan
            $lamaPerjalanan = '-';
            $days = 0;
            if (!empty($row['periode_mulai']) && !empty($row['periode_selesai'])) {
                $start = new \DateTime($row['periode_mulai']);
                $end = new \DateTime($row['periode_selesai']);
                $diff = $start->diff($end);
                $days = $diff->days + 1;
                
                // Convert number to text (simple representation, ideally use a library but this works for common days)
                $angkaTeks = [
                    1 => 'satu', 2 => 'dua', 3 => 'tiga', 4 => 'empat', 5 => 'lima',
                    6 => 'enam', 7 => 'tujuh', 8 => 'delapan', 9 => 'sembilan', 10 => 'sepuluh',
                    11 => 'sebelas', 12 => 'dua belas', 13 => 'tiga belas', 14 => 'empat belas', 
                    15 => 'lima belas', 16 => 'enam belas', 17 => 'tujuh belas', 18 => 'delapan belas', 
                    19 => 'sembilan belas', 20 => 'dua puluh'
                ];
                
                $teks = $angkaTeks[$days] ?? $days;
                $lamaPerjalanan = $days . ' (' . $teks . ') hari';
            }
            
            $kotaTujuan = esc($row['kota_tujuan'] ?? '-');
            $totalSemuaUangHarian = 0;
            $totalSemuaTransport = 0;
            $totalSemuaPenginapan = 0;
            $totalSemuaTotal = 0;
            ?>
            <?php foreach ($pelaksana as $p): ?>
                <?php 
                    // Extract Golongan directly from Master Pegawai data or fallback
                    $golongan = trim((string)($p['golongan'] ?? ''));
                    $jabatanStr = trim((string)($p['jabatan'] ?? ''));
                    if ($golongan === '') {
                        if (preg_match('/^([^\(]+)\((.*?)\)/', $jabatanStr, $matches)) {
                            $golongan = trim($matches[1]);
                        } else {
                            $golongan = $jabatanStr;
                        }
                    }
                    if (preg_match('/^([^\(\)]+)/', $golongan, $gMatches)) {
                        $golongan = trim($gMatches[1]);
                    }
                    
                    // Determine Penginapan Tariff based on Jabatan
                    $tarifPenginapan = $biaya_master['penginapan_e4'] ?? 0;
                    $jabatanUpper = strtoupper($jabatanStr);
                    if (strpos($jabatanUpper, 'ESELON I') !== false && strpos($jabatanUpper, 'ESELON II') === false && strpos($jabatanUpper, 'ESELON III') === false) {
                        $tarifPenginapan = $biaya_master['penginapan_e1'] ?? 0;
                    } elseif (strpos($jabatanUpper, 'ESELON II') !== false && strpos($jabatanUpper, 'ESELON III') === false) {
                        $tarifPenginapan = $biaya_master['penginapan_e2'] ?? 0;
                    } elseif (strpos($jabatanUpper, 'ESELON III') !== false) {
                        $tarifPenginapan = $biaya_master['penginapan_e3'] ?? 0;
                    }
                    
                    $rincianBiaya = json_decode((string)($row['rincian_biaya_json'] ?? '{}'), true) ?: [];

                    // 1. Transport Calculation:
                    $transportStart = $rincianBiaya['transport_start_date'] ?? '';
                    $transportEnd   = $rincianBiaya['transport_end_date'] ?? '';
                    $transportNominal = (int) ($rincianBiaya['transport_nominal'] ?? 0);

                    $transportDays = 0;
                    if (!empty($transportStart) && !empty($transportEnd)) {
                        try {
                            $tsStart = new \DateTime($transportStart);
                            $tsEnd   = new \DateTime($transportEnd);
                            $transportDays = max(0, $tsStart->diff($tsEnd)->days + 1);
                        } catch (\Throwable $e) {}
                    }
                    $calcTransport = $transportDays * $transportNominal;

                    // 2. Penginapan Calculation:
                    $penginapanStart = $rincianBiaya['penginapan_start_date'] ?? '';
                    $penginapanEnd   = $rincianBiaya['penginapan_end_date'] ?? '';
                    $hasCustomPenginapanNominal = isset($rincianBiaya['penginapan_nominal']) && $rincianBiaya['penginapan_nominal'] !== null && $rincianBiaya['penginapan_nominal'] !== '';
                    $penginapanNominalInput = $hasCustomPenginapanNominal ? (int) $rincianBiaya['penginapan_nominal'] : null;

                    $penginapanNights = 0;
                    if (!empty($penginapanStart) && !empty($penginapanEnd)) {
                        try {
                            $pStart = new \DateTime($penginapanStart);
                            $pEnd   = new \DateTime($penginapanEnd);
                            $penginapanNights = max(0, $pStart->diff($pEnd)->days);
                        } catch (\Throwable $e) {}
                    } else {
                        $penginapanNights = max(0, $days - 1);
                    }

                    if ($hasCustomPenginapanNominal && $penginapanNominalInput >= 0) {
                        $calcPenginapan = $penginapanNights * $penginapanNominalInput;
                    } else {
                        $calcPenginapan = $penginapanNights * (int) ($tarifPenginapan * 0.3);
                    }

                    $calcHarian = ($biaya_master['harian'] ?? 0) * $days;
                    $calcTotal = $calcHarian + $calcTransport + $calcPenginapan;
                    
                    $totalSemuaUangHarian += $calcHarian;
                    $totalSemuaTransport += $calcTransport;
                    $totalSemuaPenginapan += $calcPenginapan;
                    $totalSemuaTotal += $calcTotal;
                ?>
                <tr>
                    <td class="text-center"><?= $no++; ?></td>
                    <td>
                        <?= esc($p['nama'] ?? '-'); ?><br>
                        NIP. <?= esc($p['nip'] ?? '-'); ?>
                    </td>
                    <td class="text-center">
                        <?= esc($golongan !== '' ? $golongan : '-'); ?>
                    </td>
                    <td class="text-center"><?= $kotaTujuan; ?></td>
                    <td class="text-center"><?= $tglBerangkat; ?></td>
                    <td class="text-center"><?= $tglKembali; ?></td>
                    <td class="text-center"><?= $lamaPerjalanan; ?></td>
                    <td class="text-center">Rp <?= number_format($calcHarian, 0, ',', '.'); ?></td>
                    <td class="text-center"><?= $calcTransport > 0 ? 'Rp ' . number_format($calcTransport, 0, ',', '.') : 'Rp -'; ?></td>
                    <td class="text-center">Rp <?= number_format($calcPenginapan, 0, ',', '.'); ?></td>
                    <td class="text-center">Rp <?= number_format($calcTotal, 0, ',', '.'); ?></td>
                </tr>
            <?php endforeach; ?>
            
            <?php if (empty($pelaksana)): ?>
                <tr>
                    <td colspan="11" class="text-center">Data pelaksana tidak tersedia.</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="text-right font-weight-bold">JUMLAH</td>
                <td class="text-center font-weight-bold">Rp <?= number_format($totalSemuaUangHarian, 0, ',', '.'); ?></td>
                <td class="text-center font-weight-bold"><?= $totalSemuaTransport > 0 ? 'Rp ' . number_format($totalSemuaTransport, 0, ',', '.') : 'Rp -'; ?></td>
                <td class="text-center font-weight-bold">Rp <?= number_format($totalSemuaPenginapan, 0, ',', '.'); ?></td>
                <td class="text-center font-weight-bold">Rp <?= number_format($totalSemuaTotal, 0, ',', '.'); ?></td>
            </tr>
        </tfoot>
    </table>

    <table class="signature-table">
        <tr>
            <td></td>
            <td></td>
            <td>
                <?php 
                    // This could be dynamic based on user setup, but for now matching format
                ?>
                Pejabat Pembuat Komitmen<br>
                Pelaksanaan Prasarana Strategis<br>
                <div class="signature-box"></div>
                <span class="font-weight-bold">NURHIDAYAT NUGROHO, S.Ars.</span><br>
                NIP. 19901221 201802 1 001
            </td>
        </tr>
    </table>

</body>
</html>
