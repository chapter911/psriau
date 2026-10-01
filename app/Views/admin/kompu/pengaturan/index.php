<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<style>
    .kompu-card {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 18px -4px rgba(0, 0, 0, 0.05);
        background: #ffffff;
    }
    .custom-switch-lg .custom-control-label::before {
        height: 1.5rem;
        width: 2.75rem;
        border-radius: 1rem;
    }
    .custom-switch-lg .custom-control-label::after {
        width: calc(1.5rem - 4px);
        height: calc(1.5rem - 4px);
        border-radius: 50%;
    }
    .custom-switch-lg .custom-control-input:checked ~ .custom-control-label::after {
        transform: translateX(1.25rem);
    }
    .nav-tabs .nav-link {
        font-weight: 600;
        color: #64748b;
        border-radius: 8px 8px 0 0;
        padding: 10px 18px;
    }
    .nav-tabs .nav-link.active {
        color: #0A66C2;
        border-color: #e2e8f0 #e2e8f0 #ffffff;
        background: #ffffff;
    }
    .font-mono {
        font-family: 'SFMono-Regular', Consolas, Menlo, monospace;
    }
</style>

<!-- HEADER ROW -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card kompu-card border-0 mb-3">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center">
                            <span class="badge badge-danger mr-3 p-2" style="font-size: 1.1rem; border-radius: 8px;">
                                <i class="fas fa-user-shield"></i>
                            </span>
                            <div>
                                <h4 class="font-weight-bold text-dark mb-1">
                                    Pengaturan Akses Modul KOMPU
                                </h4>
                                <p class="text-muted small mb-0">
                                    Area khusus <strong>Super Administrator</strong> untuk menentukan pengguna terotorisasi, perizinan role, dan integrasi server email kredensial.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-right mt-2 mt-md-0">
                        <a href="<?= site_url('admin/kompu/sosmed'); ?>" class="btn btn-outline-primary btn-sm shadow-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Akun Sosmed
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TABS NAVIGATION -->
<div class="row">
    <div class="col-12">
        <ul class="nav nav-tabs border-bottom mb-3" id="kompuSettingsTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="users-tab" data-toggle="tab" href="#tab-users" role="tab" aria-controls="tab-users" aria-selected="true">
                    <i class="fas fa-users-cog mr-2"></i> Otorisasi Pengguna KOMPU
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="roles-tab" data-toggle="tab" href="#tab-roles" role="tab" aria-controls="tab-roles" aria-selected="false">
                    <i class="fas fa-key mr-2"></i> Hak Akses Role (menu_akses)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="smtp-tab" data-toggle="tab" href="#tab-smtp" role="tab" aria-controls="tab-smtp" aria-selected="false">
                    <i class="fas fa-server mr-2"></i> Server Email &amp; Uji Coba
                </a>
            </li>
        </ul>

        <div class="tab-content" id="kompuSettingsTabContent">
            <!-- TAB 1: OTORISASI PENGGUNA SPESIFIK -->
            <div class="tab-pane fade show active" id="tab-users" role="tabpanel" aria-labelledby="users-tab">
                <div class="card kompu-card">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                <h5 class="card-title font-weight-bold text-dark mb-1">
                                    <i class="fas fa-user-check text-success mr-2"></i> Daftar Otorisasi Akses Pengguna
                                </h5>
                                <small class="text-muted">
                                    Tentukan siapa saja pengguna yang berhak membuka menu KOMPU dan melihat log pengiriman kredensial.
                                </small>
                            </div>
                            <div class="mt-2 mt-sm-0">
                                <button type="button" class="btn btn-outline-success btn-xs mr-1" onclick="toggleAllAccess(true)">
                                    <i class="fas fa-check-double mr-1"></i> Izinkan Semua
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-xs" onclick="toggleAllAccess(false)">
                                    <i class="fas fa-ban mr-1"></i> Cabut Semua
                                </button>
                            </div>
                        </div>
                    </div>
                    <form method="post" action="<?= site_url('admin/kompu/pengaturan/user-access'); ?>">
                        <?= csrf_field(); ?>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light text-muted text-uppercase" style="font-size: 0.78rem; letter-spacing: 0.5px;">
                                        <tr>
                                            <th style="width: 50px;" class="text-center">No.</th>
                                            <th>Nama Pengguna &amp; NIP</th>
                                            <th>Jabatan / Unit</th>
                                            <th>Role Sistem</th>
                                            <th class="text-center" style="width: 140px;">Akses Modul KOMPU</th>
                                            <th class="text-center" style="width: 140px;">Lihat Semua Log</th>
                                            <th>Catatan PIC</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($users)): ?>
                                            <tr>
                                                <td colspan="7" class="text-center py-4 text-muted">Tidak ada data pengguna.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php $no = 1; foreach ($users as $u): ?>
                                                <?php
                                                    $uid = (int) $u['id'];
                                                    $uar = $userAccessMap[$uid] ?? null;
                                                    $isAllowed = $uar !== null ? ((int) ($uar['can_access'] ?? 1) === 1) : in_array($u['role'], ['super_administrator', 'admin']);
                                                    $canLog    = $uar !== null ? ((int) ($uar['can_view_log'] ?? 0) === 1) : ($u['role'] === 'super_administrator');
                                                    $notes     = $uar['notes'] ?? '';
                                                ?>
                                                <tr>
                                                    <td class="text-center font-weight-bold text-muted"><?= $no++; ?>.</td>
                                                    <td>
                                                        <div class="font-weight-bold text-dark"><?= esc($u['full_name']); ?></div>
                                                        <small class="text-muted font-mono">
                                                            <?= esc($u['nip'] ?: $u['username']); ?>
                                                            <?php if (! empty($u['email'])): ?>
                                                                • <span class="text-primary"><?= esc($u['email']); ?></span>
                                                            <?php endif; ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <small class="text-dark font-weight-500"><?= esc($u['jabatan'] ?: '-'); ?></small>
                                                    </td>
                                                    <td>
                                                        <?php
                                                            $roleBadge = 'badge-secondary';
                                                            if ($u['role'] === 'super_administrator') $roleBadge = 'badge-danger';
                                                            elseif ($u['role'] === 'admin') $roleBadge = 'badge-primary';
                                                            elseif ($u['role'] === 'editor') $roleBadge = 'badge-info';
                                                            elseif ($u['role'] === 'keuangan') $roleBadge = 'badge-success';
                                                        ?>
                                                        <span class="badge <?= $roleBadge; ?>"><?= esc(ucwords(str_replace('_', ' ', $u['role']))); ?></span>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="custom-control custom-switch d-inline-block">
                                                            <input type="checkbox" 
                                                                   class="custom-control-input user-access-chk" 
                                                                   name="user_access[<?= $uid; ?>]" 
                                                                   value="1" 
                                                                   id="access_<?= $uid; ?>" 
                                                                   <?= $isAllowed ? 'checked' : ''; ?>>
                                                            <label class="custom-control-label cursor-pointer" for="access_<?= $uid; ?>"></label>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="custom-control custom-switch d-inline-block">
                                                            <input type="checkbox" 
                                                                   class="custom-control-input view-log-chk" 
                                                                   name="can_view_log[<?= $uid; ?>]" 
                                                                   value="1" 
                                                                   id="log_<?= $uid; ?>" 
                                                                   <?= $canLog ? 'checked' : ''; ?>>
                                                            <label class="custom-control-label cursor-pointer" for="log_<?= $uid; ?>"></label>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="notes[<?= $uid; ?>]" value="<?= esc($notes); ?>" class="form-control form-control-sm" placeholder="Catatan tugas / PIC..." style="min-width: 140px;">
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-light text-right py-3">
                            <button type="submit" class="btn btn-primary btn-sm shadow-sm">
                                <i class="fas fa-save mr-1"></i> Simpan Otorisasi Pengguna
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- TAB 2: MATRIKS HAK AKSES ROLE (MENU_AKSES) -->
            <div class="tab-pane fade" id="tab-roles" role="tabpanel" aria-labelledby="roles-tab">
                <div class="card kompu-card">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h5 class="card-title font-weight-bold text-dark mb-1">
                            <i class="fas fa-th mr-2 text-primary"></i> Matriks Hak Akses Role (Tabel menu_akses)
                        </h5>
                        <small class="text-muted d-block">
                            Atur izin fitur (View, Tambah, Edit, Hapus, Export) untuk masing-masing Role pengguna pada submenu KOMPU.
                        </small>
                    </div>
                    <form method="post" action="<?= site_url('admin/kompu/pengaturan/role-permissions'); ?>">
                        <?= csrf_field(); ?>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle mb-0">
                                    <thead class="bg-light text-muted text-center" style="font-size: 0.8rem;">
                                        <tr>
                                            <th rowspan="2" class="align-middle text-left" style="min-width: 180px;">Role Pengguna</th>
                                            <th colspan="5" class="bg-white border-bottom">Media Sosial Satker (13-01)</th>
                                            <th colspan="2" class="bg-light border-bottom">Log Pengiriman (13-02)</th>
                                        </tr>
                                        <tr>
                                            <th style="width: 70px;">View</th>
                                            <th style="width: 70px;">Add</th>
                                            <th style="width: 70px;">Edit</th>
                                            <th style="width: 70px;">Delete</th>
                                            <th style="width: 70px;">Export</th>
                                            <th style="width: 70px;">View</th>
                                            <th style="width: 70px;">Export</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($roles as $role): ?>
                                            <?php
                                                $rId = (int) $role['id'];
                                                $isSuper = ($rId === 1);
                                                $pSosmed = $menuAksesMap[$rId]['13-01'] ?? ['add' => false, 'edit' => false, 'delete' => false, 'export' => false];
                                                $hasViewSosmed = isset($menuAksesMap[$rId]['13-01']);
                                                $pLog    = $menuAksesMap[$rId]['13-02'] ?? ['export' => false];
                                                $hasViewLog = isset($menuAksesMap[$rId]['13-02']);
                                            ?>
                                            <tr>
                                                <td>
                                                    <strong class="text-dark"><?= esc($role['label']); ?></strong>
                                                    <div class="text-muted small font-mono">Key: <?= esc($role['role_key']); ?></div>
                                                </td>

                                                <!-- 13-01: Media Sosial Satker -->
                                                <td class="text-center">
                                                    <input type="checkbox" name="matrix[<?= $rId; ?>][13-01][view]" value="1" <?= ($hasViewSosmed || $isSuper) ? 'checked' : ''; ?> <?= $isSuper ? 'disabled checked' : ''; ?>>
                                                </td>
                                                <td class="text-center">
                                                    <input type="checkbox" name="matrix[<?= $rId; ?>][13-01][add]" value="1" <?= ($pSosmed['add'] || $isSuper) ? 'checked' : ''; ?> <?= $isSuper ? 'disabled checked' : ''; ?>>
                                                </td>
                                                <td class="text-center">
                                                    <input type="checkbox" name="matrix[<?= $rId; ?>][13-01][edit]" value="1" <?= ($pSosmed['edit'] || $isSuper) ? 'checked' : ''; ?> <?= $isSuper ? 'disabled checked' : ''; ?>>
                                                </td>
                                                <td class="text-center">
                                                    <input type="checkbox" name="matrix[<?= $rId; ?>][13-01][delete]" value="1" <?= ($pSosmed['delete'] || $isSuper) ? 'checked' : ''; ?> <?= $isSuper ? 'disabled checked' : ''; ?>>
                                                </td>
                                                <td class="text-center">
                                                    <input type="checkbox" name="matrix[<?= $rId; ?>][13-01][export]" value="1" <?= ($pSosmed['export'] || $isSuper) ? 'checked' : ''; ?> <?= $isSuper ? 'disabled checked' : ''; ?>>
                                                </td>

                                                <!-- 13-02: Log Kredensial -->
                                                <td class="text-center bg-light">
                                                    <input type="checkbox" name="matrix[<?= $rId; ?>][13-02][view]" value="1" <?= ($hasViewLog || $isSuper) ? 'checked' : ''; ?> <?= $isSuper ? 'disabled checked' : ''; ?>>
                                                </td>
                                                <td class="text-center bg-light">
                                                    <input type="checkbox" name="matrix[<?= $rId; ?>][13-02][export]" value="1" <?= ($pLog['export'] || $isSuper) ? 'checked' : ''; ?> <?= $isSuper ? 'disabled checked' : ''; ?>>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="p-3 bg-light border-top">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle text-info mr-1"></i> Catatan: Submenu <strong>Pengaturan (13-03)</strong> dikunci secara eksklusif hanya untuk <strong>Super Administrator</strong> demi alasan keamanan otorisasi sistem.
                                </small>
                            </div>
                        </div>
                        <div class="card-footer bg-light text-right py-3">
                            <button type="submit" class="btn btn-primary btn-sm shadow-sm">
                                <i class="fas fa-save mr-1"></i> Simpan Matriks Hak Akses
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- TAB 3: SERVER EMAIL SMTP & UJI COBA -->
            <div class="tab-pane fade" id="tab-smtp" role="tabpanel" aria-labelledby="smtp-tab">
                <div class="row">
                    <div class="col-md-7">
                        <div class="card kompu-card mb-3">
                            <div class="card-header bg-white py-3 border-bottom-0">
                                <h5 class="card-title font-weight-bold text-dark mb-0">
                                    <i class="fas fa-server mr-2 text-primary"></i> Konfigurasi SMTP Pengiriman Email
                                </h5>
                            </div>
                            <div class="card-body pt-0">
                                <table class="table table-sm table-borderless mb-0">
                                    <tbody>
                                        <tr>
                                            <td style="width: 170px;" class="font-weight-600 text-muted">Pengirim (From Email):</td>
                                            <td class="font-weight-bold text-dark font-mono"><?= esc($smtpInfo['fromEmail']); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-600 text-muted">Nama Pengirim:</td>
                                            <td class="font-weight-bold text-dark"><?= esc($smtpInfo['fromName']); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-600 text-muted">Protokol:</td>
                                            <td><span class="badge badge-primary text-uppercase"><?= esc($smtpInfo['protocol']); ?></span></td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-600 text-muted">SMTP Host:</td>
                                            <td class="font-mono"><?= esc($smtpInfo['host']); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-600 text-muted">Port &amp; Enkripsi:</td>
                                            <td class="font-mono"><?= esc($smtpInfo['port']); ?> (<?= strtoupper(esc($smtpInfo['crypto'])); ?>)</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-600 text-muted">Akun SMTP:</td>
                                            <td class="font-mono"><?= esc($smtpInfo['user']); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="card kompu-card">
                            <div class="card-header bg-white py-3 border-bottom-0">
                                <h5 class="card-title font-weight-bold text-dark mb-0">
                                    <i class="fas fa-paper-plane mr-2 text-success"></i> Uji Coba Pengiriman Email
                                </h5>
                            </div>
                            <div class="card-body pt-0">
                                <p class="text-muted small">
                                    Kirimkan pesan uji coba untuk memverifikasi apakah server SMTP mampu mengirim email kredensial ke alamat tujuan dengan lancar.
                                </p>
                                <div class="form-group">
                                    <label class="font-weight-bold small">Alamat Email Tujuan Pengujian</label>
                                    <input type="email" id="testEmailInput" class="form-control form-control-sm font-mono" placeholder="nama@email.com" value="<?= esc(session()->get('username') . '@example.com'); ?>">
                                </div>
                                <button type="button" id="btnTestEmail" class="btn btn-success btn-sm btn-block shadow-sm" onclick="sendTestEmail()">
                                    <i class="fas fa-paper-plane mr-1"></i> Kirim Email Uji Coba Sekarang
                                </button>
                                <div id="testEmailResult" class="mt-3" style="display: none;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleAllAccess(enable) {
        document.querySelectorAll('.user-access-chk').forEach(function(chk) {
            chk.checked = enable;
        });
    }

    function sendTestEmail() {
        var email = document.getElementById('testEmailInput').value.trim();
        var resultDiv = document.getElementById('testEmailResult');
        var btn = document.getElementById('btnTestEmail');

        if (!email) {
            alert('Masukkan alamat email tujuan terlebih dahulu.');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Mengirim Pengujian...';
        resultDiv.style.display = 'none';

        var formData = new FormData();
        formData.append('test_email', email);
        formData.append('<?= csrf_token(); ?>', '<?= csrf_hash(); ?>');

        fetch('<?= site_url('admin/kompu/pengaturan/test-email'); ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane mr-1"></i> Kirim Email Uji Coba Sekarang';
            resultDiv.style.display = 'block';

            if (data.status === 'success') {
                resultDiv.innerHTML = '<div class="alert alert-success alert-sm small mb-0"><i class="fas fa-check-circle mr-1"></i> ' + data.message + '</div>';
            } else {
                resultDiv.innerHTML = '<div class="alert alert-danger alert-sm small mb-0"><i class="fas fa-times-circle mr-1"></i> ' + data.message + '</div>';
            }
        })
        .catch(function(err) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane mr-1"></i> Kirim Email Uji Coba Sekarang';
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = '<div class="alert alert-danger alert-sm small mb-0"><i class="fas fa-times-circle mr-1"></i> Gagal berkomunikasi dengan server: ' + err + '</div>';
        });
    }
</script>
<?= $this->endSection(); ?>
