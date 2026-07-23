<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php
$sekolahs = $sekolahs ?? [];
$canImport = (bool) ($can_import ?? false);
$filterPaketId = $filter_paket_id ?? '';
$pakets = $pakets ?? [];
?>


<div class="card mb-3">
    <div class="card-body">
        <div class="form-row align-items-end">
            <div class="col-md-4 col-sm-6">
                <label for="filter_paket_id" class="small text-muted font-weight-bold">Filter Paket</label>
                <select class="form-control form-control-sm" id="filter_paket_id" name="paket_id">
                    <option value="all">- semua paket -</option>
                    <?php foreach ($pakets as $paket): ?>
                        <option value="<?= esc((string) $paket['id']); ?>" <?= (string)$filterPaketId === (string)$paket['id'] ? 'selected' : ''; ?>>
                            <?= esc((string) $paket['nama_paket']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title mb-0"><i class="fas fa-school mr-1"></i> Daftar Sekolah - RAB</h3>
        <div class="card-tools ml-auto">
            <a href="<?= site_url('admin/laporan/rab-gedung/detail-semua'); ?>" class="btn btn-info btn-sm mr-1">
                <i class="fas fa-list-alt mr-1"></i> Lihat Seluruh Data Detail
            </a>
            <?php if ($canImport): ?>
                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalImport">
                    <i class="fas fa-file-import mr-1"></i> Import Excel RAB
                </button>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover w-100" id="tableSekolahRab">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 50px;" class="text-center">No</th>
                        <th>Sekolah</th>
                        <th class="text-center">Paket</th>
                        <th>Kecamatan</th>
                        <th>Kabupaten</th>
                        <th style="width: 160px;" class="text-center">Jumlah Pekerjaan</th>
                        <th style="width: 140px;" class="text-center">Total Item Pekerjaan</th>
                        <th style="width: 120px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($sekolahs !== []): ?>
                        <?php $no = 1; foreach ($sekolahs as $sekolah): ?>
                            <tr>
                                <td class="text-center"><?= esc((string) $no++); ?></td>
                                <td><strong><?= esc((string) ($sekolah['nama'] ?? '-')); ?></strong></td>
                                <td class="text-center" data-paket-id="<?= esc((string)($sekolah['paket_id'] ?? '')); ?>">
                                    <?php if (!empty($sekolah['nama_paket'])): ?>
                                        <span class="badge badge-success"><i class="fas fa-box mr-1"></i> <?= esc($sekolah['nama_paket']); ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-warning text-dark"><i class="fas fa-exclamation-triangle mr-1"></i> Belum ada paket</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc((string) ($sekolah['kecamatan'] ?? '-')); ?></td>
                                <td><?= esc((string) ($sekolah['kabupaten'] ?? '-')); ?></td>
                                <td class="text-center">
                                    <span class="badge badge-info"><?= esc((string) ($sekolah['total_gedung'] ?? 0)); ?> Pekerjaan</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-secondary"><?= esc((string) ($sekolah['total_items'] ?? 0)); ?> Item</span>
                                </td>
                                <td class="text-center text-nowrap">
                                    <a href="<?= site_url('admin/laporan/rab-gedung/detail/' . (int) ($sekolah['npsn'] ?? 0)); ?>" class="btn btn-primary btn-sm mr-1" title="Detail">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                    <?php if ($can_edit ?? false): ?>
                                        <button type="button" class="btn btn-warning btn-sm btn-edit-paket" data-npsn="<?= esc((string)($sekolah['npsn'] ?? '')); ?>" data-nama="<?= esc((string)($sekolah['nama'] ?? '')); ?>" data-paket-id="<?= esc((string)($sekolah['paket_id'] ?? '')); ?>" title="Ubah Paket">
                                            <i class="fas fa-box"></i> Ubah Paket
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Belum ada data sekolah. Silakan import file Excel terlebih dahulu.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Import -->
<?php if ($canImport): ?>
<div class="modal fade" id="modalImport" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mb-0"><i class="fas fa-file-import mr-1"></i> Import RAB Seluruh Sekolah</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('admin/laporan/rab-gedung/import'); ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="icon fas fa-info-circle mr-1"></i> Unggah file adendum Excel (format .xlsx atau .xls). Pastikan file memiliki sheet bernama <strong>"RAB PER GEDUNG"</strong> dengan struktur kolom yang sesuai.
                    </div>
                    <div class="form-group">
                        <label for="file_excel">File Excel</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="file_excel" name="file_excel" accept=".xlsx,.xls" required>
                            <label class="custom-file-label" for="file_excel">Pilih file...</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="import_paket_id">Paket <span class="text-danger">*</span></label>
                        <select class="form-control" id="import_paket_id" name="paket_id" required>
                            <option value="">-- Pilih Paket --</option>
                            <?php foreach ($pakets as $paket): ?>
                                <option value="<?= esc((string) $paket['id']); ?>"><?= esc((string) $paket['nama_paket']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="clear_data" name="clear_data" value="1" checked>
                            <label class="custom-control-label" for="clear_data">Kosongkan data sebelum import (Trunkasi tabel)</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-upload mr-1"></i> Mulai Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Edit Paket Sekolah -->
<?php if ($can_edit ?? false): ?>
<div class="modal fade" id="modalEditPaket" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mb-0"><i class="fas fa-box mr-1"></i> Ubah Paket Sekolah</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditPaket" action="" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Sekolah</label>
                        <input type="text" class="form-control" id="edit_paket_sekolah_nama" readonly>
                    </div>
                    <div class="form-group">
                        <label for="edit_paket_id">Paket <span class="text-danger">*</span></label>
                        <select class="form-control" id="edit_paket_id" name="paket_id" required>
                            <option value="">-- Pilih Paket --</option>
                            <?php foreach ($pakets as $paket): ?>
                                <option value="<?= esc((string) $paket['id']); ?>"><?= esc((string) $paket['nama_paket']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script>
$(document).ready(function() {
    // File upload label update
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });

    // Client-side DataTable for the school list
    const $table = $('#tableSekolahRab');
    if ($table.length && ! $.fn.dataTable.isDataTable($table)) {
        const table = $table.DataTable({
            responsive: false,
            autoWidth: false,
            scrollX: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Semua']],
            language: {
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data',
                zeroRecords: 'Data tidak ditemukan',
                paginate: {
                    first: 'Awal',
                    last: 'Akhir',
                    next: 'Berikutnya',
                    previous: 'Sebelumnya',
                },
            },
        });

        // Filter handler
        $('#filter_paket_id').on('change', function() {
            table.draw();
        });

        // Custom filtering function based on data-paket-id attribute
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                if (settings.nTable.id !== 'tableSekolahRab') {
                    return true;
                }
                const filterVal = $('#filter_paket_id').val();
                if (filterVal === '' || filterVal === 'all') {
                    return true;
                }
                
                const cell = table.cell(dataIndex, 2).node();
                const rowPaketId = $(cell).data('paket-id');
                
                return (String(rowPaketId) === String(filterVal));
            }
        );

        // Initial filter load if set
        if ($('#filter_paket_id').val() !== '' && $('#filter_paket_id').val() !== 'all') {
            $('#filter_paket_id').trigger('change');
        }
    }

    // Edit Paket button handler
    $('.btn-edit-paket').on('click', function() {
        const npsn = $(this).data('npsn');
        const nama = $(this).data('nama');
        const paketId = $(this).data('paket-id');

        $('#formEditPaket').attr('action', '<?= site_url('admin/laporan/rab-gedung/sekolah/'); ?>' + npsn + '/ubah-paket');
        $('#edit_paket_sekolah_nama').val(nama);
        $('#edit_paket_id').val(paketId);

        $('#modalEditPaket').modal('show');
    });
});
</script>
<?= $this->endSection(); ?>
