<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Akses Ditolak</title>
    <style>
        :root {
            --bg-1: #0f172a;
            --bg-2: #1e293b;
            --card: #ffffff;
            --text: #111827;
            --muted: #6b7280;
            --primary: #0ea5e9;
            --primary-dark: #0284c7;
            --secondary: #e5e7eb;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: radial-gradient(1200px 600px at 10% 10%, #1d4ed8 0%, transparent 60%),
                        radial-gradient(900px 500px at 100% 100%, #0ea5e9 0%, transparent 60%),
                        linear-gradient(140deg, var(--bg-1), var(--bg-2));
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text);
            padding: 24px;
        }

        .card {
            width: 100%;
            max-width: 760px;
            background: var(--card);
            border-radius: 18px;
            box-shadow: 0 20px 60px rgba(2, 6, 23, 0.35);
            overflow: hidden;
        }

        .top {
            padding: 26px 28px;
            background: linear-gradient(90deg, #e0f2fe, #f0f9ff);
            border-bottom: 1px solid #dbeafe;
        }

        .code {
            display: inline-block;
            font-weight: 800;
            letter-spacing: 0.08em;
            color: #075985;
            margin-bottom: 8px;
        }

        h1 {
            margin: 0;
            font-size: 32px;
            line-height: 1.2;
        }

        .content {
            padding: 26px 28px 30px;
        }

        p {
            margin: 0 0 14px;
            font-size: 16px;
            color: #1f2937;
        }

        .hint {
            color: var(--muted);
            font-size: 14px;
            margin-top: 10px;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 22px;
        }

        .btn {
            border: 0;
            border-radius: 10px;
            padding: 10px 16px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform .15s ease, box-shadow .15s ease, background .15s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-primary {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 10px 18px rgba(2, 132, 199, 0.24);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-secondary {
            background: var(--secondary);
            color: #111827;
        }
    </style>
</head>
<body>
    <main class="card" role="main" aria-labelledby="title403">
        <section class="top">
            <span class="code">ERROR 403</span>
            <h1 id="title403">Akses Ditolak</h1>
        </section>
        <section class="content">
            <p>Anda tidak memiliki izin untuk membuka halaman ini.</p>
            <?php if (! empty($from ?? null)): ?>
                <p class="hint">Halaman yang diminta: <strong><?= esc((string) $from); ?></strong></p>
            <?php endif; ?>
            <p class="hint">Hubungi administrator jika akses ini seharusnya tersedia untuk role Anda.</p>

            <div class="actions">
                <a class="btn btn-primary" href="<?= esc((string) ($default_home ?? '/')); ?>">Kembali ke Halaman Utama</a>
                <button type="button" class="btn btn-secondary" onclick="window.history.length > 1 ? window.history.back() : window.location.href='<?= esc((string) ($default_home ?? '/')); ?>';">Kembali ke Halaman Sebelumnya</button>
            </div>
        </section>
    </main>
</body>
</html>
