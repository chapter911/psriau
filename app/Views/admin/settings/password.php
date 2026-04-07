<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="card card-primary mt-4" style="max-width:500px;margin:auto;">
    <div class="card-header">
        <h3 class="card-title">Update Password</h3>
    </div>
    <form action="<?= site_url('/admin/password/update'); ?>" method="post" autocomplete="off">
        <div class="card-body">
            <?= csrf_field(); ?>
            <?php if (session('error')): ?>
                <div class="alert alert-danger"> <?= esc(session('error')) ?> </div>
            <?php endif; ?>
            <?php if (session('message')): ?>
                <div class="alert alert-success"> <?= esc(session('message')) ?> </div>
            <?php endif; ?>
            <div class="form-group">
                <label for="current_password">Password Lama</label>
                <input type="password" name="current_password" id="current_password" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="new_password">Password Baru</label>
                <input type="password" name="new_password" id="new_password" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password Baru</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
            </div>
        </div>
        <div class="card-footer text-right">
            <button type="submit" class="btn btn-primary"><i class="fas fa-key mr-1"></i> Update Password</button>
        </div>
    </form>
</div>
<?= $this->endSection(); ?>
