<?php

http_response_code(200);

$publicPath = __DIR__ . '/public';
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$requestPath = rawurldecode($requestPath);

if ($requestPath !== '/' && strpos($requestPath, '/public/') !== 0) {
	$candidate = $publicPath . $requestPath;
	$realPublicPath = realpath($publicPath);
	$realCandidate = realpath($candidate);

	if ($realPublicPath !== false && $realCandidate !== false && strncmp($realCandidate, $realPublicPath, strlen($realPublicPath)) === 0 && is_file($realCandidate)) {
		$extension = strtolower(pathinfo($realCandidate, PATHINFO_EXTENSION));
		$mimeTypes = [
			'css' => 'text/css; charset=UTF-8',
			'js' => 'application/javascript; charset=UTF-8',
			'mjs' => 'application/javascript; charset=UTF-8',
			'json' => 'application/json; charset=UTF-8',
			'map' => 'application/json; charset=UTF-8',
			'svg' => 'image/svg+xml',
			'png' => 'image/png',
			'jpg' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'gif' => 'image/gif',
			'webp' => 'image/webp',
			'ico' => 'image/x-icon',
			'woff' => 'font/woff',
			'woff2' => 'font/woff2',
			'ttf' => 'font/ttf',
			'eot' => 'application/vnd.ms-fontobject',
			'otf' => 'font/otf',
			'html' => 'text/html; charset=UTF-8',
			'htm' => 'text/html; charset=UTF-8',
		];

		header('Content-Type: ' . ($mimeTypes[$extension] ?? (function_exists('mime_content_type') ? (mime_content_type($realCandidate) ?: 'application/octet-stream') : 'application/octet-stream')));
		header('Content-Length: ' . filesize($realCandidate));
		header('Cache-Control: public, max-age=31536000');

		if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'HEAD') {
			readfile($realCandidate);
		}

		exit;
	}
}

chdir(__DIR__ . '/public');
require_once __DIR__ . '/public/index.php';
