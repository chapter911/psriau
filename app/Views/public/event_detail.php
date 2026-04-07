<?= $this->extend('layouts/public'); ?>

<?= $this->section('content'); ?>
<section class="section">
    <div class="container">
        <a href="<?= site_url('/acara'); ?>">&larr; Kembali ke daftar acara</a>
        <h1><?= esc($event['title']); ?></h1>
        <p class="meta"><?= esc($event['event_date'] ?? '-'); ?> | <?= esc($event['location'] ?? 'Lokasi belum diisi'); ?></p>
        <?php $eventImage = $event['image_url'] ?: ($globalSetting['default_event_image'] ?? ''); ?>
        <?php if (! empty($eventImage)): ?>
            <img src="<?= esc($eventImage); ?>" alt="<?= esc($event['title']); ?>" style="width:100%;max-height:400px;object-fit:cover;border-radius:14px;margin-bottom:1rem;">
        <?php endif; ?>
        <div class="content-wrap">
            <p><?= nl2br(esc($event['content'])); ?></p>
        </div>
    </div>
</section>
<?= $this->endSection(); ?>
