<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php
    $role = strtolower(trim((string) session()->get('role')));
    $isSuperAdmin = in_array($role, ['super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);
    $isDevEnv = defined('ENVIRONMENT') && ENVIRONMENT === 'development';
    $isProdEnv = defined('ENVIRONMENT') && ENVIRONMENT === 'production';
    $ciVersion = defined('\CodeIgniter\CodeIgniter::CI_VERSION') ? \CodeIgniter\CodeIgniter::CI_VERSION : '4.x';
    $officialName = trim((string) ($globalSetting['official_name'] ?? 'Satker PPS Riau'));
    $smtpFrom = (string) env('EMAIL_FROM', 'no-reply@satkerpps-riau.online');
    $smtpHost = (string) env('EMAIL_SMTP_HOST', '-');
    $smtpPort = (string) env('EMAIL_SMTP_PORT', '-');
    $smtpCrypto = (string) env('EMAIL_SMTP_CRYPTO', '-');
    $smtpUser = (string) env('EMAIL_SMTP_USER', '-');
    $gdriveFolderId = (string) env('GOOGLE_DRIVE_UPLOAD_FOLDER_ID', '');
    $errCount = (int) ($errorLogCount ?? 0);
?>

<style>
    .settings-shell {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }

    .settings-tabs-bar {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 12px 16px 10px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        overflow-x: auto;
        white-space: nowrap;
        scrollbar-width: thin;
        scrollbar-color: #94a3b8 #e2e8f0;
    }

    .settings-tabs-bar::-webkit-scrollbar {
        height: 6px;
    }

    .settings-tabs-bar::-webkit-scrollbar-track {
        background: #e2e8f0;
        border-radius: 999px;
    }

    .settings-tabs-bar::-webkit-scrollbar-thumb {
        background: #94a3b8;
        border-radius: 999px;
    }

    .settings-tab-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 0.86rem;
        font-weight: 600;
        color: #475569;
        background: transparent;
        border: 1px solid transparent;
        cursor: pointer;
        transition: all 0.15s ease;
        text-decoration: none !important;
        flex-shrink: 0;
    }

    .settings-tab-pill:hover {
        color: #0f172a;
        background: #eef2f7;
    }

    .settings-tab-pill.active {
        background: var(--app-primary, #0A66C2);
        color: #fff !important;
        box-shadow: 0 2px 6px rgba(10, 102, 194, 0.25);
    }

    .settings-section-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #475569;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .settings-subcard {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #fff;
        margin-bottom: 16px;
    }

    .settings-subcard-header {
        padding: 11px 16px;
        border-bottom: 1px solid #eef2f7;
        font-weight: 700;
        font-size: 0.93rem;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 8px;
        background: #fcfdff;
        border-radius: 10px 10px 0 0;
    }

    .settings-subcard-body {
        padding: 16px;
    }

    .settings-toggle-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
    }

    .settings-switch {
        position: relative;
        display: inline-block;
        width: 46px;
        height: 25px;
        flex-shrink: 0;
        margin: 0;
    }

    .settings-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .settings-switch-slider {
        position: absolute;
        cursor: pointer;
        inset: 0;
        background-color: #cbd5e1;
        transition: 0.2s;
        border-radius: 999px;
    }

    .settings-switch-slider:before {
        position: absolute;
        content: "";
        height: 19px;
        width: 19px;
        left: 3px;
        bottom: 3px;
        background-color: #fff;
        transition: 0.2s;
        border-radius: 50%;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    }

    .settings-switch input:checked + .settings-switch-slider {
        background-color: var(--app-primary, #0A66C2);
    }

    .settings-switch input:checked + .settings-switch-slider:before {
        transform: translateX(21px);
    }

    .env-summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.87rem;
    }

    .env-summary-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .settings-footer-bar {
        padding: 14px 20px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }
</style>

<div class="settings-shell mb-4">
    <div class="settings-tabs-bar" id="appSettingsTabs" role="tablist">
        <a class="settings-tab-pill active" data-toggle="pill" href="#tab-general" role="tab">
            <i class="fas fa-sliders-h"></i> General Settings
        </a>
        <a class="settings-tab-pill" data-toggle="pill" href="#tab-appearance" role="tab">
            <i class="fas fa-palette"></i> Appearance &amp; Branding
        </a>
        <a class="settings-tab-pill" data-toggle="pill" href="#tab-email" role="tab">
            <i class="fas fa-envelope"></i> Email Settings (SMTP)
        </a>
        <a class="settings-tab-pill" data-toggle="pill" href="#tab-storage" role="tab">
            <i class="fas fa-hdd"></i> File &amp; Storage
        </a>
        <a class="settings-tab-pill" data-toggle="pill" href="#tab-security" role="tab">
            <i class="fas fa-shield-alt"></i> Security &amp; Session
        </a>
        <a class="settings-tab-pill" data-toggle="pill" href="#tab-system" role="tab">
            <i class="fas fa-microchip"></i> System Information &amp; Diagnostics
        </a>
        <a class="settings-tab-pill" data-toggle="pill" href="#tab-git" role="tab">
            <i class="fas fa-code-branch"></i> Git &amp; Version Control
        </a>
        <a class="settings-tab-pill" data-toggle="pill" href="#tab-database" role="tab">
            <i class="fas fa-database"></i> Database &amp; Merge Sync
        </a>
        <a class="settings-tab-pill" data-toggle="pill" href="#tab-logs" role="tab">
            <i class="fas fa-bug"></i> System &amp; Error Logs
            <?php if ($errCount > 0): ?>
                <span class="badge badge-danger badge-pill ml-1"><?= $errCount; ?></span>
            <?php endif; ?>
        </a>
    </div>

    <form action="<?= site_url('/admin/pengaturan/application'); ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field(); ?>
        <div class="p-3 p-md-4">
            <div class="tab-content" id="appSettingsTabContent">

                <!-- 1. GENERAL SETTINGS -->
                <div class="tab-pane fade show active" id="tab-general" role="tabpanel">
                    <div class="settings-section-title">
                        <i class="fas fa-sliders-h text-primary"></i> General Settings
                    </div>
                    <div class="row">
                        <div class="col-lg-7">
                            <div class="settings-subcard">
                                <div class="settings-subcard-header">
                                    <i class="fas fa-building text-primary"></i> Application Identity &amp; Information
                                </div>
                                <div class="settings-subcard-body">
                                    <div class="form-group">
                                        <label for="app_name" class="font-weight-bold small">Application Name <span class="text-danger">*</span></label>
                                        <input
                                            type="text"
                                            id="app_name"
                                            name="app_name"
                                            class="form-control"
                                            value="<?= old('app_name', $setting['app_name'] ?? 'SATKER PPS Riau'); ?>"
                                            required
                                        >
                                        <small class="text-muted">Nama aplikasi yang ditampilkan di header dan tab browser.</small>
                                    </div>

                                    <div class="form-group">
                                        <label class="font-weight-bold small">Instansi / Satuan Kerja</label>
                                        <input type="text" class="form-control bg-light" value="<?= esc($officialName); ?>" readonly>
                                        <small class="text-muted">Diambil dari pengaturan identitas instansi global.</small>
                                    </div>

                                    <div class="form-row">
                                        <div class="col-md-6 form-group mb-md-0">
                                            <label class="font-weight-bold small">System Contact Email</label>
                                            <input type="text" class="form-control bg-light" value="<?= esc($smtpFrom); ?>" readonly>
                                        </div>
                                        <div class="col-md-6 form-group mb-0">
                                            <label class="font-weight-bold small">Default Timezone</label>
                                            <input type="text" class="form-control bg-light" value="Asia/Jakarta (WIB - UTC+7)" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <div class="settings-subcard">
                                <div class="settings-subcard-header">
                                    <i class="fas fa-tools text-primary"></i> Maintenance Mode
                                </div>
                                <div class="settings-subcard-body">
                                    <div class="settings-toggle-box">
                                        <div>
                                            <div class="font-weight-bold small text-dark">Aktifkan Mode Maintenance</div>
                                            <div class="small text-muted">Halaman publik menampilkan halaman maintenance sementara admin tetap dapat masuk.</div>
                                        </div>
                                        <label class="settings-switch" for="maintenance_mode">
                                            <input type="hidden" name="maintenance_mode" value="0">
                                            <input
                                                type="checkbox"
                                                id="maintenance_mode"
                                                name="maintenance_mode"
                                                value="1"
                                                <?= (int) old('maintenance_mode', $setting['maintenance_mode'] ?? 0) === 1 ? 'checked' : ''; ?>
                                            >
                                            <span class="settings-switch-slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="settings-subcard">
                                <div class="settings-subcard-header">
                                    <i class="fas fa-info-circle text-primary"></i> Server Environment Summary
                                </div>
                                <div class="settings-subcard-body">
                                    <div class="env-summary-row">
                                        <span class="text-muted">Environment:</span>
                                        <span class="badge <?= $isDevEnv ? 'badge-warning text-dark' : 'badge-success'; ?> px-2 py-1">
                                            <?= esc(defined('ENVIRONMENT') ? ENVIRONMENT : 'unknown'); ?>
                                        </span>
                                    </div>
                                    <div class="env-summary-row">
                                        <span class="text-muted">Framework Engine:</span>
                                        <span class="font-weight-bold text-dark">CodeIgniter <?= esc($ciVersion); ?></span>
                                    </div>
                                    <div class="env-summary-row">
                                        <span class="text-muted">PHP Runtime:</span>
                                        <span class="font-weight-bold text-dark"><?= esc(PHP_VERSION); ?></span>
                                    </div>
                                    <div class="env-summary-row">
                                        <span class="text-muted">Server Time:</span>
                                        <span class="font-weight-bold text-primary"><?= esc(date('H:i:s')); ?> WIB</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. APPEARANCE & BRANDING -->
                <div class="tab-pane fade" id="tab-appearance" role="tabpanel">
                    <div class="settings-section-title">
                        <i class="fas fa-palette text-primary"></i> Appearance &amp; Branding
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="settings-subcard">
                                <div class="settings-subcard-header">
                                    <i class="fas fa-fill-drip text-primary"></i> Warna Utama &amp; Sidebar
                                </div>
                                <div class="settings-subcard-body">
                                    <div class="form-row mb-3">
                                        <div class="col-sm-6 form-group mb-sm-0">
                                            <label for="primary_color" class="font-weight-bold small">Warna Primary Aplikasi</label>
                                            <input type="color" id="primary_color" name="primary_color" class="form-control" value="<?= old('primary_color', $setting['primary_color'] ?? '#0A66C2'); ?>" style="max-width: 96px;" required>
                                        </div>
                                        <div class="col-sm-6 form-group mb-0">
                                            <label for="preloader_duration_ms" class="font-weight-bold small">Durasi Preloader (ms)</label>
                                            <input type="number" id="preloader_duration_ms" name="preloader_duration_ms" class="form-control" value="<?= old('preloader_duration_ms', (int) ($setting['preloader_duration_ms'] ?? 500)); ?>" min="0" max="10000" step="1" required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-6 col-sm-3 mb-2">
                                            <label for="sidebar_bg_color" class="small font-weight-bold">BG Sidebar</label>
                                            <input type="color" id="sidebar_bg_color" name="sidebar_bg_color" class="form-control" value="<?= old('sidebar_bg_color', $setting['sidebar_bg_color'] ?? '#2F3A45'); ?>" style="max-width: 88px;" required>
                                        </div>
                                        <div class="col-6 col-sm-3 mb-2">
                                            <label for="sidebar_text_color" class="small font-weight-bold">Teks Sidebar</label>
                                            <input type="color" id="sidebar_text_color" name="sidebar_text_color" class="form-control" value="<?= old('sidebar_text_color', $setting['sidebar_text_color'] ?? '#C2CBD5'); ?>" style="max-width: 88px;" required>
                                        </div>
                                        <div class="col-6 col-sm-3 mb-2">
                                            <label for="sidebar_active_bg_color" class="small font-weight-bold">Active BG</label>
                                            <input type="color" id="sidebar_active_bg_color" name="sidebar_active_bg_color" class="form-control" value="<?= old('sidebar_active_bg_color', $setting['sidebar_active_bg_color'] ?? '#0A66C2'); ?>" style="max-width: 88px;" required>
                                        </div>
                                        <div class="col-6 col-sm-3 mb-2">
                                            <label for="sidebar_active_text_color" class="small font-weight-bold">Active Teks</label>
                                            <input type="color" id="sidebar_active_text_color" name="sidebar_active_text_color" class="form-control" value="<?= old('sidebar_active_text_color', $setting['sidebar_active_text_color'] ?? '#FFFFFF'); ?>" style="max-width: 88px;" required>
                                        </div>
                                    </div>

                                    <div class="mt-2">
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
                                            <i class="fas fa-rotate-left mr-1"></i> Reset Sidebar ke Default AdminLTE
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="settings-subcard">
                                <div class="settings-subcard-header">
                                    <i class="fas fa-image text-primary"></i> Logo &amp; Background Login
                                </div>
                                <div class="settings-subcard-body">
                                    <div class="row align-items-center mb-3">
                                        <div class="col-sm-3 text-center">
                                            <?php if (! empty($setting['app_logo_url'] ?? '')): ?>
                                                <img src="<?= esc($setting['app_logo_url']); ?>" alt="Preview logo aplikasi" class="setting-preview mb-2" style="max-height: 72px;">
                                            <?php else: ?>
                                                <div class="bg-light border rounded mb-2" style="height:72px;width:72px;display:inline-block;"></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-sm-9">
                                            <label for="app_logo_file" class="small font-weight-bold">Logo Aplikasi</label>
                                            <input type="file" id="app_logo_file" name="app_logo_file" class="form-control form-control-sm mb-1" accept="image/*">
                                            <small class="text-muted">Kosongkan jika tidak ingin mengganti logo. Maksimal 2 MB.</small>
                                        </div>
                                    </div>
                                    <div class="row align-items-center">
                                        <div class="col-sm-3 text-center">
                                            <?php if (! empty($setting['login_background_url'] ?? '')): ?>
                                                <img src="<?= esc($setting['login_background_url']); ?>" alt="Preview background login" class="setting-preview mb-2" style="max-height: 72px;">
                                            <?php else: ?>
                                                <div class="bg-light border rounded mb-2" style="height:72px;width:100px;display:inline-block;"></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-sm-9">
                                            <label for="login_bg_file" class="small font-weight-bold">Background Login</label>
                                            <input type="file" id="login_bg_file" name="login_bg_file" class="form-control form-control-sm mb-1" accept="image/*">
                                            <small class="text-muted">Kosongkan jika tidak ingin mengganti background login. Maksimal 4 MB.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. EMAIL SETTINGS (SMTP) -->
                <div class="tab-pane fade" id="tab-email" role="tabpanel">
                    <div class="settings-section-title">
                        <i class="fas fa-envelope text-primary"></i> Email Settings (SMTP)
                    </div>
                    <div class="row">
                        <div class="col-lg-5">
                            <div class="settings-subcard">
                                <div class="settings-subcard-header">
                                    <i class="fas fa-server text-primary"></i> Konfigurasi SMTP Aktif (.env)
                                </div>
                                <div class="settings-subcard-body">
                                    <div class="env-summary-row">
                                        <span class="text-muted">SMTP Host:</span>
                                        <span class="font-weight-bold text-monospace small"><?= esc($smtpHost); ?></span>
                                    </div>
                                    <div class="env-summary-row">
                                        <span class="text-muted">Port / Crypto:</span>
                                        <span class="font-weight-bold"><?= esc($smtpPort . ' / ' . strtoupper($smtpCrypto)); ?></span>
                                    </div>
                                    <div class="env-summary-row">
                                        <span class="text-muted">SMTP User:</span>
                                        <span class="font-weight-bold text-monospace small"><?= esc($smtpUser); ?></span>
                                    </div>
                                    <div class="env-summary-row">
                                        <span class="text-muted">Sender Email:</span>
                                        <span class="font-weight-bold text-monospace small"><?= esc($smtpFrom); ?></span>
                                    </div>
                                    <small class="text-muted d-block mt-2">Konfigurasi kredensial SMTP dikelola melalui file <code>.env</code> demi keamanan.</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-7">
                            <div class="settings-subcard">
                                <div class="settings-subcard-header">
                                    <i class="fas fa-paper-plane text-primary"></i> Uji Coba Kirim Email
                                </div>
                                <div class="settings-subcard-body">
                                    <div class="alert d-none" id="testEmailResult" role="alert"></div>
                                    <div class="form-group">
                                        <label for="test_email_to" class="small font-weight-bold">Email Tujuan</label>
                                        <input type="email" class="form-control form-control-sm" id="test_email_to" placeholder="contoh@domain.com">
                                    </div>
                                    <div class="form-group">
                                        <label for="test_email_subject" class="small font-weight-bold">Subject</label>
                                        <input type="text" class="form-control form-control-sm" id="test_email_subject" value="Uji Coba Email - SATKER PPS Riau">
                                    </div>
                                    <div class="form-group">
                                        <label for="test_email_message" class="small font-weight-bold">Isi Pesan</label>
                                        <textarea class="form-control form-control-sm" id="test_email_message" rows="3">Ini adalah email uji coba dari halaman Pengaturan Aplikasi.</textarea>
                                    </div>
                                    <button type="button" class="btn btn-info btn-sm" id="testEmailSubmitBtn">
                                        <i class="fas fa-paper-plane mr-1"></i> Kirim Uji Coba Email
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. FILE & STORAGE -->
                <div class="tab-pane fade" id="tab-storage" role="tabpanel">
                    <div class="settings-section-title">
                        <i class="fas fa-hdd text-primary"></i> File &amp; Storage
                    </div>
                    <div class="row">
                        <div class="col-lg-7">
                            <div class="settings-subcard">
                                <div class="settings-subcard-header">
                                    <i class="fas fa-cloud-upload-alt text-primary"></i> Batas Upload &amp; Panduan SIMAK
                                </div>
                                <div class="settings-subcard-body">
                                    <div class="form-group">
                                        <label for="simak_max_upload_mb" class="font-weight-bold small">Maksimal Batas Ukuran Upload SIMAK (MB)</label>
                                        <input
                                            type="number"
                                            id="simak_max_upload_mb"
                                            name="simak_max_upload_mb"
                                            class="form-control"
                                            value="<?= old('simak_max_upload_mb', (int) ($setting['simak_max_upload_mb'] ?? 0)); ?>"
                                            min="0"
                                            max="1000"
                                            required
                                        >
                                        <small class="text-muted">Isi <code>0</code> untuk unlimited (tanpa batas).</small>
                                    </div>

                                    <div class="form-group mb-0">
                                        <label for="simak_upload_tutorial_url" class="font-weight-bold small">Link Tutorial Upload SIMAK (YouTube)</label>
                                        <input
                                            type="url"
                                            id="simak_upload_tutorial_url"
                                            name="simak_upload_tutorial_url"
                                            class="form-control"
                                            value="<?= old('simak_upload_tutorial_url', $setting['simak_upload_tutorial_url'] ?? 'https://www.youtube.com/watch?v=fHQhAJ-B3qE'); ?>"
                                            placeholder="https://www.youtube.com/watch?v=..."
                                        >
                                        <small class="text-muted">Dipakai pada halaman share SIMAK sebagai tombol tutorial upload.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <div class="settings-subcard">
                                <div class="settings-subcard-header">
                                    <i class="fab fa-google-drive text-primary"></i> Integrasi Penyimpanan Cloud
                                </div>
                                <div class="settings-subcard-body">
                                    <div class="env-summary-row">
                                        <span class="text-muted">Google Drive Folder ID:</span>
                                        <span class="font-weight-bold text-monospace small"><?= esc($gdriveFolderId !== '' ? $gdriveFolderId : 'Belum diatur'); ?></span>
                                    </div>
                                    <div class="env-summary-row">
                                        <span class="text-muted">PHP Upload Max Filesize:</span>
                                        <span class="font-weight-bold"><?= esc(ini_get('upload_max_filesize')); ?></span>
                                    </div>
                                    <div class="env-summary-row">
                                        <span class="text-muted">PHP Post Max Size:</span>
                                        <span class="font-weight-bold"><?= esc(ini_get('post_max_size')); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. SECURITY & SESSION -->
                <div class="tab-pane fade" id="tab-security" role="tabpanel">
                    <div class="settings-section-title">
                        <i class="fas fa-shield-alt text-primary"></i> Security &amp; Session
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="settings-subcard">
                                <div class="settings-subcard-header">
                                    <i class="fas fa-clock text-primary"></i> Session &amp; Auto Logout
                                </div>
                                <div class="settings-subcard-body">
                                    <div class="form-group mb-0">
                                        <label for="auto_logout_minutes" class="font-weight-bold small">Timer Logout Otomatis (menit)</label>
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
                                        <small class="text-muted">Pengguna akan otomatis logout jika tidak ada aktivitas selama durasi ini (1 – 1440 menit).</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="settings-subcard">
                                <div class="settings-subcard-header">
                                    <i class="fas fa-user-shield text-primary"></i> Informasi Keamanan Sesi
                                </div>
                                <div class="settings-subcard-body">
                                    <div class="env-summary-row">
                                        <span class="text-muted">CSRF Protection:</span>
                                        <span class="badge badge-success">Aktif</span>
                                    </div>
                                    <div class="env-summary-row">
                                        <span class="text-muted">Hak Akses Menu:</span>
                                        <span class="font-weight-bold">Berbasis Tabel <code>menu_akses</code></span>
                                    </div>
                                    <div class="env-summary-row">
                                        <span class="text-muted">Role Anda Saat Ini:</span>
                                        <span class="font-weight-bold"><?= esc((string) session()->get('role')); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 6. SYSTEM INFORMATION & DIAGNOSTICS -->
                <div class="tab-pane fade" id="tab-system" role="tabpanel">
                    <div class="settings-section-title">
                        <i class="fas fa-microchip text-primary"></i> System Information &amp; Diagnostics
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="settings-subcard">
                                <div class="settings-subcard-header">
                                    <i class="fas fa-server text-primary"></i> Runtime &amp; PHP Limits
                                </div>
                                <div class="settings-subcard-body">
                                    <div class="env-summary-row"><span class="text-muted">Environment:</span> <span class="badge <?= $isDevEnv ? 'badge-warning text-dark' : 'badge-success'; ?>"><?= esc(defined('ENVIRONMENT') ? ENVIRONMENT : '-'); ?></span></div>
                                    <div class="env-summary-row"><span class="text-muted">CodeIgniter:</span> <span class="font-weight-bold"><?= esc($ciVersion); ?></span></div>
                                    <div class="env-summary-row"><span class="text-muted">PHP Version:</span> <span class="font-weight-bold"><?= esc(PHP_VERSION); ?></span></div>
                                    <div class="env-summary-row"><span class="text-muted">Memory Limit:</span> <span class="font-weight-bold"><?= esc(ini_get('memory_limit')); ?></span></div>
                                    <div class="env-summary-row"><span class="text-muted">Max Execution Time:</span> <span class="font-weight-bold"><?= esc(ini_get('max_execution_time')); ?> detik</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="settings-subcard">
                                <div class="settings-subcard-header">
                                    <i class="fas fa-puzzle-piece text-primary"></i> Koneksi Database &amp; Ekstensi PHP
                                </div>
                                <div class="settings-subcard-body">
                                    <div class="env-summary-row"><span class="text-muted">DB Driver:</span> <span class="font-weight-bold"><?= esc($dbInfo['driver'] ?? '-'); ?></span></div>
                                    <div class="env-summary-row"><span class="text-muted">Database:</span> <span class="font-weight-bold text-monospace small"><?= esc($dbInfo['database'] ?? '-'); ?></span></div>
                                    <div class="env-summary-row"><span class="text-muted">Host / Port:</span> <span class="font-weight-bold text-monospace small"><?= esc(($dbInfo['hostname'] ?? '-') . ':' . ($dbInfo['port'] ?? 3306)); ?></span></div>
                                    <div class="env-summary-row">
                                        <span class="text-muted">Ekstensi Wajib:</span>
                                        <span>
                                            <?php foreach (['mysqli', 'curl', 'gd', 'zip', 'mbstring'] as $ext): ?>
                                                <span class="badge <?= extension_loaded($ext) ? 'badge-success' : 'badge-danger'; ?> mr-1"><?= esc($ext); ?></span>
                                            <?php endforeach; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 7. GIT & VERSION CONTROL -->
                <div class="tab-pane fade" id="tab-git" role="tabpanel">
                    <div class="settings-section-title">
                        <i class="fas fa-code-branch text-primary"></i> Git &amp; Version Control
                    </div>
                    <div class="settings-subcard">
                        <div class="settings-subcard-header">
                            <i class="fas fa-code-branch text-primary"></i> Status Repository &amp; Deployment
                        </div>
                        <div class="settings-subcard-body">
                            <div class="env-summary-row"><span class="text-muted">Branch Aktif:</span> <span class="badge badge-info"><?= esc($gitBranch ?? '-'); ?></span></div>
                            <div class="env-summary-row"><span class="text-muted">Commit Terakhir:</span> <span class="font-weight-bold text-monospace small"><?= esc($gitCommit ?? '-'); ?></span></div>
                            <div class="mt-3">
                                <?php if ($isProdEnv && $isSuperAdmin): ?>
                                    <button type="button" class="btn btn-primary btn-sm" onclick="document.querySelector('form[action*=\'git-pull\']')?.submit();">
                                        <i class="fas fa-code-branch mr-1"></i> Jalankan Git Pull Sekarang
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted small"><i class="fas fa-info-circle mr-1"></i> Tombol eksekusi Git Pull hanya aktif di environment <strong>production</strong> untuk Super Administrator.</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 8. DATABASE & MERGE SYNC -->
                <div class="tab-pane fade" id="tab-database" role="tabpanel">
                    <div class="settings-section-title">
                        <i class="fas fa-database text-primary"></i> Database &amp; Merge Sync
                    </div>
                    <div class="settings-subcard">
                        <div class="settings-subcard-header">
                            <i class="fas fa-sync-alt text-primary"></i> Utilitas Sinkronisasi &amp; Ekstrak Database
                        </div>
                        <div class="settings-subcard-body">
                            <?php if ($isDevEnv && $isSuperAdmin): ?>
                                <p class="small text-muted mb-3">Anda berada di mode <strong>Development (Lokal)</strong>. Anda dapat menarik database atau modul tertentu secara <em>read-only</em> dari server Production menggunakan Token Sync.</p>
                                <button type="button" class="btn btn-warning text-dark font-weight-bold btn-sm" data-toggle="modal" data-target="#syncProdDbModalNavbar">
                                    <i class="fas fa-cloud-download-alt mr-1"></i> Buka Ambil DB Production
                                </button>
                            <?php elseif ($isProdEnv && $isSuperAdmin): ?>
                                <p class="small text-muted mb-3">Anda berada di mode <strong>Production</strong>. Gunakan utilitas berikut untuk merge migrasi atau ekstrak backup database.</p>
                                <button type="button" class="btn btn-outline-primary btn-sm mr-2" onclick="document.querySelector('form[action*=\'merge-database\']')?.submit();">
                                    <i class="fas fa-database mr-1"></i> Merge Database
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#extractDatabaseModalNavbar">
                                    <i class="fas fa-file-export mr-1"></i> Ekstrak Database
                                </button>
                            <?php else: ?>
                                <span class="text-muted small">Utilitas database hanya tersedia untuk Super Administrator.</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- 9. SYSTEM & ERROR LOGS -->
                <div class="tab-pane fade" id="tab-logs" role="tabpanel">
                    <div class="settings-section-title">
                        <i class="fas fa-bug text-primary"></i> System &amp; Error Logs
                    </div>
                    <div class="settings-subcard">
                        <div class="settings-subcard-header">
                            <i class="fas fa-file-alt text-primary"></i> Log Aplikasi (<code>writable/logs</code>)
                        </div>
                        <div class="settings-subcard-body">
                            <div class="env-summary-row">
                                <span class="text-muted">Total File Log Tersimpan:</span>
                                <span class="badge <?= $errCount > 0 ? 'badge-danger' : 'badge-success'; ?>"><?= $errCount; ?> file</span>
                            </div>
                            <div class="mt-3">
                                <?php if ($isProdEnv && $isSuperAdmin): ?>
                                    <button type="button" class="btn btn-warning text-dark btn-sm" data-toggle="modal" data-target="#errorLogModalNavbar">
                                        <i class="fas fa-triangle-exclamation mr-1"></i> Buka Viewer Log Error
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted small">Di environment lokal, log tersimpan langsung di folder <code>writable/logs/</code>.</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="settings-footer-bar">
            <span class="text-muted small"><i class="fas fa-info-circle mr-1"></i> Perubahan pada tab General, Appearance, Storage, dan Security disimpan bersamaan.</span>
            <button type="submit" class="btn btn-primary px-4 font-weight-bold shadow-sm">
                <i class="fas fa-save mr-1"></i> Save Settings
            </button>
        </div>
    </form>
</div>

<script>
(() => {
    const testBtn = document.getElementById('testEmailSubmitBtn');
    const resultBox = document.getElementById('testEmailResult');
    if (!testBtn) return;

    const showResult = (ok, message, detail = '') => {
        if (!resultBox) return;
        resultBox.classList.remove('d-none', 'alert-success', 'alert-danger');
        resultBox.classList.add(ok ? 'alert-success' : 'alert-danger');
        const detailHtml = detail ? `<hr class="my-2"><pre class="mb-0" style="white-space:pre-wrap;max-height:220px;overflow:auto;">${detail.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</pre>` : '';
        resultBox.innerHTML = `<div>${message}</div>${detailHtml}`;
    };

    testBtn.addEventListener('click', async () => {
        const toEmail = document.getElementById('test_email_to')?.value.trim() || '';
        const subject = document.getElementById('test_email_subject')?.value.trim() || '';
        const message = document.getElementById('test_email_message')?.value || '';
        const csrfInput = document.querySelector('input[name="<?= csrf_token(); ?>"]');

        if (!toEmail) {
            showResult(false, 'Silakan isi Email Tujuan terlebih dahulu.');
            return;
        }

        const fd = new FormData();
        fd.append('to_email', toEmail);
        fd.append('subject', subject);
        fd.append('message', message);
        if (csrfInput) {
            fd.append(csrfInput.name, csrfInput.value);
        }

        testBtn.disabled = true;
        const originalText = testBtn.innerHTML;
        testBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Mengirim...';

        try {
            const response = await fetch('<?= site_url('/admin/pengaturan/application/test-email'); ?>', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: fd,
            });
            const payload = await response.json();
            const isOk = response.ok && payload && payload.status === 'ok';
            showResult(isOk, payload?.message || 'Terjadi kesalahan.', payload?.error_detail || '');

            if (payload && payload.csrf_hash && csrfInput) {
                csrfInput.value = payload.csrf_hash;
            }
        } catch (error) {
            showResult(false, 'Request gagal dijalankan.', error?.message || 'Unknown error');
        } finally {
            testBtn.disabled = false;
            testBtn.innerHTML = originalText;
        }
    });
})();
</script>
<?= $this->endSection(); ?>
