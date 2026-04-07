<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="mb-3">
    <a class="btn btn-primary" href="<?= site_url('/admin/berita/tambah'); ?>">
        <i class="fas fa-plus mr-1"></i> Tambah Embed Instagram
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Embed Instagram</h3>
    </div>
    <div class="card-body">
        <table class="table table-striped table-hover js-datatable w-100">
            <thead>
            <tr>
                <th>Judul</th>
                <th>Kategori</th>
                <th>Status</th>
                <th>Diterbitkan</th>
                <th class="text-right">Aksi</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($articles as $article): ?>
                <tr>
                    <td><?= esc($article['title']); ?></td>
                    <td><?= esc($article['category']); ?></td>
                    <td>
                        <?php if ($article['is_published']): ?>
                            <span class="badge badge-success">Terbit</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">Draft</span>
                        <?php endif; ?>
                    </td>
                    <td><?= esc($article['published_at'] ?? '-'); ?></td>
                    <td class="text-right">
                        <a class="btn btn-sm btn-warning" href="<?= site_url('/admin/berita/' . $article['id'] . '/ubah'); ?>">Ubah</a>
                        <form
                            class="inline-form"
                            method="post"
                            action="<?= site_url('/admin/berita/' . $article['id'] . '/hapus'); ?>"
                            data-confirm-title="Hapus Artikel"
                            data-confirm-text="Hapus artikel ini?"
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
