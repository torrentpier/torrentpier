<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * Front Controller
 *
 * Handles routing for all non-excluded paths.
 * Excluded: /admin/*, /bt/*, direct *.php files
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

// Parse request URI
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH);

// Remove trailing slash for consistency (except root)
if ($path !== '/' && str_ends_with($path, '/')) {
    $path = rtrim($path, '/');
}

// ==============================================================
// Bypass conditions - excluded directories handled directly
// ==============================================================

$excludedPrefixes = ['/admin', '/bt'];

foreach ($excludedPrefixes as $prefix) {
    if (str_starts_with($path, $prefix)) {
        $file = __DIR__ . $path;
        if (is_file($file)) {
            require $file;
            exit;
        }
        // Try index.php for directory requests (e.g., /admin → /admin/index.php)
        if (is_file($file . '/index.php')) {
            require $file . '/index.php';
            exit;
        }
        http_response_code(404);
        exit;
    }
}

// 3. Direct PHP file requests
//    - Files with routes: 301 redirect to clean URL (SEO friendly)
//    - Other files: include it directly if exists (gradual migration)
//    - Non-existent files: 301 redirect to clean URL
if (str_ends_with($path, '.php') && $path !== '/index.php') {
    $cleanPath = substr($path, 0, -4);
    $query = $_SERVER['QUERY_STRING'] ?? '';

    // Files that have clean URL routes - redirect for SEO
    $routedFiles = ['/search', '/tracker', '/profile', '/privmsg', '/posting', '/poll', '/modcp', '/memberlist', '/login', '/info', '/terms'];
    if (in_array($cleanPath, $routedFiles)) {
        $redirectUrl = $cleanPath . ($query ? '?' . $query : '');
        header('Location: ' . $redirectUrl, true, 301);
        exit;
    }

    // Other .php files - include it directly if exists
    $filePath = __DIR__ . $path;
    if (is_file($filePath)) {
        require $filePath;
        exit;
    }

    // File doesn't exist - fall through to router (will show 404 page)
}

// 4. Static files (images, css, js, etc.) - let server handle
$staticExtensions = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'ico', 'svg', 'woff', 'woff2', 'ttf', 'eot', 'map'];
$extension = pathinfo($path, PATHINFO_EXTENSION);
if (in_array(strtolower($extension), $staticExtensions)) {
    return false; // Web server should serve static files
}

// ==============================================================
// Special case: Root path "/" = forum index
// Until index is migrated to controllers, use legacy file
// ==============================================================

if ($path === '/' || $path === '/index.php') {
    // Simply include the legacy index file - it handles everything itself
    require __DIR__ . '/index_legacy.php';
    exit;
}

// ==============================================================
// Router handling for clean URLs
// ==============================================================

define('BB_ROOT', './');
define('FRONT_CONTROLLER', true);

// Load common.php in global scope (BB_SCRIPT will be '' for routed pages)
// This ensures all globals are properly initialized
// Note: Using require_once because legacy files also require common.php
require_once __DIR__ . '/common.php';

// Initialize router
$router = \TorrentPier\Router\Router::getInstance();

// Load routes
$routes = require __DIR__ . '/library/routes.php';
$routes($router);

// Create PSR-7 request from globals
$request = \Laminas\Diactoros\ServerRequestFactory::fromGlobals();

try {
    // Dispatch the request
    $response = $router->dispatch($request);

    // Check if this is a legacy file that needs global scope execution
    if ($response->hasHeader('X-Legacy-Execute')) {
        // Execute in global scope - legacy files use $GLOBALS and need this
        require $GLOBALS['__legacy_controller_path'];
        exit;
    }

    // Emit the response
    (new \Laminas\HttpHandlerRunner\Emitter\SapiEmitter())->emit($response);
} catch (\League\Route\Http\Exception\NotFoundException $e) {
    // Route not found - show 404 page
    http_response_code(404);

    // Define BB_SCRIPT for page_header.php
    if (!defined('BB_SCRIPT')) {
        define('BB_SCRIPT', '404');
    }

    // common.php already loaded by front controller
    global $user;
    if ($user !== null && !defined('SESSION_STARTED')) {
        $user->session_start();
    }

    bb_die('PAGE_NOT_FOUND', 404);
} catch (\League\Route\Http\Exception\MethodNotAllowedException $e) {
    // Method not allowed
    http_response_code(405);
    header('Allow: ' . implode(', ', $e->getAllowedMethods()));
    echo 'Method Not Allowed';
} catch (\Throwable $e) {
    // Unexpected error
    http_response_code(500);

    if (defined('APP_ENV') && APP_ENV === 'development') {
        echo "Error: " . htmlspecialchars($e->getMessage()) . "\n";
        echo "File: " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "\n";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        error_log('Router error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        echo 'Internal Server Error';
    }
}
