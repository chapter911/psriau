<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class Media extends BaseController
{
    public function index(): ResponseInterface
    {
        $relativePath = trim((string) $this->request->getGet('path'));
        if ($relativePath === '') {
            return $this->response->setStatusCode(404);
        }

        return $this->serveRelativePath($relativePath);
    }

    public function assets(string $path = ''): ResponseInterface
    {
        if ($path === '') {
            return $this->response->setStatusCode(404);
        }

        return $this->serveRelativePath('assets/' . $path);
    }

    public function uploads(string $path = ''): ResponseInterface
    {
        if ($path === '') {
            return $this->response->setStatusCode(404);
        }

        return $this->serveRelativePath('uploads/' . $path);
    }

    public function geojson(string $path = ''): ResponseInterface
    {
        if ($path === '') {
            return $this->response->setStatusCode(404);
        }

        return $this->serveRelativePath('geojson/' . $path);
    }

    private function serveRelativePath(string $relativePath): ResponseInterface
    {
        $relativePath = ltrim(rawurldecode($relativePath), '/');
        $rootPath = realpath(FCPATH);
        $filePath = realpath(FCPATH . $relativePath);

        if ($rootPath === false || $filePath === false || strncmp($filePath, $rootPath, strlen($rootPath)) !== 0 || ! is_file($filePath)) {
            return $this->response->setStatusCode(404);
        }

        $mimeType = @mime_content_type($filePath);
        if (! is_string($mimeType) || $mimeType === '') {
            $mimeType = 'application/octet-stream';
        }

        $response = $this->response;
        $response->setHeader('Content-Type', $mimeType);
        $response->setHeader('Content-Length', (string) filesize($filePath));
        $response->setHeader('Cache-Control', 'public, max-age=31536000');

        if (strtoupper($this->request->getMethod()) !== 'HEAD') {
            $response->setBody((string) file_get_contents($filePath));
        }

        return $response;
    }
}
