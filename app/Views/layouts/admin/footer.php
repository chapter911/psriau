<footer class="main-footer">
    <strong><?= esc($globalSetting['official_name'] ?? 'Satker PPS Kementerian PU'); ?></strong>
    <?php if (defined('ENVIRONMENT') && ENVIRONMENT === 'development'): ?>
        <span class="badge badge-warning text-dark ml-2" title="Aplikasi berjalan di environment development lokal">
            <i class="fas fa-laptop-code mr-1"></i> DEV MODE • LOKAL
        </span>
    <?php endif; ?>
    <div class="float-right d-none d-sm-inline-block">
        <a href="<?= site_url('/'); ?>">Kembali ke Situs Publik</a>
    </div>
</footer>
