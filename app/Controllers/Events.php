<?php

namespace App\Controllers;

use App\Models\EventModel;

class Events extends BaseController
{
    public function index(): string
    {
        $events = (new EventModel())
            ->where('is_published', 1)
            ->orderBy('event_date', 'DESC')
            ->findAll();

        return view('public/events', ['events' => $events]);
    }

    public function show(string $slug): string
    {
        $event = (new EventModel())
            ->where('slug', $slug)
            ->where('is_published', 1)
            ->first();

        if (! $event) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Acara tidak ditemukan.');
        }

        return view('public/event_detail', ['event' => $event]);
    }
}
