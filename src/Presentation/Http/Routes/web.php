<?php

declare(strict_types=1);

use TorrentPier\Infrastructure\Http\Router;
use TorrentPier\Presentation\Http\Controllers\Web\HelloWorldController;
use TorrentPier\Presentation\Http\Controllers\Web\LegacyController;

return function (Router $router): void {
    // Hello World test routes
    $router->get('/hello', [HelloWorldController::class, 'index']);
    $router->get('/hello/json', [HelloWorldController::class, 'json']);

    // Legacy controller routes (hacky but organized approach)
    $legacyRoutes = [
        'ajax.php',
        'dl.php',
        'dl_list.php',
        'feed.php',
        'filelist.php',
        'group.php',
        'group_edit.php',
        'index.php',
        'info.php',
        'login.php',
        'memberlist.php',
        'modcp.php',
        'playback_m3u.php',
        'poll.php',
        'posting.php',
        'privmsg.php',
        'profile.php',
        'search.php',
        'terms.php',
        'tracker.php',
        'viewforum.php',
        'viewtopic.php',
    ];

    foreach ($legacyRoutes as $route) {
        // Route with .php extension
        $router->any('/' . $route, [LegacyController::class, 'handle']);

        // Route without .php extension (e.g., /terms for /terms.php)
        $routeWithoutExtension = str_replace('.php', '', $route);
        $router->any('/' . $routeWithoutExtension, [LegacyController::class, 'handle']);
    }

    // Root route should serve the legacy index.php controller
    $router->any('/', [LegacyController::class, 'handle']);

    // Future modern routes will be added here
};
