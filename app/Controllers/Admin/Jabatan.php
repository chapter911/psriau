<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Jabatan extends BaseController
{
    public function index()
    {
        return view('admin/master/jabatan', [
            'pageTitle' => 'Master Jabatan',
        ]);
    }
}