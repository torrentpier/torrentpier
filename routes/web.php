<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

use TorrentPier\Http\Controllers\RobotsController;
use TorrentPier\Router\LegacyAdapter;
use TorrentPier\Router\Router;
use TorrentPier\Router\SemanticUrl\LegacyRedirect;
use TorrentPier\Router\SemanticUrl\RouteAdapter;

/**
 * Route definitions for TorrentPier web application
 *
 * @param Router $router
 */
return function (Router $router): void {
    $basePath = dirname(__DIR__);

    // Middleware aliases (resolved from HttpKernel)
    $auth = $router->resolveMiddleware('auth');

    // ==============================================================
    // Global middleware (applied to all routes)
    // ==============================================================

    $router->middleware('session');

    // ==============================================================
    // Modern PSR-7 controllers (in app/Http/Controllers/)
    // ==============================================================

    $router->get('/robots.txt', new RobotsController);

    // ==============================================================
    // SEO-friendly semantic routes (must be defined before legacy routes)
    // Format: /type/slug.id
    // ==============================================================

    // Threads: /threads/slug.123
    $router->any('/threads/{params}[/]', new RouteAdapter('threads'));

    // Forums: /forums/slug.123
    $router->any('/forums/{params}[/]', new RouteAdapter('forums'));

    // Groups: /groups (list), /groups/slug.123, /groups/slug.123/edit
    $router->any('/groups[/]', new LegacyAdapter($basePath . '/app/Http/Controllers/group.php', 'group'))
        ->middleware($auth);
    $router->any('/groups/{params}/edit[/]', new RouteAdapter('groups_edit'))
        ->middleware($auth);
    $router->any('/groups/{params}[/]', new RouteAdapter('groups'))
        ->middleware($auth);

    // Categories: /categories/slug.123
    $router->any('/categories/{params}[/]', new RouteAdapter('categories'));

    // Profile standalone pages (static routes MUST come before variable routes)
    $router->any('/profile/bonus[/]', new LegacyAdapter($basePath . '/app/Http/Controllers/profile.php', 'profile', options: ['mode' => 'bonus']))
        ->middleware($auth);
    $router->any('/profile/watchlist[/]', new LegacyAdapter($basePath . '/app/Http/Controllers/profile.php', 'profile', options: ['mode' => 'watch']))
        ->middleware($auth);

    // Members: /members (list), /members/slug.123 (profile), /members/slug.123/email
    $router->any('/members[/]', new LegacyAdapter($basePath . '/app/Http/Controllers/memberlist.php'))
        ->middleware($auth);
    $router->any('/members/{params}/email[/]', new RouteAdapter('members', options: ['action' => 'email']));
    $router->any('/members/{params}[/]', new RouteAdapter('members'));

    // Standalone auth/account pages
    $router->any('/register[/]', new LegacyAdapter($basePath . '/app/Http/Controllers/profile.php', 'profile', options: ['mode' => 'register']));
    $router->any('/settings[/]', new LegacyAdapter($basePath . '/app/Http/Controllers/profile.php', 'profile', options: ['mode' => 'editprofile']))
        ->middleware($auth);
    $router->any('/password-recovery[/]', new LegacyAdapter($basePath . '/app/Http/Controllers/profile.php', 'profile', options: ['mode' => 'sendpassword']));
    $router->any('/activate/{u}/{key}[/]', new LegacyAdapter($basePath . '/app/Http/Controllers/profile.php', 'profile', options: ['mode' => 'activate']));

    // ==============================================================
    // Migrated controllers (in app/Http/Controllers/)
    // ==============================================================

    // Index (homepage) and forum map
    $router->any('/', new LegacyAdapter($basePath . '/app/Http/Controllers/index.php'));
    $router->get('/map[/]', new LegacyAdapter($basePath . '/app/Http/Controllers/index.php', options: ['map' => 1]));

    // Downloads: /dl/123, /dl/123/files, /dl/123/list
    $router->get('/dl/{t}[/]', new LegacyAdapter($basePath . '/app/Http/Controllers/dl.php'));
    $router->get('/dl/{t}/files[/]', new LegacyAdapter($basePath . '/app/Http/Controllers/filelist.php'));
    $router->any('/dl/{t}/list[/]', new LegacyAdapter($basePath . '/app/Http/Controllers/dl_list.php'));

    // Feed: /feed/f/123 (forum), /feed/u/123 (user)
    $router->get('/feed/{type}/{id}[/]', new LegacyAdapter($basePath . '/app/Http/Controllers/feed.php'))
        ->middleware($auth);

    // Playback: /playback/123
    $router->get('/playback/{t}[/]', new LegacyAdapter($basePath . '/app/Http/Controllers/playback_m3u.php'))
        ->middleware($auth);

    // GET
    $router->get('/info', new LegacyAdapter($basePath . '/app/Http/Controllers/info.php'));
    $router->get('/terms', new LegacyAdapter($basePath . '/app/Http/Controllers/terms.php'));

    // POST
    $router->post('/poll', new LegacyAdapter($basePath . '/app/Http/Controllers/poll.php', 'vote'))
        ->middleware($auth);

    // ANY (GET + POST)
    $router->any('/login', new LegacyAdapter($basePath . '/app/Http/Controllers/login.php'));
    $router->any('/modcp', new LegacyAdapter($basePath . '/app/Http/Controllers/modcp.php'))
        ->middleware($auth);
    $router->any('/posting', new LegacyAdapter($basePath . '/app/Http/Controllers/posting.php'));
    $router->any('/privmsg', new LegacyAdapter($basePath . '/app/Http/Controllers/privmsg.php', 'pm'))
        ->middleware($auth);
    $router->any('/search', new LegacyAdapter($basePath . '/app/Http/Controllers/search.php'))
        ->middleware($auth);
    $router->any('/tracker', new LegacyAdapter($basePath . '/app/Http/Controllers/tracker.php'))
        ->middleware($auth);

    // ==============================================================
    // Legacy routes with redirects to SEO-friendly URLs
    // ==============================================================

    // viewtopic?t=123 -> /threads/slug.123
    $router->any('/viewtopic', new LegacyRedirect(
        'threads',
        $basePath . '/app/Http/Controllers/viewtopic.php',
    ));

    // viewforum?f=123 -> /forums/slug.123
    $router->any('/viewforum', new LegacyRedirect(
        'forums',
        $basePath . '/app/Http/Controllers/viewforum.php',
    ));
};
