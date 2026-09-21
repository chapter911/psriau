<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php if (empty($table_ready)): ?>
    <div class="alert alert-warning">
        Tabel pegawai belum tersedia. Jalankan migration terlebih dahulu.
    </div>
<?php endif; ?>

<style>
    .jabatan-col {
        width: 220px;
        min-width: 180px;
        max-width: 240px;
    }
    .jabatan-text-clamp {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: normal;
        word-break: break-word;
        cursor: pointer;
        line-height: 1.35;
        transition: color 0.15s ease-in-out;
    }
    .jabatan-text-clamp:hover {
        color: #0056b3;
    }
    /* Scoped Select2 Multiple Tag Style for Pegawai Filter */
    .select2-filter-jp-wrapper .select2-container .select2-selection--multiple {
        min-height: 31px !important;
        padding-bottom: 2px !important;
        border-color: #ced4da;
    }
    .select2-filter-jp-wrapper .select2-container .select2-selection--multiple .select2-selection__rendered {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        gap: 3px !important;
        padding: 2px 4px !important;
    }
    .select2-filter-jp-wrapper .select2-container .select2-selection--multiple .select2-selection__choice {
        display: inline-flex !important;
        flex-direction: row-reverse !important;
        align-items: center !important;
        width: auto !important;
        margin: 2px 2px !important;
        padding: 1px 6px !important;
        font-size: 0.78rem !important;
        background-color: var(--app-primary, #0A66C2) !important;
        border: 1px solid var(--app-primary, #0A66C2) !important;
        color: #fff !important;
        border-radius: 3px !important;
        line-height: 1.3 !important;
    }
    .select2-filter-jp-wrapper .select2-container .select2-selection--multiple .select2-selection__choice__remove {
        color: rgba(255, 255, 255, 0.9) !important;
        font-size: 0.85rem !important;
        margin-left: 5px !important;
        padding: 0 !important;
        font-weight: bold !important;
    }
    .select2-filter-jp-wrapper .select2-container .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #ffcccc !important;
    }
    .select2-filter-jp-wrapper .select2-container .select2-selection--multiple .select2-search--inline {
        display: inline-block !important;
        width: auto !important;
        margin: 0 !important;
    }
    .select2-filter-jp-wrapper .select2-container .select2-selection--multiple .select2-search__field {
        width: auto !important;
        height: 24px !important;
        min-width: 60px !important;
        padding: 2px 4px !important;
        margin: 0 !important;
        font-size: 0.82rem !important;
        line-height: normal !important;
    }
</style>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Pegawai</h3>
        <div class="float-right">
            <button type="button" class="btn btn-outline-info mr-2 js-btn-search-nfc" title="Cari data pegawai dengan menempelkan kartu NFC ke HP">
                <i class="fas fa-id-card-alt mr-1"></i> Cari via NFC
            </button>
            <?php if (! empty($can_export)): ?>
                <a href="<?= site_url('/admin/master/pegawai/export'); ?>" class="btn btn-success mr-2" id="btn-export-excel" title="Export ke format Excel (.xlsx)">
                    <i class="fas fa-file-excel mr-1"></i> Export Excel
                </a>
                <a href="<?= site_url('/admin/master/pegawai/export-pdf'); ?>" class="btn btn-danger mr-2" id="btn-export-pdf" target="_blank" title="Export ke format PDF (.pdf)">
                    <i class="fas fa-file-pdf mr-1"></i> Export PDF
                </a>
            <?php endif; ?>
            <?php if (! empty($can_import)): ?>
                <button type="button" class="btn btn-info mr-2" data-toggle="modal" data-target="#modal-import-pegawai">Import Excel</button>
            <?php endif; ?>
            <?php if (! empty($can_add)): ?>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-tambah-pegawai">Tambah Pegawai</button>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <!-- Filter Panel -->
        <div class="card card-outline card-info mb-3">
            <div class="card-header py-2">
                <h3 class="card-title font-weight-bold text-dark" style="font-size: 0.95rem;">
                    <i class="fas fa-filter text-info mr-1"></i> Filter Data Pegawai
                    <span id="active-filter-badge" class="badge badge-info ml-2" style="display: none;">0 Filter Aktif</span>
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool text-danger font-weight-bold" id="btn-reset-filter" style="display: none;" title="Reset Filter">
                        <i class="fas fa-redo-alt mr-1"></i> Reset Filter
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body py-2">
                <div class="row align-items-start">
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0 select2-filter-jp-wrapper">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="small font-weight-bold text-muted mb-0">JENIS PEGAWAI</label>
                            <span class="small" style="font-size: 0.72rem;">
                                <a href="#" class="btn-select-all-jp text-primary mr-1" title="Pilih Semua">[Semua]</a>
                                <a href="#" class="btn-reset-jp text-danger" title="Kosongkan">[Reset]</a>
                            </span>
                        </div>
                        <select id="filter-jenis-pegawai" class="form-control select2" multiple="multiple" data-placeholder="Semua Jenis Pegawai..." style="width: 100%;">
                            <option value="pns">PNS</option>
                            <option value="cpns">CPNS</option>
                            <option value="pppk">PPPK</option>
                            <option value="ppnpn">PPNPN</option>
                            <option value="konsultan">Konsultan Individual</option>
                            <option value="security">Security (Keamanan)</option>
                            <option value="cleaning_service">Cleaning Service (Kebersihan)</option>
                            <option value="lainnya">Lainnya (Non-ASN)</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <div class="mb-1" style="height: 17px;">
                            <label class="small font-weight-bold text-muted mb-0">ESELON</label>
                        </div>
                        <select id="filter-eselon" class="form-control form-control-sm custom-select custom-select-sm">
                            <option value="">Semua Eselon</option>
                            <option value="Eselon I">Eselon I</option>
                            <option value="Eselon II">Eselon II</option>
                            <option value="Eselon III">Eselon III</option>
                            <option value="Eselon IV">Eselon IV</option>
                            <option value="Non Eselon">Non Eselon</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <div class="mb-1" style="height: 17px;">
                            <label class="small font-weight-bold text-muted mb-0">GOLONGAN</label>
                        </div>
                        <select id="filter-golongan" class="form-control form-control-sm custom-select custom-select-sm">
                            <option value="">Semua Golongan</option>
                            <?php
                                $golOptions = ['I/a','I/b','I/c','I/d','II/a','II/b','II/c','II/d','III/a','III/b','III/c','III/d','IV/a','IV/b','IV/c','IV/d','IV/e'];
                                foreach ($golOptions as $g) {
                                    echo '<option value="' . esc($g) . '">' . esc($g) . '</option>';
                                }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <div class="mb-1" style="height: 17px;">
                            <label class="small font-weight-bold text-muted mb-0">STATUS</label>
                        </div>
                        <select id="filter-status" class="form-control form-control-sm custom-select custom-select-sm">
                            <option value="">Semua Status</option>
                            <option value="Aktif">Aktif</option>
                            <option value="Nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <table class="table table-bordered table-striped w-100 nowrap js-datatable js-pegawai-table">
            <thead>
                <tr style="white-space: nowrap;">
                    <th class="text-center">#</th>
                    <th class="text-center">FOTO PROFIL</th>
                    <th class="text-center">FOTO ID CARD</th>
                    <th class="text-center">NIP</th>
                    <th class="text-center">NAMA</th>
                    <th class="text-center">ID CARD (RFID)</th>
                    <th class="text-center">EMAIL</th>
                    <th class="text-center jabatan-col">JABATAN (FUNGSIONAL/PELAKSANA)</th>
                    <th class="text-center jabatan-col">JABATAN (PERBENDAHARAAN)</th>
                    <th class="text-center">JENIS PEGAWAI</th>
                    <th class="text-center">ESELON</th>
                    <th class="text-center">GOLONGAN</th>
                    <th class="text-center">MASA KERJA</th>
                    <th class="text-center">STATUS</th>
                    <?php if (! empty($can_edit)): ?>
                        <th class="text-center">ACTION</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach (($items ?? []) as $item): ?>
                    <?php
                        $isActive = (int) ($item['is_active'] ?? 1) === 1;
                        $fotoPath = trim((string) ($item['foto'] ?? ''));
                        $fotoUrl = $fotoPath !== '' ? media_url($fotoPath) : '';
                        $fotoIdCardPath = trim((string) ($item['foto_id_card'] ?? ''));
                        $fotoIdCardUrl = $fotoIdCardPath !== '' ? media_url($fotoIdCardPath) : '';
                        $jpVal = strtolower(trim((string) ($item['jenis_pegawai'] ?? 'pns')));
                        $isAsn = in_array($jpVal, ['pns', 'cpns', 'pppk'], true);
                        $displayNip = $isAsn ? trim((string) ($item['nip'] ?? '')) : '';
                        $idCardVal = trim((string) ($item['id_card'] ?? ''));
                    ?>
                    <tr
                        data-id_card="<?= esc($idCardVal, 'attr'); ?>"
                        data-rfid_counterpart="<?= esc((string) ($item['rfid_counterpart'] ?? ''), 'attr'); ?>"
                        data-nama="<?= esc((string) ($item['nama'] ?? ''), 'attr'); ?>"
                        data-nip="<?= esc($displayNip, 'attr'); ?>"
                        data-foto_url="<?= esc($fotoUrl, 'attr'); ?>"
                        data-jabatan="<?= esc((string) ($item['jabatan_utama_label'] ?? '-'), 'attr'); ?>"
                        data-jenis="<?= esc((string) ($item['jenis_pegawai'] ?? 'pns'), 'attr'); ?>"
                        data-is_active="<?= $isActive ? '1' : '0'; ?>"
                    >
                        <td><?= esc((string) $i++); ?></td>
                        <td class="text-center">
                            <?php if ($fotoUrl !== ''): ?>
                                <button
                                    type="button"
                                    class="btn p-0 border-0 bg-transparent js-open-foto-modal"
                                    data-foto-url="<?= esc($fotoUrl, 'attr'); ?>"
                                    data-nama="Foto Profil - <?= esc((string) ($item['nama'] ?? 'Pegawai'), 'attr'); ?>"
                                    title="Lihat foto profil"
                                >
                                    <img src="<?= esc($fotoUrl); ?>" alt="Foto Pegawai" style="width: 50px; height: 50px; border-radius: 6px; object-fit: cover; border: 1px solid #dee2e6;">
                                </button>
                            <?php elseif (! empty($can_edit)): ?>
                                <button
                                    type="button"
                                    class="btn btn-light border d-inline-flex align-items-center justify-content-center js-open-edit-pegawai-foto"
                                    data-toggle="modal"
                                    data-target="#modal-ubah-pegawai"
                                    data-id="<?= esc((string) ($item['id'] ?? ''), 'attr'); ?>"
                                    data-nip="<?= esc((string) ($item['nip'] ?? ''), 'attr'); ?>"
                                    data-nama="<?= esc((string) ($item['nama'] ?? ''), 'attr'); ?>"
                                    data-email="<?= esc((string) ($item['email'] ?? ''), 'attr'); ?>"
                                    data-id_card="<?= esc($idCardVal, 'attr'); ?>"
                                    data-foto-url=""
                                    data-foto_id_card-url="<?= esc($fotoIdCardUrl, 'attr'); ?>"
                                    data-jabatan_utama_id="<?= esc((string) ($item['jabatan_utama_id'] ?? ''), 'attr'); ?>"
                                    data-jabatan_perbendaharaan_id="<?= esc((string) ($item['jabatan_perbendaharaan_id'] ?? ''), 'attr'); ?>"
                                    data-jenis_pegawai="<?= esc((string) ($item['jenis_pegawai'] ?? 'pns'), 'attr'); ?>"
                                    data-eselon="<?= esc((string) ($item['eselon'] ?? ''), 'attr'); ?>"
                                    data-golongan="<?= esc((string) ($item['golongan'] ?? ''), 'attr'); ?>"
                                    data-masa_kerja="<?= esc((string) ($item['masa_kerja'] ?? ''), 'attr'); ?>"
                                    data-is_active="<?= esc((string) ($item['is_active'] ?? 1), 'attr'); ?>"
                                    title="Upload foto profil"
                                    style="width: 50px; height: 50px; border-radius: 6px; border-style: dashed !important;"
                                >
                                    <span class="text-muted font-weight-bold">+</span>
                                </button>
                            <?php else: ?>
                                <span class="badge badge-light border">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($fotoIdCardUrl !== ''): ?>
                                <button
                                    type="button"
                                    class="btn p-0 border-0 bg-transparent js-open-foto-modal"
                                    data-foto-url="<?= esc($fotoIdCardUrl, 'attr'); ?>"
                                    data-nama="Foto ID Card - <?= esc((string) ($item['nama'] ?? 'Pegawai'), 'attr'); ?>"
                                    title="Lihat foto ID Card"
                                >
                                    <img src="<?= esc($fotoIdCardUrl); ?>" alt="Foto ID Card" style="width: 58px; height: 38px; border-radius: 4px; object-fit: cover; border: 1px solid #17a2b8; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                </button>
                            <?php elseif (! empty($can_edit)): ?>
                                <button
                                    type="button"
                                    class="btn btn-light border d-inline-flex align-items-center justify-content-center js-open-edit-pegawai-foto"
                                    data-toggle="modal"
                                    data-target="#modal-ubah-pegawai"
                                    data-id="<?= esc((string) ($item['id'] ?? ''), 'attr'); ?>"
                                    data-nip="<?= esc((string) ($item['nip'] ?? ''), 'attr'); ?>"
                                    data-nama="<?= esc((string) ($item['nama'] ?? ''), 'attr'); ?>"
                                    data-email="<?= esc((string) ($item['email'] ?? ''), 'attr'); ?>"
                                    data-id_card="<?= esc($idCardVal, 'attr'); ?>"
                                    data-foto-url="<?= esc($fotoUrl, 'attr'); ?>"
                                    data-foto_id_card-url=""
                                    data-jabatan_utama_id="<?= esc((string) ($item['jabatan_utama_id'] ?? ''), 'attr'); ?>"
                                    data-jabatan_perbendaharaan_id="<?= esc((string) ($item['jabatan_perbendaharaan_id'] ?? ''), 'attr'); ?>"
                                    data-jenis_pegawai="<?= esc((string) ($item['jenis_pegawai'] ?? 'pns'), 'attr'); ?>"
                                    data-eselon="<?= esc((string) ($item['eselon'] ?? ''), 'attr'); ?>"
                                    data-golongan="<?= esc((string) ($item['golongan'] ?? ''), 'attr'); ?>"
                                    data-masa_kerja="<?= esc((string) ($item['masa_kerja'] ?? ''), 'attr'); ?>"
                                    data-is_active="<?= esc((string) ($item['is_active'] ?? 1), 'attr'); ?>"
                                    title="Upload foto ID card"
                                    style="width: 58px; height: 38px; border-radius: 4px; border-style: dashed !important;"
                                >
                                    <i class="fas fa-id-card text-muted small mr-1"></i><span class="text-muted font-weight-bold" style="font-size: 11px;">+</span>
                                </button>
                            <?php else: ?>
                                <span class="badge badge-light border">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($displayNip !== '' ? $displayNip : '-'); ?></td>
                        <td><?= esc((string) ($item['nama'] ?? '-')); ?></td>
                        <td class="text-center">
                            <?php if ($idCardVal !== ''): ?>
                                <span class="badge badge-info" style="font-family: monospace; font-size: 0.82rem; letter-spacing: 0.5px; padding: 4px 6px;" title="Nomor RFID Terdaftar">
                                    <i class="fas fa-id-badge mr-1"></i><?= esc($idCardVal); ?>
                                </span>
                                <?php if (! empty($item['rfid_counterpart'])): ?>
                                    <div class="mt-1" style="font-family: monospace; font-size: 0.72rem;">
                                        <span class="badge badge-light border text-muted" style="font-size: 0.7rem; font-weight: 500;" title="Padanan Konversi Otomatis (Reader USB / NFC Android)">
                                            <i class="fas fa-sync-alt mr-1 text-info"></i><?= esc($item['rfid_counterpart_label'] ?? $item['rfid_counterpart']); ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?= esc((string) ($item['email'] ?? '-')); ?></td>
                        <td class="jabatan-col">
                            <?php $jabatanUtama = trim((string) ($item['jabatan_utama_label'] ?? '-')); ?>
                            <div class="jabatan-text-clamp" title="<?= esc($jabatanUtama !== '' ? $jabatanUtama : '-', 'attr'); ?>" data-toggle="tooltip" data-placement="top">
                                <?= esc($jabatanUtama !== '' ? $jabatanUtama : '-'); ?>
                            </div>
                        </td>
                        <td class="jabatan-col">
                            <?php $jabatanPerbend = trim((string) ($item['jabatan_perbendaharaan_label'] ?? '-')); ?>
                            <div class="jabatan-text-clamp" title="<?= esc($jabatanPerbend !== '' ? $jabatanPerbend : '-', 'attr'); ?>" data-toggle="tooltip" data-placement="top">
                                <?= esc($jabatanPerbend !== '' ? $jabatanPerbend : '-'); ?>
                            </div>
                        </td>
                        <?php $jpVal = strtolower(trim((string) ($item['jenis_pegawai'] ?? 'pns'))); ?>
                        <td class="text-center" data-filter="<?= esc($jpVal, 'attr'); ?>">
                            <?php
                                $jpBadge = match ($jpVal) {
                                    'pns' => '<span class="badge badge-primary">PNS</span>',
                                    'cpns' => '<span class="badge badge-info">CPNS</span>',
                                    'pppk' => '<span class="badge badge-info">PPPK</span>',
                                    'ppnpn' => '<span class="badge badge-secondary">PPNPN</span>',
                                    'konsultan' => '<span class="badge badge-success">Konsultan Individual</span>',
                                    'security' => '<span class="badge badge-warning">Security</span>',
                                    'cleaning_service' => '<span class="badge badge-dark">Cleaning Service</span>',
                                    'lainnya' => '<span class="badge badge-light border">Lainnya</span>',
                                    default => '<span class="badge badge-secondary">' . esc(strtoupper($jpVal)) . '</span>',
                                };
                                echo $jpBadge;
                            ?>
                        </td>
                        <td><?= esc((string) ($item['eselon'] ?? '-')); ?></td>
                        <td><?= esc((string) ($item['golongan'] ?? '-')); ?></td>
                        <td><?= esc((string) ($item['masa_kerja'] ?? '-')); ?></td>
                        <td class="text-center">
                            <?php if ($isActive): ?>
                                <span class="badge badge-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <?php if (! empty($can_edit)): ?>
                            <td class="text-center" style="white-space: nowrap;">
                                <button
                                    type="button"
                                    class="btn btn-warning btn-sm"
                                    data-toggle="modal"
                                    data-target="#modal-ubah-pegawai"
                                    data-id="<?= esc((string) ($item['id'] ?? ''), 'attr'); ?>"
                                    data-nip="<?= esc((string) ($item['nip'] ?? ''), 'attr'); ?>"
                                    data-nama="<?= esc((string) ($item['nama'] ?? ''), 'attr'); ?>"
                                    data-email="<?= esc((string) ($item['email'] ?? ''), 'attr'); ?>"
                                    data-id_card="<?= esc($idCardVal, 'attr'); ?>"
                                    data-foto-url="<?= esc($fotoUrl, 'attr'); ?>"
                                    data-foto_id_card-url="<?= esc($fotoIdCardUrl, 'attr'); ?>"
                                    data-jabatan_utama_id="<?= esc((string) ($item['jabatan_utama_id'] ?? ''), 'attr'); ?>"
                                    data-jabatan_perbendaharaan_id="<?= esc((string) ($item['jabatan_perbendaharaan_id'] ?? ''), 'attr'); ?>"
                                    data-jenis_pegawai="<?= esc((string) ($item['jenis_pegawai'] ?? 'pns'), 'attr'); ?>"
                                    data-eselon="<?= esc((string) ($item['eselon'] ?? ''), 'attr'); ?>"
                                    data-golongan="<?= esc((string) ($item['golongan'] ?? ''), 'attr'); ?>"
                                    data-masa_kerja="<?= esc((string) ($item['masa_kerja'] ?? ''), 'attr'); ?>"
                                    data-is_active="<?= esc((string) ($item['is_active'] ?? 1), 'attr'); ?>"
                                >UBAH</button>

                                <form action="<?= site_url('/admin/master/pegawai/' . (int) ($item['id'] ?? 0) . '/status'); ?>" method="post" class="d-inline-block js-status-form" data-skip-confirm="1">
                                    <?= csrf_field(); ?>
                                    <input type="hidden" name="is_active" value="<?= $isActive ? '0' : '1'; ?>">
                                    <button type="submit" class="btn btn-sm <?= $isActive ? 'btn-secondary' : 'btn-success'; ?>">
                                        <?= $isActive ? 'NONAKTIFKAN' : 'AKTIFKAN'; ?>
                                    </button>
                                </form>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (! empty($can_import)): ?>
<div class="modal fade" id="modal-import-pegawai" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Pegawai (Excel)</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('/admin/master/pegawai/import'); ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        Kolom wajib: <strong>nip</strong>, <strong>nama</strong>, <strong>jabatan_utama</strong>, <strong>jenis_pegawai</strong> (pns/cpns/pppk/ppnpn/konsultan/security/cleaning_service/lainnya).<br>
                        Kolom opsional: <strong>id_card</strong>, <strong>email</strong>, <strong>jabatan_perbendaharaan</strong>, <strong>eselon</strong>, <strong>golongan</strong>, <strong>masa_kerja</strong>, <strong>status</strong>.
                    </div>
                    <div class="mb-3">
                        <a href="<?= site_url('/admin/master/pegawai/template'); ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-download mr-1"></i> Download Template Excel
                        </a>
                    </div>
                    <div class="form-group mb-0">
                        <label for="file_excel_pegawai">File Excel</label>
                        <input type="file" class="form-control" id="file_excel_pegawai" name="file_excel" accept=".xls,.xlsx,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                        <small class="text-muted">Import hanya untuk data teks (termasuk nomor RFID/ID Card). Foto profil & foto ID Card diupload dari form tambah/ubah.</small>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (! empty($can_add)): ?>
<div class="modal fade" id="modal-tambah-pegawai" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Pegawai</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-tambah-pegawai" action="<?= site_url('/admin/master/pegawai/tambah'); ?>" method="post" enctype="multipart/form-data" data-skip-confirm="1">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>NIP / NIK / ID Kontrak</label>
                            <input type="text" name="nip" class="form-control" required maxlength="30" placeholder="NIP / NIK / ID Kontrak">
                            <small class="text-muted">Isi NIK/ID Kontrak untuk Non-PNS</small>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Nama</label>
                            <input type="text" name="nama" class="form-control" required maxlength="150">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" maxlength="255" placeholder="contoh@domain.com">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label><i class="fas fa-id-card text-info mr-1"></i> ID Card (RFID)</label>
                            <div class="input-group">
                                <input type="text" id="add_id_card" name="id_card" class="form-control font-monospace" maxlength="100" placeholder="Nomor / UID Kartu RFID">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-info js-btn-scan-nfc" data-target-input="#add_id_card" title="Scan kartu menggunakan sensor NFC Handphone">
                                        <i class="fas fa-wifi mr-1"></i> Scan NFC
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted d-block">Mendukung format USB Reader (10 digit desimal) &amp; NFC Android (Hex).</small>
                            <div id="add_rfid_helper" class="mt-2 p-2 border rounded bg-light" style="display:none; font-size: 0.8rem;">
                                <div class="d-flex align-items-center justify-content-between mb-1 pb-1 border-bottom">
                                    <span class="font-weight-bold text-dark"><i class="fas fa-microchip text-info mr-1"></i> Deteksi Format RFID Mifare</span>
                                    <span class="badge badge-success" id="add_rfid_badge">Mifare 4-Byte</span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <div class="mr-2 text-truncate">
                                        <span class="text-muted">Reader USB (Desimal):</span>
                                        <strong id="add_rfid_dec" class="text-primary font-monospace ml-1">-</strong>
                                    </div>
                                    <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2 flex-shrink-0" id="add_btn_use_dec" title="Gunakan format desimal ini">Pilih</button>
                                </div>
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="mr-2 text-truncate">
                                        <span class="text-muted">NFC Android (Hex):</span>
                                        <strong id="add_rfid_hex" class="text-success font-monospace ml-1">-</strong>
                                    </div>
                                    <button type="button" class="btn btn-xs btn-outline-success py-0 px-2 flex-shrink-0" id="add_btn_use_hex" title="Gunakan format hex ini">Pilih</button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Foto ID Card</label>
                            <input type="file" name="foto_id_card" class="form-control" accept="image/*">
                            <small class="text-muted">Upload scan / foto kartu fisik ID Card</small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Foto Profil</label>
                            <input type="file" name="foto" class="form-control" accept="image/*">
                            <small class="text-muted">Upload foto profil pegawai</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Status</label>
                            <select name="is_active" class="form-control" required>
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Jabatan (Fungsional/Pelaksana/Opsional)</label>
                        <select name="jabatan_utama_id" class="form-control">
                            <option value="">Kosongkan jika tidak ada (Tanpa Jabatan)</option>
                            <?php foreach (($jabatan_utama_options ?? []) as $option): ?>
                                <option value="<?= esc((string) ($option['id'] ?? '')); ?>"><?= esc((string) ($option['label'] ?? '')); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Kosongkan untuk Konsultan, Security, CS, PPNPN, atau Non-ASN yang tidak memiliki jabatan.</small>
                    </div>
                    <div class="form-group">
                        <label>Jabatan (Perbendaharaan/Opsional)</label>
                        <select name="jabatan_perbendaharaan_id" class="form-control">
                            <option value="">Kosongkan jika tidak ada</option>
                            <?php foreach (($jabatan_perbendaharaan_options ?? []) as $option): ?>
                                <option value="<?= esc((string) ($option['id'] ?? '')); ?>"><?= esc((string) ($option['label'] ?? '')); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jenis Pegawai</label>
                        <select name="jenis_pegawai" class="form-control" required>
                            <option value="pns">PNS</option>
                            <option value="cpns">CPNS</option>
                            <option value="pppk">PPPK</option>
                            <option value="ppnpn">PPNPN</option>
                            <option value="konsultan">Konsultan Individual</option>
                            <option value="security">Security (Tenaga Keamanan)</option>
                            <option value="cleaning_service">Cleaning Service (Tenaga Kebersihan)</option>
                            <option value="lainnya">Lainnya (Non-ASN)</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Eselon</label>
                            <input type="text" name="eselon" class="form-control" maxlength="50">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Golongan</label>
                            <input type="text" name="golongan" class="form-control" maxlength="50">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Masa Kerja</label>
                            <input type="text" name="masa_kerja" class="form-control js-masa-kerja-input" maxlength="50" readonly>
                            <small class="text-muted">Otomatis dihitung dari NIP.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (! empty($can_edit)): ?>
<div class="modal fade" id="modal-ubah-pegawai" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ubah Pegawai</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-ubah-pegawai" action="" method="post" enctype="multipart/form-data" data-skip-confirm="1">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>NIP / NIK / ID Kontrak</label>
                            <input type="text" id="edit_nip" name="nip" class="form-control" required maxlength="30" placeholder="NIP / NIK / ID Kontrak">
                            <small class="text-muted">Isi NIK/ID Kontrak untuk Non-PNS</small>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Nama</label>
                            <input type="text" id="edit_nama" name="nama" class="form-control" required maxlength="150">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Email</label>
                            <input type="email" id="edit_email" name="email" class="form-control" maxlength="255" placeholder="contoh@domain.com">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label><i class="fas fa-id-card text-info mr-1"></i> ID Card (RFID)</label>
                            <div class="input-group">
                                <input type="text" id="edit_id_card" name="id_card" class="form-control font-monospace" maxlength="100" placeholder="Nomor / UID Kartu RFID">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-info js-btn-scan-nfc" data-target-input="#edit_id_card" title="Scan kartu menggunakan sensor NFC Handphone">
                                        <i class="fas fa-wifi mr-1"></i> Scan NFC
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted d-block">Mendukung format USB Reader (10 digit desimal) &amp; NFC Android (Hex).</small>
                            <div id="edit_rfid_helper" class="mt-2 p-2 border rounded bg-light" style="display:none; font-size: 0.8rem;">
                                <div class="d-flex align-items-center justify-content-between mb-1 pb-1 border-bottom">
                                    <span class="font-weight-bold text-dark"><i class="fas fa-microchip text-info mr-1"></i> Deteksi Format RFID Mifare</span>
                                    <span class="badge badge-success" id="edit_rfid_badge">Mifare 4-Byte</span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <div class="mr-2 text-truncate">
                                        <span class="text-muted">Reader USB (Desimal):</span>
                                        <strong id="edit_rfid_dec" class="text-primary font-monospace ml-1">-</strong>
                                    </div>
                                    <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2 flex-shrink-0" id="edit_btn_use_dec" title="Gunakan format desimal ini">Pilih</button>
                                </div>
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="mr-2 text-truncate">
                                        <span class="text-muted">NFC Android (Hex):</span>
                                        <strong id="edit_rfid_hex" class="text-success font-monospace ml-1">-</strong>
                                    </div>
                                    <button type="button" class="btn btn-xs btn-outline-success py-0 px-2 flex-shrink-0" id="edit_btn_use_hex" title="Gunakan format hex ini">Pilih</button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Foto ID Card</label>
                            <input type="file" id="edit_foto_id_card" name="foto_id_card" class="form-control" accept="image/*">
                            <small class="text-muted">Kosongkan jika foto ID Card tidak diubah.</small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Foto Profil</label>
                            <input type="file" name="foto" class="form-control" accept="image/*">
                            <small class="text-muted">Kosongkan jika foto profil tidak diubah.</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Status</label>
                            <select id="edit_is_active" name="is_active" class="form-control" required>
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row mb-3">
                        <div class="col-md-6">
                            <label class="small text-muted font-weight-bold d-block mb-1">Preview Foto Profil:</label>
                            <img id="edit_foto_preview" src="" alt="Preview Foto Profil" style="display:none;width:56px;height:56px;border-radius:6px;object-fit:cover;border:1px solid #dee2e6;">
                            <span id="edit_foto_empty_text" class="text-muted small italic" style="display:none;">Belum ada foto profil</span>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted font-weight-bold d-block mb-1">Preview Foto ID Card:</label>
                            <img id="edit_foto_id_card_preview" src="" alt="Preview Foto ID Card" style="display:none;width:90px;height:56px;border-radius:6px;object-fit:cover;border:1px solid #17a2b8;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                            <span id="edit_foto_id_card_empty_text" class="text-muted small italic" style="display:none;">Belum ada foto ID Card</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Jabatan (Fungsional/Pelaksana/Opsional)</label>
                        <select id="edit_jabatan_utama_id" name="jabatan_utama_id" class="form-control">
                            <option value="">Kosongkan jika tidak ada (Tanpa Jabatan)</option>
                            <?php foreach (($jabatan_utama_options ?? []) as $option): ?>
                                <option value="<?= esc((string) ($option['id'] ?? '')); ?>"><?= esc((string) ($option['label'] ?? '')); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Kosongkan untuk Konsultan, Security, CS, PPNPN, atau Non-ASN yang tidak memiliki jabatan.</small>
                    </div>
                    <div class="form-group">
                        <label>Jabatan (Perbendaharaan/Opsional)</label>
                        <select id="edit_jabatan_perbendaharaan_id" name="jabatan_perbendaharaan_id" class="form-control">
                            <option value="">Kosongkan jika tidak ada</option>
                            <?php foreach (($jabatan_perbendaharaan_options ?? []) as $option): ?>
                                <option value="<?= esc((string) ($option['id'] ?? '')); ?>"><?= esc((string) ($option['label'] ?? '')); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jenis Pegawai</label>
                        <select id="edit_jenis_pegawai" name="jenis_pegawai" class="form-control" required>
                            <option value="pns">PNS</option>
                            <option value="cpns">CPNS</option>
                            <option value="pppk">PPPK</option>
                            <option value="ppnpn">PPNPN</option>
                            <option value="konsultan">Konsultan Individual</option>
                            <option value="security">Security (Tenaga Keamanan)</option>
                            <option value="cleaning_service">Cleaning Service (Tenaga Kebersihan)</option>
                            <option value="lainnya">Lainnya (Non-ASN)</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Eselon</label>
                            <input type="text" id="edit_eselon" name="eselon" class="form-control" maxlength="50">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Golongan</label>
                            <input type="text" id="edit_golongan" name="golongan" class="form-control" maxlength="50">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Masa Kerja</label>
                            <input type="text" id="edit_masa_kerja" name="masa_kerja" class="form-control js-masa-kerja-input" maxlength="50" readonly>
                            <small class="text-muted">Otomatis dihitung dari NIP.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="modal fade" id="modal-foto-pegawai" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fotoPegawaiModalTitle">Foto Pegawai</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="fotoPegawaiModalImage" src="" alt="Foto" style="max-width: 100%; max-height: 70vh; border-radius: 8px; border: 1px solid #dee2e6; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-nfc-scanner" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content text-center shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-body p-4">
                <div class="nfc-pulse-wrapper mb-3" style="position: relative; display: inline-block; width: 80px; height: 80px;">
                    <div class="nfc-pulse-ring" style="position: absolute; width: 100%; height: 100%; border-radius: 50%; border: 3px solid #17a2b8; animation: nfcPulseAnim 1.8s infinite ease-out;"></div>
                    <div style="width: 80px; height: 80px; border-radius: 50%; background: #e0f2fe; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-wifi text-info" style="font-size: 2.2rem; transform: rotate(45deg);"></i>
                    </div>
                </div>
                <h5 class="font-weight-bold text-dark mb-1" id="nfc-modal-title">Mendengarkan Kartu NFC...</h5>
                <p class="text-muted small mb-3" id="nfc-modal-desc">Tempelkan kartu RFID / Mifare ke <strong>bodi belakang smartphone</strong> Anda.</p>
                <div class="alert alert-info py-1 px-2 small mb-2" style="font-size: 0.78rem;">
                    <i class="fas fa-info-circle mr-1"></i> Pastikan NFC aktif &amp; posisi kartu tepat di bodi belakang HP.
                </div>
                <div class="text-muted small mb-3" style="font-size: 0.72rem; line-height: 1.4;">
                    <i class="fas fa-question-circle mr-1 text-secondary"></i> Kartu Mifare tidak merespons? Web Chrome hanya membaca kartu berformat <strong>NDEF</strong>. Format kartu sekali via menu <em>Write &gt; Text</em> di aplikasi <strong>NFC Tools</strong>, atau gunakan scanner USB Reader di PC.
                </div>
                <button type="button" class="btn btn-secondary btn-sm px-4" id="btn-cancel-nfc" style="border-radius: 20px;">
                    <i class="fas fa-times mr-1"></i> Batal
                </button>
            </div>
        </div>
    </div>
</div>
<style>
@keyframes nfcPulseAnim {
    0% { transform: scale(0.85); opacity: 0.9; }
    100% { transform: scale(1.45); opacity: 0; }
}
</style>
<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script>
    (function () {
        const masaKerjaInputs = Array.from(document.querySelectorAll('.js-masa-kerja-input'));

        const computeMasaKerjaFromNip = (nip) => {
            const digits = (nip || '').replace(/\D+/g, '');
            if (digits.length < 14) {
                return '';
            }

            const year = Number(digits.slice(8, 12));
            const month = Number(digits.slice(12, 14));
            if (!year || !month || month < 1 || month > 12 || year < 1950) {
                return '';
            }

            const currentYear = new Date().getFullYear();
            if (year > currentYear) {
                return '';
            }

            const tmtDate = new Date(year, month - 1, 1);
            if (Number.isNaN(tmtDate.getTime())) {
                return '';
            }

            const today = new Date();
            if (tmtDate > today) {
                return '';
            }

            let years = today.getFullYear() - tmtDate.getFullYear();
            let months = today.getMonth() - tmtDate.getMonth();
            if (today.getDate() < tmtDate.getDate()) {
                months -= 1;
            }
            if (months < 0) {
                years -= 1;
                months += 12;
            }

            const parts = [];
            if (years > 0) {
                parts.push(years + ' Tahun');
            }
            if (months > 0) {
                parts.push(months + ' Bulan');
            }

            return parts.length > 0 ? parts.join(' ') : '0 Bulan';
        };

        const syncMasaKerja = (nipValue, fallbackValue = '') => {
            const computed = computeMasaKerjaFromNip(nipValue);
            const nextValue = computed || fallbackValue || '';
            masaKerjaInputs.forEach((input) => {
                input.value = nextValue;
                if (computed) {
                    input.readOnly = true;
                } else {
                    input.readOnly = false;
                }
            });
        };

        const initNipAutoFill = (inputSelector) => {
            const input = document.querySelector(inputSelector);
            if (!input) {
                return;
            }

            input.addEventListener('input', function () {
                syncMasaKerja(this.value);
            });
        };

        initNipAutoFill('input[name="nip"]');
        initNipAutoFill('#edit_nip');

        // Auto-clear Jabatan when Non-PNS type is selected
        document.querySelectorAll('select[name="jenis_pegawai"], #edit_jenis_pegawai').forEach(selectEl => {
            selectEl.addEventListener('change', function() {
                const val = this.value;
                if (['konsultan', 'security', 'cleaning_service', 'lainnya'].includes(val)) {
                    const targetJabatan = this.id === 'edit_jenis_pegawai' 
                        ? document.getElementById('edit_jabatan_utama_id') 
                        : (this.form ? this.form.querySelector('select[name="jabatan_utama_id"]') : null);
                    if (targetJabatan) {
                        targetJabatan.value = '';
                    }
                }
            });
        });
    })();

    (function () {
        const modalEdit = document.getElementById('modal-ubah-pegawai');
        if (!modalEdit) return;

        const form = document.getElementById('form-ubah-pegawai');
        const fieldNip = document.getElementById('edit_nip');
        const fieldNama = document.getElementById('edit_nama');
        const fieldEmail = document.getElementById('edit_email');
        const fieldIdCard = document.getElementById('edit_id_card');
        const fieldJabatanUtama = document.getElementById('edit_jabatan_utama_id');
        const fieldJabatanPerbend = document.getElementById('edit_jabatan_perbendaharaan_id');
        const fieldJenisPegawai = document.getElementById('edit_jenis_pegawai');
        const fieldEselon = document.getElementById('edit_eselon');
        const fieldGolongan = document.getElementById('edit_golongan');
        const fieldMasaKerja = document.getElementById('edit_masa_kerja');
        const fieldStatus = document.getElementById('edit_is_active');
        const fotoPreview = document.getElementById('edit_foto_preview');
        const fotoEmptyText = document.getElementById('edit_foto_empty_text');
        const fotoIdCardPreview = document.getElementById('edit_foto_id_card_preview');
        const fotoIdCardEmptyText = document.getElementById('edit_foto_id_card_empty_text');

        const applyEditData = (el) => {
            if (!el) {
                return;
            }
            const trigger = el.closest('button[data-target="#modal-ubah-pegawai"]') || el;

            const id = trigger.getAttribute('data-id') || '';
            form.action = '<?= site_url('/admin/master/pegawai'); ?>/' + encodeURIComponent(id) + '/ubah';
            fieldNip.value = trigger.getAttribute('data-nip') || '';
            fieldNama.value = trigger.getAttribute('data-nama') || '';
            if (fieldEmail) fieldEmail.value = trigger.getAttribute('data-email') || '';
            if (fieldIdCard) {
                fieldIdCard.value = trigger.getAttribute('data-id_card') || '';
                fieldIdCard.dispatchEvent(new Event('input'));
            }
            fieldJabatanUtama.value = trigger.getAttribute('data-jabatan_utama_id') || '';
            fieldJabatanPerbend.value = trigger.getAttribute('data-jabatan_perbendaharaan_id') || '';
            fieldJenisPegawai.value = (trigger.getAttribute('data-jenis_pegawai') || 'pns').toLowerCase();
            fieldEselon.value = trigger.getAttribute('data-eselon') || '';
            fieldGolongan.value = trigger.getAttribute('data-golongan') || '';
            fieldMasaKerja.value = trigger.getAttribute('data-masa_kerja') || '';
            fieldStatus.value = trigger.getAttribute('data-is_active') || '1';

            syncMasaKerja(fieldNip.value, fieldMasaKerja.value);

            const fotoUrl = trigger.getAttribute('data-foto-url') || '';
            if (fotoUrl) {
                fotoPreview.src = fotoUrl;
                fotoPreview.style.display = 'inline-block';
                if (fotoEmptyText) fotoEmptyText.style.display = 'none';
            } else {
                fotoPreview.src = '';
                fotoPreview.style.display = 'none';
                if (fotoEmptyText) fotoEmptyText.style.display = 'inline-block';
            }

            const fotoIdCardUrl = trigger.getAttribute('data-foto_id_card-url') || '';
            if (fotoIdCardUrl) {
                fotoIdCardPreview.src = fotoIdCardUrl;
                fotoIdCardPreview.style.display = 'inline-block';
                if (fotoIdCardEmptyText) fotoIdCardEmptyText.style.display = 'none';
            } else {
                fotoIdCardPreview.src = '';
                fotoIdCardPreview.style.display = 'none';
                if (fotoIdCardEmptyText) fotoIdCardEmptyText.style.display = 'inline-block';
            }
        };

        document.addEventListener('click', function (event) {
            const trigger = event.target.closest('button[data-target="#modal-ubah-pegawai"]');
            if (!trigger) {
                return;
            }

            applyEditData(trigger);
        });

        modalEdit.addEventListener('show.bs.modal', function (event) {
            applyEditData(event.relatedTarget);
        });
    })();

    (function () {
        const fotoModal = document.getElementById('modal-foto-pegawai');
        if (!fotoModal) return;

        const fotoTitle = document.getElementById('fotoPegawaiModalTitle');
        const fotoImage = document.getElementById('fotoPegawaiModalImage');

        document.addEventListener('click', function (event) {
            const trigger = event.target.closest('.js-open-foto-modal');
            if (!trigger) {
                return;
            }

            const fotoUrl = trigger.getAttribute('data-foto-url') || '';
            const nama = trigger.getAttribute('data-nama') || 'Foto';

            fotoTitle.textContent = nama;
            fotoImage.src = fotoUrl;

            if (typeof $ !== 'undefined') {
                $('#modal-foto-pegawai').modal('show');
            }
        });

        fotoModal.addEventListener('hidden.bs.modal', function () {
            fotoImage.src = '';
        });
    })();

    // AJAX Form Submission and DataTable reloading
    function refreshTable() {
        const tableElement = $('.js-datatable');
        let currentPage = 0;
        let currentSearch = '';
        let currentOrder = [];

        if ($.fn.DataTable.isDataTable(tableElement)) {
            const dt = tableElement.DataTable();
            currentPage = dt.page();
            currentSearch = dt.search();
            currentOrder = dt.order();
            dt.destroy();
        }

        $.ajax({
            url: '<?= site_url('/admin/master/pegawai'); ?>',
            method: 'GET',
            success: function (html) {
                const newTableHtml = $(html).find('.js-datatable').html();
                tableElement.html(newTableHtml);

                const newDt = tableElement.DataTable({
                    responsive: false,
                    autoWidth: false,
                    scrollX: true,
                    scrollCollapse: true,
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
                            previous: 'Sebelumnya'
                        }
                    }
                });

                if (currentOrder && currentOrder.length > 0) {
                    newDt.order(currentOrder);
                }
                if (currentSearch) {
                    newDt.search(currentSearch);
                }

                newDt.page(currentPage).draw(false);
            },
            error: function () {
                Swal.fire('Error', 'Gagal memuat ulang data tabel.', 'error');
            }
        });
    }

    function submitFormAjax(form, modalSelector = null, isCreate = false) {
        const submitBtn = $(form).find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        const formData = new FormData(form);

        $.ajax({
            url: $(form).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'JSON',
            beforeSend: function () {
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');
            },
            success: function (response) {
                submitBtn.prop('disabled', false).html(originalBtnText);

                if (response.csrf_hash) {
                    $('input[name="<?= csrf_token(); ?>"]').val(response.csrf_hash);
                }

                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });

                    if (modalSelector) {
                        $(modalSelector).modal('hide');
                    }

                    if (isCreate) {
                        form.reset();
                        $(form).find('.js-masa-kerja-input').val('');
                    }

                    refreshTable();
                } else {
                    Swal.fire('Gagal', response.message || 'Terjadi kesalahan.', 'error');
                }
            },
            error: function (xhr) {
                submitBtn.prop('disabled', false).html(originalBtnText);

                if (xhr.responseJSON && xhr.responseJSON.csrf_hash) {
                    $('input[name="<?= csrf_token(); ?>"]').val(xhr.responseJSON.csrf_hash);
                }

                const errorMsg = xhr.responseJSON && xhr.responseJSON.message 
                    ? xhr.responseJSON.message 
                    : 'Terjadi kesalahan sistem. Silakan coba lagi.';

                Swal.fire('Error', errorMsg, 'error');
            }
        });
    }

    $(document).ready(function () {
        $('#form-tambah-pegawai').on('submit', function (e) {
            e.preventDefault();
            submitFormAjax(this, '#modal-tambah-pegawai', true);
        });

        $('#form-ubah-pegawai').on('submit', function (e) {
            e.preventDefault();
            submitFormAjax(this, '#modal-ubah-pegawai', false);
        });

        $(document).on('submit', '.js-status-form', function (e) {
            e.preventDefault();
            const form = this;
            Swal.fire({
                title: 'Konfirmasi',
                text: 'Yakin ingin mengubah status pegawai ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Ubah',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    submitFormAjax(form, null, false);
                }
            });
        });
    });

    // Initialize Select2 Multi-select on Jenis Pegawai filter
    if (typeof $ !== 'undefined' && $.fn && $.fn.select2) {
        $('#filter-jenis-pegawai').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: 'Semua Jenis Pegawai...',
            allowClear: true,
            closeOnSelect: false
        });
    }

    // Quick action: Select All / Reset Jenis Pegawai
    $(document).on('click', '.btn-select-all-jp', function (e) {
        e.preventDefault();
        $('#filter-jenis-pegawai option').prop('selected', true);
        $('#filter-jenis-pegawai').trigger('change');
    });

    $(document).on('click', '.btn-reset-jp', function (e) {
        e.preventDefault();
        $('#filter-jenis-pegawai').val(null).trigger('change');
    });

    // Custom DataTables Filter for Pegawai
    if (typeof $ !== 'undefined' && $.fn && $.fn.dataTable) {
        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            if (!$(settings.nTable).hasClass('js-pegawai-table')) {
                return true;
            }

            let selectedJenis = $('#filter-jenis-pegawai').val();
            if (!selectedJenis) {
                selectedJenis = [];
            } else if (!Array.isArray(selectedJenis)) {
                selectedJenis = [selectedJenis];
            }
            selectedJenis = selectedJenis.map(function (v) {
                return (v || '').toString().toLowerCase().trim();
            }).filter(function (v) {
                return v !== '';
            });

            const filterEselon = ($('#filter-eselon').val() || '').toLowerCase().trim();
            const filterGolongan = ($('#filter-golongan').val() || '').toLowerCase().trim();
            const filterStatus = ($('#filter-status').val() || '').toLowerCase().trim();

            const rowJenis = (data[9] || '').trim().toLowerCase();
            const rowEselon = (data[10] || '').trim().toLowerCase();
            const rowGolongan = (data[11] || '').trim().toLowerCase();
            const rowStatus = (data[13] || '').trim().toLowerCase();

            if (selectedJenis.length > 0) {
                let rawJp = '';
                if (settings.aoData && settings.aoData[dataIndex] && settings.aoData[dataIndex].nTr) {
                    const cell = $(settings.aoData[dataIndex].nTr).find('td').eq(9);
                    rawJp = (cell.attr('data-filter') || '').toLowerCase().trim();
                }
                if (!rawJp) {
                    rawJp = rowJenis;
                }

                const matchesJenis = selectedJenis.some(function (fj) {
                    if (rawJp === fj) return true;
                    if (fj === 'konsultan' && (rawJp.includes('konsultan') || rowJenis.includes('konsultan'))) return true;
                    if (fj === 'security' && (rawJp.includes('security') || rowJenis.includes('security'))) return true;
                    if (fj === 'cleaning_service' && (rawJp.includes('cleaning') || rawJp.includes('kebersihan') || rowJenis.includes('cleaning'))) return true;
                    if (fj === 'lainnya' && (rawJp.includes('lainnya') || rawJp.includes('non-asn') || rowJenis.includes('lainnya'))) return true;
                    if (rowJenis === fj || rowJenis.includes(fj)) return true;
                    return false;
                });

                if (!matchesJenis) {
                    return false;
                }
            }

            if (filterEselon && rowEselon !== filterEselon) {
                return false;
            }
            if (filterGolongan && rowGolongan !== filterGolongan) {
                return false;
            }
            if (filterStatus && rowStatus !== filterStatus) {
                return false;
            }

            return true;
        });
    }

    function syncExportUrls() {
        const params = new URLSearchParams();
        const jenisVals = $('#filter-jenis-pegawai').val();
        const eselon = ($('#filter-eselon').val() || '').trim();
        const golongan = ($('#filter-golongan').val() || '').trim();
        const status = ($('#filter-status').val() || '').trim();

        if (Array.isArray(jenisVals) && jenisVals.length > 0) {
            params.set('jenis_pegawai', jenisVals.join(','));
        } else if (typeof jenisVals === 'string' && jenisVals.trim() !== '') {
            params.set('jenis_pegawai', jenisVals.trim());
        }

        if (eselon) params.set('eselon', eselon);
        if (golongan) params.set('golongan', golongan);
        if (status) params.set('status', status);

        const qs = params.toString() ? ('?' + params.toString()) : '';
        const baseUrlExcel = '<?= site_url('/admin/master/pegawai/export'); ?>';
        const baseUrlPdf = '<?= site_url('/admin/master/pegawai/export-pdf'); ?>';

        $('#btn-export-excel').attr('href', baseUrlExcel + qs);
        $('#btn-export-pdf').attr('href', baseUrlPdf + qs);
    }

    function updatePegawaiFilterIndicator() {
        let activeCount = 0;

        const selectedJenis = $('#filter-jenis-pegawai').val();
        if (Array.isArray(selectedJenis) && selectedJenis.length > 0) {
            activeCount++;
            $('.select2-filter-jp-wrapper .select2-selection').addClass('border-info');
        } else {
            $('.select2-filter-jp-wrapper .select2-selection').removeClass('border-info');
        }

        $('#filter-eselon, #filter-golongan, #filter-status').each(function () {
            if ($(this).val() !== '') {
                activeCount++;
                $(this).addClass('border-info text-info font-weight-bold');
            } else {
                $(this).removeClass('border-info text-info font-weight-bold');
            }
        });

        if (activeCount > 0) {
            $('#btn-reset-filter').show();
            $('#active-filter-badge').text(activeCount + ' Filter Aktif').show();
        } else {
            $('#btn-reset-filter').hide();
            $('#active-filter-badge').hide();
        }

        syncExportUrls();
    }

    $(document).on('change', '#filter-jenis-pegawai, #filter-eselon, #filter-golongan, #filter-status', function () {
        updatePegawaiFilterIndicator();
        if ($.fn.DataTable.isDataTable('.js-pegawai-table')) {
            $('.js-pegawai-table').DataTable().draw();
        }
    });

    $(document).on('click', '#btn-reset-filter', function () {
        $('#filter-jenis-pegawai').val(null).trigger('change');
        $('#filter-eselon').val('');
        $('#filter-golongan').val('');
        $('#filter-status').val('');
        updatePegawaiFilterIndicator();
        if ($.fn.DataTable.isDataTable('.js-pegawai-table')) {
            $('.js-pegawai-table').DataTable().draw();
        }
    });
    // Global RFID Live Converter Function (Mifare 4-Byte Hex <-> USB Decimal Little-Endian)
    window.parseRfid = function parseRfid(val) {
        if (!val) return null;
        val = String(val).trim();
        if (!val) return null;

        // Numeric decimal (e.g. 3188450969)
        if (/^\d+$/.test(val)) {
            const num = Number(val);
            if (num >= 0 && num <= 4294967295) {
                const hex = num.toString(16).padStart(8, '0');
                const b3 = hex.substr(0, 2);
                const b2 = hex.substr(2, 2);
                const b1 = hex.substr(4, 2);
                const b0 = hex.substr(6, 2);
                const nfcHex = `${b0}:${b1}:${b2}:${b3}`.toUpperCase();
                return {
                    type: 'decimal',
                    decimal: String(num),
                    hex: nfcHex,
                    badgeText: 'Mifare 4-Byte'
                };
            }
        }

        // Hex (e.g. 99:e6:0b:be or 99e60bbe or 99-e6-0b-be)
        const cleanHex = val.replace(/[^0-9a-fA-F]/g, '');
        if (cleanHex.length === 8) {
            const b0 = cleanHex.substr(0, 2);
            const b1 = cleanHex.substr(2, 2);
            const b2 = cleanHex.substr(4, 2);
            const b3 = cleanHex.substr(6, 2);
            const littleEndianHex = `${b3}${b2}${b1}${b0}`;
            const dec = parseInt(littleEndianHex, 16);
            const nfcHex = `${b0}:${b1}:${b2}:${b3}`.toUpperCase();
            return {
                type: 'hex_4byte',
                decimal: String(dec),
                hex: nfcHex,
                badgeText: 'Mifare 4-Byte'
            };
        }

        if (cleanHex.length === 14) {
            const chunks = cleanHex.match(/.{1,2}/g) || [];
            const nfcHex = chunks.join(':').toUpperCase();
            return {
                type: 'hex_7byte',
                decimal: '-',
                hex: nfcHex,
                badgeText: 'Mifare 7-Byte'
            };
        }

        return null;
    };

    // RFID Live Converter Helper for Tambah & Ubah Modal
    (function () {
        const parseRfid = window.parseRfid;

        function setupRfidHelper(inputId, helperId, decId, hexId, badgeId, btnDecId, btnHexId) {
            const input = document.getElementById(inputId);
            const helper = document.getElementById(helperId);
            const decEl = document.getElementById(decId);
            const hexEl = document.getElementById(hexId);
            const badgeEl = document.getElementById(badgeId);
            const btnDec = document.getElementById(btnDecId);
            const btnHex = document.getElementById(btnHexId);

            if (!input || !helper) return null;

            function update() {
                const res = parseRfid(input.value);
                if (res) {
                    helper.style.display = 'block';
                    if (decEl) decEl.textContent = res.decimal;
                    if (hexEl) hexEl.textContent = res.hex;
                    if (badgeEl) badgeEl.textContent = res.badgeText;
                    if (btnDec) btnDec.style.display = (res.decimal && res.decimal !== '-') ? 'inline-block' : 'none';
                    if (btnHex) btnHex.style.display = res.hex ? 'inline-block' : 'none';
                } else {
                    helper.style.display = 'none';
                }
            }

            input.addEventListener('input', update);
            input.addEventListener('change', update);

            if (btnDec) {
                btnDec.addEventListener('click', function () {
                    const res = parseRfid(input.value);
                    if (res && res.decimal && res.decimal !== '-') {
                        input.value = res.decimal;
                        update();
                    }
                });
            }

            if (btnHex) {
                btnHex.addEventListener('click', function () {
                    const res = parseRfid(input.value);
                    if (res && res.hex) {
                        input.value = res.hex;
                        update();
                    }
                });
            }

            return update;
        }

        const updateAdd = setupRfidHelper('add_id_card', 'add_rfid_helper', 'add_rfid_dec', 'add_rfid_hex', 'add_rfid_badge', 'add_btn_use_dec', 'add_btn_use_hex');
        const updateEdit = setupRfidHelper('edit_id_card', 'edit_rfid_helper', 'edit_rfid_dec', 'edit_rfid_hex', 'edit_rfid_badge', 'edit_btn_use_dec', 'edit_btn_use_hex');

        // Reset add helper on modal open
        const modalAdd = document.getElementById('modal-tambah-pegawai');
        if (modalAdd) {
            modalAdd.addEventListener('show.bs.modal', function () {
                setTimeout(function () {
                    if (updateAdd) updateAdd();
                }, 100);
            });
        }

        // Trigger edit helper on modal edit open
        const modalEdit = document.getElementById('modal-ubah-pegawai');
        if (modalEdit) {
            modalEdit.addEventListener('shown.bs.modal', function () {
                if (updateEdit) updateEdit();
            });
        }
    })();

    // Web NFC Scanner Integration for Smartphone Android
    (function () {
        let nfcAbortController = null;
        let activeTargetInput = null;
        const nfcModal = $('#modal-nfc-scanner');

        $(document).on('click', '.js-btn-scan-nfc', async function () {
            const targetSelector = $(this).data('target-input');
            const targetInput = document.querySelector(targetSelector);
            if (!targetInput) return;

            activeTargetInput = targetInput;

            // 1. Check if Web NFC (NDEFReader) is supported
            if (!('NDEFReader' in window)) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Web NFC Tidak Didukung di Perangkat Ini',
                        html: '<div class="text-left small" style="line-height:1.6;">' +
                            '<p>Fitur <strong>Scan NFC langsung lewat browser</strong> membutuhkan:</p>' +
                            '<ul>' +
                            '<li><strong>Smartphone Android</strong> dengan sensor hardware NFC aktif.</li>' +
                            '<li>Browser <strong>Google Chrome</strong>, <strong>Microsoft Edge</strong>, atau <strong>Samsung Internet</strong>.</li>' +
                            '<li>Koneksi aman <strong>HTTPS</strong> (sudah terpenuhi).</li>' +
                            '</ul>' +
                            '<p class="mb-0 text-muted"><em>Catatan untuk Desktop/Laptop: Silakan gunakan alat USB RFID Reader (otomatis terdeteksi saat kartu di-tap) atau ketik nomor kartu secara manual.</em></p>' +
                            '</div>',
                        confirmButtonText: 'Mengerti',
                        confirmButtonColor: '#17a2b8'
                    });
                } else {
                    alert('Web NFC tidak didukung di perangkat ini. Pada PC/Laptop silakan gunakan USB RFID Reader.');
                }
                return;
            }

            // 2. Web NFC is supported, start scanning
            try {
                nfcAbortController = new AbortController();
                const ndef = new NDEFReader();

                $('#nfc-modal-title').text('Mendengarkan Kartu NFC...');
                $('#nfc-modal-desc').html('Tempelkan kartu RFID / Mifare ke <strong>bodi belakang smartphone</strong> Anda.');
                nfcModal.modal('show');

                await ndef.scan({ signal: nfcAbortController.signal });

                ndef.onreading = (event) => {
                    try {
                        const serial = (event.serialNumber || '').trim();
                        if (navigator.vibrate) {
                            navigator.vibrate([100, 50, 100]);
                        }

                        if (activeTargetInput && serial) {
                            activeTargetInput.value = serial;
                            activeTargetInput.dispatchEvent(new Event('input'));
                            activeTargetInput.dispatchEvent(new Event('change'));
                        }

                        if (nfcAbortController) {
                            nfcAbortController.abort();
                            nfcAbortController = null;
                        }
                        nfcModal.modal('hide');

                        if (typeof toastr !== 'undefined') {
                            toastr.success('Kartu NFC berhasil terdeteksi: ' + (serial || 'Berhasil dibaca'), 'Scan NFC Berhasil');
                        }
                    } catch (scanErr) {
                        console.error('NFC Scan onreading error:', scanErr);
                    }
                };

                ndef.onreadingerror = () => {
                    if (typeof toastr !== 'undefined') {
                        toastr.warning('Kartu terdeteksi tetapi gagal dibaca. Coba tempelkan kembali lebih dekat.', 'Peringatan');
                    }
                };

            } catch (err) {
                if (nfcAbortController) {
                    nfcAbortController.abort();
                    nfcAbortController = null;
                }
                nfcModal.modal('hide');

                if (err.name !== 'AbortError') {
                    console.error('NFC Scan Error:', err);
                    const isPermissionDenied = err.name === 'NotAllowedError' || (err.message && err.message.toLowerCase().includes('permission'));
                    if (isPermissionDenied) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Izin NFC Belum Diizinkan',
                                html: '<div class="text-left small" style="line-height: 1.6;">' +
                                    '<p class="mb-2">Browser Chrome di HP Anda memblokir izin NFC untuk website ini (<em>permission request denied</em>).</p>' +
                                    '<div class="p-2 bg-light border rounded mb-2">' +
                                    '<strong>Cara Membuka Izin NFC di Chrome HP:</strong>' +
                                    '<ol class="pl-3 mb-0 mt-1">' +
                                    '<li>Lihat kolom alamat website (URL) di bagian paling atas Chrome.</li>' +
                                    '<li>Klik ikon <strong>Gembok / Setelan Situs</strong> (di sebelah kiri tulisan <code>https://...</code>).</li>' +
                                    '<li>Pilih menu <strong>Izin (Permissions)</strong> atau <strong>Setelan Situs</strong>.</li>' +
                                    '<li>Cari item <strong>NFC</strong> lalu ubah menjadi <strong>Izinkan (Allow)</strong> atau klik <strong>Reset Izin</strong>.</li>' +
                                    '<li>Muat ulang (refresh) halaman dan klik tombol <strong>Scan NFC</strong> kembali.</li>' +
                                    '</ol>' +
                                    '</div>' +
                                    '<p class="mb-0 text-muted"><em>Pastikan juga sensor NFC di menu Pengaturan / Tirai Notifikasi HP sudah dalam kondisi Aktif (ON).</em></p>' +
                                    '</div>',
                                confirmButtonText: 'Saya Mengerti',
                                confirmButtonColor: '#17a2b8'
                            });
                        } else if (typeof toastr !== 'undefined') {
                            toastr.warning('Izin NFC diblokir di Chrome. Buka ikon gembok di sebelah URL untuk mengizinkan NFC.', 'Izin Ditolak');
                        }
                    } else {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(err.message || 'Gagal memulai pemindaian NFC. Pastikan NFC di HP Anda dalam kondisi aktif.', 'Error NFC');
                        } else {
                            alert('Gagal memulai pemindaian NFC: ' + (err.message || 'Pastikan NFC aktif.'));
                        }
                    }
                }
            }
        });

        // Handler for "Cari via NFC" button
        $(document).on('click', '.js-btn-search-nfc', async function () {
            // 1. Check if Web NFC is supported
            if (!('NDEFReader' in window)) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Pencarian Kartu di Komputer / Laptop',
                        html: '<div class="text-left small" style="line-height:1.6;">' +
                            '<p>Untuk mencari data pegawai menggunakan kartu RFID di <strong>Komputer / Laptop</strong>:</p>' +
                            '<ol class="pl-3 mb-2">' +
                            '<li>Klik kursor pada kotak <strong>"Cari:" (Search)</strong> di atas tabel pegawai.</li>' +
                            '<li>Tempelkan kartu ke alat <strong>USB RFID Reader</strong>.</li>' +
                            '<li>Alat akan otomatis mengetik nomor kartu dan tabel langsung menyaring pegawai yang bersangkutan.</li>' +
                            '</ol>' +
                            '<p class="mb-0 text-muted"><em>Tombol "Cari via NFC" langsung dari browser ini didukung pada smartphone Android ber-NFC via Google Chrome / Edge.</em></p>' +
                            '</div>',
                        confirmButtonText: 'Mengerti',
                        confirmButtonColor: '#17a2b8'
                    });
                } else {
                    alert('Di PC/Laptop, silakan klik kotak Cari dan tap kartu di USB RFID Reader.');
                }
                return;
            }

            // 2. Start scanning
            try {
                nfcAbortController = new AbortController();
                const ndef = new NDEFReader();

                $('#nfc-modal-title').text('Mencari Data Pegawai...');
                $('#nfc-modal-desc').html('Tempelkan kartu pegawai ke <strong>bodi belakang smartphone</strong> untuk mencari data.');
                nfcModal.modal('show');

                await ndef.scan({ signal: nfcAbortController.signal });

                ndef.onreading = async (event) => {
                    try {
                        const serial = (event.serialNumber || '').trim();
                        if (navigator.vibrate) {
                            navigator.vibrate([100, 50, 100]);
                        }

                        if (nfcAbortController) {
                            nfcAbortController.abort();
                            nfcAbortController = null;
                        }

                        // Close scan modal safely and wait for Bootstrap modal fade-out to finish
                        // to prevent SweetAlert2 backdrop conflict
                        await new Promise(function (resolve) {
                            if (nfcModal.hasClass('show')) {
                                nfcModal.one('hidden.bs.modal', function () {
                                    setTimeout(resolve, 80);
                                });
                                nfcModal.modal('hide');
                                setTimeout(resolve, 400); // Safety fallback timeout
                            } else {
                                nfcModal.modal('hide');
                                resolve();
                            }
                        });

                        if (!serial) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'UID Kartu Tidak Terdeteksi',
                                    text: 'Kartu berhasil dibaca namun browser tidak menerima UID kartu. Coba tempelkan kembali.',
                                    confirmButtonText: 'Tutup'
                                });
                            } else {
                                alert('UID Kartu tidak terdeteksi.');
                            }
                            return;
                        }

                        // Convert to get all equivalent representations (Hex Big-Endian <-> Decimal Little-Endian)
                        const cleanSerial = serial.replace(/[^0-9a-fA-F]/g, '').toLowerCase();
                        const rfidRes = typeof window.parseRfid === 'function' ? window.parseRfid(serial) : null;

                        const candidateSet = new Set();
                        candidateSet.add(serial.toLowerCase());
                        candidateSet.add(serial.toUpperCase());
                        if (cleanSerial) {
                            candidateSet.add(cleanSerial.toLowerCase());
                            candidateSet.add(cleanSerial.toUpperCase());
                        }
                        if (rfidRes) {
                            if (rfidRes.decimal && rfidRes.decimal !== '-') {
                                candidateSet.add(String(rfidRes.decimal).trim());
                            }
                            if (rfidRes.hex) {
                                candidateSet.add(rfidRes.hex.toLowerCase().trim());
                                candidateSet.add(rfidRes.hex.toUpperCase().trim());
                                const cleanHex = rfidRes.hex.replace(/[^0-9a-fA-F]/g, '');
                                candidateSet.add(cleanHex.toLowerCase());
                                candidateSet.add(cleanHex.toUpperCase());
                            }
                        }
                        const candidates = Array.from(candidateSet);

                        // Find matching row across ALL pages/records in DataTables
                        let matchedData = null;

                        function evaluateRow(el) {
                            const $row = $(el);
                            const rowCard = ($row.attr('data-id_card') || '').trim();
                            const rowCounterpart = ($row.attr('data-rfid_counterpart') || '').trim();
                            const cleanRowCard = rowCard.replace(/[^0-9a-fA-F]/g, '');
                            const cleanRowCounterpart = rowCounterpart.replace(/[^0-9a-fA-F]/g, '');

                            const rowValues = [
                                rowCard.toLowerCase(),
                                rowCard.toUpperCase(),
                                rowCounterpart.toLowerCase(),
                                rowCounterpart.toUpperCase(),
                                cleanRowCard.toLowerCase(),
                                cleanRowCard.toUpperCase(),
                                cleanRowCounterpart.toLowerCase(),
                                cleanRowCounterpart.toUpperCase()
                            ].filter(Boolean);

                            const isMatch = candidates.some(c => rowValues.includes(c));
                            if (isMatch) {
                                return {
                                    nama: $row.attr('data-nama') || 'Pegawai',
                                    nip: $row.attr('data-nip') || '',
                                    fotoUrl: $row.attr('data-foto_url') || '',
                                    jabatan: $row.attr('data-jabatan') || '-',
                                    jenis: $row.attr('data-jenis') || 'PNS',
                                    isActive: $row.attr('data-is_active') === '1',
                                    rowCardVal: rowCard || serial
                                };
                            }
                            return null;
                        }

                        if ($.fn.DataTable && $.fn.DataTable.isDataTable('.js-pegawai-table')) {
                            const dt = $('.js-pegawai-table').DataTable();
                            dt.rows().every(function () {
                                const node = this.node();
                                if (node && !matchedData) {
                                    const found = evaluateRow(node);
                                    if (found) matchedData = found;
                                }
                            });
                        }

                        if (!matchedData) {
                            $('.js-pegawai-table tbody tr').each(function () {
                                const found = evaluateRow(this);
                                if (found) {
                                    matchedData = found;
                                    return false;
                                }
                            });
                        }

                        if (matchedData) {
                            // Reset custom select dropdown filters so the row is definitely not excluded
                            $('#filter-jenis-pegawai').val(null).trigger('change');
                            $('#filter-eselon').val('');
                            $('#filter-golongan').val('');
                            $('#filter-status').val('');
                            if (typeof updatePegawaiFilterIndicator === 'function') {
                                updatePegawaiFilterIndicator();
                            }

                            // Filter the DataTables to show this pegawai
                            if ($.fn.DataTable && $.fn.DataTable.isDataTable('.js-pegawai-table')) {
                                $('.js-pegawai-table').DataTable().search(matchedData.rowCardVal).draw();
                            }

                            // Show Quick Identity Card
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    title: '<span style="font-size:1.15rem;font-weight:700;"><i class="fas fa-id-badge text-info mr-1"></i> Data Pegawai Ditemukan</span>',
                                    html: '<div class="text-center p-2">' +
                                        (matchedData.fotoUrl ? '<img src="' + matchedData.fotoUrl + '" style="width:85px;height:85px;border-radius:50%;object-fit:cover;border:3px solid #17a2b8;margin-bottom:12px;box-shadow:0 4px 10px rgba(0,0,0,0.15);">' : '<div style="width:75px;height:75px;border-radius:50%;background:#e0f2fe;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px;"><i class="fas fa-user text-info" style="font-size:2.2rem;"></i></div>') +
                                        '<h5 class="font-weight-bold text-dark mb-1">' + matchedData.nama + '</h5>' +
                                        '<div class="text-muted small mb-2">' + (matchedData.nip ? 'NIP: ' + matchedData.nip : '<span class="badge badge-light border">Non-ASN</span>') + '</div>' +
                                        '<div class="p-2 bg-light border rounded text-left small mb-2" style="font-size: 0.85rem; line-height: 1.6;">' +
                                        '<div><span class="text-muted">Jabatan:</span> <strong>' + matchedData.jabatan + '</strong></div>' +
                                        '<div><span class="text-muted">Jenis:</span> <strong class="text-primary text-uppercase">' + matchedData.jenis + '</strong></div>' +
                                        '<div><span class="text-muted">ID Card (RFID):</span> <code class="text-info font-weight-bold">' + matchedData.rowCardVal + '</code></div>' +
                                        '<div><span class="text-muted">Status:</span> ' + (matchedData.isActive ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-secondary">Nonaktif</span>') + '</div>' +
                                        '</div>' +
                                        '<div class="small text-muted font-italic"><i class="fas fa-check-circle text-success mr-1"></i>Tabel di bawah telah otomatis disaring menampilkan pegawai ini.</div>' +
                                        '</div>',
                                    confirmButtonText: 'Tutup',
                                    confirmButtonColor: '#17a2b8'
                                });
                            }
                        } else {
                            // Card not found in database
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Pegawai Tidak Ditemukan',
                                    html: '<div class="small text-left">' +
                                        '<p class="mb-2">Kartu RFID / Mifare dengan nomor berikut <strong>belum terdaftar</strong> pada database Master Pegawai:</p>' +
                                        '<div class="p-2 bg-light border rounded text-center mb-2">' +
                                        '<strong class="font-monospace text-primary" style="font-size:1.05rem;">' + serial + '</strong>' +
                                        (rfidRes && rfidRes.decimal && rfidRes.decimal !== '-' ? '<br><span class="text-muted small">Desimal USB: </span><strong class="font-monospace text-success">' + rfidRes.decimal + '</strong>' : '') +
                                        '</div>' +
                                        '<p class="mb-0 text-muted"><em>Silakan daftarkan kartu ini terlebih dahulu melalui tombol Tambah Pegawai atau Ubah data pegawai terkait.</em></p>' +
                                        '</div>',
                                    confirmButtonText: 'Tutup',
                                    confirmButtonColor: '#17a2b8'
                                });
                            }
                        }
                    } catch (readingErr) {
                        console.error('NFC Search onreading error:', readingErr);
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Memproses Data Kartu',
                                text: 'Terjadi kendala: ' + (readingErr.message || readingErr),
                                confirmButtonText: 'Tutup'
                            });
                        } else {
                            alert('Gagal memproses kartu: ' + (readingErr.message || readingErr));
                        }
                    }
                };

                ndef.onreadingerror = () => {
                    if (typeof toastr !== 'undefined') {
                        toastr.warning('Kartu terdeteksi tetapi gagal dibaca. Coba tempelkan kembali lebih dekat.', 'Peringatan');
                    }
                };
            } catch (err) {
                if (nfcAbortController) {
                    nfcAbortController.abort();
                    nfcAbortController = null;
                }
                nfcModal.modal('hide');

                if (err.name !== 'AbortError') {
                    console.error('NFC Search Error:', err);
                    const isPermissionDenied = err.name === 'NotAllowedError' || (err.message && err.message.toLowerCase().includes('permission'));
                    if (isPermissionDenied) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Izin NFC Belum Diizinkan',
                                html: '<div class="text-left small" style="line-height: 1.6;">' +
                                    '<p class="mb-2">Browser Chrome di HP Anda memblokir izin NFC untuk website ini (<em>permission request denied</em>).</p>' +
                                    '<div class="p-2 bg-light border rounded mb-2">' +
                                    '<strong>Cara Membuka Izin NFC di Chrome HP:</strong>' +
                                    '<ol class="pl-3 mb-0 mt-1">' +
                                    '<li>Lihat kolom alamat website (URL) di bagian paling atas Chrome.</li>' +
                                    '<li>Klik ikon <strong>Gembok / Setelan Situs</strong> (di sebelah kiri tulisan <code>https://...</code>).</li>' +
                                    '<li>Pilih menu <strong>Izin (Permissions)</strong> atau <strong>Setelan Situs</strong>.</li>' +
                                    '<li>Cari item <strong>NFC</strong> lalu ubah menjadi <strong>Izinkan (Allow)</strong> atau klik <strong>Reset Izin</strong>.</li>' +
                                    '<li>Muat ulang (refresh) halaman dan klik tombol <strong>Cari via NFC</strong> kembali.</li>' +
                                    '</ol>' +
                                    '</div>' +
                                    '<p class="mb-0 text-muted"><em>Pastikan juga sensor NFC di menu Pengaturan / Tirai Notifikasi HP sudah dalam kondisi Aktif (ON).</em></p>' +
                                    '</div>',
                                confirmButtonText: 'Saya Mengerti',
                                confirmButtonColor: '#17a2b8'
                            });
                        }
                    } else {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(err.message || 'Gagal memulai pemindaian NFC.', 'Error NFC');
                        }
                    }
                }
            }
        });

        // Cancel button in NFC modal
        $('#btn-cancel-nfc').on('click', function () {
            if (nfcAbortController) {
                nfcAbortController.abort();
                nfcAbortController = null;
            }
            nfcModal.modal('hide');
        });

        // Abort scan when NFC modal is closed
        nfcModal.on('hidden.bs.modal', function () {
            if (nfcAbortController) {
                nfcAbortController.abort();
                nfcAbortController = null;
            }
            // If another modal is open, keep 'modal-open' on body for proper scrolling
            if ($('.modal.show').length > 0) {
                $('body').addClass('modal-open');
            }
        });
    })();
</script>
<?= $this->endSection(); ?>