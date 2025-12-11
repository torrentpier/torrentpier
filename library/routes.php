<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

use TorrentPier\Controllers\RobotsController;
use TorrentPier\Http\Middleware\TrailingSlashRedirect;
use TorrentPier\Router\LegacyAdapter;
use TorrentPier\Router\Router;
use TorrentPier\Router\SemanticUrl\LegacyRedirect;
use TorrentPier\Router\SemanticUrl\RouteAdapter;

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
    // SEO-friendly semantic routes (must be defined before legacy routes)
    // Format: /type/slug.id/
    // ==============================================================

    // Threads: /threads/slug.123/
    $router->any('/threads/{params}/', new RouteAdapter('threads'));
    $router->get('/threads/{params}', new TrailingSlashRedirect());

    // Forums: /forums/slug.123/
    $router->any('/forums/{params}/', new RouteAdapter('forums'));
    $router->get('/forums/{params}', new TrailingSlashRedirect());

    // Groups: /groups/ (list), /groups/slug.123/, /groups/slug.123/edit/
    $router->any('/groups/', new LegacyAdapter($basePath . '/src/Controllers/group.php', 'group', options: ['manage_session' => true]));
    $router->get('/groups', new TrailingSlashRedirect());
    $router->any('/groups/{params}/edit/', new RouteAdapter('groups_edit', options: ['manage_session' => true]));
    $router->get('/groups/{params}/edit', new TrailingSlashRedirect());
    $router->any('/groups/{params}/', new RouteAdapter('groups', options: ['manage_session' => true]));
    $router->get('/groups/{params}', new TrailingSlashRedirect());

    // Categories: /categories/slug.123/
    $router->any('/categories/{params}/', new RouteAdapter('categories', options: ['manage_session' => true]));
    $router->get('/categories/{params}', new TrailingSlashRedirect());

    // Profile standalone pages (static routes MUST come before variable routes)
    $router->any('/profile/bonus/', new LegacyAdapter($basePath . '/src/Controllers/profile.php', 'profile', options: ['mode' => 'bonus']));
    $router->get('/profile/bonus', new TrailingSlashRedirect());
    $router->any('/profile/watchlist/', new LegacyAdapter($basePath . '/src/Controllers/profile.php', 'profile', options: ['mode' => 'watch']));
    $router->get('/profile/watchlist', new TrailingSlashRedirect());

    // Members: /members/ (list), /members/slug.123/ (profile), /members/slug.123/email/
    $router->any('/members/', new LegacyAdapter($basePath . '/src/Controllers/memberlist.php', options: ['manage_session' => true]));
    $router->get('/members', new TrailingSlashRedirect());
    $router->any('/members/{params}/email/', new RouteAdapter('members', options: ['action' => 'email']));
    $router->get('/members/{params}/email', new TrailingSlashRedirect());
    $router->any('/members/{params}/', new RouteAdapter('members'));
    $router->get('/members/{params}', new TrailingSlashRedirect());

    // Standalone auth/account pages
    $router->any('/register/', new LegacyAdapter($basePath . '/src/Controllers/profile.php', 'profile', options: ['mode' => 'register']));
    $router->get('/register', new TrailingSlashRedirect());
    $router->any('/settings/', new LegacyAdapter($basePath . '/src/Controllers/profile.php', 'profile', options: ['mode' => 'editprofile']));
    $router->get('/settings', new TrailingSlashRedirect());
    $router->any('/password-recovery/', new LegacyAdapter($basePath . '/src/Controllers/profile.php', 'profile', options: ['mode' => 'sendpassword']));
    $router->get('/password-recovery', new TrailingSlashRedirect());
    $router->any('/activate/{key}/', new LegacyAdapter($basePath . '/src/Controllers/profile.php', 'profile', options: ['mode' => 'activate']));
    $router->get('/activate/{key}', new TrailingSlashRedirect());

    // ==============================================================
    // Migrated controllers (in src/Controllers/)
    // ==============================================================

    // Index (homepage) and forum map
    $router->any('/', new LegacyAdapter($basePath . '/src/Controllers/index.php', options: ['manage_session' => true]));
    $router->get('/map/', new LegacyAdapter($basePath . '/src/Controllers/index.php', options: ['manage_session' => true, 'map' => 1]));
    $router->get('/map', new TrailingSlashRedirect());

    // Downloads: /dl/123/, /dl/123/files/, /dl/123/list/
    $router->get('/dl/{t}/', new LegacyAdapter($basePath . '/src/Controllers/dl.php'));
    $router->get('/dl/{t}', new TrailingSlashRedirect());
    $router->get('/dl/{t}/files/', new LegacyAdapter($basePath . '/src/Controllers/filelist.php'));
    $router->get('/dl/{t}/files', new TrailingSlashRedirect());
    $router->any('/dl/{t}/list/', new LegacyAdapter($basePath . '/src/Controllers/dl_list.php'));
    $router->get('/dl/{t}/list', new TrailingSlashRedirect());

    // Feed: /feed/f/123/ (forum), /feed/u/123/ (user)
    $router->get('/feed/{type}/{id}/', new LegacyAdapter($basePath . '/src/Controllers/feed.php', options: ['manage_session' => true]));
    $router->get('/feed/{type}/{id}', new TrailingSlashRedirect());

    // Playback: /playback/123/
    $router->get('/playback/{t}/', new LegacyAdapter($basePath . '/src/Controllers/playback_m3u.php', options: ['manage_session' => true]));
    $router->get('/playback/{t}', new TrailingSlashRedirect());

    // GET
    $router->get('/info', new LegacyAdapter($basePath . '/src/Controllers/info.php'));
    $router->get('/terms', new LegacyAdapter($basePath . '/src/Controllers/terms.php'));

    // POST
    $router->post('/poll', new LegacyAdapter($basePath . '/src/Controllers/poll.php', 'vote', ['manage_session' => true]));

    // ANY (GET + POST)
    $router->any('/ajax', new LegacyAdapter($basePath . '/src/Controllers/ajax.php'));
    $router->any('/login', new LegacyAdapter($basePath . '/src/Controllers/login.php'));
    $router->any('/modcp', new LegacyAdapter($basePath . '/src/Controllers/modcp.php', options: ['manage_session' => true]));
    $router->any('/posting', new LegacyAdapter($basePath . '/src/Controllers/posting.php'));
    $router->any('/privmsg', new LegacyAdapter($basePath . '/src/Controllers/privmsg.php', 'pm', ['manage_session' => true]));
    $router->any('/search', new LegacyAdapter($basePath . '/src/Controllers/search.php', options: ['manage_session' => true]));
    $router->any('/tracker', new LegacyAdapter($basePath . '/src/Controllers/tracker.php', options: ['manage_session' => true]));

    // ==============================================================
    // Legacy routes with redirects to SEO-friendly URLs
    // ==============================================================

    // viewtopic?t=123 -> /threads/slug.123/
    $router->any('/viewtopic', new LegacyRedirect(
        'threads',
        $basePath . '/src/Controllers/viewtopic.php'
    ));

    // viewforum?f=123 -> /forums/slug.123/
    $router->any('/viewforum', new LegacyRedirect(
        'forums',
        $basePath . '/src/Controllers/viewforum.php'
    ));

    // profile?mode=viewprofile&u=123 -> /members/slug.123/
    // Note: Only redirects for mode=viewprofile, other modes use fallback controller
    $router->any('/profile', new LegacyRedirect(
        'members',
        $basePath . '/src/Controllers/profile.php'
    ));
};
