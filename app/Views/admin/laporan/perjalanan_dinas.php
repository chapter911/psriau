<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="card">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title mb-0">Laporan Perjalanan Dinas</h3>
        <div class="card-tools ml-auto">
            <a href="<?= site_url('/admin/laporan/perjalanan-dinas/buat'); ?>" class="btn btn-primary btn-sm">Buat Laporan</a>
        </div>
    </div>
    <div class="card-body">
        <div class="alert alert-info mb-0" role="alert">
            Halaman ini menjadi pintu masuk untuk membuat laporan perjalanan dinas dalam bentuk PDF.
        </div>
    </div>
</div>
<?= $this->endSection(); ?>
