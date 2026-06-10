<?php

namespace App\Filters;

use App\Models\AppSettingModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class MaintenanceFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $path = trim((string) $request->getUri()->getPath(), '/');

        if ($path === '' || $path === 'masuk' || $path === 'keluar' || str_starts_with($path, 'admin')) {
            return null;
        }

        try {
            $setting = (new AppSettingModel())
                ->select('maintenance_mode, app_name')
                ->first();
        } catch (\Throwable $e) {
            $setting = [];
        }

        $maintenanceMode = isset($setting['maintenance_mode']) && (int) $setting['maintenance_mode'] === 1;
        if (! $maintenanceMode) {
            return null;
        }

        $appName = trim((string) ($setting['app_name'] ?? 'Aplikasi'));
        $body = view('maintenance', [
            'appName' => $appName,
        ]);

        return service('response')
            ->setStatusCode(503)
            ->setBody($body);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
