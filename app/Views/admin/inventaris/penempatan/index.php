<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">

    <!-- Summary Widgets -->
    <div class="row mb-3">
        <div class="col-12 col-sm-6 col-md-4">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #0F2A4A;">
                <span class="info-box-icon elevation-1" style="border-radius: 8px; background-color: #0F2A4A; color: white;"><i class="fas fa-boxes"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Total Aset BMN</span>
                    <span class="info-box-number text-dark" style="font-size: 1.35rem;"><?= number_format((int) ($totalAset ?? 0)); ?></span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #28a745;">
                <span class="info-box-icon bg-success elevation-1" style="border-radius: 8px;"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Sudah Ditempatkan</span>
                    <span class="info-box-number text-success" style="font-size: 1.35rem;"><?= number_format((int) ($totalSudah ?? 0)); ?></span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #ffc107;">
                <span class="info-box-icon bg-warning elevation-1" style="border-radius: 8px; color: #fff;"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Belum Ditempatkan</span>
                    <span class="info-box-number text-warning" style="font-size: 1.35rem;"><?= number_format((int) ($totalBelum ?? 0)); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm mb-3" style="border-radius: 10px;">
        <div class="card-body py-2">
            <form method="GET" action="<?= site_url('admin/inventaris/penempatan'); ?>" class="form-row align-items-center">
                <div class="col-12 col-sm-3 my-1">
                    <select name="paket_id" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">-- Semua Paket Pekerjaan --</option>
                        <?php foreach ($pakets as $p): ?>
                            <option value="<?= esc($p['id']); ?>" <?= ($filterPaketId === (string) $p['id']) ? 'selected' : ''; ?>>
                                <?= esc($p['nama_paket']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-sm-3 my-1">
                    <select name="sekolah_id" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">-- Semua Sekolah --</option>
                        <?php foreach ($sekolahs as $s): ?>
                            <option value="<?= esc($s['npsn']); ?>" <?= ($filterSekolahId === (string) $s['npsn']) ? 'selected' : ''; ?>>
                                <?= esc($s['nama']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-sm-2 my-1">
                    <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">-- Semua Status --</option>
                        <option value="sudah_ditempatkan" <?= ($filterStatus === 'sudah_ditempatkan') ? 'selected' : ''; ?>>Sudah Ditempatkan</option>
                        <option value="belum_ditempatkan" <?= ($filterStatus === 'belum_ditempatkan') ? 'selected' : ''; ?>>Belum Ditempatkan</option>
                    </select>
                </div>
                <div class="col-12 col-sm-3 my-1">
                    <div class="input-group input-group-sm">
                        <input type="text" name="keyword" class="form-control" placeholder="Cari nama, kode, NUP, lokasi..." value="<?= esc($keyword); ?>">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-1 my-1 text-right">
                    <a href="<?= site_url('admin/inventaris/penempatan'); ?>" class="btn btn-sm btn-outline-secondary" title="Reset Filter"><i class="fas fa-redo"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="card shadow-sm" style="border-radius: 10px;">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title font-weight-bold mb-0 text-dark">
                <i class="fas fa-map-marked-alt mr-2 text-primary"></i> Daftar Penempatan Aset BMN
            </h5>
            <div class="card-tools">
                <?php if (! empty($can_add)): ?>
                    <button type="button" class="btn btn-sm btn-outline-primary mr-1" data-toggle="modal" data-target="#modalTambahGedung">
                        <i class="fas fa-plus mr-1"></i> Tambah Gedung
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-info" data-toggle="modal" data-target="#modalTambahRuangan">
                        <i class="fas fa-plus mr-1"></i> Tambah Ruangan
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-striped mb-0 text-sm">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 40px;" class="text-center">No</th>
                        <th>Kode & NUP</th>
                        <th>Kode Register (QR)</th>
                        <th>Nama Barang / Merk</th>
                        <th>Kondisi</th>
                        <th>Status</th>
                        <th>Lokasi Fisik (Sekolah > Gedung > Ruang)</th>
                        <th>Tgl Penempatan</th>
                        <th class="text-center" style="width: 100px;">Dokumentasi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-3x mb-2 text-black-50 d-block"></i>
                                Tidak ada data penempatan aset BMN yang sesuai filter.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $idx => $it): ?>
                            <?php $isPlaced = ($it['status_penempatan'] === 'sudah_ditempatkan'); ?>
                            <tr>
                                <td class="text-center font-weight-bold"><?= $idx + 1; ?></td>
                                <td>
                                    <span class="badge badge-light border">Kd: <?= esc($it['kode_barang']); ?></span>
                                    <br>
                                    <span class="badge badge-warning">NUP: <?= esc($it['nup']); ?></span>
                                </td>
                                <td>
                                    <?php if (! empty($it['kode_register'])): ?>
                                        <code class="text-dark bg-light px-2 py-1 rounded" style="font-size: 0.82rem;">
                                            <i class="fas fa-qrcode mr-1 text-primary"></i><?= esc($it['kode_register']); ?>
                                        </code>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= esc($it['nama_barang']); ?></strong>
                                    <?php if (! empty($it['merk_tipe'])): ?>
                                        <div class="text-muted text-xs"><?= esc($it['merk_tipe']); ?> (Thn <?= esc($it['tahun_perolehan'] ?? '-'); ?>)</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                        $kondisi = strtolower($it['kondisi'] ?? 'baik');
                                        $badgeClass = ($kondisi === 'baik') ? 'badge-success' : (($kondisi === 'rusak_ringan') ? 'badge-warning' : 'badge-danger');
                                    ?>
                                    <span class="badge <?= $badgeClass; ?>"><?= strtoupper(str_replace('_', ' ', $kondisi)); ?></span>
                                </td>
                                <td>
                                    <?php if ($isPlaced): ?>
                                        <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> Ditempatkan</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Belum Ditempatkan</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($isPlaced && ! empty($it['nama_sekolah'])): ?>
                                        <div class="font-weight-bold text-dark"><i class="fas fa-school mr-1 text-info"></i> <?= esc($it['nama_sekolah']); ?></div>
                                        <div class="text-muted text-xs">
                                            <i class="fas fa-building mr-1"></i> <?= esc($it['nama_gedung'] ?? '-'); ?> 
                                            &bull; <i class="fas fa-door-open mr-1"></i> <?= esc($it['nama_ruangan'] ?? '-'); ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted font-italic">- Belum Dialokasikan -</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (! empty($it['tanggal_penempatan'])): ?>
                                        <?= date('d/m/Y H:i', strtotime($it['tanggal_penempatan'])); ?>
                                        <div class="text-muted text-xs">Oleh: <?= esc($it['petugas_penempatan'] ?? 'Mobile'); ?></div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (! empty($it['penempatan'])): ?>
                                        <button type="button" class="btn btn-xs btn-outline-primary btn-view-foto" 
                                                data-asli-1="<?= ! empty($it['penempatan']['foto_asli_1']) ? base_url($it['penempatan']['foto_asli_1']) : ''; ?>"
                                                data-asli-2="<?= ! empty($it['penempatan']['foto_asli_2']) ? base_url($it['penempatan']['foto_asli_2']) : ''; ?>"
                                                data-asli-3="<?= ! empty($it['penempatan']['foto_asli_3']) ? base_url($it['penempatan']['foto_asli_3']) : ''; ?>"
                                                data-asli-4="<?= ! empty($it['penempatan']['foto_asli_4']) ? base_url($it['penempatan']['foto_asli_4']) : ''; ?>"
                                                data-wm-1="<?= ! empty($it['penempatan']['foto_wm_1']) ? base_url($it['penempatan']['foto_wm_1']) : ''; ?>"
                                                data-wm-2="<?= ! empty($it['penempatan']['foto_wm_2']) ? base_url($it['penempatan']['foto_wm_2']) : ''; ?>"
                                                data-wm-3="<?= ! empty($it['penempatan']['foto_wm_3']) ? base_url($it['penempatan']['foto_wm_3']) : ''; ?>"
                                                data-wm-4="<?= ! empty($it['penempatan']['foto_wm_4']) ? base_url($it['penempatan']['foto_wm_4']) : ''; ?>"
                                                data-title="<?= esc($it['nama_barang']); ?>"
                                                title="Lihat Foto Fisik Lapangan">
                                            <i class="fas fa-images mr-1"></i> Foto
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted text-xs">-</span>
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

<!-- Modal Foto Galeri -->
<div class="modal fade" id="modalFotoGaleri" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius: 12px;">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title font-weight-bold" id="modalFotoTitle">Dokumentasi Fisik Aset</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted text-sm">Pilih tampilan foto:</span>
                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
                        <label class="btn btn-sm btn-outline-primary active">
                            <input type="radio" name="photoVersion" id="optWatermark" checked> Foto Watermark
                        </label>
                        <label class="btn btn-sm btn-outline-secondary">
                            <input type="radio" name="photoVersion" id="optAsli"> Foto Asli
                        </label>
                    </div>
                </div>
                <div class="row" id="photoGalleryContainer">
                    <!-- Foto dinamis via JS -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Gedung -->
<div class="modal fade" id="modalTambahGedung" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 12px;">
            <form action="<?= site_url('admin/inventaris/penempatan/gedung/tambah'); ?>" method="POST">
                <?= csrf_field(); ?>
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-building mr-1"></i> Tambah Master Gedung</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Pilih Sekolah *</label>
                        <select name="sekolah_id" class="form-control" required>
                            <option value="">-- Pilih Sekolah --</option>
                            <?php foreach ($sekolahs as $s): ?>
                                <option value="<?= esc($s['npsn']); ?>"><?= esc($s['nama']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Nama Gedung *</label>
                        <input type="text" name="nama_gedung" class="form-control" placeholder="Contoh: Gedung A, Gedung Lab Komputer" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Simpan Gedung</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tambah Ruangan -->
<div class="modal fade" id="modalTambahRuangan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 12px;">
            <form action="<?= site_url('admin/inventaris/penempatan/ruangan/tambah'); ?>" method="POST">
                <?= csrf_field(); ?>
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-door-open mr-1"></i> Tambah Master Ruangan</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Pilih Gedung *</label>
                        <select name="gedung_id" class="form-control" id="selectGedungForRuangan" required>
                            <option value="">-- Pilih Gedung --</option>
                            <!-- Gedung loaded dinamis -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Nama Ruangan *</label>
                        <input type="text" name="nama_ruangan" class="form-control" placeholder="Contoh: Ruang Guru, Lab Komputer 1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info btn-sm">Simpan Ruangan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
$(document).ready(function() {
    let currentAsli = [];
    let currentWm = [];

    $('.btn-view-foto').on('click', function() {
        currentAsli = [];
        currentWm = [];
        const title = $(this).data('title');
        $('#modalFotoTitle').text('Dokumentasi: ' + title);

        for (let i = 1; i <= 4; i++) {
            const a = $(this).data('asli-' + i);
            const w = $(this).data('wm-' + i);
            if (a) currentAsli.push(a);
            if (w) currentWm.push(w);
        }

        renderPhotos(true);
        $('#modalFotoGaleri').modal('show');
    });

    $('input[name="photoVersion"]').on('change', function() {
        const isWm = $('#optWatermark').is(':checked');
        renderPhotos(isWm);
    });

    function renderPhotos(isWm) {
        const list = isWm ? currentWm : currentAsli;
        const container = $('#photoGalleryContainer');
        container.empty();

        if (list.length === 0) {
            container.html('<div class="col-12 text-center text-muted py-4">Foto tidak tersedia.</div>');
            return;
        }

        list.forEach(function(url, idx) {
            const html = `
                <div class="col-6 col-md-6 mb-3">
                    <div class="border rounded p-1 shadow-sm bg-light text-center">
                        <a href="${url}" target="_blank">
                            <img src="${url}" class="img-fluid rounded" style="max-height: 240px; object-fit: cover;" alt="Foto BMN">
                        </a>
                        <div class="text-xs text-muted mt-1">Foto ${idx + 1} (${isWm ? 'Watermark' : 'Asli'})</div>
                    </div>
                </div>
            `;
            container.append(html);
        });
    }

    // Load list gedung ke dropdown modal ruangan
    $.get('<?= site_url('api/bmn/gedung?sekolah_id=0'); ?>', function(res) {
        // Optional
    });
});
</script>
<?= $this->endSection(); ?>
