<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <?php if (session()->getFlashdata('message')): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-check-circle mr-2"></i><?= esc((string) session()->getFlashdata('message')); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-exclamation-circle mr-2"></i><?= esc((string) session()->getFlashdata('error')); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm" style="border: 1px solid #e9eef5; border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white d-flex align-items-center justify-content-between py-3" style="border-bottom: 1px solid #e9eef5;">
                    <h3 class="card-title mb-0 font-weight-bold text-dark" style="font-size: 1.15rem;">
                        <i class="fas fa-gavel text-primary mr-2"></i>Master Dasar SPT (Legal Basis)
                    </h3>
                    <?php if (! empty($can_add)): ?>
                        <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm" data-toggle="modal" data-target="#modal-tambah-dasar" style="border-radius: 6px;">
                            <i class="fas fa-plus mr-1"></i> Tambah Dasar SPT
                        </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-striped w-100 js-datatable" style="border-radius: 8px;">
                            <thead>
                                <tr>
                                    <th style="width: 60px;" class="text-center">#</th>
                                    <th>Uraian Dasar SPT</th>
                                    <?php if (! empty($can_edit) || ! empty($can_delete)): ?>
                                        <th style="width: 150px;" class="text-center">Aksi</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; foreach (($items ?? []) as $item): ?>
                                    <tr>
                                        <td class="text-center align-middle"><?= esc((string) $i++); ?></td>
                                        <td class="align-middle" style="white-space: normal; line-height: 1.5; font-size: 0.95rem;">
                                            <?= esc((string) ($item['uraian'] ?? '-')); ?>
                                        </td>
                                        <?php if (! empty($can_edit) || ! empty($can_delete)): ?>
                                            <td class="text-center align-middle" style="white-space: nowrap;">
                                                <div class="btn-group" role="group" style="gap: 5px;">
                                                    <?php if (! empty($can_edit)): ?>
                                                        <button
                                                            type="button"
                                                            class="btn btn-outline-primary btn-xs px-2 py-1"
                                                            data-toggle="modal"
                                                            data-target="#modal-ubah-dasar"
                                                            data-id="<?= esc((string) ($item['id'] ?? ''), 'attr'); ?>"
                                                            data-uraian="<?= esc((string) ($item['uraian'] ?? ''), 'attr'); ?>"
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
                                                            data-target="#modal-hapus-dasar"
                                                            data-id="<?= esc((string) ($item['id'] ?? ''), 'attr'); ?>"
                                                            data-uraian="<?= esc((string) ($item['uraian'] ?? ''), 'attr'); ?>"
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
<div class="modal fade" id="modal-tambah-dasar" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header bg-light py-3" style="border-bottom: 1px solid #e9eef5;">
                <h5 class="modal-title font-weight-bold text-dark" style="font-size: 1.05rem;">
                    <i class="fas fa-plus-circle text-primary mr-2"></i>Tambah Dasar SPT
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('/admin/master/dasar-spt/tambah'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body py-4">
                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-secondary mb-2" style="font-size: 0.9rem;">Uraian Dasar SPT <span class="text-danger">*</span></label>
                        <textarea name="uraian" class="form-control" rows="5" required placeholder="Contoh: Memorandum Direktur Jenderal Prasarana Strategis Nomor: PR0301/B/Ds/2026/01 Tanggal 16 April 2026, hal Arahan Percepatan Kegiatan Pembangunan Sekolah Rakyat Tahun Anggaran (TA) 2025-2026;" style="border-radius: 8px; resize: vertical;"></textarea>
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
<div class="modal fade" id="modal-ubah-dasar" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header bg-light py-3" style="border-bottom: 1px solid #e9eef5;">
                <h5 class="modal-title font-weight-bold text-dark" style="font-size: 1.05rem;">
                    <i class="fas fa-edit text-warning mr-2"></i>Ubah Dasar SPT
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-ubah-dasar" action="" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body py-4">
                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-secondary mb-2" style="font-size: 0.9rem;">Uraian Dasar SPT <span class="text-danger">*</span></label>
                        <textarea id="edit_uraian" name="uraian" class="form-control" rows="5" required style="border-radius: 8px; resize: vertical;"></textarea>
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
<div class="modal fade" id="modal-hapus-dasar" tabindex="-1" role="dialog" aria-hidden="true">
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
            <form id="form-hapus-dasar" action="" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body py-4 text-center">
                    <i class="fas fa-exclamation-triangle text-danger mb-3" style="font-size: 3rem;"></i>
                    <p class="mb-2 font-weight-bold text-dark">Apakah Anda yakin ingin menghapus data Dasar SPT ini?</p>
                    <div class="alert alert-light border text-left p-3 mt-3 text-secondary" id="hapus_uraian_preview" style="font-size: 0.9rem; border-radius: 8px; max-height: 120px; overflow-y: auto;">
                    </div>
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

<?= $this->section('pageScripts'); ?>
<script>
    (function () {
        // Edit flow
        const modalEdit = document.getElementById('modal-ubah-dasar');
        if (modalEdit) {
            const form = document.getElementById('form-ubah-dasar');
            const fieldUraian = document.getElementById('edit_uraian');

            const applyEditData = (trigger) => {
                if (!trigger) return;
                const id = trigger.getAttribute('data-id') || '';
                form.action = '<?= site_url('/admin/master/dasar-spt'); ?>/' + encodeURIComponent(id) + '/ubah';
                fieldUraian.value = trigger.getAttribute('data-uraian') || '';
            };

            document.addEventListener('click', function (event) {
                const trigger = event.target.closest('button[data-target="#modal-ubah-dasar"]');
                if (trigger) applyEditData(trigger);
            });

            modalEdit.addEventListener('show.bs.modal', function (event) {
                applyEditData(event.relatedTarget);
            });
        }

        // Delete flow
        const modalDelete = document.getElementById('modal-hapus-dasar');
        if (modalDelete) {
            const form = document.getElementById('form-hapus-dasar');
            const preview = document.getElementById('hapus_uraian_preview');

            const applyDeleteData = (trigger) => {
                if (!trigger) return;
                const id = trigger.getAttribute('data-id') || '';
                form.action = '<?= site_url('/admin/master/dasar-spt'); ?>/' + encodeURIComponent(id) + '/hapus';
                preview.textContent = trigger.getAttribute('data-uraian') || '';
            };

            document.addEventListener('click', function (event) {
                const trigger = event.target.closest('button[data-target="#modal-hapus-dasar"]');
                if (trigger) applyDeleteData(trigger);
            });

            modalDelete.addEventListener('show.bs.modal', function (event) {
                applyDeleteData(event.relatedTarget);
            });
        }
    })();
</script>
<?= $this->endSection(); ?>
