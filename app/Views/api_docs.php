<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PSRiau API Documentation - Swagger UI</title>
    <!-- Swagger UI CSS -->
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        html {
            box-sizing: border-box;
            overflow: -margin-top-webkit-scrollbar;
        }
        *, *:before, *:after {
            box-sizing: inherit;
        }
        body {
            margin: 0;
            background: #fafafa;
            font-family: 'Inter', sans-serif !important;
        }
        /* Customize Topbar */
        .swagger-ui .topbar {
            background-color: #0b2240 !important; /* Premium Dark Blue to match PSRiau theme */
            padding: 12px 0;
            border-bottom: 3px solid #f2a900; /* Gold accent border */
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
        .swagger-ui .topbar a {
            font-family: 'Inter', sans-serif !important;
            font-weight: 700;
            font-size: 1.2rem;
            color: #ffffff;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        .swagger-ui .topbar a span {
            margin-left: 10px;
            color: #f2a900; /* Gold */
        }
        .swagger-ui .info {
            margin: 40px 0 20px 0 !important;
        }
        .swagger-ui .info .title {
            font-family: 'Inter', sans-serif !important;
            font-weight: 700;
            color: #0b2240;
        }
        .swagger-ui .info .title small {
            background-color: #f2a900 !important;
            color: #0b2240 !important;
            font-weight: 600;
            border-radius: 4px;
            padding: 4px 8px;
        }
        .swagger-ui .btn.authorize {
            background-color: transparent;
            color: #10b981 !important;
            border-color: #10b981 !important;
            font-weight: 600;
            border-radius: 6px;
            transition: all 0.2s ease;
        }
        .swagger-ui .btn.authorize:hover {
            background-color: #10b981 !important;
            color: #fff !important;
        }
        .swagger-ui .btn.authorize svg {
            fill: currentColor;
        }
        /* Custom Container styling */
        .swagger-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 40px 20px;
        }
        /* Footer branding */
        .footer-branding {
            text-align: center;
            padding: 20px 0;
            font-size: 0.85rem;
            color: #888;
            border-top: 1px solid #e5e7eb;
            margin-top: 40px;
        }
    </style>
</head>
<body>

    <!-- Header bar -->
    <div class="swagger-ui">
        <div class="topbar">
            <div class="wrapper">
                <div class="topbar-wrapper">
                    <a href="#">
                        <img src="https://riau.pu.go.id/images/logo.png" alt="Logo PU" height="40" onerror="this.style.display='none'">
                        <span>PSRiau API Explorer</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Swagger UI Mount Point -->
    <div id="swagger-ui" class="swagger-container"></div>

    <div class="footer-branding">
        &copy; <?= date('Y'); ?> Satker Pelaksanaan Prasarana Strategis Riau. All rights reserved.
    </div>

    <!-- Swagger UI scripts -->
    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js" charset="UTF-8"></script>
    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-standalone-preset.js" charset="UTF-8"></script>
    <script>
        window.onload = function() {
            // Build a system
            const ui = SwaggerUIBundle({
                url: "<?= base_url('swagger.json'); ?>",
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "BaseLayout",
                docExpansion: "list", // Default expand mode
                defaultModelsExpandDepth: 1,
                persistAuthorization: true
            });

            window.ui = ui;
        };
    </script>
</body>
</html>
