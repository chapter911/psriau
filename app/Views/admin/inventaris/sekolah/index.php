<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid px-3 py-2">

    <!-- KPI Summary Row -->
    <div class="row mb-3">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #007bff;">
                <span class="info-box-icon bg-primary elevation-1" style="border-radius: 8px;"><i class="fas fa-layer-group"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Total Nilai Kontrak Fisik (ATR)</span>
                    <span class="info-box-number text-dark" style="font-size: 1.15rem;">Rp <?= number_format($stats['total_fisik'] ?? 0, 0, ',', '.'); ?></span>
                    <small class="text-muted">9 Paket Rehabilitasi Madrasah</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #17a2b8;">
                <span class="info-box-icon bg-info elevation-1" style="border-radius: 8px;"><i class="fas fa-user-tie"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Konsultan MK / Supervisi</span>
                    <span class="info-box-number text-dark" style="font-size: 1.15rem;">Rp <?= number_format($stats['total_mk'] ?? 0, 0, ',', '.'); ?></span>
                    <small class="text-muted">Total 9 Paket PHTC</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #ffc107;">
                <span class="info-box-icon bg-warning elevation-1 text-white" style="border-radius: 8px;"><i class="fas fa-chair"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Pengadaan Meubelair (ASP)</span>
                    <span class="info-box-number text-dark" style="font-size: 1.15rem;">Rp <?= number_format($stats['total_asp'] ?? 0, 0, ',', '.'); ?></span>
                    <small class="text-muted"><?= number_format($stats['total_asp_unit'] ?? 0); ?> Unit Peralatan &amp; Mesin</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #28a745;">
                <span class="info-box-icon bg-success elevation-1" style="border-radius: 8px;"><i class="fas fa-calculator"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Total Nilai Investasi BMN</span>
                    <span class="info-box-number text-success font-weight-bold" style="font-size: 1.15rem;">Rp <?= number_format($stats['total_gabungan'] ?? 0, 0, ',', '.'); ?></span>
                    <small class="text-muted">Akumulasi Fisik ATR + ASP</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Card Container with Tabs -->
    <div class="card shadow-sm" style="border: 1px solid #e9eef5; border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white p-3" style="border-bottom: 1px solid #e9eef5;">
            <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap: 12px;">
                <div>
                    <h3 class="card-title font-weight-bold text-dark m-0" style="font-size: 1.2rem; line-height: 1.5;">
                        <i class="fas fa-file-invoice text-primary mr-2"></i>Rincian Hasil Penelitian Barang Milik Negara (BMN) - PHTC Provinsi Riau
                    </h3>
                    <small class="text-muted d-block mt-1">
                        Struktur Tab &amp; Tabel 100% Mengikuti Format Asli Berkas Penelitian BMN (Sheet Riau All, Rekap PHTC, dan Riau 1 s/d 9)
                    </small>
                </div>
                <div class="d-flex align-items-center" style="gap: 8px;">
                    <?php if ($can_export): ?>
                        <a href="<?= site_url('admin/inventaris/sekolah/export-excel'); ?>" class="btn btn-success btn-sm shadow-sm" style="border-radius: 6px; font-weight: 600;">
                            <i class="fas fa-file-excel mr-1"></i> Ekspor Excel (11 Sheet Sesuai Asli)
                        </a>
                        <a href="<?= site_url('admin/inventaris/sekolah/cetak-pdf'); ?>" target="_blank" class="btn btn-danger btn-sm shadow-sm" style="border-radius: 6px; font-weight: 600;">
                            <i class="fas fa-file-pdf mr-1"></i> Cetak PDF
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tab Bar 1: Sheet Asli Penelitian BMN -->
            <div class="mt-3">
                <div class="d-flex align-items-center mb-1">
                    <span class="badge badge-primary px-2 py-1 mr-2" style="font-size: 0.75rem;"><i class="fas fa-file-alt mr-1"></i> FORMAT ASLI DOKUMEN</span>
                    <span class="text-muted small font-weight-bold">Pilih Sheet Berkas Penelitian BMN:</span>
                </div>
                <ul class="nav nav-pills" style="gap: 5px; flex-wrap: wrap;">
                    <li class="nav-item">
                        <a class="nav-link <?= in_array($activeTab, ['riau-all', 'rekap'], true) ? 'active font-weight-bold shadow-sm' : 'text-dark bg-light'; ?>" 
                           href="<?= site_url('admin/inventaris/sekolah?tab=riau-all'); ?>" style="border-radius: 6px; font-size: 0.88rem;">
                            <i class="fas fa-table mr-1"></i> Riau All
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($activeTab === 'rekap-phtc') ? 'active font-weight-bold shadow-sm' : 'text-dark bg-light'; ?>" 
                           href="<?= site_url('admin/inventaris/sekolah?tab=rekap-phtc'); ?>" style="border-radius: 6px; font-size: 0.88rem;">
                            <i class="fas fa-chart-pie mr-1"></i> Rekap PHTC
                        </a>
                    </li>
                    <?php for ($i = 1; $i <= 9; $i++): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= ($activeTab === 'riau-' . $i) ? 'active font-weight-bold shadow-sm' : 'text-dark bg-light'; ?>" 
                               href="<?= site_url('admin/inventaris/sekolah?tab=riau-' . $i); ?>" style="border-radius: 6px; font-size: 0.88rem;">
                                <i class="fas fa-file-alt mr-1"></i> Riau <?= $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </div>

            <!-- Tab Bar 2: Integrasi Acuan Dokumen (DED & RAB) & Master -->
            <div class="mt-2 pt-2 border-top">
                <div class="d-flex align-items-center mb-1">
                    <span class="badge badge-secondary px-2 py-1 mr-2" style="font-size: 0.75rem;"><i class="fas fa-folder mr-1"></i> INTEGRASI ACUAN</span>
                    <span class="text-muted small font-weight-bold">Dokumen Acuan &amp; Data Master:</span>
                </div>
                <ul class="nav nav-pills" style="gap: 5px; flex-wrap: wrap;">
                    <li class="nav-item">
                        <a class="nav-link <?= in_array($activeTab, ['madrasah', 'acuan-dokumen'], true) ? 'active font-weight-bold shadow-sm' : 'text-dark bg-light'; ?>" 
                           href="<?= site_url('admin/inventaris/sekolah?tab=acuan-dokumen'); ?>" style="border-radius: 6px; font-size: 0.88rem;">
                            <i class="fas fa-folder-open mr-1"></i> Acuan Dokumen (DED &amp; RAB Fisik Final)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($activeTab === 'asp') ? 'active font-weight-bold shadow-sm' : 'text-dark bg-light'; ?>" 
                           href="<?= site_url('admin/inventaris/sekolah?tab=asp'); ?>" style="border-radius: 6px; font-size: 0.88rem;">
                            <i class="fas fa-chair mr-1"></i> Katalog Lengkap Meubelair (18.881 Unit)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($activeTab === 'master') ? 'active font-weight-bold shadow-sm' : 'text-dark bg-light'; ?>" 
                           href="<?= site_url('admin/inventaris/sekolah?tab=master'); ?>" style="border-radius: 6px; font-size: 0.88rem;">
                            <i class="fas fa-school mr-1"></i> Master Sekolah Binaan
                        </a>
                    </li>
                </ul>
            </div>

        </div>

        <div class="card-body p-3">

            <!-- ============================================================== -->
            <!-- TAB: RIAU ALL (Format Dokumen Asli Sheet Riau All) -->
            <!-- ============================================================== -->
            <?php if (in_array($activeTab, ['riau-all', 'rekap'], true)): ?>
                
                <div class="p-3 mb-3 bg-light border" style="border-radius: 8px;">
                    <h5 class="font-weight-bold text-dark mb-1 text-center" style="letter-spacing: 0.5px;">
                        RINCIAN HASIL PENELITIAN BARANG MILIK NEGARA
                    </h5>
                    <div class="text-center font-weight-bold text-primary mb-1">Riau</div>
                    <div class="text-center small font-weight-bold text-secondary text-uppercase">
                        PEKERJAAN REHABILITASI DAN RENOVASI MADRASAH (PHTC)
                    </div>
                </div>

                <div class="table-responsive mb-4" style="border: 1px solid #c0c0c0; border-radius: 6px;">
                    <table class="table table-bordered table-sm m-0" style="font-size: 0.84rem; min-width: 1400px;">
                        <thead style="background-color: #1F4E79; color: #ffffff;">
                            <tr class="text-center align-middle">
                                <th style="width: 40px;">No</th>
                                <th style="min-width: 280px;">Uraian</th>
                                <th style="width: 85px;">Tahun</th>
                                <th style="width: 160px;">Jenis BMN</th>
                                <th style="width: 95px;">Kode BMN</th>
                                <th style="width: 80px;">Merk/Type</th>
                                <th style="width: 50px;">Baik</th>
                                <th style="width: 45px;">RR</th>
                                <th style="width: 45px;">RB</th>
                                <th style="width: 60px;">Satuan</th>
                                <th style="width: 75px;">Jumlah Barang/Luas</th>
                                <th class="text-right" style="width: 130px;">Harga Satuan Kontrak Fisik (Rp)</th>
                                <th class="text-right" style="width: 135px;">Total Nilai Kontrak Fisik (Rp)</th>
                                <th style="width: 60px;">DED</th>
                                <th class="text-right" style="width: 110px;">MK/Supervisi</th>
                                <th style="width: 95px;">Nilai Perolehan</th>
                                <th style="width: 95px;">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $totalAtr = 0;
                                foreach ($allPakets as $p): 
                                    $totalAtr += (float) $p['nilai_kontrak_fisik'];
                            ?>
                                <tr>
                                    <td class="text-center font-weight-bold"><?= esc($p['no_paket']); ?></td>
                                    <td>
                                        <strong>REHABILITASI DAN RENOVASI MADRASAH PHTC PROVINSI RIAU <?= esc($p['no_paket']); ?></strong>
                                        <div class="small text-muted"><i class="fas fa-map-marker-alt text-danger mr-1"></i><?= esc($p['wilayah']); ?></div>
                                    </td>
                                    <td class="text-center"><?= esc($p['tahun_anggaran']); ?></td>
                                    <td class="small"><?= esc($p['jenis_bmn']); ?></td>
                                    <td class="text-center font-monospace small"><code><?= esc($p['kode_bmn']); ?></code></td>
                                    <td class="text-center">-</td>
                                    <td class="text-center">1</td>
                                    <td class="text-center">0</td>
                                    <td class="text-center">0</td>
                                    <td class="text-center">Unit</td>
                                    <td class="text-center font-weight-bold">1</td>
                                    <td class="text-right"><?= number_format($p['nilai_kontrak_fisik'], 0, ',', '.'); ?></td>
                                    <td class="text-right font-weight-bold"><?= number_format($p['nilai_kontrak_fisik'], 0, ',', '.'); ?></td>
                                    <td class="text-center text-muted">-</td>
                                    <td class="text-right text-info"><?= number_format($p['nilai_mk_supervisi'], 0, ',', '.'); ?></td>
                                    <td class="text-center text-muted">-</td>
                                    <td class="text-center text-muted">-</td>
                                </tr>
                            <?php endforeach; ?>

                            <!-- Baris Subtotal Jumlah ATR -->
                            <tr style="background-color: #D9E1F2; font-weight: bold;">
                                <td colspan="10" class="text-left font-weight-bold pl-3">Jumlah ATR</td>
                                <td class="text-center"><?= count($allPakets); ?></td>
                                <td class="text-right">-</td>
                                <td class="text-right"><?= number_format($totalAtr, 0, ',', '.'); ?></td>
                                <td colspan="4"></td>
                            </tr>

                            <!-- Baris Jumlah Peralatan Mesin (ASP) -->
                            <tr style="background-color: #D9E1F2; font-weight: bold;">
                                <td colspan="10" class="text-left font-weight-bold pl-3">Jumlah Peralatan Mesin (ASP)</td>
                                <td class="text-center">0</td>
                                <td class="text-right">-</td>
                                <td class="text-right">0</td>
                                <td colspan="4"></td>
                            </tr>

                            <!-- Baris TOTAL ATR + PM -->
                            <tr style="background-color: #B4C6E7; font-weight: bold; font-size: 0.88rem;">
                                <td colspan="10" class="text-left font-weight-bold pl-3">TOTAL ATR + PM</td>
                                <td class="text-center"><?= count($allPakets); ?></td>
                                <td class="text-right">-</td>
                                <td class="text-right text-primary font-weight-bold"><?= number_format($totalAtr, 0, ',', '.'); ?></td>
                                <td colspan="4"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            <!-- ============================================================== -->
            <!-- TAB: REKAP PHTC (Format Dokumen Asli Sheet Rekap PHTC) -->
            <!-- ============================================================== -->
            <?php elseif ($activeTab === 'rekap-phtc'): ?>

                <div class="p-3 mb-3 bg-light border" style="border-radius: 8px;">
                    <h5 class="font-weight-bold text-dark mb-1 text-center" style="letter-spacing: 0.5px;">
                        REKAPITULASI BMN PHTC PROVINSI RIAU
                    </h5>
                    <div class="text-center small font-weight-bold text-secondary text-uppercase">
                        Akumulasi Fisik ATR, Peralatan &amp; Mesin (ASP Meubelair), dan Total BMN per Lokasi Kegiatan
                    </div>
                </div>

                <div class="table-responsive mb-4" style="border: 1px solid #c0c0c0; border-radius: 6px;">
                    <table class="table table-bordered table-hover table-sm m-0" style="font-size: 0.88rem;">
                        <thead style="background-color: #1F4E79; color: #ffffff;">
                            <tr class="text-center align-middle">
                                <th style="width: 50px;">No</th>
                                <th>Lokasi</th>
                                <th class="text-right" style="width: 220px;">Jumlah ATR (Rp)</th>
                                <th class="text-right" style="width: 220px;">Jumlah ASP (Rp)</th>
                                <th class="text-right" style="width: 240px;">Total ATR + PM (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $totalAtrLok = 0; 
                                $totalAspLok = 0; 
                                $totalGabLok = 0;
                                foreach ($allPakets as $p): 
                                    $totalAtrLok += (float) $p['nilai_kontrak_fisik'];
                                    $totalAspLok += (float) $p['total_asp_nilai'];
                                    $totalGabLok += (float) $p['total_gabungan'];
                            ?>
                                <tr>
                                    <td class="text-center font-weight-bold"><?= esc($p['no_paket']); ?></td>
                                    <td>
                                        <strong><?= esc($p['wilayah']); ?></strong>
                                        <span class="badge badge-light border ml-2">Paket <?= esc($p['no_paket']); ?></span>
                                    </td>
                                    <td class="text-right"><?= number_format($p['nilai_kontrak_fisik'], 0, ',', '.'); ?></td>
                                    <td class="text-right"><?= number_format($p['total_asp_nilai'], 0, ',', '.'); ?></td>
                                    <td class="text-right font-weight-bold text-success"><?= number_format($p['total_gabungan'], 0, ',', '.'); ?></td>
                                </tr>
                            <?php endforeach; ?>

                            <!-- Baris Total Rekapitulasi -->
                            <tr style="background-color: #B4C6E7; font-weight: bold; font-size: 0.95rem;">
                                <td colspan="2" class="text-center font-weight-bold">TOTAL</td>
                                <td class="text-right font-weight-bold"><?= number_format($totalAtrLok, 0, ',', '.'); ?></td>
                                <td class="text-right font-weight-bold"><?= number_format($totalAspLok, 0, ',', '.'); ?></td>
                                <td class="text-right font-weight-bold text-primary"><?= number_format($totalGabLok, 0, ',', '.'); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            <!-- ============================================================== -->
            <!-- TAB: RIAU 1 S/D 9 (Format Dokumen Asli Sheet Riau 1..9) -->
            <!-- ============================================================== -->
            <?php elseif (preg_match('/^riau-[1-9]$/', $activeTab)): ?>
                <?php 
                    $curPaketNo = (int) str_replace('riau-', '', $activeTab);
                    $curPaket = $paketByNo[$curPaketNo] ?? null;
                    $curSekolah = $sekolahByNo[$curPaketNo] ?? [];
                    $curAsp = $aspByNo[$curPaketNo] ?? [];
                ?>

                <?php if ($curPaket): ?>
                    <div class="p-3 mb-3 bg-light border" style="border-radius: 8px;">
                        <h5 class="font-weight-bold text-dark mb-1 text-center" style="letter-spacing: 0.5px;">
                            RINCIAN HASIL PENELITIAN BARANG MILIK NEGARA
                        </h5>
                        <div class="text-center font-weight-bold text-primary mb-1">
                            <?= esc($curPaket['wilayah']); ?>
                        </div>
                        <div class="text-center small font-weight-bold text-secondary text-uppercase">
                            PEKERJAAN REHABILITASI DAN RENOVASI MADRASAH (PHTC) - PAKET <?= $curPaketNo; ?>
                        </div>
                    </div>

                    <div class="table-responsive mb-4" style="border: 1px solid #c0c0c0; border-radius: 6px;">
                        <table class="table table-bordered table-sm m-0" style="font-size: 0.84rem; min-width: 1400px;">
                            <thead style="background-color: #1F4E79; color: #ffffff;">
                                <tr class="text-center align-middle">
                                    <th style="width: 40px;">No</th>
                                    <th style="min-width: 280px;">Uraian</th>
                                    <th style="width: 85px;">Tahun</th>
                                    <th style="width: 160px;">Jenis BMN</th>
                                    <th style="width: 95px;">Kode BMN</th>
                                    <th style="width: 180px;">Merk/Type</th>
                                    <th style="width: 50px;">Baik</th>
                                    <th style="width: 45px;">RR</th>
                                    <th style="width: 45px;">RB</th>
                                    <th style="width: 60px;">Satuan</th>
                                    <th style="width: 75px;">Jumlah Barang/Luas</th>
                                    <th class="text-right" style="width: 130px;">Harga Satuan Kontrak Fisik (Rp)</th>
                                    <th class="text-right" style="width: 135px;">Total Nilai Kontrak Fisik (Rp)</th>
                                    <th style="width: 60px;">DED</th>
                                    <th class="text-right" style="width: 110px;">MK/Supervisi</th>
                                    <th style="width: 95px;">Nilai Perolehan</th>
                                    <th style="width: 95px;">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Baris 1: Item Paket ATR -->
                                <tr>
                                    <td class="text-center font-weight-bold">1</td>
                                    <td>
                                        <strong>Rehabilitasi Dan Renovasi Madrasah PHTC Provinsi Riau <?= $curPaketNo; ?></strong>
                                    </td>
                                    <td class="text-center"><?= esc($curPaket['tahun_anggaran']); ?></td>
                                    <td class="small"><?= esc($curPaket['jenis_bmn']); ?></td>
                                    <td class="text-center font-monospace small"><code><?= esc($curPaket['kode_bmn']); ?></code></td>
                                    <td class="text-center">-</td>
                                    <td class="text-center">1</td>
                                    <td class="text-center">0</td>
                                    <td class="text-center">0</td>
                                    <td class="text-center">Unit</td>
                                    <td class="text-center font-weight-bold">1</td>
                                    <td class="text-right"><?= number_format($curPaket['nilai_kontrak_fisik'], 0, ',', '.'); ?></td>
                                    <td class="text-right font-weight-bold"><?= number_format($curPaket['nilai_kontrak_fisik'], 0, ',', '.'); ?></td>
                                    <td class="text-center text-muted">-</td>
                                    <td class="text-right text-info"><?= number_format($curPaket['nilai_mk_supervisi'], 0, ',', '.'); ?></td>
                                    <td class="text-center text-muted">-</td>
                                    <td class="text-center text-muted">-</td>
                                </tr>

                                <!-- Sub-baris Madrasah Sasaran -->
                                <?php foreach ($curSekolah as $s): ?>
                                    <tr style="background-color: #fafbfc;">
                                        <td></td>
                                        <td style="padding-left: 28px;">
                                            <i class="fas fa-school text-primary mr-1"></i>
                                            <?= esc($s['nama_sekolah']); ?>
                                            <span class="text-muted small ml-1">(<?= esc($s['kabupaten']); ?>)</span>
                                        </td>
                                        <td colspan="14"></td>
                                        <td class="text-center">
                                            <?php if ($s['status_sekolah'] === 'Negeri'): ?>
                                                <span class="badge badge-success px-2">Negeri</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary px-2">Swasta</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                                <!-- Baris Subtotal Jumlah ATR -->
                                <tr style="background-color: #D9E1F2; font-weight: bold;">
                                    <td colspan="10" class="text-left font-weight-bold pl-3">Jumlah ATR</td>
                                    <td class="text-center font-weight-bold">1</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right font-weight-bold"><?= number_format($curPaket['nilai_kontrak_fisik'], 0, ',', '.'); ?></td>
                                    <td colspan="4"></td>
                                </tr>

                                <!-- Baris-baris Peralatan & Mesin (ASP) -->
                                <?php 
                                    $noAsp = 2;
                                    $totalVolAsp = 0;
                                    $totalNilaiAsp = 0;
                                    foreach ($curAsp as $a): 
                                        $totalVolAsp += (int) $a['jumlah'];
                                        $totalNilaiAsp += (float) $a['total_nilai'];
                                ?>
                                    <tr>
                                        <td class="text-center font-weight-bold"><?= $noAsp++; ?></td>
                                        <td><strong><?= esc($a['nama_barang']); ?></strong></td>
                                        <td class="text-center"><?= esc($a['tahun']); ?></td>
                                        <td class="small"><?= esc($a['jenis_bmn']); ?></td>
                                        <td class="text-center font-monospace small"><code><?= esc($a['kode_bmn']); ?></code></td>
                                        <td class="small"><?= esc($a['merk_tipe']); ?></td>
                                        <td class="text-center"><?= esc($a['kondisi_baik']); ?></td>
                                        <td class="text-center">0</td>
                                        <td class="text-center">0</td>
                                        <td class="text-center"><?= esc($a['satuan']); ?></td>
                                        <td class="text-center font-weight-bold"><?= number_format($a['jumlah']); ?></td>
                                        <td class="text-right"><?= number_format($a['harga_satuan'], 0, ',', '.'); ?></td>
                                        <td class="text-right font-weight-bold"><?= number_format($a['total_nilai'], 0, ',', '.'); ?></td>
                                        <td class="text-center text-muted">-</td>
                                        <td class="text-center text-muted">-</td>
                                        <td class="text-center text-muted">-</td>
                                        <td class="text-center text-muted">-</td>
                                    </tr>
                                <?php endforeach; ?>

                                <!-- Baris Subtotal Jumlah Peralatan Mesin (ASP) -->
                                <tr style="background-color: #D9E1F2; font-weight: bold;">
                                    <td colspan="10" class="text-left font-weight-bold pl-3">Jumlah Peralatan Mesin (ASP)</td>
                                    <td class="text-center font-weight-bold"><?= number_format($totalVolAsp); ?></td>
                                    <td class="text-right">-</td>
                                    <td class="text-right font-weight-bold text-dark"><?= number_format($totalNilaiAsp, 0, ',', '.'); ?></td>
                                    <td colspan="4"></td>
                                </tr>

                                <!-- Baris TOTAL ATR + PM -->
                                <tr style="background-color: #B4C6E7; font-weight: bold; font-size: 0.88rem;">
                                    <td colspan="10" class="text-left font-weight-bold pl-3">TOTAL ATR + PM</td>
                                    <td class="text-center font-weight-bold"><?= number_format(1 + $totalVolAsp); ?></td>
                                    <td class="text-right">-</td>
                                    <td class="text-right font-weight-bold text-primary"><?= number_format((float) $curPaket['nilai_kontrak_fisik'] + $totalNilaiAsp, 0, ',', '.'); ?></td>
                                    <td colspan="4"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

            <!-- ============================================================== -->
            <!-- TAB: ACUAN DOKUMEN (DED & RAB FISIK FINAL) -->
            <!-- ============================================================== -->
            <?php elseif (in_array($activeTab, ['madrasah', 'acuan-dokumen'], true)): ?>
                
                <div class="card bg-light border-0 shadow-sm mb-3" style="border-radius: 8px;">
                    <div class="card-body py-2 px-3">
                        <form method="get" action="<?= site_url('admin/inventaris/sekolah'); ?>">
                            <input type="hidden" name="tab" value="acuan-dokumen">
                            <div class="row align-items-end">
                                <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                                    <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-cube mr-1"></i> Paket PHTC</label>
                                    <select name="paket_id" class="form-control form-control-sm" style="border-radius: 6px;">
                                        <option value="">Semua Paket</option>
                                        <?php foreach ($pakets as $p): ?>
                                            <option value="<?= esc($p['id']); ?>" <?= ($filterPaketId == $p['id']) ? 'selected' : ''; ?>>
                                                <?= esc($p['nama_paket']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2 col-sm-6 mb-2 mb-md-0">
                                    <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-filter mr-1"></i> Status Sekolah</label>
                                    <select name="status" class="form-control form-control-sm" style="border-radius: 6px;">
                                        <option value="">Semua Status</option>
                                        <option value="Negeri" <?= ($filterStatus === 'Negeri') ? 'selected' : ''; ?>>Negeri</option>
                                        <option value="Swasta" <?= ($filterStatus === 'Swasta') ? 'selected' : ''; ?>>Swasta</option>
                                    </select>
                                </div>
                                <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                                    <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-map-marker-alt mr-1"></i> Kabupaten / Kota</label>
                                    <select name="kabupaten" class="form-control form-control-sm" style="border-radius: 6px;">
                                        <option value="">Semua Kabupaten</option>
                                        <?php foreach ($kabupatenOptions as $kab): ?>
                                            <option value="<?= esc($kab); ?>" <?= ($filterKabupaten === $kab) ? 'selected' : ''; ?>><?= esc($kab); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                                    <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-search mr-1"></i> Cari Nama Madrasah</label>
                                    <input type="text" name="keyword" class="form-control form-control-sm" placeholder="Nama madrasah..." value="<?= esc($keyword); ?>" style="border-radius: 6px;">
                                </div>
                                <div class="col-md-1 col-sm-12 text-right">
                                    <button type="submit" class="btn btn-primary btn-sm btn-block" style="border-radius: 6px;">
                                        <i class="fas fa-filter"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-responsive" style="border: 1px solid #e9eef5; border-radius: 8px;">
                    <table class="table table-bordered table-hover w-100 m-0" style="font-size: 0.88rem;">
                        <thead class="thead-light">
                            <tr class="text-center align-middle">
                                <th style="width: 45px;">No</th>
                                <th style="width: 100px;">Paket</th>
                                <th>Nama Madrasah / Sekolah</th>
                                <th style="width: 90px;">Status</th>
                                <th style="width: 140px;">Kabupaten / Kota</th>
                                <th class="text-right" style="width: 160px;">Nilai Fisik ATR (Rp)</th>
                                <th>Acuan Dokumen DED (Perencanaan)</th>
                                <th>Acuan Dokumen RAB Fisik (Final / Adendum)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($sekolahList)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        Tidak ada data madrasah sasaran yang sesuai dengan filter.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($sekolahList as $s): ?>
                                    <tr>
                                        <td class="text-center"><?= $no++; ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-light border">Paket <?= esc($s['no_paket']); ?></span>
                                        </td>
                                        <td>
                                            <strong class="text-dark"><?= esc($s['nama_sekolah']); ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($s['status_sekolah'] === 'Negeri'): ?>
                                                <span class="badge badge-success px-2">Negeri</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary px-2">Swasta</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= esc($s['kabupaten']); ?></td>
                                        <td class="text-right font-weight-bold text-dark">
                                            <?php if (!empty($s['nilai_rab_fisik']) && $s['nilai_rab_fisik'] > 0): ?>
                                                Rp <?= number_format($s['nilai_rab_fisik'], 0, ',', '.'); ?>
                                            <?php else: ?>
                                                <span class="text-muted small">Alokasi Paket</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($s['acuan_ded'])): ?>
                                                <span class="badge badge-info px-2 py-1" style="font-size: 0.82rem; font-weight: 500;">
                                                    <i class="fas fa-file-pdf mr-1"></i><?= esc($s['acuan_ded']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-warning px-2 py-1"><i class="fas fa-exclamation-triangle mr-1"></i>Belum Ada DED</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($s['acuan_rab_fisik'])): ?>
                                                <span class="badge badge-primary px-2 py-1" style="font-size: 0.82rem; font-weight: 500;">
                                                    <i class="fas fa-file-excel mr-1"></i><?= esc($s['acuan_rab_fisik']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-light border px-2 py-1 text-muted">
                                                    <i class="fas fa-file-alt mr-1"></i>BoQ Tender Kontrak
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            <!-- ============================================================== -->
            <!-- TAB: KATALOG MEUBELAIR (ASP) LENGKAP -->
            <!-- ============================================================== -->
            <?php elseif ($activeTab === 'asp'): ?>
                
                <div class="card bg-light border-0 shadow-sm mb-3" style="border-radius: 8px;">
                    <div class="card-body py-2 px-3">
                        <form method="get" action="<?= site_url('admin/inventaris/sekolah'); ?>">
                            <input type="hidden" name="tab" value="asp">
                            <div class="row align-items-end">
                                <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                                    <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-cube mr-1"></i> Filter Paket</label>
                                    <select name="paket_id" class="form-control form-control-sm" style="border-radius: 6px;">
                                        <option value="">Semua Paket</option>
                                        <?php foreach ($pakets as $p): ?>
                                            <option value="<?= esc($p['id']); ?>" <?= ($filterPaketId == $p['id']) ? 'selected' : ''; ?>>
                                                <?= esc($p['nama_paket']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                                    <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-tag mr-1"></i> Jenis Barang Meubelair</label>
                                    <select name="barang" class="form-control form-control-sm" style="border-radius: 6px;">
                                        <option value="">Semua Jenis Meubelair</option>
                                        <?php foreach ($barangOptions as $b): ?>
                                            <option value="<?= esc($b); ?>" <?= ($filterBarang === $b) ? 'selected' : ''; ?>><?= esc($b); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-5 col-sm-6 mb-2 mb-md-0">
                                    <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-search mr-1"></i> Cari Merk / Kode BMN</label>
                                    <input type="text" name="keyword" class="form-control form-control-sm" placeholder="Ketik merk/spesifikasi/kode..." value="<?= esc($keyword); ?>" style="border-radius: 6px;">
                                </div>
                                <div class="col-md-1 col-sm-12 text-right">
                                    <button type="submit" class="btn btn-primary btn-sm btn-block" style="border-radius: 6px;">
                                        <i class="fas fa-filter"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-responsive" style="border: 1px solid #e9eef5; border-radius: 8px;">
                    <table class="table table-bordered table-hover w-100 m-0" style="font-size: 0.88rem;">
                        <thead class="thead-light">
                            <tr class="text-center align-middle">
                                <th style="width: 45px;">No</th>
                                <th style="width: 90px;">Paket</th>
                                <th>Uraian Barang BMN</th>
                                <th style="width: 80px;">Tahun</th>
                                <th style="width: 140px;">Jenis BMN</th>
                                <th style="width: 100px;">Kode BMN</th>
                                <th>Merk / Tipe Spesifikasi</th>
                                <th style="width: 65px;">Kondisi</th>
                                <th style="width: 65px;">Satuan</th>
                                <th class="text-right" style="width: 90px;">Volume</th>
                                <th class="text-right" style="width: 130px;">Harga Satuan (Rp)</th>
                                <th class="text-right" style="width: 140px;">Total Nilai (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($aspList)): ?>
                                <tr>
                                    <td colspan="12" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        Tidak ada data meubelair yang sesuai dengan filter.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                    $no = 1; 
                                    $totVol = 0; 
                                    $totVal = 0; 
                                    foreach ($aspList as $a): 
                                        $totVol += (int) $a['jumlah'];
                                        $totVal += (float) $a['total_nilai'];
                                ?>
                                    <tr>
                                        <td class="text-center"><?= $no++; ?></td>
                                        <td class="text-center"><span class="badge badge-light border">Paket <?= esc($a['no_paket']); ?></span></td>
                                        <td><strong><?= esc($a['nama_barang']); ?></strong></td>
                                        <td class="text-center"><?= esc($a['tahun']); ?></td>
                                        <td class="small"><?= esc($a['jenis_bmn']); ?></td>
                                        <td class="text-center font-monospace small"><code><?= esc($a['kode_bmn']); ?></code></td>
                                        <td class="small"><?= esc($a['merk_tipe']); ?></td>
                                        <td class="text-center"><span class="badge badge-success">Baik (<?= esc($a['kondisi_baik']); ?>)</span></td>
                                        <td class="text-center"><?= esc($a['satuan']); ?></td>
                                        <td class="text-right font-weight-bold"><?= number_format($a['jumlah']); ?></td>
                                        <td class="text-right"><?= number_format($a['harga_satuan'], 0, ',', '.'); ?></td>
                                        <td class="text-right font-weight-bold text-dark"><?= number_format($a['total_nilai'], 0, ',', '.'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr style="background-color: #B4C6E7; font-weight: bold;">
                                    <td colspan="9" class="text-center">TOTAL KESELURUHAN PERALATAN &amp; MESIN (ASP)</td>
                                    <td class="text-right"><?= number_format($totVol); ?></td>
                                    <td></td>
                                    <td class="text-right text-primary"><?= number_format($totVal, 0, ',', '.'); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            <!-- ============================================================== -->
            <!-- TAB: MASTER SEKOLAH BINAAN -->
            <!-- ============================================================== -->
            <?php elseif ($activeTab === 'master'): ?>
                
                <div class="card bg-light border-0 shadow-sm mb-3" style="border-radius: 8px;">
                    <div class="card-body py-2 px-3">
                        <form method="get" action="<?= site_url('admin/inventaris/sekolah'); ?>">
                            <input type="hidden" name="tab" value="master">
                            <div class="row align-items-end">
                                <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                                    <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-cube mr-1"></i> Paket Proyek</label>
                                    <select name="paket_id" class="form-control form-control-sm" style="border-radius: 6px;">
                                        <option value="">Semua Paket</option>
                                        <?php foreach ($pakets as $p): ?>
                                            <option value="<?= esc($p['id']); ?>" <?= ($filterPaketId == $p['id']) ? 'selected' : ''; ?>>
                                                <?= esc($p['nama_paket']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                                    <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-map-marker-alt mr-1"></i> Kabupaten</label>
                                    <select name="kabupaten" class="form-control form-control-sm" style="border-radius: 6px;">
                                        <option value="">Semua Kabupaten</option>
                                        <?php foreach ($kabupatenOptions as $kab): ?>
                                            <option value="<?= esc($kab); ?>" <?= ($filterKabupaten === $kab) ? 'selected' : ''; ?>><?= esc($kab); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-5 col-sm-6 mb-2 mb-md-0">
                                    <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-search mr-1"></i> Cari Sekolah / NPSN</label>
                                    <input type="text" name="keyword" class="form-control form-control-sm" placeholder="Nama sekolah / NPSN..." value="<?= esc($keyword); ?>" style="border-radius: 6px;">
                                </div>
                                <div class="col-md-1 col-sm-12 text-right">
                                    <button type="submit" class="btn btn-primary btn-sm btn-block" style="border-radius: 6px;">
                                        <i class="fas fa-filter"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-responsive" style="border: 1px solid #e9eef5; border-radius: 8px;">
                    <table class="table table-bordered table-hover w-100 m-0" style="font-size: 0.88rem;">
                        <thead class="thead-light">
                            <tr class="text-center align-middle">
                                <th style="width: 45px;">No</th>
                                <th style="width: 110px;">NPSN</th>
                                <th style="width: 110px;">NSM</th>
                                <th>Nama Sekolah / Madrasah</th>
                                <th style="width: 80px;">Jenjang</th>
                                <th style="width: 140px;">Kabupaten / Kota</th>
                                <th style="width: 140px;">Kecamatan</th>
                                <th style="width: 160px;">Paket Proyek</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($masterItems)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        Tidak ada data sekolah master yang ditemukan.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($masterItems as $m): ?>
                                    <tr>
                                        <td class="text-center"><?= $no++; ?></td>
                                        <td class="text-center font-monospace"><code><?= esc($m['npsn'] ?? '-'); ?></code></td>
                                        <td class="text-center font-monospace"><code><?= esc($m['nsm'] ?? '-'); ?></code></td>
                                        <td><strong><?= esc($m['nama']); ?></strong></td>
                                        <td class="text-center"><span class="badge badge-info"><?= esc($m['jenis'] ?? '-'); ?></span></td>
                                        <td><?= esc($m['kabupaten'] ?? '-'); ?></td>
                                        <td><?= esc($m['kecamatan'] ?? '-'); ?></td>
                                        <td class="small"><?= esc($m['nama_paket'] ?? '-'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            <?php endif; ?>

        </div>
    </div>
</div>
<?= $this->endSection(); ?>
