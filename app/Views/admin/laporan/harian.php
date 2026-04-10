<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php
$titles = $titles ?? [];
$titleStats = $title_stats ?? [];
$canEdit = (bool) ($can_edit ?? false);
?>

<div class="card">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title mb-0">Daftar Sekolah</h3>
        <?php if ($canEdit): ?>
            <div class="card-tools ml-auto">
                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalTambahSekolah">
                    Tambah Sekolah
                </button>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover w-100" id="tableTitleLaporanHarian">
                <thead class="thead-light">
                    <tr>
                        <th style="width:60px;">No</th>
                        <th>Sekolah</th>
                        <th style="width:140px;" class="text-center">Total Laporan</th>
                        <th style="width:150px;" class="text-center">Laporan Terakhir</th>
                        <th style="width:170px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($titles !== []): ?>
                        <?php $no = 1; foreach ($titles as $title): ?>
                            <?php $stats = $titleStats[(int) ($title['id'] ?? 0)] ?? ['total_reports' => 0, 'last_report_date' => '']; ?>
                            <tr>
                                <td class="text-center\"><?= esc((string) $no++); ?></td>
                                <td><?= esc((string) ($title['name'] ?? '-')); ?></td>
                                <td class="text-center"><?= esc((string) ($stats['total_reports'] ?? 0)); ?></td>
                                <td class="text-center"><?= esc((string) ($stats['last_report_date'] ?: '-')); ?></td>
                                <td class="text-center">
                                    <a href="<?= site_url('admin/laporan/harian/' . (int) ($title['id'] ?? 0)); ?>" class="btn btn-primary btn-sm">Detail</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Belum ada data sekolah.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($canEdit): ?>
<div class="modal fade" id="modalTambahSekolah" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mb-0">Tambah Sekolah</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('admin/laporan/harian/sekolah/tambah'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-group mb-0">
                        <label for="name">Nama Sekolah</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script>
(() => {
    if (typeof $ === 'undefined' || ! $.fn.DataTable) {
        return;
    }

    const $table = $('#tableTitleLaporanHarian');
    if ($table.length && ! $.fn.dataTable.isDataTable($table)) {
        $table.DataTable({
            responsive: false,
            autoWidth: false,
            scrollX: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Semua']],
            language: {
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data',
                zeroRecords: 'Data tidak ditemukan',
                paginate: {
                    first: 'Awal',
                    last: 'Akhir',
                    next: 'Berikutnya',
                    previous: 'Sebelumnya',
                },
            },
        });
    }
})();
</script>
<?= $this->endSection(); ?>
