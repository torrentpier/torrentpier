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
require __DIR__ . '/vendor/autoload.php';

$fc = new FrontController(__DIR__);
$result = $fc->resolve();

switch ($result['action']) {
    case FrontController::ACTION_REQUIRE_EXIT:
        require $result['file'];
        exit;

    case FrontController::ACTION_REDIRECT:
        \TorrentPier\Http\Response::permanentRedirect($result['url'])->send();
        exit;

    case FrontController::ACTION_NOT_FOUND:
        \TorrentPier\Http\Response::notFound()->send();
        exit;

    case FrontController::ACTION_STATIC:
        return false; // Let web server handle

    case FrontController::ACTION_ROUTE:
        // Bootstrap and route
        define('BB_ROOT', './');
        define('FRONT_CONTROLLER', true);
        require_once __DIR__ . '/common.php';

        $router = \TorrentPier\Router\Router::getInstance();

        // Load routes only if not already loaded (FrontController may have loaded them)
        if (!$router->areRoutesLoaded()) {
            $routes = require __DIR__ . '/library/routes.php';
            $routes($router);
            $router->setRoutesLoaded();
        }

        $request = \Laminas\Diactoros\ServerRequestFactory::fromGlobals();

        try {
            $response = $router->dispatch($request);

            // Legacy file needs global scope execution
            if ($response->hasHeader('X-Legacy-Execute')) {
                require $GLOBALS['__legacy_controller_path'];
                exit;
            }

            (new \Laminas\HttpHandlerRunner\Emitter\SapiEmitter())->emit($response);

        } catch (\League\Route\Http\Exception\NotFoundException $e) {
            http_response_code(404);
            if (!defined('BB_SCRIPT')) {
                define('BB_SCRIPT', '404');
            }
            if (!defined('SESSION_STARTED')) {
                user()->session_start();
            }
            bb_die('PAGE_NOT_FOUND', 404);

        } catch (\League\Route\Http\Exception\MethodNotAllowedException $e) {
            \TorrentPier\Http\Response::error('Method Not Allowed', 405)->send();

        } catch (\Throwable $e) {
            dev()->getWhoops()->handleException($e);
        }
        break;
}
