<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">

    <!-- Summary Widgets -->
    <div class="row mb-3">
        <div class="col-12 col-sm-6 col-md-4">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #17a2b8;">
                <span class="info-box-icon bg-info elevation-1" style="border-radius: 8px;"><i class="fas fa-door-open"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Total Ruangan Terdata</span>
                    <span class="info-box-number text-dark" style="font-size: 1.35rem;"><?= number_format((int) ($totalRuangan ?? 0)); ?> Ruangan</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #28a745;">
                <span class="info-box-icon bg-success elevation-1" style="border-radius: 8px;"><i class="fas fa-check-double"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Aset Berlokasi (DBR)</span>
                    <span class="info-box-number text-success" style="font-size: 1.35rem;"><?= number_format((int) ($totalAsetBerlokasi ?? 0)); ?> Aset</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #ffc107;">
                <span class="info-box-icon bg-warning elevation-1" style="border-radius: 8px;"><i class="fas fa-box-open"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Belum Masuk Ruangan</span>
                    <span class="info-box-number text-warning" style="font-size: 1.35rem;"><?= number_format((int) ($totalAsetBelum ?? 0)); ?> Aset</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Card -->
    <div class="card shadow-sm" style="border: 1px solid #e9eef5; border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white py-3" style="border-bottom: 1px solid #e9eef5;">
            <div class="d-flex flex-wrap justify-content-between align-items-center w-100">
                <div>
                    <h3 class="card-title mb-0 font-weight-bold text-dark" style="font-size: 1.15rem; line-height: 1.8;">
                        <i class="fas fa-door-open text-primary mr-2"></i>Daftar Ruangan (DBR)
                    </h3>
                    <small class="text-muted d-block mt-1">Master ruangan kantor Satker PPS Riau, alokasi penempatan barang, dan cetak lembar resmi DBR</small>
                </div>
                <div class="card-tools d-flex align-items-center m-0" style="gap: 8px;">
                    <a href="<?= site_url('admin/inventaris/satker'); ?>" class="btn btn-outline-secondary btn-sm px-3 shadow-sm" style="border-radius: 6px;">
                        <i class="fas fa-boxes mr-1"></i> Daftar Barang Inventaris
                    </a>
                    <?php if (! empty($can_add)): ?>
                        <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm" data-toggle="modal" data-target="#modal-tambah-ruangan" style="border-radius: 6px;">
                            <i class="fas fa-plus mr-1"></i> Tambah Ruangan
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered table-striped w-100 js-datatable" style="border-radius: 8px;">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 45px;" class="text-center align-middle">#</th>
                            <th style="width: 130px;" class="text-center align-middle">Kode Ruangan</th>
                            <th class="align-middle">Nama Ruangan</th>
                            <th class="align-middle">Penanggung Jawab Ruangan</th>
                            <th class="text-center align-middle" style="width: 140px;">Total Barang</th>
                            <th style="width: 250px;" class="text-center align-middle">Aksi & Dokumen DBR</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach (($ruanganList ?? []) as $r): ?>
                            <tr>
                                <td class="text-center align-middle"><?= $no++; ?></td>
                                <td class="text-center align-middle font-weight-bold text-primary">
                                    <span class="badge badge-light border px-2 py-1" style="font-size: 0.9rem;">
                                        <?= esc($r['kode_ruangan']); ?>
                                    </span>
                                </td>
                                <td class="align-middle">
                                    <span class="font-weight-bold text-dark d-block" style="font-size: 1rem;"><?= esc($r['nama_ruangan']); ?></span>
                                    <?php if (! empty($r['lokasi_lantai'])): ?>
                                        <small class="text-muted"><i class="fas fa-layer-group mr-1"></i> <?= esc($r['lokasi_lantai']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="align-middle">
                                    <?php if (! empty($r['penanggung_jawab_nama'])): ?>
                                        <span class="font-weight-bold text-dark d-block"><?= esc($r['penanggung_jawab_nama']); ?></span>
                                        <small class="text-muted">NIP. <?= esc($r['penanggung_jawab_nip'] ?: '-'); ?></small>
                                    <?php else: ?>
                                        <span class="text-muted font-italic">- Belum Ditentukan -</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center align-middle">
                                    <span class="badge badge-success px-2 py-1 font-weight-bold" style="font-size: 0.85rem;">
                                        <?= number_format((int) ($r['total_unit'] ?? 0)); ?> Unit
                                    </span>
                                    <small class="text-muted d-block mt-1"><?= number_format((int) ($r['total_aset'] ?? 0)); ?> Item Barang</small>
                                </td>
                                <td class="text-center align-middle" style="white-space: nowrap;">
                                    <div class="btn-group" role="group" style="gap: 4px;">
                                        <a href="<?= site_url('admin/inventaris/dbr/' . $r['id']); ?>" class="btn btn-info btn-xs px-2 py-1 shadow-sm" style="border-radius: 4px;" title="Kelola Isi Barang Ruangan">
                                            <i class="fas fa-boxes-stacked mr-1"></i> Kelola Barang
                                        </a>
                                        <?php if (! empty($can_export)): ?>
                                            <a href="<?= site_url('admin/inventaris/dbr/' . $r['id'] . '/cetak-pdf'); ?>" target="_blank" class="btn btn-danger btn-xs px-2 py-1 shadow-sm" style="border-radius: 4px;" title="Cetak PDF Rekap DBR">
                                                <i class="fas fa-file-pdf mr-1"></i> PDF
                                            </a>
                                            <a href="<?= site_url('admin/inventaris/dbr/' . $r['id'] . '/export-excel'); ?>" class="btn btn-success btn-xs px-2 py-1 shadow-sm" style="border-radius: 4px;" title="Export Excel Rekap DBR">
                                                <i class="fas fa-file-excel mr-1"></i> Excel
                                            </a>
                                        <?php endif; ?>
                                        <?php if (! empty($can_edit)): ?>
                                            <button
                                                type="button"
                                                class="btn btn-outline-primary btn-xs px-2 py-1 btn-edit-ruangan"
                                                data-toggle="modal"
                                                data-target="#modal-edit-ruangan"
                                                data-id="<?= esc((string) $r['id'], 'attr'); ?>"
                                                data-kode="<?= esc((string) $r['kode_ruangan'], 'attr'); ?>"
                                                data-nama="<?= esc((string) $r['nama_ruangan'], 'attr'); ?>"
                                                data-pegawai="<?= esc((string) ($r['pegawai_id'] ?? ''), 'attr'); ?>"
                                                data-pj-nama="<?= esc((string) ($r['penanggung_jawab_nama'] ?? ''), 'attr'); ?>"
                                                data-pj-nip="<?= esc((string) ($r['penanggung_jawab_nip'] ?? ''), 'attr'); ?>"
                                                data-lantai="<?= esc((string) ($r['lokasi_lantai'] ?? ''), 'attr'); ?>"
                                                data-keterangan="<?= esc((string) ($r['keterangan'] ?? ''), 'attr'); ?>"
                                                style="border-radius: 4px;"
                                                title="Edit Ruangan"
                                            >
                                                <i class="fas fa-pen"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if (! empty($can_delete)): ?>
                                            <button
                                                type="button"
                                                class="btn btn-outline-danger btn-xs px-2 py-1 btn-delete-ruangan"
                                                data-toggle="modal"
                                                data-target="#modal-delete-ruangan"
                                                data-id="<?= esc((string) $r['id'], 'attr'); ?>"
                                                data-nama="<?= esc((string) $r['nama_ruangan'], 'attr'); ?>"
                                                style="border-radius: 4px;"
                                                title="Hapus Ruangan"
                                            >
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Ruangan -->
<?php if (! empty($can_add)): ?>
<div class="modal fade" id="modal-tambah-ruangan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.12);">
            <div class="modal-header bg-light py-3" style="border-bottom: 1px solid #e9eef5;">
                <h5 class="modal-title font-weight-bold text-dark" style="font-size: 1.1rem;">
                    <i class="fas fa-plus-circle text-primary mr-2"></i>Tambah Ruangan Baru
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('admin/inventaris/dbr/ruangan/tambah'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body py-4">
                    <div class="form-group">
                        <label class="font-weight-bold small text-dark">Kode Ruangan <span class="text-danger">*</span></label>
                        <input type="text" name="kode_ruangan" class="form-control" placeholder="Contoh: R.01 / 101" required style="border-radius: 6px;">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small text-dark">Nama Ruangan <span class="text-danger">*</span></label>
                        <input type="text" name="nama_ruangan" class="form-control" placeholder="Contoh: RUANG KASATKER, RUANG TATA USAHA" required style="border-radius: 6px;">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small text-dark">Pilih Pegawai Penanggung Jawab Ruangan</label>
                        <select name="pegawai_id" id="tambah-pegawai-select" class="form-control" style="border-radius: 6px;">
                            <option value="">-- Pilih dari Master Pegawai (Opsional) --</option>
                            <?php foreach ($pegawaiList as $p): ?>
                                <option value="<?= esc($p['id']); ?>" data-nama="<?= esc($p['nama']); ?>" data-nip="<?= esc($p['nip']); ?>">
                                    <?= esc($p['nama']); ?> (NIP. <?= esc($p['nip'] ?: '-'); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-dark">Nama Penanggung Jawab</label>
                            <input type="text" name="penanggung_jawab_nama" id="tambah-pj-nama" class="form-control" placeholder="Nama lengkap & gelar" style="border-radius: 6px;">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-dark">NIP Penanggung Jawab</label>
                            <input type="text" name="penanggung_jawab_nip" id="tambah-pj-nip" class="form-control" placeholder="NIP 18 digit" style="border-radius: 6px;">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small text-dark">Lantai / Lokasi Gedung</label>
                        <input type="text" name="lokasi_lantai" class="form-control" placeholder="Contoh: Lantai 1, Gedung Utama" style="border-radius: 6px;">
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold small text-dark">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan tambahan..." style="border-radius: 6px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-3" style="border-top: 1px solid #e9eef5;">
                    <button type="button" class="btn btn-secondary px-3" data-dismiss="modal" style="border-radius: 6px;">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm" style="border-radius: 6px;">
                        <i class="fas fa-save mr-1"></i> Simpan Ruangan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Edit Ruangan -->
<?php if (! empty($can_edit)): ?>
<div class="modal fade" id="modal-edit-ruangan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.12);">
            <div class="modal-header bg-light py-3" style="border-bottom: 1px solid #e9eef5;">
                <h5 class="modal-title font-weight-bold text-dark" style="font-size: 1.1rem;">
                    <i class="fas fa-edit text-primary mr-2"></i>Ubah Data Ruangan
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-edit-ruangan" action="" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body py-4">
                    <div class="form-group">
                        <label class="font-weight-bold small text-dark">Kode Ruangan <span class="text-danger">*</span></label>
                        <input type="text" id="edit-kode" name="kode_ruangan" class="form-control" required style="border-radius: 6px;">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small text-dark">Nama Ruangan <span class="text-danger">*</span></label>
                        <input type="text" id="edit-nama" name="nama_ruangan" class="form-control" required style="border-radius: 6px;">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small text-dark">Pilih Pegawai Penanggung Jawab Ruangan</label>
                        <select name="pegawai_id" id="edit-pegawai-select" class="form-control" style="border-radius: 6px;">
                            <option value="">-- Pilih dari Master Pegawai (Opsional) --</option>
                            <?php foreach ($pegawaiList as $p): ?>
                                <option value="<?= esc($p['id']); ?>" data-nama="<?= esc($p['nama']); ?>" data-nip="<?= esc($p['nip']); ?>">
                                    <?= esc($p['nama']); ?> (NIP. <?= esc($p['nip'] ?: '-'); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-dark">Nama Penanggung Jawab</label>
                            <input type="text" name="penanggung_jawab_nama" id="edit-pj-nama" class="form-control" style="border-radius: 6px;">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-dark">NIP Penanggung Jawab</label>
                            <input type="text" name="penanggung_jawab_nip" id="edit-pj-nip" class="form-control" style="border-radius: 6px;">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small text-dark">Lantai / Lokasi Gedung</label>
                        <input type="text" id="edit-lantai" name="lokasi_lantai" class="form-control" style="border-radius: 6px;">
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold small text-dark">Keterangan</label>
                        <textarea id="edit-keterangan" name="keterangan" class="form-control" rows="2" style="border-radius: 6px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-3" style="border-top: 1px solid #e9eef5;">
                    <button type="button" class="btn btn-secondary px-3" data-dismiss="modal" style="border-radius: 6px;">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm" style="border-radius: 6px;">
                        <i class="fas fa-save mr-1"></i> Perbarui Ruangan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Hapus Ruangan -->
<?php if (! empty($can_delete)): ?>
<div class="modal fade" id="modal-delete-ruangan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.12);">
            <div class="modal-header bg-danger text-white py-3">
                <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem;">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Konfirmasi Hapus Ruangan
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-delete-ruangan" action="" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body py-4 text-center">
                    <i class="fas fa-trash-alt text-danger mb-3" style="font-size: 3rem;"></i>
                    <p class="mb-1 text-dark font-weight-bold">Apakah Anda yakin ingin menghapus ruangan ini?</p>
                    <p id="delete-ruangan-name" class="text-primary font-weight-bold mb-2"></p>
                    <small class="text-muted">Barang yang ada di ruangan ini otomatis akan diubah statusnya menjadi "Belum berlokasi".</small>
                </div>
                <div class="modal-footer bg-light py-3" style="border-top: 1px solid #e9eef5;">
                    <button type="button" class="btn btn-secondary px-3" data-dismiss="modal" style="border-radius: 6px;">Batal</button>
                    <button type="submit" class="btn btn-danger px-4 shadow-sm" style="border-radius: 6px;">
                        <i class="fas fa-trash-alt mr-1"></i> Ya, Hapus Ruangan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-fill PJ name & NIP when selecting employee (Tambah)
    var selectTambah = document.getElementById('tambah-pegawai-select');
    if (selectTambah) {
        selectTambah.addEventListener('change', function() {
            var opt = this.options[this.selectedIndex];
            document.getElementById('tambah-pj-nama').value = opt.getAttribute('data-nama') || '';
            document.getElementById('tambah-pj-nip').value = opt.getAttribute('data-nip') || '';
        });
    }

    // Auto-fill PJ name & NIP when selecting employee (Edit)
    var selectEdit = document.getElementById('edit-pegawai-select');
    if (selectEdit) {
        selectEdit.addEventListener('change', function() {
            var opt = this.options[this.selectedIndex];
            document.getElementById('edit-pj-nama').value = opt.getAttribute('data-nama') || '';
            document.getElementById('edit-pj-nip').value = opt.getAttribute('data-nip') || '';
        });
    }

    function populateEditModal(btn) {
        if (!btn) return;
        var id = btn.getAttribute('data-id') || '';
        var form = document.getElementById('form-edit-ruangan');
        if (form && id) {
            form.action = '<?= site_url('admin/inventaris/dbr/ruangan'); ?>/' + id + '/ubah';
        }
        var editKode = document.getElementById('edit-kode');
        if (editKode) editKode.value = btn.getAttribute('data-kode') || '';
        
        var editNama = document.getElementById('edit-nama');
        if (editNama) editNama.value = btn.getAttribute('data-nama') || '';
        
        var editPjNama = document.getElementById('edit-pj-nama');
        if (editPjNama) editPjNama.value = btn.getAttribute('data-pj-nama') || '';
        
        var editPjNip = document.getElementById('edit-pj-nip');
        if (editPjNip) editPjNip.value = btn.getAttribute('data-pj-nip') || '';
        
        var editLantai = document.getElementById('edit-lantai');
        if (editLantai) editLantai.value = btn.getAttribute('data-lantai') || '';
        
        var editKeterangan = document.getElementById('edit-keterangan');
        if (editKeterangan) editKeterangan.value = btn.getAttribute('data-keterangan') || '';

        var pegId = btn.getAttribute('data-pegawai') || '';
        var pegSelect = document.getElementById('edit-pegawai-select');
        if (pegSelect) {
            pegSelect.value = pegId;
        }
    }

    function populateDeleteModal(btn) {
        if (!btn) return;
        var id = btn.getAttribute('data-id') || '';
        var nama = btn.getAttribute('data-nama') || '';
        var form = document.getElementById('form-delete-ruangan');
        if (form && id) {
            form.action = '<?= site_url('admin/inventaris/dbr/ruangan'); ?>/' + id + '/hapus';
        }
        var nameEl = document.getElementById('delete-ruangan-name');
        if (nameEl) {
            nameEl.textContent = '"' + nama + '"';
        }
    }

    // Delegated click listener (survives DataTables redraw, pagination, and sorting)
    document.addEventListener('click', function(e) {
        var editBtn = e.target.closest('.btn-edit-ruangan');
        if (editBtn) {
            populateEditModal(editBtn);
        }

        var deleteBtn = e.target.closest('.btn-delete-ruangan');
        if (deleteBtn) {
            populateDeleteModal(deleteBtn);
        }
    });

    // Also hook Bootstrap's show.bs.modal event via jQuery if available
    if (typeof $ !== 'undefined') {
        $('#modal-edit-ruangan').on('show.bs.modal', function(event) {
            var btn = event.relatedTarget;
            if (btn) {
                populateEditModal(btn);
            }
        });

        $('#modal-delete-ruangan').on('show.bs.modal', function(event) {
            var btn = event.relatedTarget;
            if (btn) {
                populateDeleteModal(btn);
            }
        });
    }
});
</script>
<?= $this->endSection(); ?>
