<?= $this->extend('layouts/admin'); ?>
<?php helper('custom'); ?>

<?= $this->section('content'); ?>
<style>
    .simak-list-table th,
    .simak-list-table td {
        vertical-align: middle !important;
    }

    .simak-filter-card .form-group {
        margin-bottom: 0.75rem;
    }

    .simak-filter-card label {
        margin-bottom: 0.35rem;
        font-weight: 600;
    }

    .simak-filter-card .form-control {
        height: 38px;
    }

    .simak-filter-card .form-control-sm {
        height: 38px;
        padding-top: 0.375rem;
        padding-bottom: 0.375rem;
    }

    .simak-filter-card select.form-control {
        padding-right: 2rem;
    }

    .simak-filter-card .card-body {
        padding-bottom: 0.5rem;
    }
</style>
<div class="card">
    <div class="card-header">
        <h2 class="card-title">SIMAK Kontrak</h2>
        <?php if (($can_edit ?? false) === true): ?>
            <div class="float-right">
                <?php if (($can_import ?? false) === true): ?>
                    <button type="button" class="btn btn-success mr-2" data-toggle="modal" data-target="#modal-import-simak">Import Excel</button>
                <?php endif; ?>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-tambah-simak">Input Data SIMAK</button>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= esc((string) session()->getFlashdata('success')); ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= esc((string) session()->getFlashdata('error')); ?></div>
        <?php endif; ?>

        <?php $simakShareLink = (string) (session()->getFlashdata('simak_share_link') ?? ''); ?>
        <?php if ($simakShareLink !== ''): ?>
            <div class="alert alert-info">
                <strong>Link share terbaru:</strong>
                <a href="<?= esc($simakShareLink); ?>" target="_blank" rel="noopener"><?= esc($simakShareLink); ?></a>
            </div>
        <?php endif; ?>

        <?php $simakShareNotice = (string) (session()->getFlashdata('simak_share_notice') ?? ''); ?>
        <?php if ($simakShareNotice !== ''): ?>
            <div class="alert alert-warning"><?= esc($simakShareNotice); ?></div>
        <?php endif; ?>

        <?php $importSimakReport = session()->getFlashdata('import_simak_report'); ?>
        <?php if (is_array($importSimakReport) && $importSimakReport !== []): ?>
            <div class="alert alert-warning">
                <strong>Detail Baris Import:</strong>
                <ul class="mb-0 mt-2 pl-3">
                    <?php foreach ($importSimakReport as $reportLine): ?>
                        <li><?= esc((string) $reportLine); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (! empty($error ?? '')): ?>
            <div class="alert alert-danger"><?= esc((string) $error); ?></div>
        <?php endif; ?>

        <div class="card card-outline card-secondary simak-filter-card mb-3">
            <div class="card-header py-2">
                <h3 class="card-title mb-0">Filter SIMAK</h3>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="filter_simak_nomor_kontrak">Nomor Kontrak</label>
                        <input type="text" class="form-control form-control-sm" id="filter_simak_nomor_kontrak" placeholder="Cari nomor kontrak">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="filter_simak_nama_paket">Nama Paket</label>
                        <input type="text" class="form-control form-control-sm" id="filter_simak_nama_paket" placeholder="Cari nama paket">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="filter_simak_tahun_anggaran">Tahun Anggaran</label>
                        <select class="form-control form-control-sm" id="filter_simak_tahun_anggaran">
                            <option value="">Semua</option>
                            <?php foreach (array_values(array_unique(array_filter(array_map(static function ($row) {
                                return (string) ($row['tahun_anggaran'] ?? '');
                            }, $data ?? [])))) as $tahunAnggaran): ?>
                                <option value="<?= esc($tahunAnggaran); ?>"><?= esc($tahunAnggaran); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label for="filter_simak_ppk">PPK</label>
                        <input type="text" class="form-control form-control-sm" id="filter_simak_ppk" placeholder="Cari nama / NIP">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="filter_simak_status">Status Kelengkapan</label>
                        <select class="form-control form-control-sm" id="filter_simak_status">
                            <option value="">Semua</option>
                            <option value="lengkap">Lengkap</option>
                            <option value="belum_lengkap">Belum Lengkap</option>
                            <option value="belum_ada">Belum ada</option>
                        </select>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">Filter bekerja langsung pada tabel di bawah.</small>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-reset-filter-simak">Reset Filter</button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped w-100 nowrap js-datatable simak-list-table">
                <thead>
                    <tr style="white-space: nowrap;">
                        <th class="text-center">#</th>
                        <th>Nomor Kontrak</th>
                        <th>Nama Paket</th>
                        <th>Tahun Anggaran</th>
                        <th>PPK</th>
                        <th>Tanggal Pemeriksaan</th>
                        <th class="text-right">Nilai Kontrak (Rp)</th>
                        <th class="text-right">Nilai Add On (Rp)</th>
                        <th class="text-right">Total Kontrak (Rp)</th>
                        <th class="text-center">Kelengkapan Dokumen Administrasi (%)</th>
                        <th class="text-center">Action</th>
                        <th class="text-center">Share</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; foreach (($data ?? []) as $item): ?>
                        <?php
                            $statusLengkap = (float) ($item['kelengkapan_dokumen_lengkap_persen'] ?? 0);
                            $statusBelumLengkap = (float) ($item['kelengkapan_dokumen_belum_lengkap_persen'] ?? 0);
                            $statusBelumAda = (float) ($item['kelengkapan_dokumen_belum_ada_persen'] ?? 0);
                            $statusKelengkapan = 'belum_lengkap';

                            if ($statusLengkap >= 100) {
                                $statusKelengkapan = 'lengkap';
                            } elseif ($statusBelumAda >= 100) {
                                $statusKelengkapan = 'belum_ada';
                            }
                        ?>
                        <tr data-kelengkapan-status="<?= esc($statusKelengkapan); ?>" data-kelengkapan-lengkap="<?= esc((string) $statusLengkap); ?>" data-kelengkapan-belum-lengkap="<?= esc((string) $statusBelumLengkap); ?>" data-kelengkapan-belum-ada="<?= esc((string) $statusBelumAda); ?>">
                            <td class="text-center"><?= esc((string) $i++); ?></td>
                            <td><?= esc((string) ($item['nomor_kontrak'] ?? '-')); ?></td>
                            <td><?= esc((string) ($item['nama_paket'] ?? '-')); ?></td>
                            <td><?= esc((string) ($item['tahun_anggaran'] ?? '-')); ?></td>
                            <td>
                                <div class="font-weight-bold"><?= esc((string) ($item['ppk_nama'] ?? '-')); ?></div>
                                <small class="text-muted">NIP: <?= esc((string) ($item['ppk_nip'] ?? '-')); ?></small>
                            </td>
                            <td><?= esc((string) ($item['tanggal_pemeriksaan'] ?? '-')); ?></td>
                            <td class="text-right"><?= esc(angka_ribuan_id($item['nilai_kontrak'] ?? 0)); ?></td>
                            <td class="text-right"><?= esc(angka_ribuan_id($item['nilai_add_on'] ?? 0)); ?></td>
                            <td class="text-right font-weight-bold"><?= esc(angka_ribuan_id($item['total_kontrak'] ?? 0)); ?></td>
                                <td>
                                    <div class="small">
                                        <div><strong>Lengkap:</strong> <?= esc(number_format((float) ($item['kelengkapan_dokumen_lengkap_persen'] ?? 0), 2, ',', '.')); ?>%</div>
                                        <div><strong>Belum Lengkap:</strong> <?= esc(number_format((float) ($item['kelengkapan_dokumen_belum_lengkap_persen'] ?? 0), 2, ',', '.')); ?>%</div>
                                        <div><strong>Belum ada:</strong> <?= esc(number_format((float) ($item['kelengkapan_dokumen_belum_ada_persen'] ?? 0), 2, ',', '.')); ?>%</div>
                                    </div>
                                </td>
                            <td class="text-center">
                                <?php if (($can_edit ?? false) === true): ?>
                                    <button
                                        type="button"
                                        class="btn btn-warning btn-sm js-open-edit-simak"
                                        data-id="<?= esc((string) ($item['id'] ?? 0)); ?>"
                                        data-satker="<?= esc((string) ($item['satker'] ?? 'Perencanaan Prasarana Strategis')); ?>"
                                        data-ppk-nip="<?= esc((string) ($item['ppk_nip'] ?? '')); ?>"
                                        data-nama-paket="<?= esc((string) ($item['nama_paket'] ?? '')); ?>"
                                        data-tahun-anggaran="<?= esc((string) ($item['tahun_anggaran'] ?? '')); ?>"
                                        data-penyedia="<?= esc((string) ($item['penyedia'] ?? '')); ?>"
                                        data-nomor-kontrak="<?= esc((string) ($item['nomor_kontrak'] ?? '')); ?>"
                                        data-nilai-kontrak="<?= esc((string) ($item['nilai_kontrak'] ?? 0)); ?>"
                                        data-tahapan-pekerjaan="<?= esc((string) ($item['tahapan_pekerjaan'] ?? '')); ?>"
                                        data-tanggal-pemeriksaan="<?= esc((string) ($item['tanggal_pemeriksaan'] ?? '')); ?>"
                                    >EDIT</button>
                                <?php endif; ?>
                                <a href="<?= site_url('admin/kontrak/simak/' . (int) ($item['id'] ?? 0)); ?>" class="btn btn-success btn-sm">DETAIL</a>
                            </td>
                            <td class="text-center">
                                <?php if (($can_share ?? false) === true): ?>
                                    <button
                                        type="button"
                                        class="btn btn-primary btn-sm js-open-share-modal"
                                        data-toggle="modal"
                                        data-target="#shareDurationModal"
                                        data-share-url="<?= site_url('admin/kontrak/simak/' . (int) ($item['id'] ?? 0) . '/share'); ?>"
                                        data-share-deactivate-url="<?= site_url('admin/kontrak/simak/' . (int) ($item['id'] ?? 0) . '/share/deactivate'); ?>"
                                        data-share-public-url="<?= esc(trim((string) ($item['share_public_url'] ?? ''))); ?>"
                                        data-nomor-kontrak="<?= esc((string) ($item['nomor_kontrak'] ?? '-')); ?>"
                                    >
                                        <i class="fas fa-share-alt mr-1"></i>
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (($can_share ?? false) === true): ?>
<div class="modal fade" id="shareDurationModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bagikan SIMAK</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Berapa lama durasi dokumen ini akan dibagikan?</p>
                <p class="small text-muted mb-3" id="shareSimakNomorKontrak">-</p>

                <div class="alert alert-info py-2 px-3 d-none" id="shareActiveInfo"></div>

                <div class="mb-3 d-none" id="shareCurrentLinkSection">
                    <label for="shareCurrentLinkInput" class="small text-muted mb-1">Link Share Aktif</label>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" id="shareCurrentLinkInput" readonly>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-primary" id="btnCopyCurrentShareLink">Salin</button>
                        </div>
                    </div>
                </div>

                <div id="shareDurationSection">
                    <div class="custom-control custom-radio mb-2">
                        <input type="radio" id="shareDuration1Week" name="shareDuration" class="custom-control-input" value="1week" checked>
                        <label class="custom-control-label" for="shareDuration1Week">1 minggu</label>
                    </div>
                    <div class="custom-control custom-radio mb-2">
                        <input type="radio" id="shareDuration30Days" name="shareDuration" class="custom-control-input" value="30days">
                        <label class="custom-control-label" for="shareDuration30Days">30 hari</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger mr-auto d-none" id="btnDeactivateShareLink">
                    Nonaktifkan Link
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnGenerateShareLink">
                    Buat Link Bagikan
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (($can_edit ?? false) === true): ?>
<?php
    $simakYearStart = 2020;
    $simakCurrentYear = (int) date('Y');
    $simakYearMax = $simakCurrentYear + 2;
    $simakDefaultAnggaran = $simakCurrentYear . ' - ' . ($simakCurrentYear + 1);
    $simakDefaultPpkNip = '199012212018021001';
?>
<div class="modal fade" id="modal-tambah-simak" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Input Data SIMAK</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-tambah-simak" action="<?= site_url('admin/kontrak/simak/tambah'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="alert alert-info py-2">
                        Add On tidak diinput pada form tambah. Setelah data tersimpan, gunakan tombol EDIT untuk mengelola Add On.
                    </div>

                    <div class="form-group">
                        <label for="satker">Satker</label>
                        <input type="text" class="form-control" id="satker" name="satker" value="Perencanaan Prasarana Strategis" readonly>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="pegawai_nip_selector">PPK (ambil dari master pegawai)</label>
                            <select class="form-control" id="pegawai_nip_selector" name="ppk_nip" required>
                                <option value="">-- Pilih PPK berdasarkan NIP --</option>
                                <?php foreach (($pegawaiOptions ?? []) as $pegawai): ?>
                                    <?php $pegawaiNip = (string) ($pegawai['nip'] ?? ''); ?>
                                    <option value="<?= esc($pegawaiNip); ?>" data-nama="<?= esc((string) ($pegawai['nama'] ?? '')); ?>" <?= $pegawaiNip === $simakDefaultPpkNip ? 'selected' : ''; ?>>
                                        <?= esc($pegawaiNip); ?> - <?= esc((string) ($pegawai['nama'] ?? '-')); ?><?= ! empty($pegawai['jabatan_label']) ? ' (' . esc((string) $pegawai['jabatan_label']) . ')' : ''; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="ppk_nama">PPK</label>
                            <input type="text" class="form-control" id="ppk_nama" name="ppk_nama" readonly required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="ppk_nip">NIP</label>
                        <input type="text" class="form-control" id="ppk_nip" readonly>
                    </div>

                    <h6 class="mb-3">Data Pekerjaan Konstruksi</h6>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="nama_paket">Nama Paket</label>
                            <input type="text" class="form-control" id="nama_paket" name="nama_paket" maxlength="255" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="tahun_anggaran">Tahun Anggaran</label>
                            <select class="form-control" id="tahun_anggaran" name="tahun_anggaran" required>
                                <?php for ($year = $simakYearMax; $year >= $simakYearStart; $year--): ?>
                                    <?php $anggaranLabel = $year . ' - ' . ($year + 1); ?>
                                    <option value="<?= esc($anggaranLabel); ?>" <?= $anggaranLabel === $simakDefaultAnggaran ? 'selected' : ''; ?>>
                                        <?= esc($anggaranLabel); ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="penyedia">Penyedia</label>
                            <input type="text" class="form-control" id="penyedia" name="penyedia" maxlength="255">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="nomor_kontrak">Nomor Kontrak</label>
                            <input type="text" class="form-control" id="nomor_kontrak" name="nomor_kontrak" maxlength="120" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="nilai_kontrak">Nilai Kontrak (Rp)</label>
                            <input type="text" class="form-control" id="nilai_kontrak" name="nilai_kontrak" inputmode="numeric" autocomplete="off" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="tahapan_pekerjaan">Tahapan Pekerjaan</label>
                            <input type="text" class="form-control" id="tahapan_pekerjaan" name="tahapan_pekerjaan" maxlength="255">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="tanggal_pemeriksaan">Tanggal Pemeriksaan</label>
                            <input type="date" class="form-control" id="tanggal_pemeriksaan" name="tanggal_pemeriksaan">
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

<div class="modal fade" id="modal-import-simak" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Excel SIMAK</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('admin/kontrak/simak/import'); ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="alert alert-info">
                        Gunakan file template yang sudah disediakan. Field minimum yang wajib ada: ppk_nip, nama_paket, tahun_anggaran, nomor_kontrak, nilai_kontrak. Nama PPK akan diambil otomatis dari master pegawai berdasarkan NIP.
                    </div>

                    <div class="mb-3">
                        <a href="<?= site_url('admin/kontrak/simak/template'); ?>" class="btn btn-success btn-sm" target="_blank">
                            <i class="fas fa-download mr-1"></i> Download Template (XLSX)
                        </a>
                    </div>

                    <div class="form-group">
                        <label for="file_excel_simak">File Excel</label>
                        <input type="file" class="form-control-file" id="file_excel_simak" name="file_excel" accept=".xls,.xlsx" required>
                        <small class="text-muted">Format file: .xls atau .xlsx</small>
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

<div class="modal fade" id="modal-edit-simak" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Data SIMAK</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-edit-simak" action="#" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="simakEditTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="tab-data-simak" data-toggle="tab" href="#panel-data-simak" role="tab" aria-controls="panel-data-simak" aria-selected="true">Data Simak</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="tab-add-on" data-toggle="tab" href="#panel-add-on" role="tab" aria-controls="panel-add-on" aria-selected="false">Add On</a>
                        </li>
                    </ul>

                    <div class="tab-content pt-3" id="simakEditTabContent">
                        <div class="tab-pane fade show active" id="panel-data-simak" role="tabpanel" aria-labelledby="tab-data-simak">
                            <div class="form-group">
                                <label for="satker_edit">Satker</label>
                                <input type="text" class="form-control" id="satker_edit" name="satker" value="Perencanaan Prasarana Strategis" readonly>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="pegawai_nip_selector_edit">PPK (ambil dari master pegawai)</label>
                                    <select class="form-control" id="pegawai_nip_selector_edit" name="ppk_nip" required>
                                        <option value="">-- Pilih PPK berdasarkan NIP --</option>
                                        <?php foreach (($pegawaiOptions ?? []) as $pegawai): ?>
                                            <option value="<?= esc((string) ($pegawai['nip'] ?? '')); ?>" data-nama="<?= esc((string) ($pegawai['nama'] ?? '')); ?>">
                                                <?= esc((string) ($pegawai['nip'] ?? '')); ?> - <?= esc((string) ($pegawai['nama'] ?? '-')); ?><?= ! empty($pegawai['jabatan_label']) ? ' (' . esc((string) $pegawai['jabatan_label']) . ')' : ''; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="ppk_nama_edit">PPK</label>
                                    <input type="text" class="form-control" id="ppk_nama_edit" name="ppk_nama" readonly required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="ppk_nip_edit">NIP</label>
                                <input type="text" class="form-control" id="ppk_nip_edit" readonly>
                            </div>

                            <h6 class="mb-3">Data Pekerjaan Konstruksi</h6>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="nama_paket_edit">Nama Paket</label>
                                    <input type="text" class="form-control" id="nama_paket_edit" name="nama_paket" maxlength="255" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="tahun_anggaran_edit">Tahun Anggaran</label>
                                    <select class="form-control" id="tahun_anggaran_edit" name="tahun_anggaran" required>
                                        <?php for ($year = $simakYearMax; $year >= $simakYearStart; $year--): ?>
                                            <?php $anggaranLabel = $year . ' - ' . ($year + 1); ?>
                                            <option value="<?= esc($anggaranLabel); ?>" <?= $anggaranLabel === $simakDefaultAnggaran ? 'selected' : ''; ?>>
                                                <?= esc($anggaranLabel); ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="penyedia_edit">Penyedia</label>
                                    <input type="text" class="form-control" id="penyedia_edit" name="penyedia" maxlength="255">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="nomor_kontrak_edit">Nomor Kontrak</label>
                                    <input type="text" class="form-control" id="nomor_kontrak_edit" name="nomor_kontrak" maxlength="120" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="nilai_kontrak_edit">Nilai Kontrak (Rp)</label>
                                    <input type="text" class="form-control" id="nilai_kontrak_edit" name="nilai_kontrak" inputmode="numeric" autocomplete="off" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="tahapan_pekerjaan_edit">Tahapan Pekerjaan</label>
                                    <input type="text" class="form-control" id="tahapan_pekerjaan_edit" name="tahapan_pekerjaan" maxlength="255">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="tanggal_pemeriksaan_edit">Tanggal Pemeriksaan</label>
                                    <input type="date" class="form-control" id="tanggal_pemeriksaan_edit" name="tanggal_pemeriksaan">
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="panel-add-on" role="tabpanel" aria-labelledby="tab-add-on">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Daftar Add On (Mode Edit)</h6>
                                <button type="button" class="btn btn-primary btn-sm" id="btn-tambah-add-on">Tambah Add On</button>
                            </div>
                            <div id="add-on-container"></div>
                            <small class="text-muted d-block">Setiap Add On akan dinamai otomatis: Add On 1, Add On 2, dan seterusnya.</small>
                            <small class="text-muted d-block">Add On hanya tersedia pada mode edit.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
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
    'use strict';

    var addOnsBySimakId = <?= json_encode($addOnsBySimakId ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;

    var formatCurrency = function (value) {
        var digits = String(value || '').replace(/\D+/g, '');
        if (digits === '') {
            return '';
        }

        return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    };

    var parseCurrency = function (value) {
        var digits = String(value || '').replace(/\D+/g, '');
        return digits === '' ? '' : digits;
    };

    var bindCurrencyInput = function (inputId) {
        var input = document.getElementById(inputId);
        if (!input) {
            return;
        }

        input.addEventListener('input', function () {
            var cursorAtEnd = input.selectionStart === input.value.length;
            input.value = formatCurrency(input.value);
            if (cursorAtEnd) {
                input.setSelectionRange(input.value.length, input.value.length);
            }
        });

        input.addEventListener('focus', function () {
            input.value = parseCurrency(input.value);
        });

        input.addEventListener('blur', function () {
            input.value = formatCurrency(input.value);
        });
    };

    var bindCurrencyInputElement = function (input) {
        if (!input || input.dataset.currencyBinded === '1') {
            return;
        }

        input.dataset.currencyBinded = '1';

        input.addEventListener('input', function () {
            var cursorAtEnd = input.selectionStart === input.value.length;
            input.value = formatCurrency(input.value);
            if (cursorAtEnd) {
                input.setSelectionRange(input.value.length, input.value.length);
            }
        });

        input.addEventListener('focus', function () {
            input.value = parseCurrency(input.value);
        });

        input.addEventListener('blur', function () {
            input.value = formatCurrency(input.value);
        });
    };

    var simakTableInstance = null;
    var simakFilterState = {
        nomorKontrak: '',
        namaPaket: '',
        tahunAnggaran: '',
        ppk: '',
        status: ''
    };

    var normalizeText = function (value) {
        return String(value || '').toLowerCase().replace(/\s+/g, ' ').trim();
    };

    var applySimakFilter = function () {
        if (simakTableInstance && typeof simakTableInstance.draw === 'function') {
            simakTableInstance.draw();
        }
    };

    var renderAddOnRows = function (items) {
        var container = document.getElementById('add-on-container');
        if (!container) {
            return;
        }

        container.innerHTML = '';
        var list = Array.isArray(items) && items.length > 0 ? items : [{ value: '', date: '' }];

        list.forEach(function (item, idx) {
            var row = document.createElement('div');
            row.className = 'form-row align-items-end js-add-on-row';

            var colLabel = document.createElement('div');
            colLabel.className = 'form-group col-md-3';
            var label = document.createElement('label');
            label.textContent = 'Add On ' + (idx + 1);
            colLabel.appendChild(label);

            var colDate = document.createElement('div');
            colDate.className = 'form-group col-md-3';
            var dateLabel = document.createElement('label');
            dateLabel.textContent = 'Tanggal Add On';
            var dateInput = document.createElement('input');
            dateInput.type = 'date';
            dateInput.className = 'form-control js-add-on-date';
            dateInput.name = 'add_on_dates[]';
            dateInput.value = (item && item.date) ? String(item.date) : '';
            colDate.appendChild(dateLabel);
            colDate.appendChild(dateInput);

            var colValue = document.createElement('div');
            colValue.className = 'form-group col-md-4';
            var input = document.createElement('input');
            input.type = 'text';
            input.className = 'form-control js-add-on-value';
            input.name = 'add_on_values[]';
            input.setAttribute('inputmode', 'numeric');
            input.setAttribute('autocomplete', 'off');
            input.value = formatCurrency(item && item.value ? item.value : '');
            colValue.appendChild(input);

            var colAction = document.createElement('div');
            colAction.className = 'form-group col-md-2';
            var btnDelete = document.createElement('button');
            btnDelete.type = 'button';
            btnDelete.className = 'btn btn-danger btn-block js-remove-add-on';
            btnDelete.textContent = 'Hapus';
            colAction.appendChild(btnDelete);

            row.appendChild(colLabel);
            row.appendChild(colDate);
            row.appendChild(colValue);
            row.appendChild(colAction);
            container.appendChild(row);

            bindCurrencyInputElement(input);
        });

        refreshAddOnLabels();
    };

    var refreshAddOnLabels = function () {
        var rows = document.querySelectorAll('#add-on-container .js-add-on-row');
        rows.forEach(function (row, index) {
            var label = row.querySelector('label');
            if (label) {
                label.textContent = 'Add On ' + (index + 1);
            }
        });
    };

    var appendAddOnRow = function () {
        var currentItems = [];
        var rows = document.querySelectorAll('#add-on-container .js-add-on-row');
        rows.forEach(function (row) {
            var valueInput = row.querySelector('.js-add-on-value');
            var dateInput = row.querySelector('.js-add-on-date');
            currentItems.push({
                value: valueInput ? parseCurrency(valueInput.value) : '',
                date: dateInput ? dateInput.value : '',
            });
        });
        currentItems.push({ value: '', date: '' });
        renderAddOnRows(currentItems);
    };

    var setupSimakForm = function (config) {
        var pegawaiSelector = document.getElementById(config.pegawaiSelectorId);
        var ppkNamaInput = document.getElementById(config.ppkNamaId);
        var ppkNipInput = document.getElementById(config.ppkNipId);

        var syncPpk = function () {
            if (!pegawaiSelector || !ppkNamaInput || !ppkNipInput) {
                return;
            }

            var selectedOption = pegawaiSelector.options[pegawaiSelector.selectedIndex];
            if (!selectedOption && pegawaiSelector.value !== '') {
                selectedOption = pegawaiSelector.querySelector('option[value="' + pegawaiSelector.value + '"]');
            }

            var nip = selectedOption ? selectedOption.value : '';
            var nama = selectedOption ? (selectedOption.getAttribute('data-nama') || '') : '';

            if (nama === '' && selectedOption) {
                var optionText = (selectedOption.textContent || '').trim();
                var splitText = optionText.split(' - ');
                if (splitText.length > 1) {
                    nama = splitText.slice(1).join(' - ').replace(/\s*\(.+\)\s*$/, '').trim();
                }
            }

            ppkNipInput.value = nip;
            ppkNamaInput.value = nama;
        };

        if (pegawaiSelector) {
            pegawaiSelector.addEventListener('change', syncPpk);

            if (window.jQuery && window.jQuery.fn) {
                window.jQuery(pegawaiSelector)
                    .on('change.select2-simak', syncPpk)
                    .on('select2:select.select2-simak', syncPpk)
                    .on('select2:clear.select2-simak', syncPpk);
            }
        }

        syncPpk();

        return {
            syncPpk: syncPpk,
        };
    };

    bindCurrencyInput('nilai_kontrak');
    bindCurrencyInput('nilai_kontrak_edit');

    var addForm = setupSimakForm({
        pegawaiSelectorId: 'pegawai_nip_selector',
        ppkNamaId: 'ppk_nama',
        ppkNipId: 'ppk_nip'
    });

    var editForm = setupSimakForm({
        pegawaiSelectorId: 'pegawai_nip_selector_edit',
        ppkNamaId: 'ppk_nama_edit',
        ppkNipId: 'ppk_nip_edit'
    });

    var formEdit = document.getElementById('form-edit-simak');
    var editButtons = document.querySelectorAll('.js-open-edit-simak');
    var shareModalButtons = document.querySelectorAll('.js-open-share-modal');
    var shareModalEl = document.getElementById('shareDurationModal');
    var shareNomorKontrakEl = document.getElementById('shareSimakNomorKontrak');
    var shareActiveInfo = document.getElementById('shareActiveInfo');
    var shareDurationSection = document.getElementById('shareDurationSection');
    var shareCurrentLinkSection = document.getElementById('shareCurrentLinkSection');
    var shareCurrentLinkInput = document.getElementById('shareCurrentLinkInput');
    var btnCopyCurrentShareLink = document.getElementById('btnCopyCurrentShareLink');
    var btnGenerateShareLink = document.getElementById('btnGenerateShareLink');
    var btnDeactivateShareLink = document.getElementById('btnDeactivateShareLink');
    var btnTambahAddOn = document.getElementById('btn-tambah-add-on');
    var filterNomorKontrak = document.getElementById('filter_simak_nomor_kontrak');
    var filterNamaPaket = document.getElementById('filter_simak_nama_paket');
    var filterTahunAnggaran = document.getElementById('filter_simak_tahun_anggaran');
    var filterPpk = document.getElementById('filter_simak_ppk');
    var filterStatus = document.getElementById('filter_simak_status');
    var btnResetFilter = document.getElementById('btn-reset-filter-simak');

    var csrfTokenName = <?= json_encode(csrf_token(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
    var csrfTokenValue = <?= json_encode(csrf_hash(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;

    var selectedShareConfig = {
        shareUrl: '',
        deactivateUrl: '',
        currentShareUrl: '',
        isActive: false,
        nomorKontrak: '',
    };

    var escapeHtml = function (value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    };

    if (window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable) {
        var tableNode = document.querySelector('.simak-list-table');
        if (tableNode && window.jQuery.fn.dataTable && window.jQuery.fn.dataTable.isDataTable(tableNode)) {
            simakTableInstance = window.jQuery(tableNode).DataTable();
        }

        if (simakTableInstance && window.jQuery.fn.dataTable && !window.__simakFilterRegistered) {
            window.__simakFilterRegistered = true;
            window.jQuery.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
                if (!settings || !settings.nTable || !settings.nTable.classList.contains('simak-list-table')) {
                    return true;
                }

                var rowNode = settings.aoData && settings.aoData[dataIndex] ? settings.aoData[dataIndex].nTr : null;
                var row = rowNode ? rowNode : null;

                var nomorKontrak = normalizeText(data[1] || '');
                var namaPaket = normalizeText(data[2] || '');
                var tahunAnggaran = normalizeText(data[3] || '');
                var ppk = normalizeText(data[4] || '');
                var status = normalizeText(row && row.getAttribute ? row.getAttribute('data-kelengkapan-status') : '');

                if (simakFilterState.nomorKontrak && nomorKontrak.indexOf(simakFilterState.nomorKontrak) === -1) {
                    return false;
                }

                if (simakFilterState.namaPaket && namaPaket.indexOf(simakFilterState.namaPaket) === -1) {
                    return false;
                }

                if (simakFilterState.tahunAnggaran && tahunAnggaran !== simakFilterState.tahunAnggaran) {
                    return false;
                }

                if (simakFilterState.ppk && ppk.indexOf(simakFilterState.ppk) === -1) {
                    return false;
                }

                if (simakFilterState.status && status !== simakFilterState.status) {
                    return false;
                }

                return true;
            });
        }
    }

    if (filterNomorKontrak) {
        filterNomorKontrak.addEventListener('input', function () {
            simakFilterState.nomorKontrak = normalizeText(this.value);
            applySimakFilter();
        });
    }

    if (filterNamaPaket) {
        filterNamaPaket.addEventListener('input', function () {
            simakFilterState.namaPaket = normalizeText(this.value);
            applySimakFilter();
        });
    }

    if (filterTahunAnggaran) {
        filterTahunAnggaran.addEventListener('change', function () {
            simakFilterState.tahunAnggaran = normalizeText(this.value);
            applySimakFilter();
        });
    }

    if (filterPpk) {
        filterPpk.addEventListener('input', function () {
            simakFilterState.ppk = normalizeText(this.value);
            applySimakFilter();
        });
    }

    if (filterStatus) {
        filterStatus.addEventListener('change', function () {
            simakFilterState.status = normalizeText(this.value);
            applySimakFilter();
        });
    }

    if (btnResetFilter) {
        btnResetFilter.addEventListener('click', function () {
            simakFilterState.nomorKontrak = '';
            simakFilterState.namaPaket = '';
            simakFilterState.tahunAnggaran = '';
            simakFilterState.ppk = '';
            simakFilterState.status = '';

            if (filterNomorKontrak) filterNomorKontrak.value = '';
            if (filterNamaPaket) filterNamaPaket.value = '';
            if (filterTahunAnggaran) filterTahunAnggaran.value = '';
            if (filterPpk) filterPpk.value = '';
            if (filterStatus) filterStatus.value = '';

            applySimakFilter();
        });
    }

    if (btnTambahAddOn) {
        btnTambahAddOn.addEventListener('click', appendAddOnRow);
    }

    shareModalButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            var shareUrl = String(this.getAttribute('data-share-url') || '').trim();
            var deactivateUrl = String(this.getAttribute('data-share-deactivate-url') || '').trim();
            var currentShareUrl = String(this.getAttribute('data-share-public-url') || '').trim();
            var nomorKontrak = String(this.getAttribute('data-nomor-kontrak') || '-').trim();

            selectedShareConfig.shareUrl = shareUrl;
            selectedShareConfig.deactivateUrl = deactivateUrl;
            selectedShareConfig.currentShareUrl = currentShareUrl;
            selectedShareConfig.isActive = currentShareUrl !== '';
            selectedShareConfig.nomorKontrak = nomorKontrak;

            if (shareNomorKontrakEl) {
                shareNomorKontrakEl.textContent = nomorKontrak !== '' ? nomorKontrak : '-';
            }

            if (shareCurrentLinkSection && shareCurrentLinkInput) {
                if (currentShareUrl !== '') {
                    shareCurrentLinkSection.classList.remove('d-none');
                    shareCurrentLinkInput.value = currentShareUrl;
                } else {
                    shareCurrentLinkSection.classList.add('d-none');
                    shareCurrentLinkInput.value = '';
                }
            }

            if (btnDeactivateShareLink) {
                if (currentShareUrl !== '') {
                    btnDeactivateShareLink.classList.remove('d-none');
                } else {
                    btnDeactivateShareLink.classList.add('d-none');
                }
            }

            if (shareActiveInfo) {
                if (currentShareUrl !== '') {
                    shareActiveInfo.classList.remove('d-none');
                    shareActiveInfo.innerHTML = '<strong>Link aktif saat ini:</strong> Sebaiknya bagikan link yang sudah ada agar kontraktor tidak bingung.';
                } else {
                    shareActiveInfo.classList.add('d-none');
                }
            }
        });
    });

    if (btnGenerateShareLink) {
        btnGenerateShareLink.addEventListener('click', function () {
            if (!selectedShareConfig.shareUrl) {
                return;
            }

            var selectedDurationInput = document.querySelector('input[name="shareDuration"]:checked');
            var duration = selectedDurationInput ? selectedDurationInput.value : '1week';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Membuat link...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: function () {
                        Swal.showLoading();
                    }
                });
            }

            $.ajax({
                url: selectedShareConfig.shareUrl,
                method: 'POST',
                dataType: 'json',
                data: {
                    duration: duration,
                    [csrfTokenName]: csrfTokenValue,
                },
            }).done(function (response) {
                if (response && response.csrf_hash) {
                    csrfTokenValue = response.csrf_hash;
                }

                if (typeof $.fn.modal === 'function') {
                    $(shareModalEl).modal('hide');
                }

                var shareUrl = response && response.share_url ? response.share_url : '';
                
                if (typeof Swal === 'undefined') {
                    window.alert('Link share berhasil dibuat:\n\n' + shareUrl);
                    return;
                }

                Swal.fire({
                    icon: 'success',
                    title: response && response.is_update ? 'Durasi Link Diperbarui' : 'Link Berhasil Dibuat',
                    html: '<div class="text-left"><small class="text-muted">Bagikan tautan berikut:</small><input id="shareLinkInput" class="form-control mt-2" value="' + escapeHtml(shareUrl) + '" readonly></div>',
                    showCancelButton: true,
                    confirmButtonText: 'Salin Link',
                    cancelButtonText: 'Tutup',
                    preConfirm: function () {
                        var input = document.getElementById('shareLinkInput');
                        if (!input) {
                            return;
                        }

                        input.select();
                        input.setSelectionRange(0, 99999);

                        if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
                            return navigator.clipboard.writeText(input.value);
                        }

                        document.execCommand('copy');
                    }
                });

                simakTableInstance.ajax.reload(null, false);
            }).fail(function (xhr) {
                var message = xhr && xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Gagal membuat link berbagi.';

                if (typeof Swal === 'undefined') {
                    window.alert(message);
                    return;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: message,
                });
            });
        });
    }

    if (btnCopyCurrentShareLink) {
        btnCopyCurrentShareLink.addEventListener('click', function () {
            var url = shareCurrentLinkInput ? shareCurrentLinkInput.value : '';
            if (!url) {
                return;
            }

            var onSuccess = function () {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Tersalin',
                        text: 'Link share berhasil disalin.',
                        timer: 1200,
                        showConfirmButton: false,
                    });
                }
            };

            if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
                navigator.clipboard.writeText(url).then(onSuccess);
                return;
            }

            if (shareCurrentLinkInput) {
                shareCurrentLinkInput.value = url;
                shareCurrentLinkInput.select();
                shareCurrentLinkInput.setSelectionRange(0, 99999);
                document.execCommand('copy');
                shareCurrentLinkInput.value = url;
                onSuccess();
            }
        });
    }

    if (btnDeactivateShareLink) {
        btnDeactivateShareLink.addEventListener('click', function () {
            if (!selectedShareConfig.deactivateUrl || !selectedShareConfig.isActive) {
                return;
            }

            var proceed = function () {
                $.ajax({
                    url: selectedShareConfig.deactivateUrl,
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        [csrfTokenName]: csrfTokenValue,
                    },
                }).done(function (response) {
                    if (response && response.csrf_hash) {
                        csrfTokenValue = response.csrf_hash;
                    }

                    if (typeof $.fn.modal === 'function') {
                        $(shareModalEl).modal('hide');
                    }

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response && response.message ? response.message : 'Link berbagi berhasil dinonaktifkan.',
                        });
                    }

                    simakTableInstance.ajax.reload(null, false);
                }).fail(function (xhr) {
                    var message = xhr && xhr.responseJSON && xhr.responseJSON.message
                        ? xhr.responseJSON.message
                        : 'Gagal menonaktifkan link.';

                    if (typeof Swal === 'undefined') {
                        window.alert(message);
                        return;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: message,
                    });
                });
            };

            if (typeof Swal === 'undefined') {
                if (window.confirm('Yakin ingin menonaktifkan link berbagi ini?')) {
                    proceed();
                }
                return;
            }

            Swal.fire({
                icon: 'warning',
                title: 'Nonaktifkan Link',
                text: 'Yakin ingin menonaktifkan link berbagi ini?',
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal',
            }).then(function (result) {
                if (result.isConfirmed) {
                    proceed();
                }
            });
        });
    }

    document.addEventListener('click', function (event) {
        if (!event.target.classList.contains('js-remove-add-on')) {
            return;
        }

        var row = event.target.closest('.js-add-on-row');
        if (!row) {
            return;
        }

        row.remove();
        var remainRows = document.querySelectorAll('#add-on-container .js-add-on-row');
        if (remainRows.length === 0) {
            renderAddOnRows([{ value: '', date: '' }]);
            return;
        }

        refreshAddOnLabels();
    });

    editButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            var id = this.getAttribute('data-id') || '0';
            var satkerEdit = document.getElementById('satker_edit');
            var ppkSelectorEdit = document.getElementById('pegawai_nip_selector_edit');
            var namaPaketEdit = document.getElementById('nama_paket_edit');
            var tahunAnggaranEdit = document.getElementById('tahun_anggaran_edit');
            var penyediaEdit = document.getElementById('penyedia_edit');
            var nomorKontrakEdit = document.getElementById('nomor_kontrak_edit');
            var nilaiKontrakEdit = document.getElementById('nilai_kontrak_edit');
            var tahapanPekerjaanEdit = document.getElementById('tahapan_pekerjaan_edit');
            var tanggalPemeriksaanEdit = document.getElementById('tanggal_pemeriksaan_edit');

            if (formEdit) {
                formEdit.action = '<?= site_url('admin/kontrak/simak'); ?>/' + id + '/ubah';
            }

            var addOnRows = addOnsBySimakId[id] || [];
            var addOnItems = addOnRows.map(function (row) {
                return {
                    value: row && row.nilai_add_on ? String(row.nilai_add_on) : '',
                    date: row && row.tanggal_add_on ? String(row.tanggal_add_on) : '',
                };
            });
            renderAddOnRows(addOnItems);

            if (satkerEdit) {
                satkerEdit.value = this.getAttribute('data-satker') || 'Perencanaan Prasarana Strategis';
            }
            if (ppkSelectorEdit) {
                ppkSelectorEdit.value = this.getAttribute('data-ppk-nip') || '';
            }
            if (namaPaketEdit) {
                namaPaketEdit.value = this.getAttribute('data-nama-paket') || '';
            }
            if (tahunAnggaranEdit) {
                var existingTahunAnggaran = this.getAttribute('data-tahun-anggaran') || '';
                var hasMatchingOption = Array.prototype.some.call(tahunAnggaranEdit.options, function (opt) {
                    return opt.value === existingTahunAnggaran;
                });

                if (!hasMatchingOption && existingTahunAnggaran !== '') {
                    var fallbackOption = document.createElement('option');
                    fallbackOption.value = existingTahunAnggaran;
                    fallbackOption.textContent = existingTahunAnggaran;
                    tahunAnggaranEdit.appendChild(fallbackOption);
                }

                tahunAnggaranEdit.value = existingTahunAnggaran;

                if (window.jQuery && window.jQuery.fn) {
                    window.jQuery(tahunAnggaranEdit).trigger('change.select2');
                }
            }
            if (penyediaEdit) {
                penyediaEdit.value = this.getAttribute('data-penyedia') || '';
            }
            if (nomorKontrakEdit) {
                nomorKontrakEdit.value = this.getAttribute('data-nomor-kontrak') || '';
            }
            if (nilaiKontrakEdit) {
                nilaiKontrakEdit.value = formatCurrency(this.getAttribute('data-nilai-kontrak') || '0');
            }
            if (tahapanPekerjaanEdit) {
                tahapanPekerjaanEdit.value = this.getAttribute('data-tahapan-pekerjaan') || '';
            }
            if (tanggalPemeriksaanEdit) {
                tanggalPemeriksaanEdit.value = this.getAttribute('data-tanggal-pemeriksaan') || '';
            }

            editForm.syncPpk();

            if (window.jQuery && window.jQuery.fn) {
                window.jQuery('#tab-data-simak').tab('show');
            }

            if (window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.modal === 'function') {
                window.jQuery('#modal-edit-simak').modal('show');
            }
        });
    });

    if (addForm && typeof addForm.syncPpk === 'function') {
        addForm.syncPpk();
    }

    if (window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.modal === 'function') {
        window.jQuery('#modal-tambah-simak').on('shown.bs.modal', function () {
            if (addForm && typeof addForm.syncPpk === 'function') {
                addForm.syncPpk();
            }
        });

        window.jQuery('#modal-edit-simak').on('shown.bs.modal', function () {
            if (editForm && typeof editForm.syncPpk === 'function') {
                editForm.syncPpk();
            }
        });
    }

    var formTambah = document.getElementById('form-tambah-simak');
    if (formTambah) {
        formTambah.addEventListener('submit', function () {
            var nilaiKontrakInput = document.getElementById('nilai_kontrak');
            if (nilaiKontrakInput) {
                nilaiKontrakInput.value = parseCurrency(nilaiKontrakInput.value);
            }
        });
    }

    if (formEdit) {
        formEdit.addEventListener('submit', function () {
            var nilaiKontrakEdit = document.getElementById('nilai_kontrak_edit');
            if (nilaiKontrakEdit) {
                nilaiKontrakEdit.value = parseCurrency(nilaiKontrakEdit.value);
            }

            var addOnInputs = document.querySelectorAll('#add-on-container .js-add-on-value');
            addOnInputs.forEach(function (input) {
                input.value = parseCurrency(input.value);
            });
        });
    }
})();
</script>
<?= $this->endSection(); ?>
