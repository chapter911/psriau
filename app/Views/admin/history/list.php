<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php
$rows = $rows ?? [];
$type = (string) ($history_type ?? '-');
$tableReady = (bool) ($table_ready ?? false);
$isEditHistory = $type === 'edit';
$isDeleteHistory = $type === 'delete';
?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title mb-0">History <?= esc(ucfirst($type)); ?></h3>
    </div>
    <div class="card-body">
        <?php if (! $tableReady): ?>
            <div class="alert alert-warning mb-0">Tabel audit belum tersedia. Jalankan migration terlebih dahulu.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped w-100 js-datatable">
                    <thead>
                        <tr>
                            <th style="width:170px;">Waktu</th>
                            <th style="width:120px;">User</th>
                            <th style="width:130px;">Role</th>
                            <th style="width:170px;">Path</th>
                            <th style="width:120px;">Tabel</th>
                            <th style="width:90px;">Record ID</th>
                            <th>Request</th>
                            <?php if ($isEditHistory || $isDeleteHistory): ?>
                                <th style="width:110px;">Before</th>
                            <?php endif; ?>
                            <?php if ($isEditHistory): ?>
                                <th style="width:110px;">After</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <?php
                            $beforeJson = (string) ($row['before_data_json'] ?? '');
                            $afterJson = (string) ($row['after_data_json'] ?? '');
                            ?>
                            <tr>
                                <td><?= esc((string) ($row['happened_at'] ?? '-')); ?></td>
                                <td><?= esc((string) ($row['username'] ?? '-')); ?></td>
                                <td><?= esc((string) ($row['role'] ?? '-')); ?></td>
                                <td><small><?= esc((string) ($row['module_path'] ?? '-')); ?></small></td>
                                <td><?= esc((string) ($row['table_name'] ?? '-')); ?></td>
                                <td><?= esc((string) ($row['record_id'] ?? '-')); ?></td>
                                <td><pre class="mb-0" style="white-space:pre-wrap; max-width:420px;"><?= esc((string) ($row['request_data_json'] ?? '')); ?></pre></td>
                                <?php if ($isEditHistory || $isDeleteHistory): ?>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-info js-open-json-modal" data-title="Before Data" data-json="<?= esc($beforeJson !== '' ? $beforeJson : '-', 'attr'); ?>">Lihat</button>
                                    </td>
                                <?php endif; ?>
                                <?php if ($isEditHistory): ?>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-secondary js-open-json-modal" data-title="After Data" data-json="<?= esc($afterJson !== '' ? $afterJson : '-', 'attr'); ?>">Lihat</button>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($tableReady && ($isEditHistory || $isDeleteHistory)): ?>
<div class="modal fade" id="jsonHistoryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="jsonHistoryModalTitle">Detail Data</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <pre id="jsonHistoryModalBody" class="mb-0" style="white-space: pre-wrap;"></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<?php if ($tableReady && ($isEditHistory || $isDeleteHistory)): ?>
<script>
(() => {
    const modalEl = document.getElementById('jsonHistoryModal');
    const titleEl = document.getElementById('jsonHistoryModalTitle');
    const bodyEl = document.getElementById('jsonHistoryModalBody');

    if (!modalEl || !titleEl || !bodyEl) {
        return;
    }

    const openModal = (title, content) => {
        titleEl.textContent = title || 'Detail Data';
        bodyEl.textContent = content || '-';

        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.modal) {
            window.jQuery('#jsonHistoryModal').modal('show');
        }
    };

    document.addEventListener('click', (event) => {
        const button = event.target.closest('.js-open-json-modal');
        if (!button) {
            return;
        }

        openModal(button.getAttribute('data-title') || 'Detail Data', button.getAttribute('data-json') || '-');
    });
})();
</script>
<?php endif; ?>
<?= $this->endSection(); ?>
