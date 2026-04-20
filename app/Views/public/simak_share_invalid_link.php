<!DOCTYPE html>
<html lang="id">
<head>
    <?php
        $iconUrl = trim((string) ($globalSetting['logo_url'] ?? ''));
        if ($iconUrl === '') {
            $iconUrl = trim((string) ($appSetting['app_logo_url'] ?? ''));
        }
        $brandName = trim((string) ($globalSetting['official_name'] ?? 'Portal SIMAK'));
        $brandFallback = strtoupper(substr($brandName !== '' ? $brandName : 'SM', 0, 2));
    ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc((string) ($title ?? 'Tautan Share Tidak Valid')); ?></title>
    <?php if ($iconUrl !== ''): ?>
        <link rel="icon" type="image/png" href="<?= esc($iconUrl); ?>">
        <link rel="apple-touch-icon" href="<?= esc($iconUrl); ?>">
    <?php endif; ?>
    <style>
        :root {
            --bg: #f3f7ff;
            --card: #ffffff;
            --text: #1f2937;
            --muted: #64748b;
            --accent: #0f766e;
            --accent-soft: #ecfeff;
            --border: #dbe5f3;
            --danger: #b91c1c;
            --btn-primary: #1d4ed8;
            --btn-primary-hover: #1e40af;
            --btn-secondary: #e5e7eb;
            --btn-secondary-hover: #d1d5db;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(circle at 12% 18%, #dbeafe 0%, transparent 35%),
                radial-gradient(circle at 88% 20%, #ccfbf1 0%, transparent 32%),
                linear-gradient(180deg, var(--bg) 0%, #f8fbff 100%);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .panel {
            width: min(760px, 100%);
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 18px;
            box-shadow: 0 22px 52px rgba(15, 23, 42, 0.10);
            overflow: hidden;
        }

        .panel-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 22px;
            border-bottom: 1px solid var(--border);
            background: #ffffff;
        }

        .logo {
            width: 38px;
            height: 38px;
            border-radius: 9px;
            object-fit: contain;
            background: #fff;
            border: 1px solid #cbd5e1;
            padding: 3px;
        }

        .logo-fallback {
            width: 38px;
            height: 38px;
            border-radius: 9px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.78rem;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(145deg, #0f766e, #134e4a);
            border: 1px solid #115e59;
        }

        .brand {
            font-size: 0.95rem;
            font-weight: 600;
            color: #475569;
        }

        .panel-body {
            padding: 28px 24px 24px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--accent-soft);
            color: var(--accent);
            border: 1px solid #a5f3fc;
            padding: 7px 11px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .badge-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: var(--accent);
        }

        h1 {
            margin: 14px 0 10px;
            font-size: clamp(1.25rem, 2.3vw, 1.7rem);
            line-height: 1.3;
            color: #0f172a;
        }

        .message {
            margin: 0;
            color: var(--muted);
            line-height: 1.65;
            font-size: 0.98rem;
        }

        .hint {
            margin-top: 16px;
            border: 1px dashed #fecaca;
            background: #fef2f2;
            color: var(--danger);
            border-radius: 10px;
            padding: 11px 13px;
            font-size: 0.9rem;
        }

        .meta {
            margin-top: 14px;
            border: 1px dashed #cbd5e1;
            background: #f8fafc;
            color: #475569;
            border-radius: 10px;
            padding: 11px 13px;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .actions {
            margin-top: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .btn {
            text-decoration: none;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.92rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s ease;
        }

        .btn-primary {
            background: var(--btn-primary);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--btn-primary-hover);
        }

        .btn-secondary {
            background: var(--btn-secondary);
            color: #374151;
        }

        .btn-secondary:hover {
            background: var(--btn-secondary-hover);
        }

        @media (max-width: 540px) {
            .panel-body {
                padding: 22px 18px;
            }

            .panel-header {
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
    <article class="panel" role="alert" aria-live="polite">
        <header class="panel-header">
            <?php if ($iconUrl !== ''): ?>
                <img src="<?= esc($iconUrl); ?>" alt="Logo" class="logo">
            <?php else: ?>
                <span class="logo-fallback"><?= esc($brandFallback); ?></span>
            <?php endif; ?>
            <div class="brand"><?= esc($brandName); ?></div>
        </header>

        <section class="panel-body">
            <span class="badge"><span class="badge-dot"></span> Tautan Tidak Ditemukan</span>
            <h1>Tautan share SIMAK tidak dapat dibuka</h1>
            <p class="message"><?= esc((string) ($message ?? 'Tautan share SIMAK tidak valid.')); ?></p>

            <div class="hint">
                Pastikan tautan yang dibuka benar, belum kedaluwarsa, dan tidak terpotong saat disalin.
            </div>

            <?php if (! empty($item['nama_paket'] ?? '') || ! empty($item['nomor_kontrak'] ?? '') || trim((string) ($token ?? '')) !== ''): ?>
                <div class="meta">
                    <?php if (! empty($item['nama_paket'] ?? '')): ?>
                        <div>Nama Paket: <strong><?= esc((string) $item['nama_paket']); ?></strong></div>
                    <?php endif; ?>
                    <?php if (! empty($item['nomor_kontrak'] ?? '')): ?>
                        <div>Nomor Kontrak: <strong><?= esc((string) $item['nomor_kontrak']); ?></strong></div>
                    <?php endif; ?>
                    <?php if (trim((string) ($token ?? '')) !== ''): ?>
                        <div>Token: <strong><?= esc(substr((string) $token, 0, 12) . '...'); ?></strong></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="actions">
                <a href="<?= site_url('/'); ?>" class="btn btn-primary">Ke Beranda</a>
                <a href="<?= site_url('masuk'); ?>" class="btn btn-secondary">Login</a>
            </div>
        </section>
    </article>
</body>
</html>
