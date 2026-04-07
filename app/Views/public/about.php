<?= $this->extend('layouts/public'); ?>

<?= $this->section('content'); ?>
<section class="section">
    <div class="container">
        <h1>Tentang <?= esc($globalSetting['official_name'] ?? 'Satker PPS Kementerian PU'); ?></h1>
        <div class="content-wrap">
            <p><?= nl2br(esc($aboutIntro)); ?></p>
            <p>
                Kami berkomitmen menghasilkan perencanaan prasarana strategis yang terukur, transparan,
                dan berorientasi pada manfaat jangka panjang untuk masyarakat.
            </p>
            <p>
                Fokus utama kami meliputi sinkronisasi program pusat-daerah, penyiapan dokumen perencanaan,
                serta penguatan kolaborasi lintas sektor.
            </p>
        </div>
    </div>
</section>
<?= $this->endSection(); ?>
