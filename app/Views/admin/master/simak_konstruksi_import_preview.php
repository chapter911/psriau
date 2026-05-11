<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Preview Import Master SIMAK Konstruksi</h3>
    </div>
    <div class="card-body">
        <p class="text-muted">Periksa perubahan sebelum menekan <strong>Apply</strong>. Baris yang cocok (existing) akan di-update, yang tidak cocok akan ditambahkan.</p>

        <form method="post" action="<?= site_url('/admin/master/simak/konstruksi/import/apply'); ?>">
            <?= csrf_field(); ?>
            <input type="hidden" name="confirm" value="1">
            <?php $hasConflicts = ! empty($conflicts ?? []); ?>
            <?php $dups = $duplicates ?? ['external_id'=>[], 'id'=>[]]; ?>
            <?php if ($hasConflicts || !empty($dups['external_id']) || !empty($dups['id'])): ?>
                <div class="alert alert-warning">
                    Terdapat masalah pada file import:
                    <?php if ($hasConflicts): ?>
                        <div>- Konflik pada baris: <?= implode(', ', array_map(function($c){ return $c['row_number']; }, $conflicts)); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($dups['external_id'])): ?>
                        <div>- Duplikat external_id: <?= implode(', ', array_keys($dups['external_id'])); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($dups['id'])): ?>
                        <div>- Duplikat id: <?= implode(', ', array_keys($dups['id'])); ?></div>
                    <?php endif; ?>
                    <div class="mt-2">Centang <strong>Force apply</strong> untuk melanjutkan walau ada konflik/duplikat.</div>
                </div>
            <?php endif; ?>

            <div style="overflow:auto; max-height:520px;">
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Mode</th>
                            <th>id</th>
                            <th>external_id</th>
                            <th>parent_id</th>
                            <th>display_no</th>
                            <th>uraian</th>
                            <th>row_kind</th>
                            <th>has_question</th>
                            <th>ordering</th>
                            <th>is_active</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($preview ?? []) as $i => $item):
                            $row = $item['row'] ?? [];
                            $existing = $item['existing'] ?? null;
                        ?>
                        <tr>
                            <td><?= $i + 1; ?></td>
                            <td><?= $existing ? 'Update' : 'Create'; ?></td>
                            <td><?= esc($row['id'] ?? ($existing['id'] ?? '')); ?></td>
                            <td><?= esc($row['external_id'] ?? ($existing['external_id'] ?? '')); ?></td>
                            <td><?= esc($row['parent_id'] ?? ($existing['parent_id'] ?? '')); ?></td>
                            <td><?= esc($row['display_no'] ?? ($existing['display_no'] ?? '')); ?></td>
                            <td><?= esc($row['uraian'] ?? ($existing['uraian'] ?? '')); ?></td>
                            <td><?= esc($row['row_kind'] ?? ($existing['row_kind'] ?? '')); ?></td>
                            <td><?= esc($row['has_question'] ?? ($existing['has_question'] ?? '')); ?></td>
                            <td><?= esc($row['ordering'] ?? ($existing['ordering'] ?? '')); ?></td>
                            <td><?= esc($row['is_active'] ?? ($existing['is_active'] ?? '')); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-flex justify-content-between align-items-center">
                <div>
                    <label class="mr-2"><input type="checkbox" name="force" value="1"> Force apply</label>
                </div>
                <div>
                    <a href="<?= site_url('/admin/master/simak/konstruksi'); ?>" class="btn btn-secondary mr-2">Batal</a>
                    <button type="submit" class="btn btn-primary">Apply import</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection(); ?>
