<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Kwitansi & Rincian Biaya - Excel Mode</title>
    <!-- FontAwesome & Bootstrap 4 CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    
    <style>
        :root {
            --excel-blue: #1a73e8;
            --excel-bg: #808080;
            --excel-grid: #d4d4d4;
        }

        body {
            background-color: var(--excel-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding-top: 70px;
            color: #333;
        }

        /* Top Bar */
        .preview-toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: #ffffff;
            border-bottom: 2px solid #e0e0e0;
            z-index: 1050;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        .preview-toolbar .brand-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2b579a;
            display: flex;
            align-items: center;
        }

        /* Nav Tabs for Sheets */
        .excel-sheet-tabs {
            display: flex;
            gap: 4px;
            margin-bottom: 20px;
            justify-content: center;
        }

        .excel-sheet-tabs .nav-link {
            background: #e0e0e0;
            color: #555;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 8px 24px;
            border-radius: 6px 6px 0 0;
            border: 1px solid #ccc;
            border-bottom: none;
            transition: all 0.2s ease;
        }

        .excel-sheet-tabs .nav-link.active {
            background: #ffffff;
            color: var(--excel-blue);
            border-top: 3px solid var(--excel-blue);
            border-left: 1px solid #bbb;
            border-right: 1px solid #bbb;
        }

        /* Excel Page Break Preview Box */
        .excel-page-container {
            width: 100%;
            max-width: 960px;
            margin: 0 auto 40px auto;
            position: relative;
        }

        .excel-sheet-page {
            background: #ffffff;
            border: 3px solid var(--excel-blue);
            box-shadow: 0 8px 25px rgba(0,0,0,0.35);
            padding: 25px 35px;
            position: relative;
            min-height: 1000px;
            box-sizing: border-box;
            border-radius: 2px;
        }

        /* Watermark Page 1 Overlay */
        .page-break-watermark {
            position: absolute;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 110px;
            font-weight: 900;
            color: rgba(0, 0, 0, 0.08);
            pointer-events: none;
            user-select: none;
            font-family: Arial, sans-serif;
            text-transform: uppercase;
            letter-spacing: 2px;
            z-index: 1;
        }

        /* Excel Table Base */
        .excel-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            z-index: 2;
            position: relative;
            background: transparent;
        }

        .excel-table td, .excel-table th {
            padding: 4px 6px;
            vertical-align: middle;
            font-size: 11pt;
            box-sizing: border-box;
            word-wrap: break-word;
        }

        /* Fonts */
        .font-tahoma { font-family: 'Tahoma', sans-serif; }
        .font-arial { font-family: 'Arial', sans-serif; }

        /* Borders */
        .border-thin-all { border: 1px solid #000000 !important; }
        .border-thin-top { border-top: 1px solid #000000 !important; }
        .border-thin-bottom { border-bottom: 1px solid #000000 !important; }
        .border-thin-left { border-left: 1px solid #000000 !important; }
        .border-thin-right { border-right: 1px solid #000000 !important; }
        .border-medium-bottom { border-bottom: 2px solid #000000 !important; }
        .border-medium-box { border: 2px solid #000000 !important; }

        /* Patterns */
        .bg-pattern-gray {
            background-color: #f0f2f5 !important;
            background-image: none !important;
        }

        /* Print styles */
        @media print {
            body {
                background: none;
                padding-top: 0;
            }
            .preview-toolbar, .excel-sheet-tabs, .page-break-watermark {
                display: none !important;
            }
            .excel-page-container {
                max-width: 100%;
                margin: 0;
            }
            .excel-sheet-page {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
            }
            .tab-pane {
                display: block !important;
                opacity: 1 !important;
                page-break-after: always;
            }
        }
    </style>
</head>
<body>

    <!-- Top Preview Toolbar -->
    <div class="preview-toolbar">
        <div class="brand-title">
            <i class="fas fa-file-excel text-success mr-2 style-icon" style="font-size: 1.5rem;"></i>
            <span>Preview Excel Kwitansi & Rincian Biaya</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= site_url('admin/surat/perjalanan-dinas/' . (int)$row['id'] . '/cetak-kwitansi?download=1') ?>" class="btn btn-success font-weight-bold shadow-sm px-3">
                <i class="fas fa-download mr-2"></i> Download File Excel (.xls)
            </a>
            <button onclick="window.print()" class="btn btn-outline-secondary ml-2 font-weight-bold">
                <i class="fas fa-print mr-1"></i> Print / Cetak
            </button>
            <a href="<?= site_url('admin/surat/perjalanan-dinas') ?>" class="btn btn-light border ml-2">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Sheet Tabs -->
    <div class="container-fluid">
        <ul class="nav excel-sheet-tabs" id="kwitansiSheetTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="rinci-tab" data-toggle="tab" href="#sheet-rinci" role="tab" aria-controls="sheet-rinci" aria-selected="true">
                    <i class="fas fa-table mr-1 text-success"></i> RINCI (Rincian Biaya)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="kwitansi-tab" data-toggle="tab" href="#sheet-kwitansi" role="tab" aria-controls="sheet-kwitansi" aria-selected="false">
                    <i class="fas fa-file-invoice-dollar mr-1 text-primary"></i> KWITANSI (Kwitansi Pembayaran)
                </a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="kwitansiSheetTabContent">
            
            <!-- SHEET 1: RINCI -->
            <div class="tab-pane fade show active" id="sheet-rinci" role="tabpanel" aria-labelledby="rinci-tab">
                <div class="excel-page-container">
                    <div class="excel-sheet-page font-tahoma">
                        <div class="page-break-watermark">Page 1</div>

                        <div class="text-center mb-3">
                            <h4 class="font-weight-bold mb-1" style="font-size: 16pt;">RINCIAN BIAYA PERJALANAN DINAS</h4>
                            <div style="font-size: 12pt;">LAMPIRAN SPD NOMOR : <?= esc($nomor_spd ?? ($row['kode_nomor'] ?? str_replace('SPT', 'SPD', (string)($row['nomor_surat_tugas'] ?? '-')))) ?></div>
                            <div style="font-size: 12pt;">TANGGAL : <?= esc(strtoupper($tanggal_ttd_upper ?? '')) ?></div>
                        </div>

                        <table class="excel-table font-tahoma">
                            <colgroup>
                                <col style="width: 5%;">
                                <col style="width: 50%;">
                                <col style="width: 25%;">
                                <col style="width: 20%;">
                            </colgroup>
                            <thead>
                                <tr class="bg-pattern-gray text-center font-weight-bold border-thin-all">
                                    <td class="border-thin-all">No</td>
                                    <td class="border-thin-all">RINCIAN BIAYA</td>
                                    <td class="border-thin-all">JUMLAH</td>
                                    <td class="border-thin-all">KETERANGAN</td>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Item 1: Transport -->
                                <tr>
                                    <td class="text-center align-top border-thin-left border-thin-right">1</td>
                                    <td class="font-weight-bold border-thin-left">BIAYA TRANSPORT :</td>
                                    <td class="d-flex justify-content-between border-thin-left"><span class="float-left">Rp.</span> <span class="float-right"><?= ($calc_transport ?? 0) == 0 ? '-' : number_format($calc_transport, 0, ',', '.') ?></span></td>
                                    <td class="border-thin-left border-thin-right"></td>
                                </tr>
                                <?php $multiGroup = count($transport_groups ?? []) > 1; ?>
                                <?php if (empty($transport_groups)): ?>
                                    <!-- No transport items -->
                                <?php elseif (!$multiGroup): ?>
                                    <?php $onlyGroup = reset($transport_groups); ?>
                                    <?php foreach ($onlyGroup['rows'] as $tRow): ?>
                                    <tr>
                                        <td class="border-thin-left border-thin-right"></td>
                                        <td class="pl-3 d-flex justify-content-between">
                                            <span><?= esc($tRow['ket'] !== '' ? $tRow['ket'] : (!empty($tRow['jenis']) ? $tRow['jenis'] : 'Transport')) ?></span>
                                            <span>Rp.</span>
                                        </td>
                                        <td class="text-right pr-4"><?= number_format($tRow['sub'], 0, ',', '.') ?></td>
                                        <td class="border-thin-left border-thin-right"></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <?php foreach ($transport_groups as $gLabel => $grp): ?>
                                    <tr>
                                        <td class="border-thin-left border-thin-right"></td>
                                        <td class="pl-3 font-weight-bold"><?= esc($gLabel) ?>:</td>
                                        <td class="border-thin-left"></td>
                                        <td class="border-thin-left border-thin-right"></td>
                                    </tr>
                                    <?php foreach ($grp['rows'] as $idx => $tRow): ?>
                                    <tr>
                                        <td class="border-thin-left border-thin-right"></td>
                                        <td class="pl-4 d-flex justify-content-between">
                                            <span><?= esc($tRow['ket'] !== '' ? $tRow['ket'] : $gLabel) ?></span>
                                            <span>Rp.</span>
                                        </td>
                                        <td class="text-right pr-4 <?= ($idx === count($grp['rows']) - 1 && count($grp['rows']) > 1) ? 'border-thin-bottom' : '' ?>"><?= number_format($tRow['sub'], 0, ',', '.') ?></td>
                                        <td class="border-thin-left border-thin-right"></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (count($grp['rows']) > 1): ?>
                                    <tr>
                                        <td class="border-thin-left border-thin-right"></td>
                                        <td></td>
                                        <td class="text-right font-weight-bold pr-2"><?= number_format($grp['rounded_subtotal'] ?? 0, 0, ',', '.') ?></td>
                                        <td class="border-thin-left border-thin-right"></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <tr>
                                    <td class="border-thin-left border-thin-right"></td>
                                    <td class="border-thin-left"></td>
                                    <td class="border-thin-left"></td>
                                    <td class="border-thin-left border-thin-right"></td>
                                </tr>

                                <!-- Item 2: Uang Harian -->
                                <tr>
                                    <td class="text-center align-top border-thin-left border-thin-right">2</td>
                                    <td class="font-weight-bold border-thin-left">UANG HARIAN</td>
                                    <td class="d-flex justify-content-between border-thin-left"><span class="float-left">Rp.</span> <span class="float-right"><?= ($calc_harian ?? 0) == 0 ? '-' : number_format($calc_harian, 0, ',', '.') ?></span></td>
                                    <td class="border-thin-left border-thin-right"></td>
                                </tr>
                                <tr>
                                    <td class="border-thin-left border-thin-right"></td>
                                    <td class="pl-3">Uang Makan, Uang Transport Lokal, Uang Saku selama :</td>
                                    <td class="border-thin-left"></td>
                                    <td class="border-thin-left border-thin-right"></td>
                                </tr>
                                <?php foreach ($harian_details as $hRow): ?>
                                <tr>
                                    <td class="border-thin-left border-thin-right"></td>
                                    <td class="pl-4">
                                        <?= (int)$hRow['days'] ?> hari &nbsp;&nbsp;x&nbsp;&nbsp; Rp <?= number_format($hRow['rate'], 0, ',', '.') ?> &nbsp;&nbsp;Rp &nbsp;&nbsp;<?= ($hRow['sub'] ?? 0) == 0 ? '-' : number_format($hRow['sub'], 0, ',', '.') ?>
                                    </td>
                                    <td class="border-thin-left"></td>
                                    <td class="border-thin-left border-thin-right"></td>
                                </tr>
                                <?php endforeach; ?>

                                <tr>
                                    <td class="border-thin-left border-thin-right"></td>
                                    <td class="border-thin-left"></td>
                                    <td class="border-thin-left"></td>
                                    <td class="border-thin-left border-thin-right"></td>
                                </tr>

                                <!-- Item 3: Uang Penginapan -->
                                <tr>
                                    <td class="text-center align-top border-thin-left border-thin-right">3</td>
                                    <td class="font-weight-bold border-thin-left">UANG PENGINAPAN</td>
                                    <td class="d-flex justify-content-between border-thin-left"><span class="float-left">Rp.</span> <span class="float-right"><?= ($calc_penginapan ?? 0) == 0 ? '-' : number_format($calc_penginapan, 0, ',', '.') ?></span></td>
                                    <td class="border-thin-left border-thin-right"></td>
                                </tr>
                                <tr>
                                    <td class="border-thin-left border-thin-right"></td>
                                    <td class="pl-3">Uang penginapan selama :</td>
                                    <td class="border-thin-left"></td>
                                    <td class="border-thin-left border-thin-right"></td>
                                </tr>
                                <?php foreach ($penginapan_details as $pRow): ?>
                                <tr>
                                    <td class="border-thin-left border-thin-right"></td>
                                     <td class="pl-4">
                                         <?= (int)$pRow['nights'] ?> malam &nbsp;&nbsp;x&nbsp;&nbsp; Rp <?= ((int)($pRow['nights'] ?? 0) === 0) ? '-' : number_format($pRow['rate'], 0, ',', '.') ?> &nbsp;&nbsp;Rp &nbsp;&nbsp;<?= ($pRow['sub'] ?? 0) == 0 ? '-' : number_format($pRow['sub'], 0, ',', '.') ?>
                                     </td>
                                    <td class="border-thin-left"></td>
                                    <td class="border-thin-left border-thin-right"></td>
                                </tr>
                                <?php endforeach; ?>

                                <tr>
                                    <td class="border-thin-left border-thin-right"></td>
                                    <td class="border-thin-left"></td>
                                    <td class="border-thin-left"></td>
                                    <td class="border-thin-left border-thin-right"></td>
                                </tr>

                                <!-- Total JUMLAH -->
                                <tr class="font-weight-bold border-thin-top border-thin-bottom">
                                    <td class="border-thin-left border-thin-right"></td>
                                    <td class="border-thin-left">JUMLAH :</td>
                                    <td class="d-flex justify-content-between border-thin-left border-thin-right"><span class="float-left">Rp.</span> <span class="float-right"><?= number_format($total_biaya ?? 0, 0, ',', '.') ?></span></td>
                                    <td class="border-thin-left border-thin-right"></td>
                                </tr>

                                <!-- TERBILANG -->
                                <tr class="font-weight-bold bg-pattern-gray border-thin-top border-thin-bottom">
                                    <td class="border-thin-left border-thin-right"></td>
                                    <td colspan="3" class="border-thin-left border-thin-right">
                                        <span>TERBILANG : </span>
                                        <span class="ml-3"><?= esc($terbilang_text ?? '') ?></span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Signatures 1 -->
                        <div class="row mt-4 pt-2">
                            <div class="col-6 text-center">
                                <div>Telah dibayar uang sebesar</div>
                                <div class="mt-2 font-weight-bold">Rp. <?= number_format($total_biaya ?? 0, 0, ',', '.') ?></div>
                                <div class="mt-3">Pekanbaru, &nbsp;&nbsp;&nbsp;&nbsp;<?= esc($bulan_tahun_str ?? '') ?></div>
                                <div class="font-weight-bold mt-1">Bendahara Pengeluaran,</div>
                                <div style="height: 60px;"></div>
                                <div class="font-weight-bold text-underline"><u><?= esc($bendahara_nama ?? '-') ?></u></div>
                                <div><?= esc($bendahara_nip ?? '-') ?></div>
                            </div>
                            <div class="col-6 text-center">
                                <div>Pekanbaru, &nbsp;&nbsp;&nbsp;&nbsp;<?= esc($bulan_tahun_str ?? '') ?></div>
                                <div class="mt-1">Telah terima sejumlah uang sebesar:</div>
                                <div class="mt-2 font-weight-bold">Rp. <?= number_format($total_biaya ?? 0, 0, ',', '.') ?></div>
                                <div class="font-weight-bold mt-3">Yang Menerima :</div>
                                <div style="height: 60px;"></div>
                                <div class="font-weight-bold text-underline"><u><?= esc($nama_utama ?? '-') ?></u></div>
                                <div><?= esc($nip_label_utama ?? 'NIP. ') ?><?= esc($nip_utama ?? '-') ?></div>
                            </div>
                        </div>

                        <hr class="border-medium-bottom my-4">

                        <!-- Rampung Section -->
                        <div class="text-center font-weight-bold mb-3" style="font-size: 12pt; text-decoration: underline;">
                            P E R H I T U N G A N &nbsp;&nbsp;&nbsp;&nbsp; S P D &nbsp;&nbsp; R A M P U N G
                        </div>

                        <div class="row">
                            <div class="col-8">
                                <div>Ditetapkan Sejumlah……………………………………………………………….</div>
                                <div>Yang dibayar semula ……………………………………………………………….</div>
                                <div>Sisa kurang / Lebih &nbsp;&nbsp;……………………………………………………………..</div>
                            </div>
                            <div class="col-4 text-right">
                                <div>Rp. <?= number_format($total_biaya ?? 0, 0, ',', '.') ?></div>
                                <div class="border-thin-bottom">Rp. -</div>
                                <div>Rp. -</div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-6"></div>
                            <div class="col-6 text-center">
                                <div>Pejabat Pembuat Komitmen</div>
                                <div>Pelaksanaan Prasarana Strategis</div>
                                <div style="height: 60px;"></div>
                                <div class="font-weight-bold"><u>NURHIDAYAT NUGROHO, S.Ars.</u></div>
                                <div>NIP. 19901221 201802 1 001</div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- SHEET 2: KWITANSI -->
            <div class="tab-pane fade" id="sheet-kwitansi" role="tabpanel" aria-labelledby="kwitansi-tab">
                <div class="excel-page-container">
                    <div class="excel-sheet-page font-arial">
                        <div class="page-break-watermark">Page 1</div>

                        <div class="border-medium-box p-4" style="min-height: 850px;">
                            
                            <!-- Header Row with Kop Surat & Top Right Box -->
                            <div class="mb-3 w-100 text-left">
                                <?php if (!empty($kop_surat_path) && file_exists($kop_surat_path)): ?>
                                    <img src="<?= base_url(ltrim($kop_surat['image_url'] ?? '', '/')) ?>" alt="Kop Surat" style="width: 100%; max-width: 100%; height: auto; max-height: 140px; display: block; object-fit: contain; object-position: left center;">
                                <?php elseif (!empty($kop_surat['image_url'])): ?>
                                    <img src="<?= base_url(ltrim($kop_surat['image_url'], '/')) ?>" alt="Kop Surat" style="width: 100%; max-width: 100%; height: auto; max-height: 140px; display: block; object-fit: contain; object-position: left center;">
                                <?php else: ?>
                                    <div class="font-weight-bold text-muted p-2" style="font-size: 14pt;">[ KOP SURAT ]</div>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex justify-content-end mb-4">
                                <div style="width: 320px;">
                                    <table class="excel-table font-arial border-thin-all" style="font-size: 10.5pt;">
                                        <tr>
                                            <td class="border-thin-all" style="width: 50%;">Tahun Anggaran</td>
                                            <td class="border-thin-all font-weight-bold"><?= esc($tahun_anggaran_str ?? '') ?></td>
                                        </tr>
                                        <tr>
                                            <td class="border-thin-all">Nomor Bukti</td>
                                            <td class="border-thin-all font-weight-bold"></td>
                                        </tr>
                                        <tr>
                                            <td class="border-thin-all">Mata Anggaran</td>
                                            <td class="border-thin-all font-weight-bold"><?= esc($mata_anggaran ?? '-') ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="text-center my-4">
                                <h3 class="font-weight-bold" style="font-size: 22pt; text-decoration: underline; letter-spacing: 4px;">K U I T A N S I</h3>
                            </div>

                            <div class="my-3 font-arial" style="font-size: 12pt; line-height: 2;">
                                <div class="row">
                                    <div class="col-3">Sudah di terima dari</div>
                                    <div class="col-9 font-weight-bold">: PEJABAT PEMBUAT KOMITMEN PELAKSANAAN PRASARANA STRATEGIS</div>
                                </div>
                                <div class="row my-2">
                                    <div class="col-3">Jumlah Uang</div>
                                    <div class="col-9 font-weight-bold">: Rp. <?= number_format($total_biaya ?? 0, 0, ',', '.') ?></div>
                                </div>
                                <div class="row my-2">
                                    <div class="col-3">Terbilang</div>
                                    <div class="col-9 font-weight-bold font-italic">: <?= esc($terbilang_text ?? '') ?></div>
                                </div>
                                <div class="row my-2">
                                    <div class="col-3">Untuk Pembayaran</div>
                                    <div class="col-9 text-justify">: <?= esc($full_pembayaran_text ?? ('Perjalanan Dinas a.n. ' . ($nama_utama ?? '') . ' ' . ($jabatan_utama ?? '') . ' dalam rangka ' . ($row['tujuan'] ?? '') . ', sebagaimana daftar perincian terlampir.')) ?></div>
                                </div>
                                <div class="row my-2">
                                    <div class="col-3">Berdasarkan SPD</div>
                                    <div class="col-9">:</div>
                                </div>
                                <div class="row">
                                    <div class="col-3 pl-4">Nomor</div>
                                    <div class="col-9">: <?= esc($nomor_spd ?? ($row['kode_nomor'] ?? str_replace('SPT', 'SPD', (string)($row['nomor_surat_tugas'] ?? '-')))) ?></div>
                                </div>
                                <div class="row">
                                    <div class="col-3 pl-4">Tanggal</div>
                                    <div class="col-9">: <?= esc($tanggal_ttd_upper ?? '') ?></div>
                                </div>
                                <div class="row">
                                    <div class="col-3">Untuk perjalanan dinas dari</div>
                                    <div class="col-9">: Pekanbaru - <?= esc($row['kota_tujuan'] ?? '-') ?></div>
                                </div>
                                <div class="row">
                                     <div class="col-3">Berangkat dari tanggal</div>
                                     <div class="col-9">: <?= esc(($tgl_berangkat ?? '') === ($tgl_kembali ?? '') ? ($tgl_berangkat ?? '') : (($tgl_berangkat ?? '') . ' s/d ' . ($tgl_kembali ?? ''))) ?></div>
                                </div>
                            </div>

                            <!-- Signatures Section -->
                            <div class="row mt-5 pt-4" style="font-size: 11pt;">
                                <div class="col-6 text-center">
                                    <div>An. Kuasa Pengguna Anggaran</div>
                                    <div>Pejabat Pembuat Komitmen</div>
                                    <div>Pelaksanaan Prasarana Strategis Riau</div>
                                    <div style="height: 70px;"></div>
                                    <div class="font-weight-bold text-underline"><u>NURHIDAYAT NUGROHO, S.Ars.</u></div>
                                    <div>NIP. 19901221 201802 1 001</div>
                                </div>
                                <div class="col-6 text-center">
                                     <div>Pekanbaru, &nbsp;&nbsp;&nbsp;&nbsp;<?= esc($bulan_tahun_str ?? '') ?></div>
                                     <div><?= esc($jabatan_utama_line1 ?? ($jabatan_utama ?? '-')) ?></div>
                                     <div><?= esc($jabatan_utama_line2 ?? '') ?></div>
                                    <div style="height: 70px;"></div>
                                    <div class="font-weight-bold text-underline"><u><?= esc($nama_utama ?? '-') ?></u></div>
                                    <div><?= esc($nip_label_utama ?? 'NIP. ') ?><?= esc($nip_utama ?? '-') ?></div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- JS dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
