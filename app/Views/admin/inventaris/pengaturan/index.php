<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-sm-6">
            <h4 class="m-0 font-weight-bold text-dark"><i class="fas fa-sliders-h mr-2 text-primary"></i> <?= esc($pageTitle); ?></h4>
            <p class="text-muted small mb-0">Konfigurasi Kop Surat dan Riwayat Jabatan Kasatker BMN</p>
        </div>
        <div class="col-sm-6 text-right">
            <a href="<?= site_url('admin/inventaris/pinjam-pakai'); ?>" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Pinjam Pakai
            </a>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle mr-1"></i> <?= session()->getFlashdata('success'); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle mr-1"></i> <?= session()->getFlashdata('error'); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php $activeTab = $_GET['tab'] ?? 'kop'; ?>

    <div class="card card-primary card-outline card-outline-tabs shadow-sm">
        <div class="card-header p-0 border-bottom-0">
            <ul class="nav nav-tabs" id="pengaturanTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link <?= $activeTab === 'kop' ? 'active' : ''; ?> font-weight-bold" id="tab-kop-link" data-toggle="pill" href="#tab-kop" role="tab" aria-controls="tab-kop" aria-selected="<?= $activeTab === 'kop' ? 'true' : 'false'; ?>">
                        <i class="fas fa-file-image mr-1 text-info"></i> 1. Masa Berlaku Kop Surat BMN
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $activeTab === 'kasatker' ? 'active' : ''; ?> font-weight-bold" id="tab-kasatker-link" data-toggle="pill" href="#tab-kasatker" role="tab" aria-controls="tab-kasatker" aria-selected="<?= $activeTab === 'kasatker' ? 'true' : 'false'; ?>">
                        <i class="fas fa-user-tie mr-1 text-warning"></i> 2. Riwayat Jabatan Kasatker (KPB)
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="pengaturanTabContent">
                
                <!-- ================= TAB 1: KOP SURAT ================= -->
                <div class="tab-pane fade <?= $activeTab === 'kop' ? 'show active' : ''; ?>" id="tab-kop" role="tabpanel" aria-labelledby="tab-kop-link">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="font-weight-bold text-dark mb-0">Daftar Kop Surat Resmi BMN</h5>
                            <small class="text-muted">Kop Surat akan dipilih secara otomatis saat transaksi dibuat berdasarkan rentang tanggal berlaku surat.</small>
                        </div>
                        <?php if ($menuPermissions['add'] ?? false): ?>
                            <button type="button" class="btn btn-primary btn-sm shadow-sm font-weight-bold" data-toggle="modal" data-target="#modalKopSurat" onclick="resetFormKop()">
                                <i class="fas fa-plus mr-1"></i> Tambah Kop Surat
                            </button>
                        <?php endif; ?>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="thead-light">
                                <tr class="text-center small font-weight-bold">
                                    <th style="width: 50px;">No</th>
                                    <th style="width: 200px;">Pratinjau Kop</th>
                                    <th>Nama Kop Surat</th>
                                    <th style="width: 220px;">Rentang Berlaku</th>
                                    <th style="width: 100px;">Status</th>
                                    <th>Keterangan</th>
                                    <th style="width: 120px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($kopSuratList)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">Belum ada data kop surat BMN.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1; foreach ($kopSuratList as $kop): ?>
                                        <tr>
                                            <td class="text-center font-weight-bold"><?= $no++; ?></td>
                                            <td class="text-center bg-light p-2">
                                                <?php if (! empty($kop['image_url'])): ?>
                                                    <img src="<?= esc(media_url((string) $kop['image_url'])); ?>" alt="Kop" style="max-width: 180px; max-height: 60px; object-fit: contain;">
                                                <?php else: ?>
                                                    <span class="text-muted small">Tidak ada gambar</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?= esc($kop['nama_kop']); ?></strong>
                                            </td>
                                            <td>
                                                <div class="small">
                                                    <i class="far fa-calendar-alt mr-1 text-primary"></i> 
                                                    <strong>Dari:</strong> <?= ! empty($kop['berlaku_dari']) ? date('d/m/Y', strtotime($kop['berlaku_dari'])) : '<span class="text-muted">Awal Transaksi</span>'; ?><br>
                                                    <i class="far fa-calendar-check mr-1 text-success"></i> 
                                                    <strong>Sampai:</strong> <?= ! empty($kop['berlaku_sampai']) ? date('d/m/Y', strtotime($kop['berlaku_sampai'])) : '<span class="badge badge-success">Sekarang / Seterusnya</span>'; ?>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <?php if ((int) $kop['is_active'] === 1): ?>
                                                    <span class="badge badge-success px-2 py-1"><i class="fas fa-check"></i> Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary px-2 py-1">Nonaktif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="small text-muted"><?= esc($kop['keterangan'] ?? '-'); ?></td>
                                            <td class="text-center">
                                                <?php if ($menuPermissions['edit'] ?? false): ?>
                                                    <button type="button" class="btn btn-warning btn-xs shadow-sm" onclick='editKop(<?= json_encode($kop); ?>)' title="Ubah">
                                                        <i class="fas fa-pencil-alt"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <?php if ($menuPermissions['delete'] ?? false): ?>
                                                    <a href="<?= site_url('admin/inventaris/pengaturan/kop/' . $kop['id'] . '/delete'); ?>" class="btn btn-danger btn-xs shadow-sm" onclick="return confirm('Hapus Kop Surat ini?');" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ================= TAB 2: RIWAYAT KASATKER ================= -->
                <div class="tab-pane fade <?= $activeTab === 'kasatker' ? 'show active' : ''; ?>" id="tab-kasatker" role="tabpanel" aria-labelledby="tab-kasatker-link">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="font-weight-bold text-dark mb-0">Riwayat Masa Jabatan Kepala Satuan Kerja (Kuasa Pengguna Barang)</h5>
                            <small class="text-muted">Nama pejabat yang menandatangani dokumen surat perjanjian dan DBR akan menyesuaikan dengan periode tanggal surat.</small>
                        </div>
                        <?php if ($menuPermissions['add'] ?? false): ?>
                            <button type="button" class="btn btn-warning btn-sm shadow-sm font-weight-bold text-dark" data-toggle="modal" data-target="#modalKasatker" onclick="resetFormKasatker()">
                                <i class="fas fa-plus mr-1"></i> Tambah Pejabat Kasatker
                            </button>
                        <?php endif; ?>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="thead-light">
                                <tr class="text-center small font-weight-bold">
                                    <th style="width: 50px;">No</th>
                                    <th>Nama Pejabat &amp; NIP</th>
                                    <th>Jabatan Resmi</th>
                                    <th style="width: 220px;">Periode Masa Jabatan</th>
                                    <th style="width: 120px;">Status Jabatan</th>
                                    <th style="width: 90px;">Aktif</th>
                                    <th style="width: 100px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($kasatkerList)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">Belum ada data riwayat Kasatker.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1; foreach ($kasatkerList as $k): ?>
                                        <tr>
                                            <td class="text-center font-weight-bold"><?= $no++; ?></td>
                                            <td>
                                                <strong class="text-primary"><?= esc($k['nama']); ?></strong>
                                                <div class="small text-muted">NIP. <?= esc($k['nip'] ?? '-'); ?></div>
                                            </td>
                                            <td class="small"><?= esc($k['jabatan']); ?></td>
                                            <td>
                                                <div class="small">
                                                    <i class="far fa-calendar-alt mr-1 text-primary"></i> 
                                                    <strong>Mulai:</strong> <?= ! empty($k['periode_mulai']) ? date('d/m/Y', strtotime($k['periode_mulai'])) : '<span class="text-muted">Awal Masa</span>'; ?><br>
                                                    <i class="far fa-calendar-check mr-1 text-success"></i> 
                                                    <strong>Sampai:</strong> <?= ! empty($k['periode_selesai']) ? date('d/m/Y', strtotime($k['periode_selesai'])) : '<span class="badge badge-success">Sekarang / Seterusnya</span>'; ?>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-<?= $k['status_jabatan'] === 'definitif' ? 'info' : 'warning'; ?> text-uppercase px-2 py-1">
                                                    <?= esc($k['status_jabatan']); ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ((int) $k['is_active'] === 1): ?>
                                                    <span class="badge badge-success px-2 py-1"><i class="fas fa-check"></i> Ya</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary px-2 py-1">Tidak</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($menuPermissions['edit'] ?? false): ?>
                                                    <button type="button" class="btn btn-warning btn-xs shadow-sm" onclick='editKasatker(<?= json_encode($k); ?>)' title="Ubah">
                                                        <i class="fas fa-pencil-alt"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <?php if ($menuPermissions['delete'] ?? false): ?>
                                                    <a href="<?= site_url('admin/inventaris/pengaturan/kasatker/' . $k['id'] . '/delete'); ?>" class="btn btn-danger btn-xs shadow-sm" onclick="return confirm('Hapus riwayat Kasatker ini?');" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                <?php endif; ?>
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
</div>

<!-- ================= MODAL KOP SURAT ================= -->
<div class="modal fade" id="modalKopSurat" tabindex="-1" role="dialog" aria-labelledby="modalKopSuratLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form action="<?= site_url('admin/inventaris/pengaturan/kop/save'); ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field(); ?>
            <input type="hidden" id="kop_id" name="id" value="0">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalKopSuratLabel"><i class="fas fa-file-image mr-1"></i> Form Kop Surat BMN</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="kop_nama" class="font-weight-bold">Nama / Judul Kop Surat <span class="text-danger">*</span></label>
                        <input type="text" id="kop_nama" name="nama_kop" class="form-control" placeholder="Contoh: Kop Surat Kementerian Pekerjaan Umum" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="kop_berlaku_dari" class="font-weight-bold">Berlaku Dari (Tanggal Mulai)</label>
                            <input type="date" id="kop_berlaku_dari" name="berlaku_dari" class="form-control">
                            <small class="text-muted">Kosongkan jika berlaku sejak awal pendirian satker.</small>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="kop_berlaku_sampai" class="font-weight-bold">Berlaku Sampai (Tanggal Akhir)</label>
                            <input type="date" id="kop_berlaku_sampai" name="berlaku_sampai" class="form-control">
                            <small class="text-muted">Kosongkan jika kop ini masih aktif digunakan seterusnya.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="kop_image_file" class="font-weight-bold">File Gambar Kop Surat <span id="kop_file_required" class="text-danger">*</span></label>
                        <input type="file" id="kop_image_file" name="image_file" class="form-control-file border p-1 rounded" accept="image/*">
                        <div id="kop_preview_container" class="mt-2 text-center p-2 bg-light border rounded" style="display: none;">
                            <small class="d-block text-muted mb-1">Pratinjau Gambar Saat Ini:</small>
                            <img id="kop_preview_img" src="" alt="Preview Kop" style="max-width: 100%; max-height: 80px; object-fit: contain;">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="kop_keterangan" class="font-weight-bold">Keterangan / Catatan</label>
                        <textarea id="kop_keterangan" name="keterangan" class="form-control" rows="2" placeholder="Catatan nomenklatur kementerian, dll."></textarea>
                    </div>

                    <div class="form-check">
                        <input type="checkbox" id="kop_is_active" name="is_active" value="1" class="form-check-input" checked>
                        <label class="form-check-label font-weight-bold" for="kop_is_active">Aktifkan Kop Surat ini</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary font-weight-bold"><i class="fas fa-save mr-1"></i> Simpan Kop Surat</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL KASATKER ================= -->
<div class="modal fade" id="modalKasatker" tabindex="-1" role="dialog" aria-labelledby="modalKasatkerLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form action="<?= site_url('admin/inventaris/pengaturan/kasatker/save'); ?>" method="post">
            <?= csrf_field(); ?>
            <input type="hidden" id="kasatker_id" name="id" value="0">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title font-weight-bold" id="modalKasatkerLabel"><i class="fas fa-user-tie mr-1"></i> Form Riwayat Pejabat Kasatker (KPB)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-7 form-group">
                            <label for="kasatker_nama" class="font-weight-bold">Nama Lengkap &amp; Gelar Pejabat <span class="text-danger">*</span></label>
                            <input type="text" id="kasatker_nama" name="nama" class="form-control" placeholder="Contoh: Muhammad Yudi Prasetya, ST." required>
                        </div>
                        <div class="col-md-5 form-group">
                            <label for="kasatker_nip" class="font-weight-bold">NIP</label>
                            <input type="text" id="kasatker_nip" name="nip" class="form-control" placeholder="Contoh: 198002142014121002">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="kasatker_jabatan" class="font-weight-bold">Jabatan Resmi <span class="text-danger">*</span></label>
                        <input type="text" id="kasatker_jabatan" name="jabatan" class="form-control" value="Kepala Satuan Kerja Pelaksanaan Prasarana Strategis Riau selaku Kuasa Pengguna Barang" required>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="kasatker_status_jabatan" class="font-weight-bold">Status Jabatan</label>
                            <select id="kasatker_status_jabatan" name="status_jabatan" class="form-control">
                                <option value="definitif">Definitif</option>
                                <option value="plt">Plt. (Pelaksana Tugas)</option>
                                <option value="plh">Plh. (Pelaksana Harian)</option>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="kasatker_periode_mulai" class="font-weight-bold">TMT Mulai Menjabat</label>
                            <input type="date" id="kasatker_periode_mulai" name="periode_mulai" class="form-control">
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="kasatker_periode_selesai" class="font-weight-bold">TMT Akhir Menjabat</label>
                            <input type="date" id="kasatker_periode_selesai" name="periode_selesai" class="form-control">
                            <small class="text-muted">Kosongkan jika masih aktif menjabat.</small>
                        </div>
                    </div>

                    <div class="form-check">
                        <input type="checkbox" id="kasatker_is_active" name="is_active" value="1" class="form-check-input" checked>
                        <label class="form-check-label font-weight-bold" for="kasatker_is_active">Aktifkan data pejabat ini</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning font-weight-bold text-dark"><i class="fas fa-save mr-1"></i> Simpan Pejabat</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function resetFormKop() {
    document.getElementById('kop_id').value = '0';
    document.getElementById('kop_nama').value = '';
    document.getElementById('kop_berlaku_dari').value = '';
    document.getElementById('kop_berlaku_sampai').value = '';
    document.getElementById('kop_image_file').value = '';
    document.getElementById('kop_image_file').required = true;
    document.getElementById('kop_keterangan').value = '';
    document.getElementById('kop_is_active').checked = true;
    document.getElementById('kop_preview_container').style.display = 'none';
    document.getElementById('modalKopSuratLabel').innerHTML = '<i class="fas fa-plus-circle mr-1"></i> Tambah Kop Surat BMN';
}

function editKop(data) {
    document.getElementById('kop_id').value = data.id || 0;
    document.getElementById('kop_nama').value = data.nama_kop || '';
    document.getElementById('kop_berlaku_dari').value = data.berlaku_dari || '';
    document.getElementById('kop_berlaku_sampai').value = data.berlaku_sampai || '';
    document.getElementById('kop_image_file').value = '';
    document.getElementById('kop_image_file').required = false;
    document.getElementById('kop_keterangan').value = data.keterangan || '';
    document.getElementById('kop_is_active').checked = (parseInt(data.is_active) === 1);

    if (data.image_url) {
        document.getElementById('kop_preview_img').src = '<?= base_url(); ?>/' + data.image_url.replace(/^\//, '');
        document.getElementById('kop_preview_container').style.display = 'block';
    } else {
        document.getElementById('kop_preview_container').style.display = 'none';
    }

    document.getElementById('modalKopSuratLabel').innerHTML = '<i class="fas fa-pencil-alt mr-1"></i> Ubah Kop Surat BMN';
    $('#modalKopSurat').modal('show');
}

function resetFormKasatker() {
    document.getElementById('kasatker_id').value = '0';
    document.getElementById('kasatker_nama').value = '';
    document.getElementById('kasatker_nip').value = '';
    document.getElementById('kasatker_jabatan').value = 'Kepala Satuan Kerja Pelaksanaan Prasarana Strategis Riau selaku Kuasa Pengguna Barang';
    document.getElementById('kasatker_status_jabatan').value = 'definitif';
    document.getElementById('kasatker_periode_mulai').value = '';
    document.getElementById('kasatker_periode_selesai').value = '';
    document.getElementById('kasatker_is_active').checked = true;
    document.getElementById('modalKasatkerLabel').innerHTML = '<i class="fas fa-plus-circle mr-1"></i> Tambah Pejabat Kasatker';
}

function editKasatker(data) {
    document.getElementById('kasatker_id').value = data.id || 0;
    document.getElementById('kasatker_nama').value = data.nama || '';
    document.getElementById('kasatker_nip').value = data.nip || '';
    document.getElementById('kasatker_jabatan').value = data.jabatan || '';
    document.getElementById('kasatker_status_jabatan').value = data.status_jabatan || 'definitif';
    document.getElementById('kasatker_periode_mulai').value = data.periode_mulai || '';
    document.getElementById('kasatker_periode_selesai').value = data.periode_selesai || '';
    document.getElementById('kasatker_is_active').checked = (parseInt(data.is_active) === 1);

    document.getElementById('modalKasatkerLabel').innerHTML = '<i class="fas fa-pencil-alt mr-1"></i> Ubah Data Pejabat Kasatker';
    $('#modalKasatker').modal('show');
}
</script>
<?= $this->endSection(); ?>
