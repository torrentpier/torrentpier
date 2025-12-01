<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

use TorrentPier\Router\Router;
use TorrentPier\Router\LegacyAdapter;

/**
 * Route definitions for TorrentPier
 *
 * @param Router $router
 */
return function (Router $router): void {
    $basePath = dirname(__DIR__);

    // ==============================================================
    // Migrated controllers (in src/Controllers/)
    // ==============================================================

    $router->any('/terms', new LegacyAdapter(
        $basePath . '/src/Controllers/terms.php',
        'terms'
    ));

    // Tracker needs custom session options (req_login)
    $router->any('/tracker', new LegacyAdapter(
        $basePath . '/src/Controllers/tracker.php',
        'tracker',
        ['manage_session' => true]
    ));

    // ==============================================================
    // Legacy files with clean URLs (self-bootstrap)
    // ==============================================================

    $legacyRoutes = [
        '/search',
        '/profile',
        '/privmsg',
        '/posting',
        '/poll',
        '/modcp',
        '/memberlist',
        '/login',
        '/info',
    ];

    foreach ($legacyRoutes as $path) {
        $script = ltrim($path, '/');
        $router->any($path, new LegacyAdapter(
            $basePath . '/' . $script . '.php',
            $script,
            ['self_bootstrap' => true]
        ));
    }
};
