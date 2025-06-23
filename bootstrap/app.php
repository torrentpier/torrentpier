<?php

declare(strict_types=1);

/**
 * Application Bootstrap
 * 
 * This file creates and configures the application instance
 */

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// Load legacy common.php only if not already loaded (will be loaded by LegacyController when needed)
// This prevents header conflicts for modern API routes
if (!defined('BB_PATH')) {
    // Only load for legacy routes - modern routes will skip this
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $urlPath = parse_url($requestUri, PHP_URL_PATH) ?: $requestUri;
    $isLegacyRoute = str_ends_with($urlPath, '.php') || $requestUri === '/' || str_contains($requestUri, 'tracker') || str_contains($requestUri, 'forum');
    
    if ($isLegacyRoute) {
        require_once dirname(__DIR__) . '/common.php';
    }
}

// Define application constants
define('IN_TORRENTPIER', true);

// Load container bootstrap
require_once __DIR__ . '/container.php';

// Create the application container
$container = createContainer(dirname(__DIR__));

// Get the Router instance (it will be created and registered by RouteServiceProvider)
$router = $container->make(\App\Http\Routing\Router::class);

// Return the router for handling requests
return $router;