<?php

declare(strict_types=1);

/**
 * TorrentPier - The public entry point for the web application
 * 
 * This is the front controller that handles all incoming HTTP requests
 * Following Laravel-style architecture with illuminate/http
 */

// Define the application start time
define('TORRENTPIER_START', microtime(true));

// Register the autoloader
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap the application
$router = require_once __DIR__ . '/../bootstrap/app.php';

// Create Laravel-style request from globals
$request = \Illuminate\Http\Request::createFromGlobals();

try {
    // Dispatch the request through the router
    $response = $router->dispatch($request);
} catch (\Exception $e) {
    // Simple error handling - create Laravel-style response
    $response = new \Illuminate\Http\Response('Internal Server Error', 500);
}

// Send the response - handle JsonResponse specially if headers already sent
if ($response instanceof \Illuminate\Http\JsonResponse && headers_sent()) {
    echo $response->getContent();
} else {
    $response->send();
}