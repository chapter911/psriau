<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">

    <!-- KPI Summary Widgets -->
    <div class="row mb-3">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #007bff;">
                <span class="info-box-icon bg-primary elevation-1" style="border-radius: 8px;"><i class="fas fa-file-contract"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Total Fisik (ATR)</span>
                    <span class="info-box-number text-dark" style="font-size: 1.15rem;">Rp <?= number_format($stats['total_fisik'] ?? 0, 0, ',', '.'); ?></span>
                    <small class="text-muted">9 Paket PHTC Riau</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #17a2b8;">
                <span class="info-box-icon bg-info elevation-1" style="border-radius: 8px;"><i class="fas fa-couch"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Peralatan Mesin (ASP)</span>
                    <span class="info-box-number text-dark" style="font-size: 1.15rem;">Rp <?= number_format($stats['total_asp'] ?? 0, 0, ',', '.'); ?></span>
                    <small class="text-muted"><?= number_format($stats['total_asp_unit'] ?? 0, 0, ',', '.'); ?> Unit Meubelair</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #28a745;">
                <span class="info-box-icon bg-success elevation-1" style="border-radius: 8px;"><i class="fas fa-calculator"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Total Nilai Investasi</span>
                    <span class="info-box-number text-success font-weight-bold" style="font-size: 1.15rem;">Rp <?= number_format($stats['total_gabungan'] ?? 0, 0, ',', '.'); ?></span>
                    <small class="text-muted">Akumulasi ATR + ASP</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #6f42c1;">
                <span class="info-box-icon elevation-1" style="border-radius: 8px; background-color: #6f42c1; color: white;"><i class="fas fa-school"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Madrasah Sasaran</span>
                    <span class="info-box-number text-dark" style="font-size: 1.15rem;"><?= number_format($stats['total_madrasah'] ?? 0); ?> Madrasah</span>
                    <small class="text-muted"><?= $stats['count_negeri'] ?? 0; ?> Negeri | <?= $stats['count_swasta'] ?? 0; ?> Swasta</small>
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
                        <i class="fas fa-landmark text-primary mr-2"></i>Hasil Penelitian BMN - Pekerjaan Rehabilitasi &amp; Renovasi Madrasah (PHTC)
                    </h3>
                    <small class="text-muted d-block mt-1">
                        Penatausahaan BMN Aset Tetap Renovasi (ATR), Alat &amp; Suku Cadang (ASP Meubelair), Dokumen Acuan Perencanaan (DED), dan Sarana Prasarana Sekolah
                    </small>
                </div>
                <div class="d-flex align-items-center" style="gap: 8px;">
                    <?php if ($can_export): ?>
                        <a href="<?= site_url('admin/inventaris/sekolah/export-excel'); ?>" class="btn btn-success btn-sm shadow-sm" style="border-radius: 6px;">
                            <i class="fas fa-file-excel mr-1"></i> Ekspor Excel (3 Sheet)
                        </a>
                        <a href="<?= site_url('admin/inventaris/sekolah/cetak-pdf'); ?>" target="_blank" class="btn btn-danger btn-sm shadow-sm" style="border-radius: 6px;">
                            <i class="fas fa-file-pdf mr-1"></i> Cetak PDF
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Nav Tabs -->
            <ul class="nav nav-pills mt-3" style="gap: 6px;">
                <li class="nav-item">
                    <a class="nav-link <?= ($activeTab === 'rekap') ? 'active font-weight-bold shadow-sm' : 'text-secondary'; ?>" 
                       href="<?= site_url('admin/inventaris/sekolah?tab=rekap'); ?>" style="border-radius: 8px; font-size: 0.92rem;">
                        <i class="fas fa-chart-pie mr-1"></i> 1. Rekapitulasi Paket BMN
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($activeTab === 'madrasah') ? 'active font-weight-bold shadow-sm' : 'text-secondary'; ?>" 
                       href="<?= site_url('admin/inventaris/sekolah?tab=madrasah'); ?>" style="border-radius: 8px; font-size: 0.92rem;">
                        <i class="fas fa-school mr-1"></i> 2. Madrasah Sasaran &amp; Acuan Dokumen (DED &amp; RAB)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($activeTab === 'asp') ? 'active font-weight-bold shadow-sm' : 'text-secondary'; ?>" 
                       href="<?= site_url('admin/inventaris/sekolah?tab=asp'); ?>" style="border-radius: 8px; font-size: 0.92rem;">
                        <i class="fas fa-chair mr-1"></i> 3. Rincian Meubelair (ASP)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($activeTab === 'master') ? 'active font-weight-bold shadow-sm' : 'text-secondary'; ?>" 
                       href="<?= site_url('admin/inventaris/sekolah?tab=master'); ?>" style="border-radius: 8px; font-size: 0.92rem;">
                        <i class="fas fa-database mr-1"></i> 4. Master Sekolah Binaan
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body p-3">

            <!-- ============================================================== -->
            <!-- TAB 1: REKAPITULASI PAKET BMN PHTC -->
            <!-- ============================================================== -->
            <?php if ($activeTab === 'rekap'): ?>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-hover w-100" style="border-radius: 8px; font-size: 0.9rem;">
                        <thead class="thead-light">
                            <tr class="text-center align-middle">
                                <th style="width: 45px;">No</th>
                                <th>Uraian Paket Kegiatan</th>
                                <th style="width: 100px;">Tahun</th>
                                <th>Jenis BMN</th>
                                <th style="width: 100px;">Kode BMN</th>
                                <th>Wilayah / Lokasi</th>
                                <th style="width: 90px;">Jml Madrasah</th>
                                <th class="text-right">Kontrak Fisik ATR (Rp)</th>
                                <th class="text-right">MK / Supervisi (Rp)</th>
                                <th class="text-right">Alat &amp; Mesin ASP (Rp)</th>
                                <th class="text-right">Total ATR + ASP (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $totalFisik = 0; 
                                $totalMk = 0; 
                                $totalAsp = 0; 
                                $totalGab = 0; 
                                $totalSekolahCount = 0;
                            ?>
                            <?php foreach ($paketList as $p): ?>
                                <?php 
                                    $totalFisik += (float) $p['nilai_kontrak_fisik'];
                                    $totalMk    += (float) $p['nilai_mk_supervisi'];
                                    $totalAsp   += (float) $p['total_asp_nilai'];
                                    $totalGab   += (float) $p['total_gabungan'];
                                    $totalSekolahCount += (int) $p['jumlah_madrasah'];
                                ?>
                                <tr>
                                    <td class="text-center align-middle font-weight-bold"><?= $p['no_paket']; ?></td>
                                    <td class="align-middle">
                                        <span class="font-weight-bold text-primary"><?= esc($p['nama_paket']); ?></span>
                                        <?php if (! empty($p['penyedia_jasa'])): ?>
                                            <small class="d-block text-muted mt-1">
                                                <i class="fas fa-hard-hat text-warning mr-1"></i> Penyedia: <strong><?= esc($p['penyedia_jasa']); ?></strong>
                                            </small>
                                        <?php endif; ?>
                                        <div class="mt-1 d-flex flex-wrap" style="gap: 4px;">
                                            <?php if (! empty($p['status_adendum'])): ?>
                                                <span class="badge badge-light border text-primary small">
                                                    <i class="fas fa-file-invoice mr-1"></i> <?= esc($p['status_adendum']); ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if (! empty($p['acuan_rab_fisik'])): ?>
                                                <span class="badge badge-light border text-success small" title="Acuan Dokumen Fisik / BoQ">
                                                    <i class="fas fa-paperclip mr-1"></i> <?= esc($p['acuan_rab_fisik']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle"><span class="badge badge-light border"><?= esc($p['tahun_anggaran']); ?></span></td>
                                    <td class="align-middle small"><?= esc($p['jenis_bmn']); ?></td>
                                    <td class="text-center align-middle font-italic small"><?= esc($p['kode_bmn']); ?></td>
                                    <td class="align-middle small font-weight-bold text-secondary"><?= esc($p['wilayah']); ?></td>
                                    <td class="text-center align-middle">
                                        <a href="<?= site_url('admin/inventaris/sekolah?tab=madrasah&paket_id=' . $p['id']); ?>" class="badge badge-info px-2 py-1" title="Lihat Madrasah Sasaran">
                                            <?= $p['jumlah_madrasah']; ?> Unit
                                        </a>
                                    </td>
                                    <td class="text-right align-middle font-weight-bold text-dark">
                                        <?= number_format($p['nilai_kontrak_fisik'], 0, ',', '.'); ?>
                                    </td>
                                    <td class="text-right align-middle text-muted">
                                        <?= number_format($p['nilai_mk_supervisi'], 0, ',', '.'); ?>
                                    </td>
                                    <td class="text-right align-middle text-info font-weight-bold">
                                        <a href="<?= site_url('admin/inventaris/sekolah?tab=asp&paket_id=' . $p['id']); ?>" class="text-info" title="Lihat Rincian Meubelair">
                                            <?= number_format($p['total_asp_nilai'], 0, ',', '.'); ?>
                                        </a>
                                    </td>
                                    <td class="text-right align-middle font-weight-bold text-success" style="background-color: #f7fafc;">
                                        <?= number_format($p['total_gabungan'], 0, ',', '.'); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot style="background-color: #e9ecef; font-weight: bold; font-size: 0.95rem;">
                            <tr>
                                <td colspan="6" class="text-right align-middle py-3">TOTAL KESELURUHAN (PAKET 1 - 9):</td>
                                <td class="text-center align-middle py-3 text-info"><?= $totalSekolahCount; ?> Unit</td>
                                <td class="text-right align-middle py-3 text-primary">Rp <?= number_format($totalFisik, 0, ',', '.'); ?></td>
                                <td class="text-right align-middle py-3 text-muted">Rp <?= number_format($totalMk, 0, ',', '.'); ?></td>
                                <td class="text-right align-middle py-3 text-info">Rp <?= number_format($totalAsp, 0, ',', '.'); ?></td>
                                <td class="text-right align-middle py-3 text-success">Rp <?= number_format($totalGab, 0, ',', '.'); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Info Box Keterangan BMN -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 shadow-sm border-0 bg-light" style="border-radius: 8px;">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-dark mb-2"><i class="fas fa-info-circle text-info mr-1"></i> Penjelasan Kategori BMN:</h6>
                                <ul class="small text-muted mb-0 pl-3" style="line-height: 1.8;">
                                    <li><strong>ATR (Aset Tetap Renovasi / Fisik):</strong> Gedung dan prasarana madrasah yang direhabilitasi melalui kontrak fisik Paket 1 s/d 9.</li>
                                    <li><strong>ASP (Alat &amp; Suku Cadang / Peralatan Mesin):</strong> Pengadaan meubelair sekolah (Meja Siswa, Kursi Siswa, Meja Guru, Kursi Guru, Lemari Besi, Whiteboard) dengan total 18.881 unit barang.</li>
                                    <li><strong>MK / Supervisi:</strong> Biaya Konsultan Manajemen Konstruksi / Supervisi Pelaksanaan untuk memastikan mutu pekerjaan.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 shadow-sm border-0 bg-light" style="border-radius: 8px;">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-dark mb-2"><i class="fas fa-folder-open text-warning mr-1"></i> Acuan Dokumen Perencanaan (DED):</h6>
                                <p class="small text-muted mb-2">
                                    Tersedia <strong>49 berkas perencanaan teknis (PDF &amp; RAR)</strong> yang menjadi acuan legalitas dan spesifikasi renovasi madrasah binaan di Satker PPS Riau.
                                </p>
                                <a href="<?= site_url('admin/inventaris/sekolah?tab=madrasah'); ?>" class="btn btn-outline-primary btn-sm" style="border-radius: 6px;">
                                    <i class="fas fa-list-check mr-1"></i> Buka Tab Madrasah &amp; Acuan DED
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            <!-- ============================================================== -->
            <!-- TAB 2: DAFTAR MADRASAH SASARAN & STATUS ACUAN DED -->
            <!-- ============================================================== -->
            <?php elseif ($activeTab === 'madrasah'): ?>
                <!-- Filter Form -->
                <div class="card bg-light border-0 shadow-sm mb-3" style="border-radius: 8px;">
                    <div class="card-body py-2 px-3">
                        <form method="get" action="<?= site_url('admin/inventaris/sekolah'); ?>">
                            <input type="hidden" name="tab" value="madrasah">
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
                                    <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-tag mr-1"></i> Status</label>
                                    <select name="status" class="form-control form-control-sm" style="border-radius: 6px;">
                                        <option value="">Semua Status</option>
                                        <option value="Negeri" <?= ($filterStatus === 'Negeri') ? 'selected' : ''; ?>>Negeri</option>
                                        <option value="Swasta" <?= ($filterStatus === 'Swasta') ? 'selected' : ''; ?>>Swasta</option>
                                    </select>
                                </div>
                                <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                                    <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-map-marker-alt mr-1"></i> Wilayah / Kabupaten</label>
                                    <select name="kabupaten" class="form-control form-control-sm" style="border-radius: 6px;">
                                        <option value="">Semua Wilayah</option>
                                        <?php foreach ($kabupatenOptions as $kab): ?>
                                            <option value="<?= esc($kab); ?>" <?= ($filterKabupaten === $kab) ? 'selected' : ''; ?>><?= esc($kab); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2 col-sm-6 mb-2 mb-md-0">
                                    <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-search mr-1"></i> Kata Kunci</label>
                                    <input type="text" name="keyword" class="form-control form-control-sm" placeholder="Nama madrasah..." value="<?= esc($keyword ?? ''); ?>" style="border-radius: 6px;">
                                </div>
                                <div class="col-md-2 col-sm-6 d-flex" style="gap: 6px;">
                                    <button type="submit" class="btn btn-primary btn-sm flex-fill shadow-sm" style="border-radius: 6px;">
                                        <i class="fas fa-filter mr-1"></i> Filter
                                    </button>
                                    <a href="<?= site_url('admin/inventaris/sekolah?tab=madrasah'); ?>" class="btn btn-outline-secondary btn-sm shadow-sm" style="border-radius: 6px;" title="Reset Filter">
                                        <i class="fas fa-undo"></i>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped w-100 js-datatable" style="border-radius: 8px; font-size: 0.9rem;">
                        <thead class="thead-light">
                            <tr class="text-center align-middle">
                                <th style="width: 45px;">No</th>
                                <th style="width: 110px;">Paket</th>
                                <th>Nama Madrasah / Sekolah</th>
                                <th style="width: 85px;">Status</th>
                                <th>Kabupaten / Kota</th>
                                <th class="text-right" style="width: 140px;">Nilai Fisik ATR (Rp)</th>
                                <th>Acuan Dokumen DED (Perencanaan)</th>
                                <th>Acuan Dokumen RAB Fisik (Final / Adendum)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($sekolahList as $s): ?>
                                <tr>
                                    <td class="text-center align-middle"><?= $no++; ?></td>
                                    <td class="align-middle text-center">
                                        <span class="badge badge-secondary px-2 py-1">Paket <?= esc($s['no_paket']); ?></span>
                                    </td>
                                    <td class="align-middle font-weight-bold text-primary">
                                        <i class="fas fa-school mr-1 text-muted"></i> <?= esc($s['nama_sekolah']); ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <?php if ($s['status_sekolah'] === 'Negeri'): ?>
                                            <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Negeri</span>
                                        <?php else: ?>
                                            <span class="badge badge-info px-2 py-1"><i class="fas fa-building mr-1"></i> Swasta</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle"><?= esc($s['kabupaten'] ?: '-'); ?></td>
                                    <td class="text-right align-middle">
                                        <?php if ((float) ($s['nilai_rab_fisik'] ?? 0) > 0): ?>
                                            <span class="font-weight-bold text-dark">Rp <?= number_format($s['nilai_rab_fisik'], 0, ',', '.'); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted small">Termasuk Nilai Paket</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <?php if (! empty($s['acuan_ded'])): ?>
                                            <div class="d-flex align-items-center" style="gap: 6px;">
                                                <span class="badge badge-light border text-dark font-weight-bold px-2 py-1">
                                                    <i class="fas fa-file-pdf text-danger mr-1"></i> <?= esc($s['acuan_ded']); ?>
                                                </span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small">- Acuan DED Paket -</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <?php if (! empty($s['acuan_rab_fisik'])): ?>
                                            <div class="d-flex align-items-center" style="gap: 6px;">
                                                <span class="badge badge-light border text-success font-weight-bold px-2 py-1">
                                                    <i class="fas fa-calculator text-success mr-1"></i> <?= esc($s['acuan_rab_fisik']); ?>
                                                </span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small">- Acuan BoQ Paket -</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <!-- ============================================================== -->
            <!-- TAB 3: RINCIAN PERALATAN & MESIN (ASP / MEUBELAIR) -->
            <!-- ============================================================== -->
            <?php elseif ($activeTab === 'asp'): ?>
                <!-- Filter Form -->
                <div class="card bg-light border-0 shadow-sm mb-3" style="border-radius: 8px;">
                    <div class="card-body py-2 px-3">
                        <form method="get" action="<?= site_url('admin/inventaris/sekolah'); ?>">
                            <input type="hidden" name="tab" value="asp">
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
                                    <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-couch mr-1"></i> Jenis Barang</label>
                                    <select name="barang" class="form-control form-control-sm" style="border-radius: 6px;">
                                        <option value="">Semua Jenis Barang</option>
                                        <?php foreach ($barangOptions as $b): ?>
                                            <option value="<?= esc($b); ?>" <?= ($filterBarang === $b) ? 'selected' : ''; ?>><?= esc($b); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 col-sm-6 mb-2 mb-md-0">
                                    <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-search mr-1"></i> Kata Kunci (Merk/Kode)</label>
                                    <input type="text" name="keyword" class="form-control form-control-sm" placeholder="Nama merk / tipe / kode BMN..." value="<?= esc($keyword ?? ''); ?>" style="border-radius: 6px;">
                                </div>
                                <div class="col-md-2 col-sm-6 d-flex" style="gap: 6px;">
                                    <button type="submit" class="btn btn-primary btn-sm flex-fill shadow-sm" style="border-radius: 6px;">
                                        <i class="fas fa-filter mr-1"></i> Filter
                                    </button>
                                    <a href="<?= site_url('admin/inventaris/sekolah?tab=asp'); ?>" class="btn btn-outline-secondary btn-sm shadow-sm" style="border-radius: 6px;" title="Reset Filter">
                                        <i class="fas fa-undo"></i>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped w-100 js-datatable" style="border-radius: 8px; font-size: 0.9rem;">
                        <thead class="thead-light">
                            <tr class="text-center align-middle">
                                <th style="width: 45px;">No</th>
                                <th style="width: 120px;">Paket</th>
                                <th>Uraian Barang BMN</th>
                                <th style="width: 80px;">Tahun</th>
                                <th>Jenis BMN</th>
                                <th style="width: 100px;">Kode BMN</th>
                                <th>Merk / Tipe Spesifikasi</th>
                                <th style="width: 70px;">Satuan</th>
                                <th style="width: 80px;" class="text-right">Volume</th>
                                <th class="text-right">Harga Satuan (Rp)</th>
                                <th class="text-right">Total Nilai Fisik (Rp)</th>
                                <th style="width: 70px;">Kondisi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $no = 1; 
                                $subTotalUnit = 0; 
                                $subTotalNilai = 0; 
                            ?>
                            <?php foreach ($aspList as $a): ?>
                                <?php 
                                    $subTotalUnit  += (int) $a['jumlah'];
                                    $subTotalNilai += (float) $a['total_nilai'];
                                ?>
                                <tr>
                                    <td class="text-center align-middle"><?= $no++; ?></td>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-secondary px-2 py-1">Paket <?= esc($a['no_paket']); ?></span>
                                    </td>
                                    <td class="align-middle font-weight-bold text-dark">
                                        <?= esc($a['nama_barang']); ?>
                                    </td>
                                    <td class="text-center align-middle small text-muted"><?= esc($a['tahun']); ?></td>
                                    <td class="align-middle small"><?= esc($a['jenis_bmn']); ?></td>
                                    <td class="text-center align-middle font-italic small"><?= esc($a['kode_bmn']); ?></td>
                                    <td class="align-middle font-weight-bold text-primary small"><?= esc($a['merk_tipe'] ?: '-'); ?></td>
                                    <td class="text-center align-middle"><?= esc($a['satuan']); ?></td>
                                    <td class="text-right align-middle font-weight-bold text-info">
                                        <?= number_format($a['jumlah'], 0, ',', '.'); ?>
                                    </td>
                                    <td class="text-right align-middle text-muted">
                                        <?= number_format($a['harga_satuan'], 0, ',', '.'); ?>
                                    </td>
                                    <td class="text-right align-middle font-weight-bold text-success">
                                        <?= number_format($a['total_nilai'], 0, ',', '.'); ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-success px-2 py-1"><i class="fas fa-check"></i> Baik</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot style="background-color: #f1f3f5; font-weight: bold;">
                            <tr>
                                <td colspan="8" class="text-right align-middle py-2">SUBTOTAL FILTER / SELURUHNYA:</td>
                                <td class="text-right align-middle py-2 text-info"><?= number_format($subTotalUnit, 0, ',', '.'); ?> Unit</td>
                                <td></td>
                                <td class="text-right align-middle py-2 text-success">Rp <?= number_format($subTotalNilai, 0, ',', '.'); ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            <!-- ============================================================== -->
            <!-- TAB 4: MASTER SEKOLAH BINAAN (EXISTING MST_SEKOLAH) -->
            <!-- ============================================================== -->
            <?php elseif ($activeTab === 'master'): ?>
                <!-- Filter Form -->
                <div class="card bg-light border-0 shadow-sm mb-3" style="border-radius: 8px;">
                    <div class="card-body py-2 px-3">
                        <form method="get" action="<?= site_url('admin/inventaris/sekolah'); ?>">
                            <input type="hidden" name="tab" value="master">
                            <div class="row align-items-end">
                                <div class="col-md-4 col-sm-6 mb-2 mb-md-0">
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
                                    <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-map mr-1"></i> Kabupaten</label>
                                    <select name="kabupaten" class="form-control form-control-sm" style="border-radius: 6px;">
                                        <option value="">Semua Kabupaten</option>
                                        <?php foreach ($kabupatenOptions as $kab): ?>
                                            <option value="<?= esc($kab); ?>" <?= ($filterKabupaten === $kab) ? 'selected' : ''; ?>><?= esc($kab); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                                    <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-search mr-1"></i> Kata Kunci</label>
                                    <input type="text" name="keyword" class="form-control form-control-sm" placeholder="Nama sekolah / NPSN..." value="<?= esc($keyword ?? ''); ?>" style="border-radius: 6px;">
                                </div>
                                <div class="col-md-2 col-sm-6 d-flex" style="gap: 6px;">
                                    <button type="submit" class="btn btn-primary btn-sm flex-fill shadow-sm" style="border-radius: 6px;">
                                        <i class="fas fa-filter mr-1"></i> Filter
                                    </button>
                                    <a href="<?= site_url('admin/inventaris/sekolah?tab=master'); ?>" class="btn btn-outline-secondary btn-sm shadow-sm" style="border-radius: 6px;" title="Reset Filter">
                                        <i class="fas fa-undo"></i>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered table-striped w-100 js-datatable" style="border-radius: 8px;">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 45px;" class="text-center align-middle">#</th>
                                <th class="align-middle">NPSN / NSM</th>
                                <th class="align-middle">Nama Sekolah</th>
                                <th class="align-middle">Jenis</th>
                                <th class="align-middle">Paket Pembangunan</th>
                                <th class="align-middle">Kabupaten</th>
                                <th class="align-middle">Kecamatan</th>
                                <th class="text-center align-middle" style="width: 140px;">Status Sarpras</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($masterItems as $item): ?>
                                <tr>
                                    <td class="text-center align-middle"><?= $no++; ?></td>
                                    <td class="align-middle">
                                        <span class="font-weight-bold text-dark"><?= esc($item['npsn'] ?: '-'); ?></span>
                                        <?php if (! empty($item['nsm'])): ?>
                                            <small class="text-muted d-block">NSM: <?= esc($item['nsm']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <span class="font-weight-bold text-primary"><?= esc($item['nama']); ?></span>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge badge-light border"><?= esc($item['jenis'] ?: 'Sekolah'); ?></span>
                                    </td>
                                    <td class="align-middle">
                                        <?php if (! empty($item['nama_paket'])): ?>
                                            <span class="badge badge-secondary px-2 py-1"><?= esc($item['nama_paket']); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted small">- Belum Ditautkan -</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle"><?= esc($item['kabupaten'] ?: '-'); ?></td>
                                    <td class="align-middle"><?= esc($item['kecamatan'] ?: '-'); ?></td>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-success px-2 py-1">
                                            <i class="fas fa-check-circle mr-1"></i> Terinventarisir
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>
<?= $this->endSection(); ?>
