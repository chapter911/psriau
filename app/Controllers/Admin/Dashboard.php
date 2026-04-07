<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ArticleModel;
use App\Models\EventModel;
use App\Models\HomeSlideModel;

class Dashboard extends BaseController
{
    public function index(): string
    {
        $eventModel   = new EventModel();
        $articleModel = new ArticleModel();
        $slideModel   = new HomeSlideModel();

        $eventCount = $eventModel->countAllResults();
        $eventPublishedCount = (new EventModel())->where('is_published', 1)->countAllResults();
        $eventDraftCount = max(0, $eventCount - $eventPublishedCount);

        $articleCount = $articleModel->countAllResults();
        $articlePublishedCount = (new ArticleModel())->where('is_published', 1)->countAllResults();
        $articleDraftCount = max(0, $articleCount - $articlePublishedCount);

        $slideCount = $slideModel->countAllResults();
        $slideActiveCount = (new HomeSlideModel())->where('is_active', 1)->countAllResults();

        $latestEvents = (new EventModel())
            ->select('title, event_date, is_published')
            ->orderBy('updated_at', 'DESC')
            ->findAll(5);

        $latestInstagramPosts = (new ArticleModel())
            ->select('title, content, published_at, is_published')
            ->orderBy('updated_at', 'DESC')
            ->findAll(5);

        return view('admin/dashboard', [
            'pageTitle' => 'Dashboard Admin',
            'eventCount' => $eventCount,
            'eventPublishedCount' => $eventPublishedCount,
            'eventDraftCount' => $eventDraftCount,
            'articleCount' => $articleCount,
            'articlePublishedCount' => $articlePublishedCount,
            'articleDraftCount' => $articleDraftCount,
            'slideCount' => $slideCount,
            'slideActiveCount' => $slideActiveCount,
            'latestEvents' => $latestEvents,
            'latestInstagramPosts' => $latestInstagramPosts,
        ]);
    }
}
