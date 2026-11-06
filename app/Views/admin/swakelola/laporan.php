<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php
$activePaketId = (int) ($paket['id'] ?? 0);
$paguPaket     = (float) ($paket['pagu_anggaran'] ?? 0);
$realPaket     = (float) ($paket['realisasi_anggaran'] ?? 0);
$sisaPaket     = $paguPaket - $realPaket;
$pctPaket      = $paguPaket > 0 ? round(($realPaket / $paguPaket) * 100, 2) : 0.0;
$docAda        = (int) ($paket['dokumen_ada'] ?? 0);
$docTot        = (int) ($paket['dokumen_total'] ?? 28);
?>
<div class="container-fluid">

    <!-- Paket Switcher Bar (Paket 1 s/d Paket 4 LHA No. 9) -->
    <div class="card shadow-sm border-0 mb-3" style="border-radius: 12px;">
        <div class="card-body py-3 px-4 d-flex flex-wrap justify-content-between align-items-center">
            <div class="d-flex align-items-center mb-2 mb-lg-0">
                <span class="font-weight-bold text-dark mr-3">
                    <i class="fas fa-layer-group text-primary mr-1"></i> Pilih Paket LHA No. 9:
                </span>
                <div class="nav nav-pills flex-wrap">
                    <?php foreach ($paketList as $pItem):
                        $isAct = ((int) $pItem['id'] === $activePaketId);
                        $lhaNo = (int) ($pItem['lha_no'] ?? 1);
                    ?>
                        <a href="<?= base_url('admin/swakelola/laporan?paket_id=' . $pItem['id']); ?>"
                           class="btn btn-sm <?= $isAct ? 'btn-primary font-weight-bold shadow-sm' : 'btn-outline-secondary'; ?> mr-2 mb-1"
                           style="border-radius: 20px;">
                            Paket <?= $lhaNo; ?> (<?= esc($pItem['pic_label'] ?? $pItem['pic_nama']); ?>)
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <select class="form-control form-control-sm" style="min-width: 260px; border-radius: 8px;" onchange="if(this.value) window.location.href=this.value;">
                    <?php foreach ($paketList as $pItem):
                        $isAct = ((int) $pItem['id'] === $activePaketId);
                        $lhaNo = (int) ($pItem['lha_no'] ?? 1);
                    ?>
                        <option value="<?= base_url('admin/swakelola/laporan?paket_id=' . $pItem['id']); ?>" <?= $isAct ? 'selected' : ''; ?>>
                            Paket <?= $lhaNo; ?> — <?= esc($pItem['nama_kegiatan']); ?> (PIC: <?= esc($pItem['pic_label'] ?? '-'); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Top Banner -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; background: linear-gradient(135deg, #3730a3 0%, #1e1b4b 100%); color: #ffffff;">
        <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <div class="mb-1">
                    <span class="badge badge-warning text-dark font-weight-bold px-2 py-1 mr-2">
                        PAKET <?= (int) ($paket['lha_no'] ?? 1); ?> — PIC: <?= esc($paket['pic_nama'] ?? '-'); ?>
                    </span>
                </div>
                <h4 class="font-weight-bold mb-1 text-white">
                    <i class="fas fa-file-invoice-dollar mr-2 text-warning"></i> Rekapitulasi RAB vs Realisasi &amp; Laporan Pertanggungjawaban
                </h4>
                <p class="text-white-50 mb-0 small">
                    <?= esc($paket['nama_kegiatan'] ?? 'Kegiatan Swakelola'); ?> — TA <?= esc($paket['tahun_anggaran'] ?? '2025'); ?>
                </p>
                <div class="mt-2 text-white-50 small">
                    <span class="mr-3"><i class="fas fa-barcode mr-1"></i> Kode MAK: <strong><?= esc($paket['kode_paket'] ?? '-'); ?></strong></span>
                    <span class="mr-3"><i class="fas fa-money-bill-wave mr-1"></i> Pagu POK Rev 16: <strong>Rp <?= number_format($paguPaket, 0, ',', '.'); ?></strong></span>
                    <span><i class="fas fa-check-circle mr-1 text-success"></i> Serapan: <strong>Rp <?= number_format($realPaket, 0, ',', '.'); ?> (<?= number_format($pctPaket, 2, ',', '.'); ?>%)</strong></span>
                </div>
            </div>
            <div class="mt-3 mt-md-0 d-flex flex-wrap gap-2">
                <a href="<?= base_url('admin/swakelola/perjadin?paket_id=' . $activePaketId); ?>" class="btn btn-warning text-dark font-weight-bold shadow-sm px-3 py-2 mr-2" style="border-radius: 8px;">
                    <i class="fas fa-plane-departure mr-1"></i> Daftar Perjadin
                </a>
                <?php if (! empty($permissions['export'])): ?>
                    <a href="<?= base_url('admin/swakelola/laporan/export-excel?paket_id=' . $activePaketId); ?>" class="btn btn-success font-weight-bold shadow-sm px-3 py-2 mr-2" style="border-radius: 8px;">
                        <i class="fas fa-file-excel mr-1"></i> Download Checklist Lengkap (.xlsx)
                    </a>
                <?php endif; ?>
                <a href="<?= base_url('admin/swakelola/dokumen?paket_id=' . $activePaketId); ?>" class="btn btn-outline-light font-weight-bold shadow-sm px-3 py-2" style="border-radius: 8px;">
                    <i class="fas fa-check-double mr-1"></i> Lihat 28 Dokumen (<?= $docAda; ?>/<?= $docTot; ?>)
                </a>
            </div>
        </div>
    </div>

    <!-- Matriks Perbandingan RAB / POK Rev 16 vs Realisasi -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
        <div class="card-header bg-white py-3 border-0 d-flex flex-wrap justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table mr-2"></i> Matriks Perbandingan Pagu POK Revisi 16 vs Realisasi Aktual (<?= (int) ($realisasiCount ?? 0); ?> Transaksi SPM/SP2D)
            </h6>
            <span class="badge badge-info px-3 py-2" style="border-radius: 20px;">
                Sumber: POK Rev 16 (23 Des 2025) &amp; Data Realisasi SPM TA <?= esc($paket['tahun_anggaran'] ?? '2025'); ?>
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-light">
                        <tr style="font-size: 0.85rem;">
                            <th class="text-center" style="width: 50px;">No</th>
                            <th style="width: 90px;">Kode Akun</th>
                            <th>Uraian Komponen Belanja (POK Revisi 16)</th>
                            <th class="text-right" style="width: 150px;">Pagu RAB (Rp)</th>
                            <th class="text-right" style="width: 150px;">Realisasi SP2D (Rp)</th>
                            <th class="text-right" style="width: 150px;">Sisa Pagu (Rp)</th>
                            <th class="text-center" style="width: 95px;">% Serapan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            $totPagu = 0.0; 
                            $totReal = 0.0; 
                            $totSisa = 0.0; 
                            foreach ($rabComparison as $i => $item): 
                                $totPagu += (float) $item['pagu'];
                                $totReal += (float) $item['realisasi'];
                                $totSisa += (float) $item['sisa'];
                        ?>
                            <tr>
                                <td class="text-center font-weight-bold"><?= $i + 1; ?></td>
                                <td><span class="badge badge-secondary"><?= esc($item['akun']); ?></span></td>
                                <td class="font-weight-bold text-dark"><?= esc($item['uraian']); ?></td>
                                <td class="text-right font-weight-bold">
                                    Rp <?= number_format((float) $item['pagu'], 0, ',', '.'); ?>
                                </td>
                                <td class="text-right font-weight-bold text-success">
                                    Rp <?= number_format((float) $item['realisasi'], 0, ',', '.'); ?>
                                </td>
                                <td class="text-right text-muted">
                                    Rp <?= number_format((float) $item['sisa'], 0, ',', '.'); ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= $item['persen'] > 0 ? 'badge-success' : 'badge-light text-muted'; ?> px-2 py-1">
                                        <?= number_format((float) $item['persen'], 2, ',', '.'); ?>%
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <?php $totPct = $totPagu > 0 ? round(($totReal / $totPagu) * 100, 2) : 0.0; ?>
                    <tfoot class="bg-light font-weight-bold" style="font-size: 1rem;">
                        <tr>
                            <td colspan="3" class="text-right text-uppercase py-3">Total Kumulatif:</td>
                            <td class="text-right py-3">Rp <?= number_format($totPagu, 0, ',', '.'); ?></td>
                            <td class="text-right text-success py-3">Rp <?= number_format($totReal, 0, ',', '.'); ?></td>
                            <td class="text-right text-primary py-3">Rp <?= number_format($totSisa, 0, ',', '.'); ?></td>
                            <td class="text-center text-success py-3"><?= number_format($totPct, 2, ',', '.'); ?>%</td>
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
                <i class="fas fa-info-circle mr-2 text-info"></i> Catatan Pertanggungjawaban Administrasi Swakelola (Paket <?= (int) ($paket['lha_no'] ?? 1); ?> — <?= esc($paket['nama_kegiatan']); ?>):
            </h6>
            <ol class="text-muted small mb-0 pl-3" style="line-height: 1.8;">
                <li><strong>Penanggung Jawab (PIC Paket):</strong> <?= esc($paket['pic_nama'] ?? '-'); ?> di bawah supervisi PPK (<?= esc($paket['ppk_nama'] ?? '-'); ?>) dan Kepala Satker PPS Riau (<?= esc($paket['kasatker_nama'] ?? '-'); ?>).</li>
                <li><strong>Landasan Anggaran POK Revisi 16:</strong> Kode MAK <code><?= esc($paket['kode_paket']); ?></code> dengan Pagu Anggaran sebesar <strong>Rp <?= number_format($totPagu, 0, ',', '.'); ?>,-</strong> dan Realisasi SP2D tercatat sebesar <strong>Rp <?= number_format($totReal, 0, ',', '.'); ?>,-</strong> (Sisa Pagu Efisiensi Kas Negara: <strong>Rp <?= number_format($totSisa, 0, ',', '.'); ?>,-</strong>).</li>
                <li><strong>Progres Kelengkapan 28 Dokumen LHA No. 9:</strong> Saat ini telah tersedia <strong><?= $docAda; ?> dari <?= $docTot; ?> dokumen</strong> (<?= number_format((float) ($paket['dokumen_pct'] ?? 0), 1, ',', '.'); ?>%) pada Tahap A (Perencanaan), Tahap B (Persiapan), Tahap C (Pelaksanaan), Tahap D (Pengawasan), dan Tahap E (Serah Terima).</li>
                <li><strong>Pengelolaan Multi-Paket:</strong> Setiap PIC paket dapat mengunggah/memperbarui berkas dokumen (<code>.pdf</code> / <code>.docx</code>) pada menu <em>Checklist Dokumen</em> serta menambahkan rincian voucher SPM/SP2D pada menu <em>Realisasi Anggaran</em> sesuai hak akses <code>menu_akses</code>.</li>
            </ol>
        </div>
    </div>

</div>
<?= $this->endSection(); ?>
