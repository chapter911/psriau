<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OAuth Success</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            text-align: center;
        }
        .success-box {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 30px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .success-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        h1 {
            color: #155724;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #28a745;
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
    <div class="success-box">
        <div class="success-icon">✅</div>
        <h1>Authentication Successful!</h1>
        <p><?= esc($message) ?></p>
        <p>You can now upload files to Google Drive.</p>
    </div>

    <div style="margin-top: 30px;">
        <a href="<?= base_url() ?>" class="btn">Go to Homepage</a>
        <a href="<?= base_url('oauth/test') ?>" class="btn btn-secondary">Test Upload</a>
    </div>
</body>
</html>
