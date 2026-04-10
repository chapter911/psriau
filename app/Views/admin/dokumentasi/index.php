<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="mb-3 d-flex flex-wrap justify-content-between align-items-center" style="gap:12px;">
    <div>
        <h3 class="mb-1">Kegiatan Lapangan</h3>
        <p class="text-muted mb-0">Dokumentasi foto kegiatan lapangan dengan tampilan tabel dan preview foto utama.</p>
    </div>
    <a class="btn btn-primary" href="<?= site_url('/admin/dokumentasi/kegiatan-lapangan/tambah'); ?>">
        <i class="fas fa-plus mr-1"></i> Tambah Kegiatan
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Kegiatan Lapangan</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover js-datatable w-100" data-order='[[1,"desc"]]'>
                <thead>
                <tr>
                    <th>Judul Kegiatan</th>
                    <th>Tanggal Kegiatan</th>
                    <th>Lokasi Kegiatan</th>
                    <th>Foto Kegiatan</th>
                    <th>Dibuat Oleh</th>
                    <th class="text-right">Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($activities as $activity): ?>
                    <?php $coverPhoto = (string) ($activity['cover_photo'] ?? ''); ?>
                    <tr>
                        <td><?= esc((string) ($activity['title'] ?? '-')); ?></td>
                        <td><?= esc((string) ($activity['activity_date'] ?? '-')); ?></td>
                        <td><?= esc((string) ($activity['location'] ?? '-')); ?></td>
                        <td>
                            <div class="d-flex align-items-center" style="gap:10px;">
                                <?php if ($coverPhoto !== ''): ?>
                                    <img src="<?= esc($coverPhoto); ?>" alt="Foto kegiatan" style="width:54px;height:54px;object-fit:cover;border-radius:10px;border:1px solid #dee2e6;">
                                <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-center bg-light text-muted" style="width:54px;height:54px;border-radius:10px;border:1px solid #dee2e6;">
                                        <i class="far fa-image"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <div class="font-weight-bold"><?= (int) ($activity['photo_count'] ?? 0); ?> foto</div>
                                    <small class="text-muted">Foto pertama ditampilkan di sini.</small>
                                </div>
                            </div>
                        </td>
                        <td><?= esc((string) ($activity['created_by'] ?? '-')); ?></td>
                        <td class="text-right">
                            <a class="btn btn-sm btn-warning" href="<?= site_url('/admin/dokumentasi/kegiatan-lapangan/' . $activity['id'] . '/ubah'); ?>">Ubah</a>
                            <form
                                class="inline-form"
                                method="post"
                                action="<?= site_url('/admin/dokumentasi/kegiatan-lapangan/' . $activity['id'] . '/hapus'); ?>"
                                data-confirm-title="Hapus Kegiatan"
                                data-confirm-text="Hapus kegiatan lapangan ini beserta seluruh fotonya?"
                                data-confirm-button="Ya, hapus"
                            >
                                <?= csrf_field(); ?>
                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>