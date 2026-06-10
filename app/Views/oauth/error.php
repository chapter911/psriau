<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OAuth Error</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            text-align: center;
        }
        .error-box {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 30px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .error-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        h1 {
            color: #721c24;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
        }
        .btn-secondary {
            background: #6c757d;
        }
    </style>
</head>
<body>
    <div class="error-box">
        <div class="error-icon">❌</div>
        <h1>Authentication Failed</h1>
        <p><?= esc($message) ?></p>
    </div>

    <div style="margin-top: 30px;">
        <a href="<?= base_url('oauth/connect') ?>" class="btn">Try Again</a>
        <a href="<?= base_url() ?>" class="btn btn-secondary">Go to Homepage</a>
    </div>
</body>
</html>
