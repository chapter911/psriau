<?php

namespace App\Controllers;

use App\Models\ArticleModel;

class Articles extends BaseController
{
    public function index(): string
    {
        $articles = (new ArticleModel())
            ->where('is_published', 1)
            ->orderBy('published_at', 'DESC')
            ->findAll();

        return view('public/articles', ['articles' => $articles]);
    }

    public function show(string $slug): string
    {
        $article = (new ArticleModel())
            ->where('slug', $slug)
            ->where('is_published', 1)
            ->first();

        if (! $article) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Post Instagram tidak ditemukan.');
        }

        return view('public/article_detail', ['article' => $article]);
    }
}
