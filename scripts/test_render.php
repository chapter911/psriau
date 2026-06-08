<?php

// Set front controller path
define('FCPATH', __DIR__ . '/../public/');

// Load Paths config
require __DIR__ . '/../app/Config/Paths.php';
$paths = new Config\Paths();

// Load the framework bootstrap file
require $paths->systemDirectory . '/Boot.php';

// Define environment constant for console boot
define('ENVIRONMENT', 'development');

// Boot Console/CLI environment (loads composer autoloader, constants, common functions, SPL autoloader, helpers)
\CodeIgniter\Boot::bootConsole($paths);

// Mock session variables in CodeIgniter Services
$session = \Config\Services::session();
$session->set([
    'role' => 'super_administrator',
    'userId' => 1,
    'username' => 'admin',
    'fullName' => 'Admin'
]);

// Instantiate a mock IncomingRequest instead of CLIRequest
$config = new \Config\App();
$uri = new \CodeIgniter\HTTP\SiteURI($config, 'admin/laporan/perjalanan-dinas');
$userAgent = new \CodeIgniter\HTTP\UserAgent();
$request = new \CodeIgniter\HTTP\IncomingRequest($config, $uri, null, $userAgent);

// Inject the mock request into the Services container so all shared calls receive it
\Config\Services::injectMock('request', $request);

// Instantiate controller
$controller = new \App\Controllers\Admin\Laporan();
$controller->initController(
    $request,
    \Config\Services::response(),
    \Config\Services::logger()
);

try {
    // Execute and capture rendering
    $result = $controller->perjalananDinas();
    if ($result instanceof \CodeIgniter\HTTP\ResponseInterface) {
        $body = $result->getBody();
    } else {
        $body = (string) $result;
    }
    
    // Output a snippet to verify layout elements are present
    echo "--- RENDER SUCCESS ---\n";
    echo "Total characters: " . strlen($body) . "\n";
    if (strpos($body, '<!DOCTYPE html>') !== false || strpos($body, '<html') !== false) {
        echo "Layout wrapper (layouts/admin) rendered successfully!\n";
    } else {
        echo "WARNING: Layout wrapper not detected!\n";
    }
    
    // Output first 200 chars and last 200 chars
    echo "\n--- FIRST 200 CHARS ---\n";
    echo substr($body, 0, 200) . "\n";
    echo "\n--- LAST 200 CHARS ---\n";
    echo substr($body, -200) . "\n";
    
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
