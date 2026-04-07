<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title"><?= esc($pageTitle ?? 'Form Acara'); ?></h3>
    </div>
    <form action="<?= site_url($actionUrl); ?>" method="post" enctype="multipart/form-data">
        <div class="card-body">
            <?= csrf_field(); ?>
            <div class="form-group">
                <label for="title">Judul Acara</label>
                <input type="text" id="title" name="title" class="form-control" value="<?= old('title', $event['title'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="event_date">Tanggal Acara</label>
                <input type="date" id="event_date" name="event_date" class="form-control" value="<?= old('event_date', $event['event_date'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="location">Lokasi</label>
                <input type="text" id="location" name="location" class="form-control" value="<?= old('location', $event['location'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="image_file">Gambar Acara</label>
                <input type="file" id="image_file" name="image_file" class="form-control" accept="image/*">
                <?php if (! empty($event['image_url'] ?? '')): ?>
                    <img src="<?= esc($event['image_url']); ?>" alt="Preview gambar acara" class="setting-preview">
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="summary">Ringkasan</label>
                <textarea id="summary" name="summary" rows="3" class="form-control" required><?= old('summary', $event['summary'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="content">Konten</label>
                <textarea id="content" name="content" rows="8" class="form-control" required><?= old('content', $event['content'] ?? ''); ?></textarea>
            </div>

            <div class="form-check">
                <input type="checkbox" id="is_published" name="is_published" value="1" class="form-check-input" <?= old('is_published', $event['is_published'] ?? 1) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="is_published">Publikasikan acara</label>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="<?= site_url('/admin/acara'); ?>" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>
<?= $this->endSection(); ?>
