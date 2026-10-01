<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<style>
    .kompu-card {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 18px -4px rgba(0, 0, 0, 0.05);
        background: #ffffff;
        transition: all 0.25s ease;
    }
    .platform-icon-circle {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        color: #ffffff;
        box-shadow: 0 3px 10px rgba(0,0,0,0.12);
    }
    .platform-instagram { background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%); }
    .platform-threads   { background: #000000; }
    .platform-tiktok    { background: #000000; border: 1px solid #25F4EE; }
    .platform-twitter, .platform-x { background: #000000; }
    .platform-facebook  { background: #1877F2; }
    .platform-youtube   { background: #FF0000; }
    .platform-email     { background: #EA4335; }
    .platform-default   { background: #0A66C2; }

    .copy-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        padding: 3px 9px;
        border-radius: 6px;
        font-family: 'SFMono-Regular', Consolas, Menlo, monospace;
        font-size: 0.85rem;
        color: #1e293b;
        cursor: pointer;
        transition: all 0.15s ease-in-out;
    }
    .copy-pill:hover {
        background: #e2e8f0;
        border-color: #94a3b8;
        transform: translateY(-1px);
    }
    .copy-pill i {
        color: #64748b;
        font-size: 0.75rem;
    }
    .password-mask-badge {
        font-family: 'SFMono-Regular', Consolas, Menlo, monospace;
        letter-spacing: 2px;
        font-size: 0.95rem;
        background: #e2e8f0;
        color: #475569;
        padding: 3px 8px;
        border-radius: 6px;
        user-select: none;
    }
    .btn-send-email {
        transition: all 0.2s ease;
        font-weight: 600;
        border-radius: 6px;
    }
    .btn-send-email:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(10, 102, 194, 0.25);
    }
    .stat-box {
        border-radius: 10px;
        padding: 16px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
    }
</style>

<div class="row mb-3">
    <div class="col-12">
        <div class="card kompu-card border-0 mb-3">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-md-7">
                        <h4 class="font-weight-bold text-dark mb-1">
                            <i class="fas fa-hashtag text-primary mr-2"></i> Media Sosial &amp; Akun Publikasi Satker
                        </h4>
                        <p class="text-muted small mb-md-0">
                            Kelola direktori resmi akun komunikasi publik, profil medsos, dan pengiriman password terenkripsi via email kedinasan.
                        </p>
                    </div>
                    <div class="col-md-5 text-md-right">
                        <?php if ($menuPermissions['add']): ?>
                            <button type="button" class="btn btn-primary btn-sm shadow-sm mr-1" onclick="openAddSosmedModal()">
                                <i class="fas fa-plus mr-1"></i> Tambah Akun
                            </button>
                        <?php endif; ?>
                        <?php if ($menuPermissions['export']): ?>
                            <a href="<?= site_url('admin/kompu/sosmed/export'); ?>" class="btn btn-outline-secondary btn-sm shadow-sm mr-1">
                                <i class="fas fa-file-csv mr-1"></i> Export CSV
                            </a>
                        <?php endif; ?>
                        <a href="<?= site_url('admin/kompu/log-kredensial'); ?>" class="btn btn-outline-info btn-sm shadow-sm">
                            <i class="fas fa-history mr-1"></i> Log Pengiriman
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- INFO BANNER: USER RECIPIENT EMAIL -->
<div class="row mb-3">
    <div class="col-12">
        <?php if (! empty($currentUserEmail)): ?>
            <div class="stat-box d-flex align-items-center justify-content-between flex-wrap" style="background: #f0fdf4; border-color: #bbf7d0;">
                <div class="d-flex align-items-center">
                    <div class="mr-3">
                        <span class="badge badge-success p-2" style="font-size: 1.1rem; border-radius: 50%;">
                            <i class="fas fa-envelope-check"></i>
                        </span>
                    </div>
                    <div>
                        <strong class="text-success" style="font-size: 0.95rem;">Email Penerima Terdaftar:</strong>
                        <span class="font-weight-bold ml-1 text-dark font-mono"><?= esc($currentUserEmail); ?></span>
                        <div class="text-muted small">Saat Anda menekan tombol "Kirim ke Email", password akun akan secara aman dikirimkan ke kotak masuk email ini.</div>
                    </div>
                </div>
                <div class="mt-2 mt-md-0">
                    <span class="badge badge-success px-3 py-2"><i class="fas fa-shield-check mr-1"></i> Siap Kirim Kredensial</span>
                </div>
            </div>
        <?php else: ?>
            <div class="stat-box d-flex align-items-center justify-content-between flex-wrap" style="background: #fef2f2; border-color: #fecaca;">
                <div class="d-flex align-items-center">
                    <div class="mr-3">
                        <span class="badge badge-danger p-2" style="font-size: 1.1rem; border-radius: 50%;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </span>
                    </div>
                    <div>
                        <strong class="text-danger" style="font-size: 0.95rem;">Email Belum Terdaftar di Profil Pegawai!</strong>
                        <div class="text-muted small">Anda belum memiliki alamat email yang tersimpan pada data master pegawai/user. Fitur kirim password tidak dapat digunakan sampai email didaftarkan.</div>
                    </div>
                </div>
                <div class="mt-2 mt-md-0">
                    <a href="<?= site_url('admin/pegawai'); ?>" class="btn btn-danger btn-sm">
                        <i class="fas fa-user-edit mr-1"></i> Lengkapi Profil Email
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- SOSMED LIST TABLE -->
<div class="row">
    <div class="col-12">
        <div class="card kompu-card">
            <div class="card-header bg-white border-bottom-0 py-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <h5 class="card-title font-weight-bold text-dark mb-0">
                        <i class="fas fa-list-ul mr-2 text-primary"></i> Daftar Akun &amp; Kredensial Resmi Satker (<?= count($items); ?> Akun)
                    </h5>
                    <div class="card-tools mt-2 mt-sm-0">
                        <input type="text" id="sosmedSearchInput" class="form-control form-control-sm" placeholder="Cari platform / username..." style="width: 240px;">
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tableSosmed">
                        <thead class="bg-light text-muted text-uppercase" style="font-size: 0.78rem; letter-spacing: 0.5px;">
                            <tr>
                                <th style="width: 50px;" class="text-center">No.</th>
                                <th>Platform / Akun</th>
                                <th>Data Login (Username / Email)</th>
                                <th>Password Kredensial</th>
                                <th>Laman Profil</th>
                                <th style="width: 110px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fas fa-folder-open fa-3x mb-3 text-secondary d-block"></i>
                                        Belum ada data akun media sosial yang tersimpan.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($items as $item): ?>
                                    <?php
                                        $iconClass = trim((string) ($item['icon'] ?: 'fas fa-hashtag'));
                                        $namaLower = strtolower($item['nama_sosmed']);
                                        $platformStyle = 'platform-default';
                                        if (str_contains($namaLower, 'instagram')) $platformStyle = 'platform-instagram';
                                        elseif (str_contains($namaLower, 'threads')) $platformStyle = 'platform-threads';
                                        elseif (str_contains($namaLower, 'tiktok')) $platformStyle = 'platform-tiktok';
                                        elseif (str_contains($namaLower, 'twitter') || $namaLower === 'x' || str_contains($namaLower, 'x (')) $platformStyle = 'platform-twitter';
                                        elseif (str_contains($namaLower, 'facebook')) $platformStyle = 'platform-facebook';
                                        elseif (str_contains($namaLower, 'youtube')) $platformStyle = 'platform-youtube';
                                        elseif (str_contains($namaLower, 'email')) $platformStyle = 'platform-email';

                                        $hasPassword = ! empty(trim((string) $item['password']));
                                    ?>
                                    <tr class="sosmed-row" data-search="<?= esc(strtolower($item['nama_sosmed'] . ' ' . $item['username'] . ' ' . $item['email_login'] . ' ' . $item['kategori'])); ?>">
                                        <td class="text-center font-weight-bold text-muted"><?= $no++; ?>.</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="platform-icon-circle <?= $platformStyle; ?> mr-3 flex-shrink-0">
                                                    <i class="<?= esc($iconClass); ?>"></i>
                                                </div>
                                                <div>
                                                    <div class="font-weight-bold text-dark" style="font-size: 1rem;"><?= esc($item['nama_sosmed']); ?></div>
                                                    <span class="badge badge-light border text-muted small"><?= esc($item['kategori'] ?: 'Media Sosial'); ?></span>
                                                    <?php if (! empty($item['metode_login'])): ?>
                                                        <span class="badge badge-secondary small ml-1"><?= esc($item['metode_login']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if (! empty($item['username'])): ?>
                                                <div class="mb-1">
                                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Username:</small>
                                                    <span class="copy-pill" onclick="copyText('<?= esc(addslashes($item['username'])); ?>', 'Username')">
                                                        <i class="far fa-copy mr-1"></i> <?= esc($item['username']); ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (! empty($item['email_login']) && $item['email_login'] !== $item['username']): ?>
                                                <div>
                                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Email Login:</small>
                                                    <span class="copy-pill" onclick="copyText('<?= esc(addslashes($item['email_login'])); ?>', 'Email Login')">
                                                        <i class="far fa-envelope mr-1"></i> <?= esc($item['email_login']); ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (empty($item['username']) && empty($item['email_login'])): ?>
                                                <span class="text-muted font-italic small">- Tidak ada username -</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($hasPassword): ?>
                                                <div class="d-flex align-items-center flex-wrap gap-2">
                                                    <span class="password-mask-badge mr-2">••••••••</span>
                                                    <button type="button" 
                                                            class="btn btn-outline-primary btn-sm btn-send-email" 
                                                            onclick="confirmSendPassword(<?= (int) $item['id']; ?>, '<?= esc(addslashes($item['nama_sosmed'])); ?>')"
                                                            title="Kirim password ke email saya">
                                                        <i class="fas fa-paper-plane mr-1"></i> Kirim ke Email
                                                    </button>
                                                </div>
                                            <?php else: ?>
                                                <div>
                                                    <span class="badge badge-light border text-muted p-2">
                                                        <i class="fab fa-google text-danger mr-1"></i> Login via Akun Gmail Satker
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (! empty($item['url_profil'])): ?>
                                                <a href="<?= esc($item['url_profil']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-xs btn-outline-secondary">
                                                    <i class="fas fa-external-link-alt mr-1"></i> Kunjungi Profil
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted small">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <?php if ($menuPermissions['edit']): ?>
                                                    <button type="button" class="btn btn-outline-secondary" onclick='openEditSosmedModal(<?= json_encode($item, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)' title="Ubah Data">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <?php if ($menuPermissions['delete']): ?>
                                                    <button type="button" class="btn btn-outline-danger" onclick="confirmDeleteSosmed(<?= (int) $item['id']; ?>, '<?= esc(addslashes($item['nama_sosmed'])); ?>')" title="Hapus Akun">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL ADD / EDIT SOSMED -->
<div class="modal fade" id="modalSosmed" tabindex="-1" role="dialog" aria-labelledby="modalSosmedLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form method="post" action="<?= site_url('admin/kompu/sosmed/simpan'); ?>" id="formSosmed" class="modal-content">
            <?= csrf_field(); ?>
            <input type="hidden" name="id" id="sosmed_id" value="">
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold" id="modalSosmedLabel">
                    <i class="fas fa-hashtag text-primary mr-2"></i> Tambah Akun Media Sosial
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold">Nama Platform / Media Sosial <span class="text-danger">*</span></label>
                        <input type="text" name="nama_sosmed" id="input_nama_sosmed" class="form-control" placeholder="Contoh: Instagram, TikTok, YouTube" required>
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold">Kategori</label>
                        <select name="kategori" id="input_kategori" class="form-control">
                            <option value="Media Sosial">Media Sosial</option>
                            <option value="Email">Email Satker</option>
                            <option value="Platform Streaming/Video">Platform Streaming / Video</option>
                            <option value="Website & Portal">Website &amp; Portal Publikasi</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold">Username Akun</label>
                        <input type="text" name="username" id="input_username" class="form-control" placeholder="Contoh: pu_prasaranastrategis_riau">
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold">Email Login Terkait</label>
                        <input type="email" name="email_login" id="input_email_login" class="form-control" placeholder="satkerpsriau@gmail.com">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold">Password Kredensial</label>
                        <div class="input-group">
                            <input type="password" name="password" id="input_password" class="form-control" placeholder="Masukkan password (kosongkan jika via Google SSO)">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('input_password', this)">
                                    <i class="far fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <small class="text-muted">Password akan disimpan terenkripsi dan dirahasiakan di tabel tampilan.</small>
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold">Metode Login</label>
                        <input type="text" name="metode_login" id="input_metode_login" class="form-control" placeholder="Contoh: Username & Password / Login dengan Google">
                    </div>
                </div>

                <div class="form-group">
                    <label class="font-weight-bold">Laman Profil (URL)</label>
                    <input type="url" name="url_profil" id="input_url_profil" class="form-control" placeholder="https://www.instagram.com/pu_prasaranastrategis_riau/">
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold">Icon FontAwesome</label>
                        <input type="text" name="icon" id="input_icon" class="form-control font-mono" placeholder="fab fa-instagram">
                        <div class="mt-1 d-flex gap-2 flex-wrap" style="gap: 6px;">
                            <span class="badge badge-light border cursor-pointer" onclick="setIconPreset('fab fa-instagram')"><i class="fab fa-instagram"></i> IG</span>
                            <span class="badge badge-light border cursor-pointer" onclick="setIconPreset('fab fa-threads')"><i class="fab fa-threads"></i> Threads</span>
                            <span class="badge badge-light border cursor-pointer" onclick="setIconPreset('fab fa-tiktok')"><i class="fab fa-tiktok"></i> TikTok</span>
                            <span class="badge badge-light border cursor-pointer" onclick="setIconPreset('fab fa-x-twitter')"><i class="fab fa-x-twitter"></i> X</span>
                            <span class="badge badge-light border cursor-pointer" onclick="setIconPreset('fab fa-facebook-f')"><i class="fab fa-facebook-f"></i> FB</span>
                            <span class="badge badge-light border cursor-pointer" onclick="setIconPreset('fab fa-youtube')"><i class="fab fa-youtube"></i> YT</span>
                            <span class="badge badge-light border cursor-pointer" onclick="setIconPreset('fas fa-envelope')"><i class="fas fa-envelope"></i> Email</span>
                        </div>
                    </div>
                    <div class="col-md-3 form-group">
                        <label class="font-weight-bold">Nomor Urutan</label>
                        <input type="number" name="ordering" id="input_ordering" class="form-control" value="0" min="0">
                    </div>
                    <div class="col-md-3 form-group">
                        <label class="font-weight-bold">Status</label>
                        <select name="is_active" id="input_is_active" class="form-control">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div class="form-group mb-0">
                    <label class="font-weight-bold">Keterangan / Catatan Akun</label>
                    <textarea name="keterangan" id="input_keterangan" class="form-control" rows="2" placeholder="Catatan fungsi akun atau PIC pengelola..."></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-save mr-1"></i> Simpan Data
                </button>
            </div>
        </form>
    </div>
</div>

<!-- HIDDEN DELETE FORM -->
<form id="formDeleteSosmed" method="post" action="" style="display: none;">
    <?= csrf_field(); ?>
</form>

<script>
    // Copy to clipboard helper
    function copyText(text, label) {
        if (!navigator.clipboard) {
            // Fallback
            var textArea = document.createElement("textarea");
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand("copy");
            document.body.removeChild(textArea);
        } else {
            navigator.clipboard.writeText(text);
        }

        if (window.Swal) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: label + ' berhasil disalin!',
                showConfirmButton: false,
                timer: 1800
            });
        } else {
            alert(label + ' disalin: ' + text);
        }
    }

    // Toggle password visibility
    function togglePasswordVisibility(fieldId, btn) {
        var input = document.getElementById(fieldId);
        var icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'far fa-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'far fa-eye';
        }
    }

    function setIconPreset(iconClass) {
        document.getElementById('input_icon').value = iconClass;
    }

    // Filter table
    document.getElementById('sosmedSearchInput').addEventListener('input', function() {
        var val = this.value.toLowerCase().trim();
        var rows = document.querySelectorAll('.sosmed-row');
        rows.forEach(function(row) {
            var searchData = row.getAttribute('data-search') || '';
            row.style.display = searchData.includes(val) ? '' : 'none';
        });
    });

    // Add Modal
    function openAddSosmedModal() {
        document.getElementById('modalSosmedLabel').innerHTML = '<i class="fas fa-plus text-primary mr-2"></i> Tambah Akun Media Sosial';
        document.getElementById('sosmed_id').value = '';
        document.getElementById('formSosmed').reset();
        document.getElementById('input_ordering').value = '<?= count($items) + 1; ?>';
        document.getElementById('input_is_active').value = '1';
        document.getElementById('input_icon').value = 'fab fa-instagram';
        $('#modalSosmed').modal('show');
    }

    // Edit Modal
    function openEditSosmedModal(item) {
        document.getElementById('modalSosmedLabel').innerHTML = '<i class="fas fa-edit text-primary mr-2"></i> Ubah Akun Media Sosial';
        document.getElementById('sosmed_id').value = item.id;
        document.getElementById('input_nama_sosmed').value = item.nama_sosmed || '';
        document.getElementById('input_kategori').value = item.kategori || 'Media Sosial';
        document.getElementById('input_username').value = item.username || '';
        document.getElementById('input_email_login').value = item.email_login || '';
        document.getElementById('input_password').value = item.password || '';
        document.getElementById('input_metode_login').value = item.metode_login || '';
        document.getElementById('input_url_profil').value = item.url_profil || '';
        document.getElementById('input_icon').value = item.icon || 'fas fa-hashtag';
        document.getElementById('input_ordering').value = item.ordering || 0;
        document.getElementById('input_is_active').value = item.is_active != null ? item.is_active : 1;
        document.getElementById('input_keterangan').value = item.keterangan || '';
        $('#modalSosmed').modal('show');
    }

    // Delete confirmation
    function confirmDeleteSosmed(id, name) {
        if (!window.Swal) {
            if (confirm('Apakah Anda yakin ingin menghapus akun ' + name + '?')) {
                var form = document.getElementById('formDeleteSosmed');
                form.action = '<?= site_url('admin/kompu/sosmed'); ?>/' + id + '/hapus';
                form.submit();
            }
            return;
        }

        Swal.fire({
            title: 'Hapus Akun?',
            text: 'Data kredensial untuk akun "' + name + '" akan dihapus permanen dari sistem.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash-alt mr-1"></i> Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then(function(result) {
            if (result.isConfirmed) {
                var form = document.getElementById('formDeleteSosmed');
                form.action = '<?= site_url('admin/kompu/sosmed'); ?>/' + id + '/hapus';
                form.submit();
            }
        });
    }

    // Send password to logged-in user email
    function confirmSendPassword(id, name) {
        var userEmail = '<?= esc($currentUserEmail ?: ''); ?>';
        if (!userEmail) {
            Swal.fire({
                icon: 'error',
                title: 'Email Belum Terdaftar',
                text: 'Akun login Anda belum memiliki alamat email yang tercatat pada master pegawai. Harap lengkapi email profil Anda terlebih dahulu.',
                confirmButtonText: 'Tutup'
            });
            return;
        }

        Swal.fire({
            title: 'Kirim Password Akun?',
            html: 'Kirimkan password untuk <strong>' + name + '</strong> ke email Anda:<br><strong class="text-primary font-mono" style="font-size: 1.05rem;">' + userEmail + '</strong>?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0A66C2',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-paper-plane mr-1"></i> Ya, Kirim Sekarang',
            cancelButtonText: 'Batal'
        }).then(function(result) {
            if (result.isConfirmed) {
                // Show loading modal
                Swal.fire({
                    title: 'Mengirim Email Kredensial...',
                    html: 'Sedang menghubungi server email kedinasan. Mohon tunggu sejenak.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });

                var formData = new FormData();
                formData.append('sosmed_id', id);
                formData.append('<?= csrf_token(); ?>', '<?= csrf_hash(); ?>');

                fetch('<?= site_url('admin/kompu/sosmed/kirim-password'); ?>', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Email Berhasil Terkirim!',
                            html: data.message,
                            confirmButtonColor: '#0A66C2'
                        });
                    } else if (data.status === 'info' || data.status === 'warning') {
                        Swal.fire({
                            icon: 'info',
                            title: 'Informasi Akun',
                            html: data.message,
                            confirmButtonColor: '#0A66C2'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Pengiriman Gagal',
                            html: data.message,
                            confirmButtonColor: '#dc3545'
                        });
                    }
                })
                .catch(function(err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Kesalahan Sistem',
                        text: 'Terjadi kegagalan komunikasi jaringan saat mengirim email: ' + err,
                        confirmButtonColor: '#dc3545'
                    });
                });
            }
        });
    }
</script>
<?= $this->endSection(); ?>
