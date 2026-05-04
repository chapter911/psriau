<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="card card-primary mb-4">
    <div class="card-header">
        <h3 class="card-title">Setting Tampilan Aplikasi</h3>
    </div>
    <form action="<?= site_url('/admin/pengaturan/application'); ?>" method="post" enctype="multipart/form-data">
        <div class="card-body">
            <?= csrf_field(); ?>


            <div class="form-group mb-4">
                <label for="app_name">Nama Aplikasi</label>
                <input
                    type="text"
                    id="app_name"
                    name="app_name"
                    class="form-control"
                    value="<?= old('app_name', $setting['app_name'] ?? 'PLN EPM-Digi'); ?>"
                    required
                >
            </div>

            <div class="form-group mb-4">
                <label for="primary_color">Warna Primary Aplikasi</label>
                <input
                    type="color"
                    id="primary_color"
                    name="primary_color"
                    class="form-control"
                    value="<?= old('primary_color', $setting['primary_color'] ?? '#0A66C2'); ?>"
                    style="max-width: 92px;"
                    required
                >
                <small class="text-muted">Warna ini digunakan untuk elemen utama aplikasi.</small>
            </div>

            <div class="card card-light mb-4">
                <div class="card-header py-2"><h5 class="card-title mb-0" style="font-size:1rem;">Pengaturan Warna Sidebar</h5></div>
                <div class="card-body pb-2">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="sidebar_bg_color">Background Sidebar</label>
                            <input type="color" id="sidebar_bg_color" name="sidebar_bg_color" class="form-control" value="<?= old('sidebar_bg_color', $setting['sidebar_bg_color'] ?? '#2F3A45'); ?>" style="max-width: 92px;" required>
                        </div>
                        <div class="col-md-3">
                            <label for="sidebar_text_color">Teks Sidebar</label>
                            <input type="color" id="sidebar_text_color" name="sidebar_text_color" class="form-control" value="<?= old('sidebar_text_color', $setting['sidebar_text_color'] ?? '#C2CBD5'); ?>" style="max-width: 92px;" required>
                        </div>
                        <div class="col-md-3">
                            <label for="sidebar_active_bg_color">Active Sidebar</label>
                            <input type="color" id="sidebar_active_bg_color" name="sidebar_active_bg_color" class="form-control" value="<?= old('sidebar_active_bg_color', $setting['sidebar_active_bg_color'] ?? '#0A66C2'); ?>" style="max-width: 92px;" required>
                        </div>
                        <div class="col-md-3">
                            <label for="sidebar_active_text_color">Teks Active Sidebar</label>
                            <input type="color" id="sidebar_active_text_color" name="sidebar_active_text_color" class="form-control" value="<?= old('sidebar_active_text_color', $setting['sidebar_active_text_color'] ?? '#FFFFFF'); ?>" style="max-width: 92px;" required>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">Atur kontras sidebar supaya menu dan submenu lebih jelas terlihat.</small>
                    <div class="mt-3">
                        <button
                            type="submit"
                            class="btn btn-outline-secondary btn-sm"
                            formaction="<?= site_url('/admin/pengaturan/application/reset-sidebar'); ?>"
                            formmethod="post"
                            formnovalidate
                            data-confirm-title="Reset Sidebar"
                            data-confirm-text="Reset warna sidebar ke default AdminLTE?"
                            data-confirm-button="Ya, reset"
                        >
                            <i class="fas fa-rotate-left mr-1"></i> Reset Pengaturan Sidebar (Default AdminLTE)
                        </button>
                    </div>
                </div>
            </div>

            <div class="card card-light mb-4">
                <div class="card-header py-2"><h5 class="card-title mb-0" style="font-size:1rem;">Logo & Background Login</h5></div>
                <div class="card-body pb-2">
                    <div class="row align-items-center mb-3">
                        <div class="col-md-3 text-center">
                            <?php if (! empty($setting['app_logo_url'] ?? '')): ?>
                                <img src="<?= esc($setting['app_logo_url']); ?>" alt="Preview logo aplikasi" class="setting-preview mb-2" style="max-height: 80px;">
                            <?php else: ?>
                                <div class="bg-light border rounded mb-2" style="height:80px;width:80px;display:inline-block;"></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-9">
                            <label for="app_logo_file">Logo Aplikasi</label>
                            <input type="file" id="app_logo_file" name="app_logo_file" class="form-control mb-1" accept="image/*">
                            <small class="text-muted">Kosongkan jika tidak ingin mengganti logo. Maksimal 2 MB.</small>
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-3 text-center">
                            <?php if (! empty($setting['login_background_url'] ?? '')): ?>
                                <img src="<?= esc($setting['login_background_url']); ?>" alt="Preview background login" class="setting-preview mb-2" style="max-height: 80px;">
                            <?php else: ?>
                                <div class="bg-light border rounded mb-2" style="height:80px;width:120px;display:inline-block;"></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-9">
                            <label for="login_bg_file">Background Login</label>
                            <input type="file" id="login_bg_file" name="login_bg_file" class="form-control mb-1" accept="image/*">
                            <small class="text-muted">Kosongkan jika tidak ingin mengganti background login. Maksimal 4 MB.</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="auto_logout_minutes">Timer Logout Otomatis (menit)</label>
                <input
                    type="number"
                    id="auto_logout_minutes"
                    name="auto_logout_minutes"
                    class="form-control"
                    value="<?= old('auto_logout_minutes', (int) ($setting['auto_logout_minutes'] ?? 60)); ?>"
                    min="1"
                    max="1440"
                    required
                >
                <small class="text-muted">Pengguna akan otomatis logout jika tidak ada aktivitas sesuai durasi ini.</small>
            </div>

            <div class="form-group">
                <label for="preloader_duration_ms">Durasi Preloader Halaman (ms)</label>
                <input
                    type="number"
                    id="preloader_duration_ms"
                    name="preloader_duration_ms"
                    class="form-control"
                    value="<?= old('preloader_duration_ms', (int) ($setting['preloader_duration_ms'] ?? 500)); ?>"
                    min="0"
                    max="10000"
                    step="1"
                    required
                >
                <small class="text-muted">Digunakan untuk menentukan berapa lama preloader tampil sebelum halaman disembunyikan. Contoh: 500 ms.</small>
            </div>
        </div>

        <div class="card-footer d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <button type="submit" class="btn btn-primary mr-2">
                    <i class="fas fa-floppy-disk mr-1"></i> Simpan Setting
                </button>
                <button type="button" class="btn btn-outline-info" data-toggle="modal" data-target="#testEmailModal">
                    <i class="fas fa-paper-plane mr-1"></i> Uji Coba Kirim Email
                </button>
            </div>
            <span class="text-muted small">Pastikan perubahan tampilan sesuai identitas aplikasi.</span>
        </div>
    </form>
</div>

<div class="modal fade" id="testEmailModal" tabindex="-1" role="dialog" aria-labelledby="testEmailModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="testEmailModalLabel">Uji Coba Kirim Email</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="testEmailForm" method="post" action="<?= site_url('/admin/pengaturan/application/test-email'); ?>">
                <div class="modal-body">
                    <?= csrf_field(); ?>

                    <div class="alert d-none" id="testEmailResult" role="alert"></div>

                    <div class="form-group">
                        <label for="test_email_to">Email Tujuan</label>
                        <input type="email" class="form-control" id="test_email_to" name="to_email" placeholder="contoh@domain.com" required>
                    </div>

                    <div class="form-group">
                        <label for="test_email_subject">Subject</label>
                        <input type="text" class="form-control" id="test_email_subject" name="subject" value="Uji Coba Email - SATKER PPS Riau">
                    </div>

                    <div class="form-group mb-0">
                        <label for="test_email_message">Isi Pesan</label>
                        <textarea class="form-control" id="test_email_message" name="message" rows="4">Ini adalah email uji coba dari halaman Pengaturan Aplikasi.</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-info" id="testEmailSubmitBtn">
                        <i class="fas fa-paper-plane mr-1"></i> Kirim Uji Coba
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (() => {
        const form = document.getElementById('testEmailForm');
        if (!form) {
            return;
        }

        const submitBtn = document.getElementById('testEmailSubmitBtn');
        const resultBox = document.getElementById('testEmailResult');

        const showResult = (ok, message, detail = '') => {
            if (!resultBox) {
                return;
            }

            resultBox.classList.remove('d-none', 'alert-success', 'alert-danger');
            resultBox.classList.add(ok ? 'alert-success' : 'alert-danger');

            const detailHtml = detail ? `<hr class="my-2"><pre class="mb-0" style="white-space:pre-wrap;max-height:220px;overflow:auto;">${detail.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</pre>` : '';
            resultBox.innerHTML = `<div>${message}</div>${detailHtml}`;
        };

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (!submitBtn) {
                return;
            }

            submitBtn.disabled = true;
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Mengirim...';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new FormData(form),
                });

                const payload = await response.json();
                const isOk = response.ok && payload && payload.status === 'ok';
                const message = (payload && payload.message) ? payload.message : 'Terjadi kesalahan saat memproses permintaan.';
                const detail = (payload && payload.error_detail) ? payload.error_detail : '';

                showResult(isOk, message, detail);

                if (payload && payload.csrf_hash) {
                    const tokenField = form.querySelector('input[name="<?= csrf_token(); ?>"]');
                    if (tokenField) {
                        tokenField.value = payload.csrf_hash;
                    }
                }
            } catch (error) {
                showResult(false, 'Request gagal dijalankan.', error && error.message ? error.message : 'Unknown error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    })();
</script>
<?= $this->endSection(); ?>
