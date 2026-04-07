<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php
$titles = $titles ?? [];
$reports = $reports ?? [];
$historyMap = $history_map ?? [];
$canEdit = (bool) ($can_edit ?? false);

$weekLabelFromDate = static function (?string $date): string {
    if (! is_string($date) || trim($date) === '') {
        return '-';
    }

    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return '-';
    }

    $weekOfYear = (int) date('W', $timestamp);
    $weekYear = (int) date('o', $timestamp);
    return 'Minggu ke-' . $weekOfYear . ' (' . $weekYear . ')';
};

$weekNumberFromDate = static function (?string $date): string {
    if (! is_string($date) || trim($date) === '') {
        return '-';
    }

    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return '-';
    }

    $weekOfYear = (int) date('W', $timestamp);
    $weekYear = (int) date('o', $timestamp);
    return 'Minggu ke-' . $weekOfYear . ' (' . $weekYear . ')';
};
?>
<div class="card">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title mb-0">Laporan Mingguan</h3>
        <?php if ($canEdit): ?>
            <div class="card-tools ml-auto">
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalMingguanForm">Tambah Laporan Mingguan</button>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover w-100" id="tableLaporanMingguan">
                <thead class="thead-light">
                    <tr>
                        <th style="width:60px;">No</th>
                        <th>Sekolah</th>
                        <th style="width:150px;" class="text-center">Tanggal Dipilih</th>
                        <th style="width:190px;" class="text-center">Minggu Ke-</th>
                        <th>Deskripsi</th>
                        <th style="width:100px;" class="text-center">File</th>
                        <?php if ($canEdit): ?><th style="width:130px;" class="text-center">Aksi</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($reports !== []): ?>
                        <?php $no = 1; foreach ($reports as $report): ?>
                            <?php
                            $reportPayload = [
                                'id' => (int) ($report['id'] ?? 0),
                                'sekolah_id' => (int) ($report['sekolah_id'] ?? 0),
                                'period_date' => (string) ($report['period_start'] ?? ''),
                                'description' => (string) ($report['description'] ?? ''),
                            ];
                            $reportHistory = $historyMap[(int) ($report['id'] ?? 0)] ?? [];
                            ?>
                            <tr>
                                <td class="text-center"><?= esc((string) $no++); ?></td>
                                <td><?= esc((string) ($report['sekolah_name'] ?? '-')); ?></td>
                                <td class="text-center"><?= esc((string) ($report['period_start'] ?? '-')); ?></td>
                                <td class="text-center"><?= esc($weekLabelFromDate((string) ($report['period_start'] ?? ''))); ?></td>
                                <td><?= nl2br(esc((string) ($report['description'] ?? '-'))); ?></td>
                                <td class="text-center">
                                    <?php if (! empty($report['file_path'])): ?>
                                        <a href="<?= esc((string) $report['file_path']); ?>" target="_blank" class="btn btn-info btn-sm" title="Lihat File">
                                            <i class="fas fa-file-alt"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <?php if ($canEdit): ?>
                                    <td class="text-center">
                                        <button
                                            type="button"
                                            class="btn btn-warning btn-sm js-edit-mingguan"
                                            data-report="<?= esc(json_encode($reportPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'attr'); ?>"
                                            data-toggle="modal"
                                            data-target="#modalMingguanForm"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-secondary btn-sm js-history-mingguan"
                                            data-history="<?= esc(json_encode($reportHistory, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'attr'); ?>"
                                            data-toggle="modal"
                                            data-target="#modalMingguanHistory"
                                        >
                                            Riwayat
                                        </button>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalMingguanHistory" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mb-0">Riwayat Edit Laporan Mingguan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width:180px;">Waktu Edit</th>
                                <th style="width:120px;">Editor</th>
                                <th style="width:140px;">Tanggal Dipilih</th>
                                <th>Deskripsi</th>
                                <th style="width:90px;" class="text-center">File</th>
                            </tr>
                        </thead>
                        <tbody id="mingguanHistoryBody">
                            <tr>
                                <td colspan="5" class="text-center text-muted">Belum ada riwayat edit.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?php if ($canEdit): ?>
<div class="modal fade" id="modalMingguanForm" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0" id="mingguanModalTitle">Tambah Laporan Mingguan</h5>
                    <small class="text-muted">Upload file PDF, PPT, atau PPTX.</small>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formMingguan" action="<?= site_url('admin/laporan/mingguan/tambah'); ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <input type="hidden" name="report_id" id="mingguanReportId" value="">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="sekolah_id_mingguan">Sekolah</label>
                        <select class="form-control" id="sekolah_id_mingguan" name="sekolah_id" required>
                            <option value="">- pilih sekolah -</option>
                            <?php foreach ($titles as $title): ?>
                                <option value="<?= esc((string) ($title['id'] ?? 0)); ?>"><?= esc((string) ($title['name'] ?? '-')); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="period_date">Tanggal Laporan</label>
                            <input type="date" class="form-control" id="period_date" name="period_date" value="<?= esc(date('Y-m-d')); ?>" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="week_label">Minggu Keberapa</label>
                            <input type="text" class="form-control" id="week_label" value="<?= esc($weekNumberFromDate(date('Y-m-d'))); ?>" readonly>
                            <small class="text-muted d-block mt-1">Terhitung otomatis berdasarkan minggu dalam tahun dari tanggal yang dipilih.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Keterangan</label>
                        <textarea class="form-control" id="description" name="description" rows="4" placeholder="Opsional"></textarea>
                    </div>

                    <div class="form-group mb-0">
                        <label for="report_file">File Laporan</label>
                        <input type="file" class="form-control" id="report_file" name="report_file" accept=".pdf,.ppt,.pptx,application/pdf,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation">
                        <small class="text-muted d-block mt-1">Format: PDF/PPT/PPTX. Ukuran maksimal 10MB. Jika edit dan tidak mengunggah file baru, file lama tetap dipakai.</small>
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
<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script>
(() => {
    if (typeof $ === 'undefined' || ! $.fn.DataTable) {
        return;
    }

    const $table = $('#tableLaporanMingguan');
    if ($table.length && ! $.fn.dataTable.isDataTable($table)) {
        $table.DataTable({
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
                emptyTable: 'Belum ada laporan mingguan.',
                paginate: {
                    first: 'Awal',
                    last: 'Akhir',
                    next: 'Berikutnya',
                    previous: 'Sebelumnya',
                },
            },
        });
    }
})();

(() => {
    const historyBody = document.getElementById('mingguanHistoryBody');

    if (!historyBody) {
        return;
    }

    const emptyState = '<tr><td colspan="5" class="text-center text-muted">Belum ada riwayat edit.</td></tr>';

    const escapeHtml = (value) => String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');

    const rowHtml = (item) => {
        const changedAt = escapeHtml(item.changed_at || '-');
        const changedBy = escapeHtml(item.changed_by || '-');
        const periodStart = escapeHtml(item.period_start || '-');
        const description = escapeHtml(item.description || '-').replace(/\n/g, '<br>');
        const filePath = String(item.file_path || '').trim();

        const fileCell = filePath !== ''
            ? `<a href="${escapeHtml(filePath)}" target="_blank" class="btn btn-info btn-sm" title="Lihat File"><i class="fas fa-file-alt"></i></a>`
            : '<span class="text-muted">-</span>';

        return `<tr>
            <td>${changedAt}</td>
            <td>${changedBy}</td>
            <td>${periodStart}</td>
            <td>${description}</td>
            <td class="text-center">${fileCell}</td>
        </tr>`;
    };

    document.querySelectorAll('.js-history-mingguan').forEach((button) => {
        button.addEventListener('click', () => {
            let payload = [];
            try {
                payload = JSON.parse(button.getAttribute('data-history') || '[]');
            } catch (error) {
                payload = [];
            }

            if (!Array.isArray(payload) || payload.length === 0) {
                historyBody.innerHTML = emptyState;
                return;
            }

            historyBody.innerHTML = payload.map((item) => rowHtml(item)).join('');
        });
    });

    if (typeof $ !== 'undefined') {
        $('#modalMingguanHistory').on('hidden.bs.modal', () => {
            historyBody.innerHTML = emptyState;
        });
    }
})();

(() => {
    const form = document.getElementById('formMingguan');
    const reportIdInput = document.getElementById('mingguanReportId');
    const modalTitle = document.getElementById('mingguanModalTitle');
    const periodDateInput = document.getElementById('period_date');
    const weekLabelInput = document.getElementById('week_label');

    if (!form || !reportIdInput || !modalTitle || !periodDateInput || !weekLabelInput) {
        return;
    }

    const computeWeekOfYear = (dateString) => {
        if (!dateString) {
            return '-';
        }

        const date = new Date(`${dateString}T00:00:00`);
        if (Number.isNaN(date.getTime())) {
            return '-';
        }

        // ISO week-year (week starts Monday, week 1 contains Jan 4)
        const utcDate = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
        const dayNum = utcDate.getUTCDay() || 7;
        utcDate.setUTCDate(utcDate.getUTCDate() + 4 - dayNum);
        const isoYear = utcDate.getUTCFullYear();
        const yearStart = new Date(Date.UTC(isoYear, 0, 1));
        const week = Math.ceil((((utcDate - yearStart) / 86400000) + 1) / 7);

        return `Minggu ke-${week} (${isoYear})`;
    };

    const syncWeekLabel = () => {
        weekLabelInput.value = computeWeekOfYear(periodDateInput.value);
    };

    const resetForm = () => {
        form.reset();
        reportIdInput.value = '';
        form.action = '<?= site_url('admin/laporan/mingguan/tambah'); ?>';
        modalTitle.textContent = 'Tambah Laporan Mingguan';
        periodDateInput.value = '<?= esc(date('Y-m-d')); ?>';
        syncWeekLabel();
    };

    document.querySelectorAll('.js-edit-mingguan').forEach((button) => {
        button.addEventListener('click', () => {
            let payload = {};
            try {
                payload = JSON.parse(button.getAttribute('data-report') || '{}');
            } catch (error) {
                payload = {};
            }
            form.reset();
            reportIdInput.value = payload.id || '';
            form.action = '<?= site_url('admin/laporan/mingguan/tambah'); ?>';
            modalTitle.textContent = 'Edit Laporan Mingguan';
            document.getElementById('sekolah_id_mingguan').value = payload.sekolah_id || '';
            periodDateInput.value = payload.period_date || '<?= esc(date('Y-m-d')); ?>';
            document.getElementById('description').value = payload.description || '';
            syncWeekLabel();
        });
    });

    periodDateInput.addEventListener('change', syncWeekLabel);
    syncWeekLabel();

    $('#modalMingguanForm').on('hidden.bs.modal', resetForm);
})();
</script>
<?= $this->endSection(); ?>
