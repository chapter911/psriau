<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php $appName = trim((string) ($appSetting['app_name'] ?? 'PLN EPM-Digi')); ?>
    <title><?= esc('Masuk Admin | ' . $appName); ?></title>
    <?php if (! empty($globalSetting['logo_url'] ?? '')): ?>
        <link rel="icon" type="image/png" href="<?= esc($globalSetting['logo_url']); ?>">
        <link rel="apple-touch-icon" href="<?= esc($globalSetting['logo_url']); ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= base_url('assets/adminlte/plugins/fontawesome-free/css/all.min.css'); ?>">
    <style>
        @font-face {
            font-family: "Barlow";
            font-style: normal;
            font-weight: 400;
            font-display: swap;
            src: url("<?= base_url('assets/fonts/Barlow-Regular.ttf'); ?>") format("truetype");
        }

        @font-face {
            font-family: "Barlow";
            font-style: normal;
            font-weight: 500;
            font-display: swap;
            src: url("<?= base_url('assets/fonts/Barlow-Medium.ttf'); ?>") format("truetype");
        }

        @font-face {
            font-family: "Barlow";
            font-style: normal;
            font-weight: 700;
            font-display: swap;
            src: url("<?= base_url('assets/fonts/Barlow-Bold.ttf'); ?>") format("truetype");
        }

        @font-face {
            font-family: "Barlow";
            font-style: normal;
            font-weight: 800;
            font-display: swap;
            src: url("<?= base_url('assets/fonts/Barlow-ExtraBold.ttf'); ?>") format("truetype");
        }

        @font-face {
            font-family: "Fraunces";
            font-style: normal;
            font-weight: 100 900;
            font-display: swap;
            src: url("<?= base_url('assets/fonts/Fraunces-Variable.ttf'); ?>") format("truetype");
        }

        :root {
            --app-primary: <?= esc($appSetting['primary_color'] ?? '#0A66C2'); ?>;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Barlow", sans-serif;
            background: #f0f4f8;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1rem;
            position: relative;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background-image: <?= ! empty($appSetting['login_background_url'] ?? '') ? "url('" . esc($appSetting['login_background_url']) . "')" : 'none'; ?>;
            background-size: cover;
            background-position: center;
            opacity: <?= ! empty($appSetting['login_background_url'] ?? '') ? '0.22' : '0'; ?>;
            z-index: -2;
        }

        body::after {
            content: "";
            position: fixed;
            inset: 0;
            background: radial-gradient(circle at 15% 15%, rgba(10, 102, 194, 0.12), transparent 45%), radial-gradient(circle at 85% 10%, rgba(10, 102, 194, 0.08), transparent 38%);
            z-index: -1;
        }

        .login-wrapper {
            width: 100%;
            max-width: 820px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 16px 42px rgba(0, 0, 0, 0.12);
            overflow: hidden;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        .login-info {
            background: linear-gradient(135deg, var(--app-primary) 0%, #1f546f 100%);
            color: #fff;
            padding: 2.4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 1rem;
        }

        .login-info-logo {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            margin-bottom: 0.5rem;
        }

        .login-info-logo img {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.2);
            padding: 6px;
            object-fit: contain;
        }

        .login-info-logo-fallback {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.2rem;
        }

        .login-info h1 {
            font-family: "Fraunces", serif;
            font-size: 1.45rem;
            font-weight: 700;
            line-height: 1.3;
        }

        .login-info p {
            font-size: 0.88rem;
            line-height: 1.5;
            opacity: 0.95;
        }

        .login-form-wrapper {
            padding: 2.4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-form-header {
            margin-bottom: 1.4rem;
        }

        .login-form-header h2 {
            font-size: 1.25rem;
            color: var(--app-primary);
            margin-bottom: 0.3rem;
        }

        .login-form-header p {
            color: #657780;
            font-size: 0.82rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.45rem;
            font-size: 0.84rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.7rem 0.85rem;
            border: 2px solid #e0e6ed;
            border-radius: 8px;
            font-size: 0.88rem;
            font-family: inherit;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--app-primary);
            background: #f8fafc;
            box-shadow: 0 0 0 3px rgba(10, 102, 194, 0.1);
        }

        .alert {
            padding: 0.8rem 0.9rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.84rem;
            border-left: 4px solid;
        }

        .alert-success {
            background: #dff6ec;
            color: #0c5b36;
            border-color: #0c5b36;
        }

        .alert-danger {
            background: #ffe2dc;
            color: #7a1f15;
            border-color: #7a1f15;
        }

        .btn-login {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, var(--app-primary) 0%, #1f546f 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.88rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(10, 102, 194, 0.3);
            margin-top: 0.5rem;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(10, 102, 194, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .login-footer {
            margin-top: 1.2rem;
            padding-top: 1rem;
            border-top: 1px solid #e0e6ed;
            text-align: center;
        }

        .login-footer p {
            font-size: 0.76rem;
            color: #657780;
            margin-bottom: 0.5rem;
        }

        .login-footer a {
            color: var(--app-primary);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .login-footer a:hover {
            color: #1f546f;
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .login-wrapper {
                grid-template-columns: 1fr;
            }

            .login-info {
                padding: 1.8rem;
                min-height: none;
                order: 2;
            }

            .login-form-wrapper {
                padding: 1.8rem;
                order: 1;
            }

            .login-info h1 {
                font-size: 1.2rem;
            }

            .login-info p {
                font-size: 0.82rem;
            }

            .login-form-header h2 {
                font-size: 1.08rem;
            }
        }
    </style>
</head>
<body>
<div class="login-wrapper">
    <div class="login-info">
        <div>
            <div class="login-info-logo">
                <?php $appLogo = $globalSetting['logo_url'] ?? ''; ?>
                <?php $appLogo = $appSetting['app_logo_url'] ?? $appLogo; ?>
                <?php if (! empty($appLogo)): ?>
                    <img src="<?= esc($appLogo); ?>" alt="Logo">
                <?php else: ?>
                    <div class="login-info-logo-fallback">SP</div>
                <?php endif; ?>
            </div>
        </div>
        <h1><?= esc($appSetting['app_name'] ?? ($globalSetting['official_name'] ?? 'Satker PPS Kementerian PU')); ?></h1>
        <p><?= esc($globalSetting['hero_subtitle'] ?? 'Silakan login untuk mengakses laporan, analisis, dan monitoring berbasis unit secara terpusat.'); ?></p>
    </div>

    <div class="login-form-wrapper">
        <div class="login-form-header">
            <h2>Masuk ke Sistem</h2>
            <p>Gunakan akun yang telah terdaftar (username / nip)</p>
        </div>

        <?php if (session()->getFlashdata('message')): ?>
            <div class="alert alert-success">✓ <?= esc(session()->getFlashdata('message')); ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger">✕ <?= esc(session()->getFlashdata('error')); ?></div>
        <?php endif; ?>

        <form action="<?= site_url('/masuk'); ?>" method="post">
            <?= csrf_field(); ?>
            <div class="form-group">
                <label for="username">Username / NIP</label>
                <input type="text" id="username" name="username" placeholder="Masukkan username / nip" value="<?= old('username'); ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Masukkan password Anda" required>
            </div>

            <button type="submit" class="btn-login">Login</button>
        </form>

        <div class="login-footer">
            <p style="margin-top: 1rem;">
                <a href="<?= site_url('/'); ?>">← Kembali ke website</a>
            </p>
        </div>
    </div>
</div>

</body>
</html>
