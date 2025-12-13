<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * Front Controller Entry Point
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

use TorrentPier\Router\FrontController;

// Load autoloader (provides all classes including TorrentPier\Router\*)
require dirname(__DIR__) . '/vendor/autoload.php';

$fc = new FrontController(dirname(__DIR__), __DIR__);
$result = $fc->resolve();

switch ($result['action']) {
    case FrontController::ACTION_REQUIRE_EXIT:
        require $result['file'];
        exit;

    case FrontController::ACTION_REDIRECT:
        TorrentPier\Http\Response::permanentRedirect($result['url'])->send();
        exit;

    case FrontController::ACTION_NOT_FOUND:
        TorrentPier\Http\Response::notFound()->send();
        exit;

    case FrontController::ACTION_STATIC:
        // For PHP built-in server, return false lets the server handle static files
        // For nginx/php-fpm (like Herd), we need to serve the file ourselves
        if (PHP_SAPI === 'cli-server') {
            return false;
        }

        $requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Sitemap rewrite: /sitemap.xml -> /storage/sitemap/sitemap.xml
        if ($requestPath === '/sitemap.xml') {
            $requestPath = '/storage/sitemap/sitemap.xml';
        }

        // Check if a file exists and serve it
        $staticFile = __DIR__ . $requestPath;
        if (is_file($staticFile)) {
            $mimeTypes = [
                'xml' => 'application/xml',
                'css' => 'text/css',
                'js' => 'application/javascript',
                'json' => 'application/json',
            ];
            $ext = strtolower(pathinfo($staticFile, PATHINFO_EXTENSION));
            $contentType = $mimeTypes[$ext] ?? mime_content_type($staticFile) ?: 'application/octet-stream';
            header('Content-Type: ' . $contentType);
            header('Content-Length: ' . filesize($staticFile));
            readfile($staticFile);
            exit;
        }
        // File not found - fall through to 404
        TorrentPier\Http\Response::notFound()->send();
        exit;

    case FrontController::ACTION_ROUTE:
        // Bootstrap and route
        define('BB_ROOT', './');
        define('FRONT_CONTROLLER', true);
        require_once dirname(__DIR__) . '/library/common.php';

        $router = TorrentPier\Router\Router::getInstance();

        // Load routes only if not already loaded (FrontController may have loaded them)
        if (!$router->areRoutesLoaded()) {
            $routes = require dirname(__DIR__) . '/routes/web.php';
            $routes($router);
            $router->setRoutesLoaded();
        }

        $request = Laminas\Diactoros\ServerRequestFactory::fromGlobals();

        try {
            $response = $router->dispatch($request);

            // Legacy file needs global scope execution
            if ($response->hasHeader('X-Legacy-Execute')) {
                require $GLOBALS['__legacy_controller_path'];
                exit;
            }

            (new Laminas\HttpHandlerRunner\Emitter\SapiEmitter())->emit($response);
        } catch (League\Route\Http\Exception\NotFoundException $e) {
            http_response_code(404);
            if (!defined('BB_SCRIPT')) {
                define('BB_SCRIPT', '404');
            }
            if (!defined('SESSION_STARTED')) {
                user()->session_start();
            }
            bb_die('PAGE_NOT_FOUND', 404);
        } catch (League\Route\Http\Exception\MethodNotAllowedException $e) {
            TorrentPier\Http\Response::error('Method Not Allowed', 405)->send();
        } catch (Throwable $e) {
            whoops()->whoops->handleException($e);
        }
        break;
}
