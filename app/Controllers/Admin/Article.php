<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ArticleModel;

class Article extends BaseController
{
    public function index(): string
    {
        $articles = (new ArticleModel())->orderBy('published_at', 'DESC')->findAll();

        return view('admin/articles/index', ['articles' => $articles]);
    }

    public function create()
    {
        if ($this->request->getMethod() === 'POST') {
            return $this->saveData();
        }

        return view('admin/articles/form', [
            'article'   => null,
            'instagramEmbed' => '',
            'pageTitle' => 'Tambah Berita/Artikel',
            'actionUrl' => '/admin/berita/tambah',
        ]);
    }

    public function edit(int $id)
    {
        $model   = new ArticleModel();
        $article = $model->find($id);

        if (! $article) {
            return redirect()->to('/admin/berita')->with('error', 'Artikel tidak ditemukan.');
        }

        if ($this->request->getMethod() === 'POST') {
            return $this->saveData($id, $article);
        }

        $instagramEmbed = $this->extractInstagramPostUrl((string) ($article['content'] ?? ''));

        return view('admin/articles/form', [
            'article'   => $article,
            'instagramEmbed' => $instagramEmbed,
            'pageTitle' => 'Ubah Berita/Artikel',
            'actionUrl' => '/admin/berita/' . $id . '/ubah',
        ]);
    }

    public function delete(int $id)
    {
        $model = new ArticleModel();
        $article = $model->find($id);

        if ($article) {
            $this->deleteLocalImage($article['image_url'] ?? null);
            $model->delete($id);
        }

        return redirect()->to('/admin/berita')->with('message', 'Artikel berhasil dihapus.');
    }

    private function saveData(?int $id = null, ?array $existing = null)
    {
        $rules = [
            'instagram_embed' => 'required|min_length[10]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Data artikel belum valid.');
        }

        $model = new ArticleModel();
        $instagramUrl = $this->extractInstagramPostUrl((string) $this->request->getPost('instagram_embed'));
        if ($instagramUrl === '') {
            return redirect()->back()->withInput()->with('error', 'Format embed Instagram tidak valid. Gunakan URL post Instagram atau kode embed resmi.');
        }

        $shortcode = $this->extractInstagramShortcode($instagramUrl);
        $title = 'Instagram Post ' . strtoupper($shortcode ?: date('YmdHis'));
        $slug  = $this->buildUniqueSlug($title, $id);

        $isPublished = (int) ($this->request->getPost('is_published') ? 1 : 0);

        $payload = [
            'title'        => $title,
            'slug'         => $slug,
            'summary'      => $instagramUrl,
            'content'      => $instagramUrl,
            'category'     => 'Instagram',
            'image_url'    => null,
            'is_published' => $isPublished,
            'published_at' => $isPublished ? date('Y-m-d H:i:s') : null,
        ];

        if ($id === null) {
            $model->insert($payload);
            return redirect()->to('/admin/berita')->with('message', 'Artikel berhasil ditambahkan.');
        }

        $model->update($id, $payload);

        return redirect()->to('/admin/berita')->with('message', 'Artikel berhasil diperbarui.');
    }

    private function extractInstagramShortcode(string $url): string
    {
        if (preg_match('~^https://www\.instagram\.com/(p|reel|tv)/([A-Za-z0-9_-]+)/$~', $url, $matches)) {
            return $matches[2];
        }

        return '';
    }

    private function extractInstagramPostUrl(string $value): string
    {
        $raw = html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8');
        if (trim($raw) === '') {
            return '';
        }

        preg_match_all('~https?://[^\s"\'<>]+~i', $raw, $matches);
        $candidates = $matches[0] ?? [];

        if ($candidates === []) {
            $textOnly = trim(strip_tags($raw));
            if ($textOnly !== '') {
                $candidates[] = $textOnly;
            }
        }

        foreach ($candidates as $candidate) {
            $candidate = trim($candidate, " \t\n\r\0\x0B\"'<>(),;");
            if ($candidate === '' || ! filter_var($candidate, FILTER_VALIDATE_URL)) {
                continue;
            }

            $parts = parse_url($candidate);
            if (! is_array($parts) || empty($parts['host']) || empty($parts['path'])) {
                continue;
            }

            $host = strtolower($parts['host']);
            if (! in_array($host, ['instagram.com', 'www.instagram.com', 'm.instagram.com'], true)) {
                continue;
            }

            if (! preg_match('~^/(p|reel|tv)/([A-Za-z0-9_-]+)(?:/embed)?/?$~', $parts['path'], $pathMatches)) {
                continue;
            }

            return 'https://www.instagram.com/' . $pathMatches[1] . '/' . $pathMatches[2] . '/';
        }

        return '';
    }

    private function buildUniqueSlug(string $title, ?int $id = null): string
    {
        $model    = new ArticleModel();
        $baseSlug = url_title($title, '-', true);
        $slug     = $baseSlug;
        $counter  = 1;

        while (true) {
            $existing = $model->where('slug', $slug)->first();
            if (! $existing || ($id !== null && (int) $existing['id'] === $id)) {
                return $slug;
            }
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
    }

    private function uploadImage(string $fieldName, string $directory): ?string
    {
        $file = $this->request->getFile($fieldName);

        if (! $file || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if (! $file->isValid() || $file->hasMoved()) {
            return null;
        }

        $targetDir = FCPATH . 'uploads/' . $directory;
        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $newName = $file->getRandomName();
        $file->move($targetDir, $newName);

        return '/uploads/' . $directory . '/' . $newName;
    }

    private function deleteLocalImage(?string $path): void
    {
        if (empty($path) || strpos($path, '/uploads/') !== 0) {
            return;
        }

        $filePath = FCPATH . ltrim($path, '/');
        if (is_file($filePath)) {
            unlink($filePath);
        }
    }
}
