<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm" style="border: 1px solid #e9eef5; border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white py-3" style="border-bottom: 1px solid #e9eef5;">
                    <h3 class="card-title mb-0 font-weight-bold text-dark" style="font-size: 1.15rem; line-height: 1.8;">
                        <i class="fas fa-wallet text-primary mr-2"></i>Master Mata Anggaran
                    </h3>
                    <?php if (! empty($can_add)): ?>
                        <div class="card-tools float-right">
                            <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm" data-toggle="modal" data-target="#modal-tambah-mata-anggaran" style="border-radius: 6px;">
                                <i class="fas fa-plus mr-1"></i> Tambah Mata Anggaran
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-striped w-100 js-datatable" style="border-radius: 8px;">
                            <thead>
                                <tr>
                                    <th style="width: 50px;" class="text-center">#</th>
                                    <th>Mata Anggaran</th>
                                    <th style="width: 240px;" class="text-center">Periode Berlaku</th>
                                    <th style="width: 140px;" class="text-center">Status</th>
                                    <?php if (! empty($can_edit) || ! empty($can_delete)): ?>
                                        <th style="width: 150px;" class="text-center">Aksi</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; foreach (($items ?? []) as $item): ?>
                                    <?php
                                        $dari = ! empty($item['berlaku_dari']) ? date('d M Y', strtotime($item['berlaku_dari'])) : '-';
                                        $hingga = ! empty($item['berlaku_hingga']) ? date('d M Y', strtotime($item['berlaku_hingga'])) : null;
                                        $isAktif = strtolower((string) ($item['status'] ?? '')) === 'aktif';
                                    ?>
                                    <tr>
                                        <td class="text-center align-middle"><?= esc((string) $i++); ?></td>
                                        <td class="align-middle font-weight-bold" style="font-size: 0.95rem;">
                                            <?= esc((string) ($item['mata_anggaran'] ?? '-')); ?>
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="badge badge-light p-2" style="font-size: 0.85rem; border: 1px solid #dee2e6;">
                                                <i class="far fa-calendar-alt text-primary mr-1"></i>
                                                <?= esc($dari); ?>
                                                <i class="fas fa-arrow-right mx-1 text-muted" style="font-size: 0.75rem;"></i>
                                                <?php if ($hingga !== null): ?>
                                                    <?= esc($hingga); ?>
                                                <?php else: ?>
                                                    <span class="badge badge-info font-weight-normal">Seterusnya / Tanpa Batas</span>
                                                <?php endif; ?>
                                            </span>
                                        </td>
                                        <td class="text-center align-middle">
                                            <?php if ($can_edit): ?>
                                                <form action="<?= site_url('/admin/master/mata-anggaran/' . esc((string) ($item['id'] ?? ''), 'url') . '/status'); ?>" method="post" style="display: inline-block;">
                                                    <?= csrf_field(); ?>
                                                    <input type="hidden" name="status" value="<?= $isAktif ? 'tidak_aktif' : 'aktif'; ?>">
                                                    <button type="submit" class="btn btn-sm <?= $isAktif ? 'btn-success' : 'btn-secondary'; ?> px-3 py-1 shadow-sm" style="border-radius: 20px; font-size: 0.8rem;" title="Klik untuk mengubah status">
                                                        <i class="fas <?= $isAktif ? 'fa-check-circle' : 'fa-times-circle'; ?> mr-1"></i>
                                                        <?= $isAktif ? 'Aktif' : 'Tidak Aktif'; ?>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="badge <?= $isAktif ? 'badge-success' : 'badge-secondary'; ?> px-3 py-2" style="border-radius: 20px; font-size: 0.8rem;">
                                                    <?= $isAktif ? 'Aktif' : 'Tidak Aktif'; ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <?php if (! empty($can_edit) || ! empty($can_delete)): ?>
                                            <td class="text-center align-middle" style="white-space: nowrap;">
                                                <div class="btn-group" role="group" style="gap: 5px;">
                                                    <?php if (! empty($can_edit)): ?>
                                                        <button
                                                            type="button"
                                                            class="btn btn-outline-primary btn-xs px-2 py-1"
                                                            data-toggle="modal"
                                                            data-target="#modal-ubah-mata-anggaran"
                                                            data-id="<?= esc((string) ($item['id'] ?? ''), 'attr'); ?>"
                                                            data-mata_anggaran="<?= esc((string) ($item['mata_anggaran'] ?? ''), 'attr'); ?>"
                                                            data-berlaku_dari="<?= esc((string) ($item['berlaku_dari'] ?? ''), 'attr'); ?>"
                                                            data-berlaku_hingga="<?= esc((string) ($item['berlaku_hingga'] ?? ''), 'attr'); ?>"
                                                            data-status="<?= esc((string) ($item['status'] ?? 'aktif'), 'attr'); ?>"
                                                            style="border-radius: 4px; font-weight: 500;"
                                                            title="Edit"
                                                        >
                                                            <i class="fas fa-pen mr-1"></i> Edit
                                                        </button>
                                                    <?php endif; ?>
                                                    <?php if (! empty($can_delete)): ?>
                                                        <button
                                                            type="button"
                                                            class="btn btn-outline-danger btn-xs px-2 py-1"
                                                            data-toggle="modal"
                                                            data-target="#modal-hapus-mata-anggaran"
                                                            data-id="<?= esc((string) ($item['id'] ?? ''), 'attr'); ?>"
                                                            data-mata_anggaran="<?= esc((string) ($item['mata_anggaran'] ?? ''), 'attr'); ?>"
                                                            style="border-radius: 4px; font-weight: 500;"
                                                            title="Hapus"
                                                        >
                                                            <i class="fas fa-trash-alt mr-1"></i> Hapus
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (! empty($can_add)): ?>
<div class="modal fade" id="modal-tambah-mata-anggaran" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header bg-light py-3" style="border-bottom: 1px solid #e9eef5;">
                <h5 class="modal-title font-weight-bold text-dark" style="font-size: 1.05rem;">
                    <i class="fas fa-plus-circle text-primary mr-2"></i>Tambah Mata Anggaran
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('/admin/master/mata-anggaran/tambah'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body py-3">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary mb-1" style="font-size: 0.88rem;">Mata Anggaran <span class="text-danger">*</span></label>
                        <input type="text" name="mata_anggaran" class="form-control" required placeholder="Contoh: 521211 - Belanja Bahan" style="border-radius: 6px;" value="<?= old('mata_anggaran'); ?>">
                        <small class="text-muted">Mata anggaran harus unik dan tidak boleh sama dengan mata anggaran lainnya.</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold text-secondary mb-1" style="font-size: 0.88rem;">Berlaku Dari <span class="text-danger">*</span></label>
                            <input type="date" name="berlaku_dari" class="form-control" required style="border-radius: 6px;" value="<?= old('berlaku_dari', date('Y-m-d')); ?>">
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold text-secondary mb-1" style="font-size: 0.88rem;">Berlaku Hingga</label>
                            <input type="date" name="berlaku_hingga" class="form-control" style="border-radius: 6px;" value="<?= old('berlaku_hingga'); ?>">
                            <small class="text-muted">Boleh dikosongkan (Catatan: Hanya 1 data yang periode hingganya boleh kosong).</small>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-secondary mb-1" style="font-size: 0.88rem;">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-control" style="border-radius: 6px;">
                            <option value="aktif" <?= old('status') === 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                            <option value="tidak_aktif" <?= old('status') === 'tidak_aktif' ? 'selected' : ''; ?>>Tidak Aktif</option>
                        </select>
                        <small class="text-muted">Jika diset Aktif, mata anggaran lain akan otomatis menjadi Tidak Aktif (hanya 1 yang aktif).</small>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2" style="border-top: 1px solid #e9eef5;">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal" style="border-radius: 6px;">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3" style="border-radius: 6px;">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (! empty($can_edit)): ?>
<div class="modal fade" id="modal-ubah-mata-anggaran" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header bg-light py-3" style="border-bottom: 1px solid #e9eef5;">
                <h5 class="modal-title font-weight-bold text-dark" style="font-size: 1.05rem;">
                    <i class="fas fa-edit text-warning mr-2"></i>Ubah Mata Anggaran
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-ubah-mata-anggaran" action="" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body py-3">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary mb-1" style="font-size: 0.88rem;">Mata Anggaran <span class="text-danger">*</span></label>
                        <input type="text" id="edit_mata_anggaran" name="mata_anggaran" class="form-control" required style="border-radius: 6px;">
                        <small class="text-muted">Mata anggaran harus unik dan tidak boleh sama dengan mata anggaran lainnya.</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold text-secondary mb-1" style="font-size: 0.88rem;">Berlaku Dari <span class="text-danger">*</span></label>
                            <input type="date" id="edit_berlaku_dari" name="berlaku_dari" class="form-control" required style="border-radius: 6px;">
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold text-secondary mb-1" style="font-size: 0.88rem;">Berlaku Hingga</label>
                            <input type="date" id="edit_berlaku_hingga" name="berlaku_hingga" class="form-control" style="border-radius: 6px;">
                            <small class="text-muted">Boleh dikosongkan (Catatan: Hanya 1 data yang periode hingganya boleh kosong).</small>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-secondary mb-1" style="font-size: 0.88rem;">Status <span class="text-danger">*</span></label>
                        <select id="edit_status" name="status" class="form-control" style="border-radius: 6px;">
                            <option value="aktif">Aktif</option>
                            <option value="tidak_aktif">Tidak Aktif</option>
                        </select>
                        <small class="text-muted">Jika diset Aktif, mata anggaran lain akan otomatis menjadi Tidak Aktif (hanya 1 yang aktif).</small>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2" style="border-top: 1px solid #e9eef5;">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal" style="border-radius: 6px;">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3" style="border-radius: 6px;">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (! empty($can_delete)): ?>
<div class="modal fade" id="modal-hapus-mata-anggaran" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header bg-danger text-white py-3">
                <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem;">
                    <i class="fas fa-trash-alt mr-2"></i>Konfirmasi Hapus
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-hapus-mata-anggaran" action="" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body py-4">
                    <p class="mb-0 text-dark">Apakah Anda yakin ingin menghapus data mata anggaran <strong id="hapus_mata_anggaran_text"></strong>?</p>
                </div>
                <div class="modal-footer bg-light py-2" style="border-top: 1px solid #e9eef5;">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal" style="border-radius: 6px;">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm px-3" style="border-radius: 6px;">Ya, Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
    $(document).ready(function () {
        if ($.fn.DataTable && ! $.fn.DataTable.isDataTable('.js-datatable')) {
            $('.js-datatable').DataTable({
                responsive: true,
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                    zeroRecords: "Tidak ada data yang ditemukan",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Lanjut",
                        previous: "Kembali"
                    }
                }
            });
        }

        $('#modal-ubah-mata-anggaran').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var mataAnggaran = button.data('mata_anggaran');
            var berlakuDari = button.data('berlaku_dari');
            var berlakuHingga = button.data('berlaku_hingga');
            var status = button.data('status');

            var modal = $(this);
            modal.find('#form-ubah-mata-anggaran').attr('action', '<?= site_url('/admin/master/mata-anggaran'); ?>/' + id + '/ubah');
            modal.find('#edit_mata_anggaran').val(mataAnggaran);
            modal.find('#edit_berlaku_dari').val(berlakuDari);
            modal.find('#edit_berlaku_hingga').val(berlakuHingga);
            modal.find('#edit_status').val(status);
        });

        $('#modal-hapus-mata-anggaran').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var mataAnggaran = button.data('mata_anggaran');

            var modal = $(this);
            modal.find('#form-hapus-mata-anggaran').attr('action', '<?= site_url('/admin/master/mata-anggaran'); ?>/' + id + '/hapus');
            modal.find('#hapus_mata_anggaran_text').text('"' + mataAnggaran + '"');
        });
    });
</script>
<?= $this->endSection(); ?>
