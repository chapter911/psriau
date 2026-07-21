<?php
define('ENVIRONMENT', 'development');
require 'public/index.php';

$request = \Config\Services::request();
$request->setGlobal('get', [
    'zoom' => '10',
    'south' => '0.35',
    'west' => '101.35',
    'north' => '0.65',
    'east' => '101.65'
]);

$controller = new \App\Controllers\Admin\Dashboard();
$controller->initController($request, \Config\Services::response(), \Config\Services::logger());
$response = $controller->mapContourData();
echo $response->getBody();
