<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * Application Bootstrap
 *
 * This file creates and configures the Application instance.
 * It is called once per request/command to bootstrap the container.
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

use TorrentPier\Application;

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new TorrentPier application instance
| which serves as the "glue" for all the components and is the IoC container
| for the system binding all the various parts.
|
*/

$app = new Application(
    dirname(__DIR__),
);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all the application's service providers which are
| used to bind services into the container. Service providers are totally
| responsible for loading and configuring all framework components.
|
*/

$providers = require __DIR__ . '/providers.php';

foreach ($providers as $provider) {
    $app->register($provider);
}

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
|
*/

return $app;
