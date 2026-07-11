<?php

namespace App\Controllers;

class ApiDocs extends BaseController
{
    /**
     * Renders the Swagger UI page.
     * URL: GET /api/docs
     */
    public function index(): string
    {
        return view('api_docs');
    }
}
