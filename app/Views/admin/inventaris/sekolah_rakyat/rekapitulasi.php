<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">

    <!-- Header Dokumen Ringkasan Eksekutif -->
    <div class="card shadow-sm mb-3" style="border-radius: 12px; border: 1px solid #e9eef5;">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <span class="badge badge-primary px-2.5 py-1 mb-2" style="font-size: 0.85rem;">Satker Pelaksanaan Prasarana Strategis Riau</span>
                    <h4 class="font-weight-bold text-dark mb-1">Rekapitulasi Kertas Kerja Rincian BMN Sekolah Rakyat</h4>
                    <p class="text-muted mb-0 small">
                        Pekerjaan: <strong>Pembangunan Sekolah Rakyat Provinsi Riau</strong><br>
                        Lokasi: Komplek Sport Center, Kel. Sungai Jering, Kec. Kuantan Tengah, Kab. Kuantan Singingi, Riau<br>
                        Kontrak: <code>HK.02.01/PPS-RIAU/FSK-SR/2025/01</code> tanggal 24 Desember 2025 | Penyedia Jasa: PT. Duta Rama – PT. Gala Karya, KSO
                    </p>
                </div>
                <div class="col-md-4 text-md-right mt-3 mt-md-0">
                    <div class="p-3 bg-light rounded border">
                        <small class="text-muted font-weight-bold d-block">TOTAL NILAI KONTRAK (ADDENDUM V)</small>
                        <h3 class="font-weight-bold text-primary mb-0" style="font-size: 1.6rem;">
                            Rp <?= number_format($stats['total_nilai'] ?? 0, 0, ',', '.'); ?>
                        </h3>
                        <small class="text-muted">Termasuk PPN 11%</small>
                    </div>
                    <?php if ($canExport): ?>
                        <div class="mt-2">
                            <a href="<?= site_url('admin/inventaris/sekolah-rakyat/rekapitulasi/cetak-pdf'); ?>" target="_blank" class="btn btn-sm btn-danger shadow-sm">
                                <i class="fas fa-file-pdf mr-1"></i> Cetak PDF Rekapitulasi
                            </a>
                            <a href="<?= site_url('admin/inventaris/sekolah-rakyat/barang/export-excel'); ?>" class="btn btn-sm btn-success shadow-sm">
                                <i class="fas fa-file-excel mr-1"></i> Unduh Excel
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 1. Rekapitulasi Berdasarkan Kelompok BMN -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm" style="border-radius: 12px; border: 1px solid #e9eef5;">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title font-weight-bold text-dark mb-0">
                        <i class="fas fa-layer-group text-primary mr-2"></i>1. Rekapitulasi Berdasarkan 3 Klasifikasi Kelompok BMN
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped mb-0" style="font-size: 0.9rem;">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 50px;" class="text-center align-middle">No</th>
                                    <th class="align-middle">Kelompok BMN</th>
                                    <th style="width: 140px;" class="text-center align-middle">Jumlah Rincian</th>
                                    <th style="width: 160px;" class="text-center align-middle">Total Kuantitas Fisik</th>
                                    <th style="width: 220px;" class="text-right align-middle">Total Nilai Perolehan (Rp)</th>
                                    <th style="width: 120px;" class="text-center align-middle">Proporsi (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    $grandTotal = $stats['total_nilai'] ?: 1;
                                    $no = 1;
                                    foreach ($kelompokSummary as $ks): 
                                        $proporsi = round(($ks['total_nilai'] / $grandTotal) * 100, 2);
                                ?>
                                    <tr>
                                        <td class="text-center font-weight-bold"><?= $no++; ?></td>
                                        <td class="font-weight-bold text-primary">
                                            <?= esc($ks['kelompok_bmn']); ?>
                                        </td>
                                        <td class="text-center font-weight-bold"><?= number_format($ks['total_item']); ?> Item</td>
                                        <td class="text-center"><?= number_format($ks['total_unit']); ?> Unit/Set/Buah</td>
                                        <td class="text-right font-weight-bold text-dark">
                                            Rp <?= number_format((float) $ks['total_nilai'], 0, ',', '.'); ?>
                                        </td>
                                        <td class="text-center font-weight-bold"><?= $proporsi; ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="bg-light font-weight-bold" style="font-size: 0.95rem;">
                                <tr>
                                    <td colspan="2" class="text-center">TOTAL KESELURUHAN ASET BMN</td>
                                    <td class="text-center text-primary"><?= number_format($stats['total_items']); ?> Item</td>
                                    <td class="text-center">-</td>
                                    <td class="text-right text-primary">Rp <?= number_format($stats['total_nilai'], 0, ',', '.'); ?></td>
                                    <td class="text-center">100.00%</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Alokasi Biaya Umum (Dari Sheet Dasar Perhitungan) -->
    <div class="row mb-3">
        <div class="col-md-5">
            <div class="card shadow-sm h-100" style="border-radius: 12px; border: 1px solid #e9eef5;">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title font-weight-bold text-dark mb-0">
                        <i class="fas fa-calculator text-info mr-2"></i>Alokasi Biaya Umum Proyek
                    </h5>
                    <small class="text-muted d-block mt-1">Biaya umum dialokasikan proporsional ke aset Gedung dan Jaringan</small>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-bordered mb-0" style="font-size: 0.85rem;">
                        <thead class="thead-light">
                            <tr>
                                <th>Kode</th>
                                <th>Uraian Biaya Umum</th>
                                <th class="text-right">Nilai (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>A</td><td>SMKK (di luar APAR)</td><td class="text-right">Rp 529.246.583</td></tr>
                            <tr><td>B.1</td><td>Pematangan Lahan</td><td class="text-right">Rp 4.724.406.658</td></tr>
                            <tr><td>B.2</td><td>Pekerjaan Persiapan</td><td class="text-right">Rp 585.650.000</td></tr>
                            <tr><td>B.3</td><td>Mobilisasi/Demobilisasi Alat Berat</td><td class="text-right">Rp 111.500.000</td></tr>
                            <tr><td>D.17</td><td>Pekerjaan Pengujian</td><td class="text-right">Rp 209.900.000</td></tr>
                            <tr><td>E.16</td><td>Penyambungan Daya PLN & Perijinan</td><td class="text-right">Rp 316.202.500</td></tr>
                        </tbody>
                        <tfoot class="bg-light font-weight-bold">
                            <tr>
                                <td colspan="2" class="text-center">Jumlah Biaya Umum Dialokasikan</td>
                                <td class="text-right text-primary">Rp 6.476.905.741</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card shadow-sm h-100" style="border-radius: 12px; border: 1px solid #e9eef5;">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title font-weight-bold text-dark mb-0">
                        <i class="fas fa-clipboard-check text-success mr-2"></i>Catatan Metodologi Pencatatan BMN
                    </h5>
                </div>
                <div class="card-body py-3" style="font-size: 0.88rem; line-height: 1.6;">
                    <ol class="pl-3 mb-0 text-muted">
                        <li class="mb-2"><strong>Peralatan dan Mesin (294 Item):</strong> Mebel, AC split, kipas angin, dan CCTV dicatat tersendiri per unit/set sesuai uraian item RAB Addendum V, lengkap dengan kode BMN 3.xx masing-masing.</li>
                        <li class="mb-2"><strong>Utilitas Melekat pada Gedung:</strong> Sprinkler, exhaust fan, pompa air penunjang, sound system, dan instalasi mekanikal/elektrikal yang menempel dicatat terintegrasi ke dalam nilai gedung tempat utilitas tersebut berada.</li>
                        <li class="mb-2"><strong>Bangunan Khusus Kawasan:</strong> Rumah Pompa + GWT, Rumah Genset, Bangunan IPAL Kawasan, TPS, dan Jalan Khusus Kompleks dicatat tersendiri sesuai kodefikasi BMN kelompok Gedung/Jaringan.</li>
                        <li class="mb-0"><strong>Pembulatan Nilai Kontrak:</strong> Seluruh nilai perolehan dihitung dalam rupiah penuh tanpa desimal. Selisih pembulatan dibebankan ke Gedung Sekolah SD sehingga total nilai pencatatan tepat sama dengan Nilai Kontrak Addendum V (Rp 239.445.195.000,-).</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Rincian Dasar Perhitungan 33 Aset Gedung & Jaringan -->
    <div class="card shadow-sm mb-4" style="border-radius: 12px; border: 1px solid #e9eef5;">
        <div class="card-header bg-white py-3">
            <h5 class="card-title font-weight-bold text-dark mb-0">
                <i class="fas fa-table text-primary mr-2"></i>2. Rincian Perhitungan Nilai Perolehan Gedung, Bangunan, Jalan dan Jaringan (33 Aset)
            </h5>
            <small class="text-muted d-block mt-1">Breakdown komponen RAB Addendum V, alokasi biaya umum, dan PPN 11%</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover mb-0" style="font-size: 0.83rem;">
                    <thead class="thead-dark">
                        <tr>
                            <th style="width: 35px;" class="text-center align-middle">No</th>
                            <th class="align-middle">Nama Aset / Prasarana</th>
                            <th style="width: 140px;" class="align-middle">Komponen RAB</th>
                            <th style="width: 60px;" class="text-center align-middle">Jml</th>
                            <th style="width: 110px;" class="text-right align-middle">RAB Sblm PPN</th>
                            <th style="width: 100px;" class="text-right align-middle">Alokasi Biaya Umum</th>
                            <th style="width: 110px;" class="text-right align-middle">Subtotal</th>
                            <th style="width: 100px;" class="text-right align-middle">PPN 11%</th>
                            <th style="width: 120px;" class="text-right align-middle">Total Perolehan (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dasarItems as $d): ?>
                            <tr>
                                <td class="text-center font-weight-bold"><?= $d['no_urut']; ?></td>
                                <td class="font-weight-bold text-primary"><?= esc($d['nama_aset']); ?></td>
                                <td><code><?= esc($d['komponen_rab'] ?: '-'); ?></code></td>
                                <td class="text-center"><?= rtrim(rtrim(number_format((float) $d['jumlah'], 2, ',', '.'), '0'), ','); ?> <?= esc($d['satuan']); ?></td>
                                <td class="text-right">Rp <?= number_format((float) $d['nilai_rab'], 0, ',', '.'); ?></td>
                                <td class="text-right">Rp <?= number_format((float) $d['alokasi_biaya_umum'], 0, ',', '.'); ?></td>
                                <td class="text-right">Rp <?= number_format((float) $d['subtotal'], 0, ',', '.'); ?></td>
                                <td class="text-right">Rp <?= number_format((float) $d['ppn'], 0, ',', '.'); ?></td>
                                <td class="text-right font-weight-bold text-dark">Rp <?= number_format((float) $d['total_nilai_perolehan'], 0, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>
