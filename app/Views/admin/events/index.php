<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="mb-3">
    <a class="btn btn-primary" href="<?= site_url('/admin/acara/tambah'); ?>">
        <i class="fas fa-plus mr-1"></i> Tambah Acara
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Acara</h3>
    </div>
    <div class="card-body">
        <table class="table table-striped table-hover js-datatable w-100">
            <thead>
            <tr>
                <th>Judul</th>
                <th>Tanggal</th>
                <th>Lokasi</th>
                <th>Status</th>
                <th class="text-right">Aksi</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($events as $event): ?>
                <tr>
                    <td><?= esc($event['title']); ?></td>
                    <td><?= esc($event['event_date'] ?? '-'); ?></td>
                    <td><?= esc($event['location'] ?? '-'); ?></td>
                    <td>
                        <?php if ($event['is_published']): ?>
                            <span class="badge badge-success">Terbit</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">Draft</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-right">
                        <a class="btn btn-sm btn-warning" href="<?= site_url('/admin/acara/' . $event['id'] . '/ubah'); ?>">Ubah</a>
                        <form
                            class="inline-form"
                            method="post"
                            action="<?= site_url('/admin/acara/' . $event['id'] . '/hapus'); ?>"
                            data-confirm-title="Hapus Acara"
                            data-confirm-text="Hapus acara ini?"
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
<?= $this->endSection(); ?>
