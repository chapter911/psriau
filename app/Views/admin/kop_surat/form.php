<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title"><?= esc($pageTitle ?? 'Form Kop Surat'); ?></h3>
    </div>
    <form action="<?= site_url($actionUrl); ?>" method="post" enctype="multipart/form-data">
        <div class="card-body">
            <?= csrf_field(); ?>

            <div class="form-group">
                <label for="title">Judul Kop Surat</label>
                <input type="text" id="title" name="title" class="form-control" value="<?= old('title', $item['title'] ?? ''); ?>" required>
                <small class="text-muted">Contoh: Kop Surat Kantor Pusat, Kop Surat Cabang, dll.</small>
            </div>

            <div class="form-group">
                <label for="description">Keterangan</label>
                <textarea id="description" name="description" rows="2" class="form-control" placeholder="Opsional: keterangan tambahan"><?= old('description', $item['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="image_file">File Gambar Kop Surat</label>
                <input type="file" id="image_file" name="image_file" class="form-control" accept="image/*" <?= empty($item) ? 'required' : ''; ?>>
                <?php if (! empty($item['image_url'] ?? '')): ?>
                    <img src="<?= esc(base_url($item['image_url'])); ?>" alt="Preview kop surat" class="setting-preview">
                <?php endif; ?>
            </div>

            <div class="form-check">
                <input type="checkbox" id="is_active" name="is_active" value="1" class="form-check-input" <?= old('is_active', $item['is_active'] ?? 1) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="is_active">Aktifkan kop surat ini</label>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="<?= site_url('/admin/master/kop-surat'); ?>" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('form[action]');
    if (!form) {
        return;
    }

    form.addEventListener('submit', function () {
        var submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Menyimpan Kop Surat',
                text: 'Mohon tunggu...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: function () {
                    Swal.showLoading();
                }
            });
        }
    });
});
</script>
<?= $this->endSection(); ?>
