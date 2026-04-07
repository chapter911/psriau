<?php

namespace App\Controllers;

use App\Models\ArticleModel;
use App\Models\EventModel;
use App\Models\HomeSettingModel;
use App\Models\HomeSlideModel;

class Home extends BaseController
{
    public function forbidden()
    {
        $from = trim((string) $this->request->getGet('from'));
        $from = trim($from, '/');

        $defaultHome = '/';
        if ($from !== '' && strpos($from, 'admin') === 0) {
            $defaultHome = '/admin';
        }

        return $this->response
            ->setStatusCode(403)
            ->setBody(view('public/forbidden', [
                'from' => $from !== '' ? '/' . $from : null,
                'default_home' => $defaultHome,
            ]));
    }

    public function index(): string
    {
        $settingModel = new HomeSettingModel();
        $slideModel   = new HomeSlideModel();
        $eventModel   = new EventModel();
        $articleModel = new ArticleModel();

        $setting = $settingModel->first() ?? [
            'hero_title'    => 'Satker PPS Kementerian PU',
            'hero_subtitle' => 'Mewujudkan infrastruktur yang terencana untuk Indonesia yang tangguh.',
            'about_intro'   => 'Unit kerja perencanaan prasarana strategis Kementerian Pekerjaan Umum.',
            'instagram_profile_url' => 'https://www.instagram.com/pu_prasaranastrategis_riau/',
            'default_event_image' => '',
            'default_article_image' => '',
        ];

        $instagramPostUrls = [];
        $instagramSourceArticles = $articleModel
            ->where('is_published', 1)
            ->orderBy('published_at', 'DESC')
            ->findAll(12);

        foreach ($instagramSourceArticles as $instagramSourceArticle) {
            $normalizedUrl = $this->normalizeInstagramPostUrl((string) ($instagramSourceArticle['content'] ?? ''));
            if ($normalizedUrl !== '') {
                $instagramPostUrls[] = $normalizedUrl;
            }
        }

        $instagramPostUrls = array_slice(array_values(array_unique($instagramPostUrls)), 0, 6);

        $slides = $slideModel
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->findAll();

        $events = $eventModel
            ->where('is_published', 1)
            ->orderBy('event_date', 'DESC')
            ->findAll(3);

        return view('public/home', [
            'setting'  => $setting,
            'slides'   => $slides,
            'events'   => $events,
            'instagramPostUrls' => $instagramPostUrls,
        ]);
    }

    private function normalizeInstagramPostUrl(string $value): string
    {
        $raw = trim(html_entity_decode(strip_tags($value), ENT_QUOTES, 'UTF-8'));
        if ($raw === '') {
            return '';
        }

        preg_match_all('~https?://[^\s"\'<>]+~i', $raw, $matches);
        $candidates = $matches[0] ?? [$raw];

        foreach ($candidates as $candidate) {
            $candidate = trim($candidate);
            if ($candidate === '' || ! filter_var($candidate, FILTER_VALIDATE_URL)) {
                continue;
            }

            $parts = parse_url($candidate);
            if (! is_array($parts) || empty($parts['host']) || empty($parts['path'])) {
                continue;
            }

            $host = strtolower($parts['host']);
            if (! in_array($host, ['instagram.com', 'www.instagram.com'], true)) {
                continue;
            }

            if (! preg_match('~^/(p|reel|tv)/[A-Za-z0-9_-]+/?$~', $parts['path'])) {
                continue;
            }

            return 'https://www.instagram.com' . rtrim($parts['path'], '/') . '/';
        }

        return '';
    }
}
