<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php
    $isEdit = (bool) ($is_edit ?? false);
    $title = $title ?? ($isEdit ? 'Ubah Lupa Absen' : 'Ajukan Lupa Absen');
    $currentInput = $current_input ?? [];
    $currentPegawai = $current_pegawai ?? [];
    $formError = $form_error ?? null;
    $formId = $id ?? null;
    $existingEntries = $existing_entries ?? [];
    $jabatanOptions = $jabatan_options ?? [];
?>
<style>
    .table-entry td {
        vertical-align: middle;
    }
    .btn-remove-entry {
        width: 32px;
        height: 32px;
        padding: 0;
    }
    .pegawai-info {
        background-color: #e8f4fd;
        border: 1px solid #b8daff;
        border-radius: 4px;
        padding: 15px;
        margin-bottom: 15px;
    }
    .pegawai-info .info-row {
        display: flex;
        margin-bottom: 8px;
    }
    .pegawai-info .info-row:last-child {
        margin-bottom: 0;
    }
    .pegawai-info .info-label {
        font-weight: 600;
        width: 140px;
        flex-shrink: 0;
    }
    .pegawai-info .info-value {
        color: #333;
    }
</style>

<div class="card">
    <div class="card-header">
        <h3 class="card-title mb-0"><?= esc($title); ?></h3>
        <div class="card-tools">
            <a href="<?= site_url('admin/surat/lupa-absen'); ?>" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
    <div class="card-body">
        <?php if (! empty($formError)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <?= esc($formError); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= site_url('admin/surat/lupa-absen' . ($isEdit ? '/' . $formId . '/ubah' : '/buat')); ?>" autocomplete="off" id="formLupaAbsen">
            <?= csrf_field(); ?>

            <!-- Data Pegawai (Auto-fill dari Login) -->
            <div class="card card-outline card-primary mb-3">
                <div class="card-header">
                    <h3 class="card-title mb-0">Data Pegawai</h3>
                    <?php if (! $isEdit): ?>
                        <span class="badge badge-info ml-2">Otomatis dari data login</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (! $isEdit): ?>
                        <!-- Display mode - show auto-filled data -->
                        <div class="pegawai-info">
                            <div class="info-row">
                                <span class="info-label">Nama:</span>
                                <span class="info-value"><?= esc($currentPegawai['nama'] ?? '-'); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">NIP:</span>
                                <span class="info-value"><?= esc($currentPegawai['nip'] ?? '-'); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Jabatan:</span>
                                <span class="info-value"><?= esc($currentPegawai['jabatan'] ?? '-'); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Unit Kerja:</span>
                                <span class="info-value"><?= esc($currentPegawai['unit_kerja'] ?? '-'); ?></span>
                            </div>
                        </div>
                        <!-- Hidden fields to submit employee data -->
                        <input type="hidden" name="nama" value="<?= esc($currentPegawai['nama'] ?? ''); ?>">
                        <input type="hidden" name="nip" value="<?= esc($currentPegawai['nip'] ?? ''); ?>">
                        <input type="hidden" name="jabatan_id" value="<?= (int) ($currentPegawai['jabatan_id'] ?? 0); ?>">
                        <input type="hidden" name="jabatan_display" value="<?= esc($currentPegawai['jabatan'] ?? ''); ?>">
                        <input type="hidden" name="unit_kerja" value="<?= esc($currentPegawai['unit_kerja'] ?? ''); ?>">
                    <?php else: ?>
                        <!-- Edit mode - allow editing -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nama">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control"
                                           id="nama"
                                           name="nama"
                                           value="<?= esc((string) ($currentInput['nama'] ?? '')); ?>"
                                           required
                                           placeholder="Masukkan nama lengkap">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nip">NIP <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control"
                                           id="nip"
                                           name="nip"
                                           value="<?= esc((string) ($currentInput['nip'] ?? '')); ?>"
                                           required
                                           placeholder="Masukkan NIP">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="jabatan_id">Jabatan <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="jabatan_id" name="jabatan_id" required>
                                        <option value="">-- Pilih Jabatan --</option>
                                        <?php foreach ($jabatan_options as $jab): ?>
                                            <option value="<?= (int) ($jab['id'] ?? 0); ?>"
                                                <?= ((int) ($currentInput['jabatan_id'] ?? 0) === (int) ($jab['id'] ?? 0)) ? 'selected' : ''; ?>>
                                                <?= esc($jab['display_label'] ?? ($jab['jabatan'] ?? '')); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="unit_kerja">Unit Kerja <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control"
                                           id="unit_kerja"
                                           name="unit_kerja"
                                           value="<?= esc((string) ($currentInput['unit_kerja'] ?? '')); ?>"
                                           required
                                           placeholder="Contoh: Sekretariat Direktorat Jenderal Prasarana Strategis">
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tabel Absensi -->
            <div class="card card-outline card-primary mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Tabel Absensi yang Terlewat</h3>
                    <button type="button" class="btn btn-success btn-sm" id="btn-add-entry">
                        <i class="fas fa-plus"></i> Tambah Baris
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="tableEntries">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th style="width: 50px;" class="text-center">No</th>
                                    <th style="width: 150px;" class="text-center">Tanggal</th>
                                    <th style="width: 120px;" class="text-center">Hari</th>
                                    <th style="width: 100px;" class="text-center">Jam</th>
                                    <th style="width: 150px;" class="text-center">Jenis Absen</th>
                                    <th>Keterangan</th>
                                    <th style="width: 80px;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="entriesBody">
                                <?php
                                $entries = $existingEntries;
                                if (empty($entries)) {
                                    $entries = [[
                                        'tanggal' => '',
                                        'hari' => '',
                                        'jam' => '',
                                        'jenis' => '',
                                        'keterangan' => ''
                                    ]];
                                }
                                foreach ($entries as $idx => $entry):
                                ?>
                                <tr class="table-entry" data-index="<?= $idx; ?>">
                                    <td class="text-center align-middle"><?= $idx + 1; ?></td>
                                    <td>
                                        <input type="date" class="form-control form-control-sm" name="entries[<?= $idx; ?>][tanggal]" value="<?= esc((string) ($entry['tanggal'] ?? '')); ?>" required>
                                    </td>
                                    <td>
                                        <select class="form-control form-control-sm" name="entries[<?= $idx; ?>][hari]" required>
                                            <option value="">--</option>
                                            <?php
                                            $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                                            $selectedHari = $entry['hari'] ?? '';
                                            foreach ($days as $day):
                                            ?>
                                            <option value="<?= $day; ?>" <?= ($selectedHari === $day) ? 'selected' : ''; ?>><?= $day; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="time" class="form-control form-control-sm" name="entries[<?= $idx; ?>][jam]" value="<?= esc((string) ($entry['jam'] ?? '')); ?>" required>
                                    </td>
                                    <td>
                                        <select class="form-control form-control-sm" name="entries[<?= $idx; ?>][jenis]" required>
                                            <option value="">-- Pilih --</option>
                                            <option value="Masuk" <?= (($entry['jenis'] ?? '') === 'Masuk') ? 'selected' : ''; ?>>Absen Masuk</option>
                                            <option value="Pulang" <?= (($entry['jenis'] ?? '') === 'Pulang') ? 'selected' : ''; ?>>Absen Pulang</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" name="entries[<?= $idx; ?>][keterangan]" value="<?= esc((string) ($entry['keterangan'] ?? '')); ?>" placeholder="Contoh: Terlambat karena macet">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger btn-sm btn-remove-entry <?= ($idx === 0) ? 'd-none' : ''; ?>" title="Hapus Baris">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <small class="text-muted">Minimal 1 baris harus diisi. Gunakan tombol "Tambah Baris" untuk menambah entri.</small>
                </div>
            </div>

            <!-- Alasan -->
            <div class="card card-outline card-primary mb-3">
                <div class="card-header">
                    <h3 class="card-title mb-0">Alasan Terlambat / Tidak Presensi</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="alasan_kategori">Kategori Alasan <span class="text-danger">*</span></label>
                                <select class="form-control" id="alasan_kategori" name="alasan_kategori" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    <option value="Macet / Terlambat" <?= (($currentInput['alasan_kategori'] ?? '') === 'Macet / Terlambat') ? 'selected' : ''; ?>>Macet / Terlambat</option>
                                    <option value="Kendaraan Bermotor Rusak" <?= (($currentInput['alasan_kategori'] ?? '') === 'Kendaraan Bermotor Rusak') ? 'selected' : ''; ?>>Kendaraan Bermotor Rusak</option>
                                    <option value="Sakit" <?= (($currentInput['alasan_kategori'] ?? '') === 'Sakit') ? 'selected' : ''; ?>>Sakit</option>
                                    <option value="Urusan Keluarga / Darurat" <?= (($currentInput['alasan_kategori'] ?? '') === 'Urusan Keluarga / Darurat') ? 'selected' : ''; ?>>Urusan Keluarga / Darurat</option>
                                    <option value="Lupa / Kelalaian" <?= (($currentInput['alasan_kategori'] ?? '') === 'Lupa / Kelalaian') ? 'selected' : ''; ?>>Lupa / Kelalaian</option>
                                    <option value="Lainnya" <?= (($currentInput['alasan_kategori'] ?? '') === 'Lainnya') ? 'selected' : ''; ?>>Lainnya</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tanggal_surat">Tanggal Surat <span class="text-danger">*</span></label>
                                <input type="date"
                                       class="form-control"
                                       id="tanggal_surat"
                                       name="tanggal_surat"
                                       value="<?= esc((string) ($currentInput['tanggal_surat'] ?? date('Y-m-d'))); ?>"
                                       required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="alasan_detail">Detail Alasan / Penjelasan <span class="text-danger">*</span></label>
                        <textarea class="form-control"
                                  id="alasan_detail"
                                  name="alasan_detail"
                                  rows="3"
                                  required
                                  placeholder="Jelaskan secara rinci alasan lupa/tidak presensi"><?= esc((string) ($currentInput['alasan_detail'] ?? '')); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Ajukan'; ?>
                </button>
                <a href="<?= site_url('admin/surat/lupa-absen'); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize Select2 if available
    if ($.fn.select2) {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
    }

    // Auto-calculate day when date changes
    $(document).on('change', 'input[name*="[tanggal]"]', function() {
        var dateVal = $(this).val();
        var $row = $(this).closest('tr');
        var $hariSelect = $row.find('select[name*="[hari]"]');

        if (dateVal && $hariSelect.length) {
            var days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            var date = new Date(dateVal);
            var dayName = days[date.getDay()];
            $hariSelect.val(dayName).trigger('change');
        }
    });

    // Add new row
    $('#btn-add-entry').click(function() {
        var $tbody = $('#entriesBody');
        var newIndex = $tbody.find('tr').length;
        var newRow = `
        <tr class="table-entry" data-index="${newIndex}">
            <td class="text-center align-middle">${newIndex + 1}</td>
            <td>
                <input type="date" class="form-control form-control-sm" name="entries[${newIndex}][tanggal]" required>
            </td>
            <td>
                <select class="form-control form-control-sm" name="entries[${newIndex}][hari]" required>
                    <option value="">--</option>
                    <option value="Senin">Senin</option>
                    <option value="Selasa">Selasa</option>
                    <option value="Rabu">Rabu</option>
                    <option value="Kamis">Kamis</option>
                    <option value="Jumat">Jumat</option>
                    <option value="Sabtu">Sabtu</option>
                    <option value="Minggu">Minggu</option>
                </select>
            </td>
            <td>
                <input type="time" class="form-control form-control-sm" name="entries[${newIndex}][jam]" required>
            </td>
            <td>
                <select class="form-control form-control-sm" name="entries[${newIndex}][jenis]" required>
                    <option value="">-- Pilih --</option>
                    <option value="Masuk">Absen Masuk</option>
                    <option value="Pulang">Absen Pulang</option>
                </select>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" name="entries[${newIndex}][keterangan]" placeholder="Contoh: Terlambat karena macet">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm btn-remove-entry" title="Hapus Baris">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
        `;
        $tbody.append(newRow);
        renumberRows();
    });

    // Remove row
    $(document).on('click', '.btn-remove-entry', function() {
        var $tbody = $('#entriesBody');
        if ($tbody.find('tr').length > 1) {
            $(this).closest('tr').remove();
            renumberRows();
        } else {
            alert('Minimal harus ada 1 baris data!');
        }
    });

    function renumberRows() {
        $('#entriesBody tr').each(function(index) {
            $(this).find('td:first').text(index + 1);
            $(this).attr('data-index', index);
            $(this).find('input, select').each(function() {
                var name = $(this).attr('name');
                if (name) {
                    $(this).attr('name', name.replace(/\[\d+\]/g, '[' + index + ']'));
                }
            });
        });

        // Show/hide remove button based on row count
        var $tbody = $('#entriesBody');
        $tbody.find('.btn-remove-entry').removeClass('d-none');
        $tbody.find('tr:first .btn-remove-entry').addClass('d-none');
    }
});
</script>
<?= $this->endSection(); ?>
