<!DOCTYPE html>
<html lang="id">
<head>
    <?php
        $iconUrl = trim((string) ($globalSetting['logo_url'] ?? ''));
        if ($iconUrl === '') {
            $iconUrl = trim((string) ($appSetting['app_logo_url'] ?? ''));
        }
        $brandName = trim((string) ($globalSetting['official_name'] ?? 'Portal SIMAK'));
        $brandFallback = strtoupper(substr($brandName !== '' ? $brandName : 'PS', 0, 2));
    ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc((string) ($title ?? 'Dokumen Tidak Ditemukan')); ?></title>
    <?php if ($iconUrl !== ''): ?>
        <link rel="icon" type="image/png" href="<?= esc($iconUrl); ?>">
        <link rel="apple-touch-icon" href="<?= esc($iconUrl); ?>">
    <?php endif; ?>
    <style>
        :root {
            --bg1: #edf4ff;
            --bg2: #f7fbff;
            --card: #ffffff;
            --text: #1f2937;
            --muted: #6b7280;
            --accent: #d9480f;
            --accent-soft: #fff4ed;
            --border: #e5e7eb;
            --btn: #1d4ed8;
            --btn-hover: #1e40af;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text);
            background: radial-gradient(circle at top right, #dbeafe 0%, transparent 45%), linear-gradient(180deg, var(--bg1) 0%, var(--bg2) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .card {
            width: min(760px, 100%);
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 20px 48px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        .header {
            padding: 18px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 12px;
            background: #fff;
        }

        .logo {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            object-fit: contain;
            background: #fff;
            border: 1px solid #dbe4ef;
            padding: 3px;
        }

        .logo-fallback {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.78rem;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(145deg, #1f6c71, #0f3f43);
            border: 1px solid #0f3f43;
        }

        .brand {
            font-size: 0.95rem;
            color: #4b5563;
            font-weight: 600;
        }

        .content {
            padding: 28px 24px 26px;
        }

        .badge {
            display: inline-block;
            background: var(--accent-soft);
            color: var(--accent);
            border: 1px solid #fed7aa;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.4px;
            padding: 6px 10px;
            border-radius: 999px;
            text-transform: uppercase;
        }

        h1 {
            margin: 14px 0 10px;
            font-size: clamp(1.25rem, 2.2vw, 1.6rem);
            line-height: 1.3;
        }

        .message {
            margin: 0;
            color: var(--muted);
            font-size: 0.98rem;
            line-height: 1.6;
        }

        .meta {
            margin-top: 18px;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px dashed #cbd5e1;
            background: #f8fafc;
            color: #475569;
            font-size: 0.9rem;
        }

        .actions {
            margin-top: 22px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .btn {
            appearance: none;
            border: 0;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.92rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s ease;
        }

        .btn-primary {
            background: var(--btn);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--btn-hover);
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #374151;
        }

        .btn-secondary:hover {
            background: #d1d5db;
        }

        @media (max-width: 520px) {
            .content {
                padding: 22px 18px;
            }

            .header {
                padding: 14px 18px;
            }

            .actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <article class="card" role="alert" aria-live="polite">
        <header class="header">
            <?php if ($iconUrl !== ''): ?>
                <img src="<?= esc($iconUrl); ?>" alt="Logo" class="logo">
            <?php else: ?>
                <span class="logo-fallback"><?= esc($brandFallback); ?></span>
            <?php endif; ?>
            <div class="brand"><?= esc($brandName); ?></div>
        </header>

        <section class="content">
            <span class="badge">Dokumen Tidak Tersedia</span>
            <h1>File yang Anda buka tidak dapat ditemukan</h1>
            <p class="message"><?= esc((string) ($message ?? 'Dokumen tidak ditemukan.')); ?></p>

            <?php if (! empty($item['nama_paket'] ?? '') || ! empty($item['nomor_kontrak'] ?? '')): ?>
                <div class="meta">
                    <?php if (! empty($item['nama_paket'] ?? '')): ?>
                        <div>Nama Paket: <strong><?= esc((string) $item['nama_paket']); ?></strong></div>
                    <?php endif; ?>
                    <?php if (! empty($item['nomor_kontrak'] ?? '')): ?>
                        <div>Nomor Kontrak: <strong><?= esc((string) $item['nomor_kontrak']); ?></strong></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="actions">
                <a href="<?= site_url('simak/share/' . (string) ($token ?? '')); ?>" class="btn btn-primary">Kembali ke Halaman Share</a>
                <a href="<?= site_url('/'); ?>" class="btn btn-secondary">Ke Beranda</a>
            </div>
        </section>
    </article>
</body>
</html>
