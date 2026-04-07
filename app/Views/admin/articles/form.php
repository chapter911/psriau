<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title"><?= esc($pageTitle ?? 'Form Berita Instagram'); ?></h3>
    </div>
    <form action="<?= site_url($actionUrl); ?>" method="post">
        <div class="card-body">
            <?= csrf_field(); ?>
            <div class="form-group">
                <label for="instagram_embed">Embed Instagram</label>
                <textarea id="instagram_embed" name="instagram_embed" rows="5" class="form-control" required><?= old('instagram_embed', $instagramEmbed ?? ''); ?></textarea>
                <small id="instagram-embed-help" class="form-text text-muted">Tempelkan URL post Instagram (https://www.instagram.com/p/.../) atau kode embed resmi Instagram.</small>
            </div>

            <div class="form-group mb-0">
                <label>Preview Embed</label>
                <div id="instagram-embed-preview-empty" class="alert alert-light border mb-2">Preview akan tampil otomatis setelah URL/embed Instagram valid dimasukkan.</div>
                <div id="instagram-embed-preview" style="display:none;">
                    <blockquote id="instagram-embed-blockquote" class="instagram-media" data-instgrm-version="14" style="background:#fff;border:0;border-radius:12px;box-shadow:0 1px 10px rgba(0,0,0,.08);margin:0;min-width:280px;padding:0;width:100%;max-width:540px;"></blockquote>
                </div>
            </div>

            <div class="form-check">
                <input type="checkbox" id="is_published" name="is_published" value="1" class="form-check-input" <?= old('is_published', $article['is_published'] ?? 1) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="is_published">Publikasikan post</label>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" id="submit-instagram-embed" class="btn btn-primary">Simpan</button>
            <a href="<?= site_url('/admin/berita'); ?>" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>

<script>
(() => {
    const form = document.querySelector('form[action="<?= site_url($actionUrl); ?>"]');
    const input = document.getElementById('instagram_embed');
    const helpText = document.getElementById('instagram-embed-help');
    const submitButton = document.getElementById('submit-instagram-embed');
    const preview = document.getElementById('instagram-embed-preview');
    const previewEmpty = document.getElementById('instagram-embed-preview-empty');
    const embedBlockquote = document.getElementById('instagram-embed-blockquote');
    let isValid = false;

    const extractInstagramUrl = (text) => {
        const raw = (text || '').trim();
        if (!raw) return '';

        const matches = raw.match(/https?:\/\/[^\s"'<>]+/gi) || [raw];
        for (const candidate of matches) {
            try {
                const parsed = new URL(candidate.trim());
                const host = parsed.hostname.toLowerCase();
                if (host !== 'instagram.com' && host !== 'www.instagram.com') {
                    continue;
                }

                const path = parsed.pathname.replace(/\/$/, '');
                if (!/^\/(p|reel|tv)\/[A-Za-z0-9_-]+$/.test(path)) {
                    continue;
                }

                return `https://www.instagram.com${path}/`;
            } catch (e) {
                // Ignore invalid candidate and continue trying.
            }
        }

        return '';
    };

    const renderPreview = () => {
        const normalizedUrl = extractInstagramUrl(input.value);
        if (!normalizedUrl) {
            isValid = false;
            preview.style.display = 'none';
            previewEmpty.style.display = 'block';
            embedBlockquote.removeAttribute('data-instgrm-permalink');
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
            helpText.classList.remove('text-success');
            helpText.classList.add('text-danger');
            helpText.textContent = 'Format belum valid. Gunakan URL post Instagram atau kode embed resmi.';
            if (submitButton) {
                submitButton.disabled = true;
            }
            return;
        }

        isValid = true;
        embedBlockquote.setAttribute('data-instgrm-permalink', normalizedUrl);
        preview.style.display = 'block';
        previewEmpty.style.display = 'none';
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        helpText.classList.remove('text-danger');
        helpText.classList.add('text-success');
        helpText.textContent = 'Format valid. Preview embed siap ditampilkan.';
        if (submitButton) {
            submitButton.disabled = false;
        }

        if (window.instgrm && window.instgrm.Embeds) {
            window.instgrm.Embeds.process();
        }
    };

    form?.addEventListener('submit', (event) => {
        renderPreview();
        if (!isValid) {
            event.preventDefault();
            input.focus();
        }
    });

    input.addEventListener('input', renderPreview);
    renderPreview();
})();
</script>

<script async defer src="https://www.instagram.com/embed.js"></script>
<?= $this->endSection(); ?>
