<?= $this->extend('layouts/public'); ?>

<?= $this->section('content'); ?>
<section class="section">
    <div class="container">
        <h1>Daftar Acara</h1>
        <div class="cards">
            <?php foreach ($events as $event): ?>
                <article class="card">
                    <?php $eventImage = $event['image_url'] ?: ($globalSetting['default_event_image'] ?? ''); ?>
                    <?php if (! empty($eventImage)): ?>
                        <img src="<?= esc($eventImage); ?>" alt="<?= esc($event['title']); ?>">
                    <?php endif; ?>
                    <div class="card-body">
                        <h3><?= esc($event['title']); ?></h3>
                        <p class="meta"><?= esc($event['event_date'] ?? '-'); ?> | <?= esc($event['location'] ?? 'Lokasi belum diisi'); ?></p>
                        <p><?= esc($event['summary']); ?></p>
                        <a class="btn-secondary" href="<?= site_url('/acara/' . $event['slug']); ?>">Detail Acara</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?= $this->endSection(); ?>
