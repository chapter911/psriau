<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Disposisi Perjalanan Dinas</title>
    <style>
        @page {
            margin: 1.2cm 1.5cm 1.2cm 1.5cm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            line-height: 1.6;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
            padding: 0;
            line-height: 1.3;
        }
        .section-title {
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 5px;
            font-size: 13px;
        }
        .form-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .form-table td {
            padding: 4px 0;
            vertical-align: bottom;
        }
        .form-table td.num {
            width: 25px;
            font-weight: normal;
        }
        .form-table td.dotted-border {
            border-bottom: 1px dotted #000;
        }
        .signature-table {
            width: 100%;
            margin-top: 60px;
            border-collapse: collapse;
        }
        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 13px;
        }
        .signature-name {
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <?php
        $pelaksana = json_decode((string) ($data['pelaksana_json'] ?? '[]'), true);
        
        // Format Periode
        $tglMulai = $data['periode_mulai'] ? date('d-m-Y', strtotime($data['periode_mulai'])) : '';
        $tglSelesai = $data['periode_selesai'] ? date('d-m-Y', strtotime($data['periode_selesai'])) : '';
        if ($tglMulai && $tglSelesai) {
            $periodeHtml = $tglMulai === $tglSelesai ? $tglMulai : $tglMulai . ' s/d ' . $tglSelesai;
        } else {
            $periodeHtml = '';
        }

        // Format NIP helper
        $formatNip = static function(string $nip): string {
            $nip = preg_replace('/\s+/', '', $nip);
            if (strlen($nip) === 18) {
                return substr($nip, 0, 8) . ' ' . substr($nip, 8, 6) . ' ' . substr($nip, 14, 1) . ' ' . substr($nip, 15);
            }
            return $nip;
        };

        $menyetujuiNip = $formatNip($menyetujui['nip'] ?? '');
        $diketahuiNip = $formatNip($diketahui['nip'] ?? '');
        
        // Dynamic year for title
        $year = '2026';
        if (! empty($data['periode_mulai'])) {
            $year = date('Y', strtotime($data['periode_mulai']));
        }
    ?>

    <!-- Header / Kop Surat -->
    <div style="text-align: center; margin-bottom: 15px;">
        <?php if (function_exists('kop_surat_img_tag') && kop_surat_img_tag('', '', 'Kop Surat') !== ''): ?>
            <?= kop_surat_img_tag('', 'width: 100%; max-height: 110px; object-fit: contain;', 'Kop Surat'); ?>
        <?php else: ?>
            <div style="font-size: 14pt; font-weight: bold; text-transform: uppercase;">
                KEMENTERIAN PEKERJAAN UMUM
            </div>
            <div style="font-size: 12pt; font-weight: bold;">
                Satuan Kerja Prasarana Strategis Riau
            </div>
            <div style="border-bottom: 3px double #000; margin: 5px 0 15px;"></div>
        <?php endif; ?>
    </div>

    <div class="header">
        <h1>DISPOSISI PERJALANAN DINAS</h1>
        <h1>SATKER PPS RIAU TA <?= esc($year); ?></h1>
    </div>

    <!-- Pelaksana SPPD -->
    <div class="section-title">Pelaksana SPPD</div>
    <table class="form-table">
        <?php 
        $limit = max(5, count($pelaksana));
        for ($i = 0; $i < $limit; $i++): 
        ?>
            <tr>
                <td class="num"><?= $i + 1; ?>)</td>
                <td class="dotted-border">
                    <?php if (isset($pelaksana[$i])): ?>
                        <?= esc($pelaksana[$i]['nama']); ?>
                        <?php if (! empty($pelaksana[$i]['nip'])): ?>
                             - NIP. <?= esc($formatNip($pelaksana[$i]['nip'])); ?>
                        <?php endif; ?>
                    <?php else: ?>
                        &nbsp;
                    <?php endif; ?>
                </td>
            </tr>
        <?php endfor; ?>
    </table>

    <!-- Periode Perjalanan Dinas -->
    <div class="section-title">Periode Perjalanan Dinas</div>
    <table class="form-table">
        <tr>
            <td class="dotted-border">
                <?= esc($periodeHtml); ?>&nbsp;
            </td>
        </tr>
    </table>

    <!-- Tujuan -->
    <div class="section-title">Tujuan</div>
    <table class="form-table">
        <tr>
            <td class="dotted-border">
                <?= esc($data['kota_tujuan'] ?? ''); ?><?= (! empty($data['tujuan']) ? ' - ' . esc($data['tujuan']) : ''); ?>&nbsp;
            </td>
        </tr>
    </table>

    <!-- Transportasi -->
    <div class="section-title">Transportasi</div>
    <table class="form-table">
        <tr>
            <td class="dotted-border">
                <?= esc($data['transportasi'] ?? ''); ?>&nbsp;
            </td>
        </tr>
    </table>

    <!-- Perihal -->
    <div class="section-title">Perihal</div>
    <table class="form-table">
        <tr>
            <td class="dotted-border">
                <?= esc($data['perihal'] ?? ''); ?>&nbsp;
            </td>
        </tr>
        <tr>
            <td class="dotted-border" style="height: 15px;">&nbsp;</td>
        </tr>
        <tr>
            <td class="dotted-border" style="height: 15px;">&nbsp;</td>
        </tr>
    </table>

    <!-- Signatures -->
    <table class="signature-table">
        <tr>
            <td>
                Menyetujui,<br>
                Pejabat Pembuat Komitmen
                <br><br><br><br><br>
                <span class="signature-name"><?= esc($menyetujui['nama'] ?? ''); ?></span><br>
                NIP. <?= esc($menyetujuiNip); ?>
            </td>
            <td>
                Diketahui,<br>
                Kepala Satuan Kerja PPS Riau
                <br><br><br><br><br>
                <span class="signature-name"><?= esc($diketahui['nama'] ?? ''); ?></span><br>
                NIP. <?= esc($diketahuiNip); ?>
            </td>
        </tr>
    </table>

</body>
</html>
