<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($appName); ?> - Maintenance</title>
    <style>
        body {
            margin: 0;
            font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #f4f6f9;
            color: #2f3a45;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            text-align: center;
            max-width: 680px;
            margin: 24px;
            padding: 36px;
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 24px 48px rgba(15, 23, 42, 0.08);
        }
        h1 {
            margin: 0 0 16px;
            font-size: 2.5rem;
        }
        p {
            margin: 0 0 18px;
            line-height: 1.7;
            color: #57606a;
            font-size: 1rem;
        }
        .code {
            display: inline-flex;
            padding: 0.5rem 0.85rem;
            font-family: monospace;
            background: #eef2ff;
            color: #3730a3;
            border-radius: 999px;
            font-size: 0.95rem;
        }
        .footer {
            margin-top: 22px;
            font-size: 0.95rem;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?= esc($appName); ?></h1>
        <p>Maaf, saat ini aplikasi sedang dalam mode maintenance. Silakan cek kembali beberapa saat lagi.</p>
        <div class="code">503 - Service Unavailable</div>
        <div class="footer">Jika ini adalah Anda sebagai administrator, silakan masuk lewat <a href="/masuk">halaman login</a> untuk menonaktifkan mode maintenance.</div>
    </div>
</body>
</html>
