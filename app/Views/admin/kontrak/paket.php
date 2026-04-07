<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Daftar Paket</h2>
        <?php if (($can_edit ?? false) === true): ?>
            <div class="float-right">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-tambah-paket">Tambah Paket</button>
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

        <?php if (! empty($error ?? '')): ?>
            <div class="alert alert-danger"><?= esc((string) $error); ?></div>
        <?php endif; ?>

        <table class="table table-bordered table-striped w-100 nowrap js-datatable">
            <thead>
                <tr style="white-space: nowrap;">
                    <th class="text-center">#</th>
                    <th class="text-center">PAKET</th>
                    <th class="text-center">SYARAT UMUM (SPK)</th>
                    <th class="text-center">KOP SURAT</th>
                    <th class="text-center">ACTION</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach (($data ?? []) as $d): ?>
                    <tr>
                        <td><?= esc((string) $i++); ?></td>
                        <td><?= esc((string) ($d['nama_paket'] ?? '-')); ?></td>
                        <td>
                            <button
                                type="button"
                                class="btn btn-warning btn-sm btn-block js-open-syarat-umum"
                                data-id="<?= esc((string) ($d['id'] ?? '0')); ?>"
                                data-nama-paket="<?= esc((string) ($d['nama_paket'] ?? '-')); ?>"
                                data-laporan="<?= esc((string) ($d['laporan'] ?? '')); ?>"
                                data-hasil="<?= esc((string) ($d['hasil'] ?? '')); ?>"
                                data-tugas="<?= esc((string) ($d['tugas_tanggung_jawab'] ?? '')); ?>"
                            >UPDATE</button>
                        </td>
                        <td>
                            <?php
                                $kopSuratLabel = trim((string) ($d['kop_surat_title'] ?? ''));
                                if ($kopSuratLabel === '') {
                                    $kopSuratLabel = 'Default';
                                }
                                if ((int) ($d['kop_surat_is_active'] ?? 0) !== 1 && ! empty($d['kop_surat_id'])) {
                                    $kopSuratLabel;
                                }
                            ?>
                            <div class="d-flex flex-column align-items-start" style="gap: 6px;">
                                <button
                                    type="button"
                                    class="btn btn-info btn-block btn-sm js-open-kop-surat-paket"
                                    data-id="<?= esc((string) ($d['id'] ?? '0')); ?>"
                                    data-nama-paket="<?= esc((string) ($d['nama_paket'] ?? '-')); ?>"
                                    data-kop-surat-id="<?= esc((string) ($d['kop_surat_id'] ?? '')); ?>"
                                ><?= esc($kopSuratLabel); ?></button>
                            </div>
                        </td>
                        <td>
                            <a href="<?= site_url('admin/kontrak/ki/' . (int) ($d['id'] ?? 0)); ?>" class="btn btn-success btn-sm">DETAIL</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (($can_edit ?? false) === true): ?>
<div class="modal fade" id="modal-tambah-paket" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Paket</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-tambah-paket" action="<?= site_url('admin/kontrak/paket/tambah'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-group mb-0">
                        <label for="nama_paket">Nama Paket</label>
                        <input type="text" class="form-control" id="nama_paket" name="nama_paket" maxlength="255" required>
                        <small class="form-text text-muted">Masukkan nama paket baru.</small>
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

<?php if (($can_edit ?? false) === true): ?>
<div class="modal fade" id="modal-kop-surat-paket" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Kop Surat Paket - <span id="modal-kop-surat-nama-paket">-</span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-kop-surat-paket" method="post" action="">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-group mb-0">
                        <label for="kop_surat_id_paket">Kop Surat</label>
                        <select class="form-control" id="kop_surat_id_paket" name="kop_surat_id">
                            <option value="">-- Gunakan Kop Surat Aktif Default --</option>
                            <?php foreach (($kopSuratList ?? []) as $ks): ?>
                                <option value="<?= esc((string) ($ks['id'] ?? '')); ?>">
                                    <?= esc((string) ($ks['title'] ?? '-')); ?>
                                    <?= ((int) ($ks['is_active'] ?? 0) === 1) ? ' (Aktif)' : ''; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">Setting ini jadi default kop surat untuk kontrak KI di paket ini (jika kop surat KI tidak dipilih manual).</small>
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

<div class="modal fade" id="modal-syarat-umum" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 95vw;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Syarat Umum - <span id="modal-syarat-nama-paket">-</span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-syarat-umum">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <input type="hidden" id="paket_id" name="paket_id">
                    <div class="form-group">
                        <label for="jabatan_filter">Jabatan</label>
                        <select class="form-control" id="jabatan_filter" name="jabatan_filter" required>
                            <option value="">-- Pilih Jabatan --</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-lg-7">
                            <ul class="nav nav-tabs" id="syaratUmumTab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="laporan-tab" data-toggle="tab" href="#laporan" role="tab" aria-controls="laporan" aria-selected="true">Laporan Hasil Pekerjaan</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="hasil-tab" data-toggle="tab" href="#hasil" role="tab" aria-controls="hasil" aria-selected="false">Produk Hasil Pekerjaan</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="tugas-tab" data-toggle="tab" href="#tugas" role="tab" aria-controls="tugas" aria-selected="false">Tugas dan Tanggung Jawab</a>
                                </li>
                            </ul>

                            <div class="d-flex justify-content-end mt-2">
                                <div class="btn-group btn-group-sm mr-2" role="group" aria-label="Format teks">
                                    <button type="button" class="btn btn-outline-dark js-inline-format" data-format="bold" title="Bold"><strong>B</strong></button>
                                    <button type="button" class="btn btn-outline-dark js-inline-format" data-format="italic" title="Italic"><em>I</em></button>
                                    <button type="button" class="btn btn-outline-dark js-inline-format" data-format="underline" title="Underline"><u>U</u></button>
                                </div>
                                <button type="button" class="btn btn-outline-secondary btn-sm js-fill-template">Isi Template</button>
                            </div>

                            <div class="tab-content" id="syaratUmumTabContent">
                                <div class="tab-pane fade show active" id="laporan" role="tabpanel" aria-labelledby="laporan-tab">
                                    <div class="form-group mb-0 mt-3">
                                        <textarea id="laporan-editor" class="form-control form-syarat-umum-editor" name="laporan" rows="13" placeholder="Contoh:&#10;LAPORAN HASIL PEKERJAAN&#10;Sub Judul&#10;1. Uraian pertama&#10;2. Uraian kedua&#10;a. Sub uraian"></textarea>
                                        <small class="form-text text-muted">Gunakan pola: Judul, Sub Judul, lalu daftar bernomor. Sistem akan format otomatis saat simpan.</small>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="hasil" role="tabpanel" aria-labelledby="hasil-tab">
                                    <div class="form-group mb-0 mt-3">
                                        <textarea id="hasil-editor" class="form-control form-syarat-umum-editor" name="hasil" rows="13" placeholder="Contoh:&#10;PRODUK HASIL PEKERJAAN&#10;Sub Judul&#10;1. Output pertama&#10;2. Output kedua&#10;a. Detail output"></textarea>
                                        <small class="form-text text-muted">Pisahkan tiap poin dengan baris baru agar hasil format lebih rapi.</small>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="tugas" role="tabpanel" aria-labelledby="tugas-tab">
                                    <div class="form-group mb-0 mt-3">
                                        <textarea id="tugas-editor" class="form-control form-syarat-umum-editor" name="tugas_tanggung_jawab" rows="13" placeholder="Contoh:&#10;TUGAS DAN TANGGUNG JAWAB&#10;Sub Judul&#10;1. Tugas pertama&#10;2. Tugas kedua&#10;a. Rincian tugas"></textarea>
                                        <small class="form-text text-muted">Jika ada sub poin, gunakan format huruf seperti: a. b. c.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-5 mt-3 mt-lg-0">
                            <label class="mb-2">Preview Format Otomatis</label>
                            <div id="preview-syarat-umum" class="border rounded p-3 bg-light" style="min-height: 100%; max-height: 650px; overflow-y: auto;"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                    <?php if (($can_edit ?? false) === true): ?>
                        <button type="submit" class="btn btn-primary js-submit-syarat-umum">Simpan</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script>
(function ($) {
    'use strict';

    var csrfTokenName = '<?= csrf_token(); ?>';

    var SyaratUmumModal = {
        paketId: null,
        isSaving: false,

        init: function () {
            this.bindEvents();
        },

        bindEvents: function () {
            var self = this;

            $(document).on('click', '.js-open-syarat-umum', function () {
                self.openModal($(this));
            });

            $(document).on('change', '#jabatan_filter', function () {
                self.loadSyaratUmum();
            });

            $(document).on('input', '.form-syarat-umum-editor', function () {
                self.updatePreviewForActiveTab();
            });

            $('#syaratUmumTab a[data-toggle="tab"]').on('shown.bs.tab', function () {
                self.updatePreviewForActiveTab();
            });

            $(document).on('click', '.js-fill-template', function () {
                self.fillTemplateForActiveTab();
            });

            $(document).on('click', '.js-inline-format', function () {
                self.applyInlineFormat($(this).data('format'));
            });

            $('#form-syarat-umum').on('submit', function (e) {
                e.preventDefault();
                self.saveSyaratUmum();
            });

            $('#modal-syarat-umum').on('shown.bs.modal', function () {
                self.autoSelectSingleJabatan();
                self.updatePreviewForActiveTab();
            });

            $('#modal-syarat-umum').on('hidden.bs.modal', function () {
                self.resetFormState();
            });

            $(document).on('keydown', function (e) {
                if (!$('#modal-syarat-umum').hasClass('show')) {
                    return;
                }

                var isModKey = e.ctrlKey || e.metaKey;
                if (!isModKey) {
                    return;
                }

                var key = String(e.key || '').toLowerCase();

                if (key === 's') {
                    e.preventDefault();
                    self.saveSyaratUmum();
                    return;
                }

                if (key === 'b') {
                    e.preventDefault();
                    self.applyInlineFormat('bold');
                    return;
                }

                if (key === 'i') {
                    e.preventDefault();
                    self.applyInlineFormat('italic');
                    return;
                }

                if (key === 'u') {
                    e.preventDefault();
                    self.applyInlineFormat('underline');
                }
            });

            $(document).on('click', '.js-open-kop-surat-paket', function () {
                var $btn = $(this);
                var paketId = $btn.data('id') || '';
                var namaPaket = $btn.data('nama-paket') || '-';
                var kopSuratId = String($btn.data('kop-surat-id') || '');

                $('#modal-kop-surat-nama-paket').text(namaPaket);
                $('#kop_surat_id_paket').val(kopSuratId);
                $('#form-kop-surat-paket').attr('action', '<?= site_url('admin/kontrak/paket'); ?>/' + paketId + '/kop-surat');
                $('#modal-kop-surat-paket').modal('show');
            });
        },

        openModal: function ($btn) {
            var self = this;
            this.paketId = $btn.data('id') || '';
            $('#paket_id').val(this.paketId);
            $('#modal-syarat-nama-paket').text($btn.data('nama-paket') || '-');

            if (this.paketId) {
                $.ajax({
                    url: '<?= site_url("admin/kontrak/syarat-umum/jabatan"); ?>',
                    type: 'GET',
                    data: { paket_id: this.paketId },
                    dataType: 'JSON',
                    beforeSend: function () {
                        Swal.fire({
                            title: 'Memuat Data',
                            text: 'Mengambil data jabatan...',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: function () {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function (data) {
                        Swal.close();
                        self.updateCsrfHash(data.csrfHash || null);

                        $('#jabatan_filter').empty().append('<option value="">-- Pilih Jabatan --</option>');
                        if (data.jabatan && data.jabatan.length > 0) {
                            $.each(data.jabatan, function (key, value) {
                                $('#jabatan_filter').append('<option value="' + value.jabatan + '">' + value.jabatan + '</option>');
                            });
                        }
                        $('#syaratUmumTab a[href="#laporan"]').tab('show');
                        $('#modal-syarat-umum').modal('show');
                    },
                    error: function () {
                        Swal.close();
                        Swal.fire('Error', 'Gagal mengambil data jabatan', 'error');
                    }
                });
            }
        },

        loadSyaratUmum: function () {
            var jabatan = $('#jabatan_filter').val();

            if (!jabatan) {
                this.setEditorValues('', '', '');
                return;
            }

            $.ajax({
                url: '<?= site_url("admin/kontrak/syarat-umum/get"); ?>',
                type: 'POST',
                data: $.extend({
                    paket_id: this.paketId,
                    jabatan: jabatan
                }, this.getCsrfData()),
                dataType: 'JSON',
                success: function (data) {
                    SyaratUmumModal.updateCsrfHash(data.csrfHash || null);
                    if (data.paket) {
                        SyaratUmumModal.setEditorValues(data.paket.laporan || '', data.paket.hasil || '', data.paket.tugas_tanggung_jawab || '');
                    } else {
                        SyaratUmumModal.setEditorValues('', '', '');
                    }
                },
                error: function () {
                    Swal.fire('Error', 'Gagal mengambil data syarat umum', 'error');
                }
            });
        },

        saveSyaratUmum: function () {
            if (this.isSaving) {
                return;
            }

            var jabatan = $('#jabatan_filter').val();

            if (!jabatan) {
                Swal.fire('Warning', 'Pilih jabatan terlebih dahulu', 'warning');
                return;
            }

            this.isSaving = true;
            this.setSaveButtonsState(true);

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Memproses Data',
                    text: 'Mohon tunggu...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: function () {
                        Swal.showLoading();
                    }
                });
            }

            var data = $.extend({
                paket_id: this.paketId,
                jabatan: jabatan,
                laporan_modal: $('#laporan-editor').val(),
                hasil_modal: $('#hasil-editor').val(),
                tugas_tanggung_jawab_modal: $('#tugas-editor').val(),
                laporan: this.formatSyaratUmumHtml($('#laporan-editor').val()),
                hasil: this.formatSyaratUmumHtml($('#hasil-editor').val()),
                tugas_tanggung_jawab: this.formatSyaratUmumHtml($('#tugas-editor').val())
            }, this.getCsrfData());

            $.ajax({
                url: '<?= site_url("admin/kontrak/syarat-umum/save"); ?>',
                type: 'POST',
                data: data,
                dataType: 'JSON',
                success: function (result) {
                    SyaratUmumModal.isSaving = false;
                    SyaratUmumModal.setSaveButtonsState(false);
                    SyaratUmumModal.updateCsrfHash(result.csrfHash || null);
                    if (typeof Swal !== 'undefined') {
                        Swal.close();
                    }

                    if (result.success) {
                        $('#modal-syarat-umum').modal('hide');
                    } else {
                        Swal.fire('Error', result.error || 'Gagal menyimpan syarat umum', 'error');
                    }
                },
                error: function () {
                    SyaratUmumModal.isSaving = false;
                    SyaratUmumModal.setSaveButtonsState(false);
                    if (typeof Swal !== 'undefined') {
                        Swal.close();
                    }
                    Swal.fire('Error', 'Gagal menyimpan syarat umum', 'error');
                }
            });
        },

        autoSelectSingleJabatan: function () {
            var $filter = $('#jabatan_filter');
            if ($filter.val()) {
                return;
            }

            if ($filter.find('option').length === 2) {
                $filter.prop('selectedIndex', 1).trigger('change');
            }
        },

        setSaveButtonsState: function (isBusy) {
            $('.js-submit-syarat-umum')
                .prop('disabled', isBusy)
                .toggleClass('disabled', isBusy);
        },

        applyInlineFormat: function (format) {
            var paneId = this.getActivePaneId();
            var textarea = $(paneId + ' textarea.form-syarat-umum-editor').get(0);
            if (!textarea) {
                return;
            }

            var markers = {
                bold: ['**', '**'],
                italic: ['*', '*'],
                underline: ['++', '++']
            };

            if (!markers[format]) {
                return;
            }

            var start = textarea.selectionStart || 0;
            var end = textarea.selectionEnd || 0;
            var before = textarea.value.substring(0, start);
            var selected = textarea.value.substring(start, end);
            var after = textarea.value.substring(end);

            var open = markers[format][0];
            var close = markers[format][1];

            textarea.value = before + open + selected + close + after;

            if (selected.length === 0) {
                var cursor = start + open.length;
                textarea.setSelectionRange(cursor, cursor);
            } else {
                textarea.setSelectionRange(start + open.length, end + open.length);
            }

            textarea.focus();
            $(textarea).trigger('input');
        },

        fillTemplateForActiveTab: function () {
            var paneId = this.getActivePaneId();
            var textareaSelector = paneId + ' textarea.form-syarat-umum-editor';
            var $textarea = $(textareaSelector);

            if (! $textarea.length) {
                return;
            }

            var templateText = this.getTemplateTextByPaneId(paneId);
            if (templateText === '') {
                return;
            }

            var currentText = String($textarea.val() || '').trim();
            if (currentText !== '') {
                var proceed = function () {
                    $textarea.val(templateText);
                    $textarea.trigger('input');
                };

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Timpa isi saat ini?',
                        text: 'Template akan mengganti isi yang sudah ada.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, isi template',
                        cancelButtonText: 'Batal',
                        reverseButtons: true
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            proceed();
                        }
                    });
                    return;
                }

                if (window.confirm('Template akan mengganti isi yang sudah ada. Lanjutkan?')) {
                    proceed();
                }

                return;
            }

            $textarea.val(templateText);
            $textarea.trigger('input');
        },

        getActivePaneId: function () {
            return $('#syaratUmumTab .nav-link.active').attr('href') || '#laporan';
        },

        getTemplateTextByPaneId: function (paneId) {
            if (paneId === '#hasil') {
                return [
                    'PRODUK HASIL PEKERJAAN',
                    'Sub Judul',
                    '1. Output pertama',
                    '2. Output kedua',
                    'a. Detail output',
                ].join('\n');
            }

            if (paneId === '#tugas') {
                return [
                    'TUGAS DAN TANGGUNG JAWAB',
                    'Sub Judul',
                    '1. Tugas pertama',
                    '2. Tugas kedua',
                    'a. Rincian tugas',
                ].join('\n');
            }

            return [
                'LAPORAN HASIL PEKERJAAN',
                'Sub Judul',
                '1. Uraian pertama',
                '2. Uraian kedua',
                'a. Sub uraian',
            ].join('\n');
        },

        updatePreviewForActiveTab: function () {
            var activePaneId = this.getActivePaneId();
            var textareaSelector = activePaneId + ' textarea.form-syarat-umum-editor';
            var rawValue = $(textareaSelector).val() || '';
            var formatted = this.formatSyaratUmumHtml(rawValue);

            if (!formatted) {
                $('#preview-syarat-umum').html('<span class="text-muted">Preview akan tampil di sini.</span>');
                return;
            }

            $('#preview-syarat-umum').html(formatted);
        },

        formatSyaratUmumHtml: function (html) {
            if (!html) {
                return '';
            }

            var normalized = String(html)
                .replace(/<p><br><\/p>/gi, '')
                .replace(/&nbsp;/gi, ' ')
                .replace(/\s+$/g, '');

            var escapeHtml = function (value) {
                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            };

            var applyInlineMarkup = function (value) {
                return String(value)
                    .replace(/\+\+([^+\n][\s\S]*?)\+\+/g, '<u>$1</u>')
                    .replace(/\*\*([^*\n][\s\S]*?)\*\*/g, '<strong>$1</strong>')
                    .replace(/\*([^*\n][\s\S]*?)\*/g, '<em>$1</em>');
            };

            var lines = normalized.split(/\r?\n/).map(function (line) {
                return String(line).trim();
            }).filter(function (line) {
                return line !== '';
            });

            if (!lines.length) {
                return '';
            }

            var makeLineBlock = function (marker, text, markerWidth) {
                var width = markerWidth || '20px';

                return [
                    '<div style="margin: 0 0 2px 0; padding-left: ' + width + '; text-indent: -' + width + '; line-height: 1.2; text-align: justify; white-space: normal; word-break: normal; overflow-wrap: break-word; word-wrap: break-word;">',
                    '<span style="display: inline-block; width: ' + width + '; text-align: right; padding-right: 2px; vertical-align: top;">' + marker + '</span>',
                    applyInlineMarkup(escapeHtml(text)),
                    '</div>'
                ].join('');
            };

            var output = [];

            lines.forEach(function (line) {
                var numberedMatch = line.match(/^(\d+)\.\s+(.*)$/);
                var alphaMatch = line.match(/^([a-z])\.\s+(.*)$/i);

                if (numberedMatch) {
                    output.push(makeLineBlock(numberedMatch[1] + '.', numberedMatch[2], '14px'));
                    return;
                }

                if (alphaMatch) {
                    output.push(makeLineBlock(alphaMatch[1].toLowerCase() + '.', alphaMatch[2], '12px'));
                    return;
                }

                output.push('<div style="margin: 0 0 2px 0; line-height: 1.2;">' + applyInlineMarkup(escapeHtml(line)) + '</div>');
            });

            return output.join('');
        },

        setEditorValues: function (laporan, hasil, tugas) {
            $('#laporan-editor').val(this.convertHtmlToText(laporan));
            $('#hasil-editor').val(this.convertHtmlToText(hasil));
            $('#tugas-editor').val(this.convertHtmlToText(tugas));
            this.updatePreviewForActiveTab();
        },

        convertHtmlToText: function (value) {
            var html = String(value || '');
            if (html.trim() === '') {
                return '';
            }

            if (!/<[a-z][\s\S]*>/i.test(html)) {
                return html;
            }

            var text = html
                .replace(/<\/span>\s*<span/gi, '</span> <span')
                .replace(/<strong[^>]*>(.*?)<\/strong>/gi, '**$1**')
                .replace(/<b[^>]*>(.*?)<\/b>/gi, '**$1**')
                .replace(/<em[^>]*>(.*?)<\/em>/gi, '*$1*')
                .replace(/<i[^>]*>(.*?)<\/i>/gi, '*$1*')
                .replace(/<u[^>]*>(.*?)<\/u>/gi, '++$1++')
                .replace(/<br\s*\/?>/gi, '\n')
                .replace(/<\/p>/gi, '\n')
                .replace(/<\/div>/gi, '\n')
                .replace(/<tr[^>]*>/gi, '\n')
                .replace(/<\/tr>/gi, '\n')
                .replace(/<td[^>]*>/gi, ' ')
                .replace(/<\/td>/gi, ' ')
                .replace(/<li[^>]*>/gi, '- ')
                .replace(/<\/li>/gi, '\n')
                .replace(/<\/ol>/gi, '\n')
                .replace(/<\/ul>/gi, '\n')
                .replace(/<[^>]+>/g, '');

            var decoded = $('<textarea/>').html(text).text();

            return decoded
                .replace(/\n{3,}/g, '\n\n')
                .trim();
        },

        getCsrfData: function () {
            var data = {};
            data[csrfTokenName] = $('#form-syarat-umum input[name="<?= csrf_token(); ?>"]').val();
            return data;
        },

        updateCsrfHash: function (hash) {
            if (!hash) {
                return;
            }

            $('#form-syarat-umum input[name="<?= csrf_token(); ?>"]').val(hash);
        },

        resetFormState: function () {
            $('#jabatan_filter').val('');
            $('#laporan-editor').val('');
            $('#hasil-editor').val('');
            $('#tugas-editor').val('');
            $('#preview-syarat-umum').html('<span class="text-muted">Preview akan tampil di sini.</span>');
        }
    };

    $(document).ready(function () {
        SyaratUmumModal.init();

        $('#form-tambah-paket').on('submit', function () {
            var $submitButton = $(this).find('button[type="submit"]');
            $submitButton.prop('disabled', true);

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Memproses Data',
                    text: 'Mohon tunggu...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: function () {
                        Swal.showLoading();
                    }
                });
            }
        });

        $('#form-kop-surat-paket').on('submit', function (e) {
            var form = this;
            var $form = $(form);
            var $submitButton = $form.find('button[type="submit"]');

            if ($form.data('submitting') === true) {
                return;
            }

            e.preventDefault();

            var proceed = function () {
                $form.data('submitting', true);
                $submitButton.prop('disabled', true);

                if (document.activeElement && typeof document.activeElement.blur === 'function') {
                    document.activeElement.blur();
                }

                $('#modal-kop-surat-paket')
                    .one('hidden.bs.modal', function () {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Memproses Data',
                                text: 'Mohon tunggu...',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                didOpen: function () {
                                    Swal.showLoading();
                                }
                            });
                        }

                        window.setTimeout(function () {
                            HTMLFormElement.prototype.submit.call(form);
                        }, 0);
                    })
                    .modal('hide');
            };

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Simpan perubahan kop surat?',
                    text: 'Data paket akan diperbarui.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, simpan',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then(function (result) {
                    if (result.isConfirmed) {
                        proceed();
                    }
                });
                return;
            }

            proceed();
        });
    });
})(jQuery);
</script>
<?= $this->endSection(); ?>
