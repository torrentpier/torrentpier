<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

use TorrentPier\Controllers\RobotsController;
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
    // Modern PSR-7 controllers (in src/Controllers/)
    // ==============================================================

    $router->get('/robots.txt', new RobotsController());

    // ==============================================================
    // Migrated controllers (in src/Controllers/)
    // ==============================================================

    // GET
    $router->get('/dl', new LegacyAdapter($basePath . '/src/Controllers/dl.php'));
    $router->get('/feed', new LegacyAdapter($basePath . '/src/Controllers/feed.php', options: ['manage_session' => true]));
    $router->get('/filelist', new LegacyAdapter($basePath . '/src/Controllers/filelist.php'));
    $router->get('/info', new LegacyAdapter($basePath . '/src/Controllers/info.php'));
    $router->get('/playback_m3u', new LegacyAdapter($basePath . '/src/Controllers/playback_m3u.php', options: ['manage_session' => true]));
    $router->get('/terms', new LegacyAdapter($basePath . '/src/Controllers/terms.php'));

    // POST
    $router->post('/poll', new LegacyAdapter($basePath . '/src/Controllers/poll.php', 'vote', ['manage_session' => true]));

    // ANY (GET + POST)
    $router->any('/ajax', new LegacyAdapter($basePath . '/src/Controllers/ajax.php'));
    $router->any('/dl_list', new LegacyAdapter($basePath . '/src/Controllers/dl_list.php'));
    $router->any('/group', new LegacyAdapter($basePath . '/src/Controllers/group.php', options: ['manage_session' => true]));
    $router->any('/group_edit', new LegacyAdapter($basePath . '/src/Controllers/group_edit.php', options: ['manage_session' => true]));
    $router->any('/login', new LegacyAdapter($basePath . '/src/Controllers/login.php'));
    $router->any('/memberlist', new LegacyAdapter($basePath . '/src/Controllers/memberlist.php', options: ['manage_session' => true]));
    $router->any('/modcp', new LegacyAdapter($basePath . '/src/Controllers/modcp.php', options: ['manage_session' => true]));
    $router->any('/posting', new LegacyAdapter($basePath . '/src/Controllers/posting.php'));
    $router->any('/privmsg', new LegacyAdapter($basePath . '/src/Controllers/privmsg.php', 'pm', ['manage_session' => true]));
    $router->any('/profile', new LegacyAdapter($basePath . '/src/Controllers/profile.php'));
    $router->any('/search', new LegacyAdapter($basePath . '/src/Controllers/search.php', options: ['manage_session' => true]));
    $router->any('/tracker', new LegacyAdapter($basePath . '/src/Controllers/tracker.php', options: ['manage_session' => true]));
    $router->any('/viewforum', new LegacyAdapter($basePath . '/src/Controllers/viewforum.php', 'forum'));
    $router->any('/viewtopic', new LegacyAdapter($basePath . '/src/Controllers/viewtopic.php', 'topic'));
};
