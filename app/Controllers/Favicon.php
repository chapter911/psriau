<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\AppSettingModel;
use App\Models\HomeSettingModel;

class Favicon extends Controller
{
    public function index()
    {
        $logoUrl = null;

        // 1. Try to fetch from AppSettingModel first
        try {
            $appSetting = (new AppSettingModel())->first();
            if (is_array($appSetting) && !empty($appSetting['app_logo_url'])) {
                $logoUrl = $appSetting['app_logo_url'];
            }
        } catch (\Throwable $e) {
            // Ignore database/table errors
        }

        // 2. Try to fetch from HomeSettingModel if not found in AppSettings
        if (empty($logoUrl)) {
            try {
                $homeSetting = (new HomeSettingModel())->first();
                if (is_array($homeSetting) && !empty($homeSetting['logo_url'])) {
                    $logoUrl = $homeSetting['logo_url'];
                }
            } catch (\Throwable $e) {
                // Ignore database/table errors
            }
        }

        // 3. Resolve the file path
        $filePath = null;
        if (!empty($logoUrl)) {
            $cleanedPath = FCPATH . ltrim((string) $logoUrl, '/');
            if (is_file($cleanedPath)) {
                $filePath = $cleanedPath;
            }
        }

        // 4. Fallback to default favicon if setting logo file is not found
        if (empty($filePath)) {
            $defaultFavicon = FCPATH . 'favicon_default.ico';
            if (is_file($defaultFavicon)) {
                $filePath = $defaultFavicon;
            }
        }

        // If even the default is not found, return 404
        if (empty($filePath) || !is_file($filePath)) {
            return $this->response->setStatusCode(404)->setBody('Favicon not found');
        }

        // 5. Detect mime type
        $mime = $this->getMimeType($filePath);

        // 6. Serve the file
        $content = file_get_contents($filePath);
        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Cache-Control', 'public, max-age=86400') // Cache for 1 day
            ->setBody($content);
    }

    private function getMimeType(string $filePath): string
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        return match ($ext) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            default => 'image/x-icon',
        };
    }
}
