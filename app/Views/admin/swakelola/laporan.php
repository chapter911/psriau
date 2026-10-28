<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">

    <!-- Top Banner -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; background: linear-gradient(135deg, #3730a3 0%, #1e1b4b 100%); color: #ffffff;">
        <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <h4 class="font-weight-bold mb-1 text-white">
                    <i class="fas fa-file-invoice-dollar mr-2 text-warning"></i> Rekapitulasi RAB vs Realisasi & Laporan Pertanggungjawaban
                </h4>
                <p class="text-white-50 mb-0 small">
                    <?= esc($paket['nama_kegiatan'] ?? 'Kegiatan Swakelola'); ?> — TA <?= esc($paket['tahun_anggaran'] ?? '2025'); ?>
                </p>
                <div class="mt-2 text-white-50 small">
                    <span class="mr-3"><i class="fas fa-barcode mr-1"></i> Kode: <strong><?= esc($paket['kode_paket'] ?? '-'); ?></strong></span>
                    <span class="mr-3"><i class="fas fa-money-bill-wave mr-1"></i> Pagu: <strong>Rp <?= number_format((float)($paket['pagu_anggaran'] ?? 0), 0, ',', '.'); ?></strong></span>
                    <span><i class="fas fa-check-circle mr-1 text-success"></i> Serapan: <strong>Rp <?= number_format((float)($paket['realisasi_anggaran'] ?? 0), 0, ',', '.'); ?> (34,18%)</strong></span>
                </div>
            </div>
            <div class="mt-3 mt-md-0 d-flex flex-wrap gap-2">
                <a href="<?= base_url('admin/swakelola/laporan/export-excel'); ?>" class="btn btn-success font-weight-bold shadow-sm px-3 py-2 mr-2" style="border-radius: 8px;">
                    <i class="fas fa-file-excel mr-1"></i> Download Checklist Lengkap (.xlsx)
                </a>
                <a href="<?= base_url('admin/swakelola/dokumen'); ?>" class="btn btn-outline-light font-weight-bold shadow-sm px-3 py-2" style="border-radius: 8px;">
                    <i class="fas fa-check-double mr-1"></i> Lihat 28 Dokumen
                </a>
            </div>
        </div>
    </div>

    <!-- Matriks Perbandingan RAB vs Realisasi -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table mr-2"></i> Matriks Perbandingan Anggaran Biaya (RAB) vs Realisasi Aktual (SPM/SP2D)
            </h6>
            <span class="badge badge-info px-3 py-2" style="border-radius: 20px;">
                Sumber: DIPA POK & Berkas SPM Tahun 2025
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-light">
                        <tr style="font-size: 0.85rem;">
                            <th class="text-center" style="width: 50px;">No</th>
                            <th style="width: 90px;">Kode Akun</th>
                            <th>Uraian Komponen Belanja</th>
                            <th class="text-right" style="width: 150px;">Pagu RAB (Rp)</th>
                            <th class="text-right" style="width: 150px;">Realisasi SP2D (Rp)</th>
                            <th class="text-right" style="width: 150px;">Sisa Pagu (Rp)</th>
                            <th class="text-center" style="width: 90px;">% Serapan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            $totPagu = 0; 
                            $totReal = 0; 
                            $totSisa = 0; 
                            foreach ($rabComparison as $i => $item): 
                                $totPagu += $item['pagu'];
                                $totReal += $item['realisasi'];
                                $totSisa += $item['sisa'];
                        ?>
                            <tr>
                                <td class="text-center font-weight-bold"><?= $i + 1; ?></td>
                                <td><span class="badge badge-secondary"><?= esc($item['akun']); ?></span></td>
                                <td class="font-weight-bold text-dark"><?= esc($item['uraian']); ?></td>
                                <td class="text-right font-weight-bold">
                                    Rp <?= number_format($item['pagu'], 0, ',', '.'); ?>
                                </td>
                                <td class="text-right font-weight-bold text-success">
                                    Rp <?= number_format($item['realisasi'], 0, ',', '.'); ?>
                                </td>
                                <td class="text-right text-muted">
                                    Rp <?= number_format($item['sisa'], 0, ',', '.'); ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= $item['persen'] > 0 ? 'badge-success' : 'badge-light text-muted'; ?> px-2 py-1">
                                        <?= number_format($item['persen'], 2, ',', '.'); ?>%
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="bg-light font-weight-bold" style="font-size: 1rem;">
                        <tr>
                            <td colspan="3" class="text-right text-uppercase py-3">Total Kumulatif:</td>
                            <td class="text-right py-3">Rp <?= number_format($totPagu, 0, ',', '.'); ?></td>
                            <td class="text-right text-success py-3">Rp <?= number_format($totReal, 0, ',', '.'); ?></td>
                            <td class="text-right text-primary py-3">Rp <?= number_format($totSisa, 0, ',', '.'); ?></td>
                            <td class="text-center text-success py-3">34,18%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Catatan Penjelasan Hasil Audit LHA No. 9 -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; background-color: #f8fafc;">
        <div class="card-body p-4">
            <h6 class="font-weight-bold text-dark mb-3">
                <i class="fas fa-info-circle mr-2 text-info"></i> Penjelasan Pertanggungjawaban Realisasi Swakelola SPPG:
            </h6>
            <ol class="text-muted small mb-0 pl-3" style="line-height: 1.8;">
                <li><strong>Capaian Fisik 100%:</strong> Seluruh rangkaian pendampingan teknis dan administrasi percepatan pembangunan Gedung SPPG di Provinsi Riau telah selesai dilaksanakan sesuai target sasaran KAK.</li>
                <li><strong>Efisiensi Anggaran Kas Negara:</strong> Sisa pagu anggaran sebesar <strong>Rp 271.840.900,-</strong> merupakan efisiensi belanja riil yang tidak direalisasikan secara berlebihan dan tetap berada di Rekening Kas Umum Negara (RKUN).</li>
                <li><strong>Kelengkapan Bukti Pengeluaran:</strong> Seluruh realisasi sebesar Rp 141.159.100,- telah didukung bukti pengeluaran yang sah (kuitansi, faktur, SPM, SP2D, bukti potongan PPN/PPh, lembar SPPD, dan dokumentasi foto fisik kegiatan).</li>
                <li><strong>Penyelesaian Administrasi:</strong> Seluruh 28 item dokumen checklist yang dipersyaratkan pada LHA No. 9 telah disusun lengkap (Tahap A s/d E) dan dapat diunduh langsung melalui modul Swakelola ini.</li>
            </ol>
        </div>
    </div>

</div>
<?= $this->endSection(); ?>

