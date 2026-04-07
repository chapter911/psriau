<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="card card-primary mb-4">
    <div class="card-header">
        <h3 class="card-title">Pengaturan Hero & Identitas Home</h3>
    </div>
    <form action="<?= site_url('/admin/pengaturan-home'); ?>" method="post" enctype="multipart/form-data">
        <div class="card-body">
            <?= csrf_field(); ?>

            <div class="form-group">
                <label for="hero_title">Judul Hero</label>
                <input type="text" id="hero_title" name="hero_title" class="form-control" value="<?= old('hero_title', $setting['hero_title']); ?>" required>
            </div>

            <div class="form-group">
                <label for="hero_subtitle">Subjudul Hero</label>
                <textarea id="hero_subtitle" name="hero_subtitle" rows="3" class="form-control" required><?= old('hero_subtitle', $setting['hero_subtitle']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="about_intro">Pengantar Tentang Kami</label>
                <textarea id="about_intro" name="about_intro" rows="4" class="form-control" required><?= old('about_intro', $setting['about_intro']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="official_name">Nama Resmi Instansi</label>
                <input type="text" id="official_name" name="official_name" class="form-control" value="<?= old('official_name', $setting['official_name'] ?? 'Satker PPS Kementerian PU'); ?>" required>
            </div>

            <div class="form-group">
                <label for="logo_file">Logo Aplikasi</label>
                <input type="file" id="logo_file" name="logo_file" class="form-control" accept="image/*">
                <?php $logoPreview = $setting['logo_url'] ?? ''; ?>
                <?php if (! empty($logoPreview)): ?>
                    <img src="<?= esc($logoPreview); ?>" alt="Preview logo" class="setting-preview">
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="contact_email">Email Kontak Resmi</label>
                <input type="email" id="contact_email" name="contact_email" class="form-control" value="<?= old('contact_email', $setting['contact_email'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="contact_phone">Telepon Kontak Resmi</label>
                <input type="text" id="contact_phone" name="contact_phone" class="form-control" value="<?= old('contact_phone', $setting['contact_phone'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="contact_address">Alamat Resmi</label>
                <textarea id="contact_address" name="contact_address" rows="2" class="form-control"><?= old('contact_address', $setting['contact_address'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="contact_map_url">URL Peta Lokasi</label>
                <input type="url" id="contact_map_url" name="contact_map_url" class="form-control" value="<?= old('contact_map_url', $setting['contact_map_url'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="instagram_profile_url">URL Profil Instagram</label>
                <input type="url" id="instagram_profile_url" name="instagram_profile_url" class="form-control" value="<?= old('instagram_profile_url', $setting['instagram_profile_url'] ?? ''); ?>" placeholder="https://www.instagram.com/pu_prasaranastrategis_riau/">
                <small class="form-text text-muted">Dipakai untuk tombol follow di homepage.</small>
            </div>

            <div class="form-group">
                <label for="default_event_image_file">Default Gambar Acara</label>
                <input type="file" id="default_event_image_file" name="default_event_image_file" class="form-control" accept="image/*">
                <?php if (! empty($setting['default_event_image'] ?? '')): ?>
                    <img src="<?= esc($setting['default_event_image']); ?>" alt="Default gambar acara" class="setting-preview">
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="default_article_image_file">Default Gambar Berita/Artikel</label>
                <input type="file" id="default_article_image_file" name="default_article_image_file" class="form-control" accept="image/*">
                <?php if (! empty($setting['default_article_image'] ?? '')): ?>
                    <img src="<?= esc($setting['default_article_image']); ?>" alt="Default gambar berita" class="setting-preview">
                <?php endif; ?>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Simpan Pengaturan Home</button>
        </div>
    </form>
</div>

<div class="card card-outline card-secondary mb-4">
    <div class="card-header">
        <h3 class="card-title">Tambah Slide</h3>
    </div>
    <form action="<?= site_url('/admin/slide/tambah'); ?>" method="post" enctype="multipart/form-data">
        <div class="card-body">
            <?= csrf_field(); ?>
            <div class="form-group">
                <label for="slide_title">Judul Slide</label>
                <input type="text" id="slide_title" name="title" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="slide_image">Gambar Slide</label>
                <input type="file" id="slide_image" name="slide_image" class="form-control" accept="image/*" required>
            </div>

            <div class="form-group">
                <label for="slide_order">Urutan</label>
                <input type="number" id="slide_order" name="sort_order" class="form-control" value="1" required>
            </div>

            <div class="form-check">
                <input type="checkbox" id="slide_active" name="is_active" value="1" class="form-check-input" checked>
                <label class="form-check-label" for="slide_active">Aktifkan slide</label>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-success">Tambah Slide</button>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Slide Home</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-striped table-hover mb-0">
            <thead>
            <tr>
                <th>Judul</th>
                <th>Gambar</th>
                <th>Urutan</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($slides as $slide): ?>
                <tr>
                    <td><?= esc($slide['title']); ?></td>
                    <td>
                        <?php if (! empty($slide['image_url'])): ?>
                            <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#imageViewModal" onclick="showImage('<?= esc($slide['image_url']); ?>', '<?= esc($slide['title']); ?>')" title="Lihat Gambar">
                                <i class="fas fa-image"></i>
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Belum ada gambar">
                                <i class="fas fa-image"></i>
                            </button>
                        <?php endif; ?>
                    </td>
                    <td><?= esc((string) $slide['sort_order']); ?></td>
                    <td>
                        <?php if ($slide['is_active']): ?>
                            <span class="badge badge-success">Aktif</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">Nonaktif</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#slideEditModal<?= esc((string) $slide['id']); ?>">
                            <i class="fas fa-pen"></i>
                        </button>
                        <form
                            class="inline-form"
                            action="<?= site_url('/admin/slide/' . $slide['id'] . '/hapus'); ?>"
                            method="post"
                            data-confirm-title="Hapus Slide"
                            data-confirm-text="Hapus slide ini?"
                            data-confirm-button="Ya, hapus"
                        >
                            <?= csrf_field(); ?>
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>

            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php foreach ($slides as $slide): ?>
    <div class="modal fade" id="slideEditModal<?= esc((string) $slide['id']); ?>" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form action="<?= site_url('/admin/slide/' . $slide['id'] . '/ubah'); ?>" method="post" enctype="multipart/form-data">
                    <?= csrf_field(); ?>
                    <div class="modal-header">
                        <h5 class="modal-title">Ubah Slide</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Judul</label>
                            <input type="text" name="title" class="form-control" value="<?= esc($slide['title']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Ganti Gambar (opsional)</label>
                            <input type="file" name="slide_image" class="form-control" accept="image/*">
                        </div>
                        <?php if (! empty($slide['image_url'])): ?>
                            <img src="<?= esc($slide['image_url']); ?>" alt="Preview slide" class="setting-preview">
                        <?php endif; ?>
                        <div class="form-group mt-2">
                            <label>Urutan</label>
                            <input type="number" name="sort_order" class="form-control" value="<?= esc((string) $slide['sort_order']); ?>" required>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" id="slide_active_<?= esc((string) $slide['id']); ?>" name="is_active" value="1" class="form-check-input" <?= $slide['is_active'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="slide_active_<?= esc((string) $slide['id']); ?>">Aktif</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- Modal untuk Lihat Gambar Slide -->
<div class="modal fade" id="imageViewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title" id="imageTitle">Lihat Gambar</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="Gambar Slide" style="max-width: 100%; height: auto;">
            </div>
        </div>
    </div>
</div>

<script>
    function showImage(imageSrc, imageTitle) {
        document.getElementById('modalImage').src = imageSrc;
        document.getElementById('imageTitle').textContent = 'Gambar: ' + imageTitle;
    }
</script>

<?= $this->endSection(); ?>
