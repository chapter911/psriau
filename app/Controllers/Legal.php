<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Legal extends Controller
{
    public function privacy()
    {
        $file = FCPATH . 'privacy-policy.html';
        if (is_file($file)) {
            return $this->response->setHeader('Content-Type', 'text/html; charset=utf-8')
                ->setBody(file_get_contents($file));
        }

        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Privacy Policy');
    }

    public function terms()
    {
        $file = FCPATH . 'terms-of-service.html';
        if (is_file($file)) {
            return $this->response->setHeader('Content-Type', 'text/html; charset=utf-8')
                ->setBody(file_get_contents($file));
        }

        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Terms of Service');
    }
}
