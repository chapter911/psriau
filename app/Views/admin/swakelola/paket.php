<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">

    <!-- Header Banner -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; background: linear-gradient(135deg, #0d3b66 0%, #001e3d 100%); color: #ffffff;">
        <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <h4 class="font-weight-bold mb-1 text-white">
                    <i class="fas fa-clipboard-check mr-2 text-warning"></i> Administrasi Swakelola Satker PPS Riau (LHA No. 9)
                </h4>
                <p class="text-white-50 mb-0 small">
                    Pengelolaan terpadu 4 Paket Kegiatan Swakelola Tipe I, monitoring checklist 28 dokumen (Tahap A s/d E), dan akuntabilitas realisasi SPM/SP2D.
                </p>
            </div>
            <div class="mt-3 mt-md-0">
                <?php if (! empty($permissions['export'])): ?>
                    <a href="<?= base_url('admin/swakelola/laporan/export-excel'); ?>" class="btn btn-success font-weight-bold shadow-sm px-3 py-2" style="border-radius: 8px;">
                        <i class="fas fa-file-excel mr-1"></i> Download Checklist Lengkap (.xlsx)
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Table Paket Swakelola -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
        <div class="card-header bg-white py-3 border-0 d-flex flex-wrap justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-tasks mr-2"></i> Daftar 4 Paket Kegiatan Swakelola (Mandat LHA No. 9)
            </h6>
            <span class="badge badge-primary px-3 py-2" style="border-radius: 20px;">
                Total: <?= count($paketList); ?> Paket Terdata
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="thead-light">
                        <tr style="font-size: 0.85rem;">
                            <th class="text-center" style="width: 65px;">Paket</th>
                            <th>Nama Kegiatan &amp; Kode MAK (POK Rev 16)</th>
                            <th>PIC Penanggung Jawab</th>
                            <th class="text-center" style="width: 155px;">Kelengkapan 28 Dokumen</th>
                            <th class="text-right">Pagu POK Rev 16</th>
                            <th class="text-right">Realisasi (SPM)</th>
                            <th class="text-center" style="width: 100px;">Serapan</th>
                            <th>Pejabat (PPK / Kasatker)</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" style="width: 195px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($paketList)): ?>
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">Belum ada paket swakelola terdaftar.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($paketList as $idx => $p):
                                $pagu   = (float) ($p['pagu_anggaran'] ?? 0);
                                $real   = (float) ($p['realisasi_anggaran'] ?? 0);
                                $pct    = $pagu > 0 ? round(($real / $pagu) * 100, 1) : 0;
                                $docAda = (int) ($p['dokumen_ada'] ?? 0);
                                $docTot = (int) ($p['dokumen_total'] ?? 28);
                                $docPct = (float) ($p['dokumen_pct'] ?? 0);
                                $lhaNo  = (int) ($p['lha_no'] ?? ($idx + 1));
                                $isDone = ($p['status'] ?? '') === 'Selesai' || $docAda >= $docTot;
                            ?>
                                <tr>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-dark px-2 py-1 font-weight-bold">Paket <?= $lhaNo; ?></span>
                                        <small class="d-block text-muted mt-1"><?= esc($p['tahun_anggaran']); ?> / <?= esc($p['tipe_swakelola']); ?></small>
                                    </td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold text-dark"><?= esc($p['nama_kegiatan']); ?></div>
                                        <small class="text-muted"><i class="fas fa-barcode mr-1"></i> <?= esc($p['kode_paket']); ?></small><br>
                                        <small class="text-info"><i class="fas fa-map-marker-alt mr-1"></i> <?= esc($p['lokasi']); ?></small>
                                    </td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold text-primary">
                                            <i class="fas fa-user-check mr-1"></i> <?= esc($p['pic_nama'] ?? '-'); ?>
                                        </div>
                                        <?php if (! empty($p['pic_label'])): ?>
                                            <span class="badge badge-light border text-secondary mt-1">PIC: <?= esc($p['pic_label']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="font-weight-bold <?= $docAda >= $docTot ? 'text-success' : 'text-primary'; ?>">
                                            <?= $docAda; ?> / <?= $docTot; ?> Dokumen (<?= $docPct; ?>%)
                                        </div>
                                        <div class="progress mt-1" style="height: 7px;">
                                            <div class="progress-bar <?= $docAda >= $docTot ? 'bg-success' : 'bg-info'; ?>" style="width: <?= min(100, $docPct); ?>%"></div>
                                        </div>
                                    </td>
                                    <td class="text-right font-weight-bold align-middle">
                                        Rp <?= number_format($pagu, 0, ',', '.'); ?>
                                    </td>
                                    <td class="text-right font-weight-bold text-success align-middle">
                                        Rp <?= number_format($real, 0, ',', '.'); ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="font-weight-bold text-dark"><?= $pct; ?>%</div>
                                        <div class="progress mt-1" style="height: 6px;">
                                            <div class="progress-bar bg-success" style="width: <?= min(100, $pct); ?>%"></div>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <small class="d-block"><strong>PPK:</strong> <?= esc($p['ppk_nama']); ?></small>
                                        <small class="text-muted d-block"><strong>Kasatker:</strong> <?= esc($p['kasatker_nama']); ?></small>
                                    </td>
                                    <td class="text-center align-middle">
                                        <?php if ($isDone): ?>
                                            <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Selesai</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-clock mr-1"></i> <?= esc($p['status']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= base_url('admin/swakelola/dokumen?paket_id=' . $p['id']); ?>" class="btn btn-outline-primary" title="Kelola 28 Dokumen Checklist">
                                                <i class="fas fa-check-double mr-1"></i> Dokumen
                                            </a>
                                            <a href="<?= base_url('admin/swakelola/realisasi?paket_id=' . $p['id']); ?>" class="btn btn-outline-success" title="Realisasi SPM &amp; Kuitansi">
                                                <i class="fas fa-receipt mr-1"></i> Realisasi
                                            </a>
                                            <a href="<?= base_url('admin/swakelola/laporan?paket_id=' . $p['id']); ?>" class="btn btn-outline-info" title="Rekapitulasi RAB vs Realisasi">
                                                <i class="fas fa-chart-pie"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<?= $this->endSection(); ?>
