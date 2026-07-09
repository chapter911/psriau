<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php
$rekap = $rekap ?? [];
$sekolahs = $sekolahs ?? [];
$canAdd = (bool) ($can_add ?? false);
$canEdit = (bool) ($can_edit ?? false);
$canDelete = (bool) ($can_delete ?? false);

// Calculate Bottom Totals
$sumJumlahHarga = 0;
$sumBobot = 0;
$sumProgresLalu = 0;
$sumProgresIni = 0;
$sumProgresSampaiIni = 0;
$sumRencana = 0;
$sumDeviasi = 0;

foreach ($sekolahs as $sekolah) {
    $sumJumlahHarga += (float) ($sekolah['jumlah_harga'] ?? 0);
    $sumBobot += (float) ($sekolah['bobot'] ?? 0);
    $sumProgresLalu += (float) ($sekolah['progres_minggu_lalu'] ?? 0);
    $sumProgresIni += (float) ($sekolah['progres_minggu_ini'] ?? 0);
    $sumProgresSampaiIni += (float) ($sekolah['progres_sampai_minggu_ini'] ?? 0);
    $sumRencana += (float) ($sekolah['rencana'] ?? 0);
    $sumDeviasi += (float) ($sekolah['deviasi'] ?? 0);
}

// PPN 11%
$ppnVal = $sumJumlahHarga * 0.11;

// Total A + B
$totalVal = $sumJumlahHarga + $ppnVal;

// Pembulatan
$pembulatanVal = round($totalVal, -2);
?>

<div class="d-flex align-items-center mb-3">
    <a href="<?= site_url('admin/laporan/rekap-mingguan'); ?>" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar Periode
    </a>
</div>

<div class="card card-outline card-primary">
    <div class="card-header bg-light d-flex align-items-center">
        <h3 class="card-title mb-0 font-weight-bold text-primary">
            <i class="fas fa-table mr-1"></i> REKAPITULASI LAPORAN BOBOT - <?= esc($rekap['judul']); ?> (<?= esc($rekap['nama_paket'] ?? 'Tanpa Paket'); ?>)
        </h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover table-sm w-100" id="tableRekapShow" style="font-size: 0.9rem;">
                <thead class="thead-dark text-center">
                    <tr>
                        <th class="align-middle" style="width: 50px;">NO.</th>
                        <th class="align-middle" style="min-width: 250px;">JENIS PEKERJAAN</th>
                        <th class="align-middle" style="min-width: 150px;">JUMLAH HARGA ADD 2</th>
                        <th class="align-middle" style="width: 90px;">BOBOT (%)</th>
                        <th class="align-middle" style="width: 120px;">PROGRES MINGGU LALU</th>
                        <th class="align-middle" style="width: 120px;">PROGRES MINGGU INI</th>
                        <th class="align-middle" style="width: 130px;">PROGRES SAMPAI MINGGU INI</th>
                        <th class="align-middle" style="width: 100px;">RENCANA</th>
                        <th class="align-middle" style="width: 100px;">DEVIASI</th>
                        <th class="align-middle" style="width: 150px;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($sekolahs !== []): ?>
                        <?php foreach ($sekolahs as $sekolah): ?>
                            <tr>
                                <td class="text-center align-middle font-weight-bold"><?= esc($sekolah['no_urut']); ?></td>
                                <td class="align-middle font-weight-bold text-dark"><?= esc($sekolah['nama_sekolah']); ?></td>
                                <td class="text-right align-middle">Rp <?= number_format((float)$sekolah['jumlah_harga'], 2, ',', '.'); ?></td>
                                <td class="text-center align-middle"><?= number_format((float)$sekolah['bobot'], 3, ',', '.'); ?>%</td>
                                <td class="text-center align-middle"><?= number_format((float)$sekolah['progres_minggu_lalu'], 3, ',', '.'); ?>%</td>
                                <td class="text-center align-middle"><?= number_format((float)$sekolah['progres_minggu_ini'], 3, ',', '.'); ?>%</td>
                                <td class="text-center align-middle font-weight-bold text-success"><?= number_format((float)$sekolah['progres_sampai_minggu_ini'], 3, ',', '.'); ?>%</td>
                                <td class="text-center align-middle italic text-muted"><?= number_format((float)$sekolah['rencana'], 2, ',', '.'); ?>%</td>
                                <td class="text-center align-middle text-danger"><?= number_format((float)$sekolah['deviasi'], 3, ',', '.'); ?>%</td>
                                <td class="text-center align-middle">
                                    <a href="<?= site_url('admin/laporan/rekap-mingguan/detail/' . (int)$rekap['id'] . '?sekolah=' . urlencode($sekolah['nama_sekolah'])); ?>" 
                                       class="btn btn-primary btn-xs mr-1" title="Buka Detail RAB">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                    <?php if ($canEdit): ?>
                                        <button type="button" class="btn btn-warning btn-xs btn-edit-summary" 
                                                data-id="<?= $sekolah['id']; ?>" 
                                                data-sekolah="<?= esc($sekolah['nama_sekolah']); ?>"
                                                data-rencana="<?= $sekolah['rencana']; ?>"
                                                data-progres="<?= $sekolah['progres_minggu_ini']; ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <!-- Bottom Totals Rows -->
                        <tr class="bg-light font-weight-bold" style="font-size: 0.9rem;">
                            <td class="text-center align-middle"></td>
                            <td class="align-middle">(A) Jumlah Harga Pekerjaan (termasuk Biaya Umum dan Keuntungan)</td>
                            <td class="text-right align-middle">Rp <?= number_format($sumJumlahHarga, 2, ',', '.'); ?></td>
                            <td class="text-center align-middle"><?= number_format($sumBobot, 3, ',', '.'); ?>%</td>
                            <td class="text-center align-middle"><?= number_format($sumProgresLalu, 3, ',', '.'); ?>%</td>
                            <td class="text-center align-middle"><?= number_format($sumProgresIni, 3, ',', '.'); ?>%</td>
                            <td class="text-center align-middle text-success"><?= number_format($sumProgresSampaiIni, 3, ',', '.'); ?>%</td>
                            <td class="text-center align-middle text-muted"><?= number_format($sumRencana, 2, ',', '.'); ?>%</td>
                            <td class="text-center align-middle text-danger"><?= number_format($sumDeviasi, 3, ',', '.'); ?>%</td>
                            <td></td>
                        </tr>
                        
                        <tr class="bg-light font-weight-bold" style="font-size: 0.9rem;">
                            <td class="text-center align-middle"></td>
                            <td class="align-middle">(B) Pajak Pertambahan Nilai ( PPn ) = 11% x (A)</td>
                            <td class="text-right align-middle">Rp <?= number_format($ppnVal, 2, ',', '.'); ?></td>
                            <td colspan="7"></td>
                        </tr>

                        <tr class="bg-light font-weight-bold" style="font-size: 0.9rem;">
                            <td class="text-center align-middle"></td>
                            <td class="align-middle">(C) Jumlah Total Harga Pekerjaan = (A) + (B)</td>
                            <td class="text-right align-middle">Rp <?= number_format($totalVal, 2, ',', '.'); ?></td>
                            <td colspan="7"></td>
                        </tr>

                        <tr class="bg-primary font-weight-bold" style="font-size: 0.95rem;">
                            <td class="text-center align-middle"></td>
                            <td class="align-middle text-uppercase">Pembulatan</td>
                            <td class="text-right align-middle text-white">Rp <?= number_format($pembulatanVal, 2, ',', '.'); ?></td>
                            <td colspan="7"></td>
                        </tr>
                        
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">Data Rekap belum tersedia. Silakan unggah berkas excel untuk mengisi data.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Edit Summary Item -->
<?php if ($canEdit): ?>
<div class="modal fade" id="modalEditSummary" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mb-0"><i class="fas fa-edit mr-1"></i> Edit Data Pekerjaan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('admin/laporan/rekap-mingguan/' . $rekap['id'] . '/ubah'); ?>" method="post">
                <?= csrf_field(); ?>
                <input type="hidden" name="action" value="edit_summary_item">
                <input type="hidden" name="item_id" id="edit_item_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Jenis Pekerjaan</label>
                        <input type="text" class="form-control" id="edit_sekolah_nama" readonly>
                    </div>
                    <div class="form-group">
                        <label for="edit_rencana">Rencana (%)</label>
                        <input type="number" step="0.0001" class="form-control" id="edit_rencana" name="rencana" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_progres_ini">Progres Minggu Ini (%)</label>
                        <input type="number" step="0.0001" class="form-control" id="edit_progres_ini" name="progres_minggu_ini" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script>
$(document).ready(function() {
    // Edit Summary handler
    $('.btn-edit-summary').on('click', function() {
        const id = $(this).data('id');
        const sekolah = $(this).data('sekolah');
        const rencana = $(this).data('rencana');
        const progres = $(this).data('progres');

        $('#edit_item_id').val(id);
        $('#edit_sekolah_nama').val(sekolah);
        $('#edit_rencana').val(rencana);
        $('#edit_progres_ini').val(progres);
        $('#modalEditSummary').modal('show');
    });
});
</script>
<?= $this->endSection(); ?>
