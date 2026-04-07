<?= $this->extend('layouts/public'); ?>

<?= $this->section('content'); ?>
<section class="section">
    <div class="container">
        <h1>Instagram</h1>
        <div class="cards">
            <?php foreach ($articles as $article): ?>
                <?php $isInstagramPost = preg_match('~^https://www\.instagram\.com/(p|reel|tv)/[A-Za-z0-9_-]+/?$~', (string) ($article['content'] ?? '')) === 1; ?>
                <article class="card">
                    <?php $articleImage = $article['image_url'] ?: ($globalSetting['default_article_image'] ?? ''); ?>
                    <?php if (! $isInstagramPost && ! empty($articleImage)): ?>
                        <img src="<?= esc($articleImage); ?>" alt="<?= esc($article['title']); ?>">
                    <?php endif; ?>
                    <div class="card-body">
                        <h3><?= esc($article['title']); ?></h3>
                        <p class="meta"><?= esc($article['category']); ?></p>
                        <?php if ($isInstagramPost): ?>
                            <blockquote class="instagram-media" data-instgrm-permalink="<?= esc($article['content']); ?>" data-instgrm-version="14" style="background:#fff;border:0;border-radius:12px;box-shadow:0 1px 10px rgba(0,0,0,.08);margin:0;min-width:100%;padding:0;width:100%;"></blockquote>
                        <?php else: ?>
                            <p><?= esc($article['summary']); ?></p>
                            <a class="btn-secondary" href="<?= site_url('/instagram/' . $article['slug']); ?>">Baca Selengkapnya</a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if (! empty($articles)): ?>
    <script async defer src="https://www.instagram.com/embed.js"></script>
<?php endif; ?>
<?= $this->endSection(); ?>
