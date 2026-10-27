<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php
    $canAdd = (bool) ($can_add ?? true);
    $canEdit = (bool) ($can_edit ?? false);
    $canApprove = (bool) ($can_approve ?? false);
    $currentPegawai = $current_pegawai ?? [];
    $kopSuratList = $kop_surat_list ?? [];
?>
<style>
    #tableLupaAbsen thead th {
        vertical-align: middle !important;
        text-align: center;
        padding: 8px 10px !important;
        font-size: 13.5px;
    }

    #tableLupaAbsen tbody td {
        vertical-align: middle !important;
        padding: 8px 10px !important;
        font-size: 13.5px;
    }

    .doc-btn-group {
        display: inline-flex;
        flex-wrap: wrap;
        gap: 4px;
        justify-content: center;
    }

    .doc-btn-group .btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        font-size: 14px;
    }

    .jam-24-wrapper {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .jam-24-wrapper select.form-control {
        width: 82px;
        text-align: center;
        font-weight: 600;
        padding-left: 6px;
        padding-right: 6px;
    }

    .jam-24-sep {
        font-weight: 700;
        font-size: 16px;
        color: #495057;
    }

    .jam-preset-btns .btn {
        font-size: 11.5px;
        padding: 2px 8px;
    }
</style>

<div class="card">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title mb-0">Daftar Pengajuan Lupa Absen</h3>
        <?php if ($canAdd): ?>
        <div class="card-tools ml-auto">
            <button type="button" class="btn btn-primary btn-sm" onclick="openCreateModal()">
                <i class="fas fa-plus"></i> Ajukan Lupa Absen
            </button>
        </div>
        <?php endif; ?>
    </div>
    <div class="card-body">

        <div class="table-responsive">
            <table id="tableLupaAbsen" class="table table-bordered table-striped table-hover w-100">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>NIP</th>
                        <th>Nama</th>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th>Alasan</th>
                        <th>Status</th>
                        <th>Dokumen</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Buat Lupa Absen -->
<div class="modal fade" id="modalLupaAbsen" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Ajukan Lupa Absen</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" action="<?= site_url('admin/surat/lupa-absen/buat'); ?>" id="formLupaAbsen">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <!-- Data Pegawai (Read-only) -->
                    <div class="alert alert-info mb-3">
                        <strong>Data Pegawai:</strong><br>
                        Nama: <?= esc($currentPegawai['nama'] ?? '-'); ?><br>
                        NIP: <?= esc($currentPegawai['nip'] ?? '-'); ?><br>
                        Jabatan: <?= esc($currentPegawai['jabatan'] ?? '-'); ?>
                    </div>

                    <input type="hidden" name="nama" value="<?= esc($currentPegawai['nama'] ?? ''); ?>">
                    <input type="hidden" name="nip" value="<?= esc($currentPegawai['nip'] ?? ''); ?>">
                    <input type="hidden" name="jabatan_id" value="<?= (int) ($currentPegawai['jabatan_id'] ?? 0); ?>">
                    <input type="hidden" name="jabatan" value="<?= esc($currentPegawai['jabatan'] ?? ''); ?>">
                    <input type="hidden" name="unit_kerja" value="<?= esc($currentPegawai['unit_kerja'] ?? ''); ?>">

                    <?php if (!empty($kopSuratList)): ?>
                        <div class="form-group">
                            <label for="kop_surat_id">Pilih Kop Surat <span class="text-danger">*</span></label>
                            <select class="form-control no-select2" id="kop_surat_id" name="kop_surat_id" required>
                                <?php foreach ($kopSuratList as $kop): ?>
                                    <option value="<?= $kop['id']; ?>" <?= $kop['is_active'] ? 'selected' : ''; ?>>
                                        <?= esc($kop['nama']); ?> <?= $kop['is_active'] ? '(Aktif)' : ''; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="tanggal_absen">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_absen" name="tanggal_absen" required>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="jenis_absen">Jenis Absen <span class="text-danger">*</span></label>
                            <select class="form-control no-select2" id="jenis_absen" name="jenis_absen" required onchange="updateAlasanTemplate()">
                                <option value="">-- Pilih --</option>
                                <option value="masuk">Absen Masuk</option>
                                <option value="pulang">Absen Pulang</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="jam_absen">Jam Absen (Format 24 Jam) <span class="text-danger" id="jamRequiredMark">*</span></label>
                        <div class="jam-24-wrapper">
                            <select class="form-control no-select2" id="jam_hour" name="jam_hour" aria-label="Jam (00-23)" onchange="syncJamFromSelects()">
                                <option value="">--</option>
                                <?php for ($h = 0; $h <= 23; $h++): ?>
                                    <?php $hStr = sprintf('%02d', $h); ?>
                                    <option value="<?= $hStr; ?>" <?= $h === 16 ? 'selected' : ''; ?>><?= $hStr; ?></option>
                                <?php endfor; ?>
                            </select>
                            <span class="jam-24-sep">:</span>
                            <select class="form-control no-select2" id="jam_minute" name="jam_minute" aria-label="Menit (00-59)" onchange="syncJamFromSelects()">
                                <option value="">--</option>
                                <?php for ($m = 0; $m <= 59; $m++): ?>
                                    <?php $mStr = sprintf('%02d', $m); ?>
                                    <option value="<?= $mStr; ?>" <?= $m === 0 ? 'selected' : ''; ?>><?= $mStr; ?></option>
                                <?php endfor; ?>
                            </select>
                            <input type="text"
                                   class="form-control ml-2"
                                   id="jam_absen"
                                   name="jam_absen"
                                   value="16:00"
                                   placeholder="HH:mm (contoh: 16:00)"
                                   maxlength="5"
                                   pattern="^([01][0-9]|2[0-3]):[0-5][0-9]$"
                                   title="Gunakan format 24 jam HH:mm, contoh: 07:30 atau 16:00"
                                   oninput="handleJamTextInput(this)"
                                   onblur="normalizeJamTextInput(this)"
                                   required>
                        </div>
                        <div class="jam-preset-btns mt-2 d-flex flex-wrap align-items-center" style="gap: 5px;">
                            <small class="text-muted mr-1">Pilih Cepat (24 Jam):</small>
                            <button type="button" class="btn btn-outline-secondary btn-xs" onclick="setJam24('07:30')">07:30</button>
                            <button type="button" class="btn btn-outline-secondary btn-xs" onclick="setJam24('08:00')">08:00</button>
                            <button type="button" class="btn btn-outline-secondary btn-xs" onclick="setJam24('12:00')">12:00</button>
                            <button type="button" class="btn btn-outline-secondary btn-xs" onclick="setJam24('16:00')">16:00</button>
                            <button type="button" class="btn btn-outline-secondary btn-xs" onclick="setJam24('16:30')">16:30</button>
                            <button type="button" class="btn btn-outline-secondary btn-xs" onclick="setJam24('17:00')">17:00</button>
                        </div>
                    </div>

                    <div class="form-group mb-1">
                        <label for="alasan_detail">Alasan <span class="text-danger">*</span></label>
                        <select class="form-control no-select2" id="alasan_select" onchange="handleAlasanSelect()">
                            <option value="">-- Pilih Template --</option>
                            <option value="Lupa Absen Masuk">Lupa Absen Masuk</option>
                            <option value="Lupa Absen Pulang">Lupa Absen Pulang</option>
                            <option value="Terlambat Masuk">Terlambat Masuk</option>
                            <option value="Pulang Sebelum Waktunya">Pulang Sebelum Waktunya</option>
                            <option value="__custom__">Ketik Manual...</option>
                        </select>
                        <input type="text" class="form-control mt-2" id="alasan_custom" name="alasan_detail" placeholder="Ketik alasan di sini..." oninput="updatePdfReasonPreview()" style="display: none;">
                        <div class="alert alert-light border mt-2 mb-0 py-2 px-3 small">
                            <i class="fas fa-file-pdf text-danger mr-1"></i>
                            Hasil pada Export PDF: <strong id="pdfReasonPreviewText" class="text-dark">-</strong>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Ajukan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus pengajuan lupa absen ini?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <a id="btnConfirmDelete" href="#" class="btn btn-danger">Hapus</a>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script>
var isEditingLegacyWithoutJam = false;

function clearJam24() {
    document.getElementById('jam_hour').value = '';
    document.getElementById('jam_minute').value = '';
    document.getElementById('jam_absen').value = '';
    updatePdfReasonPreview();
}

function setJam24(jamStr) {
    var clean = (jamStr || '').trim().replace('.', ':');
    if (clean === '') {
        clearJam24();
        return;
    }
    var match = clean.match(/^(\d{1,2}):(\d{2})/);
    if (!match) {
        return;
    }
    var h = Math.min(23, Math.max(0, parseInt(match[1], 10)));
    var m = Math.min(59, Math.max(0, parseInt(match[2], 10)));
    var hPad = (h < 10 ? '0' : '') + h;
    var mPad = (m < 10 ? '0' : '') + m;
    document.getElementById('jam_hour').value = hPad;
    document.getElementById('jam_minute').value = mPad;
    document.getElementById('jam_absen').value = hPad + ':' + mPad;
    updatePdfReasonPreview();
}

function syncJamFromSelects() {
    var h = document.getElementById('jam_hour').value;
    var m = document.getElementById('jam_minute').value;
    if (h === '' && m === '') {
        document.getElementById('jam_absen').value = '';
        updatePdfReasonPreview();
        return;
    }
    h = h || '00';
    m = m || '00';
    document.getElementById('jam_hour').value = h;
    document.getElementById('jam_minute').value = m;
    document.getElementById('jam_absen').value = h + ':' + m;
    updatePdfReasonPreview();
}

function handleJamTextInput(inputEl) {
    var val = inputEl.value.replace(/[^0-9:.]/g, '').replace('.', ':');
    if (val === '') {
        document.getElementById('jam_hour').value = '';
        document.getElementById('jam_minute').value = '';
        updatePdfReasonPreview();
        return;
    }
    if (/^\d{3,4}$/.test(val)) {
        val = val.slice(0, 2) + ':' + val.slice(2, 4);
    }
    inputEl.value = val;
    var match = val.match(/^(\d{2}):(\d{2})$/);
    if (match) {
        var h = parseInt(match[1], 10);
        var m = parseInt(match[2], 10);
        if (h >= 0 && h <= 23 && m >= 0 && m <= 59) {
            document.getElementById('jam_hour').value = match[1];
            document.getElementById('jam_minute').value = match[2];
        }
    }
    updatePdfReasonPreview();
}

function normalizeJamTextInput(inputEl) {
    var val = (inputEl.value || '').trim().replace('.', ':');
    if (val === '' && isEditingLegacyWithoutJam) {
        clearJam24();
        return;
    }
    var match = val.match(/^(\d{1,2}):(\d{1,2})$/);
    if (match) {
        setJam24(match[1] + ':' + (match[2].length === 1 ? '0' + match[2] : match[2]));
    } else {
        syncJamFromSelects();
    }
}

function updatePdfReasonPreview() {
    var alasan = (document.getElementById('alasan_custom').value || '').trim();
    alasan = alasan.replace(/\s*,\s*Pukul\s+\d{1,2}[:.]\d{2}(?::\d{2})?\s*$/i, '').trim();
    var jam = (document.getElementById('jam_absen').value || '').trim();
    var previewEl = document.getElementById('pdfReasonPreviewText');
    if (!previewEl) {
        return;
    }
    if (alasan === '' && jam === '') {
        previewEl.textContent = '-';
        return;
    }
    if (alasan !== '' && jam !== '') {
        previewEl.textContent = alasan + ', Pukul ' + jam;
    } else if (alasan !== '') {
        previewEl.textContent = alasan;
    } else {
        previewEl.textContent = 'Pukul ' + jam;
    }
}

function openCreateModal() {
    isEditingLegacyWithoutJam = false;
    // Reset form
    document.getElementById('formLupaAbsen').reset();
    document.getElementById('alasan_custom').style.display = 'none';
    document.getElementById('alasan_custom').value = '';
    document.getElementById('alasan_select').value = '';
    document.getElementById('jam_absen').required = true;
    if (document.getElementById('jamRequiredMark')) {
        document.getElementById('jamRequiredMark').style.display = 'inline';
    }
    
    // Reset form action
    var actionUrl = '<?= site_url('admin/surat/lupa-absen/buat'); ?>';
    document.getElementById('formLupaAbsen').setAttribute('action', actionUrl);
    
    // Reset modal title
    $('#modalLupaAbsen .modal-title').text('Ajukan Lupa Absen');

    // Set default date to today
    var today = new Date().toISOString().split('T')[0];
    document.getElementById('tanggal_absen').value = today;

    // Default jam 16:00 (24-hour format) for new submission
    setJam24('16:00');
    updatePdfReasonPreview();

    // Show modal
    $('#modalLupaAbsen').modal('show');
}

function openEditModal(data) {
    // Reset form
    document.getElementById('formLupaAbsen').reset();
    
    // Set form action
    var actionUrl = '<?= site_url('admin/surat/lupa-absen'); ?>/' + data.id + '/ubah';
    document.getElementById('formLupaAbsen').setAttribute('action', actionUrl);
    
    // Set modal title
    $('#modalLupaAbsen .modal-title').text('Ubah Pengajuan Lupa Absen');
    
    // Populate fields
    document.getElementById('tanggal_absen').value = data.tanggal_absen;
    document.getElementById('jenis_absen').value = data.jenis_absen;
    
    if (document.getElementById('kop_surat_id')) {
        document.getElementById('kop_surat_id').value = data.kop_surat_id || '';
    }

    // Populate jam_absen (24h) - keep empty for legacy records without jam
    var jamVal = data.jam_absen || '';
    if (!jamVal && data.alasan_detail) {
        var mJam = String(data.alasan_detail).match(/Pukul\s+(\d{1,2}[:.]\d{2})/i);
        if (mJam) {
            jamVal = mJam[1];
        }
    }
    if (jamVal) {
        isEditingLegacyWithoutJam = false;
        document.getElementById('jam_absen').required = true;
        if (document.getElementById('jamRequiredMark')) {
            document.getElementById('jamRequiredMark').style.display = 'inline';
        }
        setJam24(jamVal);
    } else {
        isEditingLegacyWithoutJam = true;
        document.getElementById('jam_absen').required = false;
        if (document.getElementById('jamRequiredMark')) {
            document.getElementById('jamRequiredMark').style.display = 'none';
        }
        clearJam24();
    }
    
    // Populate alasan
    var alasan = (data.alasan_raw !== undefined ? data.alasan_raw : (data.alasan_detail || '')).replace(/\s*,\s*Pukul\s+\d{1,2}[:.]\d{2}(?::\d{2})?\s*$/i, '').trim();
    var templates = ['Lupa Absen Masuk', 'Lupa Absen Pulang', 'Terlambat Masuk', 'Pulang Sebelum Waktunya'];
    var select = document.getElementById('alasan_select');
    var customInput = document.getElementById('alasan_custom');
    
    if (templates.includes(alasan)) {
        select.value = alasan;
        customInput.value = alasan;
        customInput.style.display = 'none';
        customInput.required = false;
    } else {
        select.value = '__custom__';
        customInput.value = alasan;
        customInput.style.display = 'block';
        customInput.required = true;
    }

    updatePdfReasonPreview();
    
    // Show modal
    $('#modalLupaAbsen').modal('show');
}

function updateAlasanTemplate() {
    var jenis = document.getElementById('jenis_absen').value;
    var select = document.getElementById('alasan_select');
    var currentJam = document.getElementById('jam_absen').value;

    if (jenis === 'masuk') {
        select.value = 'Lupa Absen Masuk';
        if (!isEditingLegacyWithoutJam && (!currentJam || currentJam === '16:00')) {
            setJam24('07:30');
        }
    } else if (jenis === 'pulang') {
        select.value = 'Lupa Absen Pulang';
        if (!isEditingLegacyWithoutJam && (!currentJam || currentJam === '07:30')) {
            setJam24('16:00');
        }
    }

    handleAlasanSelect();
}

function handleAlasanSelect() {
    var select = document.getElementById('alasan_select');
    var customInput = document.getElementById('alasan_custom');

    if (select.value === '__custom__') {
        customInput.style.display = 'block';
        customInput.required = true;
        customInput.focus();
    } else if (select.value !== '') {
        // Set the hidden field directly
        customInput.name = 'alasan_detail';
        customInput.value = select.value;
        customInput.style.display = 'none';
        customInput.required = false;
    } else {
        customInput.value = '';
        customInput.style.display = 'none';
        customInput.required = false;
    }
    updatePdfReasonPreview();
}

$(document).ready(function() {
    var table = $('#tableLupaAbsen').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= site_url('admin/surat/lupa-absen'); ?>',
            type: 'GET'
        },
        columns: [
            {
                data: null,
                sortable: false,
                searchable: false,
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                },
                className: 'text-center'
            },
            { data: 'nip', className: 'text-center' },
            { data: 'nama' },
            { data: 'tanggal_formatted', className: 'text-center' },
            { data: 'jenis_formatted', className: 'text-center' },
            { data: 'alasan_detail' },
            { data: 'status_badge', className: 'text-center', sortable: false, searchable: false },
            { data: 'dokumen_html', className: 'text-center', sortable: false, searchable: false },
            { data: 'action_html', className: 'text-center', sortable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        language: {
            processing: 'Memproses...',
            zeroRecords: 'Tidak ada data',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            infoEmpty: 'Menampilkan 0 - 0 dari 0 data',
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data',
            paginate: {
                first: 'Pertama',
                last: 'Terakhir',
                next: 'Berikutnya',
                previous: 'Sebelumnya'
            }
        }
    });

    // Edit button handler
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        var rowData = table.row($(this).closest('tr')).data();
        if (rowData) {
            openEditModal(rowData);
        }
    });

    // Delete button handler
    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        $('#btnConfirmDelete').attr('href', '<?= site_url('admin/surat/lupa-absen'); ?>/' + id + '/hapus');
        $('#deleteModal').modal('show');
    });

    // Approve button handler
    $(document).on('click', '.btn-approve', function() {
        var id = $(this).data('id');
        if (confirm('Apakah Anda yakin ingin MENYETUJUI pengajuan ini?')) {
            window.location.href = '<?= site_url('admin/surat/lupa-absen'); ?>/' + id + '/approve';
        }
    });

    // Reject button handler
    $(document).on('click', '.btn-reject', function() {
        var id = $(this).data('id');
        if (confirm('Apakah Anda yakin ingin MENOLAK pengajuan ini?')) {
            window.location.href = '<?= site_url('admin/surat/lupa-absen'); ?>/' + id + '/reject';
        }
    });
});
</script>
<?= $this->endSection(); ?>
