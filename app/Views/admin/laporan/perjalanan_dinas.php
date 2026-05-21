<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php
$canEdit = (bool) ($can_edit ?? false);
?>
<div class="card">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title mb-0">Laporan Perjalanan Dinas</h3>
        <?php if ($canEdit): ?>
            <div class="card-tools ml-auto">
                <button type="button" class="btn btn-primary btn-sm" disabled title="Fitur sedang dalam pengembangan">Tambah Laporan</button>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="alert alert-info" role="alert">
            <i class="fas fa-info-circle mr-2"></i>
            <strong>Informasi:</strong> Fitur laporan perjalanan dinas sedang dalam pengembangan. Silakan kembali lagi nanti.
        </div>
        <p class="text-muted">Halaman ini akan menampilkan daftar laporan perjalanan dinas dengan fitur untuk menambah, mengedit, dan menghapus laporan.</p>
    </div>
</div>
<?= $this->endSection(); ?>
