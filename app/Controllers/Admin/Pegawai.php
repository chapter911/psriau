<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Pegawai extends BaseController
{
    public function index()
    {
        return view('admin/master/pegawai', [
            'pageTitle' => 'Master Pegawai',
        ]);
    }
}