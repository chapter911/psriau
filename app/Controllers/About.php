<?php

namespace App\Controllers;

use App\Models\HomeSettingModel;

class About extends BaseController
{
    public function index(): string
    {
        $setting = (new HomeSettingModel())->first();

        return view('public/about', [
            'aboutIntro' => $setting['about_intro'] ?? '',
        ]);
    }
}
