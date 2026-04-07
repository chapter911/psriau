<?= $this->extend('layouts/public'); ?>

<?= $this->section('content'); ?>
<section class="section">
    <div class="container">
        <?php $isInstagramPost = preg_match('~^https://www\.instagram\.com/(p|reel|tv)/[A-Za-z0-9_-]+/?$~', (string) ($article['content'] ?? '')) === 1; ?>
        <a href="<?= site_url('/instagram'); ?>">&larr; Kembali ke Instagram</a>
        <h1><?= esc($article['title']); ?></h1>
        <p class="meta"><?= esc($article['category']); ?> | <?= esc($article['published_at'] ?? '-'); ?></p>
        <?php $articleImage = $article['image_url'] ?: ($globalSetting['default_article_image'] ?? ''); ?>
        <?php if (! $isInstagramPost && ! empty($articleImage)): ?>
            <img src="<?= esc($articleImage); ?>" alt="<?= esc($article['title']); ?>" style="width:100%;max-height:400px;object-fit:cover;border-radius:14px;margin-bottom:1rem;">
        <?php endif; ?>
        <div class="content-wrap">
            <?php if ($isInstagramPost): ?>
                <blockquote class="instagram-media" data-instgrm-permalink="<?= esc($article['content']); ?>" data-instgrm-version="14" style="background:#fff;border:0;border-radius:12px;box-shadow:0 1px 10px rgba(0,0,0,.08);margin:0 auto;min-width:280px;padding:0;width:100%;max-width:540px;"></blockquote>
            <?php else: ?>
                <p><?= nl2br(esc($article['content'])); ?></p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php if ($isInstagramPost): ?>
    <script async defer src="https://www.instagram.com/embed.js"></script>
<?php endif; ?>
<?= $this->endSection(); ?>
