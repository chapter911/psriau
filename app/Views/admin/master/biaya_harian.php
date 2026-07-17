<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">
    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-1"></i> <?= session()->getFlashdata('message'); ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-1"></i> <?= session()->getFlashdata('error'); ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm" style="border: 1px solid #e9eef5; border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white py-3" style="border-bottom: 1px solid #e9eef5;">
                    <h3 class="card-title mb-0 font-weight-bold text-dark" style="font-size: 1.15rem; line-height: 1.8;">
                        <i class="fas fa-user-clock text-primary mr-2"></i>
                        Satuan Biaya Uang Harian &amp; Uang Representasi Perjalanan Dinas Dalam Negeri
                    </h3>
                    <?php if (! empty($can_add)): ?>
                        <div class="card-tools float-right">
                            <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm" data-toggle="modal" data-target="#modal-tambah" style="border-radius: 6px;">
                                <i class="fas fa-plus mr-1"></i> Tambah Data
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="tbl-harian" class="table table-hover table-bordered table-striped w-100 mb-0" style="font-size: 0.88rem;">
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-center align-middle" rowspan="2" style="width: 5%; vertical-align: middle;">NO.</th>
                                    <th class="text-center align-middle" rowspan="2" style="width: 15%; vertical-align: middle;">PROVINSI</th>
                                    <th class="text-center align-middle" rowspan="2" style="width: 5%; vertical-align: middle;">SATUAN</th>
                                    <th class="text-center align-middle" style="width: 15%; background-color: #f0f4f8;">LUAR KOTA</th>
                                    <th class="text-center align-middle" style="width: 25%; background-color: #f0f4f8; font-size: 0.78rem;">DALAM KOTA<br>LEBIH DARI 8 (DELAPAN) JAM</th>
                                    <th class="text-center align-middle" style="width: 15%; background-color: #f0f4f8;">DIKLAT</th>
                                    <th class="text-center align-middle" rowspan="2" style="width: 10%; vertical-align: middle;">PERIODE AWAL</th>
                                    <th class="text-center align-middle" rowspan="2" style="width: 10%; vertical-align: middle;">PERIODE AKHIR</th>
                                    <th class="text-center align-middle" rowspan="2" style="width: 5%; vertical-align: middle;">STATUS</th>
                                    <?php if (! empty($can_edit)): ?>
                                        <th class="text-center align-middle" rowspan="2" style="width: 5%; vertical-align: middle;">AKSI</th>
                                    <?php endif; ?>
                                </tr>
                                <tr>
                                    <th class="text-center" style="font-size: 0.78rem; background-color: #e8eef5;">(Rp)</th>
                                    <th class="text-center" style="font-size: 0.78rem; background-color: #e8eef5;">(Rp)</th>
                                    <th class="text-center" style="font-size: 0.78rem; background-color: #e8eef5;">(Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; foreach (($items ?? []) as $item): ?>
                                    <tr>
                                        <td class="text-center align-middle"><?= esc((string) $i++); ?></td>
                                        <td class="align-middle font-weight-500"><?= esc((string) ($item['nama_provinsi'] ?? $item['provinsi_kode'] ?? '-')); ?></td>
                                        <td class="text-center align-middle"><?= esc((string) ($item['satuan'] ?? 'OH')); ?></td>
                                        <td class="text-right align-middle font-weight-bold">Rp <?= number_format((int) ($item['luar_kota'] ?? 0), 0, ',', '.'); ?></td>
                                        <td class="text-right align-middle">Rp <?= number_format((int) ($item['dalam_kota'] ?? 0), 0, ',', '.'); ?></td>
                                        <td class="text-right align-middle">Rp <?= number_format((int) ($item['diklat'] ?? 0), 0, ',', '.'); ?></td>
                                        <td class="text-center align-middle">
                                            <?= date('d M Y', strtotime($item['berlaku_mulai'] ?? '2024-01-01')); ?>
                                        </td>
                                        <td class="text-center align-middle">
                                            <?= !empty($item['berlaku_hingga']) ? date('d M Y', strtotime($item['berlaku_hingga'])) : '-'; ?>
                                        </td>
                                        <td class="text-center align-middle">
                                            <?php if ((int)($item['is_active'] ?? 1) === 1): ?>
                                                <span class="badge badge-success px-2 py-1">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary px-2 py-1">Tidak Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <?php if (! empty($can_edit)): ?>
                                            <td class="text-center align-middle" style="white-space: nowrap;">
                                                <div class="d-flex justify-content-center" style="gap: 4px;">
                                                    <?php if (! empty($can_edit)): ?>
                                                        <button type="button"
                                                            class="btn btn-outline-primary btn-xs px-2 py-1"
                                                            data-toggle="modal"
                                                            data-target="#modal-ubah"
                                                            data-id="<?= esc((string) ($item['id'] ?? ''), 'attr'); ?>"
                                                            data-provinsi="<?= esc((string) ($item['provinsi_kode'] ?? ''), 'attr'); ?>"
                                                            data-satuan="<?= esc((string) ($item['satuan'] ?? 'OH'), 'attr'); ?>"
                                                            data-luar-kota="<?= esc((string) ($item['luar_kota'] ?? '0'), 'attr'); ?>"
                                                            data-dalam-kota="<?= esc((string) ($item['dalam_kota'] ?? '0'), 'attr'); ?>"
                                                            data-diklat="<?= esc((string) ($item['diklat'] ?? '0'), 'attr'); ?>"
                                                            data-mulai="<?= esc((string) ($item['berlaku_mulai'] ?? '2024-01-01'), 'attr'); ?>"
                                                            data-hingga="<?= esc((string) ($item['berlaku_hingga'] ?? ''), 'attr'); ?>"
                                                            data-status="<?= esc((string) ($item['is_active'] ?? '1'), 'attr'); ?>"
                                                            onclick="fillEditModal(this)"
                                                            style="border-radius: 4px;" title="Edit">
                                                            <i class="fas fa-pen mr-1"></i>Edit
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

<!-- Modal Tambah -->
<?php if (! empty($can_add)): ?>
<div class="modal fade" id="modal-tambah" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header bg-light py-3" style="border-bottom: 1px solid #e9eef5;">
                <h5 class="modal-title font-weight-bold text-dark" style="font-size: 1.05rem;">
                    <i class="fas fa-plus-circle text-primary mr-2"></i>Tambah Biaya Harian Personel
                </h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="<?= site_url('/admin/master/biaya/harian/tambah'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body py-4">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label class="font-weight-bold text-secondary mb-1" style="font-size: 0.875rem;">Provinsi <span class="text-danger">*</span></label>
                                <select name="provinsi_kode" class="form-control" required style="border-radius: 6px;">
                                    <option value="">-- Pilih Provinsi --</option>
                                    <?php foreach (($provinsis ?? []) as $prov): ?>
                                        <option value="<?= esc((string) ($prov['kode_provinsi'] ?? ''), 'attr'); ?>">
                                            <?= esc((string) ($prov['nama_provinsi'] ?? '')); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold text-secondary mb-1" style="font-size: 0.875rem;">Satuan</label>
                                <input type="text" name="satuan" class="form-control" value="OH" style="border-radius: 6px;">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold text-secondary mb-1" style="font-size: 0.875rem;">Berlaku Mulai <span class="text-danger">*</span></label>
                                <input type="date" name="berlaku_mulai" class="form-control" value="2024-01-01" required style="border-radius: 6px;">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold text-secondary mb-1" style="font-size: 0.875rem;">Berlaku Hingga</label>
                                <input type="date" name="berlaku_hingga" class="form-control" style="border-radius: 6px;">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold text-secondary mb-1" style="font-size: 0.875rem;">Status <span class="text-danger">*</span></label>
                                <select name="is_active" class="form-control" required style="border-radius: 6px;">
                                    <option value="1">Aktif</option>
                                    <option value="0">Tidak Aktif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <hr class="my-2">
                    <p class="font-weight-bold text-secondary mb-2" style="font-size: 0.875rem;"><i class="fas fa-money-bill-wave mr-1"></i> Uang Harian (Rp)</p>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold text-secondary mb-1" style="font-size: 0.875rem;">Luar Kota <span class="text-danger">*</span></label>
                                <input type="text" name="luar_kota" class="form-control input-ribuan" required style="border-radius: 6px;">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold text-secondary mb-1" style="font-size: 0.875rem;">Dalam Kota > 8 Jam <span class="text-danger">*</span></label>
                                <input type="text" name="dalam_kota" class="form-control input-ribuan" required style="border-radius: 6px;">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold text-secondary mb-1" style="font-size: 0.875rem;">Diklat <span class="text-danger">*</span></label>
                                <input type="text" name="diklat" class="form-control input-ribuan" required style="border-radius: 6px;">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-3" style="border-top: 1px solid #e9eef5;">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal" style="border-radius: 6px;">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3" style="border-radius: 6px;">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Ubah -->
<?php if (! empty($can_edit)): ?>
<div class="modal fade" id="modal-ubah" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header bg-light py-3" style="border-bottom: 1px solid #e9eef5;">
                <h5 class="modal-title font-weight-bold text-dark" style="font-size: 1.05rem;">
                    <i class="fas fa-pen-fancy text-primary mr-2"></i>Ubah Biaya Harian Personel
                </h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="form-ubah" action="" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body py-4">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label class="font-weight-bold text-secondary mb-1" style="font-size: 0.875rem;">Provinsi <span class="text-danger">*</span></label>
                                <select id="ubah-provinsi" name="provinsi_kode" class="form-control" required style="border-radius: 6px;">
                                    <option value="">-- Pilih Provinsi --</option>
                                    <?php foreach (($provinsis ?? []) as $prov): ?>
                                        <option value="<?= esc((string) ($prov['kode_provinsi'] ?? ''), 'attr'); ?>">
                                            <?= esc((string) ($prov['nama_provinsi'] ?? '')); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold text-secondary mb-1" style="font-size: 0.875rem;">Satuan</label>
                                <input type="text" id="ubah-satuan" name="satuan" class="form-control" style="border-radius: 6px;">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold text-secondary mb-1" style="font-size: 0.875rem;">Berlaku Mulai <span class="text-danger">*</span></label>
                                <input type="date" id="ubah-mulai" name="berlaku_mulai" class="form-control" required style="border-radius: 6px;">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold text-secondary mb-1" style="font-size: 0.875rem;">Berlaku Hingga</label>
                                <input type="date" id="ubah-hingga" name="berlaku_hingga" class="form-control" style="border-radius: 6px;">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold text-secondary mb-1" style="font-size: 0.875rem;">Status <span class="text-danger">*</span></label>
                                <select id="ubah-status" name="is_active" class="form-control" required style="border-radius: 6px;">
                                    <option value="1">Aktif</option>
                                    <option value="0">Tidak Aktif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <hr class="my-2">
                    <p class="font-weight-bold text-secondary mb-2" style="font-size: 0.875rem;"><i class="fas fa-money-bill-wave mr-1"></i> Uang Harian (Rp)</p>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold text-secondary mb-1" style="font-size: 0.8rem;">Luar Kota <span class="text-danger">*</span></label>
                                <label class="font-weight-bold text-secondary mb-1" style="font-size: 0.875rem;">Luar Kota <span class="text-danger">*</span></label>
                                <input type="text" id="ubah-luar-kota" name="luar_kota" class="form-control input-ribuan" required style="border-radius: 6px;">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label class="font-weight-bold text-secondary mb-1" style="font-size: 0.875rem;">Dalam Kota > 8 Jam <span class="text-danger">*</span></label>
                                <input type="text" id="ubah-dalam-kota" name="dalam_kota" class="form-control input-ribuan" required style="border-radius: 6px;">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label class="font-weight-bold text-secondary mb-1" style="font-size: 0.875rem;">Diklat <span class="text-danger">*</span></label>
                                <input type="text" id="ubah-diklat" name="diklat" class="form-control input-ribuan" required style="border-radius: 6px;">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-3" style="border-top: 1px solid #e9eef5;">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal" style="border-radius: 6px;">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3" style="border-radius: 6px;">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
$(document).ready(function () {
    $('#tbl-harian').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[1, 'asc']],
        language: {
            processing:   'Memproses...',
            zeroRecords:  'Tidak ada data',
            info:         'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            infoEmpty:    'Tidak ada data',
            search:       'Cari:',
            lengthMenu:   'Tampilkan _MENU_ data',
            paginate: { first: 'Pertama', last: 'Terakhir', next: '&rsaquo;', previous: '&lsaquo;' }
        },
        columnDefs: [
            { orderable: false, targets: -1 }
        ]
    });

    $(document).on('input', '.input-ribuan', function() {
        $(this).val(formatRibuan($(this).val()));
    });
});

function formatRibuan(angka) {
    var val = angka.toString().replace(/[^0-9]/g, '');
    if (val !== '') {
        return val.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
    return '';
}

function fillEditModal(el) {
    var btn = $(el);
    $('#ubah-provinsi').val(btn.attr('data-provinsi')).trigger('change');
    
    var mulai = btn.attr('data-mulai') || '';
    $('#ubah-mulai').val(mulai.length > 10 ? mulai.substring(0, 10) : mulai);
    
    var hingga = btn.attr('data-hingga') || '';
    $('#ubah-hingga').val(hingga.length > 10 ? hingga.substring(0, 10) : hingga);
    
    $('#ubah-status').val(btn.attr('data-status')).trigger('change');
    $('#ubah-satuan').val(btn.attr('data-satuan'));
    $('#ubah-luar-kota').val(formatRibuan(btn.attr('data-luar-kota') || ''));
    $('#ubah-dalam-kota').val(formatRibuan(btn.attr('data-dalam-kota') || ''));
    $('#ubah-diklat').val(formatRibuan(btn.attr('data-diklat') || ''));
    $('#form-ubah').attr('action', '<?= site_url('/admin/master/biaya/harian'); ?>/' + btn.attr('data-id') + '/ubah');
}
</script>
<?= $this->endSection(); ?>
